<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Guard;
use App\Models\Project;
use App\Models\Shift;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * 日報管理Controller
 * 
 * 警備業務の日報管理を担当
 * - 警備員日報の作成・編集・承認
 * - 現場状況レポート管理
 * - 異常・事故報告書管理
 * - 日報統計・分析機能
 */
class DailyReportController extends Controller
{
    /**
     * 日報一覧表示
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = DailyReport::with(['guard', 'project', 'shift', 'creator', 'approver']);
            
            // ユーザー権限に基づくフィルタリング
            $user = Auth::user();
            if ($user->role === 'guard') {
                $query->where('guard_id', $user->guard_id);
            } elseif ($user->role === 'manager') {
                $query->whereHas('guard', function($q) use ($user) {
                    $q->where('company_id', $user->company_id);
                });
            }
            
            // 検索・フィルタリング
            if ($request->filled('guard_id')) {
                $query->where('guard_id', $request->guard_id);
            }
            
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            
            if ($request->filled('shift_id')) {
                $query->where('shift_id', $request->shift_id);
            }
            
            if ($request->filled('report_type')) {
                $query->where('report_type', $request->report_type);
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('date_from')) {
                $query->where('report_date', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->where('report_date', '<=', $request->date_to);
            }
            
            // 重要度フィルタ
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }
            
            // 異常・事故報告のみ
            if ($request->filled('incidents_only')) {
                $query->where('has_incident', true);
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
            $sortField = $request->get('sort', 'report_date');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
            
            // ページネーション
            $reports = $query->paginate(15);
            
            // 統計情報
            $statistics = $this->getDailyReportStatistics($request);
            
            // API リクエストの場合
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'reports' => $reports,
                    'statistics' => $statistics
                ]);
            }
            
            return view('daily_report.index', compact('reports', 'statistics'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('日報一覧の取得に失敗しました。');
        }
    }
    
    /**
     * 日報詳細表示
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            $report = DailyReport::with([
                'guard',
                'project',
                'shift',
                'attendance',
                'creator',
                'approver'
            ])->findOrFail($id);
            
            // アクセス権限チェック
            if (!$this->canAccessReport($report)) {
                return $this->errorResponse('アクセス権限がありません。', 403);
            }
            
            // 関連する日報
            $relatedReports = DailyReport::where('project_id', $report->project_id)
                                        ->where('id', '!=', $report->id)
                                        ->where('report_date', '>=', Carbon::parse($report->report_date)->subDays(7))
                                        ->where('report_date', '<=', Carbon::parse($report->report_date)->addDays(7))
                                        ->with(['guard'])
                                        ->orderBy('report_date', 'desc')
                                        ->limit(10)
                                        ->get();
            
            // 日報分析データ
            $analysis = $this->analyzeReport($report);
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'report' => $report,
                    'related_reports' => $relatedReports,
                    'analysis' => $analysis
                ]);
            }
            
            return view('daily_report.show', compact('report', 'relatedReports', 'analysis'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('日報詳細の取得に失敗しました。');
        }
    }
    
    /**
     * 日報作成フォーム表示
     * 
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $guards = Guard::active()->get();
            $projects = Project::active()->get();
            
            // 今日のシフト取得
            $todayShifts = Shift::with(['guard', 'project'])
                              ->where('date', now()->format('Y-m-d'))
                              ->get();
            
            // 日報テンプレート
            $templates = $this->getReportTemplates();
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'guards' => $guards,
                    'projects' => $projects,
                    'today_shifts' => $todayShifts,
                    'templates' => $templates
                ]);
            }
            
            return view('daily_report.create', compact('guards', 'projects', 'todayShifts', 'templates'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('日報作成フォームの表示に失敗しました。');
        }
    }
    
    /**
     * 日報作成
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validator = Validator::make($request->all(), [
                'guard_id' => 'required|exists:guards,id',
                'project_id' => 'required|exists:projects,id',
                'shift_id' => 'nullable|exists:shifts,id',
                'report_date' => 'required|date',
                'report_type' => 'required|in:daily,incident,maintenance,security_check,patrol',
                'priority' => 'required|in:low,normal,high,urgent',
                'weather_condition' => 'required|string|max:50',
                'summary' => 'required|string|max:300',
                'detailed_report' => 'required|string|max:2000',
                'patrol_route' => 'nullable|string|max:500',
                'visitor_count' => 'nullable|integer|min:0',
                'incident_details' => 'nullable|string|max:1000',
                'equipment_status' => 'nullable|string|max:500',
                'maintenance_notes' => 'nullable|string|max:500',
                'safety_observations' => 'nullable|string|max:500',
                'recommendations' => 'nullable|string|max:500',
                'next_shift_notes' => 'nullable|string|max:300',
                'has_incident' => 'boolean',
                'has_equipment_issue' => 'boolean',
                'has_safety_concern' => 'boolean'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            // 関連する勤怠記録を取得
            $attendance = null;
            if ($request->shift_id) {
                $attendance = Attendance::where('shift_id', $request->shift_id)->first();
            }
            
            $report = DailyReport::create([
                'guard_id' => $request->guard_id,
                'project_id' => $request->project_id,
                'shift_id' => $request->shift_id,
                'attendance_id' => $attendance ? $attendance->id : null,
                'report_date' => $request->report_date,
                'report_type' => $request->report_type,
                'priority' => $request->priority,
                'weather_condition' => $request->weather_condition,
                'summary' => $request->summary,
                'detailed_report' => $request->detailed_report,
                'patrol_route' => $request->patrol_route,
                'visitor_count' => $request->visitor_count,
                'incident_details' => $request->incident_details,
                'equipment_status' => $request->equipment_status,
                'maintenance_notes' => $request->maintenance_notes,
                'safety_observations' => $request->safety_observations,
                'recommendations' => $request->recommendations,
                'next_shift_notes' => $request->next_shift_notes,
                'has_incident' => $request->has_incident ?? false,
                'has_equipment_issue' => $request->has_equipment_issue ?? false,
                'has_safety_concern' => $request->has_safety_concern ?? false,
                'status' => 'draft',
                'created_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'report' => $report->load(['guard', 'project', 'shift']),
                'message' => '日報を作成しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('日報の作成に失敗しました。');
        }
    }
    
    /**
     * 日報編集フォーム表示
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        try {
            $report = DailyReport::with(['guard', 'project', 'shift'])->findOrFail($id);
            
            if (!$this->canEditReport($report)) {
                return $this->errorResponse('編集権限がありません。', 403);
            }
            
            $guards = Guard::active()->get();
            $projects = Project::active()->get();
            $shifts = Shift::with(['guard', 'project'])->get();
            $templates = $this->getReportTemplates();
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'report' => $report,
                    'guards' => $guards,
                    'projects' => $projects,
                    'shifts' => $shifts,
                    'templates' => $templates
                ]);
            }
            
            return view('daily_report.edit', compact('report', 'guards', 'projects', 'shifts', 'templates'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('日報編集フォームの表示に失敗しました。');
        }
    }
    
    /**
     * 日報更新
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $report = DailyReport::findOrFail($id);
            
            if (!$this->canEditReport($report)) {
                return $this->errorResponse('編集権限がありません。', 403);
            }
            
            $validator = Validator::make($request->all(), [
                'guard_id' => 'required|exists:guards,id',
                'project_id' => 'required|exists:projects,id',
                'shift_id' => 'nullable|exists:shifts,id',
                'report_date' => 'required|date',
                'report_type' => 'required|in:daily,incident,maintenance,security_check,patrol',
                'priority' => 'required|in:low,normal,high,urgent',
                'weather_condition' => 'required|string|max:50',
                'summary' => 'required|string|max:300',
                'detailed_report' => 'required|string|max:2000',
                'patrol_route' => 'nullable|string|max:500',
                'visitor_count' => 'nullable|integer|min:0',
                'incident_details' => 'nullable|string|max:1000',
                'equipment_status' => 'nullable|string|max:500',
                'maintenance_notes' => 'nullable|string|max:500',
                'safety_observations' => 'nullable|string|max:500',
                'recommendations' => 'nullable|string|max:500',
                'next_shift_notes' => 'nullable|string|max:300',
                'has_incident' => 'boolean',
                'has_equipment_issue' => 'boolean',
                'has_safety_concern' => 'boolean'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            // 関連する勤怠記録を取得
            $attendance = null;
            if ($request->shift_id) {
                $attendance = Attendance::where('shift_id', $request->shift_id)->first();
            }
            
            $report->update([
                'guard_id' => $request->guard_id,
                'project_id' => $request->project_id,
                'shift_id' => $request->shift_id,
                'attendance_id' => $attendance ? $attendance->id : null,
                'report_date' => $request->report_date,
                'report_type' => $request->report_type,
                'priority' => $request->priority,
                'weather_condition' => $request->weather_condition,
                'summary' => $request->summary,
                'detailed_report' => $request->detailed_report,
                'patrol_route' => $request->patrol_route,
                'visitor_count' => $request->visitor_count,
                'incident_details' => $request->incident_details,
                'equipment_status' => $request->equipment_status,
                'maintenance_notes' => $request->maintenance_notes,
                'safety_observations' => $request->safety_observations,
                'recommendations' => $request->recommendations,
                'next_shift_notes' => $request->next_shift_notes,
                'has_incident' => $request->has_incident ?? false,
                'has_equipment_issue' => $request->has_equipment_issue ?? false,
                'has_safety_concern' => $request->has_safety_concern ?? false,
                'status' => 'updated',
                'updated_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'report' => $report->load(['guard', 'project', 'shift']),
                'message' => '日報を更新しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('日報の更新に失敗しました。');
        }
    }
    
    /**
     * 日報承認
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $report = DailyReport::findOrFail($id);
            
            if (!$this->canApproveReport()) {
                return $this->errorResponse('承認権限がありません。', 403);
            }
            
            $validator = Validator::make($request->all(), [
                'approval_memo' => 'nullable|string|max:300'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            $report->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
                'approval_memo' => $request->approval_memo
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'report' => $report->load(['guard', 'project', 'shift', 'approver']),
                'message' => '日報を承認しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('日報の承認に失敗しました。');
        }
    }
    
    /**
     * 日報提出
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function submit(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $report = DailyReport::findOrFail($id);
            
            if (!$this->canSubmitReport($report)) {
                return $this->errorResponse('提出権限がありません。', 403);
            }
            
            $report->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'submitted_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'report' => $report->load(['guard', 'project', 'shift']),
                'message' => '日報を提出しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('日報の提出に失敗しました。');
        }
    }
    
    /**
     * 日報統計情報取得
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function statistics(Request $request)
    {
        try {
            $statistics = $this->getDailyReportStatistics($request);
            
            return $this->successResponse($statistics);
            
        } catch (\Exception $e) {
            return $this->errorResponse('日報統計の取得に失敗しました。');
        }
    }
    
    /**
     * 日報レポート生成
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function report(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'report_type' => 'required|in:summary,incident,maintenance,performance',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'guard_id' => 'nullable|exists:guards,id',
                'project_id' => 'nullable|exists:projects,id'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            $query = DailyReport::with(['guard', 'project'])
                              ->whereBetween('report_date', [$request->start_date, $request->end_date]);
            
            if ($request->filled('guard_id')) {
                $query->where('guard_id', $request->guard_id);
            }
            
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            
            $reports = $query->get();
            
            $reportData = [
                'period' => [
                    'start' => $request->start_date,
                    'end' => $request->end_date,
                    'type' => $request->report_type
                ],
                'summary' => [
                    'total_reports' => $reports->count(),
                    'incident_reports' => $reports->where('has_incident', true)->count(),
                    'equipment_issues' => $reports->where('has_equipment_issue', true)->count(),
                    'safety_concerns' => $reports->where('has_safety_concern', true)->count(),
                    'average_visitor_count' => round($reports->avg('visitor_count'), 1)
                ]
            ];
            
            // レポートタイプ別の詳細データ
            switch ($request->report_type) {
                case 'incident':
                    $reportData['incidents'] = $reports->where('has_incident', true)
                                                     ->map(function($report) {
                                                         return [
                                                             'date' => $report->report_date,
                                                             'guard' => $report->guard->name,
                                                             'project' => $report->project->name,
                                                             'priority' => $report->priority,
                                                             'summary' => $report->summary,
                                                             'details' => $report->incident_details
                                                         ];
                                                     })
                                                     ->values();
                    break;
                    
                case 'maintenance':
                    $reportData['maintenance'] = $reports->where('has_equipment_issue', true)
                                                        ->map(function($report) {
                                                            return [
                                                                'date' => $report->report_date,
                                                                'project' => $report->project->name,
                                                                'equipment_status' => $report->equipment_status,
                                                                'maintenance_notes' => $report->maintenance_notes
                                                            ];
                                                        })
                                                        ->values();
                    break;
                    
                case 'performance':
                    $reportData['performance'] = $reports->groupBy('guard_id')
                                                        ->map(function($group) {
                                                            return [
                                                                'guard' => $group->first()->guard->name,
                                                                'report_count' => $group->count(),
                                                                'incident_rate' => round($group->where('has_incident', true)->count() / $group->count() * 100, 1),
                                                                'punctuality_score' => $this->calculatePunctualityScore($group)
                                                            ];
                                                        })
                                                        ->values();
                    break;
            }
            
            return $this->successResponse([
                'report' => $reportData,
                'reports' => $reports
            ]);
            
        } catch (\Exception $e) {
            return $this->errorResponse('日報レポートの生成に失敗しました。');
        }
    }
    
    /**
     * 日報統計情報を取得
     * 
     * @param Request $request
     * @return array
     */
    private function getDailyReportStatistics(Request $request)
    {
        $query = DailyReport::query();
        
        // ユーザー権限に基づくフィルタリング
        $user = Auth::user();
        if ($user->role === 'guard') {
            $query->where('guard_id', $user->guard_id);
        } elseif ($user->role === 'manager') {
            $query->whereHas('guard', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }
        
        // フィルタリング条件適用
        if ($request->filled('date_from')) {
            $query->where('report_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('report_date', '<=', $request->date_to);
        }
        
        $reports = $query->get();
        
        return [
            'total_reports' => $reports->count(),
            'submitted_reports' => $reports->whereIn('status', ['submitted', 'approved'])->count(),
            'approved_reports' => $reports->where('status', 'approved')->count(),
            'incident_count' => $reports->where('has_incident', true)->count(),
            'equipment_issue_count' => $reports->where('has_equipment_issue', true)->count(),
            'safety_concern_count' => $reports->where('has_safety_concern', true)->count(),
            'report_type_breakdown' => [
                'daily' => $reports->where('report_type', 'daily')->count(),
                'incident' => $reports->where('report_type', 'incident')->count(),
                'maintenance' => $reports->where('report_type', 'maintenance')->count(),
                'security_check' => $reports->where('report_type', 'security_check')->count(),
                'patrol' => $reports->where('report_type', 'patrol')->count()
            ],
            'priority_breakdown' => [
                'low' => $reports->where('priority', 'low')->count(),
                'normal' => $reports->where('priority', 'normal')->count(),
                'high' => $reports->where('priority', 'high')->count(),
                'urgent' => $reports->where('priority', 'urgent')->count()
            ],
            'monthly_trend' => $reports->groupBy(function($item) {
                return Carbon::parse($item->report_date)->format('Y-m');
            })->map(function($group) {
                return [
                    'count' => $group->count(),
                    'incidents' => $group->where('has_incident', true)->count(),
                    'equipment_issues' => $group->where('has_equipment_issue', true)->count()
                ];
            }),
            'guard_performance' => $reports->groupBy('guard_id')
                                          ->map(function($group) {
                                              return [
                                                  'guard_name' => $group->first()->guard->name,
                                                  'report_count' => $group->count(),
                                                  'incident_rate' => $group->count() > 0 ? 
                                                      round($group->where('has_incident', true)->count() / $group->count() * 100, 1) : 0,
                                                  'average_rating' => $this->calculateReportQuality($group)
                                              ];
                                          })
                                          ->sortByDesc('report_count')
                                          ->take(10)
                                          ->values()
        ];
    }
    
    /**
     * 日報分析
     * 
     * @param DailyReport $report
     * @return array
     */
    private function analyzeReport(DailyReport $report)
    {
        return [
            'completeness_score' => $this->calculateCompletenessScore($report),
            'detail_level' => $this->assessDetailLevel($report),
            'risk_indicators' => $this->identifyRiskIndicators($report),
            'follow_up_needed' => $this->assessFollowUpNeed($report)
        ];
    }
    
    /**
     * 日報の完全性スコア計算
     * 
     * @param DailyReport $report
     * @return int
     */
    private function calculateCompletenessScore(DailyReport $report)
    {
        $score = 0;
        $maxScore = 100;
        
        // 必須項目チェック
        if ($report->summary) $score += 20;
        if ($report->detailed_report) $score += 25;
        if ($report->weather_condition) $score += 10;
        if ($report->patrol_route) $score += 15;
        if ($report->equipment_status) $score += 15;
        if ($report->safety_observations) $score += 15;
        
        return min($score, $maxScore);
    }
    
    /**
     * 詳細レベル評価
     * 
     * @param DailyReport $report
     * @return string
     */
    private function assessDetailLevel(DailyReport $report)
    {
        $detailScore = 0;
        
        if (strlen($report->detailed_report) > 500) $detailScore += 2;
        elseif (strlen($report->detailed_report) > 200) $detailScore += 1;
        
        if ($report->patrol_route) $detailScore += 1;
        if ($report->visitor_count !== null) $detailScore += 1;
        if ($report->recommendations) $detailScore += 1;
        
        if ($detailScore >= 4) return 'excellent';
        if ($detailScore >= 3) return 'good';
        if ($detailScore >= 2) return 'fair';
        return 'needs_improvement';
    }
    
    /**
     * リスク指標特定
     * 
     * @param DailyReport $report
     * @return array
     */
    private function identifyRiskIndicators(DailyReport $report)
    {
        $risks = [];
        
        if ($report->has_incident) {
            $risks[] = ['type' => 'incident', 'level' => 'high', 'description' => '事故・異常発生'];
        }
        
        if ($report->has_equipment_issue) {
            $risks[] = ['type' => 'equipment', 'level' => 'medium', 'description' => '設備不具合'];
        }
        
        if ($report->has_safety_concern) {
            $risks[] = ['type' => 'safety', 'level' => 'high', 'description' => '安全上の懸念'];
        }
        
        if ($report->priority === 'urgent') {
            $risks[] = ['type' => 'priority', 'level' => 'urgent', 'description' => '緊急事項'];
        }
        
        return $risks;
    }
    
    /**
     * フォローアップ必要性評価
     * 
     * @param DailyReport $report
     * @return bool
     */
    private function assessFollowUpNeed(DailyReport $report)
    {
        return $report->has_incident || 
               $report->has_equipment_issue || 
               $report->has_safety_concern || 
               $report->priority === 'urgent' ||
               !empty($report->recommendations);
    }
    
    /**
     * 日報品質評価
     * 
     * @param Collection $reports
     * @return float
     */
    private function calculateReportQuality($reports)
    {
        $totalScore = 0;
        $count = $reports->count();
        
        foreach ($reports as $report) {
            $totalScore += $this->calculateCompletenessScore($report);
        }
        
        return $count > 0 ? round($totalScore / $count, 1) : 0;
    }
    
    /**
     * 出勤率スコア計算
     * 
     * @param Collection $reports
     * @return float
     */
    private function calculatePunctualityScore($reports)
    {
        // 実際の実装では勤怠データと連携
        // 現在は仮の値を返す
        return 85.5;
    }
    
    /**
     * 日報テンプレート取得
     * 
     * @return array
     */
    private function getReportTemplates()
    {
        return [
            [
                'id' => 'daily_security',
                'name' => '日常警備日報',
                'type' => 'daily',
                'template' => [
                    'summary' => '○○施設での日常警備業務を実施しました。',
                    'detailed_report' => '【巡回状況】\n- 時間通りに巡回を実施\n- 異常なし\n\n【来訪者対応】\n- 適切に対応\n\n【設備点検】\n- 正常に稼働',
                    'patrol_route' => '正面入口 → 駐車場 → 裏口 → 各階フロア → 屋上',
                    'equipment_status' => '防犯カメラ、センサー類すべて正常稼働',
                    'safety_observations' => '特に安全上の問題なし'
                ]
            ],
            [
                'id' => 'incident_report',
                'name' => '事故・異常報告',
                'type' => 'incident',
                'template' => [
                    'summary' => '○時頃、○○にて異常を発見し、適切に対応しました。',
                    'detailed_report' => '【発生時刻】\n○時○分\n\n【発生場所】\n○○\n\n【状況詳細】\n○○\n\n【対応内容】\n○○',
                    'incident_details' => '詳細な事故・異常の内容と対応記録',
                    'recommendations' => '再発防止のための提案事項'
                ]
            ]
        ];
    }
    
    /**
     * 日報アクセス権限チェック
     * 
     * @param DailyReport $report
     * @return bool
     */
    private function canAccessReport(DailyReport $report)
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'manager') {
            return $user->company_id === $report->guard->company_id;
        }
        
        if ($user->role === 'guard') {
            return $user->guard_id === $report->guard_id;
        }
        
        return false;
    }
    
    /**
     * 日報編集権限チェック
     * 
     * @param DailyReport $report
     * @return bool
     */
    private function canEditReport(DailyReport $report)
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            return true;
        }
        
        // 承認済みの場合は編集不可
        if ($report->approved_at) {
            return false;
        }
        
        if ($user->role === 'manager') {
            return $user->company_id === $report->guard->company_id;
        }
        
        if ($user->role === 'guard') {
            return $user->guard_id === $report->guard_id;
        }
        
        return false;
    }
    
    /**
     * 日報提出権限チェック
     * 
     * @param DailyReport $report
     * @return bool
     */
    private function canSubmitReport(DailyReport $report)
    {
        $user = Auth::user();
        
        if (in_array($report->status, ['submitted', 'approved'])) {
            return false;
        }
        
        if ($user->role === 'guard') {
            return $user->guard_id === $report->guard_id;
        }
        
        return in_array($user->role, ['admin', 'manager']);
    }
    
    /**
     * 日報承認権限チェック
     * 
     * @return bool
     */
    private function canApproveReport()
    {
        $user = Auth::user();
        return in_array($user->role, ['admin', 'manager']);
    }
}
