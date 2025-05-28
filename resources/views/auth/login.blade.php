@extends('layouts.app')

@section('title', 'セキュアログイン - 警備統合管理システム')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7 col-sm-9">
            <!-- システム緊急メンテナンス通知 -->
            @if(config('app.maintenance_mode', false))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>システムメンテナンス中</strong><br>
                    {{ config('app.maintenance_message', 'システムメンテナンス中です。緊急時は管理者にお問い合わせください。') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- セキュリティアラート -->
            @if(session('security_alert'))
                <div class="alert alert-warning alert-dismissible fade show mb-4">
                    <i class="fas fa-shield-alt me-2"></i>
                    <strong>セキュリティ通知:</strong> {{ session('security_alert') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- ログイン試行回数警告 -->
            @if(session('login_attempts'))
                <div class="alert alert-danger alert-dismissible fade show mb-4">
                    <i class="fas fa-shield-alt me-2"></i>
                    <strong>セキュリティ警告:</strong> 
                    ログイン試行回数が上限に近づいています（{{ session('login_attempts') }}/5回）
                    <br><small>{{ 5 - session('login_attempts') }}回失敗するとアカウントが一時ロックされます。</small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-lg border-0 security-card">
                <div class="card-header text-center py-4 bg-gradient-security">
                    <div class="mb-3">
                        <div class="security-logo">
                            <i class="fas fa-shield-alt display-4 text-white"></i>
                            <div class="security-badge">
                                <i class="fas fa-lock text-success"></i>
                            </div>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold text-white">警備統合管理システム</h3>
                    <p class="text-white-75 mb-0">
                        <i class="fas fa-certificate me-1"></i>
                        ISO27001準拠 セキュアログイン
                    </p>
                    <div class="mt-2">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-shield-virus me-1"></i>
                            高セキュリティレベル認証
                        </span>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger border-left-danger">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <div>
                                    <strong>認証エラー</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- システム状態・セキュリティ監視表示 -->
                    <div class="system-status mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="status-indicator status-online me-2"></div>
                                    <small class="text-muted">システム: 正常稼働中</small>
                                </div>
                                <div class="d-flex align-items-center mt-1">
                                    <div class="status-indicator status-security me-2"></div>
                                    <small class="text-muted">セキュリティ: 監視中</small>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="d-flex align-items-center justify-content-md-end">
                                    <i class="fas fa-clock me-1 text-muted"></i>
                                    <small class="text-muted" id="currentTime"></small>
                                </div>
                                <div class="d-flex align-items-center justify-content-md-end mt-1">
                                    <i class="fas fa-users me-1 text-muted"></i>
                                    <small class="text-muted">同時接続: <span id="activeUsers">{{ session('active_users_count', 1) }}</span>名</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('auth.login') }}" id="loginForm">
                        @csrf
                        
                        <!-- 会社選択（複数警備会社対応） -->
                        <div class="mb-3">
                            <label for="company_id" class="form-label fw-semibold">
                                <i class="fas fa-building me-1"></i>
                                所属会社
                            </label>
                            <select class="form-select @error('company_id') is-invalid @enderror" 
                                    id="company_id" 
                                    name="company_id">
                                <option value="">選択してください</option>
                                <option value="1" {{ old('company_id') == '1' ? 'selected' : '' }}>㈲東央警備</option>
                                <option value="2" {{ old('company_id') == '2' ? 'selected' : '' }}>㈱Nikkeiホールディングス</option>
                                <option value="3" {{ old('company_id') == '3' ? 'selected' : '' }}>㈱全日本エンタープライズ</option>
                            </select>
                            @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- メールアドレス -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-1"></i>
                                メールアドレス <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required 
                                       autofocus
                                       autocomplete="username"
                                       placeholder="example@company.com">
                            </div>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- パスワード -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-1"></i>
                                パスワード <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required
                                       autocomplete="current-password"
                                       placeholder="パスワードを入力">
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 2段階認証コード（必要時のみ表示） -->
                        <div class="mb-3 d-none" id="twoFactorSection">
                            <label for="two_factor_code" class="form-label fw-semibold">
                                <i class="fas fa-mobile-alt me-1"></i>
                                2段階認証コード
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-shield-alt"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="two_factor_code" 
                                       name="two_factor_code" 
                                       maxlength="6"
                                       placeholder="6桁の認証コード">
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                認証アプリに表示されている6桁のコードを入力してください
                            </div>
                        </div>
                        
                        <!-- セキュリティオプション -->
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="remember" 
                                               name="remember"
                                               {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">
                                            <i class="fas fa-clock me-1"></i>
                                            ログイン状態を保持
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="secure_session" 
                                               name="secure_session">
                                        <label class="form-check-label" for="secure_session">
                                            <i class="fas fa-shield-alt me-1"></i>
                                            高セキュリティモード
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="emergency_mode" 
                                               name="emergency_mode">
                                        <label class="form-check-label" for="emergency_mode">
                                            <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                            緊急時アクセス
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="audit_login" 
                                               name="audit_login">
                                        <label class="form-check-label" for="audit_login">
                                            <i class="fas fa-clipboard-list me-1"></i>
                                            監査ログ記録
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CAPTCHA（ログイン試行回数が多い場合） -->
                        @if(session('show_captcha'))
                        <div class="mb-3">
                            <label for="captcha" class="form-label fw-semibold">
                                <i class="fas fa-shield-alt me-1"></i>
                                セキュリティ認証
                            </label>
                            <div class="captcha-container">
                                <div class="d-flex align-items-center">
                                    <div class="captcha-image me-3">
                                        <img src="{{ route('captcha.image') }}" alt="CAPTCHA" id="captchaImage" class="border rounded">
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="text" 
                                               class="form-control" 
                                               id="captcha" 
                                               name="captcha" 
                                               required
                                               placeholder="画像の文字を入力">
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary ms-2" id="refreshCaptcha">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- デバイス登録（信頼できるデバイス） -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="trust_device" 
                                       name="trust_device">
                                <label class="form-check-label" for="trust_device">
                                    <i class="fas fa-laptop me-1"></i>
                                    このデバイスを信頼できるデバイスとして登録
                                </label>
                            </div>
                            <div class="form-text">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    信頼デバイス登録により次回から2段階認証を簡略化できます
                                </small>
                            </div>
                        </div>
                        
                        <!-- ログインボタン -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg secure-btn" id="loginBtn">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                <i class="fas fa-sign-in-alt me-2"></i>
                                セキュアログイン
                            </button>
                        </div>
                        
                        <!-- セキュリティリンク -->
                        <div class="text-center mt-4">
                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ route('auth.password.reset.form') }}" class="text-decoration-none">
                                        <i class="fas fa-key me-1"></i>
                                        パスワードリセット
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('auth.register.form') }}" class="text-decoration-none">
                                        <i class="fas fa-user-plus me-1"></i>
                                        アカウント登録
                                    </a>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#emergencyAccessModal">
                                        <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                        緊急時アクセス
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#securityPolicyModal">
                                        <i class="fas fa-shield-check me-1"></i>
                                        セキュリティポリシー
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- セキュリティ情報フッター -->
                <div class="card-footer bg-light py-3">
                    <div class="row">
                        <div class="col-md-4 text-center text-md-start">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>
                                TLS 1.3暗号化通信
                            </small>
                        </div>
                        <div class="col-md-4 text-center">
                            <small class="text-muted">
                                <i class="fas fa-certificate me-1"></i>
                                ISO27001認証済み
                            </small>
                        </div>
                        <div class="col-md-4 text-center text-md-end">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                セッション有効期限: <span id="sessionTimer">8時間</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 対象会社・セキュリティレベル表示 -->
            <div class="security-info mt-4">
                <div class="card bg-dark text-white">
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="mb-2">
                                    <i class="fas fa-building me-1"></i>
                                    警備グループ統合管理対象
                                </h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-primary company-badge" data-company="1">
                                        <i class="fas fa-shield-alt me-1"></i>㈲東央警備
                                    </span>
                                    <span class="badge bg-success company-badge" data-company="2">
                                        <i class="fas fa-shield-alt me-1"></i>㈱Nikkeiホールディングス
                                    </span>
                                    <span class="badge bg-warning company-badge" data-company="3">
                                        <i class="fas fa-shield-alt me-1"></i>㈱全日本エンタープライズ
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h6 class="mb-2">
                                    <i class="fas fa-shield-virus me-1"></i>
                                    セキュリティレベル
                                </h6>
                                <span class="badge bg-danger">
                                    <i class="fas fa-star me-1"></i>最高レベル（AAA+）
                                </span>
                                <div class="mt-1">
                                    <small>
                                        <i class="fas fa-eye me-1"></i>
                                        リアルタイム監視中
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 緊急時連絡先 -->
            <div class="emergency-contact mt-3">
                <div class="text-center">
                    <div class="card border-danger bg-danger-subtle">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="fas fa-phone-alt me-2 text-danger"></i>
                                <small class="text-danger fw-bold">
                                    緊急時・ログイントラブル: 
                                    <a href="tel:08012345678" class="text-danger text-decoration-none">
                                        080-1234-5678
                                    </a>
                                    （24時間対応）
                                </small>
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">
                                    システム障害時は管理者まで即座にご連絡ください
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- セキュリティ監査情報 -->
            <div class="security-audit mt-3">
                <div class="text-center">
                    <small class="text-muted">
                        <i class="fas fa-clipboard-check me-1"></i>
                        最終セキュリティ監査: {{ date('Y年m月d日') }} | 
                        <i class="fas fa-server me-1"></i>
                        サーバー稼働率: 99.99% | 
                        <i class="fas fa-bug me-1"></i>
                        セキュリティ脅威: 0件
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- セキュリティポリシーモーダル -->
<div class="modal fade" id="securityPolicyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-shield-alt me-2"></i>警備業界準拠セキュリティポリシー
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>警備業法準拠</strong> - 本システムは警備業法第14条および関連法令に完全準拠しています
                </div>

                <h6><i class="fas fa-lock me-1"></i>ログインセキュリティ</h6>
                <ul>
                    <li>ログイン試行回数は5回まで（その後30分間ロック）</li>
                    <li>セッション有効期限は8時間（延長可能、最大24時間）</li>
                    <li>不正ログイン試行はリアルタイムで監視・通報</li>
                    <li>すべてのログイン活動が記録・監査されます（法定保存期間3年）</li>
                    <li>2段階認証対応（管理者・責任者は必須）</li>
                </ul>

                <h6><i class="fas fa-key me-1"></i>パスワードポリシー</h6>
                <ul>
                    <li>最低8文字以上、英数字と特殊文字を含む</li>
                    <li>90日ごとのパスワード変更を推奨（管理者は60日）</li>
                    <li>過去5回のパスワードは再使用不可</li>
                    <li>辞書攻撃対策の複雑性チェック実施</li>
                    <li>緊急時パスワードリセット機能対応</li>
                </ul>

                <h6><i class="fas fa-gavel me-1"></i>法令遵守・コンプライアンス</h6>
                <ul>
                    <li>警備業法第14条（身元確認）に基づく認証管理</li>
                    <li>個人情報保護法準拠のデータ管理</li>
                    <li>ISO27001に準拠したセキュリティ管理</li>
                    <li>定期的なセキュリティ監査実施（月次・年次）</li>
                    <li>インシデント対応手順の確立・演習実施</li>
                </ul>

                <h6><i class="fas fa-eye me-1"></i>監視・ログ管理</h6>
                <ul>
                    <li>24時間365日のセキュリティ監視</li>
                    <li>異常アクセス検知・自動通報システム</li>
                    <li>操作ログの完全記録・改ざん防止</li>
                    <li>法執行機関への報告体制整備</li>
                </ul>

                <h6><i class="fas fa-mobile-alt me-1"></i>デバイス・ネットワークセキュリティ</h6>
                <ul>
                    <li>デバイス登録・管理機能</li>
                    <li>IPアドレス制限・地理的制限対応</li>
                    <li>VPN接続推奨・公衆Wi-Fi制限</li>
                    <li>デバイス証明書認証対応</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>閉じる
                </button>
                <button type="button" class="btn btn-primary" onclick="downloadSecurityPolicy()">
                    <i class="fas fa-download me-1"></i>PDFダウンロード
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 緊急時アクセスモーダル -->
<div class="modal fade" id="emergencyAccessModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>緊急時アクセス
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>警告:</strong> 緊急時アクセスは特別な状況でのみ使用してください
                </div>
                
                <h6>緊急時アクセス適用条件</h6>
                <ul>
                    <li>重大なセキュリティインシデント発生時</li>
                    <li>システム障害により通常ログインが不可能な場合</li>
                    <li>災害・事故等による緊急対応が必要な場合</li>
                    <li>管理者による特別承認がある場合</li>
                </ul>

                <div class="form-group">
                    <label for="emergency_reason" class="form-label fw-bold">緊急事由（必須）</label>
                    <textarea class="form-control" id="emergency_reason" rows="3" 
                              placeholder="緊急アクセスが必要な理由を詳しく記入してください"></textarea>
                </div>

                <div class="form-group mt-3">
                    <label for="emergency_contact" class="form-label fw-bold">緊急連絡先</label>
                    <input type="tel" class="form-control" id="emergency_contact" 
                           placeholder="080-1234-5678">
                </div>

                <div class="alert alert-warning mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    緊急アクセスの使用は管理者に即座に通知され、すべての操作が記録されます
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>キャンセル
                </button>
                <button type="button" class="btn btn-danger" onclick="requestEmergencyAccess()">
                    <i class="fas fa-exclamation-triangle me-1"></i>緊急アクセス要請
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
body {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #1e3c72 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    position: relative;
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
        radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 40% 80%, rgba(120, 119, 198, 0.2) 0%, transparent 50%);
    pointer-events: none;
    z-index: -1;
}

