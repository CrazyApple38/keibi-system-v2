<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '警備システム') - 統合管理システム</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-bg: #f8fafc;
            --border-color: #e5e7eb;
        }
        
        body {
            font-family: 'Noto Sans JP', 'Hiragino Sans', 'ヒラギノ角ゴシック', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-color);
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(30, 58, 138, 0.15);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            color: white !important;
        }
        
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem !important;
            border-radius: 6px;
            margin: 0 0.2rem;
        }
        
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            margin-top: 0.5rem;
        }
        
        .dropdown-item {
            padding: 0.7rem 1.2rem;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: var(--light-bg);
            transform: translateX(3px);
        }
        
        .main-content {
            min-height: calc(100vh - 200px);
            padding: 2rem 0;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.2rem 1.5rem;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(30, 58, 138, 0.3);
        }
        
        .btn-secondary {
            background-color: #6b7280;
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-success {
            background-color: var(--success-color);
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
        }
        
        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 0.7rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        
        .table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        
        .table thead th {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: 1rem;
        }
        
        .table tbody td {
            padding: 1rem;
            border-color: var(--border-color);
            vertical-align: middle;
        }
        
        .alert {
            border: none;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #065f46;
            border-left: 4px solid var(--success-color);
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border-left: 4px solid var(--danger-color);
        }
        
        .alert-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #92400e;
            border-left: 4px solid var(--warning-color);
        }
        
        .alert-info {
            background-color: rgba(59, 130, 246, 0.1);
            color: #1e40af;
            border-left: 4px solid var(--secondary-color);
        }
        
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin-bottom: 2rem;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: var(--secondary-color);
            font-weight: bold;
        }
        
        .breadcrumb-item a {
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .breadcrumb-item a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb-item.active {
            color: var(--dark-color);
            font-weight: 500;
        }
        
        .footer {
            background: linear-gradient(135deg, var(--dark-color) 0%, #374151 100%);
            color: rgba(255, 255, 255, 0.8);
            padding: 2rem 0;
            margin-top: 3rem;
        }
        
        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer a:hover {
            color: white;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        @media (max-width: 768px) {
            .navbar-nav {
                margin-top: 1rem;
            }
            
            .main-content {
                padding: 1rem 0;
            }
            
            .card {
                margin-bottom: 1rem;
            }
        }
        
        /* カスタムスクロールバー */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- ナビゲーションバー -->
    @include('partials.navigation')
    
    <!-- メインコンテンツ -->
    <main class="main-content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>
    
    <!-- フッター -->
    @include('partials.footer')
    
    <!-- Bootstrap 5 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- 共通JavaScript -->
    <script>
        // CSRF トークン設定
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // 共通関数：ローディング表示
        function showLoading(element) {
            $(element).addClass('loading').prop('disabled', true);
            $(element).find('.spinner-border').removeClass('d-none');
        }
        
        // 共通関数：ローディング非表示
        function hideLoading(element) {
            $(element).removeClass('loading').prop('disabled', false);
            $(element).find('.spinner-border').addClass('d-none');
        }
        
        // 共通関数：成功メッセージ表示
        function showSuccessMessage(message) {
            const alert = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            $('.main-content .container-fluid').prepend(alert);
            
            // 3秒後に自動的に閉じる
            setTimeout(() => {
                $('.alert-success').alert('close');
            }, 3000);
        }
        
        // 共通関数：エラーメッセージ表示
        function showErrorMessage(message) {
            const alert = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            $('.main-content .container-fluid').prepend(alert);
            
            // 5秒後に自動的に閉じる
            setTimeout(() => {
                $('.alert-danger').alert('close');
            }, 5000);
        }
        
        // 共通関数：確認ダイアログ
        function confirmAction(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }
        
        // フォーム送信時の重複防止
        $('form').on('submit', function() {
            const submitBtn = $(this).find('button[type="submit"]');
            showLoading(submitBtn);
        });
        
        // テーブルソート機能
        $('.table-sortable th[data-sort]').click(function() {
            const column = $(this).data('sort');
            const order = $(this).hasClass('asc') ? 'desc' : 'asc';
            
            // ソート状態をリセット
            $('.table-sortable th').removeClass('asc desc');
            $(this).addClass(order);
            
            // ソート実行（この部分は各ページで実装）
            if (typeof sortTable === 'function') {
                sortTable(column, order);
            }
        });
        
        // 検索フォームの遅延実行
        let searchTimeout;
        $('.search-input').on('input', function() {
            clearTimeout(searchTimeout);
            const query = $(this).val();
            
            searchTimeout = setTimeout(() => {
                if (typeof performSearch === 'function') {
                    performSearch(query);
                }
            }, 300);
        });
        
        // 自動保存機能
        $('.auto-save').on('input change', function() {
            clearTimeout(window.autoSaveTimeout);
            window.autoSaveTimeout = setTimeout(() => {
                if (typeof autoSave === 'function') {
                    autoSave();
                }
            }, 2000);
        });
        
        // ページ読み込み完了時
        $(document).ready(function() {
            // ツールチップ初期化
            $('[data-bs-toggle="tooltip"]').tooltip();
            
            // ポップオーバー初期化
            $('[data-bs-toggle="popover"]').popover();
            
            // アラートの自動フェードアウト
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
    
    @stack('scripts')
</body>
</html>
