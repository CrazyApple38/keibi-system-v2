@extends('layouts.app')

@section('title', 'シフト作成')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('shifts.index') }}">シフト管理</a></li>
                    <li class="breadcrumb-item active">新規作成</li>
                </ol>
            </nav>
            <h2 class="mb-1">
                <i class="bi bi-calendar-plus me-2"></i>
                シフト作成
            </h2>
            <p class="text-muted mb-0">新しいシフトの作成・警備員配置</p>
        </div>
    </div>
    
    <!-- シフト作成フォーム -->
    <form action="{{ route('shifts.store') }}" method="POST" id="shiftForm">
        @csrf
        
        <div class="row">
            <!-- メインフォーム -->
            <div class="col-lg-8 col-md-12">
                <!-- 基本情報 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar me-2"></i>
                            基本情報
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- シフトタイプ -->
                            <div class="col-12">
                                <label class="form-label required">シフトタイプ</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="shift_type" id="single_shift" value="single" checked>
                                    <label class="btn btn-outline-primary" for="single_shift">
                                        <i class="bi bi-calendar-day me-1"></i>単発シフト
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="shift_type" id="recurring_shift" value="recurring">
                                    <label class="btn btn-outline-primary" for="recurring_shift">
                                        <i class="bi bi-arrow-repeat me-1"></i>定期シフト
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="shift_type" id="template_shift" value="template">
                                    <label class="btn btn-outline-primary" for="template_shift">
                                        <i class="bi bi-copy me-1"></i>テンプレート使用
                                    </label>
                                </div>
                            </div>
                            
                            <!-- プロジェクト -->
                            <div class="col-md-6">
                                <label for="project_id" class="form-label required">プロジェクト</label>
                                <select class="form-select @error('project_id') is-invalid @enderror" 
                                        id="project_id" name="project_id" required>
                                    <option value="">選択してください</option>
                                    @foreach($projects ?? [] as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}
                                                data-location="{{ $project->location }}" 
                                                data-default-guards="{{ $project->default_guards }}">
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- ステータス -->
                            <div class="col-md-6">
                                <label for="status" class="form-label required">ステータス</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="scheduled" {{ old('status', 'scheduled') === 'scheduled' ? 'selected' : '' }}>
                                        予定
                                    </option>
                                    <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>
                                        下書き
                                    </option>
                                    <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>
                                        キャンセル
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 日付設定（単発シフト） -->
                            <div id="singleShiftDates" class="col-12">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="start_date" class="form-label required">開始日</label>
                                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                               id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="end_date" class="form-label">終了日</label>
                                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                               id="end_date" name="end_date" value="{{ old('end_date') }}">
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">空白の場合は開始日と同じ</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">曜日</label>
                                        <input type="text" class="form-control" id="day_of_week" readonly 
                                               style="background-color: #f8f9fa;">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 定期シフト設定 -->
                            <div id="recurringShiftSettings" class="col-12 d-none">
                                <div class="card border-info">
                                    <div class="card-header bg-info bg-opacity-10">
                                        <h6 class="mb-0">定期シフト設定</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="recurring_start_date" class="form-label">開始日</label>
                                                <input type="date" class="form-control" id="recurring_start_date" 
                                                       name="recurring_start_date" value="{{ old('recurring_start_date', date('Y-m-d')) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="recurring_end_date" class="form-label">終了日</label>
                                                <input type="date" class="form-control" id="recurring_end_date" 
                                                       name="recurring_end_date" value="{{ old('recurring_end_date') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="recurring_pattern" class="form-label">繰り返しパターン</label>
                                                <select class="form-select" id="recurring_pattern" name="recurring_pattern">
                                                    <option value="daily">毎日</option>
                                                    <option value="weekly" selected>毎週</option>
                                                    <option value="monthly">毎月</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">繰り返し曜日</label>
                                                <div class="d-flex gap-2">
                                                    @foreach(['monday' => '月', 'tuesday' => '火', 'wednesday' => '水', 'thursday' => '木', 'friday' => '金', 'saturday' => '土', 'sunday' => '日'] as $day => $label)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="recurring_days[]" 
                                                               value="{{ $day }}" id="day_{{ $day }}">
                                                        <label class="form-check-label" for="day_{{ $day }}">{{ $label }}</label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- テンプレート選択 -->
                            <div id="templateSelection" class="col-12 d-none">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning bg-opacity-10">
                                        <h6 class="mb-0">テンプレート選択</h6>
                                    </div>
                                    <div class="card-body">
                                        <select class="form-select" id="template_id" name="template_id">
                                            <option value="">テンプレートを選択してください</option>
                                            @foreach($templates ?? [] as $template)
                                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 時間設定 -->
                            <div class="col-md-6">
                                <label for="start_time" class="form-label required">開始時間</label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                       id="start_time" name="start_time" value="{{ old('start_time', '09:00') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="end_time" class="form-label required">終了時間</label>
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                       id="end_time" name="end_time" value="{{ old('end_time', '18:00') }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">勤務時間: <span id="workingHours">9時間</span></div>
                            </div>
                            
                            <!-- 休憩時間 -->
                            <div class="col-md-6">
                                <label for="break_time" class="form-label">休憩時間（分）</label>
                                <input type="number" class="form-control @error('break_time') is-invalid @enderror" 
                                       id="break_time" name="break_time" value="{{ old('break_time', 60) }}" 
                                       min="0" max="480" step="15">
                                @error('break_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 必要警備員数 -->
                            <div class="col-md-6">
                                <label for="required_guards" class="form-label required">必要警備員数</label>
                                <input type="number" class="form-control @error('required_guards') is-invalid @enderror" 
                                       id="required_guards" name="required_guards" value="{{ old('required_guards', 1) }}" 
                                       min="1" max="50" required>
                                @error('required_guards')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 警備員配置 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-people me-2"></i>
                                警備員配置
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="autoAssign">
                                <i class="bi bi-cpu me-1"></i>
                                自動配置
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- 検索・フィルター -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="guardSearch" 
                                       placeholder="警備員名で検索">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="guardSkillFilter">
                                    <option value="">スキル絞り込み</option>
                                    <option value="patrol">巡回警備</option>
                                    <option value="traffic">交通誘導</option>
                                    <option value="event">イベント警備</option>
                                    <option value="vip">VIP警備</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="guardExperienceFilter">
                                    <option value="">経験年数</option>
                                    <option value="0-1">1年未満</option>
                                    <option value="1-3">1-3年</option>
                                    <option value="3-5">3-5年</option>
                                    <option value="5+">5年以上</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">
                                    <i class="bi bi-x-circle me-1"></i>
                                    クリア
                                </button>
                            </div>
                        </div>
                        
                        <!-- 利用可能な警備員一覧 -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>利用可能な警備員</h6>
                                <div class="border rounded p-3" style="height: 300px; overflow-y: auto;" id="availableGuards">
                                    <!-- 警備員リストはJavaScriptで動的に読み込み -->
                                    <div class="text-center text-muted py-5">
                                        <i class="bi bi-clock-history display-6"></i>
                                        <div class="mt-2">日付と時間を設定してください</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>配置済み警備員 <span class="badge bg-primary" id="assignedCount">0</span></h6>
                                <div class="border rounded p-3" style="height: 300px; overflow-y: auto;" id="assignedGuards">
                                    <!-- 配置済み警備員リストはJavaScriptで管理 -->
                                    <div class="text-center text-muted py-5">
                                        <i class="bi bi-person-plus display-6"></i>
                                        <div class="mt-2">警備員を配置してください</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 推奨警備員 -->
                        <div id="recommendedGuards" class="d-none">
                            <h6>推奨警備員</h6>
                            <div class="row" id="recommendedGuardsList">
                                <!-- 推奨警備員はJavaScriptで表示 -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 追加情報 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            追加情報
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- 特別な要件 -->
                            <div class="col-md-6">
                                <label class="form-label">特別な要件</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="requirements[]" 
                                           value="guard_license" id="req_guard_license">
                                    <label class="form-check-label" for="req_guard_license">
                                        警備員検定必須
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="requirements[]" 
                                           value="experience_required" id="req_experience">
                                    <label class="form-check-label" for="req_experience">
                                        経験者優先
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="requirements[]" 
                                           value="multilingual" id="req_multilingual">
                                    <label class="form-check-label" for="req_multilingual">
                                        多言語対応
                                    </label>
                                </div>
                            </div>
                            
                            <!-- 時給設定 -->
                            <div class="col-md-6">
                                <label for="hourly_rate" class="form-label">時給（オーバーライド）</label>
                                <div class="input-group">
                                    <span class="input-group-text">¥</span>
                                    <input type="number" class="form-control @error('hourly_rate') is-invalid @enderror" 
                                           id="hourly_rate" name="hourly_rate" value="{{ old('hourly_rate') }}" 
                                           min="900" max="10000" placeholder="個別設定">
                                    <span class="input-group-text">/時</span>
                                </div>
                                @error('hourly_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">空白の場合は警備員の基本時給を使用</div>
                            </div>
                            
                            <!-- 備考 -->
                            <div class="col-12">
                                <label for="notes" class="form-label">備考</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="特記事項、注意点など">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- サイドバー -->
            <div class="col-lg-4 col-md-12">
                <!-- プレビュー -->
                <div class="card mb-4 sticky-top">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-eye me-2"></i>
                            シフトプレビュー
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="shiftPreview">
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-calendar3 display-6"></i>
                                <div class="mt-2">情報を入力してください</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 費用計算 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-calculator me-2"></i>
                            費用計算
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted">基本料金</small>
                                <div class="fw-bold" id="basicCost">¥0</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">時間外料金</small>
                                <div class="fw-bold" id="overtimeCost">¥0</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">深夜料金</small>
                                <div class="fw-bold" id="nightCost">¥0</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">休日料金</small>
                                <div class="fw-bold" id="holidayCost">¥0</div>
                            </div>
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>合計費用</strong>
                                    <strong class="text-primary" id="totalCost">¥0</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 競合チェック -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            競合チェック
                        </h5>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-outline-warning w-100 mb-3" id="checkConflicts">
                            <i class="bi bi-search me-1"></i>
                            競合をチェック
                        </button>
                        <div id="conflictResults">
                            <div class="text-center text-muted">
                                <i class="bi bi-shield-check display-6"></i>
                                <div class="mt-2">競合はありません</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- クイックアクション -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning me-2"></i>
                            クイックアクション
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-info" id="saveAsDraft">
                                <i class="bi bi-file-earmark me-1"></i>
                                下書き保存
                            </button>
                            <button type="button" class="btn btn-outline-success" id="saveAsTemplate">
                                <i class="bi bi-star me-1"></i>
                                テンプレート保存
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="copyFromPrevious">
                                <i class="bi bi-copy me-1"></i>
                                前回シフト複製
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 警備員配置データ（隠しフィールド） -->
        <input type="hidden" name="assigned_guards" id="assignedGuardsData" value="">
        
        <!-- フォーム送信ボタン -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('shifts.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                戻る
                            </a>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" onclick="validateForm()">
                                    <i class="bi bi-check-circle me-1"></i>
                                    入力確認
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                    <i class="bi bi-calendar-plus me-1"></i>
                                    シフト作成
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .required::after {
        content: " *";
        color: #dc3545;
    }
    
    .guard-item {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .guard-item:hover {
        border-color: var(--bs-primary);
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    .guard-item.selected {
        border-color: var(--bs-success);
        background-color: rgba(25, 135, 84, 0.1);
    }
    
    .guard-item.recommended {
        border-color: var(--bs-warning);
        background-color: rgba(255, 193, 7, 0.1);
    }
    
    .guard-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .sticky-top {
        top: 1rem;
    }
    
    .form-check-input:checked {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
    }
    
    @media (max-width: 768px) {
        .sticky-top {
            position: relative !important;
            top: auto !important;
        }
        
        .col-md-6, .col-md-4, .col-md-3 {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // 初期化
        initializeForm();
        
        // シフトタイプ変更
        $('input[name="shift_type"]').change(function() {
            const shiftType = $(this).val();
            toggleShiftTypeFields(shiftType);
        });
        
        // 日付・時間変更時の処理
        $('#start_date, #end_date, #start_time, #end_time').change(function() {
            updateDayOfWeek();
            updateWorkingHours();
            updateShiftPreview();
            updateCostCalculation();
            loadAvailableGuards();
        });
        
        // プロジェクト変更時の処理
        $('#project_id').change(function() {
            updateProjectDefaults();
            updateShiftPreview();
            loadAvailableGuards();
        });
        
        // 必要警備員数変更
        $('#required_guards').change(function() {
            updateShiftPreview();
            updateCostCalculation();
        });
        
        // 自動配置
        $('#autoAssign').click(function() {
            autoAssignGuards();
        });
        
        // 警備員検索・フィルター
        $('#guardSearch').on('input', function() {
            filterGuards();
        });
        
        $('#guardSkillFilter, #guardExperienceFilter').change(function() {
            filterGuards();
        });
        
        $('#clearFilters').click(function() {
            $('#guardSearch').val('');
            $('#guardSkillFilter, #guardExperienceFilter').val('');
            filterGuards();
        });
        
        // 競合チェック
        $('#checkConflicts').click(function() {
            checkConflicts();
        });
        
        // フォーム送信
        $('#shiftForm').on('submit', function(e) {
            e.preventDefault();
            if (validateForm()) {
                updateAssignedGuardsData();
                showLoading();
                this.submit();
            }
        });
        
        // クイックアクション
        $('#saveAsDraft').click(function() {
            saveAsDraft();
        });
        
        $('#saveAsTemplate').click(function() {
            saveAsTemplate();
        });
        
        $('#copyFromPrevious').click(function() {
            copyFromPrevious();
        });
    });
    
    // フォーム初期化
    function initializeForm() {
        updateDayOfWeek();
        updateWorkingHours();
        updateShiftPreview();
        toggleShiftTypeFields('single');
    }
    
    // シフトタイプフィールド切り替え
    function toggleShiftTypeFields(shiftType) {
        $('#singleShiftDates, #recurringShiftSettings, #templateSelection').addClass('d-none');
        
        switch(shiftType) {
            case 'single':
                $('#singleShiftDates').removeClass('d-none');
                break;
            case 'recurring':
                $('#recurringShiftSettings').removeClass('d-none');
                break;
            case 'template':
                $('#templateSelection').removeClass('d-none');
                break;
        }
    }
    
    // 曜日更新
    function updateDayOfWeek() {
        const startDate = $('#start_date').val();
        if (startDate) {
            const date = new Date(startDate);
            const days = ['日', '月', '火', '水', '木', '金', '土'];
            $('#day_of_week').val(days[date.getDay()] + '曜日');
        }
    }
    
    // 勤務時間計算
    function updateWorkingHours() {
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        const breakTime = parseInt($('#break_time').val()) || 0;
        
        if (startTime && endTime) {
            const start = new Date(`2000-01-01 ${startTime}`);
            const end = new Date(`2000-01-01 ${endTime}`);
            
            let diffMs = end - start;
            if (diffMs < 0) {
                diffMs += 24 * 60 * 60 * 1000; // 翌日にまたがる場合
            }
            
            const diffHours = diffMs / (1000 * 60 * 60);
            const workingHours = diffHours - (breakTime / 60);
            
            $('#workingHours').text(workingHours.toFixed(1) + '時間');
        }
    }
    
    // プロジェクトデフォルト値更新
    function updateProjectDefaults() {
        const selectedOption = $('#project_id option:selected');
        const defaultGuards = selectedOption.data('default-guards');
        
        if (defaultGuards) {
            $('#required_guards').val(defaultGuards);
        }
    }
    
    // シフトプレビュー更新
    function updateShiftPreview() {
        const project = $('#project_id option:selected').text();
        const startDate = $('#start_date').val();
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        const requiredGuards = $('#required_guards').val();
        
        if (project && startDate && startTime && endTime) {
            const preview = `
                <div class="text-center">
                    <h6>${project}</h6>
                    <div class="mb-2">
                        <i class="bi bi-calendar me-1"></i>
                        ${new Date(startDate).toLocaleDateString('ja-JP')}
                    </div>
                    <div class="mb-2">
                        <i class="bi bi-clock me-1"></i>
                        ${startTime} - ${endTime}
                    </div>
                    <div class="mb-2">
                        <i class="bi bi-people me-1"></i>
                        ${requiredGuards}名必要
                    </div>
                    <div class="badge bg-success">配置済み: <span id="assignedCountPreview">0</span>名</div>
                </div>
            `;
            $('#shiftPreview').html(preview);
        }
    }
    
    // 費用計算更新
    function updateCostCalculation() {
        // 費用計算ロジック（実装省略）
        console.log('費用計算を更新中...');
    }
    
    // 利用可能な警備員読み込み
    function loadAvailableGuards() {
        const startDate = $('#start_date').val();
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        
        if (!startDate || !startTime || !endTime) {
            return;
        }
        
        $.get('{{ route("shifts.available-guards") }}', {
            start_date: startDate,
            start_time: startTime,
            end_time: endTime
        })
        .done(function(guards) {
            displayAvailableGuards(guards);
        })
        .fail(function() {
            console.error('利用可能な警備員の取得に失敗しました');
        });
    }
    
    // 利用可能な警備員表示
    function displayAvailableGuards(guards) {
        let html = '';
        
        guards.forEach(guard => {
            html += `
                <div class="guard-item" data-guard-id="${guard.id}" onclick="assignGuard(${guard.id})">
                    <div class="d-flex align-items-center">
                        <img src="${guard.avatar || '/default-avatar.png'}" class="guard-avatar me-3" alt="${guard.name}">
                        <div class="flex-grow-1">
                            <div class="fw-bold">${guard.name}</div>
                            <small class="text-muted">${guard.experience_years}年経験</small>
                            <div class="mt-1">
                                ${guard.skills.map(skill => `<span class="badge bg-secondary me-1">${skill}</span>`).join('')}
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">¥${guard.hourly_rate.toLocaleString()}</div>
                            <small class="text-muted">/時</small>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#availableGuards').html(html);
    }
    
    // 警備員配置
    function assignGuard(guardId) {
        // 警備員配置ロジック（実装省略）
        console.log('警備員配置:', guardId);
    }
    
    // 自動配置
    function autoAssignGuards() {
        const requiredGuards = parseInt($('#required_guards').val());
        const requirements = $('input[name="requirements[]"]:checked').map(function() {
            return $(this).val();
        }).get();
        
        $.post('{{ route("shifts.auto-assign") }}', {
            start_date: $('#start_date').val(),
            start_time: $('#start_time').val(),
            end_time: $('#end_time').val(),
            required_guards: requiredGuards,
            requirements: requirements,
            _token: '{{ csrf_token() }}'
        })
        .done(function(assignedGuards) {
            // 自動配置結果を表示
            console.log('自動配置完了:', assignedGuards);
        })
        .fail(function() {
            showErrorMessage('自動配置に失敗しました');
        });
    }
    
    // 警備員フィルター
    function filterGuards() {
        const searchTerm = $('#guardSearch').val().toLowerCase();
        const skillFilter = $('#guardSkillFilter').val();
        const experienceFilter = $('#guardExperienceFilter').val();
        
        $('.guard-item').each(function() {
            const guardName = $(this).find('.fw-bold').text().toLowerCase();
            const guardSkills = $(this).find('.badge').map(function() {
                return $(this).text();
            }).get();
            
            let show = true;
            
            // 名前検索
            if (searchTerm && !guardName.includes(searchTerm)) {
                show = false;
            }
            
            // スキルフィルター
            if (skillFilter && !guardSkills.includes(skillFilter)) {
                show = false;
            }
            
            // 経験年数フィルター（実装省略）
            
            $(this).toggle(show);
        });
    }
    
    // 競合チェック
    function checkConflicts() {
        const formData = {
            start_date: $('#start_date').val(),
            start_time: $('#start_time').val(),
            end_time: $('#end_time').val(),
            assigned_guards: getAssignedGuards(),
            _token: '{{ csrf_token() }}'
        };
        
        $.post('{{ route("shifts.check-conflicts") }}', formData)
            .done(function(conflicts) {
                displayConflictResults(conflicts);
            })
            .fail(function() {
                showErrorMessage('競合チェックに失敗しました');
            });
    }
    
    // 競合結果表示
    function displayConflictResults(conflicts) {
        if (conflicts.length === 0) {
            $('#conflictResults').html(`
                <div class="text-center text-success">
                    <i class="bi bi-shield-check display-6"></i>
                    <div class="mt-2">競合はありません</div>
                </div>
            `);
        } else {
            let html = '<div class="alert alert-warning">';
            html += '<strong>競合が見つかりました：</strong><ul class="mb-0 mt-2">';
            conflicts.forEach(conflict => {
                html += `<li>${conflict.message}</li>`;
            });
            html += '</ul></div>';
            $('#conflictResults').html(html);
        }
    }
    
    // 配置済み警備員データ更新
    function updateAssignedGuardsData() {
        const assignedGuards = getAssignedGuards();
        $('#assignedGuardsData').val(JSON.stringify(assignedGuards));
    }
    
    // 配置済み警備員取得
    function getAssignedGuards() {
        // 配置済み警備員データを取得（実装省略）
        return [];
    }
    
    // バリデーション
    function validateForm() {
        const requiredFields = ['project_id', 'start_date', 'start_time', 'end_time', 'required_guards'];
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
        }
        
        return isValid;
    }
    
    // ローディング表示
    function showLoading() {
        const submitBtn = $('#shiftForm button[type="submit"]');
        const spinner = submitBtn.find('.spinner-border');
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
    }
    
    // 下書き保存
    function saveAsDraft() {
        $('#status').val('draft');
        if (validateForm()) {
            updateAssignedGuardsData();
            showLoading();
            $('#shiftForm').submit();
        }
    }
    
    // テンプレート保存
    function saveAsTemplate() {
        // テンプレート保存モーダル表示（実装省略）
        console.log('テンプレート保存');
    }
    
    // 前回シフト複製
    function copyFromPrevious() {
        // 前回シフト複製モーダル表示（実装省略）
        console.log('前回シフト複製');
    }
</script>
@endpush
@endsection