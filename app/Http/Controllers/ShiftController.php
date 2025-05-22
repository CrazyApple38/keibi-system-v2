<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Project;
use App\Models\Guard;
use App\Models\ShiftGuardAssignment;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * シフト管理Controller
 * 
 * シフトスケジューリング、警備員割り当て、自動最適化機能を提供
 * 運用管理システムの中核機能
 */
class ShiftController extends Controller
{
    /**
     * シフト一覧を表示
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Shift::with(['project.customer', 'assignments.guard.user'])
            ->withCount('assignments')
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->whereHas('project', function($subQ) use ($user) {
                    $subQ->where('customer_id', $user->company_id);
                });
            })
            ->when($user->role === 'guard', function($q) use ($user) {
                return $q->whereHas('assignments', function($subQ) use ($user) {
                    $subQ->where('guard_id', $user->guard_id);
                });
            });

        // 検索条件
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('project', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%")
                           ->orWhereHas('customer', function($customerQ) use ($search) {
                               $customerQ->where('name', 'like', "%{$search}%");
                           });
                  });
            });
        }

        // ステータスフィルター
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // プロジェクトフィルター
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // 日付範囲フィルター
        if ($request->filled('date_from')) {
            $query->where('shift_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('shift_date', '<=', $request->date_to);
        }

        // 時間フィルター
        if ($request->filled('time_from')) {
            $query->where('start_time', '>=', $request->time_from);
        }
        if ($request->filled('time_to')) {
            $query->where('end_time', '<=', $request->time_to);
        }

        // 警備員フィルター
        if ($request->filled('guard_id')) {
            $query->whereHas('assignments', function($q) use ($request) {
                $q->where('guard_id', $request->guard_id);
            });
        }

        // ソート
        $sortBy = $request->get('sort_by', 'shift_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        if ($request->expectsJson()) {
            $shifts = $query->paginate($request->get('per_page', 15));
            return $this->paginationResponse($shifts, 'シフト一覧を取得しました');
        }

        $shifts = $query->paginate(15);
        $projects = Project::where('status', 'active')->with('customer')->get();
        $guards = Guard::where('status', 'active')->with('user')->get();
        
        return view('shifts.index', compact('shifts', 'projects', 'guards'));
    }

    /**
     * シフト詳細を表示
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        $user = Auth::user();
        
        $shift = Shift::with([
            'project.customer',
            'assignments.guard.user',
            'attendances.guard.user',
            'dailyReports.guard.user'
        ])
        ->when($user->role !== 'admin', function($q) use ($user) {
            return $q->whereHas('project', function($subQ) use ($user) {
                $subQ->where('customer_id', $user->company_id);
            });
        })
        ->when($user->role === 'guard', function($q) use ($user) {
            return $q->whereHas('assignments', function($subQ) use ($user) {
                $subQ->where('guard_id', $user->guard_id);
            });
        })
        ->findOrFail($id);

        // シフト統計情報
        $statistics = $this->getShiftStatistics($shift);

        if ($request->expectsJson()) {
            return $this->successResponse([
                'shift' => $shift,
                'statistics' => $statistics
            ], 'シフト詳細を取得しました');
        }

        return view('shifts.show', compact('shift', 'statistics'));
    }

    /**
     * シフト作成フォームを表示
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $projects = Project::where('status', 'active')->with('customer')->get();
        $guards = Guard::where('status', 'active')->with('user')->get();
        
        return view('shifts.create', compact('projects', 'guards'));
    }

    /**
     * 新規シフトを作成
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'shift_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'required_guards' => 'required|integer|min:1|max:50',
            'description' => 'nullable|string',
            'special_instructions' => 'nullable|string',
            'weather_conditions' => 'nullable|string|max:100',
            'guard_ids' => 'nullable|array',
            'guard_ids.*' => 'exists:guards,id',
            'hourly_rate' => 'nullable|numeric|min:0',
            'break_duration' => 'nullable|integer|min:0|max:480',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        ], [
            'project_id.required' => 'プロジェクトは必須です',
            'project_id.exists' => '存在しないプロジェクトです',
            'shift_date.required' => 'シフト日は必須です',
            'shift_date.after_or_equal' => 'シフト日は今日以降の日付を入力してください',
            'start_time.required' => '開始時刻は必須です',
            'start_time.date_format' => '開始時刻は HH:MM 形式で入力してください',
            'end_time.required' => '終了時刻は必須です',
            'end_time.date_format' => '終了時刻は HH:MM 形式で入力してください',
            'end_time.after' => '終了時刻は開始時刻より後の時刻を入力してください',
            'location.required' => '勤務場所は必須です',
            'required_guards.required' => '必要警備員数は必須です',
            'required_guards.integer' => '必要警備員数は整数で入力してください',
            'required_guards.min' => '必要警備員数は1人以上で入力してください',
            'required_guards.max' => '必要警備員数は50人以下で入力してください',
            'hourly_rate.numeric' => '時給は数値で入力してください',
            'hourly_rate.min' => '時給は0以上で入力してください',
            'break_duration.integer' => '休憩時間は整数で入力してください',
            'break_duration.min' => '休憩時間は0分以上で入力してください',
            'break_duration.max' => '休憩時間は480分以下で入力してください',
            'status.required' => 'ステータスは必須です',
            'status.in' => '無効なステータスです',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('入力データが無効です', 422, $validator->errors());
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // 勤務時間を計算
            $startTime = Carbon::createFromFormat('H:i', $request->start_time);
            $endTime = Carbon::createFromFormat('H:i', $request->end_time);
            
            // 日をまたぐ場合の処理
            if ($endTime->lessThan($startTime)) {
                $endTime->addDay();
            }
            
            $durationHours = $endTime->diffInMinutes($startTime) / 60;
            if ($request->break_duration) {
                $durationHours -= ($request->break_duration / 60);
            }

            // シフト作成
            $shift = Shift::create([
                'project_id' => $request->project_id,
                'shift_date' => $request->shift_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'location' => $request->location,
                'required_guards' => $request->required_guards,
                'description' => $request->description,
                'special_instructions' => $request->special_instructions,
                'weather_conditions' => $request->weather_conditions,
                'hourly_rate' => $request->hourly_rate,
                'break_duration' => $request->break_duration ?? 0,
                'duration_hours' => $durationHours,
                'status' => $request->status,
                'created_by' => Auth::id(),
            ]);

            // 警備員を割り当て
            if ($request->filled('guard_ids')) {
                $this->assignGuardsToShift($shift, $request->guard_ids, $request->hourly_rate);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return $this->successResponse($shift->load(['project', 'assignments']), 'シフトを作成しました', 201);
            }

            return redirect()->route('shifts.show', $shift)
                           ->with('success', 'シフトを作成しました');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return $this->errorResponse('シフトの作成に失敗しました', 500);
            }
            
            return back()->withInput()->with('error', 'シフトの作成に失敗しました');
        }
    }

    /**
     * シフト編集フォームを表示
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        $shift = Shift::with(['project', 'assignments.guard'])
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->whereHas('project', function($subQ) use ($user) {
                    $subQ->where('customer_id', $user->company_id);
                });
            })
            ->findOrFail($id);

        $projects = Project::where('status', 'active')->with('customer')->get();
        $guards = Guard::where('status', 'active')->with('user')->get();
        
        return view('shifts.edit', compact('shift', 'projects', 'guards'));
    }

    /**
     * シフト情報を更新
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        $shift = Shift::when($user->role !== 'admin', function($q) use ($user) {
            return $q->whereHas('project', function($subQ) use ($user) {
                $subQ->where('customer_id', $user->company_id);
            });
        })->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'shift_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'required_guards' => 'required|integer|min:1|max:50',
            'description' => 'nullable|string',
            'special_instructions' => 'nullable|string',
            'weather_conditions' => 'nullable|string|max:100',
            'guard_ids' => 'nullable|array',
            'guard_ids.*' => 'exists:guards,id',
            'hourly_rate' => 'nullable|numeric|min:0',
            'break_duration' => 'nullable|integer|min:0|max:480',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        ], [
            'project_id.required' => 'プロジェクトは必須です',
            'project_id.exists' => '存在しないプロジェクトです',
            'shift_date.required' => 'シフト日は必須です',
            'start_time.required' => '開始時刻は必須です',
            'start_time.date_format' => '開始時刻は HH:MM 形式で入力してください',
            'end_time.required' => '終了時刻は必須です',
            'end_time.date_format' => '終了時刻は HH:MM 形式で入力してください',
            'end_time.after' => '終了時刻は開始時刻より後の時刻を入力してください',
            'location.required' => '勤務場所は必須です',
            'required_guards.required' => '必要警備員数は必須です',
            'required_guards.integer' => '必要警備員数は整数で入力してください',
            'required_guards.min' => '必要警備員数は1人以上で入力してください',
            'required_guards.max' => '必要警備員数は50人以下で入力してください',
            'hourly_rate.numeric' => '時給は数値で入力してください',
            'hourly_rate.min' => '時給は0以上で入力してください',
            'break_duration.integer' => '休憩時間は整数で入力してください',
            'break_duration.min' => '休憩時間は0分以上で入力してください',
            'break_duration.max' => '休憩時間は480分以下で入力してください',
            'status.required' => 'ステータスは必須です',
            'status.in' => '無効なステータスです',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('入力データが無効です', 422, $validator->errors());
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // 勤務時間を再計算
            $startTime = Carbon::createFromFormat('H:i', $request->start_time);
            $endTime = Carbon::createFromFormat('H:i', $request->end_time);
            
            if ($endTime->lessThan($startTime)) {
                $endTime->addDay();
            }
            
            $durationHours = $endTime->diffInMinutes($startTime) / 60;
            if ($request->break_duration) {
                $durationHours -= ($request->break_duration / 60);
            }

            // シフト更新
            $shift->update([
                'project_id' => $request->project_id,
                'shift_date' => $request->shift_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'location' => $request->location,
                'required_guards' => $request->required_guards,
                'description' => $request->description,
                'special_instructions' => $request->special_instructions,
                'weather_conditions' => $request->weather_conditions,
                'hourly_rate' => $request->hourly_rate,
                'break_duration' => $request->break_duration ?? 0,
                'duration_hours' => $durationHours,
                'status' => $request->status,
                'updated_by' => Auth::id(),
            ]);

            // 警備員割り当てを更新
            if ($request->has('guard_ids')) {
                $shift->assignments()->delete();
                if (!empty($request->guard_ids)) {
                    $this->assignGuardsToShift($shift, $request->guard_ids, $request->hourly_rate);
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return $this->successResponse($shift->load(['project', 'assignments']), 'シフト情報を更新しました');
            }

            return redirect()->route('shifts.show', $shift)
                           ->with('success', 'シフト情報を更新しました');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return $this->errorResponse('シフト情報の更新に失敗しました', 500);
            }
            
            return back()->withInput()->with('error', 'シフト情報の更新に失敗しました');
        }
    }

    /**
     * シフトを削除
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id, Request $request)
    {
        $user = Auth::user();
        
        // 管理者のみ削除可能
        if ($user->role !== 'admin') {
            if ($request->expectsJson()) {
                return $this->errorResponse('権限がありません', 403);
            }
            return back()->with('error', '権限がありません');
        }

        $shift = Shift::findOrFail($id);

        // 関連データの確認
        if ($shift->attendances()->exists()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('関連する勤怠記録が存在するため削除できません', 400);
            }
            return back()->with('error', '関連する勤怠記録が存在するため削除できません');
        }

        DB::beginTransaction();
        try {
            // 警備員割り当てを削除
            $shift->assignments()->delete();
            
            $shift->delete();
            DB::commit();

            if ($request->expectsJson()) {
                return $this->successResponse(null, 'シフトを削除しました');
            }

            return redirect()->route('shifts.index')
                           ->with('success', 'シフトを削除しました');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return $this->errorResponse('シフトの削除に失敗しました', 500);
            }
            
            return back()->with('error', 'シフトの削除に失敗しました');
        }
    }

    /**
     * シフトに警備員を割り当て
     * 
     * @param Shift $shift
     * @param array $guardIds
     * @param float|null $hourlyRate
     * @return void
     */
    private function assignGuardsToShift(Shift $shift, array $guardIds, ?float $hourlyRate = null): void
    {
        foreach ($guardIds as $guardId) {
            $guard = Guard::find($guardId);
            if ($guard) {
                ShiftGuardAssignment::create([
                    'shift_id' => $shift->id,
                    'guard_id' => $guardId,
                    'hourly_rate' => $hourlyRate ?? $guard->hourly_wage,
                    'status' => 'assigned',
                    'assigned_by' => Auth::id(),
                ]);
            }
        }
    }

