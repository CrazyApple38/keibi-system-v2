<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * 顧客管理Controller
 * 
 * 顧客情報のCRUD操作、顧客別統計、関連データ管理を提供
 * 警備グループシステムの顧客管理機能の中核
 */
class CustomerController extends Controller
{
    /**
     * 顧客一覧を表示
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Customer::with(['projects', 'contracts'])
            ->withCount(['projects', 'contracts'])
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('id', $user->company_id);
            });

        // 検索条件
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // ステータスフィルター
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 業界フィルター
        if ($request->filled('industry')) {
            $query->where('industry', $request->industry);
        }

        // ソート
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        if ($request->expectsJson()) {
            $customers = $query->paginate($request->get('per_page', 15));
            return $this->paginationResponse($customers, '顧客一覧を取得しました');
        }

        $customers = $query->paginate(15);
        return view('customers.index', compact('customers'));
    }

    /**
     * 顧客詳細を表示
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        $user = Auth::user();
        
        $customer = Customer::with([
            'projects.guards',
            'contracts.invoices',
            'projects.shifts.assignments.guard'
        ])
        ->when($user->role !== 'admin', function($q) use ($user) {
            return $q->where('id', $user->company_id);
        })
        ->findOrFail($id);

        // 顧客統計情報
        $statistics = $this->getCustomerStatistics($customer);

        if ($request->expectsJson()) {
            return $this->successResponse([
                'customer' => $customer,
                'statistics' => $statistics
            ], '顧客詳細を取得しました');
        }

        return view('customers.show', compact('customer', 'statistics'));
    }

    /**
     * 顧客作成フォームを表示
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * 新規顧客を作成
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'industry' => 'required|string|max:100',
            'contact_person' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:customers',
            'address' => 'required|string|max:500',
            'postal_code' => 'nullable|string|max:10',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended',
            'employee_count' => 'nullable|integer|min:1',
            'annual_revenue' => 'nullable|numeric|min:0',
            'contract_preferences' => 'nullable|array',
            'emergency_contact' => 'nullable|array',
        ], [
            'name.required' => '会社名は必須です',
            'industry.required' => '業界は必須です',
            'contact_person.required' => '担当者名は必須です',
            'phone.required' => '電話番号は必須です',
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '有効なメールアドレスを入力してください',
            'email.unique' => 'このメールアドレスは既に使用されています',
            'address.required' => '住所は必須です',
            'website.url' => '有効なURLを入力してください',
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
            $customer = Customer::create([
                'name' => $request->name,
                'industry' => $request->industry,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'postal_code' => $request->postal_code,
                'website' => $request->website,
                'notes' => $request->notes,
                'status' => $request->status,
                'employee_count' => $request->employee_count,
                'annual_revenue' => $request->annual_revenue,
                'contract_preferences' => $request->contract_preferences ?? [],
                'emergency_contact' => $request->emergency_contact ?? [],
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return $this->successResponse($customer, '顧客を作成しました', 201);
            }

            return redirect()->route('customers.show', $customer)
                           ->with('success', '顧客を作成しました');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return $this->errorResponse('顧客の作成に失敗しました', 500);
            }
            
            return back()->withInput()->with('error', '顧客の作成に失敗しました');
        }
    }

    /**
     * 顧客編集フォームを表示
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        $customer = Customer::when($user->role !== 'admin', function($q) use ($user) {
            return $q->where('id', $user->company_id);
        })->findOrFail($id);

        return view('customers.edit', compact('customer'));
    }

    /**
     * 顧客情報を更新
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        $customer = Customer::when($user->role !== 'admin', function($q) use ($user) {
            return $q->where('id', $user->company_id);
        })->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'industry' => 'required|string|max:100',
            'contact_person' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:customers,email,' . $id,
            'address' => 'required|string|max:500',
            'postal_code' => 'nullable|string|max:10',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended',
            'employee_count' => 'nullable|integer|min:1',
            'annual_revenue' => 'nullable|numeric|min:0',
            'contract_preferences' => 'nullable|array',
            'emergency_contact' => 'nullable|array',
        ], [
            'name.required' => '会社名は必須です',
            'industry.required' => '業界は必須です',
            'contact_person.required' => '担当者名は必須です',
            'phone.required' => '電話番号は必須です',
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '有効なメールアドレスを入力してください',
            'email.unique' => 'このメールアドレスは既に使用されています',
            'address.required' => '住所は必須です',
            'website.url' => '有効なURLを入力してください',
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
            $customer->update([
                'name' => $request->name,
                'industry' => $request->industry,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'postal_code' => $request->postal_code,
                'website' => $request->website,
                'notes' => $request->notes,
                'status' => $request->status,
                'employee_count' => $request->employee_count,
                'annual_revenue' => $request->annual_revenue,
                'contract_preferences' => $request->contract_preferences ?? [],
                'emergency_contact' => $request->emergency_contact ?? [],
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return $this->successResponse($customer, '顧客情報を更新しました');
            }

            return redirect()->route('customers.show', $customer)
                           ->with('success', '顧客情報を更新しました');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return $this->errorResponse('顧客情報の更新に失敗しました', 500);
            }
            
            return back()->withInput()->with('error', '顧客情報の更新に失敗しました');
        }
    }

    /**
     * 顧客を削除
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

        $customer = Customer::findOrFail($id);

        // 関連データの確認
        if ($customer->projects()->exists()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('関連する案件が存在するため削除できません', 400);
            }
            return back()->with('error', '関連する案件が存在するため削除できません');
        }

        DB::beginTransaction();
        try {
            $customer->delete();
            DB::commit();

            if ($request->expectsJson()) {
                return $this->successResponse(null, '顧客を削除しました');
            }

            return redirect()->route('customers.index')
                           ->with('success', '顧客を削除しました');

        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return $this->errorResponse('顧客の削除に失敗しました', 500);
            }
            
            return back()->with('error', '顧客の削除に失敗しました');
        }
    }

    /**
     * 顧客統計情報を取得
     * 
     * @param Customer $customer
     * @return array
     */
    private function getCustomerStatistics(Customer $customer): array
    {
        return [
            'total_projects' => $customer->projects()->count(),
            'active_projects' => $customer->projects()->where('status', 'active')->count(),
            'total_contracts' => $customer->contracts()->count(),
            'active_contracts' => $customer->contracts()->where('status', 'active')->count(),
            'total_revenue' => $customer->contracts()
                ->join('invoices', 'contracts.id', '=', 'invoices.contract_id')
                ->where('invoices.status', 'paid')
                ->sum('invoices.total_amount'),
            'pending_invoices' => $customer->contracts()
                ->join('invoices', 'contracts.id', '=', 'invoices.contract_id')
                ->where('invoices.status', 'pending')
                ->sum('invoices.total_amount'),
            'average_project_duration' => $customer->projects()
                ->whereNotNull('end_date')
                ->avg(DB::raw('DATEDIFF(end_date, start_date)')),
        ];
    }

    /**
     * 顧客をアクティブに設定
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function activate($id, Request $request)
    {
        $customer = Customer::findOrFail($id);
        $customer->update(['status' => 'active']);

        if ($request->expectsJson()) {
            return $this->successResponse($customer, '顧客をアクティブにしました');
        }

        return back()->with('success', '顧客をアクティブにしました');
    }

    /**
     * 顧客を非アクティブに設定
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deactivate($id, Request $request)
    {
        $customer = Customer::findOrFail($id);
        $customer->update(['status' => 'inactive']);

        if ($request->expectsJson()) {
            return $this->successResponse($customer, '顧客を非アクティブにしました');
        }

        return back()->with('success', '顧客を非アクティブにしました');
    }
}
