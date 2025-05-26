@extends('layouts.app')

@section('title', '警備員詳細 - ' . $guard->name)

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('guards.index') }}">警備員管理</a></li>
                    <li class="breadcrumb-item active">{{ $guard->name }}</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-person-badge me-2"></i>
                        {{ $guard->name }} 詳細
                    </h2>
                    <p class="text-muted mb-0">社員ID: {{ $guard->employee_id }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('guards.edit', $guard) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil me-1"></i>
                        編集
                    </a>
                    <button class="btn btn-outline-primary" onclick="printGuardInfo()">
                        <i class="bi bi-printer me-1"></i>
                        印刷
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-1"></i>
                            操作
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('guards.shifts', $guard) }}">
                                <i class="bi bi-calendar3 me-2"></i>シフト履歴
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('guards.attendances', $guard) }}">
                                <i class="bi bi-clock me-2"></i>勤怠履歴
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('guards.performance', $guard) }}">
                                <i class="bi bi-graph-up me-2"></i>パフォーマンス
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('guards.export.individual', $guard) }}">
                                <i class="bi bi-download me-2"></i>情報エクスポート
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- メイン情報 -->
        <div class="col-lg-8 col-md-12">
            <!-- 基本情報 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person me-2"></i>
                        基本情報
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">氏名</label>
                            <p class="mb-0">{{ $guard->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">氏名（カナ）</label>
                            <p class="mb-0">{{ $guard->name_kana }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">生年月日</label>
                            <p class="mb-0">{{ $guard->birth_date ? $guard->birth_date->format('Y年n月j日') : '未設定' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">年齢</label>
                            <p class="mb-0">{{ $guard->age ?? '不明' }}歳</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">性別</label>
                            <p class="mb-0">{{ $guard->getGenderText() }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">所属会社</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $guard->company === 'touo_security' ? 'primary' : ($guard->company === 'nikkei_hd' ? 'success' : 'warning') }} fs-6">
                                    {{ $guard->getCompanyName() }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ステータス</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $guard->getStatusColor() }} fs-6">
                                    {{ $guard->getStatusText() }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 連絡先情報 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-telephone me-2"></i>
                        連絡先情報
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">電話番号</label>
                            <p class="mb-0">
                                @if($guard->phone)
                                    <a href="tel:{{ $guard->phone }}" class="text-decoration-none">
                                        <i class="bi bi-telephone me-1"></i>{{ $guard->phone }}
                                    </a>
                                @else
                                    <span class="text-muted">未設定</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">メールアドレス</label>
                            <p class="mb-0">
                                @if($guard->email)
                                    <a href="mailto:{{ $guard->email }}" class="text-decoration-none">
                                        <i class="bi bi-envelope me-1"></i>{{ $guard->email }}
                                    </a>
                                @else
                                    <span class="text-muted">未設定</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">緊急連絡先</label>
                            <p class="mb-0">
                                @if($guard->emergency_contact)
                                    <a href="tel:{{ $guard->emergency_contact }}" class="text-decoration-none">
                                        <i class="bi bi-telephone-fill me-1 text-danger"></i>{{ $guard->emergency_contact }}
                                    </a>
                                @else
                                    <span class="text-muted">未設定</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">緊急連絡先（続柄）</label>
                            <p class="mb-0">{{ $guard->emergency_contact_relation ?? '未設定' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 住所情報 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-geo-alt me-2"></i>
                        住所情報
                    </h5>
                </div>
                <div class="card-body">
                    @if($guard->postal_code || $guard->prefecture || $guard->city || $guard->address)
                        <p class="mb-0">
                            {{ $guard->postal_code ? '〒' . $guard->postal_code . ' ' : '' }}
                            {{ $guard->prefecture }}{{ $guard->city }}{{ $guard->address }}
                        </p>
                    @else
                        <p class="text-muted mb-0">住所が登録されていません</p>
                    @endif
                </div>
            </div>
            
            <!-- 雇用情報 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-briefcase me-2"></i>
                        雇用情報
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">入社日</label>
                            <p class="mb-0">{{ $guard->hire_date ? $guard->hire_date->format('Y年n月j日') : '未設定' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">勤続年数</label>
                            <p class="mb-0">{{ $guard->getWorkingYears() }}年</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">雇用形態</label>
                            <p class="mb-0">{{ $guard->getEmploymentTypeText() }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">時給</label>
                            <p class="mb-0">
                                <span class="fs-5 fw-bold text-success">¥{{ number_format($guard->hourly_rate ?? 0) }}</span>
                                <small class="text-muted">/時</small>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">警備業務経験年数</label>
                            <p class="mb-0">{{ $guard->experience_years ?? 0 }}年</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">前職</label>
                            <p class="mb-0">{{ $guard->previous_job ?? '未設定' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 保有資格・スキル -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-award me-2"></i>
                        保有資格・スキル
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">保有資格</label>
                            <div>
                                @if($guard->qualifications && count($guard->qualifications) > 0)
                                    @foreach($guard->qualifications as $qualification)
                                        <span class="badge bg-info me-1 mb-1">{{ $guard->getQualificationText($qualification) }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">資格なし</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">スキル・特技</label>
                            <div>
                                @if($guard->skills && count($guard->skills) > 0)
                                    @foreach($guard->skills as $skill)
                                        <span class="badge bg-success me-1 mb-1">{{ $guard->getSkillText($skill) }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">スキルなし</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 備考 -->
            @if($guard->notes)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-chat-text me-2"></i>
                        備考
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0 white-space-pre-line">{{ $guard->notes }}</p>
                </div>
            </div>
            @endif
        </div>
        
        <!-- サイドバー -->
        <div class="col-lg-4 col-md-12">
            <!-- プロフィール写真 -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    @if($guard->profile_photo)
                        <img src="{{ Storage::url($guard->profile_photo) }}" 
                             class="img-fluid rounded-circle mb-3" 
                             style="width: 200px; height: 200px; object-fit: cover;" 
                             alt="{{ $guard->name }}のプロフィール写真">
                    @else
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 200px; height: 200px;">
                            <span class="text-white" style="font-size: 4rem; font-weight: bold;">
                                {{ mb_substr($guard->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                    <h4 class="mb-1">{{ $guard->name }}</h4>
                    <p class="text-muted mb-3">{{ $guard->name_kana }}</p>
                    <div class="d-flex justify-content-center gap-2">
                        @if($guard->phone)
                            <a href="tel:{{ $guard->phone }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-telephone"></i>
                            </a>
                        @endif
                        @if($guard->email)
                            <a href="mailto:{{ $guard->email }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-envelope"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- 勤務統計 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        勤務統計（今月）
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="text-primary fw-bold fs-4" id="workingDays">{{ $workingStats['days'] ?? 0 }}</div>
                            <small class="text-muted">勤務日数</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-success fw-bold fs-4" id="workingHours">{{ $workingStats['hours'] ?? 0 }}</div>
                            <small class="text-muted">勤務時間</small>
                        </div>
                        <div class="col-6">
                            <div class="text-warning fw-bold fs-4" id="attendanceRate">{{ $workingStats['attendance_rate'] ?? 0 }}%</div>
                            <small class="text-muted">出席率</small>
                        </div>
                        <div class="col-6">
                            <div class="text-info fw-bold fs-4" id="earningsThisMonth">¥{{ number_format($workingStats['earnings'] ?? 0) }}</div>
                            <small class="text-muted">今月収入</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- パフォーマンス評価 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-star me-2"></i>
                        パフォーマンス評価
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>時間厳守</small>
                            <small>{{ $performance['punctuality'] ?? 85 }}%</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: {{ $performance['punctuality'] ?? 85 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>信頼性</small>
                            <small>{{ $performance['reliability'] ?? 90 }}%</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-primary" style="width: {{ $performance['reliability'] ?? 90 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>業務品質</small>
                            <small>{{ $performance['quality'] ?? 88 }}%</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: {{ $performance['quality'] ?? 88 }}%"></div>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-warning fw-bold fs-5">{{ $performance['overall'] ?? 88 }}%</div>
                        <small class="text-muted">総合評価</small>
                    </div>
                </div>
            </div>
            
            <!-- 今週のシフト -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-week me-2"></i>
                        今週のシフト
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($weeklyShifts) && count($weeklyShifts) > 0)
                        @foreach($weeklyShifts as $shift)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                            <div>
                                <div class="fw-bold">{{ $shift->start_date->format('m/d') }}</div>
                                <small class="text-muted">{{ $shift->start_time }}-{{ $shift->end_time }}</small>
                            </div>
                            <div class="text-end">
                                <div class="small">{{ $shift->project->name ?? '未設定' }}</div>
                                <span class="badge bg-{{ $shift->getStatusColor() }}">{{ $shift->getStatusText() }}</span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center mb-0">今週のシフトはありません</p>
                    @endif
                </div>
            </div>
            
            <!-- 最近のアクティビティ -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        最近のアクティビティ
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($recentActivities) && count($recentActivities) > 0)
                        @foreach($recentActivities as $activity)
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-{{ $activity['type'] === 'attendance' ? 'success' : ($activity['type'] === 'shift' ? 'primary' : 'info') }} rounded-circle p-2 me-3">
                                <i class="bi bi-{{ $activity['type'] === 'attendance' ? 'clock' : ($activity['type'] === 'shift' ? 'calendar' : 'file-text') }} text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small fw-bold">{{ $activity['title'] }}</div>
                                <div class="small text-muted">{{ $activity['description'] }}</div>
                                <div class="small text-muted">{{ $activity['date'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center mb-0">最近のアクティビティはありません</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .white-space-pre-line {
        white-space: pre-line;
    }
    
    .progress {
        height: 8px;
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    @media print {
        .btn, .dropdown, .breadcrumb {
            display: none !important;
        }
        
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
        
        .badge {
            border: 1px solid #ddd !important;
        }
    }
    
    @media (max-width: 768px) {
        .col-md-6, .col-md-4 {
            margin-bottom: 1rem;
        }
        
        .img-fluid {
            width: 150px !important;
            height: 150px !important;
        }
        
        .bg-primary {
            width: 150px !important;
            height: 150px !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // 統計情報の更新
        updateWorkingStats();
        
        // パフォーマンス評価の更新
        updatePerformanceStats();
        
        // 今週のシフトの更新
        updateWeeklyShifts();
        
        // 最近のアクティビティの更新
        updateRecentActivities();
    });
    
    // 勤務統計更新
    function updateWorkingStats() {
        $.get(`{{ route('guards.stats.working', $guard) }}`)
            .done(function(data) {
                $('#workingDays').text(data.days || 0);
                $('#workingHours').text(data.hours || 0);
                $('#attendanceRate').text((data.attendance_rate || 0) + '%');
                $('#earningsThisMonth').text('¥' + (data.earnings || 0).toLocaleString());
            })
            .fail(function() {
                console.error('勤務統計の取得に失敗しました');
            });
    }
    
    // パフォーマンス評価更新
    function updatePerformanceStats() {
        $.get(`{{ route('guards.stats.performance', $guard) }}`)
            .done(function(data) {
                $('.progress-bar').each(function(index) {
                    const metrics = ['punctuality', 'reliability', 'quality'];
                    const value = data[metrics[index]] || 0;
                    $(this).css('width', value + '%');
                    $(this).siblings('.d-flex').find('small:last').text(value + '%');
                });
                
                $('.text-warning.fw-bold.fs-5').text((data.overall || 0) + '%');
            })
            .fail(function() {
                console.error('パフォーマンス評価の取得に失敗しました');
            });
    }
    
    // 今週のシフト更新
    function updateWeeklyShifts() {
        // 実際のAPIエンドポイントが実装されるまでは、サンプルデータを使用
        console.log('今週のシフトを更新中...');
    }
    
    // 最近のアクティビティ更新
    function updateRecentActivities() {
        // 実際のAPIエンドポイントが実装されるまでは、サンプルデータを使用
        console.log('最近のアクティビティを更新中...');
    }
    
    // 印刷機能
    function printGuardInfo() {
        window.print();
    }
</script>
@endpush
@endsection