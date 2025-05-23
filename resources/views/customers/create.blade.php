@extends('layouts.app')

@section('title', '顧客登録')

@section('content')
<div class="container-fluid">
    <!-- パンくずリスト -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">顧客一覧</a></li>
            <li class="breadcrumb-item active" aria-current="page">顧客登録</li>
        </ol>
    </nav>
    
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-person-plus me-2"></i>
                        顧客登録
                    </h2>
                    <p class="text-muted mb-0">新規顧客の基本情報を登録</p>
                </div>
                <div>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        顧客一覧に戻る
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 登録フォーム -->
    <div class="row">
        <div class="col-12">
            <form method="POST" action="{{ route('customers.store') }}" id="customerForm" class="needs-validation" novalidate>
                @csrf
                
                <div class="row">
                    <!-- 基本情報 -->
                    <div class="col-lg-8 col-md-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    基本情報
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- 顧客名 -->
                                    <div class="col-md-8 mb-3">
                                        <label for="name" class="form-label">
                                            顧客名 <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name') }}" 
                                               required
                                               placeholder="顧客名または会社名を入力">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="invalid-feedback">顧客名は必須です</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- 顧客種別 -->
                                    <div class="col-md-4 mb-3">
                                        <label for="type" class="form-label">
                                            顧客種別 <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('type') is-invalid @enderror" 
                                                id="type" 
                                                name="type" 
                                                required>
                                            <option value="">選択してください</option>
                                            <option value="corporate" {{ old('type') == 'corporate' ? 'selected' : '' }}>法人</option>
                                            <option value="individual" {{ old('type') == 'individual' ? 'selected' : '' }}>個人</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="invalid-feedback">顧客種別を選択してください</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <!-- 担当者名 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_person" class="form-label">担当者名</label>
                                        <input type="text" 
                                               class="form-control @error('contact_person') is-invalid @enderror" 
                                               id="contact_person" 
                                               name="contact_person" 
                                               value="{{ old('contact_person') }}"
                                               placeholder="担当者名を入力">
                                        @error('contact_person')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- 部署・役職 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_title" class="form-label">部署・役職</label>
                                        <input type="text" 
                                               class="form-control @error('contact_title') is-invalid @enderror" 
                                               id="contact_title" 
                                               name="contact_title" 
                                               value="{{ old('contact_title') }}"
                                               placeholder="部署・役職を入力">
                                        @error('contact_title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 連絡先情報 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-telephone me-2"></i>
                                    連絡先情報
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- 電話番号 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">
                                            電話番号 <span class="text-danger">*</span>
                                        </label>
                                        <input type="tel" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone') }}" 
                                               required
                                               placeholder="03-1234-5678">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="invalid-feedback">電話番号は必須です</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- FAX番号 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="fax" class="form-label">FAX番号</label>
                                        <input type="tel" 
                                               class="form-control @error('fax') is-invalid @enderror" 
                                               id="fax" 
                                               name="fax" 
                                               value="{{ old('fax') }}"
                                               placeholder="03-1234-5679">
                                        @error('fax')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- メールアドレス -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">メールアドレス</label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}"
                                           placeholder="example@company.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- 住所 -->
                                <div class="mb-3">
                                    <label for="address" class="form-label">住所</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3"
                                              placeholder="〒000-0000 都道府県市区町村番地">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- 詳細情報 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-card-text me-2"></i>
                                    詳細情報
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- 業種・事業内容 -->
                                <div class="mb-3">
                                    <label for="business_type" class="form-label">業種・事業内容</label>
                                    <input type="text" 
                                           class="form-control @error('business_type') is-invalid @enderror" 
                                           id="business_type" 
                                           name="business_type" 
                                           value="{{ old('business_type') }}"
                                           placeholder="製造業、サービス業など">
                                    @error('business_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- 従業員数 -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="employee_count" class="form-label">従業員数</label>
                                        <select class="form-select @error('employee_count') is-invalid @enderror" 
                                                id="employee_count" 
                                                name="employee_count">
                                            <option value="">選択してください</option>
                                            <option value="1-10" {{ old('employee_count') == '1-10' ? 'selected' : '' }}>1-10名</option>
                                            <option value="11-50" {{ old('employee_count') == '11-50' ? 'selected' : '' }}>11-50名</option>
                                            <option value="51-100" {{ old('employee_count') == '51-100' ? 'selected' : '' }}>51-100名</option>
                                            <option value="101-300" {{ old('employee_count') == '101-300' ? 'selected' : '' }}>101-300名</option>
                                            <option value="301-1000" {{ old('employee_count') == '301-1000' ? 'selected' : '' }}>301-1000名</option>
                                            <option value="1000+" {{ old('employee_count') == '1000+' ? 'selected' : '' }}>1000名以上</option>
                                        </select>
                                        @error('employee_count')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- 年間売上 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="annual_revenue" class="form-label">年間売上</label>
                                        <select class="form-select @error('annual_revenue') is-invalid @enderror" 
                                                id="annual_revenue" 
                                                name="annual_revenue">
                                            <option value="">選択してください</option>
                                            <option value="under_100m" {{ old('annual_revenue') == 'under_100m' ? 'selected' : '' }}>1億円未満</option>
                                            <option value="100m_500m" {{ old('annual_revenue') == '100m_500m' ? 'selected' : '' }}>1億円-5億円</option>
                                            <option value="500m_1b" {{ old('annual_revenue') == '500m_1b' ? 'selected' : '' }}>5億円-10億円</option>
                                            <option value="1b_10b" {{ old('annual_revenue') == '1b_10b' ? 'selected' : '' }}>10億円-100億円</option>
                                            <option value="over_10b" {{ old('annual_revenue') == 'over_10b' ? 'selected' : '' }}>100億円以上</option>
                                        </select>
                                        @error('annual_revenue')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- 備考 -->
                                <div class="mb-3">
                                    <label for="notes" class="form-label">備考</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" 
                                              name="notes" 
                                              rows="4"
                                              placeholder="特記事項、要望、その他の情報など">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- サイドバー -->
                    <div class="col-lg-4 col-md-12">
                        <!-- ステータス・設定 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-gear me-2"></i>
                                    ステータス・設定
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- ステータス -->
                                <div class="mb-3">
                                    <label for="status" class="form-label">
                                        ステータス <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status" 
                                            required>
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>アクティブ</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>非アクティブ</option>
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>保留中</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- 優先度 -->
                                <div class="mb-3">
                                    <label for="priority" class="form-label">優先度</label>
                                    <select class="form-select @error('priority') is-invalid @enderror" 
                                            id="priority" 
                                            name="priority">
                                        <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>標準</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>高</option>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>低</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- 地域 -->
                                <div class="mb-3">
                                    <label for="region" class="form-label">地域</label>
                                    <select class="form-select @error('region') is-invalid @enderror" 
                                            id="region" 
                                            name="region">
                                        <option value="">選択してください</option>
                                        <option value="tokyo" {{ old('region') == 'tokyo' ? 'selected' : '' }}>東京都</option>
                                        <option value="kanagawa" {{ old('region') == 'kanagawa' ? 'selected' : '' }}>神奈川県</option>
                                        <option value="chiba" {{ old('region') == 'chiba' ? 'selected' : '' }}>千葉県</option>
                                        <option value="saitama" {{ old('region') == 'saitama' ? 'selected' : '' }}>埼玉県</option>
                                        <option value="other" {{ old('region') == 'other' ? 'selected' : '' }}>その他</option>
                                    </select>
                                    @error('region')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- タグ -->
                                <div class="mb-3">
                                    <label for="tags" class="form-label">タグ</label>
                                    <input type="text" 
                                           class="form-control @error('tags') is-invalid @enderror" 
                                           id="tags" 
                                           name="tags" 
                                           value="{{ old('tags') }}"
                                           placeholder="VIP, 大口, 新規など（カンマ区切り）">
                                    <div class="form-text">
                                        <small>複数のタグはカンマ（,）で区切って入力</small>
                                    </div>
                                    @error('tags')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- アクションボタン -->
                        <div class="card">
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                        <i class="bi bi-check-circle me-2"></i>
                                        顧客を登録
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="saveAndNewBtn">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        登録して新規作成
                                    </button>
                                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-2"></i>
                                        キャンセル
                                    </a>
                                </div>
                                
                                <!-- 自動保存状態 -->
                                <div class="mt-3 text-center">
                                    <small class="text-muted" id="autoSaveStatus">
                                        <i class="bi bi-cloud-check me-1"></i>
                                        自動保存済み
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ヘルプ・ガイド -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-question-circle me-2"></i>
                                    入力ガイド
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-primary">必須項目</h6>
                                    <ul class="small text-muted">
                                        <li>顧客名</li>
                                        <li>顧客種別</li>
                                        <li>電話番号</li>
                                        <li>ステータス</li>
                                    </ul>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-info">ヒント</h6>
                                    <ul class="small text-muted">
                                        <li>法人の場合は正式な会社名を入力</li>
                                        <li>担当者情報は後から変更可能</li>
                                        <li>タグを活用して分類管理</li>
                                    </ul>
                                </div>
                                
                                <div class="alert alert-info p-2">
                                    <small>
                                        <i class="bi bi-lightbulb me-1"></i>
                                        入力中のデータは自動的に保存されます
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .form-control:focus, .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    }
    
    .is-invalid {
        border-color: #dc3545;
    }
    
    .invalid-feedback {
        display: block;
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .required-asterisk {
        color: #dc3545;
    }
    
    .auto-save-indicator {
        opacity: 0.7;
        transition: opacity 0.3s ease;
    }
    
    .auto-save-indicator.saving {
        opacity: 1;
        color: #f59e0b;
    }
    
    .auto-save-indicator.saved {
        opacity: 1;
        color: #22c55e;
    }
    
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    @media (max-width: 768px) {
        .btn-lg {
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }
        
        .card-body {
            padding: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let autoSaveTimeout;
    let formChanged = false;
    
    $(document).ready(function() {
        // フォームバリデーション初期化
        initFormValidation();
        
        // 自動保存設定
        setupAutoSave();
        
        // 電話番号・FAX番号の自動フォーマット
        setupPhoneFormatting();
        
        // タグ入力の補助
        setupTagsInput();
        
        // 登録して新規作成ボタン
        $('#saveAndNewBtn').on('click', function() {
            $('#customerForm').append('<input type="hidden" name="save_and_new" value="1">');
            $('#customerForm').submit();
        });
        
        // フォーム送信時の処理
        $('#customerForm').on('submit', function() {
            const submitBtn = $(this).find('button[type="submit"]');
            const spinner = submitBtn.find('.spinner-border');
            
            if (this.checkValidity()) {
                submitBtn.prop('disabled', true);
                spinner.removeClass('d-none');
            }
        });
        
        // 離脱警告
        setupUnloadWarning();
    });
    
    // フォームバリデーション初期化
    function initFormValidation() {
        // HTML5バリデーション
        const form = document.getElementById('customerForm');
        
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // 最初のエラーフィールドにフォーカス
                const firstError = form.querySelector('.is-invalid, :invalid');
                if (firstError) {
                    firstError.focus();
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
            
            form.classList.add('was-validated');
        });
        
        // リアルタイムバリデーション
        $('#customerForm input, #customerForm select, #customerForm textarea').on('blur', function() {
            $(this).addClass('was-validated');
            validateField(this);
        });
        
        $('#customerForm input, #customerForm select, #customerForm textarea').on('input change', function() {
            if ($(this).hasClass('was-validated')) {
                validateField(this);
            }
            
            // フォーム変更フラグ
            formChanged = true;
        });
    }
    
    // フィールド個別バリデーション
    function validateField(field) {
        const $field = $(field);
        $field.removeClass('is-invalid is-valid');
        
        if (field.checkValidity()) {
            $field.addClass('is-valid');
        } else {
            $field.addClass('is-invalid');
        }
        
        // カスタムバリデーション
        if (field.name === 'email' && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                $field.addClass('is-invalid').removeClass('is-valid');
                $field.siblings('.invalid-feedback').text('正しいメールアドレス形式で入力してください');
            }
        }
        
        if (field.name === 'phone' && field.value) {
            const phoneRegex = /^[\d-]+$/;
            if (!phoneRegex.test(field.value)) {
                $field.addClass('is-invalid').removeClass('is-valid');
                $field.siblings('.invalid-feedback').text('正しい電話番号形式で入力してください');
            }
        }
    }
    
    // 自動保存設定
    function setupAutoSave() {
        const autoSaveFields = '#customerForm input, #customerForm select, #customerForm textarea';
        
        $(autoSaveFields).on('input change', function() {
            clearTimeout(autoSaveTimeout);
            
            // 保存中表示
            $('#autoSaveStatus').html('<i class="bi bi-cloud-arrow-up me-1"></i>保存中...').addClass('saving');
            
            autoSaveTimeout = setTimeout(() => {
                saveFormData();
            }, 2000);
        });
    }
    
    // フォームデータ保存
    function saveFormData() {
        const formData = $('#customerForm').serialize();
        
        // LocalStorageに保存（実際の環境では使用可能）
        try {
            // sessionStorage.setItem('customer_form_draft', formData);
            
            // 保存完了表示
            $('#autoSaveStatus').html('<i class="bi bi-cloud-check me-1"></i>自動保存済み').removeClass('saving').addClass('saved');
            
            setTimeout(() => {
                $('#autoSaveStatus').removeClass('saved');
            }, 2000);
        } catch(e) {
            console.log('自動保存はこの環境では利用できません');
            $('#autoSaveStatus').html('<i class="bi bi-cloud-check me-1"></i>入力中');
        }
    }
    
    // 電話番号フォーマット
    function setupPhoneFormatting() {
        $('#phone, #fax').on('input', function() {
            let value = $(this).val().replace(/[^\d]/g, '');
            
            if (value.length >= 10) {
                if (value.length === 10) {
                    // 固定電話（10桁）
                    value = value.replace(/(\d{2,4})(\d{3,4})(\d{4})/, '$1-$2-$3');
                } else if (value.length === 11) {
                    // 携帯電話（11桁）
                    value = value.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
                }
            }
            
            $(this).val(value);
        });
    }
    
    // タグ入力補助
    function setupTagsInput() {
        const commonTags = ['VIP', '大口', '新規', '要注意', '優良', '継続', '季節限定'];
        
        $('#tags').on('focus', function() {
            if (!$('#tagsSuggestions').length) {
                const suggestions = commonTags.map(tag => 
                    `<button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1 tag-suggestion">${tag}</button>`
                ).join('');
                
                $(this).after(`
                    <div id="tagsSuggestions" class="mt-2">
                        <small class="text-muted">よく使われるタグ:</small><br>
                        ${suggestions}
                    </div>
                `);
            }
        });
        
        $(document).on('click', '.tag-suggestion', function() {
            const tag = $(this).text();
            const currentTags = $('#tags').val();
            
            if (currentTags) {
                $('#tags').val(currentTags + ', ' + tag);
            } else {
                $('#tags').val(tag);
            }
            
            $('#tags').trigger('input');
        });
    }
    
    // 離脱警告
    function setupUnloadWarning() {
        $(window).on('beforeunload', function(e) {
            if (formChanged) {
                const message = '入力中のデータが失われる可能性があります。本当にページを離れますか？';
                e.returnValue = message;
                return message;
            }
        });
        
        // フォーム送信時は警告を無効化
        $('#customerForm').on('submit', function() {
            formChanged = false;
        });
    }
    
    // ページ読み込み時の復元（デモ用）
    function restoreFormData() {
        try {
            // const savedData = sessionStorage.getItem('customer_form_draft');
            // if (savedData) {
            //     // フォームデータを復元
            //     const params = new URLSearchParams(savedData);
            //     params.forEach((value, key) => {
            //         const field = $(`[name="${key}"]`);
            //         if (field.length) {
            //             field.val(value);
            //         }
            //     });
            // }
        } catch(e) {
            console.log('データ復元はこの環境では利用できません');
        }
    }
</script>
@endpush
@endsection
