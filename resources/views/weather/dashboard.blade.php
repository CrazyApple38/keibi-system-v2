@extends('layouts.app')

@section('title', '天気予報ダッシュボード - 警備システム')

@section('page_title', '天気予報ダッシュボード')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">ダッシュボード</a></li>
        <li class="breadcrumb-item active" aria-current="page">天気予報</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- 天気アラート -->
    @if(count($highRiskWeather) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>天気アラート:</strong> {{ count($highRiskWeather) }}地点で高リスクの天気条件が検出されています。
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- 統計サマリー -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center bg-gradient-primary text-white">
                <div class="card-body">
                    <i class="fas fa-thermometer-half fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $weeklyStats['week_avg_temp'] ?? 0 }}°C</h4>
                    <small>今週の平均気温</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-gradient-info text-white">
                <div class="card-body">
                    <i class="fas fa-cloud-rain fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $weeklyStats['week_rainfall'] ?? 0 }}mm</h4>
                    <small>今週の総降水量</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-gradient-warning text-white">
                <div class="card-body">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $weeklyStats['high_risk_days'] ?? 0 }}</h4>
                    <small>高リスク日数</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-gradient-success text-white">
                <div class="card-body">
                    <i class="fas fa-shield-alt fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $weeklyStats['outdoor_work_days'] ?? 0 }}</h4>
                    <small>屋外業務適合日数</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 現在の天気情報 -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-cloud-sun me-2"></i>現在の天気情報</h5>
                    <button class="btn btn-outline-primary btn-sm" onclick="refreshWeatherData()">
                        <i class="fas fa-sync-alt"></i> 更新
                    </button>
                </div>
                <div class="card-body">
                    @if(count($currentWeather) > 0)
                        <div class="row">
                            @foreach($currentWeather->take(6) as $weather)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="{{ $weather->weather_icon_url }}" alt="{{ $weather->weather_description }}" class="me-2" width="40">
                                        <div>
                                            <h6 class="mb-0">{{ $weather->location_name }}</h6>
                                            <small class="text-muted">{{ $weather->weather_date->format('H:i') }}</small>
                                        </div>
                                    </div>
                                    <div class="row text-center">
                                        <div class="col">
                                            <strong class="h5">{{ $weather->temperature }}°C</strong>
                                            <br><small>気温</small>
                                        </div>
                                        <div class="col">
                                            <strong>{{ $weather->humidity }}%</strong>
                                            <br><small>湿度</small>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-{{ $weather->weather_risk_level == 'critical' ? 'danger' : ($weather->weather_risk_level == 'high' ? 'warning' : ($weather->weather_risk_level == 'medium' ? 'info' : 'success')) }}">
                                            {{ $weather->risk_level_japanese }}
                                        </span>
                                        @if(!$weather->outdoor_work_suitable)
                                            <span class="badge bg-secondary">屋外業務注意</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center">
                            <a href="{{ route('weather.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-list"></i> 全ての天気情報を見る
                            </a>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-cloud fa-3x mb-3"></i>
                            <p>現在の天気情報がありません。</p>
                            <button class="btn btn-primary" onclick="updateAllLocations()">
                                <i class="fas fa-download"></i> 天気情報を取得
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 今日の予報 -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>今日の予報</h5>
                </div>
                <div class="card-body">
                    @if(count($todayForecast) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>時刻</th>
                                        <th>場所</th>
                                        <th>天気</th>
                                        <th>気温</th>
                                        <th>リスク</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todayForecast->take(8) as $forecast)
                                    <tr>
                                        <td>{{ $forecast->weather_date->format('H:i') }}</td>
                                        <td>
                                            <small>{{ Str::limit($forecast->location_name, 15) }}</small>
                                        </td>
                                        <td>
                                            <img src="{{ $forecast->weather_icon_url }}" alt="{{ $forecast->weather_description }}" width="25" class="me-1">
                                            {{ $forecast->temperature }}°C
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $forecast->feels_like }}°C</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $forecast->weather_risk_level == 'critical' ? 'danger' : ($forecast->weather_risk_level == 'high' ? 'warning' : ($forecast->weather_risk_level == 'medium' ? 'info' : 'success')) }} badge-sm">
                                                {{ $forecast->risk_level_japanese }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                            <p>今日の予報データがありません。</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 高リスク地点 -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>高リスク地点</h5>
                </div>
                <div class="card-body">
                    @if(count($highRiskWeather) > 0)
                        @foreach($highRiskWeather as $weather)
                        <div class="border-start border-{{ $weather->weather_risk_level == 'critical' ? 'danger' : 'warning' }} border-3 ps-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $weather->location_name }}</h6>
                                    <p class="mb-1 text-muted small">{{ $weather->weather_description }}</p>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-{{ $weather->weather_risk_level == 'critical' ? 'danger' : 'warning' }}">
                                            {{ $weather->risk_level_japanese }}
                                        </span>
                                        @if($weather->weather_alerts)
                                            @foreach($weather->weather_alerts as $alert)
                                                <span class="badge bg-secondary">{{ $alert['message'] }}</span>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                                <div class="text-end">
                                    <strong>{{ $weather->temperature }}°C</strong>
                                    <br><small class="text-muted">{{ $weather->weather_date->format('H:i') }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div class="text-center">
                            <a href="{{ route('weather.alerts') }}" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-bell"></i> 全てのアラートを見る
                            </a>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                            <p>現在、高リスクの天気条件はありません。</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 警備地点別サマリー -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>警備地点別サマリー</h5>
                </div>
                <div class="card-body">
                    @if(count($locationSummary) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>地点</th>
                                        <th>現在</th>
                                        <th>週平均気温</th>
                                        <th>降水量</th>
                                        <th>適性</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($locationSummary as $location => $data)
                                    <tr>
                                        <td>
                                            <small>{{ Str::limit($location, 20) }}</small>
                                        </td>
                                        <td>
                                            @if($data['latest'])
                                                <img src="{{ $data['latest']->weather_icon_url }}" alt="weather" width="20" class="me-1">
                                                {{ $data['latest']->temperature }}°C
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $data['stats']['avg_temperature'] ?? '-' }}°C</td>
                                        <td>{{ $data['stats']['total_rainfall'] ?? 0 }}mm</td>
                                        <td>
                                            @if($data['latest'] && $data['latest']->outdoor_work_suitable)
                                                <i class="fas fa-check text-success"></i>
                                            @else
                                                <i class="fas fa-times text-danger"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-map fa-2x mb-2"></i>
                            <p>警備地点の天気データがありません。</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 天気チャート -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>天気トレンド</h5>
                </div>
                <div class="card-body">
                    <canvas id="weatherTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 天気情報更新モーダル -->
<div class="modal fade" id="weatherUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">天気情報更新</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="updateProgress" class="d-none">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">更新中...</span>
                        </div>
                        <p class="mt-2">天気情報を更新しています...</p>
                    </div>
                </div>
                <div id="updateForm">
                    <form id="manualUpdateForm">
                        <div class="mb-3">
                            <label class="form-label">場所名</label>
                            <input type="text" class="form-control" name="location_name" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">緯度</label>
                                    <input type="number" class="form-control" name="latitude" step="any" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">経度</label>
                                    <input type="number" class="form-control" name="longitude" step="any" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_forecast" checked>
                                <label class="form-check-label">
                                    天気予報も取得する
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="submitWeatherUpdate()">更新実行</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
}
.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8, #117a8b);
}
.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107, #d39e00);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
}
.badge-sm {
    font-size: 0.7em;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// 天気トレンドチャート
let weatherChart;

document.addEventListener('DOMContentLoaded', function() {
    initWeatherChart();
    loadWeatherAlerts();
});

function initWeatherChart() {
    const ctx = document.getElementById('weatherTrendChart').getContext('2d');
    
    // 週間天気データを取得
    fetch('/api/weather/trend-data')
        .then(response => response.json())
        .then(data => {
            weatherChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: '平均気温 (°C)',
                        data: data.temperatures || [],
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        fill: true,
                        tension: 0.4
                    }, {
                        label: '降水量 (mm)',
                        data: data.rainfall || [],
                        borderColor: '#17a2b8',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: '週間天気トレンド'
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('天気チャートデータの取得に失敗しました:', error);
        });
}

function refreshWeatherData() {
    showLoading('天気情報を更新しています...');
    
    fetch('/api/weather/refresh-current', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showAlert('success', '天気情報を更新しました');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('error', data.message || '天気情報の更新に失敗しました');
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('error', '天気情報の更新中にエラーが発生しました');
        console.error('Weather refresh error:', error);
    });
}