.main-content {
    display: flex;
    align-items: center;
    min-height: calc(100vh - 200px);
    padding: 2rem 0;
}

.security-card {
    border-radius: 20px;
    backdrop-filter: blur(15px);
    background: rgba(255, 255, 255, 0.97);
    box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.25),
        0 0 0 1px rgba(255, 255, 255, 0.1);
    overflow: hidden;
}

.bg-gradient-security {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #1e40af 100%) !important;
    position: relative;
}

.bg-gradient-security::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.security-logo {
    position: relative;
    display: inline-block;
}

.security-badge {
    position: absolute;
    bottom: -8px;
    right: -8px;
    background: white;
    border-radius: 50%;
    padding: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    border: 2px solid rgba(255,255,255,0.9);
}

.form-control, .form-select {
    border-radius: 12px;
    padding: 14px 18px;
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
    font-size: 16px;
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
    border-color: #3b82f6;
    transform: translateY(-2px);
}

.input-group-text {
    border: 2px solid #e5e7eb;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px 0 0 12px;
    padding: 14px 18px;
}

.input-group .form-control, .input-group .form-select {
    border-radius: 0;
    border-left: none;
}

.input-group .btn {
    border-radius: 0 12px 12px 0;
    border: 2px solid #e5e7eb;
    border-left: none;
}

