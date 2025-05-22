<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * 見積管理Controller
 * 
 * 警備契約の見積書管理を担当
 * - 見積書作成・編集・承認
 * - 見積書テンプレート管理
 * - 価格計算・自動見積生成
 * - 顧客への見積提出管理
 */
class QuotationController extends Controller
{
    /**
     * 見積一覧表示
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Quotation::with(['customer', 'project', 'creator']);
            
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
            
            if ($request->filled('quotation_number')) {
                $query->where('quotation_number', 'like', '%' . $request->quotation_number . '%');
            }
            
            if ($request->filled('date_from')) {
                $query->where('quotation_date', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->where('quotation_date', '<=', $request->date_to);
            }
            
            if ($request->filled('amount_from')) {
                $query->where('total_amount', '>=', $request->amount_from);
            }
            
            if ($request->filled('amount_to')) {
                $query->where('total_amount', '<=', $request->amount_to);
            }
            
            // ソート
            $sortField = $request->get('sort', 'quotation_date');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
            
            // ページネーション
            $quotations = $query->paginate(15);
            
            // 統計情報
            $statistics = $this->getQuotationStatistics($request);
            
            // API リクエストの場合
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'quotations' => $quotations,
                    'statistics' => $statistics
                ]);
            }
            
            return view('quotation.index', compact('quotations', 'statistics'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('見積一覧の取得に失敗しました。');
        }
    }
    
    /**
     * 見積詳細表示
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            $quotation = Quotation::with([
                'customer',
                'project',
                'creator',
                'approver'
            ])->findOrFail($id);
            
            // アクセス権限チェック
            if (!$this->canAccessQuotation($quotation)) {
                return $this->errorResponse('アクセス権限がありません。', 403);
            }
            
            // 見積明細の分析
            $itemAnalysis = $this->analyzeQuotationItems($quotation->items);
            
            // 関連データ
            $relatedQuotations = Quotation::where('customer_id', $quotation->customer_id)
                                        ->where('id', '!=', $quotation->id)
                                        ->orderBy('quotation_date', 'desc')
                                        ->limit(5)
                                        ->get();
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'quotation' => $quotation,
                    'item_analysis' => $itemAnalysis,
                    'related_quotations' => $relatedQuotations
                ]);
            }
            
            return view('quotation.show', compact('quotation', 'itemAnalysis', 'relatedQuotations'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('見積詳細の取得に失敗しました。');
        }
    }
    
    /**
     * 見積作成フォーム表示
     * 
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $customers = Customer::active()->get();
            $projects = Project::active()->get();
            
            // 見積テンプレート取得
            $templates = $this->getQuotationTemplates();
            
            // 見積番号自動生成
            $nextQuotationNumber = $this->generateQuotationNumber();
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'customers' => $customers,
                    'projects' => $projects,
                    'templates' => $templates,
                    'next_quotation_number' => $nextQuotationNumber
                ]);
            }
            
            return view('quotation.create', compact('customers', 'projects', 'templates', 'nextQuotationNumber'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('見積作成フォームの表示に失敗しました。');
        }
    }
    
    /**
     * 見積作成
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
                'quotation_number' => 'required|string|max:50|unique:quotations,quotation_number',
                'title' => 'required|string|max:200',
                'quotation_date' => 'required|date',
                'valid_until' => 'required|date|after:quotation_date',
                'items' => 'required|array|min:1',
                'items.*.name' => 'required|string|max:100',
                'items.*.description' => 'nullable|string|max:300',
                'items.*.quantity' => 'required|numeric|min:0',
                'items.*.unit' => 'required|string|max:20',
                'items.*.unit_price' => 'required|numeric|min:0',
                'terms_conditions' => 'nullable|string|max:1000',
                'notes' => 'nullable|string|max:500'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            // 金額計算
            $items = $request->items;
            $subtotal = 0;
            
            foreach ($items as &$item) {
                $item['amount'] = $item['quantity'] * $item['unit_price'];
                $subtotal += $item['amount'];
            }
            
            $taxRate = 0.10; // 消費税率10%
            $taxAmount = $subtotal * $taxRate;
            $totalAmount = $subtotal + $taxAmount;
            
            $quotation = Quotation::create([
                'customer_id' => $request->customer_id,
                'project_id' => $request->project_id,
                'quotation_number' => $request->quotation_number,
                'title' => $request->title,
                'quotation_date' => $request->quotation_date,
                'valid_until' => $request->valid_until,
                'items' => json_encode($items),
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'terms_conditions' => $request->terms_conditions,
                'notes' => $request->notes,
                'status' => 'draft',
                'company_id' => Auth::user()->company_id,
                'created_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'quotation' => $quotation->load(['customer', 'project']),
                'message' => '見積書を作成しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('見積書の作成に失敗しました。');
        }
    }
    
    /**
     * 見積編集フォーム表示
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        try {
            $quotation = Quotation::with(['customer', 'project'])->findOrFail($id);
            
            if (!$this->canEditQuotation($quotation)) {
                return $this->errorResponse('編集権限がありません。', 403);
            }
            
            $customers = Customer::active()->get();
            $projects = Project::active()->get();
            $templates = $this->getQuotationTemplates();
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'quotation' => $quotation,
                    'customers' => $customers,
                    'projects' => $projects,
                    'templates' => $templates
                ]);
            }
            
            return view('quotation.edit', compact('quotation', 'customers', 'projects', 'templates'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('見積編集フォームの表示に失敗しました。');
        }
    }
    
    /**
     * 見積更新
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $quotation = Quotation::findOrFail($id);
            
            if (!$this->canEditQuotation($quotation)) {
                return $this->errorResponse('編集権限がありません。', 403);
            }
            
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'project_id' => 'nullable|exists:projects,id',
                'title' => 'required|string|max:200',
                'quotation_date' => 'required|date',
                'valid_until' => 'required|date|after:quotation_date',
                'items' => 'required|array|min:1',
                'items.*.name' => 'required|string|max:100',
                'items.*.description' => 'nullable|string|max:300',
                'items.*.quantity' => 'required|numeric|min:0',
                'items.*.unit' => 'required|string|max:20',
                'items.*.unit_price' => 'required|numeric|min:0',
                'terms_conditions' => 'nullable|string|max:1000',
                'notes' => 'nullable|string|max:500'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            // 金額再計算
            $items = $request->items;
            $subtotal = 0;
            
            foreach ($items as &$item) {
                $item['amount'] = $item['quantity'] * $item['unit_price'];
                $subtotal += $item['amount'];
            }
            
            $taxRate = 0.10;
            $taxAmount = $subtotal * $taxRate;
            $totalAmount = $subtotal + $taxAmount;
            
            $quotation->update([
                'customer_id' => $request->customer_id,
                'project_id' => $request->project_id,
                'title' => $request->title,
                'quotation_date' => $request->quotation_date,
                'valid_until' => $request->valid_until,
                'items' => json_encode($items),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'terms_conditions' => $request->terms_conditions,
                'notes' => $request->notes,
                'status' => 'updated',
                'updated_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'quotation' => $quotation->load(['customer', 'project']),
                'message' => '見積書を更新しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('見積書の更新に失敗しました。');
        }
    }
    
    /**
     * 見積承認
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $quotation = Quotation::findOrFail($id);
            
            if (!$this->canApproveQuotation()) {
                return $this->errorResponse('承認権限がありません。', 403);
            }
            
            $validator = Validator::make($request->all(), [
                'approval_memo' => 'nullable|string|max:300'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            $quotation->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
                'approval_memo' => $request->approval_memo
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'quotation' => $quotation->load(['customer', 'project', 'approver']),
                'message' => '見積書を承認しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('見積書の承認に失敗しました。');
        }
    }
    
    /**
     * 見積提出
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function submit(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $quotation = Quotation::findOrFail($id);
            
            if ($quotation->status !== 'approved') {
                return $this->errorResponse('承認済みの見積書のみ提出可能です。', 400);
            }
            
            $validator = Validator::make($request->all(), [
                'submit_method' => 'required|in:email,mail,hand_delivery,fax',
                'submit_memo' => 'nullable|string|max:300',
                'email_to' => 'required_if:submit_method,email|email',
                'email_subject' => 'required_if:submit_method,email|string|max:200',
                'email_body' => 'required_if:submit_method,email|string|max:1000'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            $quotation->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'submit_method' => $request->submit_method,
                'submit_memo' => $request->submit_memo,
                'email_info' => $request->submit_method === 'email' ? json_encode([
                    'to' => $request->email_to,
                    'subject' => $request->email_subject,
                    'body' => $request->email_body
                ]) : null,
                'submitted_by' => Auth::id()
            ]);
            
            // メール送信処理（実装は別途）
            if ($request->submit_method === 'email') {
                // $this->sendQuotationEmail($quotation, $request);
            }
            
            DB::commit();
            
            return $this->successResponse([
                'quotation' => $quotation->load(['customer', 'project']),
                'message' => '見積書を提出しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('見積書の提出に失敗しました。');
        }
    }
    
    /**
     * 見積複製
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function duplicate(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $originalQuotation = Quotation::findOrFail($id);
            
            if (!$this->canAccessQuotation($originalQuotation)) {
                return $this->errorResponse('アクセス権限がありません。', 403);
            }
            
            $newQuotationNumber = $this->generateQuotationNumber();
            
            $duplicatedQuotation = $originalQuotation->replicate([
                'quotation_number',
                'quotation_date',
                'valid_until',
                'status',
                'approved_at',
                'approved_by',
                'submitted_at',
                'submitted_by'
            ]);
            
            $duplicatedQuotation->quotation_number = $newQuotationNumber;
            $duplicatedQuotation->quotation_date = now()->format('Y-m-d');
            $duplicatedQuotation->valid_until = now()->addDays(30)->format('Y-m-d');
            $duplicatedQuotation->status = 'draft';
            $duplicatedQuotation->created_by = Auth::id();
            $duplicatedQuotation->save();
            
            DB::commit();
            
            return $this->successResponse([
                'quotation' => $duplicatedQuotation->load(['customer', 'project']),
                'message' => '見積書を複製しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('見積書の複製に失敗しました。');
        }
    }
    
    /**
     * 見積統計情報取得
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function statistics(Request $request)
    {
        try {
            $statistics = $this->getQuotationStatistics($request);
            
            return $this->successResponse($statistics);
            
        } catch (\Exception $e) {
            return $this->errorResponse('見積統計の取得に失敗しました。');
        }
    }
    
    /**
     * 見積統計情報を取得
     * 
     * @param Request $request
     * @return array
     */
    private function getQuotationStatistics(Request $request)
    {
        $query = Quotation::query();
        
        // ユーザー権限に基づくフィルタリング
        $user = Auth::user();
        if ($user->role === 'manager') {
            $query->where('company_id', $user->company_id);
        }
        
        $quotations = $query->get();
        
        return [
            'total_quotations' => $quotations->count(),
            'total_amount' => $quotations->sum('total_amount'),
            'average_amount' => round($quotations->avg('total_amount'), 2),
            'status_breakdown' => [
                'draft' => $quotations->where('status', 'draft')->count(),
                'approved' => $quotations->where('status', 'approved')->count(),
                'submitted' => $quotations->where('status', 'submitted')->count(),
                'accepted' => $quotations->where('status', 'accepted')->count(),
                'rejected' => $quotations->where('status', 'rejected')->count()
            ],
            'monthly_trend' => $quotations->groupBy(function($item) {
                return Carbon::parse($item->quotation_date)->format('Y-m');
            })->map(function($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('total_amount'),
                    'average' => round($group->avg('total_amount'), 2)
                ];
            }),
            'customer_breakdown' => $quotations->groupBy('customer_id')
                                            ->map(function($group) {
                                                return [
                                                    'count' => $group->count(),
                                                    'amount' => $group->sum('total_amount')
                                                ];
                                            })
                                            ->sortByDesc('amount')
                                            ->take(10)
        ];
    }
    
    /**
     * 見積テンプレート取得
     * 
     * @return array
     */
    private function getQuotationTemplates()
    {
        return [
            [
                'id' => 'security_basic',
                'name' => '基本警備パッケージ',
                'items' => [
                    ['name' => '常駐警備', 'unit' => '人/月', 'unit_price' => 200000],
                    ['name' => '巡回警備', 'unit' => '回/月', 'unit_price' => 5000],
                    ['name' => '機械警備', 'unit' => '台/月', 'unit_price' => 15000]
                ]
            ],
            [
                'id' => 'event_security',
                'name' => 'イベント警備',
                'items' => [
                    ['name' => '交通誘導警備', 'unit' => '人/日', 'unit_price' => 12000],
                    ['name' => '雑踏警備', 'unit' => '人/日', 'unit_price' => 15000],
                    ['name' => '施設警備', 'unit' => '人/日', 'unit_price' => 13000]
                ]
            ]
        ];
    }
    
    /**
     * 見積番号自動生成
     * 
     * @return string
     */
    private function generateQuotationNumber()
    {
        $prefix = 'Q' . date('Y');
        $latestQuotation = Quotation::where('quotation_number', 'like', $prefix . '%')
                                  ->orderBy('quotation_number', 'desc')
                                  ->first();
        
        if ($latestQuotation) {
            $lastNumber = (int) substr($latestQuotation->quotation_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . sprintf('%04d', $nextNumber);
    }
    
    /**
     * 見積明細分析
     * 
     * @param string $itemsJson
     * @return array
     */
    private function analyzeQuotationItems($itemsJson)
    {
        $items = json_decode($itemsJson, true);
        
        $totalQuantity = array_sum(array_column($items, 'quantity'));
        $totalAmount = array_sum(array_column($items, 'amount'));
        
        return [
            'item_count' => count($items),
            'total_quantity' => $totalQuantity,
            'total_amount' => $totalAmount,
            'average_unit_price' => count($items) > 0 ? round($totalAmount / $totalQuantity, 2) : 0,
            'categories' => array_count_values(array_column($items, 'category'))
        ];
    }
    
    /**
     * 見積アクセス権限チェック
     * 
     * @param Quotation $quotation
     * @return bool
     */
    private function canAccessQuotation(Quotation $quotation)
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'manager') {
            return $user->company_id === $quotation->company_id;
        }
        
        if ($user->role === 'operator') {
            return $user->id === $quotation->created_by;
        }
        
        return false;
    }
    
    /**
     * 見積編集権限チェック
     * 
     * @param Quotation $quotation
     * @return bool
     */
    private function canEditQuotation(Quotation $quotation)
    {
        if (in_array($quotation->status, ['submitted', 'accepted', 'rejected'])) {
            return false;
        }
        
        return $this->canAccessQuotation($quotation);
    }
    
    /**
     * 見積承認権限チェック
     * 
     * @return bool
     */
    private function canApproveQuotation()
    {
        $user = Auth::user();
        return in_array($user->role, ['admin', 'manager']);
    }
}