    /**
     * シフト統計情報を取得
     * 
     * @param Shift $shift
     * @return array
     */
    private function getShiftStatistics(Shift $shift): array
    {
        $assignedGuards = $shift->assignments()->count();
        $attendedGuards = $shift->attendances()->count();
        $attendanceRate = $assignedGuards > 0 ? ($attendedGuards / $assignedGuards) * 100 : 0;

        $totalCost = $shift->assignments()->sum(DB::raw('hourly_rate * ' . $shift->duration_hours));
        
        return [
            'assigned_guards' => $assignedGuards,
            'required_guards' => $shift->required_guards,
            'attended_guards' => $attendedGuards,
            'attendance_rate' => round($attendanceRate, 1),
            'total_cost' => $totalCost,
            'average_cost_per_guard' => $assignedGuards > 0 ? $totalCost / $assignedGuards : 0,
            'is_fully_staffed' => $assignedGuards >= $shift->required_guards,
            'staffing_percentage' => $shift->required_guards > 0 ? 
                ($assignedGuards / $shift->required_guards) * 100 : 0,
        ];
    }

    /**
     * シフトのステータスを更新
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateStatus($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('入力データが無効です', 422, $validator->errors());
            }
            return back()->withErrors($validator);
        }

        $shift = Shift::findOrFail($id);
        
        $shift->update([
            'status' => $request->status,
            'status_updated_at' => now(),
            'status_update_reason' => $request->reason,
            'updated_by' => Auth::id(),
        ]);

        if ($request->expectsJson()) {
            return $this->successResponse($shift, 'シフトステータスを更新しました');
        }

        return back()->with('success', 'シフトステータスを更新しました');
    }

    /**
     * シフトの自動最適化
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function optimize(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'exists:projects,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('入力データが無効です', 422, $validator->errors());
        }

        try {
            $optimizedShifts = $this->performShiftOptimization(
                $request->date_from,
                $request->date_to,
                $request->project_ids
            );

            return $this->successResponse([
                'optimized_shifts' => $optimizedShifts,
                'total_shifts' => count($optimizedShifts),
                'optimization_score' => $this->calculateOptimizationScore($optimizedShifts),
            ], 'シフト最適化が完了しました');

        } catch (\Exception $e) {
            return $this->errorResponse('シフト最適化に失敗しました', 500);
        }
    }

    /**
     * シフト最適化を実行
     * 
     * @param string $dateFrom
     * @param string $dateTo
     * @param array|null $projectIds
     * @return array
     */
    private function performShiftOptimization(string $dateFrom, string $dateTo, ?array $projectIds = null): array
    {
        // 対象シフトを取得
        $shifts = Shift::whereBetween('shift_date', [$dateFrom, $dateTo])
            ->where('status', 'scheduled')
            ->when($projectIds, function($q) use ($projectIds) {
                return $q->whereIn('project_id', $projectIds);
            })
            ->with(['assignments.guard', 'project'])
            ->get();

        // 利用可能な警備員を取得
        $availableGuards = Guard::where('status', 'active')->get();

        $optimizedShifts = [];

        foreach ($shifts as $shift) {
            $optimization = $this->optimizeSingleShift($shift, $availableGuards);
            $optimizedShifts[] = $optimization;
        }

        return $optimizedShifts;
    }

