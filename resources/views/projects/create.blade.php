@extends('layouts.app')

@section('title', '案件作成')

@section('content')
<div class="container-fluid">
    <!-- パンくずリスト -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">案件一覧</a></li>
            <li class="breadcrumb-item active" aria-current="page">案件作成</li>
        </ol>
    </nav>
    
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-plus-circle me-2"></i>
                        案件作成
                    </h2>
                    <p class="text-muted mb-0">新規プロジェクトの詳細情報を登録</p>
                </div>
                <div>
                    <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        案件一覧に戻る
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 作成フォーム -->
    <div class="row">
        <div class="col-12">
            <form method="POST" action="{{ route('projects.store') }}" id="projectForm" class="needs-validation" novalidate>
                @csrf
                
                <div class="row">
                    <!-- メインコンテンツ -->
                    <div class="col-lg-8 col-md-12">
                        <!-- 基本情報 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    基本情報
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- 案件名 -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        案件名 <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           required
                                           placeholder="案件名を入力">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">案件名は必須です</div>
                                    @enderror
                                </div>
                                
                                <!-- 顧客選択 -->
                                <div class="mb-3">
                                    <label for="customer_id" class="form-label">
                                        顧客 <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <select class="form-select @error('customer_id') is-invalid @enderror" 
                                                id="customer_id" 
                                                name="customer_id" 
                                                required>
                                            <option value="">顧客を選択してください</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" id="addCustomerBtn">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">顧客を選択してください</div>
                                    @enderror
                                </div>
                                
                                <!-- 案件タイプ -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="type" class="form-label">
                                            案件タイプ <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('type') is-invalid @enderror" 
                                                id="type" 
                                                name="type" 
                                                required>
                                            <option value="">選択してください</option>
                                            <option value="office_security" {{ old('type') == 'office_security' ? 'selected' : '' }}>オフィス警備</option>
                                            <option value="construction_security" {{ old('type') == 'construction_security' ? 'selected' : '' }}>工事現場警備</option>
                                            <option value="event_security" {{ old('type') == 'event_security' ? 'selected' : '' }}>イベント警備</option>
                                            <option value="facility_security" {{ old('type') == 'facility_security' ? 'selected' : '' }}>施設警備</option>
                                            <option value="traffic_control" {{ old('type') == 'traffic_control' ? 'selected' : '' }}>交通誘導</option>
                                            <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>その他</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="invalid-feedback">案件タイプを選択してください</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- 勤務場所 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="location" class="form-label">
                                            勤務場所 <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('location') is-invalid @enderror" 
                                               id="location" 
                                               name="location" 
                                               value="{{ old('location') }}" 
                                               required
                                               placeholder="東京都渋谷区...">
                                        @error('location')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="invalid-feedback">勤務場所は必須です</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- 案件説明 -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">案件説明</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="4"
                                              placeholder="案件の詳細、特記事項等">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- スケジュール情報 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-calendar me-2"></i>
                                    スケジュール情報
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- 開始日 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="start_date" class="form-label">
                                            開始日 <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" 
                                               class="form-control @error('start_date') is-invalid @enderror" 
                                               id="start_date" 
                                               name="start_date" 
                                               value="{{ old('start_date') }}" 
                                               required>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="invalid-feedback">開始日は必須です</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- 終了予定日 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="end_date" class="form-label">
                                            終了予定日 <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" 
                                               class="form-control @error('end_date') is-invalid @enderror" 
                                               id="end_date" 
                                               name="end_date" 
                                               value="{{ old('end_date') }}" 
                                               required>
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="invalid-feedback">終了予定日は必須です</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <!-- 勤務時間 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="work_hours" class="form-label">勤務時間</label>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" 
                                                       class="form-control @error('start_time') is-invalid @enderror" 
                                                       id="start_time" 
                                                       name="start_time" 
                                                       value="{{ old('start_time', '09:00') }}">
                                                <div class="form-text">開始時刻</div>
                                            </div>
                                            <div class="col-6">
                                                <input type="time" 
                                                       class="form-control @error('end_time') is-invalid @enderror" 
                                                       id="end_time" 
                                                       name="end_time" 
                                                       value="{{ old('end_time', '18:00') }}">
                                                <div class="form-text">終了時刻</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- 勤務日 -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">勤務日</label>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="btn-group w-100" role="group" id="workDaysGroup">
                                                    <input type="checkbox" class="btn-check" id="monday" name="work_days[]" value="1" {{ is_array(old('work_days')) && in_array('1', old('work_days')) ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="monday">月</label>
                                                    
                                                    <input type="checkbox" class="btn-check" id="tuesday" name="work_days[]" value="2" {{ is_array(old('work_days')) && in_array('2', old('work_days')) ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="tuesday">火</label>
                                                    
                                                    <input type="checkbox" class="btn-check" id="wednesday" name="work_days[]" value="3" {{ is_array(old('work_days')) && in_array('3', old('work_days')) ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="wednesday">水</label>
                                                    
                                                    <input type="checkbox" class="btn-check" id="thursday" name="work_days[]" value="4" {{ is_array(old('work_days')) && in_array('4', old('work_days')) ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="thursday">木</label>
                                                    
                                                    <input type="checkbox" class="btn-check" id="friday" name="work_days[]" value="5" {{ is_array(old('work_days')) && in_array('5', old('work_days')) ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="friday">金</label>
                                                    
                                                    <input type="checkbox" class="btn-check" id="saturday" name="work_days[]" value="6" {{ is_array(old('work_days')) && in_array('6', old('work_days')) ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="saturday">土</label>
                                                    
                                                    <input type="checkbox" class="btn-check" id="sunday" name="work_days[]" value="0" {{ is_array(old('work_days')) && in_array('0', old('work_days')) ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="sunday">日</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 期間計算結果 -->
                                <div class="alert alert-info" id="durationInfo" style="display: none;">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <span id="durationText"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 警備要件 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-shield-check me-2"></i>
                                    警備要件
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- 必要人数 -->
                                    <div class="col-md-4 mb-3">
                                        <label for="required_guards" class="form-label">
                                            必要人数 <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="number" 
                                                   class="form-control @error('required_guards') is-invalid @enderror" 
                                                   id="required_guards" 
                                                   name="required_guards" 
                                                   value="{{ old('required_guards', 1) }}" 
                                                   min="1" max="50"
                                                   required>
                                            <span class="input-group-text">名</span>
                                        </div>
                                        @error('required_guards')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="invalid-feedback">必要人数は必須です</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- 必要資格 -->
                                    <div class="col-md-4 mb-3">
                                        <label for="required_qualifications" class="form-label">必要資格</label>
                                        <select class="form-select" id="required_qualifications" name="required_qualifications[]" multiple>
                                            <option value="security_guard_1" {{ is_array(old('required_qualifications')) && in_array('security_guard_1', old('required_qualifications')) ? 'selected' : '' }}>警備員検定1級</option>
                                            <option value="security_guard_2" {{ is_array(old('required_qualifications')) && in_array('security_guard_2', old('required_qualifications')) ? 'selected' : '' }}>警備員検定2級</option>
                                            <option value="traffic_control" {{ is_array(old('required_qualifications')) && in_array('traffic_control', old('required_qualifications')) ? 'selected' : '' }}>交通誘導警備業務検定</option>
                                            <option value="facility_security" {{ is_array(old('required_qualifications')) && in_array('facility_security', old('required_qualifications')) ? 'selected' : '' }}>施設警備業務検定</option>
                                            <option value="fire_prevention" {{ is_array(old('required_qualifications')) && in_array('fire_prevention', old('required_qualifications')) ? 'selected' : '' }}>防火管理者</option>
                                        </select>
                                        <div class="form-text">Ctrlキーを押しながら複数選択可能</div>
                                    </div>
                                    
                                    <!-- 経験年数 -->
                                    <div class="col-md-4 mb-3">
                                        <label for="required_experience" class="form-label">必要経験年数</label>
                                        <select class="form-select @error('required_experience') is-invalid @enderror" 
                                                id="required_experience" 
                                                name="required_experience">
                                            <option value="">指定なし</option>
                                            <option value="0" {{ old('required_experience') == '0' ? 'selected' : '' }}>未経験可</option>
                                            <option value="1" {{ old('required_experience') == '1' ? 'selected' : '' }}>1年以上</option>
                                            <option value="3" {{ old('required_experience') == '3' ? 'selected' : '' }}>3年以上</option>
                                            <option value="5" {{ old('required_experience') == '5' ? 'selected' : '' }}>5年以上</option>
                                            <option value="10" {{ old('required_experience') == '10' ? 'selected' : '' }}>10年以上</option>
                                        </select>
                                        @error('required_experience')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- 特記事項 -->
                                <div class="mb-3">
                                    <label for="special_requirements" class="form-label">特記事項・注意点</label>
                                    <textarea class="form-control @error('special_requirements') is-invalid @enderror" 
                                              id="special_requirements" 
                                              name="special_requirements" 
                                              rows="3"
                                              placeholder="制服要件、持参物、特別な注意点など">{{ old('special_requirements') }}</textarea>
                                    @error('special_requirements')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- 契約情報 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-currency-yen me-2"></i>
                                    契約情報
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- 契約金額 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="contract_amount" class="form-label">契約金額</label>
                                        <div class="input-group">
                                            <span class="input-group-text">¥</span>
                                            <input type="number" 
                                                   class="form-control @error('contract_amount') is-invalid @enderror" 
                                                   id="contract_amount" 
                                                   name="contract_amount" 
                                                   value="{{ old('contract_amount') }}" 
                                                   min="0" step="1000"
                                                   placeholder="0">
                                        </div>
                                        @error('contract_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- 時給 -->
                                    <div class="col-md-6 mb-3">
                                        <label for="hourly_rate" class="form-label">時給</label>
                                        <div class="input-group">
                                            <span class="input-group-text">¥</span>
                                            <input type="number" 
                                                   class="form-control @error('hourly_rate') is-invalid @enderror" 
                                                   id="hourly_rate" 
                                                   name="hourly_rate" 
                                                   value="{{ old('hourly_rate', 1200) }}" 
                                                   min="900" max="5000"
                                                   placeholder="1200">
                                            <span class="input-group-text">/時</span>
                                        </div>
                                        @error('hourly_rate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- 支払い条件 -->
                                <div class="mb-3">
                                    <label for="payment_terms" class="form-label">支払い条件</label>
                                    <select class="form-select @error('payment_terms') is-invalid @enderror" 
                                            id="payment_terms" 
                                            name="payment_terms">
                                        <option value="monthly" {{ old('payment_terms', 'monthly') == 'monthly' ? 'selected' : '' }}>月末締め翌月払い</option>
                                        <option value="weekly" {{ old('payment_terms') == 'weekly' ? 'selected' : '' }}>週次払い</option>
                                        <option value="project_end" {{ old('payment_terms') == 'project_end' ? 'selected' : '' }}>案件完了時一括</option>
                                        <option value="custom" {{ old('payment_terms') == 'custom' ? 'selected' : '' }}>その他</option>
                                    </select>
                                    @error('payment_terms')
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
                                        <option value="planning" {{ old('status', 'planning') == 'planning' ? 'selected' : '' }}>計画中</option>
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>実行中</option>
                                        <option value="on_hold" {{ old('status') == 'on_hold' ? 'selected' : '' }}>保留中</option>
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
                                
                                <!-- 担当者 -->
                                <div class="mb-3">
                                    <label for="assignee_id" class="form-label">担当者</label>
                                    <select class="form-select @error('assignee_id') is-invalid @enderror" 
                                            id="assignee_id" 
                                            name="assignee_id">
                                        <option value="">選択してください</option>
                                    </select>
                                    @error('assignee_id')
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
                                           placeholder="緊急, 大型, 長期など（カンマ区切り）">
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
                                        案件を作成
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="saveAndAssignBtn">
                                        <i class="bi bi-people me-2"></i>
                                        作成して警備員アサイン
                                    </button>
                                    <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-2"></i>
                                        キャンセル
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 作成ガイド -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-lightbulb me-2"></i>
                                    作成ガイド
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-primary">入力のポイント</h6>
                                    <ul class="small text-muted">
                                        <li>案件名は分かりやすく具体的に</li>
                                        <li>勤務場所は正確な住所を記載</li>
                                        <li>必要資格は適切に設定</li>
                                        <li>特記事項で詳細を補足</li>
                                    </ul>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-info">作成後の流れ</h6>
                                    <ol class="small text-muted">
                                        <li>警備員のアサイン</li>
                                        <li>シフトスケジュール作成</li>
                                        <li>契約書作成・締結</li>
                                        <li>案件開始</li>
                                    </ol>
                                </div>
                                
                                <div class="alert alert-info p-2">
                                    <small>
                                        <i class="bi bi-info-circle me-1"></i>
                                        作成後も情報の変更は可能です
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
    
    .btn-check:checked + .btn {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    
    .is-invalid {
        border-color: #dc3545;
    }
    
    .invalid-feedback {
        display: block;
    }
    
    .duration-info {
        background-color: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 10px 15px;
        margin-top: 10px;
        border-radius: 4px;
    }
    
    #workDaysGroup .btn {
        font-size: 0.9rem;
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
        
        #workDaysGroup {
            display: flex;
            flex-wrap: wrap;
        }
        
        #workDaysGroup .btn {
            flex: 1;
            min-width: 40px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let formChanged = false;
    
    $(document).ready(function() {
        // 初期化処理
        initFormValidation();
        loadCustomers();
        loadAssignees();
        setupFormInteractions();
        
        // フォーム送信時の処理
        $('#projectForm').on('submit', function() {
            const submitBtn = $(this).find('button[type="submit"]');
            const spinner = submitBtn.find('.spinner-border');
            
            if (this.checkValidity()) {
                submitBtn.prop('disabled', true);
                spinner.removeClass('d-none');
            }
        });
        
        // 作成して警備員アサインボタン
        $('#saveAndAssignBtn').on('click', function() {
            $('#projectForm').append('<input type="hidden" name="save_and_assign" value="1">');
            $('#projectForm').submit();
        });
        
        // 離脱警告設定
        setupUnloadWarning();
    });
    
    // フォームバリデーション初期化
    function initFormValidation() {
        const form = document.getElementById('projectForm');
        
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
        
        // リアルタイムバリデーション
        $('#projectForm input, #projectForm select, #projectForm textarea').on('blur', function() {
            validateField(this);
        });
        
        $('#projectForm input, #projectForm select, #projectForm textarea').on('input change', function() {
            validateField(this);
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
        if (field.name === 'end_date') {
            const startDate = $('#start_date').val();
            const endDate = field.value;
            
            if (startDate && endDate && new Date(endDate) <= new Date(startDate)) {
                $field.addClass('is-invalid').removeClass('is-valid');
                $field.siblings('.invalid-feedback').text('終了日は開始日より後の日付を設定してください');
            }
        }
    }
    
    // 顧客データ読み込み
    function loadCustomers() {
        $.get('{{ route("customers.search") }}?status=active')
            .done(function(response) {
                const customerSelect = $('#customer_id');
                customerSelect.empty().append('<option value="">顧客を選択してください</option>');
                
                response.data.forEach(customer => {
                    customerSelect.append(`<option value="${customer.id}">${customer.name}</option>`);
                });
                
                // URLパラメータから顧客IDを取得
                const urlParams = new URLSearchParams(window.location.search);
                const customerId = urlParams.get('customer_id');
                if (customerId) {
                    customerSelect.val(customerId);
                }
            })
            .fail(function() {
                console.error('顧客データの読み込みに失敗しました');
            });
    }
    
    // 担当者データ読み込み
    function loadAssignees() {
        // デモデータ（実際はAPIから取得）
        const assignees = [
            { id: 1, name: '山田 太郎' },
            { id: 2, name: '田中 花子' },
            { id: 3, name: '佐藤 次郎' },
            { id: 4, name: '鈴木 三郎' }
        ];
        
        const assigneeSelect = $('#assignee_id');
        assigneeSelect.empty().append('<option value="">選択してください</option>');
        
        assignees.forEach(assignee => {
            assigneeSelect.append(`<option value="${assignee.id}">${assignee.name}</option>`);
        });
    }
    
    // フォームインタラクション設定
    function setupFormInteractions() {
        // 開始日・終了日変更時の期間計算
        $('#start_date, #end_date').on('change', function() {
            calculateDuration();
        });
        
        // 案件タイプ変更時の自動設定
        $('#type').on('change', function() {
            const type = $(this).val();
            autoSetDefaults(type);
        });
        
        // 必要人数変更時の時給計算
        $('#required_guards, #hourly_rate').on('input', function() {
            calculateEstimatedCost();
        });
        
        // 顧客追加ボタン
        $('#addCustomerBtn').on('click', function() {
            window.open('{{ route("customers.create") }}', '_blank');
        });
        
        // 勤務日一括選択
        setupWorkDaysSelection();
    }
    
    // 期間計算
    function calculateDuration() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = end - start;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays > 0) {
                $('#durationInfo').show();
                $('#durationText').text(`期間: ${diffDays}日間`);
            } else {
                $('#durationInfo').hide();
            }
        } else {
            $('#durationInfo').hide();
        }
    }
    
    // 案件タイプ別デフォルト設定
    function autoSetDefaults(type) {
        const defaults = {
            'office_security': {
                hourly_rate: 1200,
                required_guards: 1,
                start_time: '09:00',
                end_time: '18:00'
            },
            'construction_security': {
                hourly_rate: 1400,
                required_guards: 2,
                start_time: '08:00',
                end_time: '17:00'
            },
            'event_security': {
                hourly_rate: 1500,
                required_guards: 3,
                start_time: '10:00',
                end_time: '22:00'
            },
            'facility_security': {
                hourly_rate: 1300,
                required_guards: 1,
                start_time: '00:00',
                end_time: '24:00'
            },
            'traffic_control': {
                hourly_rate: 1100,
                required_guards: 2,
                start_time: '08:00',
                end_time: '17:00'
            }
        };
        
        if (defaults[type]) {
            const config = defaults[type];
            $('#hourly_rate').val(config.hourly_rate);
            $('#required_guards').val(config.required_guards);
            $('#start_time').val(config.start_time);
            $('#end_time').val(config.end_time);
            
            // 平日を自動選択（月〜金）
            if (type !== 'event_security') {
                $('#workDaysGroup input[type="checkbox"]').prop('checked', false);
                $('#monday, #tuesday, #wednesday, #thursday, #friday').prop('checked', true);
            }
        }
    }
    
    // 予想コスト計算
    function calculateEstimatedCost() {
        const guards = parseInt($('#required_guards').val()) || 0;
        const hourlyRate = parseInt($('#hourly_rate').val()) || 0;
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (guards && hourlyRate && startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
            const hoursPerDay = 8; // 仮定
            
            const estimatedCost = guards * hourlyRate * hoursPerDay * days;
            
            if (!$('#estimatedCost').length) {
                $('#contract_amount').parent().after(`
                    <div id="estimatedCost" class="alert alert-info mt-2">
                        <small><i class="bi bi-calculator me-1"></i>
                        予想コスト: ¥${estimatedCost.toLocaleString()}
                        （${guards}名 × ¥${hourlyRate}/時 × 8時間 × ${days}日）</small>
                    </div>
                `);
            } else {
                $('#estimatedCost').html(`
                    <small><i class="bi bi-calculator me-1"></i>
                    予想コスト: ¥${estimatedCost.toLocaleString()}
                    （${guards}名 × ¥${hourlyRate}/時 × 8時間 × ${days}日）</small>
                `);
            }
        }
    }
    
    // 勤務日選択設定
    function setupWorkDaysSelection() {
        // 全選択ボタン
        if (!$('#selectAllDays').length) {
            $('#workDaysGroup').before(`
                <div class="mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="selectAllDays">全選択</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="selectWeekdays">平日</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllDays">クリア</button>
                </div>
            `);
        }
        
        $('#selectAllDays').on('click', function() {
            $('#workDaysGroup input[type="checkbox"]').prop('checked', true);
        });
        
        $('#selectWeekdays').on('click', function() {
            $('#workDaysGroup input[type="checkbox"]').prop('checked', false);
            $('#monday, #tuesday, #wednesday, #thursday, #friday').prop('checked', true);
        });
        
        $('#clearAllDays').on('click', function() {
            $('#workDaysGroup input[type="checkbox"]').prop('checked', false);
        });
    }
    
    // 離脱警告設定
    function setupUnloadWarning() {
        $(window).on('beforeunload', function(e) {
            if (formChanged) {
                const message = '入力中のデータが失われる可能性があります。本当にページを離れますか？';
                e.returnValue = message;
                return message;
            }
        });
        
        $('#projectForm').on('submit', function() {
            formChanged = false;
        });
    }
</script>
@endpush
@endsection
