@extends('layouts.app')

@section('title', 'ダッシュボード - 警備統合管理システム')

@section('content')
<div class="container-fluid">
    <!-- 緊急時ヘッダー -->
    <div class="row mb-3" id="emergencyHeader" style="display: none;">
        <div class="col-12">
            <div class="alert alert-danger border-0 shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3 text-danger"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-1">
                            <i class="fas fa-broadcast-tower me-1"></i>
                            緊急事態発生
                        </h5>
                        <p class="mb-0" id="emergencyMessage">緊急事態が発生しました。管理者に連絡してください。</p>
                    </div>
                    <div class="ms-3">
                        <button class="btn btn-outline-danger" id="emergencyResponse">
                            <i class="fas fa-phone me-1"></i>
                            緊急対応
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-shield-alt me-2 text-primary"></i>
                        警備統合管理ダッシュボード
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-user me-1"></i>{{ Auth::user()->name }}さん 
                        <span class="mx-2">|</span>
                        <i class="fas fa-building me-1"></i>{{ Auth::user()->company->name ?? '所属会社' }}
                        <span class="mx-2">|</span>
                        <i class="fas fa-clock me-1"></i><span id="currentDateTime"></span>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <!-- セキュリティステータス -->
                    <div class="btn btn-outline-success" id="securityStatus">
                        <i class="fas fa-shield-check me-1"></i>
                        <span class="security-status-text">セキュア</span>
                    </div>
                    
                    <!-- 更新ボタン -->
                    <button class="btn btn-outline-primary" id="refreshData">
                        <i class="fas fa-sync-alt me-1"></i>
                        更新
                    </button>
                    
                    <!-- 期間設定 -->
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar-alt me-1"></i>
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

                    <!-- システム管理（管理者のみ） -->
                    @if(Auth::user()->role === 'admin')
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>
                            システム
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="showSystemHealth()">
                                <i class="fas fa-heartbeat me-1"></i>システム監視
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="showAuditLog()">
                                <i class="fas fa-clipboard-list me-1"></i>監査ログ
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="showSecurityReport()">
                                <i class="fas fa-shield-virus me-1"></i>セキュリティレポート
                            </a></li>
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- アラート・通知セクション -->
    <div class="row mb-4" id="alertsSection">
        <!-- アラートはJavaScriptで動的に表示 -->
    </div>
    
    <!-- 警備業界特化KPIカードセクション -->
    <div class="row mb-4">
        <!-- 総売上 -->
        <div class="col-xl-2 col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-0 shadow-sm kpi-card">
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
                        <div class="kpi-icon bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-yen-sign fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- アクティブ警備員 -->
        <div class="col-xl-2 col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-0 shadow-sm kpi-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">稼働中警備員</h6>
                            <h3 class="card-title mb-1" id="activeGuards">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-6"></span>
                                </div>
                            </h3>
                            <small class="text-success" id="guardsChange">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-8"></span>
                                </div>
                            </small>
                        </div>
                        <div class="kpi-icon bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-user-shield fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 進行中案件 -->
        <div class="col-xl-2 col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-0 shadow-sm kpi-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">進行中案件</h6>
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
                        <div class="kpi-icon bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-briefcase fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 本日シフト -->
        <div class="col-xl-2 col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-0 shadow-sm kpi-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">本日シフト</h6>
                            <h3 class="card-title mb-1" id="todayShifts">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-6"></span>
                                </div>
                            </h3>
                            <small class="text-secondary" id="shiftsStatus">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-8"></span>
                                </div>
                            </small>
                        </div>
                        <div class="kpi-icon bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-calendar-check fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 事故・インシデント -->
        <div class="col-xl-2 col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-0 shadow-sm kpi-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">事故・インシデント</h6>
                            <h3 class="card-title mb-1" id="incidentCount">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-6"></span>
                                </div>
                            </h3>
                            <small class="text-danger" id="incidentStatus">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-8"></span>
                                </div>
                            </small>
                        </div>
                        <div class="kpi-icon bg-danger bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-exclamation-triangle fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- コンプライアンス評価 -->
        <div class="col-xl-2 col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-0 shadow-sm kpi-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">コンプライアンス</h6>
                            <h3 class="card-title mb-1" id="complianceScore">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-6"></span>
                                </div>
                            </h3>
                            <small class="text-success" id="complianceStatus">
                                <div class="placeholder-glow">
                                    <span class="placeholder col-8"></span>
                                </div>
                            </small>
                        </div>
                        <div class="kpi-icon bg-secondary bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-gavel fs-4 text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 重要情報・リアルタイム監視セクション -->
    <div class="row mb-4">
        <!-- リアルタイム警備員位置 -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                        リアルタイム警備員位置
                    </h5>
                    <div class="status-indicator status-live"></div>
                </div>
                <div class="card-body p-0">
                    <div class="guard-locations" id="guardLocations">
                        <div class="location-item p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">山田太郎</div>
                                    <small class="text-muted">
                                        <i class="fas fa-building me-1"></i>新宿オフィスビル
                                        <span class="ms-2">
                                            <i class="fas fa-clock me-1"></i>09:15更新
                                        </span>
                                    </small>
                                </div>
                                <span class="badge bg-success">
                                    <i class="fas fa-shield-check me-1"></i>正常
                                </span>
                            </div>
                        </div>
                        <!-- 他の警備員位置もここに表示 -->
                    </div>
                    <div class="card-footer text-center">
                        <button class="btn btn-sm btn-outline-primary" onclick="showFullMap()">
                            <i class="fas fa-expand me-1"></i>全体マップ表示
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 緊急時対応状況 -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-circle me-2 text-warning"></i>
                        緊急時対応状況
                    </h5>
                    <button class="btn btn-sm btn-outline-danger" onclick="showEmergencyPanel()">
                        <i class="fas fa-phone me-1"></i>緊急対応
                    </button>
                </div>
                <div class="card-body">
                    <div id="emergencyStatus">
                        <div class="alert alert-success border-left-success">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <div>
                                    <strong>正常運用中</strong><br>
                                    <small>緊急事態は発生していません</small>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mb-2">対応履歴（24時間）</h6>
                        <div class="emergency-history">
                            <div class="d-flex justify-content-between mb-2">
                                <span>緊急通報訓練</span>
                                <small class="text-muted">昨日 14:30</small>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>設備点検完了</span>
                                <small class="text-muted">昨日 10:00</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- システム監視・セキュリティ -->
        <div class="col-lg-4 col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-virus me-2 text-info"></i>
                        システム監視
                    </h5>
                </div>
                <div class="card-body">
                    <div class="system-metrics">
                        <div class="row">
                            <div class="col-6 text-center mb-3">
                                <div class="metric-value text-success fs-4 fw-bold">99.9%</div>
                                <small class="text-muted">稼働率</small>
                            </div>
                            <div class="col-6 text-center mb-3">
                                <div class="metric-value text-primary fs-4 fw-bold">15ms</div>
                                <small class="text-muted">応答時間</small>
                            </div>
                            <div class="col-6 text-center">
                                <div class="metric-value text-warning fs-4 fw-bold">128</div>
                                <small class="text-muted">接続数</small>
                            </div>
                            <div class="col-6 text-center">
                                <div class="metric-value text-info fs-4 fw-bold">0</div>
                                <small class="text-muted">エラー</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-2">セキュリティ状況</h6>
                    <div class="security-metrics">
                        <div class="d-flex justify-content-between mb-2">
                            <span>ファイアウォール</span>
                            <span class="badge bg-success">正常</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>侵入検知</span>
                            <span class="badge bg-success">正常</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>脅威スキャン</span>
                            <span class="badge bg-success">クリーン</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- チャート・分析セクション -->
    <div class="row mb-4">
        <!-- 警備業務実績推移 -->
        <div class="col-lg-8 col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        警備業務実績推移
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="performanceChart" id="perf-revenue" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="perf-revenue">売上</label>
                        
                        <input type="radio" class="btn-check" name="performanceChart" id="perf-hours" autocomplete="off">
                        <label class="btn btn-outline-primary" for="perf-hours">勤務時間</label>
                        
                        <input type="radio" class="btn-check" name="performanceChart" id="perf-incidents" autocomplete="off">
                        <label class="btn btn-outline-primary" for="perf-incidents">インシデント</label>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- 警備会社別実績 -->
        <div class="col-lg-4 col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-building me-2"></i>
                        警備会社別実績
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="companyPerformanceChart" height="250"></canvas>
                    <div class="mt-3" id="companyLegend">
                        <!-- 凡例はJavaScriptで動的に生成 -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 詳細情報・運用管理セクション -->
    <div class="row mb-4">
        <!-- 本日の警備スケジュール -->
        <div class="col-lg-6 col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-day me-2"></i>
                        本日の警備スケジュール
                    </h5>
                    <a href="{{ route('shifts.calendar') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>カレンダー表示
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="todaySchedule">
                        <!-- 予定はJavaScriptで動的に表示 -->
                    </div>
                </div>
            </div>
        </div>

        <!-- 最近のアクティビティ・監査ログ -->
        <div class="col-lg-6 col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        最近のアクティビティ
                    </h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active" data-activity-type="all">すべて</button>
                        <button class="btn btn-outline-warning" data-activity-type="security">セキュリティ</button>
                        <button class="btn btn-outline-danger" data-activity-type="alert">アラート</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="recentActivities">
                        <!-- アクティビティはJavaScriptで動的に表示 -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 運用管理・分析セクション -->
    <div class="row mb-4">
        <!-- 警備員パフォーマンス分析 -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-cog me-2"></i>
                        警備員パフォーマンス
                    </h5>
                </div>
                <div class="card-body">
                    <div id="guardPerformance">
                        <!-- パフォーマンスデータはJavaScriptで動的に表示 -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 案件進捗管理 -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>
                        案件進捗管理
                    </h5>
                </div>
                <div class="card-body">
                    <div id="projectProgress">
                        <!-- 進捗はJavaScriptで動的に表示 -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 月間売上目標達成率 -->
        <div class="col-lg-4 col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bullseye me-2"></i>
                        月間売上目標
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
    
    <!-- クイックアクション・機能ショートカットセクション -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        クイックアクション
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- 緊急時アクション -->
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 quick-action-btn" onclick="triggerEmergencyAlert()">
                                <i class="fas fa-exclamation-triangle fs-4 mb-2"></i>
                                <span>緊急通報</span>
                            </button>
                        </div>

                        <!-- 顧客登録 -->
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('customers.create') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 quick-action-btn">
                                <i class="fas fa-user-plus fs-4 mb-2"></i>
                                <span>顧客登録</span>
                            </a>
                        </div>
                        
                        <!-- 案件作成 -->
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('projects.create') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 quick-action-btn">
                                <i class="fas fa-briefcase fs-4 mb-2"></i>
                                <span>案件作成</span>
                            </a>
                        </div>
                        
                        <!-- 警備員登録 -->
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('guards.create') }}" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 quick-action-btn">
                                <i class="fas fa-user-shield fs-4 mb-2"></i>
                                <span>警備員登録</span>
                            </a>
                        </div>
                        
                        <!-- シフト作成 -->
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('shifts.create') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 quick-action-btn">
                                <i class="fas fa-calendar-plus fs-4 mb-2"></i>
                                <span>シフト作成</span>
                            </a>
                        </div>
                        
                        <!-- 見積作成 -->
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('quotations.create') }}" class="btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 quick-action-btn">
                                <i class="fas fa-file-contract fs-4 mb-2"></i>
                                <span>見積作成</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- 第二行：より高度な機能 -->
                    <div class="row g-3 mt-2">
                        <!-- 日報作成 -->
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('daily_reports.create') }}" class="btn btn-outline-dark w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 quick-action-btn">
                                <i class="fas fa-clipboard-list fs-4 mb-2"></i>
                                <span>日報作成</span>
                            </a>
                        </div>

                        <!-- 勤怠記録 -->
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('attendances.create') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 quick-action-btn">
                                <i class="fas fa-clock fs-4 mb-2"></i>
                                <span>勤怠記録</span>
                            </a>
                        </div>

                        <!-- 請求書作成 -->
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('invoices.create') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 quick-action-btn">
                                <i class="fas fa-file-invoice fs-4 mb-2"></i>
                                <span>請求書作成</span>
                            </a>
                        </div>

                        <!-- 全体レポート -->
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 quick-action-btn" onclick="generateComprehensiveReport()">
                                <i class="fas fa-chart-bar fs-4 mb-2"></i>
                                <span>全体レポート</span>
                            </button>
                        </div>

                        <!-- 売上分析 -->
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('dashboard.revenue.analysis') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 quick-action-btn">
                                <i class="fas fa-chart-line fs-4 mb-2"></i>
                                <span>売上分析</span>
                            </a>
                        </div>

                        <!-- 設定・管理 -->
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button class="btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 quick-action-btn" data-bs-toggle="modal" data-bs-target="#settingsModal">
                                <i class="fas fa-cogs fs-4 mb-2"></i>
                                <span>設定・管理</span>
                            </button>
                        </div>

                        <!-- ヘルプ・サポート -->
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <button class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 quick-action-btn" data-bs-toggle="modal" data-bs-target="#helpModal">
                                <i class="fas fa-question-circle fs-4 mb-2"></i>
                                <span>ヘルプ</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 設定モーダル -->