    /**
     * 単一シフトを最適化
     * 
     * @param Shift $shift
     * @param \Illuminate\Database\Eloquent\Collection $availableGuards
     * @return array
     */
    private function optimizeSingleShift(Shift $shift, $availableGuards): array
    {
        // 現在の割り当て
        $currentAssignments = $shift->assignments()->with('guard')->get();
        
        // 最適な警備員を選択
        $optimalGuards = $this->selectOptimalGuards(
            $shift,
            $availableGuards,
            $shift->required_guards
        );

        return [
            'shift_id' => $shift->id,
            'shift_date' => $shift->shift_date,
            'location' => $shift->location,
            'current_assignments' => $currentAssignments->count(),
            'optimal_assignments' => count($optimalGuards),
            'recommendations' => $optimalGuards,
            'cost_impact' => $this->calculateCostImpact($shift, $optimalGuards),
        ];
    }

    /**
     * 最適な警備員を選択
     * 
     * @param Shift $shift
     * @param \Illuminate\Database\Eloquent\Collection $availableGuards
     * @param int $requiredCount
     * @return array
     */
    private function selectOptimalGuards(Shift $shift, $availableGuards, int $requiredCount): array
    {
        // スコアリング基準
        $scoredGuards = $availableGuards->map(function($guard) use ($shift) {
            $score = 0;
            
            // 経験年数スコア
            $score += min($guard->experience_years * 10, 50);
            
            // 関連プロジェクト経験スコア
            if ($guard->projects()->where('projects.id', $shift->project_id)->exists()) {
                $score += 30;
            }
            
            // スキルマッチスコア
            $requiredSkills = $shift->project->requirements['required_skills'] ?? [];
            $matchingSkills = array_intersect($guard->skills ?? [], $requiredSkills);
            $score += count($matchingSkills) * 5;
            
            // 時給効率スコア（低い方が高スコア）
            $score += max(0, 50 - ($guard->hourly_wage - 1000) / 50);
            
            return [
                'guard' => $guard,
                'score' => $score,
                'reasons' => $this->getSelectionReasons($guard, $shift)
            ];
        })->sortByDesc('score');

        return $scoredGuards->take($requiredCount)->values()->toArray();
    }

