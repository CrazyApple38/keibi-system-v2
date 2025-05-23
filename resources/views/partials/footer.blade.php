<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <!-- 会社情報 -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="mb-3">
                    <i class="bi bi-shield-check me-2"></i>
                    警備システム
                </h5>
                <p class="mb-3">
                    警備グループ会社の受注管理・シフト管理を統合する<br>
                    包括的な業務管理システムです。
                </p>
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-building me-2"></i>
                    <span>対象会社：3社統合管理</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-calendar-check me-2"></i>
                    <span>24時間365日対応</span>
                </div>
            </div>
            
            <!-- 主要機能 -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="mb-3">主要機能</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('customers.index') }}" class="text-decoration-none">
                            <i class="bi bi-people me-1"></i>顧客管理
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('projects.index') }}" class="text-decoration-none">
                            <i class="bi bi-briefcase me-1"></i>案件管理
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('guards.index') }}" class="text-decoration-none">
                            <i class="bi bi-person-badge me-1"></i>警備員管理
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('shifts.index') }}" class="text-decoration-none">
                            <i class="bi bi-calendar3 me-1"></i>シフト管理
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- 業務管理 -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="mb-3">業務管理</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('attendances.index') }}" class="text-decoration-none">
                            <i class="bi bi-clock me-1"></i>勤怠管理
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('daily_reports.index') }}" class="text-decoration-none">
                            <i class="bi bi-journal-text me-1"></i>日報管理
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('quotations.index') }}" class="text-decoration-none">
                            <i class="bi bi-file-text me-1"></i>見積管理
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('invoices.index') }}" class="text-decoration-none">
                            <i class="bi bi-receipt me-1"></i>請求管理
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- システム情報 -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="mb-3">システム情報</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="#" class="text-decoration-none">
                            <i class="bi bi-question-circle me-1"></i>ヘルプ
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-decoration-none">
                            <i class="bi bi-book me-1"></i>マニュアル
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-decoration-none">
                            <i class="bi bi-headset me-1"></i>サポート
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-decoration-none">
                            <i class="bi bi-info-circle me-1"></i>システム情報
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- 緊急連絡先・システム状態 -->
            <div class="col-lg-2 col-md-12 mb-4">
                <h6 class="mb-3">システム状態</h6>
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                        <small>システム正常</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                        <small>データベース正常</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                        <small>バックアップ正常</small>
                    </div>
                </div>
                
                <div class="alert alert-dark py-2 px-3" style="font-size: 0.8rem;">
                    <i class="bi bi-telephone me-1"></i>
                    <strong>緊急時連絡先</strong><br>
                    <span class="text-warning">システム障害時：</span><br>
                    080-1234-5678
                </div>
            </div>
        </div>
        
        <hr class="my-4" style="border-color: rgba(255, 255, 255, 0.2);">
        
        <!-- フッター下部 -->
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                <div class="d-flex flex-wrap align-items-center">
                    <span class="me-4">
                        &copy; {{ date('Y') }} 警備グループ統合管理システム
                    </span>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-gear me-1"></i>
                        <small>Laravel {{ app()->version() }} / PHP {{ PHP_VERSION }}</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 col-md-12">
                <div class="d-flex justify-content-lg-end justify-content-start align-items-center">
                    <!-- 対象会社表示 -->
                    <div class="me-4">
                        <small class="text-muted">対象会社：</small>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary me-1" style="font-size: 0.7rem;">東央警備</span>
                            <span class="badge bg-primary me-1" style="font-size: 0.7rem;">Nikkei HD</span>
                            <span class="badge bg-primary" style="font-size: 0.7rem;">全日本EP</span>
                        </div>
                    </div>
                    
                    <!-- 最終更新時刻 -->
                    <div class="text-end">
                        <small class="text-muted">最終更新：</small><br>
                        <small id="last-updated">{{ date('Y-m-d H:i:s') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- フッター用JavaScript -->
<script>
    $(document).ready(function() {
        // 現在時刻を表示する関数
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleString('ja-JP', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            $('#last-updated').text(timeString);
        }
        
        // 初回実行
        updateCurrentTime();
        
        // 30秒間隔で時刻を更新
        setInterval(updateCurrentTime, 30000);
        
        // システム状態チェック（5分間隔）
        function checkSystemStatus() {
            // システム状態をチェックするAPI呼び出し
            // 実際の実装では、ヘルスチェックエンドポイントを作成する
            $.get('/api/health-check')
                .done(function(data) {
                    updateSystemStatus(data);
                })
                .fail(function() {
                    // エラー時は警告状態に
                    updateSystemStatus({
                        system: 'warning',
                        database: 'warning',
                        backup: 'warning'
                    });
                });
        }
        
        // システム状態表示更新
        function updateSystemStatus(status) {
            const statusElements = {
                system: $('.bg-success').eq(0),
                database: $('.bg-success').eq(1),
                backup: $('.bg-success').eq(2)
            };
            
            Object.keys(statusElements).forEach(key => {
                const element = statusElements[key];
                element.removeClass('bg-success bg-warning bg-danger');
                
                switch(status[key]) {
                    case 'normal':
                        element.addClass('bg-success');
                        break;
                    case 'warning':
                        element.addClass('bg-warning');
                        break;
                    case 'error':
                        element.addClass('bg-danger');
                        break;
                    default:
                        element.addClass('bg-success');
                }
            });
        }
        
        // 初回システム状態チェック
        setTimeout(checkSystemStatus, 1000);
        
        // 5分間隔でシステム状態をチェック
        setInterval(checkSystemStatus, 300000);
    });
</script>
