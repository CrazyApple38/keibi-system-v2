@extends('layouts.app')

@section('title', 'ログイン')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7 col-sm-9">
            <div class="card shadow-lg border-0">
                <div class="card-header text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-shield-check display-4 text-primary"></i>
                    </div>
                    <h3 class="mb-1">ログイン</h3>
                    <p class="text-muted mb-0">警備システムにアクセス</p>
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
                    
                    <form method="POST" action="{{ route('auth.login') }}" id="loginForm">
                        @csrf
                        
                        <!-- メールアドレス -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i>
                                メールアドレス
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus
                                   placeholder="example@company.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- パスワード -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-1"></i>
                                パスワード
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required
                                       placeholder="パスワードを入力">
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="bi bi-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- ログイン状態を保持 -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="remember" 
                                       name="remember"
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    ログイン状態を保持する
                                </label>
                            </div>
                        </div>
                        
                        <!-- ログインボタン -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                ログイン
                            </button>
                        </div>
                        
                        <!-- リンク -->
                        <div class="text-center mt-4">
                            <div class="mb-2">
                                <a href="{{ route('auth.password.reset.form') }}" class="text-decoration-none">
                                    <i class="bi bi-question-circle me-1"></i>
                                    パスワードを忘れた方
                                </a>
                            </div>
                            <div>
                                <span class="text-muted">アカウントをお持ちでない方は</span>
                                <a href="{{ route('auth.register.form') }}" class="text-decoration-none">
                                    <i class="bi bi-person-plus me-1"></i>
                                    ユーザー登録
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- フッター情報 -->
                <div class="card-footer text-center bg-light py-3">
                    <small class="text-muted">
                        <i class="bi bi-shield-lock me-1"></i>
                        安全な暗号化通信で保護されています
                    </small>
                </div>
            </div>
            
            <!-- 対象会社表示 -->
            <div class="text-center mt-4">
                <div class="mb-2">
                    <small class="text-muted">対象会社</small>
                </div>
                <div class="d-flex justify-content-center gap-2">
                    <span class="badge bg-primary">㈲東央警備</span>
                    <span class="badge bg-success">㈱Nikkeiホールディングス</span>
                    <span class="badge bg-warning">㈱全日本エンタープライズ</span>
                </div>
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
        min-height: calc(100vh - 200px);
    }
    
    .card {
        border-radius: 15px;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
    }
    
    .form-control {
        border-radius: 8px;
        padding: 12px 15px;
    }
    
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        border-color: #3b82f6;
    }
    
    .btn-primary {
        padding: 12px;
        font-weight: 600;
        border-radius: 8px;
    }
    
    .input-group .btn {
        border-radius: 0 8px 8px 0;
    }
    
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
    
    .badge {
        animation: fadeInUp 0.8s ease-out;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // パスワード表示切り替え
        $('#togglePassword').click(function() {
            const passwordInput = $('#password');
            const passwordIcon = $('#passwordIcon');
            
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                passwordIcon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                passwordInput.attr('type', 'password');
                passwordIcon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });
        
        // フォーム送信時の処理
        $('#loginForm').on('submit', function() {
            const submitBtn = $(this).find('button[type="submit"]');
            const spinner = submitBtn.find('.spinner-border');
            
            // ローディング状態に切り替え
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');
        });
        
        // エンターキーでログイン
        $(document).keypress(function(e) {
            if (e.which == 13) {
                $('#loginForm').submit();
            }
        });
        
        // フォーカス時のアニメーション
        $('.form-control').focus(function() {
            $(this).parent().addClass('focused');
        }).blur(function() {
            $(this).parent().removeClass('focused');
        });
    });
</script>
@endpush
@endsection