    /**
     * 選択理由を取得
     * 
     * @param Guard $guard
     * @param Shift $shift
     * @return array
     */
    private function getSelectionReasons(Guard $guard, Shift $shift): array
    {
        $reasons = [];
        
        if ($guard->experience_years >= 3) {
            $reasons[] = '豊富な経験（' . $guard->experience_years . '年）';
        }
        
        if ($guard->projects()->where('projects.id', $shift->project_id)->exists()) {
            $reasons[] = '当該プロジェクト経験有り';
        }
        
        if ($guard->hourly_wage <= 1200) {
            $reasons[] = 'コスト効率良好';
        }
        
        return $reasons;
    }

    /**
     * コスト影響を計算
     * 
     * @param Shift $shift
     * @param array $optimalGuards
     * @return array
     */
    private function calculateCostImpact(Shift $shift, array $optimalGuards): array
    {
        $currentCost = $shift->assignments()->sum('hourly_rate') * $shift->duration_hours;
        
        $optimalCost = collect($optimalGuards)->sum(function($item) use ($shift) {
            return $item['guard']->hourly_wage * $shift->duration_hours;
        });

        return [
            'current_cost' => $currentCost,
            'optimal_cost' => $optimalCost,
            'savings' => $currentCost - $optimalCost,
            'savings_percentage' => $currentCost > 0 ? 
                (($currentCost - $optimalCost) / $currentCost) * 100 : 0,
        ];
    }

