@extends('layouts.app')

@section('title', 'ユーザー登録')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-sm-10">
            <div class="card shadow-lg border-0">
                <div class="card-header text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-person-plus display-4 text-success"></i>
                    </div>
                    <h3 class="mb-1">ユーザー登録</h3>
                    <p class="text-muted mb-0">新しいアカウントを作成</p>
                </div>
                
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('auth.register') }}" id="registerForm">
                        @csrf
                        
                        <div class="row">
                            <!-- 氏名 -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="bi bi-person me-1"></i>
                                    氏名 <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       required 
                                       autofocus
                                       placeholder="山田 太郎">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 社員ID -->
                            <div class="col-md-6 mb-3">
                                <label for="employee_id" class="form-label">
                                    <i class="bi bi-badge-tm me-1"></i>
                                    社員ID <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('employee_id') is-invalid @enderror" 
                                       id="employee_id" 
                                       name="employee_id" 
                                       value="{{ old('employee_id') }}" 
                                       required
                                       placeholder="EMP001">
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- メールアドレス -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i>
                                メールアドレス <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required
                                   placeholder="yamada@company.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <!-- 所属会社 -->
                            <div class="col-md-6 mb-3">
                                <label for="company_id" class="form-label">
                                    <i class="bi bi-building me-1"></i>
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
                            </div>
                            
                            <!-- 部署 -->
                            <div class="col-md-6 mb-3">
                                <label for="department" class="form-label">
                                    <i class="bi bi-diagram-3 me-1"></i>
                                    部署
                                </label>
                                <input type="text" 
                                       class="form-control @error('department') is-invalid @enderror" 
                                       id="department" 
                                       name="department" 
                                       value="{{ old('department') }}"
                                       placeholder="営業部">
                                @error('department')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- パスワード -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-1"></i>
                                    パスワード <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           required
                                           placeholder="8文字以上">
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="bi bi-eye" id="passwordIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <small>8文字以上、英数字を含む</small>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- パスワード確認 -->
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">
                                    <i class="bi bi-lock-fill me-1"></i>
                                    パスワード確認 <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           required
                                           placeholder="パスワードを再入力">
                                    <button type="button" class="btn btn-outline-secondary" id="togglePasswordConfirm">
                                        <i class="bi bi-eye" id="passwordConfirmIcon"></i>
                                    </button>
                                </div>
                                <div id="passwordMatch" class="form-text"></div>
                            </div>
                        </div>
                        
                        <!-- 電話番号 -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="bi bi-telephone me-1"></i>
                                電話番号
                            </label>
                            <input type="tel" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}"
                                   placeholder="090-1234-5678">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- 利用規約同意 -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input @error('terms') is-invalid @enderror" 
                                       id="terms" 
                                       name="terms"
                                       required
                                       {{ old('terms') ? 'checked' : '' }}>
                                <label class="form-check-label" for="terms">
                                    <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#termsModal">
                                        利用規約
                                    </a>
                                    および
                                    <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#privacyModal">
                                        プライバシーポリシー
                                    </a>
                                    に同意する <span class="text-danger">*</span>
                                </label>
                                @error('terms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- 登録ボタン -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                <i class="bi bi-person-plus me-2"></i>
                                アカウントを作成
                            </button>
                        </div>
                        
                        <!-- リンク -->
                        <div class="text-center mt-4">
                            <span class="text-muted">既にアカウントをお持ちの方は</span>
                            <a href="{{ route('auth.login.form') }}" class="text-decoration-none">
                                <i class="bi bi-box-arrow-in-right me-1"></i>
                                ログイン
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- フッター情報 -->
                <div class="card-footer text-center bg-light py-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        登録後、管理者による承認が必要です
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 利用規約モーダル -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">利用規約</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>第1条（適用）</h6>
                <p>本規約は、警備グループ統合管理システム（以下「本サービス」）の利用に関して、利用者と当社との間の権利義務関係を定めるものです。</p>
                
                <h6>第2条（利用登録）</h6>
                <p>利用希望者は所定の方法により利用登録を申請し、当社がこれを承認することによって利用登録が完了します。</p>
                
                <h6>第3条（禁止事項）</h6>
                <p>利用者は以下の行為を行ってはならないものとします：</p>
                <ul>
                    <li>システムの不正利用</li>
                    <li>他人になりすます行為</li>
                    <li>システムの安定稼働を妨げる行為</li>
                    <li>その他当社が不適切と判断する行為</li>
                </ul>
                
                <h6>第4条（個人情報の取扱い）</h6>
                <p>当社は利用者の個人情報を適切に管理し、法令に基づく場合を除き第三者に開示しません。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

<!-- プライバシーポリシーモーダル -->
<div class="modal fade" id="privacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">プライバシーポリシー</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>個人情報の収集について</h6>
                <p>当社は以下の個人情報を収集します：</p>
                <ul>
                    <li>氏名、メールアドレス、電話番号</li>
                    <li>所属会社、部署、社員ID</li>
                    <li>システム利用ログ</li>
                </ul>
                
                <h6>個人情報の利用目的</h6>
                <p>収集した個人情報は以下の目的で利用します：</p>
                <ul>
                    <li>サービス提供・運営</li>
                    <li>ユーザーサポート</li>
                    <li>システム改善・品質向上</li>
                </ul>
                
                <h6>個人情報の管理</h6>
                <p>当社は個人情報を適切に管理し、不正アクセス、漏洩、改ざん等を防止します。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    
    .main-content {
        display: flex;
        align-items: center;
        min-height: calc(100vh - 100px);
        padding: 2rem 0;
    }
    
    .card {
        border-radius: 15px;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        padding: 12px 15px;
    }
    
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.2rem rgba(34, 197, 94, 0.25);
        border-color: #22c55e;
    }
    
    .btn-success {
        padding: 12px;
        font-weight: 600;
        border-radius: 8px;
    }
    
    .input-group .btn {
        border-radius: 0 8px 8px 0;
    }
    
    .password-strength {
        height: 4px;
        border-radius: 2px;
        margin-top: 5px;
        transition: all 0.3s ease;
    }
    
    .strength-weak { background-color: #ef4444; }
    .strength-medium { background-color: #f59e0b; }
    .strength-strong { background-color: #22c55e; }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .card {
        animation: fadeInUp 0.6s ease-out;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // パスワード表示切り替え
        $('#togglePassword').click(function() {
            togglePasswordVisibility('#password', '#passwordIcon');
        });
        
        $('#togglePasswordConfirm').click(function() {
            togglePasswordVisibility('#password_confirmation', '#passwordConfirmIcon');
        });
        
        function togglePasswordVisibility(inputSelector, iconSelector) {
            const input = $(inputSelector);
            const icon = $(iconSelector);
            
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        }
        
        // パスワード強度チェック
        $('#password').on('input', function() {
            const password = $(this).val();
            checkPasswordStrength(password);
        });
        
        function checkPasswordStrength(password) {
            let strength = 0;
            let strengthText = '';
            let strengthClass = '';
            
            // 長さチェック
            if (password.length >= 8) strength++;
            // 数字チェック
            if (/\d/.test(password)) strength++;
            // 英字チェック
            if (/[a-zA-Z]/.test(password)) strength++;
            // 特殊文字チェック
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
            
            switch (strength) {
                case 0:
                case 1:
                    strengthText = '弱い';
                    strengthClass = 'strength-weak';
                    break;
                case 2:
                case 3:
                    strengthText = '普通';
                    strengthClass = 'strength-medium';
                    break;
                case 4:
                    strengthText = '強い';
                    strengthClass = 'strength-strong';
                    break;
            }
            
            // 強度バーを表示
            let strengthBar = $('#passwordStrength');
            if (strengthBar.length === 0 && password.length > 0) {
                $('#password').after(`
                    <div id="passwordStrength" class="password-strength ${strengthClass}"></div>
                    <div class="form-text"><small>パスワード強度: <span id="strengthText">${strengthText}</span></small></div>
                `);
            } else if (password.length > 0) {
                strengthBar.removeClass('strength-weak strength-medium strength-strong').addClass(strengthClass);
                $('#strengthText').text(strengthText);
            } else {
                strengthBar.remove();
                $('#strengthText').parent().remove();
            }
        }
        
        // パスワード一致チェック
        $('#password_confirmation').on('input', function() {
            const password = $('#password').val();
            const confirmPassword = $(this).val();
            const matchDiv = $('#passwordMatch');
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    matchDiv.html('<small class="text-success"><i class="bi bi-check-circle me-1"></i>パスワードが一致しています</small>');
                } else {
                    matchDiv.html('<small class="text-danger"><i class="bi bi-x-circle me-1"></i>パスワードが一致しません</small>');
                }
            } else {
                matchDiv.html('');
            }
        });
        
        // フォーム送信時の処理
        $('#registerForm').on('submit', function(e) {
            const password = $('#password').val();
            const confirmPassword = $('#password_confirmation').val();
            
            // パスワード一致チェック
            if (password !== confirmPassword) {
                e.preventDefault();
                showErrorMessage('パスワードが一致しません');
                return false;
            }
            
            // 利用規約チェック
            if (!$('#terms').is(':checked')) {
                e.preventDefault();
                showErrorMessage('利用規約に同意してください');
                return false;
            }
            
            // ローディング状態に切り替え
            const submitBtn = $('#submitBtn');
            const spinner = submitBtn.find('.spinner-border');
            
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');
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
    });
</script>
@endpush
@endsection
