<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Project;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * 契約管理Controller
 * 
 * 警備契約の管理を担当
 * - 契約書作成・編集・承認
 * - 契約更新・変更管理
 * - 契約状況監視・アラート
 * - 契約統計・分析
 */
class ContractController extends Controller
{
    /**
     * 契約一覧表示
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Contract::with(['customer', 'project', 'quotation', 'creator']);
            
            // ユーザー権限に基づくフィルタリング
            $user = Auth::user();
            if ($user->role === 'manager') {
                $query->where('company_id', $user->company_id);
            } elseif ($user->role === 'operator') {
                $query->where('created_by', $user->id);
            }
            
            // 検索・フィルタリング
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }
            
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('contract_number')) {
                $query->where('contract_number', 'like', '%' . $request->contract_number . '%');
            }
            
            if ($request->filled('date_from')) {
                $query->where('contract_date', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->where('contract_date', '<=', $request->date_to);
            }
            
            if ($request->filled('amount_from')) {
                $query->where('contract_amount', '>=', $request->amount_from);
            }
            
            if ($request->filled('amount_to')) {
                $query->where('contract_amount', '<=', $request->amount_to);
            }
            
            // 期限切れアラート
            if ($request->filled('expiring_soon')) {
                $query->where('end_date', '<=', now()->addDays(30))
                      ->where('status', 'active');
            }
            
            // ソート
            $sortField = $request->get('sort', 'contract_date');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
            
            // ページネーション
            $contracts = $query->paginate(15);
            
            // 統計情報
            $statistics = $this->getContractStatistics($request);
            
            // アラート情報
            $alerts = $this->getContractAlerts();
            
            // API リクエストの場合
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'contracts' => $contracts,
                    'statistics' => $statistics,
                    'alerts' => $alerts
                ]);
            }
            
            return view('contract.index', compact('contracts', 'statistics', 'alerts'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('契約一覧の取得に失敗しました。');
        }
    }
    
    /**
     * 契約詳細表示
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            $contract = Contract::with([
                'customer',
                'project',
                'quotation',
                'creator',
                'approver'
            ])->findOrFail($id);
            
            // アクセス権限チェック
            if (!$this->canAccessContract($contract)) {
                return $this->errorResponse('アクセス権限がありません。', 403);
            }
            
            // 契約関連データ
            $contractHistory = $this->getContractHistory($contract);
            $renewalInfo = $this->getRenewalInfo($contract);
            $performanceData = $this->getContractPerformance($contract);
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'contract' => $contract,
                    'history' => $contractHistory,
                    'renewal_info' => $renewalInfo,
                    'performance' => $performanceData
                ]);
            }
            
            return view('contract.show', compact('contract', 'contractHistory', 'renewalInfo', 'performanceData'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('契約詳細の取得に失敗しました。');
        }
    }
    
    /**
     * 契約作成フォーム表示
     * 
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $customers = Customer::active()->get();
            $projects = Project::active()->get();
            $quotations = Quotation::where('status', 'accepted')->get();
            
            // 契約テンプレート取得
            $templates = $this->getContractTemplates();
            
            // 契約番号自動生成
            $nextContractNumber = $this->generateContractNumber();
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'customers' => $customers,
                    'projects' => $projects,
                    'quotations' => $quotations,
                    'templates' => $templates,
                    'next_contract_number' => $nextContractNumber
                ]);
            }
            
            return view('contract.create', compact('customers', 'projects', 'quotations', 'templates', 'nextContractNumber'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('契約作成フォームの表示に失敗しました。');
        }
    }
    
    /**
     * 契約作成
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'project_id' => 'nullable|exists:projects,id',
                'quotation_id' => 'nullable|exists:quotations,id',
                'contract_number' => 'required|string|max:50|unique:contracts,contract_number',
                'title' => 'required|string|max:200',
                'contract_date' => 'required|date',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'contract_amount' => 'required|numeric|min:0',
                'payment_terms' => 'required|string|max:100',
                'contract_details' => 'required|string|max:2000',
                'special_conditions' => 'nullable|string|max:1000',
                'auto_renewal' => 'boolean',
                'renewal_notice_days' => 'nullable|integer|min:1',
                'contract_type' => 'required|in:fixed_term,indefinite,project_based'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            $contract = Contract::create([
                'customer_id' => $request->customer_id,
                'project_id' => $request->project_id,
                'quotation_id' => $request->quotation_id,
                'contract_number' => $request->contract_number,
                'title' => $request->title,
                'contract_date' => $request->contract_date,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'contract_amount' => $request->contract_amount,
                'payment_terms' => $request->payment_terms,
                'contract_details' => $request->contract_details,
                'special_conditions' => $request->special_conditions,
                'auto_renewal' => $request->auto_renewal ?? false,
                'renewal_notice_days' => $request->renewal_notice_days,
                'contract_type' => $request->contract_type,
                'status' => 'draft',
                'company_id' => Auth::user()->company_id,
                'created_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'contract' => $contract->load(['customer', 'project', 'quotation']),
                'message' => '契約書を作成しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('契約書の作成に失敗しました。');
        }
    }
    
    /**
     * 契約編集フォーム表示
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        try {
            $contract = Contract::with(['customer', 'project', 'quotation'])->findOrFail($id);
            
            if (!$this->canEditContract($contract)) {
                return $this->errorResponse('編集権限がありません。', 403);
            }
            
            $customers = Customer::active()->get();
            $projects = Project::active()->get();
            $quotations = Quotation::where('status', 'accepted')->get();
            $templates = $this->getContractTemplates();
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'contract' => $contract,
                    'customers' => $customers,
                    'projects' => $projects,
                    'quotations' => $quotations,
                    'templates' => $templates
                ]);
            }
            
            return view('contract.edit', compact('contract', 'customers', 'projects', 'quotations', 'templates'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('契約編集フォームの表示に失敗しました。');
        }
    }
    
    /**
     * 契約更新
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $contract = Contract::findOrFail($id);
            
            if (!$this->canEditContract($contract)) {
                return $this->errorResponse('編集権限がありません。', 403);
            }
            
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'project_id' => 'nullable|exists:projects,id',
                'title' => 'required|string|max:200',
                'contract_date' => 'required|date',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'contract_amount' => 'required|numeric|min:0',
                'payment_terms' => 'required|string|max:100',
                'contract_details' => 'required|string|max:2000',
                'special_conditions' => 'nullable|string|max:1000',
                'auto_renewal' => 'boolean',
                'renewal_notice_days' => 'nullable|integer|min:1',
                'contract_type' => 'required|in:fixed_term,indefinite,project_based'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            $contract->update([
                'customer_id' => $request->customer_id,
                'project_id' => $request->project_id,
                'title' => $request->title,
                'contract_date' => $request->contract_date,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'contract_amount' => $request->contract_amount,
                'payment_terms' => $request->payment_terms,
                'contract_details' => $request->contract_details,
                'special_conditions' => $request->special_conditions,
                'auto_renewal' => $request->auto_renewal ?? false,
                'renewal_notice_days' => $request->renewal_notice_days,
                'contract_type' => $request->contract_type,
                'status' => 'updated',
                'updated_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'contract' => $contract->load(['customer', 'project', 'quotation']),
                'message' => '契約書を更新しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('契約書の更新に失敗しました。');
        }
    }
    
    /**
     * 契約承認
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $contract = Contract::findOrFail($id);
            
            if (!$this->canApproveContract()) {
                return $this->errorResponse('承認権限がありません。', 403);
            }
            
            $validator = Validator::make($request->all(), [
                'approval_memo' => 'nullable|string|max:300'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            $contract->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
                'approval_memo' => $request->approval_memo
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'contract' => $contract->load(['customer', 'project', 'quotation', 'approver']),
                'message' => '契約書を承認しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('契約書の承認に失敗しました。');
        }
    }
    
    /**
     * 契約発効
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function activate(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $contract = Contract::findOrFail($id);
            
            if ($contract->status !== 'approved') {
                return $this->errorResponse('承認済みの契約のみ発効可能です。', 400);
            }
            
            $contract->update([
                'status' => 'active',
                'activated_at' => now(),
                'activated_by' => Auth::id()
            ]);
            
            // プロジェクト状態更新
            if ($contract->project) {
                $contract->project->update(['status' => 'active']);
            }
            
            DB::commit();
            
            return $this->successResponse([
                'contract' => $contract->load(['customer', 'project']),
                'message' => '契約を発効しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('契約の発効に失敗しました。');
        }
    }
    
    /**
     * 契約更新
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function renew(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $originalContract = Contract::findOrFail($id);
            
            if (!$this->canRenewContract($originalContract)) {
                return $this->errorResponse('更新権限がありません。', 403);
            }
            
            $validator = Validator::make($request->all(), [
                'new_end_date' => 'required|date|after:' . $originalContract->end_date,
                'new_amount' => 'nullable|numeric|min:0',
                'renewal_memo' => 'nullable|string|max:500'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            // 新契約作成
            $newContractNumber = $this->generateContractNumber();
            
            $renewedContract = $originalContract->replicate([
                'contract_number',
                'end_date',
                'contract_amount',
                'status',
                'approved_at',
                'approved_by'
            ]);
            
            $renewedContract->contract_number = $newContractNumber;
            $renewedContract->start_date = $originalContract->end_date;
            $renewedContract->end_date = $request->new_end_date;
            $renewedContract->contract_amount = $request->new_amount ?? $originalContract->contract_amount;
            $renewedContract->status = 'draft';
            $renewedContract->parent_contract_id = $originalContract->id;
            $renewedContract->renewal_memo = $request->renewal_memo;
            $renewedContract->created_by = Auth::id();
            $renewedContract->save();
            
            // 元契約を更新済みに変更
            $originalContract->update([
                'status' => 'renewed',
                'renewed_contract_id' => $renewedContract->id,
                'renewed_at' => now(),
                'renewed_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'renewed_contract' => $renewedContract->load(['customer', 'project']),
                'original_contract' => $originalContract,
                'message' => '契約を更新しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('契約の更新に失敗しました。');
        }
    }
    
    /**
     * 契約統計情報取得
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function statistics(Request $request)
    {
        try {
            $statistics = $this->getContractStatistics($request);
            
            return $this->successResponse($statistics);
            
        } catch (\Exception $e) {
            return $this->errorResponse('契約統計の取得に失敗しました。');
        }
    }
    
    /**
     * 契約統計情報を取得
     * 
     * @param Request $request
     * @return array
     */
    private function getContractStatistics(Request $request)
    {
        $query = Contract::query();
        
        // ユーザー権限に基づくフィルタリング
        $user = Auth::user();
        if ($user->role === 'manager') {
            $query->where('company_id', $user->company_id);
        }
        
        $contracts = $query->get();
        
        return [
            'total_contracts' => $contracts->count(),
            'active_contracts' => $contracts->where('status', 'active')->count(),
            'total_amount' => $contracts->where('status', 'active')->sum('contract_amount'),
            'average_amount' => round($contracts->where('status', 'active')->avg('contract_amount'), 2),
            'status_breakdown' => [
                'draft' => $contracts->where('status', 'draft')->count(),
                'approved' => $contracts->where('status', 'approved')->count(),
                'active' => $contracts->where('status', 'active')->count(),
                'completed' => $contracts->where('status', 'completed')->count(),
                'terminated' => $contracts->where('status', 'terminated')->count(),
                'renewed' => $contracts->where('status', 'renewed')->count()
            ],
            'contract_type_breakdown' => [
                'fixed_term' => $contracts->where('contract_type', 'fixed_term')->count(),
                'indefinite' => $contracts->where('contract_type', 'indefinite')->count(),
                'project_based' => $contracts->where('contract_type', 'project_based')->count()
            ],
            'expiring_soon' => $contracts->where('status', 'active')
                                       ->where('end_date', '<=', now()->addDays(30))
                                       ->count(),
            'monthly_revenue' => $contracts->where('status', 'active')
                                          ->groupBy(function($item) {
                                              return Carbon::parse($item->start_date)->format('Y-m');
                                          })
                                          ->map(function($group) {
                                              return $group->sum('contract_amount');
                                          })
        ];
    }
    
