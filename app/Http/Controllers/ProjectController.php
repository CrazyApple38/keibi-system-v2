<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use App\Models\Guard;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 案件管理Controller
 * 
 * 警備案件のCRUD操作、進捗管理、警備員アサイン機能を提供
 * 受注管理システムの中核機能
 */
class ProjectController extends Controller
{
    /**
     * 案件一覧を表示
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Project::with(['customer', 'guards', 'shifts'])
            ->withCount(['guards', 'shifts'])
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('customer_id', $user->company_id);
            });

        // 検索条件
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // ステータスフィルター
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 顧客フィルター
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // 日付範囲フィルター
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        // ソート
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        if ($request->expectsJson()) {
            $projects = $query->paginate($request->get('per_page', 15));
            return $this->paginationResponse($projects, '案件一覧を取得しました');
        }

        $projects = $query->paginate(15);
        $customers = Customer::where('status', 'active')->get();
        
        return view('projects.index', compact('projects', 'customers'));
    }

    /**
     * 案件詳細を表示
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        $user = Auth::user();
        
        $project = Project::with([
            'customer',
            'guards.user',
            'shifts.assignments.guard.user',
            'shifts.dailyReports',
            'contracts.invoices'
        ])
        ->when($user->role !== 'admin', function($q) use ($user) {
            return $q->where('customer_id', $user->company_id);
        })
        ->findOrFail($id);

        // 案件統計情報
        $statistics = $this->getProjectStatistics($project);

        if ($request->expectsJson()) {
            return $this->successResponse([
                'project' => $project,
                'statistics' => $statistics
            ], '案件詳細を取得しました');
        }

        return view('projects.show', compact('project', 'statistics'));
    }

    /**
     * 案件作成フォームを表示
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $customers = Customer::where('status', 'active')->get();
        $guards = Guard::where('status', 'active')->with('user')->get();
        
        return view('projects.create', compact('customers', 'guards'));
    }

    /**
     * 新規案件を作成
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|in:planning,active,completed,cancelled,on_hold',
            'priority' => 'required|in:low,medium,high,urgent',
            'requirements' => 'nullable|array',
            'risk_assessment' => 'nullable|string',
            'special_instructions' => 'nullable|string',
            'contact_info' => 'nullable|array',
            'guard_ids' => 'nullable|array',
            'guard_ids.*' => 'exists:guards,id',
        ], [
            'customer_id.required' => '顧客は必須です',
            'customer_id.exists' => '存在しない顧客です',
            'name.required' => '案件名は必須です',
            'description.required' => '案件詳細は必須です',
            'location.required' => '現場住所は必須です',
            'start_date.required' => '開始日は必須です',
            'start_date.after_or_equal' => '開始日は今日以降の日付を入力してください',
            'end_date.after' => '終了日は開始日より後の日付を入力してください',
            'budget.numeric' => '予算は数値で入力してください',
            'budget.min' => '予算は0以上で入力してください',
            'status.required' => 'ステータスは必須です',
            'status.in' => '無効なステータスです',
            'priority.required' => '優先度は必須です',
            'priority.in' => '無効な優先度です',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('入力データが無効です', 422, $validator->errors());
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $project = Project::create([
                'customer_id' => $request->customer_id,
                'name' => $request->name,
                'description' => $request->description,
                'location' => $request->location,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'budget' => $request->budget,
                'status' => $request->status,
                'priority' => $request->priority,
                'requirements' => $request->requirements ?? [],
                'risk_assessment' => $request->risk_assessment,
                'special_instructions' => $request->special_instructions,
                'contact_info' => $request->contact_info ?? [],
                'created_by' => Auth::id(),
            ]);

            // 警備員をアサイン
            if ($request->filled('guard_ids')) {
                $project->guards()->attach($request->guard_ids);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return $this->successResponse($project->load(['customer', 'guards']), '案件を作成しました', 201);
            }

            return redirect()->route('projects.show', $project)
                           ->with('success', '案件を作成しました');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return $this->errorResponse('案件の作成に失敗しました', 500);
            }
            
            return back()->withInput()->with('error', '案件の作成に失敗しました');
        }
    }

    /**
     * 案件編集フォームを表示
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        $project = Project::with(['customer', 'guards'])
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('customer_id', $user->company_id);
            })
            ->findOrFail($id);

        $customers = Customer::where('status', 'active')->get();
        $guards = Guard::where('status', 'active')->with('user')->get();
        
        return view('projects.edit', compact('project', 'customers', 'guards'));
    }

    /**
     * 案件情報を更新
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        $project = Project::when($user->role !== 'admin', function($q) use ($user) {
            return $q->where('customer_id', $user->company_id);
        })->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|in:planning,active,completed,cancelled,on_hold',
            'priority' => 'required|in:low,medium,high,urgent',
            'requirements' => 'nullable|array',
            'risk_assessment' => 'nullable|string',
            'special_instructions' => 'nullable|string',
            'contact_info' => 'nullable|array',
            'guard_ids' => 'nullable|array',
            'guard_ids.*' => 'exists:guards,id',
        ], [
            'customer_id.required' => '顧客は必須です',
            'customer_id.exists' => '存在しない顧客です',
            'name.required' => '案件名は必須です',
            'description.required' => '案件詳細は必須です',
            'location.required' => '現場住所は必須です',
            'start_date.required' => '開始日は必須です',
            'end_date.after' => '終了日は開始日より後の日付を入力してください',
            'budget.numeric' => '予算は数値で入力してください',
            'budget.min' => '予算は0以上で入力してください',
            'status.required' => 'ステータスは必須です',
            'status.in' => '無効なステータスです',
            'priority.required' => '優先度は必須です',
            'priority.in' => '無効な優先度です',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('入力データが無効です', 422, $validator->errors());
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $project->update([
                'customer_id' => $request->customer_id,
                'name' => $request->name,
                'description' => $request->description,
                'location' => $request->location,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'budget' => $request->budget,
                'status' => $request->status,
                'priority' => $request->priority,
                'requirements' => $request->requirements ?? [],
                'risk_assessment' => $request->risk_assessment,
                'special_instructions' => $request->special_instructions,
                'contact_info' => $request->contact_info ?? [],
                'updated_by' => Auth::id(),
            ]);

            // 警備員アサインを更新
            if ($request->has('guard_ids')) {
                $project->guards()->sync($request->guard_ids ?? []);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return $this->successResponse($project->load(['customer', 'guards']), '案件情報を更新しました');
            }

            return redirect()->route('projects.show', $project)
                           ->with('success', '案件情報を更新しました');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return $this->errorResponse('案件情報の更新に失敗しました', 500);
            }
            
            return back()->withInput()->with('error', '案件情報の更新に失敗しました');
        }
    }

    /**
     * 案件を削除
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

        $project = Project::findOrFail($id);

        // 関連データの確認
        if ($project->shifts()->exists()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('関連するシフトが存在するため削除できません', 400);
            }
            return back()->with('error', '関連するシフトが存在するため削除できません');
        }

        DB::beginTransaction();
        try {
            // 警備員アサインを削除
            $project->guards()->detach();
            
            $project->delete();
            DB::commit();

            if ($request->expectsJson()) {
                return $this->successResponse(null, '案件を削除しました');
            }

            return redirect()->route('projects.index')
                           ->with('success', '案件を削除しました');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return $this->errorResponse('案件の削除に失敗しました', 500);
            }
            
            return back()->with('error', '案件の削除に失敗しました');
        }
    }

    /**
     * 案件統計情報を取得
     * 
     * @param Project $project
     * @return array
     */
    private function getProjectStatistics(Project $project): array
    {
        $totalShifts = $project->shifts()->count();
        $completedShifts = $project->shifts()->where('status', 'completed')->count();
        $completionRate = $totalShifts > 0 ? ($completedShifts / $totalShifts) * 100 : 0;

        return [
            'total_shifts' => $totalShifts,
            'completed_shifts' => $completedShifts,
            'pending_shifts' => $project->shifts()->where('status', 'scheduled')->count(),
            'completion_rate' => round($completionRate, 1),
            'total_guards' => $project->guards()->count(),
            'active_guards' => $project->guards()->where('status', 'active')->count(),
            'total_hours' => $project->shifts()->sum('duration_hours'),
            'days_until_completion' => $project->end_date ? 
                Carbon::parse($project->end_date)->diffInDays(Carbon::now(), false) : null,
            'budget_utilization' => $project->budget ? 
                $this->calculateBudgetUtilization($project) : 0,
        ];
    }

