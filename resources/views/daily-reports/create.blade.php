@extends('layouts.app')

@section('title', '新規日報作成')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-plus-circle me-2"></i>新規日報作成
                    </h1>
                    <p class="mb-0 text-muted">警備業務の日報を作成します</p>
                </div>
                <div>
                    <a href="{{ route('daily-reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>一覧に戻る
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form id="dailyReportForm" method="POST" action="{{ route('daily-reports.store') }}">
        @csrf
        
        <div class="row">
            <!-- 左側：基本情報 -->
            <div class="col-lg-8">
                <!-- 基本情報カード -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>基本情報
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guard_id" class="form-label required">警備員 <span class="text-danger">*</span></label>
                                <select class="form-select @error('guard_id') is-invalid @enderror" 
                                        id="guard_id" name="guard_id" required>
                                    <option value="">選択してください</option>
                                    @foreach($guards as $guard)
                                        <option value="{{ $guard->id }}" 
                                                {{ old('guard_id') == $guard->id ? 'selected' : '' }}>
                                            {{ $guard->name }} （{{ $guard->employee_id }}）
                                        </option>
                                    @endforeach
                                </select>
                                @error('guard_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="project_id" class="form-label required">プロジェクト <span class="text-danger">*</span></label>
                                <select class="form-select @error('project_id') is-invalid @enderror" 
                                        id="project_id" name="project_id" required>
                                    <option value="">選択してください</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" 
                                                {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="shift_id" class="form-label">関連シフト</label>
                                <select class="form-select @error('shift_id') is-invalid @enderror" 
                                        id="shift_id" name="shift_id">
                                    <option value="">関連シフトを選択（任意）</option>
                                    @foreach($todayShifts as $shift)
                                        <option value="{{ $shift->id }}" 
                                                {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                            {{ $shift->guard->name ?? '未割当' }} - 
                                            {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}〜{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                            （{{ $shift->project->name ?? '不明' }}）
                                        </option>
                                    @endforeach
                                </select>
                                @error('shift_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">今日のシフトから関連するものを選択できます</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="report_date" class="form-label required">日報日 <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('report_date') is-invalid @enderror" 
                                       id="report_date" name="report_date" 
                                       value="{{ old('report_date', now()->format('Y-m-d')) }}" required>
                                @error('report_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="report_type" class="form-label required">日報種別 <span class="text-danger">*</span></label>
                                <select class="form-select @error('report_type') is-invalid @enderror" 
                                        id="report_type" name="report_type" required>
                                    <option value="">選択してください</option>
                                    <option value="daily" {{ old('report_type') == 'daily' ? 'selected' : '' }}>日常警備</option>
                                    <option value="incident" {{ old('report_type') == 'incident' ? 'selected' : '' }}>事故・異常</option>
                                    <option value="maintenance" {{ old('report_type') == 'maintenance' ? 'selected' : '' }}>設備点検</option>
                                    <option value="security_check" {{ old('report_type') == 'security_check' ? 'selected' : '' }}>警備点検</option>
                                    <option value="patrol" {{ old('report_type') == 'patrol' ? 'selected' : '' }}>巡回報告</option>
                                </select>
                                @error('report_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="priority" class="form-label required">重要度 <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" name="priority" required>
                                    <option value="">選択してください</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>低</option>
                                    <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>通常</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>高</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>緊急</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="weather_condition" class="form-label required">天候 <span class="text-danger">*</span></label>
                                <select class="form-select @error('weather_condition') is-invalid @enderror" 
                                        id="weather_condition" name="weather_condition" required>
                                    <option value="">選択してください</option>
                                    <option value="晴れ" {{ old('weather_condition') == '晴れ' ? 'selected' : '' }}>晴れ</option>
                                    <option value="曇り" {{ old('weather_condition') == '曇り' ? 'selected' : '' }}>曇り</option>
                                    <option value="雨" {{ old('weather_condition') == '雨' ? 'selected' : '' }}>雨</option>
                                    <option value="雪" {{ old('weather_condition') == '雪' ? 'selected' : '' }}>雪</option>
                                    <option value="強風" {{ old('weather_condition') == '強風' ? 'selected' : '' }}>強風</option>
                                    <option value="その他" {{ old('weather_condition') == 'その他' ? 'selected' : '' }}>その他</option>
                                </select>
                                @error('weather_condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 日報内容カード -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-file-alt me-2"></i>日報内容
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label for="summary" class="form-label required">概要 <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('summary') is-invalid @enderror" 
                                      id="summary" name="summary" rows="3" required 
                                      placeholder="日報の概要を300文字以内で入力してください">{{ old('summary') }}</textarea>
                            <div class="form-text">
                                <span id="summaryCount">0</span>/300文字
                            </div>
                            @error('summary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="detailed_report" class="form-label required">詳細報告 <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('detailed_report') is-invalid @enderror" 
                                      id="detailed_report" name="detailed_report" rows="8" required 
                                      placeholder="業務の詳細内容を2000文字以内で入力してください">{{ old('detailed_report') }}</textarea>
                            <div class="form-text">
                                <span id="detailCount">0</span>/2000文字
                            </div>
                            @error('detailed_report')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="patrol_route" class="form-label">巡回ルート</label>
                                <textarea class="form-control @error('patrol_route') is-invalid @enderror" 
                                          id="patrol_route" name="patrol_route" rows="3" 
                                          placeholder="巡回した経路を入力してください">{{ old('patrol_route') }}</textarea>
                                @error('patrol_route')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="visitor_count" class="form-label">来訪者数</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('visitor_count') is-invalid @enderror" 
                                           id="visitor_count" name="visitor_count" min="0" 
                                           value="{{ old('visitor_count') }}" placeholder="0">
                                    <span class="input-group-text">人</span>
                                </div>
                                @error('visitor_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 詳細情報カード -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-cogs me-2"></i>詳細情報
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4" id="incidentDetailsSection" style="display: none;">
                            <label for="incident_details" class="form-label">事故・異常詳細</label>
                            <textarea class="form-control @error('incident_details') is-invalid @enderror" 
                                      id="incident_details" name="incident_details" rows="4" 
                                      placeholder="事故や異常の詳細を入力してください">{{ old('incident_details') }}</textarea>
                            @error('incident_details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="equipment_status" class="form-label">設備状況</label>
                                <textarea class="form-control @error('equipment_status') is-invalid @enderror" 
                                          id="equipment_status" name="equipment_status" rows="3" 
                                          placeholder="設備の稼働状況や点検結果を入力してください">{{ old('equipment_status') }}</textarea>
                                @error('equipment_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="maintenance_notes" class="form-label">保守・メンテナンス</label>
                                <textarea class="form-control @error('maintenance_notes') is-invalid @enderror" 
                                          id="maintenance_notes" name="maintenance_notes" rows="3" 
                                          placeholder="実施した保守作業やメンテナンス内容を入力してください">{{ old('maintenance_notes') }}</textarea>
                                @error('maintenance_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="safety_observations" class="form-label">安全確認・注意事項</label>
                                <textarea class="form-control @error('safety_observations') is-invalid @enderror" 
                                          id="safety_observations" name="safety_observations" rows="3" 
                                          placeholder="安全面での気づきや注意事項を入力してください">{{ old('safety_observations') }}</textarea>
                                @error('safety_observations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="recommendations" class="form-label">改善提案・推奨事項</label>
                                <textarea class="form-control @error('recommendations') is-invalid @enderror" 
                                          id="recommendations" name="recommendations" rows="3" 
                                          placeholder="業務改善のための提案や推奨事項を入力してください">{{ old('recommendations') }}</textarea>
                                @error('recommendations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="next_shift_notes" class="form-label">次番への引き継ぎ事項</label>
                            <textarea class="form-control @error('next_shift_notes') is-invalid @enderror" 
                                      id="next_shift_notes" name="next_shift_notes" rows="3" 
                                      placeholder="次の勤務者への引き継ぎ事項を入力してください">{{ old('next_shift_notes') }}</textarea>
                            @error('next_shift_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- 右側：フラグ・テンプレート・プレビュー -->
            <div class="col-lg-4">
                <!-- 特記事項フラグカード -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-flag me-2"></i>特記事項
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="has_incident" name="has_incident" 
                                   {{ old('has_incident') ? 'checked' : '' }}>
                            <label class="form-check-label text-danger" for="has_incident">
                                <i class="fas fa-exclamation-triangle me-1"></i>事故・異常が発生した
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="has_equipment_issue" name="has_equipment_issue" 
                                   {{ old('has_equipment_issue') ? 'checked' : '' }}>
                            <label class="form-check-label text-warning" for="has_equipment_issue">
                                <i class="fas fa-tools me-1"></i>設備に不具合があった
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="has_safety_concern" name="has_safety_concern" 
                                   {{ old('has_safety_concern') ? 'checked' : '' }}>
                            <label class="form-check-label text-info" for="has_safety_concern">
                                <i class="fas fa-shield-alt me-1"></i>安全上の懸念がある
                            </label>
                        </div>
                    </div>
                </div>

                <!-- テンプレート選択カード -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-file-copy me-2"></i>テンプレート
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="template_select" class="form-label">テンプレート選択</label>
                            <select class="form-select" id="template_select">
                                <option value="">テンプレートを選択</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template['id'] }}" data-template="{{ json_encode($template['template']) }}">
                                        {{ $template['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-outline-info btn-sm w-100" id="applyTemplateBtn" disabled>
                            <i class="fas fa-magic me-1"></i>テンプレートを適用
                        </button>
                        <div class="form-text mt-2">
                            定型的な日報作成に便利です
                        </div>
                    </div>
                </div>

                <!-- プレビュー・保存アクションカード -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-save me-2"></i>保存・確認
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary" id="previewBtn">
                                <i class="fas fa-eye me-1"></i>プレビュー
                            </button>
                            <button type="submit" class="btn btn-primary" name="action" value="draft">
                                <i class="fas fa-save me-1"></i>下書き保存
                            </button>
                            <button type="submit" class="btn btn-success" name="action" value="submit">
                                <i class="fas fa-paper-plane me-1"></i>提出
                            </button>
                        </div>
                        <div class="form-text mt-2">
                            下書き保存は後で編集可能です
                        </div>
                    </div>
                </div>

                <!-- 入力ヘルプカード -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-secondary">
                            <i class="fas fa-question-circle me-2"></i>入力ヘルプ
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="helpAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="help1">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#helpCollapse1">
                                        概要の書き方
                                    </button>
                                </h2>
                                <div id="helpCollapse1" class="accordion-collapse collapse" 
                                     data-bs-parent="#helpAccordion">
                                    <div class="accordion-body">
                                        <small>
                                            ・業務の要点を簡潔に記載<br>
                                            ・異常時は重要事項を優先<br>
                                            ・300文字以内で記載
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="help2">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#helpCollapse2">
                                        詳細報告の書き方
                                    </button>
                                </h2>
                                <div id="helpCollapse2" class="accordion-collapse collapse" 
                                     data-bs-parent="#helpAccordion">
                                    <div class="accordion-body">
                                        <small>
                                            ・時系列で記載<br>
                                            ・5W1Hを意識<br>
                                            ・客観的事実を記載<br>
                                            ・推測や憶測は避ける
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- プレビューモーダル -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">日報プレビュー</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- プレビュー内容がここに動的に挿入されます -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" onclick="$('#dailyReportForm').submit();">
                    このまま保存
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // 文字数カウント
    $('#summary').on('input', function() {
        const count = $(this).val().length;
        $('#summaryCount').text(count);
        if (count > 300) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    $('#detailed_report').on('input', function() {
        const count = $(this).val().length;
        $('#detailCount').text(count);
        if (count > 2000) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // 事故・異常フラグの切り替え
    $('#has_incident').change(function() {
        if ($(this).is(':checked')) {
            $('#incidentDetailsSection').slideDown();
            $('#incident_details').attr('required', true);
            // 重要度を自動的に高に設定
            if ($('#priority').val() === 'low' || $('#priority').val() === 'normal') {
                $('#priority').val('high');
            }
        } else {
            $('#incidentDetailsSection').slideUp();
            $('#incident_details').removeAttr('required');
        }
    });

    // 日報種別の変更
    $('#report_type').change(function() {
        const type = $(this).val();
        if (type === 'incident') {
            $('#has_incident').prop('checked', true).trigger('change');
            $('#priority').val('high');
        }
    });

    // テンプレート選択
    $('#template_select').change(function() {
        const selectedOption = $(this).find('option:selected');
        $('#applyTemplateBtn').prop('disabled', selectedOption.val() === '');
    });

    // テンプレート適用
    $('#applyTemplateBtn').click(function() {
        const selectedOption = $('#template_select').find('option:selected');
        const templateData = selectedOption.data('template');
        
        if (templateData) {
            if (templateData.summary) $('#summary').val(templateData.summary);
            if (templateData.detailed_report) $('#detailed_report').val(templateData.detailed_report);
            if (templateData.patrol_route) $('#patrol_route').val(templateData.patrol_route);
            if (templateData.equipment_status) $('#equipment_status').val(templateData.equipment_status);
            if (templateData.safety_observations) $('#safety_observations').val(templateData.safety_observations);
            
            // 文字数カウントを更新
            $('#summary').trigger('input');
            $('#detailed_report').trigger('input');
            
            showAlert('success', 'テンプレートを適用しました。');
        }
    });

    // プレビュー機能
    $('#previewBtn').click(function() {
        generatePreview();
    });

    // フォーム送信前バリデーション
    $('#dailyReportForm').submit(function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
    });

    // 警備員選択に応じてシフト候補を絞り込み
    $('#guard_id').change(function() {
        filterShiftsByGuard();
    });

    // プロジェクト選択に応じてシフト候補を絞り込み
    $('#project_id').change(function() {
        filterShiftsByProject();
    });

    // 初期値の文字数カウント
    $('#summary').trigger('input');
    $('#detailed_report').trigger('input');
});

// フォームバリデーション
function validateForm() {
    let isValid = true;
    const requiredFields = ['guard_id', 'project_id', 'report_date', 'report_type', 'priority', 'weather_condition', 'summary', 'detailed_report'];
    
    requiredFields.forEach(function(field) {
        const element = $(`#${field}`);
        if (!element.val()) {
            element.addClass('is-invalid');
            isValid = false;
        } else {
            element.removeClass('is-invalid');
        }
    });

    if ($('#summary').val().length > 300) {
        $('#summary').addClass('is-invalid');
        isValid = false;
    }

    if ($('#detailed_report').val().length > 2000) {
        $('#detailed_report').addClass('is-invalid');
        isValid = false;
    }

    if (!isValid) {
        showAlert('error', '必須項目を正しく入力してください。');
    }

    return isValid;
}

// プレビュー生成
function generatePreview() {
    const formData = {
        guard: $('#guard_id option:selected').text(),
        project: $('#project_id option:selected').text(),
        report_date: $('#report_date').val(),
        report_type: $('#report_type option:selected').text(),
        priority: $('#priority option:selected').text(),
        weather_condition: $('#weather_condition').val(),
        summary: $('#summary').val(),
        detailed_report: $('#detailed_report').val(),
        patrol_route: $('#patrol_route').val(),
        visitor_count: $('#visitor_count').val(),
        equipment_status: $('#equipment_status').val(),
        maintenance_notes: $('#maintenance_notes').val(),
        safety_observations: $('#safety_observations').val(),
        recommendations: $('#recommendations').val(),
        next_shift_notes: $('#next_shift_notes').val(),
        has_incident: $('#has_incident').is(':checked'),
        has_equipment_issue: $('#has_equipment_issue').is(':checked'),
        has_safety_concern: $('#has_safety_concern').is(':checked')
    };

    let previewHtml = `
        <div class="border p-3">
            <h6>基本情報</h6>
            <table class="table table-sm">
                <tr><td>警備員</td><td>${formData.guard}</td></tr>
                <tr><td>プロジェクト</td><td>${formData.project}</td></tr>
                <tr><td>日報日</td><td>${formData.report_date}</td></tr>
                <tr><td>種別</td><td>${formData.report_type}</td></tr>
                <tr><td>重要度</td><td>${formData.priority}</td></tr>
                <tr><td>天候</td><td>${formData.weather_condition}</td></tr>
            </table>
            
            <h6>概要</h6>
            <p>${formData.summary.replace(/\n/g, '<br>')}</p>
            
            <h6>詳細報告</h6>
            <p>${formData.detailed_report.replace(/\n/g, '<br>')}</p>
    `;

    if (formData.patrol_route) {
        previewHtml += `<h6>巡回ルート</h6><p>${formData.patrol_route.replace(/\n/g, '<br>')}</p>`;
    }

    if (formData.visitor_count) {
        previewHtml += `<h6>来訪者数</h6><p>${formData.visitor_count}人</p>`;
    }

    if (formData.equipment_status) {
        previewHtml += `<h6>設備状況</h6><p>${formData.equipment_status.replace(/\n/g, '<br>')}</p>`;
    }

    if (formData.safety_observations) {
        previewHtml += `<h6>安全確認・注意事項</h6><p>${formData.safety_observations.replace(/\n/g, '<br>')}</p>`;
    }

    if (formData.recommendations) {
        previewHtml += `<h6>改善提案・推奨事項</h6><p>${formData.recommendations.replace(/\n/g, '<br>')}</p>`;
    }

    if (formData.next_shift_notes) {
        previewHtml += `<h6>次番への引き継ぎ事項</h6><p>${formData.next_shift_notes.replace(/\n/g, '<br>')}</p>`;
    }

    const flags = [];
    if (formData.has_incident) flags.push('事故・異常あり');
    if (formData.has_equipment_issue) flags.push('設備不具合あり');
    if (formData.has_safety_concern) flags.push('安全上の懸念あり');

    if (flags.length > 0) {
        previewHtml += `<h6>特記事項</h6><p class="text-warning">${flags.join(', ')}</p>`;
    }

    previewHtml += '</div>';

    $('#previewContent').html(previewHtml);
    $('#previewModal').modal('show');
}

// 警備員によるシフト絞り込み
function filterShiftsByGuard() {
    const guardId = $('#guard_id').val();
    const projectId = $('#project_id').val();
    
    $('#shift_id option').each(function() {
        if ($(this).val() === '') return;
        
        const shiftText = $(this).text();
        const guardName = shiftText.split(' - ')[0];
        const selectedGuardName = $('#guard_id option:selected').text().split(' ')[0];
        
        if (!guardId || guardName === selectedGuardName) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

// プロジェクトによるシフト絞り込み
function filterShiftsByProject() {
    const projectId = $('#project_id').val();
    const selectedProjectName = $('#project_id option:selected').text();
    
    $('#shift_id option').each(function() {
        if ($(this).val() === '') return;
        
        const shiftText = $(this).text();
        const projectMatch = shiftText.includes(selectedProjectName);
        
        if (!projectId || projectMatch) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

// アラート表示
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    $('.container-fluid').prepend(alertHtml);
    
    // 3秒後に自動で閉じる
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 3000);
}
</script>
@endpush

@push('styles')
<style>
.required {
    font-weight: 600;
}

.form-label .text-danger {
    font-weight: 700;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.accordion-button {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}

.accordion-body {
    padding: 0.75rem 1rem;
}

.form-check-label {
    font-weight: 500;
}

.text-truncate-preview {
    max-height: 200px;
    overflow-y: auto;
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn-group .btn {
        font-size: 0.875rem;
    }
}

@media print {
    .btn, .card-header, .modal {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endpush
