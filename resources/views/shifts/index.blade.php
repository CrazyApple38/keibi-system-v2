@extends('layouts.app')

@section('title', 'シフト管理')

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
                            <li class="breadcrumb-item active">シフト管理</li>
                        </ol>
                    </nav>
                    <h2 class="mb-1">
                        <i class="bi bi-calendar3 me-2"></i>
                        シフト管理
                    </h2>
                    <p class="text-muted mb-0">警備員シフトの一覧表示・作成・管理</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('shifts.calendar') }}" class="btn btn-outline-info">
                        <i class="bi bi-calendar-month me-1"></i>
                        カレンダー表示
                    </a>
                    <button class="btn btn-outline-primary" id="exportShifts">
                        <i class="bi bi-download me-1"></i>
                        エクスポート
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-plus me-1"></i>
                            新規作成
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('shifts.create') }}">
                                <i class="bi bi-calendar-plus me-2"></i>単発シフト作成
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('shifts.create.recurring') }}">
                                <i class="bi bi-arrow-repeat me-2"></i>定期シフト作成
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('shifts.optimize') }}">
                                <i class="bi bi-cpu me-2"></i>自動最適化
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
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">開始日</label>
                            <input type="date" class="form-control" name="start_date" 
                                   value="{{ request('start_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">終了日</label>
                            <input type="date" class="form-control" name="end_date" 
                                   value="{{ request('end_date', date('Y-m-d', strtotime('+7 days'))) }}">
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
                                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>予定</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>実行中</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>完了</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>キャンセル</option>
                            </select>
                        </div>
                        
                        <!-- 警備員 -->
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
                                <button type="button" class="btn btn-outline-warning" id="conflictCheck">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    競合チェック
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
                    <i class="bi bi-calendar-check display-6 text-primary mb-2"></i>
                    <h4 class="mb-1" id="totalShifts">{{ $shifts->count() ?? 0 }}</h4>
                    <small class="text-muted">総シフト数</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-people display-6 text-success mb-2"></i>
                    <h4 class="mb-1" id="assignedGuards">0</h4>
                    <small class="text-muted">配置済み警備員</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-exclamation-triangle display-6 text-warning mb-2"></i>
                    <h4 class="mb-1" id="unassignedShifts">0</h4>
                    <small class="text-muted">未配置シフト</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-clock display-6 text-info mb-2"></i>
                    <h4 class="mb-1" id="totalHours">0</h4>
                    <small class="text-muted">総勤務時間</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 表示オプション -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="viewMode" id="listView" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="listView">
                            <i class="bi bi-list-ul me-1"></i>リスト表示
                        </label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="gridView" autocomplete="off">
                        <label class="btn btn-outline-primary" for="gridView">
                            <i class="bi bi-grid me-1"></i>グリッド表示
                        </label>
                        
                        <input type="radio" class="btn-check" name="viewMode" id="timelineView" autocomplete="off">
                        <label class="btn btn-outline-primary" for="timelineView">
                            <i class="bi bi-clock-history me-1"></i>タイムライン
                        </label>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="showConflicts">
                        <label class="form-check-label" for="showConflicts">競合表示</label>
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center">
                        <label class="me-2">表示件数:</label>
                        <select class="form-select form-select-sm" style="width: auto;" id="perPage">
                            <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25件</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50件</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100件</option>
                        </select>
                    </div>
                    
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-1"></i>
                            一括操作
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="bulkAssign()">
                                <i class="bi bi-person-plus me-2"></i>一括配置
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="bulkCancel()">
                                <i class="bi bi-x-circle me-2"></i>一括キャンセル
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="bulkCopy()">
                                <i class="bi bi-copy me-2"></i>一括複製
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- シフト一覧テーブル -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list me-2"></i>
                            シフト一覧
                        </h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">
                                全選択
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <!-- リスト表示 -->
                    <div id="listViewContent">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 table-sortable" id="shiftsTable">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" class="form-check-input" id="selectAllHeader">
                                        </th>
                                        <th data-sort="start_date">
                                            <i class="bi bi-sort-alpha-down me-1"></i>
                                            日付
                                        </th>
                                        <th data-sort="start_time">時間</th>
                                        <th data-sort="project_name">プロジェクト</th>
                                        <th>配置警備員</th>
                                        <th data-sort="required_guards">必要人数</th>
                                        <th data-sort="status">ステータス</th>
                                        <th>勤務時間</th>
                                        <th>費用</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($shifts ?? [] as $shift)
                                    <tr class="shift-row {{ $shift->hasConflicts() ? 'table-warning' : '' }}" data-shift-id="{{ $shift->id }}">
                                        <td>
                                            <input type="checkbox" class="form-check-input shift-checkbox" value="{{ $shift->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <strong>{{ $shift->start_date ? $shift->start_date->format('n/j') : '未設定' }}</strong>
                                                <small class="text-muted">{{ $shift->start_date ? $shift->start_date->format('(D)') : '' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span>{{ $shift->start_time }} - {{ $shift->end_time }}</span>
                                                <small class="text-muted">{{ $shift->getDurationText() }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <strong>{{ $shift->project->name ?? '未設定' }}</strong>
                                                <small class="text-muted">{{ $shift->project->location ?? '' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @if($shift->assignedGuards && $shift->assignedGuards->count() > 0)
                                                    @foreach($shift->assignedGuards->take(3) as $guard)
                                                        <span class="badge bg-success">{{ $guard->name }}</span>
                                                    @endforeach
                                                    @if($shift->assignedGuards->count() > 3)
                                                        <span class="badge bg-secondary">+{{ $shift->assignedGuards->count() - 3 }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">未配置</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $shift->assignedGuards->count() ?? 0 }}/{{ $shift->required_guards ?? 0 }}</span>
                                                @if(($shift->assignedGuards->count() ?? 0) < ($shift->required_guards ?? 0))
                                                    <i class="bi bi-exclamation-triangle text-warning" title="人員不足"></i>
                                                @elseif(($shift->assignedGuards->count() ?? 0) == ($shift->required_guards ?? 0))
                                                    <i class="bi bi-check-circle text-success" title="配置完了"></i>
                                                @else
                                                    <i class="bi bi-person-plus text-info" title="過剰配置"></i>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $shift->getStatusColor() }}">
                                                {{ $shift->getStatusText() }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $shift->getDurationHours() }}h</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <strong>¥{{ number_format($shift->calculateTotalCost()) }}</strong>
                                                <small class="text-muted">¥{{ number_format($shift->calculateHourlyCost()) }}/h</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('shifts.show', $shift) }}" 
                                                   class="btn btn-sm btn-outline-info" title="詳細表示">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('shifts.edit', $shift) }}" 
                                                   class="btn btn-sm btn-outline-warning" title="編集">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="assignGuards({{ $shift->id }})" title="警備員配置">
                                                    <i class="bi bi-person-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteShift({{ $shift->id }})" title="削除">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <i class="bi bi-calendar-x display-1 text-muted"></i>
                                            <div class="mt-3">
                                                <h5 class="text-muted">シフトが登録されていません</h5>
                                                <p class="text-muted">新しいシフトを作成してください。</p>
                                                <a href="{{ route('shifts.create') }}" class="btn btn-primary">
                                                    <i class="bi bi-calendar-plus me-1"></i>
                                                    シフトを作成
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- グリッド表示 -->
                    <div id="gridViewContent" class="d-none p-3">
                        <div class="row" id="shiftsGrid">
                            @foreach($shifts ?? [] as $shift)
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card h-100 shift-card {{ $shift->hasConflicts() ? 'border-warning' : '' }}" data-shift-id="{{ $shift->id }}">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $shift->start_date ? $shift->start_date->format('n/j') : '未設定' }}</strong>
                                                <small class="text-muted ms-1">{{ $shift->start_date ? $shift->start_date->format('(D)') : '' }}</small>
                                            </div>
                                            <input type="checkbox" class="form-check-input shift-checkbox" value="{{ $shift->id }}">
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $shift->project->name ?? '未設定' }}</h6>
                                        <div class="mb-2">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $shift->start_time }} - {{ $shift->end_time }}
                                            <small class="text-muted">({{ $shift->getDurationText() }})</small>
                                        </div>
                                        <div class="mb-2">
                                            <i class="bi bi-people me-1"></i>
                                            {{ $shift->assignedGuards->count() ?? 0 }}/{{ $shift->required_guards ?? 0 }}名
                                        </div>
                                        <div class="mb-2">
                                            <span class="badge bg-{{ $shift->getStatusColor() }}">
                                                {{ $shift->getStatusText() }}
                                            </span>
                                        </div>
                                        <div class="mt-auto">
                                            <strong>¥{{ number_format($shift->calculateTotalCost()) }}</strong>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="btn-group w-100" role="group">
                                            <a href="{{ route('shifts.show', $shift) }}" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('shifts.edit', $shift) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="assignGuards({{ $shift->id }})">
                                                <i class="bi bi-person-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- タイムライン表示 -->
                    <div id="timelineViewContent" class="d-none p-3">
                        <div id="timelineContainer">
                            <!-- タイムラインはJavaScriptで動的に生成 -->
                        </div>
                    </div>
                </div>
                
                @if(isset($shifts) && $shifts->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            {{ $shifts->firstItem() }}～{{ $shifts->lastItem() }}件 / {{ $shifts->total() }}件中
                        </div>
                        {{ $shifts->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 警備員配置モーダル -->
<div class="modal fade" id="assignGuardsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">警備員配置</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="assignGuardsContent">
                    <!-- コンテンツはJavaScriptで動的に読み込み -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="saveGuardAssignments()">配置を保存</button>
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
    
    .shift-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .shift-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .shift-row.table-warning {
        background-color: rgba(255, 193, 7, 0.15) !important;
    }
    
    .border-warning {
        border-color: #ffc107 !important;
        border-width: 2px !important;
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
        
        .card-body {
            padding: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // 統計情報の更新
        updateStatistics();
        
        // 表示モード切り替え
        $('input[name="viewMode"]').change(function() {
            const mode = $(this).attr('id');
            switchViewMode(mode);
        });
        
        // 検索フォーム送信
        $('#searchForm').on('submit', function(e) {
            e.preventDefault();
            performSearch();
        });
        
        // リセットボタン
        $('#resetSearch').click(function() {
            $('#searchForm')[0].reset();
            window.location.href = '{{ route("shifts.index") }}';
        });
        
        // 競合チェック
        $('#conflictCheck').click(function() {
            checkConflicts();
        });
        
        // 表示件数変更
        $('#perPage').change(function() {
            updateUrlParameter('per_page', $(this).val());
        });
        
        // 全選択
        $('#selectAll, #selectAllHeader').change(function() {
            const isChecked = $(this).is(':checked');
            $('.shift-checkbox').prop('checked', isChecked);
            updateBulkActionButtons();
        });
        
        // 個別選択
        $(document).on('change', '.shift-checkbox', function() {
            updateSelectAllState();
            updateBulkActionButtons();
        });
        
        // 競合表示切り替え
        $('#showConflicts').change(function() {
            if ($(this).is(':checked')) {
                $('.shift-row').each(function() {
                    if ($(this).hasClass('table-warning')) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            } else {
                $('.shift-row').show();
            }
        });
        
        // エクスポートボタン
        $('#exportShifts').click(function() {
            const params = new URLSearchParams($('#searchForm').serialize());
            window.open(`{{ route('shifts.export') }}?${params.toString()}`, '_blank');
        });
    });
    
    // 表示モード切り替え
    function switchViewMode(mode) {
        // 全ての表示コンテンツを非表示
        $('#listViewContent, #gridViewContent, #timelineViewContent').addClass('d-none');
        
        // 選択されたモードを表示
        switch(mode) {
            case 'listView':
                $('#listViewContent').removeClass('d-none');
                break;
            case 'gridView':
                $('#gridViewContent').removeClass('d-none');
                break;
            case 'timelineView':
                $('#timelineViewContent').removeClass('d-none');
                loadTimelineView();
                break;
        }
    }
    
    // タイムライン表示読み込み
    function loadTimelineView() {
        // タイムライン表示の実装
        console.log('タイムライン表示を読み込み中...');
    }
    
    // 検索実行
    function performSearch() {
        const formData = $('#searchForm').serialize();
        window.location.href = `{{ route('shifts.index') }}?${formData}`;
    }
    
    // 統計情報更新
    function updateStatistics() {
        $.get('{{ route("shifts.stats") }}')
            .done(function(data) {
                $('#totalShifts').text(data.total || 0);
                $('#assignedGuards').text(data.assigned_guards || 0);
                $('#unassignedShifts').text(data.unassigned || 0);
                $('#totalHours').text(data.total_hours || 0);
            })
            .fail(function() {
                console.error('統計情報の取得に失敗しました');
            });
    }
    
    // 競合チェック
    function checkConflicts() {
        $.get('{{ route("shifts.conflicts") }}')
            .done(function(conflicts) {
                if (conflicts.length === 0) {
                    showSuccessMessage('競合は見つかりませんでした');
                } else {
                    let message = `${conflicts.length}件の競合が見つかりました：\n`;
                    conflicts.forEach(conflict => {
                        message += `- ${conflict.message}\n`;
                    });
                    alert(message);
                }
            })
            .fail(function() {
                showErrorMessage('競合チェックに失敗しました');
            });
    }
    
    // 警備員配置
    function assignGuards(shiftId) {
        $.get(`{{ route('shifts.assign.form', '') }}/${shiftId}`)
            .done(function(html) {
                $('#assignGuardsContent').html(html);
                $('#assignGuardsModal').modal('show');
            })
            .fail(function() {
                showErrorMessage('警備員配置フォームの読み込みに失敗しました');
            });
    }
    
    // 警備員配置保存
    function saveGuardAssignments() {
        const shiftId = $('#assignGuardsModal').data('shift-id');
        const formData = $('#assignGuardsForm').serialize();
        
        $.post(`{{ route('shifts.assign.save', '') }}/${shiftId}`, formData)
            .done(function(response) {
                showSuccessMessage('警備員の配置を保存しました');
                $('#assignGuardsModal').modal('hide');
                location.reload();
            })
            .fail(function() {
                showErrorMessage('警備員配置の保存に失敗しました');
            });
    }
    
    // シフト削除
    function deleteShift(shiftId) {
        if (confirm('このシフトを削除してもよろしいですか？\n関連する勤怠記録も削除される可能性があります。')) {
            $.ajax({
                url: `{{ route('shifts.destroy', '') }}/${shiftId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showSuccessMessage('シフトを削除しました');
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    showErrorMessage('シフトの削除に失敗しました');
                }
            });
        }
    }
    
    // 一括配置
    function bulkAssign() {
        const selectedShifts = $('.shift-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selectedShifts.length === 0) {
            alert('シフトを選択してください');
            return;
        }
        
        // 一括配置モーダルを表示（実装省略）
        console.log('一括配置:', selectedShifts);
    }
    
    // 一括キャンセル
    function bulkCancel() {
        const selectedShifts = $('.shift-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selectedShifts.length === 0) {
            alert('シフトを選択してください');
            return;
        }
        
        if (confirm(`選択された${selectedShifts.length}件のシフトをキャンセルしますか？`)) {
            $.post('{{ route("shifts.bulk.cancel") }}', {
                shift_ids: selectedShifts,
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                showSuccessMessage('シフトをキャンセルしました');
                location.reload();
            })
            .fail(function() {
                showErrorMessage('一括キャンセルに失敗しました');
            });
        }
    }
    
    // 一括複製
    function bulkCopy() {
        const selectedShifts = $('.shift-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selectedShifts.length === 0) {
            alert('シフトを選択してください');
            return;
        }
        
        // 複製日付選択モーダルを表示（実装省略）
        console.log('一括複製:', selectedShifts);
    }
    
    // 全選択状態更新
    function updateSelectAllState() {
        const totalCheckboxes = $('.shift-checkbox').length;
        const checkedCheckboxes = $('.shift-checkbox:checked').length;
        
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
        const selectedCount = $('.shift-checkbox:checked').length;
        // 一括操作ボタンの有効/無効を切り替え（実装省略）
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