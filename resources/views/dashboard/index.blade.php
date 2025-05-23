@extends('layouts.app')

@section('title', 'ダッシュボード')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-speedometer2 me-2"></i>
                        ダッシュボード
                    </h2>
                    <p class="text-muted mb-0">警備グループ統合管理システム</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" id="refreshData">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        更新
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-calendar3 me-1"></i>
                            期間設定
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-period="today">今日</a></li>
                            <li><a class="dropdown-item" href="#" data-period="week">今週</a></li>
                            <li><a class="dropdown-item active" href="#" data-period="month">今月</a></li>
                            <li><a class="dropdown-item" href="#" data-period="quarter">四半期</a></li>
                            <li><a class="dropdown-item" href="#" data-period="year">今年</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- アラート・通知セクション -->
    <div class="row mb-4" id="alertsSection">
        <!-- アラートはJavaScriptで動的に表示 -->
    </div>
    
    <!-- KPIカードセクション -->
    <div class="row mb-4">
        <!-- 総売上 -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">総売上</h6>
                            <h3 class="card-title mb-1" id="totalRevenue">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-8"></span>
                                </div>
                            </h3>
                            <small class="text-success" id="revenueChange">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-6"></span>
                                </div>
                            </small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-currency-yen fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- アクティブ案件数 -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">アクティブ案件</h6>
                            <h3 class="card-title mb-1" id="activeProjects">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-6"></span>
                                </div>
                            </h3>
                            <small class="text-info" id="projectsChange">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-8"></span>
                                </div>
                            </small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-briefcase fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 警備員総数 -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">警備員総数</h6>
                            <h3 class="card-title mb-1" id="totalGuards">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-6"></span>
                                </div>
                            </h3>
                            <small class="text-primary" id="guardsChange">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-8"></span>
                                </div>
                            </small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-person-badge fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 月間シフト -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">月間シフト</h6>
                            <h3 class="card-title mb-1" id="monthlyShifts">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-6"></span>
                                </div>
                            </h3>
                            <small class="text-secondary" id="shiftsChange">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-8"></span>
                                </div>
                            </small>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-calendar3 fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- チャート・グラフセクション -->
    <div class="row mb-4">
        <!-- 売上推移グラフ -->
        <div class="col-lg-8 col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        売上推移
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="revenueChart" id="revenue-daily" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="revenue-daily">日別</label>
                        
                        <input type="radio" class="btn-check" name="revenueChart" id="revenue-weekly" autocomplete="off">
                        <label class="btn btn-outline-primary" for="revenue-weekly">週別</label>
                        
                        <input type="radio" class="btn-check" name="revenueChart" id="revenue-monthly" autocomplete="off">
                        <label class="btn btn-outline-primary" for="revenue-monthly">月別</label>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- 会社別売上円グラフ -->
        <div class="col-lg-4 col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart me-2"></i>
                        会社別売上
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="companyRevenueChart" height="300"></canvas>
                    <div class="mt-3" id="companyLegend">
                        <!-- 凡例はJavaScriptで動的に生成 -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 詳細情報セクション -->
    <div class="row mb-4">
        <!-- 最近のアクティビティ -->
        <div class="col-lg-6 col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        最近のアクティビティ
                    </h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">すべて表示</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="recentActivities">
                        <!-- アクティビティはJavaScriptで動的に表示 -->
                        <div class="list-group-item">
                            <div class="placeholder-glow">
                                <span class="placeholder col-7"></span>
                                <span class="placeholder col-4"></span>
                                <span class="placeholder col-4"></span>
                                <span class="placeholder col-6"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 今日の予定・シフト -->
        <div class="col-lg-6 col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-day me-2"></i>
                        今日の予定
                    </h5>
                    <a href="{{ route('shifts.calendar') }}" class="btn btn-sm btn-outline-primary">カレンダー表示</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="todaySchedule">
                        <!-- 予定はJavaScriptで動的に表示 -->
                        <div class="list-group-item">
                            <div class="placeholder-glow">
                                <span class="placeholder col-8"></span>
                                <span class="placeholder col-5"></span>
                                <span class="placeholder col-7"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ステータス・進捗セクション -->
    <div class="row mb-4">
        <!-- プロジェクト進捗 -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check me-2"></i>
                        プロジェクト進捗
                    </h5>
                </div>
                <div class="card-body">
                    <div id="projectProgress">
                        <!-- 進捗はJavaScriptで動的に表示 -->
                        <div class="mb-3">
                            <div class="placeholder-glow">
                                <span class="placeholder col-6"></span>
                            </div>
                            <div class="progress mt-1">
                                <div class="progress-bar placeholder col-7"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 警備員稼働状況 -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person-check me-2"></i>
                        警備員稼働状況
                    </h5>
                </div>
                <div class="card-body">
                    <div id="guardStatus">
                        <!-- 稼働状況はJavaScriptで動的に表示 -->
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-8"></span>
                                    <span class="placeholder col-6"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 月間目標達成率 -->
        <div class="col-lg-4 col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-target me-2"></i>
                        月間目標達成率
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block">
                        <canvas id="goalChart" width="120" height="120"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <h3 class="mb-0" id="goalPercentage">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-6"></span>
                                </div>
                            </h3>
                            <small class="text-muted">達成率</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">目標</small>
                                <div class="fw-bold" id="monthlyGoal">
                                    <div class="placeholder-glow">
                                        <span class="placeholder col-8"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">実績</small>
                                <div class="fw-bold" id="monthlyActual">
                                    <div class="placeholder-glow">
                                        <span class="placeholder col-8"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- クイックアクションセクション -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        クイックアクション
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('customers.create') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                <i class="bi bi-person-plus fs-4 mb-2"></i>
                                <span>顧客登録</span>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('projects.create') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                <i class="bi bi-briefcase-fill fs-4 mb-2"></i>
                                <span>案件作成</span>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('guards.create') }}" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                <i class="bi bi-person-badge fs-4 mb-2"></i>
                                <span>警備員登録</span>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('shifts.create') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                <i class="bi bi-calendar-plus fs-4 mb-2"></i>
                                <span>シフト作成</span>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('quotations.create') }}" class="btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                <i class="bi bi-file-text fs-4 mb-2"></i>
                                <span>見積作成</span>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('daily_reports.create') }}" class="btn btn-outline-dark w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                <i class="bi bi-journal-text fs-4 mb-2"></i>
                                <span>日報作成</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .placeholder {
        background-color: #e9ecef;
        opacity: 0.5;
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .progress {
        height: 8px;
    }
    
    .list-group-item {
        border: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
    
    .quick-action-btn {
        min-height: 100px;
        transition: all 0.3s ease;
    }
    
    .quick-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .alert-dismissible .btn-close {
        padding: 0.5rem 0.75rem;
    }
    
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
        
        .btn-group-sm .btn {
            font-size: 0.75rem;
        }
    }
</style>
@endpush

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        // 初期データ読み込み
        loadDashboardData();
        
        // 定期更新設定（5分間隔）
        setInterval(loadDashboardData, 300000);
        
        // 手動更新ボタン
        $('#refreshData').click(function() {
            $(this).find('i').addClass('fa-spin');
            loadDashboardData();
            
            setTimeout(() => {
                $(this).find('i').removeClass('fa-spin');
            }, 1000);
        });
        
        // 期間変更
        $('.dropdown-item[data-period]').click(function(e) {
            e.preventDefault();
            const period = $(this).data('period');
            
            // アクティブ状態更新
            $('.dropdown-item').removeClass('active');
            $(this).addClass('active');
            
            // データ再読み込み
            loadDashboardData(period);
        });
        
        // 売上チャート期間変更
        $('input[name="revenueChart"]').change(function() {
            const chartType = $(this).attr('id').split('-')[1];
            updateRevenueChart(chartType);
        });
    });
    
    // ダッシュボードデータ読み込み
    function loadDashboardData(period = 'month') {
        // KPI読み込み
        loadKPIData(period);
        
        // アラート読み込み
        loadAlerts();
        
        // アクティビティ読み込み
        loadRecentActivities();
        
        // 今日の予定読み込み
        loadTodaySchedule();
        
        // プロジェクト進捗読み込み
        loadProjectProgress();
        
        // 警備員稼働状況読み込み
        loadGuardStatus();
        
        // チャート読み込み
        loadCharts(period);
    }
    
    // KPIデータ読み込み
    function loadKPIData(period) {
        $.get(`{{ route('dashboard.stats') }}?period=${period}`)
            .done(function(data) {
                // 売上
                $('#totalRevenue').html(`¥${data.revenue.total.toLocaleString()}`);
                $('#revenueChange').html(`
                    <i class="bi bi-arrow-${data.revenue.change >= 0 ? 'up' : 'down'} me-1"></i>
                    ${Math.abs(data.revenue.change)}% (前期比)
                `).removeClass('text-success text-danger')
                  .addClass(data.revenue.change >= 0 ? 'text-success' : 'text-danger');
                
                // 案件数
                $('#activeProjects').html(data.projects.active);
                $('#projectsChange').html(`新規: ${data.projects.new}件`);
                
                // 警備員数
                $('#totalGuards').html(data.guards.total);
                $('#guardsChange').html(`稼働中: ${data.guards.active}名`);
                
                // シフト数
                $('#monthlyShifts').html(data.shifts.total);
                $('#shiftsChange').html(`完了: ${data.shifts.completed}件`);
            })
            .fail(function() {
                console.error('KPIデータの読み込みに失敗しました');
            });
    }
    
    // アラート読み込み
    function loadAlerts() {
        $.get('{{ route("dashboard.alerts") }}')
            .done(function(alerts) {
                const alertsHtml = alerts.map(alert => `
                    <div class="col-12 mb-2">
                        <div class="alert alert-${getAlertClass(alert.type)} alert-dismissible fade show" role="alert">
                            <i class="bi ${getAlertIcon(alert.type)} me-2"></i>
                            <strong>${alert.title}</strong> ${alert.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                `).join('');
                
                $('#alertsSection').html(alertsHtml);
            });
    }
    
    // 最近のアクティビティ読み込み
    function loadRecentActivities() {
        // デモデータ（実際はAPIから取得）
        const activities = [
            { type: 'customer', message: '新規顧客「ABC商事」が登録されました', time: '5分前', user: '田中' },
            { type: 'project', message: 'プロジェクト「渋谷ビル警備」が開始されました', time: '15分前', user: '佐藤' },
            { type: 'guard', message: '警備員「山田太郎」のシフトが承認されました', time: '32分前', user: '鈴木' },
            { type: 'invoice', message: '請求書 #INV-2024-001 が送付されました', time: '1時間前', user: '田中' },
            { type: 'contract', message: '契約「XYZ会社」の更新が完了しました', time: '2時間前', user: '佐藤' }
        ];
        
        const activitiesHtml = activities.map(activity => `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="me-3">
                        <i class="bi ${getActivityIcon(activity.type)} text-${getActivityColor(activity.type)} me-2"></i>
                        <span>${activity.message}</span>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">${activity.time}</small><br>
                        <small class="text-muted">by ${activity.user}</small>
                    </div>
                </div>
            </div>
        `).join('');
        
        $('#recentActivities').html(activitiesHtml);
    }
    
    // 今日の予定読み込み
    function loadTodaySchedule() {
        // デモデータ（実際はAPIから取得）
        const schedule = [
            { time: '09:00-17:00', title: '新宿オフィス警備', guards: 3, status: 'active' },
            { time: '10:00-18:00', title: '渋谷商業ビル警備', guards: 2, status: 'active' },
            { time: '14:00-22:00', title: '池袋イベント警備', guards: 5, status: 'pending' },
            { time: '18:00-06:00', title: '品川工事現場警備', guards: 4, status: 'scheduled' }
        ];
        
        const scheduleHtml = schedule.map(item => `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">${item.title}</div>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>${item.time} 
                            <i class="bi bi-people ms-2 me-1"></i>${item.guards}名
                        </small>
                    </div>
                    <span class="badge bg-${getStatusColor(item.status)}">${getStatusText(item.status)}</span>
                </div>
            </div>
        `).join('');
        
        $('#todaySchedule').html(scheduleHtml);
    }
    
    // プロジェクト進捗読み込み
    function loadProjectProgress() {
        // デモデータ（実際はAPIから取得）
        const projects = [
            { name: '渋谷ビル警備', progress: 85 },
            { name: '新宿オフィス警備', progress: 70 },
            { name: '池袋イベント警備', progress: 45 },
            { name: '品川工事現場警備', progress: 90 }
        ];
        
        const progressHtml = projects.map(project => `
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <small class="fw-bold">${project.name}</small>
                    <small>${project.progress}%</small>
                </div>
                <div class="progress">
                    <div class="progress-bar" style="width: ${project.progress}%"></div>
                </div>
            </div>
        `).join('');
        
        $('#projectProgress').html(progressHtml);
    }
    
    // 警備員稼働状況読み込み
    function loadGuardStatus() {
        // デモデータ（実際はAPIから取得）
        const guardStatus = {
            active: 45,
            break: 8,
            off: 12,
            total: 65
        };
        
        const statusHtml = `
            <div class="row text-center">
                <div class="col-3">
                    <div class="text-success fw-bold fs-4">${guardStatus.active}</div>
                    <small class="text-muted">稼働中</small>
                </div>
                <div class="col-3">
                    <div class="text-warning fw-bold fs-4">${guardStatus.break}</div>
                    <small class="text-muted">休憩中</small>
                </div>
                <div class="col-3">
                    <div class="text-secondary fw-bold fs-4">${guardStatus.off}</div>
                    <small class="text-muted">待機中</small>
                </div>
                <div class="col-3">
                    <div class="text-primary fw-bold fs-4">${guardStatus.total}</div>
                    <small class="text-muted">総数</small>
                </div>
            </div>
        `;
        
        $('#guardStatus').html(statusHtml);
    }
    
    // チャート読み込み
    function loadCharts(period) {
        initRevenueChart();
        initCompanyRevenueChart();
        initGoalChart();
    }
    
    // 売上推移チャート
    function initRevenueChart() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        // 既存のチャートを破棄
        if (window.revenueChart) {
            window.revenueChart.destroy();
        }
        
        window.revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['1月', '2月', '3月', '4月', '5月', '6月'],
                datasets: [{
                    label: '売上（万円）',
                    data: [1200, 1350, 1180, 1420, 1680, 1550],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + '万円';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // 会社別売上円グラフ
    function initCompanyRevenueChart() {
        const ctx = document.getElementById('companyRevenueChart').getContext('2d');
        
        if (window.companyRevenueChart) {
            window.companyRevenueChart.destroy();
        }
        
        const data = {
            labels: ['東央警備', 'Nikkei HD', '全日本EP'],
            datasets: [{
                data: [35, 40, 25],
                backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b']
            }]
        };
        
        window.companyRevenueChart = new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // 凡例を手動作成
        const legendHtml = data.labels.map((label, index) => `
            <div class="d-flex align-items-center mb-1">
                <div class="rounded-circle me-2" style="width: 12px; height: 12px; background-color: ${data.datasets[0].backgroundColor[index]}"></div>
                <small>${label}: ${data.datasets[0].data[index]}%</small>
            </div>
        `).join('');
        
        $('#companyLegend').html(legendHtml);
    }
    
    // 目標達成率チャート
    function initGoalChart() {
        const ctx = document.getElementById('goalChart').getContext('2d');
        const percentage = 78;
        
        if (window.goalChart) {
            window.goalChart.destroy();
        }
        
        window.goalChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [percentage, 100 - percentage],
                    backgroundColor: ['#22c55e', '#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        $('#goalPercentage').text(percentage + '%');
        $('#monthlyGoal').text('¥1,500万');
        $('#monthlyActual').text('¥1,170万');
    }
    
    // ヘルパー関数
    function getAlertClass(type) {
        const classes = {
            'error': 'danger',
            'warning': 'warning',
            'info': 'info',
            'success': 'success'
        };
        return classes[type] || 'info';
    }
    
    function getAlertIcon(type) {
        const icons = {
            'error': 'bi-exclamation-triangle-fill',
            'warning': 'bi-exclamation-triangle',
            'info': 'bi-info-circle-fill',
            'success': 'bi-check-circle-fill'
        };
        return icons[type] || 'bi-info-circle';
    }
    
    function getActivityIcon(type) {
        const icons = {
            'customer': 'bi-person-plus',
            'project': 'bi-briefcase',
            'guard': 'bi-person-badge',
            'invoice': 'bi-receipt',
            'contract': 'bi-file-text'
        };
        return icons[type] || 'bi-circle';
    }
    
    function getActivityColor(type) {
        const colors = {
            'customer': 'primary',
            'project': 'success',
            'guard': 'warning',
            'invoice': 'info',
            'contract': 'secondary'
        };
        return colors[type] || 'secondary';
    }
    
    function getStatusColor(status) {
        const colors = {
            'active': 'success',
            'pending': 'warning',
            'scheduled': 'info',
            'completed': 'secondary'
        };
        return colors[status] || 'secondary';
    }
    
    function getStatusText(status) {
        const texts = {
            'active': '実行中',
            'pending': '準備中',
            'scheduled': '予定',
            'completed': '完了'
        };
        return texts[status] || status;
    }
</script>
@endpush
@endsection
