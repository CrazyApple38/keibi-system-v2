@extends('layouts.app')

@section('title', '勤怠記録編集')

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
                            <li class="breadcrumb-item"><a href="{{ route('attendances.show', $attendance) }}">詳細</a></li>
                            <li class="breadcrumb-item active">編集</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0">勤怠記録編集</h1>
                    <p class="text-muted mb-0">{{ $attendance->attendance_date ? $attendance->attendance_date->format('Y年m月d日') : '未設定' }} - {{ $attendance->guard->name ?? '未設定' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('attendances.show', $attendance) }}" class="btn btn-outline-info">
                        <i class="fas fa-eye"></i> 詳細表示
                    </a>
                    <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> 一覧に戻る
                    </a>
                </div>
            </div>

            <!-- 編集制限チェック -->
            @if(($attendance->status ?? '') === 'approved')
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>注意:</strong> この勤怠記録は既に承認されています。編集には管理者権限が必要です。
                </div>
            @endif

            @if($attendance->clock_out && \Carbon\Carbon::parse($attendance->clock_out)->diffInDays(now()) > 30)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>情報:</strong> 30日以上前の記録です。編集理由の入力が必要です。
                </div>
            @endif

            <form action="{{ route('attendances.update', $attendance) }}" method="POST" enctype="multipart/form-data" id="attendanceEditForm">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- 左カラム: メインフォーム -->
                    <div class="col-lg-8">
                        <!-- 変更理由・履歴 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-edit"></i> 編集情報
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="edit_reason" class="form-label required">編集理由</label>
                                        <select class="form-select @error('edit_reason') is-invalid @enderror" id="edit_reason" name="edit_reason" required>
                                            <option value="">編集理由を選択してください</option>
                                            <option value="time_correction">時間修正</option>
                                            <option value="location_update">場所情報更新</option>
                                            <option value="break_time_adjustment">休憩時間調整</option>
                                            <option value="status_change">ステータス変更</option>
                                            <option value="photo_addition">写真追加</option>
                                            <option value="note_addition">備考追加</option>
                                            <option value="admin_correction">管理者修正</option>
                                            <option value="other">その他</option>
                                        </select>
                                        @error('edit_reason')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">編集者</label>
                                        <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_note" class="form-label">編集内容詳細</label>
                                    <textarea class="form-control @error('edit_note') is-invalid @enderror" 
                                              id="edit_note" name="edit_note" rows="3" 
                                              placeholder="具体的な編集内容や理由を入力してください" required>{{ old('edit_note') }}</textarea>
                                    @error('edit_note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 変更履歴表示 -->
                                @if($attendance->change_history ?? false)
                                    <div class="mt-3">
                                        <h6>変更履歴</h6>
                                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                            @foreach(json_decode($attendance->change_history, true) ?? [] as $change)
                                                <div class="border-bottom pb-2 mb-2">
                                                    <div class="d-flex justify-content-between">
                                                        <strong>{{ $change['reason'] ?? '理由不明' }}</strong>
                                                        <small class="text-muted">{{ $change['date'] ?? '' }}</small>
                                                    </div>
                                                    <div class="small text-muted">編集者: {{ $change['user'] ?? '不明' }}</div>
                                                    <div class="small">{{ $change['note'] ?? '' }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
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
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">警備員</label>
                                        <input type="text" class="form-control" 
                                               value="{{ $attendance->guard->name ?? '未設定' }} ({{ $attendance->guard->employee_id ?? '' }})" 
                                               readonly style="background-color: #f8f9fa;">
                                        <div class="form-text">警備員は変更できません</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">プロジェクト</label>
                                        <input type="text" class="form-control" 
                                               value="{{ $attendance->shift->project->name ?? '未設定' }}" 
                                               readonly style="background-color: #f8f9fa;">
                                        <div class="form-text">プロジェクトは変更できません</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="attendance_date" class="form-label required">勤務日</label>
                                        <input type="date" class="form-control @error('attendance_date') is-invalid @enderror" 
                                               id="attendance_date" name="attendance_date" 
                                               value="{{ old('attendance_date', $attendance->attendance_date ? $attendance->attendance_date->format('Y-m-d') : '') }}" 
                                               required>
                                        @error('attendance_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label required">ステータス</label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="pending" {{ old('status', $attendance->status) === 'pending' ? 'selected' : '' }}>承認待ち</option>
                                            <option value="approved" {{ old('status', $attendance->status) === 'approved' ? 'selected' : '' }}>承認済み</option>
                                            <option value="rejected" {{ old('status', $attendance->status) === 'rejected' ? 'selected' : '' }}>差し戻し</option>
                                            <option value="working" {{ old('status', $attendance->status) === 'working' ? 'selected' : '' }}>勤務中</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="work_location" class="form-label">勤務場所</label>
                                        <input type="text" class="form-control @error('work_location') is-invalid @enderror" 
                                               id="work_location" name="work_location" 
                                               value="{{ old('work_location', $attendance->work_location ?? '') }}" 
                                               placeholder="勤務場所">
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
                                    <span class="badge bg-secondary ms-2" id="clockInChangeBadge" style="display: none;">変更あり</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="clock_in" class="form-label">出勤時間</label>
                                        <div class="input-group">
                                            <input type="time" class="form-control @error('clock_in') is-invalid @enderror" 
                                                   id="clock_in" name="clock_in" 
                                                   value="{{ old('clock_in', $attendance->clock_in ? $attendance->clock_in->format('H:i') : '') }}"
                                                   data-original="{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}">
                                            <button type="button" class="btn btn-outline-secondary" id="setCurrentClockIn">
                                                <i class="fas fa-clock"></i>
                                            </button>
                                        </div>
                                        @error('clock_in')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            元の時間: {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '未設定' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="clock_in_note" class="form-label">出勤メモ</label>
                                        <textarea class="form-control @error('clock_in_note') is-invalid @enderror" 
                                                  id="clock_in_note" name="clock_in_note" rows="2" 
                                                  placeholder="出勤時の特記事項">{{ old('clock_in_note', $attendance->clock_in_note ?? '') }}</textarea>
                                        @error('clock_in_note')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="clock_in_photo" class="form-label">出勤時写真</label>
                                        @if($attendance->clock_in_photo ?? false)
                                            <div class="mb-2">
                                                <img src="{{ Storage::url($attendance->clock_in_photo) }}" 
                                                     alt="現在の出勤時写真" class="img-thumbnail" style="max-height: 100px;">
                                                <div class="form-text">現在の写真（新しい写真をアップロードで置き換え）</div>
                                            </div>
                                        @endif
                                        <input type="file" class="form-control @error('clock_in_photo') is-invalid @enderror" 
                                               id="clock_in_photo" name="clock_in_photo" accept="image/*">
                                        @error('clock_in_photo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">JPG, PNG形式（最大2MB）</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">GPS位置情報</label>
                                        @if($attendance->clock_in_location ?? false)
                                            <div class="border rounded p-2 mb-2 bg-light">
                                                <small class="text-muted">現在の位置:</small>
                                                <div class="small">{{ $attendance->clock_in_location }}</div>
                                            </div>
                                        @endif
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-info" id="getClockInLocation">
                                                <i class="fas fa-map-marker-alt"></i> 現在地取得
                                            </button>
                                            <span id="clockInLocationStatus" class="align-self-center text-muted">
                                                {{ $attendance->clock_in_location ? '位置情報あり' : '未取得' }}
                                            </span>
                                        </div>
                                        <input type="hidden" id="clock_in_latitude" name="clock_in_latitude" 
                                               value="{{ $attendance->clock_in_latitude ?? '' }}">
                                        <input type="hidden" id="clock_in_longitude" name="clock_in_longitude" 
                                               value="{{ $attendance->clock_in_longitude ?? '' }}">
                                        <input type="hidden" id="clock_in_accuracy" name="clock_in_accuracy" 
                                               value="{{ $attendance->clock_in_accuracy ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 退勤情報 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-sign-out-alt"></i> 退勤情報
                                    <span class="badge bg-secondary ms-2" id="clockOutChangeBadge" style="display: none;">変更あり</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="clock_out" class="form-label">退勤時間</label>
                                        <div class="input-group">
                                            <input type="time" class="form-control @error('clock_out') is-invalid @enderror" 
                                                   id="clock_out" name="clock_out" 
                                                   value="{{ old('clock_out', $attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}"
                                                   data-original="{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}">
                                            <button type="button" class="btn btn-outline-secondary" id="setCurrentClockOut">
                                                <i class="fas fa-clock"></i>
                                            </button>
                                        </div>
                                        @error('clock_out')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            元の時間: {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '未設定' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="clock_out_note" class="form-label">退勤メモ</label>
                                        <textarea class="form-control @error('clock_out_note') is-invalid @enderror" 
                                                  id="clock_out_note" name="clock_out_note" rows="2" 
                                                  placeholder="退勤時の特記事項">{{ old('clock_out_note', $attendance->clock_out_note ?? '') }}</textarea>
                                        @error('clock_out_note')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="clock_out_photo" class="form-label">退勤時写真</label>
                                        @if($attendance->clock_out_photo ?? false)
                                            <div class="mb-2">
                                                <img src="{{ Storage::url($attendance->clock_out_photo) }}" 
                                                     alt="現在の退勤時写真" class="img-thumbnail" style="max-height: 100px;">
                                                <div class="form-text">現在の写真（新しい写真をアップロードで置き換え）</div>
                                            </div>
                                        @endif
                                        <input type="file" class="form-control @error('clock_out_photo') is-invalid @enderror" 
                                               id="clock_out_photo" name="clock_out_photo" accept="image/*">
                                        @error('clock_out_photo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">JPG, PNG形式（最大2MB）</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">GPS位置情報</label>
                                        @if($attendance->clock_out_location ?? false)
                                            <div class="border rounded p-2 mb-2 bg-light">
                                                <small class="text-muted">現在の位置:</small>
                                                <div class="small">{{ $attendance->clock_out_location }}</div>
                                            </div>
                                        @endif
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-info" id="getClockOutLocation">
                                                <i class="fas fa-map-marker-alt"></i> 現在地取得
                                            </button>
                                            <span id="clockOutLocationStatus" class="align-self-center text-muted">
                                                {{ $attendance->clock_out_location ? '位置情報あり' : '未取得' }}
                                            </span>
                                        </div>
                                        <input type="hidden" id="clock_out_latitude" name="clock_out_latitude" 
                                               value="{{ $attendance->clock_out_latitude ?? '' }}">
                                        <input type="hidden" id="clock_out_longitude" name="clock_out_longitude" 
                                               value="{{ $attendance->clock_out_longitude ?? '' }}">
                                        <input type="hidden" id="clock_out_accuracy" name="clock_out_accuracy" 
                                               value="{{ $attendance->clock_out_accuracy ?? '' }}">
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
                                               value="{{ old('break_time', $attendance->break_time ?? 60) }}" 
                                               min="0" max="480" step="15"
                                               data-original="{{ $attendance->break_time ?? 60 }}">
                                        @error('break_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">元の時間: {{ $attendance->break_time ?? 60 }}分</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">実働時間</label>
                                        <input type="text" class="form-control" id="total_work_hours_display" readonly
                                               style="background-color: #f8f9fa;">
                                        <input type="hidden" id="total_work_hours" name="total_work_hours" 
                                               value="{{ $attendance->total_work_hours ?? '' }}">
                                        <div class="form-text">元の時間: {{ number_format($attendance->total_work_hours ?? 0, 2) }}時間</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">残業時間</label>
                                        <input type="text" class="form-control" id="overtime_hours_display" readonly
                                               style="background-color: #f8f9fa;">
                                        <input type="hidden" id="overtime_hours" name="overtime_hours" 
                                               value="{{ $attendance->overtime_hours ?? '' }}">
                                        <div class="form-text">元の時間: {{ number_format($attendance->overtime_hours ?? 0, 2) }}時間</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">備考</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3" 
                                              placeholder="その他特記事項">{{ old('notes', $attendance->notes ?? '') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 送信ボタン -->
                        <div class="d-flex justify-content-between mb-4">
                            <a href="{{ route('attendances.show', $attendance) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> キャンセル
                            </a>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" id="previewChangesBtn">
                                    <i class="fas fa-eye"></i> 変更プレビュー
                                </button>
                                <button type="submit" class="btn btn-success" id="updateBtn">
                                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                    <i class="fas fa-save"></i> 変更を保存
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 右カラム: 変更確認・統計 -->
                    <div class="col-lg-4">
                        <!-- 変更内容確認 -->
                        <div class="card mb-4 sticky-top" style="top: 1rem;">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clipboard-check"></i> 変更内容確認
                                </h5>
                            </div>
                            <div class="card-body" id="changesPreview">
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-edit fa-3x mb-3"></i>
                                    <p>変更はありません</p>
                                </div>
                            </div>
                        </div>

                        <!-- 勤務時間比較 -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-balance-scale"></i> 勤務時間比較
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="p-2 border rounded">
                                            <small class="text-muted">変更前</small>
                                            <div class="h6 mb-0" id="originalHours">
                                                {{ number_format($attendance->total_work_hours ?? 0, 2) }}h
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="p-2 border rounded">
                                            <small class="text-muted">変更後</small>
                                            <div class="h6 mb-0 text-primary" id="newHours">
                                                {{ number_format($attendance->total_work_hours ?? 0, 2) }}h
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-2 border rounded">
                                            <small class="text-muted">差分</small>
                                            <div class="h6 mb-0" id="hoursDiff">±0.00h</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 編集履歴 -->
                        @if($attendance->updated_at && $attendance->updated_at != $attendance->created_at)
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-history"></i> 編集履歴
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="timeline">
                                        <div class="timeline-item">
                                            <small class="text-muted">作成日時</small>
                                            <div>{{ $attendance->created_at ? $attendance->created_at->format('Y/m/d H:i') : '未設定' }}</div>
                                        </div>
                                        @if($attendance->updated_at != $attendance->created_at)
                                            <div class="timeline-item">
                                                <small class="text-muted">最終更新</small>
                                                <div>{{ $attendance->updated_at ? $attendance->updated_at->format('Y/m/d H:i') : '未設定' }}</div>
                                            </div>
                                        @endif
                                        @if($attendance->approved_at ?? false)
                                            <div class="timeline-item">
                                                <small class="text-muted">承認日時</small>
                                                <div>{{ $attendance->approved_at ? $attendance->approved_at->format('Y/m/d H:i') : '未設定' }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- バリデーション結果 -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-shield-alt"></i> バリデーション結果
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="validationResults">
                                    <div class="text-center text-success py-3">
                                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                                        <p class="mb-0">問題ありません</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 変更プレビューモーダル -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">変更内容プレビュー</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detailedPreview">
                    <!-- 詳細な変更内容がここに表示されます -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" onclick="$('#attendanceEditForm').submit()">
                    <i class="fas fa-save"></i> 変更を保存
                </button>
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

.timeline {
    position: relative;
    padding: 1rem 0;
}

.timeline-item {
    position: relative;
    padding: 0.5rem 0;
    padding-left: 1.5rem;
    border-left: 2px solid #dee2e6;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -4px;
    top: 1rem;
    width: 6px;
    height: 6px;
    background: #0d6efd;
    border-radius: 50%;
}

.sticky-top {
    top: 1rem !important;
}

.card {
    transition: box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.change-item {
    padding: 0.5rem;
    margin: 0.25rem 0;
    border-radius: 0.375rem;
    background-color: #f8f9fa;
    border-left: 4px solid #0d6efd;
}

.change-item.added {
    background-color: #d1edff;
    border-left-color: #0d6efd;
}

.change-item.modified {
    background-color: #fff3cd;
    border-left-color: #ffc107;
}

.change-item.removed {
    background-color: #f8d7da;
    border-left-color: #dc3545;
}

@media (max-width: 768px) {
    .sticky-top {
        position: relative !important;
        top: auto !important;
    }
}
</style>
@endpush>

@push('scripts')
<script>
// 元データ保存（変更検知用）
const originalData = {
    clock_in: '{{ $attendance->clock_in ? $attendance->clock_in->format("H:i") : "" }}',
    clock_out: '{{ $attendance->clock_out ? $attendance->clock_out->format("H:i") : "" }}',
    break_time: {{ $attendance->break_time ?? 60 }},
    total_work_hours: {{ $attendance->total_work_hours ?? 0 }},
    overtime_hours: {{ $attendance->overtime_hours ?? 0 }},
    status: '{{ $attendance->status ?? "" }}',
    work_location: '{{ $attendance->work_location ?? "" }}',
    clock_in_note: '{{ $attendance->clock_in_note ?? "" }}',
    clock_out_note: '{{ $attendance->clock_out_note ?? "" }}',
    notes: '{{ $attendance->notes ?? "" }}'
};

$(document).ready(function() {
    // 初期化
    initializeForm();
    
    // イベントリスナー設定
    setupEventListeners();
    
    // 初期計算
    updateCalculations();
    checkForChanges();
});

function initializeForm() {
    // 変更検知の初期設定
    trackFieldChanges();
    
    // 初期バリデーション
    validateForm();
}

function setupEventListeners() {
    // 時間変更時の処理
    $('#clock_in, #clock_out, #break_time').on('input change', function() {
        updateCalculations();
        checkForChanges();
        validateForm();
    });
    
    // その他フィールド変更時
    $('#status, #work_location, #clock_in_note, #clock_out_note, #notes').on('input change', function() {
        checkForChanges();
        validateForm();
    });
    
    // 現在時刻設定
    $('#setCurrentClockIn').click(() => setCurrentTime('clock_in'));
    $('#setCurrentClockOut').click(() => setCurrentTime('clock_out'));
    
    // GPS位置情報取得
    $('#getClockInLocation').click(() => getCurrentLocation('clock_in'));
    $('#getClockOutLocation').click(() => getCurrentLocation('clock_out'));
    
    // プレビューボタン
    $('#previewChangesBtn').click(showDetailedPreview);
    
    // フォーム送信
    $('#attendanceEditForm').submit(function(e) {
        e.preventDefault();
        if (validateForm() && confirmChanges()) {
            showLoading();
            this.submit();
        }
    });
}

function trackFieldChanges() {
    // 各フィールドの変更を監視
    $('#clock_in').on('input', function() {
        const current = $(this).val();
        const original = $(this).data('original');
        toggleChangeBadge('clockInChangeBadge', current !== original);
    });
    
    $('#clock_out').on('input', function() {
        const current = $(this).val();
        const original = $(this).data('original');
        toggleChangeBadge('clockOutChangeBadge', current !== original);
    });
}

function toggleChangeBadge(badgeId, show) {
    if (show) {
        $(`#${badgeId}`).show();
    } else {
        $(`#${badgeId}`).hide();
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
            end.add(1, 'day');
        }
        
        const totalMinutes = end.diff(start, 'minutes');
        const workMinutes = totalMinutes - breakTime;
        const workHours = workMinutes / 60;
        
        // 表示更新
        $('#total_work_hours').val(workHours.toFixed(2));
        $('#total_work_hours_display').val(`${Math.floor(workHours)}:${String(Math.round((workHours % 1) * 60)).padStart(2, '0')}`);
        $('#newHours').text(workHours.toFixed(2) + 'h');
        
        // 残業時間計算（8時間基準）
        const standardHours = 8;
        const overtimeHours = Math.max(0, workHours - standardHours);
        
        $('#overtime_hours').val(overtimeHours.toFixed(2));
        $('#overtime_hours_display').val(`${Math.floor(overtimeHours)}:${String(Math.round((overtimeHours % 1) * 60)).padStart(2, '0')}`);
        
        // 差分計算
        const originalHours = originalData.total_work_hours;
        const hoursDiff = workHours - originalHours;
        $('#hoursDiff').text((hoursDiff >= 0 ? '+' : '') + hoursDiff.toFixed(2) + 'h');
        
        if (hoursDiff > 0) {
            $('#hoursDiff').removeClass('text-danger').addClass('text-success');
        } else if (hoursDiff < 0) {
            $('#hoursDiff').removeClass('text-success').addClass('text-danger');
        } else {
            $('#hoursDiff').removeClass('text-success text-danger');
        }
    }
}

function checkForChanges() {
    const changes = [];
    
    // 各フィールドの変更をチェック
    const currentData = {
        clock_in: $('#clock_in').val(),
        clock_out: $('#clock_out').val(),
        break_time: parseInt($('#break_time').val()) || 0,
        status: $('#status').val(),
        work_location: $('#work_location').val(),
        clock_in_note: $('#clock_in_note').val(),
        clock_out_note: $('#clock_out_note').val(),
        notes: $('#notes').val()
    };
    
    Object.keys(currentData).forEach(key => {
        if (currentData[key] != originalData[key]) {
            changes.push({
                field: key,
                original: originalData[key],
                current: currentData[key],
                label: getFieldLabel(key)
            });
        }
    });
    
    updateChangesPreview(changes);
    return changes.length > 0;
}

function getFieldLabel(fieldName) {
    const labels = {
        clock_in: '出勤時間',
        clock_out: '退勤時間',
        break_time: '休憩時間',
        status: 'ステータス',
        work_location: '勤務場所',
        clock_in_note: '出勤メモ',
        clock_out_note: '退勤メモ',
        notes: '備考'
    };
    return labels[fieldName] || fieldName;
}

function updateChangesPreview(changes) {
    if (changes.length === 0) {
        $('#changesPreview').html(`
            <div class="text-center text-muted py-4">
                <i class="fas fa-edit fa-3x mb-3"></i>
                <p>変更はありません</p>
            </div>
        `);
        return;
    }
    
    const changesHtml = changes.map(change => `
        <div class="change-item modified">
            <strong>${change.label}</strong>
            <div class="small text-muted">変更前: ${change.original || '未設定'}</div>
            <div class="small text-primary">変更後: ${change.current || '未設定'}</div>
        </div>
    `).join('');
    
    $('#changesPreview').html(`
        <div class="mb-3">
            <h6><i class="fas fa-exclamation-circle text-warning"></i> ${changes.length}件の変更があります</h6>
        </div>
        ${changesHtml}
    `);
}

function showDetailedPreview() {
    const changes = checkForChanges();
    
    if (changes.length === 0) {
        alert('変更がありません。');
        return;
    }
    
    const detailedHtml = `
        <h6>変更概要</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>項目</th>
                        <th>変更前</th>
                        <th>変更後</th>
                    </tr>
                </thead>
                <tbody>
                    ${changes.map(change => `
                        <tr>
                            <td><strong>${change.label}</strong></td>
                            <td class="text-muted">${change.original || '未設定'}</td>
                            <td class="text-primary">${change.current || '未設定'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        
        <h6 class="mt-3">編集理由</h6>
        <div class="border rounded p-2">
            <strong>理由:</strong> ${$('#edit_reason option:selected').text()}<br>
            <strong>詳細:</strong> ${$('#edit_note').val() || '未入力'}
        </div>
    `;
    
    $('#detailedPreview').html(detailedHtml);
    $('#previewModal').modal('show');
}

function setCurrentTime(field) {
    const now = new Date();
    const timeString = now.toTimeString().split(' ')[0].substring(0, 5);
    $(`#${field}`).val(timeString);
    
    updateCalculations();
    checkForChanges();
    validateForm();
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
                checkForChanges();
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

function validateForm() {
    const errors = [];
    
    // 必須項目チェック
    if (!$('#edit_reason').val()) {
        errors.push('編集理由を選択してください');
    }
    
    if (!$('#edit_note').val().trim()) {
        errors.push('編集内容詳細を入力してください');
    }
    
    if (!$('#attendance_date').val()) {
        errors.push('勤務日を入力してください');
    }
    
    // 時間整合性チェック
    const clockIn = $('#clock_in').val();
    const clockOut = $('#clock_out').val();
    
    if (clockIn && clockOut) {
        const start = moment(`2000-01-01 ${clockIn}`);
        const end = moment(`2000-01-01 ${clockOut}`);
        
        if (end.isSameOrBefore(start)) {
            errors.push('退勤時間は出勤時間より後である必要があります');
        }
    }
    
    // バリデーション結果表示
    updateValidationResults(errors);
    
    return errors.length === 0;
}

function updateValidationResults(errors) {
    if (errors.length === 0) {
        $('#validationResults').html(`
            <div class="text-center text-success py-3">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <p class="mb-0">問題ありません</p>
            </div>
        `);
    } else {
        const errorsHtml = errors.map(error => `
            <div class="alert alert-danger py-2 mb-2">
                <i class="fas fa-exclamation-triangle me-2"></i>${error}
            </div>
        `).join('');
        $('#validationResults').html(errorsHtml);
    }
}

function confirmChanges() {
    const hasChanges = checkForChanges();
    
    if (!hasChanges) {
        alert('変更がありません。');
        return false;
    }
    
    const reason = $('#edit_reason option:selected').text();
    const note = $('#edit_note').val();
    
    return confirm(`以下の内容で勤怠記録を更新します：\n\n編集理由: ${reason}\n詳細: ${note}\n\n実行しますか？`);
}

function showLoading() {
    const updateBtn = $('#updateBtn');
    const spinner = updateBtn.find('.spinner-border');
    
    updateBtn.prop('disabled', true);
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
        isSameOrBefore: (other) => date <= other.date,
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
