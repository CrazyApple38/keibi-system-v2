@extends('layouts.app')

@section('title', '勤怠記録作成')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendances.index') }}">勤怠管理</a></li>
                    <li class="breadcrumb-item active">新規作成</li>
                </ol>
            </nav>
            <h2 class="mb-1">
                <i class="bi bi-clock-fill me-2"></i>
                勤怠記録作成
            </h2>
            <p class="text-muted mb-0">新しい勤怠記録の手動登録</p>
        </div>
    </div>
    
    <!-- 勤怠記録作成フォーム -->
    <form action="{{ route('attendances.store') }}" method="POST" id="attendanceForm">
        @csrf
        
        <div class="row">
            <!-- メインフォーム -->
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
                        <div class="row g-3">
                            <!-- 記録作成タイプ -->
                            <div class="col-12">
                                <label class="form-label required">記録作成タイプ</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="creation_type" id="shift_based" value="shift" checked>
                                    <label class="btn btn-outline-primary" for="shift_based">
                                        <i class="bi bi-calendar-check me-1"></i>シフトベース
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="creation_type" id="manual_entry" value="manual">
                                    <label class="btn btn-outline-primary" for="manual_entry">
                                        <i class="bi bi-pencil me-1"></i>手動入力
                                    </label>
                                </div>
                                <div class="form-text">シフトベース：既存シフトから自動入力、手動入力：完全手動</div>
                            </div>
                            
                            <!-- シフト選択（シフトベース） -->
                            <div id="shiftSelection" class="col-12">
                                <label for="shift_id" class="form-label required">対象シフト</label>
                                <select class="form-select @error('shift_id') is-invalid @enderror" 
                                        id="shift_id" name="shift_id" required>
                                    <option value="">選択してください</option>
                                    @foreach($shifts ?? [] as $shift)
                                        <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}
                                                data-project="{{ $shift->project->name ?? '' }}"
                                                data-start-time="{{ $shift->start_time }}"
                                                data-end-time="{{ $shift->end_time }}"
                                                data-date="{{ $shift->start_date ? $shift->start_date->format('Y-m-d') : '' }}">
                                            {{ $shift->start_date ? $shift->start_date->format('n/j') : '' }} 
                                            {{ $shift->project->name ?? '未設定' }} 
                                            ({{ $shift->start_time }}-{{ $shift->end_time }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('shift_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 手動入力フィールド -->
                            <div id="manualFields" class="col-12 d-none">
                                <div class="row g-3">
                                    <!-- 警備員選択 -->
                                    <div class="col-md-6">
                                        <label for="guard_id" class="form-label required">警備員</label>
                                        <select class="form-select @error('guard_id') is-invalid @enderror" 
                                                id="guard_id" name="guard_id">
                                            <option value="">選択してください</option>
                                            @foreach($guards ?? [] as $guard)
                                                <option value="{{ $guard->id }}" {{ old('guard_id') == $guard->id ? 'selected' : '' }}>
                                                    {{ $guard->name }} ({{ $guard->employee_id }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('guard_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- プロジェクト選択 -->
                                    <div class="col-md-6">
                                        <label for="project_id" class="form-label required">プロジェクト</label>
                                        <select class="form-select @error('project_id') is-invalid @enderror" 
                                                id="project_id" name="project_id">
                                            <option value="">選択してください</option>
                                            @foreach($projects ?? [] as $project)
                                                <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                                    {{ $project->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('project_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 勤務日 -->
                            <div class="col-md-4">
                                <label for="date" class="form-label required">勤務日</label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                       id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- ステータス -->
                            <div class="col-md-4">
                                <label for="status" class="form-label required">ステータス</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="present" {{ old('status', 'present') === 'present' ? 'selected' : '' }}>出勤</option>
                                    <option value="absent" {{ old('status') === 'absent' ? 'selected' : '' }}>欠勤</option>
                                    <option value="late" {{ old('status') === 'late' ? 'selected' : '' }}>遅刻</option>
                                    <option value="early_leave" {{ old('status') === 'early_leave' ? 'selected' : '' }}>早退</option>
                                    <option value="overtime" {{ old('status') === 'overtime' ? 'selected' : '' }}>残業</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 承認状態 -->
                            <div class="col-md-4">
                                <label for="approval_status" class="form-label required">承認状態</label>
                                <select class="form-select @error('approval_status') is-invalid @enderror" 
                                        id="approval_status" name="approval_status" required>
                                    <option value="pending" {{ old('approval_status', 'pending') === 'pending' ? 'selected' : '' }}>承認待ち</option>
                                    <option value="approved" {{ old('approval_status') === 'approved' ? 'selected' : '' }}>承認済み</option>
                                    <option value="rejected" {{ old('approval_status') === 'rejected' ? 'selected' : '' }}>却下</option>
                                </select>
                                @error('approval_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 出退勤時間 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clock me-2"></i>
                            出退勤時間
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- 出勤時間 -->
                            <div class="col-md-6">
                                <label for="check_in_time" class="form-label">出勤時間</label>
                                <div class="input-group">
                                    <input type="time" class="form-control @error('check_in_time') is-invalid @enderror" 
                                           id="check_in_time" name="check_in_time" value="{{ old('check_in_time') }}">
                                    <button type="button" class="btn btn-outline-secondary" id="setCurrentTimeIn">
                                        <i class="bi bi-clock-fill"></i>
                                    </button>
                                </div>
                                @error('check_in_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">予定時間: <span id="scheduledStartTime">-</span></div>
                            </div>
                            
                            <!-- 退勤時間 -->
                            <div class="col-md-6">
                                <label for="check_out_time" class="form-label">退勤時間</label>
                                <div class="input-group">
                                    <input type="time" class="form-control @error('check_out_time') is-invalid @enderror" 
                                           id="check_out_time" name="check_out_time" value="{{ old('check_out_time') }}">
                                    <button type="button" class="btn btn-outline-secondary" id="setCurrentTimeOut">
                                        <i class="bi bi-clock-fill"></i>
                                    </button>
                                </div>
                                @error('check_out_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">予定時間: <span id="scheduledEndTime">-</span></div>
                            </div>
                            
                            <!-- 休憩時間 -->
                            <div class="col-md-4">
                                <label for="break_time" class="form-label">休憩時間（分）</label>
                                <input type="number" class="form-control @error('break_time') is-invalid @enderror" 
                                       id="break_time" name="break_time" value="{{ old('break_time', 60) }}" 
                                       min="0" max="480" step="15">
                                @error('break_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 実働時間（自動計算） -->
                            <div class="col-md-4">
                                <label class="form-label">実働時間</label>
                                <input type="text" class="form-control" id="actual_hours" name="actual_hours" 
                                       value="{{ old('actual_hours') }}" readonly style="background-color: #f8f9fa;">
                                <div class="form-text">出退勤時間から自動計算</div>
                            </div>
                            
                            <!-- 残業時間（自動計算） -->
                            <div class="col-md-4">
                                <label class="form-label">残業時間</label>
                                <input type="text" class="form-control" id="overtime_hours" name="overtime_hours" 
                                       value="{{ old('overtime_hours') }}" readonly style="background-color: #f8f9fa;">
                                <div class="form-text">予定時間との差分</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 追加情報 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-info-square me-2"></i>
                            追加情報
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- 遅刻・早退理由 -->
                            <div class="col-md-6" id="lateEarlyReason" style="display: none;">
                                <label for="late_early_reason" class="form-label">遅刻・早退理由</label>
                                <select class="form-select" id="late_early_reason" name="late_early_reason">
                                    <option value="">選択してください</option>
                                    <option value="traffic">交通遅延</option>
                                    <option value="illness">体調不良</option>
                                    <option value="family">家庭の事情</option>
                                    <option value="emergency">緊急事態</option>
                                    <option value="other">その他</option>
                                </select>
                            </div>
                            
                            <!-- 欠勤理由 -->
                            <div class="col-md-6" id="absentReason" style="display: none;">
                                <label for="absent_reason" class="form-label">欠勤理由</label>
                                <select class="form-select" id="absent_reason" name="absent_reason">
                                    <option value="">選択してください</option>
                                    <option value="illness">病気</option>
                                    <option value="injury">怪我</option>
                                    <option value="family_emergency">家族の緊急事態</option>
                                    <option value="personal">私用</option>
                                    <option value="traffic_accident">交通事故</option>
                                    <option value="other">その他</option>
                                </select>
                            </div>
                            
                            <!-- 位置情報 -->
                            <div class="col-md-6">
                                <label for="location" class="form-label">勤務場所</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                       id="location" name="location" value="{{ old('location') }}" 
                                       placeholder="勤務場所を入力">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <button type="button" class="btn btn-sm btn-outline-info mt-1" id="getCurrentLocation">
                                    <i class="bi bi-geo-alt me-1"></i>現在地取得
                                </button>
                            </div>
                            
                            <!-- IP記録 -->
                            <div class="col-md-6">
                                <label class="form-label">記録IP</label>
                                <input type="text" class="form-control" id="ip_address" name="ip_address" 
                                       value="{{ request()->ip() }}" readonly style="background-color: #f8f9fa;">
                                <div class="form-text">自動記録されます</div>
                            </div>
                            
                            <!-- 備考 -->
                            <div class="col-12">
                                <label for="notes" class="form-label">備考</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="特記事項、詳細な状況など">{{ old('notes') }}</textarea>
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
                <div class="card mb-4 sticky-top" style="top: 1rem;">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-eye me-2"></i>
                            記録プレビュー
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="attendancePreview">
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-clock display-6"></i>
                                <div class="mt-2">情報を入力してください</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 計算結果 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-calculator me-2"></i>
                            時間計算
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted">予定勤務時間</small>
                                <div class="fw-bold" id="scheduledHours">0h</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">実働時間</small>
                                <div class="fw-bold" id="actualHoursDisplay">0h</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">残業時間</small>
                                <div class="fw-bold text-warning" id="overtimeDisplay">0h</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">給与計算</small>
                                <div class="fw-bold text-success" id="salaryCalculation">¥0</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 勤怠規則チェック -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-shield-check me-2"></i>
                            勤怠規則チェック
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="ruleCheckResults">
                            <div class="text-center text-muted">
                                <i class="bi bi-check-circle display-6"></i>
                                <div class="mt-2">問題ありません</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- アクションボタン -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning me-2"></i>
                            クイックアクション
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-info" id="autoFillFromShift">
                                <i class="bi bi-magic me-1"></i>
                                シフトから自動入力
                            </button>
                            <button type="button" class="btn btn-outline-success" id="clockInNow">
                                <i class="bi bi-play-circle me-1"></i>
                                現在時刻で出勤
                            </button>
                            <button type="button" class="btn btn-outline-warning" id="clockOutNow">
                                <i class="bi bi-stop-circle me-1"></i>
                                現在時刻で退勤
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="copyPrevious">
                                <i class="bi bi-copy me-1"></i>
                                前回記録複製
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- フォーム送信ボタン -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('attendances.index') }}" class="btn btn-secondary">
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
                                    <i class="bi bi-save me-1"></i>
                                    記録作成
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
    
    .sticky-top {
        top: 1rem;
    }
    
    .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    @media (max-width: 768px) {
        .sticky-top {
            position: relative !important;
            top: auto !important;
        }
        
        .col-md-4, .col-md-6 {
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
        
        // 作成タイプ変更
        $('input[name="creation_type"]').change(function() {
            const creationType = $(this).val();
            toggleCreationFields(creationType);
        });
        
        // シフト選択変更
        $('#shift_id').change(function() {
            updateShiftInfo();
            updatePreview();
        });
        
        // 時間変更時の処理
        $('#check_in_time, #check_out_time, #break_time').on('input change', function() {
            calculateHours();
            updatePreview();
            checkRules();
        });
        
        // ステータス変更時
        $('#status').change(function() {
            toggleReasonFields();
            updatePreview();
        });
        
        // 現在時刻設定ボタン
        $('#setCurrentTimeIn').click(function() {
            setCurrentTime('check_in_time');
        });
        
        $('#setCurrentTimeOut').click(function() {
            setCurrentTime('check_out_time');
        });
        
        // クイックアクションボタン
        $('#autoFillFromShift').click(function() {
            autoFillFromShift();
        });
        
        $('#clockInNow').click(function() {
            clockInNow();
        });
        
        $('#clockOutNow').click(function() {
            clockOutNow();
        });
        
        $('#copyPrevious').click(function() {
            copyPreviousRecord();
        });
        
        // 現在地取得
        $('#getCurrentLocation').click(function() {
            getCurrentLocation();
        });
        
        // フォーム送信
        $('#attendanceForm').on('submit', function(e) {
            e.preventDefault();
            if (validateForm()) {
                showLoading();
                this.submit();
            }
        });
    });
    
    // フォーム初期化
    function initializeForm() {
        toggleCreationFields('shift');
        toggleReasonFields();
        updateShiftInfo();
        calculateHours();
        updatePreview();
    }
    
    // 作成タイプフィールド切り替え
    function toggleCreationFields(type) {
        if (type === 'shift') {
            $('#shiftSelection').removeClass('d-none');
            $('#manualFields').addClass('d-none');
            $('#shift_id').prop('required', true);
            $('#guard_id, #project_id').prop('required', false);
        } else {
            $('#shiftSelection').addClass('d-none');
            $('#manualFields').removeClass('d-none');
            $('#shift_id').prop('required', false);
            $('#guard_id, #project_id').prop('required', true);
        }
    }
    
    // 理由フィールド表示切り替え
    function toggleReasonFields() {
        const status = $('#status').val();
        
        $('#lateEarlyReason, #absentReason').hide();
        
        if (status === 'late' || status === 'early_leave') {
            $('#lateEarlyReason').show();
        } else if (status === 'absent') {
            $('#absentReason').show();
        }
    }
    
    // シフト情報更新
    function updateShiftInfo() {
        const selectedOption = $('#shift_id option:selected');
        
        if (selectedOption.val()) {
            const startTime = selectedOption.data('start-time');
            const endTime = selectedOption.data('end-time');
            const date = selectedOption.data('date');
            
            $('#date').val(date);
            $('#scheduledStartTime').text(startTime || '-');
            $('#scheduledEndTime').text(endTime || '-');
            
            // 予定時間から実働時間を計算
            if (startTime && endTime) {
                const start = new Date(`2000-01-01 ${startTime}`);
                const end = new Date(`2000-01-01 ${endTime}`);
                let diffMs = end - start;
                if (diffMs < 0) {
                    diffMs += 24 * 60 * 60 * 1000;
                }
                const diffHours = diffMs / (1000 * 60 * 60);
                $('#scheduledHours').text(diffHours.toFixed(1) + 'h');
            }
        } else {
            $('#scheduledStartTime, #scheduledEndTime').text('-');
            $('#scheduledHours').text('0h');
        }
    }
    
    // 実働時間計算
    function calculateHours() {
        const checkInTime = $('#check_in_time').val();
        const checkOutTime = $('#check_out_time').val();
        const breakTime = parseInt($('#break_time').val()) || 0;
        
        if (checkInTime && checkOutTime) {
            const start = new Date(`2000-01-01 ${checkInTime}`);
            const end = new Date(`2000-01-01 ${checkOutTime}`);
            
            let diffMs = end - start;
            if (diffMs < 0) {
                diffMs += 24 * 60 * 60 * 1000; // 翌日にまたがる場合
            }
            
            const totalHours = diffMs / (1000 * 60 * 60);
            const actualHours = totalHours - (breakTime / 60);
            
            $('#actual_hours').val(actualHours.toFixed(2));
            $('#actualHoursDisplay').text(actualHours.toFixed(1) + 'h');
            
            // 残業時間計算
            const scheduledHours = parseFloat($('#scheduledHours').text()) || 0;
            const overtimeHours = Math.max(0, actualHours - scheduledHours);
            $('#overtime_hours').val(overtimeHours.toFixed(2));
            $('#overtimeDisplay').text(overtimeHours.toFixed(1) + 'h');
            
            // 給与計算（仮）
            const hourlyRate = 1500; // デフォルト時給
            const salary = Math.round(actualHours * hourlyRate + overtimeHours * hourlyRate * 0.25);
            $('#salaryCalculation').text('¥' + salary.toLocaleString());
        } else {
            $('#actual_hours, #overtime_hours').val('');
            $('#actualHoursDisplay, #overtimeDisplay').text('0h');
            $('#salaryCalculation').text('¥0');
        }
    }
    
    // プレビュー更新
    function updatePreview() {
        const shiftInfo = $('#shift_id option:selected').text();
        const guardName = $('#guard_id option:selected').text();
        const date = $('#date').val();
        const checkIn = $('#check_in_time').val();
        const checkOut = $('#check_out_time').val();
        const status = $('#status option:selected').text();
        
        if (date) {
            const preview = `
                <div class="text-center">
                    <h6>${shiftInfo || guardName || '未選択'}</h6>
                    <div class="mb-2">
                        <i class="bi bi-calendar me-1"></i>
                        ${new Date(date).toLocaleDateString('ja-JP')}
                    </div>
                    <div class="mb-2">
                        <i class="bi bi-clock me-1"></i>
                        ${checkIn || '--:--'} - ${checkOut || '--:--'}
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-primary">${status}</span>
                    </div>
                    <div class="small text-muted">実働: ${$('#actualHoursDisplay').text()}</div>
                </div>
            `;
            $('#attendancePreview').html(preview);
        }
    }
    
    // 勤怠規則チェック
    function checkRules() {
        const checkInTime = $('#check_in_time').val();
        const checkOutTime = $('#check_out_time').val();
        const scheduledStart = $('#scheduledStartTime').text();
        const scheduledEnd = $('#scheduledEndTime').text();
        
        let warnings = [];
        
        if (checkInTime && scheduledStart !== '-') {
            const checkIn = new Date(`2000-01-01 ${checkInTime}`);
            const scheduled = new Date(`2000-01-01 ${scheduledStart}`);
            
            if (checkIn > scheduled) {
                const diffMinutes = (checkIn - scheduled) / (1000 * 60);
                warnings.push(`遅刻: ${Math.round(diffMinutes)}分`);
            }
        }
        
        if (checkOutTime && scheduledEnd !== '-') {
            const checkOut = new Date(`2000-01-01 ${checkOutTime}`);
            const scheduled = new Date(`2000-01-01 ${scheduledEnd}`);
            
            if (checkOut < scheduled) {
                const diffMinutes = (scheduled - checkOut) / (1000 * 60);
                warnings.push(`早退: ${Math.round(diffMinutes)}分`);
            }
        }
        
        if (warnings.length > 0) {
            const warningsHtml = warnings.map(warning => 
                `<div class="alert alert-warning py-2">${warning}</div>`
            ).join('');
            $('#ruleCheckResults').html(warningsHtml);
        } else {
            $('#ruleCheckResults').html(`
                <div class="text-center text-success">
                    <i class="bi bi-check-circle display-6"></i>
                    <div class="mt-2">問題ありません</div>
                </div>
            `);
        }
    }
    
    // 現在時刻設定
    function setCurrentTime(fieldId) {
        const now = new Date();
        const timeString = now.toTimeString().split(' ')[0].substring(0, 5);
        $(`#${fieldId}`).val(timeString);
        calculateHours();
        updatePreview();
        checkRules();
    }
    
    // シフトから自動入力
    function autoFillFromShift() {
        const selectedOption = $('#shift_id option:selected');
        if (selectedOption.val()) {
            const startTime = selectedOption.data('start-time');
            const endTime = selectedOption.data('end-time');
            
            $('#check_in_time').val(startTime);
            $('#check_out_time').val(endTime);
            
            calculateHours();
            updatePreview();
            checkRules();
        }
    }
    
    // 現在時刻で出勤
    function clockInNow() {
        setCurrentTime('check_in_time');
        $('#status').val('present');
        toggleReasonFields();
        updatePreview();
    }
    
    // 現在時刻で退勤
    function clockOutNow() {
        setCurrentTime('check_out_time');
        calculateHours();
        updatePreview();
        checkRules();
    }
    
    // 前回記録複製
    function copyPreviousRecord() {
        // 前回記録複製機能（実装省略）
        console.log('前回記録複製');
    }
    
    // 現在地取得
    function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                $('#location').val(`緯度: ${lat.toFixed(6)}, 経度: ${lng.toFixed(6)}`);
            }, function(error) {
                alert('位置情報の取得に失敗しました');
            });
        } else {
            alert('このブラウザは位置情報をサポートしていません');
        }
    }
    
    // バリデーション
    function validateForm() {
        const creationType = $('input[name="creation_type"]:checked').val();
        let requiredFields = ['date', 'status', 'approval_status'];
        
        if (creationType === 'shift') {
            requiredFields.push('shift_id');
        } else {
            requiredFields.push('guard_id', 'project_id');
        }
        
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
        const submitBtn = $('#attendanceForm button[type="submit"]');
        const spinner = submitBtn.find('.spinner-border');
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
    }
</script>
@endpush
@endsection