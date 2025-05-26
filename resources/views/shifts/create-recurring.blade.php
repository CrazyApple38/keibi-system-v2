@extends('layouts.app')

@section('title', '定期シフト作成')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('shifts.index') }}">シフト管理</a></li>
                    <li class="breadcrumb-item active">定期シフト作成</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-arrow-repeat me-2"></i>
                        定期シフト作成
                    </h2>
                    <p class="text-muted mb-0">継続的なシフトパターンを設定・自動生成</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        シフト一覧へ戻る
                    </a>
                    <a href="{{ route('shifts.calendar') }}" class="btn btn-outline-info">
                        <i class="bi bi-calendar-month me-1"></i>
                        カレンダー表示
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form id="recurringShiftForm" method="POST" action="{{ route('shifts.store') }}">
        @csrf
        <div class="row">
            <!-- 基本情報セクション -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            基本情報
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="project_id" class="form-label">プロジェクト <span class="text-danger">*</span></label>
                                <select class="form-select @error('project_id') is-invalid @enderror" 
                                        id="project_id" name="project_id" required>
                                    <option value="">プロジェクトを選択...</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" 
                                                {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->customer->name }} - {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">勤務場所 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                       id="location" name="location" value="{{ old('location') }}" 
                                       placeholder="勤務場所を入力..." required>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="start_time" class="form-label">開始時刻 <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                       id="start_time" name="start_time" value="{{ old('start_time', '09:00') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="end_time" class="form-label">終了時刻 <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                       id="end_time" name="end_time" value="{{ old('end_time', '18:00') }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="required_guards" class="form-label">必要警備員数 <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('required_guards') is-invalid @enderror" 
                                       id="required_guards" name="required_guards" value="{{ old('required_guards', 1) }}" 
                                       min="1" max="50" required>
                                @error('required_guards')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="break_duration" class="form-label">休憩時間（分）</label>
                                <input type="number" class="form-control @error('break_duration') is-invalid @enderror" 
                                       id="break_duration" name="break_duration" value="{{ old('break_duration', 60) }}" 
                                       min="0" max="480">
                                @error('break_duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">業務内容・説明</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="業務内容や注意事項を入力...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="special_instructions" class="form-label">特別指示事項</label>
                            <textarea class="form-control @error('special_instructions') is-invalid @enderror" 
                                      id="special_instructions" name="special_instructions" rows="2" 
                                      placeholder="特別な指示や注意点があれば入力...">{{ old('special_instructions') }}</textarea>
                            @error('special_instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 繰り返し設定セクション -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-week me-2"></i>
                            繰り返し設定
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="recurring_type" class="form-label">繰り返しパターン <span class="text-danger">*</span></label>
                                <select class="form-select" id="recurring_type" name="recurring_type" required>
                                    <option value="">パターンを選択...</option>
                                    <option value="daily" {{ old('recurring_type') == 'daily' ? 'selected' : '' }}>毎日</option>
                                    <option value="weekly" {{ old('recurring_type') == 'weekly' ? 'selected' : '' }}>毎週</option>
                                    <option value="monthly" {{ old('recurring_type') == 'monthly' ? 'selected' : '' }}>毎月</option>
                                    <option value="custom" {{ old('recurring_type') == 'custom' ? 'selected' : '' }}>カスタム</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="recurring_interval" class="form-label">間隔</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="recurring_interval" 
                                           name="recurring_interval" value="{{ old('recurring_interval', 1) }}" min="1" max="12">
                                    <span class="input-group-text" id="intervalUnit">回ごと</span>
                                </div>
                                <small class="text-muted">例：2なら2週間ごと、3なら3日ごと</small>
                            </div>
                        </div>

                        <!-- 曜日選択（週次の場合） -->
                        <div class="mb-3" id="weeklyOptions" style="display: none;">
                            <label class="form-label">実行曜日を選択</label>
                            <div class="row">
                                @foreach(['monday' => '月', 'tuesday' => '火', 'wednesday' => '水', 'thursday' => '木', 'friday' => '金', 'saturday' => '土', 'sunday' => '日'] as $day => $label)
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="weekly_days[]" 
                                                   value="{{ $day }}" id="day_{{ $day }}">
                                            <label class="form-check-label" for="day_{{ $day }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- 月次オプション -->
                        <div class="mb-3" id="monthlyOptions" style="display: none;">
                            <label class="form-label">月次パターン</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="monthly_type" value="day_of_month" id="dayOfMonth">
                                <label class="form-check-label" for="dayOfMonth">
                                    毎月 <input type="number" class="form-control d-inline-block" style="width: 80px;" 
                                               name="monthly_day" min="1" max="31" value="1"> 日
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="monthly_type" value="day_of_week" id="dayOfWeek">
                                <label class="form-check-label" for="dayOfWeek">
                                    毎月第
                                    <select class="form-select d-inline-block" style="width: 80px;" name="monthly_week">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="last">最終</option>
                                    </select>
                                    <select class="form-select d-inline-block" style="width: 80px;" name="monthly_weekday">
                                        <option value="monday">月</option>
                                        <option value="tuesday">火</option>
                                        <option value="wednesday">水</option>
                                        <option value="thursday">木</option>
                                        <option value="friday">金</option>
                                        <option value="saturday">土</option>
                                        <option value="sunday">日</option>
                                    </select>
                                    曜日
                                </label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">開始日 <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">終了日</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                       id="end_date" name="end_date" value="{{ old('end_date') }}">
                                <small class="text-muted">空欄の場合は無期限</small>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="max_occurrences" class="form-label">最大作成回数</label>
                                <input type="number" class="form-control" id="max_occurrences" 
                                       name="max_occurrences" value="{{ old('max_occurrences') }}" min="1" max="365">
                                <small class="text-muted">空欄の場合は回数制限なし</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="exclude_holidays" class="form-label">除外設定</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="exclude_holidays" 
                                           id="exclude_holidays" {{ old('exclude_holidays') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="exclude_holidays">
                                        祝日を除外する
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="exclude_weekends" 
                                           id="exclude_weekends" {{ old('exclude_weekends') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="exclude_weekends">
                                        土日を除外する
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- サイドバー（プレビュー・警備員選択） -->
            <div class="col-lg-4">
                <!-- プレビューセクション -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-eye me-2"></i>
                            生成プレビュー
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="previewArea">
                            <p class="text-muted text-center">設定内容を入力すると、<br>生成されるシフトの<br>プレビューが表示されます</p>
                        </div>
                        
                        <button type="button" class="btn btn-outline-primary w-100 mt-3" id="previewButton">
                            <i class="bi bi-search me-1"></i>
                            プレビュー生成
                        </button>
                    </div>
                </div>

                <!-- 警備員選択セクション -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-people me-2"></i>
                            警備員選択
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">割り当て方法</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="assignment_method" 
                                       value="manual" id="manual_assignment" checked>
                                <label class="form-check-label" for="manual_assignment">
                                    手動選択
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="assignment_method" 
                                       value="auto" id="auto_assignment">
                                <label class="form-check-label" for="auto_assignment">
                                    自動最適化
                                </label>
                            </div>
                        </div>

                        <div id="manualAssignmentArea">
                            <label class="form-label">警備員を選択</label>
                            <div class="guard-selection" style="max-height: 300px; overflow-y: auto;">
                                @foreach($guards as $guard)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="guard_ids[]" 
                                               value="{{ $guard->id }}" id="guard_{{ $guard->id }}">
                                        <label class="form-check-label" for="guard_{{ $guard->id }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $guard->user->name }}</strong>
                                                    <small class="text-muted d-block">{{ $guard->experience_years }}年経験</small>
                                                </div>
                                                <span class="badge bg-secondary">¥{{ number_format($guard->hourly_wage) }}/h</span>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div id="autoAssignmentArea" style="display: none;">
                            <p class="text-info">
                                <i class="bi bi-info-circle me-1"></i>
                                自動最適化では、経験年数・スキル・コスト効率を総合的に判断して最適な警備員を自動選択します。
                            </p>
                            
                            <div class="mb-3">
                                <label for="optimization_priority" class="form-label">最適化優先度</label>
                                <select class="form-select" id="optimization_priority" name="optimization_priority">
                                    <option value="balanced">バランス重視</option>
                                    <option value="cost">コスト重視</option>
                                    <option value="experience">経験重視</option>
                                    <option value="skill">スキル重視</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 実行ボタン -->
                <div class="card mt-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-success w-100 mb-2" id="createButton">
                            <i class="bi bi-calendar-plus me-1"></i>
                            定期シフトを作成
                        </button>
                        
                        <button type="button" class="btn btn-outline-warning w-100" id="saveTemplateButton">
                            <i class="bi bi-bookmark me-1"></i>
                            テンプレートとして保存
                        </button>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                設定した条件に基づいて<br>
                                複数のシフトが一括作成されます
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 繰り返しタイプによる表示切り替え
    $('#recurring_type').change(function() {
        const type = $(this).val();
        
        // 全てのオプションを非表示
        $('#weeklyOptions, #monthlyOptions').hide();
        
        // 間隔単位の更新
        let unit = '';
        switch(type) {
            case 'daily':
                unit = '日ごと';
                break;
            case 'weekly':
                unit = '週ごと';
                $('#weeklyOptions').show();
                break;
            case 'monthly':
                unit = 'ヶ月ごと';
                $('#monthlyOptions').show();
                break;
            case 'custom':
                unit = '回ごと';
                break;
        }
        $('#intervalUnit').text(unit);
    });

    // 割り当て方法による表示切り替え
    $('input[name="assignment_method"]').change(function() {
        if ($(this).val() === 'manual') {
            $('#manualAssignmentArea').show();
            $('#autoAssignmentArea').hide();
        } else {
            $('#manualAssignmentArea').hide();
            $('#autoAssignmentArea').show();
        }
    });

    // プレビュー生成
    $('#previewButton').click(function() {
        generatePreview();
    });

    // フォーム値変更時の自動プレビュー更新
    $('#recurring_type, #recurring_interval, #start_date, #end_date, #max_occurrences').change(function() {
        generatePreview();
    });

    // プレビュー生成関数
    function generatePreview() {
        const formData = new FormData($('#recurringShiftForm')[0]);
        
        // バリデーション
        const requiredFields = ['project_id', 'recurring_type', 'start_date', 'start_time', 'end_time', 'required_guards'];
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!formData.get(field)) {
                isValid = false;
            }
        });

        if (!isValid) {
            $('#previewArea').html(`
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    必須項目を入力してからプレビューを生成してください
                </div>
            `);
            return;
        }

        // プレビュー生成のAjax処理
        $('#previewButton').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>生成中...');

        // 実際のプレビュー生成ロジック（簡易版）
        setTimeout(() => {
            const dates = generateDateList(formData);
            
            let previewHtml = `
                <div class="mb-3">
                    <h6>生成予定シフト: ${dates.length}件</h6>
                    <small class="text-muted">最初の10件を表示</small>
                </div>
                <div class="list-group list-group-flush">
            `;
            
            dates.slice(0, 10).forEach((date, index) => {
                previewHtml += `
                    <div class="list-group-item p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="fw-bold">${date}</small>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    ${formData.get('start_time')} - ${formData.get('end_time')}
                                </div>
                            </div>
                            <span class="badge bg-primary">${formData.get('required_guards')}名</span>
                        </div>
                    </div>
                `;
            });
            
            if (dates.length > 10) {
                previewHtml += `
                    <div class="list-group-item p-2 text-center text-muted">
                        他 ${dates.length - 10} 件...
                    </div>
                `;
            }
            
            previewHtml += '</div>';
            
            $('#previewArea').html(previewHtml);
            $('#previewButton').prop('disabled', false).html('<i class="bi bi-search me-1"></i>プレビュー更新');
        }, 1000);
    }

    // 日付リスト生成（簡易版）
    function generateDateList(formData) {
        const dates = [];
        const startDate = new Date(formData.get('start_date'));
        const endDate = formData.get('end_date') ? new Date(formData.get('end_date')) : null;
        const recurringType = formData.get('recurring_type');
        const interval = parseInt(formData.get('recurring_interval')) || 1;
        const maxOccurrences = parseInt(formData.get('max_occurrences')) || 100;
        
        let currentDate = new Date(startDate);
        let count = 0;
        
        while (count < maxOccurrences && (!endDate || currentDate <= endDate)) {
            if (count > 50) break; // 表示上の制限
            
            dates.push(currentDate.toLocaleDateString('ja-JP'));
            count++;
            
            // 次の日付を計算
            switch(recurringType) {
                case 'daily':
                    currentDate.setDate(currentDate.getDate() + interval);
                    break;
                case 'weekly':
                    currentDate.setDate(currentDate.getDate() + (7 * interval));
                    break;
                case 'monthly':
                    currentDate.setMonth(currentDate.getMonth() + interval);
                    break;
                default:
                    currentDate.setDate(currentDate.getDate() + interval);
            }
        }
        
        return dates;
    }

    // フォーム送信処理
    $('#recurringShiftForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('is_recurring', '1');
        
        $('#createButton').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>作成中...');
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    window.location.href = "{{ route('shifts.calendar') }}";
                } else {
                    alert('定期シフトの作成に失敗しました');
                }
            },
            error: function(xhr) {
                alert('エラーが発生しました');
                console.error(xhr.responseText);
            },
            complete: function() {
                $('#createButton').prop('disabled', false).html('<i class="bi bi-calendar-plus me-1"></i>定期シフトを作成');
            }
        });
    });

    // テンプレート保存
    $('#saveTemplateButton').click(function() {
        // テンプレート保存のロジック（実装は要件に応じて）
        alert('テンプレート保存機能は現在開発中です');
    });

    // 初期化
    $('#recurring_type').trigger('change');
});
</script>
@endpush
@endsection