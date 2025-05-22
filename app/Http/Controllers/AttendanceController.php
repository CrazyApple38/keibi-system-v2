<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Shift;
use App\Models\Guard;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * 勤怠管理Controller
 * 
 * 警備員の勤怠情報の管理を担当
 * - 出退勤記録の管理
 * - 勤怠統計情報の提供
 * - 承認フロー管理
 * - 勤怠データの分析
 */
class AttendanceController extends Controller
{
    /**
     * 勤怠一覧表示
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Attendance::with(['shift', 'guard', 'project']);
            
            // ユーザー権限に基づくフィルタリング
            $user = Auth::user();
            if ($user->role === 'guard') {
                $query->whereHas('shift', function($q) use ($user) {
                    $q->where('guard_id', $user->guard_id);
                });
            } elseif ($user->role === 'manager') {
                $query->whereIn('company_id', [$user->company_id]);
            }
            
            // 検索・フィルタリング
            if ($request->filled('guard_id')) {
                $query->whereHas('shift', function($q) use ($request) {
                    $q->where('guard_id', $request->guard_id);
                });
            }
            
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('date_from')) {
                $query->where('actual_start_time', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->where('actual_end_time', '<=', $request->date_to . ' 23:59:59');
            }
            
            // 承認状態での絞り込み
            if ($request->filled('approval_status')) {
                if ($request->approval_status === 'approved') {
                    $query->whereNotNull('approved_at');
                } elseif ($request->approval_status === 'pending') {
                    $query->whereNull('approved_at');
                }
            }
            
            // ソート
            $sortField = $request->get('sort', 'actual_start_time');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
            
            // ページネーション
            $attendances = $query->paginate(15);
            
            // API リクエストの場合
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'attendances' => $attendances,
                    'statistics' => $this->getAttendanceStatistics($request)
                ]);
            }
            
            return view('attendance.index', compact('attendances'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('勤怠一覧の取得に失敗しました。');
        }
    }
    
    /**
     * 勤怠詳細表示
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            $attendance = Attendance::with([
                'shift.guard',
                'project',
                'approver'
            ])->findOrFail($id);
            
            // アクセス権限チェック
            if (!$this->canAccessAttendance($attendance)) {
                return $this->errorResponse('アクセス権限がありません。', 403);
            }
            
            // 勤怠詳細統計
            $statistics = [
                'planned_duration' => $attendance->planned_duration_hours,
                'actual_duration' => $attendance->actual_duration_hours,
                'overtime_hours' => $attendance->overtime_hours,
                'break_duration' => $attendance->break_duration_minutes,
                'late_minutes' => $attendance->late_minutes,
                'early_leave_minutes' => $attendance->early_leave_minutes
            ];
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'attendance' => $attendance,
                    'statistics' => $statistics
                ]);
            }
            
            return view('attendance.show', compact('attendance', 'statistics'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('勤怠詳細の取得に失敗しました。');
        }
    }
    
    /**
     * 勤怠記録作成フォーム表示
     * 
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $shifts = Shift::with(['guard', 'project'])
                          ->where('date', '>=', now()->subDays(7))
                          ->where('date', '<=', now()->addDays(7))
                          ->get();
            
            $guards = Guard::active()->get();
            $projects = Project::active()->get();
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'shifts' => $shifts,
                    'guards' => $guards,
                    'projects' => $projects
                ]);
            }
            
            return view('attendance.create', compact('shifts', 'guards', 'projects'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('勤怠記録作成フォームの表示に失敗しました。');
        }
    }
    
    /**
     * 勤怠記録作成
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validator = Validator::make($request->all(), [
                'shift_id' => 'required|exists:shifts,id',
                'actual_start_time' => 'required|date',
                'actual_end_time' => 'required|date|after:actual_start_time',
                'break_duration' => 'nullable|integer|min:0',
                'memo' => 'nullable|string|max:500',
                'location_info' => 'nullable|json'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            $shift = Shift::findOrFail($request->shift_id);
            
            // 自動計算項目
            $actualStart = Carbon::parse($request->actual_start_time);
            $actualEnd = Carbon::parse($request->actual_end_time);
            $plannedStart = Carbon::parse($shift->start_time);
            $plannedEnd = Carbon::parse($shift->end_time);
            
            $actualDuration = $actualStart->diffInMinutes($actualEnd);
            $plannedDuration = $plannedStart->diffInMinutes($plannedEnd);
            $breakDuration = $request->break_duration ?? 0;
            
            // 遅刻・早退時間計算
            $lateMinutes = $actualStart->gt($plannedStart) ? $actualStart->diffInMinutes($plannedStart) : 0;
            $earlyLeaveMinutes = $actualEnd->lt($plannedEnd) ? $plannedEnd->diffInMinutes($actualEnd) : 0;
            
            $attendance = Attendance::create([
                'shift_id' => $request->shift_id,
                'project_id' => $shift->project_id,
                'actual_start_time' => $request->actual_start_time,
                'actual_end_time' => $request->actual_end_time,
                'break_duration' => $breakDuration,
                'actual_duration' => $actualDuration - $breakDuration,
                'planned_duration' => $plannedDuration,
                'late_minutes' => $lateMinutes,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'overtime_hours' => max(0, ($actualDuration - $breakDuration - $plannedDuration) / 60),
                'status' => 'recorded',
                'memo' => $request->memo,
                'location_info' => $request->location_info,
                'created_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'attendance' => $attendance->load(['shift.guard', 'project']),
                'message' => '勤怠記録を作成しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('勤怠記録の作成に失敗しました。');
        }
    }
    
    /**
     * 勤怠記録編集フォーム表示
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        try {
            $attendance = Attendance::with(['shift.guard', 'project'])->findOrFail($id);
            
            if (!$this->canEditAttendance($attendance)) {
                return $this->errorResponse('編集権限がありません。', 403);
            }
            
            $shifts = Shift::with(['guard', 'project'])->get();
            $projects = Project::active()->get();
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'attendance' => $attendance,
                    'shifts' => $shifts,
                    'projects' => $projects
                ]);
            }
            
            return view('attendance.edit', compact('attendance', 'shifts', 'projects'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('勤怠記録編集フォームの表示に失敗しました。');
        }
    }
    
    /**
     * 勤怠記録更新
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $attendance = Attendance::findOrFail($id);
            
            if (!$this->canEditAttendance($attendance)) {
                return $this->errorResponse('編集権限がありません。', 403);
            }
            
            $validator = Validator::make($request->all(), [
                'actual_start_time' => 'required|date',
                'actual_end_time' => 'required|date|after:actual_start_time',
                'break_duration' => 'nullable|integer|min:0',
                'memo' => 'nullable|string|max:500',
                'location_info' => 'nullable|json'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            // 自動計算項目を再計算
            $shift = $attendance->shift;
            $actualStart = Carbon::parse($request->actual_start_time);
            $actualEnd = Carbon::parse($request->actual_end_time);
            $plannedStart = Carbon::parse($shift->start_time);
            $plannedEnd = Carbon::parse($shift->end_time);
            
            $actualDuration = $actualStart->diffInMinutes($actualEnd);
            $plannedDuration = $plannedStart->diffInMinutes($plannedEnd);
            $breakDuration = $request->break_duration ?? 0;
            
            $lateMinutes = $actualStart->gt($plannedStart) ? $actualStart->diffInMinutes($plannedStart) : 0;
            $earlyLeaveMinutes = $actualEnd->lt($plannedEnd) ? $plannedEnd->diffInMinutes($actualEnd) : 0;
            
            $attendance->update([
                'actual_start_time' => $request->actual_start_time,
                'actual_end_time' => $request->actual_end_time,
                'break_duration' => $breakDuration,
                'actual_duration' => $actualDuration - $breakDuration,
                'late_minutes' => $lateMinutes,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'overtime_hours' => max(0, ($actualDuration - $breakDuration - $plannedDuration) / 60),
                'memo' => $request->memo,
                'location_info' => $request->location_info,
                'updated_by' => Auth::id(),
                'status' => 'updated'
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'attendance' => $attendance->load(['shift.guard', 'project']),
                'message' => '勤怠記録を更新しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('勤怠記録の更新に失敗しました。');
        }
    }
    
    /**
     * 勤怠記録承認
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $attendance = Attendance::findOrFail($id);
            
            if (!$this->canApproveAttendance()) {
                return $this->errorResponse('承認権限がありません。', 403);
            }
            
            $validator = Validator::make($request->all(), [
                'approval_memo' => 'nullable|string|max:300'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            $attendance->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
                'approval_memo' => $request->approval_memo
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'attendance' => $attendance->load(['shift.guard', 'project', 'approver']),
                'message' => '勤怠記録を承認しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('勤怠記録の承認に失敗しました。');
        }
    }
    
    /**
     * 勤怠統計情報取得
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function statistics(Request $request)
    {
        try {
            $statistics = $this->getAttendanceStatistics($request);
            
            return $this->successResponse($statistics);
            
        } catch (\Exception $e) {
            return $this->errorResponse('勤怠統計の取得に失敗しました。');
        }
    }
    
    /**
     * 勤怠レポート生成
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function report(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'report_type' => 'required|in:monthly,weekly,daily,custom',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'guard_id' => 'nullable|exists:guards,id',
                'project_id' => 'nullable|exists:projects,id'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            $query = Attendance::with(['shift.guard', 'project'])
                              ->whereBetween('actual_start_time', [$request->start_date, $request->end_date]);
            
            if ($request->filled('guard_id')) {
                $query->whereHas('shift', function($q) use ($request) {
                    $q->where('guard_id', $request->guard_id);
                });
            }
            
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            
            $attendances = $query->get();
            
            $report = [
                'period' => [
                    'start' => $request->start_date,
                    'end' => $request->end_date,
                    'type' => $request->report_type
                ],
                'summary' => [
                    'total_records' => $attendances->count(),
                    'total_hours' => round($attendances->sum('actual_duration') / 60, 2),
                    'total_overtime' => round($attendances->sum('overtime_hours'), 2),
                    'average_duration' => round($attendances->avg('actual_duration') / 60, 2),
                    'late_count' => $attendances->where('late_minutes', '>', 0)->count(),
                    'early_leave_count' => $attendances->where('early_leave_minutes', '>', 0)->count(),
                    'approval_rate' => $attendances->count() > 0 ? 
                        round($attendances->whereNotNull('approved_at')->count() / $attendances->count() * 100, 1) : 0
                ],
                'details' => $attendances->groupBy(function($item) {
                    return Carbon::parse($item->actual_start_time)->format('Y-m-d');
                })->map(function($group) {
                    return [
                        'date' => $group->first()->actual_start_time,
                        'records' => $group->count(),
                        'total_hours' => round($group->sum('actual_duration') / 60, 2),
                        'overtime_hours' => round($group->sum('overtime_hours'), 2),
                        'late_count' => $group->where('late_minutes', '>', 0)->count()
                    ];
                })->values()
            ];
            
            return $this->successResponse([
                'report' => $report,
                'attendances' => $attendances
            ]);
            
        } catch (\Exception $e) {
            return $this->errorResponse('勤怠レポートの生成に失敗しました。');
        }
    }
    
    /**
     * 勤怠統計情報を取得
     * 
     * @param Request $request
     * @return array
     */
    private function getAttendanceStatistics(Request $request)
    {
        $query = Attendance::query();
        
        // フィルタリング条件適用
        if ($request->filled('guard_id')) {
            $query->whereHas('shift', function($q) use ($request) {
                $q->where('guard_id', $request->guard_id);
            });
        }
        
        if ($request->filled('date_from')) {
            $query->where('actual_start_time', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('actual_end_time', '<=', $request->date_to . ' 23:59:59');
        }
        
        $attendances = $query->get();
        
        return [
            'total_records' => $attendances->count(),
            'total_hours' => round($attendances->sum('actual_duration') / 60, 2),
            'average_hours' => round($attendances->avg('actual_duration') / 60, 2),
            'total_overtime' => round($attendances->sum('overtime_hours'), 2),
            'punctuality_rate' => $attendances->count() > 0 ? 
                round($attendances->where('late_minutes', 0)->count() / $attendances->count() * 100, 1) : 0,
            'approval_rate' => $attendances->count() > 0 ? 
                round($attendances->whereNotNull('approved_at')->count() / $attendances->count() * 100, 1) : 0,
            'monthly_trend' => $attendances->groupBy(function($item) {
                return Carbon::parse($item->actual_start_time)->format('Y-m');
            })->map(function($group) {
                return [
                    'records' => $group->count(),
                    'hours' => round($group->sum('actual_duration') / 60, 2),
                    'overtime' => round($group->sum('overtime_hours'), 2)
                ];
            })
        ];
    }
    
    /**
     * 勤怠データアクセス権限チェック
     * 
     * @param Attendance $attendance
     * @return bool
     */
    private function canAccessAttendance(Attendance $attendance)
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'manager') {
            return $user->company_id === $attendance->shift->guard->company_id;
        }
        
        if ($user->role === 'guard') {
            return $user->guard_id === $attendance->shift->guard_id;
        }
        
        return false;
    }
    
    /**
     * 勤怠記録編集権限チェック
     * 
     * @param Attendance $attendance
     * @return bool
     */
    private function canEditAttendance(Attendance $attendance)
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'manager') {
            return $user->company_id === $attendance->shift->guard->company_id;
        }
        
        // 承認済みの場合は編集不可
        if ($attendance->approved_at) {
            return false;
        }
        
        if ($user->role === 'guard') {
            return $user->guard_id === $attendance->shift->guard_id;
        }
        
        return false;
    }
    
    /**
     * 勤怠承認権限チェック
     * 
     * @return bool
     */
    private function canApproveAttendance()
    {
        $user = Auth::user();
        return in_array($user->role, ['admin', 'manager']);
    }
}
