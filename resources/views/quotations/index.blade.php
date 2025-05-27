@extends('layouts.app')

@section('title', '見積管理')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-file-invoice-dollar me-2"></i>見積管理
                    </h1>
                    <p class="mb-0 text-muted">顧客向け見積書の作成・管理・承認を行います</p>
                </div>
                <div>
                    <a href="{{ route('quotations.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>新規見積作成
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 統計情報カード -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                今月の見積件数
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="monthlyQuotations">
                                {{ $statistics['monthly_count'] ?? 0 }}件
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                今月の見積金額
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="monthlyAmount">
                                ¥{{ number_format($statistics['monthly_amount'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-yen-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                承認待ち
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pendingQuotations">
                                {{ $statistics['pending_count'] ?? 0 }}件
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                成約率
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="conversionRate">
                                {{ number_format($statistics['conversion_rate'] ?? 0, 1) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 検索・フィルター -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-search me-2"></i>検索・フィルター
            </h6>
        </div>
        <div class="card-body">
            <form id="searchForm" method="GET" action="{{ route('quotations.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">見積番号・顧客名検索</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="見積番号または顧客名を入力">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="status" class="form-label">ステータス</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">全て</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>下書き</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>承認待ち</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>承認済み</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>送付済み</option>
                            <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>受注</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>失注</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>期限切れ</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="customer_id" class="form-label">顧客</label>
                        <select class="form-select" id="customer_id" name="customer_id">
                            <option value="">全て</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="date_from" class="form-label">作成日（開始）</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="date_to" class="form-label">作成日（終了）</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 見積一覧テーブル -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">見積一覧</h6>
            <div class="d-flex gap-2">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllBtn">
                        <i class="fas fa-check-square me-1"></i>全選択
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="bulkDeleteBtn" disabled>
                        <i class="fas fa-trash me-1"></i>一括削除
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" id="bulkApproveBtn" disabled>
                        <i class="fas fa-check me-1"></i>一括承認
                    </button>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportData('csv')">
                        <i class="fas fa-file-csv me-1"></i>CSV
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportData('excel')">
                        <i class="fas fa-file-excel me-1"></i>Excel
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="quotationsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>見積番号</th>
                            <th>顧客名</th>
                            <th>案件名</th>
                            <th>見積金額</th>
                            <th>ステータス</th>
                            <th>作成日</th>
                            <th>有効期限</th>
                            <th>担当者</th>
                            <th width="120">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotations as $quotation)
                            <tr data-id="{{ $quotation->id }}">
                                <td>
                                    <input type="checkbox" class="form-check-input row-checkbox" 
                                           value="{{ $quotation->id }}">
                                </td>
                                <td>
                                    <a href="{{ route('quotations.show', $quotation) }}" 
                                       class="text-decoration-none fw-bold">
                                        {{ $quotation->quotation_number }}
                                    </a>
                                </td>
                                <td>{{ $quotation->customer->name ?? '未設定' }}</td>
                                <td>{{ $quotation->project->name ?? $quotation->project_name ?? '未設定' }}</td>
                                <td class="text-end">
                                    <span class="fw-bold text-success">
                                        ¥{{ number_format($quotation->total_amount) }}
                                    </span>
                                </td>
                                <td>
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
                                    <span class="badge bg-{{ $statusClasses[$quotation->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$quotation->status] ?? $quotation->status }}
                                    </span>
                                </td>
                                <td>{{ $quotation->created_at->format('Y/m/d') }}</td>
                                <td>
                                    @if($quotation->valid_until)
                                        <span class="{{ \Carbon\Carbon::parse($quotation->valid_until)->isPast() ? 'text-danger' : '' }}">
                                            {{ \Carbon\Carbon::parse($quotation->valid_until)->format('Y/m/d') }}
                                        </span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </td>
                                <td>{{ $quotation->created_by_user->name ?? '未設定' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('quotations.show', $quotation) }}" 
                                           class="btn btn-outline-info btn-sm" title="詳細">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('quotations.edit', $quotation) }}" 
                                           class="btn btn-outline-primary btn-sm" title="編集">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="deleteQuotation({{ $quotation->id }})" title="削除">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        見積データがありません
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- ページネーション -->
            @if($quotations instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        {{ $quotations->firstItem() ?? 0 }}件目〜{{ $quotations->lastItem() ?? 0 }}件目を表示
                        （全{{ $quotations->total() }}件中）
                    </div>
                    {{ $quotations->appends(request()->query())->links() }}
                </div>
            @endif
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
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">削除</button>
            </div>
        </div>
    </div>
</div>

<!-- 一括操作確認モーダル -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionModalLabel">一括操作確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="bulkActionMessage"></p>
                <p class="text-warning"><strong>※ 選択した見積に対して一括で操作を実行します。</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" id="confirmBulkActionBtn">実行</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // チェックボックス全選択
    $('#selectAll').change(function() {
        $('.row-checkbox').prop('checked', this.checked);
        updateBulkActionButtons();
    });

    // 行チェックボックス
    $('.row-checkbox').change(function() {
        const allChecked = $('.row-checkbox').length === $('.row-checkbox:checked').length;
        $('#selectAll').prop('checked', allChecked);
        updateBulkActionButtons();
    });

    // 全選択ボタン
    $('#selectAllBtn').click(function() {
        const allChecked = $('.row-checkbox:checked').length === $('.row-checkbox').length;
        $('.row-checkbox').prop('checked', !allChecked);
        $('#selectAll').prop('checked', !allChecked);
        updateBulkActionButtons();
    });

    // 一括削除ボタン
    $('#bulkDeleteBtn').click(function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) return;

        $('#bulkActionMessage').text(`選択した${selectedIds.length}件の見積を削除します。`);
        $('#bulkActionModal').modal('show');
        
        $('#confirmBulkActionBtn').off('click').on('click', function() {
            bulkDelete(selectedIds);
        });
    });

    // 一括承認ボタン
    $('#bulkApproveBtn').click(function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) return;

        $('#bulkActionMessage').text(`選択した${selectedIds.length}件の見積を承認します。`);
        $('#bulkActionModal').modal('show');
        
        $('#confirmBulkActionBtn').off('click').on('click', function() {
            bulkApprove(selectedIds);
        });
    });
});

