@extends('layouts.app')

@section('title', '勤怠管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- ページヘッダー -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">勤怠管理</h1>
                    <p class="text-muted mb-0">出退勤記録の管理と承認を行います</p>
                </div>
                <div class="d-flex gap-2">
                    @can('create', App\Models\Attendance::class)
                        <a href="{{ route('attendances.create') }}" class="btn btn-primary">
                            <i class="fas fa-clock"></i> 新規勤怠記録
                        </a>
                    @endcan
                    <button type="button" class="btn btn-success" id="quickCheckInOut">
                        <i class="fas fa-stopwatch"></i> 簡易打刻
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> エクスポート
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportAttendances('csv')">CSV形式</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportAttendances('excel')">Excel形式</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportAttendances('pdf')">PDF形式</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 統計カード -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">本日の出勤者</h5>
                                    <h2 class="mb-0" id="todayAttendees">{{ $statistics['today_attendees'] ?? 0 }}</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-check fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">勤務中</h5>
                                    <h2 class="mb-0" id="currentWorking">{{ $statistics['current_working'] ?? 0 }}</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">要承認</h5>
                                    <h2 class="mb-0" id="pendingApproval">{{ $statistics['pending_approval'] ?? 0 }}</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-hourglass-half fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">異常・アラート</h5>
                                    <h2 class="mb-0" id="alerts">{{ $statistics['alerts'] ?? 0 }}</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 検索・フィルターカード -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-search"></i> 検索・フィルター
                        <button class="btn btn-sm btn-outline-secondary ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#searchFilters">
                            <i class="fas fa-filter"></i> 詳細フィルター
                        </button>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="searchForm" method="GET">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="search" class="form-label">検索キーワード</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="警備員名、現場名など">
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">開始日</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="{{ request('date_from', date('Y-m-01')) }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">終了日</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="{{ request('date_to', date('Y-m-d')) }}">
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">ステータス</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">すべて</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>承認待ち</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>承認済み</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>差し戻し</option>
                                    <option value="working" {{ request('status') === 'working' ? 'selected' : '' }}>勤務中</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> 検索
                                    </button>
                                    <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo"></i> リセット
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- 詳細フィルター（折りたたみ） -->
                        <div class="collapse mt-3" id="searchFilters">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="project_id" class="form-label">プロジェクト</label>
                                    <select class="form-select" id="project_id" name="project_id">
                                        <option value="">すべてのプロジェクト</option>
                                        @foreach($projects ?? [] as $project)
                                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="guard_id" class="form-label">警備員</label>
                                    <select class="form-select" id="guard_id" name="guard_id">
                                        <option value="">すべての警備員</option>
                                        @foreach($guards ?? [] as $guard)
                                            <option value="{{ $guard->id }}" {{ request('guard_id') == $guard->id ? 'selected' : '' }}>
                                                {{ $guard->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="overtime_only" class="form-label">表示条件</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="overtime_only" name="overtime_only" 
                                               value="1" {{ request('overtime_only') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="overtime_only">
                                            残業あり のみ
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="anomaly_only" name="anomaly_only" 
                                               value="1" {{ request('anomaly_only') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="anomaly_only">
                                            異常あり のみ
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="late_only" name="late_only" 
                                               value="1" {{ request('late_only') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="late_only">
                                            遅刻・早退 のみ
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 勤怠記録テーブル -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table"></i> 勤怠記録一覧
                        <span class="badge bg-secondary ms-2">{{ $attendances->total() ?? 0 }}件</span>
                    </h5>
                    <div class="d-flex gap-2">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="view_mode" id="table_view" value="table" checked>
                            <label class="btn btn-outline-primary btn-sm" for="table_view">
                                <i class="fas fa-table"></i> テーブル
                            </label>
                            <input type="radio" class="btn-check" name="view_mode" id="card_view" value="card">
                            <label class="btn btn-outline-primary btn-sm" for="card_view">
                                <i class="fas fa-th-large"></i> カード
                            </label>
                        </div>
                        @can('approve-attendances')
                            <button class="btn btn-success btn-sm" onclick="bulkApprove()">
                                <i class="fas fa-check"></i> 一括承認
                            </button>
                        @endcan
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- テーブル表示 -->
                    <div id="tableView" class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    @can('approve-attendances')
                                        <th width="40">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                    @endcan
                                    <th>日付</th>
                                    <th>警備員</th>
                                    <th>プロジェクト/現場</th>
                                    <th>出勤時間</th>
                                    <th>退勤時間</th>
                                    <th>勤務時間</th>
                                    <th>ステータス</th>
                                    <th>異常・アラート</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances ?? [] as $attendance)
                                    <tr class="attendance-row" data-id="{{ $attendance->id }}">
                                        @can('approve-attendances')
                                            <td>
                                                <input type="checkbox" class="form-check-input attendance-checkbox" 
                                                       value="{{ $attendance->id }}" name="attendance_ids[]">
                                            </td>
                                        @endcan
                                        <td>
                                            <strong>{{ $attendance->attendance_date ? $attendance->attendance_date->format('Y/m/d') : '未設定' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $attendance->attendance_date ? $attendance->attendance_date->format('(D)') : '' }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if(isset($attendance->guard->photo))
                                                    <img src="{{ Storage::url($attendance->guard->photo) }}" 
                                                         alt="{{ $attendance->guard->name }}" 
                                                         class="rounded-circle me-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $attendance->guard->name ?? '未設定' }}</strong>
                                                    <br>
                                                    <small class="text-muted">ID: {{ $attendance->guard->employee_id ?? '' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ $attendance->shift->project->name ?? '未設定' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $attendance->shift->location ?? '' }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="fw-bold">{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '未打刻' }}</span>
                                                @if($attendance->clock_in && method_exists($attendance, 'isLate') && $attendance->isLate())
                                                    <span class="badge bg-warning ms-2">遅刻</span>
                                                @endif
                                                @if($attendance->clock_in_photo ?? false)
                                                    <i class="fas fa-camera text-primary ms-1" title="写真あり"></i>
                                                @endif
                                                @if($attendance->clock_in_location ?? false)
                                                    <i class="fas fa-map-marker-alt text-success ms-1" title="GPS記録あり"></i>
                                                @endif
                                            </div>
                                            @if($attendance->clock_in_note ?? false)
                                                <small class="text-muted d-block">{{ Str::limit($attendance->clock_in_note, 30) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="fw-bold">{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '勤務中' }}</span>
                                                @if($attendance->clock_out && method_exists($attendance, 'isEarlyLeave') && $attendance->isEarlyLeave())
                                                    <span class="badge bg-warning ms-2">早退</span>
                                                @endif
                                                @if($attendance->clock_out_photo ?? false)
                                                    <i class="fas fa-camera text-primary ms-1" title="写真あり"></i>
                                                @endif
                                                @if($attendance->clock_out_location ?? false)
                                                    <i class="fas fa-map-marker-alt text-success ms-1" title="GPS記録あり"></i>
                                                @endif
                                            </div>
                                            @if($attendance->clock_out_note ?? false)
                                                <small class="text-muted d-block">{{ Str::limit($attendance->clock_out_note, 30) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attendance->total_work_hours ?? false)
                                                <span class="fw-bold">{{ number_format($attendance->total_work_hours, 2) }}時間</span>
                                                @if(($attendance->overtime_hours ?? 0) > 0)
                                                    <br>
                                                    <small class="text-warning">残業: {{ number_format($attendance->overtime_hours, 2) }}h</small>
                                                @endif
                                                @if(($attendance->break_hours ?? 0) > 0)
                                                    <br>
                                                    <small class="text-muted">休憩: {{ number_format($attendance->break_hours, 2) }}h</small>
                                                @endif
                                            @else
                                                <span class="text-muted">計算中</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($attendance->status ?? 'unknown')
                                                @case('pending')
                                                    <span class="badge bg-warning">承認待ち</span>
                                                    @break
                                                @case('approved')
                                                    <span class="badge bg-success">承認済み</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge bg-danger">差し戻し</span>
                                                    @break
                                                @case('working')
                                                    <span class="badge bg-primary">勤務中</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $attendance->status ?? '不明' }}</span>
                                            @endswitch
                                            @if($attendance->approved_by ?? false)
                                                <br>
                                                <small class="text-muted">承認者: {{ $attendance->approver->name ?? '' }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if(method_exists($attendance, 'hasAnomalies') && $attendance->hasAnomalies())
                                                <div class="text-danger">
                                                    @if(method_exists($attendance, 'isLate') && $attendance->isLate())
                                                        <i class="fas fa-clock" title="遅刻"></i>
                                                    @endif
                                                    @if(method_exists($attendance, 'isEarlyLeave') && $attendance->isEarlyLeave())
                                                        <i class="fas fa-door-open" title="早退"></i>
                                                    @endif
                                                    @if(!($attendance->clock_in_location ?? false))
                                                        <i class="fas fa-map-marker-alt" title="GPS記録なし"></i>
                                                    @endif
                                                    @if(($attendance->total_work_hours ?? 0) > 12)
                                                        <i class="fas fa-exclamation-triangle" title="長時間勤務"></i>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-success">
                                                    <i class="fas fa-check-circle"></i> 正常
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('attendances.show', $attendance) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="詳細表示">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @can('update', $attendance)
                                                    <a href="{{ route('attendances.edit', $attendance) }}" 
                                                       class="btn btn-sm btn-outline-success" title="編集">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('approve-attendances')
                                                    @if(($attendance->status ?? '') === 'pending')
                                                        <button class="btn btn-sm btn-outline-success" 
                                                                onclick="approveAttendance({{ $attendance->id }})" title="承認">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-warning" 
                                                                onclick="rejectAttendance({{ $attendance->id }})" title="差し戻し">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-2x mb-3"></i>
                                                <p>勤怠記録が見つかりません。</p>
                                                @can('create', App\Models\Attendance::class)
                                                    <a href="{{ route('attendances.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus"></i> 新規勤怠記録を作成
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- カード表示（非表示） -->
                    <div id="cardView" class="d-none p-3">
                        <div class="row">
                            @forelse($attendances ?? [] as $attendance)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100 attendance-card" data-id="{{ $attendance->id }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0">{{ $attendance->attendance_date ? $attendance->attendance_date->format('Y/m/d (D)') : '未設定' }}</h6>
                                                @switch($attendance->status ?? 'unknown')
                                                    @case('pending')
                                                        <span class="badge bg-warning">承認待ち</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge bg-success">承認済み</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge bg-danger">差し戻し</span>
                                                        @break
                                                    @case('working')
                                                        <span class="badge bg-primary">勤務中</span>
                                                        @break
                                                @endswitch
                                            </div>
                                            <div class="d-flex align-items-center mb-3">
                                                @if(isset($attendance->guard->photo))
                                                    <img src="{{ Storage::url($attendance->guard->photo) }}" 
                                                         alt="{{ $attendance->guard->name }}" 
                                                         class="rounded-circle me-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $attendance->guard->name ?? '未設定' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $attendance->shift->project->name ?? '未設定' }}</small>
                                                </div>
                                            </div>
                                            <div class="row text-center mb-3">
                                                <div class="col-6">
                                                    <div class="border-end">
                                                        <small class="text-muted d-block">出勤</small>
                                                        <strong>{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '未打刻' }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">退勤</small>
                                                    <strong>{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '勤務中' }}</strong>
                                                </div>
                                            </div>
                                            @if($attendance->total_work_hours ?? false)
                                                <div class="text-center mb-3">
                                                    <span class="badge bg-light text-dark">
                                                        勤務時間: {{ number_format($attendance->total_work_hours, 2) }}時間
                                                    </span>
                                                    @if(($attendance->overtime_hours ?? 0) > 0)
                                                        <span class="badge bg-warning">
                                                            残業: {{ number_format($attendance->overtime_hours, 2) }}h
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if(method_exists($attendance, 'hasAnomalies') && $attendance->hasAnomalies())
                                                <div class="alert alert-warning alert-sm mb-3">
                                                    <i class="fas fa-exclamation-triangle"></i> 異常あり
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <div class="btn-group w-100">
                                                <a href="{{ route('attendances.show', $attendance) }}" 
                                                   class="btn btn-sm btn-outline-primary">詳細</a>
                                                @can('update', $attendance)
                                                    <a href="{{ route('attendances.edit', $attendance) }}" 
                                                       class="btn btn-sm btn-outline-success">編集</a>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-2x mb-3"></i>
                                        <p>勤怠記録が見つかりません。</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- ページネーション -->
                @if(isset($attendances) && $attendances->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                {{ $attendances->firstItem() }} - {{ $attendances->lastItem() }} / {{ $attendances->total() }}件
                            </div>
                            {{ $attendances->appends(request()->query())->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 簡易打刻モーダル -->
<div class="modal fade" id="quickCheckInOutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">簡易打刻</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickCheckInOutForm">
                    <div class="mb-3">
                        <label for="quick_guard_id" class="form-label">警備員選択</label>
                        <select class="form-select" id="quick_guard_id" name="guard_id" required>
                            <option value="">警備員を選択してください</option>
                            @foreach($guards ?? [] as $guard)
                                <option value="{{ $guard->id }}">{{ $guard->name }} ({{ $guard->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quick_action" class="form-label">打刻種別</label>
                        <select class="form-select" id="quick_action" name="action" required>
                            <option value="">選択してください</option>
                            <option value="clock_in">出勤打刻</option>
                            <option value="clock_out">退勤打刻</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quick_note" class="form-label">メモ（任意）</label>
                        <textarea class="form-control" id="quick_note" name="note" rows="2" 
                                  placeholder="特記事項があれば入力してください"></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="use_gps" name="use_gps" checked>
                        <label class="form-check-label" for="use_gps">
                            GPS位置情報を記録する
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="submitQuickCheckInOut()">
                    <i class="fas fa-clock"></i> 打刻実行
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 承認・差し戻しモーダル -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalTitle">勤怠記録の承認・差し戻し</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="approvalForm">
                    <input type="hidden" id="approval_attendance_id" name="attendance_id">
                    <input type="hidden" id="approval_action" name="action">
                    
                    <div class="mb-3">
                        <label for="approval_note" class="form-label">コメント</label>
                        <textarea class="form-control" id="approval_note" name="note" rows="3" 
                                  placeholder="承認・差し戻しの理由やコメントを入力してください"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="submitApproval()" id="approvalSubmitBtn">
                    実行
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.attendance-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.attendance-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.alert-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
}

.table th {
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.attendance-row:hover {
    background-color: #f8f9fa;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

#selectAll:checked ~ .attendance-checkbox {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // 表示モード切り替え
    $('input[name="view_mode"]').change(function() {
        if ($(this).val() === 'card') {
            $('#tableView').addClass('d-none');
            $('#cardView').removeClass('d-none');
        } else {
            $('#cardView').addClass('d-none');
            $('#tableView').removeClass('d-none');
        }
    });

    // 全選択チェックボックス
    $('#selectAll').change(function() {
        $('.attendance-checkbox').prop('checked', $(this).is(':checked'));
    });

    // 個別チェックボックス
    $('.attendance-checkbox').change(function() {
        const total = $('.attendance-checkbox').length;
        const checked = $('.attendance-checkbox:checked').length;
        $('#selectAll').prop('checked', total === checked);
    });

    // 簡易打刻モーダル
    $('#quickCheckInOut').click(function() {
        $('#quickCheckInOutModal').modal('show');
    });

    // 統計情報の自動更新（30秒ごと）
    setInterval(updateStatistics, 30000);
});

// 統計情報更新
function updateStatistics() {
    $.get('{{ route("attendances.statistics") }}', function(data) {
        $('#todayAttendees').text(data.today_attendees || 0);
        $('#currentWorking').text(data.current_working || 0);
        $('#pendingApproval').text(data.pending_approval || 0);
        $('#alerts').text(data.alerts || 0);
    }).catch(function() {
        console.log('統計情報の更新に失敗しました');
    });
}

// 簡易打刻実行
function submitQuickCheckInOut() {
    const form = $('#quickCheckInOutForm');
    const formData = new FormData(form[0]);

    // GPS位置情報を取得
    if ($('#use_gps').is(':checked')) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    formData.append('latitude', position.coords.latitude);
                    formData.append('longitude', position.coords.longitude);
                    formData.append('accuracy', position.coords.accuracy);
                    executeQuickCheckInOut(formData);
                },
                function(error) {
                    if (confirm('GPS位置情報の取得に失敗しました。位置情報なしで続行しますか？')) {
                        executeQuickCheckInOut(formData);
                    }
                }
            );
        } else {
            alert('GPS位置情報がサポートされていません。');
            executeQuickCheckInOut(formData);
        }
    } else {
        executeQuickCheckInOut(formData);
    }
}

// 簡易打刻実行（実際の送信）
function executeQuickCheckInOut(formData) {
    $.ajax({
        url: '{{ route("attendances.quick-check") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#quickCheckInOutModal').modal('hide');
            showSuccessMessage(response.message || '打刻が完了しました。');
            location.reload();
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                let errorMessage = '';
                Object.values(errors).forEach(function(messages) {
                    errorMessage += messages.join('\n') + '\n';
                });
                showErrorMessage(errorMessage);
            } else {
                showErrorMessage(xhr.responseJSON?.message || '打刻に失敗しました。');
            }
        }
    });
}

// 承認処理
function approveAttendance(attendanceId) {
    showApprovalModal(attendanceId, 'approve', '承認');
}

// 差し戻し処理
function rejectAttendance(attendanceId) {
    showApprovalModal(attendanceId, 'reject', '差し戻し');
}

// 承認・差し戻しモーダル表示
function showApprovalModal(attendanceId, action, title) {
    $('#approval_attendance_id').val(attendanceId);
    $('#approval_action').val(action);
    $('#approvalModalTitle').text(`勤怠記録の${title}`);
    $('#approvalSubmitBtn').text(title);
    $('#approvalModal').modal('show');
}

// 承認・差し戻し実行
function submitApproval() {
    const attendanceId = $('#approval_attendance_id').val();
    const action = $('#approval_action').val();
    const note = $('#approval_note').val();

    $.ajax({
        url: `{{ url('attendances') }}/${attendanceId}/${action}`,
        method: 'POST',
        data: {
            note: note,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#approvalModal').modal('hide');
            showSuccessMessage(response.message || '処理が完了しました。');
            location.reload();
        },
        error: function(xhr) {
            showErrorMessage(xhr.responseJSON?.message || '処理に失敗しました。');
        }
    });
}

// 一括承認
function bulkApprove() {
    const checkedIds = $('.attendance-checkbox:checked').map(function() {
        return $(this).val();
    }).get();

    if (checkedIds.length === 0) {
        showErrorMessage('承認する勤怠記録を選択してください。');
        return;
    }

    if (!confirm(`選択した${checkedIds.length}件の勤怠記録を一括承認しますか？`)) {
        return;
    }

    $.ajax({
        url: '{{ route("attendances.bulk-approve") }}',
        method: 'POST',
        data: {
            attendance_ids: checkedIds,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            showSuccessMessage(response.message || '一括承認が完了しました。');
            location.reload();
        },
        error: function(xhr) {
            showErrorMessage(xhr.responseJSON?.message || '一括承認に失敗しました。');
        }
    });
}

// エクスポート処理
function exportAttendances(format) {
    const params = new URLSearchParams($('#searchForm').serialize());
    params.append('format', format);
    
    const url = `{{ route('attendances.export') }}?${params.toString()}`;
    window.open(url, '_blank');
}

// 成功メッセージ表示
function showSuccessMessage(message) {
    // Toast通知やアラートで成功メッセージを表示
    if (typeof toastr !== 'undefined') {
        toastr.success(message);
    } else {
        alert(message);
    }
}

// エラーメッセージ表示
function showErrorMessage(message) {
    // Toast通知やアラートでエラーメッセージを表示
    if (typeof toastr !== 'undefined') {
        toastr.error(message);
    } else {
        alert(message);
    }
}
</script>
@endpush
