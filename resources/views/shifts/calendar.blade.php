@extends('layouts.app')

@section('title', 'シフトカレンダー')

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
                            <li class="breadcrumb-item"><a href="{{ route('shifts.index') }}">シフト管理</a></li>
                            <li class="breadcrumb-item active">カレンダー表示</li>
                        </ol>
                    </nav>
                    <h2 class="mb-1">
                        <i class="bi bi-calendar-month me-2"></i>
                        シフトカレンダー
                    </h2>
                    <p class="text-muted mb-0">月次・週次・日次のシフト表示と管理</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-list me-1"></i>
                        リスト表示
                    </a>
                    <button class="btn btn-outline-primary" id="exportCalendar">
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
                                <i class="bi bi-calendar-plus me-2"></i>単発シフト
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('shifts.create.recurring') }}">
                                <i class="bi bi-arrow-repeat me-2"></i>定期シフト
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="quickCreateShift()">
                                <i class="bi bi-lightning me-2"></i>クイック作成
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- カレンダーツールバー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <!-- 表示切り替え -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">表示タイプ</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="viewType" id="monthView" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="monthView">月</label>
                                
                                <input type="radio" class="btn-check" name="viewType" id="weekView" autocomplete="off">
                                <label class="btn btn-outline-primary" for="weekView">週</label>
                                
                                <input type="radio" class="btn-check" name="viewType" id="dayView" autocomplete="off">
                                <label class="btn btn-outline-primary" for="dayView">日</label>
                            </div>
                        </div>
                        
                        <!-- 月切り替え -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">表示月</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary" type="button" id="prevMonth">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <input type="month" class="form-control" id="currentMonth" value="{{ date('Y-m') }}">
                                <button class="btn btn-outline-secondary" type="button" id="nextMonth">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- フィルター -->
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">プロジェクト</label>
                            <select class="form-select" id="projectFilter">
                                <option value="">全て</option>
                                @foreach($projects ?? [] as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">警備員</label>
                            <select class="form-select" id="guardFilter">
                                <option value="">全て</option>
                                @foreach($guards ?? [] as $guard)
                                    <option value="{{ $guard->id }}">{{ $guard->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">ステータス</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">全て</option>
                                <option value="scheduled">予定</option>
                                <option value="in_progress">実行中</option>
                                <option value="completed">完了</option>
                                <option value="cancelled">キャンセル</option>
                            </select>
                        </div>
                        
                        <!-- 操作ボタン -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-secondary" id="todayBtn">
                                        <i class="bi bi-calendar-day me-1"></i>
                                        今日
                                    </button>
                                    <button class="btn btn-outline-info" id="refreshCalendar">
                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                        更新
                                    </button>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="showWeekends" checked>
                                        <label class="form-check-label" for="showWeekends">週末表示</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="showConflicts">
                                        <label class="form-check-label" for="showConflicts">競合表示</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 統計情報パネル -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-calendar-check display-6 text-primary mb-2"></i>
                    <h4 class="mb-1" id="monthlyShifts">0</h4>
                    <small class="text-muted">月間シフト数</small>
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
                    <h4 class="mb-1" id="conflictCount">0</h4>
                    <small class="text-muted">競合シフト</small>
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

    <!-- カレンダー本体 -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" id="calendarTitle">
                            <i class="bi bi-calendar3 me-2"></i>
                            シフトカレンダー
                        </h5>
                        
                        <!-- 凡例 -->
                        <div class="d-flex gap-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded me-2" style="width: 12px; height: 12px;"></div>
                                <small>予定</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded me-2" style="width: 12px; height: 12px;"></div>
                                <small>実行中</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary rounded me-2" style="width: 12px; height: 12px;"></div>
                                <small>完了</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="bg-danger rounded me-2" style="width: 12px; height: 12px;"></div>
                                <small>キャンセル</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="bg-warning rounded me-2" style="width: 12px; height: 12px;"></div>
                                <small>競合</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <!-- FullCalendar表示エリア -->
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- シフト詳細モーダル -->
<div class="modal fade" id="shiftDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">シフト詳細</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="shiftDetailContent">
                    <!-- シフト詳細情報はJavaScriptで動的に読み込み -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-outline-warning" id="editShiftBtn">編集</button>
                <button type="button" class="btn btn-primary" id="viewFullDetailBtn">詳細表示</button>
            </div>
        </div>
    </div>
</div>

<!-- クイック作成モーダル -->
<div class="modal fade" id="quickCreateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">クイックシフト作成</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickCreateForm">
                    <div class="mb-3">
                        <label for="quickProject" class="form-label">プロジェクト</label>
                        <select class="form-select" id="quickProject" required>
                            <option value="">選択してください</option>
                            @foreach($projects ?? [] as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quickDate" class="form-label">日付</label>
                        <input type="date" class="form-control" id="quickDate" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="quickStartTime" class="form-label">開始時間</label>
                            <input type="time" class="form-control" id="quickStartTime" value="09:00" required>
                        </div>
                        <div class="col-6">
                            <label for="quickEndTime" class="form-label">終了時間</label>
                            <input type="time" class="form-control" id="quickEndTime" value="18:00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="quickGuardCount" class="form-label">必要警備員数</label>
                        <input type="number" class="form-control" id="quickGuardCount" value="1" min="1" max="20" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="createQuickShift()">作成</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

<style>
    /* カレンダーカスタムスタイル */
    .fc {
        font-family: 'Noto Sans JP', sans-serif;
    }
    
    .fc-toolbar {
        padding: 1rem;
    }
    
    .fc-toolbar-title {
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    .fc-button {
        background: var(--bs-primary);
        border-color: var(--bs-primary);
        color: white;
        border-radius: 0.375rem;
        font-weight: 500;
    }
    
    .fc-button:hover {
        background: var(--bs-primary);
        border-color: var(--bs-primary);
        opacity: 0.9;
    }
    
    .fc-button:focus {
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    }
    
    .fc-event {
        border-radius: 4px;
        border: none;
        padding: 2px 4px;
        margin: 1px 0;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .fc-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
    
    .fc-event-title {
        font-weight: 500;
    }
    
    .fc-event-time {
        font-weight: 400;
        opacity: 0.9;
    }
    
    /* ステータス別の色分け */
    .fc-event.status-scheduled {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
    }
    
    .fc-event.status-in_progress {
        background-color: var(--bs-success);
        border-color: var(--bs-success);
    }
    
    .fc-event.status-completed {
        background-color: var(--bs-secondary);
        border-color: var(--bs-secondary);
    }
    
    .fc-event.status-cancelled {
        background-color: var(--bs-danger);
        border-color: var(--bs-danger);
    }
    
    .fc-event.has-conflict {
        background-color: var(--bs-warning);
        border-color: var(--bs-warning);
        color: var(--bs-dark);
    }
    
    /* 日付セルのカスタマイズ */
    .fc-daygrid-day {
        min-height: 120px;
    }
    
    .fc-daygrid-day-number {
        font-weight: 600;
        color: var(--bs-dark);
    }
    
    .fc-day-today {
        background-color: rgba(59, 130, 246, 0.1) !important;
    }
    
    .fc-day-past {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    /* レスポンシブ対応 */
    @media (max-width: 768px) {
        .fc-toolbar {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
        }
        
        .fc-daygrid-day {
            min-height: 80px;
        }
        
        .fc-event {
            font-size: 0.7rem;
        }
        
        .card-body {
            padding: 0.5rem;
        }
    }
    
    /* プリント対応 */
    @media print {
        .btn, .dropdown, .breadcrumb, .card-header {
            display: none !important;
        }
        
        .fc-toolbar {
            display: none !important;
        }
        
        .fc-event {
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }
    }
</style>
@endpush

@push('scripts')
<!-- FullCalendar JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/ja.global.min.js"></script>

<script>
    let calendar;
    let currentView = 'dayGridMonth';
    
    $(document).ready(function() {
        // カレンダー初期化
        initializeCalendar();
        
        // 統計情報更新
        updateStatistics();
        
        // イベントリスナー設定
        setupEventListeners();
        
        // 定期更新設定（5分間隔）
        setInterval(function() {
            calendar.refetchEvents();
            updateStatistics();
        }, 300000);
    });
    
    // カレンダー初期化
    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'ja',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            height: 'auto',
            navLinks: true,
            selectable: true,
            selectMirror: true,
            
            // イベントデータソース
            events: {
                url: '{{ route("shifts.calendar.data") }}',
                method: 'GET',
                extraParams: function() {
                    return {
                        project_id: $('#projectFilter').val(),
                        guard_id: $('#guardFilter').val(),
                        status: $('#statusFilter').val(),
                        show_conflicts: $('#showConflicts').is(':checked')
                    };
                },
                failure: function() {
                    showErrorMessage('シフトデータの読み込みに失敗しました');
                }
            },
            
            // イベントレンダリング
            eventDidMount: function(info) {
                // ステータス別のクラス追加
                info.el.classList.add('status-' + info.event.extendedProps.status);
                
                // 競合がある場合のクラス追加
                if (info.event.extendedProps.hasConflict) {
                    info.el.classList.add('has-conflict');
                }
                
                // ツールチップ設定
                $(info.el).tooltip({
                    title: generateTooltipContent(info.event),
                    html: true,
                    placement: 'top'
                });
            },
            
            // イベントクリック
            eventClick: function(info) {
                showShiftDetail(info.event.id);
            },
            
            // 日付選択
            select: function(info) {
                const selectedDate = info.start.toISOString().split('T')[0];
                $('#quickDate').val(selectedDate);
                $('#quickCreateModal').modal('show');
            },
            
            // 表示変更
            viewDidMount: function(info) {
                currentView = info.view.type;
                updateCalendarTitle();
            },
            
            // イベントドロップ（日付変更）
            eventDrop: function(info) {
                updateShiftDate(info.event.id, info.event.start);
            },
            
            // イベントリサイズ（時間変更）
            eventResize: function(info) {
                updateShiftTime(info.event.id, info.event.start, info.event.end);
            },
            
            // カレンダー設定
            weekends: true,
            nowIndicator: true,
            businessHours: {
                daysOfWeek: [1, 2, 3, 4, 5, 6, 0],
                startTime: '06:00',
                endTime: '22:00'
            },
            
            // 日本語化
            buttonText: {
                today: '今日',
                month: '月',
                week: '週',
                day: '日'
            }
        });
        
        calendar.render();
    }
    
    // イベントリスナー設定
    function setupEventListeners() {
        // 表示タイプ変更
        $('input[name="viewType"]').change(function() {
            const viewType = $(this).attr('id');
            let calendarView;
            
            switch(viewType) {
                case 'monthView':
                    calendarView = 'dayGridMonth';
                    break;
                case 'weekView':
                    calendarView = 'timeGridWeek';
                    break;
                case 'dayView':
                    calendarView = 'timeGridDay';
                    break;
            }
            
            calendar.changeView(calendarView);
        });
        
        // 月切り替え
        $('#currentMonth').change(function() {
            const date = new Date($(this).val() + '-01');
            calendar.gotoDate(date);
        });
        
        $('#prevMonth').click(function() {
            calendar.prev();
            updateCurrentMonthInput();
        });
        
        $('#nextMonth').click(function() {
            calendar.next();
            updateCurrentMonthInput();
        });
        
        // 今日ボタン
        $('#todayBtn').click(function() {
            calendar.today();
            updateCurrentMonthInput();
        });
        
        // フィルター変更
        $('#projectFilter, #guardFilter, #statusFilter').change(function() {
            calendar.refetchEvents();
            updateStatistics();
        });
        
        // 競合表示切り替え
        $('#showConflicts').change(function() {
            calendar.refetchEvents();
        });
        
        // 週末表示切り替え
        $('#showWeekends').change(function() {
            calendar.setOption('weekends', $(this).is(':checked'));
        });
        
        // 更新ボタン
        $('#refreshCalendar').click(function() {
            calendar.refetchEvents();
            updateStatistics();
            showSuccessMessage('カレンダーを更新しました');
        });
        
        // エクスポートボタン
        $('#exportCalendar').click(function() {
            exportCalendarData();
        });
    }
    
    // ツールチップコンテンツ生成
    function generateTooltipContent(event) {
        const startTime = event.start.toLocaleTimeString('ja-JP', {hour: '2-digit', minute: '2-digit'});
        const endTime = event.end ? event.end.toLocaleTimeString('ja-JP', {hour: '2-digit', minute: '2-digit'}) : '';
        
        return `
            <div class="text-start">
                <div class="fw-bold">${event.title}</div>
                <div class="small">${startTime}${endTime ? ' - ' + endTime : ''}</div>
                <div class="small">警備員: ${event.extendedProps.assignedCount}/${event.extendedProps.requiredCount}名</div>
                <div class="small">ステータス: ${event.extendedProps.statusText}</div>
                ${event.extendedProps.hasConflict ? '<div class="small text-warning">⚠ 競合あり</div>' : ''}
            </div>
        `;
    }
    
    // シフト詳細表示
    function showShiftDetail(shiftId) {
        $.get(`{{ route('shifts.show', '') }}/${shiftId}`)
            .done(function(data) {
                $('#shiftDetailContent').html(data.html);
                $('#editShiftBtn').data('shift-id', shiftId);
                $('#viewFullDetailBtn').data('shift-id', shiftId);
                $('#shiftDetailModal').modal('show');
            })
            .fail(function() {
                showErrorMessage('シフト詳細の読み込みに失敗しました');
            });
    }
    
    // シフト日付更新
    function updateShiftDate(shiftId, newDate) {
        $.post(`{{ route('shifts.update.date', '') }}/${shiftId}`, {
            new_date: newDate.toISOString().split('T')[0],
            _token: '{{ csrf_token() }}'
        })
        .done(function() {
            showSuccessMessage('シフト日付を更新しました');
            updateStatistics();
        })
        .fail(function() {
            showErrorMessage('日付更新に失敗しました');
            calendar.refetchEvents();
        });
    }
    
    // シフト時間更新
    function updateShiftTime(shiftId, startTime, endTime) {
        $.post(`{{ route('shifts.update.time', '') }}/${shiftId}`, {
            start_time: startTime.toTimeString().split(' ')[0],
            end_time: endTime ? endTime.toTimeString().split(' ')[0] : null,
            _token: '{{ csrf_token() }}'
        })
        .done(function() {
            showSuccessMessage('シフト時間を更新しました');
            updateStatistics();
        })
        .fail(function() {
            showErrorMessage('時間更新に失敗しました');
            calendar.refetchEvents();
        });
    }
    
    // 統計情報更新
    function updateStatistics() {
        const params = {
            start_date: calendar.view.activeStart.toISOString().split('T')[0],
            end_date: calendar.view.activeEnd.toISOString().split('T')[0],
            project_id: $('#projectFilter').val(),
            guard_id: $('#guardFilter').val(),
            status: $('#statusFilter').val()
        };
        
        $.get('{{ route("shifts.calendar.stats") }}', params)
            .done(function(data) {
                $('#monthlyShifts').text(data.total_shifts || 0);
                $('#assignedGuards').text(data.assigned_guards || 0);
                $('#conflictCount').text(data.conflicts || 0);
                $('#totalHours').text(data.total_hours || 0);
            })
            .fail(function() {
                console.error('統計情報の取得に失敗しました');
            });
    }
    
    // カレンダータイトル更新
    function updateCalendarTitle() {
        const viewDate = calendar.getDate();
        const viewTitle = calendar.view.title;
        $('#calendarTitle').html(`<i class="bi bi-calendar3 me-2"></i>${viewTitle}`);
    }
    
    // 現在月入力フィールド更新
    function updateCurrentMonthInput() {
        const currentDate = calendar.getDate();
        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        $('#currentMonth').val(`${year}-${month}`);
        updateCalendarTitle();
    }
    
    // クイックシフト作成
    function quickCreateShift() {
        $('#quickCreateModal').modal('show');
    }
    
    // クイック作成実行
    function createQuickShift() {
        const formData = {
            project_id: $('#quickProject').val(),
            start_date: $('#quickDate').val(),
            start_time: $('#quickStartTime').val(),
            end_time: $('#quickEndTime').val(),
            required_guards: $('#quickGuardCount').val(),
            status: 'scheduled',
            _token: '{{ csrf_token() }}'
        };
        
        // バリデーション
        if (!formData.project_id || !formData.start_date || !formData.start_time || !formData.end_time) {
            alert('必須項目を入力してください');
            return;
        }
        
        $.post('{{ route("shifts.store") }}', formData)
            .done(function(response) {
                showSuccessMessage('シフトを作成しました');
                $('#quickCreateModal').modal('hide');
                $('#quickCreateForm')[0].reset();
                calendar.refetchEvents();
                updateStatistics();
            })
            .fail(function(xhr) {
                showErrorMessage('シフト作成に失敗しました');
            });
    }
    
    // カレンダーデータエクスポート
    function exportCalendarData() {
        const params = {
            start_date: calendar.view.activeStart.toISOString().split('T')[0],
            end_date: calendar.view.activeEnd.toISOString().split('T')[0],
            project_id: $('#projectFilter').val(),
            guard_id: $('#guardFilter').val(),
            status: $('#statusFilter').val(),
            format: 'excel'
        };
        
        const queryString = new URLSearchParams(params).toString();
        window.open(`{{ route('shifts.calendar.export') }}?${queryString}`, '_blank');
    }
    
    // モーダル内の編集・詳細ボタン
    $(document).on('click', '#editShiftBtn', function() {
        const shiftId = $(this).data('shift-id');
        window.location.href = `{{ route('shifts.edit', '') }}/${shiftId}`;
    });
    
    $(document).on('click', '#viewFullDetailBtn', function() {
        const shiftId = $(this).data('shift-id');
        window.location.href = `{{ route('shifts.show', '') }}/${shiftId}`;
    });
</script>
@endpush
@endsection