<div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-cogs me-2"></i>システム設定
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>ダッシュボード設定</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="autoRefresh" checked>
                            <label class="form-check-label" for="autoRefresh">
                                自動更新を有効にする
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="showAnimations" checked>
                            <label class="form-check-label" for="showAnimations">
                                アニメーションを表示
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="compactView">
                            <label class="form-check-label" for="compactView">
                                コンパクト表示モード
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>通知設定</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="emergencyNotifications" checked>
                            <label class="form-check-label" for="emergencyNotifications">
                                緊急時通知
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="shiftNotifications" checked>
                            <label class="form-check-label" for="shiftNotifications">
                                シフト関連通知
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="systemNotifications">
                            <label class="form-check-label" for="systemNotifications">
                                システム通知
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" onclick="saveSettings()">設定を保存</button>
            </div>
        </div>
    </div>
</div>

<!-- ヘルプモーダル -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle me-2"></i>ヘルプ・サポート
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>警備統合管理システム</strong> - 警備業法準拠の総合管理システムです
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6>基本操作</h6>
                        <ul class="small">
                            <li>ダッシュボードは自動更新されます</li>
                            <li>緊急時は赤い「緊急通報」ボタンを使用</li>
                            <li>期間設定で表示データを変更可能</li>
                            <li>KPIカードにはリアルタイムデータが表示</li>
                        </ul>

                        <h6>セキュリティ</h6>
                        <ul class="small">
                            <li>全ての操作はログに記録されます</li>
                            <li>異常検知時は自動アラート</li>
                            <li>定期的なパスワード変更を推奨</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>トラブルシューティング</h6>
                        <ul class="small">
                            <li>データが表示されない場合は更新ボタンをクリック</li>
                            <li>エラーが発生した場合は管理者に連絡</li>
                            <li>ブラウザのキャッシュをクリアしてみてください</li>
                        </ul>

                        <h6>サポート連絡先</h6>
                        <div class="alert alert-warning">
                            <strong>緊急時:</strong> 080-1234-5678<br>
                            <strong>一般サポート:</strong> support@security-system.jp<br>
                            <strong>システム管理者:</strong> admin@security-system.jp
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" onclick="contactSupport()">サポートに連絡</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
body {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.placeholder {
    background-color: #e9ecef;
    opacity: 0.5;
}

.kpi-card {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    border-left-color: #3b82f6;
}

.kpi-icon {
    position: relative;
    overflow: hidden;
}

.kpi-icon::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
    transform: rotate(45deg);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.status-live {
    background: #ef4444;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.3);
    animation: livePulse 2s infinite;
}

@keyframes livePulse {
    0% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
    }
}

