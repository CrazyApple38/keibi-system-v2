@extends('layouts.app')

@section('title', '警備員編集 - ' . $guard->name)

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('guards.index') }}">警備員管理</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('guards.show', $guard) }}">{{ $guard->name }}</a></li>
                    <li class="breadcrumb-item active">編集</li>
                </ol>
            </nav>
            <h2 class="mb-1">
                <i class="bi bi-pencil me-2"></i>
                {{ $guard->name }} の情報編集
            </h2>
            <p class="text-muted mb-0">社員ID: {{ $guard->employee_id }}</p>
        </div>
    </div>
    
    <!-- 編集フォーム -->
    <form action="{{ route('guards.update', $guard) }}" method="POST" enctype="multipart/form-data" id="guardEditForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- メインフォーム -->
            <div class="col-lg-8 col-md-12">
                <!-- 基本情報 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-person me-2"></i>
                            基本情報
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- 社員ID -->
                            <div class="col-md-4">
                                <label for="employee_id" class="form-label required">社員ID</label>
                                <input type="text" class="form-control @error('employee_id') is-invalid @enderror" 
                                       id="employee_id" name="employee_id" value="{{ old('employee_id', $guard->employee_id) }}" 
                                       required>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 所属会社 -->
                            <div class="col-md-4">
                                <label for="company" class="form-label required">所属会社</label>
                                <select class="form-select @error('company') is-invalid @enderror" 
                                        id="company" name="company" required>
                                    <option value="">選択してください</option>
                                    <option value="touo_security" {{ old('company', $guard->company) === 'touo_security' ? 'selected' : '' }}>
                                        ㈲東央警備
                                    </option>
                                    <option value="nikkei_hd" {{ old('company', $guard->company) === 'nikkei_hd' ? 'selected' : '' }}>
                                        ㈱Nikkeiホールディングス
                                    </option>
                                    <option value="zennichi_ep" {{ old('company', $guard->company) === 'zennichi_ep' ? 'selected' : '' }}>
                                        ㈱全日本エンタープライズ
                                    </option>
                                </select>
                                @error('company')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- ステータス -->
                            <div class="col-md-4">
                                <label for="status" class="form-label required">ステータス</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="active" {{ old('status', $guard->status) === 'active' ? 'selected' : '' }}>
                                        稼働中
                                    </option>
                                    <option value="standby" {{ old('status', $guard->status) === 'standby' ? 'selected' : '' }}>
                                        待機中
                                    </option>
                                    <option value="break" {{ old('status', $guard->status) === 'break' ? 'selected' : '' }}>
                                        休憩中
                                    </option>
                                    <option value="off" {{ old('status', $guard->status) === 'off' ? 'selected' : '' }}>
                                        退勤
                                    </option>
                                    <option value="training" {{ old('status', $guard->status) === 'training' ? 'selected' : '' }}>
                                        研修中
                                    </option>
                                    <option value="inactive" {{ old('status', $guard->status) === 'inactive' ? 'selected' : '' }}>
                                        非稼働
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 氏名 -->
                            <div class="col-md-6">
                                <label for="name" class="form-label required">氏名</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $guard->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 氏名（カナ） -->
                            <div class="col-md-6">
                                <label for="name_kana" class="form-label required">氏名（カナ）</label>
                                <input type="text" class="form-control @error('name_kana') is-invalid @enderror" 
                                       id="name_kana" name="name_kana" value="{{ old('name_kana', $guard->name_kana) }}" required>
                                @error('name_kana')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 生年月日 -->
                            <div class="col-md-4">
                                <label for="birth_date" class="form-label required">生年月日</label>
                                <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                       id="birth_date" name="birth_date" 
                                       value="{{ old('birth_date', $guard->birth_date ? $guard->birth_date->format('Y-m-d') : '') }}" required>
                                @error('birth_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 性別 -->
                            <div class="col-md-4">
                                <label for="gender" class="form-label required">性別</label>
                                <select class="form-select @error('gender') is-invalid @enderror" 
                                        id="gender" name="gender" required>
                                    <option value="">選択してください</option>
                                    <option value="male" {{ old('gender', $guard->gender) === 'male' ? 'selected' : '' }}>男性</option>
                                    <option value="female" {{ old('gender', $guard->gender) === 'female' ? 'selected' : '' }}>女性</option>
                                    <option value="other" {{ old('gender', $guard->gender) === 'other' ? 'selected' : '' }}>その他</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 年齢（自動計算） -->
                            <div class="col-md-4">
                                <label class="form-label">年齢</label>
                                <input type="text" class="form-control" id="age" name="age" 
                                       value="{{ old('age', $guard->age) }}" readonly style="background-color: #f8f9fa;">
                                <div class="form-text">生年月日から自動計算されます</div>
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
                        <div class="row g-3">
                            <!-- 電話番号 -->
                            <div class="col-md-6">
                                <label for="phone" class="form-label required">電話番号</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $guard->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- メールアドレス -->
                            <div class="col-md-6">
                                <label for="email" class="form-label">メールアドレス</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $guard->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 緊急連絡先 -->
                            <div class="col-md-6">
                                <label for="emergency_contact" class="form-label">緊急連絡先</label>
                                <input type="tel" class="form-control @error('emergency_contact') is-invalid @enderror" 
                                       id="emergency_contact" name="emergency_contact" 
                                       value="{{ old('emergency_contact', $guard->emergency_contact) }}">
                                @error('emergency_contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 緊急連絡先（続柄） -->
                            <div class="col-md-6">
                                <label for="emergency_contact_relation" class="form-label">緊急連絡先（続柄）</label>
                                <input type="text" class="form-control @error('emergency_contact_relation') is-invalid @enderror" 
                                       id="emergency_contact_relation" name="emergency_contact_relation" 
                                       value="{{ old('emergency_contact_relation', $guard->emergency_contact_relation) }}">
                                @error('emergency_contact_relation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 住所情報 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-geo-alt me-2"></i>
                            住所情報
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- 郵便番号 -->
                            <div class="col-md-3">
                                <label for="postal_code" class="form-label">郵便番号</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       id="postal_code" name="postal_code" 
                                       value="{{ old('postal_code', $guard->postal_code) }}" 
                                       pattern="[0-9]{3}-[0-9]{4}">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 都道府県 -->
                            <div class="col-md-3">
                                <label for="prefecture" class="form-label">都道府県</label>
                                <select class="form-select @error('prefecture') is-invalid @enderror" 
                                        id="prefecture" name="prefecture">
                                    <option value="">選択してください</option>
                                    <option value="東京都" {{ old('prefecture', $guard->prefecture) === '東京都' ? 'selected' : '' }}>東京都</option>
                                    <option value="神奈川県" {{ old('prefecture', $guard->prefecture) === '神奈川県' ? 'selected' : '' }}>神奈川県</option>
                                    <option value="千葉県" {{ old('prefecture', $guard->prefecture) === '千葉県' ? 'selected' : '' }}>千葉県</option>
                                    <option value="埼玉県" {{ old('prefecture', $guard->prefecture) === '埼玉県' ? 'selected' : '' }}>埼玉県</option>
                                </select>
                                @error('prefecture')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 市区町村 -->
                            <div class="col-md-6">
                                <label for="city" class="form-label">市区町村</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                       id="city" name="city" value="{{ old('city', $guard->city) }}">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 住所詳細 -->
                            <div class="col-12">
                                <label for="address" class="form-label">住所詳細</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                       id="address" name="address" value="{{ old('address', $guard->address) }}">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 雇用情報 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-briefcase me-2"></i>
                            雇用情報
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- 入社日 -->
                            <div class="col-md-4">
                                <label for="hire_date" class="form-label required">入社日</label>
                                <input type="date" class="form-control @error('hire_date') is-invalid @enderror" 
                                       id="hire_date" name="hire_date" 
                                       value="{{ old('hire_date', $guard->hire_date ? $guard->hire_date->format('Y-m-d') : '') }}" required>
                                @error('hire_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 雇用形態 -->
                            <div class="col-md-4">
                                <label for="employment_type" class="form-label required">雇用形態</label>
                                <select class="form-select @error('employment_type') is-invalid @enderror" 
                                        id="employment_type" name="employment_type" required>
                                    <option value="">選択してください</option>
                                    <option value="full_time" {{ old('employment_type', $guard->employment_type) === 'full_time' ? 'selected' : '' }}>正社員</option>
                                    <option value="part_time" {{ old('employment_type', $guard->employment_type) === 'part_time' ? 'selected' : '' }}>パート</option>
                                    <option value="contract" {{ old('employment_type', $guard->employment_type) === 'contract' ? 'selected' : '' }}>契約社員</option>
                                    <option value="temporary" {{ old('employment_type', $guard->employment_type) === 'temporary' ? 'selected' : '' }}>派遣社員</option>
                                </select>
                                @error('employment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 時給 -->
                            <div class="col-md-4">
                                <label for="hourly_rate" class="form-label required">時給</label>
                                <div class="input-group">
                                    <span class="input-group-text">¥</span>
                                    <input type="number" class="form-control @error('hourly_rate') is-invalid @enderror" 
                                           id="hourly_rate" name="hourly_rate" 
                                           value="{{ old('hourly_rate', $guard->hourly_rate) }}" 
                                           required min="900" max="10000">
                                    <span class="input-group-text">/時</span>
                                </div>
                                @error('hourly_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 経験年数 -->
                            <div class="col-md-6">
                                <label for="experience_years" class="form-label">警備業務経験年数</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('experience_years') is-invalid @enderror" 
                                           id="experience_years" name="experience_years" 
                                           value="{{ old('experience_years', $guard->experience_years) }}" 
                                           min="0" max="50" step="0.5">
                                    <span class="input-group-text">年</span>
                                </div>
                                @error('experience_years')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 前職 -->
                            <div class="col-md-6">
                                <label for="previous_job" class="form-label">前職</label>
                                <input type="text" class="form-control @error('previous_job') is-invalid @enderror" 
                                       id="previous_job" name="previous_job" 
                                       value="{{ old('previous_job', $guard->previous_job) }}">
                                @error('previous_job')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 変更履歴 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            変更履歴
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="change_reason" class="form-label">変更理由</label>
                            <textarea class="form-control" id="change_reason" name="change_reason" 
                                      rows="3" placeholder="なぜこの変更を行うのか、理由を記載してください">{{ old('change_reason') }}</textarea>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                最終更新: {{ $guard->updated_at ? $guard->updated_at->format('Y年n月j日 H:i') : '未更新' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- サイドバー -->
            <div class="col-lg-4 col-md-12">
                <!-- プロフィール写真 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-camera me-2"></i>
                            プロフィール写真
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @if($guard->profile_photo)
                                <div id="currentPhoto">
                                    <img src="{{ Storage::url($guard->profile_photo) }}" 
                                         class="img-fluid rounded-circle" 
                                         style="width: 150px; height: 150px; object-fit: cover;" 
                                         alt="現在のプロフィール写真">
                                </div>
                            @endif
                            <div id="photoPreview" class="{{ $guard->profile_photo ? 'd-none' : '' }}">
                                <img id="previewImage" src="" class="img-fluid rounded-circle" 
                                     style="width: 150px; height: 150px; object-fit: cover;" alt="プロフィール写真">
                            </div>
                            <div id="photoPlaceholder" class="{{ $guard->profile_photo ? 'd-none' : '' }} bg-light border rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                 style="width: 150px; height: 150px;">
                                <i class="bi bi-camera fs-1 text-muted"></i>
                            </div>
                        </div>
                        <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" 
                               id="profile_photo" name="profile_photo" accept="image/*">
                        @error('profile_photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">JPG, PNG形式。最大2MB。</div>
                        @if($guard->profile_photo)
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="remove_photo" name="remove_photo" value="1">
                                <label class="form-check-label" for="remove_photo">
                                    現在の写真を削除
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- 保有資格 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-award me-2"></i>
                            保有資格
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $currentQualifications = old('qualifications', $guard->qualifications ?? []);
                        @endphp
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="qualifications[]" 
                                   value="guard_license" id="qual_guard_license"
                                   {{ in_array('guard_license', $currentQualifications) ? 'checked' : '' }}>
                            <label class="form-check-label" for="qual_guard_license">
                                警備員検定（1級・2級）
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="qualifications[]" 
                                   value="traffic_control" id="qual_traffic_control"
                                   {{ in_array('traffic_control', $currentQualifications) ? 'checked' : '' }}>
                            <label class="form-check-label" for="qual_traffic_control">
                                交通誘導警備業務検定
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="qualifications[]" 
                                   value="facility_security" id="qual_facility_security"
                                   {{ in_array('facility_security', $currentQualifications) ? 'checked' : '' }}>
                            <label class="form-check-label" for="qual_facility_security">
                                施設警備業務検定
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="qualifications[]" 
                                   value="bodyguard" id="qual_bodyguard"
                                   {{ in_array('bodyguard', $currentQualifications) ? 'checked' : '' }}>
                            <label class="form-check-label" for="qual_bodyguard">
                                身辺警備業務検定
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="qualifications[]" 
                                   value="machine_security" id="qual_machine_security"
                                   {{ in_array('machine_security', $currentQualifications) ? 'checked' : '' }}>
                            <label class="form-check-label" for="qual_machine_security">
                                機械警備業務検定
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="qualifications[]" 
                                   value="nuclear_security" id="qual_nuclear_security"
                                   {{ in_array('nuclear_security', $currentQualifications) ? 'checked' : '' }}>
                            <label class="form-check-label" for="qual_nuclear_security">
                                核燃料物質等危険物警備業務検定
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="qualifications[]" 
                                   value="first_aid" id="qual_first_aid"
                                   {{ in_array('first_aid', $currentQualifications) ? 'checked' : '' }}>
                            <label class="form-check-label" for="qual_first_aid">
                                普通救命講習修了
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="qualifications[]" 
                                   value="driving_license" id="qual_driving_license"
                                   {{ in_array('driving_license', $currentQualifications) ? 'checked' : '' }}>
                            <label class="form-check-label" for="qual_driving_license">
                                普通自動車運転免許
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- スキル・特技 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-star me-2"></i>
                            スキル・特技
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $currentSkills = old('skills', $guard->skills ?? []);
                        @endphp
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="skills[]" 
                                   value="patrol" id="skill_patrol"
                                   {{ in_array('patrol', $currentSkills) ? 'checked' : '' }}>
                            <label class="form-check-label" for="skill_patrol">
                                巡回警備
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="skills[]" 
                                   value="traffic" id="skill_traffic"
                                   {{ in_array('traffic', $currentSkills) ? 'checked' : '' }}>
                            <label class="form-check-label" for="skill_traffic">
                                交通誘導
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="skills[]" 
                                   value="event" id="skill_event"
                                   {{ in_array('event', $currentSkills) ? 'checked' : '' }}>
                            <label class="form-check-label" for="skill_event">
                                イベント警備
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="skills[]" 
                                   value="vip" id="skill_vip"
                                   {{ in_array('vip', $currentSkills) ? 'checked' : '' }}>
                            <label class="form-check-label" for="skill_vip">
                                VIP警備
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="skills[]" 
                                   value="construction" id="skill_construction"
                                   {{ in_array('construction', $currentSkills) ? 'checked' : '' }}>
                            <label class="form-check-label" for="skill_construction">
                                工事現場警備
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="skills[]" 
                                   value="night_shift" id="skill_night_shift"
                                   {{ in_array('night_shift', $currentSkills) ? 'checked' : '' }}>
                            <label class="form-check-label" for="skill_night_shift">
                                夜勤対応可能
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="skills[]" 
                                   value="multilingual" id="skill_multilingual"
                                   {{ in_array('multilingual', $currentSkills) ? 'checked' : '' }}>
                            <label class="form-check-label" for="skill_multilingual">
                                多言語対応（英語等）
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- 備考 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-chat-text me-2"></i>
                            備考
                        </h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="4" 
                                  placeholder="特記事項、アレルギー、健康状態、その他注意事項など">{{ old('notes', $guard->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <!-- フォーム送信ボタン -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('guards.show', $guard) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                戻る
                            </a>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                                    <i class="bi bi-trash me-1"></i>
                                    削除
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                    <i class="bi bi-check me-1"></i>
                                    更新する
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- 削除確認モーダル -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">警備員削除の確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>注意：この操作は取り消せません</strong>
                </div>
                <p>「<strong>{{ $guard->name }}</strong>」を削除してもよろしいですか？</p>
                <p class="text-muted">関連するシフト、勤怠記録、評価データも同時に削除されます。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-danger" onclick="executeDelete()">
                    <i class="bi bi-trash me-1"></i>
                    削除実行
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .required::after {
        content: " *";
        color: #dc3545;
    }
    
    .form-check-input:checked {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
    }
    
    #currentPhoto img,
    #photoPreview img {
        border: 3px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    #currentPhoto img:hover,
    #photoPreview img:hover {
        border-color: var(--bs-primary);
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    @media (max-width: 768px) {
        .col-md-4, .col-md-6 {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // 生年月日から年齢を自動計算
        $('#birth_date').change(function() {
            const birthDate = new Date($(this).val());
            const today = new Date();
            const age = Math.floor((today - birthDate) / (365.25 * 24 * 60 * 60 * 1000));
            
            if (!isNaN(age) && age >= 0 && age <= 120) {
                $('#age').val(age);
            } else {
                $('#age').val('');
            }
        });
        
        // 初期読み込み時に年齢を計算
        if ($('#birth_date').val()) {
            $('#birth_date').trigger('change');
        }
        
        // プロフィール写真プレビュー
        $('#profile_photo').change(function(e) {
            const file = e.target.files[0];
            if (file) {
                // ファイルサイズチェック（2MB）
                if (file.size > 2 * 1024 * 1024) {
                    alert('ファイルサイズは2MB以下にしてください。');
                    $(this).val('');
                    return;
                }
                
                // 画像形式チェック
                if (!file.type.match('image.*')) {
                    alert('画像ファイルを選択してください。');
                    $(this).val('');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImage').attr('src', e.target.result);
                    $('#photoPreview').removeClass('d-none');
                    $('#currentPhoto, #photoPlaceholder').addClass('d-none');
                    $('#remove_photo').prop('checked', false);
                };
                reader.readAsDataURL(file);
            } else {
                $('#photoPreview').addClass('d-none');
                @if($guard->profile_photo)
                    $('#currentPhoto').removeClass('d-none');
                @else
                    $('#photoPlaceholder').removeClass('d-none');
                @endif
            }
        });
        
        // 写真削除チェックボックス
        $('#remove_photo').change(function() {
            if ($(this).is(':checked')) {
                $('#currentPhoto').addClass('d-none');
                $('#photoPlaceholder').removeClass('d-none');
                $('#profile_photo').val('');
            } else {
                $('#currentPhoto').removeClass('d-none');
                $('#photoPlaceholder').addClass('d-none');
            }
        });
        
        // 郵便番号自動整形
        $('#postal_code').on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, '');
            if (value.length >= 3) {
                value = value.slice(0, 3) + '-' + value.slice(3, 7);
            }
            $(this).val(value);
        });
        
        // 電話番号自動整形
        $('#phone, #emergency_contact').on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, '');
            if (value.length >= 3 && value.length <= 11) {
                if (value.startsWith('0')) {
                    if (value.length <= 4) {
                        // 000-
                    } else if (value.length <= 8) {
                        value = value.slice(0, 3) + '-' + value.slice(3);
                    } else {
                        value = value.slice(0, 3) + '-' + value.slice(3, 7) + '-' + value.slice(7);
                    }
                }
            }
            $(this).val(value);
        });
        
        // フォーム送信前のバリデーション
        $('#guardEditForm').on('submit', function(e) {
            e.preventDefault();
            
            // 必須項目チェック
            const requiredFields = ['employee_id', 'company', 'status', 'name', 'name_kana', 
                                  'birth_date', 'gender', 'phone', 'hire_date', 'employment_type', 'hourly_rate'];
            
            let isValid = true;
            requiredFields.forEach(field => {
                const element = $(`#${field}`);
                if (!element.val()) {
                    element.addClass('is-invalid');
                    isValid = false;
                } else {
                    element.removeClass('is-invalid');
                }
            });
            
            if (!isValid) {
                alert('必須項目を入力してください。');
                return;
            }
            
            // ローディング表示
            const submitBtn = $(this).find('button[type="submit"]');
            const spinner = submitBtn.find('.spinner-border');
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');
            
            // フォーム送信
            this.submit();
        });
        
        // 変更検知（未保存の変更があることを警告）
        let isFormChanged = false;
        
        $('#guardEditForm input, #guardEditForm select, #guardEditForm textarea').on('input change', function() {
            isFormChanged = true;
        });
        
        $(window).on('beforeunload', function(e) {
            if (isFormChanged) {
                const message = '変更が保存されていません。このページを離れてもよろしいですか？';
                e.returnValue = message;
                return message;
            }
        });
        
        $('#guardEditForm').on('submit', function() {
            isFormChanged = false;
        });
    });
    
    // 削除確認
    function confirmDelete() {
        $('#deleteConfirmModal').modal('show');
    }
    
    // 削除実行
    function executeDelete() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("guards.destroy", $guard) }}';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        
        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        
        form.submit();
    }
</script>
@endpush
@endsection