@extends('layouts.app')

@section('title', '勤怠管理')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
                            <li class="breadcrumb-item active">勤怠管理</li>
                        </ol>
                    </nav>
                    <h2 class="mb-1">
                        <i class="bi bi-clock me-2"></i>
                        勤怠管理
                    </h2>
                    <p class="text-muted mb-0">警備員の出退勤記録・承認・統計管理</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success" id="bulkApprove">
                        <i class="bi bi-check-all me-1"></i>
                        一括承認
                    </button>
                    <button class="btn btn-outline-primary" id="exportAttendances">
                        <i class="bi bi-download me-1"></i>
                        エクスポート
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-plus me-1"></i>
                            新規作成
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('attendances.create') }}">
                                <i class="bi bi-plus-circle me-2"></i>手動記録
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="bulkClockIn()">
                                <i class="bi bi-clock me-2"></i>一括出勤
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('attendances.import') }}">
                                <i class="bi bi-upload me-2"></i>CSV一括登録
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 検索・フィルターセクション -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="searchForm" class="row g-3">
                        <!-- 日付範囲 -->
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">開始日</label>
                            <input type="date" class="form-control" name="start_date" 
                                   value="{{ request('start_date', date('Y-m-d', strtotime('-7 days'))) }}">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">終了日</label>
                            <input type="date" class="form-control" name="end_date" 
                                   value="{{ request('end_date', date('Y-m-d')) }}">
                        </div>
                        
                        <!-- 警備員検索 -->
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">警備員</label>
                            <select class="form-select" name="guard_id">
                                <option value="">全て</option>
                                @foreach($guards ?? [] as $guard)
                                    <option value="{{ $guard->id }}" {{ request('guard_id') == $guard->id ? 'selected' : '' }}>
                                        {{ $guard->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- プロジェクト -->
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">プロジェクト</label>
                            <select class="form-select" name="project_id">
                                <option value="">全て</option>
                                @foreach($projects ?? [] as $project)
                                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- ステータス -->
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">ステータス</label>
                            <select class="form-select" name="status">
                                <option value="">全て</option>
                                <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>出勤</option>
                                <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>欠勤</option>
                                <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>遅刻</option>
                                <option value="early_leave" {{ request('status') === 'early_leave' ? 'selected' : '' }}>早退</option>
                                <option value="overtime" {{ request('status') === 'overtime' ? 'selected' : '' }}>残業</option>
                            </select>
                        </div>
                        
                        <!-- 承認状態 -->
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">承認状態</label>
                            <select class="form-select" name="approval_status">
                                <option value="">全て</option>
                                <option value="pending" {{ request('approval_status') === 'pending' ? 'selected' : '' }}>承認待ち</option>
                                <option value="approved" {{ request('approval_status') === 'approved' ? 'selected' : '' }}>承認済み</option>
                                <option value="rejected" {{ request('approval_status') === 'rejected' ? 'selected' : '' }}>却下</option>
                            </select>
                        </div>
                        
                        <!-- 検索・リセットボタン -->
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>
                                    検索
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="resetSearch">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    リセット
                                </button>
                                <button type="button" class="btn btn-outline-info" id="quickFilters">
                                    <i class="bi bi-funnel me-1"></i>
                                    クイックフィルター
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 統計情報セクション -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-people display-6 text-success mb-2"></i>
                    <h4 class="mb-1" id="presentCount">{{ $statistics['present'] ?? 0 }}</h4>
                    <small class="text-muted">出勤数</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-person-x display-6 text-danger mb-2"></i>
                    <h4 class="mb-1" id="absentCount">{{ $statistics['absent'] ?? 0 }}</h4>
                    <small class="text-muted">欠勤数</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-clock-history display-6 text-warning mb-2"></i>
                    <h4 class="mb-1" id="pendingApproval">{{ $statistics['pending'] ?? 0 }}</h4>
                    <small class="text-muted">承認待ち</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-stopwatch display-6 text-info mb-2"></i>
                    <h4 class="mb-1" id="totalHours">{{ $statistics['total_hours'] ?? 0 }}</h4>
                    <small class="text-muted">総勤務時間</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- クイックフィルターボタン -->
    <div class="row mb-3" id="quickFilterButtons" style="display: none;">
        <div class="col-12">
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-primary btn-sm" onclick="applyQuickFilter('today')">今日</button>
                <button class="btn btn-outline-primary btn-sm" onclick="applyQuickFilter('yesterday')">昨日</button>
                <button class="btn btn-outline-primary btn-sm" onclick="applyQuickFilter('thisWeek')">今週</button>
                <button class="btn btn-outline-primary btn-sm" onclick="applyQuickFilter('lastWeek')">先週</button>
                <button class="btn btn-outline-primary btn-sm" onclick="applyQuickFilter('thisMonth')">今月</button>
                <button class="btn btn-outline-warning btn-sm" onclick="applyQuickFilter('pendingApproval')">承認待ち</button>
                <button class="btn btn-outline-danger btn-sm" onclick="applyQuickFilter('absent')">欠勤のみ</button>
                <button class="btn btn-outline-info btn-sm" onclick="applyQuickFilter('overtime')">残業のみ</button>
            </div>
        </div>
    </div>
    
    <!-- 勤怠記録一覧テーブル -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list me-2"></i>
                            勤怠記録一覧
                        </h5>
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label" for="selectAll">全選択</label>
                            </div>
                            <div class="d-flex align-items-center">
                                <label class="me-2">表示件数:</label>
                                <select class="form-select form-select-sm" style="width: auto;" id="perPage">
                                    <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25件</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50件</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100件</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-sortable" id="attendancesTable">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" class="form-check-input" id="selectAllHeader">
                                    </th>
                                    <th data-sort="date">
                                        <i class="bi bi-sort-alpha-down me-1"></i>
                                        日付
                                    </th>
                                    <th data-sort="guard_name">警備員</th>
                                    <th data-sort="project_name">プロジェクト</th>
                                    <th data-sort="check_in_time">出勤時間</th>
                                    <th data-sort="check_out_time">退勤時間</th>
                                    <th data-sort="actual_hours">実働時間</th>
                                    <th data-sort="status">ステータス</th>
                                    <th data-sort="approval_status">承認状態</th>
                                    <th>備考</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances ?? [] as $attendance)
                                <tr class="attendance-row" data-attendance-id="{{ $attendance->id }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input attendance-checkbox" value="{{ $attendance->id }}">
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $attendance->date ? $attendance->date->format('n/j') : '未設定' }}</strong>
                                            <small class="text-muted">{{ $attendance->date ? $attendance->date->format('(D)') : '' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($attendance->guard->profile_photo)
                                                <img src="{{ Storage::url($attendance->guard->profile_photo) }}" 
                                                     class="rounded-circle me-2" width="30" height="30" 
                                                     style="object-fit: cover;" alt="プロフィール">
                                            @else
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 30px; height: 30px;">
                                                    <span class="text-white fw-bold small">{{ mb_substr($attendance->guard->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $attendance->guard->name }}</div>
                                                <small class="text-muted">{{ $attendance->guard->employee_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $attendance->shift->project->name ?? '未設定' }}</strong>
                                            <small class="text-muted">{{ $attendance->shift->project->location ?? '' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-' }}</span>
                                            @if($attendance->check_in_time && $attendance->shift)
                                                <small class="text-{{ $attendance->check_in_time <= $attendance->shift->start_time ? 'success' : 'warning' }}">
                                                    (予定: {{ $attendance->shift->start_time ?? '-' }})
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-' }}</span>
                                            @if($attendance->check_out_time && $attendance->shift)
                                                <small class="text-{{ $attendance->check_out_time >= $attendance->shift->end_time ? 'success' : 'warning' }}">
                                                    (予定: {{ $attendance->shift->end_time ?? '-' }})
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $attendance->actual_hours ? $attendance->actual_hours . 'h' : '-' }}</strong>
                                            @if($attendance->break_time)
                                                <small class="text-muted">休憩{{ $attendance->break_time }}分</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $attendance->getStatusColor() }}">
                                            {{ $attendance->getStatusText() }}
                                        </span>
                                        @if($attendance->overtime_hours && $attendance->overtime_hours > 0)
                                            <br><small class="text-info">残業{{ $attendance->overtime_hours }}h</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="badge bg-{{ $attendance->getApprovalStatusColor() }}">
                                                {{ $attendance->getApprovalStatusText() }}
                                            </span>
                                            @if($attendance->approved_by)
                                                <small class="text-muted">by {{ $attendance->approver->name }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($attendance->notes)
                                            <span class="text-truncate" style="max-width: 100px;" title="{{ $attendance->notes }}">
                                                {{ Str::limit($attendance->notes, 20) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('attendances.show', $attendance) }}" 
                                               class="btn btn-sm btn-outline-info" title="詳細表示">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('attendances.edit', $attendance) }}" 
                                               class="btn btn-sm btn-outline-warning" title="編集">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if($attendance->approval_status === 'pending')
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="approveAttendance({{ $attendance->id }})" title="承認">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="rejectAttendance({{ $attendance->id }})" title="却下">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5">
                                        <i class="bi bi-clock-history display-1 text-muted"></i>
                                        <div class="mt-3">
                                            <h5 class="text-muted">勤怠記録がありません</h5>
                                            <p class="text-muted">検索条件を変更するか、新しい記録を作成してください。</p>
                                            <a href="{{ route('attendances.create') }}" class="btn btn-primary">
                                                <i class="bi bi-plus-circle me-1"></i>
                                                勤怠記録を作成
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if(isset($attendances) && $attendances->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            {{ $attendances->firstItem() }}～{{ $attendances->lastItem() }}件 / {{ $attendances->total() }}件中
                        </div>
                        {{ $attendances->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 一括操作モーダル -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionTitle">一括操作</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="bulkActionContent">
                    <!-- 一括操作内容はJavaScriptで動的に設定 -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" id="executeBulkAction">実行</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table-sortable th[data-sort] {
        cursor: pointer;
        user-select: none;
        transition: background-color 0.2s;
    }
    
    .table-sortable th[data-sort]:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .table-sortable th.asc::after {
        content: " ↑";
        color: var(--bs-primary);
    }
    
    .table-sortable th.desc::after {
        content: " ↓";
        color: var(--bs-primary);
    }
    
    .attendance-row {
        transition: all 0.3s ease;
    }
    
    .attendance-row:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .attendance-row.selected {
        background-color: rgba(13, 110, 253, 0.1);
    }
    
    .text-truncate {
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .btn-group .btn {
        border-radius: 0;
    }
    
    .btn-group .btn:first-child {
        border-top-left-radius: 0.375rem;
        border-bottom-left-radius: 0.375rem;
    }
    
    .btn-group .btn:last-child {
        border-top-right-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }
        
        .d-flex.gap-2 {
            flex-wrap: wrap;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // 統計情報の更新
        updateStatistics();
        
        // 検索フォーム送信
        $('#searchForm').on('submit', function(e) {
            e.preventDefault();
            performSearch();
        });
        
        // リセットボタン
        $('#resetSearch').click(function() {
            $('#searchForm')[0].reset();
            // 日付をデフォルト値に設定
            $('input[name="start_date"]').val('{{ date("Y-m-d", strtotime("-7 days")) }}');
            $('input[name="end_date"]').val('{{ date("Y-m-d") }}');
            performSearch();
        });
        
        // クイックフィルター表示切り替え
        $('#quickFilters').click(function() {
            $('#quickFilterButtons').toggle();
        });
        
        // 表示件数変更
        $('#perPage').change(function() {
            updateUrlParameter('per_page', $(this).val());
        });
        
        // 全選択
        $('#selectAll, #selectAllHeader').change(function() {
            const isChecked = $(this).is(':checked');
            $('.attendance-checkbox').prop('checked', isChecked);
            updateBulkActionButtons();
            updateSelectedRows();
        });
        
        // 個別選択
        $(document).on('change', '.attendance-checkbox', function() {
            updateSelectAllState();
            updateBulkActionButtons();
            updateSelectedRows();
        });
        
        // 一括承認ボタン
        $('#bulkApprove').click(function() {
            bulkApproveAttendances();
        });
        
        // エクスポートボタン
        $('#exportAttendances').click(function() {
            const params = new URLSearchParams($('#searchForm').serialize());
            window.open(`{{ route('attendances.export') }}?${params.toString()}`, '_blank');
        });
        
        // 定期更新（5分間隔）
        setInterval(updateStatistics, 300000);
    });
    
    // 検索実行
    function performSearch() {
        const formData = $('#searchForm').serialize();
        window.location.href = `{{ route('attendances.index') }}?${formData}`;
    }
    
    // 統計情報更新
    function updateStatistics() {
        const params = $('#searchForm').serialize();
        
        $.get('{{ route("attendances.stats") }}?' + params)
            .done(function(data) {
                $('#presentCount').text(data.present || 0);
                $('#absentCount').text(data.absent || 0);
                $('#pendingApproval').text(data.pending || 0);
                $('#totalHours').text(data.total_hours || 0);
            })
            .fail(function() {
                console.error('統計情報の取得に失敗しました');
            });
    }
    
    // クイックフィルター適用
    function applyQuickFilter(filter) {
        const today = new Date();
        const form = $('#searchForm');
        
        // 全フィルターをリセット
        form[0].reset();
        
        switch(filter) {
            case 'today':
                $('input[name="start_date"]').val(today.toISOString().split('T')[0]);
                $('input[name="end_date"]').val(today.toISOString().split('T')[0]);
                break;
            case 'yesterday':
                const yesterday = new Date(today);
                yesterday.setDate(today.getDate() - 1);
                $('input[name="start_date"]').val(yesterday.toISOString().split('T')[0]);
                $('input[name="end_date"]').val(yesterday.toISOString().split('T')[0]);
                break;
            case 'thisWeek':
                const weekStart = new Date(today);
                weekStart.setDate(today.getDate() - today.getDay());
                $('input[name="start_date"]').val(weekStart.toISOString().split('T')[0]);
                $('input[name="end_date"]').val(today.toISOString().split('T')[0]);
                break;
            case 'lastWeek':
                const lastWeekEnd = new Date(today);
                lastWeekEnd.setDate(today.getDate() - today.getDay() - 1);
                const lastWeekStart = new Date(lastWeekEnd);
                lastWeekStart.setDate(lastWeekEnd.getDate() - 6);
                $('input[name="start_date"]').val(lastWeekStart.toISOString().split('T')[0]);
                $('input[name="end_date"]').val(lastWeekEnd.toISOString().split('T')[0]);
                break;
            case 'thisMonth':
                const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                $('input[name="start_date"]').val(monthStart.toISOString().split('T')[0]);
                $('input[name="end_date"]').val(today.toISOString().split('T')[0]);
                break;
            case 'pendingApproval':
                $('select[name="approval_status"]').val('pending');
                $('input[name="start_date"]').val('{{ date("Y-m-d", strtotime("-30 days")) }}');
                $('input[name="end_date"]').val('{{ date("Y-m-d") }}');
                break;
            case 'absent':
                $('select[name="status"]').val('absent');
                $('input[name="start_date"]').val('{{ date("Y-m-d", strtotime("-7 days")) }}');
                $('input[name="end_date"]').val('{{ date("Y-m-d") }}');
                break;
            case 'overtime':
                $('select[name="status"]').val('overtime');
                $('input[name="start_date"]').val('{{ date("Y-m-d", strtotime("-7 days")) }}');
                $('input[name="end_date"]').val('{{ date("Y-m-d") }}');
                break;
        }
        
        performSearch();
    }
    
    // 勤怠承認
    function approveAttendance(attendanceId) {
        if (confirm('この勤怠記録を承認しますか？')) {
            $.post(`{{ route('attendances.approve', '') }}/${attendanceId}`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                showSuccessMessage('勤怠記録を承認しました');
                setTimeout(() => location.reload(), 1500);
            })
            .fail(function(xhr) {
                showErrorMessage('承認処理に失敗しました');
            });
        }
    }
    
    // 勤怠却下
    function rejectAttendance(attendanceId) {
        const reason = prompt('却下理由を入力してください：');
        if (reason) {
            $.post(`{{ route('attendances.reject', '') }}/${attendanceId}`, {
                reason: reason,
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                showSuccessMessage('勤怠記録を却下しました');
                setTimeout(() => location.reload(), 1500);
            })
            .fail(function(xhr) {
                showErrorMessage('却下処理に失敗しました');
            });
        }
    }
    
    // 一括承認
    function bulkApproveAttendances() {
        const selectedAttendances = $('.attendance-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selectedAttendances.length === 0) {
            alert('勤怠記録を選択してください');
            return;
        }
        
        if (confirm(`選択された${selectedAttendances.length}件の勤怠記録を承認しますか？`)) {
            $.post('{{ route("attendances.bulk.approve") }}', {
                attendance_ids: selectedAttendances,
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                showSuccessMessage(`${selectedAttendances.length}件の勤怠記録を承認しました`);
                setTimeout(() => location.reload(), 1500);
            })
            .fail(function(xhr) {
                showErrorMessage('一括承認に失敗しました');
            });
        }
    }
    
    // 一括出勤
    function bulkClockIn() {
        // 一括出勤モーダル表示（実装省略）
        console.log('一括出勤機能');
    }
    
    // 全選択状態更新
    function updateSelectAllState() {
        const totalCheckboxes = $('.attendance-checkbox').length;
        const checkedCheckboxes = $('.attendance-checkbox:checked').length;
        
        if (checkedCheckboxes === 0) {
            $('#selectAll, #selectAllHeader').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#selectAll, #selectAllHeader').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#selectAll, #selectAllHeader').prop('indeterminate', true);
        }
    }
    
    // 一括操作ボタン更新
    function updateBulkActionButtons() {
        const selectedCount = $('.attendance-checkbox:checked').length;
        $('#bulkApprove').prop('disabled', selectedCount === 0);
    }
    
    // 選択行のハイライト更新
    function updateSelectedRows() {
        $('.attendance-row').removeClass('selected');
        $('.attendance-checkbox:checked').each(function() {
            $(this).closest('.attendance-row').addClass('selected');
        });
    }
    
    // テーブルソート
    function sortTable(column, order) {
        updateUrlParameter('sort', column);
        updateUrlParameter('order', order);
    }
    
    // URLパラメータ更新
    function updateUrlParameter(param, value) {
        const url = new URL(window.location);
        url.searchParams.set(param, value);
        window.location.href = url.toString();
    }
</script>
@endpush
@endsection