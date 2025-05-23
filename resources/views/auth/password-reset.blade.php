@extends('layouts.app')

@section('title', 'パスワードリセット')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7 col-sm-9">
            <div class="card shadow-lg border-0">
                <div class="card-header text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-key display-4 text-warning"></i>
                    </div>
                    <h3 class="mb-1">パスワードリセット</h3>
                    <p class="text-muted mb-0">新しいパスワードを設定</p>
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
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        メールアドレスを入力してください。パスワードリセット用のリンクをお送りします。
                    </div>
                    
                    <form method="POST" action="{{ route('auth.password.reset') }}" id="resetForm">
                        @csrf
                        
                        <!-- メールアドレス -->
                        <div class="mb-4">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i>
                                登録済みメールアドレス
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
                            <div class="form-text">
                                <small>システムに登録済みのメールアドレスを入力してください</small>
                            </div>
                        </div>
                        
                        <!-- 送信ボタン -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                <i class="bi bi-envelope-paper me-2"></i>
                                リセットリンクを送信
                            </button>
                        </div>
                        
                        <!-- リンク -->
                        <div class="text-center mt-4">
                            <div class="mb-2">
                                <a href="{{ route('auth.login.form') }}" class="text-decoration-none">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    ログインページに戻る
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
                
                <!-- 注意事項 -->
                <div class="card-footer bg-light py-3">
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            メールが届かない場合は、迷惑メールフォルダもご確認ください
                        </small>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>
                            リセットリンクの有効期限は24時間です
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- サポート情報 -->
            <div class="text-center mt-4">
                <div class="card bg-dark text-white">
                    <div class="card-body py-3">
                        <h6 class="mb-2">
                            <i class="bi bi-headset me-1"></i>
                            お困りの場合
                        </h6>
                        <p class="mb-1">システム管理者にお問い合わせください</p>
                        <small>
                            <i class="bi bi-telephone me-1"></i>
                            緊急時: 080-1234-5678
                        </small>
                    </div>
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
        box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25);
        border-color: #f59e0b;
    }
    
    .btn-warning {
        padding: 12px;
        font-weight: 600;
        border-radius: 8px;
        color: white;
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
    
    .alert-info {
        border-left: 4px solid #3b82f6;
        border-radius: 8px;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // フォーム送信時の処理
        $('#resetForm').on('submit', function() {
            const submitBtn = $(this).find('button[type="submit"]');
            const spinner = submitBtn.find('.spinner-border');
            
            // ローディング状態に切り替え
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');
        });
        
        // エンターキーで送信
        $(document).keypress(function(e) {
            if (e.which == 13) {
                $('#resetForm').submit();
            }
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
    });
</script>
@endpush
@endsection