.location-item:hover {
    background-color: #f8f9fa;
}

.progress {
    height: 8px;
    border-radius: 4px;
}

.list-group-item {
    border: none;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.list-group-item:last-child {
    border-bottom: none;
}

.quick-action-btn {
    min-height: 100px;
    transition: all 0.3s ease;
    border-radius: 12px;
    position: relative;
    overflow: hidden;
}

.quick-action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.5s ease;
}

.quick-action-btn:hover::before {
    left: 100%;
}

.quick-action-btn:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.alert-dismissible .btn-close {
    padding: 0.5rem 0.75rem;
}

.border-left-success {
    border-left: 4px solid #22c55e;
}

.border-left-warning {
    border-left: 4px solid #f59e0b;
}

.border-left-danger {
    border-left: 4px solid #ef4444;
}

.border-left-info {
    border-left: 4px solid #3b82f6;
}

.metric-value {
    font-family: 'Courier New', monospace;
    font-weight: bold;
}

.security-metrics .badge {
    font-size: 0.75rem;
}

.emergency-history {
    max-height: 120px;
    overflow-y: auto;
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid rgba(0,0,0,0.1);
    border-radius: 15px 15px 0 0 !important;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .btn-group-sm .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .kpi-card .card-body {
        padding: 1rem 0.75rem;
    }
    
    .quick-action-btn {
        min-height: 80px;
        padding: 0.75rem !important;
    }
    
    .quick-action-btn i {
        font-size: 1.2rem !important;
    }
}