    /**
     * 予算利用率を計算
     * 
     * @param Project $project
     * @return float
     */
    private function calculateBudgetUtilization(Project $project): float
    {
        $totalCost = $project->contracts()
            ->join('invoices', 'contracts.id', '=', 'invoices.contract_id')
            ->sum('invoices.total_amount');

        return $project->budget > 0 ? ($totalCost / $project->budget) * 100 : 0;
    }

    /**
     * 案件のステータスを更新
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateStatus($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:planning,active,completed,cancelled,on_hold',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('入力データが無効です', 422, $validator->errors());
            }
            return back()->withErrors($validator);
        }

        $project = Project::findOrFail($id);
        
        $project->update([
            'status' => $request->status,
            'status_updated_at' => now(),
            'status_update_reason' => $request->reason,
            'updated_by' => Auth::id(),
        ]);

        if ($request->expectsJson()) {
            return $this->successResponse($project, '案件ステータスを更新しました');
        }

        return back()->with('success', '案件ステータスを更新しました');
    }

    /**
     * 警備員を案件にアサイン
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function assignGuards($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guard_ids' => 'required|array',
            'guard_ids.*' => 'exists:guards,id',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('入力データが無効です', 422, $validator->errors());
            }
            return back()->withErrors($validator);
        }

        $project = Project::findOrFail($id);
        
        $project->guards()->sync($request->guard_ids);

        if ($request->expectsJson()) {
            return $this->successResponse($project->load('guards'), '警備員をアサインしました');
        }

        return back()->with('success', '警備員をアサインしました');
    }

    /*
    |--------------------------------------------------------------------------
    | Google Maps API連携機能
    |--------------------------------------------------------------------------
    */

