@extends('layouts.app')

@section('title', '顧客編集')

@section('content')
<div class="container-fluid">
    <!-- パンくずリスト -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">顧客一覧</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.show', $customer->id ?? 1) }}">顧客詳細</a></li>
            <li class="breadcrumb-item active" aria-current="page">編集</li>
        </ol>
    </nav>
    
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-pencil me-2"></i>
                        顧客編集
                    </h2>
                    <p class="text-muted mb-0">{{ $customer->name ?? 'サンプル顧客' }}の情報を編集</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('customers.show', $customer->id ?? 1) }}" class="btn btn-outline-info">
                        <i class="bi bi-eye me-1"></i>
                        詳細表示
                    </a>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        顧客一覧
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 編集フォーム -->
    <div class="row">
        <div class="col-12">
            <form method="POST" action="{{ route('customers.update', $customer->id ?? 1) }}" id="customerEditForm" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- 基本情報 -->
                    <div class="col-lg-8 col-md-12">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    基本情報
                                </h5>
                                <small class="text-muted">
                                    最終更新: {{ $customer->updated_at ?? '2024-05-23 10:30:00' }}
                                </small>
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
                                               value="{{ old('name', $customer->name ?? 'ABC商事株式会社') }}" 
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
                                            <option value="corporate" {{ old('type', $customer->type ?? 'corporate') == 'corporate' ? 'selected' : '' }}>法人</option>
                                            <option value="individual" {{ old('type', $customer->type ?? '') == 'individual' ? 'selected' : '' }}>個人</option>
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
                                               value="{{ old('contact_person', $customer->contact_person ?? '田中 太郎') }}"
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
                                               value="{{ old('contact_title', $customer->contact_title ?? '総務部 部長') }}"
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
                                               value="{{ old('phone', $customer->phone ?? '03-1234-5678') }}" 
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
                                               value="{{ old('fax', $customer->fax ?? '03-1234-5679') }}"
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
                                           value="{{ old('email', $customer->email ?? 'tanaka@abc-trading.co.jp') }}"
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
                                              placeholder="〒000-0000 都道府県市区町村番地">{{ old('address', $customer->address ?? '〒100-0001 東京都千代田区千代田1-1') }}</textarea>
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
                                           value="{{ old('business_type', $customer->business_type ?? '商社') }}"
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
                                            <option value="1-10" {{ old('employee_count', $customer->employee_count ?? '') == '1-10' ? 'selected' : '' }}>1-10名</option>
                                            <option value="11-50" {{ old('employee_count', $customer->employee_count ?? '11-50') == '11-50' ? 'selected' : '' }}>11-50名</option>
                                            <option value="51-100" {{ old('employee_count', $customer->employee_count ?? '') == '51-100' ? 'selected' : '' }}>51-100名</option>
                                            <option value="101-300" {{ old('employee_count', $customer->employee_count ?? '') == '101-300' ? 'selected' : '' }}>101-300名</option>
                                            <option value="301-1000" {{ old('employee_count', $customer->employee_count ?? '') == '301-1000' ? 'selected' : '' }}>301-1000名</option>
                                            <option value="1000+" {{ old('employee_count', $customer->employee_count ?? '') == '1000+' ? 'selected' : '' }}>1000名以上</option>
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
                                            <option value="under_100m" {{ old('annual_revenue', $customer->annual_revenue ?? '') == 'under_100m' ? 'selected' : '' }}>1億円未満</option>
                                            <option value="100m_500m" {{ old('annual_revenue', $customer->annual_revenue ?? '100m_500m') == '100m_500m' ? 'selected' : '' }}>1億円-5億円</option>
                                            <option value="500m_1b" {{ old('annual_revenue', $customer->annual_revenue ?? '') == '500m_1b' ? 'selected' : '' }}>5億円-10億円</option>
                                            <option value="1b_10b" {{ old('annual_revenue', $customer->annual_revenue ?? '') == '1b_10b' ? 'selected' : '' }}>10億円-100億円</option>
                                            <option value="over_10b" {{ old('annual_revenue', $customer->annual_revenue ?? '') == 'over_10b' ? 'selected' : '' }}>100億円以上</option>
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
                                              placeholder="特記事項、要望、その他の情報など">{{ old('notes', $customer->notes ?? '大口顧客。月次契約更新。担当者との関係良好。') }}</textarea>
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
                                        <option value="active" {{ old('status', $customer->status ?? 'active') == 'active' ? 'selected' : '' }}>アクティブ</option>
                                        <option value="inactive" {{ old('status', $customer->status ?? '') == 'inactive' ? 'selected' : '' }}>非アクティブ</option>
                                        <option value="pending" {{ old('status', $customer->status ?? '') == 'pending' ? 'selected' : '' }}>保留中</option>
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
                                        <option value="normal" {{ old('priority', $customer->priority ?? 'high') == 'normal' ? 'selected' : '' }}>標準</option>
                                        <option value="high" {{ old('priority', $customer->priority ?? 'high') == 'high' ? 'selected' : '' }}>高</option>
                                        <option value="low" {{ old('priority', $customer->priority ?? '') == 'low' ? 'selected' : '' }}>低</option>
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
                                        <option value="tokyo" {{ old('region', $customer->region ?? 'tokyo') == 'tokyo' ? 'selected' : '' }}>東京都</option>
                                        <option value="kanagawa" {{ old('region', $customer->region ?? '') == 'kanagawa' ? 'selected' : '' }}>神奈川県</option>
                                        <option value="chiba" {{ old('region', $customer->region ?? '') == 'chiba' ? 'selected' : '' }}>千葉県</option>
                                        <option value="saitama" {{ old('region', $customer->region ?? '') == 'saitama' ? 'selected' : '' }}>埼玉県</option>
                                        <option value="other" {{ old('region', $customer->region ?? '') == 'other' ? 'selected' : '' }}>その他</option>
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
                                           value="{{ old('tags', $customer->tags ?? 'VIP, 大口, 継続') }}"
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
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                        <i class="bi bi-check-circle me-2"></i>
                                        変更を保存
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="saveAndContinueBtn">
                                        <i class="bi bi-arrow-right-circle me-2"></i>
                                        保存して詳細表示
                                    </button>
                                    <a href="{{ route('customers.show', $customer->id ?? 1) }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-2"></i>
                                        キャンセル
                                    </a>
                                </div>
                                
                                <!-- 変更検知 -->
                                <div class="mt-3 text-center">
                                    <small class="text-muted" id="changeStatus">
                                        <i class="bi bi-check-circle me-1"></i>
                                        保存済み
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 変更履歴 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-clock-history me-2"></i>
                                    変更履歴
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-primary"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">登録</h6>
                                            <p class="timeline-text">顧客情報を新規登録</p>
                                            <small class="text-muted">2024-05-20 14:30</small>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">連絡先更新</h6>
                                            <p class="timeline-text">電話番号・メールアドレスを変更</p>
                                            <small class="text-muted">2024-05-22 09:15</small>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">ステータス変更</h6>
                                            <p class="timeline-text">アクティブに変更</p>
                                            <small class="text-muted">2024-05-23 10:30</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 関連情報 -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-link-45deg me-2"></i>
                                    関連情報
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="text-primary fw-bold fs-5">3</div>
                                        <small class="text-muted">案件</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-success fw-bold fs-5">8</div>
                                        <small class="text-muted">契約</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-warning fw-bold fs-5">12</div>
                                        <small class="text-muted">請求</small>
                                    </div>
                                </div>
                                
                                <hr class="my-3">
                                
                                <div class="d-grid gap-2">
                                    <a href="{{ route('projects.index', ['customer_id' => $customer->id ?? 1]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-briefcase me-1"></i>
                                        案件を表示
                                    </a>
                                    <a href="{{ route('contracts.index', ['customer_id' => $customer->id ?? 1]) }}" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-file-text me-1"></i>
                                        契約を表示
                                    </a>
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
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 25px;
    }
    
    .timeline-marker {
        position: absolute;
        left: -23px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid white;
        z-index: 1;
    }
    
    .timeline-content {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 8px;
        border-left: 3px solid #e9ecef;
    }
    
    .timeline-title {
        font-size: 0.9rem;
        margin-bottom: 5px;
        color: #495057;
    }
    
    .timeline-text {
        font-size: 0.8rem;
        margin-bottom: 5px;
        color: #6c757d;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    }
    
    .change-indicator {
        transition: all 0.3s ease;
    }
    
    .change-indicator.changed {
        color: #f59e0b;
    }
    
    .change-indicator.saved {
        color: #22c55e;
    }
    
    @media (max-width: 768px) {
        .timeline {
            padding-left: 20px;
        }
        
        .timeline-marker {
            left: -18px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let originalFormData;
    let formChanged = false;
    
    $(document).ready(function() {
        // フォームバリデーション初期化
        initFormValidation();
        
        // 変更検知設定
        setupChangeDetection();
        
        // 電話番号・FAX番号の自動フォーマット
        setupPhoneFormatting();
        
        // タグ入力の補助
        setupTagsInput();
        
        // 保存して詳細表示ボタン
        $('#saveAndContinueBtn').on('click', function() {
            $('#customerEditForm').append('<input type="hidden" name="save_and_continue" value="1">');
            $('#customerEditForm').submit();
        });
        
        // フォーム送信時の処理
        $('#customerEditForm').on('submit', function() {
            const submitBtn = $(this).find('button[type="submit"]');
            const spinner = submitBtn.find('.spinner-border');
            
            if (this.checkValidity()) {
                submitBtn.prop('disabled', true);
                spinner.removeClass('d-none');
            }
        });
        
        // 離脱警告
        setupUnloadWarning();
        
        // 元データを保存
        originalFormData = $('#customerEditForm').serialize();
    });
    
    // フォームバリデーション初期化
    function initFormValidation() {
        const form = document.getElementById('customerEditForm');
        
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                const firstError = form.querySelector('.is-invalid, :invalid');
                if (firstError) {
                    firstError.focus();
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
            
            form.classList.add('was-validated');
        });
        
        $('#customerEditForm input, #customerEditForm select, #customerEditForm textarea').on('blur', function() {
            validateField(this);
        });
        
        $('#customerEditForm input, #customerEditForm select, #customerEditForm textarea').on('input change', function() {
            validateField(this);
            checkFormChanges();
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
            }
        }
        
        if (field.name === 'phone' && field.value) {
            const phoneRegex = /^[\d-]+$/;
            if (!phoneRegex.test(field.value)) {
                $field.addClass('is-invalid').removeClass('is-valid');
            }
        }
    }
    
    // 変更検知設定
    function setupChangeDetection() {
        $('#customerEditForm input, #customerEditForm select, #customerEditForm textarea').on('input change', function() {
            checkFormChanges();
        });
    }
    
    // フォーム変更チェック
    function checkFormChanges() {
        const currentFormData = $('#customerEditForm').serialize();
        const hasChanges = currentFormData !== originalFormData;
        
        if (hasChanges && !formChanged) {
            formChanged = true;
            $('#changeStatus').html('<i class="bi bi-exclamation-circle me-1"></i>未保存の変更があります')
                             .removeClass('text-muted text-success')
                             .addClass('text-warning change-indicator changed');
        } else if (!hasChanges && formChanged) {
            formChanged = false;
            $('#changeStatus').html('<i class="bi bi-check-circle me-1"></i>保存済み')
                             .removeClass('text-warning change-indicator changed')
                             .addClass('text-muted');
        }
    }
    
    // 電話番号フォーマット
    function setupPhoneFormatting() {
        $('#phone, #fax').on('input', function() {
            let value = $(this).val().replace(/[^\d]/g, '');
            
            if (value.length >= 10) {
                if (value.length === 10) {
                    value = value.replace(/(\d{2,4})(\d{3,4})(\d{4})/, '$1-$2-$3');
                } else if (value.length === 11) {
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
                const currentTags = $(this).val().split(',').map(tag => tag.trim());
                const availableTags = commonTags.filter(tag => !currentTags.includes(tag));
                
                if (availableTags.length > 0) {
                    const suggestions = availableTags.map(tag => 
                        `<button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1 tag-suggestion">${tag}</button>`
                    ).join('');
                    
                    $(this).after(`
                        <div id="tagsSuggestions" class="mt-2">
                            <small class="text-muted">追加可能なタグ:</small><br>
                            ${suggestions}
                        </div>
                    `);
                }
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
            $('#tagsSuggestions').remove();
        });
    }
    
    // 離脱警告
    function setupUnloadWarning() {
        $(window).on('beforeunload', function(e) {
            if (formChanged) {
                const message = '未保存の変更があります。本当にページを離れますか？';
                e.returnValue = message;
                return message;
            }
        });
        
        $('#customerEditForm').on('submit', function() {
            formChanged = false;
        });
    }
    
    // ショートカットキー
    $(document).on('keydown', function(e) {
        // Ctrl+S で保存
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            if (formChanged) {
                $('#customerEditForm').submit();
            }
        }
        
        // Esc でキャンセル
        if (e.key === 'Escape' && formChanged) {
            if (confirm('未保存の変更があります。破棄しますか？')) {
                window.location.href = '{{ route("customers.show", $customer->id ?? 1) }}';
            }
        }
    });
</script>
@endpush
@endsection