/* ダークモード対応 */
@media (prefers-color-scheme: dark) {
    body {
        background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
    }
    
    .card {
        background-color: rgba(31, 41, 55, 0.9);
        color: #f9fafb;
    }
    
    .card-header {
        background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
        border-bottom-color: rgba(255,255,255,0.1);
    }
    
    .list-group-item {
        background-color: transparent;
        border-bottom-color: rgba(255,255,255,0.1);
        color: #f9fafb;
    }
    
    .list-group-item:hover {
        background-color: rgba(55, 65, 81, 0.5);
    }
}

/* 印刷対応 */
@media print {
    body {
        background: white !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #000 !important;
        break-inside: avoid;
    }
    
    .btn, .dropdown, .modal {
        display: none !important;
    }
    
    .quick-action-btn {
        display: none !important;
    }
    
    .alert {
        border: 1px solid #000 !important;
    }
}

/* アクセシビリティ */
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* 高コントラストモード */
@media (prefers-contrast: high) {
    .card {
        border: 2px solid #000 !important;
    }
    
    .btn {
        border-width: 2px !important;
    }
    
    .badge {
        border: 1px solid #000;
    }
}
</style>
@endpush

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // 現在時刻の更新
    function updateDateTime() {
        const now = new Date();
        const dateTimeString = now.toLocaleString('ja-JP', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            weekday: 'short'
        });
        $('#currentDateTime').text(dateTimeString);
    }
    
    updateDateTime();
    setInterval(updateDateTime, 1000);

    // 初期データ読み込み
    loadDashboardData();
    
    // 自動更新設定（30秒間隔）
    setInterval(loadDashboardData, 30000);
    
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
    
    // アクティビティフィルター
    $('button[data-activity-type]').click(function() {
        $('button[data-activity-type]').removeClass('active');
        $(this).addClass('active');
        
        const type = $(this).data('activity-type');
        loadRecentActivities(type);
    });
    
    // パフォーマンスチャート期間変更
    $('input[name="performanceChart"]').change(function() {
        const chartType = $(this).attr('id').split('-')[1];
        updatePerformanceChart(chartType);
    });

    // セキュリティ状態監視
    monitorSecurityStatus();
    setInterval(monitorSecurityStatus, 60000);
});