    /**
     * プロジェクト（現場）位置管理地図を表示
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function mapView(Request $request)
    {
        $user = Auth::user();
        
        $projects = Project::with(['customer', 'guards.user'])
            ->where('status', 'active')
            ->whereNotNull('location_lat')
            ->whereNotNull('location_lng')
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('customer_id', $user->company_id);
            })
            ->get();

        // 現場マップ情報を準備
        $projectsMapData = [];
        foreach ($projects as $project) {
            $projectsMapData[] = [
                'id' => $project->id,
                'name' => $project->name,
                'customer' => $project->customer->name ?? '',
                'latitude' => $project->location_lat,
                'longitude' => $project->location_lng,
                'address' => $project->full_address,
                'status' => $project->status,
                'priority' => $project->priority,
                'start_date' => $project->start_date?->format('Y-m-d'),
                'end_date' => $project->end_date?->format('Y-m-d'),
                'assigned_guards_count' => $project->guards->count(),
                'required_guards' => $project->required_guards ?? 0,
                'marker_color' => $project->marker_color,
            ];
        }

        if ($request->expectsJson()) {
            return $this->successResponse([
                'projects' => $projectsMapData,
                'center' => [
                    'lat' => config('services.google_maps.default_lat'),
                    'lng' => config('services.google_maps.default_lng'),
                ],
                'zoom' => config('services.google_maps.default_zoom'),
            ], 'プロジェクト位置情報を取得しました');
        }

        return view('projects.map', compact('projectsMapData'));
    }

    /**
     * プロジェクトの位置情報を更新
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLocation(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string|max:500',
            'building' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:20',
            'room' => 'nullable|string|max:50',
            'radius' => 'nullable|numeric|min:10|max:1000',
            'parking_info' => 'nullable|array',
            'access_info' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('位置情報が無効です', 422, $validator->errors());
        }

        $user = Auth::user();
        
        $project = Project::when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('customer_id', $user->company_id);
            })
            ->findOrFail($id);

        $project->update([
            'location_lat' => $request->latitude,
            'location_lng' => $request->longitude,
            'location_address' => $request->address,
            'location_building' => $request->building,
            'location_floor' => $request->floor,
            'location_room' => $request->room,
            'location_radius' => $request->radius ?? 100,
            'parking_info' => $request->parking_info ?? [],
            'access_info' => $request->access_info ?? [],
        ]);

        return $this->successResponse([
            'project_id' => $project->id,
            'location' => [
                'latitude' => $project->location_lat,
                'longitude' => $project->location_lng,
                'address' => $project->location_address,
                'building' => $project->location_building,
                'floor' => $project->location_floor,
                'room' => $project->location_room,
                'radius' => $project->location_radius,
                'full_address' => $project->full_address,
            ]
        ], 'プロジェクト位置情報を更新しました');
    }

    /**
     * プロジェクト近隣の警備員を検索
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function findNearbyGuards(Request $request, $id)
    {
        $user = Auth::user();
        
        $project = Project::when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('customer_id', $user->company_id);
            })
            ->findOrFail($id);

        if (!$project->hasLocation()) {
            return $this->errorResponse('プロジェクトの位置情報が設定されていません', 400);
        }

        $radius = $request->get('radius', 10); // デフォルト10km圏内
        $availableOnly = $request->get('available_only', true);

        // 近隣警備員を検索
        $nearbyGuards = $project->getNearbyGuards($radius, $availableOnly);

        $guards = $nearbyGuards->map(function($guard) {
            return [
                'id' => $guard->id,
                'name' => $guard->user->name ?? '',
                'employee_id' => $guard->employee_id,
                'status' => $guard->status,
                'location' => [
                    'latitude' => $guard->location_lat,
                    'longitude' => $guard->location_lng,
                    'address' => $guard->location_address,
                ],
                'distance' => $guard->distance_to_project,
                'skills' => $guard->skills ?? [],
                'qualifications' => $guard->qualifications ?? [],
                'hourly_wage' => $guard->hourly_wage,
                'availability' => $guard->available_times ?? [],
            ];
        });

        return $this->successResponse([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'location' => [
                    'latitude' => $project->location_lat,
                    'longitude' => $project->location_lng,
                    'address' => $project->full_address,
                ],
            ],
            'nearby_guards' => $guards,
            'search_radius' => $radius,
            'total_found' => $guards->count(),
        ], '近隣警備員情報を取得しました');
    }

    /**
     * プロジェクトの最適警備員配置を計算
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateOptimalPlacement(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'guard_count' => 'nullable|integer|min:1|max:20',
            'shift_pattern' => 'nullable|in:continuous,rotation,split',
            'coverage_priority' => 'nullable|in:entrance,perimeter,patrol,mixed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('配置計算データが無効です', 422, $validator->errors());
        }

        $user = Auth::user();
        
        $project = Project::when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('customer_id', $user->company_id);
            })
            ->findOrFail($id);

        if (!$project->hasLocation()) {
            return $this->errorResponse('プロジェクトの位置情報が設定されていません', 400);
        }

        $guardCount = $request->get('guard_count', $project->required_guards ?? 2);
        $shiftPattern = $request->get('shift_pattern', 'continuous');
        $coveragePriority = $request->get('coverage_priority', 'mixed');

        // 最適配置を計算
        $optimalPlacements = $project->getOptimalGuardPlacement($guardCount);

        // シフトパターンに応じた配置調整
        $adjustedPlacements = $this->adjustPlacementForShiftPattern(
            $optimalPlacements, 
            $shiftPattern, 
            $coveragePriority
        );

        return $this->successResponse([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'location' => $project->map_info,
            ],
            'optimal_placements' => $adjustedPlacements,
            'configuration' => [
                'guard_count' => $guardCount,
                'shift_pattern' => $shiftPattern,
                'coverage_priority' => $coveragePriority,
            ],
            'recommendations' => $this->generatePlacementRecommendations($project, $adjustedPlacements),
        ], '最適警備員配置を計算しました');
    }

    /**
     * プロジェクト間のルート最適化
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function optimizeMultiProjectRoute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_ids' => 'required|array|min:2',
            'project_ids.*' => 'exists:projects,id',
            'start_location' => 'nullable|array',
            'start_location.lat' => 'required_with:start_location|numeric|between:-90,90',
            'start_location.lng' => 'required_with:start_location|numeric|between:-180,180',
            'optimization_type' => 'nullable|in:shortest_distance,shortest_time,balanced',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('ルート最適化データが無効です', 422, $validator->errors());
        }

        $user = Auth::user();
        
        $projects = Project::whereIn('id', $request->project_ids)
            ->whereNotNull('location_lat')
            ->whereNotNull('location_lng')
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('customer_id', $user->company_id);
            })
            ->get();

        if ($projects->count() < 2) {
            return $this->errorResponse('ルート最適化には2つ以上のプロジェクトが必要です', 400);
        }

        $startLocation = $request->start_location ?? [
            'lat' => config('services.google_maps.default_lat'),
            'lng' => config('services.google_maps.default_lng'),
        ];

        $optimizationType = $request->get('optimization_type', 'balanced');

        // ルート最適化を実行
        $optimizedRoute = $this->performMultiProjectRouteOptimization(
            $projects, 
            $startLocation, 
            $optimizationType
        );

        return $this->successResponse([
            'start_location' => $startLocation,
            'projects' => $projects->map(function($project) {
                return $project->map_info;
            }),
            'optimized_route' => $optimizedRoute,
            'optimization_type' => $optimizationType,
            'total_projects' => $projects->count(),
        ], 'マルチプロジェクトルート最適化を完了しました');
    }

    /**
     * 現場エリア内の警備員監視
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function monitorGuardsInArea(Request $request, $id)
    {
        $user = Auth::user();
        
        $project = Project::when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('customer_id', $user->company_id);
            })
            ->findOrFail($id);

        if (!$project->hasLocation()) {
            return $this->errorResponse('プロジェクトの位置情報が設定されていません', 400);
        }

        // 現場エリア内の警備員を検索
        $guardsInArea = Guard::where('status', 'active')
            ->whereNotNull('location_lat')
            ->whereNotNull('location_lng')
            ->where('location_sharing_enabled', true)
            ->get()
            ->filter(function($guard) use ($project) {
                return $project->isGuardInRange($guard);
            });

        $monitoring = $guardsInArea->map(function($guard) use ($project) {
            $distance = $project->getDistanceFromGuard($guard);
            $isInRange = $project->isGuardInRange($guard);
            
            return [
                'guard' => [
                    'id' => $guard->id,
                    'name' => $guard->user->name ?? '',
                    'employee_id' => $guard->employee_id,
                    'status' => $guard->status,
                ],
                'location' => [
                    'latitude' => $guard->location_lat,
                    'longitude' => $guard->location_lng,
                    'address' => $guard->location_address,
                    'updated_at' => $guard->location_updated_at?->format('Y-m-d H:i:s'),
                ],
                'distance_from_site' => $distance,
                'is_in_authorized_area' => $isInRange,
                'area_status' => $isInRange ? 'authorized' : 'outside_area',
                'current_shift' => $guard->getCurrentShift()?->only(['id', 'start_time', 'end_time', 'status']),
            ];
        });

        return $this->successResponse([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'location' => $project->map_info,
                'authorized_radius' => $project->location_radius,
            ],
            'guards_monitoring' => $monitoring,
            'summary' => [
                'total_guards_in_area' => $monitoring->count(),
                'authorized_guards' => $monitoring->where('is_in_authorized_area', true)->count(),
                'outside_area_guards' => $monitoring->where('is_in_authorized_area', false)->count(),
            ],
            'monitored_at' => now()->format('Y-m-d H:i:s'),
        ], '現場エリア内警備員監視データを取得しました');
    }

    /*
    |--------------------------------------------------------------------------
    | Google Maps Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * シフトパターンに応じた配置調整
     * 
     * @param array $placements
     * @param string $shiftPattern
     * @param string $coveragePriority
     * @return array
     */
    private function adjustPlacementForShiftPattern(array $placements, string $shiftPattern, string $coveragePriority): array
    {
        // シフトパターンと監視優先度に応じて配置を調整
        foreach ($placements as &$placement) {
            switch ($coveragePriority) {
                case 'entrance':
                    $placement['priority'] = 'entrance_monitoring';
                    $placement['patrol_area']['radius'] *= 0.7; // エリアを縮小
                    break;
                case 'perimeter':
                    $placement['priority'] = 'perimeter_patrol';
                    $placement['patrol_area']['radius'] *= 1.2; // エリアを拡大
                    break;
                case 'patrol':
                    $placement['priority'] = 'mobile_patrol';
                    $placement['patrol_route'] = $this->generatePatrolRoute($placement);
                    break;
                default:
                    $placement['priority'] = 'mixed_coverage';
                    break;
            }
            
            $placement['shift_pattern'] = $shiftPattern;
            $placement['coverage_type'] = $coveragePriority;
        }

        return $placements;
    }

