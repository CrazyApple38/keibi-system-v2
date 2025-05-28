@extends('layouts.app')

@section('title', 'パスワードリセット - 警備統合管理システム')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-sm-10">
            <!-- システムセキュリティ通知 -->
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show mb-4">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>送信完了:</strong> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- セキュリティ警告 -->
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>セキュリティ通知:</strong> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-lg border-0 security-card">
                <div class="card-header text-center py-4 bg-gradient-warning">
                    <div class="mb-3">
                        <div class="security-logo">
                            <i class="fas fa-key display-4 text-white"></i>
                            <div class="security-badge">
                                <i class="fas fa-shield-alt text-warning"></i>
                            </div>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold text-white">パスワードリセット</h3>
                    <p class="text-white-75 mb-0">
                        <i class="fas fa-user-shield me-1"></i>
                        警備業法準拠 セキュア認証復旧
                    </p>
                    <div class="mt-2">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-clock me-1"></i>
                            身元確認プロセス実施中
                        </span>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger border-left-danger">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <div>
                                    <strong>入力エラー</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- セキュリティレベル表示 -->
                    <div class="security-level mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="status-indicator status-secure me-2"></div>
                                <small class="text-muted">セキュリティレベル: 高度認証</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-check me-1 text-success"></i>
                                <small class="text-muted">警備業法準拠</small>
                            </div>
                        </div>
                    </div>

                    <!-- パスワードリセット手順説明 -->
                    <div class="alert alert-info border-left-info">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle me-2 mt-1"></i>
                            <div>
                                <h6 class="alert-heading mb-2">パスワードリセット手順</h6>
                                <ol class="mb-0">
                                    <li>登録済みメールアドレスと所属会社を入力</li>
                                    <li>身元確認のための追加情報を入力</li>
                                    <li>セキュリティ認証を完了</li>
                                    <li>メールでリセットリンクを受信</li>
                                    <li>24時間以内に新しいパスワードを設定</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('auth.password.reset') }}" id="resetForm">
                        @csrf
                        
                        <!-- 基本情報セクション -->
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-user me-2 text-primary"></i>基本認証情報
                                </h6>
                            </div>
                            <div class="section-body">
                                <!-- 所属会社 -->
                                <div class="mb-3">
                                    <label for="company_id" class="form-label fw-semibold">
                                        <i class="fas fa-building me-1"></i>
                                        所属会社 <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('company_id') is-invalid @enderror" 
                                            id="company_id" 
                                            name="company_id" 
                                            required>
                                        <option value="">選択してください</option>
                                        <option value="1" {{ old('company_id') == '1' ? 'selected' : '' }}>㈲東央警備</option>
                                        <option value="2" {{ old('company_id') == '2' ? 'selected' : '' }}>㈱Nikkeiホールディングス</option>
                                        <option value="3" {{ old('company_id') == '3' ? 'selected' : '' }}>㈱全日本エンタープライズ</option>
                                    </select>
                                    @error('company_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        身元確認のため所属会社の選択が必要です
                                    </div>
                                </div>

                                <!-- メールアドレス -->
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold">
                                        <i class="fas fa-envelope me-1"></i>
                                        登録済みメールアドレス <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-at"></i>
                                        </span>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email') }}" 
                                               required 
                                               autofocus
                                               placeholder="example@company.com">
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <small>システムに登録済みのメールアドレスを入力してください</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 身元確認セクション -->
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-user-check me-2 text-warning"></i>身元確認（警備業法第14条準拠）
                                </h6>
                            </div>
                            <div class="section-body">
                                <!-- 社員ID -->
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label fw-semibold">
                                        <i class="fas fa-id-card me-1"></i>
                                        社員ID <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-hashtag"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control @error('employee_id') is-invalid @enderror" 
                                               id="employee_id" 
                                               name="employee_id" 
                                               value="{{ old('employee_id') }}" 
                                               required
                                               pattern="[A-Z]{2,3}[0-9]{3,4}"
                                               placeholder="EMP001">
                                    </div>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        形式: 英字2-3文字 + 数字3-4桁（例: EMP001）
                                    </div>
                                </div>

                                <!-- 電話番号 -->
                                <div class="mb-3">
                                    <label for="phone" class="form-label fw-semibold">
                                        <i class="fas fa-phone me-1"></i>
                                        登録済み電話番号 <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-mobile-alt"></i>
                                        </span>
                                        <input type="tel" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone') }}" 
                                               required
                                               placeholder="090-1234-5678">
                                    </div>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        システムに登録済みの電話番号を入力してください
                                    </div>
                                </div>

                                <!-- 生年月日 -->
                                <div class="mb-3">
                                    <label for="birth_date" class="form-label fw-semibold">
                                        <i class="fas fa-calendar me-1"></i>
                                        生年月日 <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-birthday-cake"></i>
                                        </span>
                                        <input type="date" 
                                               class="form-control @error('birth_date') is-invalid @enderror" 
                                               id="birth_date" 
                                               name="birth_date" 
                                               value="{{ old('birth_date') }}" 
                                               required
                                               max="{{ date('Y-m-d', strtotime('-18 years')) }}">
                                    </div>
                                    @error('birth_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        本人確認のため生年月日を入力してください
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- セキュリティ認証セクション -->
                        <div class="section-card mb-4" id="securitySection">
                            <div class="section-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-shield-alt me-2 text-danger"></i>セキュリティ認証
                                </h6>
                            </div>
                            <div class="section-body">
                                <!-- リセット理由 -->
                                <div class="mb-3">
                                    <label for="reset_reason" class="form-label fw-semibold">
                                        <i class="fas fa-comment me-1"></i>
                                        パスワードリセット理由 <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('reset_reason') is-invalid @enderror" 
                                            id="reset_reason" 
                                            name="reset_reason" 
                                            required>
                                        <option value="">選択してください</option>
                                        <option value="forgot" {{ old('reset_reason') == 'forgot' ? 'selected' : '' }}>パスワードを忘れた</option>
                                        <option value="suspected_compromise" {{ old('reset_reason') == 'suspected_compromise' ? 'selected' : '' }}>不正アクセスの疑い</option>
                                        <option value="security_policy" {{ old('reset_reason') == 'security_policy' ? 'selected' : '' }}>セキュリティポリシーによる変更</option>
                                        <option value="device_lost" {{ old('reset_reason') == 'device_lost' ? 'selected' : '' }}>デバイス紛失・盗難</option>
                                        <option value="account_locked" {{ old('reset_reason') == 'account_locked' ? 'selected' : '' }}>アカウントロック解除</option>
                                        <option value="other" {{ old('reset_reason') == 'other' ? 'selected' : '' }}>その他</option>
                                    </select>
                                    @error('reset_reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 追加説明（その他選択時） -->
                                <div class="mb-3 d-none" id="otherReasonSection">
                                    <label for="other_reason_detail" class="form-label fw-semibold">
                                        <i class="fas fa-edit me-1"></i>
                                        詳細説明
                                    </label>
                                    <textarea class="form-control @error('other_reason_detail') is-invalid @enderror" 
                                              id="other_reason_detail" 
                                              name="other_reason_detail" 
                                              rows="3"
                                              placeholder="パスワードリセットが必要な理由を詳しく説明してください">{{ old('other_reason_detail') }}</textarea>
                                    @error('other_reason_detail')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 緊急時フラグ -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="emergency_reset" 
                                               name="emergency_reset"
                                               {{ old('emergency_reset') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="emergency_reset">
                                            <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                            緊急時パスワードリセット
                                        </label>
                                    </div>
                                    <div class="form-text">
                                        <small class="text-muted">
                                            緊急時は管理者に即座に通知され、迅速な処理が行われます
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CAPTCHA認証 -->
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-robot me-2 text-info"></i>自動化防止認証
                                </h6>
                            </div>
                            <div class="section-body">
                                <div class="captcha-container">
                                    <label for="captcha" class="form-label fw-semibold">
                                        <i class="fas fa-eye me-1"></i>
                                        画像認証 <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <div class="captcha-image me-3">
                                            <img src="{{ route('captcha.image') }}" alt="CAPTCHA" id="captchaImage" class="border rounded">
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="text" 
                                                   class="form-control @error('captcha') is-invalid @enderror" 
                                                   id="captcha" 
                                                   name="captcha" 
                                                   required
                                                   placeholder="画像の文字を入力">
                                            @error('captcha')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="button" class="btn btn-outline-secondary ms-2" id="refreshCaptcha">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 利用規約・同意 -->
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-file-contract me-2 text-success"></i>利用規約・同意事項
                                </h6>
                            </div>
                            <div class="section-body">
                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input @error('identity_verification') is-invalid @enderror" 
                                           id="identity_verification" 
                                           name="identity_verification"
                                           required
                                           {{ old('identity_verification') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="identity_verification">
                                        <strong>身元確認調査への同意</strong> <span class="text-danger">*</span>
                                        <br><small class="text-muted">警備業法第14条に基づく身元確認に同意します</small>
                                    </label>
                                    @error('identity_verification')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input @error('security_policy_agreement') is-invalid @enderror" 
                                           id="security_policy_agreement" 
                                           name="security_policy_agreement"
                                           required
                                           {{ old('security_policy_agreement') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="security_policy_agreement">
                                        <strong>セキュリティポリシーへの同意</strong> <span class="text-danger">*</span>
                                        <br><small class="text-muted">
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#securityPolicyModal" class="text-decoration-none">
                                                セキュリティポリシー
                                            </a>
                                            に同意し、適切な利用を約束します
                                        </small>
                                    </label>
                                    @error('security_policy_agreement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="audit_log_consent" 
                                           name="audit_log_consent"
                                           {{ old('audit_log_consent') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="audit_log_consent">
                                        <strong>監査ログ記録への同意</strong>
                                        <br><small class="text-muted">詳細な操作ログが記録されることに同意します（推奨）</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 送信ボタン -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg secure-btn" id="submitBtn">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                <i class="fas fa-paper-plane me-2"></i>
                                セキュアリセットリンクを送信
                            </button>
                        </div>
                        
                        <!-- ナビゲーションリンク -->
                        <div class="text-center mt-4">
                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ route('auth.login.form') }}" class="text-decoration-none">
                                        <i class="fas fa-arrow-left me-1"></i>
                                        ログインページに戻る
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('auth.register.form') }}" class="text-decoration-none">
                                        <i class="fas fa-user-plus me-1"></i>
                                        新規アカウント登録
                                    </a>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#helpModal">
                                        <i class="fas fa-question-circle me-1"></i>
                                        ヘルプ・サポート
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
                                <i class="fas fa-clock me-1"></i>
                                リンク有効期限: 24時間
                            </small>
                        </div>
                        <div class="col-md-4 text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-check me-1"></i>
                                ISO27001準拠処理
                            </small>
                        </div>
                        <div class="col-md-4 text-center text-md-end">
                            <small class="text-muted">
                                <i class="fas fa-eye me-1"></i>
                                全操作ログ記録中
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- セキュリティ情報カード -->
            <div class="security-info mt-4">
                <div class="card bg-dark text-white">
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="mb-2">
                                    <i class="fas fa-shield-virus me-1"></i>
                                    高度セキュリティ認証システム
                                </h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-primary">
                                        <i class="fas fa-user-check me-1"></i>多段階身元確認
                                    </span>
                                    <span class="badge bg-success">
                                        <i class="fas fa-lock me-1"></i>TLS1.3暗号化
                                    </span>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-eye me-1"></i>リアルタイム監視
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h6 class="mb-2">
                                    <i class="fas fa-gavel me-1"></i>
                                    法令準拠
                                </h6>
                                <span class="badge bg-danger">
                                    <i class="fas fa-balance-scale me-1"></i>
                                    警備業法第14条準拠
                                </span>
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
                                    緊急時・パスワードリセットサポート: 
                                    <a href="tel:08012345678" class="text-danger text-decoration-none">
                                        080-1234-5678
                                    </a>
                                    （24時間対応）
                                </small>
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">
                                    メールが届かない場合や緊急時は管理者まで即座にご連絡ください
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- セキュリティポリシーモーダル -->
<div class="modal fade" id="securityPolicyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-shield-alt me-2"></i>パスワードリセット セキュリティポリシー
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>重要:</strong> パスワードリセットは警備業法に基づく厳格な身元確認プロセスを実施します
                </div>

                <h6><i class="fas fa-user-check me-1"></i>身元確認プロセス</h6>
                <ul>
                    <li>所属会社の確認（警備業法準拠）</li>
                    <li>登録済み個人情報との照合</li>
                    <li>社員IDによる本人確認</li>
                    <li>電話番号・生年月日による追加認証</li>
                    <li>必要に応じて管理者による直接確認</li>
                </ul>

                <h6><i class="fas fa-clock me-1"></i>処理時間・有効期限</h6>
                <ul>
                    <li>通常処理: リクエスト後5分以内にメール送信</li>
                    <li>緊急時処理: リクエスト後1分以内にメール送信</li>
                    <li>リセットリンク有効期限: 24時間</li>
                    <li>リンク使用後の無効化: 即座</li>
                    <li>異常検知時の自動無効化機能</li>
                </ul>

                <h6><i class="fas fa-shield-virus me-1"></i>セキュリティ対策</h6>
                <ul>
                    <li>CAPTCHA認証による自動化防止</li>
                    <li>IP制限・地理的制限対応</li>
                    <li>異常アクセスパターンの検知</li>
                    <li>リアルタイム監視・ログ記録</li>
                    <li>管理者への即座通知機能</li>
                </ul>

                <h6><i class="fas fa-gavel me-1"></i>法令遵守</h6>
                <ul>
                    <li>警備業法第14条（身元確認）完全準拠</li>
                    <li>個人情報保護法に基づくデータ管理</li>
                    <li>ISO27001準拠のセキュリティ管理</li>
                    <li>監査ログの法定保存期間管理</li>
                </ul>

                <h6><i class="fas fa-exclamation-triangle me-1"></i>緊急時対応</h6>
                <ul>
                    <li>セキュリティインシデント発生時の優先処理</li>
                    <li>管理者による即座確認・承認</li>
                    <li>臨時パスワード発行機能</li>
                    <li>緊急連絡先への自動通知</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>閉じる
                </button>
                <button type="button" class="btn btn-warning" onclick="$('#security_policy_agreement').prop('checked', true); $('#securityPolicyModal').modal('hide');">
                    <i class="fas fa-check me-1"></i>同意する
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ヘルプ・サポートモーダル -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle me-2"></i>ヘルプ・サポート
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6><i class="fas fa-envelope me-1"></i>メールが届かない場合</h6>
                <ul>
                    <li>迷惑メールフォルダをご確認ください</li>
                    <li>会社のメールフィルターをご確認ください</li>
                    <li>メールアドレスに間違いがないかご確認ください</li>
                    <li>システム管理者にお問い合わせください</li>
                </ul>

                <h6><i class="fas fa-user-times me-1"></i>アカウントがロックされた場合</h6>
                <ul>
                    <li>30分後に自動解除されます</li>
                    <li>緊急時は管理者による解除が可能です</li>
                    <li>セキュリティ違反の疑いがある場合は詳細調査を実施</li>
                </ul>

                <h6><i class="fas fa-phone me-1"></i>緊急時連絡先</h6>
                <div class="alert alert-info">
                    <strong>システム管理者</strong><br>
                    電話: <a href="tel:08012345678">080-1234-5678</a><br>
                    メール: admin@security-system.jp<br>
                    対応時間: 24時間365日
                </div>

                <h6><i class="fas fa-question me-1"></i>よくある質問</h6>
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq1">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                パスワードリセット後のログイン方法は？
                            </button>
                        </h2>
                        <div id="collapse1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                新しいパスワード設定後、通常のログインページから新しいパスワードでログインしてください。初回ログイン時は追加認証が必要な場合があります。
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                複数回失敗するとどうなりますか？
                            </button>
                        </h2>
                        <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                5回連続で失敗すると30分間アカウントがロックされます。緊急時は管理者が手動で解除できます。
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>閉じる
                </button>
                <button type="button" class="btn btn-info" onclick="contactSupport()">
                    <i class="fas fa-phone me-1"></i>サポートに連絡
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
body {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #f59e0b 100%);
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
        radial-gradient(circle at 20% 50%, rgba(245, 158, 11, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(217, 119, 6, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 40% 80%, rgba(245, 158, 11, 0.2) 0%, transparent 50%);
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

.bg-gradient-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #f59e0b 100%) !important;
    position: relative;
}

.bg-gradient-warning::before {
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

.section-card {
    border: 1px solid #e5e7eb;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.section-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.section-body {
    padding: 1.5rem;
}

.form-control, .form-select {
    border-radius: 12px;
    padding: 14px 18px;
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
    font-size: 16px;
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 0.25rem rgba(245, 158, 11, 0.25);
    border-color: #f59e0b;
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
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
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
    background: linear-gradient(135deg, #d97706 0%, #b45309 50%, #92400e 100%);
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(245, 158, 11, 0.4);
}

.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}

.status-secure {
    background: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.3);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(245, 158, 11, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
    }
}

.border-left-danger {
    border-left: 5px solid #ef4444;
}

.border-left-info {
    border-left: 5px solid #3b82f6;
}

.captcha-container img {
    height: 60px;
    background: #f8f9fa;
    border-radius: 8px;
}

.security-level {
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

.section-card {
    animation: fadeInUp 0.6s ease-out;
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

/* セクション表示アニメーション */
.section-card:nth-child(1) { animation-delay: 0.1s; }
.section-card:nth-child(2) { animation-delay: 0.2s; }
.section-card:nth-child(3) { animation-delay: 0.3s; }
.section-card:nth-child(4) { animation-delay: 0.4s; }
.section-card:nth-child(5) { animation-delay: 0.5s; }

/* モバイル対応 */
@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .section-body {
        padding: 1rem;
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
        border-color: #f59e0b;
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
        border: 3px solid #f59e0b;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // リセット理由変更時の処理
    $('#reset_reason').change(function() {
        const selectedReason = $(this).val();
        
        if (selectedReason === 'other') {
            $('#otherReasonSection').removeClass('d-none');
            $('#other_reason_detail').attr('required', true);
        } else {
            $('#otherReasonSection').addClass('d-none');
            $('#other_reason_detail').attr('required', false);
        }

        // セキュリティ関連の理由の場合は警告表示
        if (['suspected_compromise', 'device_lost'].includes(selectedReason)) {
            showSecurityAlert('warning', 'セキュリティに関する重要な事由です。管理者に即座に通知されます。');
        }
    });

    // 緊急時フラグ変更時の処理
    $('#emergency_reset').change(function() {
        if ($(this).is(':checked')) {
            showSecurityAlert('warning', '緊急時パスワードリセットが選択されました。管理者に即座に通知され、迅速な処理が行われます。');
        }
    });

    // CAPTCHA更新
    $('#refreshCaptcha').click(function() {
        $('#captchaImage').attr('src', '{{ route("captcha.image") }}?' + Date.now());
        $('#captcha').val('').focus();
    });

    // フォーム送信時の処理
    $('#resetForm').on('submit', function(e) {
        // 基本バリデーション
        const email = $('#email').val();
        const companyId = $('#company_id').val();
        const employeeId = $('#employee_id').val();
        const phone = $('#phone').val();
        const birthDate = $('#birth_date').val();
        const resetReason = $('#reset_reason').val();
        const captcha = $('#captcha').val();

        if (!email || !companyId || !employeeId || !phone || !birthDate || !resetReason || !captcha) {
            e.preventDefault();
            showSecurityAlert('error', '必須項目がすべて入力されていません。');
            return false;
        }

        // メールアドレス形式チェック
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            showSecurityAlert('error', '正しいメールアドレス形式で入力してください。');
            return false;
        }

        // 社員ID形式チェック
        const employeeIdRegex = /^[A-Z]{2,3}[0-9]{3,4}$/;
        if (!employeeIdRegex.test(employeeId)) {
            e.preventDefault();
            showSecurityAlert('error', '社員IDの形式が正しくありません。（例: EMP001）');
            return false;
        }

        // 同意事項チェック
        if (!$('#identity_verification').is(':checked') || !$('#security_policy_agreement').is(':checked')) {
            e.preventDefault();
            showSecurityAlert('error', '必要な同意事項にチェックを入れてください。');
            return false;
        }

        // 年齢チェック（18歳以上）
        const birth = new Date(birthDate);
        const today = new Date();
        const age = today.getFullYear() - birth.getFullYear();
        
        if (age < 18) {
            e.preventDefault();
            showSecurityAlert('error', '18歳以上である必要があります。');
            return false;
        }

        // 緊急時の確認
        if ($('#emergency_reset').is(':checked')) {
            if (!confirm('緊急時パスワードリセットを要請します。\nこの操作は管理者に即座に通知され、詳細なログが記録されます。\n続行しますか？')) {
                e.preventDefault();
                return false;
            }
        }

        // ローディング状態に切り替え
        const submitBtn = $('#submitBtn');
        const spinner = submitBtn.find('.spinner-border');
        const btnIcon = submitBtn.find('i.fa-paper-plane');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        btnIcon.removeClass('fa-paper-plane').addClass('fa-spinner fa-spin');

        // セキュリティログ記録
        logSecurityEvent('password_reset_attempt', {
            email: email,
            company_id: companyId,
            employee_id: employeeId,
            reset_reason: resetReason,
            emergency_reset: $('#emergency_reset').is(':checked'),
            audit_log_consent: $('#audit_log_consent').is(':checked'),
            timestamp: new Date().toISOString(),
            user_agent: navigator.userAgent,
            ip_address: '{{ request()->ip() }}',
            screen_resolution: `${screen.width}x${screen.height}`,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        });
    });

    // 社員IDの自動フォーマット
    $('#employee_id').on('input', function() {
        let value = $(this).val().toUpperCase();
        $(this).val(value);
    });

    // 電話番号の自動フォーマット
    $('#phone').on('input', function() {
        let value = $(this).val().replace(/[^\d]/g, '');
        if (value.length >= 7) {
            value = value.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
        } else if (value.length >= 4) {
            value = value.replace(/(\d{3})(\d{4})/, '$1-$2');
        }
        $(this).val(value);
    });

    // メールアドレス形式チェック
    $('#email').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            if (!$(this).siblings('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">正しいメールアドレス形式で入力してください</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });

    // エンターキーで送信
    $(document).keypress(function(e) {
        if (e.which == 13 && !$('#resetForm button[type="submit"]').prop('disabled')) {
            $('#resetForm').submit();
        }
    });

    // フォーカス時のセキュリティ効果
    $('.form-control, .form-select').focus(function() {
        $(this).closest('.input-group, .mb-3').addClass('security-focus');
    }).blur(function() {
        $(this).closest('.input-group, .mb-3').removeClass('security-focus');
    });

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
        
        $('.container .row .col-lg-6').prepend(alertHtml);
        
        // 自動非表示
        setTimeout(() => {
            $('.security-alert').fadeOut();
        }, 6000);
    }

    // セキュリティ初期化ログ
    logSecurityEvent('password_reset_page_load', {
        page: 'password-reset',
        user_agent: navigator.userAgent,
        screen_resolution: `${screen.width}x${screen.height}`,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        language: navigator.language,
        platform: navigator.platform
    });
});

// サポート連絡機能
function contactSupport() {
    const phone = '080-1234-5678';
    if (confirm(`システム管理者に電話で連絡しますか？\n\n電話番号: ${phone}\n\n※この操作はログに記録されます`)) {
        // ログ記録
        fetch('/auth/support-contact-log', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({
                type: 'password_reset_support',
                timestamp: new Date().toISOString(),
                user_agent: navigator.userAgent
            })
        });
        
        // 電話発信
        window.location.href = `tel:${phone}`;
    }
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
</script>
@endpush
