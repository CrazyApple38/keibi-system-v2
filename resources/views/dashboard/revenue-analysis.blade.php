@extends('layouts.app')

@section('title', '売上分析')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        売上分析
                    </h1>
                    <p class="text-muted mb-0">包括的な売上データ分析とパフォーマンス指標</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" id="exportDataBtn">
                        <i class="fas fa-download me-1"></i>データエクスポート
                    </button>
                    <button type="button" class="btn btn-primary" id="refreshDataBtn">
                        <i class="fas fa-sync-alt me-1"></i>更新
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- フィルターパネル -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>分析条件設定
                    </h6>
                </div>
                <div class="card-body">
                    <form id="analysisFiltersForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="periodSelect" class="form-label">分析期間</label>
                            <select class="form-select" id="periodSelect" name="period">
                                <option value="month" {{ $period == 'month' ? 'selected' : '' }}>今月</option>
                                <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>今四半期</option>
                                <option value="year" {{ $period == 'year' ? 'selected' : '' }}>今年</option>
                                <option value="last_month" {{ $period == 'last_month' ? 'selected' : '' }}>先月</option>
                                <option value="last_quarter" {{ $period == 'last_quarter' ? 'selected' : '' }}>前四半期</option>
                                <option value="last_year" {{ $period == 'last_year' ? 'selected' : '' }}>昨年</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="companySelect" class="form-label">対象会社</label>
                            <select class="form-select" id="companySelect" name="company">
                                <option value="all" {{ $companyFilter == 'all' ? 'selected' : '' }}>全社統合</option>
                                <option value="1" {{ $companyFilter == '1' ? 'selected' : '' }}>㈲東央警備</option>
                                <option value="2" {{ $companyFilter == '2' ? 'selected' : '' }}>㈱Nikkeiホールディングス</option>
                                <option value="3" {{ $companyFilter == '3' ? 'selected' : '' }}>㈱全日本エンタープライズ</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="analysisTypeSelect" class="form-label">分析タイプ</label>
                            <select class="form-select" id="analysisTypeSelect" name="analysis_type">
                                <option value="overview" {{ $analysisType == 'overview' ? 'selected' : '' }}>概要分析</option>
                                <option value="trend" {{ $analysisType == 'trend' ? 'selected' : '' }}>トレンド分析</option>
                                <option value="composition" {{ $analysisType == 'composition' ? 'selected' : '' }}>構成分析</option>
                                <option value="customer" {{ $analysisType == 'customer' ? 'selected' : '' }}>顧客分析</option>
                                <option value="project" {{ $analysisType == 'project' ? 'selected' : '' }}>プロジェクト分析</option>
                                <option value="guard" {{ $analysisType == 'guard' ? 'selected' : '' }}>警備員分析</option>
                                <option value="forecast" {{ $analysisType == 'forecast' ? 'selected' : '' }}>予測分析</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>分析実行
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-1"></i>リセット
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 売上概要カード -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">今期売上</h6>
                            <h3 class="mb-0 text-primary" id="currentRevenue">
                                ¥{{ number_format($revenueAnalysisData['overview']['current_period']['total_revenue'] ?? 0) }}
                            </h3>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-yen-sign fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            前期比 
                            <span class="fw-bold {{ ($revenueAnalysisData['overview']['previous_period']['growth_rate'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ ($revenueAnalysisData['overview']['previous_period']['growth_rate'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($revenueAnalysisData['overview']['previous_period']['growth_rate'] ?? 0, 1) }}%
                            </span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">平均日次売上</h6>
                            <h3 class="mb-0 text-success" id="avgDailyRevenue">
                                ¥{{ number_format($revenueAnalysisData['overview']['current_period']['average_daily'] ?? 0) }}
                            </h3>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">請求件数: {{ number_format($revenueAnalysisData['overview']['current_period']['invoice_count'] ?? 0) }}件</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">平均請求額</h6>
                            <h3 class="mb-0 text-info" id="avgInvoiceAmount">
                                ¥{{ number_format($revenueAnalysisData['overview']['current_period']['average_invoice_amount'] ?? 0) }}
                            </h3>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-file-invoice fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">支払完了率: {{ number_format($revenueAnalysisData['overview']['metrics']['payment_completion_rate'] ?? 0, 1) }}%</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">売上変動率</h6>
                            <h3 class="mb-0 text-warning" id="revenueVolatility">
                                {{ number_format($revenueAnalysisData['overview']['metrics']['revenue_volatility'] ?? 0, 1) }}%
                            </h3>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-chart-bar fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">リスク指標</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- メイン分析エリア -->
    <div class="row">
        <!-- 売上トレンドグラフ -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-area me-2"></i>売上トレンド分析
                    </h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="trendPeriod" id="trendDaily" value="daily" checked>
                        <label class="btn btn-outline-primary" for="trendDaily">日次</label>
                        <input type="radio" class="btn-check" name="trendPeriod" id="trendWeekly" value="weekly">
                        <label class="btn btn-outline-primary" for="trendWeekly">週次</label>
                        <input type="radio" class="btn-check" name="trendPeriod" id="trendMonthly" value="monthly">
                        <label class="btn btn-outline-primary" for="trendMonthly">月次</label>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueTrendChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- 売上構成分析 -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-pie-chart me-2"></i>売上構成分析
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueCompositionChart" height="250"></canvas>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">施設警備</span>
                            <span class="fw-bold">45.5%</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 45.5%"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">イベント警備</span>
                            <span class="fw-bold">24.2%</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 24.2%"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">交通誘導</span>
                            <span class="fw-bold">18.2%</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 18.2%"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">身辺警護</span>
                            <span class="fw-bold">12.1%</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 12.1%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 詳細分析テーブル -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-table me-2"></i>詳細分析データ
                    </h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="detailView" id="customerView" value="customer" checked>
                        <label class="btn btn-outline-primary" for="customerView">顧客別</label>
                        <input type="radio" class="btn-check" name="detailView" id="projectView" value="project">
                        <label class="btn btn-outline-primary" for="projectView">プロジェクト別</label>
                        <input type="radio" class="btn-check" name="detailView" id="guardView" value="guard">
                        <label class="btn btn-outline-primary" for="guardView">警備員別</label>
                        <input type="radio" class="btn-check" name="detailView" id="companyView" value="company">
                        <label class="btn btn-outline-primary" for="companyView">会社別</label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="revenueAnalysisTable">
                            <thead class="table-light">
                                <tr id="tableHeaders">
                                    <!-- ヘッダーはJavaScriptで動的に生成 -->
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <!-- データはJavaScriptで動的に生成 -->
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            <small>表示中: <span id="displayedRows">0</span>件 / 全<span id="totalRows">0</span>件</small>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="tablePagination">
                                <!-- ページネーションはJavaScriptで生成 -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 予測・洞察パネル -->
    <div class="row mt-4">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-crystal-ball me-2"></i>売上予測
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-primary mb-1">¥{{ number_format($revenueAnalysisData['forecast_analysis']['next_month']['forecast'] ?? 0) }}</h4>
                                <p class="text-muted mb-0 small">来月予測</p>
                                <small class="text-success">信頼度 {{ $revenueAnalysisData['forecast_analysis']['next_month']['confidence'] ?? 0 }}%</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-primary mb-1">¥{{ number_format($revenueAnalysisData['forecast_analysis']['next_quarter']['forecast'] ?? 0) }}</h4>
                                <p class="text-muted mb-0 small">来四半期予測</p>
                                <small class="text-warning">信頼度 {{ $revenueAnalysisData['forecast_analysis']['next_quarter']['confidence'] ?? 0 }}%</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="text-primary mb-1">¥{{ number_format($revenueAnalysisData['forecast_analysis']['next_year']['forecast'] ?? 0) }}</h4>
                            <p class="text-muted mb-0 small">来年予測</p>
                            <small class="text-danger">信頼度 {{ $revenueAnalysisData['forecast_analysis']['next_year']['confidence'] ?? 0 }}%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2"></i>パフォーマンス洞察
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">目標達成率</span>
                        <span class="fw-bold text-success">{{ $revenueAnalysisData['performance_analysis']['vs_target'] ?? 100 }}%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">前年同期比</span>
                        <span class="fw-bold text-info">{{ $revenueAnalysisData['performance_analysis']['vs_last_year'] ?? 100 }}%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">業界平均比</span>
                        <span class="fw-bold text-warning">{{ $revenueAnalysisData['performance_analysis']['vs_industry_average'] ?? 100 }}%</span>
                    </div>
                    <div class="alert alert-info py-2">
                        <small class="mb-0">
                            <strong>総合評価:</strong> {{ $revenueAnalysisData['performance_analysis']['performance_score'] ?? 'N/A' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- データエクスポートモーダル -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">データエクスポート</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <div class="mb-3">
                        <label for="exportFormat" class="form-label">ファイル形式</label>
                        <select class="form-select" id="exportFormat" name="format">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="exportData" class="form-label">エクスポートデータ</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="exportSummary" name="data[]" value="summary" checked>
                            <label class="form-check-label" for="exportSummary">概要データ</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="exportTrend" name="data[]" value="trend">
                            <label class="form-check-label" for="exportTrend">トレンドデータ</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="exportDetail" name="data[]" value="detail">
                            <label class="form-check-label" for="exportDetail">詳細データ</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" id="executeExport">
                    <i class="fas fa-download me-1"></i>エクスポート実行
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.table-responsive {
    border-radius: 0.375rem;
}

.progress {
    background-color: #f1f3f4;
}

#revenueTrendChart, #revenueCompositionChart {
    max-height: 300px;
}

.btn-check:checked + .btn-outline-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

.modal-content {
    border-radius: 0.5rem;
}

.alert-info {
    background-color: #e7f3ff;
    border-color: #b8daff;
    color: #0c5460;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // データ変数の初期化
    const revenueAnalysisData = @json($revenueAnalysisData);
    let currentView = 'customer';
    let currentTrendPeriod = 'daily';
    
    // チャート初期化
    initializeCharts();
    
    // テーブル初期化
    initializeTable();
    
    // イベントリスナー設定
    setupEventListeners();
    
    // チャート初期化関数
    function initializeCharts() {
        // 売上トレンドチャート
        const trendCtx = document.getElementById('revenueTrendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: Object.keys(revenueAnalysisData.trend_analysis?.daily_trend?.data || {}),
                datasets: [{
                    label: '売上',
                    data: Object.values(revenueAnalysisData.trend_analysis?.daily_trend?.data || {}),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '¥' + new Intl.NumberFormat('ja-JP').format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '売上: ¥' + new Intl.NumberFormat('ja-JP').format(context.parsed.y);
                            }
                        }
                    }
                }
            }
        });
        
        // 売上構成チャート
        const compositionCtx = document.getElementById('revenueCompositionChart').getContext('2d');
        const compositionChart = new Chart(compositionCtx, {
            type: 'doughnut',
            data: {
                labels: ['施設警備', 'イベント警備', '交通誘導', '身辺警護'],
                datasets: [{
                    data: [45.5, 24.2, 18.2, 12.1],
                    backgroundColor: ['#0d6efd', '#198754', '#17a2b8', '#ffc107'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // テーブル初期化関数
    function initializeTable() {
        updateTable('customer');
    }
    
    // テーブル更新関数
    function updateTable(viewType) {
        const tableHeaders = document.getElementById('tableHeaders');
        const tableBody = document.getElementById('tableBody');
        
        // ヘッダーを更新
        switch(viewType) {
            case 'customer':
                tableHeaders.innerHTML = `
                    <th>顧客名</th>
                    <th>顧客タイプ</th>
                    <th>売上金額</th>
                    <th>請求件数</th>
                    <th>平均請求額</th>
                    <th>成長率</th>
                `;
                break;
            case 'project':
                tableHeaders.innerHTML = `
                    <th>プロジェクト名</th>
                    <th>顧客名</th>
                    <th>売上金額</th>
                    <th>期間</th>
                    <th>日次売上</th>
                    <th>収益性</th>
                `;
                break;
            case 'guard':
                tableHeaders.innerHTML = `
                    <th>警備員名</th>
                    <th>貢献売上</th>
                    <th>勤務時間</th>
                    <th>時間効率</th>
                    <th>評価</th>
                `;
                break;
            case 'company':
                tableHeaders.innerHTML = `
                    <th>会社名</th>
                    <th>売上金額</th>
                    <th>シェア</th>
                    <th>成長率</th>
                    <th>警備員数</th>
                    <th>効率性</th>
                `;
                break;
        }
        
        // ボディを更新（サンプルデータ）
        let rowsHtml = '';
        const data = getSampleData(viewType);
        
        data.forEach(row => {
            rowsHtml += '<tr>';
            row.forEach(cell => {
                rowsHtml += `<td>${cell}</td>`;
            });
            rowsHtml += '</tr>';
        });
        
        tableBody.innerHTML = rowsHtml;
        
        // 件数を更新
        document.getElementById('displayedRows').textContent = data.length;
        document.getElementById('totalRows').textContent = data.length;
    }
    
    // サンプルデータ取得関数
    function getSampleData(viewType) {
        switch(viewType) {
            case 'customer':
                return [
                    ['東京都庁', '官公庁', '¥15,000,000', '12', '¥1,250,000', '+8.5%'],
                    ['三菱商事', '大企業', '¥12,500,000', '8', '¥1,562,500', '+12.3%'],
                    ['イオンモール', '大企業', '¥8,200,000', '15', '¥546,667', '+5.2%'],
                    ['コスモワールド', 'イベント', '¥6,800,000', '6', '¥1,133,333', '+15.8%'],
                    ['銀座商店街', '中小企業', '¥4,500,000', '18', '¥250,000', '-2.1%']
                ];
            case 'project':
                return [
                    ['東京スカイツリー警備', '東京スカイツリー', '¥12,000,000', '180日', '¥66,667', '25.5%'],
                    ['羽田空港セキュリティ', 'JAL', '¥8,500,000', '90日', '¥94,444', '22.8%'],
                    ['新宿イベント警備', 'イベント会社A', '¥6,200,000', '45日', '¥137,778', '28.3%'],
                    ['銀座パトロール', '銀座商店街', '¥4,800,000', '120日', '¥40,000', '18.9%'],
                    ['コンサート警備', '音楽イベント社', '¥3,600,000', '12日', '¥300,000', '32.1%']
                ];
            case 'guard':
                return [
                    ['佐藤一郎', '¥2,800,000', '180時間', '¥15,556', '★★★★★'],
                    ['田中次郎', '¥2,450,000', '165時間', '¥14,848', '★★★★☆'],
                    ['山田三郎', '¥2,100,000', '155時間', '¥13,548', '★★★★☆'],
                    ['鈴木四郎', '¥1,950,000', '170時間', '¥11,471', '★★★☆☆'],
                    ['高橋五郎', '¥1,750,000', '145時間', '¥12,069', '★★★☆☆']
                ];
            case 'company':
                return [
                    ['㈲東央警備', '¥18,500,000', '45.2%', '+8.5%', '25名', '¥740,000'],
                    ['㈱Nikkeiホールディングス', '¥13,200,000', '32.3%', '+12.3%', '18名', '¥733,333'],
                    ['㈱全日本エンタープライズ', '¥9,200,000', '22.5%', '+6.8%', '12名', '¥766,667']
                ];
            default:
                return [];
        }
    }
    
    // イベントリスナー設定
    function setupEventListeners() {
        // フィルターフォーム送信
        document.getElementById('analysisFiltersForm').addEventListener('submit', function(e) {
            e.preventDefault();
            refreshAnalysisData();
        });
        
        // ビュー切り替え
        document.querySelectorAll('input[name="detailView"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    currentView = this.value;
                    updateTable(currentView);
                }
            });
        });
        
        // データ更新ボタン
        document.getElementById('refreshDataBtn').addEventListener('click', function() {
            refreshAnalysisData();
        });
        
        // エクスポートボタン
        document.getElementById('exportDataBtn').addEventListener('click', function() {
            const exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
            exportModal.show();
        });
        
        // エクスポート実行
        document.getElementById('executeExport').addEventListener('click', function() {
            executeExport();
        });
    }
    
    // 分析データ更新
    function refreshAnalysisData() {
        const formData = new FormData(document.getElementById('analysisFiltersForm'));
        const params = new URLSearchParams(formData);
        
        // ローディング表示
        showLoading(true);
        
        // Ajax リクエスト
        fetch(`{{ route('dashboard.revenue.analysis.data') }}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateAnalysisDisplay(data.data);
            } else {
                showError('データの取得に失敗しました');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('通信エラーが発生しました');
        })
        .finally(() => {
            showLoading(false);
        });
    }
    
    // エクスポート実行
    function executeExport() {
        const formData = new FormData(document.getElementById('exportForm'));
        const params = new URLSearchParams(formData);
        
        // 現在のフィルター条件を追加
        const filterForm = new FormData(document.getElementById('analysisFiltersForm'));
        for (let [key, value] of filterForm.entries()) {
            params.append(key, value);
        }
        
        // ダウンロード実行
        window.location.href = `{{ route('dashboard.revenue.analysis.export') }}?${params.toString()}`;
        
        // モーダルを閉じる
        const exportModal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
        exportModal.hide();
    }
    
    // ローディング表示
    function showLoading(show) {
        const refreshBtn = document.getElementById('refreshDataBtn');
        if (show) {
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>更新中...';
            refreshBtn.disabled = true;
        } else {
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i>更新';
            refreshBtn.disabled = false;
        }
    }
    
    // エラー表示
    function showError(message) {
        // Toast通知または適切なエラー表示を実装
        alert(message);
    }
    
    // 分析表示更新
    function updateAnalysisDisplay(data) {
        // 概要カードの更新
        document.getElementById('currentRevenue').textContent = 
            '¥' + new Intl.NumberFormat('ja-JP').format(data.overview?.current_period?.total_revenue || 0);
        document.getElementById('avgDailyRevenue').textContent = 
            '¥' + new Intl.NumberFormat('ja-JP').format(data.overview?.current_period?.average_daily || 0);
        document.getElementById('avgInvoiceAmount').textContent = 
            '¥' + new Intl.NumberFormat('ja-JP').format(data.overview?.current_period?.average_invoice_amount || 0);
        document.getElementById('revenueVolatility').textContent = 
            (data.overview?.metrics?.revenue_volatility || 0).toFixed(1) + '%';
        
        // チャートの更新
        // 実装は実際のデータ構造に応じて調整
        
        // テーブルの更新
        updateTable(currentView);
    }
});
</script>
@endpush