    /**
     * 契約アラート情報取得
     * 
     * @return array
     */
    private function getContractAlerts()
    {
        $alerts = [];
        
        // 期限切れ間近の契約
        $expiringContracts = Contract::where('status', 'active')
                                   ->where('end_date', '<=', now()->addDays(30))
                                   ->with(['customer'])
                                   ->get();
        
        foreach ($expiringContracts as $contract) {
            $daysUntilExpiry = now()->diffInDays(Carbon::parse($contract->end_date));
            $alerts[] = [
                'type' => 'expiring_contract',
                'level' => $daysUntilExpiry <= 7 ? 'danger' : 'warning',
                'message' => "契約「{$contract->title}」が{$daysUntilExpiry}日後に期限切れになります",
                'contract_id' => $contract->id,
                'days_until_expiry' => $daysUntilExpiry
            ];
        }
        
        // 自動更新対象の契約
        $autoRenewalContracts = Contract::where('status', 'active')
                                      ->where('auto_renewal', true)
                                      ->whereRaw('end_date <= DATE_ADD(NOW(), INTERVAL renewal_notice_days DAY)')
                                      ->with(['customer'])
                                      ->get();
        
        foreach ($autoRenewalContracts as $contract) {
            $alerts[] = [
                'type' => 'auto_renewal',
                'level' => 'info',
                'message' => "契約「{$contract->title}」の自動更新処理が必要です",
                'contract_id' => $contract->id
            ];
        }
        
        return $alerts;
    }
    
