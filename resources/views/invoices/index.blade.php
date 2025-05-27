@extends('layouts.app')

@section('title', '請求管理')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-0">請求管理</h1>
                    <p class="text-muted mb-0">Invoice Management</p>
                </div>
                <div class="d-flex gap-2">
                    @can('create', App\Models\Invoice::class)
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-plus"></i> 新規請求書作成
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('invoices.create') }}">
                                    <i class="fas fa-file-invoice"></i> 通常請求書
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('invoices.create', ['type' => 'contract']) }}">
                                    <i class="fas fa-file-contract"></i> 契約ベース請求書
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('invoices.create', ['type' => 'recurring']) }}">
                                    <i class="fas fa-redo"></i> 定期請求書
                                </a></li>
                            </ul>
                        </div>
                    @endcan
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> エクスポート
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportData('csv')">CSV形式</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('excel')">Excel形式</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('pdf')">PDF形式</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('accounting')">会計ソフト形式</a></li>
                        </ul>
                    </div>
                    <button class="btn btn-outline-info" onclick="showReminders()">
                        <i class="fas fa-bell"></i> 督促管理
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 統計カード -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">今月の請求額</div>
                            <div class="h4 mb-0" id="monthly-invoices">¥{{ number_format($statistics['monthly_amount'] ?? 0) }}</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-file-invoice-dollar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">入金済み</div>
                            <div class="h4 mb-0" id="paid-amount">¥{{ number_format($statistics['paid_amount'] ?? 0) }}</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">未収金</div>
                            <div class="h4 mb-0" id="outstanding-amount">¥{{ number_format($statistics['outstanding_amount'] ?? 0) }}</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">延滞請求</div>
                            <div class="h4 mb-0" id="overdue-count">{{ $statistics['overdue_count'] ?? 0 }}件</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 検索・フィルターエリア -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-search"></i> 検索・フィルター
            </h5>
        </div>
        <div class="card-body">
            <form id="search-form" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">キーワード検索</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                               placeholder="請求書番号、顧客名、契約名で検索">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">支払い状況</label>
                        <select class="form-select" name="payment_status">
                            <option value="">全て</option>
                            <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>未請求</option>
                            <option value="sent" {{ request('payment_status') === 'sent' ? 'selected' : '' }}>送付済み</option>
                            <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>一部入金</option>
                            <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>入金済み</option>
                            <option value="overdue" {{ request('payment_status') === 'overdue' ? 'selected' : '' }}>延滞</option>
                            <option value="cancelled" {{ request('payment_status') === 'cancelled' ? 'selected' : '' }}>キャンセル</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">顧客</label>
                        <select class="form-select" name="customer_id">
                            <option value="">全て</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">請求日</label>
                        <input type="date" class="form-control" name="invoice_date_from" value="{{ request('invoice_date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">支払期限</label>
                        <input type="date" class="form-control" name="due_date_to" value="{{ request('due_date_to') }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">金額範囲（以上）</label>
                                <div class="input-group">
                                    <span class="input-group-text">¥</span>
                                    <input type="number" class="form-control" name="amount_from" 
                                           value="{{ request('amount_from') }}" placeholder="0" step="1000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">金額範囲（以下）</label>
                                <div class="input-group">
                                    <span class="input-group-text">¥</span>
                                    <input type="number" class="form-control" name="amount_to" 
                                           value="{{ request('amount_to') }}" placeholder="999999999" step="1000">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2 align-items-end h-100">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                                <i class="fas fa-times"></i> フィルタークリア
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="saveFilter()">
                                <i class="fas fa-save"></i> フィルター保存
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="loadSavedFilter()">
                                <i class="fas fa-folder-open"></i> 保存済みフィルター
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="showAnalytics()">
                                <i class="fas fa-chart-bar"></i> 分析表示
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 一括操作エリア -->
    <div class="card mb-4" id="bulk-actions" style="display: none;">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold">選択された項目:</span>
                <span id="selected-count" class="badge bg-primary">0</span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-info" onclick="bulkAction('send')">
                        <i class="fas fa-paper-plane"></i> 一括送付
                    </button>
                    <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('payment')">
                        <i class="fas fa-money-check"></i> 入金記録
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('reminder')">
                        <i class="fas fa-bell"></i> 督促送付
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="bulkAction('export')">
                        <i class="fas fa-download"></i> 一括エクスポート
                    </button>
                    @can('delete', App\Models\Invoice::class)
                        <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                            <i class="fas fa-trash"></i> 一括削除
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- 請求書一覧テーブル -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list"></i> 請求書一覧
                    <span class="badge bg-secondary ms-2">{{ $invoices->total() }}件</span>
                </h5>
                <div class="d-flex gap-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="select-all">
                        <label class="form-check-label" for="select-all">全選択</label>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            表示設定
                        </button>
                        <ul class="dropdown-menu">
                            <li><label class="dropdown-item"><input type="checkbox" checked> 請求書番号</label></li>
                            <li><label class="dropdown-item"><input type="checkbox" checked> 顧客名</label></li>
                            <li><label class="dropdown-item"><input type="checkbox" checked> 請求日</label></li>
                            <li><label class="dropdown-item"><input type="checkbox" checked> 支払期限</label></li>
                            <li><label class="dropdown-item"><input type="checkbox" checked> 請求金額</label></li>
                            <li><label class="dropdown-item"><input type="checkbox" checked> 支払い状況</label></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="50">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="check-all">
                                    </div>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'invoice_number', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-white text-decoration-none">
                                        請求書番号
                                        @if(request('sort') === 'invoice_number')
                                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'customer_name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-white text-decoration-none">
                                        顧客名
                                        @if(request('sort') === 'customer_name')
                                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>契約・案件</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'invoice_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-white text-decoration-none">
                                        請求日
                                        @if(request('sort') === 'invoice_date')
                                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'due_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-white text-decoration-none">
                                        支払期限
                                        @if(request('sort') === 'due_date')
                                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'total_amount', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-white text-decoration-none">
                                        請求金額
                                        @if(request('sort') === 'total_amount')
                                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>支払い状況</th>
                                <th>更新日</th>
                                <th width="150">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $invoice->id }}">
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $invoice->invoice_number }}</span>
                                        @if($invoice->is_recurring)
                                            <span class="badge bg-info ms-1" title="定期請求">定期</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="fw-bold">{{ $invoice->contract->customer->name ?? $invoice->customer->name }}</div>
                                                <small class="text-muted">{{ $invoice->contract->customer->company_type ?? $invoice->customer->company_type }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($invoice->contract)
                                            <div>
                                                <div class="fw-bold">{{ Str::limit($invoice->contract->title, 25) }}</div>
                                                <small class="text-muted">{{ $invoice->contract->project->name ?? '' }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">単発請求</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $invoice->invoice_date->format('Y/m/d') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $invoice->due_date->format('Y/m/d') }}</div>
                                        @if($invoice->due_date->isPast() && $invoice->payment_status !== 'paid')
                                            <span class="badge bg-danger">延滞</span>
                                        @elseif($invoice->due_date->diffInDays() <= 7 && $invoice->payment_status !== 'paid')
                                            <span class="badge bg-warning">期限間近</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold">¥{{ number_format($invoice->total_amount) }}</div>
                                        @if($invoice->paid_amount > 0 && $invoice->payment_status !== 'paid')
                                            <small class="text-success">入金: ¥{{ number_format($invoice->paid_amount) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusConfig = [
                                                'pending' => ['class' => 'bg-secondary', 'icon' => 'fas fa-edit', 'text' => '未請求'],
                                                'sent' => ['class' => 'bg-info', 'icon' => 'fas fa-paper-plane', 'text' => '送付済み'],
                                                'partial' => ['class' => 'bg-warning', 'icon' => 'fas fa-coins', 'text' => '一部入金'],
                                                'paid' => ['class' => 'bg-success', 'icon' => 'fas fa-check', 'text' => '入金済み'],
                                                'overdue' => ['class' => 'bg-danger', 'icon' => 'fas fa-clock', 'text' => '延滞'],
                                                'cancelled' => ['class' => 'bg-dark', 'icon' => 'fas fa-ban', 'text' => 'キャンセル']
                                            ];
                                            $config = $statusConfig[$invoice->payment_status] ?? $statusConfig['pending'];
                                        @endphp
                                        <span class="badge {{ $config['class'] }}">
                                            <i class="{{ $config['icon'] }}"></i> {{ $config['text'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-muted">
                                            {{ $invoice->updated_at->format('Y/m/d H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('invoices.show', $invoice) }}" 
                                               class="btn btn-sm btn-outline-primary" title="詳細表示">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('update', $invoice)
                                                <a href="{{ route('invoices.edit', $invoice) }}" 
                                                   class="btn btn-sm btn-outline-warning" title="編集">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                        type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="downloadInvoice({{ $invoice->id }})">
                                                        <i class="fas fa-download"></i> PDF ダウンロード
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="printInvoice({{ $invoice->id }})">
                                                        <i class="fas fa-print"></i> 印刷
                                                    </a></li>
                                                    @if($invoice->payment_status === 'pending')
                                                        <li><a class="dropdown-item" href="#" onclick="sendInvoice({{ $invoice->id }})">
                                                            <i class="fas fa-paper-plane"></i> 送付
                                                        </a></li>
                                                    @endif
                                                    @if($invoice->payment_status !== 'paid' && $invoice->payment_status !== 'cancelled')
                                                        <li><a class="dropdown-item text-success" href="#" onclick="recordPayment({{ $invoice->id }})">
                                                            <i class="fas fa-money-check"></i> 入金記録
                                                        </a></li>
                                                    @endif
                                                    @if($invoice->payment_status === 'sent' || $invoice->payment_status === 'overdue')
                                                        <li><a class="dropdown-item text-warning" href="#" onclick="sendReminder({{ $invoice->id }})">
                                                            <i class="fas fa-bell"></i> 督促
                                                        </a></li>
                                                    @endif
                                                    <li><a class="dropdown-item" href="#" onclick="duplicateInvoice({{ $invoice->id }})">
                                                        <i class="fas fa-copy"></i> 複製
                                                    </a></li>
                                                    @can('delete', $invoice)
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteInvoice({{ $invoice->id }})">
                                                            <i class="fas fa-trash"></i> 削除
                                                        </a></li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- ページネーション -->
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                {{ $invoices->firstItem() }}〜{{ $invoices->lastItem() }}件目 
                                (全{{ $invoices->total() }}件中)
                            </small>
                        </div>
                        <div>
                            {{ $invoices->links() }}
                        </div>
                    </div>
                </div>
            @else
                <!-- データが無い場合 -->
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">請求書データがありません</h5>
                    <p class="text-muted">検索条件を変更するか、新しい請求書を作成してください。</p>
                    @can('create', App\Models\Invoice::class)
                        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> 新規請求書作成
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 一括操作モーダル -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">一括操作</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="bulk-send" style="display: none;">
                    <h6>請求書一括送付</h6>
                    <p>選択された請求書を顧客に送付します。送付方法を選択してください。</p>
                    <select class="form-select" id="send-method">
                        <option value="email">メール送付</option>
                        <option value="pdf">PDF生成</option>
                        <option value="print">印刷用出力</option>
                    </select>
                </div>
                <div id="bulk-payment" style="display: none;">
                    <h6>入金記録</h6>
                    <div class="mb-3">
                        <label class="form-label">入金日</label>
                        <input type="date" class="form-control" id="payment-date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">入金方法</label>
                        <select class="form-select" id="payment-method">
                            <option value="bank_transfer">銀行振込</option>
                            <option value="cash">現金</option>
                            <option value="check">小切手</option>
                            <option value="credit_card">クレジットカード</option>
                        </select>
                    </div>
                </div>
                <div id="bulk-reminder" style="display: none;">
                    <h6>督促送付</h6>
                    <div class="mb-3">
                        <label class="form-label">督促種別</label>
                        <select class="form-select" id="reminder-type">
                            <option value="first">初回督促</option>
                            <option value="second">再督促</option>
                            <option value="final">最終督促</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">督促メッセージ</label>
                        <textarea class="form-control" id="reminder-message" rows="3" 
                                  placeholder="督促に関する追加メッセージを入力"></textarea>
                    </div>
                </div>
                <div id="bulk-export" style="display: none;">
                    <h6>エクスポート形式選択</h6>
                    <select class="form-select" id="export-format">
                        <option value="csv">CSV形式</option>
                        <option value="excel">Excel形式</option>
                        <option value="pdf">PDF形式</option>
                        <option value="accounting">会計ソフト形式</option>
                    </select>
                </div>
                <div id="bulk-delete" style="display: none;">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        選択された請求書を削除します。この操作は取り消せません。
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="executeBulkAction()">実行</button>
            </div>
        </div>
    </div>
</div>

<!-- 督促管理モーダル -->
<div class="modal fade" id="reminderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">督促管理</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reminder-content">
                    <!-- 督促管理内容がここに表示される -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

<!-- 分析表示モーダル -->
<div class="modal fade" id="analyticsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">請求分析</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="analytics-content">
                    <!-- 分析内容がここに表示される -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    border-radius: 0.25rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.dropdown-menu {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.input-group-text {
    min-width: 50px;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // 全選択チェックボックス
    $('#check-all').change(function() {
        $('.row-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });

    // 行チェックボックス
    $('.row-checkbox').change(function() {
        updateBulkActions();
        
        // 全選択チェックボックスの状態更新
        const total = $('.row-checkbox').length;
        const checked = $('.row-checkbox:checked').length;
        $('#check-all').prop('indeterminate', checked > 0 && checked < total);
        $('#check-all').prop('checked', checked === total);
    });

    // 検索フォームの自動送信
    $('#search-form select, #search-form input[type="date"]').change(function() {
        $('#search-form').submit();
    });

    // リアルタイム統計更新
    setInterval(updateStatistics, 30000); // 30秒間隔
});

/**
 * 一括操作エリアの表示・非表示制御
 */
function updateBulkActions() {
    const checkedCount = $('.row-checkbox:checked').length;
    $('#selected-count').text(checkedCount);
    
    if (checkedCount > 0) {
        $('#bulk-actions').slideDown();
    } else {
        $('#bulk-actions').slideUp();
    }
}

/**
 * 一括操作実行
 */
function bulkAction(action) {
    const selectedIds = $('.row-checkbox:checked').map(function() {
        return this.value;
    }).get();

    if (selectedIds.length === 0) {
        alert('操作する項目を選択してください。');
        return;
    }

    // モーダル内容をリセット
    $('#bulk-send, #bulk-payment, #bulk-reminder, #bulk-export, #bulk-delete').hide();
    
    // アクションに応じてモーダル内容を表示
    $('#bulk-' + action).show();
    $('#bulkActionModal').modal('show');
    
    // 現在のアクションを保存
    $('#bulkActionModal').data('action', action);
    $('#bulkActionModal').data('ids', selectedIds);
}

/**
 * 一括操作の実行
 */
function executeBulkAction() {
    const action = $('#bulkActionModal').data('action');
    const ids = $('#bulkActionModal').data('ids');
    
    let data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        ids: ids,
        action: action
    };

    // アクション別のデータ追加
    if (action === 'send') {
        data.method = $('#send-method').val();
    } else if (action === 'payment') {
        data.payment_date = $('#payment-date').val();
        data.payment_method = $('#payment-method').val();
    } else if (action === 'reminder') {
        data.reminder_type = $('#reminder-type').val();
        data.message = $('#reminder-message').val();
    } else if (action === 'export') {
        data.format = $('#export-format').val();
    }

    // Ajax送信
    $.ajax({
        url: '{{ route("invoices.bulk") }}',
        method: 'POST',
        data: data,
        beforeSend: function() {
            $('.modal-footer .btn-primary').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> 処理中...');
        },
        success: function(response) {
            $('#bulkActionModal').modal('hide');
            
            if (action === 'export') {
                // ファイルダウンロード
                window.location.href = response.download_url;
            } else {
                // ページリロード
                location.reload();
            }
        },
        error: function(xhr) {
            alert('エラーが発生しました: ' + xhr.responseJSON.message);
        },
        complete: function() {
            $('.modal-footer .btn-primary').prop('disabled', false).html('実行');
        }
    });
}

/**
 * フィルタークリア
 */
function clearFilters() {
    $('#search-form')[0].reset();
    $('#search-form').submit();
}

/**
 * フィルター保存
 */
function saveFilter() {
    const filterData = $('#search-form').serialize();
    localStorage.setItem('invoice_filter', filterData);
    alert('フィルター設定を保存しました。');
}

/**
 * 保存済みフィルター読み込み
 */
function loadSavedFilter() {
    const savedFilter = localStorage.getItem('invoice_filter');
    if (savedFilter) {
        window.location.href = '{{ route("invoices.index") }}?' + savedFilter;
    } else {
        alert('保存されたフィルター設定がありません。');
    }
}

/**
 * エクスポート実行
 */
function exportData(format) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('export', format);
    window.location.href = currentUrl.toString();
}

/**
 * 請求書ダウンロード
 */
function downloadInvoice(invoiceId) {
    window.location.href = '{{ url("/invoices") }}/' + invoiceId + '/download';
}

/**
 * 請求書印刷
 */
function printInvoice(invoiceId) {
    window.open('{{ url("/invoices") }}/' + invoiceId + '/print', '_blank');
}

/**
 * 請求書送付
 */
function sendInvoice(invoiceId) {
    if (confirm('この請求書を顧客に送付しますか？')) {
        $.ajax({
            url: '{{ url("/invoices") }}/' + invoiceId + '/send',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert('請求書を送付しました。');
                location.reload();
            },
            error: function(xhr) {
                alert('エラーが発生しました: ' + xhr.responseJSON.message);
            }
        });
    }
}

/**
 * 入金記録
 */
function recordPayment(invoiceId) {
    // 入金記録モーダルを表示（簡略版）
    const amount = prompt('入金金額を入力してください：');
    if (amount && !isNaN(amount)) {
        $.ajax({
            url: '{{ url("/invoices") }}/' + invoiceId + '/payment',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                amount: parseFloat(amount),
                payment_date: new Date().toISOString().split('T')[0],
                payment_method: 'bank_transfer'
            },
            success: function(response) {
                alert('入金を記録しました。');
                location.reload();
            },
            error: function(xhr) {
                alert('エラーが発生しました: ' + xhr.responseJSON.message);
            }
        });
    }
}

/**
 * 督促送付
 */
function sendReminder(invoiceId) {
    if (confirm('督促を送付しますか？')) {
        $.ajax({
            url: '{{ url("/invoices") }}/' + invoiceId + '/reminder',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert('督促を送付しました。');
                location.reload();
            },
            error: function(xhr) {
                alert('エラーが発生しました: ' + xhr.responseJSON.message);
            }
        });
    }
}

/**
 * 請求書複製
 */
function duplicateInvoice(invoiceId) {
    if (confirm('この請求書を複製しますか？')) {
        window.location.href = '{{ route("invoices.create") }}?duplicate=' + invoiceId;
    }
}

/**
 * 請求書削除
 */
function deleteInvoice(invoiceId) {
    if (confirm('この請求書を削除しますか？この操作は取り消せません。')) {
        $.ajax({
            url: '{{ url("/invoices") }}/' + invoiceId,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert('請求書を削除しました。');
                location.reload();
            },
            error: function(xhr) {
                alert('エラーが発生しました: ' + xhr.responseJSON.message);
            }
        });
    }
}

/**
 * 督促管理表示
 */
function showReminders() {
    $.ajax({
        url: '{{ route("invoices.reminders") }}',
        method: 'GET',
        success: function(data) {
            $('#reminder-content').html(data);
            $('#reminderModal').modal('show');
        },
        error: function() {
            alert('督促管理データの取得に失敗しました。');
        }
    });
}

/**
 * 分析表示
 */
function showAnalytics() {
    $.ajax({
        url: '{{ route("invoices.analytics") }}',
        method: 'GET',
        success: function(data) {
            $('#analytics-content').html(data);
            $('#analyticsModal').modal('show');
        },
        error: function() {
            alert('分析データの取得に失敗しました。');
        }
    });
}

/**
 * 統計情報更新
 */
function updateStatistics() {
    $.ajax({
        url: '{{ route("invoices.statistics") }}',
        method: 'GET',
        success: function(data) {
            $('#monthly-invoices').text('¥' + new Intl.NumberFormat('ja-JP').format(data.monthly_amount));
            $('#paid-amount').text('¥' + new Intl.NumberFormat('ja-JP').format(data.paid_amount));
            $('#outstanding-amount').text('¥' + new Intl.NumberFormat('ja-JP').format(data.outstanding_amount));
            $('#overdue-count').text(data.overdue_count + '件');
        }
    });
}
</script>
@endpush