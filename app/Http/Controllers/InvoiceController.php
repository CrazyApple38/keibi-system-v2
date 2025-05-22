<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Project;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * 請求管理Controller
 * 
 * 警備サービスの請求書管理を担当
 * - 請求書作成・編集・承認
 * - 請求データ集計・分析
 * - 入金管理・売掛管理
 * - 請求書送付・履歴管理
 */
class InvoiceController extends Controller
{
    /**
     * 請求一覧表示
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Invoice::with(['customer', 'project', 'contract', 'creator']);
            
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
            
            if ($request->filled('contract_id')) {
                $query->where('contract_id', $request->contract_id);
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }
            
            if ($request->filled('invoice_number')) {
                $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
            }
            
            if ($request->filled('date_from')) {
                $query->where('invoice_date', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->where('invoice_date', '<=', $request->date_to);
            }
            
            if ($request->filled('amount_from')) {
                $query->where('total_amount', '>=', $request->amount_from);
            }
            
            if ($request->filled('amount_to')) {
                $query->where('total_amount', '<=', $request->amount_to);
            }
            
            // 支払期限過ぎ
            if ($request->filled('overdue')) {
                $query->where('payment_due_date', '<', now())
                      ->where('payment_status', '!=', 'paid');
            }
            
            // ソート
            $sortField = $request->get('sort', 'invoice_date');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
            
            // ページネーション
            $invoices = $query->paginate(15);
            
            // 統計情報
            $statistics = $this->getInvoiceStatistics($request);
            
            // アラート情報
            $alerts = $this->getInvoiceAlerts();
            
            // API リクエストの場合
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'invoices' => $invoices,
                    'statistics' => $statistics,
                    'alerts' => $alerts
                ]);
            }
            
            return view('invoice.index', compact('invoices', 'statistics', 'alerts'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('請求一覧の取得に失敗しました。');
        }
    }
    
    /**
     * 請求詳細表示
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            $invoice = Invoice::with([
                'customer',
                'project',
                'contract',
                'creator',
                'approver'
            ])->findOrFail($id);
            
            // アクセス権限チェック
            if (!$this->canAccessInvoice($invoice)) {
                return $this->errorResponse('アクセス権限がありません。', 403);
            }
            
            // 請求明細分析
            $itemAnalysis = $this->analyzeInvoiceItems($invoice->items);
            
            // 支払履歴
            $paymentHistory = $this->getPaymentHistory($invoice);
            
            // 関連請求書
            $relatedInvoices = Invoice::where('customer_id', $invoice->customer_id)
                                    ->where('id', '!=', $invoice->id)
                                    ->orderBy('invoice_date', 'desc')
                                    ->limit(5)
                                    ->get();
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'invoice' => $invoice,
                    'item_analysis' => $itemAnalysis,
                    'payment_history' => $paymentHistory,
                    'related_invoices' => $relatedInvoices
                ]);
            }
            
            return view('invoice.show', compact('invoice', 'itemAnalysis', 'paymentHistory', 'relatedInvoices'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('請求詳細の取得に失敗しました。');
        }
    }
    
    /**
     * 請求作成フォーム表示
     * 
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $customers = Customer::active()->get();
            $projects = Project::active()->get();
            $contracts = Contract::where('status', 'active')->get();
            
            // 請求テンプレート取得
            $templates = $this->getInvoiceTemplates();
            
            // 請求番号自動生成
            $nextInvoiceNumber = $this->generateInvoiceNumber();
            
            // デフォルト支払期日（30日後）
            $defaultDueDate = now()->addDays(30)->format('Y-m-d');
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'customers' => $customers,
                    'projects' => $projects,
                    'contracts' => $contracts,
                    'templates' => $templates,
                    'next_invoice_number' => $nextInvoiceNumber,
                    'default_due_date' => $defaultDueDate
                ]);
            }
            
            return view('invoice.create', compact('customers', 'projects', 'contracts', 'templates', 'nextInvoiceNumber', 'defaultDueDate'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('請求作成フォームの表示に失敗しました。');
        }
    }
    
    /**
     * 請求作成
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
                'contract_id' => 'nullable|exists:contracts,id',
                'invoice_number' => 'required|string|max:50|unique:invoices,invoice_number',
                'title' => 'required|string|max:200',
                'invoice_date' => 'required|date',
                'payment_due_date' => 'required|date|after_or_equal:invoice_date',
                'service_period_start' => 'required|date',
                'service_period_end' => 'required|date|after_or_equal:service_period_start',
                'items' => 'required|array|min:1',
                'items.*.name' => 'required|string|max:100',
                'items.*.description' => 'nullable|string|max:300',
                'items.*.quantity' => 'required|numeric|min:0',
                'items.*.unit' => 'required|string|max:20',
                'items.*.unit_price' => 'required|numeric|min:0',
                'payment_terms' => 'required|string|max:100',
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
            
            $invoice = Invoice::create([
                'customer_id' => $request->customer_id,
                'project_id' => $request->project_id,
                'contract_id' => $request->contract_id,
                'invoice_number' => $request->invoice_number,
                'title' => $request->title,
                'invoice_date' => $request->invoice_date,
                'payment_due_date' => $request->payment_due_date,
                'service_period_start' => $request->service_period_start,
                'service_period_end' => $request->service_period_end,
                'items' => json_encode($items),
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_terms' => $request->payment_terms,
                'notes' => $request->notes,
                'status' => 'draft',
                'payment_status' => 'unpaid',
                'company_id' => Auth::user()->company_id,
                'created_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'invoice' => $invoice->load(['customer', 'project', 'contract']),
                'message' => '請求書を作成しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('請求書の作成に失敗しました。');
        }
    }
    
    /**
     * 請求編集フォーム表示
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        try {
            $invoice = Invoice::with(['customer', 'project', 'contract'])->findOrFail($id);
            
            if (!$this->canEditInvoice($invoice)) {
                return $this->errorResponse('編集権限がありません。', 403);
            }
            
            $customers = Customer::active()->get();
            $projects = Project::active()->get();
            $contracts = Contract::where('status', 'active')->get();
            $templates = $this->getInvoiceTemplates();
            
            if ($request->expectsJson()) {
                return $this->successResponse([
                    'invoice' => $invoice,
                    'customers' => $customers,
                    'projects' => $projects,
                    'contracts' => $contracts,
                    'templates' => $templates
                ]);
            }
            
            return view('invoice.edit', compact('invoice', 'customers', 'projects', 'contracts', 'templates'));
            
        } catch (\Exception $e) {
            return $this->errorResponse('請求編集フォームの表示に失敗しました。');
        }
    }
    
    /**
     * 請求更新
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $invoice = Invoice::findOrFail($id);
            
            if (!$this->canEditInvoice($invoice)) {
                return $this->errorResponse('編集権限がありません。', 403);
            }
            
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'project_id' => 'nullable|exists:projects,id',
                'contract_id' => 'nullable|exists:contracts,id',
                'title' => 'required|string|max:200',
                'invoice_date' => 'required|date',
                'payment_due_date' => 'required|date|after_or_equal:invoice_date',
                'service_period_start' => 'required|date',
                'service_period_end' => 'required|date|after_or_equal:service_period_start',
                'items' => 'required|array|min:1',
                'items.*.name' => 'required|string|max:100',
                'items.*.description' => 'nullable|string|max:300',
                'items.*.quantity' => 'required|numeric|min:0',
                'items.*.unit' => 'required|string|max:20',
                'items.*.unit_price' => 'required|numeric|min:0',
                'payment_terms' => 'required|string|max:100',
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
            
            $invoice->update([
                'customer_id' => $request->customer_id,
                'project_id' => $request->project_id,
                'contract_id' => $request->contract_id,
                'title' => $request->title,
                'invoice_date' => $request->invoice_date,
                'payment_due_date' => $request->payment_due_date,
                'service_period_start' => $request->service_period_start,
                'service_period_end' => $request->service_period_end,
                'items' => json_encode($items),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_terms' => $request->payment_terms,
                'notes' => $request->notes,
                'status' => 'updated',
                'updated_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'invoice' => $invoice->load(['customer', 'project', 'contract']),
                'message' => '請求書を更新しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('請求書の更新に失敗しました。');
        }
    }
    
    /**
     * 請求承認
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $invoice = Invoice::findOrFail($id);
            
            if (!$this->canApproveInvoice()) {
                return $this->errorResponse('承認権限がありません。', 403);
            }
            
            $validator = Validator::make($request->all(), [
                'approval_memo' => 'nullable|string|max:300'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            $invoice->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
                'approval_memo' => $request->approval_memo
            ]);
            
            DB::commit();
            
            return $this->successResponse([
                'invoice' => $invoice->load(['customer', 'project', 'contract', 'approver']),
                'message' => '請求書を承認しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('請求書の承認に失敗しました。');
        }
    }
    
    /**
     * 請求送付
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function send(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $invoice = Invoice::findOrFail($id);
            
            if ($invoice->status !== 'approved') {
                return $this->errorResponse('承認済みの請求書のみ送付可能です。', 400);
            }
            
            $validator = Validator::make($request->all(), [
                'send_method' => 'required|in:email,mail,hand_delivery,fax',
                'send_memo' => 'nullable|string|max:300',
                'email_to' => 'required_if:send_method,email|email',
                'email_subject' => 'required_if:send_method,email|string|max:200',
                'email_body' => 'required_if:send_method,email|string|max:1000'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            $invoice->update([
                'status' => 'sent',
                'sent_at' => now(),
                'send_method' => $request->send_method,
                'send_memo' => $request->send_memo,
                'email_info' => $request->send_method === 'email' ? json_encode([
                    'to' => $request->email_to,
                    'subject' => $request->email_subject,
                    'body' => $request->email_body
                ]) : null,
                'sent_by' => Auth::id()
            ]);
            
            // メール送信処理（実装は別途）
            if ($request->send_method === 'email') {
                // $this->sendInvoiceEmail($invoice, $request);
            }
            
            DB::commit();
            
            return $this->successResponse([
                'invoice' => $invoice->load(['customer', 'project', 'contract']),
                'message' => '請求書を送付しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('請求書の送付に失敗しました。');
        }
    }
    
    /**
     * 入金記録
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function recordPayment(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $invoice = Invoice::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'payment_amount' => 'required|numeric|min:0|max:' . $invoice->total_amount,
                'payment_date' => 'required|date',
                'payment_method' => 'required|in:bank_transfer,cash,check,credit_card,other',
                'payment_memo' => 'nullable|string|max:300',
                'reference_number' => 'nullable|string|max:100'
            ]);
            
            if ($validator->fails()) {
                return $this->errorResponse('入力データに誤りがあります。', 422, $validator->errors());
            }
            
            $currentPaidAmount = $invoice->paid_amount ?? 0;
            $newPaidAmount = $currentPaidAmount + $request->payment_amount;
            
            // 支払状況の判定
            $paymentStatus = 'unpaid';
            if ($newPaidAmount >= $invoice->total_amount) {
                $paymentStatus = 'paid';
            } elseif ($newPaidAmount > 0) {
                $paymentStatus = 'partial';
            }
            
            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'payment_status' => $paymentStatus,
                'payment_date' => $paymentStatus === 'paid' ? $request->payment_date : $invoice->payment_date,
                'payment_method' => $request->payment_method,
                'payment_memo' => $request->payment_memo,
                'reference_number' => $request->reference_number,
                'updated_by' => Auth::id()
            ]);
            
            // 入金履歴を記録（別テーブルで管理する場合）
            // PaymentHistory::create([...]);
            
            DB::commit();
            
            return $this->successResponse([
                'invoice' => $invoice->load(['customer', 'project', 'contract']),
                'message' => '入金を記録しました。'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('入金記録に失敗しました。');
        }
    }
    
    /**
     * 請求統計情報取得
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function statistics(Request $request)
    {
        try {
            $statistics = $this->getInvoiceStatistics($request);
            
            return $this->successResponse($statistics);
            
        } catch (\Exception $e) {
            return $this->errorResponse('請求統計の取得に失敗しました。');
        }
    }
    
    /**
     * 売掛レポート生成
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function receivableReport(Request $request)
    {
        try {
            $query = Invoice::with(['customer', 'project'])
                          ->where('payment_status', '!=', 'paid');
            
            // ユーザー権限に基づくフィルタリング
            $user = Auth::user();
            if ($user->role === 'manager') {
                $query->where('company_id', $user->company_id);
            }
            
            $unpaidInvoices = $query->get();
            
            $report = [
                'summary' => [
                    'total_receivable' => $unpaidInvoices->sum('total_amount') - $unpaidInvoices->sum('paid_amount'),
                    'overdue_amount' => $unpaidInvoices->where('payment_due_date', '<', now())
                                                     ->sum('total_amount') - 
                                       $unpaidInvoices->where('payment_due_date', '<', now())
                                                     ->sum('paid_amount'),
                    'current_amount' => $unpaidInvoices->where('payment_due_date', '>=', now())
                                                      ->sum('total_amount') - 
                                       $unpaidInvoices->where('payment_due_date', '>=', now())
                                                      ->sum('paid_amount')
                ],
                'aging_analysis' => [
                    'current' => $unpaidInvoices->where('payment_due_date', '>=', now())->count(),
                    '1_30_days' => $unpaidInvoices->where('payment_due_date', '<', now())
                                                 ->where('payment_due_date', '>=', now()->subDays(30))
                                                 ->count(),
                    '31_60_days' => $unpaidInvoices->where('payment_due_date', '<', now()->subDays(30))
                                                  ->where('payment_due_date', '>=', now()->subDays(60))
                                                  ->count(),
                    'over_60_days' => $unpaidInvoices->where('payment_due_date', '<', now()->subDays(60))
                                                    ->count()
                ],
                'customer_breakdown' => $unpaidInvoices->groupBy('customer_id')
                                                      ->map(function($group) {
                                                          return [
                                                              'customer' => $group->first()->customer->name,
                                                              'count' => $group->count(),
                                                              'amount' => $group->sum('total_amount') - $group->sum('paid_amount')
                                                          ];
                                                      })
                                                      ->sortByDesc('amount')
                                                      ->values()
            ];
            
            return $this->successResponse($report);
            
        } catch (\Exception $e) {
            return $this->errorResponse('売掛レポートの生成に失敗しました。');
        }
    }
    
    /**
     * 請求統計情報を取得
     * 
     * @param Request $request
     * @return array
     */
    private function getInvoiceStatistics(Request $request)
    {
        $query = Invoice::query();
        
        // ユーザー権限に基づくフィルタリング
        $user = Auth::user();
        if ($user->role === 'manager') {
            $query->where('company_id', $user->company_id);
        }
        
        $invoices = $query->get();
        
        return [
            'total_invoices' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'paid_amount' => $invoices->sum('paid_amount'),
            'outstanding_amount' => $invoices->sum('total_amount') - $invoices->sum('paid_amount'),
            'average_amount' => round($invoices->avg('total_amount'), 2),
            'payment_rate' => $invoices->count() > 0 ? 
                round($invoices->where('payment_status', 'paid')->count() / $invoices->count() * 100, 1) : 0,
            'status_breakdown' => [
                'draft' => $invoices->where('status', 'draft')->count(),
                'approved' => $invoices->where('status', 'approved')->count(),
                'sent' => $invoices->where('status', 'sent')->count()
            ],
            'payment_status_breakdown' => [
                'unpaid' => $invoices->where('payment_status', 'unpaid')->count(),
                'partial' => $invoices->where('payment_status', 'partial')->count(),
                'paid' => $invoices->where('payment_status', 'paid')->count(),
                'overdue' => $invoices->where('payment_due_date', '<', now())
                                    ->where('payment_status', '!=', 'paid')
                                    ->count()
            ],
            'monthly_trend' => $invoices->groupBy(function($item) {
                return Carbon::parse($item->invoice_date)->format('Y-m');
            })->map(function($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('total_amount'),
                    'paid_amount' => $group->sum('paid_amount')
                ];
            })
        ];
    }
    
    /**
     * 請求アラート情報取得
     * 
     * @return array
     */
    private function getInvoiceAlerts()
    {
        $alerts = [];
        
        // 支払期限過ぎ
        $overdueInvoices = Invoice::where('payment_due_date', '<', now())
                                 ->where('payment_status', '!=', 'paid')
                                 ->with(['customer'])
                                 ->get();
        
        foreach ($overdueInvoices as $invoice) {
            $daysPastDue = now()->diffInDays(Carbon::parse($invoice->payment_due_date));
            $alerts[] = [
                'type' => 'overdue_payment',
                'level' => $daysPastDue > 30 ? 'danger' : 'warning',
                'message' => "請求書「{$invoice->invoice_number}」の支払いが{$daysPastDue}日遅れています",
                'invoice_id' => $invoice->id,
                'days_overdue' => $daysPastDue,
                'amount' => $invoice->total_amount - $invoice->paid_amount
            ];
        }
        
        // 支払期限間近
        $upcomingDueInvoices = Invoice::whereBetween('payment_due_date', [now(), now()->addDays(7)])
                                     ->where('payment_status', '!=', 'paid')
                                     ->with(['customer'])
                                     ->get();
        
        foreach ($upcomingDueInvoices as $invoice) {
            $daysUntilDue = now()->diffInDays(Carbon::parse($invoice->payment_due_date));
            $alerts[] = [
                'type' => 'upcoming_due',
                'level' => 'info',
                'message' => "請求書「{$invoice->invoice_number}」の支払期限が{$daysUntilDue}日後です",
                'invoice_id' => $invoice->id,
                'days_until_due' => $daysUntilDue
            ];
        }
        
        return $alerts;
    }
    
    /**
     * 請求明細分析
     * 
     * @param string $itemsJson
     * @return array
     */
    private function analyzeInvoiceItems($itemsJson)
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
     * 入金履歴取得
     * 
     * @param Invoice $invoice
     * @return array
     */
    private function getPaymentHistory(Invoice $invoice)
    {
        // 実際の実装では別テーブルから履歴を取得
        // 現在は基本情報のみ返す
        
        if ($invoice->payment_status === 'paid' || $invoice->payment_status === 'partial') {
            return [
                [
                    'payment_date' => $invoice->payment_date,
                    'amount' => $invoice->paid_amount,
                    'method' => $invoice->payment_method,
                    'memo' => $invoice->payment_memo,
                    'reference' => $invoice->reference_number
                ]
            ];
        }
        
        return [];
    }
    
    /**
     * 請求テンプレート取得
     * 
     * @return array
     */
    private function getInvoiceTemplates()
    {
        return [
            [
                'id' => 'monthly_security',
                'name' => '月次警備サービス',
                'payment_terms' => '月末締め翌月末払い',
                'items' => [
                    ['name' => '常駐警備', 'unit' => '人/月', 'unit_price' => 200000],
                    ['name' => '巡回警備', 'unit' => '回/月', 'unit_price' => 5000]
                ]
            ],
            [
                'id' => 'event_security',
                'name' => 'イベント警備',
                'payment_terms' => '請求書発行日から30日以内',
                'items' => [
                    ['name' => '交通誘導警備', 'unit' => '人/日', 'unit_price' => 12000],
                    ['name' => '雑踏警備', 'unit' => '人/日', 'unit_price' => 15000]
                ]
            ]
        ];
    }
    
    /**
     * 請求番号自動生成
     * 
     * @return string
     */
    private function generateInvoiceNumber()
    {
        $prefix = 'INV' . date('Y');
        $latestInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
                               ->orderBy('invoice_number', 'desc')
                               ->first();
        
        if ($latestInvoice) {
            $lastNumber = (int) substr($latestInvoice->invoice_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . sprintf('%04d', $nextNumber);
    }
    
    /**
     * 請求アクセス権限チェック
     * 
     * @param Invoice $invoice
     * @return bool
     */
    private function canAccessInvoice(Invoice $invoice)
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'manager') {
            return $user->company_id === $invoice->company_id;
        }
        
        if ($user->role === 'operator') {
            return $user->id === $invoice->created_by;
        }
        
        return false;
    }
    
    /**
     * 請求編集権限チェック
     * 
     * @param Invoice $invoice
     * @return bool
     */
    private function canEditInvoice(Invoice $invoice)
    {
        if (in_array($invoice->status, ['sent']) || $invoice->payment_status === 'paid') {
            return false;
        }
        
        return $this->canAccessInvoice($invoice);
    }
    
    /**
     * 請求承認権限チェック
     * 
     * @return bool
     */
    private function canApproveInvoice()
    {
        $user = Auth::user();
        return in_array($user->role, ['admin', 'manager']);
    }
}
