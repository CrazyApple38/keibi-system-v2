<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <!-- ブランドロゴ -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard.index') }}">
            <i class="bi bi-shield-check me-2"></i>
            警備システム
        </a>
        
        <!-- モバイル用ハンバーガーメニュー -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- ナビゲーションメニュー -->
        <div class="collapse navbar-collapse" id="navbarNav">
            @auth
                <ul class="navbar-nav me-auto">
                    <!-- ダッシュボード -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}" 
                           href="{{ route('dashboard.index') }}">
                            <i class="bi bi-speedometer2 me-1"></i>
                            ダッシュボード
                        </a>
                    </li>
                    
                    <!-- 顧客管理 -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('customers.*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-people me-1"></i>
                            顧客管理
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('customers.index') }}">
                                <i class="bi bi-list me-2"></i>顧客一覧
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('customers.create') }}">
                                <i class="bi bi-plus-circle me-2"></i>顧客登録
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('customers.stats') }}">
                                <i class="bi bi-graph-up me-2"></i>顧客統計
                            </a></li>
                        </ul>
                    </li>
                    
                    <!-- 案件管理 -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('projects.*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-briefcase me-1"></i>
                            案件管理
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('projects.index') }}">
                                <i class="bi bi-list me-2"></i>案件一覧
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('projects.create') }}">
                                <i class="bi bi-plus-circle me-2"></i>案件登録
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('projects.stats') }}">
                                <i class="bi bi-graph-up me-2"></i>案件統計
                            </a></li>
                        </ul>
                    </li>
                    
                    <!-- 警備員管理 -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('guards.*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-badge me-1"></i>
                            警備員管理
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('guards.index') }}">
                                <i class="bi bi-list me-2"></i>警備員一覧
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('guards.create') }}">
                                <i class="bi bi-plus-circle me-2"></i>警備員登録
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('guards.stats') }}">
                                <i class="bi bi-graph-up me-2"></i>警備員統計
                            </a></li>
                        </ul>
                    </li>
                    
                    <!-- シフト管理 -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('shifts.*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-calendar3 me-1"></i>
                            シフト管理
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('shifts.index') }}">
                                <i class="bi bi-list me-2"></i>シフト一覧
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('shifts.create') }}">
                                <i class="bi bi-plus-circle me-2"></i>シフト作成
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('shifts.calendar') }}">
                                <i class="bi bi-calendar-month me-2"></i>カレンダー表示
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('shifts.stats') }}">
                                <i class="bi bi-graph-up me-2"></i>シフト統計
                            </a></li>
                        </ul>
                    </li>
                    
                    <!-- 業務管理 -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs(['attendances.*', 'daily_reports.*']) ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-clipboard-check me-1"></i>
                            業務管理
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('attendances.index') }}">
                                <i class="bi bi-clock me-2"></i>勤怠管理
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('daily_reports.index') }}">
                                <i class="bi bi-journal-text me-2"></i>日報管理
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('attendances.stats') }}">
                                <i class="bi bi-graph-up me-2"></i>勤怠統計
                            </a></li>
                        </ul>
                    </li>
                    
                    <!-- 売上管理 -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs(['quotations.*', 'contracts.*', 'invoices.*']) ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-currency-yen me-1"></i>
                            売上管理
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('quotations.index') }}">
                                <i class="bi bi-file-text me-2"></i>見積管理
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('contracts.index') }}">
                                <i class="bi bi-file-earmark-text me-2"></i>契約管理
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('invoices.index') }}">
                                <i class="bi bi-receipt me-2"></i>請求管理
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('invoices.reports.revenue') }}">
                                <i class="bi bi-graph-up me-2"></i>売上レポート
                            </a></li>
                        </ul>
                    </li>
                </ul>
                
                <!-- 右側メニュー -->
                <ul class="navbar-nav">
                    <!-- 通知 -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle position-relative" href="#" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                                  style="font-size: 0.6rem;" id="notification-count">
                                0
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;" id="notification-dropdown">
                            <li><h6 class="dropdown-header">通知</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li id="notification-list">
                                <div class="dropdown-item-text text-muted text-center py-3">
                                    新しい通知はありません
                                </div>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- ユーザーメニュー -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-2"></i>
                            {{ Auth::user()->name ?? 'ユーザー' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">
                                <i class="bi bi-person me-2"></i>プロフィール
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('auth.password.change.form') }}">
                                <i class="bi bi-key me-2"></i>パスワード変更
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('auth.logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i>ログアウト
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            @else
                <!-- 未認証時のメニュー -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('auth.login.form') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            ログイン
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('auth.register.form') }}">
                            <i class="bi bi-person-plus me-1"></i>
                            ユーザー登録
                        </a>
                    </li>
                </ul>
            @endauth
        </div>
    </div>
