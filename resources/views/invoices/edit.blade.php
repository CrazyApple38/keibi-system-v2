@extends('layouts.app')

@section('title', '請求書編集 - ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-0">請求書編集</h1>
                    <p class="text-muted mb-0">Edit Invoice - {{ $invoice->invoice_number }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> 詳細に戻る
                    </a>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> 一覧に戻る
                    </a>
                    <button type="button" class="btn btn-outline-info" onclick="showChangeHistory()">
                        <i class="fas fa-history"></i> 変更履歴
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="compareVersions()">
                        <i class="fas fa-exchange-alt"></i> 変更前後比較
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 編集制限アラート -->
    @if($invoice->payment_status === 'paid')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>注意:</strong> この請求書は入金済みです。編集は制限されている項目があります。
        </div>
    @elseif($invoice->payment_status === 'sent')
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            <strong>情報:</strong> この請求書は送付済みです。重要な変更は顧客への通知が必要な場合があります。
        </div>
    @elseif($invoice->payment_status === 'overdue')
        <div class="alert alert-danger">
            <i class="fas fa-clock"></i> 
            <strong>延滞中:</strong> この請求書は支払期限を過ぎています。編集時は顧客への通知をご検討ください。
        </div>
    @endif

    <!-- 編集理由記録 -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-edit"></i> 編集理由
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">編集理由</label>
                    <select class="form-select" id="edit-reason" name="edit_reason" required>
                        <option value="">編集理由を選択</option>
                        <option value="correction">記載内容の修正</option>
                        <option value="amount_change">金額変更</option>
                        <option value="item_change">項目変更</option>
                        <option value="customer_request">顧客からの要求</option>
                        <option value="payment_terms">支払条件変更</option>
                        <option value="additional_items">追加項目</option>
                        <option value="other">その他</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label required">編集詳細</label>
                    <input type="text" class="form-control" id="edit-detail" name="edit_detail" 
                           placeholder="編集内容の詳細を記載" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">編集備考</label>
                    <textarea class="form-control" id="edit-notes" name="edit_notes" rows="2" 
                              placeholder="編集に関する詳細な備考があれば記載"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- 請求書編集フォーム -->
    <form id="invoice-edit-form" method="POST" action="{{ route('invoices.update', $invoice) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <!-- 編集理由の隠しフィールド -->
        <input type="hidden" name="edit_reason_hidden" id="edit-reason-hidden">
        <input type="hidden" name="edit_detail_hidden" id="edit-detail-hidden">
        <input type="hidden" name="edit_notes_hidden" id="edit-notes-hidden">

        <!-- 基本情報 -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> 基本情報
                    </h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="basic-info-changed">
                        <label class="form-check-label" for="basic-info-changed">変更有り</label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">請求書番号</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" 
                                   name="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number) }}" 
                                   {{ $invoice->payment_status === 'paid' ? 'readonly' : '' }}>
                            @if($invoice->payment_status === 'paid')
                                <span class="input-group-text"><i class="fas fa-lock text-warning"></i></span>
                            @endif
                        </div>
                        @error('invoice_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($invoice->payment_status === 'paid')
                            <small class="form-text text-warning">入金済みのため変更できません</small>
                        @endif
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label required">顧客</label>
                        <div class="input-group">
                            <select class="form-select @error('customer_id') is-invalid @enderror" name="customer_id" required
                                    onchange="updateCustomerInfo()" {{ $invoice->payment_status === 'paid' ? 'disabled' : '' }}>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                            {{ old('customer_id', $invoice->contract->customer_id ?? $invoice->customer_id) == $customer->id ? 'selected' : '' }}
                                            data-name="{{ $customer->name }}"
                                            data-address="{{ $customer->address }}"
                                            data-phone="{{ $customer->phone }}"
                                            data-email="{{ $customer->email }}"
                                            data-payment-terms="{{ $customer->payment_terms }}">
                                        {{ $customer->name }} ({{ $customer->company_type }})
                                    </option>
                                @endforeach
                            </select>
                            @if($invoice->payment_status === 'paid')
                                <span class="input-group-text"><i class="fas fa-lock text-warning"></i></span>
                                <input type="hidden" name="customer_id" value="{{ $invoice->contract->customer_id ?? $invoice->customer_id }}">
                            @endif
                        </div>
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">関連契約</label>
                        <div class="input-group">
                            <select class="form-select" name="contract_id" onchange="updateContractInfo()"
                                    {{ $invoice->payment_status === 'paid' ? 'disabled' : '' }}>
                                <option value="">契約を選択（任意）</option>
                                @foreach($contracts as $contract)
                                    <option value="{{ $contract->id }}" 
                                            {{ old('contract_id', $invoice->contract_id) == $contract->id ? 'selected' : '' }}>
                                        {{ $contract->contract_number }} - {{ $contract->title }}
                                    </option>
                                @endforeach
                            </select>
                            @if($invoice->payment_status === 'paid')
                                <span class="input-group-text"><i class="fas fa-lock text-warning"></i></span>
                                <input type="hidden" name="contract_id" value="{{ $invoice->contract_id }}">
                            @endif
                        </div>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label required">請求件名</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               name="title" value="{{ old('title', $invoice->title) }}" required
                               placeholder="請求の件名を入力">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">請求種別</label>
                        <div class="input-group">
                            <select class="form-select @error('invoice_type') is-invalid @enderror" name="invoice_type"
                                    onchange="updateInvoiceType()" {{ $invoice->payment_status === 'paid' ? 'disabled' : '' }}>
                                <option value="standard" {{ old('invoice_type', $invoice->invoice_type) === 'standard' ? 'selected' : '' }}>通常請求</option>
                                <option value="contract_based" {{ old('invoice_type', $invoice->invoice_type) === 'contract_based' ? 'selected' : '' }}>契約ベース請求</option>
                                <option value="recurring" {{ old('invoice_type', $invoice->invoice_type) === 'recurring' ? 'selected' : '' }}>定期請求</option>
                                <option value="partial" {{ old('invoice_type', $invoice->invoice_type) === 'partial' ? 'selected' : '' }}>分割請求</option>
                                <option value="final" {{ old('invoice_type', $invoice->invoice_type) === 'final' ? 'selected' : '' }}>最終請求</option>
                            </select>
                            @if($invoice->payment_status === 'paid')
                                <span class="input-group-text"><i class="fas fa-lock text-warning"></i></span>
                                <input type="hidden" name="invoice_type" value="{{ $invoice->invoice_type }}">
                            @endif
                        </div>
                        @error('invoice_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- 変更影響チェック -->
                <div class="row mt-3" id="change-impact" style="display: none;">
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> 変更影響チェック</h6>
                            <div id="impact-details">
                                <!-- JavaScript で動的に表示される -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 請求日・支払期限 -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt"></i> 請求日・支払期限
                    </h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="date-info-changed">
                        <label class="form-check-label" for="date-info-changed">変更有り</label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label required">請求日</label>
                        <div class="input-group">
                            <input type="date" class="form-control @error('invoice_date') is-invalid @enderror" 
                                   name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required
                                   onchange="calculateDueDate(); checkDateChange()" 
                                   {{ $invoice->payment_status === 'paid' ? 'readonly' : '' }}>
                            @if($invoice->payment_status === 'paid')
                                <span class="input-group-text"><i class="fas fa-lock text-warning"></i></span>
                            @endif
                        </div>
                        @error('invoice_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">元: {{ $invoice->invoice_date->format('Y/m/d') }}</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">支払期限</label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                               name="due_date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required
                               onchange="checkDateChange()">
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">元: {{ $invoice->due_date->format('Y/m/d') }}</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">支払条件</label>
                        <select class="form-select" name="payment_terms" onchange="calculateDueDate()">
                            <option value="">支払条件を選択</option>
                            <option value="net_30" {{ old('payment_terms', $invoice->payment_terms) === 'net_30' ? 'selected' : '' }}>30日以内</option>
                            <option value="net_60" {{ old('payment_terms', $invoice->payment_terms) === 'net_60' ? 'selected' : '' }}>60日以内</option>
                            <option value="monthly_end" {{ old('payment_terms', $invoice->payment_terms) === 'monthly_end' ? 'selected' : '' }}>月末締め翌月末払い</option>
                            <option value="monthly_25" {{ old('payment_terms', $invoice->payment_terms) === 'monthly_25' ? 'selected' : '' }}>25日締め翌月25日払い</option>
                            <option value="immediate" {{ old('payment_terms', $invoice->payment_terms) === 'immediate' ? 'selected' : '' }}>即時払い</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">支払方法</label>
                        <select class="form-select" name="payment_method">
                            <option value="">支払方法を選択</option>
                            <option value="bank_transfer" {{ old('payment_method', $invoice->payment_method) === 'bank_transfer' ? 'selected' : '' }}>銀行振込</option>
                            <option value="cash" {{ old('payment_method', $invoice->payment_method) === 'cash' ? 'selected' : '' }}>現金</option>
                            <option value="check" {{ old('payment_method', $invoice->payment_method) === 'check' ? 'selected' : '' }}>小切手</option>
                            <option value="credit_card" {{ old('payment_method', $invoice->payment_method) === 'credit_card' ? 'selected' : '' }}>クレジットカード</option>
                        </select>
                    </div>
                </div>

                <!-- 定期請求設定 -->
                <div class="row g-3 mt-3" id="recurring-settings" 
                     style="{{ $invoice->invoice_type === 'recurring' ? 'display: block;' : 'display: none;' }}">
                    <div class="col-md-12">
                        <h6>定期請求設定</h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">請求間隔</label>
                        <select class="form-select" name="recurring_interval">
                            <option value="monthly" {{ old('recurring_interval', $invoice->recurring_interval) === 'monthly' ? 'selected' : '' }}>毎月</option>
                            <option value="quarterly" {{ old('recurring_interval', $invoice->recurring_interval) === 'quarterly' ? 'selected' : '' }}>四半期</option>
                            <option value="semi_annual" {{ old('recurring_interval', $invoice->recurring_interval) === 'semi_annual' ? 'selected' : '' }}>半年</option>
                            <option value="annual" {{ old('recurring_interval', $invoice->recurring_interval) === 'annual' ? 'selected' : '' }}>年次</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">次回請求日</label>
                        <input type="date" class="form-control" name="next_invoice_date" 
                               value="{{ old('next_invoice_date', $invoice->next_invoice_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">終了日</label>
                        <input type="date" class="form-control" name="recurring_end_date" 
                               value="{{ old('recurring_end_date', $invoice->recurring_end_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="auto_send" value="1" id="auto-send"
                                   {{ old('auto_send', $invoice->auto_send) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto-send">
                                自動送付
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 請求項目 -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i> 請求項目
                    </h5>
                    <div class="d-flex gap-2">
                        @if($invoice->payment_status !== 'paid')
                            @if($invoice->contract)
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="loadContractItems()">
                                    <i class="fas fa-file-contract"></i> 契約項目読み込み
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="loadShiftData()">
                                    <i class="fas fa-calendar-alt"></i> シフト実績読み込み
                                </button>
                            @endif
                            <button type="button" class="btn btn-sm btn-primary" onclick="addInvoiceItem()">
                                <i class="fas fa-plus"></i> 項目追加
                            </button>
                        @else
                            <span class="text-warning"><i class="fas fa-lock"></i> 入金済みのため編集不可</span>
                        @endif
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="items-changed">
                            <label class="form-check-label" for="items-changed">変更有り</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($invoice->payment_status === 'paid')
                    <!-- 入金済みの場合は読み取り専用表示 -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
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
                                    @foreach($invoice->items as $index => $item)
                                        <tr>
                                            <td>{{ $item['name'] ?? '' }}</td>
                                            <td class="text-center">{{ $item['quantity'] ?? 1 }}</td>
                                            <td class="text-center">{{ $item['unit'] ?? '' }}</td>
                                            <td class="text-end">¥{{ number_format($item['unit_price'] ?? 0) }}</td>
                                            <td class="text-end">¥{{ number_format($item['amount'] ?? 0) }}</td>
                                            <td class="text-center">{{ $item['tax_type'] ?? 'taxable' }}</td>
                                        </tr>
                                        <!-- 隠しフィールドとして項目データを保持 -->
                                        <input type="hidden" name="items[{{ $index }}][name]" value="{{ $item['name'] ?? '' }}">
                                        <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}">
                                        <input type="hidden" name="items[{{ $index }}][unit]" value="{{ $item['unit'] ?? '' }}">
                                        <input type="hidden" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] ?? 0 }}">
                                        <input type="hidden" name="items[{{ $index }}][amount]" value="{{ $item['amount'] ?? 0 }}">
                                        <input type="hidden" name="items[{{ $index }}][tax_type]" value="{{ $item['tax_type'] ?? 'taxable' }}">
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-lock"></i> 請求項目は入金済みのため編集できません。
                    </div>
                @else
                    <!-- 編集可能な場合の項目テーブル -->
                    <div class="table-responsive">
                        <table class="table table-bordered" id="invoice-items-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="30%">項目名</th>
                                    <th width="15%">数量</th>
                                    <th width="10%">単位</th>
                                    <th width="15%">単価</th>
                                    <th width="15%">金額</th>
                                    <th width="10%">税区分</th>
                                    <th width="5%">操作</th>
                                </tr>
                            </thead>
                            <tbody id="invoice-items">
                                @if($invoice->items && is_array($invoice->items))
                                    @foreach($invoice->items as $index => $item)
                                        <tr class="invoice-item">
                                            <td>
                                                <input type="text" class="form-control item-name" name="items[{{ $index }}][name]" 
                                                       value="{{ $item['name'] ?? '' }}" placeholder="項目名を入力" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control item-quantity" name="items[{{ $index }}][quantity]" 
                                                       value="{{ $item['quantity'] ?? 1 }}" step="0.1" min="0" onchange="calculateItemAmount(this)" required>
                                            </td>
                                            <td>
                                                <select class="form-select item-unit" name="items[{{ $index }}][unit]">
                                                    <option value="時間" {{ ($item['unit'] ?? '') === '時間' ? 'selected' : '' }}>時間</option>
                                                    <option value="日" {{ ($item['unit'] ?? '') === '日' ? 'selected' : '' }}>日</option>
                                                    <option value="月" {{ ($item['unit'] ?? '') === '月' ? 'selected' : '' }}>月</option>
                                                    <option value="人" {{ ($item['unit'] ?? '') === '人' ? 'selected' : '' }}>人</option>
                                                    <option value="件" {{ ($item['unit'] ?? '') === '件' ? 'selected' : '' }}>件</option>
                                                    <option value="式" {{ ($item['unit'] ?? '') === '式' ? 'selected' : '' }}>式</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control item-unit-price" name="items[{{ $index }}][unit_price]" 
                                                       value="{{ $item['unit_price'] ?? 0 }}" step="1" min="0" onchange="calculateItemAmount(this)" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control item-amount" name="items[{{ $index }}][amount]" 
                                                       value="{{ $item['amount'] ?? 0 }}" step="1" readonly>
                                            </td>
                                            <td>
                                                <select class="form-select item-tax" name="items[{{ $index }}][tax_type]" onchange="calculateTotals()">
                                                    <option value="taxable" {{ ($item['tax_type'] ?? 'taxable') === 'taxable' ? 'selected' : '' }}>課税</option>
                                                    <option value="exempt" {{ ($item['tax_type'] ?? 'taxable') === 'exempt' ? 'selected' : '' }}>非課税</option>
                                                    <option value="zero_rated" {{ ($item['tax_type'] ?? 'taxable') === 'zero_rated' ? 'selected' : '' }}>0%課税</option>
                                                </select>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="removeInvoiceItem(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <!-- 項目がない場合の初期行 -->
                                    <tr class="invoice-item">
                                        <td>
                                            <input type="text" class="form-control item-name" name="items[0][name]" 
                                                   placeholder="項目名を入力" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control item-quantity" name="items[0][quantity]" 
                                                   value="1" step="0.1" min="0" onchange="calculateItemAmount(this)" required>
                                        </td>
                                        <td>
                                            <select class="form-select item-unit" name="items[0][unit]">
                                                <option value="時間">時間</option>
                                                <option value="日">日</option>
                                                <option value="月">月</option>
                                                <option value="人">人</option>
                                                <option value="件">件</option>
                                                <option value="式">式</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control item-unit-price" name="items[0][unit_price]" 
                                                   value="0" step="1" min="0" onchange="calculateItemAmount(this)" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control item-amount" name="items[0][amount]" 
                                                   value="0" step="1" readonly>
                                        </td>
                                        <td>
                                            <select class="form-select item-tax" name="items[0][tax_type]" onchange="calculateTotals()">
                                                <option value="taxable">課税</option>
                                                <option value="exempt">非課税</option>
                                                <option value="zero_rated">0%課税</option>
                                            </select>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeInvoiceItem(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- 金額計算 -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calculator"></i> 金額計算
                    </h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="amount-changed">
                        <label class="form-check-label" for="amount-changed">変更有り</label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <!-- 割引・追加料金 -->
                        <div class="row g-2">
                            <div class="col-12">
                                <h6>割引・追加料金</h6>
                            </div>
                            <div class="col-6">
                                <label class="form-label">割引</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="discount_amount" 
                                           value="{{ old('discount_amount', $invoice->discount_amount ?? 0) }}" 
                                           step="1" min="0" onchange="calculateTotals(); checkAmountChange()" id="discount-amount"
                                           {{ $invoice->payment_status === 'paid' ? 'readonly' : '' }}>
                                    <span class="input-group-text">円</span>
                                </div>
                                <small class="form-text text-muted">元: ¥{{ number_format($invoice->discount_amount ?? 0) }}</small>
                            </div>
                            <div class="col-6">
                                <label class="form-label">追加料金</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="additional_amount" 
                                           value="{{ old('additional_amount', $invoice->additional_amount ?? 0) }}" 
                                           step="1" min="0" onchange="calculateTotals(); checkAmountChange()" id="additional-amount"
                                           {{ $invoice->payment_status === 'paid' ? 'readonly' : '' }}>
                                    <span class="input-group-text">円</span>
                                </div>
                                <small class="form-text text-muted">元: ¥{{ number_format($invoice->additional_amount ?? 0) }}</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">備考</label>
                                <input type="text" class="form-control" name="discount_note" 
                                       value="{{ old('discount_note', $invoice->discount_note) }}"
                                       placeholder="割引・追加料金の理由">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- 金額サマリー -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-6"><strong>小計:</strong></div>
                                    <div class="col-6 text-end" id="subtotal-amount">¥{{ number_format($invoice->subtotal_amount ?? 0) }}</div>
                                    
                                    <div class="col-6">割引:</div>
                                    <div class="col-6 text-end text-danger" id="discount-display">¥{{ number_format($invoice->discount_amount ?? 0) }}</div>
                                    
                                    <div class="col-6">追加料金:</div>
                                    <div class="col-6 text-end text-success" id="additional-display">¥{{ number_format($invoice->additional_amount ?? 0) }}</div>
                                    
                                    <div class="col-6">課税対象額:</div>
                                    <div class="col-6 text-end" id="taxable-amount">¥{{ number_format($invoice->subtotal_amount - ($invoice->discount_amount ?? 0) + ($invoice->additional_amount ?? 0)) }}</div>
                                    
                                    <div class="col-6">消費税(10%):</div>
                                    <div class="col-6 text-end" id="tax-amount-display">¥{{ number_format($invoice->tax_amount ?? 0) }}</div>
                                    
                                    <div class="col-12"><hr class="my-2"></div>
                                    
                                    <div class="col-6"><strong>合計金額:</strong></div>
                                    <div class="col-6 text-end"><strong class="h5 text-primary" id="total-amount-display">¥{{ number_format($invoice->total_amount ?? 0) }}</strong></div>
                                </div>
                                
                                <!-- 変更前後比較 -->
                                <div class="mt-3" id="amount-comparison" style="display: none;">
                                    <div class="border-top pt-2">
                                        <small class="text-muted">変更前: ¥{{ number_format($invoice->total_amount) }}</small><br>
                                        <small class="text-muted">変更後: <span id="new-total">¥0</span></small><br>
                                        <small class="text-muted">差額: <span id="amount-diff" class="fw-bold">¥0</span></small>
                                    </div>
                                </div>
                                
                                <!-- 隠しフィールド -->
                                <input type="hidden" name="subtotal_amount" id="subtotal-hidden" value="{{ $invoice->subtotal_amount ?? 0 }}">
                                <input type="hidden" name="tax_amount" id="tax-amount-hidden" value="{{ $invoice->tax_amount ?? 0 }}">
                                <input type="hidden" name="total_amount" id="total-amount-hidden" value="{{ $invoice->total_amount ?? 0 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 請求詳細・備考 -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit"></i> 請求詳細・備考
                    </h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="details-changed">
                        <label class="form-check-label" for="details-changed">変更有り</label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">請求内容詳細</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" rows="4" 
                                  placeholder="請求内容の詳細説明を記載してください">{{ old('description', $invoice->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">備考・特記事項</label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="請求に関する備考があれば記載">{{ old('notes', $invoice->notes) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">内部メモ</label>
                        <textarea class="form-control" name="internal_notes" rows="3" 
                                  placeholder="社内用メモ（請求書には表示されません）">{{ old('internal_notes', $invoice->internal_notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- 添付ファイル -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-paperclip"></i> 添付ファイル
                    </h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="attachments-changed">
                        <label class="form-check-label" for="attachments-changed">変更有り</label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- 既存ファイル -->
                @if($invoice->attachments && count($invoice->attachments) > 0)
                    <div class="mb-3">
                        <label class="form-label">既存ファイル</label>
                        <div class="row g-2">
                            @foreach($invoice->attachments as $index => $attachment)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-2 border rounded">
                                        <i class="fas fa-file me-2"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">{{ $attachment['name'] }}</div>
                                            <small class="text-muted">{{ $attachment['size'] ?? '' }}</small>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a href="{{ $attachment['url'] }}" class="btn btn-sm btn-outline-primary" 
                                               target="_blank" title="ダウンロード">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="removeAttachment({{ $index }})" title="削除">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="existing_attachments[{{ $index }}]" value="{{ json_encode($attachment) }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <!-- 新規ファイル追加 -->
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">新規ファイル追加</label>
                        <input type="file" class="form-control" name="new_attachments[]" multiple 
                               accept=".pdf,.doc,.docx,.xlsx,.jpg,.jpeg,.png,.gif">
                        <small class="form-text text-muted">
                            PDF、Word、Excel、画像ファイル対応。複数ファイル選択可能。最大10MB/ファイル
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- フォームアクション -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button type="button" class="btn btn-outline-secondary" onclick="previewChanges()">
                            <i class="fas fa-eye"></i> 変更プレビュー
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="validateChanges()">
                            <i class="fas fa-check-circle"></i> 変更検証
                        </button>
                    </div>
                    <div>
                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> キャンセル
                        </a>
                        <button type="button" class="btn btn-warning" onclick="saveChanges('draft')">
                            <i class="fas fa-save"></i> 下書き保存
                        </button>
                        <button type="submit" class="btn btn-primary" onclick="saveChanges('update')">
                            <i class="fas fa-save"></i> 変更保存
                        </button>
                        @if($invoice->payment_status === 'pending')
                            <button type="submit" class="btn btn-success" onclick="saveChanges('update_and_send')">
                                <i class="fas fa-paper-plane"></i> 保存・送付
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- 変更履歴モーダル -->
<div class="modal fade" id="changeHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">変更履歴</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="change-history-content">
                    <!-- 変更履歴がここに表示される -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

<!-- 変更前後比較モーダル -->
<div class="modal fade" id="compareModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">変更前後比較</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>変更前</h6>
                        <div id="original-content" class="p-3 border rounded bg-light">
                            <!-- 元の内容がここに表示される -->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>変更後</h6>
                        <div id="modified-content" class="p-3 border rounded bg-light">
                            <!-- 変更後の内容がここに表示される -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

<!-- プレビューモーダル -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">変更プレビュー</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="invoice-preview" class="p-4">
                    <!-- プレビュー内容がここに表示される -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" onclick="printPreview()">
                    <i class="fas fa-print"></i> 印刷
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.invoice-item input, .invoice-item select {
    font-size: 0.9rem;
}

.table-bordered th, .table-bordered td {
    border: 1px solid #dee2e6;
}

#invoice-preview {
    background-color: #fff;
    font-size: 14px;
    line-height: 1.6;
}

.input-group-text {
    min-width: 50px;
}

.form-control:read-only {
    background-color: #f8f9fa;
    opacity: 0.8;
}

.change-highlight {
    background-color: #fff3cd !important;
    border-color: #ffc107 !important;
}

.change-indicator {
    border-left: 4px solid #ffc107;
    padding-left: 10px;
}

@media print {
    .modal-header, .modal-footer {
        display: none !important;
    }
    
    #invoice-preview {
        padding: 0 !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
let itemIndex = {{ count($invoice->items ?? []) }};
let originalData = {
    invoice_number: '{{ $invoice->invoice_number }}',
    title: '{{ $invoice->title }}',
    invoice_date: '{{ $invoice->invoice_date->format('Y-m-d') }}',
    due_date: '{{ $invoice->due_date->format('Y-m-d') }}',
    total_amount: {{ $invoice->total_amount }},
    items: @json($invoice->items ?? [])
};

$(document).ready(function() {
    // 初期設定
    calculateTotals();
    updateInvoiceType();
    
    // 変更検知
    setupChangeDetection();
    
    // 編集理由の入力チェック
    $('#edit-reason, #edit-detail').change(function() {
        updateEditReason();
    });
});

/**
 * 変更検知の設定
 */
function setupChangeDetection() {
    // フォーム要素の変更を監視
    $('#invoice-edit-form input, #invoice-edit-form select, #invoice-edit-form textarea').change(function() {
        checkForChanges();
    });
    
    // リアルタイム変更検知
    setInterval(checkForChanges, 1000);
}

/**
 * 変更のチェック
 */
function checkForChanges() {
    let hasChanges = false;
    
    // 基本情報の変更チェック
    if ($('input[name="invoice_number"]').val() !== originalData.invoice_number ||
        $('input[name="title"]').val() !== originalData.title) {
        $('#basic-info-changed').prop('checked', true);
        hasChanges = true;
    }
    
    // 日付の変更チェック
    if ($('input[name="invoice_date"]').val() !== originalData.invoice_date ||
        $('input[name="due_date"]').val() !== originalData.due_date) {
        $('#date-info-changed').prop('checked', true);
        hasChanges = true;
    }
    
    // 金額の変更チェック
    const currentTotal = parseFloat($('#total-amount-hidden').val()) || 0;
    if (Math.abs(currentTotal - originalData.total_amount) > 0.01) {
        $('#amount-changed').prop('checked', true);
        $('#amount-comparison').show();
        $('#new-total').text('¥' + new Intl.NumberFormat('ja-JP').format(currentTotal));
        const diff = currentTotal - originalData.total_amount;
        $('#amount-diff').text((diff >= 0 ? '+' : '') + '¥' + new Intl.NumberFormat('ja-JP').format(Math.abs(diff)));
        $('#amount-diff').removeClass('text-success text-danger').addClass(diff >= 0 ? 'text-success' : 'text-danger');
        hasChanges = true;
    }
    
    // 変更影響チェック
    if (hasChanges) {
        showChangeImpact();
    }
}

/**
 * 請求項目追加
 */
function addInvoiceItem() {
    itemIndex++;
    const newRow = `
        <tr class="invoice-item">
            <td>
                <input type="text" class="form-control item-name" name="items[${itemIndex}][name]" 
                       placeholder="項目名を入力" required>
            </td>
            <td>
                <input type="number" class="form-control item-quantity" name="items[${itemIndex}][quantity]" 
                       value="1" step="0.1" min="0" onchange="calculateItemAmount(this)" required>
            </td>
            <td>
                <select class="form-select item-unit" name="items[${itemIndex}][unit]">
                    <option value="時間">時間</option>
                    <option value="日">日</option>
                    <option value="月">月</option>
                    <option value="人">人</option>
                    <option value="件">件</option>
                    <option value="式">式</option>
                </select>
            </td>
            <td>
                <input type="number" class="form-control item-unit-price" name="items[${itemIndex}][unit_price]" 
                       value="0" step="1" min="0" onchange="calculateItemAmount(this)" required>
            </td>
            <td>
                <input type="number" class="form-control item-amount" name="items[${itemIndex}][amount]" 
                       value="0" step="1" readonly>
            </td>
            <td>
                <select class="form-select item-tax" name="items[${itemIndex}][tax_type]" onchange="calculateTotals()">
                    <option value="taxable">課税</option>
                    <option value="exempt">非課税</option>
                    <option value="zero_rated">0%課税</option>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeInvoiceItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    $('#invoice-items').append(newRow);
    $('#items-changed').prop('checked', true);
    
    // 新しく追加された行にフォーカス
    $('#invoice-items tr:last-child .item-name').focus();
}

/**
 * 請求項目削除
 */
function removeInvoiceItem(button) {
    if ($('.invoice-item').length > 1) {
        $(button).closest('tr').remove();
        calculateTotals();
        $('#items-changed').prop('checked', true);
    } else {
        alert('最低1つの項目は必要です。');
    }
}

/**
 * 項目金額計算
 */
function calculateItemAmount(element) {
    const row = $(element).closest('tr');
    const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
    const unitPrice = parseFloat(row.find('.item-unit-price').val()) || 0;
    const amount = quantity * unitPrice;
    
    row.find('.item-amount').val(amount);
    calculateTotals();
    $('#items-changed').prop('checked', true);
}

/**
 * 合計金額計算
 */
function calculateTotals() {
    let subtotal = 0;
    let taxableAmount = 0;
    
    // 各項目の金額を集計
    $('.invoice-item').each(function() {
        const amount = parseFloat($(this).find('.item-amount').val()) || 0;
        const taxType = $(this).find('.item-tax').val();
        
        subtotal += amount;
        if (taxType === 'taxable') {
            taxableAmount += amount;
        }
    });
    
    // 割引・追加料金
    const discountAmount = parseFloat($('#discount-amount').val()) || 0;
    const additionalAmount = parseFloat($('#additional-amount').val()) || 0;
    
    // 税額計算
    const adjustedTaxableAmount = Math.max(0, taxableAmount - discountAmount + additionalAmount);
    const taxAmount = Math.floor(adjustedTaxableAmount * 0.1);
    
    // 合計金額
    const totalAmount = subtotal - discountAmount + additionalAmount + taxAmount;
    
    // 表示更新
    $('#subtotal-amount').text('¥' + new Intl.NumberFormat('ja-JP').format(subtotal));
    $('#discount-display').text('¥' + new Intl.NumberFormat('ja-JP').format(discountAmount));
    $('#additional-display').text('¥' + new Intl.NumberFormat('ja-JP').format(additionalAmount));
    $('#taxable-amount').text('¥' + new Intl.NumberFormat('ja-JP').format(adjustedTaxableAmount));
    $('#tax-amount-display').text('¥' + new Intl.NumberFormat('ja-JP').format(taxAmount));
    $('#total-amount-display').text('¥' + new Intl.NumberFormat('ja-JP').format(totalAmount));
    
    // 隠しフィールド更新
    $('#subtotal-hidden').val(subtotal);
    $('#tax-amount-hidden').val(taxAmount);
    $('#total-amount-hidden').val(totalAmount);
}

/**
 * 支払期限自動計算
 */
function calculateDueDate() {
    const invoiceDate = $('input[name="invoice_date"]').val();
    const paymentTerms = $('select[name="payment_terms"]').val();
    
    if (invoiceDate && paymentTerms) {
        const invoice = new Date(invoiceDate);
        let dueDate = new Date(invoice);
        
        switch (paymentTerms) {
            case 'net_30':
                dueDate.setDate(dueDate.getDate() + 30);
                break;
            case 'net_60':
                dueDate.setDate(dueDate.getDate() + 60);
                break;
            case 'monthly_end':
                dueDate.setMonth(dueDate.getMonth() + 2, 0);
                break;
            case 'monthly_25':
                dueDate.setMonth(dueDate.getMonth() + 1, 25);
                break;
            case 'immediate':
                break;
        }
        
        $('input[name="due_date"]').val(dueDate.toISOString().split('T')[0]);
    }
}

/**
 * 顧客情報更新
 */
function updateCustomerInfo() {
    const selectedOption = $('select[name="customer_id"] option:selected');
    const paymentTerms = selectedOption.data('payment-terms');
    
    if (paymentTerms) {
        $('select[name="payment_terms"]').val(paymentTerms);
        calculateDueDate();
    }
}

/**
 * 契約情報更新
 */
function updateContractInfo() {
    const contractId = $('select[name="contract_id"]').val();
    
    if (contractId) {
        $('select[name="invoice_type"]').val('contract_based');
        updateInvoiceType();
    }
}

/**
 * 請求種別更新
 */
function updateInvoiceType() {
    const invoiceType = $('select[name="invoice_type"]').val();
    
    if (invoiceType === 'recurring') {
        $('#recurring-settings').slideDown();
    } else {
        $('#recurring-settings').slideUp();
    }
}

/**
 * 日付変更チェック
 */
function checkDateChange() {
    const invoiceDate = $('input[name="invoice_date"]').val();
    const dueDate = $('input[name="due_date"]').val();
    
    if (invoiceDate !== originalData.invoice_date || dueDate !== originalData.due_date) {
        $('#date-info-changed').prop('checked', true);
        $('input[name="invoice_date"], input[name="due_date"]').addClass('change-highlight');
    }
}

/**
 * 金額変更チェック
 */
function checkAmountChange() {
    $('#amount-changed').prop('checked', true);
    $('#discount-amount, #additional-amount').addClass('change-highlight');
}

/**
 * 編集理由更新
 */
function updateEditReason() {
    $('#edit-reason-hidden').val($('#edit-reason').val());
    $('#edit-detail-hidden').val($('#edit-detail').val());
    $('#edit-notes-hidden').val($('#edit-notes').val());
}

/**
 * 変更影響表示
 */
function showChangeImpact() {
    const impacts = [];
    
    if ($('#basic-info-changed').prop('checked')) {
        impacts.push('基本情報の変更により、請求書の整合性に影響があります。');
    }
    
    if ($('#date-info-changed').prop('checked')) {
        impacts.push('請求日・支払期限の変更により、支払スケジュールに影響があります。');
    }
    
    if ($('#amount-changed').prop('checked')) {
        impacts.push('金額変更により、契約内容との整合性確認が必要です。');
    }
    
    if ($('#items-changed').prop('checked')) {
        impacts.push('請求項目の変更により、契約・シフト実績との照合が必要です。');
    }
    
    if (impacts.length > 0) {
        $('#impact-details').html('<ul><li>' + impacts.join('</li><li>') + '</li></ul>');
        $('#change-impact').show();
    }
}

/**
 * 変更履歴表示
 */
function showChangeHistory() {
    $.ajax({
        url: '{{ route("invoices.history", $invoice) }}',
        method: 'GET',
        success: function(data) {
            $('#change-history-content').html(data);
            $('#changeHistoryModal').modal('show');
        },
        error: function() {
            alert('変更履歴の取得に失敗しました。');
        }
    });
}

/**
 * 変更前後比較
 */
function compareVersions() {
    // 現在のフォームデータを収集
    const currentData = {
        invoice_number: $('input[name="invoice_number"]').val(),
        title: $('input[name="title"]').val(),
        total_amount: parseFloat($('#total-amount-hidden').val()) || 0
    };
    
    // 比較表示
    $('#original-content').html(`
        <h6>基本情報</h6>
        <p><strong>請求書番号:</strong> ${originalData.invoice_number}</p>
        <p><strong>件名:</strong> ${originalData.title}</p>
        <p><strong>合計金額:</strong> ¥${new Intl.NumberFormat('ja-JP').format(originalData.total_amount)}</p>
    `);
    
    $('#modified-content').html(`
        <h6>基本情報</h6>
        <p><strong>請求書番号:</strong> ${currentData.invoice_number}</p>
        <p><strong>件名:</strong> ${currentData.title}</p>
        <p><strong>合計金額:</strong> ¥${new Intl.NumberFormat('ja-JP').format(currentData.total_amount)}</p>
    `);
    
    $('#compareModal').modal('show');
}

/**
 * 変更プレビュー
 */
function previewChanges() {
    const formData = new FormData($('#invoice-edit-form')[0]);
    
    $.ajax({
        url: '{{ route("invoices.preview") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(html) {
            $('#invoice-preview').html(html);
            $('#previewModal').modal('show');
        },
        error: function() {
            alert('プレビューの生成に失敗しました。');
        }
    });
}

/**
 * 変更検証
 */
function validateChanges() {
    const reason = $('#edit-reason').val();
    const detail = $('#edit-detail').val();
    
    if (!reason || !detail) {
        alert('編集理由と編集詳細を入力してください。');
        return false;
    }
    
    // 追加の検証ロジック
    const currentTotal = parseFloat($('#total-amount-hidden').val()) || 0;
    if (Math.abs(currentTotal - originalData.total_amount) > originalData.total_amount * 0.5) {
        if (!confirm('金額が50%以上変更されています。この変更を続行しますか？')) {
            return false;
        }
    }
    
    alert('変更内容の検証が完了しました。');
    return true;
}

/**
 * 変更保存
 */
function saveChanges(action) {
    if (!validateChanges()) {
        return;
    }
    
    updateEditReason();
    
    // アクションをフォームに追加
    const actionInput = $('<input>').attr({
        type: 'hidden',
        name: 'action',
        value: action
    });
    
    $('#invoice-edit-form').append(actionInput);
}

/**
 * 添付ファイル削除
 */
function removeAttachment(index) {
    if (confirm('この添付ファイルを削除しますか？')) {
        $(`input[name="existing_attachments[${index}]"]`).remove();
        $(event.target).closest('.col-md-6').remove();
        $('#attachments-changed').prop('checked', true);
    }
}

/**
 * 契約項目読み込み
 */
function loadContractItems() {
    const contractId = $('select[name="contract_id"]').val();
    
    if (!contractId) {
        alert('契約を選択してください。');
        return;
    }
    
    if (!confirm('現在の項目を契約項目で置き換えますか？')) {
        return;
    }
    
    $.ajax({
        url: '{{ url("/contracts") }}/' + contractId + '/items',
        method: 'GET',
        success: function(items) {
            $('#invoice-items').html('');
            itemIndex = 0;
            
            items.forEach(function(item) {
                addInvoiceItem();
                const lastRow = $('#invoice-items tr:last-child');
                lastRow.find('.item-name').val(item.name);
                lastRow.find('.item-unit').val(item.unit);
                lastRow.find('.item-unit-price').val(item.unit_price);
                calculateItemAmount(lastRow.find('.item-quantity')[0]);
            });
            
            $('#items-changed').prop('checked', true);
        },
        error: function() {
            alert('契約項目の読み込みに失敗しました。');
        }
    });
}

/**
 * シフト実績読み込み
 */
function loadShiftData() {
    const contractId = $('select[name="contract_id"]').val();
    
    if (!contractId) {
        alert('契約を選択してください。');
        return;
    }
    
    const dateFrom = prompt('集計開始日を入力してください (YYYY-MM-DD):');
    const dateTo = prompt('集計終了日を入力してください (YYYY-MM-DD):');
    
    if (!dateFrom || !dateTo) return;
    
    $.ajax({
        url: '{{ url("/shifts/billing-data") }}',
        method: 'GET',
        data: {
            contract_id: contractId,
            date_from: dateFrom,
            date_to: dateTo
        },
        success: function(data) {
            $('#invoice-items').html('');
            itemIndex = 0;
            
            data.forEach(function(item) {
                addInvoiceItem();
                const lastRow = $('#invoice-items tr:last-child');
                lastRow.find('.item-name').val(item.name);
                lastRow.find('.item-unit').val('時間');
                lastRow.find('.item-quantity').val(item.hours);
                lastRow.find('.item-unit-price').val(item.rate);
                calculateItemAmount(lastRow.find('.item-quantity')[0]);
            });
            
            $('#items-changed').prop('checked', true);
        },
        error: function() {
            alert('シフト実績の読み込みに失敗しました。');
        }
    });
}

/**
 * プレビュー印刷
 */
function printPreview() {
    const printContent = document.getElementById('invoice-preview');
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <html>
            <head>
                <title>請求書</title>
                <style>
                    body { font-family: 'Hiragino Sans', 'Yu Gothic', sans-serif; }
                    .invoice-header { text-align: center; margin-bottom: 30px; }
                    .invoice-content { line-height: 1.8; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #333; padding: 8px; }
                </style>
            </head>
            <body>
                ${printContent.innerHTML}
            </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}
</script>
@endpush