    /**
     * 契約履歴取得
     * 
     * @param Contract $contract
     * @return array
     */
    private function getContractHistory(Contract $contract)
    {
        $history = [];
        
        // 親契約がある場合（更新契約）
        if ($contract->parent_contract_id) {
            $parentContract = Contract::find($contract->parent_contract_id);
            if ($parentContract) {
                $history[] = [
                    'type' => 'original',
                    'contract' => $parentContract,
                    'relationship' => '元契約'
                ];
            }
        }
        
        // 子契約がある場合（更新された契約）
        if ($contract->renewed_contract_id) {
            $renewedContract = Contract::find($contract->renewed_contract_id);
            if ($renewedContract) {
                $history[] = [
                    'type' => 'renewed',
                    'contract' => $renewedContract,
                    'relationship' => '更新契約'
                ];
            }
        }
        
        return $history;
    }
    
    /**
     * 契約更新情報取得
     * 
     * @param Contract $contract
     * @return array
     */
    private function getRenewalInfo(Contract $contract)
    {
        if ($contract->status !== 'active') {
            return null;
        }
        
        $daysUntilExpiry = now()->diffInDays(Carbon::parse($contract->end_date));
        $canRenew = $daysUntilExpiry <= ($contract->renewal_notice_days ?? 30);
        
        return [
            'can_renew' => $canRenew,
            'days_until_expiry' => $daysUntilExpiry,
            'auto_renewal' => $contract->auto_renewal,
            'renewal_notice_days' => $contract->renewal_notice_days,
            'suggested_end_date' => Carbon::parse($contract->end_date)->addYear()->format('Y-m-d')
        ];
    }
    