    /**
     * 巡回ルートを生成
     * 
     * @param array $placement
     * @return array
     */
    private function generatePatrolRoute(array $placement): array
    {
        $centerLat = $placement['lat'];
        $centerLng = $placement['lng'];
        $radius = $placement['patrol_area']['radius'];
        
        $routes = [];
        for ($i = 0; $i < 4; $i++) {
            $angle = ($i / 4) * 2 * M_PI;
            $lat = $centerLat + ($radius / 111.32) * cos($angle);
            $lng = $centerLng + ($radius / (111.32 * cos(deg2rad($centerLat)))) * sin($angle);
            
            $routes[] = [
                'checkpoint' => $i + 1,
                'lat' => $lat,
                'lng' => $lng,
                'estimated_time' => ($i + 1) * 5, // 5分間隔
            ];
        }
        
        return $routes;
    }

    /**
     * 配置推奨事項を生成
     * 
     * @param Project $project
     * @param array $placements
     * @return array
     */
    private function generatePlacementRecommendations(Project $project, array $placements): array
    {
        $recommendations = [];
        
        // 現場タイプに応じた推奨事項
        switch ($project->project_type ?? 'general') {
            case 'event_security':
                $recommendations[] = '入場口付近に警備員を重点配置することを推奨します';
                $recommendations[] = '緊急時の避難経路確保を優先してください';
                break;
            case 'facility_security':
                $recommendations[] = '24時間監視体制の構築を検討してください';
                $recommendations[] = '定期巡回ルートの設定が効果的です';
                break;
            case 'construction_security':
                $recommendations[] = '重機・資材置き場の監視を強化してください';
                $recommendations[] = '工事時間外の警備体制を重視してください';
                break;
            default:
                $recommendations[] = '現場の特性に応じた警備計画を策定してください';
                break;
        }
        
        // 配置数に応じた推奨事項
        if (count($placements) === 1) {
            $recommendations[] = '単独勤務時は定期的な報告体制を構築してください';
        } elseif (count($placements) >= 3) {
            $recommendations[] = '複数名配置時は効果的な連携体制を構築してください';
        }
        
        return $recommendations;
    }

