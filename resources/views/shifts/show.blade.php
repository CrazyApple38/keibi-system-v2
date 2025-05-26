@extends('layouts.app')

@section('title', 'シフト詳細')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('shifts.index') }}">シフト管理</a></li>
                    <li class="breadcrumb-item active">シフト詳細</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-calendar-event me-2"></i>
                        シフト詳細
                    </h2>
                    <p class="text-muted mb-0">{{ $shift->project->name ?? '未設定' }} - {{ $shift->start_date ? $shift->start_date->format('Y年n月j日') : '未設定' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="printShift()">
                        <i class="bi bi-printer me-1"></i>
                        印刷
                    </button>
                    <a href="{{ route('shifts.edit', $shift) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil me-1"></i>
                        編集
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-1"></i>
                            操作
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="assignGuards()">
                                <i class="bi bi-person-plus me-2"></i>警備員配置
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="startShift()">
                                <i class="bi bi-play-circle me-2"></i>シフト開始
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="completeShift()">
                                <i class="bi bi-check-circle me-2"></i>完了処理
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="duplicateShift()">
                                <i class="bi bi-copy me-2"></i>複製作成
                            </a></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="cancelShift()">
                                <i class="bi bi-x-circle me-2"></i>キャンセル
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- メイン情報 -->
        <div class="col-lg-8 col-md-12">
            <!-- 基本情報 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        基本情報
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">プロジェクト</label>
                            <p class="mb-0">{{ $shift->project->name ?? '未設定' }}</p>
                            @if($shift->project && $shift->project->location)
                                <small class="text-muted">{{ $shift->project->location }}</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ステータス</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $shift->getStatusColor() }} fs-6">
                                    {{ $shift->getStatusText() }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">開始日時</label>
                            <p class="mb-0">
                                {{ $shift->start_date ? $shift->start_date->format('Y年n月j日') : '未設定' }}
                                <br>
                                <small class="text-muted">{{ $shift->start_time ?? '未設定' }}</small>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">終了日時</label>
                            <p class="mb-0">
                                {{ $shift->end_date ? $shift->end_date->format('Y年n月j日') : ($shift->start_date ? $shift->start_date->format('Y年n月j日') : '未設定') }}
                                <br>
                                <small class="text-muted">{{ $shift->end_time ?? '未設定' }}</small>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">勤務時間</label>
                            <p class="mb-0">
                                <strong>{{ $shift->getDurationHours() }}時間</strong>
                                @if($shift->break_time)
                                    <br><small class="text-muted">（休憩{{ $shift->break_time }}分含む）</small>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">必要警備員数</label>
                            <p class="mb-0">{{ $shift->required_guards ?? 0 }}名</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">配置済み警備員数</label>
                            <p class="mb-0">
                                <span class="text-{{ ($shift->assignedGuards->count() >= $shift->required_guards) ? 'success' : 'warning' }}">
                                    {{ $shift->assignedGuards->count() }}名
                                </span>
                                @if($shift->assignedGuards->count() < $shift->required_guards)
                                    <small class="text-warning">（{{ $shift->required_guards - $shift->assignedGuards->count() }}名不足）</small>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    @if($shift->notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">備考</label>
                            <p class="mb-0">{{ $shift->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- 配置済み警備員 -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-people me-2"></i>
                            配置済み警備員
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="assignGuards()">
                            <i class="bi bi-person-plus me-1"></i>
                            警備員追加
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($shift->assignedGuards && $shift->assignedGuards->count() > 0)
                        <div class="row g-3">
                            @foreach($shift->assignedGuards as $assignment)
                            <div class="col-lg-6 col-md-12">
                                <div class="card border">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            @if($assignment->guard->profile_photo)
                                                <img src="{{ Storage::url($assignment->guard->profile_photo) }}" 
                                                     class="rounded-circle me-3" width="50" height="50" 
                                                     style="object-fit: cover;" alt="プロフィール">
                                            @else
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                     style="width: 50px; height: 50px;">
                                                    <span class="text-white fw-bold">{{ mb_substr($assignment->guard->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $assignment->guard->name }}</h6>
                                                <small class="text-muted">{{ $assignment->guard->employee_id }}</small>
                                                <div class="mt-1">
                                                    @if($assignment->guard->skills)
                                                        @foreach(array_slice($assignment->guard->skills, 0, 2) as $skill)
                                                            <span class="badge bg-secondary me-1">{{ $assignment->guard->getSkillText($skill) }}</span>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold">¥{{ number_format($assignment->hourly_rate ?? $assignment->guard->hourly_rate) }}</div>
                                                <small class="text-muted">/時</small>
                                                <div class="mt-1">
                                                    <span class="badge bg-{{ $assignment->getStatusColor() }}">
                                                        {{ $assignment->getStatusText() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <div class="btn-group btn-group-sm w-100" role="group">
                                                <button class="btn btn-outline-info" onclick="viewGuardDetails({{ $assignment->guard->id }})">
                                                    <i class="bi bi-eye"></i> 詳細
                                                </button>
                                                <button class="btn btn-outline-warning" onclick="editAssignment({{ $assignment->id }})">
                                                    <i class="bi bi-pencil"></i> 編集
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="removeAssignment({{ $assignment->id }})">
                                                    <i class="bi bi-x"></i> 除外
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-person-plus display-1 text-muted"></i>
                            <div class="mt-3">
                                <h5 class="text-muted">警備員が配置されていません</h5>
                                <p class="text-muted">警備員を配置してシフトを完成させてください。</p>
                                <button class="btn btn-primary" onclick="assignGuards()">
                                    <i class="bi bi-person-plus me-1"></i>
                                    警備員を配置
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 勤怠記録 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock me-2"></i>
                        勤怠記録
                    </h5>
                </div>
                <div class="card-body">
                    @if($shift->attendances && $shift->attendances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>警備員</th>
                                        <th>出勤時刻</th>
                                        <th>退勤時刻</th>
                                        <th>実働時間</th>
                                        <th>休憩時間</th>
                                        <th>ステータス</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shift->attendances as $attendance)
                                    <tr>
                                        <td>{{ $attendance->guard->name }}</td>
                                        <td>{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-' }}</td>
                                        <td>{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-' }}</td>
                                        <td>{{ $attendance->actual_hours ? $attendance->actual_hours . 'h' : '-' }}</td>
                                        <td>{{ $attendance->break_time ? $attendance->break_time . '分' : '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $attendance->getStatusColor() }}">
                                                {{ $attendance->getStatusText() }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-clock-history display-6"></i>
                            <div class="mt-2">勤怠記録はまだありません</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 日報・報告書 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-journal-text me-2"></i>
                        日報・報告書
                    </h5>
                </div>
                <div class="card-body">
                    @if($shift->dailyReports && $shift->dailyReports->count() > 0)
                        @foreach($shift->dailyReports as $report)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">{{ $report->guard->name }}の日報</h6>
                                    <small class="text-muted">{{ $report->created_at->format('Y年n月j日 H:i') }}</small>
                                </div>
                                <span class="badge bg-{{ $report->getStatusColor() }}">
                                    {{ $report->getStatusText() }}
                                </span>
                            </div>
                            <p class="mb-2">{{ Str::limit($report->report_content, 200) }}</p>
                            <a href="{{ route('daily_reports.show', $report) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>詳細表示
                            </a>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-journal display-6"></i>
                            <div class="mt-2">日報はまだ提出されていません</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- サイドバー -->
        <div class="col-lg-4 col-md-12">
            <!-- ステータス・進捗 -->
            <div class="card mb-4 sticky-top" style="top: 1rem;">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-speedometer2 me-2"></i>
                        ステータス・進捗
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="progress mb-2" style="height: 10px;">
                            <div class="progress-bar" style="width: {{ $shift->getCompletionPercentage() }}%"></div>
                        </div>
                        <strong>{{ $shift->getCompletionPercentage() }}% 完了</strong>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="text-primary fw-bold fs-4">{{ $shift->assignedGuards->count() }}</div>
                            <small class="text-muted">配置済み警備員</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-success fw-bold fs-4">{{ $shift->attendances->where('status', 'present')->count() }}</div>
                            <small class="text-muted">出勤済み</small>
                        </div>
                        <div class="col-6">
                            <div class="text-warning fw-bold fs-4">{{ $shift->dailyReports->count() }}</div>
                            <small class="text-muted">日報提出</small>
                        </div>
                        <div class="col-6">
                            <div class="text-info fw-bold fs-4">{{ $shift->getDurationHours() }}h</div>
                            <small class="text-muted">勤務時間</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 費用情報 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-currency-yen me-2"></i>
                        費用情報
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <small class="text-muted">予定費用</small>
                            <div class="fw-bold">¥{{ number_format($shift->calculatePlannedCost()) }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">実績費用</small>
                            <div class="fw-bold">¥{{ number_format($shift->calculateActualCost()) }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">基本料金</small>
                            <div class="fw-bold">¥{{ number_format($shift->calculateBasicCost()) }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">割増料金</small>
                            <div class="fw-bold">¥{{ number_format($shift->calculateOvertimeCost()) }}</div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <strong>合計費用</strong>
                        <strong class="text-primary">¥{{ number_format($shift->calculateTotalCost()) }}</strong>
                    </div>
                    
                    @if($shift->hourly_rate)
                    <div class="mt-2">
                        <small class="text-muted">時給設定: ¥{{ number_format($shift->hourly_rate) }}/時</small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- アクションボタン -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        クイックアクション
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($shift->status === 'scheduled')
                            <button class="btn btn-success" onclick="startShift()">
                                <i class="bi bi-play-circle me-1"></i>
                                シフト開始
                            </button>
                        @elseif($shift->status === 'in_progress')
                            <button class="btn btn-primary" onclick="completeShift()">
                                <i class="bi bi-check-circle me-1"></i>
                                完了処理
                            </button>
                        @endif
                        
                        <button class="btn btn-outline-info" onclick="exportShiftData()">
                            <i class="bi bi-download me-1"></i>
                            データエクスポート
                        </button>
                        
                        <button class="btn btn-outline-warning" onclick="duplicateShift()">
                            <i class="bi bi-copy me-1"></i>
                            複製作成
                        </button>
                        
                        @if(in_array($shift->status, ['scheduled', 'draft']))
                            <button class="btn btn-outline-danger" onclick="cancelShift()">
                                <i class="bi bi-x-circle me-1"></i>
                                キャンセル
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 最近のアクティビティ -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        最近のアクティビティ
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($recentActivities) && count($recentActivities) > 0)
                        @foreach($recentActivities as $activity)
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-{{ $activity['type'] === 'attendance' ? 'success' : ($activity['type'] === 'assignment' ? 'primary' : 'info') }} rounded-circle p-2 me-3">
                                <i class="bi bi-{{ $activity['type'] === 'attendance' ? 'clock' : ($activity['type'] === 'assignment' ? 'person' : 'file-text') }} text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small fw-bold">{{ $activity['title'] }}</div>
                                <div class="small text-muted">{{ $activity['description'] }}</div>
                                <div class="small text-muted">{{ $activity['date'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-clock-history display-6"></i>
                            <div class="mt-2">アクティビティはありません</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .sticky-top {
        top: 1rem;
    }
    
    .progress {
        height: 10px;
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    @media print {
        .btn, .dropdown, .breadcrumb {
            display: none !important;
        }
        
        .sticky-top {
            position: relative !important;
            top: auto !important;
        }
        
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
    
    @media (max-width: 768px) {
        .sticky-top {
            position: relative !important;
            top: auto !important;
        }
        
        .col-lg-6 {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // ページ読み込み時の初期化
        updateShiftStatus();
        
        // 定期的にステータスを更新（30秒間隔）
        setInterval(updateShiftStatus, 30000);
    });
    
    // シフトステータス更新
    function updateShiftStatus() {
        $.get(`{{ route('shifts.status', $shift) }}`)
            .done(function(data) {
                // ステータス情報を更新
                updateStatusDisplay(data);
            })
            .fail(function() {
                console.error('ステータス更新に失敗しました');
            });
    }
    
    // ステータス表示更新
    function updateStatusDisplay(data) {
        // 進捗率更新
        $('.progress-bar').css('width', data.completion_percentage + '%');
        
        // 統計情報更新
        $('.text-primary.fw-bold.fs-4').eq(0).text(data.assigned_count);
        $('.text-success.fw-bold.fs-4').eq(0).text(data.present_count);
        $('.text-warning.fw-bold.fs-4').eq(0).text(data.report_count);
    }
    
    // 警備員配置
    function assignGuards() {
        window.location.href = `{{ route('shifts.assign', $shift) }}`;
    }
    
    // 警備員詳細表示
    function viewGuardDetails(guardId) {
        window.open(`{{ route('guards.show', '') }}/${guardId}`, '_blank');
    }
    
    // 配置編集
    function editAssignment(assignmentId) {
        // 配置編集モーダル表示（実装省略）
        console.log('配置編集:', assignmentId);
    }
    
    // 配置除外
    function removeAssignment(assignmentId) {
        if (confirm('この警備員をシフトから除外しますか？')) {
            $.ajax({
                url: `{{ route('shifts.assignments.destroy', '') }}/${assignmentId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showSuccessMessage('警備員を除外しました');
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    showErrorMessage('除外処理に失敗しました');
                }
            });
        }
    }
    
    // シフト開始
    function startShift() {
        if (confirm('シフトを開始しますか？\n開始後は警備員の出勤打刻が可能になります。')) {
            $.post(`{{ route('shifts.start', $shift) }}`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                showSuccessMessage('シフトを開始しました');
                setTimeout(() => location.reload(), 1500);
            })
            .fail(function() {
                showErrorMessage('シフト開始に失敗しました');
            });
        }
    }
    
    // シフト完了
    function completeShift() {
        if (confirm('シフトを完了しますか？\n完了後は勤怠記録が確定されます。')) {
            $.post(`{{ route('shifts.complete', $shift) }}`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                showSuccessMessage('シフトを完了しました');
                setTimeout(() => location.reload(), 1500);
            })
            .fail(function() {
                showErrorMessage('シフト完了に失敗しました');
            });
        }
    }
    
    // シフトキャンセル
    function cancelShift() {
        if (confirm('シフトをキャンセルしますか？\nこの操作は取り消せません。')) {
            $.post(`{{ route('shifts.cancel', $shift) }}`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                showSuccessMessage('シフトをキャンセルしました');
                setTimeout(() => location.reload(), 1500);
            })
            .fail(function() {
                showErrorMessage('シフトキャンセルに失敗しました');
            });
        }
    }
    
    // シフト複製
    function duplicateShift() {
        const newDate = prompt('複製先の日付を入力してください（YYYY-MM-DD形式）:', 
                              '{{ now()->addDay()->format("Y-m-d") }}');
        
        if (newDate) {
            $.post(`{{ route('shifts.duplicate', $shift) }}`, {
                new_date: newDate,
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                showSuccessMessage('シフトを複製しました');
                window.location.href = `{{ route('shifts.show', '') }}/${response.shift_id}`;
            })
            .fail(function() {
                showErrorMessage('シフト複製に失敗しました');
            });
        }
    }
    
    // データエクスポート
    function exportShiftData() {
        window.open(`{{ route('shifts.export.individual', $shift) }}`, '_blank');
    }
    
    // 印刷
    function printShift() {
        window.print();
    }
</script>
@endpush
@endsection