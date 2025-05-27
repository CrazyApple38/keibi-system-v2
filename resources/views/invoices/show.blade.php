@extends('layouts.app')

@section('title', '請求書詳細 - ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-0">請求書詳細</h1>
                    <p class="text-muted mb-0">Invoice Details - {{ $invoice->invoice_number }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> 一覧に戻る
                    </a>
                    @can('update', $invoice)
                        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> 編集
                        </a>
                    @endcan
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i> 操作
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="downloadInvoice()">
                                <i class="fas fa-download"></i> PDF ダウンロード
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="printInvoice()">
                                <i class="fas fa-print"></i> 印刷
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            @if($invoice->payment_status === 'pending')
                                <li><a class="dropdown-item text-info" href="#" onclick="sendInvoice()">
                                    <i class="fas fa-paper-plane"></i> 顧客に送付
                                </a></li>
                            @endif
                            @if($invoice->payment_status !== 'paid' && $invoice->payment_status !== 'cancelled')
                                <li><a class="dropdown-item text-success" href="#" onclick="showPaymentModal()">
                                    <i class="fas fa-money-check"></i> 入金記録
                                </a></li>
                            @endif
                            @if($invoice->payment_status === 'sent' || $invoice->payment_status === 'overdue')
                                <li><a class="dropdown-item text-warning" href="#" onclick="sendReminder()">
                                    <i class="fas fa-bell"></i> 督促送付
                                </a></li>
                            @endif
                            @if($invoice->payment_status === 'paid')
                                <li><a class="dropdown-item text-primary" href="#" onclick="generateReceipt()">
                                    <i class="fas fa-receipt"></i> 領収書発行
                                </a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="duplicateInvoice()">
                                <i class="fas fa-copy"></i> 請求書複製
                            </a></li>
                            @if($invoice->is_recurring)
                                <li><a class="dropdown-item" href="#" onclick="manageRecurring()">
                                    <i class="fas fa-redo"></i> 定期請求管理
                                </a></li>
                            @endif
                            @can('delete', $invoice)
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteInvoice()">
                                    <i class="fas fa-trash"></i> 削除
                                </a></li>
                            @endcan
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ステータスアラート -->
    @if($invoice->payment_status === 'pending')
        <div class="alert alert-secondary">
            <i class="fas fa-edit"></i> この請求書は未送付です。顧客への送付を行うことができます。
        </div>
    @elseif($invoice->payment_status === 'sent')
        <div class="alert alert-info">
            <i class="fas fa-paper-plane"></i> この請求書は送付済みです。支払期限: {{ $invoice->due_date->format('Y年m月d日') }}
        </div>
    @elseif($invoice->payment_status === 'overdue')
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> この請求書は支払期限を過ぎています。督促の送付をご検討ください。
        </div>
    @elseif($invoice->payment_status === 'partial')
        <div class="alert alert-warning">
            <i class="fas fa-coins"></i> この請求書は一部入金済みです。残額: ¥{{ number_format($invoice->total_amount - $invoice->paid_amount) }}
        </div>
    @elseif($invoice->payment_status === 'paid')
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> この請求書は入金完了しています。
        </div>
    @endif

    <div class="row">
        <!-- 左側: 請求書詳細情報 -->
        <div class="col-lg-8">
            <!-- 基本情報 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> 基本情報
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong class="text-muted">請求書番号</strong>
                            <div class="fs-5 fw-bold">{{ $invoice->invoice_number }}</div>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted">支払い状況</strong>
                            <div>
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
                                <span class="badge {{ $config['class'] }} fs-6">
                                    <i class="{{ $config['icon'] }}"></i> {{ $config['text'] }}
                                </span>
                                @if($invoice->is_recurring)
                                    <span class="badge bg-info ms-2">定期請求</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted">顧客名</strong>
                            <div>
                                @if($invoice->contract)
                                    <a href="{{ route('customers.show', $invoice->contract->customer) }}" class="text-decoration-none">
                                        {{ $invoice->contract->customer->name }}
                                    </a>
                                    <br><small class="text-muted">{{ $invoice->contract->customer->company_type }}</small>
                                @else
                                    <a href="{{ route('customers.show', $invoice->customer) }}" class="text-decoration-none">
                                        {{ $invoice->customer->name }}
                                    </a>
                                    <br><small class="text-muted">{{ $invoice->customer->company_type }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted">関連契約</strong>
                            <div>
                                @if($invoice->contract)
                                    <a href="{{ route('contracts.show', $invoice->contract) }}" class="text-decoration-none">
                                        {{ $invoice->contract->contract_number }}
                                    </a>
                                    <br><small class="text-muted">{{ $invoice->contract->title }}</small>
                                @else
                                    <span class="text-muted">単発請求</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12">
                            <strong class="text-muted">請求件名</strong>
                            <div class="fs-5">{{ $invoice->title }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 請求日・支払期限 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt"></i> 請求日・支払期限
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <strong class="text-muted">請求日</strong>
                            <div class="fs-6">{{ $invoice->invoice_date->format('Y年m月d日') }}</div>
                        </div>
                        <div class="col-md-4">
                            <strong class="text-muted">支払期限</strong>
                            <div class="fs-6">
                                {{ $invoice->due_date->format('Y年m月d日') }}
                                @if($invoice->due_date->isPast() && $invoice->payment_status !== 'paid')
                                    <span class="badge bg-danger ms-2">期限切れ</span>
                                @elseif($invoice->due_date->diffInDays() <= 7 && $invoice->payment_status !== 'paid')
                                    <span class="badge bg-warning ms-2">期限間近</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <strong class="text-muted">請求期間</strong>
                            <div class="fs-6">
                                @if($invoice->invoice_date->diffInDays($invoice->due_date) > 0)
                                    {{ $invoice->invoice_date->diffInDays($invoice->due_date) }}日間
                                @else
                                    即時払い
                                @endif
                            </div>
                        </div>
                        @if($invoice->payment_terms)
                            <div class="col-md-6">
                                <strong class="text-muted">支払条件</strong>
                                <div>{{ $invoice->payment_terms }}</div>
                            </div>
                        @endif
                        @if($invoice->payment_method)
                            <div class="col-md-6">
                                <strong class="text-muted">支払方法</strong>
                                <div>{{ $invoice->payment_method }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 請求項目詳細 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i> 請求項目詳細
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>項目名</th>
                                    <th class="text-center">数量</th>
                                    <th class="text-center">単位</th>
                                    <th class="text-end">単価</th>
                                    <th class="text-end">金額</th>
                                    <th class="text-center">税区分</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($invoice->items && is_array($invoice->items))
                                    @foreach($invoice->items as $item)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $item['name'] }}</div>
                                                @if(isset($item['description']))
                                                    <small class="text-muted">{{ $item['description'] }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $item['quantity'] ?? 1 }}</td>
                                            <td class="text-center">{{ $item['unit'] ?? '' }}</td>
                                            <td class="text-end">¥{{ number_format($item['unit_price'] ?? 0) }}</td>
                                            <td class="text-end">¥{{ number_format($item['amount'] ?? 0) }}</td>
                                            <td class="text-center">
                                                @php
                                                    $taxLabels = [
                                                        'taxable' => '課税',
                                                        'exempt' => '非課税',
                                                        'zero_rated' => '0%課税'
                                                    ];
                                                @endphp
                                                <span class="badge bg-light text-dark">
                                                    {{ $taxLabels[$item['tax_type'] ?? 'taxable'] ?? '課税' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            請求項目データがありません
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 金額詳細 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calculator"></i> 金額詳細
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="row g-2">
                                <div class="col-6"><strong>小計:</strong></div>
                                <div class="col-6 text-end">¥{{ number_format($invoice->subtotal_amount) }}</div>
                                
                                @if($invoice->discount_amount > 0)
                                    <div class="col-6">割引:</div>
                                    <div class="col-6 text-end text-danger">-¥{{ number_format($invoice->discount_amount) }}</div>
                                @endif
                                
                                @if($invoice->additional_amount > 0)
                                    <div class="col-6">追加料金:</div>
                                    <div class="col-6 text-end text-success">+¥{{ number_format($invoice->additional_amount) }}</div>
                                @endif
                                
                                <div class="col-6">消費税:</div>
                                <div class="col-6 text-end">¥{{ number_format($invoice->tax_amount) }}</div>
                                
                                <div class="col-12"><hr class="my-2"></div>
                                
                                <div class="col-6"><strong>合計金額:</strong></div>
                                <div class="col-6 text-end"><strong class="h5 text-primary">¥{{ number_format($invoice->total_amount) }}</strong></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">入金状況</h6>
                                    <div class="row g-2">
                                        <div class="col-6">入金済み:</div>
                                        <div class="col-6 text-end text-success">¥{{ number_format($invoice->paid_amount) }}</div>
                                        
                                        <div class="col-6">未収金:</div>
                                        <div class="col-6 text-end text-warning">¥{{ number_format($invoice->total_amount - $invoice->paid_amount) }}</div>
                                        
                                        @if($invoice->payment_date)
                                            <div class="col-6">入金日:</div>
                                            <div class="col-6 text-end">{{ $invoice->payment_date->format('Y/m/d') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($invoice->discount_note)
                        <div class="mt-3">
                            <strong class="text-muted">割引・追加料金の備考:</strong>
                            <div class="mt-1 p-2 bg-light rounded">{{ $invoice->discount_note }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 請求詳細・備考 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit"></i> 請求詳細・備考
                    </h5>
                </div>
                <div class="card-body">
                    @if($invoice->description)
                        <div class="mb-3">
                            <strong class="text-muted">請求内容詳細</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                {!! nl2br(e($invoice->description)) !!}
                            </div>
                        </div>
                    @endif
                    
                    @if($invoice->notes)
                        <div class="mb-3">
                            <strong class="text-muted">備考・特記事項</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                {!! nl2br(e($invoice->notes)) !!}
                            </div>
                        </div>
                    @endif
                    
                    @if($invoice->internal_notes && auth()->user()->can('viewInternal', $invoice))
                        <div class="mb-3">
                            <strong class="text-muted">内部メモ</strong>
                            <div class="mt-2 p-3 bg-warning bg-opacity-10 rounded">
                                <small><i class="fas fa-lock"></i> 社内専用</small><br>
                                {!! nl2br(e($invoice->internal_notes)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 添付ファイル -->
            @if($invoice->attachments && count($invoice->attachments) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-paperclip"></i> 添付ファイル
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($invoice->attachments as $attachment)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-2 border rounded">
                                        <i class="fas fa-file me-2"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">{{ $attachment['name'] }}</div>
                                            <small class="text-muted">{{ $attachment['size'] ?? '' }}</small>
                                        </div>
                                        <a href="{{ $attachment['url'] }}" class="btn btn-sm btn-outline-primary" 
                                           target="_blank" title="ダウンロード">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- 右側: サイドバー -->
        <div class="col-lg-4">
            <!-- クイック情報 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-tachometer-alt"></i> クイック情報
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h5 mb-0 text-primary">{{ $statistics['days_since_invoice'] ?? 0 }}</div>
                                <small class="text-muted">請求からの日数</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-0 text-{{ $invoice->due_date->isPast() ? 'danger' : 'success' }}">
                                {{ $invoice->due_date->isPast() ? $invoice->due_date->diffInDays() : $invoice->due_date->diffInDays() }}
                            </div>
                            <small class="text-muted">{{ $invoice->due_date->isPast() ? '延滞日数' : '期限まで' }}</small>
                        </div>
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h5 mb-0 text-info">{{ $statistics['payment_rate'] ?? 0 }}%</div>
                                <small class="text-muted">入金率</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-0 text-warning">{{ $statistics['reminder_count'] ?? 0 }}</div>
                            <small class="text-muted">督促回数</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 入金履歴 -->
            @if($invoice->payments && count($invoice->payments) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-money-check-alt"></i> 入金履歴
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($invoice->payments as $payment)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">¥{{ number_format($payment['amount']) }}</h6>
                                        <small class="text-muted">
                                            {{ $payment['date'] }} - {{ $payment['method'] }}
                                        </small>
                                        @if(isset($payment['note']))
                                            <div class="mt-1 small">{{ $payment['note'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- 送付・督促履歴 -->
            @if($invoice->communications && count($invoice->communications) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-paper-plane"></i> 送付・督促履歴
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($invoice->communications as $comm)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-{{ $comm['type'] === 'sent' ? 'info' : 'warning' }}"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">{{ $comm['action'] }}</h6>
                                        <small class="text-muted">
                                            {{ $comm['date'] }} - {{ $comm['method'] }}
                                        </small>
                                        @if(isset($comm['note']))
                                            <div class="mt-1 small">{{ $comm['note'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- 関連情報 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-link"></i> 関連情報
                    </h6>
                </div>
                <div class="card-body">
                    @if($invoice->contract)
                        <div class="mb-3">
                            <strong class="text-muted">関連契約</strong>
                            <div>
                                <a href="{{ route('contracts.show', $invoice->contract) }}" class="text-decoration-none">
                                    {{ $invoice->contract->contract_number }}
                                </a>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong class="text-muted">関連シフト</strong>
                            <div>
                                <a href="{{ route('shifts.index', ['contract_id' => $invoice->contract_id]) }}" class="text-decoration-none">
                                    シフト一覧を見る
                                </a>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong class="text-muted">関連日報</strong>
                            <div>
                                <a href="{{ route('daily-reports.index', ['contract_id' => $invoice->contract_id]) }}" class="text-decoration-none">
                                    日報一覧を見る
                                </a>
                            </div>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <strong class="text-muted">顧客の他請求書</strong>
                        <div>
                            <a href="{{ route('invoices.index', ['customer_id' => $invoice->contract->customer_id ?? $invoice->customer_id]) }}" class="text-decoration-none">
                                顧客請求書一覧
                            </a>
                        </div>
                    </div>
                    
                    @if($invoice->is_recurring)
                        <div class="mb-3">
                            <strong class="text-muted">定期請求シリーズ</strong>
                            <div>
                                <a href="{{ route('invoices.recurring', $invoice->recurring_id) }}" class="text-decoration-none">
                                    関連請求書一覧
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 更新履歴 -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-clock"></i> 更新履歴
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-2">
                            <strong>作成日時:</strong><br>
                            {{ $invoice->created_at->format('Y/m/d H:i') }}
                        </div>
                        <div class="mb-2">
                            <strong>最終更新:</strong><br>
                            {{ $invoice->updated_at->format('Y/m/d H:i') }}
                        </div>
                        @if($invoice->sent_at)
                            <div class="mb-2">
                                <strong>送付日時:</strong><br>
                                {{ $invoice->sent_at->format('Y/m/d H:i') }}
                            </div>
                        @endif
                        @if($invoice->payment_date)
                            <div class="mb-2">
                                <strong>入金日時:</strong><br>
                                {{ $invoice->payment_date->format('Y/m/d H:i') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 入金記録モーダル -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">入金記録</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="payment-form">
                    <div class="mb-3">
                        <label class="form-label">入金金額</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" class="form-control" id="payment-amount" 
                                   value="{{ $invoice->total_amount - $invoice->paid_amount }}" 
                                   max="{{ $invoice->total_amount - $invoice->paid_amount }}" min="1" step="1" required>
                        </div>
                        <small class="form-text text-muted">
                            残額: ¥{{ number_format($invoice->total_amount - $invoice->paid_amount) }}
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">入金日</label>
                        <input type="date" class="form-control" id="payment-date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">支払方法</label>
                        <select class="form-select" id="payment-method" required>
                            <option value="">支払方法を選択</option>
                            <option value="bank_transfer">銀行振込</option>
                            <option value="cash">現金</option>
                            <option value="check">小切手</option>
                            <option value="credit_card">クレジットカード</option>
                            <option value="other">その他</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">備考</label>
                        <textarea class="form-control" id="payment-note" rows="2" 
                                  placeholder="入金に関する備考があれば記載"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-success" onclick="recordPayment()">入金記録</button>
            </div>
        </div>
    </div>
</div>

<!-- 督促送付モーダル -->
<div class="modal fade" id="reminderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">督促送付</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reminder-form">
                    <div class="mb-3">
                        <label class="form-label">督促種別</label>
                        <select class="form-select" id="reminder-type" required>
                            <option value="first">初回督促</option>
                            <option value="second">再督促</option>
                            <option value="final">最終督促</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">送付方法</label>
                        <select class="form-select" id="reminder-method" required>
                            <option value="email">メール</option>
                            <option value="postal">郵送</option>
                            <option value="phone">電話</option>
                            <option value="fax">FAX</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">督促メッセージ</label>
                        <textarea class="form-control" id="reminder-message" rows="4" 
                                  placeholder="督促に関する追加メッセージを入力"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-warning" onclick="sendReminderAction()">督促送付</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
}

.timeline-item {
    position: relative;
    padding-left: 30px;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 5px;
    top: 12px;
    width: 2px;
    height: calc(100% + 8px);
    background-color: #dee2e6;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.fs-4 {
    font-size: 1.5rem !important;
}

.fs-5 {
    font-size: 1.25rem !important;
}

.fs-6 {
    font-size: 1rem !important;
}

@media print {
    .btn, .dropdown, .alert {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // 初期化処理
});

/**
 * 請求書ダウンロード
 */
function downloadInvoice() {
    window.location.href = '{{ route("invoices.download", $invoice) }}';
}

/**
 * 請求書印刷
 */
function printInvoice() {
    window.print();
}

/**
 * 請求書送付
 */
function sendInvoice() {
    if (confirm('この請求書を顧客に送付しますか？')) {
        $.ajax({
            url: '{{ route("invoices.send", $invoice) }}',
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
 * 入金記録モーダル表示
 */
function showPaymentModal() {
    $('#paymentModal').modal('show');
}

/**
 * 入金記録実行
 */
function recordPayment() {
    const amount = $('#payment-amount').val();
    const date = $('#payment-date').val();
    const method = $('#payment-method').val();
    const note = $('#payment-note').val();
    
    if (!amount || !date || !method) {
        alert('必須項目を入力してください。');
        return;
    }
    
    $.ajax({
        url: '{{ route("invoices.payment", $invoice) }}',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            amount: parseFloat(amount),
            payment_date: date,
            payment_method: method,
            note: note
        },
        beforeSend: function() {
            $('#paymentModal .btn-success').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> 処理中...');
        },
        success: function(response) {
            $('#paymentModal').modal('hide');
            alert('入金を記録しました。');
            location.reload();
        },
        error: function(xhr) {
            alert('エラーが発生しました: ' + xhr.responseJSON.message);
        },
        complete: function() {
            $('#paymentModal .btn-success').prop('disabled', false).html('入金記録');
        }
    });
}

/**
 * 督促送付
 */
function sendReminder() {
    $('#reminderModal').modal('show');
}

/**
 * 督促送付実行
 */
function sendReminderAction() {
    const type = $('#reminder-type').val();
    const method = $('#reminder-method').val();
    const message = $('#reminder-message').val();
    
    if (!type || !method) {
        alert('必須項目を入力してください。');
        return;
    }
    
    $.ajax({
        url: '{{ route("invoices.reminder", $invoice) }}',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            reminder_type: type,
            method: method,
            message: message
        },
        beforeSend: function() {
            $('#reminderModal .btn-warning').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> 送付中...');
        },
        success: function(response) {
            $('#reminderModal').modal('hide');
            alert('督促を送付しました。');
            location.reload();
        },
        error: function(xhr) {
            alert('エラーが発生しました: ' + xhr.responseJSON.message);
        },
        complete: function() {
            $('#reminderModal .btn-warning').prop('disabled', false).html('督促送付');
        }
    });
}

/**
 * 領収書発行
 */
function generateReceipt() {
    if (confirm('領収書を発行しますか？')) {
        window.location.href = '{{ route("invoices.receipt", $invoice) }}';
    }
}

/**
 * 請求書複製
 */
function duplicateInvoice() {
    if (confirm('この請求書を複製して新しい請求書を作成しますか？')) {
        window.location.href = '{{ route("invoices.create") }}?duplicate={{ $invoice->id }}';
    }
}

/**
 * 定期請求管理
 */
function manageRecurring() {
    window.location.href = '{{ route("invoices.recurring.manage", $invoice->recurring_id) }}';
}

/**
 * 請求書削除
 */
function deleteInvoice() {
    if (confirm('この請求書を削除しますか？この操作は取り消せません。')) {
        $.ajax({
            url: '{{ route("invoices.destroy", $invoice) }}',
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert('請求書を削除しました。');
                window.location.href = '{{ route("invoices.index") }}';
            },
            error: function(xhr) {
                alert('エラーが発生しました: ' + xhr.responseJSON.message);
            }
        });
    }
}
</script>
@endpush