    /**
     * マルチプロジェクトルート最適化を実行
     * 
     * @param Collection $projects
     * @param array $startLocation
     * @param string $optimizationType
     * @return array
     */
    private function performMultiProjectRouteOptimization($projects, array $startLocation, string $optimizationType): array
    {
        // 実際の実装では Google Maps Directions API を使用
        // ここでは仮の最適化ロジックを実装
        
        $projectLocations = $projects->map(function($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'lat' => $project->location_lat,
                'lng' => $project->location_lng,
            ];
        })->toArray();
        
        // 簡易的な距離ベースソート（実際にはより高度な最適化アルゴリズムを使用）
        usort($projectLocations, function($a, $b) use ($startLocation) {
            $distA = $this->calculateHaversineDistance($startLocation['lat'], $startLocation['lng'], $a['lat'], $a['lng']);
            $distB = $this->calculateHaversineDistance($startLocation['lat'], $startLocation['lng'], $b['lat'], $b['lng']);
            return $distA <=> $distB;
        });
        
        return [
            'route_order' => $projectLocations,
            'total_distance' => $this->calculateTotalRouteDistance($startLocation, $projectLocations),
            'estimated_time' => $this->estimateRouteTime($startLocation, $projectLocations),
            'optimization_method' => $optimizationType,
        ];
    }

    /**
     * ハヴァサイン公式による距離計算
     * 
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float
     */
    private function calculateHaversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // 地球の半径（km）
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    /**
     * ルート総距離を計算
     * 
     * @param array $startLocation
     * @param array $locations
     * @return float
     */
    private function calculateTotalRouteDistance(array $startLocation, array $locations): float
    {
        $totalDistance = 0;
        $currentLocation = $startLocation;
        
        foreach ($locations as $location) {
            $distance = $this->calculateHaversineDistance(
                $currentLocation['lat'], $currentLocation['lng'],
                $location['lat'], $location['lng']
            );
            $totalDistance += $distance;
            $currentLocation = $location;
        }
        
        return round($totalDistance, 2);
    }

    /**
     * ルート所要時間を推定
     * 
     * @param array $startLocation
     * @param array $locations
     * @return int
     */
    private function estimateRouteTime(array $startLocation, array $locations): int
    {
        $totalDistance = $this->calculateTotalRouteDistance($startLocation, $locations);
        // 平均時速30kmで計算 + 各現場で15分の作業時間
        $travelTimeMinutes = ($totalDistance / 30) * 60;
        $workTimeMinutes = count($locations) * 15;
        
        return round($travelTimeMinutes + $workTimeMinutes);
    }
}