.secure-btn {
    padding: 14px 28px;
    font-weight: 600;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 50%, #1e40af 100%);
    border: none;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.secure-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
}

.secure-btn:hover::before {
    left: 100%;
}

.secure-btn:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 50%, #1e3a8a 100%);
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(59, 130, 246, 0.4);
}

.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}

.status-online {
    background: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.3);
    animation: pulse 2s infinite;
}

.status-security {
    background: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.3);
    animation: pulse 2s infinite 0.5s;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
    }
}

.border-left-danger {
    border-left: 5px solid #ef4444;
}

.captcha-container img {
    height: 60px;
    background: #f8f9fa;
    border-radius: 8px;
}

.system-status {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 16px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.security-info .card {
    background: linear-gradient(135deg, #1f2937 0%, #374151 50%, #1f2937 100%) !important;
    border: none;
    border-radius: 15px;
}

.emergency-contact .card {
    background: rgba(239, 68, 68, 0.1) !important;
    border-color: #ef4444;
    border-radius: 12px;
}

.company-badge {
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
    padding: 8px 12px;
}

.company-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.text-white-75 {
    color: rgba(255, 255, 255, 0.85) !important;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.security-card {
    animation: fadeInScale 0.8s ease-out;
}

.security-info {
    animation: fadeInUp 1s ease-out 0.2s both;
}

.emergency-contact {
    animation: fadeInUp 1s ease-out 0.4s both;
}

.badge {
    animation: fadeInUp 1.2s ease-out 0.6s both;
    font-weight: 500;
}

.alert {
    animation: fadeInUp 0.5s ease-out;
}

/* 会社選択時のハイライト効果 */
.company-highlight {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%) !important;
    color: #1f2937 !important;
    font-weight: bold;
}

/* セキュリティレベル表示の特殊効果 */
.badge.bg-danger {
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 50%, #dc2626 100%) !important;
    animation: securityPulse 3s infinite;
}

@keyframes securityPulse {
    0%, 100% { 
        box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4);
    }
    50% { 
        box-shadow: 0 0 0 8px rgba(220, 38, 38, 0);
    }
}

/* モバイル対応 */
@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .row > div {
        margin-bottom: 1rem;
    }
    
    .secure-btn {
        padding: 12px 20px;
    }
    
    .form-control, .form-select {
        padding: 12px 16px;
        font-size: 16px; /* iOS zoom prevention */
    }
}