</nav>

@auth
<!-- 通知取得用JavaScript -->
<script>
    // 通知を取得する関数
    function loadNotifications() {
        $.get('{{ route("dashboard.alerts") }}')
            .done(function(data) {
                updateNotificationDisplay(data);
            })
            .fail(function() {
                console.error('通知の取得に失敗しました');
            });
    }
    
    // 通知表示を更新する関数
    function updateNotificationDisplay(notifications) {
        const count = notifications.length;
        const countBadge = $('#notification-count');
        const notificationList = $('#notification-list');
        
        // カウント表示更新
        if (count > 0) {
            countBadge.text(count > 99 ? '99+' : count).show();
        } else {
            countBadge.hide();
        }
        
        // 通知リスト更新
        if (count === 0) {
            notificationList.html(`
                <div class="dropdown-item-text text-muted text-center py-3">
                    新しい通知はありません
                </div>
            `);
        } else {
            let html = '';
            notifications.slice(0, 5).forEach(function(notification) {
                const timeAgo = moment(notification.created_at).fromNow();
                html += `
                    <li>
                        <a class="dropdown-item py-2 ${notification.read_at ? '' : 'bg-light'}" 
                           href="#" onclick="markAsRead('${notification.id}')">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-2">
                                    <i class="bi ${getNotificationIcon(notification.type)} text-${getNotificationColor(notification.type)}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">${notification.title}</div>
                                    <div class="small text-muted">${notification.message}</div>
                                    <div class="small text-muted">${timeAgo}</div>
                                </div>
                            </div>
                        </a>
                    </li>
                `;
            });
            
            if (count > 5) {
                html += `
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center" href="#">
                        すべての通知を表示 (${count - 5}件の未読)
                    </a></li>
                `;
            }
            
            notificationList.html(html);
        }
    }
    
    // 通知アイコンを取得する関数
    function getNotificationIcon(type) {
        const icons = {
            'info': 'bi-info-circle',
            'warning': 'bi-exclamation-triangle',
            'error': 'bi-x-circle',
            'success': 'bi-check-circle',
            'shift': 'bi-calendar3',
            'attendance': 'bi-clock',
            'contract': 'bi-file-earmark-text',
            'invoice': 'bi-receipt'
        };
        return icons[type] || 'bi-bell';
    }
    
    // 通知カラーを取得する関数
    function getNotificationColor(type) {
        const colors = {
            'info': 'primary',
            'warning': 'warning',
            'error': 'danger',
            'success': 'success',
            'shift': 'info',
            'attendance': 'secondary',
            'contract': 'dark',
            'invoice': 'success'
        };
        return colors[type] || 'primary';
    }
    
    // 通知を既読にする関数
    function markAsRead(notificationId) {
        $.post(`{{ route('dashboard.alerts.dismiss', '') }}/${notificationId}`)
            .done(function() {
                loadNotifications();
            })
            .fail(function() {
                console.error('通知の既読処理に失敗しました');
            });
    }
    
    // ページ読み込み時とタイマーで通知を取得
    $(document).ready(function() {
        loadNotifications();
        
        // 30秒間隔で通知を更新
        setInterval(loadNotifications, 30000);
    });
</script>

<!-- Moment.js (時間表示用) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/ja.min.js"></script>
<script>
    moment.locale('ja');
</script>
@endauth
