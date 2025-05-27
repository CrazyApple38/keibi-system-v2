@extends('layouts.app')

@section('title', '新規請求書作成')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-0">新規請求書作成</h1>
                    <p class="text-muted mb-0">Create New Invoice</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> 一覧に戻る
                    </a>
                    <button type="button" class="btn btn-outline-info" onclick="loadTemplate()">
                        <i class="fas fa-file-import"></i> テンプレート読み込み
                    </button>
                    @if(request('contract_id'))
                        <a href="{{ route('contracts.show', request('contract_id')) }}" class="btn btn-outline-primary">
                            <i class="fas fa-file-contract"></i> 元契約確認
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 契約からの請求書作成情報 -->
    @if(request('contract_id') && isset($contract))
        <div class="alert alert-info mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle me-2"></i>
                <div>
                    <strong>契約からの請求書作成</strong><br>
                    契約番号: {{ $contract->contract_number }} | 
                    顧客: {{ $contract->customer->name }} | 
                    案件: {{ $contract->project->name }} | 
                    契約金額: ¥{{ number_format($contract->total_amount) }}
                </div>
            </div>
        </div>
    @endif

    <!-- 請求書作成フォーム -->
    <form id="invoice-form" method="POST" action="{{ route('invoices.store') }}" enctype="multipart/form-data">
        @csrf
        @if(request('contract_id'))
            <input type="hidden" name="contract_id" value="{{ request('contract_id') }}">
        @endif

        <!-- 基本情報 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle"></i> 基本情報
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">請求書番号</label>
                        <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" 
                               name="invoice_number" value="{{ old('invoice_number', $invoice_number ?? '') }}" 
                               placeholder="自動生成されます">
                        @error('invoice_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">空欄の場合は自動生成されます</small>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label required">顧客</label>
                        <select class="form-select @error('customer_id') is-invalid @enderror" name="customer_id" required
                                onchange="updateCustomerInfo()">
                            <option value="">顧客を選択</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" 
                                        {{ old('customer_id', $contract->customer_id ?? '') == $customer->id ? 'selected' : '' }}
                                        data-name="{{ $customer->name }}"
                                        data-address="{{ $customer->address }}"
                                        data-phone="{{ $customer->phone }}"
                                        data-email="{{ $customer->email }}"
                                        data-payment-terms="{{ $customer->payment_terms }}">
                                    {{ $customer->name }} ({{ $customer->company_type }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">関連契約</label>
                        <select class="form-select" name="contract_id" onchange="updateContractInfo()">
                            <option value="">契約を選択（任意）</option>
                            @if(isset($contract))
                                <option value="{{ $contract->id }}" selected>
                                    {{ $contract->contract_number }} - {{ $contract->title }}
                                </option>
                            @else
                                @foreach($contracts as $contractOption)
                                    <option value="{{ $contractOption->id }}" 
                                            {{ old('contract_id') == $contractOption->id ? 'selected' : '' }}>
                                        {{ $contractOption->contract_number }} - {{ $contractOption->title }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <small class="form-text text-muted">契約ベース請求書の場合に選択</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">請求件名</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               name="title" value="{{ old('title', $contract->title ?? '') }}" required
                               placeholder="請求の件名を入力">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">請求種別</label>
                        <select class="form-select @error('invoice_type') is-invalid @enderror" name="invoice_type"
                                onchange="updateInvoiceType()">
                            <option value="standard" {{ old('invoice_type') === 'standard' ? 'selected' : '' }}>通常請求</option>
                            <option value="contract_based" {{ old('invoice_type') === 'contract_based' ? 'selected' : '' }}>契約ベース請求</option>
                            <option value="recurring" {{ old('invoice_type') === 'recurring' ? 'selected' : '' }}>定期請求</option>
                            <option value="partial" {{ old('invoice_type') === 'partial' ? 'selected' : '' }}>分割請求</option>
                            <option value="final" {{ old('invoice_type') === 'final' ? 'selected' : '' }}>最終請求</option>
                        </select>
                        @error('invoice_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                    <div class="col-md-3">
                        <label class="form-label required">請求日</label>
                        <input type="date" class="form-control @error('invoice_date') is-invalid @enderror" 
                               name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required
                               onchange="calculateDueDate()">
                        @error('invoice_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">支払期限</label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                               name="due_date" value="{{ old('due_date') }}" required>
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">支払条件</label>
                        <select class="form-select" name="payment_terms" onchange="calculateDueDate()">
                            <option value="">支払条件を選択</option>
                            <option value="net_30" {{ old('payment_terms') === 'net_30' ? 'selected' : '' }}>30日以内</option>
                            <option value="net_60" {{ old('payment_terms') === 'net_60' ? 'selected' : '' }}>60日以内</option>
                            <option value="monthly_end" {{ old('payment_terms') === 'monthly_end' ? 'selected' : '' }}>月末締め翌月末払い</option>
                            <option value="monthly_25" {{ old('payment_terms') === 'monthly_25' ? 'selected' : '' }}>25日締め翌月25日払い</option>
                            <option value="immediate" {{ old('payment_terms') === 'immediate' ? 'selected' : '' }}>即時払い</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">支払方法</label>
                        <select class="form-select" name="payment_method">
                            <option value="">支払方法を選択</option>
                            <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>銀行振込</option>
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>現金</option>
                            <option value="check" {{ old('payment_method') === 'check' ? 'selected' : '' }}>小切手</option>
                            <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>クレジットカード</option>
                        </select>
                    </div>
                </div>

                <!-- 定期請求設定 -->
                <div class="row g-3 mt-3" id="recurring-settings" style="display: none;">
                    <div class="col-md-12">
                        <h6>定期請求設定</h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">請求間隔</label>
                        <select class="form-select" name="recurring_interval">
                            <option value="monthly">毎月</option>
                            <option value="quarterly">四半期</option>
                            <option value="semi_annual">半年</option>
                            <option value="annual">年次</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">次回請求日</label>
                        <input type="date" class="form-control" name="next_invoice_date">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">終了日</label>
                        <input type="date" class="form-control" name="recurring_end_date">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="auto_send" value="1" id="auto-send">
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
                        @if(isset($contract))
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
                    </div>
                </div>
            </div>
            <div class="card-body">
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
                            <!-- 初期項目 -->
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
                        </tbody>
                    </table>
                </div>

                <!-- 項目テンプレート -->
                <div class="mt-3">
                    <h6>よく使う項目</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addItemTemplate('警備業務', '時間', 2500)">
                            警備業務
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addItemTemplate('夜間警備', '時間', 3000)">
                            夜間警備
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addItemTemplate('交通誘導', '日', 20000)">
                            交通誘導
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addItemTemplate('イベント警備', '日', 25000)">
                            イベント警備
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addItemTemplate('交通費', '式', 5000)">
                            交通費
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 金額計算 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calculator"></i> 金額計算
                </h5>
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
                                           value="0" step="1" min="0" onchange="calculateTotals()" id="discount-amount">
                                    <span class="input-group-text">円</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">追加料金</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="additional_amount" 
                                           value="0" step="1" min="0" onchange="calculateTotals()" id="additional-amount">
                                    <span class="input-group-text">円</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">備考</label>
                                <input type="text" class="form-control" name="discount_note" 
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
                                    <div class="col-6 text-end" id="subtotal-amount">¥0</div>
                                    
                                    <div class="col-6">割引:</div>
                                    <div class="col-6 text-end text-danger" id="discount-display">¥0</div>
                                    
                                    <div class="col-6">追加料金:</div>
                                    <div class="col-6 text-end text-success" id="additional-display">¥0</div>
                                    
                                    <div class="col-6">課税対象額:</div>
                                    <div class="col-6 text-end" id="taxable-amount">¥0</div>
                                    
                                    <div class="col-6">消費税(10%):</div>
                                    <div class="col-6 text-end" id="tax-amount-display">¥0</div>
                                    
                                    <div class="col-12"><hr class="my-2"></div>
                                    
                                    <div class="col-6"><strong>合計金額:</strong></div>
                                    <div class="col-6 text-end"><strong class="h5 text-primary" id="total-amount-display">¥0</strong></div>
                                </div>
                                
                                <!-- 隠しフィールド -->
                                <input type="hidden" name="subtotal_amount" id="subtotal-hidden" value="0">
                                <input type="hidden" name="tax_amount" id="tax-amount-hidden" value="0">
                                <input type="hidden" name="total_amount" id="total-amount-hidden" value="0">
                            </div>
                        </div>
                    </div>
                </div>
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
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">請求内容詳細</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" rows="4" 
                                  placeholder="請求内容の詳細説明を記載してください">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">備考・特記事項</label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="請求に関する備考があれば記載">{{ old('notes') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">内部メモ</label>
                        <textarea class="form-control" name="internal_notes" rows="3" 
                                  placeholder="社内用メモ（請求書には表示されません）">{{ old('internal_notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- 添付ファイル -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-paperclip"></i> 添付ファイル
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">請求関連ファイル</label>
                        <input type="file" class="form-control" name="attachments[]" multiple 
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
                        <button type="button" class="btn btn-outline-secondary" onclick="previewInvoice()">
                            <i class="fas fa-eye"></i> プレビュー
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="saveAsDraft()">
                            <i class="fas fa-save"></i> 下書き保存
                        </button>
                    </div>
                    <div>
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> キャンセル
                        </a>
                        <button type="submit" class="btn btn-primary" name="action" value="create">
                            <i class="fas fa-plus"></i> 請求書作成
                        </button>
                        <button type="submit" class="btn btn-success" name="action" value="create_and_send">
                            <i class="fas fa-paper-plane"></i> 作成・送付
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- テンプレート選択モーダル -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">請求書テンプレート選択</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card template-card" data-template="security_monthly">
                            <div class="card-body text-center">
                                <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                                <h5>月次警備請求</h5>
                                <p class="text-muted">月間警備業務の定期請求テンプレート</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card template-card" data-template="event_security">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                                <h5>イベント警備請求</h5>
                                <p class="text-muted">単発イベント警備の請求テンプレート</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card template-card" data-template="traffic_control">
                            <div class="card-body text-center">
                                <i class="fas fa-traffic-light fa-3x text-warning mb-3"></i>
                                <h5>交通誘導請求</h5>
                                <p class="text-muted">交通誘導業務の請求テンプレート</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card template-card" data-template="facility_management">
                            <div class="card-body text-center">
                                <i class="fas fa-building fa-3x text-info mb-3"></i>
                                <h5>施設管理請求</h5>
                                <p class="text-muted">施設管理・巡回警備の請求テンプレート</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="applyTemplate()">適用</button>
            </div>
        </div>
    </div>
</div>

<!-- プレビューモーダル -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">請求書プレビュー</h5>
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

.template-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.template-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.template-card.selected {
    border-color: #0d6efd;
    background-color: #f8f9ff;
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
let selectedTemplate = null;
let itemIndex = 0;

$(document).ready(function() {
    // 初期設定
    calculateTotals();
    updateInvoiceType();
    
    // 顧客選択時の自動入力
    updateCustomerInfo();
    
    // 日付変更時の自動計算
    calculateDueDate();
    
    // テンプレートカード選択
    $('.template-card').click(function() {
        $('.template-card').removeClass('selected');
        $(this).addClass('selected');
        selectedTemplate = $(this).data('template');
    });
});

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
    } else {
        alert('最低1つの項目は必要です。');
    }
}

/**
 * 項目テンプレート追加
 */
function addItemTemplate(name, unit, unitPrice) {
    itemIndex++;
    const newRow = `
        <tr class="invoice-item">
            <td>
                <input type="text" class="form-control item-name" name="items[${itemIndex}][name]" 
                       value="${name}" required>
            </td>
            <td>
                <input type="number" class="form-control item-quantity" name="items[${itemIndex}][quantity]" 
                       value="1" step="0.1" min="0" onchange="calculateItemAmount(this)" required>
            </td>
            <td>
                <select class="form-select item-unit" name="items[${itemIndex}][unit]">
                    <option value="時間" ${unit === '時間' ? 'selected' : ''}>時間</option>
                    <option value="日" ${unit === '日' ? 'selected' : ''}>日</option>
                    <option value="月" ${unit === '月' ? 'selected' : ''}>月</option>
                    <option value="人" ${unit === '人' ? 'selected' : ''}>人</option>
                    <option value="件" ${unit === '件' ? 'selected' : ''}>件</option>
                    <option value="式" ${unit === '式' ? 'selected' : ''}>式</option>
                </select>
            </td>
            <td>
                <input type="number" class="form-control item-unit-price" name="items[${itemIndex}][unit_price]" 
                       value="${unitPrice}" step="1" min="0" onchange="calculateItemAmount(this)" required>
            </td>
            <td>
                <input type="number" class="form-control item-amount" name="items[${itemIndex}][amount]" 
                       value="${unitPrice}" step="1" readonly>
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
    calculateTotals();
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
                // 翌月末
                dueDate.setMonth(dueDate.getMonth() + 2, 0);
                break;
            case 'monthly_25':
                // 翌月25日
                dueDate.setMonth(dueDate.getMonth() + 1, 25);
                break;
            case 'immediate':
                // 即時（同日）
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
        // 契約ベース請求に自動切り替え
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
 * 契約項目読み込み
 */
function loadContractItems() {
    const contractId = $('select[name="contract_id"]').val();
    
    if (!contractId) {
        alert('契約を選択してください。');
        return;
    }
    
    $.ajax({
        url: '{{ url("/contracts") }}/' + contractId + '/items',
        method: 'GET',
        success: function(items) {
            // 既存項目をクリア
            $('#invoice-items').html('');
            itemIndex = 0;
            
            // 契約項目を追加
            items.forEach(function(item) {
                addItemTemplate(item.name, item.unit, item.unit_price);
            });
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
    
    // 期間選択ダイアログ（簡略版）
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
            // 既存項目をクリア
            $('#invoice-items').html('');
            itemIndex = 0;
            
            // シフト実績から項目を生成
            data.forEach(function(item) {
                addItemTemplate(item.name, '時間', item.rate);
                // 数量を実績時間に設定
                $('#invoice-items tr:last-child .item-quantity').val(item.hours);
                calculateItemAmount($('#invoice-items tr:last-child .item-quantity')[0]);
            });
        },
        error: function() {
            alert('シフト実績の読み込みに失敗しました。');
        }
    });
}

/**
 * テンプレート読み込み
 */
function loadTemplate() {
    $('#templateModal').modal('show');
}

/**
 * テンプレート適用
 */
function applyTemplate() {
    if (!selectedTemplate) {
        alert('テンプレートを選択してください。');
        return;
    }
    
    $.ajax({
        url: '{{ route("invoices.template") }}',
        method: 'GET',
        data: { template: selectedTemplate },
        success: function(data) {
            // フォームにテンプレートデータを適用
            if (data.title) $('input[name="title"]').val(data.title);
            if (data.payment_terms) $('select[name="payment_terms"]').val(data.payment_terms);
            if (data.description) $('textarea[name="description"]').val(data.description);
            
            // 項目をクリアして追加
            if (data.items) {
                $('#invoice-items').html('');
                itemIndex = 0;
                
                data.items.forEach(function(item) {
                    addItemTemplate(item.name, item.unit, item.unit_price);
                });
            }
            
            $('#templateModal').modal('hide');
            alert('テンプレートを適用しました。');
        },
        error: function() {
            alert('テンプレートの読み込みに失敗しました。');
        }
    });
}

/**
 * プレビュー表示
 */
function previewInvoice() {
    const formData = new FormData($('#invoice-form')[0]);
    
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
 * 下書き保存
 */
function saveAsDraft() {
    const form = $('#invoice-form');
    
    // 下書きフラグを追加
    $('<input>').attr({
        type: 'hidden',
        name: 'save_as_draft',
        value: '1'
    }).appendTo(form);
    
    form.submit();
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