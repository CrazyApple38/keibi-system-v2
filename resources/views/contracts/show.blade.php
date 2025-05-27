@extends('layouts.app')

@section('title', '契約詳細 - ' . $contract->contract_number)

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-0">契約詳細</h1>
                    <p class="text-muted mb-0">Contract Details - {{ $contract->contract_number }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> 一覧に戻る
                    </a>
                    @can('update', $contract)
                        <a href="{{ route('contracts.edit', $contract) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> 編集
                        </a>
                    @endcan
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i> 操作
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="downloadContract()">
                                <i class="fas fa-download"></i> 契約書ダウンロード
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="printContract()">
                                <i class="fas fa-print"></i> 契約書印刷
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            @if($contract->status === 'draft')
                                <li><a class="dropdown-item text-info" href="#" onclick="submitForApproval()">
                                    <i class="fas fa-paper-plane"></i> 承認依頼
                                </a></li>
                            @endif
                            @if($contract->status === 'under_review' && auth()->user()->can('approve', $contract))
                                <li><a class="dropdown-item text-success" href="#" onclick="approveContract()">
                                    <i class="fas fa-check"></i> 承認
                                </a></li>
                                <li><a class="dropdown-item text-warning" href="#" onclick="rejectContract()">
                                    <i class="fas fa-times"></i> 差し戻し
                                </a></li>
                            @endif
                            @if($contract->status === 'approved')
                                <li><a class="dropdown-item text-primary" href="#" onclick="activateContract()">
                                    <i class="fas fa-play"></i> 契約発効
                                </a></li>
                            @endif
                            @if($contract->status === 'active' && $contract->is_auto_renewal)
                                <li><a class="dropdown-item text-info" href="#" onclick="renewContract()">
                                    <i class="fas fa-redo"></i> 契約更新
                                </a></li>
                            @endif
                            @if($contract->status === 'active')
                                <li><a class="dropdown-item text-warning" href="#" onclick="terminateContract()">
                                    <i class="fas fa-ban"></i> 契約解約
                                </a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="duplicateContract()">
                                <i class="fas fa-copy"></i> 契約複製
                            </a></li>
                            @can('delete', $contract)
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteContract()">
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
    @if($contract->status === 'draft')
        <div class="alert alert-secondary">
            <i class="fas fa-edit"></i> この契約は下書き状態です。承認依頼を行うことができます。
        </div>
    @elseif($contract->status === 'under_review')
        <div class="alert alert-warning">
            <i class="fas fa-clock"></i> この契約は承認待ち状態です。
        </div>
    @elseif($contract->status === 'approved')
        <div class="alert alert-info">
            <i class="fas fa-check"></i> この契約は承認済みです。発効処理を行うことができます。
        </div>
    @elseif($contract->end_date && $contract->end_date->isPast())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> この契約は期限切れです。
        </div>
    @elseif($contract->end_date && $contract->end_date->diffInDays() <= 30)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> この契約の期限まで{{ $contract->end_date->diffInDays() }}日です。
        </div>
    @endif

    <div class="row">
        <!-- 左側: 契約詳細情報 -->
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
                            <strong class="text-muted">契約番号</strong>
                            <div class="fs-5 fw-bold">{{ $contract->contract_number }}</div>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted">契約ステータス</strong>
                            <div>
                                @php
                                    $statusConfig = [
                                        'draft' => ['class' => 'bg-secondary', 'icon' => 'fas fa-edit', 'text' => '下書き'],
                                        'under_review' => ['class' => 'bg-warning', 'icon' => 'fas fa-clock', 'text' => '承認待ち'],
                                        'approved' => ['class' => 'bg-info', 'icon' => 'fas fa-check', 'text' => '承認済み'],
                                        'active' => ['class' => 'bg-success', 'icon' => 'fas fa-play', 'text' => '有効'],
                                        'completed' => ['class' => 'bg-primary', 'icon' => 'fas fa-flag-checkered', 'text' => '完了'],
                                        'terminated' => ['class' => 'bg-danger', 'icon' => 'fas fa-times', 'text' => '解約']
                                    ];
                                    $config = $statusConfig[$contract->status] ?? $statusConfig['draft'];
                                @endphp
                                <span class="badge {{ $config['class'] }} fs-6">
                                    <i class="{{ $config['icon'] }}"></i> {{ $config['text'] }}
                                </span>
                                @if($contract->is_auto_renewal)
                                    <span class="badge bg-info ms-2">自動更新</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted">顧客名</strong>
                            <div>
                                <a href="{{ route('customers.show', $contract->customer) }}" class="text-decoration-none">
                                    {{ $contract->customer->name }}
                                </a>
                                <br><small class="text-muted">{{ $contract->customer->company_type }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted">案件名</strong>
                            <div>
                                <a href="{{ route('projects.show', $contract->project) }}" class="text-decoration-none">
                                    {{ $contract->project->name }}
                                </a>
                                <br><small class="text-muted">{{ $contract->project->project_type }}</small>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <strong class="text-muted">契約件名</strong>
                            <div class="fs-5">{{ $contract->title }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 契約期間・条件 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt"></i> 契約期間・条件
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <strong class="text-muted">契約開始日</strong>
                            <div class="fs-6">{{ $contract->start_date->format('Y年m月d日') }}</div>
                        </div>
                        <div class="col-md-4">
                            <strong class="text-muted">契約終了日</strong>
                            <div class="fs-6">{{ $contract->end_date->format('Y年m月d日') }}</div>
                        </div>
                        <div class="col-md-4">
                            <strong class="text-muted">契約期間</strong>
                            <div class="fs-6">
                                {{ $contract->start_date->diffInDays($contract->end_date) }}日間
                                ({{ $contract->start_date->diffInMonths($contract->end_date) }}ヶ月)
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted">支払い条件</strong>
                            <div>{{ $contract->payment_terms ?: '未設定' }}</div>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted">支払い方法</strong>
                            <div>{{ $contract->payment_method ?: '未設定' }}</div>
                        </div>
                        @if($contract->is_auto_renewal)
                            <div class="col-md-6">
                                <strong class="text-muted">更新期間</strong>
                                <div>{{ $contract->renewal_period ?: '未設定' }}</div>
                            </div>
                            <div class="col-md-6">
                                <strong class="text-muted">更新通知期間</strong>
                                <div>{{ $contract->renewal_notice_period ?: '未設定' }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 契約金額 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-yen-sign"></i> 契約金額
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <strong class="text-muted">基本契約金額</strong>
                            <div class="fs-5">¥{{ number_format($contract->base_amount) }}</div>
                        </div>
                        <div class="col-md-4">
                            <strong class="text-muted">消費税額</strong>
                            <div class="fs-6">¥{{ number_format($contract->tax_amount) }}</div>
                        </div>
                        <div class="col-md-4">
                            <strong class="text-muted">総契約金額</strong>
                            <div class="fs-4 fw-bold text-primary">¥{{ number_format($contract->total_amount) }}</div>
                        </div>
                        @if($contract->price_type)
                            <div class="col-md-6">
                                <strong class="text-muted">単価形態</strong>
                                <div>{{ $contract->price_type }}</div>
                            </div>
                        @endif
                        @if($contract->unit_price)
                            <div class="col-md-6">
                                <strong class="text-muted">単価</strong>
                                <div>¥{{ number_format($contract->unit_price) }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 契約条件・特記事項 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-contract"></i> 契約条件・特記事項
                    </h5>
                </div>
                <div class="card-body">
                    @if($contract->terms_conditions)
                        <div class="mb-3">
                            <strong class="text-muted">契約条件</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                {!! nl2br(e($contract->terms_conditions)) !!}
                            </div>
                        </div>
                    @endif
                    
                    @if($contract->special_notes)
                        <div class="mb-3">
                            <strong class="text-muted">特記事項</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                {!! nl2br(e($contract->special_notes)) !!}
                            </div>
                        </div>
                    @endif
                    
                    @if($contract->work_content)
                        <div class="mb-3">
                            <strong class="text-muted">業務内容</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                {!! nl2br(e($contract->work_content)) !!}
                            </div>
                        </div>
                    @endif
                    
                    @if($contract->work_location)
                        <div class="mb-3">
                            <strong class="text-muted">履行場所</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                {!! nl2br(e($contract->work_location)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 責任者・担当者 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users"></i> 責任者・担当者
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($contract->manager)
                            <div class="col-md-6">
                                <strong class="text-muted">契約責任者</strong>
                                <div>
                                    <a href="{{ route('users.show', $contract->manager) }}" class="text-decoration-none">
                                        {{ $contract->manager->name }}
                                    </a>
                                    @if($contract->manager->department)
                                        <br><small class="text-muted">{{ $contract->manager->department }}</small>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        @if($contract->salesPerson)
                            <div class="col-md-6">
                                <strong class="text-muted">営業担当者</strong>
                                <div>
                                    <a href="{{ route('users.show', $contract->salesPerson) }}" class="text-decoration-none">
                                        {{ $contract->salesPerson->name }}
                                    </a>
                                </div>
                            </div>
                        @endif
                        
                        @if($contract->customer_contact_name)
                            <div class="col-md-6">
                                <strong class="text-muted">顧客担当者</strong>
                                <div>{{ $contract->customer_contact_name }}</div>
                                @if($contract->customer_contact_info)
                                    <small class="text-muted">{{ $contract->customer_contact_info }}</small>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 添付ファイル -->
            @if($contract->attachments && count($contract->attachments) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-paperclip"></i> 添付ファイル
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($contract->attachments as $attachment)
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
                                <div class="h5 mb-0 text-primary">{{ $statistics['total_shifts'] ?? 0 }}</div>
                                <small class="text-muted">シフト数</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-0 text-success">{{ $statistics['total_guards'] ?? 0 }}</div>
                            <small class="text-muted">配置人数</small>
                        </div>
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h5 mb-0 text-info">{{ $statistics['completion_rate'] ?? 0 }}%</div>
                                <small class="text-muted">完了率</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-0 text-warning">¥{{ number_format($statistics['total_invoiced'] ?? 0) }}</div>
                            <small class="text-muted">請求済み</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 承認履歴 -->
            @if($contract->approvals && count($contract->approvals) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-history"></i> 承認履歴
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($contract->approvals as $approval)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-{{ $approval['status'] === 'approved' ? 'success' : ($approval['status'] === 'rejected' ? 'danger' : 'warning') }}"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">{{ $approval['action'] }}</h6>
                                        <small class="text-muted">
                                            {{ $approval['user'] }} - {{ $approval['date'] }}
                                        </small>
                                        @if(isset($approval['comment']))
                                            <div class="mt-1 small">{{ $approval['comment'] }}</div>
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
                    @if($contract->quotation)
                        <div class="mb-3">
                            <strong class="text-muted">元見積</strong>
                            <div>
                                <a href="{{ route('quotations.show', $contract->quotation) }}" class="text-decoration-none">
                                    {{ $contract->quotation->quotation_number }}
                                </a>
                            </div>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <strong class="text-muted">関連シフト</strong>
                        <div>
                            <a href="{{ route('shifts.index', ['project_id' => $contract->project_id]) }}" class="text-decoration-none">
                                シフト一覧を見る
                            </a>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong class="text-muted">関連請求書</strong>
                        <div>
                            <a href="{{ route('invoices.index', ['contract_id' => $contract->id]) }}" class="text-decoration-none">
                                請求書一覧を見る
                            </a>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong class="text-muted">関連日報</strong>
                        <div>
                            <a href="{{ route('daily-reports.index', ['project_id' => $contract->project_id]) }}" class="text-decoration-none">
                                日報一覧を見る
                            </a>
                        </div>
                    </div>
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
                            {{ $contract->created_at->format('Y/m/d H:i') }}
                        </div>
                        <div class="mb-2">
                            <strong>最終更新:</strong><br>
                            {{ $contract->updated_at->format('Y/m/d H:i') }}
                        </div>
                        @if($contract->approved_at)
                            <div class="mb-2">
                                <strong>承認日時:</strong><br>
                                {{ $contract->approved_at->format('Y/m/d H:i') }}
                            </div>
                        @endif
                        @if($contract->activated_at)
                            <div class="mb-2">
                                <strong>発効日時:</strong><br>
                                {{ $contract->activated_at->format('Y/m/d H:i') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 操作確認モーダル -->
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">操作確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="action-content">
                    <!-- 操作内容がここに表示される -->
                </div>
                <div id="comment-section" style="display: none;">
                    <label class="form-label mt-3">コメント</label>
                    <textarea class="form-control" id="action-comment" rows="3" placeholder="必要に応じてコメントを入力"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" id="confirm-action">実行</button>
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
let currentAction = null;

$(document).ready(function() {
    // リアルタイム統計更新
    setInterval(updateStatistics, 60000); // 1分間隔
});

/**
 * 契約書ダウンロード
 */
function downloadContract() {
    window.location.href = '{{ route("contracts.download", $contract) }}';
}

/**
 * 契約書印刷
 */
function printContract() {
    window.print();
}

/**
 * 承認依頼
 */
function submitForApproval() {
    showActionModal(
        '承認依頼',
        'この契約を承認依頼しますか？',
        'submit_for_approval',
        'btn-info'
    );
}

/**
 * 契約承認
 */
function approveContract() {
    showActionModal(
        '契約承認',
        'この契約を承認しますか？',
        'approve',
        'btn-success',
        true
    );
}

/**
 * 契約差し戻し
 */
function rejectContract() {
    showActionModal(
        '契約差し戻し',
        'この契約を差し戻しますか？理由を入力してください。',
        'reject',
        'btn-warning',
        true
    );
}

/**
 * 契約発効
 */
function activateContract() {
    showActionModal(
        '契約発効',
        'この契約を発効しますか？発効後は契約内容の変更ができなくなります。',
        'activate',
        'btn-primary'
    );
}

/**
 * 契約更新
 */
function renewContract() {
    showActionModal(
        '契約更新',
        'この契約を更新しますか？自動更新設定に基づいて新しい契約期間が設定されます。',
        'renew',
        'btn-info'
    );
}

/**
 * 契約解約
 */
function terminateContract() {
    showActionModal(
        '契約解約',
        'この契約を解約しますか？この操作は取り消せません。',
        'terminate',
        'btn-warning',
        true
    );
}

/**
 * 契約複製
 */
function duplicateContract() {
    if (confirm('この契約を複製して新しい契約を作成しますか？')) {
        window.location.href = '{{ route("contracts.create") }}?duplicate={{ $contract->id }}';
    }
}

/**
 * 契約削除
 */
function deleteContract() {
    if (confirm('この契約を削除しますか？この操作は取り消せません。')) {
        $.ajax({
            url: '{{ route("contracts.destroy", $contract) }}',
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert('契約を削除しました。');
                window.location.href = '{{ route("contracts.index") }}';
            },
            error: function(xhr) {
                alert('エラーが発生しました: ' + xhr.responseJSON.message);
            }
        });
    }
}

/**
 * 操作モーダル表示
 */
function showActionModal(title, content, action, buttonClass, showComment = false) {
    $('#actionModal .modal-title').text(title);
    $('#action-content').html('<p>' + content + '</p>');
    
    if (showComment) {
        $('#comment-section').show();
    } else {
        $('#comment-section').hide();
    }
    
    $('#confirm-action')
        .removeClass('btn-primary btn-success btn-warning btn-info btn-danger')
        .addClass(buttonClass);
    
    currentAction = action;
    $('#actionModal').modal('show');
}

/**
 * 操作実行
 */
$('#confirm-action').click(function() {
    if (!currentAction) return;
    
    const comment = $('#action-comment').val();
    
    $.ajax({
        url: '{{ route("contracts.action", $contract) }}',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            action: currentAction,
            comment: comment
        },
        beforeSend: function() {
            $('#confirm-action').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> 処理中...');
        },
        success: function(response) {
            $('#actionModal').modal('hide');
            alert(response.message);
            location.reload();
        },
        error: function(xhr) {
            alert('エラーが発生しました: ' + xhr.responseJSON.message);
        },
        complete: function() {
            $('#confirm-action').prop('disabled', false).html('実行');
        }
    });
});

/**
 * 統計情報更新
 */
function updateStatistics() {
    $.ajax({
        url: '{{ route("contracts.statistics", $contract) }}',
        method: 'GET',
        success: function(data) {
            // 統計カードの更新
            $('.h5.mb-0.text-primary').text(data.total_shifts);
            $('.h5.mb-0.text-success').text(data.total_guards);
            $('.h5.mb-0.text-info').text(data.completion_rate + '%');
            $('.h5.mb-0.text-warning').text('¥' + new Intl.NumberFormat('ja-JP').format(data.total_invoiced));
        }
    });
}
</script>
@endpush