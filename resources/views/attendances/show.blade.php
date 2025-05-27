@extends('layouts.app')

@section('title', '勤怠記録詳細')

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
                            <li class="breadcrumb-item active">詳細表示</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0">勤怠記録詳細</h1>
                    <p class="text-muted mb-0">{{ $attendance->attendance_date ? $attendance->attendance_date->format('Y年m月d日 (D)') : '未設定' }} の勤怠記録</p>
                </div>
                <div class="d-flex gap-2">
                    @can('update', $attendance)
                        <a href="{{ route('attendances.edit', $attendance) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> 編集
                        </a>
                    @endcan
                    
                    @can('approve-attendances')
                        @if(($attendance->status ?? '') === 'pending')
                            <button class="btn btn-success" onclick="approveAttendance({{ $attendance->id }})">
                                <i class="fas fa-check"></i> 承認
                            </button>
                            <button class="btn btn-warning" onclick="rejectAttendance({{ $attendance->id }})">
                                <i class="fas fa-times"></i> 差し戻し
                            </button>
                        @endif
                    @endcan
                    
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="printAttendance()">
                                <i class="fas fa-print"></i> 印刷
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportPDF()">
                                <i class="fas fa-file-pdf"></i> PDF出力
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('attendances.create') }}">
                                <i class="fas fa-copy"></i> 複製して新規作成
                            </a></li>
                        </ul>
                    </div>
                    
                    <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> 一覧に戻る
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- 左カラム: 基本情報 -->
                <div class="col-lg-8">
                    <!-- 基本情報カード -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user"></i> 基本情報
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- 警備員情報 -->
                                    <div class="d-flex align-items-center mb-4">
                                        @if(isset($attendance->guard->photo))
                                            <img src="{{ Storage::url($attendance->guard->photo) }}" 
                                                 alt="{{ $attendance->guard->name }}" 
                                                 class="rounded-circle me-3" 
                                                 style="width: 80px; height: 80px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 80px; height: 80px;">
                                                <i class="fas fa-user fa-2x text-white"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h4 class="mb-1">{{ $attendance->guard->name ?? '未設定' }}</h4>
                                            <p class="text-muted mb-1">ID: {{ $attendance->guard->employee_id ?? '未設定' }}</p>
                                            <p class="text-muted mb-0">部署: {{ $attendance->guard->department ?? '未設定' }}</p>
                                        </div>
                                    </div>

                                    <!-- 勤務日時 -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">勤務日</label>
                                        <p class="mb-0">{{ $attendance->attendance_date ? $attendance->attendance_date->format('Y年m月d日 (D)') : '未設定' }}</p>
                                    </div>

                                    <!-- プロジェクト情報 -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">プロジェクト</label>
                                        <p class="mb-0">{{ $attendance->shift->project->name ?? '未設定' }}</p>
                                        @if($attendance->shift->location ?? false)
                                            <small class="text-muted">現場: {{ $attendance->shift->location }}</small>
                                        @endif
                                    </div>

                                    <!-- シフト情報 -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">予定勤務時間</label>
                                        <p class="mb-0">
                                            {{ $attendance->shift->start_time ?? '未設定' }} ～ {{ $attendance->shift->end_time ?? '未設定' }}
                                            @if($attendance->shift->start_time && $attendance->shift->end_time)
                                                <span class="text-muted">
                                                    ({{ \Carbon\Carbon::parse($attendance->shift->end_time)->diffInHours(\Carbon\Carbon::parse($attendance->shift->start_time)) }}時間)
                                                </span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <!-- 出勤情報 -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">出勤打刻</label>
                                        <div class="border rounded p-3 bg-light">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="h5 mb-0">{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '未打刻' }}</span>
                                                <div>
                                                    @if($attendance->clock_in && method_exists($attendance, 'isLate') && $attendance->isLate())
                                                        <span class="badge bg-warning">遅刻</span>
                                                    @elseif($attendance->clock_in)
                                                        <span class="badge bg-success">正常</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            @if($attendance->clock_in_note ?? false)
                                                <small class="text-muted d-block mb-2">
                                                    <i class="fas fa-comment"></i> {{ $attendance->clock_in_note }}
                                                </small>
                                            @endif
                                            
                                            <div class="d-flex gap-2">
                                                @if($attendance->clock_in_photo ?? false)
                                                    <button class="btn btn-sm btn-outline-primary" onclick="showPhoto('{{ Storage::url($attendance->clock_in_photo) }}', '出勤時の写真')">
                                                        <i class="fas fa-camera"></i> 写真
                                                    </button>
                                                @endif
                                                @if($attendance->clock_in_location ?? false)
                                                    <button class="btn btn-sm btn-outline-success" onclick="showLocation('{{ $attendance->clock_in_location }}', '出勤地点')">
                                                        <i class="fas fa-map-marker-alt"></i> 位置
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 退勤情報 -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">退勤打刻</label>
                                        <div class="border rounded p-3 bg-light">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="h5 mb-0">{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '勤務中' }}</span>
                                                <div>
                                                    @if($attendance->clock_out && method_exists($attendance, 'isEarlyLeave') && $attendance->isEarlyLeave())
                                                        <span class="badge bg-warning">早退</span>
                                                    @elseif($attendance->clock_out)
                                                        <span class="badge bg-success">正常</span>
                                                    @else
                                                        <span class="badge bg-primary">勤務中</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            @if($attendance->clock_out_note ?? false)
                                                <small class="text-muted d-block mb-2">
                                                    <i class="fas fa-comment"></i> {{ $attendance->clock_out_note }}
                                                </small>
                                            @endif
                                            
                                            <div class="d-flex gap-2">
                                                @if($attendance->clock_out_photo ?? false)
                                                    <button class="btn btn-sm btn-outline-primary" onclick="showPhoto('{{ Storage::url($attendance->clock_out_photo) }}', '退勤時の写真')">
                                                        <i class="fas fa-camera"></i> 写真
                                                    </button>
                                                @endif
                                                @if($attendance->clock_out_location ?? false)
                                                    <button class="btn btn-sm btn-outline-success" onclick="showLocation('{{ $attendance->clock_out_location }}', '退勤地点')">
                                                        <i class="fas fa-map-marker-alt"></i> 位置
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 勤務時間詳細カード -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-clock"></i> 勤務時間詳細
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                                        <h4 class="mb-1">{{ $attendance->total_work_hours ? number_format($attendance->total_work_hours, 2) : '0.00' }}</h4>
                                        <small class="text-muted">総勤務時間</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-coffee fa-2x text-warning mb-2"></i>
                                        <h4 class="mb-1">{{ $attendance->break_hours ? number_format($attendance->break_hours, 2) : '0.00' }}</h4>
                                        <small class="text-muted">休憩時間</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-business-time fa-2x text-success mb-2"></i>
                                        <h4 class="mb-1">{{ $attendance->actual_work_hours ? number_format($attendance->actual_work_hours, 2) : '0.00' }}</h4>
                                        <small class="text-muted">実働時間</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-clock fa-2x text-danger mb-2"></i>
                                        <h4 class="mb-1">{{ $attendance->overtime_hours ? number_format($attendance->overtime_hours, 2) : '0.00' }}</h4>
                                        <small class="text-muted">残業時間</small>
                                    </div>
                                </div>
                            </div>

                            @if($attendance->notes ?? false)
                                <div class="mt-4">
                                    <label class="form-label fw-bold">備考</label>
                                    <div class="border rounded p-3 bg-light">
                                        {{ $attendance->notes }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 異常・アラート情報 -->
                    @if(method_exists($attendance, 'hasAnomalies') && $attendance->hasAnomalies())
                        <div class="card mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle"></i> 異常・アラート情報
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @if(method_exists($attendance, 'isLate') && $attendance->isLate())
                                        <div class="col-md-6 mb-3">
                                            <div class="alert alert-warning d-flex align-items-center">
                                                <i class="fas fa-clock me-2"></i>
                                                <div>
                                                    <strong>遅刻</strong><br>
                                                    <small>予定時刻より遅れて出勤しています</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if(method_exists($attendance, 'isEarlyLeave') && $attendance->isEarlyLeave())
                                        <div class="col-md-6 mb-3">
                                            <div class="alert alert-warning d-flex align-items-center">
                                                <i class="fas fa-door-open me-2"></i>
                                                <div>
                                                    <strong>早退</strong><br>
                                                    <small>予定時刻より早く退勤しています</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!($attendance->clock_in_location ?? false) || !($attendance->clock_out_location ?? false))
                                        <div class="col-md-6 mb-3">
                                            <div class="alert alert-info d-flex align-items-center">
                                                <i class="fas fa-map-marker-alt me-2"></i>
                                                <div>
                                                    <strong>GPS記録なし</strong><br>
                                                    <small>位置情報が記録されていません</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if(($attendance->total_work_hours ?? 0) > 12)
                                        <div class="col-md-6 mb-3">
                                            <div class="alert alert-danger d-flex align-items-center">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <div>
                                                    <strong>長時間勤務</strong><br>
                                                    <small>12時間を超える勤務です</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- 右カラム: ステータス・履歴 -->
                <div class="col-lg-4">
                    <!-- ステータス情報 -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> ステータス情報
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">承認状態</label>
                                <div>
                                    @switch($attendance->status ?? 'unknown')
                                        @case('pending')
                                            <span class="badge bg-warning fs-6">承認待ち</span>
                                            @break
                                        @case('approved')
                                            <span class="badge bg-success fs-6">承認済み</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge bg-danger fs-6">差し戻し</span>
                                            @break
                                        @case('working')
                                            <span class="badge bg-primary fs-6">勤務中</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary fs-6">{{ $attendance->status ?? '不明' }}</span>
                                    @endswitch
                                </div>
                            </div>

                            @if($attendance->approved_by ?? false)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">承認者</label>
                                    <p class="mb-0">{{ $attendance->approver->name ?? '未設定' }}</p>
                                    <small class="text-muted">{{ $attendance->approved_at ? $attendance->approved_at->format('Y/m/d H:i') : '' }}</small>
                                </div>
                            @endif

                            @if($attendance->approval_note ?? false)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">承認コメント</label>
                                    <div class="border rounded p-2 bg-light">
                                        {{ $attendance->approval_note }}
                                    </div>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label fw-bold">作成日時</label>
                                <p class="mb-0">{{ $attendance->created_at ? $attendance->created_at->format('Y/m/d H:i') : '未設定' }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">最終更新</label>
                                <p class="mb-0">{{ $attendance->updated_at ? $attendance->updated_at->format('Y/m/d H:i') : '未設定' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- 関連シフト情報 -->
                    @if($attendance->shift ?? false)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-alt"></i> 関連シフト情報
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">シフトID</label>
                                    <p class="mb-0">{{ $attendance->shift->id }}</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">シフト種別</label>
                                    <p class="mb-0">{{ $attendance->shift->type ?? '通常' }}</p>
                                </div>

                                @if($attendance->shift->description ?? false)
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">シフト説明</label>
                                        <div class="border rounded p-2 bg-light">
                                            {{ $attendance->shift->description }}
                                        </div>
                                    </div>
                                @endif

                                <div class="d-grid">
                                    <a href="{{ route('shifts.show', $attendance->shift) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i> シフト詳細を見る
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- アクションボタン -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cogs"></i> アクション
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @can('update', $attendance)
                                    <a href="{{ route('attendances.edit', $attendance) }}" class="btn btn-primary">
                                        <i class="fas fa-edit"></i> 編集
                                    </a>
                                @endcan

                                @if(!$attendance->clock_out && ($attendance->status ?? '') === 'working')
                                    <button class="btn btn-success" onclick="quickClockOut({{ $attendance->id }})">
                                        <i class="fas fa-sign-out-alt"></i> 退勤打刻
                                    </button>
                                @endif

                                <button class="btn btn-outline-info" onclick="copyAttendance({{ $attendance->id }})">
                                    <i class="fas fa-copy"></i> 複製作成
                                </button>

                                <button class="btn btn-outline-secondary" onclick="exportPDF()">
                                    <i class="fas fa-file-pdf"></i> PDF出力
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 写真表示モーダル -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalTitle">写真表示</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="photoModalImage" src="" alt="写真" class="img-fluid rounded">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <a id="photoDownloadBtn" href="" class="btn btn-primary" download>
                    <i class="fas fa-download"></i> ダウンロード
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 位置情報表示モーダル -->
<div class="modal fade" id="locationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="locationModalTitle">位置情報</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="mapContainer" style="height: 400px; border-radius: 8px;"></div>
                <div class="mt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>緯度:</strong> <span id="latitude">-</span>
                        </div>
                        <div class="col-md-6">
                            <strong>経度:</strong> <span id="longitude">-</span>
                        </div>
                        <div class="col-md-6">
                            <strong>精度:</strong> <span id="accuracy">-</span>m
                        </div>
                        <div class="col-md-6">
                            <strong>記録時刻:</strong> <span id="recordTime">-</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" onclick="openInGoogleMaps()">
                    <i class="fas fa-external-link-alt"></i> Google Mapsで開く
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 承認・差し戻しモーダル -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalTitle">勤怠記録の承認・差し戻し</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="approvalForm">
                    <input type="hidden" id="approval_attendance_id" name="attendance_id">
                    <input type="hidden" id="approval_action" name="action">
                    
                    <div class="mb-3">
                        <label for="approval_note" class="form-label">コメント</label>
                        <textarea class="form-control" id="approval_note" name="note" rows="3" 
                                  placeholder="承認・差し戻しの理由やコメントを入力してください"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="submitApproval()" id="approvalSubmitBtn">
                    実行
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card-title {
    font-weight: 600;
}

.badge.fs-6 {
    font-size: 1rem !important;
    padding: 0.5rem 1rem;
}

.border.rounded.p-3 {
    background-color: #f8f9fa;
}

.alert {
    border-left: 4px solid;
    border-left-color: var(--bs-warning);
}

.alert-warning {
    border-left-color: var(--bs-warning);
}

.alert-info {
    border-left-color: var(--bs-info);
}

.alert-danger {
    border-left-color: var(--bs-danger);
}

#mapContainer {
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.text-center.p-3.border.rounded:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s;
}

@media (max-width: 768px) {
    .d-flex.gap-2 {
        flex-direction: column;
    }
    
    .btn-group {
        flex-direction: column;
    }
}
</style>
@endpush

@push('scripts')
<script>
let currentLatLng = null;

// 写真表示
function showPhoto(photoUrl, title) {
    $('#photoModalTitle').text(title);
    $('#photoModalImage').attr('src', photoUrl);
    $('#photoDownloadBtn').attr('href', photoUrl);
    $('#photoModal').modal('show');
}

// 位置情報表示
function showLocation(locationData, title) {
    try {
        const location = typeof locationData === 'string' ? JSON.parse(locationData) : locationData;
        
        $('#locationModalTitle').text(title);
        $('#latitude').text(location.latitude || '-');
        $('#longitude').text(location.longitude || '-');
        $('#accuracy').text(location.accuracy || '-');
        $('#recordTime').text(location.recorded_at || '-');
        
        currentLatLng = {
            lat: parseFloat(location.latitude),
            lng: parseFloat(location.longitude)
        };
        
        $('#locationModal').modal('show');
        
        // モーダルが表示されてから地図を初期化
        $('#locationModal').on('shown.bs.modal', function() {
            initializeMap();
        });
        
    } catch (error) {
        console.error('位置情報の解析に失敗しました:', error);
        alert('位置情報の表示に失敗しました。');
    }
}

// 地図初期化
function initializeMap() {
    if (!currentLatLng) return;
    
    // Google Maps APIが利用可能な場合の地図表示
    if (typeof google !== 'undefined' && google.maps) {
        const map = new google.maps.Map(document.getElementById('mapContainer'), {
            zoom: 15,
            center: currentLatLng
        });
        
        new google.maps.Marker({
            position: currentLatLng,
            map: map,
            title: '打刻位置'
        });
    } else {
        // Google Maps APIが利用できない場合のフォールバック
        $('#mapContainer').html(`
            <div class="text-center">
                <i class="fas fa-map-marked-alt fa-3x mb-3"></i>
                <p>地図機能を利用するにはGoogle Maps APIが必要です。</p>
                <button class="btn btn-primary" onclick="openInGoogleMaps()">
                    Google Mapsで開く
                </button>
            </div>
        `);
    }
}

// Google Mapsで開く
function openInGoogleMaps() {
    if (currentLatLng) {
        const url = `https://www.google.com/maps?q=${currentLatLng.lat},${currentLatLng.lng}`;
        window.open(url, '_blank');
    }
}

// 承認処理
function approveAttendance(attendanceId) {
    showApprovalModal(attendanceId, 'approve', '承認');
}

// 差し戻し処理
function rejectAttendance(attendanceId) {
    showApprovalModal(attendanceId, 'reject', '差し戻し');
}

// 承認・差し戻しモーダル表示
function showApprovalModal(attendanceId, action, title) {
    $('#approval_attendance_id').val(attendanceId);
    $('#approval_action').val(action);
    $('#approvalModalTitle').text(`勤怠記録の${title}`);
    $('#approvalSubmitBtn').text(title);
    $('#approvalModal').modal('show');
}

// 承認・差し戻し実行
function submitApproval() {
    const attendanceId = $('#approval_attendance_id').val();
    const action = $('#approval_action').val();
    const note = $('#approval_note').val();

    $.ajax({
        url: `{{ url('attendances') }}/${attendanceId}/${action}`,
        method: 'POST',
        data: {
            note: note,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#approvalModal').modal('hide');
            showSuccessMessage(response.message || '処理が完了しました。');
            setTimeout(() => location.reload(), 1500);
        },
        error: function(xhr) {
            showErrorMessage(xhr.responseJSON?.message || '処理に失敗しました。');
        }
    });
}

// 簡易退勤打刻
function quickClockOut(attendanceId) {
    if (confirm('退勤打刻を行いますか？')) {
        // GPS位置情報を取得
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    executeClockOut(attendanceId, {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    });
                },
                function(error) {
                    if (confirm('GPS位置情報の取得に失敗しました。位置情報なしで退勤打刻しますか？')) {
                        executeClockOut(attendanceId, null);
                    }
                }
            );
        } else {
            executeClockOut(attendanceId, null);
        }
    }
}