// ダッシュボードデータ読み込み
function loadDashboardData(period = 'month') {
    loadKPIData(period);
    loadAlerts();
    loadRecentActivities();
    loadTodaySchedule();
    loadGuardLocations();
    loadProjectProgress();
    loadGuardPerformance();
    loadCharts(period);
}

// KPIデータ読み込み
function loadKPIData(period) {
    // デモデータ（実際はAPIから取得）
    const kpiData = {
        totalRevenue: { value: 15750000, change: 12.5 },
        activeGuards: { value: 45, change: 3 },
        activeProjects: { value: 8, change: 1 },
        todayShifts: { value: 23, completed: 18 },
        incidentCount: { value: 0, status: '正常' },
        complianceScore: { value: 98, status: '優良' }
    };
    
    // 売上
    $('#totalRevenue').html(`¥${kpiData.totalRevenue.value.toLocaleString()}`);
    $('#revenueChange').html(`
        <i class="fas fa-arrow-up me-1"></i>
        ${kpiData.totalRevenue.change}% (前期比)
    `).removeClass('text-danger').addClass('text-success');
    
    // 稼働中警備員
    $('#activeGuards').html(`${kpiData.activeGuards.value}名`);
    $('#guardsChange').html(`新規: ${kpiData.activeGuards.change}名`);
    
    // 進行中案件
    $('#activeProjects').html(`${kpiData.activeProjects.value}件`);
    $('#projectsChange').html(`新規: ${kpiData.activeProjects.change}件`);
    
    // 本日シフト
    $('#todayShifts').html(`${kpiData.todayShifts.value}件`);
    $('#shiftsStatus').html(`完了: ${kpiData.todayShifts.completed}件`);
    
    // インシデント
    $('#incidentCount').html(kpiData.incidentCount.value);
    $('#incidentStatus').html(`状況: ${kpiData.incidentCount.status}`);
    
    // コンプライアンス
    $('#complianceScore').html(`${kpiData.complianceScore.value}点`);
    $('#complianceStatus').html(`評価: ${kpiData.complianceScore.status}`);
}

// アラート読み込み
function loadAlerts() {
    // デモデータ（実際はAPIから取得）
    const alerts = [
        { 
            type: 'info', 
            title: 'システム更新通知', 
            message: '本日23:00-24:00にシステムメンテナンスを実施します' 
        },
        { 
            type: 'warning', 
            title: '警備員不足警告', 
            message: '明日のシフトで2名の警備員が不足しています' 
        }
    ];
    
    const alertsHtml = alerts.map(alert => `
        <div class="col-12 mb-2">
            <div class="alert alert-${getAlertClass(alert.type)} alert-dismissible fade show" role="alert">
                <i class="fas ${getAlertIcon(alert.type)} me-2"></i>
                <strong>${alert.title}</strong> ${alert.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    `).join('');
    
    $('#alertsSection').html(alertsHtml);
}

