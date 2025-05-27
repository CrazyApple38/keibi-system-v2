@extends('layouts.app')

@section('title', '見積詳細')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-file-invoice-dollar me-2"></i>見積詳細
                    </h1>
                    <p class="mb-0 text-muted">見積書の詳細情報と操作</p>
                </div>
                <div>
                    <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>一覧に戻る
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 左カラム：見積内容 -->
        <div class="col-lg-8">
            <!-- 基本情報 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>基本情報
                    </h6>
                    @php
                        $statusClasses = [
                            'draft' => 'secondary',
                            'pending' => 'warning',
                            'approved' => 'info',
                            'sent' => 'primary',
                            'accepted' => 'success',
                            'rejected' => 'danger',
                            'expired' => 'dark'
                        ];
                        $statusLabels = [
                            'draft' => '下書き',
                            'pending' => '承認待ち',
                            'approved' => '承認済み',
                            'sent' => '送付済み',
                            'accepted' => '受注',
                            'rejected' => '失注',
                            'expired' => '期限切れ'
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusClasses[$quotation->status] ?? 'secondary' }} fs-6">
                        {{ $statusLabels[$quotation->status] ?? $quotation->status }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="120">見積番号:</th>
                                    <td class="fw-bold text-primary">{{ $quotation->quotation_number }}</td>
                                </tr>
                                <tr>
                                    <th>件名:</th>
                                    <td>{{ $quotation->subject }}</td>
                                </tr>
                                <tr>
                                    <th>案件名:</th>
                                    <td>{{ $quotation->project->name ?? $quotation->project_name ?? '未設定' }}</td>
                                </tr>
                                <tr>
                                    <th>警備場所:</th>
                                    <td>{{ $quotation->delivery_location ?? '未設定' }}</td>
                                </tr>
                                <tr>
                                    <th>顧客:</th>
                                    <td>
                                        @if($quotation->customer)
                                            <a href="{{ route('customers.show', $quotation->customer) }}" 
                                               class="text-decoration-none">
                                                {{ $quotation->customer->name }}
                                            </a>
                                        @else
                                            未設定
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="120">作成日:</th>
                                    <td>{{ $quotation->created_at->format('Y年m月d日') }}</td>
                                </tr>
                                <tr>
                                    <th>有効期限:</th>
                                    <td>
                                        @if($quotation->valid_until)
                                            <span class="{{ \Carbon\Carbon::parse($quotation->valid_until)->isPast() ? 'text-danger fw-bold' : '' }}">
                                                {{ \Carbon\Carbon::parse($quotation->valid_until)->format('Y年m月d日') }}
                                                @if(\Carbon\Carbon::parse($quotation->valid_until)->isPast())
                                                    <i class="fas fa-exclamation-triangle ms-1" title="期限切れ"></i>
                                                @endif
                                            </span>
                                        @else
                                            未設定
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>担当者:</th>
                                    <td>{{ $quotation->created_by_user->name ?? '未設定' }}</td>
                                </tr>
                                <tr>
                                    <th>更新日:</th>
                                    <td>{{ $quotation->updated_at->format('Y年m月d日 H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>見積金額:</th>
                                    <td class="fw-bold text-success fs-5">
                                        ¥{{ number_format($quotation->total_amount) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 見積明細 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>見積明細
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">No.</th>
                                    <th>項目名</th>
                                    <th width="80">数量</th>
                                    <th width="60">単位</th>
                                    <th width="120">単価</th>
                                    <th width="120">金額</th>
                                    <th width="150">備考</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($quotation->items && is_array($quotation->items))
                                    @foreach($quotation->items as $index => $item)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                {{ $item['name'] ?? '未設定' }}
                                                @if(isset($item['type']))
                                                    <small class="text-muted d-block">
                                                        @switch($item['type'])
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
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $item['quantity'] ?? 0 }}</td>
                                            <td class="text-center">{{ $item['unit'] ?? '' }}</td>
                                            <td class="text-end">¥{{ number_format($item['unit_price'] ?? 0) }}</td>
                                            <td class="text-end fw-bold">
                                                ¥{{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)) }}
                                            </td>
                                            <td class="small">{{ $item['description'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            見積項目がありません
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">小計</td>
                                    <td class="text-end fw-bold">¥{{ number_format($quotation->subtotal_amount) }}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end">
                                        消費税 ({{ $quotation->tax_rate ?? 10 }}%)
                                    </td>
                                    <td class="text-end">¥{{ number_format($quotation->tax_amount) }}</td>
                                    <td></td>
                                </tr>
                                <tr class="table-primary">
                                    <td colspan="5" class="text-end fw-bold">合計金額</td>
                                    <td class="text-end fw-bold fs-5">¥{{ number_format($quotation->total_amount) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 条件・備考 -->
            @if($quotation->terms_conditions || $quotation->notes)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-clipboard-list me-2"></i>条件・備考
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($quotation->terms_conditions)
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-primary">取引条件</h6>
                                    <div class="border p-3 bg-light">
                                        {!! nl2br(e($quotation->terms_conditions)) !!}
                                    </div>
                                </div>
                            @endif
                            
                            @if($quotation->notes)
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-primary">備考</h6>
                                    <div class="border p-3 bg-light">
                                        {!! nl2br(e($quotation->notes)) !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- 承認履歴・変更履歴 -->
            @if($quotation->approval_history || $quotation->change_history)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-history me-2"></i>履歴
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($quotation->approval_history && is_array($quotation->approval_history))
                            <h6 class="text-primary mb-3">承認履歴</h6>
                            <div class="timeline mb-4">
                                @foreach($quotation->approval_history as $history)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-primary"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">{{ $history['action'] ?? '' }}</h6>
                                            <p class="timeline-text">
                                                {{ $history['user_name'] ?? '' }} - 
                                                {{ $history['created_at'] ?? '' }}
                                            </p>
                                            @if(isset($history['comment']))
                                                <p class="text-muted small">{{ $history['comment'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        
                        @if($quotation->change_history && is_array($quotation->change_history))
                            <h6 class="text-primary mb-3">変更履歴</h6>
                            <div class="timeline">
                                @foreach($quotation->change_history as $history)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">{{ $history['field'] ?? '' }}を変更</h6>
                                            <p class="timeline-text">
                                                {{ $history['user_name'] ?? '' }} - 
                                                {{ $history['created_at'] ?? '' }}
                                            </p>
                                            @if(isset($history['old_value']) || isset($history['new_value']))
                                                <p class="text-muted small">
                                                    変更前: {{ $history['old_value'] ?? '未設定' }}<br>
                                                    変更後: {{ $history['new_value'] ?? '未設定' }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- 右カラム：操作・関連情報 -->
        <div class="col-lg-4">
            <!-- 操作パネル -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs me-2"></i>操作
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <!-- ステータス別操作ボタン -->
                        @if($quotation->status === 'draft')
                            <a href="{{ route('quotations.edit', $quotation) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>編集
                            </a>
                            <button type="button" class="btn btn-info" onclick="submitForApproval()">
                                <i class="fas fa-paper-plane me-1"></i>承認依頼
                            </button>
                        @elseif($quotation->status === 'pending')
                            @if(auth()->user()->hasRole(['admin', 'manager']))
                                <button type="button" class="btn btn-success" onclick="approveQuotation()">
                                    <i class="fas fa-check me-1"></i>承認
                                </button>
                                <button type="button" class="btn btn-warning" onclick="rejectQuotation()">
                                    <i class="fas fa-times me-1"></i>差し戻し
                                </button>
                            @endif
                        @elseif($quotation->status === 'approved')
                            <button type="button" class="btn btn-primary" onclick="sendToCustomer()">
                                <i class="fas fa-envelope me-1"></i>顧客に送付
                            </button>
                            <a href="{{ route('quotations.edit', $quotation) }}" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>編集
                            </a>
                        @elseif($quotation->status === 'sent')
                            <button type="button" class="btn btn-success" onclick="markAsAccepted()">
                                <i class="fas fa-handshake me-1"></i>受注確定
                            </button>
                            <button type="button" class="btn btn-danger" onclick="markAsRejected()">
                                <i class="fas fa-times-circle me-1"></i>失注登録
                            </button>
                        @endif
                        
                        <!-- 共通操作 -->
                        <hr>
                        
                        <button type="button" class="btn btn-info" onclick="printQuotation()">
                            <i class="fas fa-print me-1"></i>印刷
                        </button>
                        
                        <button type="button" class="btn btn-success" onclick="exportPDF()">
                            <i class="fas fa-file-pdf me-1"></i>PDF出力
                        </button>
                        
                        <button type="button" class="btn btn-warning" onclick="duplicateQuotation()">
                            <i class="fas fa-copy me-1"></i>複製
                        </button>
                        
                        @if($quotation->status === 'accepted')
                            <a href="{{ route('contracts.create', ['quotation_id' => $quotation->id]) }}" 
                               class="btn btn-primary">
                                <i class="fas fa-file-contract me-1"></i>契約書作成
                            </a>
                        @endif
                        
                        <hr>
                        
                        @if(in_array($quotation->status, ['draft', 'pending']))
                            <button type="button" class="btn btn-danger" onclick="deleteQuotation()">
                                <i class="fas fa-trash me-1"></i>削除
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 顧客情報 -->
            @if($quotation->customer)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-building me-2"></i>顧客情報
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h5>{{ $quotation->customer->name }}</h5>
                        </div>
                        
                        <table class="table table-borderless table-sm">
                            @if($quotation->customer->address)
                                <tr>
                                    <th width="60"><i class="fas fa-map-marker-alt text-muted"></i></th>
                                    <td>{{ $quotation->customer->address }}</td>
                                </tr>
                            @endif
                            
                            @if($quotation->customer->phone)
                                <tr>
                                    <th><i class="fas fa-phone text-muted"></i></th>
                                    <td>
                                        <a href="tel:{{ $quotation->customer->phone }}" class="text-decoration-none">
                                            {{ $quotation->customer->phone }}
                                        </a>
                                    </td>
                                </tr>
                            @endif
                            
                            @if($quotation->customer->email)
                                <tr>
                                    <th><i class="fas fa-envelope text-muted"></i></th>
                                    <td>
                                        <a href="mailto:{{ $quotation->customer->email }}" class="text-decoration-none">
                                            {{ $quotation->customer->email }}
                                        </a>
                                    </td>
                                </tr>
                            @endif
                            
                            @if($quotation->customer->contact_person)
                                <tr>
                                    <th><i class="fas fa-user text-muted"></i></th>
                                    <td>{{ $quotation->customer->contact_person }}</td>
                                </tr>
                            @endif
                        </table>
                        
                        <div class="text-center mt-3">
                            <a href="{{ route('customers.show', $quotation->customer) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i>詳細表示
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 関連情報 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-link me-2"></i>関連情報
                    </h6>
                </div>
                <div class="card-body">
                    @if($quotation->project)
                        <div class="mb-3">
                            <h6 class="text-primary">関連案件</h6>
                            <a href="{{ route('projects.show', $quotation->project) }}" 
                               class="text-decoration-none">
                                {{ $quotation->project->name }}
                            </a>
                        </div>
                    @endif
                    
                    <!-- 同じ顧客の他の見積 -->
                    @if($quotation->customer && $relatedQuotations->count() > 0)
                        <div class="mb-3">
                            <h6 class="text-primary">同じ顧客の他の見積</h6>
                            <div class="list-group list-group-flush">
                                @foreach($relatedQuotations->take(5) as $related)
                                    <a href="{{ route('quotations.show', $related) }}" 
                                       class="list-group-item list-group-item-action py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small">{{ $related->subject }}</span>
                                            <span class="badge bg-{{ $statusClasses[$related->status] ?? 'secondary' }} small">
                                                {{ $statusLabels[$related->status] ?? $related->status }}
                                            </span>
                                        </div>
                                        <div class="small text-muted">
                                            {{ $related->created_at->format('Y/m/d') }}
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            
                            @if($relatedQuotations->count() > 5)
                                <div class="text-center mt-2">
                                    <a href="{{ route('quotations.index', ['customer_id' => $quotation->customer->id]) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        他の見積も表示
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                    
                    <!-- 契約情報 -->
                    @if($quotation->status === 'accepted' && $quotation->contracts && $quotation->contracts->count() > 0)
                        <div class="mb-3">
                            <h6 class="text-primary">関連契約</h6>
                            @foreach($quotation->contracts as $contract)
                                <a href="{{ route('contracts.show', $contract) }}" 
                                   class="btn btn-outline-success btn-sm d-block mb-2">
                                    <i class="fas fa-file-contract me-1"></i>
                                    契約書 #{{ $contract->contract_number }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- 見積統計 -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i>見積統計
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-2">
                            <div class="border-end">
                                <div class="h6 mb-0 text-primary">{{ $quotation->items ? count($quotation->items) : 0 }}</div>
                                <small class="text-muted">項目数</small>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="h6 mb-0 text-success">{{ $quotation->tax_rate ?? 10 }}%</div>
                            <small class="text-muted">消費税率</small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    @if($quotation->valid_until)
                        @php
                            $validUntil = \Carbon\Carbon::parse($quotation->valid_until);
                            $daysLeft = $validUntil->diffInDays(now(), false);
                        @endphp
                        <div class="text-center">
                            <div class="h6 mb-0 {{ $daysLeft > 0 ? 'text-danger' : 'text-info' }}">
                                {{ $daysLeft > 0 ? '期限切れ' : abs($daysLeft) . '日' }}
                            </div>
                            <small class="text-muted">
                                {{ $daysLeft > 0 ? '' : '有効期限まで' }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 承認・差し戻しモーダル -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">見積承認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="approvalComment" class="form-label">コメント</label>
                    <textarea class="form-control" id="approvalComment" rows="3" 
                              placeholder="承認・差し戻しの理由やコメントを入力してください"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" id="confirmApprovalBtn">確定</button>
            </div>
        </div>
    </div>
</div>

<!-- 顧客送付モーダル -->
<div class="modal fade" id="sendModal" tabindex="-1" aria-labelledby="sendModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendModalLabel">顧客に送付</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="sendEmail" class="form-label">送付先メールアドレス</label>
                    <input type="email" class="form-control" id="sendEmail" 
                           value="{{ $quotation->customer->email ?? '' }}" required>
                </div>
                <div class="mb-3">
                    <label for="sendSubject" class="form-label">件名</label>
                    <input type="text" class="form-control" id="sendSubject" 
                           value="【見積書送付】{{ $quotation->subject }}" required>
                </div>
                <div class="mb-3">
                    <label for="sendMessage" class="form-label">メッセージ</label>
                    <textarea class="form-control" id="sendMessage" rows="5" required>{{ $quotation->customer->name ?? 'お客様' }}

いつもお世話になっております。
ご依頼いただきました件につきまして、見積書を送付いたします。

ご確認の程、よろしくお願いいたします。

警備システム株式会社</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="confirmSendToCustomer()">送付</button>
            </div>
        </div>
    </div>
</div>

<!-- 削除確認モーダル -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">見積削除確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>この見積を削除してもよろしいですか？</p>
                <p class="text-danger"><strong>※ この操作は取り消せません。</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">削除</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // ページロード時の初期化処理
});

// 承認依頼
function submitForApproval() {
    $('#approvalModalLabel').text('承認依頼');
    $('#confirmApprovalBtn').text('承認依頼').removeClass().addClass('btn btn-info');
    $('#approvalModal').modal('show');
    
    $('#confirmApprovalBtn').off('click').on('click', function() {
        updateQuotationStatus('pending', $('#approvalComment').val());
    });
}

// 承認
function approveQuotation() {
    $('#approvalModalLabel').text('見積承認');
    $('#confirmApprovalBtn').text('承認').removeClass().addClass('btn btn-success');
    $('#approvalModal').modal('show');
    
    $('#confirmApprovalBtn').off('click').on('click', function() {
        updateQuotationStatus('approved', $('#approvalComment').val());
    });
}

// 差し戻し
function rejectQuotation() {
    $('#approvalModalLabel').text('見積差し戻し');
    $('#confirmApprovalBtn').text('差し戻し').removeClass().addClass('btn btn-warning');
    $('#approvalModal').modal('show');
    
    $('#confirmApprovalBtn').off('click').on('click', function() {
        updateQuotationStatus('draft', $('#approvalComment').val());
    });
}

// 顧客に送付
function sendToCustomer() {
    $('#sendModal').modal('show');
}

function confirmSendToCustomer() {
    const email = $('#sendEmail').val();
    const subject = $('#sendSubject').val();
    const message = $('#sendMessage').val();
    
    if (!email || !subject || !message) {
        alert('全ての項目を入力してください。');
        return;
    }
    
    $.ajax({
        url: `/quotations/{{ $quotation->id }}/send`,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            email: email,
            subject: subject,
            message: message
        },
        success: function(response) {
            $('#sendModal').modal('hide');
            if (response.success) {
                showAlert('success', '見積書を送付しました。');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', response.message || '送付に失敗しました。');
            }
        },
        error: function(xhr) {
            $('#sendModal').modal('hide');
            showAlert('error', 'エラーが発生しました。');
        }
    });
}

// 受注確定
function markAsAccepted() {
    if (confirm('この見積を受注確定してもよろしいですか？')) {
        updateQuotationStatus('accepted');
    }
}

// 失注登録
function markAsRejected() {
    if (confirm('この見積を失注登録してもよろしいですか？')) {
        updateQuotationStatus('rejected');
    }
}

// ステータス更新
function updateQuotationStatus(status, comment = '') {
    $.ajax({
        url: `/quotations/{{ $quotation->id }}/status`,
        type: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            status: status,
            comment: comment
        },
        success: function(response) {
            $('#approvalModal').modal('hide');
            if (response.success) {
                showAlert('success', 'ステータスを更新しました。');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', response.message || 'ステータス更新に失敗しました。');
            }
        },
        error: function(xhr) {
            $('#approvalModal').modal('hide');
            showAlert('error', 'エラーが発生しました。');
        }
    });
}

// 印刷
function printQuotation() {
    window.print();
}

// PDF出力
function exportPDF() {
    const url = `/quotations/{{ $quotation->id }}/pdf`;
    window.open(url, '_blank');
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
                    showAlert('success', '見積を複製しました。');
                    window.location.href = response.redirect || '{{ route("quotations.edit", ":id") }}'.replace(':id', response.quotation_id);
                } else {
                    showAlert('error', response.message || '複製に失敗しました。');
                }
            },
            error: function(xhr) {
                showAlert('error', 'エラーが発生しました。');
            }
        });
    }
}

// 削除
function deleteQuotation() {
    $('#deleteModal').modal('show');
}

function confirmDelete() {
    $.ajax({
        url: `/quotations/{{ $quotation->id }}`,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#deleteModal').modal('hide');
            if (response.success) {
                showAlert('success', '見積を削除しました。');
                setTimeout(() => {
                    window.location.href = '{{ route("quotations.index") }}';
                }, 1000);
            } else {
                showAlert('error', response.message || '削除に失敗しました。');
            }
        },
        error: function(xhr) {
            $('#deleteModal').modal('hide');
            showAlert('error', 'エラーが発生しました。');
        }
    });
}

// アラート表示
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    $('.container-fluid').prepend(alertHtml);
    
    // 3秒後に自動で閉じる
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 3000);
}
</script>
@endpush

@push('styles')
<style>
.table th {
    background-color: #f8f9fc;
    border-color: #e3e6f0;
    font-weight: 600;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -2rem;
    top: 0.5rem;
    bottom: -1.5rem;
    width: 2px;
    background-color: #e3e6f0;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: -2.3rem;
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
    padding: 0.75rem;
}

.timeline-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.timeline-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .timeline {
        padding-left: 1.5rem;
    }
    
    .timeline-item::before {
        left: -1.5rem;
    }
    
    .timeline-marker {
        left: -1.8rem;
    }
}

@media print {
    .btn, .card-header, .modal, .col-lg-4 {
        display: none !important;
    }
    
    .col-lg-8 {
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }
    
    .table {
        font-size: 0.8rem;
    }
    
    body {
        font-size: 0.9rem;
    }
}
</style>
@endpush
