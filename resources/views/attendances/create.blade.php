@extends('layouts.app')

@section('title', '勤怠記録作成')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- ページヘッダー -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('attendances.index') }}">勤怠管理</a></li>
                            <li class="breadcrumb-item active">新規作成</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0">勤怠記録作成</h1>
                    <p class="text-muted mb-0">新しい勤怠記録を作成します</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-info" id="quickClockInBtn">
                        <i class="fas fa-play"></i> 簡易出勤打刻
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="saveAsDraftBtn">
                        <i class="fas fa-save"></i> 下書き保存
                    </button>
                    <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> 戻る
                    </a>
                </div>
            </div>

            <form action="{{ route('attendances.store') }}" method="POST" enctype="multipart/form-data" id="attendanceForm">
                @csrf
                <div class="row">
                    <!-- 左カラム: メインフォーム -->
                    <div class="col-lg-8">
                        <!-- 作成モード選択 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-cog"></i> 作成モード
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="creation_mode" id="shift_mode" value="shift" checked>
                                    <label class="btn btn-outline-primary" for="shift_mode">
                                        <i class="fas fa-calendar-alt"></i> シフト連携
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="creation_mode" id="manual_mode" value="manual">
                                    <label class="btn btn-outline-primary" for="manual_mode">
                                        <i class="fas fa-edit"></i> 手動入力
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="creation_mode" id="timeclock_mode" value="timeclock">
                                    <label class="btn btn-outline-primary" for="timeclock_mode">
                                        <i class="fas fa-stopwatch"></i> タイムカード
                                    </label>
                                </div>
                                <div class="form-text mt-2">
                                    <strong>シフト連携:</strong> 既存シフトから自動設定　
                                    <strong>手動入力:</strong> 完全手動　
                                    <strong>タイムカード:</strong> リアルタイム打刻
                                </div>
                            </div>
                        </div>

                        <!-- 基本情報 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user"></i> 基本情報
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- シフト選択エリア -->
                                <div id="shiftSelectionArea" class="mb-3">
                                    <label for="shift_id" class="form-label required">対象シフト</label>
                                    <select class="form-select @error('shift_id') is-invalid @enderror" id="shift_id" name="shift_id">
                                        <option value="">シフトを選択してください</option>
                                        @foreach($shifts ?? [] as $shift)
                                            <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}
                                                    data-guard-id="{{ $shift->guard_id ?? '' }}"
                                                    data-guard-name="{{ $shift->guard->name ?? '' }}"
                                                    data-project-id="{{ $shift->project_id ?? '' }}"
                                                    data-project-name="{{ $shift->project->name ?? '' }}"
                                                    data-start-time="{{ $shift->start_time ?? '' }}"
                                                    data-end-time="{{ $shift->end_time ?? '' }}"
                                                    data-date="{{ $shift->shift_date ? $shift->shift_date->format('Y-m-d') : '' }}"
                                                    data-location="{{ $shift->location ?? '' }}"
                                                    data-hourly-rate="{{ $shift->hourly_rate ?? 1500 }}">
                                                {{ $shift->shift_date ? $shift->shift_date->format('m/d') : '' }} 
                                                {{ $shift->guard->name ?? '未設定' }} - 
                                                {{ $shift->project->name ?? '未設定' }} 
                                                ({{ $shift->start_time ?? '' }}～{{ $shift->end_time ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shift_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 手動入力エリア -->
                                <div id="manualInputArea" class="d-none">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="guard_id" class="form-label required">警備員</label>
                                            <select class="form-select @error('guard_id') is-invalid @enderror" id="guard_id" name="guard_id">
                                                <option value="">警備員を選択してください</option>
                                                @foreach($guards ?? [] as $guard)
                                                    <option value="{{ $guard->id }}" {{ old('guard_id') == $guard->id ? 'selected' : '' }}
                                                            data-name="{{ $guard->name }}"
                                                            data-employee-id="{{ $guard->employee_id }}"
                                                            data-hourly-rate="{{ $guard->hourly_rate ?? 1500 }}">
                                                        {{ $guard->name }} ({{ $guard->employee_id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('guard_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="project_id" class="form-label required">プロジェクト</label>
                                            <select class="form-select @error('project_id') is-invalid @enderror" id="project_id" name="project_id">
                                                <option value="">プロジェクトを選択してください</option>
                                                @foreach($projects ?? [] as $project)
                                                    <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}
                                                            data-name="{{ $project->name }}"
                                                            data-location="{{ $project->location ?? '' }}">
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

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="attendance_date" class="form-label required">勤務日</label>
                                        <input type="date" class="form-control @error('attendance_date') is-invalid @enderror" 
                                               id="attendance_date" name="attendance_date" 
                                               value="{{ old('attendance_date', date('Y-m-d')) }}" required>
                                        @error('attendance_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="status" class="form-label required">ステータス</label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>承認待ち</option>
                                            <option value="approved" {{ old('status') === 'approved' ? 'selected' : '' }}>承認済み</option>
                                            <option value="rejected" {{ old('status') === 'rejected' ? 'selected' : '' }}>差し戻し</option>
                                            <option value="working" {{ old('status') === 'working' ? 'selected' : '' }}>勤務中</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="work_location" class="form-label">勤務場所</label>
                                        <input type="text" class="form-control @error('work_location') is-invalid @enderror" 
                                               id="work_location" name="work_location" 
                                               value="{{ old('work_location') }}" placeholder="勤務場所">
                                        @error('work_location')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 出勤情報 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-sign-in-alt"></i> 出勤情報
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="clock_in" class="form-label">出勤時間</label>
                                        <div class="input-group">
                                            <input type="time" class="form-control @error('clock_in') is-invalid @enderror" 
                                                   id="clock_in" name="clock_in" value="{{ old('clock_in') }}">
                                            <button type="button" class="btn btn-outline-secondary" id="setCurrentClockIn">
                                                <i class="fas fa-clock"></i> 現在時刻
                                            </button>
                                        </div>
                                        @error('clock_in')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            予定: <span id="scheduledClockIn">未設定</span>
                                            <span id="clockInDifference" class="text-muted"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="clock_in_note" class="form-label">出勤メモ</label>
                                        <textarea class="form-control @error('clock_in_note') is-invalid @enderror" 
                                                  id="clock_in_note" name="clock_in_note" rows="2" 
                                                  placeholder="出勤時の特記事項">{{ old('clock_in_note') }}</textarea>
                                        @error('clock_in_note')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="clock_in_photo" class="form-label">出勤時写真</label>
                                        <input type="file" class="form-control @error('clock_in_photo') is-invalid @enderror" 
                                               id="clock_in_photo" name="clock_in_photo" accept="image/*">
                                        @error('clock_in_photo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">JPG, PNG形式（最大2MB）</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">GPS位置情報</label>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-info" id="getClockInLocation">
                                                <i class="fas fa-map-marker-alt"></i> 現在地取得
                                            </button>
                                            <span id="clockInLocationStatus" class="align-self-center text-muted">未取得</span>
                                        </div>
                                        <input type="hidden" id="clock_in_latitude" name="clock_in_latitude">
                                        <input type="hidden" id="clock_in_longitude" name="clock_in_longitude">
                                        <input type="hidden" id="clock_in_accuracy" name="clock_in_accuracy">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 退勤情報 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-sign-out-alt"></i> 退勤情報
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="clock_out" class="form-label">退勤時間</label>
                                        <div class="input-group">
                                            <input type="time" class="form-control @error('clock_out') is-invalid @enderror" 
                                                   id="clock_out" name="clock_out" value="{{ old('clock_out') }}">
                                            <button type="button" class="btn btn-outline-secondary" id="setCurrentClockOut">
                                                <i class="fas fa-clock"></i> 現在時刻
                                            </button>
                                        </div>
                                        @error('clock_out')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            予定: <span id="scheduledClockOut">未設定</span>
                                            <span id="clockOutDifference" class="text-muted"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="clock_out_note" class="form-label">退勤メモ</label>
                                        <textarea class="form-control @error('clock_out_note') is-invalid @enderror" 
                                                  id="clock_out_note" name="clock_out_note" rows="2" 
                                                  placeholder="退勤時の特記事項">{{ old('clock_out_note') }}</textarea>
                                        @error('clock_out_note')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="clock_out_photo" class="form-label">退勤時写真</label>
                                        <input type="file" class="form-control @error('clock_out_photo') is-invalid @enderror" 
                                               id="clock_out_photo" name="clock_out_photo" accept="image/*">
                                        @error('clock_out_photo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">JPG, PNG形式（最大2MB）</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">GPS位置情報</label>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-info" id="getClockOutLocation">
                                                <i class="fas fa-map-marker-alt"></i> 現在地取得
                                            </button>
                                            <span id="clockOutLocationStatus" class="align-self-center text-muted">未取得</span>
                                        </div>
                                        <input type="hidden" id="clock_out_latitude" name="clock_out_latitude">
                                        <input type="hidden" id="clock_out_longitude" name="clock_out_longitude">
                                        <input type="hidden" id="clock_out_accuracy" name="clock_out_accuracy">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 勤務時間・追加情報 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clock"></i> 勤務時間・追加情報
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="break_time" class="form-label">休憩時間（分）</label>
                                        <input type="number" class="form-control @error('break_time') is-invalid @enderror" 
                                               id="break_time" name="break_time" 
                                               value="{{ old('break_time', 60) }}" min="0" max="480" step="15">
                                        @error('break_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">実働時間</label>
                                        <input type="text" class="form-control" id="total_work_hours_display" readonly
                                               style="background-color: #f8f9fa;">
                                        <input type="hidden" id="total_work_hours" name="total_work_hours">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">残業時間</label>
                                        <input type="text" class="form-control" id="overtime_hours_display" readonly
                                               style="background-color: #f8f9fa;">
                                        <input type="hidden" id="overtime_hours" name="overtime_hours">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">備考</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3" 
                                              placeholder="その他特記事項">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 送信ボタン -->
                        <div class="d-flex justify-content-between mb-4">
                            <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> キャンセル
                            </a>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" id="previewBtn">
                                    <i class="fas fa-eye"></i> プレビュー
                                </button>
                                <button type="submit" class="btn btn-success" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                    <i class="fas fa-save"></i> 勤怠記録作成
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 右カラム: プレビュー・統計 -->
                    <div class="col-lg-4">
                        <!-- リアルタイムプレビュー -->
                        <div class="card mb-4 sticky-top" style="top: 1rem;">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-eye"></i> プレビュー
                                </h5>
                            </div>
                            <div class="card-body" id="attendancePreview">
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-clock fa-3x mb-3"></i>
                                    <p>情報を入力してください</p>
                                </div>
                            </div>
                        </div>

                        <!-- 勤務時間計算 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calculator"></i> 勤務時間計算
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="p-2 border rounded">
                                            <small class="text-muted">予定時間</small>
                                            <div class="h5 mb-0" id="scheduledHours">0:00</div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="p-2 border rounded">
                                            <small class="text-muted">実働時間</small>
                                            <div class="h5 mb-0 text-primary" id="actualHours">0:00</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 border rounded">
                                            <small class="text-muted">残業時間</small>
                                            <div class="h5 mb-0 text-warning" id="overtimeHours">0:00</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 border rounded">
                                            <small class="text-muted">給与概算</small>
                                            <div class="h5 mb-0 text-success" id="estimatedWage">¥0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- アラート・警告 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle"></i> アラート
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="alertsContainer">
                                    <div class="text-center text-success py-3">
                                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                                        <p class="mb-0">問題ありません</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- クイックアクション -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bolt"></i> クイックアクション
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-primary" id="fillFromShiftBtn">
                                        <i class="fas fa-magic"></i> シフトから自動入力
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="clockInNowBtn">
                                        <i class="fas fa-play"></i> 今すぐ出勤打刻
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" id="clockOutNowBtn">
                                        <i class="fas fa-stop"></i> 今すぐ退勤打刻
                                    </button>
                                    <button type="button" class="btn btn-outline-info" id="autoBreakBtn">
                                        <i class="fas fa-coffee"></i> 標準休憩時間設定
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- タイムカードモーダル -->
<div class="modal fade" id="timeclockModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">リアルタイム タイムカード</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="display-3 fw-bold text-primary mb-3" id="currentTime">--:--:--</div>
                    <div class="h5 text-muted mb-4" id="currentDate">----年--月--日</div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <button class="btn btn-success btn-lg w-100 mb-3" id="timeclockClockIn">
                                <i class="fas fa-play fa-2x"></i>
                                <div class="mt-2">出勤打刻</div>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-danger btn-lg w-100 mb-3" id="timeclockClockOut" disabled>
                                <i class="fas fa-stop fa-2x"></i>
                                <div class="mt-2">退勤打刻</div>
                            </button>
                        </div>
                    </div>
                    
                    <div id="timeclockStatus" class="alert alert-info">
                        タイムカードモードです。出勤ボタンを押して開始してください。
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.card {
    transition: box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.sticky-top {
    top: 1rem !important;
}

.border.rounded.p-2:hover {
    background-color: #f8f9fa;
}

#currentTime {
    font-family: 'Courier New', monospace;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

@media (max-width: 768px) {
    .sticky-top {
        position: relative !important;
        top: auto !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // 初期化
    initializeForm();
    
    // イベントリスナー設定
    setupEventListeners();
    
    // リアルタイム計算開始
    startRealTimeCalculation();
});

function initializeForm() {
    // 作成モード初期化
    toggleCreationMode('shift');
    
    // 初期値設定
    updateCalculations();
    updatePreview();
    checkAlerts();
}

function setupEventListeners() {
    // 作成モード変更
    $('input[name="creation_mode"]').change(function() {
        toggleCreationMode($(this).val());
    });
    
    // シフト選択変更
    $('#shift_id').change(function() {
        fillFromShift();
        updateCalculations();
        updatePreview();
    });
    
    // 時間変更時の処理
    $('#clock_in, #clock_out, #break_time').on('input change', function() {
        updateCalculations();
        updatePreview();
        checkAlerts();
    });
    
    // 現在時刻設定
    $('#setCurrentClockIn').click(() => setCurrentTime('clock_in'));
    $('#setCurrentClockOut').click(() => setCurrentTime('clock_out'));
    
    // GPS位置情報取得
    $('#getClockInLocation').click(() => getCurrentLocation('clock_in'));
    $('#getClockOutLocation').click(() => getCurrentLocation('clock_out'));
    
    // クイックアクション
    $('#fillFromShiftBtn').click(fillFromShift);
    $('#clockInNowBtn').click(clockInNow);
    $('#clockOutNowBtn').click(clockOutNow);
    $('#autoBreakBtn').click(setStandardBreak);
    
    // タイムカードモード
    $('#timeclock_mode').change(function() {
        if ($(this).is(':checked')) {
            $('#timeclockModal').modal('show');
            startTimeclock();
        }
    });
    
    // フォーム送信
    $('#attendanceForm').submit(function(e) {
        e.preventDefault();
        if (validateForm()) {
            showLoading();
            this.submit();
        }
    });
}

function toggleCreationMode(mode) {
    $('#shiftSelectionArea, #manualInputArea').addClass('d-none');
    
    switch(mode) {
        case 'shift':
            $('#shiftSelectionArea').removeClass('d-none');
            $('#shift_id').prop('required', true);
            $('#guard_id, #project_id').prop('required', false);
            break;
        case 'manual':
            $('#manualInputArea').removeClass('d-none');
            $('#shift_id').prop('required', false);
            $('#guard_id, #project_id').prop('required', true);
            break;
        case 'timeclock':
            $('#timeclockModal').modal('show');
            break;
    }
}

function fillFromShift() {
    const selectedOption = $('#shift_id option:selected');
    if (selectedOption.val()) {
        const data = selectedOption.data();
        
        // 基本情報設定
        $('#attendance_date').val(data.date);
        $('#work_location').val(data.location);
        
        // 予定時間設定
        $('#clock_in').val(data.startTime);
        $('#clock_out').val(data.endTime);
        
        // プレビュー更新
        $('#scheduledClockIn').text(data.startTime || '未設定');
        $('#scheduledClockOut').text(data.endTime || '未設定');
        
        updateCalculations();
        updatePreview();
    }
}

function updateCalculations() {
    const clockIn = $('#clock_in').val();
    const clockOut = $('#clock_out').val();
    const breakTime = parseInt($('#break_time').val()) || 0;
    
    if (clockIn && clockOut) {
        // 勤務時間計算
        const start = moment(`2000-01-01 ${clockIn}`);
        const end = moment(`2000-01-01 ${clockOut}`);
        
        if (end.isBefore(start)) {
            end.add(1, 'day'); // 翌日にまたがる場合
        }
        
        const totalMinutes = end.diff(start, 'minutes');
        const workMinutes = totalMinutes - breakTime;
        const workHours = workMinutes / 60;
        
        // 表示更新
        $('#total_work_hours').val(workHours.toFixed(2));
        $('#total_work_hours_display').val(`${Math.floor(workHours)}:${String(Math.round((workHours % 1) * 60)).padStart(2, '0')}`);
        $('#actualHours').text(`${Math.floor(workHours)}:${String(Math.round((workHours % 1) * 60)).padStart(2, '0')}`);
        
        // 残業時間計算
        const scheduledHours = calculateScheduledHours();
        const overtimeHours = Math.max(0, workHours - scheduledHours);
        
        $('#overtime_hours').val(overtimeHours.toFixed(2));
        $('#overtime_hours_display').val(`${Math.floor(overtimeHours)}:${String(Math.round((overtimeHours % 1) * 60)).padStart(2, '0')}`);
        $('#overtimeHours').text(`${Math.floor(overtimeHours)}:${String(Math.round((overtimeHours % 1) * 60)).padStart(2, '0')}`);
        
        // 給与概算
        const hourlyRate = getHourlyRate();
        const estimatedWage = Math.round(workHours * hourlyRate + overtimeHours * hourlyRate * 0.25);
        $('#estimatedWage').text(`¥${estimatedWage.toLocaleString()}`);
        
        // 差異チェック
        updateTimeDifferences(clockIn, clockOut);
    } else {
        // 未入力時はリセット
        $('#total_work_hours, #overtime_hours').val('');
        $('#total_work_hours_display, #overtime_hours_display').val('');
        $('#actualHours, #overtimeHours').text('0:00');
        $('#estimatedWage').text('¥0');
    }
}

function calculateScheduledHours() {
    const scheduledStart = $('#scheduledClockIn').text();
    const scheduledEnd = $('#scheduledClockOut').text();
    
    if (scheduledStart !== '未設定' && scheduledEnd !== '未設定') {
        const start = moment(`2000-01-01 ${scheduledStart}`);
        const end = moment(`2000-01-01 ${scheduledEnd}`);
        
        if (end.isBefore(start)) {
            end.add(1, 'day');
        }
        
        return end.diff(start, 'minutes') / 60;
    }
    
    return 8; // デフォルト8時間
}

function getHourlyRate() {
    const shiftData = $('#shift_id option:selected').data('hourlyRate');
    const guardData = $('#guard_id option:selected').data('hourlyRate');
    return shiftData || guardData || 1500;
}

function updateTimeDifferences(clockIn, clockOut) {
    const scheduledStart = $('#scheduledClockIn').text();
    const scheduledEnd = $('#scheduledClockOut').text();
    
    if (scheduledStart !== '未設定' && clockIn) {
        const actual = moment(`2000-01-01 ${clockIn}`);
        const scheduled = moment(`2000-01-01 ${scheduledStart}`);
        const diffMinutes = actual.diff(scheduled, 'minutes');
        
        if (diffMinutes > 0) {
            $('#clockInDifference').html(`<span class="text-warning">(+${diffMinutes}分遅れ)</span>`);
        } else if (diffMinutes < 0) {
            $('#clockInDifference').html(`<span class="text-success">(${Math.abs(diffMinutes)}分早め)</span>`);
        } else {
            $('#clockInDifference').html(`<span class="text-success">(時間通り)</span>`);
        }
    }
    
    if (scheduledEnd !== '未設定' && clockOut) {
        const actual = moment(`2000-01-01 ${clockOut}`);
        const scheduled = moment(`2000-01-01 ${scheduledEnd}`);
        const diffMinutes = actual.diff(scheduled, 'minutes');
        
        if (diffMinutes > 0) {
            $('#clockOutDifference').html(`<span class="text-info">(+${diffMinutes}分延長)</span>`);
        } else if (diffMinutes < 0) {
            $('#clockOutDifference').html(`<span class="text-warning">(${Math.abs(diffMinutes)}分早退)</span>`);
        } else {
            $('#clockOutDifference').html(`<span class="text-success">(時間通り)</span>`);
        }
    }
}

function updatePreview() {
    const mode = $('input[name="creation_mode"]:checked').val();
    const date = $('#attendance_date').val();
    const clockIn = $('#clock_in').val();
    const clockOut = $('#clock_out').val();
    const status = $('#status option:selected').text();
    
    let guardName = '未選択';
    let projectName = '未選択';
    
    if (mode === 'shift') {
        const shiftOption = $('#shift_id option:selected');
        guardName = shiftOption.data('guardName') || '未選択';
        projectName = shiftOption.data('projectName') || '未選択';
    } else {
        guardName = $('#guard_id option:selected').text().split(' (')[0] || '未選択';
        projectName = $('#project_id option:selected').text() || '未選択';
    }
    
    const preview = `
        <div class="text-center">
            <div class="h5 mb-3">${guardName}</div>
            <div class="mb-2"><i class="fas fa-building"></i> ${projectName}</div>
            <div class="mb-2"><i class="fas fa-calendar"></i> ${date ? new Date(date).toLocaleDateString('ja-JP') : '未設定'}</div>
            <div class="mb-3">
                <i class="fas fa-clock"></i> 
                ${clockIn || '--:--'} ～ ${clockOut || '--:--'}
            </div>
            <span class="badge bg-primary">${status}</span>
            <div class="mt-3 small text-muted">
                実働: ${$('#actualHours').text()}<br>
                残業: ${$('#overtimeHours').text()}
            </div>
        </div>
    `;
    
    $('#attendancePreview').html(preview);
}

function checkAlerts() {
    const alerts = [];
    const clockIn = $('#clock_in').val();
    const clockOut = $('#clock_out').val();
    const workHours = parseFloat($('#total_work_hours').val()) || 0;
    
    // 遅刻チェック
    if (clockIn && $('#scheduledClockIn').text() !== '未設定') {
        const actual = moment(`2000-01-01 ${clockIn}`);
        const scheduled = moment(`2000-01-01 ${$('#scheduledClockIn').text()}`);
        
        if (actual.isAfter(scheduled)) {
            const diffMinutes = actual.diff(scheduled, 'minutes');
            alerts.push({
                type: 'warning',
                icon: 'fas fa-clock',
                message: `遅刻: ${diffMinutes}分遅れ`
            });
        }
    }
    
    // 早退チェック
    if (clockOut && $('#scheduledClockOut').text() !== '未設定') {
        const actual = moment(`2000-01-01 ${clockOut}`);
        const scheduled = moment(`2000-01-01 ${$('#scheduledClockOut').text()}`);
        
        if (actual.isBefore(scheduled)) {
            const diffMinutes = scheduled.diff(actual, 'minutes');
            alerts.push({
                type: 'warning',
                icon: 'fas fa-door-open',
                message: `早退: ${diffMinutes}分早め`
            });
        }
    }
    
    // 長時間勤務チェック
    if (workHours > 12) {
        alerts.push({
            type: 'danger',
            icon: 'fas fa-exclamation-triangle',
            message: `長時間勤務: ${workHours.toFixed(1)}時間`
        });
    }
    
    // GPS未取得チェック
    if (!$('#clock_in_latitude').val()) {
        alerts.push({
            type: 'info',
            icon: 'fas fa-map-marker-alt',
            message: '出勤時GPS位置情報未取得'
        });
    }
    
    // アラート表示更新
    if (alerts.length > 0) {
        const alertsHtml = alerts.map(alert => `
            <div class="alert alert-${alert.type} py-2 mb-2">
                <i class="${alert.icon} me-2"></i>${alert.message}
            </div>
        `).join('');
        $('#alertsContainer').html(alertsHtml);
    } else {
        $('#alertsContainer').html(`
            <div class="text-center text-success py-3">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <p class="mb-0">問題ありません</p>
            </div>
        `);
    }
}

function setCurrentTime(field) {
    const now = new Date();
    const timeString = now.toTimeString().split(' ')[0].substring(0, 5);
    $(`#${field}`).val(timeString);
    
    updateCalculations();
    updatePreview();
    checkAlerts();
}

function getCurrentLocation(type) {
    if (navigator.geolocation) {
        const statusEl = $(`#${type}LocationStatus`);
        statusEl.text('取得中...');
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                $(`#${type}_latitude`).val(position.coords.latitude);
                $(`#${type}_longitude`).val(position.coords.longitude);
                $(`#${type}_accuracy`).val(position.coords.accuracy);
                
                statusEl.html('<span class="text-success"><i class="fas fa-check"></i> 取得完了</span>');
                checkAlerts();
            },
            function(error) {
                statusEl.html('<span class="text-danger"><i class="fas fa-times"></i> 取得失敗</span>');
                console.error('GPS取得エラー:', error);
            }
        );
    } else {
        alert('GPS位置情報がサポートされていません。');
    }
}

function clockInNow() {
    setCurrentTime('clock_in');
    getCurrentLocation('clock_in');
    
    // 出勤時の自動設定
    if (!$('#status').val() || $('#status').val() === 'pending') {
        $('#status').val('working');
    }
}

function clockOutNow() {
    setCurrentTime('clock_out');
    getCurrentLocation('clock_out');
    
    // 退勤時の自動設定
    if ($('#status').val() === 'working') {
        $('#status').val('pending');
    }
}

function setStandardBreak() {
    const workHours = parseFloat($('#total_work_hours').val()) || 0;
    
    if (workHours <= 6) {
        $('#break_time').val(0);
    } else if (workHours <= 8) {
        $('#break_time').val(60);
    } else {
        $('#break_time').val(90);
    }
    
    updateCalculations();
}

function startRealTimeCalculation() {
    setInterval(function() {
        if ($('#clock_in').val() && !$('#clock_out').val()) {
            // 勤務中の場合、現在時刻で計算更新
            const now = new Date();
            const currentTime = now.toTimeString().split(' ')[0].substring(0, 5);
            const tempClockOut = $('#clock_out').val();
            
            $('#clock_out').val(currentTime);
            updateCalculations();
            $('#clock_out').val(tempClockOut);
        }
    }, 30000); // 30秒ごと
}

function startTimeclock() {
    const updateClock = () => {
        const now = new Date();
        $('#currentTime').text(now.toLocaleTimeString('ja-JP'));
        $('#currentDate').text(now.toLocaleDateString('ja-JP', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            weekday: 'long'
        }));
    };
    
    updateClock();
    setInterval(updateClock, 1000);
    
    // タイムカード打刻処理
    $('#timeclockClockIn').click(function() {
        clockInNow();
        $(this).prop('disabled', true);
        $('#timeclockClockOut').prop('disabled', false);
        $('#timeclockStatus').removeClass('alert-info').addClass('alert-success').text('出勤打刻完了！退勤時に退勤ボタンを押してください。');
    });
    
    $('#timeclockClockOut').click(function() {
        clockOutNow();
        $(this).prop('disabled', true);
        $('#timeclockClockIn').prop('disabled', false);
        $('#timeclockStatus').removeClass('alert-success').addClass('alert-primary').text('退勤打刻完了！記録を保存してください。');
        $('#timeclockModal').modal('hide');
    });
}

function validateForm() {
    let isValid = true;
    const requiredFields = ['attendance_date', 'status'];
    
    const mode = $('input[name="creation_mode"]:checked').val();
    if (mode === 'shift') {
        requiredFields.push('shift_id');
    } else if (mode === 'manual') {
        requiredFields.push('guard_id', 'project_id');
    }
    
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

function showLoading() {
    const submitBtn = $('#submitBtn');
    const spinner = submitBtn.find('.spinner-border');
    
    submitBtn.prop('disabled', true);
    spinner.removeClass('d-none');
}

// Moment.js簡易実装
const moment = (dateString) => {
    const date = new Date(dateString);
    return {
        diff: (other, unit) => {
            const diffMs = date - other.date;
            switch(unit) {
                case 'minutes': return Math.round(diffMs / (1000 * 60));
                case 'hours': return diffMs / (1000 * 60 * 60);
                default: return diffMs;
            }
        },
        isAfter: (other) => date > other.date,
        isBefore: (other) => date < other.date,
        add: (amount, unit) => {
            const newDate = new Date(date);
            switch(unit) {
                case 'day': newDate.setDate(newDate.getDate() + amount); break;
                case 'hour': newDate.setHours(newDate.getHours() + amount); break;
            }
            return moment(newDate);
        },
        date: date
    };
};
</script>
@endpush
@endsection