// 警備員位置情報読み込み
function loadGuardLocations() {
    const guards = [
        { name: '山田太郎', location: '新宿オフィスビル', status: 'normal', lastUpdate: '09:15' },
        { name: '田中花子', location: '渋谷商業ビル', status: 'normal', lastUpdate: '09:12' },
        { name: '佐藤次郎', location: '池袋イベント会場', status: 'alert', lastUpdate: '09:18' },
        { name: '鈴木美咲', location: '品川工事現場', status: 'normal', lastUpdate: '09:10' }
    ];
    
    const guardLocationsHtml = guards.map(guard => `
        <div class="location-item p-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold">${guard.name}</div>
                    <small class="text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i>${guard.location}
                        <span class="ms-2">
                            <i class="fas fa-clock me-1"></i>${guard.lastUpdate}更新
                        </span>
                    </small>
                </div>
                <span class="badge bg-${guard.status === 'normal' ? 'success' : 'warning'}">
                    <i class="fas fa-${guard.status === 'normal' ? 'check' : 'exclamation-triangle'} me-1"></i>
                    ${guard.status === 'normal' ? '正常' : '要確認'}
                </span>
            </div>
        </div>
    `).join('');
    
    $('#guardLocations').html(guardLocationsHtml);
}

// 本日スケジュール読み込み
function loadTodaySchedule() {
    const schedule = [
        { 
            time: '09:00-17:00', 
            title: '新宿オフィス警備', 
            guards: 3, 
            status: 'active',
            company: '㈲東央警備'
        },
        { 
            time: '10:00-18:00', 
            title: '渋谷商業ビル警備', 
            guards: 2, 
            status: 'active',
            company: '㈱Nikkeiホールディングス'
        },
        { 
            time: '14:00-22:00', 
            title: '池袋イベント警備', 
            guards: 5, 
            status: 'pending',
            company: '㈱全日本エンタープライズ'
        },
        { 
            time: '18:00-06:00', 
            title: '品川工事現場警備', 
            guards: 4, 
            status: 'scheduled',
            company: '㈲東央警備'
        }
    ];
    
    const scheduleHtml = schedule.map(item => `
        <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="fw-bold">${item.title}</div>
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>${item.time} 
                        <i class="fas fa-users ms-2 me-1"></i>${item.guards}名
                    </small>
                    <br>
                    <small class="text-info">${item.company}</small>
                </div>
                <span class="badge bg-${getStatusColor(item.status)}">${getStatusText(item.status)}</span>
            </div>
        </div>
    `).join('');
    
    $('#todaySchedule').html(scheduleHtml);
}

// 最近のアクティビティ読み込み
function loadRecentActivities(type = 'all') {
    const activities = [
        { 
            type: 'security', 
            message: '警備員「山田太郎」が異常を報告しました', 
            time: '5分前', 
            user: '山田太郎',
            priority: 'high'
        },
        { 
            type: 'system', 
            message: '新規顧客「ABC商事」が登録されました', 
            time: '15分前', 
            user: '田中管理者',
            priority: 'normal'
        },
        { 
            type: 'security', 
            message: 'シフト変更により人員配置を調整しました', 
            time: '32分前', 
            user: '佐藤主任',
            priority: 'normal'
        },
        { 
            type: 'alert', 
            message: 'システム負荷が一時的に上昇しました', 
            time: '1時間前', 
            user: 'システム',
            priority: 'low'
        },
        { 
            type: 'system', 
            message: '月次レポートが自動生成されました', 
            time: '2時間前', 
            user: 'システム',
            priority: 'normal'
        }
    ];
    
    // フィルタリング
    const filteredActivities = type === 'all' ? activities : 
        activities.filter(activity => activity.type === type);
    
    const activitiesHtml = filteredActivities.map(activity => `
        <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-start">
                <div class="me-3">
                    <i class="fas ${getActivityIcon(activity.type)} text-${getActivityColor(activity.type)} me-2"></i>
                    <span class="${activity.priority === 'high' ? 'fw-bold' : ''}">${activity.message}</span>
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

// 警備員パフォーマンス読み込み
function loadGuardPerformance() {
    const performance = [
        { name: '山田太郎', score: 95, hours: 168, rating: 'excellent' },
        { name: '田中花子', score: 88, hours: 160, rating: 'good' },
        { name: '佐藤次郎', score: 92, hours: 152, rating: 'excellent' },
        { name: '鈴木美咲', score: 85, hours: 144, rating: 'good' }
    ];
    
    const performanceHtml = performance.map(guard => `
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <small class="fw-bold">${guard.name}</small>
                <small>${guard.score}点 (${guard.hours}h)</small>
            </div>
            <div class="progress">
                <div class="progress-bar bg-${guard.rating === 'excellent' ? 'success' : 'primary'}" 
                     style="width: ${guard.score}%"></div>
            </div>
        </div>
    `).join('');
    
    $('#guardPerformance').html(performanceHtml);
}

// 案件進捗読み込み
function loadProjectProgress() {
    const projects = [
        { name: '渋谷ビル警備', progress: 85, deadline: '2024-06-30' },
        { name: '新宿オフィス警備', progress: 70, deadline: '2024-07-15' },
        { name: '池袋イベント警備', progress: 45, deadline: '2024-06-15' },
        { name: '品川工事現場警備', progress: 90, deadline: '2024-08-30' }
    ];
    
    const progressHtml = projects.map(project => `
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <small class="fw-bold">${project.name}</small>
                <small>${project.progress}%</small>
            </div>
            <div class="progress">
                <div class="progress-bar ${project.progress >= 80 ? 'bg-success' : project.progress >= 50 ? 'bg-warning' : 'bg-danger'}" 
                     style="width: ${project.progress}%"></div>
            </div>
            <small class="text-muted">期限: ${project.deadline}</small>
        </div>
    `).join('');
    
    $('#projectProgress').html(progressHtml);
}

// チャート読み込み
function loadCharts(period) {
    initPerformanceChart();
    initCompanyPerformanceChart();
    initGoalChart();
}

// パフォーマンスチャート
function initPerformanceChart() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    if (window.performanceChart) {
        window.performanceChart.destroy();
    }
    
    window.performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['1月', '2月', '3月', '4月', '5月', '6月'],
            datasets: [{
                label: '売上（万円）',
                data: [1200, 1350, 1180, 1420, 1680, 1575],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: '勤務時間（時間）',
                data: [2400, 2650, 2380, 2720, 2950, 2850],
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: '月'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: '売上（万円）'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: '勤務時間（時間）'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
}

// 会社別パフォーマンスチャート
function initCompanyPerformanceChart() {
    const ctx = document.getElementById('companyPerformanceChart').getContext('2d');
    
    if (window.companyPerformanceChart) {
        window.companyPerformanceChart.destroy();
    }
    
    const data = {
        labels: ['㈲東央警備', '㈱Nikkeiホールディングス', '㈱全日本エンタープライズ'],
        datasets: [{
            data: [40, 35, 25],
            backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    };
    
    window.companyPerformanceChart = new Chart(ctx, {
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
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center">
                <div class="rounded-circle me-2" style="width: 12px; height: 12px; background-color: ${data.datasets[0].backgroundColor[index]}"></div>
                <small>${label}</small>
            </div>
            <small class="fw-bold">${data.datasets[0].data[index]}%</small>
        </div>
    `).join('');
    
    $('#companyLegend').html(legendHtml);
}

