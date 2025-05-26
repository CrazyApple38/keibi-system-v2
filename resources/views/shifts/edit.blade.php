@extends('layouts.app')

@section('title', 'シフト編集')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('shifts.index') }}">シフト管理</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('shifts.show', $shift) }}">シフト詳細</a></li>
                    <li class="breadcrumb-item active">編集</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-pencil-square me-2"></i>
                        シフト編集
                    </h2>
                    <p class="text-muted mb-0">{{ $shift->project->name ?? '未設定' }} - {{ $shift->start_date ? $shift->start_date->format('Y年n月j日') : '未設定' }}</p>
                </div>
                <div class="d-flex gap-2">
                    @if(in_array($shift->status, ['in_progress', 'completed']))
                    <div class="alert alert-warning py-2 px-3 mb-0" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <small>進行中・完了済みシフトの編集は制限されます</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- シフト編集フォーム -->
    <form action="{{ route('shifts.update', $shift) }}" method="POST" id="shiftEditForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- メインフォーム -->
            <div class="col-lg-8 col-md-12">
                <!-- 基本情報 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar me-2"></i>
                                基本情報
                            </h5>
                            <div class="d-flex gap-2">
                                <span class="badge bg-{{ $shift->getStatusColor() }} fs-6">
                                    {{ $shift->getStatusText() }}
                                </span>
                                @if($shift->updated_at && $shift->updated_at->diffInDays() < 7)
                                <span class="badge bg-info">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $shift->updated_at->diffForHumans() }}更新
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- プロジェクト -->
                            <div class="col-md-6">
                                <label for="project_id" class="form-label required">プロジェクト</label>
                                <select class="form-select @error('project_id') is-invalid @enderror" 
                                        id="project_id" name="project_id" required
                                        {{ in_array($shift->status, ['in_progress', 'completed']) ? 'disabled' : '' }}>
                                    <option value="">選択してください</option>
                                    @foreach($projects ?? [] as $project)
                                        <option value="{{ $project->id }}" 
                                                {{ (old('project_id', $shift->project_id) == $project->id) ? 'selected' : '' }}
                                                data-location="{{ $project->location }}" 
                                                data-default-guards="{{ $project->default_guards }}">
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($shift->project_id && old('project_id', $shift->project_id) != $shift->project_id)
                                    <div class="form-text text-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        プロジェクト変更は警備員配置に影響する可能性があります
                                    </div>
                                @endif
                            </div>
                            
                            <!-- ステータス -->
                            <div class="col-md-6">
                                <label for="status" class="form-label required">ステータス</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    @foreach(['scheduled' => '予定', 'draft' => '下書き', 'cancelled' => 'キャンセル', 'in_progress' => '進行中', 'completed' => '完了'] as $value => $text)
                                        @if($shift->canTransitionTo($value) || $shift->status === $value)
                                        <option value="{{ $value }}" {{ old('status', $shift->status) === $value ? 'selected' : '' }}>
                                            {{ $text }}
                                        </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    利用可能なステータス変更のみ表示されています
                                </div>
                            </div>
                            
                            <!-- 日付設定 -->
                            <div class="col-md-4">
                                <label for="start_date" class="form-label required">開始日</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" name="start_date" 
                                       value="{{ old('start_date', $shift->start_date ? $shift->start_date->format('Y-m-d') : '') }}" 
                                       required
                                       {{ $shift->status === 'completed' ? 'readonly' : '' }}>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">終了日</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                       id="end_date" name="end_date" 
                                       value="{{ old('end_date', $shift->end_date ? $shift->end_date->format('Y-m-d') : '') }}"
                                       {{ $shift->status === 'completed' ? 'readonly' : '' }}>
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
                            
                            <!-- 時間設定 -->
                            <div class="col-md-6">
                                <label for="start_time" class="form-label required">開始時間</label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                       id="start_time" name="start_time" 
                                       value="{{ old('start_time', $shift->start_time) }}" 
                                       required
                                       {{ $shift->status === 'completed' ? 'readonly' : '' }}>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="end_time" class="form-label required">終了時間</label>
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                       id="end_time" name="end_time" 
                                       value="{{ old('end_time', $shift->end_time) }}" 
                                       required
                                       {{ $shift->status === 'completed' ? 'readonly' : '' }}>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">勤務時間: <span id="workingHours">{{ $shift->getDurationHours() }}時間</span></div>
                            </div>
                            
                            <!-- 休憩時間・必要警備員数 -->
                            <div class="col-md-6">
                                <label for="break_time" class="form-label">休憩時間（分）</label>
                                <input type="number" class="form-control @error('break_time') is-invalid @enderror" 
                                       id="break_time" name="break_time" 
                                       value="{{ old('break_time', $shift->break_time) }}" 
                                       min="0" max="480" step="15"
                                       {{ $shift->status === 'completed' ? 'readonly' : '' }}>
                                @error('break_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="required_guards" class="form-label required">必要警備員数</label>
                                <input type="number" class="form-control @error('required_guards') is-invalid @enderror" 
                                       id="required_guards" name="required_guards" 
                                       value="{{ old('required_guards', $shift->required_guards) }}" 
                                       min="1" max="50" required>
                                @error('required_guards')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    現在配置済み: {{ $shift->assignedGuards->count() }}名
                                    @if($shift->assignedGuards->count() > old('required_guards', $shift->required_guards))
                                        <span class="text-warning">（配置済み警備員数を下回っています）</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 配置済み警備員管理 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-people me-2"></i>
                                配置済み警備員管理
                                <span class="badge bg-primary ms-2">{{ $shift->assignedGuards->count() }}名</span>
                            </h5>
                            @if(!in_array($shift->status, ['completed']))
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addGuards">
                                    <i class="bi bi-person-plus me-1"></i>
                                    警備員追加
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" id="autoOptimize">
                                    <i class="bi bi-cpu me-1"></i>
                                    自動最適化
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if($shift->assignedGuards && $shift->assignedGuards->count() > 0)
                            <div class="row g-3" id="assignedGuardsList">
                                @foreach($shift->assignedGuards as $assignment)
                                <div class="col-lg-6 col-md-12" data-assignment-id="{{ $assignment->id }}">
                                    <div class="card border {{ $assignment->hasAttendance() ? 'border-success' : '' }}">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                @if($assignment->guard->profile_photo)
                                                    <img src="{{ Storage::url($assignment->guard->profile_photo) }}" 
                                                         class="rounded-circle me-3" width="50" height="50" 
                                                         style="object-fit: cover;" alt="プロフィール">
                                                @else
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 50px; height: 50px;">
                                                        <span class="text-white fw-bold">{{ mb_substr($assignment->guard->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">{{ $assignment->guard->name }}</h6>
                                                    <small class="text-muted">{{ $assignment->guard->employee_id }}</small>
                                                    <div class="mt-1">
                                                        @if($assignment->guard->skills)
                                                            @foreach(array_slice($assignment->guard->skills, 0, 2) as $skill)
                                                                <span class="badge bg-secondary me-1">{{ $assignment->guard->getSkillText($skill) }}</span>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="input-group input-group-sm mb-1" style="width: 100px;">
                                                        <span class="input-group-text">¥</span>
                                                        <input type="number" 
                                                               class="form-control hourly-rate-input" 
                                                               name="assignments[{{ $assignment->id }}][hourly_rate]"
                                                               value="{{ $assignment->hourly_rate ?? $assignment->guard->hourly_rate }}"
                                                               min="900" max="10000"
                                                               {{ $shift->status === 'completed' ? 'readonly' : '' }}>
                                                    </div>
                                                    <span class="badge bg-{{ $assignment->getStatusColor() }}">
                                                        {{ $assignment->getStatusText() }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            @if($assignment->hasAttendance())
                                            <div class="mt-2 p-2 bg-light rounded">
                                                <small class="text-success fw-bold">
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    勤怠記録あり
                                                </small>
                                                @if($assignment->attendance)
                                                <div class="small text-muted">
                                                    出勤: {{ $assignment->attendance->check_in_time ? $assignment->attendance->check_in_time->format('H:i') : '-' }} 
                                                    退勤: {{ $assignment->attendance->check_out_time ? $assignment->attendance->check_out_time->format('H:i') : '-' }}
                                                </div>
                                                @endif
                                            </div>
                                            @endif
                                            
                                            @if(!in_array($shift->status, ['completed']))
                                            <div class="mt-2">
                                                <div class="btn-group btn-group-sm w-100" role="group">
                                                    <button type="button" class="btn btn-outline-info" 
                                                            onclick="viewGuardDetails({{ $assignment->guard->id }})">
                                                        <i class="bi bi-eye"></i> 詳細
                                                    </button>
                                                    @if(!$assignment->hasAttendance())
                                                    <button type="button" class="btn btn-outline-warning" 
                                                            onclick="editAssignment({{ $assignment->id }})">
                                                        <i class="bi bi-pencil"></i> 編集
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="removeAssignment({{ $assignment->id }})">
                                                        <i class="bi bi-x"></i> 除外
                                                    </button>
                                                    @else
                                                    <button type="button" class="btn btn-outline-secondary" disabled>
                                                        <i class="bi bi-lock"></i> 勤怠記録済み
                                                    </button>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <!-- 配置変更の影響チェック -->
                            <div id="assignmentChanges" class="mt-3 d-none">
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-info-circle me-2"></i>変更の影響</h6>
                                    <ul id="changeImpactList" class="mb-0">
                                        <!-- JavaScriptで動的に更新 -->
                                    </ul>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-person-plus display-6 text-muted"></i>
                                <div class="mt-3">
                                    <h6 class="text-muted">警備員が配置されていません</h6>
                                    <button type="button" class="btn btn-primary" id="addGuards">
                                        <i class="bi bi-person-plus me-1"></i>
                                        警備員を配置
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- 追加情報・設定 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-gear me-2"></i>
                            追加設定
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- 特別な要件 -->
                            <div class="col-md-6">
                                <label class="form-label">特別な要件</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="requirements[]" 
                                           value="guard_license" id="req_guard_license"
                                           {{ in_array('guard_license', $shift->requirements ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="req_guard_license">
                                        警備員検定必須
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="requirements[]" 
                                           value="experience_required" id="req_experience"
                                           {{ in_array('experience_required', $shift->requirements ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="req_experience">
                                        経験者優先
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="requirements[]" 
                                           value="multilingual" id="req_multilingual"
                                           {{ in_array('multilingual', $shift->requirements ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="req_multilingual">
                                        多言語対応
                                    </label>
                                </div>
                            </div>
                            
                            <!-- 時給設定 -->
                            <div class="col-md-6">
                                <label for="hourly_rate" class="form-label">基本時給（オーバーライド）</label>
                                <div class="input-group">
                                    <span class="input-group-text">¥</span>
                                    <input type="number" class="form-control @error('hourly_rate') is-invalid @enderror" 
                                           id="hourly_rate" name="hourly_rate" 
                                           value="{{ old('hourly_rate', $shift->hourly_rate) }}" 
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
                                          placeholder="特記事項、注意点など">{{ old('notes', $shift->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 変更理由（編集時のみ） -->
                            <div class="col-12">
                                <label for="change_reason" class="form-label required">変更理由</label>
                                <textarea class="form-control @error('change_reason') is-invalid @enderror" 
                                          id="change_reason" name="change_reason" rows="2" 
                                          placeholder="変更内容と理由を入力してください" required>{{ old('change_reason') }}</textarea>
                                @error('change_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">変更履歴として記録されます</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- サイドバー -->
            <div class="col-lg-4 col-md-12">
                <!-- 現在の状況 -->
                <div class="card mb-4 sticky-top" style="top: 1rem;">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-speedometer2 me-2"></i>
                            現在の状況
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar" style="width: {{ $shift->getCompletionPercentage() }}%"></div>
                            </div>
                            <strong>{{ $shift->getCompletionPercentage() }}% 完了</strong>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="text-primary fw-bold fs-4">{{ $shift->assignedGuards->count() }}</div>
                                <small class="text-muted">配置済み警備員</small>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-success fw-bold fs-4">{{ $shift->attendances->where('status', 'present')->count() }}</div>
                                <small class="text-muted">出勤済み</small>
                            </div>
                            <div class="col-6">
                                <div class="text-warning fw-bold fs-4">{{ $shift->dailyReports->count() }}</div>
                                <small class="text-muted">日報提出</small>
                            </div>
                            <div class="col-6">
                                <div class="text-info fw-bold fs-4">{{ $shift->getDurationHours() }}h</div>
                                <small class="text-muted">勤務時間</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 費用計算（編集版） -->
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
                                <small class="text-muted">変更前</small>
                                <div class="fw-bold" id="originalCost">¥{{ number_format($shift->calculateTotalCost()) }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">変更後</small>
                                <div class="fw-bold text-primary" id="newCost">¥{{ number_format($shift->calculateTotalCost()) }}</div>
                            </div>
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>差額</strong>
                                    <strong id="costDifference" class="text-success">¥0</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 編集制限・注意事項 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            編集制限・注意事項
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($shift->status === 'in_progress')
                        <div class="alert alert-warning py-2 mb-2">
                            <small><strong>進行中シフト:</strong> 日時変更は制限されます</small>
                        </div>
                        @endif
                        
                        @if($shift->status === 'completed')
                        <div class="alert alert-info py-2 mb-2">
                            <small><strong>完了済みシフト:</strong> 基本情報の変更はできません</small>
                        </div>
                        @endif
                        
                        @if($shift->assignedGuards->whereNotNull('attendance_id')->count() > 0)
                        <div class="alert alert-warning py-2 mb-2">
                            <small><strong>勤怠記録済み:</strong> 該当警備員の除外はできません</small>
                        </div>
                        @endif
                        
                        <div class="small text-muted">
                            <ul class="mb-0">
                                <li>変更は変更履歴として記録されます</li>
                                <li>配置済み警備員への通知が送信されます</li>
                                <li>重要な変更には承認が必要な場合があります</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- 変更履歴プレビュー -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            変更履歴
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(isset($changeHistory) && count($changeHistory) > 0)
                            @foreach($changeHistory as $change)
                            <div class="d-flex align-items-start mb-3">
                                <div class="bg-secondary rounded-circle p-2 me-3">
                                    <i class="bi bi-pencil text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small fw-bold">{{ $change['field'] }}の変更</div>
                                    <div class="small text-muted">{{ $change['reason'] }}</div>
                                    <div class="small text-muted">{{ $change['date'] }} by {{ $change['user'] }}</div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-clock-history display-6"></i>
                                <div class="mt-2">変更履歴はありません</div>
                            </div>
                        @endif
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
                            <a href="{{ route('shifts.show', $shift) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                キャンセル
                            </a>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-info" onclick="previewChanges()">
                                    <i class="bi bi-eye me-1"></i>
                                    変更プレビュー
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="validateForm()">
                                    <i class="bi bi-check-circle me-1"></i>
                                    入力確認
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                    <i class="bi bi-check2 me-1"></i>
                                    変更を保存
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- 変更プレビューモーダル -->
<div class="modal fade" id="changePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i>
                    変更プレビュー
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="changePreviewContent">
                    <!-- JavaScriptで動的に更新 -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-success" onclick="submitForm()">
                    <i class="bi bi-check2 me-1"></i>
                    この内容で保存
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
    
    .sticky-top {
        top: 1rem;
    }
    
    .card.border-success {
        border-color: #198754 !important;
    }
    
    .progress {
        height: 10px;
    }
    
    .hourly-rate-input {
        font-size: 0.875rem;
    }
    
    .form-check-input:checked {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
    }
    
    .alert.py-2 {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
    }
    
    .change-indicator {
        border-left: 4px solid #ffc107;
        background-color: rgba(255, 193, 7, 0.1);
    }
    
    @media (max-width: 768px) {
        .sticky-top {
            position: relative !important;
            top: auto !important;
        }
        
        .col-lg-6 {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // 初期データ保存（変更検知用）
    const originalFormData = new FormData(document.getElementById('shiftEditForm'));
    const originalCost = {{ $shift->calculateTotalCost() }};
    
    $(document).ready(function() {
        // 初期化
        initializeForm();
        
        // 日付・時間変更時の処理
        $('#start_date, #end_date, #start_time, #end_time').change(function() {
            updateDayOfWeek();
            updateWorkingHours();
            updateCostCalculation();
            checkChangeImpact();
        });
        
        // プロジェクト変更時の処理
        $('#project_id').change(function() {
            updateProjectDefaults();
            checkChangeImpact();
        });
        
        // 必要警備員数変更
        $('#required_guards').change(function() {
            checkChangeImpact();
            updateCostCalculation();
        });
        
        // 時給変更監視
        $('.hourly-rate-input').on('input', function() {
            updateCostCalculation();
            checkChangeImpact();
        });
        
        // 警備員管理
        $('#addGuards').click(function() {
            addGuards();
        });
        
        $('#autoOptimize').click(function() {
            autoOptimizeAssignments();
        });
        
        // フォーム送信
        $('#shiftEditForm').on('submit', function(e) {
            e.preventDefault();
            if (validateForm()) {
                showLoading();
                this.submit();
            }
        });
        
        // 変更監視
        $('input, select, textarea').on('change input', function() {
            detectChanges();
        });
    });
    
    // フォーム初期化
    function initializeForm() {
        updateDayOfWeek();
        updateWorkingHours();
        updateCostCalculation();
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
        
        if (defaultGuards && !$('#required_guards').val()) {
            $('#required_guards').val(defaultGuards);
        }
    }
    
    // 費用計算更新
    function updateCostCalculation() {
        const formData = new FormData(document.getElementById('shiftEditForm'));
        
        $.post('{{ route("shifts.calculate-cost", $shift) }}', {
            start_time: $('#start_time').val(),
            end_time: $('#end_time').val(),
            break_time: $('#break_time').val(),
            required_guards: $('#required_guards').val(),
            hourly_rate: $('#hourly_rate').val(),
            assignments: getAssignmentData(),
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            $('#newCost').text('¥' + response.total_cost.toLocaleString());
            
            const difference = response.total_cost - originalCost;
            const differenceEl = $('#costDifference');
            
            if (difference > 0) {
                differenceEl.text('+¥' + difference.toLocaleString()).removeClass('text-success').addClass('text-danger');
            } else if (difference < 0) {
                differenceEl.text('-¥' + Math.abs(difference).toLocaleString()).removeClass('text-danger').addClass('text-success');
            } else {
                differenceEl.text('¥0').removeClass('text-danger text-success').addClass('text-success');
            }
        })
        .fail(function() {
            console.error('費用計算に失敗しました');
        });
    }
    
    // 変更の影響チェック
    function checkChangeImpact() {
        const impacts = [];
        
        // 日時変更の影響
        if ($('#start_date').val() !== '{{ $shift->start_date ? $shift->start_date->format("Y-m-d") : "" }}' ||
            $('#start_time').val() !== '{{ $shift->start_time }}' ||
            $('#end_time').val() !== '{{ $shift->end_time }}') {
            impacts.push('警備員への通知が必要です');
            impacts.push('勤怠記録への影響を確認してください');
        }
        
        // 警備員数変更の影響
        const currentRequired = parseInt($('#required_guards').val());
        const originalRequired = {{ $shift->required_guards }};
        const assignedCount = {{ $shift->assignedGuards->count() }};
        
        if (currentRequired < assignedCount) {
            impacts.push(`配置済み警備員数(${assignedCount}名)を下回っています`);
        }
        
        if (currentRequired !== originalRequired) {
            impacts.push('警備員配置の見直しが必要な可能性があります');
        }
        
        // プロジェクト変更の影響
        if ($('#project_id').val() !== '{{ $shift->project_id }}') {
            impacts.push('プロジェクト変更により、配置要件が変わる可能性があります');
        }
        
        // 影響表示
        if (impacts.length > 0) {
            const impactHtml = impacts.map(impact => `<li>${impact}</li>`).join('');
            $('#changeImpactList').html(impactHtml);
            $('#assignmentChanges').removeClass('d-none');
        } else {
            $('#assignmentChanges').addClass('d-none');
        }
    }
    
    // 変更検知
    function detectChanges() {
        const currentFormData = new FormData(document.getElementById('shiftEditForm'));
        let hasChanges = false;
        
        // フォームデータ比較（簡易版）
        for (let [key, value] of currentFormData.entries()) {
            if (originalFormData.get(key) !== value) {
                hasChanges = true;
                break;
            }
        }
        
        // 変更された要素にクラス追加
        $('input, select, textarea').each(function() {
            const element = $(this);
            const fieldName = element.attr('name');
            
            if (fieldName && originalFormData.get(fieldName) !== element.val()) {
                element.closest('.card').addClass('change-indicator');
            } else {
                element.closest('.card').removeClass('change-indicator');
            }
        });
    }
    
    // 配置データ取得
    function getAssignmentData() {
        const assignments = {};
        $('.hourly-rate-input').each(function() {
            const input = $(this);
            const name = input.attr('name');
            if (name) {
                const match = name.match(/assignments\[(\d+)\]\[hourly_rate\]/);
                if (match) {
                    assignments[match[1]] = {
                        hourly_rate: input.val()
                    };
                }
            }
        });
        return assignments;
    }
    
    // 警備員追加
    function addGuards() {
        window.location.href = `{{ route('shifts.assign', $shift) }}`;
    }
    
    // 自動最適化
    function autoOptimizeAssignments() {
        if (confirm('現在の配置を最適化しますか？\n配置済み警備員の変更が行われる可能性があります。')) {
            $.post(`{{ route('shifts.optimize', $shift) }}`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                showSuccessMessage('配置を最適化しました');
                setTimeout(() => location.reload(), 1500);
            })
            .fail(function() {
                showErrorMessage('最適化に失敗しました');
            });
        }
    }
    
    // 警備員詳細表示
    function viewGuardDetails(guardId) {
        window.open(`{{ route('guards.show', '') }}/${guardId}`, '_blank');
    }
    
    // 配置編集
    function editAssignment(assignmentId) {
        // 配置編集モーダル表示（実装省略）
        console.log('配置編集:', assignmentId);
    }
    
    // 配置除外
    function removeAssignment(assignmentId) {
        if (confirm('この警備員をシフトから除外しますか？')) {
            $.ajax({
                url: `{{ route('shifts.assignments.destroy', '') }}/${assignmentId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showSuccessMessage('警備員を除外しました');
                    $(`div[data-assignment-id="${assignmentId}"]`).fadeOut();
                    updateCostCalculation();
                    checkChangeImpact();
                },
                error: function(xhr) {
                    showErrorMessage('除外処理に失敗しました');
                }
            });
        }
    }
    
    // 変更プレビュー
    function previewChanges() {
        const changes = getFormChanges();
        
        let previewHtml = '<div class="table-responsive"><table class="table table-sm">';
        previewHtml += '<thead><tr><th>項目</th><th>変更前</th><th>変更後</th></tr></thead><tbody>';
        
        if (changes.length === 0) {
            previewHtml += '<tr><td colspan="3" class="text-center text-muted">変更はありません</td></tr>';
        } else {
            changes.forEach(change => {
                previewHtml += `
                    <tr>
                        <td>${change.field}</td>
                        <td class="text-muted">${change.oldValue}</td>
                        <td class="text-primary fw-bold">${change.newValue}</td>
                    </tr>
                `;
            });
        }
        
        previewHtml += '</tbody></table></div>';
        
        $('#changePreviewContent').html(previewHtml);
        $('#changePreviewModal').modal('show');
    }
    
    // フォーム変更取得
    function getFormChanges() {
        const changes = [];
        
        // 主要フィールドの変更チェック
        const fieldMap = {
            'project_id': 'プロジェクト',
            'status': 'ステータス',
            'start_date': '開始日',
            'end_date': '終了日',
            'start_time': '開始時間',
            'end_time': '終了時間',
            'break_time': '休憩時間',
            'required_guards': '必要警備員数',
            'hourly_rate': '基本時給',
            'notes': '備考'
        };
        
        Object.entries(fieldMap).forEach(([field, label]) => {
            const currentValue = $(`#${field}`).val();
            const originalValue = originalFormData.get(field) || '';
            
            if (currentValue !== originalValue) {
                changes.push({
                    field: label,
                    oldValue: originalValue || '（未設定）',
                    newValue: currentValue || '（未設定）'
                });
            }
        });
        
        return changes;
    }
    
    // バリデーション
    function validateForm() {
        const requiredFields = ['project_id', 'start_date', 'start_time', 'end_time', 'required_guards', 'change_reason'];
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
        
        // 必要警備員数と配置済み警備員数の整合性チェック
        const requiredGuards = parseInt($('#required_guards').val());
        const assignedGuards = {{ $shift->assignedGuards->count() }};
        
        if (requiredGuards < assignedGuards) {
            const confirm = window.confirm(
                `警告: 必要警備員数(${requiredGuards}名)が配置済み警備員数(${assignedGuards}名)を下回っています。\n` +
                '一部の警備員が自動的に除外される可能性があります。続行しますか？'
            );
            if (!confirm) {
                isValid = false;
            }
        }
        
        if (!isValid) {
            alert('必須項目を入力してください。');
        }
        
        return isValid;
    }
    
    // フォーム送信
    function submitForm() {
        $('#changePreviewModal').modal('hide');
        if (validateForm()) {
            showLoading();
            $('#shiftEditForm').submit();
        }
    }
    
    // ローディング表示
    function showLoading() {
        const submitBtn = $('#shiftEditForm button[type="submit"]');
        const spinner = submitBtn.find('.spinner-border');
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
    }
</script>
@endpush
@endsection