@extends('layouts.app')

@section('title', 'パスワード変更')

@section('content')
<div class="container">
    <!-- パンくずリスト -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
            <li class="breadcrumb-item active" aria-current="page">パスワード変更</li>
        </ol>
    </nav>
    
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-key me-2"></i>
                        パスワード変更
                    </h4>
                </div>
                
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        セキュリティ保護のため、定期的なパスワード変更をお勧めします。
                    </div>
                    
                    <form method="POST" action="{{ route('auth.password.change') }}" id="changePasswordForm">
                        @csrf
                        
                        <!-- 現在のパスワード -->
                        <div class="mb-4">
                            <label for="current_password" class="form-label">
                                <i class="bi bi-lock me-1"></i>
                                現在のパスワード <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" 
                                       name="current_password" 
                                       required
                                       placeholder="現在のパスワードを入力">
                                <button type="button" class="btn btn-outline-secondary" id="toggleCurrentPassword">
                                    <i class="bi bi-eye" id="currentPasswordIcon"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- 新しいパスワード -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label">
                                <i class="bi bi-lock-fill me-1"></i>
                                新しいパスワード <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('new_password') is-invalid @enderror" 
                                       id="new_password" 
                                       name="new_password" 
                                       required
                                       placeholder="8文字以上の新しいパスワード">
                                <button type="button" class="btn btn-outline-secondary" id="toggleNewPassword">
                                    <i class="bi bi-eye" id="newPasswordIcon"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <small>8文字以上、英数字を含む強固なパスワードを設定してください</small>
                            </div>
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- 新しいパスワード確認 -->
                        <div class="mb-4">
                            <label for="new_password_confirmation" class="form-label">
                                <i class="bi bi-lock-fill me-1"></i>
                                新しいパスワード（確認） <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="new_password_confirmation" 
                                       name="new_password_confirmation" 
                                       required
                                       placeholder="新しいパスワードを再入力">
                                <button type="button" class="btn btn-outline-secondary" id="toggleNewPasswordConfirm">
                                    <i class="bi bi-eye" id="newPasswordConfirmIcon"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="form-text"></div>
                        </div>
                        
                        <!-- パスワード要件 -->
                        <div class="card bg-light mb-4">
                            <div class="card-body py-3">
                                <h6 class="card-title mb-2">
                                    <i class="bi bi-shield-check me-1"></i>
                                    パスワード要件
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-unstyled mb-0">
                                            <li id="req-length" class="text-muted">
                                                <i class="bi bi-circle me-1"></i>8文字以上
                                            </li>
                                            <li id="req-letter" class="text-muted">
                                                <i class="bi bi-circle me-1"></i>英字を含む
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="list-unstyled mb-0">
                                            <li id="req-number" class="text-muted">
                                                <i class="bi bi-circle me-1"></i>数字を含む
                                            </li>
                                            <li id="req-special" class="text-muted">
                                                <i class="bi bi-circle me-1"></i>特殊文字を含む（推奨）
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 変更ボタン -->
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                <i class="bi bi-check-circle me-2"></i>
                                パスワードを変更
                            </button>
                            <a href="{{ route('dashboard.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>
                                キャンセル
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- セキュリティ情報 -->
                <div class="card-footer bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-2">
                                <i class="bi bi-shield-lock me-1"></i>
                                セキュリティのヒント
                            </h6>
                            <ul class="small text-muted mb-0">
                                <li>他のサービスと同じパスワードは使用しない</li>
                                <li>推測されやすい個人情報は避ける</li>
                                <li>定期的にパスワードを変更する</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2">
                                <i class="bi bi-clock-history me-1"></i>
                                最終変更
                            </h6>
                            <p class="small text-muted mb-0">
                                {{ Auth::user()->password_changed_at ?? '初回ログイン' }}
                            </p>
                            <p class="small text-muted mb-0">
                                <i class="bi bi-geo-alt me-1"></i>
                                変更者: {{ Auth::user()->name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .password-strength {
        height: 4px;
        border-radius: 2px;
        margin-top: 8px;
        transition: all 0.3s ease;
    }
    
    .strength-weak { 
        background: linear-gradient(90deg, #ef4444 0%, #ef4444 33%, #e5e7eb 33%, #e5e7eb 100%);
    }
    .strength-medium { 
        background: linear-gradient(90deg, #f59e0b 0%, #f59e0b 66%, #e5e7eb 66%, #e5e7eb 100%);
    }
    .strength-strong { 
        background: linear-gradient(90deg, #22c55e 0%, #22c55e 100%);
    }
    
    .requirement-met {
        color: #22c55e !important;
    }
    
    .requirement-met .bi-circle {
        display: none;
    }
    
    .requirement-met::before {
        content: '✓';
        margin-right: 0.5rem;
        font-weight: bold;
    }
    
    .input-group .btn {
        border-radius: 0 8px 8px 0;
    }
    
    .form-control {
        border-radius: 8px 0 0 8px;
    }
    
    .alert-info {
        border-left: 4px solid #3b82f6;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // パスワード表示切り替え
        $('#toggleCurrentPassword').click(function() {
            togglePasswordVisibility('#current_password', '#currentPasswordIcon');
        });
        
        $('#toggleNewPassword').click(function() {
            togglePasswordVisibility('#new_password', '#newPasswordIcon');
        });
        
        $('#toggleNewPasswordConfirm').click(function() {
            togglePasswordVisibility('#new_password_confirmation', '#newPasswordConfirmIcon');
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
        $('#new_password').on('input', function() {
            const password = $(this).val();
            checkPasswordStrength(password);
            checkPasswordRequirements(password);
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
                $('#new_password').closest('.input-group').after(`
                    <div id="passwordStrength" class="password-strength ${strengthClass}"></div>
                    <div class="form-text mt-1"><small>パスワード強度: <span id="strengthText">${strengthText}</span></small></div>
                `);
            } else if (password.length > 0) {
                strengthBar.removeClass('strength-weak strength-medium strength-strong').addClass(strengthClass);
                $('#strengthText').text(strengthText);
            } else {
                strengthBar.remove();
                $('#strengthText').parent().remove();
            }
        }
        
        function checkPasswordRequirements(password) {
            // 長さチェック
            if (password.length >= 8) {
                $('#req-length').addClass('requirement-met');
            } else {
                $('#req-length').removeClass('requirement-met');
            }
            
            // 英字チェック
            if (/[a-zA-Z]/.test(password)) {
                $('#req-letter').addClass('requirement-met');
            } else {
                $('#req-letter').removeClass('requirement-met');
            }
            
            // 数字チェック
            if (/\d/.test(password)) {
                $('#req-number').addClass('requirement-met');
            } else {
                $('#req-number').removeClass('requirement-met');
            }
            
            // 特殊文字チェック
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                $('#req-special').addClass('requirement-met');
            } else {
                $('#req-special').removeClass('requirement-met');
            }
        }
        
        // パスワード一致チェック
        $('#new_password_confirmation').on('input', function() {
            const newPassword = $('#new_password').val();
            const confirmPassword = $(this).val();
            const matchDiv = $('#passwordMatch');
            
            if (confirmPassword.length > 0) {
                if (newPassword === confirmPassword) {
                    matchDiv.html('<small class="text-success"><i class="bi bi-check-circle me-1"></i>パスワードが一致しています</small>');
                } else {
                    matchDiv.html('<small class="text-danger"><i class="bi bi-x-circle me-1"></i>パスワードが一致しません</small>');
                }
            } else {
                matchDiv.html('');
            }
        });
        
        // フォーム送信時の処理
        $('#changePasswordForm').on('submit', function(e) {
            const newPassword = $('#new_password').val();
            const confirmPassword = $('#new_password_confirmation').val();
            
            // パスワード一致チェック
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                showErrorMessage('新しいパスワードが一致しません');
                return false;
            }
            
            // パスワード強度チェック
            if (newPassword.length < 8) {
                e.preventDefault();
                showErrorMessage('パスワードは8文字以上で入力してください');
                return false;
            }
            
            // ローディング状態に切り替え
            const submitBtn = $(this).find('button[type="submit"]');
            const spinner = submitBtn.find('.spinner-border');
            
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');
        });
        
        // 現在のパスワードと新しいパスワードの同一性チェック
        $('#new_password').on('blur', function() {
            const currentPassword = $('#current_password').val();
            const newPassword = $(this).val();
            
            if (currentPassword && newPassword && currentPassword === newPassword) {
                showErrorMessage('新しいパスワードは現在のパスワードと異なるものを設定してください');
                $(this).focus();
            }
        });
    });
</script>
@endpush
@endsection
