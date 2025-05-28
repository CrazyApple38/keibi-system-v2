@extends('layouts.app')

@section('title', 'パスワード変更 - 警備統合管理システム')

@section('content')
<div class="container">
    <!-- パンくずリスト -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard.index') }}">
                    <i class="fas fa-home me-1"></i>ダッシュボード
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard.profile') }}">
                    <i class="fas fa-user me-1"></i>プロフィール
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-key me-1"></i>パスワード変更
            </li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- セキュリティステータス表示 -->
            <div class="alert alert-info border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-shield-alt me-3 fa-2x"></i>
                    <div>
                        <h6 class="alert-heading mb-1">パスワードセキュリティ状況</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    最終変更: {{ Auth::user()->password_changed_at ? Auth::user()->password_changed_at->format('Y年m月d日 H:i') : '初回ログイン' }}
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    強度レベル: 
                                    <span class="badge bg-success">
                                        <i class="fas fa-star me-1"></i>高
                                    </span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- パスワード有効期限警告 -->
            @if(Auth::user()->password_expires_at && Auth::user()->password_expires_at->diffInDays() <= 30)
                <div class="alert alert-warning border-left-warning mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <strong>パスワード有効期限通知</strong><br>
                            パスワードの有効期限まで残り {{ Auth::user()->password_expires_at->diffInDays() }} 日です。
                            セキュリティ向上のため、定期的な変更をお勧めします。
                        </div>
                    </div>
                </div>
            @endif

            <div class="card shadow-lg border-0 security-card">
                <div class="card-header py-4 bg-gradient-primary">
                    <div class="d-flex align-items-center">
                        <div class="security-icon me-3">
                            <i class="fas fa-key fa-2x text-white"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 text-white fw-bold">パスワード変更</h4>
                            <p class="mb-0 text-white-75">
                                <i class="fas fa-shield-check me-1"></i>
                                警備業法準拠セキュリティ管理
                            </p>
                        </div>
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

                    @if (session('success'))
                        <div class="alert alert-success border-left-success">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <div>
                                    <strong>変更完了</strong><br>
                                    {{ session('success') }}
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- セキュリティ情報 -->
                    <div class="security-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="status-indicator status-secure me-2"></div>
                                    <small class="text-muted">セキュリティ監視: アクティブ</small>
                                </div>
                                <div class="d-flex align-items-center mt-1">
                                    <div class="status-indicator status-compliant me-2"></div>
                                    <small class="text-muted">コンプライアンス: 準拠</small>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="d-flex align-items-center justify-content-md-end">
                                    <i class="fas fa-user-shield me-1 text-primary"></i>
                                    <small class="text-muted">{{ Auth::user()->name }}</small>
                                </div>
                                <div class="d-flex align-items-center justify-content-md-end mt-1">
                                    <i class="fas fa-building me-1 text-muted"></i>
                                    <small class="text-muted">{{ Auth::user()->company->name ?? '所属会社' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('auth.password.change') }}" id="changePasswordForm">
                        @csrf
                        
                        <!-- 現在のパスワード確認セクション -->
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-user-check me-2 text-warning"></i>本人確認
                                </h6>
                            </div>
                            <div class="section-body">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label fw-semibold">
                                        <i class="fas fa-lock me-1"></i>
                                        現在のパスワード <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-key"></i>
                                        </span>
                                        <input type="password" 
                                               class="form-control @error('current_password') is-invalid @enderror" 
                                               id="current_password" 
                                               name="current_password" 
                                               required
                                               autocomplete="current-password"
                                               placeholder="現在のパスワードを入力">
                                        <button type="button" class="btn btn-outline-secondary" id="toggleCurrentPassword">
                                            <i class="fas fa-eye" id="currentPasswordIcon"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        セキュリティ確保のため現在のパスワードの入力が必要です
                                    </div>
                                </div>

                                <!-- 2段階認証（管理者・責任者のみ） -->
                                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'manager')
                                <div class="mb-3">
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
                                               placeholder="6桁のコード">
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                        管理者権限のため2段階認証が必要です
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- 新しいパスワード設定セクション -->
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-lock me-2 text-success"></i>新しいパスワード設定
                                </h6>
                            </div>
                            <div class="section-body">
                                <!-- 新しいパスワード -->
                                <div class="mb-3">
                                    <label for="new_password" class="form-label fw-semibold">
                                        <i class="fas fa-key me-1"></i>
                                        新しいパスワード <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-plus-circle"></i>
                                        </span>
                                        <input type="password" 
                                               class="form-control @error('new_password') is-invalid @enderror" 
                                               id="new_password" 
                                               name="new_password" 
                                               required
                                               autocomplete="new-password"
                                               placeholder="8文字以上の強固なパスワード">
                                        <button type="button" class="btn btn-outline-secondary" id="toggleNewPassword">
                                            <i class="fas fa-eye" id="newPasswordIcon"></i>
                                        </button>
                                    </div>
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- 新しいパスワード確認 -->
                                <div class="mb-3">
                                    <label for="new_password_confirmation" class="form-label fw-semibold">
                                        <i class="fas fa-check-double me-1"></i>
                                        新しいパスワード（確認） <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-check-circle"></i>
                                        </span>
                                        <input type="password" 
                                               class="form-control" 
                                               id="new_password_confirmation" 
                                               name="new_password_confirmation" 
                                               required
                                               autocomplete="new-password"
                                               placeholder="新しいパスワードを再入力">
                                        <button type="button" class="btn btn-outline-secondary" id="toggleNewPasswordConfirm">
                                            <i class="fas fa-eye" id="newPasswordConfirmIcon"></i>
                                        </button>
                                    </div>
                                    <div id="passwordMatch" class="form-text"></div>
                                </div>

                                <!-- パスワード有効期限設定 -->
                                <div class="mb-3">
                                    <label for="password_expiry" class="form-label fw-semibold">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        パスワード有効期限
                                    </label>
                                    <select class="form-select" id="password_expiry" name="password_expiry">
                                        <option value="90">90日後（推奨）</option>
                                        <option value="60">60日後（高セキュリティ）</option>
                                        <option value="30">30日後（最高セキュリティ）</option>
                                        <option value="never">無期限（非推奨）</option>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        警備業法に基づき定期的なパスワード変更を推奨します
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- パスワード要件表示 -->
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-shield-check me-2 text-info"></i>パスワード要件・強度
                                </h6>
                            </div>
                            <div class="section-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-2">必須要件</h6>
                                        <ul class="list-unstyled">
                                            <li id="req-length" class="text-muted">
                                                <i class="fas fa-circle me-1"></i>8文字以上
                                            </li>
                                            <li id="req-letter" class="text-muted">
                                                <i class="fas fa-circle me-1"></i>英字を含む
                                            </li>
                                            <li id="req-number" class="text-muted">
                                                <i class="fas fa-circle me-1"></i>数字を含む
                                            </li>
                                            <li id="req-special" class="text-muted">
                                                <i class="fas fa-circle me-1"></i>特殊文字を含む（推奨）
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-2">セキュリティ強度</h6>
                                        <div id="strengthMeter" class="strength-meter mb-2">
                                            <div class="strength-bar"></div>
                                        </div>
                                        <div id="strengthText" class="text-muted">パスワードを入力してください</div>
                                        
                                        <div class="mt-3">
                                            <h6 class="mb-2">履歴チェック</h6>
                                            <div id="historyCheck" class="text-muted">
                                                <i class="fas fa-history me-1"></i>過去のパスワードとの重複確認中...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- パスワード変更オプション -->
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-cog me-2 text-secondary"></i>変更オプション
                                </h6>
                            </div>
                            <div class="section-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="logout_all_devices" 
                                                   name="logout_all_devices">
                                            <label class="form-check-label" for="logout_all_devices">
                                                <i class="fas fa-sign-out-alt me-1"></i>
                                                すべてのデバイスからログアウト
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="notify_admin" 
                                                   name="notify_admin"
                                                   checked>
                                            <label class="form-check-label" for="notify_admin">
                                                <i class="fas fa-bell me-1"></i>
                                                管理者に通知
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="require_immediate_login" 
                                                   name="require_immediate_login">
                                            <label class="form-check-label" for="require_immediate_login">
                                                <i class="fas fa-shield-alt me-1"></i>
                                                即座に再ログイン要求
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="audit_log" 
                                                   name="audit_log"
                                                   checked>
                                            <label class="form-check-label" for="audit_log">
                                                <i class="fas fa-clipboard-list me-1"></i>
                                                詳細監査ログ記録
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- 変更理由 -->
                                <div class="mb-3">
                                    <label for="change_reason" class="form-label fw-semibold">
                                        <i class="fas fa-comment me-1"></i>
                                        変更理由
                                    </label>
                                    <select class="form-select" id="change_reason" name="change_reason">
                                        <option value="regular">定期変更</option>
                                        <option value="security">セキュリティ向上</option>
                                        <option value="policy">セキュリティポリシー準拠</option>
                                        <option value="suspected_compromise">不正アクセスの疑い</option>
                                        <option value="forgot">パスワード忘れ</option>
                                        <option value="admin_request">管理者要請</option>
                                        <option value="other">その他</option>
                                    </select>
                                </div>

                                <!-- その他の理由詳細 -->
                                <div class="mb-3 d-none" id="otherReasonSection">
                                    <label for="other_reason_detail" class="form-label fw-semibold">
                                        <i class="fas fa-edit me-1"></i>
                                        詳細説明
                                    </label>
                                    <textarea class="form-control" 
                                              id="other_reason_detail" 
                                              name="other_reason_detail" 
                                              rows="3"
                                              placeholder="パスワード変更の詳細な理由を記入してください"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 変更ボタン -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg secure-btn">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                <i class="fas fa-shield-check me-2"></i>
                                セキュアパスワード変更
                            </button>
                        </div>

                        <!-- アクションリンク -->
                        <div class="text-center mt-4">
                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ route('dashboard.index') }}" class="text-decoration-none">
                                        <i class="fas fa-arrow-left me-1"></i>
                                        ダッシュボードに戻る
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('dashboard.profile') }}" class="text-decoration-none">
                                        <i class="fas fa-user me-1"></i>
                                        プロフィール設定
                                    </a>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#securityTipsModal">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        セキュリティのヒント
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
                                <i class="fas fa-history me-1"></i>
                                最終変更: {{ Auth::user()->password_changed_at ? Auth::user()->password_changed_at->format('m/d H:i') : '初回' }}
                            </small>
                        </div>
                        <div class="col-md-4 text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-check me-1"></i>
                                ISO27001準拠管理
                            </small>
                        </div>
                        <div class="col-md-4 text-center text-md-end">
                            <small class="text-muted">
                                <i class="fas fa-eye me-1"></i>
                                セキュリティ監視中
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- パスワード履歴・統計情報 -->
            <div class="security-info mt-4">
                <div class="card bg-dark text-white">
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="mb-2">
                                    <i class="fas fa-chart-line me-1"></i>
                                    パスワードセキュリティ統計
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between">
                                            <span>変更回数:</span>
                                            <span class="fw-bold">{{ Auth::user()->password_changes_count ?? 0 }}回</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>平均強度:</span>
                                            <span class="fw-bold text-success">高</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between">
                                            <span>次回推奨変更:</span>
                                            <span class="fw-bold">{{ Auth::user()->password_expires_at ? Auth::user()->password_expires_at->format('m/d') : '未設定' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>コンプライアンス:</span>
                                            <span class="fw-bold text-success">準拠</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h6 class="mb-2">
                                    <i class="fas fa-award me-1"></i>
                                    セキュリティレベル
                                </h6>
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-star me-1"></i>
                                    A+グレード
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- セキュリティのヒントモーダル -->
<div class="modal fade" id="securityTipsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-lightbulb me-2"></i>パスワードセキュリティのヒント
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>警備業界特有のセキュリティ要件</strong><br>
                    警備業法に基づき、特に厳格なパスワード管理が求められます
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-check-circle me-1 text-success"></i>推奨パスワード</h6>
                        <ul class="small">
                            <li>英数字と特殊文字の組み合わせ</li>
                            <li>個人情報を含まない</li>
                            <li>辞書にない文字列</li>
                            <li>パスフレーズの活用</li>
                            <li>定期的な変更（90日推奨）</li>
                        </ul>

                        <h6><i class="fas fa-shield-alt me-1 text-primary"></i>管理のヒント</h6>
                        <ul class="small">
                            <li>パスワード管理ツールの活用</li>
                            <li>複数サービスでの使い回し禁止</li>
                            <li>メモや付箋への記載禁止</li>
                            <li>定期的な強度チェック</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-times-circle me-1 text-danger"></i>避けるべきパスワード</h6>
                        <ul class="small">
                            <li>生年月日、電話番号</li>
                            <li>簡単な単語（password123等）</li>
                            <li>連続した文字（abc123, 111111等）</li>
                            <li>会社名や部署名</li>
                            <li>家族やペットの名前</li>
                        </ul>

                        <h6><i class="fas fa-exclamation-triangle me-1 text-warning"></i>緊急時の対応</h6>
                        <ul class="small">
                            <li>不正アクセスを発見した場合は即座に変更</li>
                            <li>フィッシング詐欺に注意</li>
                            <li>公共Wi-Fi使用時の注意</li>
                            <li>定期的なセキュリティチェック</li>
                        </ul>
                    </div>
                </div>

                <div class="alert alert-warning mt-3">
                    <h6><i class="fas fa-gavel me-1"></i>法令遵守事項</h6>
                    <p class="mb-0">警備業法第14条に基づく身元確認の一環として、パスワード管理状況も監査対象となります。適切な管理をお願いします。</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>閉じる
                </button>
                <button type="button" class="btn btn-primary" onclick="downloadSecurityGuide()">
                    <i class="fas fa-download me-1"></i>セキュリティガイドをダウンロード
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
body {
    background: linear-gradient(135deg, #3b82f6 0%, #1e40af 50%, #1e3a8a 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

.bg-gradient-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #1e40af 50%, #1e3a8a 100%) !important;
    position: relative;
}

.security-icon {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    padding: 15px;
    border: 2px solid rgba(255, 255, 255, 0.2);
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
    background: linear-gradient(135deg, #3b82f6 0%, #1e40af 50%, #1e3a8a 100%);
    border: none;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.secure-btn:hover {
    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 50%, #1e3a8a 100%);
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(59, 130, 246, 0.4);
}

.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}

.status-secure {
    background: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.3);
    animation: pulse 2s infinite;
}

.status-compliant {
    background: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
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

.strength-meter {
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.strength-bar {
    height: 100%;
    width: 0%;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.strength-weak .strength-bar {
    width: 25%;
    background: linear-gradient(90deg, #ef4444, #f87171);
}

.strength-fair .strength-bar {
    width: 50%;
    background: linear-gradient(90deg, #f59e0b, #fbbf24);
}

.strength-good .strength-bar {
    width: 75%;
    background: linear-gradient(90deg, #22c55e, #4ade80);
}

.strength-excellent .strength-bar {
    width: 100%;
    background: linear-gradient(90deg, #059669, #10b981);
}

.requirement-met {
    color: #22c55e !important;
}

.requirement-met .fa-circle {
    display: none;
}

.requirement-met::before {
    content: '✓';
    margin-right: 0.5rem;
    font-weight: bold;
    color: #22c55e;
}

.border-left-danger {
    border-left: 5px solid #ef4444;
}

.border-left-info {
    border-left: 5px solid #3b82f6;
}

.border-left-warning {
    border-left: 5px solid #f59e0b;
}

.border-left-success {
    border-left: 5px solid #22c55e;
}

.security-info .card {
    background: linear-gradient(135deg, #1f2937 0%, #374151 50%, #1f2937 100%) !important;
    border: none;
    border-radius: 15px;
}

.text-white-75 {
    color: rgba(255, 255, 255, 0.85) !important;
}

.breadcrumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 0.75rem 1rem;
}

.breadcrumb-item > a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
}

.breadcrumb-item > a:hover {
    color: white;
}

.breadcrumb-item.active {
    color: rgba(255, 255, 255, 0.6);
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

.security-card {
    animation: fadeInUp 0.8s ease-out;
}

.section-card {
    animation: fadeInUp 0.6s ease-out;
}

.security-info {
    animation: fadeInUp 1s ease-out 0.2s both;
}

.alert {
    animation: fadeInUp 0.5s ease-out;
}

/* セクション表示アニメーション */
.section-card:nth-child(1) { animation-delay: 0.1s; }
.section-card:nth-child(2) { animation-delay: 0.2s; }
.section-card:nth-child(3) { animation-delay: 0.3s; }
.section-card:nth-child(4) { animation-delay: 0.4s; }

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
    
    .btn, .modal {
        display: none !important;
    }
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
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    }
    
    // パスワード強度チェック
    $('#new_password').on('input', function() {
        const password = $(this).val();
        checkPasswordStrength(password);
        checkPasswordRequirements(password);
        checkPasswordHistory(password);
    });
    
    function checkPasswordStrength(password) {
        let score = 0;
        let strengthText = '';
        let strengthClass = '';
        
        if (password.length === 0) {
            strengthText = 'パスワードを入力してください';
            strengthClass = '';
        } else {
            // 長さチェック
            if (password.length >= 8) score += 1;
            if (password.length >= 12) score += 1;
            
            // 文字種チェック
            if (/[a-z]/.test(password)) score += 1;
            if (/[A-Z]/.test(password)) score += 1;
            if (/\d/.test(password)) score += 1;
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score += 1;
            
            // 複雑性チェック
            if (!/(.)\1{2,}/.test(password)) score += 1; // 連続文字なし
            if (!/123|abc|qwe|asd/i.test(password)) score += 1; // 連続パターンなし
            
            switch (true) {
                case score <= 2:
                    strengthText = '弱い';
                    strengthClass = 'strength-weak';
                    break;
                case score <= 4:
                    strengthText = '普通';
                    strengthClass = 'strength-fair';
                    break;
                case score <= 6:
                    strengthText = '良い';
                    strengthClass = 'strength-good';
                    break;
                case score >= 7:
                    strengthText = '非常に強い';
                    strengthClass = 'strength-excellent';
                    break;
            }
        }
        
        // 強度メーターを更新
        $('#strengthMeter').removeClass('strength-weak strength-fair strength-good strength-excellent').addClass(strengthClass);
        $('#strengthText').text(`強度: ${strengthText}`);
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
    
    function checkPasswordHistory(password) {
        if (password.length < 8) {
            $('#historyCheck').html('<i class="fas fa-history me-1"></i>パスワードを入力してください');
            return;
        }
        
        // サーバーサイドで過去のパスワードとの重複チェック
        const hashedPassword = btoa(password); // 簡易ハッシュ（実際はサーバーサイドで）
        
        fetch('/auth/check-password-history', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({
                password_hash: hashedPassword
            })
        }).then(response => response.json())
        .then(data => {
            if (data.is_duplicate) {
                $('#historyCheck').html('<i class="fas fa-times-circle me-1 text-danger"></i>過去のパスワードと重複しています');
            } else {
                $('#historyCheck').html('<i class="fas fa-check-circle me-1 text-success"></i>過去のパスワードと重複していません');
            }
        }).catch(error => {
            $('#historyCheck').html('<i class="fas fa-exclamation-triangle me-1 text-warning"></i>履歴チェック中にエラーが発生しました');
        });
    }
    
    // パスワード一致チェック
    $('#new_password_confirmation').on('input', function() {
        const newPassword = $('#new_password').val();
        const confirmPassword = $(this).val();
        const matchDiv = $('#passwordMatch');
        
        if (confirmPassword.length > 0) {
            if (newPassword === confirmPassword) {
                matchDiv.html('<small class="text-success"><i class="fas fa-check-circle me-1"></i>パスワードが一致しています</small>');
            } else {
                matchDiv.html('<small class="text-danger"><i class="fas fa-times-circle me-1"></i>パスワードが一致しません</small>');
            }
        } else {
            matchDiv.html('');
        }
    });

    // 変更理由変更時の処理
    $('#change_reason').change(function() {
        const selectedReason = $(this).val();
        
        if (selectedReason === 'other') {
            $('#otherReasonSection').removeClass('d-none');
            $('#other_reason_detail').attr('required', true);
        } else {
            $('#otherReasonSection').addClass('d-none');
            $('#other_reason_detail').attr('required', false);
        }

        // セキュリティ関連の理由の場合は追加オプションを推奨
        if (['suspected_compromise', 'security'].includes(selectedReason)) {
            $('#logout_all_devices').prop('checked', true);
            $('#require_immediate_login').prop('checked', true);
            showAlert('warning', 'セキュリティ上の理由のため、追加のセキュリティオプションが推奨されます。');
        }
    });
    
    // フォーム送信時の処理
    $('#changePasswordForm').on('submit', function(e) {
        const currentPassword = $('#current_password').val();
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#new_password_confirmation').val();
        
        // 基本バリデーション
        if (!currentPassword || !newPassword || !confirmPassword) {
            e.preventDefault();
            showAlert('error', '必須項目をすべて入力してください');
            return false;
        }

        // パスワード一致チェック
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            showAlert('error', '新しいパスワードが一致しません');
            return false;
        }
        
        // パスワード強度チェック
        if (newPassword.length < 8) {
            e.preventDefault();
            showAlert('error', 'パスワードは8文字以上で入力してください');
            return false;
        }

        // 現在のパスワードと新しいパスワードの同一性チェック
        if (currentPassword === newPassword) {
            e.preventDefault();
            showAlert('error', '新しいパスワードは現在のパスワードと異なるものを設定してください');
            return false;
        }

        // 管理者権限の場合の2段階認証チェック
        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'manager')
        const twoFactorCode = $('#two_factor_code').val();
        if (!twoFactorCode || twoFactorCode.length !== 6) {
            e.preventDefault();
            showAlert('error', '管理者権限のため2段階認証コードが必要です');
            return false;
        }
        @endif

        // 確認ダイアログ
        const logoutAllDevices = $('#logout_all_devices').is(':checked');
        const requireLogin = $('#require_immediate_login').is(':checked');
        
        let confirmMessage = 'パスワードを変更します。よろしいですか？';
        if (logoutAllDevices) {
            confirmMessage += '\n\n※ すべてのデバイスからログアウトされます。';
        }
        if (requireLogin) {
            confirmMessage += '\n※ 変更後、即座に再ログインが必要になります。';
        }
        
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
        
        // ローディング状態に切り替え
        const submitBtn = $(this).find('button[type="submit"]');
        const spinner = submitBtn.find('.spinner-border');
        const btnIcon = submitBtn.find('i.fa-shield-check');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        btnIcon.removeClass('fa-shield-check').addClass('fa-spinner fa-spin');

        // セキュリティログ記録
        logSecurityEvent('password_change_attempt', {
            user_id: '{{ Auth::id() }}',
            user_email: '{{ Auth::user()->email }}',
            change_reason: $('#change_reason').val(),
            logout_all_devices: logoutAllDevices,
            require_immediate_login: requireLogin,
            notify_admin: $('#notify_admin').is(':checked'),
            audit_log: $('#audit_log').is(':checked'),
            timestamp: new Date().toISOString(),
            user_agent: navigator.userAgent,
            ip_address: '{{ request()->ip() }}'
        });
    });
    
    // 現在のパスワードと新しいパスワードの同一性チェック
    $('#new_password').on('blur', function() {
        const currentPassword = $('#current_password').val();
        const newPassword = $(this).val();
        
        if (currentPassword && newPassword && currentPassword === newPassword) {
            showAlert('error', '新しいパスワードは現在のパスワードと異なるものを設定してください');
            $(this).focus();
        }
    });

    // アラート表示関数
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
        const iconClass = type === 'success' ? 'fa-check-circle' : (type === 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle');
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('.container .row .col-lg-8').prepend(alertHtml);
        
        // 5秒後に自動で閉じる
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }

    // セキュリティイベントログ
    function logSecurityEvent(event, data) {
        const logEntry = {
            event: event,
            timestamp: new Date().toISOString(),
            session_id: sessionStorage.getItem('session_id') || 'unknown',
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
    }

    // 初期化時のログ記録
    logSecurityEvent('password_change_page_load', {
        user_id: '{{ Auth::id() }}',
        page: 'password-change',
        user_agent: navigator.userAgent
    });
});

// セキュリティガイドダウンロード
function downloadSecurityGuide() {
    const link = document.createElement('a');
    link.href = '/assets/documents/password-security-guide.pdf';
    link.download = 'パスワードセキュリティガイド.pdf';
    link.click();
}
</script>
@endpush