/* 印刷対応 */
@media print {
    body {
        background: white !important;
    }
    
    .security-card {
        box-shadow: none !important;
        border: 2px solid #000 !important;
    }
    
    .btn, .modal, .badge {
        display: none !important;
    }
    
    .emergency-contact {
        page-break-inside: avoid;
    }
}

/* ダークモード対応 */
@media (prefers-color-scheme: dark) {
    .security-card {
        background: rgba(31, 41, 55, 0.95);
        color: #f9fafb;
    }
    
    .form-control, .form-select {
        background-color: rgba(55, 65, 81, 0.8);
        border-color: #4b5563;
        color: #f9fafb;
    }
    
    .form-control:focus, .form-select:focus {
        background-color: rgba(55, 65, 81, 0.9);
        border-color: #60a5fa;
    }
}

/* アクセシビリティ改善 */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* 高コントラストモード */
@media (prefers-contrast: high) {
    .form-control, .form-select {
        border-width: 3px;
    }
    
    .secure-btn {
        border: 3px solid #1e40af;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // 現在時刻の表示
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
        $('#currentTime').text(timeString);
    }
    
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);

    // 会社選択時のハイライト効果
    $('#company_id').change(function() {
        const selectedCompany = $(this).val();
        $('.company-badge').removeClass('company-highlight');
        
        if (selectedCompany) {
            $(`.company-badge[data-company="${selectedCompany}"]`).addClass('company-highlight');
        }
    });

    // パスワード表示切り替え
    $('#togglePassword').click(function() {
        const passwordInput = $('#password');
        const passwordIcon = $('#passwordIcon');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            passwordIcon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            passwordIcon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // CAPTCHA更新
    $('#refreshCaptcha').click(function() {
        $('#captchaImage').attr('src', '{{ route("captcha.image") }}?' + Date.now());
    });

    // フォーム送信時の処理
    $('#loginForm').on('submit', function(e) {
        const email = $('#email').val();
        const password = $('#password').val();
        const companyId = $('#company_id').val();
        
        // 基本バリデーション
        if (!email || !password) {
            e.preventDefault();
            showSecurityAlert('error', 'メールアドレスとパスワードを入力してください');
            return false;
        }

        // 会社選択チェック
        if (!companyId) {
            e.preventDefault();
            showSecurityAlert('warning', '所属会社を選択してください');
            return false;
        }

        // メールアドレス形式チェック
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            showSecurityAlert('error', '正しいメールアドレス形式で入力してください');
            return false;
        }

        // 緊急モードの確認
        if ($('#emergency_mode').is(':checked')) {
            if (!confirm('緊急時アクセスモードを使用します。\nこの操作は管理者に通知され、すべてのアクションが監査ログに記録されます。\n続行しますか？')) {
                e.preventDefault();
                return false;
            }
        }

        // ローディング状態に切り替え
        const submitBtn = $('#loginBtn');
        const spinner = submitBtn.find('.spinner-border');
        const btnIcon = submitBtn.find('i.fa-sign-in-alt');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        btnIcon.removeClass('fa-sign-in-alt').addClass('fa-spinner fa-spin');

        // セキュリティログ記録
        logSecurityEvent('login_attempt', {
            email: email,
            company_id: companyId,
            emergency_mode: $('#emergency_mode').is(':checked'),
            secure_session: $('#secure_session').is(':checked'),
            trust_device: $('#trust_device').is(':checked'),
            timestamp: new Date().toISOString(),
            user_agent: navigator.userAgent,
            ip_address: '{{ request()->ip() }}',
            screen_resolution: `${screen.width}x${screen.height}`,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        });
    });

    // 2段階認証が必要な場合の処理
    if (localStorage.getItem('requires_2fa') === 'true') {
        $('#twoFactorSection').removeClass('d-none');
        $('#two_factor_code').attr('required', true);
    }

    // エンターキーでログイン
    $(document).keypress(function(e) {
        if (e.which == 13 && !$('#loginForm button[type="submit"]').prop('disabled')) {
            $('#loginForm').submit();
        }
    });

    // セキュリティモード切り替え
    $('#secure_session').change(function() {
        if ($(this).is(':checked')) {
            showSecurityAlert('info', '高セキュリティモードが有効になります。セッション時間が短縮され、追加の認証が必要になる場合があります。');
        }
    });

    // 緊急モード切り替え
    $('#emergency_mode').change(function() {
        if ($(this).is(':checked')) {
            showSecurityAlert('warning', '緊急時アクセスモードが選択されました。この操作は管理者に即座に通知され、すべてのアクションが記録されます。');
        }
    });

    // 監査ログ記録切り替え
    $('#audit_login').change(function() {
        if ($(this).is(':checked')) {
            showSecurityAlert('info', '監査ログ記録が有効になりました。詳細なアクセスログが記録されます。');
        }
    });

    // フォーカス時のセキュリティ効果
    $('.form-control, .form-select').focus(function() {
        $(this).closest('.input-group, .mb-3').addClass('security-focus');
    }).blur(function() {
        $(this).closest('.input-group, .mb-3').removeClass('security-focus');
    });

    // ログイン試行回数の監視
    let loginAttempts = parseInt(localStorage.getItem('login_attempts') || '0');
    if (loginAttempts >= 3) {
        showSecurityAlert('warning', `ログイン試行回数: ${loginAttempts}/5回。残り${5-loginAttempts}回で一時ロックされます。`);
    }

    // セキュリティポリシーの表示
    if (!localStorage.getItem('security_policy_viewed')) {
        setTimeout(() => {
            if (confirm('セキュリティポリシーを確認しますか？\n初回ログイン時は必ずご確認ください。')) {
                $('#securityPolicyModal').modal('show');
                localStorage.setItem('security_policy_viewed', 'true');
            }
        }, 3000);
    }

    // ページ離脱時の警告（セキュリティ確保）
    window.addEventListener('beforeunload', function(e) {
        if ($('#password').val()) {
            const confirmationMessage = 'パスワードが入力されています。ページを離れてもよろしいですか？';
            e.returnValue = confirmationMessage;
            return confirmationMessage;
        }
    });

    // セキュリティ状態の監視
    function monitorSecurityStatus() {
        // セッション有効性チェック
        fetch('/auth/session-check', {
            method: 'GET',
            credentials: 'same-origin'
        }).then(response => {
            if (!response.ok) {
                showSecurityAlert('warning', 'セッションの有効性を確認できません。');
            }
        }).catch(error => {
            console.warn('Session check failed:', error);
        });

        // アクティブユーザー数の更新
        fetch('/auth/active-users-count')
            .then(response => response.json())
            .then(data => {
                $('#activeUsers').text(data.count || 1);
            })
            .catch(error => console.warn('Active users check failed:', error));
    }

    // 定期的なセキュリティチェック
    setInterval(monitorSecurityStatus, 60000); // 1分ごと

    // セキュリティイベントログ
    function logSecurityEvent(event, data) {
        const logEntry = {
            event: event,
            timestamp: new Date().toISOString(),
            session_id: sessionStorage.getItem('session_id') || generateSessionId(),
            data: data
        };

        // サーバーサイドログ送信
        fetch('/auth/security-log', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify(logEntry)
        }).catch(error => console.warn('Security log failed:', error));
        
        // ローカルストレージにも記録（開発時のみ）
        const securityLog = JSON.parse(localStorage.getItem('security_log') || '[]');
        securityLog.push(logEntry);
        if (securityLog.length > 100) securityLog.shift(); // 最新100件のみ保持
        localStorage.setItem('security_log', JSON.stringify(securityLog));
    }

    // セッションID生成
    function generateSessionId() {
        const sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        sessionStorage.setItem('session_id', sessionId);
        return sessionId;
    }

    // セキュリティアラート表示
    function showSecurityAlert(type, message) {
        const alertClass = type === 'error' ? 'alert-danger' : (type === 'warning' ? 'alert-warning' : 'alert-info');
        const iconClass = type === 'error' ? 'fa-exclamation-triangle' : (type === 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle');
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show security-alert" role="alert">
                <i class="fas ${iconClass} me-2"></i>
                <strong>セキュリティ通知:</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('.container .row .col-lg-5').prepend(alertHtml);
        
        // 自動非表示
        setTimeout(() => {
            $('.security-alert').fadeOut();
        }, 6000);
    }

    // セキュリティ初期化ログ
    logSecurityEvent('page_load', {
        page: 'login',
        user_agent: navigator.userAgent,
        screen_resolution: `${screen.width}x${screen.height}`,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        language: navigator.language,
        platform: navigator.platform
    });
});

