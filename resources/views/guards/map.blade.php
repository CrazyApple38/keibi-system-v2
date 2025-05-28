@extends('layouts.app')

@section('title', '警備員位置管理マップ')

@section('head')
    <!-- Google Maps API -->
    @if(config('services.google_maps.api_key'))
        <script async defer 
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=geometry,places&callback=initMap">
        </script>
    @endif
    
    <style>
        #map-container {
            height: 70vh;
            min-height: 500px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .map-controls {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .guard-info-window {
            max-width: 300px;
            font-size: 14px;
        }
        
        .guard-info-window .guard-name {
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 5px;
        }
        
        .guard-info-window .guard-details {
            margin-bottom: 8px;
        }
        
        .guard-info-window .location-update {
            font-size: 12px;
            color: #666;
            font-style: italic;
        }
        
        .legend {
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .legend-marker {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .status-active { background-color: #28a745; }
        .status-on-shift { background-color: #007bff; }
        .status-inactive { background-color: #6c757d; }
        .status-emergency { background-color: #dc3545; }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- ページヘッダー -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">警備員位置管理マップ</h1>
                    <p class="text-muted mb-0">リアルタイム警備員位置情報・現場管理システム</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary me-2" onclick="refreshLocations()">
                        <i class="fas fa-sync-alt"></i> 位置情報更新
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="toggleFullscreen()">
                        <i class="fas fa-expand"></i> 全画面表示
                    </button>
                </div>
            </div>

            <!-- 地図コントロール -->
            <div class="map-controls">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <label class="form-label me-3 mb-0">表示フィルター:</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="show-active" checked onchange="updateMapFilters()">
                                <label class="form-check-label" for="show-active">勤務中</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="show-on-shift" checked onchange="updateMapFilters()">
                                <label class="form-check-label" for="show-on-shift">シフト中</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="show-projects" checked onchange="updateMapFilters()">
                                <label class="form-check-label" for="show-projects">現場表示</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-end">
                            <label class="form-label me-2 mb-0">更新間隔:</label>
                            <select class="form-select form-select-sm" style="width: auto;" onchange="setRefreshInterval(this.value)">
                                <option value="30">30秒</option>
                                <option value="60" selected>1分</option>
                                <option value="300">5分</option>
                                <option value="0">手動のみ</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Google Maps表示エリア -->
            <div id="map-container"></div>

            <!-- 凡例 -->
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="legend">
                        <h6 class="mb-2">警備員ステータス</h6>
                        <div class="legend-item">
                            <div class="legend-marker status-active"></div>
                            <span>勤務中</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-marker status-on-shift"></div>
                            <span>シフト勤務中</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-marker status-inactive"></div>
                            <span>待機中</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-marker status-emergency"></div>
                            <span>緊急事態</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <!-- リアルタイム統計 -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body py-2">
                                    <div class="h4 mb-0 text-primary" id="total-guards">-</div>
                                    <small class="text-muted">総警備員数</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body py-2">
                                    <div class="h4 mb-0 text-success" id="active-guards">-</div>
                                    <small class="text-muted">勤務中</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body py-2">
                                    <div class="h4 mb-0 text-info" id="on-shift-guards">-</div>
                                    <small class="text-muted">シフト中</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body py-2">
                                    <div class="h4 mb-0 text-warning" id="last-update">-</div>
                                    <small class="text-muted">最終更新</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 警備員詳細モーダル -->
<div class="modal fade" id="guardDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">警備員詳細情報</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="guard-detail-content">
                    <!-- 動的に警備員詳細情報を読み込み -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" onclick="updateGuardLocation()">位置情報更新</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Google Maps関連の変数
let map;
let guardMarkers = [];
let projectMarkers = [];
let infoWindows = [];
let refreshInterval;
let isFullscreen = false;

// 初期データ（サーバーから渡される）
const initialGuards = @json($activeGuards ?? []);

/**
 * Google Mapsの初期化
 */
function initMap() {
    // 地図の初期設定
    const mapOptions = {
        zoom: {{ config('services.google_maps.default_zoom', 12) }},
        center: {
            lat: {{ config('services.google_maps.default_lat', 35.6762) }},
            lng: {{ config('services.google_maps.default_lng', 139.6503) }}
        },
        mapTypeId: 'roadmap',
        styles: @json(config('services.google_maps.styles.security_theme', [])),
        ...@json(config('services.google_maps.options', []))
    };

    // 地図を作成
    map = new google.maps.Map(document.getElementById('map-container'), mapOptions);

    // 初期データを地図に表示
    loadGuardsOnMap(initialGuards);

    // 定期更新の設定
    setRefreshInterval(60); // デフォルト1分間隔

    // 地図クリックイベント
    map.addListener('click', function(event) {
        closeAllInfoWindows();
    });

    console.log('Google Maps初期化完了');
}

/**
 * 警備員データを地図に表示
 */
function loadGuardsOnMap(guards) {
    // 既存マーカーをクリア
    clearMarkers();

    guards.forEach(guard => {
        createGuardMarker(guard);
    });

    // 統計を更新
    updateStatistics(guards);

    console.log(`${guards.length}名の警備員を地図に表示しました`);
}

/**
 * 警備員マーカーを作成
 */
function createGuardMarker(guard) {
    if (!guard.latitude || !guard.longitude) {
        return;
    }

    // マーカーの色を決定
    let markerColor = '#6c757d'; // デフォルト: グレー
    if (guard.shift_info && guard.shift_info.status === 'active') {
        markerColor = '#007bff'; // 青: シフト中
    } else if (guard.status === 'active') {
        markerColor = '#28a745'; // 緑: 勤務中
    }

    // カスタムマーカーアイコン
    const markerIcon = {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 8,
        fillColor: markerColor,
        fillOpacity: 0.8,
        strokeColor: '#ffffff',
        strokeWeight: 2
    };

    // マーカーを作成
    const marker = new google.maps.Marker({
        position: { lat: parseFloat(guard.latitude), lng: parseFloat(guard.longitude) },
        map: map,
        icon: markerIcon,
        title: guard.name,
        animation: google.maps.Animation.DROP
    });

    // 情報ウィンドウを作成
    const infoWindowContent = createInfoWindowContent(guard);
    const infoWindow = new google.maps.InfoWindow({
        content: infoWindowContent
    });

    // マーカークリックイベント
    marker.addListener('click', function() {
        closeAllInfoWindows();
        infoWindow.open(map, marker);
    });

    // 配列に保存
    guardMarkers.push(marker);
    infoWindows.push(infoWindow);
}

/**
 * 情報ウィンドウのコンテンツを作成
 */
function createInfoWindowContent(guard) {
    const shiftInfo = guard.shift_info || {};
    const lastUpdate = guard.last_update ? new Date(guard.last_update).toLocaleString('ja-JP') : '不明';

    return `
        <div class="guard-info-window">
            <div class="guard-name">${guard.name} (${guard.employee_id})</div>
            <div class="guard-details">
                <strong>会社:</strong> ${guard.company}<br>
                ${shiftInfo.project_name ? `<strong>現在のプロジェクト:</strong> ${shiftInfo.project_name}<br>` : ''}
                ${shiftInfo.start_time ? `<strong>勤務時間:</strong> ${shiftInfo.start_time} - ${shiftInfo.end_time}<br>` : ''}
                <strong>ステータス:</strong> <span class="badge bg-primary">${getStatusLabel(guard.shift_info?.status)}</span>
            </div>
            <div class="location-update">最終更新: ${lastUpdate}</div>
            <div class="mt-2">
                <button class="btn btn-sm btn-outline-primary" onclick="showGuardDetails(${guard.id})">
                    詳細表示
                </button>
                <button class="btn btn-sm btn-outline-success" onclick="centerMapOnGuard(${guard.latitude}, ${guard.longitude})">
                    地図中央に表示
                </button>
            </div>
        </div>
    `;
}

/**
 * ステータスラベルを取得
 */
function getStatusLabel(status) {
    switch(status) {
        case 'active': return 'シフト中';
        case 'completed': return '勤務終了';
        case 'cancelled': return 'キャンセル';
        default: return '待機中';
    }
}

/**
 * すべての情報ウィンドウを閉じる
 */
function closeAllInfoWindows() {
    infoWindows.forEach(infoWindow => {
        infoWindow.close();
    });
}

/**
 * 既存マーカーをクリア
 */
function clearMarkers() {
    guardMarkers.forEach(marker => {
        marker.setMap(null);
    });
    projectMarkers.forEach(marker => {
        marker.setMap(null);
    });
    guardMarkers = [];
    projectMarkers = [];
    infoWindows = [];
}

/**
 * 位置情報を更新
 */
function refreshLocations() {
    console.log('位置情報を更新中...');
    
    fetch('{{ route("guards.map") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadGuardsOnMap(data.data.guards);
        } else {
            console.error('位置情報の取得に失敗:', data.message);
        }
    })
    .catch(error => {
        console.error('位置情報更新エラー:', error);
    });
}

/**
 * 統計情報を更新
 */
function updateStatistics(guards) {
    const totalGuards = guards.length;
    const activeGuards = guards.filter(g => g.shift_info && g.shift_info.status === 'active').length;
    const onShiftGuards = guards.filter(g => g.shift_info).length;
    
    document.getElementById('total-guards').textContent = totalGuards;
    document.getElementById('active-guards').textContent = activeGuards;
    document.getElementById('on-shift-guards').textContent = onShiftGuards;
    document.getElementById('last-update').textContent = new Date().toLocaleTimeString('ja-JP');
}

/**
 * 更新間隔を設定
 */
function setRefreshInterval(seconds) {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }

    if (seconds > 0) {
        refreshInterval = setInterval(refreshLocations, seconds * 1000);
        console.log(`自動更新を${seconds}秒間隔で設定しました`);
    } else {
        console.log('自動更新を停止しました');
    }
}

/**
 * マップフィルターを更新
 */
function updateMapFilters() {
    // 実装予定: フィルター機能
    console.log('フィルター更新');
}

/**
 * 全画面表示の切り替え
 */
function toggleFullscreen() {
    const mapContainer = document.getElementById('map-container');
    
    if (!isFullscreen) {
        mapContainer.style.position = 'fixed';
        mapContainer.style.top = '0';
        mapContainer.style.left = '0';
        mapContainer.style.width = '100vw';
        mapContainer.style.height = '100vh';
        mapContainer.style.zIndex = '9999';
        isFullscreen = true;
    } else {
        mapContainer.style.position = 'static';
        mapContainer.style.width = 'auto';
        mapContainer.style.height = '70vh';
        mapContainer.style.zIndex = 'auto';
        isFullscreen = false;
    }
    
    // 地図サイズの再計算
    setTimeout(() => {
        google.maps.event.trigger(map, 'resize');
    }, 100);
}

/**
 * 地図を指定座標に中央表示
 */
function centerMapOnGuard(lat, lng) {
    map.setCenter({ lat: parseFloat(lat), lng: parseFloat(lng) });
    map.setZoom(16);
}

/**
 * 警備員詳細を表示
 */
function showGuardDetails(guardId) {
    // モーダルを表示してAjaxで詳細情報を取得
    const modal = new bootstrap.Modal(document.getElementById('guardDetailModal'));
    modal.show();
    
    // 詳細情報をAjaxで取得
    fetch(`/guards/${guardId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('guard-detail-content').innerHTML = 
            `<p>警備員ID: ${guardId}の詳細情報を読み込み中...</p>`;
    })
    .catch(error => {
        document.getElementById('guard-detail-content').innerHTML = 
            `<p class="text-danger">詳細情報の読み込みに失敗しました。</p>`;
    });
}

// ページ読み込み完了時の処理
document.addEventListener('DOMContentLoaded', function() {
    console.log('Google Maps 警備員位置管理システム準備完了');
});

// ページ離脱時の清理
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>
@endsection
