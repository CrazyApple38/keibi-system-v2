@extends('layouts.app')

@section('title', '見積作成')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-plus-circle me-2"></i>見積作成
                    </h1>
                    <p class="mb-0 text-muted">新しい見積書を作成します</p>
                </div>
                <div>
                    <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>一覧に戻る
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form id="quotationForm" action="{{ route('quotations.store') }}" method="POST">
        @csrf
        
        <div class="row">
            <!-- 左カラム：基本情報 -->
            <div class="col-lg-8">
                <!-- 基本情報 -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>基本情報
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_id" class="form-label required">顧客 <span class="text-danger">*</span></label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" 
                                        id="customer_id" name="customer_id" required>
                                    <option value="">顧客を選択してください</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" data-customer="{{ json_encode($customer) }}"
                                                {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="project_id" class="form-label">関連案件</label>
                                <select class="form-select @error('project_id') is-invalid @enderror" 
                                        id="project_id" name="project_id">
                                    <option value="">新規案件</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label required">件名 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                       id="subject" name="subject" value="{{ old('subject') }}" 
                                       placeholder="見積件名を入力してください" required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="project_name" class="form-label">案件名</label>
                                <input type="text" class="form-control @error('project_name') is-invalid @enderror" 
                                       id="project_name" name="project_name" value="{{ old('project_name') }}" 
                                       placeholder="案件名を入力してください">
                                @error('project_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="valid_until" class="form-label required">有効期限 <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('valid_until') is-invalid @enderror" 
                                       id="valid_until" name="valid_until" value="{{ old('valid_until', date('Y-m-d', strtotime('+30 days'))) }}" required>
                                @error('valid_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="delivery_location" class="form-label">警備場所</label>
                                <input type="text" class="form-control @error('delivery_location') is-invalid @enderror" 
                                       id="delivery_location" name="delivery_location" value="{{ old('delivery_location') }}" 
                                       placeholder="警備場所を入力してください">
                                @error('delivery_location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 見積項目 -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list me-2"></i>見積項目
                        </h6>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem('labor')">
                                <i class="fas fa-plus me-1"></i>人件費
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="addItem('transport')">
                                <i class="fas fa-plus me-1"></i>交通費
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addItem('equipment')">
                                <i class="fas fa-plus me-1"></i>装備費
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="addItem('other')">
                                <i class="fas fa-plus me-1"></i>その他
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th width="200">項目名</th>
                                        <th width="80">数量</th>
                                        <th width="80">単位</th>
                                        <th width="100">単価</th>
                                        <th width="100">金額</th>
                                        <th width="200">備考</th>
                                        <th width="60">操作</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- 動的に項目が追加される -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">小計</td>
                                        <td class="text-end fw-bold" id="subtotalAmount">¥0</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end">
                                            消費税 (<span id="taxRateDisplay">10</span>%)
                                        </td>
                                        <td class="text-end" id="taxAmount">¥0</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td colspan="4" class="text-end fw-bold">合計金額</td>
                                        <td class="text-end fw-bold fs-5" id="totalAmount">¥0</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="alert alert-info mt-3" style="display: none;" id="noItemsAlert">
                            <i class="fas fa-info-circle me-2"></i>
                            上記のボタンから見積項目を追加してください。
                        </div>
                    </div>
                </div>

                <!-- 条件・備考 -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-clipboard-list me-2"></i>条件・備考
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="terms_conditions" class="form-label">取引条件</label>
                                <textarea class="form-control @error('terms_conditions') is-invalid @enderror" 
                                          id="terms_conditions" name="terms_conditions" rows="5"
                                          placeholder="支払条件、警備条件等を入力してください">{{ old('terms_conditions', "■支払条件：見積承認後、月末締め翌月末支払い\n■警備期間：契約日より〇〇まで\n■警備時間：24時間体制\n■警備人数：〇名体制\n■その他：制服・装備品は弊社にて準備いたします") }}</textarea>
                                @error('terms_conditions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="notes" class="form-label">備考</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="5"
                                          placeholder="その他特記事項があれば入力してください">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 右カラム：サマリー・操作 -->
            <div class="col-lg-4">
                <!-- 見積サマリー -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-calculator me-2"></i>見積サマリー
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="tax_rate" class="form-label">消費税率 (%)</label>
                            <input type="number" class="form-control" id="tax_rate" name="tax_rate" 
                                   value="{{ old('tax_rate', 10) }}" min="0" max="100" step="0.1">
                        </div>
                        
                        <hr>
                        
                        <div class="row mb-2">
                            <div class="col-6">小計:</div>
                            <div class="col-6 text-end fw-bold" id="summarySubtotal">¥0</div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-6">消費税:</div>
                            <div class="col-6 text-end" id="summaryTax">¥0</div>
                        </div>
                        
                        <hr>
                        
                        <div class="row mb-3">
                            <div class="col-6 fw-bold fs-5">合計:</div>
                            <div class="col-6 text-end fw-bold fs-5 text-primary" id="summaryTotal">¥0</div>
                        </div>
                        
                        <!-- 隠しフィールド -->
                        <input type="hidden" id="subtotal_amount" name="subtotal_amount" value="0">
                        <input type="hidden" id="tax_amount" name="tax_amount" value="0">
                        <input type="hidden" id="total_amount" name="total_amount" value="0">
                    </div>
                </div>

                <!-- 顧客情報 -->
                <div class="card shadow mb-4" id="customerInfo" style="display: none;">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-building me-2"></i>顧客情報
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="customerDetails">
                            <!-- 動的に顧客情報が表示される -->
                        </div>
                    </div>
                </div>

                <!-- テンプレート -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-file-medical me-2"></i>テンプレート
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="template_select" class="form-label">既存テンプレート</label>
                            <select class="form-select" id="template_select">
                                <option value="">テンプレートを選択</option>
                                <option value="standard_guard">標準警備</option>
                                <option value="event_guard">イベント警備</option>
                                <option value="facility_guard">施設警備</option>
                                <option value="traffic_guard">交通誘導警備</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-info" onclick="loadTemplate()">
                                <i class="fas fa-download me-1"></i>テンプレート読込
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="saveAsTemplate()">
                                <i class="fas fa-save me-1"></i>テンプレート保存
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 操作ボタン -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-cogs me-2"></i>操作
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-info" onclick="previewQuotation()">
                                <i class="fas fa-eye me-1"></i>プレビュー
                            </button>
                            
                            <button type="button" class="btn btn-secondary" onclick="saveDraft()">
                                <i class="fas fa-save me-1"></i>下書き保存
                            </button>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>見積作成
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- 項目追加モーダル -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">見積項目追加</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addItemForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="modal_item_name" class="form-label required">項目名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_item_name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="modal_item_type" class="form-label">項目種別</label>
                            <select class="form-select" id="modal_item_type">
                                <option value="labor">人件費</option>
                                <option value="transport">交通費</option>
                                <option value="equipment">装備費</option>
                                <option value="other">その他</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="modal_quantity" class="form-label required">数量 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="modal_quantity" min="1" value="1" required>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="modal_unit" class="form-label">単位</label>
                            <input type="text" class="form-control" id="modal_unit" value="式">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="modal_unit_price" class="form-label required">単価 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="modal_unit_price" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_description" class="form-label">備考</label>
                        <textarea class="form-control" id="modal_description" rows="3" 
                                  placeholder="詳細な説明があれば入力してください"></textarea>
                    </div>
                    
                    <!-- 人件費の場合の詳細設定 -->
                    <div id="laborDetails" style="display: none;">
                        <hr>
                        <h6 class="text-primary">人件費詳細設定</h6>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="modal_hourly_rate" class="form-label">時給</label>
                                <input type="number" class="form-control" id="modal_hourly_rate" min="0">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="modal_hours_per_day" class="form-label">1日の時間</label>
                                <input type="number" class="form-control" id="modal_hours_per_day" min="0" step="0.5">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="modal_days" class="form-label">日数</label>
                                <input type="number" class="form-control" id="modal_days" min="0">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="modal_guard_count" class="form-label">警備員数</label>
                                <input type="number" class="form-control" id="modal_guard_count" min="1" value="1">
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="modal_night_allowance">
                            <label class="form-check-label" for="modal_night_allowance">
                                夜間手当を含む
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="modal_holiday_allowance">
                            <label class="form-check-label" for="modal_holiday_allowance">
                                休日手当を含む
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="confirmAddItem()">追加</button>
            </div>
        </div>
    </div>
</div>

<!-- プレビューモーダル -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">見積書プレビュー</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <!-- プレビューコンテンツ -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-info" onclick="printPreview()">
                    <i class="fas fa-print me-1"></i>印刷
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let itemCounter = 0;

$(document).ready(function() {
    // 初期化
    updateCalculations();
    checkNoItems();
    
    // 顧客選択時の処理
    $('#customer_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            const customerData = selectedOption.data('customer');
            displayCustomerInfo(customerData);
        } else {
            $('#customerInfo').hide();
        }
    });

    // 税率変更時の計算更新
    $('#tax_rate').on('input', function() {
        updateCalculations();
    });
    
    // 項目種別変更時の詳細設定表示切り替え
    $('#modal_item_type').change(function() {
        if ($(this).val() === 'labor') {
            $('#laborDetails').show();
        } else {
            $('#laborDetails').hide();
        }
    });
    
    // 人件費詳細設定の自動計算
    $('#modal_hourly_rate, #modal_hours_per_day, #modal_days, #modal_guard_count').on('input', function() {
        calculateLaborCost();
    });
    
    // フォーム送信時のバリデーション
    $('#quotationForm').submit(function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        
        // 項目データをJSONとして隠しフィールドに設定
        const items = collectItemsData();
        $('<input>').attr({
            type: 'hidden',
            name: 'items',
            value: JSON.stringify(items)
        }).appendTo('#quotationForm');
    });
    
    // デフォルト項目追加（必要に応じて）
    if ($('#itemsTableBody').children().length === 0) {
        showNoItemsAlert();
    }
});

// 項目追加
function addItem(type) {
    $('#modal_item_type').val(type);
    
    // 項目種別に応じたデフォルト値設定
    const defaults = {
        labor: { name: '警備員人件費', unit: '式' },
        transport: { name: '交通費', unit: '式' },
        equipment: { name: '装備費', unit: '式' },
        other: { name: 'その他費用', unit: '式' }
    };
    
    if (defaults[type]) {
        $('#modal_item_name').val(defaults[type].name);
        $('#modal_unit').val(defaults[type].unit);
    }
    
    // 詳細設定の表示切り替え
    if (type === 'labor') {
        $('#laborDetails').show();
    } else {
        $('#laborDetails').hide();
    }
    
    // モーダル表示
    $('#addItemModal').modal('show');
}

// 項目追加確定
function confirmAddItem() {
    const itemName = $('#modal_item_name').val();
    const itemType = $('#modal_item_type').val();
    const quantity = parseFloat($('#modal_quantity').val()) || 0;
    const unit = $('#modal_unit').val();
    const unitPrice = parseFloat($('#modal_unit_price').val()) || 0;
    const description = $('#modal_description').val();
    
    if (!itemName || quantity <= 0 || unitPrice < 0) {
        alert('必須項目を正しく入力してください。');
        return;
    }
    
    const amount = quantity * unitPrice;
    
    const row = `
        <tr data-item-id="${itemCounter}">
            <td>
                <input type="hidden" name="items[${itemCounter}][type]" value="${itemType}">
                <input type="hidden" name="items[${itemCounter}][name]" value="${itemName}">
                ${itemName}
                <small class="text-muted d-block">${getItemTypeLabel(itemType)}</small>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm quantity-input" 
                       name="items[${itemCounter}][quantity]" value="${quantity}" min="1" onchange="updateRowAmount(this)">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" 
                       name="items[${itemCounter}][unit]" value="${unit}">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm unit-price-input" 
                       name="items[${itemCounter}][unit_price]" value="${unitPrice}" min="0" onchange="updateRowAmount(this)">
            </td>
            <td class="text-end amount-cell">¥${amount.toLocaleString()}</td>
            <td>
                <input type="text" class="form-control form-control-sm" 
                       name="items[${itemCounter}][description]" value="${description}">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${itemCounter})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#itemsTableBody').append(row);
    itemCounter++;
    
    updateCalculations();
    checkNoItems();
    
    // モーダルクリア・非表示
    $('#addItemForm')[0].reset();
    $('#laborDetails').hide();
    $('#addItemModal').modal('hide');
}

// 項目削除
function removeItem(itemId) {
    if (confirm('この項目を削除してもよろしいですか？')) {
        $(`tr[data-item-id="${itemId}"]`).remove();
        updateCalculations();
        checkNoItems();
    }
}

// 行の金額更新
function updateRowAmount(input) {
    const row = $(input).closest('tr');
    const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
    const unitPrice = parseFloat(row.find('.unit-price-input').val()) || 0;
    const amount = quantity * unitPrice;
    
    row.find('.amount-cell').text(`¥${amount.toLocaleString()}`);
    updateCalculations();
}

// 計算更新
function updateCalculations() {
    let subtotal = 0;
    
    $('#itemsTableBody tr').each(function() {
        const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
        const unitPrice = parseFloat($(this).find('.unit-price-input').val()) || 0;
        subtotal += quantity * unitPrice;
    });
    
    const taxRate = parseFloat($('#tax_rate').val()) || 0;
    const taxAmount = Math.floor(subtotal * taxRate / 100);
    const totalAmount = subtotal + taxAmount;
    
    // 表示更新
    $('#subtotalAmount, #summarySubtotal').text(`¥${subtotal.toLocaleString()}`);
    $('#taxAmount, #summaryTax').text(`¥${taxAmount.toLocaleString()}`);
    $('#totalAmount, #summaryTotal').text(`¥${totalAmount.toLocaleString()}`);
    $('#taxRateDisplay').text(taxRate);
    
    // 隠しフィールド更新
    $('#subtotal_amount').val(subtotal);
    $('#tax_amount').val(taxAmount);
    $('#total_amount').val(totalAmount);
}

// 項目なしアラートの表示制御
function checkNoItems() {
    const hasItems = $('#itemsTableBody tr').length > 0;
    $('#noItemsAlert').toggle(!hasItems);
}

function showNoItemsAlert() {
    $('#noItemsAlert').show();
}

// 顧客情報表示
function displayCustomerInfo(customer) {
    const html = `
        <div class="mb-2">
            <strong>${customer.name}</strong>
        </div>
        <div class="small text-muted mb-1">
            <i class="fas fa-map-marker-alt me-1"></i>
            ${customer.address || '住所未登録'}
        </div>
        <div class="small text-muted mb-1">
            <i class="fas fa-phone me-1"></i>
            ${customer.phone || '電話番号未登録'}
        </div>
        <div class="small text-muted">
            <i class="fas fa-envelope me-1"></i>
            ${customer.email || 'メール未登録'}
        </div>
    `;
    
    $('#customerDetails').html(html);
    $('#customerInfo').show();
}

// 項目種別ラベル取得
function getItemTypeLabel(type) {
    const labels = {
        labor: '人件費',
        transport: '交通費',
        equipment: '装備費',
        other: 'その他'
    };
    return labels[type] || type;
}

// 人件費自動計算
function calculateLaborCost() {
    const hourlyRate = parseFloat($('#modal_hourly_rate').val()) || 0;
    const hoursPerDay = parseFloat($('#modal_hours_per_day').val()) || 0;
    const days = parseFloat($('#modal_days').val()) || 0;
    const guardCount = parseFloat($('#modal_guard_count').val()) || 1;
    
    if (hourlyRate > 0 && hoursPerDay > 0 && days > 0) {
        const totalCost = hourlyRate * hoursPerDay * days * guardCount;
        $('#modal_unit_price').val(totalCost);
    }
}

// 項目データ収集
function collectItemsData() {
    const items = [];
    $('#itemsTableBody tr').each(function() {
        const row = $(this);
        const itemData = {
            name: row.find('input[name$="[name]"]').val(),
            type: row.find('input[name$="[type]"]').val(),
            quantity: parseFloat(row.find('input[name$="[quantity]"]').val()) || 0,
            unit: row.find('input[name$="[unit]"]').val(),
            unit_price: parseFloat(row.find('input[name$="[unit_price]"]').val()) || 0,
            description: row.find('input[name$="[description]"]').val()
        };
        items.push(itemData);
    });
    return items;
}

// フォームバリデーション
function validateForm() {
    const customerId = $('#customer_id').val();
    const subject = $('#subject').val();
    const validUntil = $('#valid_until').val();
    const hasItems = $('#itemsTableBody tr').length > 0;
    
    if (!customerId) {
        alert('顧客を選択してください。');
        return false;
    }
    
    if (!subject.trim()) {
        alert('件名を入力してください。');
        return false;
    }
    
    if (!validUntil) {
        alert('有効期限を設定してください。');
        return false;
    }
    
    if (!hasItems) {
        alert('見積項目を少なくとも1つ追加してください。');
        return false;
    }
    
    return true;
}

// プレビュー表示
function previewQuotation() {
    if (!validateForm()) {
        return;
    }
    
    const formData = {
        customer_id: $('#customer_id').val(),
        subject: $('#subject').val(),
        project_name: $('#project_name').val(),
        valid_until: $('#valid_until').val(),
        delivery_location: $('#delivery_location').val(),
        terms_conditions: $('#terms_conditions').val(),
        notes: $('#notes').val(),
        subtotal_amount: $('#subtotal_amount').val(),
        tax_amount: $('#tax_amount').val(),
        total_amount: $('#total_amount').val(),
        tax_rate: $('#tax_rate').val(),
        items: collectItemsData()
    };
    
    // プレビューコンテンツの生成
    generatePreviewContent(formData);
    $('#previewModal').modal('show');
}

// プレビューコンテンツ生成
function generatePreviewContent(data) {
    const customer = $('#customer_id option:selected').data('customer');
    const now = new Date();
    
    let itemsHtml = '';
    data.items.forEach((item, index) => {
        const amount = item.quantity * item.unit_price;
        itemsHtml += `
            <tr>
                <td>${index + 1}</td>
                <td>${item.name}</td>
                <td class="text-center">${item.quantity}</td>
                <td class="text-center">${item.unit}</td>
                <td class="text-end">¥${parseInt(item.unit_price).toLocaleString()}</td>
                <td class="text-end">¥${amount.toLocaleString()}</td>
            </tr>
        `;
    });
    
    const html = `
        <div class="quotation-preview">
            <div class="text-center mb-4">
                <h2>見積書</h2>
                <p class="text-muted">Quotation</p>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>宛先</h5>
                    <div class="border p-3">
                        <strong>${customer ? customer.name : ''}</strong><br>
                        ${customer ? customer.address || '' : ''}<br>
                        TEL: ${customer ? customer.phone || '' : ''}<br>
                        Email: ${customer ? customer.email || '' : ''}
                    </div>
                </div>
                <div class="col-md-6">
                    <h5>発行者</h5>
                    <div class="border p-3">
                        <strong>警備システム株式会社</strong><br>
                        〒000-0000 東京都○○区○○○○<br>
                        TEL: 03-0000-0000<br>
                        Email: info@keibi-system.co.jp
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th>件名:</th><td>${data.subject}</td></tr>
                        <tr><th>案件名:</th><td>${data.project_name || '-'}</td></tr>
                        <tr><th>警備場所:</th><td>${data.delivery_location || '-'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th>見積日:</th><td>${now.toLocaleDateString('ja-JP')}</td></tr>
                        <tr><th>有効期限:</th><td>${data.valid_until}</td></tr>
                        <tr><th>見積金額:</th><td class="fw-bold text-primary">¥${parseInt(data.total_amount).toLocaleString()}</td></tr>
                    </table>
                </div>
            </div>
            
            <h5>見積明細</h5>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th width="40">No.</th>
                        <th>項目名</th>
                        <th width="80">数量</th>
                        <th width="60">単位</th>
                        <th width="100">単価</th>
                        <th width="120">金額</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">小計</td>
                        <td class="text-end fw-bold">¥${parseInt(data.subtotal_amount).toLocaleString()}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end">消費税 (${data.tax_rate}%)</td>
                        <td class="text-end">¥${parseInt(data.tax_amount).toLocaleString()}</td>
                    </tr>
                    <tr class="table-primary">
                        <td colspan="5" class="text-end fw-bold">合計</td>
                        <td class="text-end fw-bold">¥${parseInt(data.total_amount).toLocaleString()}</td>
                    </tr>
                </tfoot>
            </table>
            
            ${data.terms_conditions ? `
                <div class="mt-4">
                    <h6>取引条件</h6>
                    <div class="border p-3">
                        ${data.terms_conditions.replace(/\n/g, '<br>')}
                    </div>
                </div>
            ` : ''}
            
            ${data.notes ? `
                <div class="mt-3">
                    <h6>備考</h6>
                    <div class="border p-3">
                        ${data.notes.replace(/\n/g, '<br>')}
                    </div>
                </div>
            ` : ''}
        </div>
    `;
    
    $('#previewContent').html(html);
}

// 印刷
function printPreview() {
    const printContent = $('#previewContent').html();
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>見積書</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                @media print {
                    body { print-color-adjust: exact; }
                    .table { page-break-inside: avoid; }
                }
            </style>
        </head>
        <body onload="window.print()">
            <div class="container">
                ${printContent}
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
}

// 下書き保存
function saveDraft() {
    const formData = new FormData($('#quotationForm')[0]);
    formData.append('status', 'draft');
    formData.append('items', JSON.stringify(collectItemsData()));
    
    $.ajax({
        url: '{{ route("quotations.store") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                alert('下書きを保存しました。');
                window.location.href = response.redirect || '{{ route("quotations.index") }}';
            } else {
                alert(response.message || '保存に失敗しました。');
            }
        },
        error: function(xhr) {
            alert('エラーが発生しました。');
        }
    });
}

// テンプレート読み込み
function loadTemplate() {
    const templateType = $('#template_select').val();
    if (!templateType) {
        alert('テンプレートを選択してください。');
        return;
    }
    
    // テンプレートデータ（実際の実装では Ajax で取得）
    const templates = {
        standard_guard: {
            subject: '標準警備サービス見積書',
            items: [
                { name: '警備員人件費', type: 'labor', quantity: 1, unit: '式', unit_price: 250000 },
                { name: '交通費', type: 'transport', quantity: 30, unit: '日', unit_price: 1000 },
                { name: '制服・装備費', type: 'equipment', quantity: 2, unit: '名分', unit_price: 15000 }
            ]
        },
        event_guard: {
            subject: 'イベント警備サービス見積書',
            items: [
                { name: 'イベント警備員人件費', type: 'labor', quantity: 1, unit: '式', unit_price: 180000 },
                { name: '交通費', type: 'transport', quantity: 10, unit: '日', unit_price: 800 },
                { name: '無線機レンタル', type: 'equipment', quantity: 5, unit: '台', unit_price: 3000 }
            ]
        }
        // 他のテンプレートも同様に定義
    };
    
    const template = templates[templateType];
    if (template) {
        $('#subject').val(template.subject);
        
        // 既存項目をクリア
        $('#itemsTableBody').empty();
        itemCounter = 0;
        
        // テンプレート項目を追加
        template.items.forEach(item => {
            addTemplateItem(item);
        });
        
        updateCalculations();
        checkNoItems();
        
        alert('テンプレートを読み込みました。');
    }
}

// テンプレート項目追加
function addTemplateItem(item) {
    const amount = item.quantity * item.unit_price;
    
    const row = `
        <tr data-item-id="${itemCounter}">
            <td>
                <input type="hidden" name="items[${itemCounter}][type]" value="${item.type}">
                <input type="hidden" name="items[${itemCounter}][name]" value="${item.name}">
                ${item.name}
                <small class="text-muted d-block">${getItemTypeLabel(item.type)}</small>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm quantity-input" 
                       name="items[${itemCounter}][quantity]" value="${item.quantity}" min="1" onchange="updateRowAmount(this)">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" 
                       name="items[${itemCounter}][unit]" value="${item.unit}">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm unit-price-input" 
                       name="items[${itemCounter}][unit_price]" value="${item.unit_price}" min="0" onchange="updateRowAmount(this)">
            </td>
            <td class="text-end amount-cell">¥${amount.toLocaleString()}</td>
            <td>
                <input type="text" class="form-control form-control-sm" 
                       name="items[${itemCounter}][description]" value="">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${itemCounter})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#itemsTableBody').append(row);
    itemCounter++;
}

// テンプレート保存
function saveAsTemplate() {
    const templateName = prompt('テンプレート名を入力してください:');
    if (!templateName) return;
    
    const templateData = {
        name: templateName,
        subject: $('#subject').val(),
        items: collectItemsData()
    };
    
    // 実際の実装では Ajax でサーバーに保存
    alert('テンプレートを保存しました。（実装予定）');
}
</script>
@endpush

@push('styles')
<style>
.required {
    position: relative;
}

.table th {
    background-color: #f8f9fc;
    border-color: #e3e6f0;
    font-weight: 600;
    white-space: nowrap;
}

.table td {
    vertical-align: middle;
}

.form-control-sm {
    font-size: 0.875rem;
}

#itemsTable .form-control {
    border: 1px solid #d1d3e2;
}

#itemsTable .form-control:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

.amount-cell {
    font-weight: 600;
    color: #5a5c69;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.quotation-preview {
    font-size: 0.9rem;
}

.quotation-preview h2 {
    color: #5a5c69;
    font-weight: 700;
}

.quotation-preview .table {
    margin-bottom: 0.5rem;
}

.quotation-preview .table th {
    background-color: #f8f9fc;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 0.5rem;
}

.quotation-preview .table td {
    padding: 0.5rem;
    font-size: 0.85rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .modal-lg {
        max-width: 95%;
    }
}

@media print {
    .btn, .modal-header, .modal-footer {
        display: none !important;
    }
    
    .quotation-preview {
        font-size: 0.8rem;
    }
    
    .table {
        font-size: 0.75rem;
    }
}
</style>
@endpush
