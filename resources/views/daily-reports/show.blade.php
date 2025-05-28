@extends('layouts.app')

@section('title', '日報詳細')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-file-alt me-2"></i>日報詳細
                    </h1>
                    <p class="mb-0 text-muted">{{ \Carbon\Carbon::parse($report->report_date)->format('Y年m月d日') }}の日報</p>
                </div>
                <div>
                    <a href="{{ route('daily-reports.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>一覧に戻る
                    </a>
                    @if($report->status !== 'approved')
                        <a href="{{ route('daily-reports.edit', $report) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>編集
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 左側：日報詳細 -->
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
                            $priorityLabels = [
                                'low' => '低',
                                'normal' => '通常',
                                'high' => '高',
                                'urgent' => '緊急'
                            ];
                            $priorityClasses = [
                                'low' => 'success',
                                'normal' => 'primary',
                                'high' => 'warning',
                                'urgent' => 'danger'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusClasses[$report->status] ?? 'secondary' }} me-2">
                            {{ $statusLabels[$report->status] ?? $report->status }}
                        </span>
                        <span class="badge bg-{{ $priorityClasses[$report->priority] ?? 'secondary' }}">
                            重要度：{{ $priorityLabels[$report->priority] ?? $report->priority }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">警備員</label>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    @if($report->guard && $report->guard->profile_photo_path)
                                        <img src="{{ Storage::url($report->guard->profile_photo_path) }}" 
                                             alt="{{ $report->guard->name }}" 
                                             class="rounded-circle" width="40" height="40">
                                    @else
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $report->guard->name ?? '未設定' }}</div>
                                    <small class="text-muted">{{ $report->guard->employee_id ?? '' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">プロジェクト</label>
                            <div>
                                <div class="fw-bold">{{ $report->project->name ?? '未設定' }}</div>
                                <small class="text-muted">{{ $report->project->location ?? '' }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">日報日</label>
                            <div>{{ \Carbon\Carbon::parse($report->report_date)->format('Y年m月d日（D）') }}</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">種別</label>
                            <div>
                                @php
                                    $typeLabels = [
                                        'daily' => '日常警備',
                                        'incident' => '事故・異常',
                                        'maintenance' => '設備点検',
                                        'security_check' => '警備点検',
                                        'patrol' => '巡回報告'
                                    ];
                                    $typeClasses = [
                                        'daily' => 'primary',
                                        'incident' => 'danger',
                                        'maintenance' => 'warning',
                                        'security_check' => 'info',
                                        'patrol' => 'secondary'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $typeClasses[$report->report_type] ?? 'secondary' }}">
                                    {{ $typeLabels[$report->report_type] ?? $report->report_type }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">天候</label>
                            <div>
                                <i class="fas fa-cloud me-1"></i>{{ $report->weather_condition }}
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">来訪者数</label>
                            <div>
                                <i class="fas fa-users me-1"></i>{{ $report->visitor_count ?? 0 }}人
                            </div>
                        </div>
                    </div>

                    @if($report->shift)
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">関連シフト</label>
                                <div class="border rounded p-2 bg-light">
                                    <a href="{{ route('shifts.show', $report->shift) }}" class="text-decoration-none">
                                        {{ \Carbon\Carbon::parse($report->shift->start_time)->format('H:i') }}〜{{ \Carbon\Carbon::parse($report->shift->end_time)->format('H:i') }}
                                        （{{ $report->shift->project->name ?? '不明' }}）
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 特記事項カード -->
            @if($report->has_incident || $report->has_equipment_issue || $report->has_safety_concern)
                <div class="card shadow mb-4 border-left-warning">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>特記事項
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($report->has_incident)
                                <div class="col-md-4 mb-2">
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>事故・異常発生
                                    </span>
                                </div>
                            @endif
                            @if($report->has_equipment_issue)
                                <div class="col-md-4 mb-2">
                                    <span class="badge bg-warning">
                                        <i class="fas fa-tools me-1"></i>設備不具合
                                    </span>
                                </div>
                            @endif
                            @if($report->has_safety_concern)
                                <div class="col-md-4 mb-2">
                                    <span class="badge bg-info">
                                        <i class="fas fa-shield-alt me-1"></i>安全上の懸念
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- 日報内容カード -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-alt me-2"></i>日報内容
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold">概要</label>
                        <div class="border rounded p-3 bg-light">
                            {{ $report->summary }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">詳細報告</label>
                        <div class="border rounded p-3 bg-light" style="min-height: 150px;">
                            {!! nl2br(e($report->detailed_report)) !!}
                        </div>
                    </div>

                    @if($report->patrol_route)
                        <div class="mb-4">
                            <label class="form-label fw-bold">巡回ルート</label>
                            <div class="border rounded p-3 bg-light">
                                {!! nl2br(e($report->patrol_route)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 事故・異常詳細カード -->
            @if($report->has_incident && $report->incident_details)
                <div class="card shadow mb-4 border-left-danger">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>事故・異常詳細
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            {!! nl2br(e($report->incident_details)) !!}
                        </div>
                    </div>
                </div>
            @endif

            <!-- 詳細情報カード -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs me-2"></i>詳細情報
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($report->equipment_status)
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">設備状況</label>
                                <div class="border rounded p-3 bg-light">
                                    {!! nl2br(e($report->equipment_status)) !!}
                                </div>
                            </div>
                        @endif

                        @if($report->maintenance_notes)
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">保守・メンテナンス</label>
                                <div class="border rounded p-3 bg-light">
                                    {!! nl2br(e($report->maintenance_notes)) !!}
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        @if($report->safety_observations)
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">安全確認・注意事項</label>
                                <div class="border rounded p-3 bg-light">
                                    {!! nl2br(e($report->safety_observations)) !!}
                                </div>
                            </div>
                        @endif

                        @if($report->recommendations)
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">改善提案・推奨事項</label>
                                <div class="border rounded p-3 bg-light">
                                    {!! nl2br(e($report->recommendations)) !!}
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($report->next_shift_notes)
                        <div class="mb-4">
                            <label class="form-label fw-bold">次番への引き継ぎ事項</label>
                            <div class="border rounded p-3 bg-warning-subtle">
                                <i class="fas fa-arrow-right me-2 text-warning"></i>
                                {!! nl2br(e($report->next_shift_notes)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 承認情報カード -->
            @if($report->approved_at || $report->status === 'submitted')
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-check-circle me-2"></i>承認情報
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($report->approved_at)
                            <div class="alert alert-success">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">承認済み</h6>
                                        <p class="mb-1">
                                            <strong>承認者：</strong>{{ $report->approver->name ?? '不明' }}<br>
                                            <strong>承認日時：</strong>{{ \Carbon\Carbon::parse($report->approved_at)->format('Y年m月d日 H:i') }}
                                        </p>
                                        @if($report->approval_memo)
                                            <p class="mb-0">
                                                <strong>承認コメント：</strong>{{ $report->approval_memo }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-clock fa-2x text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">承認待ち</h6>
                                        <p class="mb-0">この日報は承認待ちの状態です。</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- 右側：アクション・分析・関連情報 -->
        <div class="col-lg-4">
            <!-- アクションカード -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools me-2"></i>アクション
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($report->status !== 'approved')
                            <a href="{{ route('daily-reports.edit', $report) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>編集
                            </a>
                        @endif

                        @if(in_array(auth()->user()->role, ['admin', 'manager']) && $report->status !== 'approved')
                            <button type="button" class="btn btn-success" onclick="approveReport()">
                                <i class="fas fa-check me-1"></i>承認
                            </button>
                        @endif

                        @if(in_array($report->status, ['draft']) && in_array(auth()->user()->role, ['admin', 'manager', 'guard']))
                            <button type="button" class="btn btn-warning" onclick="submitReport()">
                                <i class="fas fa-paper-plane me-1"></i>提出
                            </button>
                        @endif

                        <button type="button" class="btn btn-outline-info" onclick="printReport()">
                            <i class="fas fa-print me-1"></i>印刷
                        </button>

                        <button type="button" class="btn btn-outline-success" onclick="exportPDF()">
                            <i class="fas fa-file-pdf me-1"></i>PDF出力
                        </button>

                        <button type="button" class="btn btn-outline-danger" onclick="deleteReport()">
                            <i class="fas fa-trash me-1"></i>削除
                        </button>
                    </div>
                </div>
            </div>

            <!-- 日報分析カード -->
            @if(isset($analysis))
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-chart-line me-2"></i>分析結果
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">完全性スコア</label>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $analysis['completeness_score'] }}%"
                                     aria-valuenow="{{ $analysis['completeness_score'] }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    {{ $analysis['completeness_score'] }}点
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">詳細レベル</label>
                            <div>
                                @php
                                    $detailLabels = [
                                        'excellent' => '優秀',
                                        'good' => '良好',
                                        'fair' => '普通',
                                        'needs_improvement' => '要改善'
                                    ];
                                    $detailClasses = [
                                        'excellent' => 'success',
                                        'good' => 'primary',
                                        'fair' => 'warning',
                                        'needs_improvement' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $detailClasses[$analysis['detail_level']] ?? 'secondary' }}">
                                    {{ $detailLabels[$analysis['detail_level']] ?? $analysis['detail_level'] }}
                                </span>
                            </div>
                        </div>

                        @if(!empty($analysis['risk_indicators']))
                            <div class="mb-3">
                                <label class="form-label fw-bold">リスク指標</label>
                                @foreach($analysis['risk_indicators'] as $risk)
                                    <div class="mb-1">
                                        <span class="badge bg-{{ $risk['level'] === 'high' ? 'danger' : ($risk['level'] === 'medium' ? 'warning' : 'info') }}">
                                            {{ $risk['description'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($analysis['follow_up_needed'])
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                フォローアップが必要です
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 関連日報カード -->
            @if(isset($relatedReports) && $relatedReports->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-secondary">
                            <i class="fas fa-link me-2"></i>関連日報
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @foreach($relatedReports as $relatedReport)
                                <a href="{{ route('daily-reports.show', $relatedReport) }}" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ \Carbon\Carbon::parse($relatedReport->report_date)->format('m/d') }}</h6>
                                        <small>{{ $relatedReport->guard->name ?? '' }}</small>
                                    </div>
                                    <p class="mb-1 text-truncate">{{ $relatedReport->summary }}</p>
                                    <small class="text-muted">
                                        @if($relatedReport->has_incident)
                                            <i class="fas fa-exclamation-triangle text-danger me-1"></i>
                                        @endif
                                        {{ $relatedReport->created_at->format('H:i') }}
                                    </small>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- 作成・更新情報カード -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="fas fa-info me-2"></i>作成・更新情報
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">作成者</label>
                        <div>{{ $report->creator->name ?? '不明' }}</div>
                        <small class="text-muted">{{ $report->created_at->format('Y年m月d日 H:i') }}</small>
                    </div>

                    @if($report->updated_at != $report->created_at)
                        <div class="mb-3">
                            <label class="form-label fw-bold">最終更新</label>
                            <div>{{ $report->updated_at->format('Y年m月d日 H:i') }}</div>
                        </div>
                    @endif

                    @if($report->submitted_at)
                        <div class="mb-3">
                            <label class="form-label fw-bold">提出日時</label>
                            <div>{{ \Carbon\Carbon::parse($report->submitted_at)->format('Y年m月d日 H:i') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 承認確認モーダル -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">日報承認確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>この日報を承認してもよろしいですか？</p>
                <div class="mb-3">
                    <label for="approval_memo" class="form-label">承認コメント（任意）</label>
                    <textarea class="form-control" id="approval_memo" rows="3" 
                              placeholder="承認に関するコメントがあれば入力してください"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-success" id="confirmApproveBtn">承認</button>
            </div>
        </div>
    </div>
</div>

<!-- 提出確認モーダル -->
<div class="modal fade" id="submitModal" tabindex="-1" aria-labelledby="submitModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submitModalLabel">日報提出確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>この日報を提出してもよろしいですか？</p>
                <p class="text-warning"><strong>※ 提出後は編集できなくなります。</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-warning" id="confirmSubmitBtn">提出</button>
            </div>
        </div>
    </div>
</div>

<!-- 削除確認モーダル -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">日報削除確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>この日報を削除してもよろしいですか？</p>
                <p class="text-danger"><strong>※ この操作は取り消せません。</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">削除</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// 日報承認
function approveReport() {
    $('#approveModal').modal('show');
    
    $('#confirmApproveBtn').off('click').on('click', function() {
        const memo = $('#approval_memo').val();
        
        $.ajax({
            url: `{{ route('daily-reports.approve', $report) }}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { approval_memo: memo },
            success: function(response) {
                $('#approveModal').modal('hide');
                if (response.success) {
                    showAlert('success', '日報を承認しました。');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('error', response.message || '承認に失敗しました。');
                }
            },
            error: function(xhr) {
                $('#approveModal').modal('hide');
                showAlert('error', 'エラーが発生しました。');
            }
        });
    });
}

// 日報提出
function submitReport() {
    $('#submitModal').modal('show');
    
    $('#confirmSubmitBtn').off('click').on('click', function() {
        $.ajax({
            url: `{{ route('daily-reports.submit', $report) }}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#submitModal').modal('hide');
                if (response.success) {
                    showAlert('success', '日報を提出しました。');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('error', response.message || '提出に失敗しました。');
                }
            },
            error: function(xhr) {
                $('#submitModal').modal('hide');
                showAlert('error', 'エラーが発生しました。');
            }
        });
    });
}

// 日報削除
function deleteReport() {
    $('#deleteModal').modal('show');
    
    $('#confirmDeleteBtn').off('click').on('click', function() {
        $.ajax({
            url: `{{ route('daily-reports.destroy', $report) }}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#deleteModal').modal('hide');
                if (response.success) {
                    showAlert('success', '日報を削除しました。');
                    setTimeout(() => window.location.href = '{{ route('daily-reports.index') }}', 1000);
                } else {
                    showAlert('error', response.message || '削除に失敗しました。');
                }
            },
            error: function(xhr) {
                $('#deleteModal').modal('hide');
                showAlert('error', 'エラーが発生しました。');
            }
        });
    });
}

// 印刷
function printReport() {
    window.print();
}

// PDF出力
function exportPDF() {
    const url = `{{ route('daily-reports.show', $report) }}?export=pdf`;
    window.open(url, '_blank');
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
.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.bg-warning-subtle {
    background-color: #fff3cd !important;
}

.progress {
    height: 20px;
}

.list-group-item {
    border: none;
    border-bottom: 1px solid #e3e6f0;
}

.list-group-item:last-child {
    border-bottom: none;
}

.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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
    
    .d-grid .btn {
        font-size: 0.875rem;
        padding: 0.5rem;
    }
}

@media print {
    .btn, .card-header, .modal, .alert {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }
    
    .col-lg-4 {
        display: none !important;
    }
    
    .col-lg-8 {
        width: 100% !important;
        max-width: 100% !important;
    }
    
    body {
        font-size: 12px;
    }
    
    .badge {
        border: 1px solid #000;
        color: #000 !important;
        background-color: transparent !important;
    }
}
</style>
@endpush