// 目標達成率チャート
function initGoalChart() {
    const ctx = document.getElementById('goalChart').getContext('2d');
    const percentage = 82;
    
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
    $('#monthlyGoal').text('¥1,800万');
    $('#monthlyActual').text('¥1,476万');
}

// セキュリティ状態監視
function monitorSecurityStatus() {
    // セキュリティ状態をチェック（デモ）
    const securityLevel = Math.random() > 0.1 ? 'secure' : 'warning';
    
    const statusBtn = $('#securityStatus');
    if (securityLevel === 'secure') {
        statusBtn.removeClass('btn-outline-warning btn-outline-danger')
               .addClass('btn-outline-success');
        statusBtn.find('.security-status-text').text('セキュア');
        statusBtn.find('i').removeClass('fa-exclamation-triangle fa-times-circle')
               .addClass('fa-shield-check');
    } else {
        statusBtn.removeClass('btn-outline-success btn-outline-danger')
               .addClass('btn-outline-warning');
        statusBtn.find('.security-status-text').text('警告');
        statusBtn.find('i').removeClass('fa-shield-check fa-times-circle')
               .addClass('fa-exclamation-triangle');
    }
}

// 緊急通報機能
function triggerEmergencyAlert() {
    if (confirm('緊急通報を発信します。\n\n※この操作により、管理者と関係機関に即座に通知されます。\n※誤報の場合は法的責任が発生する場合があります。\n\n本当に緊急事態ですか？')) {
        // 緊急時ヘッダーを表示
        $('#emergencyHeader').show();
        $('#emergencyMessage').text('緊急通報が発信されました。管理者に連絡中...');
        
        // サーバーに緊急通報を送信
        fetch('/emergency/alert', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({
                type: 'manual_emergency',
                user_id: '{{ Auth::id() }}',
                timestamp: new Date().toISOString(),
                location: 'ダッシュボード'
            })
        }).then(response => {
            if (response.ok) {
                $('#emergencyMessage').text('緊急通報が正常に送信されました。管理者からの連絡をお待ちください。');
            } else {
                $('#emergencyMessage').text('緊急通報の送信に失敗しました。直接電話でご連絡ください: 080-1234-5678');
            }
        }).catch(error => {
            $('#emergencyMessage').text('通信エラーが発生しました。直接電話でご連絡ください: 080-1234-5678');
        });
    }
}

