@extends('layouts.app')

@section('title', '日報管理')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-clipboard-list me-2"></i>日報管理
                    </h1>
                    <p class="mb-0 text-muted">警備業務の日報作成・管理・承認を行います</p>
                </div>
                <div>
                    <a href="{{ route('daily-reports.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>新規日報作成
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
                                今月の日報件数
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="monthlyReports">
                                {{ $statistics['total_reports'] ?? 0 }}件
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
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                事故・異常報告
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="incidentReports">
                                {{ $statistics['incident_count'] ?? 0 }}件
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                承認待ち
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pendingReports">
                                {{ $statistics['submitted_reports'] - $statistics['approved_reports'] ?? 0 }}件
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
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                報告品質スコア
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="qualityScore">
                                @php
                                    $avgQuality = collect($statistics['guard_performance'] ?? [])->avg('average_rating');
                                @endphp
                                {{ $avgQuality ? number_format($avgQuality, 1) : '0.0' }}点
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
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
            <form id="searchForm" method="GET" action="{{ route('daily-reports.index') }}">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label for="guard_id" class="form-label">警備員</label>
                        <select class="form-select" id="guard_id" name="guard_id">
                            <option value="">全て</option>
                            @foreach($guards ?? [] as $guard)
                                <option value="{{ $guard->id }}" {{ request('guard_id') == $guard->id ? 'selected' : '' }}>
                                    {{ $guard->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="project_id" class="form-label">プロジェクト</label>
                        <select class="form-select" id="project_id" name="project_id">
                            <option value="">全て</option>
                            @foreach($projects ?? [] as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="report_type" class="form-label">日報種別</label>
                        <select class="form-select" id="report_type" name="report_type">
                            <option value="">全て</option>
                            <option value="daily" {{ request('report_type') == 'daily' ? 'selected' : '' }}>日常警備</option>
                            <option value="incident" {{ request('report_type') == 'incident' ? 'selected' : '' }}>事故・異常</option>
                            <option value="maintenance" {{ request('report_type') == 'maintenance' ? 'selected' : '' }}>設備点検</option>
                            <option value="security_check" {{ request('report_type') == 'security_check' ? 'selected' : '' }}>警備点検</option>
                            <option value="patrol" {{ request('report_type') == 'patrol' ? 'selected' : '' }}>巡回報告</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="status" class="form-label">ステータス</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">全て</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>下書き</option>
                            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>提出済み</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>承認済み</option>
                            <option value="updated" {{ request('status') == 'updated' ? 'selected' : '' }}>更新済み</option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-3">
                        <label for="priority" class="form-label">重要度</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="">全て</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>低</option>
                            <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>通常</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>高</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>緊急</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="date_from" class="form-label">日報日（開始）</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="date_to" class="form-label">日報日（終了）</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('daily-reports.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="incidents_only" name="incidents_only" 
                                   {{ request('incidents_only') ? 'checked' : '' }}>
                            <label class="form-check-label" for="incidents_only">
                                事故・異常報告のみ表示
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="approval_pending" name="approval_status" 
                                   value="pending" {{ request('approval_status') == 'pending' ? 'checked' : '' }}>
                            <label class="form-check-label" for="approval_pending">
                                承認待ちのみ表示
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 日報一覧テーブル -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">日報一覧</h6>
            <div class="d-flex gap-2">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllBtn">
                        <i class="fas fa-check-square me-1"></i>全選択
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" id="bulkApproveBtn" disabled>
                        <i class="fas fa-check me-1"></i>一括承認
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="bulkDeleteBtn" disabled>
                        <i class="fas fa-trash me-1"></i>一括削除
                    </button>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportData('csv')">
                        <i class="fas fa-file-csv me-1"></i>CSV
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportData('excel')">
                        <i class="fas fa-file-excel me-1"></i>Excel
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="exportData('pdf')">
                        <i class="fas fa-file-pdf me-1"></i>PDF
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="reportsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>日報日</th>
                            <th>警備員</th>
                            <th>プロジェクト</th>
                            <th>種別</th>
                            <th>重要度</th>
                            <th>概要</th>
                            <th>ステータス</th>
                            <th>作成日</th>
                            <th>承認者</th>
                            <th width="120">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr data-id="{{ $report->id }}" class="{{ $report->has_incident ? 'table-warning' : '' }}">
                                <td>
                                    <input type="checkbox" class="form-check-input row-checkbox" 
                                           value="{{ $report->id }}">
                                </td>
                                <td>
                                    <span class="fw-bold">
                                        {{ \Carbon\Carbon::parse($report->report_date)->format('Y/m/d') }}
                                    </span>
                                    @if($report->has_incident)
                                        <i class="fas fa-exclamation-triangle text-danger ms-1" title="事故・異常あり"></i>
                                    @endif
                                    @if($report->has_equipment_issue)
                                        <i class="fas fa-tools text-warning ms-1" title="設備不具合あり"></i>
                                    @endif
                                    @if($report->has_safety_concern)
                                        <i class="fas fa-shield-alt text-info ms-1" title="安全上の懸念あり"></i>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('guards.show', $report->guard) }}" 
                                       class="text-decoration-none">
                                        {{ $report->guard->name ?? '未設定' }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('projects.show', $report->project) }}" 
                                       class="text-decoration-none">
                                        {{ $report->project->name ?? '未設定' }}
                                    </a>
                                </td>
                                <td>
                                    @php
                                        $typeLabels = [
                                            'daily' => '日常警備',
                                            'incident' => '事故・異常',
                                            'maintenance' => '設備点検',
                                            'security_check' => '警備点検',
                                            'patrol' => '巡回報告'
                                        ];
                                        $typeClasses = [
                                            'daily' => 'primary',
                                            'incident' => 'danger',
                                            'maintenance' => 'warning',
                                            'security_check' => 'info',
                                            'patrol' => 'secondary'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $typeClasses[$report->report_type] ?? 'secondary' }}">
                                        {{ $typeLabels[$report->report_type] ?? $report->report_type }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $priorityLabels = [
                                            'low' => '低',
                                            'normal' => '通常',
                                            'high' => '高',
                                            'urgent' => '緊急'
                                        ];
                                        $priorityClasses = [
                                            'low' => 'success',
                                            'normal' => 'primary',
                                            'high' => 'warning',
                                            'urgent' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $priorityClasses[$report->priority] ?? 'secondary' }}">
                                        {{ $priorityLabels[$report->priority] ?? $report->priority }}
                                    </span>
                                </td>
                                <td class="text-truncate-2" style="max-width: 200px;">
                                    {{ $report->summary }}
                                </td>
                                <td>
                                    @php
                                        $statusLabels = [
                                            'draft' => '下書き',
                                            'submitted' => '提出済み',
                                            'approved' => '承認済み',
                                            'updated' => '更新済み'
                                        ];
                                        $statusClasses = [
                                            'draft' => 'secondary',
                                            'submitted' => 'warning',
                                            'approved' => 'success',
                                            'updated' => 'info'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusClasses[$report->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$report->status] ?? $report->status }}
                                    </span>
                                </td>
                                <td>{{ $report->created_at->format('Y/m/d H:i') }}</td>
                                <td>
                                    @if($report->approved_at && $report->approver)
                                        <span class="text-success">{{ $report->approver->name }}</span>
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($report->approved_at)->format('m/d H:i') }}</small>
                                    @else
                                        <span class="text-muted">未承認</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('daily-reports.show', $report) }}" 
                                           class="btn btn-outline-info btn-sm" title="詳細">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($report->status !== 'approved')
                                            <a href="{{ route('daily-reports.edit', $report) }}" 
                                               class="btn btn-outline-primary btn-sm" title="編集">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if(in_array(auth()->user()->role, ['admin', 'manager']) && $report->status !== 'approved')
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    onclick="approveReport({{ $report->id }})" title="承認">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="deleteReport({{ $report->id }})" title="削除">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        日報データがありません
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- ページネーション -->
            @if($reports instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        {{ $reports->firstItem() ?? 0 }}件目〜{{ $reports->lastItem() ?? 0 }}件目を表示
                        （全{{ $reports->total() }}件中）
                    </div>
                    {{ $reports->appends(request()->query())->links() }}
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
                <h5 class="modal-title" id="deleteModalLabel">日報削除確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>この日報を削除してもよろしいですか？</p>
                <p class="text-danger"><strong>※ この操作は取り消せません。</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">削除</button>
            </div>
        </div>
    </div>
</div>

<!-- 承認確認モーダル -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">日報承認確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>この日報を承認してもよろしいですか？</p>
                <div class="mb-3">
                    <label for="approval_memo" class="form-label">承認コメント（任意）</label>
                    <textarea class="form-control" id="approval_memo" rows="3" 
                              placeholder="承認に関するコメントがあれば入力してください"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-success" id="confirmApproveBtn">承認</button>
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
                <p class="text-warning"><strong>※ 選択した日報に対して一括で操作を実行します。</strong></p>
                <div class="mb-3" id="bulkApprovalMemoContainer" style="display: none;">
                    <label for="bulk_approval_memo" class="form-label">承認コメント（任意）</label>
                    <textarea class="form-control" id="bulk_approval_memo" rows="3" 
                              placeholder="一括承認に関するコメントがあれば入力してください"></textarea>
                </div>
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

    // 一括承認ボタン
    $('#bulkApproveBtn').click(function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) return;

        $('#bulkActionMessage').text(`選択した${selectedIds.length}件の日報を承認します。`);
        $('#bulkApprovalMemoContainer').show();
        $('#bulkActionModal').modal('show');
        
        $('#confirmBulkActionBtn').off('click').on('click', function() {
            bulkApprove(selectedIds);
        });
    });

    // 一括削除ボタン
    $('#bulkDeleteBtn').click(function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) return;

        $('#bulkActionMessage').text(`選択した${selectedIds.length}件の日報を削除します。`);
        $('#bulkApprovalMemoContainer').hide();
        $('#bulkActionModal').modal('show');
        
        $('#confirmBulkActionBtn').off('click').on('click', function() {
            bulkDelete(selectedIds);
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

// 日報削除
function deleteReport(id) {
    $('#deleteModal').modal('show');
    
    $('#confirmDeleteBtn').off('click').on('click', function() {
        $.ajax({
            url: `/daily-reports/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#deleteModal').modal('hide');
                if (response.success) {
                    showAlert('success', '日報を削除しました。');
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

// 日報承認
function approveReport(id) {
    $('#approveModal').modal('show');
    
    $('#confirmApproveBtn').off('click').on('click', function() {
        const memo = $('#approval_memo').val();
        
        $.ajax({
            url: `/daily-reports/${id}/approve`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { approval_memo: memo },
            success: function(response) {
                $('#approveModal').modal('hide');
                if (response.success) {
                    showAlert('success', '日報を承認しました。');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('error', response.message || '承認に失敗しました。');
                }
            },
            error: function(xhr) {
                $('#approveModal').modal('hide');
                showAlert('error', 'エラーが発生しました。');
            }
        });
    });
}

// 一括削除
function bulkDelete(ids) {
    $.ajax({
        url: '/daily-reports/bulk-delete',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: { ids: ids },
        success: function(response) {
            $('#bulkActionModal').modal('hide');
            if (response.success) {
                showAlert('success', `${ids.length}件の日報を削除しました。`);
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
    const memo = $('#bulk_approval_memo').val();
    
    $.ajax({
        url: '/daily-reports/bulk-approve',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: { 
            ids: ids,
            approval_memo: memo
        },
        success: function(response) {
            $('#bulkActionModal').modal('hide');
            if (response.success) {
                showAlert('success', `${ids.length}件の日報を承認しました。`);
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
    
    const url = `{{ route('daily-reports.index') }}?${params.toString()}`;
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
        url: '/daily-reports/statistics',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#monthlyReports').text(`${response.data.total_reports}件`);
                $('#incidentReports').text(`${response.data.incident_count}件`);
                $('#pendingReports').text(`${response.data.submitted_reports - response.data.approved_reports}件`);
                
                const avgQuality = response.data.guard_performance.reduce((acc, curr) => acc + curr.average_rating, 0) / response.data.guard_performance.length;
                $('#qualityScore').text(`${avgQuality.toFixed(1)}点`);
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

.table-warning {
    background-color: #fff3cd !important;
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

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
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
    
    .text-truncate-2 {
        max-width: 150px !important;
    }
}

@media print {
    .btn, .card-header, .pagination, .modal {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 0.8rem;
    }
    
    .table-warning {
        background-color: #ffeb3b !important;
        -webkit-print-color-adjust: exact;
    }
}
</style>
@endpush
