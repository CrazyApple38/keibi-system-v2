@extends('layouts.app')

@section('title', '天気情報詳細 - 警備システム')

@section('page_title', '天気情報詳細')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">ダッシュボード</a></li>
        <li class="breadcrumb-item"><a href="{{ route('weather.dashboard') }}">天気予報</a></li>
        <li class="breadcrumb-item"><a href="{{ route('weather.index') }}">一覧</a></li>
        <li class="breadcrumb-item active" aria-current="page">詳細</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- メイン天気情報 -->
        <div class="col-lg-8">
            <!-- 基本天気情報 -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="fas fa-cloud-sun me-2"></i>
                                {{ $weather->location_name }}
                            </h5>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-{{ $weather->data_type == 'current' ? 'success' : 'info' }}">
                                {{ $weather->data_type_japanese }}
                            </span>
                            <span class="badge bg-{{ $weather->weather_risk_level == 'critical' ? 'danger' : ($weather->weather_risk_level == 'high' ? 'warning' : ($weather->weather_risk_level == 'medium' ? 'info' : 'success')) }}">
                                {{ $weather->risk_level_japanese }}リスク
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- 天気メイン表示 -->
                        <div class="col-md-6">
                            <div class="text-center mb-4">
                                <img src="{{ $weather->weather_icon_url }}" alt="{{ $weather->weather_description }}" class="mb-3" style="width: 100px;">
                                <h2 class="display-4 mb-0">{{ $weather->temperature }}°C</h2>
                                <p class="text-muted mb-2">体感温度: {{ $weather->feels_like }}°C</p>
                                <h5>{{ $weather->weather_description }}</h5>
                                <div class="mt-3">
                                    @if(!$weather->outdoor_work_suitable)
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            屋外業務に注意が必要な天気条件です
                                        </div>
                                    @else
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>
                                            屋外業務に適した天気条件です
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- 詳細データ -->
                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-thermometer-half text-primary fa-2x mb-2"></i>
                                            <h6>最高気温</h6>
                                            <strong class="h5">{{ $weather->temp_max }}°C</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-thermometer-empty text-info fa-2x mb-2"></i>
                                            <h6>最低気温</h6>
                                            <strong class="h5">{{ $weather->temp_min }}°C</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-tint text-primary fa-2x mb-2"></i>
                                            <h6>湿度</h6>
                                            <strong class="h5">{{ $weather->humidity }}%</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-compress-alt text-secondary fa-2x mb-2"></i>
                                            <h6>気圧</h6>
                                            <strong class="h5">{{ $weather->pressure }}hPa</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 気象詳細情報 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>気象詳細</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6><i class="fas fa-wind me-2"></i>風情報</h6>
                            <ul class="list-unstyled">
                                <li><strong>風速:</strong> {{ $weather->wind_speed ?? '不明' }}m/s</li>
                                <li><strong>風向:</strong> {{ $weather->wind_direction }}</li>
                                @if($weather->wind_gust)
                                    <li><strong>突風:</strong> {{ $weather->wind_gust }}m/s</li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-cloud-rain me-2"></i>降水情報</h6>
                            <ul class="list-unstyled">
                                @if($weather->rain_1h)
                                    <li><strong>1時間降水量:</strong> {{ $weather->rain_1h }}mm</li>
                                @endif
                                @if($weather->rain_3h)
                                    <li><strong>3時間降水量:</strong> {{ $weather->rain_3h }}mm</li>
                                @endif
                                @if($weather->snow_1h)
                                    <li><strong>1時間降雪量:</strong> {{ $weather->snow_1h }}mm</li>
                                @endif
                                @if($weather->snow_3h)
                                    <li><strong>3時間降雪量:</strong> {{ $weather->snow_3h }}mm</li>
                                @endif
                                @if(!$weather->rain_1h && !$weather->rain_3h && !$weather->snow_1h && !$weather->snow_3h)
                                    <li class="text-muted">降水なし</li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-eye me-2"></i>視界・その他</h6>
                            <ul class="list-unstyled">
                                <li><strong>雲量:</strong> {{ $weather->clouds }}%</li>
                                @if($weather->visibility)
                                    <li><strong>視程:</strong> {{ number_format($weather->visibility / 1000, 1) }}km</li>
                                @endif
                                @if($weather->uv_index)
                                    <li><strong>UVインデックス:</strong> {{ $weather->uv_index }}</li>
                                @endif
                                @if($weather->sea_level)
                                    <li><strong>海面気圧:</strong> {{ $weather->sea_level }}hPa</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 警備業務への影響分析 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>警備業務への影響分析</h5>
                </div>
                <div class="card-body">
                    @if($securityImpact['overall_score'] > 0)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card {{ $securityImpact['overall_score'] >= 50 ? 'border-danger' : ($securityImpact['overall_score'] >= 25 ? 'border-warning' : 'border-info') }}">
                                    <div class="card-body text-center">
                                        <h3 class="text-{{ $securityImpact['overall_score'] >= 50 ? 'danger' : ($securityImpact['overall_score'] >= 25 ? 'warning' : 'info') }}">
                                            {{ $securityImpact['overall_score'] }}点
                                        </h3>
                                        <p class="mb-0">総合影響スコア</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center h-100">
                                    <div>
                                        @if($securityImpact['overall_score'] >= 50)
                                            <div class="alert alert-danger mb-0">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <strong>高影響:</strong> 警備業務に大きな影響があります
                                            </div>
                                        @elseif($securityImpact['overall_score'] >= 25)
                                            <div class="alert alert-warning mb-0">
                                                <i class="fas fa-exclamation-circle me-2"></i>
                                                <strong>中影響:</strong> 警備業務に注意が必要です
                                            </div>
                                        @else
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>低影響:</strong> 通常の警備業務が可能です
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(count($securityImpact['factors']) > 0)
                        <div class="mb-3">
                            <h6>影響要因</h6>
                            <div class="row">
                                @foreach($securityImpact['factors'] as $factor)
                                    <div class="col-md-6 mb-2">
                                        <div class="alert alert-warning py-2 mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>{{ $factor }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(count($securityImpact['recommendations']) > 0)
                        <div>
                            <h6>推奨対策</h6>
                            <ul class="list-group list-group-flush">
                                @foreach($securityImpact['recommendations'] as $recommendation)
                                    <li class="list-group-item border-0 ps-0">
                                        <i class="fas fa-check-circle text-success me-2"></i>{{ $recommendation }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($securityImpact['overall_score'] == 0)
                        <div class="text-center text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                            <h5>良好な天気条件</h5>
                            <p>現在の天気条件は警備業務に適しています。通常通りの業務を実施できます。</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 天気アラート -->
            @if($weather->weather_alerts)
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>気象警報・注意報</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($weather->weather_alerts as $alert)
                            <div class="col-md-6 mb-2">
                                <div class="alert alert-{{ $alert['type'] == 'heavy_rain' || $alert['type'] == 'strong_wind' ? 'danger' : 'warning' }} mb-0">
                                    <strong>{{ $alert['message'] }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- サイドバー -->
        <div class="col-lg-4">
            <!-- 基本情報 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">基本情報</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>日時:</strong></td>
                            <td>{{ $weather->weather_date->format('Y年m月d日 H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>緯度:</strong></td>
                            <td>{{ number_format($weather->latitude, 6) }}</td>
                        </tr>
                        <tr>
                            <td><strong>経度:</strong></td>
                            <td>{{ number_format($weather->longitude, 6) }}</td>
                        </tr>
                        <tr>
                            <td><strong>データソース:</strong></td>
                            <td>{{ strtoupper($weather->api_source) }}</td>
                        </tr>
                        <tr>
                            <td><strong>取得日時:</strong></td>
                            <td>{{ $weather->api_fetched_at->format('Y/m/d H:i') }}</td>
                        </tr>
                    </table>
                    
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-outline-primary" onclick="showLocationMap()">
                            <i class="fas fa-map-marker-alt me-2"></i>地図で表示
                        </button>
                        <button class="btn btn-outline-info" onclick="updateWeatherData()">
                            <i class="fas fa-sync-alt me-2"></i>天気情報を更新
                        </button>
                    </div>
                </div>
            </div>

            <!-- 同じ場所の最近のデータ -->
            @if(count($recentWeather) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ $weather->location_name }} の最近のデータ</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>日時</th>
                                    <th>天気</th>
                                    <th>気温</th>
                                    <th>リスク</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentWeather->take(5) as $recent)
                                <tr>
                                    <td class="small">{{ $recent->weather_date->format('m/d H:i') }}</td>
                                    <td>
                                        <img src="{{ $recent->weather_icon_url }}" alt="{{ $recent->weather_description }}" width="25">
                                    </td>
                                    <td>{{ $recent->temperature }}°C</td>
                                    <td>
                                        <span class="badge bg-{{ $recent->weather_risk_level == 'critical' ? 'danger' : ($recent->weather_risk_level == 'high' ? 'warning' : ($recent->weather_risk_level == 'medium' ? 'info' : 'success')) }} badge-sm">
                                            {{ $recent->risk_level_japanese }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-center">
                        <a href="{{ route('weather.index', ['location' => $weather->location_name]) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list me-1"></i>全て表示
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- 同日他地点のデータ -->
            @if(count($sameDate) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ $weather->weather_date->format('m月d日') }} 他地点の天気</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>場所</th>
                                    <th>天気</th>
                                    <th>気温</th>
                                    <th>リスク</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sameDate->take(5) as $other)
                                <tr>
                                    <td class="small">{{ Str::limit($other->location_name, 15) }}</td>
                                    <td>
                                        <img src="{{ $other->weather_icon_url }}" alt="{{ $other->weather_description }}" width="25">
                                    </td>
                                    <td>{{ $other->temperature }}°C</td>
                                    <td>
                                        <span class="badge bg-{{ $other->weather_risk_level == 'critical' ? 'danger' : ($other->weather_risk_level == 'high' ? 'warning' : ($other->weather_risk_level == 'medium' ? 'info' : 'success')) }} badge-sm">
                                            {{ $other->risk_level_japanese }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- クイックアクション -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">クイックアクション</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('weather.forecast.location', ['location_name' => $weather->location_name, 'days' => 3]) }}" class="btn btn-outline-info">
                            <i class="fas fa-calendar-week me-2"></i>3日間予報を表示
                        </a>
                        <a href="{{ route('weather.export', ['location' => $weather->location_name, 'format' => 'pdf']) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-file-pdf me-2"></i>PDF出力
                        </a>
                        <button class="btn btn-outline-warning" onclick="reportIssue()">
                            <i class="fas fa-exclamation-triangle me-2"></i>問題を報告
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 地図表示モーダル -->
<div class="modal fade" id="locationMapModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $weather->location_name }} の位置</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="locationMap" style="height: 450px;"></div>
                <div class="mt-3">
                    <div class="row text-center">
                        <div class="col-4">
                            <strong>{{ $weather->temperature }}°C</strong>
                            <div class="small text-muted">現在気温</div>
                        </div>
                        <div class="col-4">
                            <strong>{{ $weather->humidity }}%</strong>
                            <div class="small text-muted">湿度</div>
                        </div>
                        <div class="col-4">
                            <strong>{{ $weather->wind_speed ?? 0 }}m/s</strong>
                            <div class="small text-muted">風速</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge-sm {
    font-size: 0.7em;
}
.alert {
    border-radius: 0.5rem;
}
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
@endpush

@push('scripts')
<!-- Leaflet.js（地図表示用） -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script>
let locationMap;

function showLocationMap() {
    const modal = new bootstrap.Modal(document.getElementById('locationMapModal'));
    modal.show();
    
    // モーダルが表示された後にマップを初期化
    document.getElementById('locationMapModal').addEventListener('shown.bs.modal', function () {
        initLocationMap();
    }, { once: true });
}

function initLocationMap() {
    if (locationMap) {
        locationMap.remove();
    }
    
    const lat = {{ $weather->latitude }};
    const lng = {{ $weather->longitude }};
    const locationName = "{{ $weather->location_name }}";
    
    locationMap = L.map('locationMap').setView([lat, lng], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(locationMap);
    
    // 天気情報付きマーカー
    const weatherIcon = L.divIcon({
        html: `<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                   <i class="fas fa-cloud-sun"></i>
               </div>`,
        className: 'custom-weather-marker',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });
    
    L.marker([lat, lng], { icon: weatherIcon })
        .addTo(locationMap)
        .bindPopup(`
            <div class="text-center">
                <h6>${locationName}</h6>
                <img src="{{ $weather->weather_icon_url }}" alt="{{ $weather->weather_description }}" width="50">
                <div><strong>{{ $weather->temperature }}°C</strong></div>
                <div class="small text-muted">{{ $weather->weather_description }}</div>
                <div class="mt-2">
                    <span class="badge bg-{{ $weather->weather_risk_level == 'critical' ? 'danger' : ($weather->weather_risk_level == 'high' ? 'warning' : ($weather->weather_risk_level == 'medium' ? 'info' : 'success')) }}">
                        {{ $weather->risk_level_japanese }}リスク
                    </span>
                </div>
            </div>
        `)
        .openPopup();
}

function updateWeatherData() {
    if (!confirm('この地点の天気情報を更新しますか？')) {
        return;
    }
    
    showLoading('天気情報を更新しています...');
    
    fetch('/api/weather/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            location_name: "{{ $weather->location_name }}",
            latitude: {{ $weather->latitude }},
            longitude: {{ $weather->longitude }},
            include_forecast: true
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showAlert('success', '天気情報を更新しました');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('error', data.message || '天気情報の更新に失敗しました');
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('error', '天気情報の更新中にエラーが発生しました');
        console.error('Weather update error:', error);
    });
}

function reportIssue() {
    const issue = prompt('問題の詳細を入力してください:');
    if (issue) {
        // TODO: 問題報告機能の実装
        showAlert('info', '問題を報告しました。ありがとうございます。');
    }
}

// ユーティリティ関数
function showLoading(message = 'Loading...') {
    // TODO: 実際のローディング表示実装
    console.log(message);
}

function hideLoading() {
    // TODO: 実際のローディング非表示実装
    console.log('Loading hidden');
}

function showAlert(type, message, duration = 5000) {
    // TODO: 実際のアラート表示実装
    console.log(`${type}: ${message}`);
    alert(message);
}
</script>
@endpush