// 全体レポート生成
function generateComprehensiveReport() {
    if (confirm('全体レポートを生成します。\n処理に数分かかる場合があります。続行しますか？')) {
        // レポート生成処理
        const reportButton = $('button[onclick="generateComprehensiveReport()"]');
        reportButton.prop('disabled', true);
        reportButton.html('<i class="fas fa-spinner fa-spin fs-4 mb-2"></i><span>生成中...</span>');
        
        setTimeout(() => {
            reportButton.prop('disabled', false);
            reportButton.html('<i class="fas fa-chart-bar fs-4 mb-2"></i><span>全体レポート</span>');
            alert('全体レポートが生成されました。ダウンロードが開始されます。');
            
            // レポートファイルのダウンロード
            const link = document.createElement('a');
            link.href = '/reports/comprehensive-report.pdf';
            link.download = `警備統合管理レポート_${new Date().toISOString().split('T')[0]}.pdf`;
            link.click();
        }, 3000);
    }
}

// 設定保存
function saveSettings() {
    const settings = {
        autoRefresh: $('#autoRefresh').is(':checked'),
        showAnimations: $('#showAnimations').is(':checked'),
        compactView: $('#compactView').is(':checked'),
        emergencyNotifications: $('#emergencyNotifications').is(':checked'),
        shiftNotifications: $('#shiftNotifications').is(':checked'),
        systemNotifications: $('#systemNotifications').is(':checked')
    };
    
    localStorage.setItem('dashboardSettings', JSON.stringify(settings));
    $('#settingsModal').modal('hide');
    
    // 設定を適用
    applySettings(settings);
    
    alert('設定が保存されました。');
}

// 設定適用
function applySettings(settings) {
    if (settings.compactView) {
        $('body').addClass('compact-view');
    } else {
        $('body').removeClass('compact-view');
    }
    
    if (!settings.showAnimations) {
        $('body').addClass('no-animations');
    } else {
        $('body').removeClass('no-animations');
    }
}

// サポート連絡
function contactSupport() {
    const phone = '080-1234-5678';
    const email = 'support@security-system.jp';
    
    if (confirm(`サポートに連絡します。\n\n電話: ${phone}\nメール: ${email}\n\n電話をかけますか？`)) {
        window.location.href = `tel:${phone}`;
    }
}

// フルマップ表示
function showFullMap() {
    alert('フルマップ機能は開発中です。次回のアップデートで利用可能になります。');
}

// 緊急対応パネル表示
function showEmergencyPanel() {
    alert('緊急対応パネルを表示します。\n\n※実際のシステムでは、ここに緊急時の詳細対応手順と連絡先が表示されます。');
}

// システム監視表示
function showSystemHealth() {
    alert('システム監視画面を表示します。\n\n現在のステータス:\n・CPU使用率: 45%\n・メモリ使用率: 62%\n・ディスク容量: 78%\n・ネットワーク: 正常');
}

// 監査ログ表示
function showAuditLog() {
    alert('監査ログ画面を表示します。\n\n最近のログ:\n・ユーザーログイン: 田中管理者 (09:15)\n・データ更新: シフト情報 (09:12)\n・システム設定変更: 通知設定 (08:45)');
}

// セキュリティレポート表示
function showSecurityReport() {
    alert('セキュリティレポートを表示します。\n\nセキュリティ状況:\n・脅威検知: 0件\n・不正アクセス試行: 0件\n・システム脆弱性: なし\n・最終スキャン: 今朝 06:00');
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
        'error': 'fa-exclamation-triangle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle',
        'success': 'fa-check-circle'
    };
    return icons[type] || 'fa-info-circle';
}

function getActivityIcon(type) {
    const icons = {
        'security': 'fa-shield-alt',
        'system': 'fa-cog',
        'alert': 'fa-exclamation-triangle'
    };
    return icons[type] || 'fa-circle';
}

function getActivityColor(type) {
    const colors = {
        'security': 'danger',
        'system': 'primary',
        'alert': 'warning'
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

// 初期設定読み込み
const savedSettings = localStorage.getItem('dashboardSettings');
if (savedSettings) {
    const settings = JSON.parse(savedSettings);
    
    $('#autoRefresh').prop('checked', settings.autoRefresh !== false);
    $('#showAnimations').prop('checked', settings.showAnimations !== false);
    $('#compactView').prop('checked', settings.compactView === true);
    $('#emergencyNotifications').prop('checked', settings.emergencyNotifications !== false);
    $('#shiftNotifications').prop('checked', settings.shiftNotifications !== false);
    $('#systemNotifications').prop('checked', settings.systemNotifications !== false);
    
    applySettings(settings);
}
</script>
@endpush
