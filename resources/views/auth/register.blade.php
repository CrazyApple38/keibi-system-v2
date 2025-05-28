@extends('layouts.app')

@section('title', 'ユーザー登録 - 警備統合管理システム')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- 登録前注意事項 -->
            <div class="alert alert-warning border-left-warning mb-4">
                <div class="d-flex">
                    <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading">重要: 警備業務従事者登録について</h6>
                        <p class="mb-2">本システムへの登録には以下の要件があります：</p>
                        <ul class="mb-2">
                            <li>警備業法に基づく身元確認書類の提出</li>
                            <li>管理者による身元調査・背景確認</li>
                            <li>所属会社の事前承認</li>
                            <li>システム利用規約への同意</li>
                        </ul>
                        <small class="text-muted">登録完了後、管理者承認まで1-3営業日かかります。</small>
                    </div>
                </div>
            </div>

            <div class="card shadow-lg border-0">
                <div class="card-header text-center py-4 bg-gradient-primary">
                    <div class="mb-3">
                        <div class="security-logo">
                            <i class="fas fa-user-shield display-4 text-white"></i>
                            <div class="security-badge">
                                <i class="fas fa-plus text-success"></i>
                            </div>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold text-white">警備員アカウント登録</h3>
                    <p class="text-white-50 mb-0">セキュアユーザー登録システム</p>
                </div>
                
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger border-left-danger">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-circle me-2 mt-1"></i>
                                <div>
                                    <strong>入力エラーが発生しました</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- 登録進捗表示 -->
                    <div class="registration-progress mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary">ステップ 1/3</span>
                            <small class="text-muted">基本情報入力</small>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: 33%"></div>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('auth.register') }}" id="registerForm" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- 基本個人情報セクション -->
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user me-2 text-primary"></i>基本個人情報
                                </h5>
                            </div>
                            <div class="section-body">
                                <div class="row">
                                    <!-- 姓 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label required">
                                            <i class="fas fa-user me-1"></i>姓 <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('last_name') is-invalid @enderror" 
                                               id="last_name" 
                                               name="last_name" 
                                               value="{{ old('last_name') }}" 
                                               required 
                                               autofocus
                                               placeholder="山田">
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- 名 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label required">
                                            <i class="fas fa-user me-1"></i>名 <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('first_name') is-invalid @enderror" 
                                               id="first_name" 
                                               name="first_name" 
                                               value="{{ old('first_name') }}" 
                                               required
                                               placeholder="太郎">
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- 姓（カナ） -->
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name_kana" class="form-label required">
                                            <i class="fas fa-font me-1"></i>姓（カナ） <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('last_name_kana') is-invalid @enderror" 
                                               id="last_name_kana" 
                                               name="last_name_kana" 
                                               value="{{ old('last_name_kana') }}" 
                                               required
                                               placeholder="ヤマダ">
                                        @error('last_name_kana')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- 名（カナ） -->
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name_kana" class="form-label required">
                                            <i class="fas fa-font me-1"></i>名（カナ） <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('first_name_kana') is-invalid @enderror" 
                                               id="first_name_kana" 
                                               name="first_name_kana" 
                                               value="{{ old('first_name_kana') }}" 
                                               required
                                               placeholder="タロウ">
                                        @error('first_name_kana')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- 生年月日 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="birth_date" class="form-label required">
                                            <i class="fas fa-calendar me-1"></i>生年月日 <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" 
                                               class="form-control @error('birth_date') is-invalid @enderror" 
                                               id="birth_date" 
                                               name="birth_date" 
                                               value="{{ old('birth_date') }}" 
                                               required
                                               max="{{ date('Y-m-d', strtotime('-18 years')) }}">
                                        @error('birth_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">18歳以上である必要があります</div>
                                    </div>

                                    <!-- 性別 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="gender" class="form-label">
                                            <i class="fas fa-venus-mars me-1"></i>性別
                                        </label>
                                        <select class="form-select @error('gender') is-invalid @enderror" 
                                                id="gender" 
                                                name="gender">
                                            <option value="">選択してください</option>
                                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>男性</option>
                                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>女性</option>
                                            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>その他</option>
                                            <option value="prefer_not_to_say" {{ old('gender') == 'prefer_not_to_say' ? 'selected' : '' }}>回答しない</option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 職業・所属情報セクション -->
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-building me-2 text-success"></i>職業・所属情報
                                </h5>
                            </div>
                            <div class="section-body">
                                <div class="row">
                                    <!-- 社員ID -->
                                    <div class="col-md-6 mb-3">
                                        <label for="employee_id" class="form-label required">
                                            <i class="fas fa-id-card me-1"></i>社員ID <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('employee_id') is-invalid @enderror" 
                                               id="employee_id" 
                                               name="employee_id" 
                                               value="{{ old('employee_id') }}" 
                                               required
                                               pattern="[A-Z]{2,3}[0-9]{3,4}"
                                               placeholder="EMP001">
                                        @error('employee_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">形式: 英字2-3文字 + 数字3-4桁（例: EMP001）</div>
                                    </div>
                                    
                                    <!-- 所属会社 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="company_id" class="form-label required">
                                            <i class="fas fa-building me-1"></i>所属会社 <span class="text-danger">*</span>
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
                                </div>

                                <div class="row">
                                    <!-- 部署 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="department" class="form-label">
                                            <i class="fas fa-sitemap me-1"></i>部署
                                        </label>
                                        <select class="form-select @error('department') is-invalid @enderror" 
                                                id="department" 
                                                name="department">
                                            <option value="">選択してください</option>
                                            <option value="security" {{ old('department') == 'security' ? 'selected' : '' }}>警備部</option>
                                            <option value="management" {{ old('department') == 'management' ? 'selected' : '' }}>管理部</option>
                                            <option value="sales" {{ old('department') == 'sales' ? 'selected' : '' }}>営業部</option>
                                            <option value="operations" {{ old('department') == 'operations' ? 'selected' : '' }}>運営部</option>
                                            <option value="training" {{ old('department') == 'training' ? 'selected' : '' }}>教育訓練部</option>
                                        </select>
                                        @error('department')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- 役職 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="position" class="form-label">
                                            <i class="fas fa-user-tie me-1"></i>役職
                                        </label>
                                        <select class="form-select @error('position') is-invalid @enderror" 
                                                id="position" 
                                                name="position">
                                            <option value="">選択してください</option>
                                            <option value="guard" {{ old('position') == 'guard' ? 'selected' : '' }}>警備員</option>
                                            <option value="senior_guard" {{ old('position') == 'senior_guard' ? 'selected' : '' }}>主任警備員</option>
                                            <option value="supervisor" {{ old('position') == 'supervisor' ? 'selected' : '' }}>警備主任</option>
                                            <option value="manager" {{ old('position') == 'manager' ? 'selected' : '' }}>管理職</option>
                                            <option value="admin" {{ old('position') == 'admin' ? 'selected' : '' }}>システム管理者</option>
                                        </select>
                                        @error('position')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- 警備業務経験 -->
                                <div class="mb-3">
                                    <label for="security_experience" class="form-label">
                                        <i class="fas fa-medal me-1"></i>警備業務経験年数
                                    </label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control @error('security_experience') is-invalid @enderror" 
                                               id="security_experience" 
                                               name="security_experience" 
                                               value="{{ old('security_experience') }}" 
                                               min="0" 
                                               max="50"
                                               placeholder="0">
                                        <span class="input-group-text">年</span>
                                    </div>
                                    @error('security_experience')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 連絡先情報セクション -->
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-address-book me-2 text-info"></i>連絡先情報
                                </h5>
                            </div>
                            <div class="section-body">
                                <!-- メールアドレス -->
                                <div class="mb-3">
                                    <label for="email" class="form-label required">
                                        <i class="fas fa-envelope me-1"></i>メールアドレス <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           required
                                           autocomplete="username"
                                           placeholder="yamada.taro@company.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">会社のメールアドレスを使用してください</div>
                                </div>

                                <div class="row">
                                    <!-- 電話番号 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label required">
                                            <i class="fas fa-phone me-1"></i>電話番号 <span class="text-danger">*</span>
                                        </label>
                                        <input type="tel" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone') }}"
                                               required
                                               placeholder="090-1234-5678">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- 緊急連絡先 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="emergency_contact" class="form-label">
                                            <i class="fas fa-phone-square-alt me-1"></i>緊急連絡先
                                        </label>
                                        <input type="tel" 
                                               class="form-control @error('emergency_contact') is-invalid @enderror" 
                                               id="emergency_contact" 
                                               name="emergency_contact" 
                                               value="{{ old('emergency_contact') }}"
                                               placeholder="03-1234-5678">
                                        @error('emergency_contact')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">家族または保証人の連絡先</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- パスワード設定セクション -->
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-lock me-2 text-warning"></i>パスワード設定
                                </h5>
                            </div>
                            <div class="section-body">
                                <div class="row">
                                    <!-- パスワード -->
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label required">
                                            <i class="fas fa-lock me-1"></i>パスワード <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   class="form-control @error('password') is-invalid @enderror" 
                                                   id="password" 
                                                   name="password" 
                                                   required
                                                   autocomplete="new-password"
                                                   placeholder="8文字以上の強固なパスワード">
                                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                                <i class="fas fa-eye" id="passwordIcon"></i>
                                            </button>
                                        </div>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- パスワード確認 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label required">
                                            <i class="fas fa-lock me-1"></i>パスワード確認 <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="password_confirmation" 
                                                   name="password_confirmation" 
                                                   required
                                                   autocomplete="new-password"
                                                   placeholder="パスワードを再入力">
                                            <button type="button" class="btn btn-outline-secondary" id="togglePasswordConfirm">
                                                <i class="fas fa-eye" id="passwordConfirmIcon"></i>
                                            </button>
                                        </div>
                                        <div id="passwordMatch" class="form-text"></div>
                                    </div>
                                </div>

                                <!-- パスワード要件表示 -->
                                <div class="password-requirements">
                                    <div class="card bg-light">
                                        <div class="card-body py-3">
                                            <h6 class="card-title mb-2">
                                                <i class="fas fa-shield-check me-1"></i>パスワード要件
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled mb-0">
                                                        <li id="req-length" class="text-muted">
                                                            <i class="fas fa-circle me-1"></i>8文字以上
                                                        </li>
                                                        <li id="req-letter" class="text-muted">
                                                            <i class="fas fa-circle me-1"></i>英字を含む
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled mb-0">
                                                        <li id="req-number" class="text-muted">
                                                            <i class="fas fa-circle me-1"></i>数字を含む
                                                        </li>
                                                        <li id="req-special" class="text-muted">
                                                            <i class="fas fa-circle me-1"></i>特殊文字を含む（推奨）
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 資格・証明書セクション -->
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-certificate me-2 text-secondary"></i>資格・証明書（任意）
                                </h5>
                            </div>
                            <div class="section-body">
                                <!-- 警備員検定 -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-award me-1"></i>警備員検定
                                    </label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="cert_1go" name="certifications[]" value="1go">
                                                <label class="form-check-label" for="cert_1go">1号警備</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="cert_2go" name="certifications[]" value="2go">
                                                <label class="form-check-label" for="cert_2go">2号警備</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="cert_3go" name="certifications[]" value="3go">
                                                <label class="form-check-label" for="cert_3go">3号警備</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="cert_4go" name="certifications[]" value="4go">
                                                <label class="form-check-label" for="cert_4go">4号警備</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- その他資格 -->
                                <div class="mb-3">
                                    <label for="other_qualifications" class="form-label">
                                        <i class="fas fa-list me-1"></i>その他の資格
                                    </label>
                                    <textarea class="form-control @error('other_qualifications') is-invalid @enderror" 
                                              id="other_qualifications" 
                                              name="other_qualifications" 
                                              rows="3"
                                              placeholder="例：普通自動車免許、防災士、警備員指導教育責任者など">{{ old('other_qualifications') }}</textarea>
                                    @error('other_qualifications')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 同意事項セクション -->
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-file-contract me-2 text-danger"></i>同意事項
                                </h5>
                            </div>
                            <div class="section-body">
                                <!-- 身元確認同意 -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input @error('identity_verification') is-invalid @enderror" 
                                               id="identity_verification" 
                                               name="identity_verification"
                                               required
                                               {{ old('identity_verification') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="identity_verification">
                                            <strong>身元確認調査への同意</strong> <span class="text-danger">*</span>
                                            <br><small class="text-muted">警備業法第14条に基づく身元確認調査に同意します</small>
                                        </label>
                                        @error('identity_verification')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- 個人情報利用同意 -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input @error('personal_info_consent') is-invalid @enderror" 
                                               id="personal_info_consent" 
                                               name="personal_info_consent"
                                               required
                                               {{ old('personal_info_consent') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="personal_info_consent">
                                            <strong>個人情報の利用同意</strong> <span class="text-danger">*</span>
                                            <br><small class="text-muted">個人情報保護法に基づく個人情報の収集・利用に同意します</small>
                                        </label>
                                        @error('personal_info_consent')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- 利用規約・プライバシーポリシー -->
                                <div class="mb-3">
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

                                <!-- システム利用責任同意 -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input @error('system_responsibility') is-invalid @enderror" 
                                               id="system_responsibility" 
                                               name="system_responsibility"
                                               required
                                               {{ old('system_responsibility') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="system_responsibility">
                                            <strong>システム利用責任への同意</strong> <span class="text-danger">*</span>
                                            <br><small class="text-muted">システムの適切な利用と情報セキュリティの遵守を約束します</small>
                                        </label>
                                        @error('system_responsibility')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 登録ボタン -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg secure-btn" id="submitBtn">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                <i class="fas fa-user-shield me-2"></i>
                                警備員アカウントを登録
                            </button>
                        </div>
                        
                        <!-- 登録後の流れ -->
                        <div class="registration-flow mt-4">
                            <div class="card bg-light">
                                <div class="card-body py-3">
                                    <h6 class="mb-2">
                                        <i class="fas fa-route me-1"></i>登録後の流れ
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-4 text-center">
                                            <i class="fas fa-user-check fa-2x text-primary mb-2"></i>
                                            <div><small>身元確認</small></div>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <i class="fas fa-user-cog fa-2x text-warning mb-2"></i>
                                            <div><small>管理者承認</small></div>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                            <div><small>利用開始</small></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ログインリンク -->
                        <div class="text-center mt-4">
                            <span class="text-muted">既にアカウントをお持ちの方は</span>
                            <a href="{{ route('auth.login.form') }}" class="text-decoration-none">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                ログイン
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- セキュリティ情報フッター -->
                <div class="card-footer bg-light py-3">
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            すべての情報は暗号化されて安全に保存されます
                        </small>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            登録処理には1-3営業日お時間をいただきます
                        </small>
                    </div>
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
                <h5 class="modal-title">
                    <i class="fas fa-file-contract me-2"></i>警備システム利用規約
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>第1条（適用範囲）</h6>
                <p>本規約は、警備グループ統合管理システム（以下「本システム」）の利用に関して、利用者と運営者との間の権利義務関係を定めるものです。</p>
                
                <h6>第2条（警備業務従事者の義務）</h6>
                <p>利用者は警備業法を遵守し、以下の義務を負います：</p>
                <ul>
                    <li>身元確認調査への協力</li>
                    <li>守秘義務の遵守</li>
                    <li>システムの適正利用</li>
                    <li>セキュリティポリシーの遵守</li>
                </ul>
                
                <h6>第3条（禁止事項）</h6>
                <p>利用者は以下の行為を行ってはならないものとします：</p>
                <ul>
                    <li>虚偽の情報の登録・報告</li>
                    <li>不正アクセスやシステムの破壊行為</li>
                    <li>第三者への認証情報の提供</li>
                    <li>業務上知り得た情報の漏洩</li>
                    <li>法令・規則に違反する行為</li>
                </ul>
                
                <h6>第4条（データ管理・監査）</h6>
                <p>システム利用に関するすべての活動は記録・監査され、必要に応じて関係機関に提供されることがあります。</p>
                
                <h6>第5条（責任・賠償）</h6>
                <p>利用者の過失によりシステムや第三者に損害を与えた場合、利用者が責任を負うものとします。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" onclick="$('#terms').prop('checked', true);">同意する</button>
            </div>
        </div>
    </div>
</div>

<!-- プライバシーポリシーモーダル -->
<div class="modal fade" id="privacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-shield me-2"></i>プライバシーポリシー
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>個人情報の収集目的</h6>
                <p>警備業法第14条に基づく身元確認および以下の目的で個人情報を収集します：</p>
                <ul>
                    <li>警備業務従事者としての適格性確認</li>
                    <li>システム利用者管理</li>
                    <li>業務連絡・緊急時対応</li>
                    <li>法令遵守・監査対応</li>
                </ul>
                
                <h6>収集する個人情報</h6>
                <ul>
                    <li>基本情報：氏名、生年月日、性別、住所</li>
                    <li>連絡先：電話番号、メールアドレス</li>
                    <li>職業情報：所属会社、部署、役職、経験年数</li>
                    <li>資格情報：警備員検定、その他資格</li>
                    <li>システム利用ログ：ログイン履歴、操作履歴</li>
                </ul>
                
                <h6>個人情報の管理・保護</h6>
                <p>収集した個人情報は以下の方法で適切に管理します：</p>
                <ul>
                    <li>暗号化による安全な保存</li>
                    <li>アクセス権限の厳格な管理</li>
                    <li>定期的なセキュリティ監査</li>
                    <li>不正アクセス防止対策</li>
                </ul>
                
                <h6>第三者提供</h6>
                <p>以下の場合を除き、個人情報を第三者に提供しません：</p>
                <ul>
                    <li>法令に基づく要求がある場合</li>
                    <li>警備業法に基づく報告義務がある場合</li>
                    <li>本人の同意がある場合</li>
                    <li>公共の安全維持のため必要な場合</li>
                </ul>
                
                <h6>個人情報の開示・訂正・削除</h6>
                <p>本人からの個人情報の開示・訂正・削除の請求に適切に対応いたします。ただし、法令により保存義務がある情報は除きます。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" onclick="$('#personal_info_consent').prop('checked', true);">同意する</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
}

.security-logo {
    position: relative;
    display: inline-block;
}

.security-badge {
    position: absolute;
    bottom: -5px;
    right: -5px;
    background: white;
    border-radius: 50%;
    padding: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section-card {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
}

.section-header {
    background: #f8f9fa;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.section-body {
    padding: 1.5rem;
}

.form-control, .form-select {
    border-radius: 8px;
    padding: 12px 15px;
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 0.2rem rgba(34, 197, 94, 0.25);
    border-color: #22c55e;
    transform: translateY(-1px);
}

.input-group-text {
    border: 2px solid #e5e7eb;
    background: #f8f9fa;
    border-radius: 8px 0 0 8px;
}

.input-group .form-control {
    border-radius: 0;
    border-left: none;
}

.input-group .btn {
    border-radius: 0 8px 8px 0;
    border: 2px solid #e5e7eb;
    border-left: none;
}

.secure-btn {
    padding: 12px 24px;
    font-weight: 600;
    border-radius: 8px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    transition: all 0.3s ease;
}

.secure-btn:hover {
    background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
}

.required {
    font-weight: 600;
}

.border-left-danger {
    border-left: 4px solid #dc3545;
}

.border-left-warning {
    border-left: 4px solid #ffc107;
}

.password-strength {
    height: 4px;
    border-radius: 2px;
    margin-top: 8px;
    transition: all 0.3s ease;
}

.strength-weak { 
    background: linear-gradient(90deg, #dc3545 0%, #dc3545 33%, #e5e7eb 33%, #e5e7eb 100%);
}
.strength-medium { 
    background: linear-gradient(90deg, #ffc107 0%, #ffc107 66%, #e5e7eb 66%, #e5e7eb 100%);
}
.strength-strong { 
    background: linear-gradient(90deg, #28a745 0%, #28a745 100%);
}

.requirement-met {
    color: #28a745 !important;
}

.requirement-met .fa-circle {
    display: none;
}

.requirement-met::before {
    content: '✓';
    margin-right: 0.5rem;
    font-weight: bold;
}

.registration-progress .progress {
    background-color: #e9ecef;
}

.registration-flow .fa-2x {
    opacity: 0.7;
    transition: all 0.3s ease;
}

.registration-flow .fa-2x:hover {
    opacity: 1;
    transform: scale(1.1);
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

.section-card {
    animation: fadeInUp 0.8s ease-out;
}

.alert {
    animation: fadeInUp 0.4s ease-out;
}

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
}

@media print {
    body {
        background: white !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #000 !important;
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
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    }
    
    // パスワード強度チェック
    $('#password').on('input', function() {
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
            $('#password').closest('.input-group').after(`
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
    $('#password_confirmation').on('input', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        const matchDiv = $('#passwordMatch');
        
        if (confirmPassword.length > 0) {
            if (password === confirmPassword) {
                matchDiv.html('<small class="text-success"><i class="fas fa-check-circle me-1"></i>パスワードが一致しています</small>');
            } else {
                matchDiv.html('<small class="text-danger"><i class="fas fa-times-circle me-1"></i>パスワードが一致しません</small>');
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
            showAlert('error', 'パスワードが一致しません');
            return false;
        }
        
        // 同意事項チェック
        const requiredConsents = ['identity_verification', 'personal_info_consent', 'terms', 'system_responsibility'];
        let missingConsents = [];
        
        requiredConsents.forEach(function(consent) {
            if (!$('#' + consent).is(':checked')) {
                missingConsents.push(consent);
            }
        });
        
        if (missingConsents.length > 0) {
            e.preventDefault();
            showAlert('error', '必要な同意事項にチェックを入れてください');
            return false;
        }
        
        // 年齢チェック
        const birthDate = new Date($('#birth_date').val());
        const today = new Date();
        const age = today.getFullYear() - birthDate.getFullYear();
        
        if (age < 18) {
            e.preventDefault();
            showAlert('error', '18歳以上である必要があります');
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
    $('#phone, #emergency_contact').on('input', function() {
        let value = $(this).val().replace(/[^\d]/g, '');
        if (value.length >= 7) {
            value = value.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
        } else if (value.length >= 4) {
            value = value.replace(/(\d{3})(\d{4})/, '$1-$2');
        }
        $(this).val(value);
    });
    
    // カナ文字の自動変換（ひらがな→カタカナ）
    $('#last_name_kana, #first_name_kana').on('blur', function() {
        let value = $(this).val();
        // ひらがなをカタカナに変換
        value = value.replace(/[\u3041-\u3096]/g, function(match) {
            return String.fromCharCode(match.charCodeAt(0) + 0x60);
        });
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
    
    // 進捗更新
    function updateProgress() {
        const totalFields = $('input[required], select[required]').length;
        const filledFields = $('input[required]:not([value=""]), select[required]:not([value=""])').length;
        const progress = Math.round((filledFields / totalFields) * 100);
        
        $('.progress-bar').css('width', progress + '%');
        
        if (progress >= 100) {
            $('.progress-bar').addClass('bg-success').removeClass('bg-primary');
        }
    }
    
    // フィールド変更時に進捗更新
    $('input, select').on('change input', function() {
        updateProgress();
    });
    
    // アラート表示関数
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('.container .row .col-lg-8').prepend(alertHtml);
        
        // 3秒後に自動で閉じる
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    // 初回進捗更新
    updateProgress();
});
</script>
@endpush
@endsection
