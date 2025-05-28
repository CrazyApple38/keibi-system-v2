@extends('layouts.app')

@section('title', 'プロジェクト現場管理マップ')

@section('head')
    <!-- Google Maps API -->
    @if(config('services.google_maps.api_key'))
        <script async defer 
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=geometry,places&callback=initProjectMap">
        </script>
    @endif
    
    <style>
        #project-map-container {
            height: 70vh;
            min-height: 500px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .project-map-controls {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .project-info-window {
            max-width: 350px;
            font-size: 14px;
        }
        
        .project-info-window .project-name {
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .project-info-window .project-details {
            margin-bottom: 8px;
        }
        
        .project-info-window .status-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-planning { background-color: #ffc107; color: #000; }
        .status-active { background-color: #28a745; color: #fff; }
        .status-completed { background-color: #6c757d; color: #fff; }
        .status-cancelled { background-color: #dc3545; color: #fff; }
        .status-on-hold { background-color: #fd7e14; color: #fff; }
        
        .priority-high { color: #dc3545; font-weight: bold; }
        .priority-medium { color: #ffc107; font-weight: bold; }
        .priority-low { color: #28a745; font-weight: bold; }
        
        .project-legend {
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
        
        .marker-planning { background-color: #ffc107; }
        .marker-active { background-color: #28a745; }
        .marker-completed { background-color: #6c757d; }
        .marker-cancelled { background-color: #dc3545; }
        .marker-on-hold { background-color: #fd7e14; }
        
        .info-panel {
            position: absolute;
            top: 80px;
            right: 10px;
            width: 300px;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
        }
        
        .info-panel-header {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
        }
        
        .info-panel-body {
            padding: 15px;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- ページヘッダー -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">プロジェクト現場管理マップ</h1>
                    <p class="text-muted mb-0">現場位置管理・警備員配置最適化システム</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary me-2" onclick="refreshProjectLocations()">
                        <i class="fas fa-sync-alt"></i> 現場情報更新
                    </button>
                    <button type="button" class="btn btn-outline-secondary me-2" onclick="showOptimizeRouteModal()">
                        <i class="fas fa-route"></i> ルート最適化
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="toggleProjectInfoPanel()">
                        <i class="fas fa-info-circle"></i> 情報パネル
                    </button>
                </div>
            </div>

            <!-- 地図コントロール -->
            <div class="project-map-controls">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <label class="form-label me-3 mb-0">表示フィルター:</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="show-planning" checked onchange="updateProjectMapFilters()">
                                <label class="form-check-label" for="show-planning">計画中</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="show-active" checked onchange="updateProjectMapFilters()">
                                <label class="form-check-label" for="show-active">進行中</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="show-completed" onchange="updateProjectMapFilters()">
                                <label class="form-check-label" for="show-completed">完了</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="show-guards" checked onchange="updateProjectMapFilters()">
                                <label class="form-check-label" for="show-guards">警備員表示</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-end">
                            <label class="form-label me-2 mb-0">優先度:</label>
                            <select class="form-select form-select-sm" style="width: auto;" onchange="filterByPriority(this.value)">
                                <option value="">すべて</option>
                                <option value="high">高</option>
                                <option value="medium">中</option>
                                <option value="low">低</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Google Maps表示エリア -->
            <div class="position-relative">
                <div id="project-map-container"></div>
                
                <!-- 情報パネル -->
                <div id="info-panel" class="info-panel">
                    <div class="info-panel-header">
                        <h6 class="mb-0">プロジェクト情報</h6>
                    </div>
                    <div class="info-panel-body">
                        <div id="info-panel-content">
                            プロジェクトを選択してください
                        </div>
                    </div>
                </div>
            </div>

            <!-- 統計情報・凡例 -->
            <div class="row mt-3">
                <div class="col-md-8">
                    <!-- リアルタイム統計 -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body py-2">
                                    <div class="h4 mb-0 text-primary" id="total-projects">-</div>
                                    <small class="text-muted">総プロジェクト数</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body py-2">
                                    <div class="h4 mb-0 text-success" id="active-projects">-</div>
                                    <small class="text-muted">進行中</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body py-2">
                                    <div class="h4 mb-0 text-warning" id="planning-projects">-</div>
                                    <small class="text-muted">計画中</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body py-2">
                                    <div class="h4 mb-0 text-info" id="total-guards-assigned">-</div>
                                    <small class="text-muted">配置警備員数</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="project-legend">
                        <h6 class="mb-2">プロジェクトステータス</h6>
                        <div class="legend-item">
                            <div class="legend-marker marker-planning"></div>
                            <span>計画中</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-marker marker-active"></div>
                            <span>進行中</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-marker marker-completed"></div>
                            <span>完了</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-marker marker-cancelled"></div>
                            <span>キャンセル</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-marker marker-on-hold"></div>
                            <span>保留</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ルート最適化モーダル -->
<div class="modal fade" id="routeOptimizeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">マルチプロジェクトルート最適化</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">対象プロジェクト</label>
                    <div id="project-selection">
                        <!-- 動的にプロジェクト一覧を生成 -->
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">最適化タイプ</label>
                    <select class="form-select" id="optimization-type">
                        <option value="shortest_distance">最短距離</option>
                        <option value="shortest_time">最短時間</option>
                        <option value="balanced" selected>バランス重視</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">開始地点</label>
                    <input type="text" class="form-control" id="start-location" placeholder="住所を入力（空白の場合はデフォルト位置）">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="executeRouteOptimization()">ルート最適化実行</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Google Maps関連の変数
let projectMap;
let projectMarkers = [];
let guardMarkers = [];
let infoWindows = [];
let currentInfoWindow = null;

// 初期データ（サーバーから渡される）
const initialProjects = @json($projectsMapData ?? []);

/**
 * Google Mapsの初期化（プロジェクト用）
 */
function initProjectMap() {
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
    projectMap = new google.maps.Map(document.getElementById('project-map-container'), mapOptions);

    // 初期データを地図に表示
    loadProjectsOnMap(initialProjects);

    // 地図クリックイベント
    projectMap.addListener('click', function(event) {
        closeAllInfoWindows();
        hideInfoPanel();
    });

    console.log('Google Maps（プロジェクト用）初期化完了');
}

/**
 * プロジェクトデータを地図に表示
 */
function loadProjectsOnMap(projects) {
    // 既存マーカーをクリア
    clearProjectMarkers();

    projects.forEach(project => {
        createProjectMarker(project);
    });

    // 統計を更新
    updateProjectStatistics(projects);

    console.log(`${projects.length}個のプロジェクトを地図に表示しました`);
}

/**
 * プロジェクトマーカーを作成
 */
function createProjectMarker(project) {
    if (!project.latitude || !project.longitude) {
        return;
    }

    // マーカーの色を決定（ステータス別）
    let markerColor = getProjectMarkerColor(project.status);

    // カスタムマーカーアイコン
    const markerIcon = {
        path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
        scale: 6,
        fillColor: markerColor,
        fillOpacity: 0.8,
        strokeColor: '#ffffff',
        strokeWeight: 2,
        rotation: 0
    };

    // マーカーを作成
    const marker = new google.maps.Marker({
        position: { lat: parseFloat(project.latitude), lng: parseFloat(project.longitude) },
        map: projectMap,
        icon: markerIcon,
        title: project.name,
        animation: google.maps.Animation.DROP
    });

    // 情報ウィンドウを作成
    const infoWindowContent = createProjectInfoWindowContent(project);
    const infoWindow = new google.maps.InfoWindow({
        content: infoWindowContent
    });

    // マーカークリックイベント
    marker.addListener('click', function() {
        closeAllInfoWindows();
        infoWindow.open(projectMap, marker);
        currentInfoWindow = infoWindow;
        showProjectInfo(project);
    });

    // 配列に保存
    projectMarkers.push(marker);
    infoWindows.push(infoWindow);
    
    // マーカーにプロジェクトデータを保存
    marker.projectData = project;
}

/**
 * プロジェクト情報ウィンドウのコンテンツを作成
 */
function createProjectInfoWindowContent(project) {
    const statusClass = `status-${project.status}`;
    const priorityClass = `priority-${project.priority}`;
    
    return `
        <div class="project-info-window">
            <div class="project-name">${project.name}</div>
            <div class="project-details">
                <strong>顧客:</strong> ${project.customer}<br>
                <strong>住所:</strong> ${project.address}<br>
                <strong>ステータス:</strong> <span class="status-badge ${statusClass}">${getStatusLabel(project.status)}</span><br>
                <strong>優先度:</strong> <span class="${priorityClass}">${getPriorityLabel(project.priority)}</span><br>
                <strong>期間:</strong> ${project.start_date} - ${project.end_date || '未定'}<br>
                <strong>配置警備員:</strong> ${project.assigned_guards_count}/${project.required_guards}名
            </div>
            <div class="mt-2">
                <button class="btn btn-sm btn-outline-primary me-1" onclick="showProjectDetails(${project.id})">
                    詳細表示
                </button>
                <button class="btn btn-sm btn-outline-success me-1" onclick="centerMapOnProject(${project.latitude}, ${project.longitude})">
                    地図中央に表示
                </button>
                <button class="btn btn-sm btn-outline-info" onclick="findNearbyGuards(${project.id})">
                    近隣警備員検索
                </button>
            </div>
        </div>
    `;
}

/**
 * プロジェクトマーカーの色を取得
 */
function getProjectMarkerColor(status) {
    switch(status) {
        case 'planning': return '#ffc107';
        case 'active': return '#28a745';
        case 'completed': return '#6c757d';
        case 'cancelled': return '#dc3545';
        case 'on_hold': return '#fd7e14';
        default: return '#17a2b8';
    }
}

/**
 * ステータスラベルを取得
 */
function getStatusLabel(status) {
    switch(status) {
        case 'planning': return '計画中';
        case 'active': return '進行中';
        case 'completed': return '完了';
        case 'cancelled': return 'キャンセル';
        case 'on_hold': return '保留';
        default: return '不明';
    }
}

/**
 * 優先度ラベルを取得
 */
function getPriorityLabel(priority) {
    switch(priority) {
        case 'high': return '高';
        case 'medium': return '中';
        case 'low': return '低';
        default: return '通常';
    }
}

/**
 * すべての情報ウィンドウを閉じる
 */
function closeAllInfoWindows() {
    infoWindows.forEach(infoWindow => {
        infoWindow.close();
    });
    currentInfoWindow = null;
}

/**
 * 既存マーカーをクリア
 */
function clearProjectMarkers() {
    projectMarkers.forEach(marker => {
        marker.setMap(null);
    });
    guardMarkers.forEach(marker => {
        marker.setMap(null);
    });
    projectMarkers = [];
    guardMarkers = [];
    infoWindows = [];
}

/**
 * プロジェクト位置情報を更新
 */
function refreshProjectLocations() {
    console.log('プロジェクト位置情報を更新中...');
    
    fetch('{{ route("projects.map") }}', {
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
            loadProjectsOnMap(data.data.projects);
        } else {
            console.error('プロジェクト位置情報の取得に失敗:', data.message);
        }
    })
    .catch(error => {
        console.error('プロジェクト位置情報更新エラー:', error);
    });
}

/**
 * 統計情報を更新
 */
function updateProjectStatistics(projects) {
    const totalProjects = projects.length;
    const activeProjects = projects.filter(p => p.status === 'active').length;
    const planningProjects = projects.filter(p => p.status === 'planning').length;
    const totalGuardsAssigned = projects.reduce((sum, p) => sum + (p.assigned_guards_count || 0), 0);
    
    document.getElementById('total-projects').textContent = totalProjects;
    document.getElementById('active-projects').textContent = activeProjects;
    document.getElementById('planning-projects').textContent = planningProjects;
    document.getElementById('total-guards-assigned').textContent = totalGuardsAssigned;
}

/**
 * 地図を指定座標に中央表示
 */
function centerMapOnProject(lat, lng) {
    projectMap.setCenter({ lat: parseFloat(lat), lng: parseFloat(lng) });
    projectMap.setZoom(16);
}

/**
 * プロジェクト詳細を表示
 */
function showProjectDetails(projectId) {
    // プロジェクト詳細ページに遷移
    window.open(`/projects/${projectId}`, '_blank');
}

/**
 * 近隣警備員を検索
 */
function findNearbyGuards(projectId) {
    fetch(`/projects/${projectId}/guards/nearby`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            displayNearbyGuards(data.data.nearby_guards);
        }
    })
    .catch(error => {
        console.error('近隣警備員検索エラー:', error);
    });
}

/**
 * 近隣警備員を地図に表示
 */
function displayNearbyGuards(guards) {
    // 既存の警備員マーカーをクリア
    guardMarkers.forEach(marker => marker.setMap(null));
    guardMarkers = [];

    guards.forEach(guard => {
        const marker = new google.maps.Marker({
            position: { lat: guard.location.latitude, lng: guard.location.longitude },
            map: projectMap,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 6,
                fillColor: '#007bff',
                fillOpacity: 0.8,
                strokeColor: '#ffffff',
                strokeWeight: 2
            },
            title: guard.name
        });

        guardMarkers.push(marker);
    });

    console.log(`${guards.length}名の近隣警備員を表示しました`);
}

/**
 * マップフィルターを更新
 */
function updateProjectMapFilters() {
    // フィルター実装
    console.log('フィルター更新');
}

/**
 * 優先度でフィルタリング
 */
function filterByPriority(priority) {
    // 優先度フィルター実装
    console.log('優先度フィルター:', priority);
}

/**
 * ルート最適化モーダルを表示
 */
function showOptimizeRouteModal() {
    // プロジェクト選択リストを生成
    const projectSelection = document.getElementById('project-selection');
    projectSelection.innerHTML = '';
    
    initialProjects.forEach(project => {
        if (project.status === 'active' || project.status === 'planning') {
            const checkbox = document.createElement('div');
            checkbox.className = 'form-check';
            checkbox.innerHTML = `
                <input class="form-check-input" type="checkbox" value="${project.id}" id="project-${project.id}">
                <label class="form-check-label" for="project-${project.id}">
                    ${project.name} (${project.customer})
                </label>
            `;
            projectSelection.appendChild(checkbox);
        }
    });

    const modal = new bootstrap.Modal(document.getElementById('routeOptimizeModal'));
    modal.show();
}

/**
 * ルート最適化を実行
 */
function executeRouteOptimization() {
    const selectedProjects = Array.from(document.querySelectorAll('#project-selection input:checked')).map(cb => cb.value);
    const optimizationType = document.getElementById('optimization-type').value;
    const startLocation = document.getElementById('start-location').value;

    if (selectedProjects.length < 2) {
        alert('ルート最適化には2つ以上のプロジェクトを選択してください');
        return;
    }

    // ルート最適化APIを呼び出し
    fetch('/projects/routes/multi-project', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            project_ids: selectedProjects,
            optimization_type: optimizationType,
            start_location: startLocation
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            displayOptimizedRoute(data.data);
            bootstrap.Modal.getInstance(document.getElementById('routeOptimizeModal')).hide();
        }
    })
    .catch(error => {
        console.error('ルート最適化エラー:', error);
    });
}

/**
 * 最適化されたルートを表示
 */
function displayOptimizedRoute(routeData) {
    console.log('最適化ルートを表示:', routeData);
    // ルート表示の実装
}

/**
 * 情報パネルの表示・非表示切り替え
 */
function toggleProjectInfoPanel() {
    const panel = document.getElementById('info-panel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

/**
 * 情報パネルを非表示
 */
function hideInfoPanel() {
    document.getElementById('info-panel').style.display = 'none';
}

/**
 * プロジェクト情報を表示
 */
function showProjectInfo(project) {
    const content = document.getElementById('info-panel-content');
    content.innerHTML = `
        <h6>${project.name}</h6>
        <p class="mb-1"><strong>顧客:</strong> ${project.customer}</p>
        <p class="mb-1"><strong>ステータス:</strong> ${getStatusLabel(project.status)}</p>
        <p class="mb-1"><strong>優先度:</strong> ${getPriorityLabel(project.priority)}</p>
        <p class="mb-1"><strong>配置警備員:</strong> ${project.assigned_guards_count}/${project.required_guards}名</p>
        <p class="mb-1"><strong>住所:</strong> ${project.address}</p>
        <hr>
        <button class="btn btn-sm btn-primary w-100 mb-2" onclick="showProjectDetails(${project.id})">
            詳細表示
        </button>
        <button class="btn btn-sm btn-outline-success w-100" onclick="findNearbyGuards(${project.id})">
            近隣警備員検索
        </button>
    `;
    
    document.getElementById('info-panel').style.display = 'block';
}

// ページ読み込み完了時の処理
document.addEventListener('DOMContentLoaded', function() {
    console.log('Google Maps プロジェクト現場管理システム準備完了');
});
</script>
@endsection
