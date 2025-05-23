@extends('layouts.app')

@section('title', '顧客一覧')

@section('content')
<div class="container-fluid">
    <!-- パンくずリスト -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
            <li class="breadcrumb-item active" aria-current="page">顧客一覧</li>
        </ol>
    </nav>
    
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-people me-2"></i>
                        顧客一覧
                    </h2>
                    <p class="text-muted mb-0">顧客情報の管理・検索</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" id="exportCustomers">
                        <i class="bi bi-download me-1"></i>
                        エクスポート
                    </button>
                    <a href="{{ route('customers.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>
                        新規顧客登録
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
                            <h6 class="text-muted mb-1">総顧客数</h6>
                            <h3 class="mb-0" id="totalCustomers">-</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-people fs-4 text-primary"></i>
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
                            <h6 class="text-muted mb-1">アクティブ顧客</h6>
                            <h3 class="mb-0" id="activeCustomers">-</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-person-check fs-4 text-success"></i>
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
                            <h6 class="text-muted mb-1">今月新規</h6>
                            <h3 class="mb-0" id="newCustomers">-</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-person-plus fs-4 text-info"></i>
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
                            <h6 class="text-muted mb-1">平均契約金額</h6>
                            <h3 class="mb-0" id="avgContractValue">-</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-currency-yen fs-4 text-warning"></i>
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
                                   placeholder="顧客名、住所、電話番号など">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">ステータス</label>
                            <select class="form-select" name="status">
                                <option value="">すべて</option>
                                <option value="active">アクティブ</option>
                                <option value="inactive">非アクティブ</option>
                                <option value="pending">保留中</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">顧客種別</label>
                            <select class="form-select" name="type">
                                <option value="">すべて</option>
                                <option value="corporate">法人</option>
                                <option value="individual">個人</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">地域</label>
                            <select class="form-select" name="region">
                                <option value="">すべて</option>
                                <option value="tokyo">東京都</option>
                                <option value="kanagawa">神奈川県</option>
                                <option value="chiba">千葉県</option>
                                <option value="saitama">埼玉県</option>
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
    
    <!-- 顧客一覧テーブル -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">顧客一覧</h5>
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
                                <li><a class="dropdown-item" href="#" data-per-page="10">10件</a></li>
                                <li><a class="dropdown-item active" href="#" data-per-page="25">25件</a></li>
                                <li><a class="dropdown-item" href="#" data-per-page="50">50件</a></li>
                                <li><a class="dropdown-item" href="#" data-per-page="100">100件</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="customersTable">
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="name">
                                        顧客名
                                        <i class="bi bi-chevron-expand text-muted"></i>
                                    </th>
                                    <th class="sortable" data-sort="type">
                                        種別
                                        <i class="bi bi-chevron-expand text-muted"></i>
                                    </th>
                                    <th>連絡先</th>
                                    <th>住所</th>
                                    <th class="sortable" data-sort="status">
                                        ステータス
                                        <i class="bi bi-chevron-expand text-muted"></i>
                                    </th>
                                    <th class="sortable" data-sort="created_at">
                                        登録日
                                        <i class="bi bi-chevron-expand text-muted"></i>
                                    </th>
                                    <th>アクション</th>
                                </tr>
                            </thead>
                            <tbody id="customersTableBody">
                                <!-- データはJavaScriptで動的に追加 -->
                                <tr>
                                    <td colspan="7" class="text-center py-4">
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
                    <nav aria-label="顧客一覧ページネーション">
                        <ul class="pagination justify-content-center mb-0" id="pagination">
                            <!-- ページネーションはJavaScriptで動的に生成 -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 顧客詳細モーダル -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-lines-fill me-2"></i>
                    顧客詳細
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="customerModalBody">
                <!-- 詳細情報はJavaScriptで動的に設定 -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <a href="#" class="btn btn-primary" id="editCustomerBtn">
                    <i class="bi bi-pencil me-1"></i>
                    編集
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 削除確認モーダル -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    削除確認
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>本当にこの顧客を削除しますか？</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    この操作は取り消すことができません。関連する案件やデータも同時に削除される可能性があります。
                </div>
                <div id="deleteCustomerInfo"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-1"></i>
                    削除する
                </button>
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
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .customer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        margin-right: 10px;
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
    }
