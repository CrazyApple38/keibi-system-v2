@extends('layouts.app')

@section('title', '契約管理')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-0">契約管理</h1>
                    <p class="text-muted mb-0">Contract Management</p>
                </div>
                <div class="d-flex gap-2">
                    @can('create', App\Models\Contract::class)
                        <a href="{{ route('contracts.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> 新規契約作成
                        </a>
                    @endcan
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> エクスポート
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportData('csv')">CSV形式</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('excel')">Excel形式</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('pdf')">PDF形式</a></li>
                        </ul>
                    </div>
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
                            <div class="small">有効契約数</div>
                            <div class="h4 mb-0" id="active-contracts">{{ $statistics['active_contracts'] ?? 0 }}</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-file-contract fa-2x"></i>
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
                            <div class="small">今月の新規契約</div>
                            <div class="h4 mb-0" id="new-contracts">{{ $statistics['new_contracts'] ?? 0 }}</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-plus-circle fa-2x"></i>
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
                            <div class="small">期限切れ間近</div>
                            <div class="h4 mb-0" id="expiring-contracts">{{ $statistics['expiring_contracts'] ?? 0 }}</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">総契約金額</div>
                            <div class="h4 mb-0" id="total-amount">¥{{ number_format($statistics['total_amount'] ?? 0) }}</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-yen-sign fa-2x"></i>
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
                               placeholder="契約番号、顧客名、案件名で検索">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">ステータス</label>
                        <select class="form-select" name="status">
                            <option value="">全て</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>下書き</option>
                            <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>承認待ち</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>承認済み</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>有効</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>完了</option>
                            <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>解約</option>
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
                        <label class="form-label">契約開始日</label>
                        <input type="date" class="form-control" name="start_date_from" value="{{ request('start_date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">契約終了日</label>
                        <input type="date" class="form-control" name="end_date_to" value="{{ request('end_date_to') }}">
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
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                                <i class="fas fa-times"></i> フィルタークリア
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="saveFilter()">
                                <i class="fas fa-save"></i> フィルター保存
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="loadSavedFilter()">
                                <i class="fas fa-folder-open"></i> 保存済みフィルター
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
                    <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('status_update')">
                        <i class="fas fa-edit"></i> ステータス更新
                    </button>
                    <button type="button" class="btn btn-sm btn-info" onclick="bulkAction('export')">
                        <i class="fas fa-download"></i> 一括エクスポート
                    </button>
                    @can('delete', App\Models\Contract::class)
                        <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                            <i class="fas fa-trash"></i> 一括削除
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- 契約一覧テーブル -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list"></i> 契約一覧
                    <span class="badge bg-secondary ms-2">{{ $contracts->total() }}件</span>
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
                            <li><label class="dropdown-item"><input type="checkbox" checked> 契約番号</label></li>
                            <li><label class="dropdown-item"><input type="checkbox" checked> 顧客名</label></li>
                            <li><label class="dropdown-item"><input type="checkbox" checked> 案件名</label></li>
                            <li><label class="dropdown-item"><input type="checkbox" checked> 契約期間</label></li>
                            <li><label class="dropdown-item"><input type="checkbox" checked> 金額</label></li>
                            <li><label class="dropdown-item"><input type="checkbox" checked> ステータス</label></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($contracts->count() > 0)
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
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'contract_number', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-white text-decoration-none">
                                        契約番号
                                        @if(request('sort') === 'contract_number')
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
                                <th>案件名</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'start_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-white text-decoration-none">
                                        契約期間
                                        @if(request('sort') === 'start_date')
                                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'total_amount', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-white text-decoration-none">
                                        契約金額
                                        @if(request('sort') === 'total_amount')
                                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>ステータス</th>
                                <th>更新日</th>
                                <th width="150">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contracts as $contract)
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $contract->id }}">
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $contract->contract_number }}</span>
                                        @if($contract->is_auto_renewal)
                                            <span class="badge bg-info ms-1" title="自動更新契約">自動更新</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="fw-bold">{{ $contract->customer->name }}</div>
                                                <small class="text-muted">{{ $contract->customer->company_type }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ Str::limit($contract->project->name, 30) }}</div>
                                            <small class="text-muted">{{ $contract->project->project_type }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $contract->start_date->format('Y/m/d') }}</div>
                                            <div class="text-muted">〜 {{ $contract->end_date->format('Y/m/d') }}</div>
                                            @if($contract->end_date->isPast())
                                                <span class="badge bg-danger">期限切れ</span>
                                            @elseif($contract->end_date->diffInDays() <= 30)
                                                <span class="badge bg-warning">期限間近</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">¥{{ number_format($contract->total_amount) }}</div>
                                        @if($contract->payment_terms)
                                            <small class="text-muted">{{ $contract->payment_terms }}</small>
                                        @endif
                                    </td>
                                    <td>
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
                                        <span class="badge {{ $config['class'] }}">
                                            <i class="{{ $config['icon'] }}"></i> {{ $config['text'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-muted">
                                            {{ $contract->updated_at->format('Y/m/d H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('contracts.show', $contract) }}" 
                                               class="btn btn-sm btn-outline-primary" title="詳細表示">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('update', $contract)
                                                <a href="{{ route('contracts.edit', $contract) }}" 
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
                                                    <li><a class="dropdown-item" href="#" onclick="downloadContract({{ $contract->id }})">
                                                        <i class="fas fa-download"></i> 契約書ダウンロード
                                                    </a></li>
                                                    @if($contract->status === 'active' && $contract->is_auto_renewal)
                                                        <li><a class="dropdown-item" href="#" onclick="renewContract({{ $contract->id }})">
                                                            <i class="fas fa-redo"></i> 契約更新
                                                        </a></li>
                                                    @endif
                                                    @if($contract->status === 'active')
                                                        <li><a class="dropdown-item text-warning" href="#" onclick="terminateContract({{ $contract->id }})">
                                                            <i class="fas fa-ban"></i> 契約解約
                                                        </a></li>
                                                    @endif
                                                    @can('delete', $contract)
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteContract({{ $contract->id }})">
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
                                {{ $contracts->firstItem() }}〜{{ $contracts->lastItem() }}件目 
                                (全{{ $contracts->total() }}件中)
                            </small>
                        </div>
                        <div>
                            {{ $contracts->links() }}
                        </div>
                    </div>
                </div>
            @else
                <!-- データが無い場合 -->
                <div class="text-center py-5">
                    <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">契約データがありません</h5>
                    <p class="text-muted">検索条件を変更するか、新しい契約を作成してください。</p>
                    @can('create', App\Models\Contract::class)
                        <a href="{{ route('contracts.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> 新規契約作成
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
                <div id="bulk-status-update" style="display: none;">
                    <h6>ステータス一括更新</h6>
                    <select class="form-select" id="bulk-status">
                        <option value="">ステータスを選択</option>
                        <option value="under_review">承認待ち</option>
                        <option value="approved">承認済み</option>
                        <option value="active">有効</option>
                        <option value="completed">完了</option>
                        <option value="terminated">解約</option>
                    </select>
                </div>
                <div id="bulk-export" style="display: none;">
                    <h6>エクスポート形式選択</h6>
                    <select class="form-select" id="export-format">
                        <option value="csv">CSV形式</option>
                        <option value="excel">Excel形式</option>
                        <option value="pdf">PDF形式</option>
                    </select>
                </div>
                <div id="bulk-delete" style="display: none;">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        選択された契約を削除します。この操作は取り消せません。
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
    $('#bulk-status-update, #bulk-export, #bulk-delete').hide();
    
    // アクションに応じてモーダル内容を表示
    $('#bulk-' + action.replace('_', '-')).show();
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

    if (action === 'status_update') {
        data.status = $('#bulk-status').val();
        if (!data.status) {
            alert('ステータスを選択してください。');
            return;
        }
    } else if (action === 'export') {
        data.format = $('#export-format').val();
    }

    // Ajax送信
    $.ajax({
        url: '{{ route("contracts.bulk") }}',
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
    localStorage.setItem('contract_filter', filterData);
    alert('フィルター設定を保存しました。');
}

/**
 * 保存済みフィルター読み込み
 */
function loadSavedFilter() {
    const savedFilter = localStorage.getItem('contract_filter');
    if (savedFilter) {
        window.location.href = '{{ route("contracts.index") }}?' + savedFilter;
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
 * 契約書ダウンロード
 */
function downloadContract(contractId) {
    window.location.href = '{{ url("/contracts") }}/' + contractId + '/download';
}

/**
 * 契約更新
 */
function renewContract(contractId) {
    if (confirm('この契約を更新しますか？')) {
        $.ajax({
            url: '{{ url("/contracts") }}/' + contractId + '/renew',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert('契約を更新しました。');
                location.reload();
            },
            error: function(xhr) {
                alert('エラーが発生しました: ' + xhr.responseJSON.message);
            }
        });
    }
}

/**
 * 契約解約
 */
function terminateContract(contractId) {
    if (confirm('この契約を解約しますか？この操作は取り消せません。')) {
        $.ajax({
            url: '{{ url("/contracts") }}/' + contractId + '/terminate',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert('契約を解約しました。');
                location.reload();
            },
            error: function(xhr) {
                alert('エラーが発生しました: ' + xhr.responseJSON.message);
            }
        });
    }
}

/**
 * 契約削除
 */
function deleteContract(contractId) {
    if (confirm('この契約を削除しますか？この操作は取り消せません。')) {
        $.ajax({
            url: '{{ url("/contracts") }}/' + contractId,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert('契約を削除しました。');
                location.reload();
            },
            error: function(xhr) {
                alert('エラーが発生しました: ' + xhr.responseJSON.message);
            }
        });
    }
}

/**
 * 統計情報更新
 */
function updateStatistics() {
    $.ajax({
        url: '{{ route("contracts.statistics") }}',
        method: 'GET',
        success: function(data) {
            $('#active-contracts').text(data.active_contracts);
            $('#new-contracts').text(data.new_contracts);
            $('#expiring-contracts').text(data.expiring_contracts);
            $('#total-amount').text('¥' + new Intl.NumberFormat('ja-JP').format(data.total_amount));
        }
    });
}
</script>
@endpush