// 退勤打刻実行
function executeClockOut(attendanceId, location) {
    const data = {
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    if (location) {
        data.latitude = location.latitude;
        data.longitude = location.longitude;
        data.accuracy = location.accuracy;
    }

    $.ajax({
        url: `{{ url('attendances') }}/${attendanceId}/clock-out`,
        method: 'POST',
        data: data,
        success: function(response) {
            showSuccessMessage(response.message || '退勤打刻が完了しました。');
            setTimeout(() => location.reload(), 1500);
        },
        error: function(xhr) {
            showErrorMessage(xhr.responseJSON?.message || '退勤打刻に失敗しました。');
        }
    });
}

// 勤怠記録複製
function copyAttendance(attendanceId) {
    if (confirm('この勤怠記録をベースに新しい記録を作成しますか？')) {
        window.location.href = `{{ route('attendances.create') }}?copy=${attendanceId}`;
    }
}

// PDF出力
function exportPDF() {
    const url = `{{ route('attendances.pdf', $attendance) }}`;
    window.open(url, '_blank');
}

// 印刷
function printAttendance() {
    window.print();
}

// 成功メッセージ表示
function showSuccessMessage(message) {
    if (typeof toastr !== 'undefined') {
        toastr.success(message);
    } else {
        alert(message);
    }
}

// エラーメッセージ表示
function showErrorMessage(message) {
    if (typeof toastr !== 'undefined') {
        toastr.error(message);
    } else {
        alert(message);
    }
}

// 印刷スタイル
@media print {
    .btn, .dropdown, .modal {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    .badge {
        border: 1px solid #000 !important;
        color: #000 !important;
    }
}
</script>
@endpush
@endsection