</style>
@endpush

@push('scripts')
<script>
    let currentPage = 1;
    let perPage = 25;
    let sortField = 'name';
    let sortOrder = 'asc';
    let searchParams = {};
    
    $(document).ready(function() {
        // 初期データ読み込み
        loadCustomers();
        loadCustomerStats();
        
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
            loadCustomers();
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
            
            // ソート状態を表示に反映
            $('.sortable').removeClass('asc desc');
            $(this).addClass(sortOrder);
            
            loadCustomers();
        });
        
        // 表示件数変更
        $('[data-per-page]').on('click', function(e) {
            e.preventDefault();
            perPage = parseInt($(this).data('per-page'));
            currentPage = 1;
            
            // アクティブ状態更新
            $('[data-per-page]').removeClass('active');
            $(this).addClass('active');
            
            loadCustomers();
        });
        
        // エクスポート
        $('#exportCustomers').on('click', function() {
            exportCustomers();
        });
    });
    
    // 顧客データ読み込み
    function loadCustomers() {
        const params = {
            page: currentPage,
            per_page: perPage,
            sort: sortField,
            order: sortOrder,
            ...searchParams
        };
        
        showLoading('#customersTableBody');
        
        $.get('{{ route("customers.search") }}', params)
            .done(function(response) {
                renderCustomersTable(response.data);
                renderPagination(response.pagination);
                updateDisplayCount(response.pagination);
            })
            .fail(function() {
                showError('顧客データの読み込みに失敗しました');
            });
    }
    
    // 顧客統計読み込み
    function loadCustomerStats() {
        $.get('{{ route("customers.stats") }}')
            .done(function(stats) {
                $('#totalCustomers').text(stats.total.toLocaleString());
                $('#activeCustomers').text(stats.active.toLocaleString());
                $('#newCustomers').text(stats.new.toLocaleString());
                $('#avgContractValue').text('¥' + stats.avgContract.toLocaleString());
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
        loadCustomers();
    }
    
    // テーブル描画
    function renderCustomersTable(customers) {
        if (customers.length === 0) {
            $('#customersTableBody').html(`
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="bi bi-inbox display-4 text-muted"></i>
                        <div class="mt-2 text-muted">該当する顧客が見つかりません</div>
                    </td>
                </tr>
            `);
            return;
        }
        
        const html = customers.map(customer => `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="customer-avatar" style="background-color: ${getAvatarColor(customer.name)}">
                            ${customer.name.charAt(0)}
                        </div>
                        <div>
                            <div class="fw-bold">${customer.name}</div>
                            <small class="text-muted">${customer.contact_person || ''}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge ${customer.type === 'corporate' ? 'bg-primary' : 'bg-info'}">
                        ${customer.type === 'corporate' ? '法人' : '個人'}
                    </span>
                </td>
                <td>
                    <div>${customer.phone || '-'}</div>
                    <small class="text-muted">${customer.email || ''}</small>
                </td>
                <td>
                    <div>${customer.address || '-'}</div>
                </td>
                <td>
                    <span class="badge status-badge ${getStatusClass(customer.status)}">
                        ${getStatusText(customer.status)}
                    </span>
                </td>
                <td>
                    <div>${formatDate(customer.created_at)}</div>
                    <small class="text-muted">${formatDateFromNow(customer.created_at)}</small>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewCustomer(${customer.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        <a href="{{ route('customers.edit', '') }}/${customer.id}" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCustomer(${customer.id}, '${customer.name}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        
        $('#customersTableBody').html(html);
    }
    
    // ページネーション描画
    function renderPagination(pagination) {
        if (pagination.last_page <= 1) {
            $('#pagination').empty();
            return;
        }
        
        let html = '';
        
        // 前へボタン
        if (pagination.current_page > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${pagination.current_page - 1})">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>`;
        }
        
        // ページ番号
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
        
        if (startPage > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="changePage(1)">1</a>
            </li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
            </li>`;
        }
        
        if (endPage < pagination.last_page) {
            if (endPage < pagination.last_page - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${pagination.last_page})">${pagination.last_page}</a>
            </li>`;
        }
        
        // 次へボタン
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
        loadCustomers();
    }
    
    // 顧客詳細表示
    function viewCustomer(customerId) {
        $.get(`{{ route('customers.show', '') }}/${customerId}`)
            .done(function(customer) {
                renderCustomerModal(customer);
                $('#customerModal').modal('show');
                $('#editCustomerBtn').attr('href', `{{ route('customers.edit', '') }}/${customerId}`);
            })
            .fail(function() {
                showErrorMessage('顧客詳細の読み込みに失敗しました');
            });
    }
    
    // 顧客詳細モーダル描画
    function renderCustomerModal(customer) {
        const html = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">基本情報</h6>
                    <table class="table table-sm">
                        <tr><th>顧客名:</th><td>${customer.name}</td></tr>
                        <tr><th>顧客種別:</th><td>${customer.type === 'corporate' ? '法人' : '個人'}</td></tr>
                        <tr><th>担当者:</th><td>${customer.contact_person || '-'}</td></tr>
                        <tr><th>ステータス:</th><td><span class="badge ${getStatusClass(customer.status)}">${getStatusText(customer.status)}</span></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">連絡先</h6>
                    <table class="table table-sm">
                        <tr><th>電話番号:</th><td>${customer.phone || '-'}</td></tr>
                        <tr><th>メール:</th><td>${customer.email || '-'}</td></tr>
                        <tr><th>住所:</th><td>${customer.address || '-'}</td></tr>
                        <tr><th>登録日:</th><td>${formatDate(customer.created_at)}</td></tr>
                    </table>
                </div>
            </div>
            ${customer.notes ? `
                <div class="mt-3">
                    <h6 class="text-muted mb-2">備考</h6>
                    <p class="mb-0">${customer.notes}</p>
                </div>
            ` : ''}
        `;
        
        $('#customerModalBody').html(html);
    }
    
    // 顧客削除
    function deleteCustomer(customerId, customerName) {
        $('#deleteCustomerInfo').html(`
            <strong>顧客名:</strong> ${customerName}
        `);
        
        $('#confirmDeleteBtn').off('click').on('click', function() {
            $.ajax({
                url: `{{ route('customers.destroy', '') }}/${customerId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function() {
                $('#deleteModal').modal('hide');
                showSuccessMessage('顧客を削除しました');
                loadCustomers();
                loadCustomerStats();
            })
            .fail(function() {
                showErrorMessage('顧客の削除に失敗しました');
            });
        });
        
        $('#deleteModal').modal('show');
    }
    
    // エクスポート
    function exportCustomers() {
        const params = new URLSearchParams({
            ...searchParams,
            sort: sortField,
            order: sortOrder
        });
        
        window.open(`/customers/export?${params.toString()}`, '_blank');
    }
    
    // ローディング表示
    function showLoading(selector) {
        $(selector).html(`
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">読み込み中...</span>
                    </div>
                    <div class="mt-2 text-muted">データを読み込んでいます...</div>
                </td>
            </tr>
        `);
    }
    
    // ユーティリティ関数
    function getAvatarColor(name) {
        const colors = ['#3b82f6', '#ef4444', '#22c55e', '#f59e0b', '#8b5cf6', '#06b6d4'];
        const hash = name.split('').reduce((a, b) => {
            a = ((a << 5) - a) + b.charCodeAt(0);
            return a & a;
        }, 0);
        return colors[Math.abs(hash) % colors.length];
    }
    
    function getStatusClass(status) {
        const classes = {
            'active': 'bg-success',
            'inactive': 'bg-secondary',
            'pending': 'bg-warning'
        };
        return classes[status] || 'bg-secondary';
    }
    
    function getStatusText(status) {
        const texts = {
            'active': 'アクティブ',
            'inactive': '非アクティブ',
            'pending': '保留中'
        };
        return texts[status] || status;
    }
    
    function formatDate(date) {
        return new Date(date).toLocaleDateString('ja-JP');
    }
    
    function formatDateFromNow(date) {
        const diff = Date.now() - new Date(date).getTime();
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (days === 0) return '今日';
        if (days === 1) return '昨日';
        if (days < 7) return `${days}日前`;
        if (days < 30) return `${Math.floor(days / 7)}週間前`;
        if (days < 365) return `${Math.floor(days / 30)}ヶ月前`;
        return `${Math.floor(days / 365)}年前`;
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