    /**
     * 契約パフォーマンス取得
     * 
     * @param Contract $contract
     * @return array
     */
    private function getContractPerformance(Contract $contract)
    {
        // 関連するプロジェクトのパフォーマンスデータを取得
        // この部分は他のテーブルとの連携が必要なため、基本的な枠組みのみ実装
        
        return [
            'total_revenue' => $contract->contract_amount,
            'duration_months' => Carbon::parse($contract->start_date)->diffInMonths(Carbon::parse($contract->end_date)),
            'performance_score' => 85.5, // 実際の計算ロジックは後で実装
            'satisfaction_rating' => 4.2 // 顧客満足度（実際のデータは別途管理）
        ];
    }
    
    /**
     * 契約テンプレート取得
     * 
     * @return array
     */
    private function getContractTemplates()
    {
        return [
            [
                'id' => 'security_standard',
                'name' => '標準警備契約',
                'contract_type' => 'fixed_term',
                'payment_terms' => '月末締め翌月末払い',
                'auto_renewal' => true,
                'renewal_notice_days' => 30
            ],
            [
                'id' => 'event_security',
                'name' => 'イベント警備契約',
                'contract_type' => 'project_based',
                'payment_terms' => '前払い',
                'auto_renewal' => false,
                'renewal_notice_days' => null
            ]
        ];
    }
    
    /**
     * 契約番号自動生成
     * 
     * @return string
     */
    private function generateContractNumber()
    {
        $prefix = 'C' . date('Y');
        $latestContract = Contract::where('contract_number', 'like', $prefix . '%')
                                 ->orderBy('contract_number', 'desc')
                                 ->first();
        
        if ($latestContract) {
            $lastNumber = (int) substr($latestContract->contract_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . sprintf('%04d', $nextNumber);
    }
    
    /**
     * 契約アクセス権限チェック
     * 
     * @param Contract $contract
     * @return bool
     */
    private function canAccessContract(Contract $contract)
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'manager') {
            return $user->company_id === $contract->company_id;
        }
        
        if ($user->role === 'operator') {
            return $user->id === $contract->created_by;
        }
        
        return false;
    }
    
    /**
     * 契約編集権限チェック
     * 
     * @param Contract $contract
     * @return bool
     */
    private function canEditContract(Contract $contract)
    {
        if (in_array($contract->status, ['active', 'completed', 'terminated'])) {
            return false;
        }
        
        return $this->canAccessContract($contract);
    }
    
    /**
     * 契約承認権限チェック
     * 
     * @return bool
     */
    private function canApproveContract()
    {
        $user = Auth::user();
        return in_array($user->role, ['admin', 'manager']);
    }
    
    /**
     * 契約更新権限チェック
     * 
     * @param Contract $contract
     * @return bool
     */
    private function canRenewContract(Contract $contract)
    {
        if ($contract->status !== 'active') {
            return false;
        }
        
        $user = Auth::user();
        return in_array($user->role, ['admin', 'manager']) && $this->canAccessContract($contract);
    }
}
