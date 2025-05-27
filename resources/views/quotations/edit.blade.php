@extends('layouts.app')

@section('title', '見積編集')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-edit me-2"></i>見積編集
                    </h1>
                    <p class="mb-0 text-muted">見積書 #{{ $quotation->quotation_number }} の編集</p>
                </div>
                <div>
                    <a href="{{ route('quotations.show', $quotation) }}" class="btn btn-outline-info me-2">
                        <i class="fas fa-eye me-1"></i>詳細表示
                    </a>
                    <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>一覧に戻る
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ステータス警告 -->
    @if($quotation->status !== 'draft')
        <div class="row mb-3">
            <div class="col-12">
                @php
                    $statusLabels = [
                        'pending' => '承認待ち',
                        'approved' => '承認済み',
                        'sent' => '送付済み',
                        'accepted' => '受注',
                        'rejected' => '失注',
                        'expired' => '期限切れ'
                    ];
                    $alertClasses = [
                        'pending' => 'warning',
                        'approved' => 'info',
                        'sent' => 'primary',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'expired' => 'dark'
                    ];
                @endphp
                <div class="alert alert-{{ $alertClasses[$quotation->status] ?? 'warning' }}" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>注意:</strong> この見積は現在「{{ $statusLabels[$quotation->status] ?? $quotation->status }}」状態です。
                    編集すると見積履歴に記録され、関係者に通知される場合があります。
                </div>
            </div>
        </div>
    @endif

    <form id="quotationForm" action="{{ route('quotations.update', $quotation) }}" method="POST">
        @csrf
        @method('PUT')
        
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
                                                {{ old('customer_id', $quotation->customer_id) == $customer->id ? 'selected' : '' }}>
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
                                        <option value="{{ $project->id }}" 
                                                {{ old('project_id', $quotation->project_id) == $project->id ? 'selected' : '' }}>
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
                                <label for="quotation_number" class="form-label">見積番号</label>
                                <input type="text" class="form-control" id="quotation_number" 
                                       value="{{ $quotation->quotation_number }}" readonly>
                                <small class="form-text text-muted">見積番号は変更できません</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">ステータス</label>
                                <select class="form-select" id="status" name="status" 
                                        {{ in_array($quotation->status, ['accepted', 'rejected']) ? 'disabled' : '' }}>
                                    <option value="draft" {{ $quotation->status === 'draft' ? 'selected' : '' }}>下書き</option>
                                    <option value="pending" {{ $quotation->status === 'pending' ? 'selected' : '' }}>承認待ち</option>
                                    <option value="approved" {{ $quotation->status === 'approved' ? 'selected' : '' }}>承認済み</option>
                                    <option value="sent" {{ $quotation->status === 'sent' ? 'selected' : '' }}>送付済み</option>
                                    @if(in_array($quotation->status, ['accepted', 'rejected', 'expired']))
                                        <option value="{{ $quotation->status }}" selected>
                                            {{ $statusLabels[$quotation->status] ?? $quotation->status }}
                                        </option>
                                    @endif
                                </select>
                                @if(in_array($quotation->status, ['accepted', 'rejected']))
                                    <small class="form-text text-muted">このステータスは変更できません</small>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label required">件名 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                       id="subject" name="subject" value="{{ old('subject', $quotation->subject) }}" 
                                       placeholder="見積件名を入力してください" required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="project_name" class="form-label">案件名</label>
                                <input type="text" class="form-control @error('project_name') is-invalid @enderror" 
                                       id="project_name" name="project_name" value="{{ old('project_name', $quotation->project_name) }}" 
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
                                       id="valid_until" name="valid_until" 
                                       value="{{ old('valid_until', $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : '') }}" required>
                                @error('valid_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="delivery_location" class="form-label">警備場所</label>
                                <input type="text" class="form-control @error('delivery_location') is-invalid @enderror" 
                                       id="delivery_location" name="delivery_location" value="{{ old('delivery_location', $quotation->delivery_location) }}" 
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
                                    <!-- 既存項目をロード -->
                                    @if($quotation->items && is_array($quotation->items))
                                        @foreach($quotation->items as $index => $item)
                                            <tr data-item-id="{{ $index }}">
                                                <td>
                                                    <input type="hidden" name="items[{{ $index }}][type]" value="{{ $item['type'] ?? 'other' }}">
                                                    <input type="hidden" name="items[{{ $index }}][name]" value="{{ $item['name'] ?? '' }}">
                                                    {{ $item['name'] ?? '未設定' }}
                                                    <small class="text-muted d-block">
                                                        @switch($item['type'] ?? 'other')
                                                            @case('labor')
                                                                <i class="fas fa-users me-1"></i>人件費
                                                                @break
                                                            @case('transport')
                                                                <i class="fas fa-car me-1"></i>交通費
                                                                @break
                                                            @case('equipment')
                                                                <i class="fas fa-tools me-1"></i>装備費
                                                                @break
                                                            @default
                                                                <i class="fas fa-tag me-1"></i>その他
                                                        @endswitch
                                                    </small>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm quantity-input" 
                                                           name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" 
                                                           min="1" onchange="updateRowAmount(this)">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" 
                                                           name="items[{{ $index }}][unit]" value="{{ $item['unit'] ?? '式' }}">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm unit-price-input" 
                                                           name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] ?? 0 }}" 
                                                           min="0" onchange="updateRowAmount(this)">
                                                </td>
                                                <td class="text-end amount-cell">
                                                    ¥{{ number_format(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0)) }}
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" 
                                                           name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem({{ $index }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">小計</td>
                                        <td class="text-end fw-bold" id="subtotalAmount">¥{{ number_format($quotation->subtotal_amount) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end">
                                            消費税 (<span id="taxRateDisplay">{{ $quotation->tax_rate ?? 10 }}</span>%)
                                        </td>
                                        <td class="text-end" id="taxAmount">¥{{ number_format($quotation->tax_amount) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td colspan="4" class="text-end fw-bold">合計金額</td>
                                        <td class="text-end fw-bold fs-5" id="totalAmount">¥{{ number_format($quotation->total_amount) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="alert alert-info mt-3" style="{{ $quotation->items && count($quotation->items) > 0 ? 'display: none;' : '' }}" id="noItemsAlert">
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
                                          placeholder="支払条件、警備条件等を入力してください">{{ old('terms_conditions', $quotation->terms_conditions) }}</textarea>
                                @error('terms_conditions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="notes" class="form-label">備考</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="5"
                                          placeholder="その他特記事項があれば入力してください">{{ old('notes', $quotation->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- 変更理由 -->
                        @if($quotation->status !== 'draft')
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="change_reason" class="form-label required">変更理由 <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('change_reason') is-invalid @enderror" 
                                              id="change_reason" name="change_reason" rows="3"
                                              placeholder="見積内容を変更する理由を入力してください" required>{{ old('change_reason') }}</textarea>
                                    @error('change_reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">変更理由は履歴として記録されます</small>
                                </div>
                            </div>
                        @endif
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
                                   value="{{ old('tax_rate', $quotation->tax_rate ?? 10) }}" min="0" max="100" step="0.1">
                        </div>
                        
                        <hr>
                        
                        <div class="row mb-2">
                            <div class="col-6">小計:</div>
                            <div class="col-6 text-end fw-bold" id="summarySubtotal">¥{{ number_format($quotation->subtotal_amount) }}</div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-6">消費税:</div>
                            <div class="col-6 text-end" id="summaryTax">¥{{ number_format($quotation->tax_amount) }}</div>
                        </div>
                        
                        <hr>
                        
                        <div class="row mb-3">
                            <div class="col-6 fw-bold fs-5">合計:</div>
                            <div class="col-6 text-end fw-bold fs-5 text-primary" id="summaryTotal">¥{{ number_format($quotation->total_amount) }}</div>
                        </div>
                        
                        <!-- 変更前金額との比較 -->
                        <div class="alert alert-light" id="amountComparisonAlert" style="display: none;">
                            <h6 class="text-primary">金額変更</h6>
                            <div class="row">
                                <div class="col-6 small">変更前:</div>
                                <div class="col-6 text-end small" id="originalAmount">¥{{ number_format($quotation->total_amount) }}</div>
                            </div>
                            <div class="row">
                                <div class="col-6 small">変更後:</div>
                                <div class="col-6 text-end small" id="newAmount">¥{{ number_format($quotation->total_amount) }}</div>
                            </div>
                            <hr class="my-2">
                            <div class="row">
                                <div class="col-6 small fw-bold">差額:</div>
                                <div class="col-6 text-end small fw-bold" id="amountDifference">¥0</div>
                            </div>
                        </div>
                        
                        <!-- 隠しフィールド -->
                        <input type="hidden" id="subtotal_amount" name="subtotal_amount" value="{{ $quotation->subtotal_amount }}">
                        <input type="hidden" id="tax_amount" name="tax_amount" value="{{ $quotation->tax_amount }}">
                        <input type="hidden" id="total_amount" name="total_amount" value="{{ $quotation->total_amount }}">
                        <input type="hidden" id="original_total_amount" value="{{ $quotation->total_amount }}">
                    </div>
                </div>

                <!-- 顧客情報 -->
                <div class="card shadow mb-4" id="customerInfo" style="{{ $quotation->customer ? '' : 'display: none;' }}">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-building me-2"></i>顧客情報
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="customerDetails">
                            @if($quotation->customer)
                                <div class="mb-2">
                                    <strong>{{ $quotation->customer->name }}</strong>
                                </div>
                                <div class="small text-muted mb-1">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $quotation->customer->address ?? '住所未登録' }}
                                </div>
                                <div class="small text-muted mb-1">
                                    <i class="fas fa-phone me-1"></i>
                                    {{ $quotation->customer->phone ?? '電話番号未登録' }}
                                </div>
                                <div class="small text-muted">
                                    <i class="fas fa-envelope me-1"></i>
                                    {{ $quotation->customer->email ?? 'メール未登録' }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 変更履歴 -->
                @if($quotation->change_history && is_array($quotation->change_history) && count($quotation->change_history) > 0)
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-history me-2"></i>変更履歴
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                @foreach(array_slice($quotation->change_history, -3) as $history)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">{{ $history['field'] ?? '' }}を変更</h6>
                                            <p class="timeline-text">
                                                {{ $history['user_name'] ?? '' }} - 
                                                {{ $history['created_at'] ?? '' }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            @if(count($quotation->change_history) > 3)
                                <div class="text-center mt-2">
                                    <a href="{{ route('quotations.show', $quotation) }}" class="btn btn-outline-info btn-sm">
                                        全履歴を表示
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

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
                            
                            <button type="submit" class="btn btn-primary" id="updateBtn">
                                <i class="fas fa-save me-1"></i>更新
                            </button>
                            
                            <hr>
                            
                            <button type="button" class="btn btn-warning" onclick="duplicateQuotation()">
                                <i class="fas fa-copy me-1"></i>複製
                            </button>
                            
                            <a href="{{ route('quotations.show', $quotation) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye me-1"></i>詳細表示
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- 項目追加モーダル（create.blade.phpと同じ構造） -->
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="confirmAddItem()">追加</button>
            </div>
        </div>
    </div>
</div>

<!-- プレビューモーダル（create.blade.phpと同じ構造） -->
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
let itemCounter = {{ $quotation->items ? count($quotation->items) : 0 }};
const originalTotalAmount = {{ $quotation->total_amount }};

$(document).ready(function() {
    // 初期化
    updateCalculations();
    checkNoItems();
    
    // 顧客情報の初期表示
    const selectedCustomerId = $('#customer_id').val();
    if (selectedCustomerId) {
        const selectedOption = $('#customer_id option:selected');
        const customerData = selectedOption.data('customer');
        if (customerData) {
            displayCustomerInfo(customerData);
        }
    }
    
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
    
    // 金額変更の比較表示
    showAmountComparison(totalAmount);
}

// 金額変更比較表示
function showAmountComparison(newTotal) {
    const originalTotal = originalTotalAmount;
    const difference = newTotal - originalTotal;
    
    if (difference !== 0) {
        $('#originalAmount').text(`¥${originalTotal.toLocaleString()}`);
        $('#newAmount').text(`¥${newTotal.toLocaleString()}`);
        
        const differenceText = (difference > 0 ? '+' : '') + `¥${Math.abs(difference).toLocaleString()}`;
        const differenceClass = difference > 0 ? 'text-success' : 'text-danger';
        
        $('#amountDifference').text(differenceText).removeClass('text-success text-danger').addClass(differenceClass);
        $('#amountComparisonAlert').show();
    } else {
        $('#amountComparisonAlert').hide();
    }
}

// 項目なしアラートの表示制御
function checkNoItems() {
    const hasItems = $('#itemsTableBody tr').length > 0;
    $('#noItemsAlert').toggle(!hasItems);
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
    const changeReason = $('#change_reason').val();
    const quotationStatus = '{{ $quotation->status }}';
    
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
    
    if (quotationStatus !== 'draft' && !changeReason.trim()) {
        alert('変更理由を入力してください。');
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
    
    // プレビューコンテンツの生成（create.blade.phpと同じ）
    generatePreviewContent(formData);
    $('#previewModal').modal('show');
}

// プレビューコンテンツ生成（create.blade.phpと同じ関数）
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
                <p class="text-muted">Quotation (改定版)</p>
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
                        <tr><th>見積番号:</th><td>{{ $quotation->quotation_number }}</td></tr>
                        <tr><th>件名:</th><td>${data.subject}</td></tr>
                        <tr><th>案件名:</th><td>${data.project_name || '-'}</td></tr>
                        <tr><th>警備場所:</th><td>${data.delivery_location || '-'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th>見積日:</th><td>{{ $quotation->created_at->format('Y年m月d日') }}</td></tr>
                        <tr><th>改定日:</th><td>${now.toLocaleDateString('ja-JP')}</td></tr>
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
    formData.set('status', 'draft');
    formData.append('items', JSON.stringify(collectItemsData()));
    
    $.ajax({
        url: '{{ route("quotations.update", $quotation) }}',
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
                location.reload();
            } else {
                alert(response.message || '保存に失敗しました。');
            }
        },
        error: function(xhr) {
            alert('エラーが発生しました。');
        }
    });
}

// 複製
function duplicateQuotation() {
    if (confirm('この見積を複製してもよろしいですか？')) {
        $.ajax({
            url: `/quotations/{{ $quotation->id }}/duplicate`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    alert('見積を複製しました。');
                    window.location.href = response.redirect || '{{ route("quotations.edit", ":id") }}'.replace(':id', response.quotation_id);
                } else {
                    alert(response.message || '複製に失敗しました。');
                }
            },
            error: function(xhr) {
                alert('エラーが発生しました。');
            }
        });
    }
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

.timeline {
    position: relative;
    padding-left: 1.5rem;
}

.timeline-item {
    position: relative;
    margin-bottom: 1rem;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 0.5rem;
    bottom: -1rem;
    width: 2px;
    background-color: #e3e6f0;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: -1.8rem;
    top: 0.3rem;
    width: 0.6rem;
    height: 0.6rem;
    border-radius: 50%;
    z-index: 1;
}

.timeline-content {
    background-color: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 0.375rem;
    padding: 0.5rem;
}

.timeline-title {
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.timeline-text {
    font-size: 0.7rem;
    color: #6c757d;
    margin-bottom: 0;
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
    .btn, .modal-header, .modal-footer, .alert {
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
