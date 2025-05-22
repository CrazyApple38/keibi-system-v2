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
}
