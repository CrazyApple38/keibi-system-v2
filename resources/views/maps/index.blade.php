@extends('layouts.app')

@section('title', '地図管理 - 警備システム')

@section('styles')
<style>
    /* 地図専用スタイル */
    #map {
        height: 70vh;
        min-height: 500px;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .map-controls {
        background: white;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .statistics-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .legend {
        background: white;
        border-radius: 0.5rem;
        padding: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .legend-marker {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-right: 0.5rem;
    }
    
    .marker-active { background-color: #28a745; }
    .marker-available { background-color: #17a2b8; }
    .marker-inactive { background-color: #6c757d; }
    .marker-project { background-color: #fd7e14; }
    .marker-emergency { background-color: #dc3545; }
    
    .info-window {
        min-width: 200px;
        font-size: 0.9rem;
    }
    
    .route-optimization {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-top: 1rem;
    }
    
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        border-radius: 0.5rem;
    }
    
    .real-time-indicator {
        width: 10px;
        height: 10px;
        background: #28a745;
        border-radius: 50%;
        animation: blink 1s infinite;
    }
    
    @keyframes blink {
        0%, 50% { opacity: 1; }
        51%, 100% { opacity: 0.3; }
    }
    
    .location-history {
        max-height: 200px;
        overflow-y: auto;
    }
    
    .emergency-alert {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
        border-radius: 0.25rem;
        padding: 0.75rem;
        margin-bottom: 1rem;
    }
    
    @media print {
        .map-controls, .no-print { display: none !important; }
        #map { height: 400px !important; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">地図管理システム</h1>
                    <p class="text-muted mb-0">警備員位置管理・現場監視・ルート最適化</p>
                </div>
                <div class="d-flex gap-2 no-print">
                    <div class="real-time-indicator"></div>
                    <span class="text-success fw-bold">リアルタイム監視中</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 緊急アラート表示 -->
    <div id="emergency-alerts"></div>

    <!-- 統計情報 -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="statistics-card">
                <div class="row text-center">
                    <div class="col-md-2">
                        <div class="fs-2 fw-bold">{{ $statistics['active_guards'] }}</div>
                        <div class="small">アクティブ警備員</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fs-2 fw-bold">{{ $statistics['active_projects'] }}</div>
                        <div class="small">稼働中現場</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fs-2 fw-bold">{{ $statistics['ongoing_shifts'] }}</div>
                        <div class="small">進行中シフト</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fs-2 fw-bold">{{ $statistics['location_updates_today'] }}</div>
                        <div class="small">本日位置更新</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fs-2 fw-bold text-warning">{{ $statistics['emergency_alerts'] }}</div>
                        <div class="small">緊急アラート</div>
                    </div>
                    <div class="col-md-2">
                        <div class="fs-2 fw-bold">{{ number_format($statistics['coverage_area'], 1) }}km²</div>
                        <div class="small">カバレッジエリア</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 地図表示エリア -->
        <div class="col-lg-9">
            <!-- 地図コントロール -->
            <div class="map-controls no-print">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">表示フィルター</label>
                        <select class="form-select" id="displayFilter">
                            <option value="all">すべて表示</option>
                            <option value="guards">警備員のみ</option>
                            <option value="projects">現場のみ</option>
                            <option value="active">稼働中のみ</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">会社選択</label>
                        <select class="form-select" id="companyFilter">
                            <option value="">全会社</option>
                            @foreach($companies as $company)
                                <option value="{{ $company }}">{{ $company }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">自動更新</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="autoRefresh" checked>
                            <label class="form-check-label" for="autoRefresh">ON</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">操作</label>
                        <div class="btn-group w-100">
                            <button type="button" class="btn btn-primary" id="refreshMap">
                                <i class="bi bi-arrow-clockwise"></i> 更新
                            </button>
                            <button type="button" class="btn btn-info" id="centerMap">
                                <i class="bi bi-geo-alt"></i> 中心化
                            </button>
                            <button type="button" class="btn btn-warning" id="emergencyCheck">
                                <i class="bi bi-exclamation-triangle"></i> 緊急確認
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 地図表示 -->
            <div class="position-relative">
                <div id="map"></div>
                <div id="mapLoading" class="loading-overlay d-none">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">読み込み中...</span>
                        </div>
                        <div class="mt-2">地図データを読み込み中...</div>
                    </div>
                </div>
            </div>

            <!-- ルート最適化パネル -->
            <div class="route-optimization">
                <h5 class="mb-3">ルート最適化</h5>
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">出発地点</label>
                            <input type="text" class="form-control" id="startLocation" placeholder="出発地点を入力またはクリックで設定">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">最適化タイプ</label>
                            <select class="form-select" id="optimizationType">
                                <option value="time">時間優先</option>
                                <option value="distance">距離優先</option>
                                <option value="cost">コスト優先</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="h-100 d-flex flex-column justify-content-center">
                            <button type="button" class="btn btn-success mb-2" id="optimizeRoute">
                                <i class="bi bi-diagram-2"></i> ルート最適化
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="clearRoute">
                                <i class="bi bi-x-circle"></i> クリア
                            </button>
                        </div>
                    </div>
                </div>
                <div id="routeResults" class="mt-3 d-none">
                    <div class="alert alert-success">
                        <h6>最適化結果</h6>
                        <div class="row">
                            <div class="col-md-4">総距離: <strong id="totalDistance">-</strong>km</div>
                            <div class="col-md-4">所要時間: <strong id="totalDuration">-</strong>分</div>
                            <div class="col-md-4">推定コスト: <strong id="estimatedCost">-</strong>円</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- サイドパネル -->
        <div class="col-lg-3">
            <!-- 凡例 -->
            <div class="legend mb-4">
                <h5 class="mb-3">凡例</h5>
                <div class="legend-item">
                    <div class="legend-marker marker-active"></div>
                    <span>勤務中警備員</span>
                </div>
                <div class="legend-item">
                    <div class="legend-marker marker-available"></div>
                    <span>待機中警備員</span>
                </div>
                <div class="legend-item">
                    <div class="legend-marker marker-inactive"></div>
                    <span>非番警備員</span>
                </div>
                <div class="legend-item">
                    <div class="legend-marker marker-project"></div>
                    <span>現場・プロジェクト</span>
                </div>
                <div class="legend-item">
                    <div class="legend-marker marker-emergency"></div>
                    <span>緊急アラート</span>
                </div>
            </div>

            <!-- 警備員リスト -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">警備員一覧</h5>
                    <span class="badge bg-primary" id="guardCount">{{ $guards->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="guardsList">
                        @foreach($guards as $guard)
                        <div class="list-group-item guard-item" data-guard-id="{{ $guard->id }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">{{ $guard->name }}</div>
                                    <small class="text-muted">{{ $guard->status }}</small>
                                </div>
                                <div class="text-end">
                                    <div class="legend-marker marker-{{ $guard->status === 'active' ? 'available' : 'inactive' }}"></div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- プロジェクト一覧 -->
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">現場一覧</h5>
                    <span class="badge bg-info" id="projectCount">{{ $projects->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="projectsList">
                        @foreach($projects as $project)
                        <div class="list-group-item project-item" data-project-id="{{ $project->id }}">
                            <div class="fw-bold">{{ $project->name }}</div>
                            <small class="text-muted">{{ $project->location ? '位置情報あり' : '位置情報なし' }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 詳細情報モーダル -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalTitle">詳細情報</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <!-- 動的コンテンツ -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Google Maps JavaScript API -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap&libraries=geometry"></script>

<script>
// グローバル変数
let map;
let guardMarkers = {};
let projectMarkers = {};
let routePolyline;
let infoWindow;
let autoRefreshInterval;

// 地図初期化
function initMap() {
    // 東京を中心とした初期地図
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 11,
        center: { lat: 35.6762, lng: 139.6503 }, // 東京駅
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true,
        styles: [
            {
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{ visibility: 'on' }]
            }
        ]
    });
    
    infoWindow = new google.maps.InfoWindow();
    
    // 初期データ読み込み
    loadMapData();
    
    // 自動更新設定
    setupAutoRefresh();
    
    // イベントリスナー設定
    setupEventListeners();
}

// 地図データ読み込み
function loadMapData() {
    showLoading(true);
    
    Promise.all([
        loadGuardLocations(),
        loadProjectLocations()
    ]).then(() => {
        showLoading(false);
    }).catch(error => {
        console.error('データ読み込みエラー:', error);
        showLoading(false);
        showAlert('データの読み込みに失敗しました', 'danger');
    });
}

// 警備員位置情報読み込み
function loadGuardLocations() {
    const params = new URLSearchParams();
    
    if (document.getElementById('companyFilter').value) {
        params.append('company', document.getElementById('companyFilter').value);
    }
    
    return fetch(`/api/maps/guards?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayGuardMarkers(data.locations);
            } else {
                throw new Error(data.message);
            }
        });
}

// プロジェクト位置情報読み込み
function loadProjectLocations() {
    return fetch('/api/maps/projects')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayProjectMarkers(data.locations);
            } else {
                throw new Error(data.message);
            }
        });
}

// 警備員マーカー表示
function displayGuardMarkers(guards) {
    // 既存マーカークリア
    Object.values(guardMarkers).forEach(marker => marker.setMap(null));
    guardMarkers = {};
    
    guards.forEach(guard => {
        if (!guard.last_location) return;
        
        const position = {
            lat: parseFloat(guard.last_location.latitude),
            lng: parseFloat(guard.last_location.longitude)
        };
        
        const marker = new google.maps.Marker({
            position: position,
            map: map,
            title: guard.name,
            icon: getGuardIcon(guard.icon_type),
            zIndex: guard.icon_type === 'active' ? 100 : 50
        });
        
        // 情報ウィンドウ設定
        marker.addListener('click', () => {
            showGuardInfo(guard, marker);
        });
        
        guardMarkers[guard.guard_id] = marker;
    });
}

// プロジェクトマーカー表示
function displayProjectMarkers(projects) {
    // 既存マーカークリア
    Object.values(projectMarkers).forEach(marker => marker.setMap(null));
    projectMarkers = {};
    
    projects.forEach(project => {
        if (!project.location) return;
        
        const position = {
            lat: parseFloat(project.location.latitude),
            lng: parseFloat(project.location.longitude)
        };
        
        const marker = new google.maps.Marker({
            position: position,
            map: map,
            title: project.name,
            icon: getProjectIcon(project.status),
            zIndex: 75
        });
        
        // 情報ウィンドウ設定
        marker.addListener('click', () => {
            showProjectInfo(project, marker);
        });
        
        projectMarkers[project.project_id] = marker;
    });
}

// 警備員アイコン取得
function getGuardIcon(iconType) {
    const colors = {
        'active': '#28a745',
        'available': '#17a2b8',
        'inactive': '#6c757d'
    };
    
    return {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 8,
        fillColor: colors[iconType] || colors.inactive,
        fillOpacity: 0.8,
        strokeColor: '#ffffff',
        strokeWeight: 2
    };
}

// プロジェクトアイコン取得
function getProjectIcon(status) {
    return {
        path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
        scale: 6,
        fillColor: '#fd7e14',
        fillOpacity: 0.8,
        strokeColor: '#ffffff',
        strokeWeight: 2
    };
}

// 警備員情報表示
function showGuardInfo(guard, marker) {
    const lastUpdate = guard.last_location_update ? 
        new Date(guard.last_location_update).toLocaleString('ja-JP') : '不明';
    
    const content = `
        <div class="info-window">
            <h6 class="mb-2">${guard.name}</h6>
            <p class="mb-1"><strong>会社:</strong> ${guard.company}</p>
            <p class="mb-1"><strong>ステータス:</strong> ${guard.status}</p>
            <p class="mb-1"><strong>電話:</strong> ${guard.phone}</p>
            <p class="mb-1"><strong>最終更新:</strong> ${lastUpdate}</p>
            ${guard.current_shift ? `
                <hr class="my-2">
                <p class="mb-1"><strong>現在のシフト:</strong></p>
                <p class="mb-1">${guard.current_shift.project_name}</p>
                <p class="mb-0">
                    ${new Date(guard.current_shift.start_time).toLocaleTimeString('ja-JP', {hour: '2-digit', minute: '2-digit'})} - 
                    ${new Date(guard.current_shift.end_time).toLocaleTimeString('ja-JP', {hour: '2-digit', minute: '2-digit'})}
                </p>
            ` : ''}
            <div class="mt-2">
                <button class="btn btn-sm btn-primary" onclick="showGuardDetail(${guard.guard_id})">詳細表示</button>
            </div>
        </div>
    `;
    
    infoWindow.setContent(content);
    infoWindow.open(map, marker);
}

// プロジェクト情報表示
function showProjectInfo(project, marker) {
    const content = `
        <div class="info-window">
            <h6 class="mb-2">${project.name}</h6>
            <p class="mb-1"><strong>ステータス:</strong> ${project.status}</p>
            <p class="mb-1"><strong>本日のシフト:</strong> ${project.today_shifts_count}件</p>
            <p class="mb-1"><strong>稼働中警備員:</strong> ${project.active_guards_count}名</p>
            <p class="mb-1"><strong>リスクレベル:</strong> ${project.risk_level}</p>
            ${project.contact_person ? `
                <hr class="my-2">
                <p class="mb-1"><strong>担当者:</strong> ${project.contact_person}</p>
                <p class="mb-1"><strong>連絡先:</strong> ${project.contact_phone}</p>
            ` : ''}
            <div class="mt-2">
                <button class="btn btn-sm btn-info" onclick="showProjectDetail(${project.project_id})">詳細表示</button>
            </div>
        </div>
    `;
    
    infoWindow.setContent(content);
    infoWindow.open(map, marker);
}

// 自動更新設定
function setupAutoRefresh() {
    const checkbox = document.getElementById('autoRefresh');
    
    function toggleAutoRefresh() {
        if (checkbox.checked) {
            autoRefreshInterval = setInterval(loadMapData, 30000); // 30秒間隔
        } else {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        }
    }
    
    checkbox.addEventListener('change', toggleAutoRefresh);
    toggleAutoRefresh(); // 初期設定
}

// イベントリスナー設定
function setupEventListeners() {
    // 更新ボタン
    document.getElementById('refreshMap').addEventListener('click', loadMapData);
    
    // 中心化ボタン
    document.getElementById('centerMap').addEventListener('click', () => {
        map.setCenter({ lat: 35.6762, lng: 139.6503 });
        map.setZoom(11);
    });
    
    // 緊急確認ボタン
    document.getElementById('emergencyCheck').addEventListener('click', performEmergencyCheck);
    
    // フィルター変更
    document.getElementById('displayFilter').addEventListener('change', applyDisplayFilter);
    document.getElementById('companyFilter').addEventListener('change', loadMapData);
    
    // ルート最適化
    document.getElementById('optimizeRoute').addEventListener('click', optimizeRoute);
    document.getElementById('clearRoute').addEventListener('click', clearRoute);
    
    // 地図クリックでルート設定
    map.addListener('click', handleMapClick);
}

// 表示フィルター適用
function applyDisplayFilter() {
    const filter = document.getElementById('displayFilter').value;
    
    Object.values(guardMarkers).forEach(marker => {
        marker.setVisible(filter === 'all' || filter === 'guards' || filter === 'active');
    });
    
    Object.values(projectMarkers).forEach(marker => {
        marker.setVisible(filter === 'all' || filter === 'projects');
    });
}

// 緊急確認実行
function performEmergencyCheck() {
    showLoading(true);
    
    fetch('/api/maps/emergency-check')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayEmergencyAlerts(data.emergency_info);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            console.error('緊急確認エラー:', error);
            showAlert('緊急確認に失敗しました', 'danger');
        })
        .finally(() => {
            showLoading(false);
        });
}

// 緊急アラート表示
function displayEmergencyAlerts(emergencyInfo) {
    const alertsContainer = document.getElementById('emergency-alerts');
    alertsContainer.innerHTML = '';
    
    const highAlerts = emergencyInfo.filter(info => info.alert_level === 'high');
    
    if (highAlerts.length > 0) {
        const alertHtml = `
            <div class="emergency-alert">
                <h5><i class="bi bi-exclamation-triangle-fill"></i> 緊急アラート (${highAlerts.length}件)</h5>
                ${highAlerts.map(alert => `
                    <div class="mb-2">
                        <strong>${alert.name}</strong> (${alert.company}) - 
                        最終更新から${alert.minutes_since_update}分経過
                        <button class="btn btn-sm btn-danger ms-2" onclick="contactGuard(${alert.guard_id})">
                            連絡
                        </button>
                    </div>
                `).join('')}
            </div>
        `;
        alertsContainer.innerHTML = alertHtml;
    }
}

// ルート最適化
function optimizeRoute() {
    const startInput = document.getElementById('startLocation');
    const optimizationType = document.getElementById('optimizationType').value;
    
    if (!startInput.value) {
        showAlert('出発地点を設定してください', 'warning');
        return;
    }
    
    // 実装予定: Google Maps Directions API を使用
    showAlert('ルート最適化機能は開発中です', 'info');
}

// ルートクリア
function clearRoute() {
    if (routePolyline) {
        routePolyline.setMap(null);
        routePolyline = null;
    }
    
    document.getElementById('startLocation').value = '';
    document.getElementById('routeResults').classList.add('d-none');
}

// 地図クリック処理
function handleMapClick(event) {
    const startInput = document.getElementById('startLocation');
    
    if (!startInput.value) {
        // 出発地点設定
        startInput.value = `${event.latLng.lat().toFixed(6)}, ${event.latLng.lng().toFixed(6)}`;
    }
}

// ユーティリティ関数
function showLoading(show) {
    const loading = document.getElementById('mapLoading');
    if (show) {
        loading.classList.remove('d-none');
    } else {
        loading.classList.add('d-none');
    }
}

function showAlert(message, type = 'info') {
    // Bootstrap Toast または Alert 表示
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // 5秒後に自動削除
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

function showGuardDetail(guardId) {
    // 警備員詳細をモーダルで表示
    fetch(`/guards/${guardId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detailModalTitle').textContent = '警備員詳細';
            document.getElementById('detailModalBody').innerHTML = html;
            new bootstrap.Modal(document.getElementById('detailModal')).show();
        })
        .catch(error => {
            console.error('詳細取得エラー:', error);
            showAlert('詳細情報の取得に失敗しました', 'danger');
        });
}

function showProjectDetail(projectId) {
    // プロジェクト詳細をモーダルで表示
    fetch(`/projects/${projectId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detailModalTitle').textContent = 'プロジェクト詳細';
            document.getElementById('detailModalBody').innerHTML = html;
            new bootstrap.Modal(document.getElementById('detailModal')).show();
        })
        .catch(error => {
            console.error('詳細取得エラー:', error);
            showAlert('詳細情報の取得に失敗しました', 'danger');
        });
}

function contactGuard(guardId) {
    // 緊急連絡機能（実装予定）
    showAlert('緊急連絡機能は開発中です', 'info');
}

// ページ読み込み完了時の初期化
document.addEventListener('DOMContentLoaded', function() {
    // Google Maps API が読み込まれるまで待機
    if (typeof google === 'undefined') {
        console.log('Google Maps API読み込み待機中...');
    }
});
</script>
@endsection
