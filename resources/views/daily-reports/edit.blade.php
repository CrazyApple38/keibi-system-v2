@extends('layouts.app')

@section('title', '日報編集')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-edit me-2"></i>日報編集
                    </h1>
                    <p class="mb-0 text-muted">{{ \Carbon\Carbon::parse($report->report_date)->format('Y年m月d日') }}の日報を編集します</p>
                </div>
                <div>
                    <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-eye me-1"></i>詳細表示
                    </a>
                    <a href="{{ route('daily-reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>一覧に戻る
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 編集制限警告 -->
    @if($report->status === 'approved')
        <div class="alert alert-warning mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>注意：</strong>この日報は既に承認済みです。編集は制限されています。
        </div>
    @endif

    <form id="dailyReportEditForm" method="POST" action="{{ route('daily-reports.update', $report) }}">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- 左側：編集フォーム -->
            <div class="col-lg-8">
                <!-- 基本情報カード -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>基本情報
                        </h6>
                        <div>
                            @php
                                $statusLabels = [
                                    'draft' => '下書き',
                                    'submitted' => '提出済み',
                                    'approved' => '承認済み',
                                    'updated' => '更新済み'
                                ];
                                $statusClasses = [
                                    'draft' => 'secondary',
                                    'submitted' => 'warning',
                                    'approved' => 'success',
                                    'updated' => 'info'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusClasses[$report->status] ?? 'secondary' }}">
                                現在のステータス：{{ $statusLabels[$report->status] ?? $report->status }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guard_id" class="form-label required">警備員 <span class="text-danger">*</span></label>
                                <select class="form-select @error('guard_id') is-invalid @enderror" 
                                        id="guard_id" name="guard_id" required 
                                        {{ $report->status === 'approved' ? 'disabled' : '' }}>
                                    <option value="">選択してください</option>
                                    @foreach($guards as $guard)
                                        <option value="{{ $guard->id }}" 
                                                {{ (old('guard_id', $report->guard_id) == $guard->id) ? 'selected' : '' }}>
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
                                        id="project_id" name="project_id" required
                                        {{ $report->status === 'approved' ? 'disabled' : '' }}>
                                    <option value="">選択してください</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" 
                                                {{ (old('project_id', $report->project_id) == $project->id) ? 'selected' : '' }}>
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
                                        id="shift_id" name="shift_id"
                                        {{ $report->status === 'approved' ? 'disabled' : '' }}>
                                    <option value="">関連シフトを選択（任意）</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}" 
                                                {{ (old('shift_id', $report->shift_id) == $shift->id) ? 'selected' : '' }}>
                                            {{ $shift->guard->name ?? '未割当' }} - 
                                            {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}〜{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                            （{{ $shift->project->name ?? '不明' }}）
                                        </option>
                                    @endforeach
                                </select>
                                @error('shift_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="report_date" class="form-label required">日報日 <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('report_date') is-invalid @enderror" 
                                       id="report_date" name="report_date" 
                                       value="{{ old('report_date', $report->report_date) }}" required
                                       {{ $report->status === 'approved' ? 'readonly' : '' }}>
                                @error('report_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="report_type" class="form-label required">日報種別 <span class="text-danger">*</span></label>
                                <select class="form-select @error('report_type') is-invalid @enderror" 
                                        id="report_type" name="report_type" required
                                        {{ $report->status === 'approved' ? 'disabled' : '' }}>
                                    <option value="">選択してください</option>
                                    <option value="daily" {{ (old('report_type', $report->report_type) == 'daily') ? 'selected' : '' }}>日常警備</option>
                                    <option value="incident" {{ (old('report_type', $report->report_type) == 'incident') ? 'selected' : '' }}>事故・異常</option>
                                    <option value="maintenance" {{ (old('report_type', $report->report_type) == 'maintenance') ? 'selected' : '' }}>設備点検</option>
                                    <option value="security_check" {{ (old('report_type', $report->report_type) == 'security_check') ? 'selected' : '' }}>警備点検</option>
                                    <option value="patrol" {{ (old('report_type', $report->report_type) == 'patrol') ? 'selected' : '' }}>巡回報告</option>
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
                                    <option value="low" {{ (old('priority', $report->priority) == 'low') ? 'selected' : '' }}>低</option>
                                    <option value="normal" {{ (old('priority', $report->priority) == 'normal') ? 'selected' : '' }}>通常</option>
                                    <option value="high" {{ (old('priority', $report->priority) == 'high') ? 'selected' : '' }}>高</option>
                                    <option value="urgent" {{ (old('priority', $report->priority) == 'urgent') ? 'selected' : '' }}>緊急</option>
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
                                    <option value="晴れ" {{ (old('weather_condition', $report->weather_condition) == '晴れ') ? 'selected' : '' }}>晴れ</option>
                                    <option value="曇り" {{ (old('weather_condition', $report->weather_condition) == '曇り') ? 'selected' : '' }}>曇り</option>
                                    <option value="雨" {{ (old('weather_condition', $report->weather_condition) == '雨') ? 'selected' : '' }}>雨</option>
                                    <option value="雪" {{ (old('weather_condition', $report->weather_condition) == '雪') ? 'selected' : '' }}>雪</option>
                                    <option value="強風" {{ (old('weather_condition', $report->weather_condition) == '強風') ? 'selected' : '' }}>強風</option>
                                    <option value="その他" {{ (old('weather_condition', $report->weather_condition) == 'その他') ? 'selected' : '' }}>その他</option>
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
                                      placeholder="日報の概要を300文字以内で入力してください"
                                      data-original="{{ $report->summary }}">{{ old('summary', $report->summary) }}</textarea>
                            <div class="form-text">
                                <span id="summaryCount">0</span>/300文字
                                <span id="summaryChanged" class="text-warning ms-2" style="display: none;">
                                    <i class="fas fa-exclamation-triangle"></i> 変更されました
                                </span>
                            </div>
                            @error('summary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="detailed_report" class="form-label required">詳細報告 <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('detailed_report') is-invalid @enderror" 
                                      id="detailed_report" name="detailed_report" rows="8" required 
                                      placeholder="業務の詳細内容を2000文字以内で入力してください"
                                      data-original="{{ $report->detailed_report }}">{{ old('detailed_report', $report->detailed_report) }}</textarea>
                            <div class="form-text">
                                <span id="detailCount">0</span>/2000文字
                                <span id="detailChanged" class="text-warning ms-2" style="display: none;">
                                    <i class="fas fa-exclamation-triangle"></i> 変更されました
                                </span>
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
                                          placeholder="巡回した経路を入力してください"
                                          data-original="{{ $report->patrol_route }}">{{ old('patrol_route', $report->patrol_route) }}</textarea>
                                <span id="patrolChanged" class="text-warning" style="display: none;">
                                    <small><i class="fas fa-exclamation-triangle"></i> 変更されました</small>
                                </span>
                                @error('patrol_route')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="visitor_count" class="form-label">来訪者数</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('visitor_count') is-invalid @enderror" 
                                           id="visitor_count" name="visitor_count" min="0" 
                                           value="{{ old('visitor_count', $report->visitor_count) }}" placeholder="0"
                                           data-original="{{ $report->visitor_count }}">
                                    <span class="input-group-text">人</span>
                                </div>
                                <span id="visitorChanged" class="text-warning" style="display: none;">
                                    <small><i class="fas fa-exclamation-triangle"></i> 変更されました</small>
                                </span>
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
                        <div class="mb-4" id="incidentDetailsSection" style="{{ $report->has_incident ? '' : 'display: none;' }}">
                            <label for="incident_details" class="form-label">事故・異常詳細</label>
                            <textarea class="form-control @error('incident_details') is-invalid @enderror" 
                                      id="incident_details" name="incident_details" rows="4" 
                                      placeholder="事故や異常の詳細を入力してください"
                                      data-original="{{ $report->incident_details }}">{{ old('incident_details', $report->incident_details) }}</textarea>
                            <span id="incidentChanged" class="text-warning" style="display: none;">
                                <small><i class="fas fa-exclamation-triangle"></i> 変更されました</small>
                            </span>
                            @error('incident_details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="equipment_status" class="form-label">設備状況</label>
                                <textarea class="form-control @error('equipment_status') is-invalid @enderror" 
                                          id="equipment_status" name="equipment_status" rows="3" 
                                          placeholder="設備の稼働状況や点検結果を入力してください"
                                          data-original="{{ $report->equipment_status }}">{{ old('equipment_status', $report->equipment_status) }}</textarea>
                                <span id="equipmentChanged" class="text-warning" style="display: none;">
                                    <small><i class="fas fa-exclamation-triangle"></i> 変更されました</small>
                                </span>
                                @error('equipment_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="maintenance_notes" class="form-label">保守・メンテナンス</label>
                                <textarea class="form-control @error('maintenance_notes') is-invalid @enderror" 
                                          id="maintenance_notes" name="maintenance_notes" rows="3" 
                                          placeholder="実施した保守作業やメンテナンス内容を入力してください"
                                          data-original="{{ $report->maintenance_notes }}">{{ old('maintenance_notes', $report->maintenance_notes) }}</textarea>
                                <span id="maintenanceChanged" class="text-warning" style="display: none;">
                                    <small><i class="fas fa-exclamation-triangle"></i> 変更されました</small>
                                </span>
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
                                          placeholder="安全面での気づきや注意事項を入力してください"
                                          data-original="{{ $report->safety_observations }}">{{ old('safety_observations', $report->safety_observations) }}</textarea>
                                <span id="safetyChanged" class="text-warning" style="display: none;">
                                    <small><i class="fas fa-exclamation-triangle"></i> 変更されました</small>
                                </span>
                                @error('safety_observations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="recommendations" class="form-label">改善提案・推奨事項</label>
                                <textarea class="form-control @error('recommendations') is-invalid @enderror" 
                                          id="recommendations" name="recommendations" rows="3" 
                                          placeholder="業務改善のための提案や推奨事項を入力してください"
                                          data-original="{{ $report->recommendations }}">{{ old('recommendations', $report->recommendations) }}</textarea>
                                <span id="recommendationsChanged" class="text-warning" style="display: none;">
                                    <small><i class="fas fa-exclamation-triangle"></i> 変更されました</small>
                                </span>
                                @error('recommendations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="next_shift_notes" class="form-label">次番への引き継ぎ事項</label>
                            <textarea class="form-control @error('next_shift_notes') is-invalid @enderror" 
                                      id="next_shift_notes" name="next_shift_notes" rows="3" 
                                      placeholder="次の勤務者への引き継ぎ事項を入力してください"
                                      data-original="{{ $report->next_shift_notes }}">{{ old('next_shift_notes', $report->next_shift_notes) }}</textarea>
                            <span id="nextShiftChanged" class="text-warning" style="display: none;">
                                <small><i class="fas fa-exclamation-triangle"></i> 変更されました</small>
                            </span>
                            @error('next_shift_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 変更理由カード -->
                <div class="card shadow mb-4" id="changeReasonCard" style="display: none;">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-edit me-2"></i>変更理由（必須）
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="edit_reason" class="form-label required">編集理由 <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_reason" name="edit_reason">
                                <option value="">選択してください</option>
                                <option value="誤字脱字修正">誤字脱字修正</option>
                                <option value="情報追加">情報追加</option>
                                <option value="詳細修正">詳細修正</option>
                                <option value="データ修正">データ修正</option>
                                <option value="上司指示">上司指示による修正</option>
                                <option value="その他">その他</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_memo" class="form-label">詳細説明</label>
                            <textarea class="form-control" id="edit_memo" name="edit_memo" rows="3" 
                                      placeholder="変更内容や理由の詳細を入力してください"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 右側：特記事項・プレビュー・保存 -->
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
                                   {{ old('has_incident', $report->has_incident) ? 'checked' : '' }}
                                   data-original="{{ $report->has_incident ? 'checked' : '' }}">
                            <label class="form-check-label text-danger" for="has_incident">
                                <i class="fas fa-exclamation-triangle me-1"></i>事故・異常が発生した
                            </label>
                            <span id="incidentFlagChanged" class="text-warning d-block" style="display: none;">
                                <small><i class="fas fa-exclamation-triangle"></i> 変更されました</small>
                            </span>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="has_equipment_issue" name="has_equipment_issue" 
                                   {{ old('has_equipment_issue', $report->has_equipment_issue) ? 'checked' : '' }}
                                   data-original="{{ $report->has_equipment_issue ? 'checked' : '' }}">
                            <label class="form-check-label text-warning" for="has_equipment_issue">
                                <i class="fas fa-tools me-1"></i>設備に不具合があった
                            </label>
                            <span id="equipmentFlagChanged" class="text-warning d-block" style="display: none;">
                                <small><i class="fas fa-exclamation-triangle"></i> 変更されました</small>
                            </span>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="has_safety_concern" name="has_safety_concern" 
                                   {{ old('has_safety_concern', $report->has_safety_concern) ? 'checked' : '' }}
                                   data-original="{{ $report->has_safety_concern ? 'checked' : '' }}">
                            <label class="form-check-label text-info" for="has_safety_concern">
                                <i class="fas fa-shield-alt me-1"></i>安全上の懸念がある
                            </label>
                            <span id="safetyFlagChanged" class="text-warning d-block" style="display: none;">
                                <small><i class="fas fa-exclamation-triangle"></i> 変更されました</small>
                            </span>
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
                            注意：適用すると現在の内容が上書きされます
                        </div>
                    </div>
                </div>

                <!-- 変更検知カード -->
                <div class="card shadow mb-4" id="changesCard" style="display: none;">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>変更検知
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                フォームに変更が検知されました。保存前に変更理由の入力が必要です。
                            </small>
                        </div>
                        <button type="button" class="btn btn-outline-warning btn-sm w-100" onclick="showChanges()">
                            <i class="fas fa-list me-1"></i>変更内容を確認
                        </button>
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
                            @if($report->status !== 'approved')
                                <button type="submit" class="btn btn-primary" name="action" value="update">
                                    <i class="fas fa-save me-1"></i>更新保存
                                </button>
                                @if($report->status === 'draft')
                                    <button type="submit" class="btn btn-success" name="action" value="submit">
                                        <i class="fas fa-paper-plane me-1"></i>提出
                                    </button>
                                @endif
                            @else
                                <div class="alert alert-info">
                                    <small>承認済みのため編集できません</small>
                                </div>
                            @endif
                        </div>
                        <div class="form-text mt-2">
                            変更がある場合は変更理由の入力が必要です
                        </div>
                    </div>
                </div>

                <!-- 元データ表示カード -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-secondary">
                            <i class="fas fa-history me-2"></i>元データ確認
                        </h6>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-2" onclick="toggleOriginalData()">
                            <i class="fas fa-eye me-1"></i>元データを表示/非表示
                        </button>
                        <div id="originalDataSection" style="display: none;">
                            <small class="text-muted">
                                <strong>作成日時：</strong>{{ $report->created_at->format('Y/m/d H:i') }}<br>
                                <strong>最終更新：</strong>{{ $report->updated_at->format('Y/m/d H:i') }}<br>
                                @if($report->approved_at)
                                    <strong>承認日時：</strong>{{ \Carbon\Carbon::parse($report->approved_at)->format('Y/m/d H:i') }}
                                @endif
                            </small>
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
                <h5 class="modal-title" id="previewModalLabel">日報編集プレビュー</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- プレビュー内容がここに動的に挿入されます -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" onclick="$('#dailyReportEditForm').submit();">
                    このまま保存
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 変更内容確認モーダル -->
<div class="modal fade" id="changesModal" tabindex="-1" aria-labelledby="changesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changesModalLabel">変更内容確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="changesContent">
                <!-- 変更内容がここに動的に挿入されます -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let hasChanges = false;
let originalValues = {};

$(document).ready(function() {
    // 元の値を保存
    saveOriginalValues();
    
    // 文字数カウント
    $('#summary').on('input', function() {
        const count = $(this).val().length;
        $('#summaryCount').text(count);
        if (count > 300) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
        checkChanges();
    });

    $('#detailed_report').on('input', function() {
        const count = $(this).val().length;
        $('#detailCount').text(count);
        if (count > 2000) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
        checkChanges();
    });

    // 変更検知
    $('input, select, textarea').on('change input', function() {
        checkChanges();
    });

    // 事故・異常フラグの切り替え
    $('#has_incident').change(function() {
        if ($(this).is(':checked')) {
            $('#incidentDetailsSection').slideDown();
            $('#incident_details').attr('required', true);
        } else {
            $('#incidentDetailsSection').slideUp();
            $('#incident_details').removeAttr('required');
        }
        checkChanges();
    });

    // テンプレート選択
    $('#template_select').change(function() {
        const selectedOption = $(this).find('option:selected');
        $('#applyTemplateBtn').prop('disabled', selectedOption.val() === '');
    });

    // テンプレート適用
    $('#applyTemplateBtn').click(function() {
        if (confirm('現在の入力内容がテンプレートで上書きされます。よろしいですか？')) {
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
                
                // 変更検知
                checkChanges();
                
                showAlert('success', 'テンプレートを適用しました。');
            }
        }
    });

    // プレビュー機能
    $('#previewBtn').click(function() {
        generatePreview();
    });

    // フォーム送信前バリデーション
    $('#dailyReportEditForm').submit(function(e) {
        if (hasChanges && !$('#edit_reason').val()) {
            e.preventDefault();
            showAlert('error', '変更理由を選択してください。');
            $('#changeReasonCard').show();
            $('#edit_reason').focus();
            return false;
        }
        
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
    });

    // 初期値の文字数カウント
    $('#summary').trigger('input');
    $('#detailed_report').trigger('input');
});

// 元の値を保存
function saveOriginalValues() {
    $('input, select, textarea').each(function() {
        const $this = $(this);
        if ($this.attr('type') === 'checkbox') {
            originalValues[$this.attr('id')] = $this.is(':checked');
        } else {
            originalValues[$this.attr('id')] = $this.val();
        }
    });
}

// 変更チェック
function checkChanges() {
    hasChanges = false;
    const changedFields = [];

    $('input, select, textarea').each(function() {
        const $this = $(this);
        const fieldId = $this.attr('id');
        const currentValue = $this.attr('type') === 'checkbox' ? $this.is(':checked') : $this.val();
        const originalValue = $this.data('original') || originalValues[fieldId];

        if (currentValue != originalValue) {
            hasChanges = true;
            changedFields.push(fieldId);
            $(`#${fieldId}Changed`).show();
        } else {
            $(`#${fieldId}Changed`).hide();
        }
    });

    // 変更検知カードの表示/非表示
    if (hasChanges) {
        $('#changesCard').show();
        $('#changeReasonCard').show();
    } else {
        $('#changesCard').hide();
        $('#changeReasonCard').hide();
    }
}

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
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>編集中のプレビューです
            </div>
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

// 変更内容表示
function showChanges() {
    let changesHtml = '<div class="list-group">';
    
    $('input, select, textarea').each(function() {
        const $this = $(this);
        const fieldId = $this.attr('id');
        const fieldLabel = $(`label[for="${fieldId}"]`).text().replace(' *', '');
        const currentValue = $this.attr('type') === 'checkbox' ? ($this.is(':checked') ? 'チェック済み' : 'チェックなし') : $this.val();
        const originalValue = $this.data('original') || originalValues[fieldId];
        const originalDisplayValue = $this.attr('type') === 'checkbox' ? (originalValue ? 'チェック済み' : 'チェックなし') : originalValue;

        if (currentValue != originalValue) {
            changesHtml += `
                <div class="list-group-item">
                    <h6 class="mb-1">${fieldLabel}</h6>
                    <p class="mb-1">
                        <strong>変更前：</strong><span class="text-muted">${originalDisplayValue || '（空）'}</span><br>
                        <strong>変更後：</strong><span class="text-primary">${currentValue || '（空）'}</span>
                    </p>
                </div>
            `;
        }
    });
    
    changesHtml += '</div>';
    
    $('#changesContent').html(changesHtml);
    $('#changesModal').modal('show');
}

// 元データ表示切り替え
function toggleOriginalData() {
    $('#originalDataSection').toggle();
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

.text-warning {
    font-weight: 500;
}

.form-check-label {
    font-weight: 500;
}

.text-truncate-preview {
    max-height: 200px;
    overflow-y: auto;
}

.list-group-item h6 {
    color: #5a5c69;
}

.border-changed {
    border-color: #f6c23e !important;
    box-shadow: 0 0 0 0.2rem rgba(246, 194, 62, 0.25);
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