// 選択されたIDを取得
function getSelectedIds() {
    return $('.row-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
}

// 一括操作ボタンの有効/無効切り替え
function updateBulkActionButtons() {
    const selectedCount = $('.row-checkbox:checked').length;
    $('#bulkDeleteBtn, #bulkApproveBtn').prop('disabled', selectedCount === 0);
}

// 見積削除
function deleteQuotation(id) {
    $('#deleteModal').modal('show');
    
    $('#confirmDeleteBtn').off('click').on('click', function() {
        $.ajax({
            url: `/quotations/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#deleteModal').modal('hide');
                if (response.success) {
                    showAlert('success', '見積を削除しました。');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('error', response.message || '削除に失敗しました。');
                }
            },
            error: function(xhr) {
                $('#deleteModal').modal('hide');
                showAlert('error', 'エラーが発生しました。');
            }
        });
    });
}

// 一括削除
function bulkDelete(ids) {
    $.ajax({
        url: '/quotations/bulk-delete',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: { ids: ids },
        success: function(response) {
            $('#bulkActionModal').modal('hide');
            if (response.success) {
                showAlert('success', `${ids.length}件の見積を削除しました。`);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', response.message || '一括削除に失敗しました。');
            }
        },
        error: function(xhr) {
            $('#bulkActionModal').modal('hide');
            showAlert('error', 'エラーが発生しました。');
        }
    });
}

// 一括承認
function bulkApprove(ids) {
    $.ajax({
        url: '/quotations/bulk-approve',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: { ids: ids },
        success: function(response) {
            $('#bulkActionModal').modal('hide');
            if (response.success) {
                showAlert('success', `${ids.length}件の見積を承認しました。`);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', response.message || '一括承認に失敗しました。');
            }
        },
        error: function(xhr) {
            $('#bulkActionModal').modal('hide');
            showAlert('error', 'エラーが発生しました。');
        }
    });
}

// データエクスポート
function exportData(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    
    const url = `{{ route('quotations.index') }}?${params.toString()}`;
    window.open(url, '_blank');
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

// 統計情報のリアルタイム更新
function updateStatistics() {
    $.ajax({
        url: '/quotations/statistics',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#monthlyQuotations').text(`${response.data.monthly_count}件`);
                $('#monthlyAmount').text(`¥${response.data.monthly_amount.toLocaleString()}`);
                $('#pendingQuotations').text(`${response.data.pending_count}件`);
                $('#conversionRate').text(`${response.data.conversion_rate.toFixed(1)}%`);
            }
        }
    });
}

// 5分ごとに統計情報を更新
setInterval(updateStatistics, 5 * 60 * 1000);
</script>
@endpush

@push('styles')
<style>
.table th {
    background-color: #f8f9fc;
    border-color: #e3e6f0;
    font-weight: 600;
    white-space: nowrap;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 0.25rem;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
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
}

@media print {
    .btn, .card-header, .pagination {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 0.8rem;
    }
}
</style>
@endpush
