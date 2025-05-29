@extends('layouts.app')

@section('title', '天気情報一覧 - 警備システム')

@section('page_title', '天気情報一覧')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">ダッシュボード</a></li>
        <li class="breadcrumb-item"><a href="{{ route('weather.dashboard') }}">天気予報</a></li>
        <li class="breadcrumb-item active" aria-current="page">一覧</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- 検索・フィルターセクション -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i>検索・フィルター</h5>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#searchFilters">
                        <i class="fas fa-filter"></i> フィルター
                    </button>
                </div>
            </div>
        </div>
        <div class="collapse show" id="searchFilters">
            <div class="card-body">
                <form method="GET" action="{{ route('weather.index') }}" id="weatherSearchForm">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">場所</label>
                            <input type="text" class="form-control" name="location" value="{{ request('location') }}" placeholder="場所名で検索">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">開始日</label>
                            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">終了日</label>
                            <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">リスクレベル</label>
                            <select class="form-select" name="risk_level">
                                <option value="">全て</option>
                                <option value="low" {{ request('risk_level') == 'low' ? 'selected' : '' }}>低</option>
                                <option value="medium" {{ request('risk_level') == 'medium' ? 'selected' : '' }}>中</option>
                                <option value="high" {{ request('risk_level') == 'high' ? 'selected' : '' }}>高</option>
                                <option value="critical" {{ request('risk_level') == 'critical' ? 'selected' : '' }}>危険</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">データタイプ</label>
                            <select class="form-select" name="data_type">
                                <option value="">全て</option>
                                <option value="current" {{ request('data_type') == 'current' ? 'selected' : '' }}>現在</option>
                                <option value="forecast" {{ request('data_type') == 'forecast' ? 'selected' : '' }}>予報</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">天気</label>
                            <select class="form-select" name="weather_main">
                                <option value="">全て</option>
                                <option value="Clear" {{ request('weather_main') == 'Clear' ? 'selected' : '' }}>晴れ</option>
                                <option value="Clouds" {{ request('weather_main') == 'Clouds' ? 'selected' : '' }}>曇り</option>
                                <option value="Rain" {{ request('weather_main') == 'Rain' ? 'selected' : '' }}>雨</option>
                                <option value="Snow" {{ request('weather_main') == 'Snow' ? 'selected' : '' }}>雪</option>
                                <option value="Thunderstorm" {{ request('weather_main') == 'Thunderstorm' ? 'selected' : '' }}>雷雨</option>
                                <option value="Drizzle" {{ request('weather_main') == 'Drizzle' ? 'selected' : '' }}>霧雨</option>
                                <option value="Mist" {{ request('weather_main') == 'Mist' ? 'selected' : '' }}>霧</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">表示件数</label>
                            <select class="form-select" name="per_page">
                                <option value="15" {{ request('per_page') == '15' ? 'selected' : '' }}>15件</option>
                                <option value="30" {{ request('per_page') == '30' ? 'selected' : '' }}>30件</option>
                                <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50件</option>
                                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100件</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">ソート</label>
                            <select class="form-select" name="sort">
                                <option value="weather_date" {{ request('sort') == 'weather_date' ? 'selected' : '' }}>日時</option>
                                <option value="location_name" {{ request('sort') == 'location_name' ? 'selected' : '' }}>場所</option>
                                <option value="temperature" {{ request('sort') == 'temperature' ? 'selected' : '' }}>気温</option>
                                <option value="weather_risk_level" {{ request('sort') == 'weather_risk_level' ? 'selected' : '' }}>リスクレベル</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> 検索
                            </button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('weather.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> クリア
                            </a>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-success" onclick="updateAllWeather()">
                                <i class="fas fa-sync-alt"></i> 一括更新
                            </button>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown">
                                <button class="btn btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-download"></i> エクスポート
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('weather.export', array_merge(request()->all(), ['format' => 'csv'])) }}">CSV形式</a></li>
                                    <li><a class="dropdown-item" href="{{ route('weather.export', array_merge(request()->all(), ['format' => 'excel'])) }}">Excel形式</a></li>
                                    <li><a class="dropdown-item" href="{{ route('weather.export', array_merge(request()->all(), ['format' => 'pdf'])) }}">PDF形式</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 統計サマリー -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-database fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $stats['total_records'] ?? 0 }}</h4>
                    <small>総レコード数</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-map-marker-alt fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $stats['locations_count'] ?? 0 }}</h4>
                    <small>監視地点数</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $stats['high_risk_count'] ?? 0 }}</h4>
                    <small>高リスク件数</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-shield-alt fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $stats['outdoor_unsuitable_count'] ?? 0 }}</h4>
                    <small>屋外業務注意</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 天気情報テーブル -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-cloud-sun me-2"></i>天気情報</h5>
            <div class="d-flex gap-2">
                <small class="text-muted">平均気温: {{ $stats['avg_temperature'] ?? 0 }}°C</small>
                <small class="text-muted">総降水量: {{ $stats['total_rainfall'] ?? 0 }}mm</small>
            </div>
        </div>
        <div class="card-body p-0">
            @if($weatherData->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <a href="{{ route('weather.index', array_merge(request()->all(), ['sort' => 'weather_date', 'direction' => request('sort') == 'weather_date' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none">
                                        日時
                                        @if(request('sort') == 'weather_date')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('weather.index', array_merge(request()->all(), ['sort' => 'location_name', 'direction' => request('sort') == 'location_name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none">
                                        場所
                                        @if(request('sort') == 'location_name')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>天気</th>
                                <th>
                                    <a href="{{ route('weather.index', array_merge(request()->all(), ['sort' => 'temperature', 'direction' => request('sort') == 'temperature' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none">
                                        気温
                                        @if(request('sort') == 'temperature')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>湿度</th>
                                <th>風</th>
                                <th>降水量</th>
                                <th>
                                    <a href="{{ route('weather.index', array_merge(request()->all(), ['sort' => 'weather_risk_level', 'direction' => request('sort') == 'weather_risk_level' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none">
                                        リスク
                                        @if(request('sort') == 'weather_risk_level')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>屋外業務</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($weatherData as $weather)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $weather->weather_date->format('m/d H:i') }}</strong>
                                        <div>
                                            <span class="badge bg-{{ $weather->data_type == 'current' ? 'success' : 'info' }} badge-sm">
                                                {{ $weather->data_type_japanese }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ Str::limit($weather->location_name, 20) }}</strong>
                                        @if($weather->latitude && $weather->longitude)
                                            <div class="small text-muted">
                                                {{ number_format($weather->latitude, 4) }}, {{ number_format($weather->longitude, 4) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $weather->weather_icon_url }}" alt="{{ $weather->weather_description }}" width="40" class="me-2">
                                        <div>
                                            <div>{{ $weather->weather_main }}</div>
                                            <small class="text-muted">{{ $weather->weather_description }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong class="fs-5">{{ $weather->temperature }}°C</strong>
                                        <div class="small text-muted">
                                            体感 {{ $weather->feels_like }}°C
                                            @if($weather->feels_like_difference != 0)
                                                <span class="text-{{ $weather->feels_like_difference > 0 ? 'danger' : 'primary' }}">
                                                    ({{ $weather->feels_like_difference > 0 ? '+' : '' }}{{ $weather->feels_like_difference }}°C)
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $weather->humidity }}%</strong>
                                        <div class="small text-muted">
                                            {{ $weather->pressure }}hPa
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($weather->wind_speed)
                                        <div>
                                            <strong>{{ $weather->wind_speed }}m/s</strong>
                                            <div class="small text-muted">
                                                {{ $weather->wind_direction }}
                                                @if($weather->wind_gust)
                                                    <br>突風: {{ $weather->wind_gust }}m/s
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($weather->rain_1h || $weather->rain_3h)
                                        <div>
                                            @if($weather->rain_1h)
                                                <strong>{{ $weather->rain_1h }}mm/h</strong>
                                            @endif
                                            @if($weather->rain_3h)
                                                <div class="small text-muted">3h: {{ $weather->rain_3h }}mm</div>
                                            @endif
                                        </div>
                                    @elseif($weather->snow_1h || $weather->snow_3h)
                                        <div class="text-info">
                                            @if($weather->snow_1h)
                                                <strong><i class="fas fa-snowflake"></i> {{ $weather->snow_1h }}mm/h</strong>
                                            @endif
                                            @if($weather->snow_3h)
                                                <div class="small">3h: {{ $weather->snow_3h }}mm</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $weather->weather_risk_level == 'critical' ? 'danger' : ($weather->weather_risk_level == 'high' ? 'warning' : ($weather->weather_risk_level == 'medium' ? 'info' : 'success')) }}">
                                        {{ $weather->risk_level_japanese }}
                                    </span>
                                    @if($weather->weather_alerts)
                                        <div class="mt-1">
                                            @foreach($weather->weather_alerts as $alert)
                                                <span class="badge bg-secondary badge-sm">{{ $alert['message'] }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($weather->outdoor_work_suitable)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> 適合
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times"></i> 注意
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('weather.show', $weather) }}" class="btn btn-outline-primary" title="詳細表示">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-info" onclick="showLocationMap({{ $weather->latitude }}, {{ $weather->longitude }}, '{{ $weather->location_name }}')" title="地図表示">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-cloud fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">天気情報が見つかりませんでした</h5>
                    <p class="text-muted">検索条件を変更するか、天気情報を更新してください。</p>
                    <button class="btn btn-primary" onclick="updateAllWeather()">
                        <i class="fas fa-sync-alt"></i> 天気情報を取得
                    </button>
                </div>
            @endif
        </div>
        
        @if($weatherData->count() > 0)
        <div class="card-footer">
            <div class="row align-items-center">
                <div class="col">
                    <small class="text-muted">
                        {{ $weatherData->firstItem() ?? 0 }}～{{ $weatherData->lastItem() ?? 0 }}件 
                        （全{{ $weatherData->total() ?? 0 }}件中）
                    </small>
                </div>
                <div class="col-auto">
                    {{ $weatherData->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- 地図表示モーダル -->
<div class="modal fade" id="locationMapModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">地点位置</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="locationMap" style="height: 400px;"></div>
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
.table td {
    vertical-align: middle;
}
.fs-5 {
    font-size: 1.1rem;
}
</style>
@endpush

@push('scripts')
<script>
let locationMap;

function updateAllWeather() {
    if (!confirm('全地点の天気情報を更新しますか？')) {
        return;
    }
    
    showLoading('天気情報を更新しています...');
    
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
            showAlert('error', data.message || '天気情報の更新に失敗しました');
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('error', '天気情報の更新中にエラーが発生しました');
        console.error('Weather update error:', error);
    });
}

function showLocationMap(lat, lng, locationName) {
    const modal = new bootstrap.Modal(document.getElementById('locationMapModal'));
    modal.show();
    
    // モーダルが表示された後にマップを初期化
    document.getElementById('locationMapModal').addEventListener('shown.bs.modal', function () {
        initLocationMap(lat, lng, locationName);
    }, { once: true });
}

function initLocationMap(lat, lng, locationName) {
    if (locationMap) {
        locationMap.remove();
    }
    
    locationMap = L.map('locationMap').setView([lat, lng], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(locationMap);
    
    L.marker([lat, lng])
        .addTo(locationMap)
        .bindPopup(locationName)
        .openPopup();
}

// 自動検索機能
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('weatherSearchForm');
    const inputs = form.querySelectorAll('input, select');
    
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            // 自動検索は無効にして、手動検索のみにする
            // form.submit();
        });
    });
});

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

<!-- Leaflet.js（地図表示用） -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
@endpush