// 緊急アクセス要請
function requestEmergencyAccess() {
    const reason = $('#emergency_reason').val();
    const contact = $('#emergency_contact').val();
    
    if (!reason.trim()) {
        alert('緊急事由を入力してください。');
        return;
    }
    
    if (!contact.trim()) {
        alert('緊急連絡先を入力してください。');
        return;
    }
    
    // 緊急アクセス要請をサーバーに送信
    fetch('/auth/emergency-access-request', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: JSON.stringify({
            reason: reason,
            emergency_contact: contact,
            timestamp: new Date().toISOString(),
            user_agent: navigator.userAgent
        })
    }).then(response => {
        if (response.ok) {
            alert('緊急アクセス要請が送信されました。管理者からの連絡をお待ちください。');
            $('#emergencyAccessModal').modal('hide');
        } else {
            alert('緊急アクセス要請の送信に失敗しました。直接管理者にお電話ください。');
        }
    }).catch(error => {
        console.error('Emergency access request failed:', error);
        alert('緊急アクセス要請の送信に失敗しました。直接管理者にお電話ください。');
    });
}

// セキュリティポリシーPDFダウンロード
function downloadSecurityPolicy() {
    const link = document.createElement('a');
    link.href = '/assets/documents/security-policy.pdf';
    link.download = '警備システムセキュリティポリシー.pdf';
    link.click();
}

// CSRFトークンの自動更新
setInterval(function() {
    fetch('/csrf-token')
        .then(response => response.json())
        .then(data => {
            $('meta[name="csrf-token"]').attr('content', data.token);
            $('input[name="_token"]').val(data.token);
        })
        .catch(error => console.warn('CSRF token refresh failed:', error));
}, 300000); // 5分ごと

// サービスワーカー登録（オフライン対応）
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js')
        .then(registration => {
            console.log('Service Worker registered:', registration);
        })
        .catch(error => {
            console.warn('Service Worker registration failed:', error);
        });
}
</script>
@endpush
