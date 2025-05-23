@extends('layouts.app')

@section('title', '案件一覧')

@section('content')
<div class="container-fluid">
    <!-- パンくずリスト -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
            <li class="breadcrumb-item active" aria-current="page">案件一覧</li>
        </ol>
    </nav>
    
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-briefcase me-2"></i>
                        案件一覧
                    </h2>
                    <p class="text-muted mb-0">プロジェクトの管理・進捗確認</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" id="exportProjects">
                        <i class="bi bi-download me-1"></i>
                        エクスポート
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-funnel me-1"></i>
                            フィルター
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-filter="all">すべて</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="active">アクティブ</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="completed">完了</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="on_hold">保留中</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('projects.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>
                        新規案件作成
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 統計カード -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">総案件数</h6>
                            <h3 class="mb-0" id="totalProjects">-</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-briefcase fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">アクティブ案件</h6>
                            <h3 class="mb-0" id="activeProjects">-</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-play-circle fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">今月完了</h6>
                            <h3 class="mb-0" id="completedProjects">-</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-check-circle fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">平均進捗率</h6>
                            <h3 class="mb-0" id="avgProgress">-</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-bar-chart fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 検索・フィルター -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="searchForm" class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">検索キーワード</label>
                            <input type="text" class="form-control search-input" name="search" 
                                   placeholder="案件名、顧客名など">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">ステータス</label>
                            <select class="form-select" name="status">
                                <option value="">すべて</option>
                                <option value="planning">計画中</option>
                                <option value="active">実行中</option>
                                <option value="on_hold">保留中</option>
                                <option value="completed">完了</option>
                                <option value="cancelled">キャンセル</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">優先度</label>
                            <select class="form-select" name="priority">
                                <option value="">すべて</option>
                                <option value="high">高</option>
                                <option value="normal">標準</option>
                                <option value="low">低</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">担当者</label>
                            <select class="form-select" name="assignee">
                                <option value="">すべて</option>
                                <option value="yamada">山田</option>
                                <option value="tanaka">田中</option>
                                <option value="sato">佐藤</option>
                                <option value="suzuki">鈴木</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-12">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>
                                    検索
                                </button>
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    リセット
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 案件一覧 -->
    <div class="row mb-4">
        <!-- 表示形式切り替え -->
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="viewMode" id="listView" autocomplete="off" checked>
                    <label class="btn btn-outline-primary" for="listView">
                        <i class="bi bi-list me-1"></i>リスト表示
                    </label>
                    
                    <input type="radio" class="btn-check" name="viewMode" id="cardView" autocomplete="off">
                    <label class="btn btn-outline-primary" for="cardView">
                        <i class="bi bi-grid me-1"></i>カード表示
                    </label>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">
                        <span id="totalCount">0</span>件中 
                        <span id="displayCount">0</span>件表示
                    </small>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-1"></i>
                            表示設定
                        </button>
                        <ul class="dropdown-menu">
                            <li><h6 class="dropdown-header">表示件数</h6></li>
                            <li><a class="dropdown-item" href="#" data-per-page="12">12件</a></li>
                            <li><a class="dropdown-item active" href="#" data-per-page="24">24件</a></li>
                            <li><a class="dropdown-item" href="#" data-per-page="48">48件</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- リスト表示 -->
    <div id="listViewContent" class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="projectsTable">
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="name">
                                        案件名
                                        <i class="bi bi-chevron-expand text-muted"></i>
                                    </th>
                                    <th class="sortable" data-sort="customer">
                                        顧客
                                        <i class="bi bi-chevron-expand text-muted"></i>
                                    </th>
                                    <th class="sortable" data-sort="status">
                                        ステータス
                                        <i class="bi bi-chevron-expand text-muted"></i>
                                    </th>
                                    <th>進捗</th>
                                    <th class="sortable" data-sort="priority">
                                        優先度
                                        <i class="bi bi-chevron-expand text-muted"></i>
                                    </th>
                                    <th class="sortable" data-sort="start_date">
                                        開始日
                                        <i class="bi bi-chevron-expand text-muted"></i>
                                    </th>
                                    <th class="sortable" data-sort="end_date">
                                        終了予定
                                        <i class="bi bi-chevron-expand text-muted"></i>
                                    </th>
                                    <th>アクション</th>
                                </tr>
                            </thead>
                            <tbody id="projectsTableBody">
                                <!-- データはJavaScriptで動的に追加 -->
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">読み込み中...</span>
                                        </div>
                                        <div class="mt-2 text-muted">データを読み込んでいます...</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <nav aria-label="案件一覧ページネーション">
                        <ul class="pagination justify-content-center mb-0" id="pagination">
                            <!-- ページネーションはJavaScriptで動的に生成 -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- カード表示 -->
    <div id="cardViewContent" class="row d-none">
        <div id="projectsCardContainer">
            <!-- カードはJavaScriptで動的に生成 -->
        </div>
    </div>
</div>

<!-- 案件詳細モーダル -->
<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-briefcase me-2"></i>
                    案件詳細
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="projectModalBody">
                <!-- 詳細情報はJavaScriptで動的に設定 -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <a href="#" class="btn btn-primary" id="editProjectBtn">
                    <i class="bi bi-pencil me-1"></i>
                    編集
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .sortable {
        cursor: pointer;
        user-select: none;
        position: relative;
    }
    
    .sortable:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .sortable.asc .bi-chevron-expand::before {
        content: "\f143"; /* bi-chevron-up */
    }
    
    .sortable.desc .bi-chevron-expand::before {
        content: "\f148"; /* bi-chevron-down */
    }
    
    .project-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 12px;
    }
    
    .project-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border-color: #3b82f6;
    }
    
    .progress {
        height: 8px;
    }
    
    .priority-high {
        border-left: 4px solid #ef4444;
    }
    
    .priority-normal {
        border-left: 4px solid #f59e0b;
    }
    
    .priority-low {
        border-left: 4px solid #22c55e;
    }
    
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(59, 130, 246, 0.05);
    }
    
    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .search-input {
        transition: all 0.3s ease;
    }
    
    .search-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .project-card {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let currentPage = 1;
    let perPage = 24;
    let sortField = 'name';
    let sortOrder = 'asc';
    let searchParams = {};
    let viewMode = 'list';
    
    $(document).ready(function() {
        // 初期データ読み込み
        loadProjects();
        loadProjectStats();
        
        // 検索フォーム送信
        $('#searchForm').on('submit', function(e) {
            e.preventDefault();
            performSearch();
        });
        
        // リアルタイム検索
        $('.search-input').on('input', debounce(function() {
            performSearch();
        }, 300));
        
        // フィルター変更
        $('#searchForm select').on('change', function() {
            performSearch();
        });
        
        // 検索リセット
        $('#searchForm button[type="reset"]').on('click', function() {
            searchParams = {};
            currentPage = 1;
            loadProjects();
        });
        
        // ソート
        $('.sortable').on('click', function() {
            const field = $(this).data('sort');
            if (sortField === field) {
                sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                sortField = field;
                sortOrder = 'asc';
            }
            
            $('.sortable').removeClass('asc desc');
            $(this).addClass(sortOrder);
            
            loadProjects();
        });
        
        // 表示形式切り替え
        $('input[name="viewMode"]').on('change', function() {
            viewMode = $(this).attr('id') === 'listView' ? 'list' : 'card';
            toggleViewMode();
        });
        
        // 表示件数変更
        $('[data-per-page]').on('click', function(e) {
            e.preventDefault();
            perPage = parseInt($(this).data('per-page'));
            currentPage = 1;
            
            $('[data-per-page]').removeClass('active');
            $(this).addClass('active');
            
            loadProjects();
        });
        
        // エクスポート
        $('#exportProjects').on('click', function() {
            exportProjects();
        });
        
        // フィルター
        $('[data-filter]').on('click', function(e) {
            e.preventDefault();
            const filter = $(this).data('filter');
            applyQuickFilter(filter);
        });
    });
    
    // 案件データ読み込み
    function loadProjects() {
        const params = {
            page: currentPage,
            per_page: perPage,
            sort: sortField,
            order: sortOrder,
            ...searchParams
        };
        
        showLoading();
        
        $.get('{{ route("projects.search") }}', params)
            .done(function(response) {
                if (viewMode === 'list') {
                    renderProjectsTable(response.data);
                } else {
                    renderProjectsCards(response.data);
                }
                renderPagination(response.pagination);
                updateDisplayCount(response.pagination);
            })
            .fail(function() {
                showError('案件データの読み込みに失敗しました');
            });
    }
    
    // 案件統計読み込み
    function loadProjectStats() {
        $.get('{{ route("projects.stats") }}')
            .done(function(stats) {
                $('#totalProjects').text(stats.total.toLocaleString());
                $('#activeProjects').text(stats.active.toLocaleString());
                $('#completedProjects').text(stats.completed.toLocaleString());
                $('#avgProgress').text(stats.avgProgress + '%');
            })
            .fail(function() {
                console.error('統計データの読み込みに失敗しました');
            });
    }
    
    // 検索実行
    function performSearch() {
        const formData = new FormData(document.getElementById('searchForm'));
        searchParams = {};
        
        for (let [key, value] of formData.entries()) {
            if (value.trim() !== '') {
                searchParams[key] = value;
            }
        }
        
        currentPage = 1;
        loadProjects();
    }
    
    // テーブル描画
    function renderProjectsTable(projects) {
        if (projects.length === 0) {
            $('#projectsTableBody').html(`
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="bi bi-inbox display-4 text-muted"></i>
                        <div class="mt-2 text-muted">該当する案件が見つかりません</div>
                    </td>
                </tr>
            `);
            return;
        }
        
        const html = projects.map(project => `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="fw-bold">${project.name}</div>
                            <small class="text-muted">${project.description || ''}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div>${project.customer_name}</div>
                    <small class="text-muted">${project.customer_contact || ''}</small>
                </td>
                <td>
                    <span class="badge status-badge ${getStatusClass(project.status)}">
                        ${getStatusText(project.status)}
                    </span>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="progress flex-grow-1 me-2" style="width: 80px;">
                            <div class="progress-bar" style="width: ${project.progress}%"></div>
                        </div>
                        <small>${project.progress}%</small>
                    </div>
                </td>
                <td>
                    <span class="badge ${getPriorityClass(project.priority)}">
                        ${getPriorityText(project.priority)}
                    </span>
                </td>
                <td>
                    <div>${formatDate(project.start_date)}</div>
                </td>
                <td>
                    <div>${formatDate(project.end_date)}</div>
                    ${isOverdue(project.end_date) ? '<small class="text-danger">期限超過</small>' : ''}
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewProject(${project.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        <a href="{{ route('projects.edit', '') }}/${project.id}" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteProject(${project.id}, '${project.name}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        
        $('#projectsTableBody').html(html);
    }
    
    // カード描画
    function renderProjectsCards(projects) {
        if (projects.length === 0) {
            $('#projectsCardContainer').html(`
                <div class="col-12 text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <div class="mt-2 text-muted">該当する案件が見つかりません</div>
                </div>
            `);
            return;
        }
        
        const html = projects.map(project => `
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card project-card h-100 priority-${project.priority}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="card-title mb-0">${project.name}</h6>
                            <span class="badge ${getStatusClass(project.status)}">
                                ${getStatusText(project.status)}
                            </span>
                        </div>
                        
                        <p class="card-text text-muted small mb-3">${project.description || '説明なし'}</p>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">進捗</small>
                                <small class="fw-bold">${project.progress}%</small>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: ${project.progress}%"></div>
                            </div>
                        </div>
                        
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <small class="text-muted">開始日</small>
                                <div class="fw-bold small">${formatDate(project.start_date)}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">終了予定</small>
                                <div class="fw-bold small ${isOverdue(project.end_date) ? 'text-danger' : ''}">${formatDate(project.end_date)}</div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-building me-1"></i>
                                ${project.customer_name}
                            </small>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="viewProject(${project.id})">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="{{ route('projects.edit', '') }}/${project.id}" class="btn btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        
        $('#projectsCardContainer').html(html);
    }
    
    // 表示形式切り替え
    function toggleViewMode() {
        if (viewMode === 'list') {
            $('#listViewContent').removeClass('d-none');
            $('#cardViewContent').addClass('d-none');
        } else {
            $('#listViewContent').addClass('d-none');
            $('#cardViewContent').removeClass('d-none');
        }
        loadProjects();
    }
    
    // ページネーション描画
    function renderPagination(pagination) {
        if (pagination.last_page <= 1) {
            $('#pagination').empty();
            return;
        }
        
        let html = '';
        
        if (pagination.current_page > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${pagination.current_page - 1})">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>`;
        }
        
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
            </li>`;
        }
        
        if (pagination.current_page < pagination.last_page) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${pagination.current_page + 1})">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>`;
        }
        
        $('#pagination').html(html);
    }
    
    // 表示件数更新
    function updateDisplayCount(pagination) {
        $('#totalCount').text(pagination.total);
        $('#displayCount').text(pagination.to - pagination.from + 1);
    }
    
    // ページ変更
    function changePage(page) {
        currentPage = page;
        loadProjects();
    }
    
    // 案件詳細表示
    function viewProject(projectId) {
        $.get(`{{ route('projects.show', '') }}/${projectId}`)
            .done(function(project) {
                renderProjectModal(project);
                $('#projectModal').modal('show');
                $('#editProjectBtn').attr('href', `{{ route('projects.edit', '') }}/${projectId}`);
            })
            .fail(function() {
                showErrorMessage('案件詳細の読み込みに失敗しました');
            });
    }
    
    // 案件詳細モーダル描画
    function renderProjectModal(project) {
        const html = `
            <div class="row">
                <div class="col-md-8">
                    <h6 class="text-muted mb-2">基本情報</h6>
                    <table class="table table-sm">
                        <tr><th>案件名:</th><td>${project.name}</td></tr>
                        <tr><th>顧客:</th><td>${project.customer_name}</td></tr>
                        <tr><th>ステータス:</th><td><span class="badge ${getStatusClass(project.status)}">${getStatusText(project.status)}</span></td></tr>
                        <tr><th>優先度:</th><td><span class="badge ${getPriorityClass(project.priority)}">${getPriorityText(project.priority)}</span></td></tr>
                        <tr><th>進捗:</th><td>${project.progress}%</td></tr>
                    </table>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted mb-2">スケジュール</h6>
                    <table class="table table-sm">
                        <tr><th>開始日:</th><td>${formatDate(project.start_date)}</td></tr>
                        <tr><th>終了予定:</th><td>${formatDate(project.end_date)}</td></tr>
                        <tr><th>期間:</th><td>${calculateDuration(project.start_date, project.end_date)}日</td></tr>
                    </table>
                </div>
            </div>
            ${project.description ? `
                <div class="mt-3">
                    <h6 class="text-muted mb-2">説明</h6>
                    <p class="mb-0">${project.description}</p>
                </div>
            ` : ''}
        `;
        
        $('#projectModalBody').html(html);
    }
    
    // 案件削除
    function deleteProject(projectId, projectName) {
        if (confirm(`案件「${projectName}」を削除しますか？\nこの操作は取り消すことができません。`)) {
            $.ajax({
                url: `{{ route('projects.destroy', '') }}/${projectId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function() {
                showSuccessMessage('案件を削除しました');
                loadProjects();
                loadProjectStats();
            })
            .fail(function() {
                showErrorMessage('案件の削除に失敗しました');
            });
        }
    }
    
    // クイックフィルター適用
    function applyQuickFilter(filter) {
        searchParams = {};
        
        if (filter !== 'all') {
            searchParams.status = filter;
        }
        
        // フォームをリセット
        $('#searchForm')[0].reset();
        if (filter !== 'all') {
            $('select[name="status"]').val(filter);
        }
        
        currentPage = 1;
        loadProjects();
    }
    
    // エクスポート
    function exportProjects() {
        const params = new URLSearchParams({
            ...searchParams,
            sort: sortField,
            order: sortOrder
        });
        
        window.open(`/projects/export?${params.toString()}`, '_blank');
    }
    
    // ローディング表示
    function showLoading() {
        if (viewMode === 'list') {
            $('#projectsTableBody').html(`
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">読み込み中...</span>
                        </div>
                        <div class="mt-2 text-muted">データを読み込んでいます...</div>
                    </td>
                </tr>
            `);
        } else {
            $('#projectsCardContainer').html(`
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">読み込み中...</span>
                    </div>
                    <div class="mt-2 text-muted">データを読み込んでいます...</div>
                </div>
            `);
        }
    }
    
    // ユーティリティ関数
    function getStatusClass(status) {
        const classes = {
            'planning': 'bg-info',
            'active': 'bg-success',
            'on_hold': 'bg-warning',
            'completed': 'bg-secondary',
            'cancelled': 'bg-danger'
        };
        return classes[status] || 'bg-secondary';
    }
    
    function getStatusText(status) {
        const texts = {
            'planning': '計画中',
            'active': '実行中',
            'on_hold': '保留中',
            'completed': '完了',
            'cancelled': 'キャンセル'
        };
        return texts[status] || status;
    }
    
    function getPriorityClass(priority) {
        const classes = {
            'high': 'bg-danger',
            'normal': 'bg-warning',
            'low': 'bg-success'
        };
        return classes[priority] || 'bg-secondary';
    }
    
    function getPriorityText(priority) {
        const texts = {
            'high': '高',
            'normal': '標準',
            'low': '低'
        };
        return texts[priority] || priority;
    }
    
    function formatDate(date) {
        return new Date(date).toLocaleDateString('ja-JP');
    }
    
    function isOverdue(endDate) {
        return new Date(endDate) < new Date();
    }
    
    function calculateDuration(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        return Math.ceil((end - start) / (1000 * 60 * 60 * 24));
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
</script>
@endpush
@endsection