    /**
     * 最適化スコアを計算
     * 
     * @param array $optimizedShifts
     * @return float
     */
    private function calculateOptimizationScore(array $optimizedShifts): float
    {
        if (empty($optimizedShifts)) {
            return 0;
        }

        $totalSavings = collect($optimizedShifts)->sum('cost_impact.savings');
        $totalCurrent = collect($optimizedShifts)->sum('cost_impact.current_cost');
        
        return $totalCurrent > 0 ? ($totalSavings / $totalCurrent) * 100 : 0;
    }

    /**
     * カレンダー形式でシフトを取得
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calendar(Request $request)
    {
        $user = Auth::user();
        
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $shifts = Shift::with(['project.customer', 'assignments.guard.user'])
            ->whereBetween('shift_date', [$startDate, $endDate])
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->whereHas('project', function($subQ) use ($user) {
                    $subQ->where('customer_id', $user->company_id);
                });
            })
            ->when($user->role === 'guard', function($q) use ($user) {
                return $q->whereHas('assignments', function($subQ) use ($user) {
                    $subQ->where('guard_id', $user->guard_id);
                });
            })
            ->get()
            ->groupBy('shift_date');

        $calendarData = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->toDateString();
            $dayShifts = $shifts->get($dateString, collect());
            
            $calendarData[] = [
                'date' => $dateString,
                'shifts' => $dayShifts->map(function($shift) {
                    return [
                        'id' => $shift->id,
                        'time' => $shift->start_time . ' - ' . $shift->end_time,
                        'location' => $shift->location,
                        'project' => $shift->project->name,
                        'customer' => $shift->project->customer->name,
                        'guards_count' => $shift->assignments->count(),
                        'required_guards' => $shift->required_guards,
                        'status' => $shift->status,
                    ];
                }),
                'total_shifts' => $dayShifts->count(),
                'total_guards' => $dayShifts->sum(function($shift) {
                    return $shift->assignments->count();
                }),
            ];
            
            $currentDate->addDay();
        }

        return $this->successResponse($calendarData, 'カレンダーデータを取得しました');
    }
}