function updateAllLocations() {
    showLoading('全地点の天気情報を更新しています...');
    
    fetch('/api/weather/update-all-locations', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 2000);
        } else {
            showAlert('error', data.message || '天気情報の一括更新に失敗しました');
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('error', '天気情報の一括更新中にエラーが発生しました');
        console.error('Bulk weather update error:', error);
    });
}

function showWeatherUpdateModal() {
    new bootstrap.Modal(document.getElementById('weatherUpdateModal')).show();
}

function submitWeatherUpdate() {
    const form = document.getElementById('manualUpdateForm');
    const formData = new FormData(form);
    
    document.getElementById('updateForm').classList.add('d-none');
    document.getElementById('updateProgress').classList.remove('d-none');
    
    fetch('/api/weather/update-manual', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('updateProgress').classList.add('d-none');
        document.getElementById('updateForm').classList.remove('d-none');
        
        if (data.success) {
            showAlert('success', '天気情報を取得しました');
            bootstrap.Modal.getInstance(document.getElementById('weatherUpdateModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('error', data.message || '天気情報の取得に失敗しました');
        }
    })
    .catch(error => {
        document.getElementById('updateProgress').classList.add('d-none');
        document.getElementById('updateForm').classList.remove('d-none');
        showAlert('error', '天気情報の取得中にエラーが発生しました');
        console.error('Manual weather update error:', error);
    });
}

function loadWeatherAlerts() {
    fetch('/api/weather/alerts')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                // アラート通知を表示
                showWeatherAlertNotification(data.data);
            }
        })
        .catch(error => {
            console.error('Weather alerts load error:', error);
        });
}

function showWeatherAlertNotification(alerts) {
    const criticalAlerts = alerts.filter(alert => alert.risk_level === 'critical');
    
    if (criticalAlerts.length > 0) {
        const alertMessage = `${criticalAlerts.length}地点で危険レベルの天気条件が検出されています。`;
        showAlert('warning', alertMessage, 8000);
    }
}

// ユーティリティ関数
function showLoading(message = 'Loading...') {
    // ローディング表示の実装
    console.log(message);
}

function hideLoading() {
    // ローディング非表示の実装
    console.log('Loading hidden');
}

function showAlert(type, message, duration = 5000) {
    // アラート表示の実装
    console.log(`${type}: ${message}`);
}
</script>
@endpush
