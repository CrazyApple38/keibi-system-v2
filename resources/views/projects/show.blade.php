@extends('layouts.app')

@section('title', '案件詳細')

@section('content')
<div class="container-fluid">
    <!-- パンくずリスト -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">案件一覧</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $project->name ?? '渋谷オフィスビル警備' }}</li>
        </ol>
    </nav>
    
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center">
                    <div class="project-icon me-3">
                        <i class="bi bi-briefcase fs-2 text-primary"></i>
                    </div>
                    <div>
                        <h2 class="mb-1">{{ $project->name ?? '渋谷オフィスビル警備' }}</h2>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge {{ getStatusClass($project->status ?? 'active') }} fs-6">
                                {{ getStatusText($project->status ?? 'active') }}
                            </span>
                            <span class="badge {{ getPriorityClass($project->priority ?? 'high') }} fs-6">
                                {{ getPriorityText($project->priority ?? 'high') }}
                            </span>
                            <span class="badge bg-info fs-6">
                                {{ getTypeText($project->type ?? 'office_security') }}
                            </span>
                        </div>
                        <p class="text-muted mb-0">
                            <i class="bi bi-building me-1"></i>
                            {{ $project->customer_name ?? 'ABC商事株式会社' }}
                            <span class="mx-2">•</span>
                            <i class="bi bi-geo-alt me-1"></i>
                            {{ $project->location ?? '東京都渋谷区' }}
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('projects.edit', $project->id ?? 1) }}" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i>
                        編集
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="duplicateProject()">
                                <i class="bi bi-files me-2"></i>複製
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportProject()">
                                <i class="bi bi-download me-2"></i>エクスポート
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="printProject()">
                                <i class="bi bi-printer me-2"></i>印刷
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="updateStatus('completed')">
                                <i class="bi bi-check-circle me-2"></i>完了にする
                            </a></li>
                            <li><a class="dropdown-item text-warning" href="#" onclick="updateStatus('on_hold')">
                                <i class="bi bi-pause-circle me-2"></i>保留にする
                            </a></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus('cancelled')">
                                <i class="bi bi-x-circle me-2"></i>キャンセル
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 進捗・統計サマリー -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">進捗率</h6>
                            <h3 class="mb-0 text-primary">{{ $project->progress ?? 75 }}%</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-bar-chart fs-4 text-primary"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar" style="width: {{ $project->progress ?? 75 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">アサイン済み警備員</h6>
                            <h3 class="mb-0 text-success">3/{{ $project->required_guards ?? 3 }}名</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-person-badge fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">総勤務時間</h6>
                            <h3 class="mb-0 text-warning">240時間</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-clock fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">売上予想</h6>
                            <h3 class="mb-0 text-info">¥{{ number_format($project->estimated_revenue ?? 2880000) }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-currency-yen fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- メインコンテンツ -->
    <div class="row">
        <!-- 左側：詳細情報 -->
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
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-muted" width="35%">案件名:</th>
                                    <td>{{ $project->name ?? '渋谷オフィスビル警備' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">顧客:</th>
                                    <td>
                                        <a href="{{ route('customers.show', $project->customer_id ?? 1) }}" class="text-decoration-none">
                                            {{ $project->customer_name ?? 'ABC商事株式会社' }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">案件タイプ:</th>
                                    <td>{{ getTypeText($project->type ?? 'office_security') }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">勤務場所:</th>
                                    <td>
                                        <div>{{ $project->location ?? '東京都渋谷区渋谷1-1-1' }}</div>
                                        <a href="https://maps.google.com/?q={{ urlencode($project->location ?? '東京都渋谷区渋谷1-1-1') }}" 
                                           target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                            <i class="bi bi-map me-1"></i>
                                            地図で確認
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">担当者:</th>
                                    <td>{{ $project->assignee_name ?? '山田 太郎' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-muted" width="35%">ステータス:</th>
                                    <td>
                                        <span class="badge {{ getStatusClass($project->status ?? 'active') }}">
                                            {{ getStatusText($project->status ?? 'active') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">優先度:</th>
                                    <td>
                                        <span class="badge {{ getPriorityClass($project->priority ?? 'high') }}">
                                            {{ getPriorityText($project->priority ?? 'high') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">開始日:</th>
                                    <td>{{ $project->start_date ?? '2024-05-01' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">終了予定:</th>
                                    <td>{{ $project->end_date ?? '2024-08-31' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">登録日:</th>
                                    <td>{{ $project->created_at ?? '2024-04-20' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($project->description ?? '渋谷の大型オフィスビルでの警備業務。24時間体制での警備を実施。')
                        <div class="mt-3">
                            <h6 class="text-muted mb-2">案件説明</h6>
                            <div class="bg-light p-3 rounded">
                                {{ $project->description ?? '渋谷の大型オフィスビルでの警備業務。24時間体制での警備を実施。' }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- 警備要件 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-check me-2"></i>
                        警備要件
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">必要人数</h6>
                            <div class="d-flex align-items-center mb-3">
                                <span class="fs-4 fw-bold text-primary me-2">{{ $project->required_guards ?? 3 }}</span>
                                <small class="text-muted">名</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">勤務時間</h6>
                            <div class="mb-3">
                                <span class="fw-bold">{{ $project->start_time ?? '09:00' }} - {{ $project->end_time ?? '18:00' }}</span>
                                <div class="small text-muted">
                                    （{{ calculateWorkHours($project->start_time ?? '09:00', $project->end_time ?? '18:00') }}時間）
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">必要経験</h6>
                            <div class="mb-3">
                                <span class="fw-bold">{{ getExperienceText($project->required_experience ?? '3') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 必要資格 -->
                    @if($project->required_qualifications ?? 'security_guard_2,facility_security')
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">必要資格</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(explode(',', $project->required_qualifications ?? 'security_guard_2,facility_security') as $qualification)
                                    <span class="badge bg-light text-dark border">{{ getQualificationText(trim($qualification)) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- 勤務日 -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">勤務日</h6>
                        <div class="d-flex gap-2">
                            @foreach(['月', '火', '水', '木', '金', '土', '日'] as $index => $day)
                                <span class="badge {{ in_array($index + 1, explode(',', $project->work_days ?? '1,2,3,4,5')) ? 'bg-primary' : 'bg-light text-dark' }}">
                                    {{ $day }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- 特記事項 -->
                    @if($project->special_requirements ?? '制服着用必須。入館証を事前配布。')
                        <div>
                            <h6 class="text-muted mb-2">特記事項・注意点</h6>
                            <div class="bg-light p-3 rounded">
                                {{ $project->special_requirements ?? '制服着用必須。入館証を事前配布。' }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- 契約・料金情報 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-currency-yen me-2"></i>
                        契約・料金情報
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">時給</h6>
                            <div class="fs-4 fw-bold text-success mb-3">
                                ¥{{ number_format($project->hourly_rate ?? 1200) }}/時
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">契約金額</h6>
                            <div class="fs-4 fw-bold text-warning mb-3">
                                ¥{{ number_format($project->contract_amount ?? 2880000) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">支払い条件</h6>
                            <div class="mb-3">
                                <span class="fw-bold">{{ getPaymentTermsText($project->payment_terms ?? 'monthly') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 進捗・活動履歴 -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        進捗・活動履歴
                    </h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="addProgressUpdate()">
                        <i class="bi bi-plus me-1"></i>
                        進捗更新
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between">
                                    <h6 class="timeline-title">警備員配置完了</h6>
                                    <small class="text-muted">2024-05-23 14:30</small>
                                </div>
                                <p class="timeline-text">3名の警備員を配置し、現場での業務を開始</p>
                                <div class="timeline-user">担当: 山田 太郎</div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between">
                                    <h6 class="timeline-title">現地調査完了</h6>
                                    <small class="text-muted">2024-05-20 10:15</small>
                                </div>
                                <p class="timeline-text">顧客との打ち合わせ及び現地調査を実施</p>
                                <div class="timeline-user">担当: 田中 花子</div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between">
                                    <h6 class="timeline-title">契約締結</h6>
                                    <small class="text-muted">2024-05-15 16:45</small>
                                </div>
                                <p class="timeline-text">警備契約の締結が完了</p>
                                <div class="timeline-user">担当: 佐藤 次郎</div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between">
                                    <h6 class="timeline-title">案件開始</h6>
                                    <small class="text-muted">2024-05-01 09:00</small>
                                </div>
                                <p class="timeline-text">案件が正式に開始されました</p>
                                <div class="timeline-user">担当: 山田 太郎</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 右側：関連情報・アクション -->
        <div class="col-lg-4 col-md-12">
            <!-- クイックアクション -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        クイックアクション
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('guards.create', ['project_id' => $project->id ?? 1]) }}" class="btn btn-primary">
                            <i class="bi bi-person-plus me-2"></i>
                            警備員アサイン
                        </a>
                        <a href="{{ route('shifts.create', ['project_id' => $project->id ?? 1]) }}" class="btn btn-outline-primary">
                            <i class="bi bi-calendar-plus me-2"></i>
                            シフト作成
                        </a>
                        <a href="{{ route('contracts.create', ['project_id' => $project->id ?? 1]) }}" class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-text me-2"></i>
                            契約書作成
                        </a>
                        <button class="btn btn-outline-warning" onclick="generateReport()">
                            <i class="bi bi-file-earmark-pdf me-2"></i>
                            レポート生成
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- アサイン済み警備員 -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>
                        アサイン済み警備員
                    </h5>
                    <a href="{{ route('guards.index', ['project_id' => $project->id ?? 1]) }}" class="btn btn-sm btn-outline-primary">
                        すべて表示
                    </a>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <span class="text-white fw-bold">田</span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">田中 一郎</h6>
                                    <small class="text-muted">警備員検定2級 • 3年経験</small>
                                </div>
                                <span class="badge bg-success">稼働中</span>
                            </div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <span class="text-white fw-bold">佐</span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">佐藤 二郎</h6>
                                    <small class="text-muted">施設警備検定 • 5年経験</small>
                                </div>
                                <span class="badge bg-success">稼働中</span>
                            </div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <span class="text-white fw-bold">鈴</span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">鈴木 三郎</h6>
                                    <small class="text-muted">警備員検定1級 • 7年経験</small>
                                </div>
                                <span class="badge bg-warning">休憩中</span>
                            </div>
                        </div>
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
                    <div class="row text-center">
                        <div class="col">
                            <div class="fw-bold small text-primary">月</div>
                            <div class="small">田中</div>
                            <div class="small">佐藤</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold small text-primary">火</div>
                            <div class="small">田中</div>
                            <div class="small">鈴木</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold small text-primary">水</div>
                            <div class="small">佐藤</div>
                            <div class="small">鈴木</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold small text-primary">木</div>
                            <div class="small">田中</div>
                            <div class="small">佐藤</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold small text-primary">金</div>
                            <div class="small">田中</div>
                            <div class="small">鈴木</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold small text-muted">土</div>
                            <div class="small text-muted">-</div>
                            <div class="small text-muted">-</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold small text-muted">日</div>
                            <div class="small text-muted">-</div>
                            <div class="small text-muted">-</div>
                        </div>
                    </div>
                    <div class="d-grid mt-3">
                        <a href="{{ route('shifts.calendar', ['project_id' => $project->id ?? 1]) }}" class="btn btn-sm btn-outline-primary">
                            詳細なシフト表示
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- 関連ドキュメント -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        関連ドキュメント
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action px-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-pdf text-danger me-3"></i>
                                <div>
                                    <h6 class="mb-1">警備契約書.pdf</h6>
                                    <small class="text-muted">2024-05-15 作成</small>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action px-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-text text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-1">現地調査報告書.docx</h6>
                                    <small class="text-muted">2024-05-20 作成</small>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action px-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-spreadsheet text-success me-3"></i>
                                <div>
                                    <h6 class="mb-1">シフト表.xlsx</h6>
                                    <small class="text-muted">2024-05-22 更新</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="d-grid mt-3">
                        <button class="btn btn-sm btn-outline-secondary" onclick="uploadDocument()">
                            <i class="bi bi-upload me-1"></i>
                            ドキュメント追加
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .project-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    
    .table-borderless th {
        font-weight: 500;
        padding: 0.5rem 0;
    }
    
    .table-borderless td {
        padding: 0.5rem 0;
    }
    
    .timeline {
        position: relative;
        padding: 1rem 0;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        padding: 0 0 2rem 50px;
    }
    
    .timeline-marker {
        position: absolute;
        left: 12px;
        top: 5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 3px solid white;
        z-index: 1;
    }
    
    .timeline-content {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        border-left: 3px solid #e9ecef;
    }
    
    .timeline-title {
        font-size: 0.95rem;
        margin-bottom: 0.5rem;
        color: #495057;
    }
    
    .timeline-text {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        color: #6c757d;
    }
    
    .timeline-user {
        font-size: 0.8rem;
        color: #6c757d;
        font-style: italic;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .list-group-item {
        border: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease;
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
    
    .list-group-item:hover {
        background-color: rgba(59, 130, 246, 0.05);
    }
    
    @media (max-width: 768px) {
        .timeline {
            padding-left: 30px;
        }
        
        .timeline-item {
            padding-left: 40px;
        }
        
        .timeline-marker {
            left: 8px;
        }
        
        .timeline::before {
            left: 16px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // ツールチップ初期化
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
    
    // 案件複製
    function duplicateProject() {
        if (confirm('この案件を複製しますか？')) {
            const projectId = {{ $project->id ?? 1 }};
            
            $.ajax({
                url: `/projects/${projectId}/duplicate`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function(response) {
                showSuccessMessage('案件を複製しました');
                window.location.href = `{{ route('projects.show', '') }}/${response.id}`;
            })
            .fail(function() {
                showErrorMessage('案件の複製に失敗しました');
            });
        }
    }
    
    // エクスポート
    function exportProject() {
        const projectId = {{ $project->id ?? 1 }};
        window.open(`/projects/${projectId}/export`, '_blank');
    }
    
    // 印刷
    function printProject() {
        window.print();
    }
    
    // ステータス更新
    function updateStatus(newStatus) {
        const projectId = {{ $project->id ?? 1 }};
        const statusNames = {
            'completed': '完了',
            'on_hold': '保留',
            'cancelled': 'キャンセル'
        };
        
        if (confirm(`案件のステータスを「${statusNames[newStatus]}」に変更しますか？`)) {
            $.ajax({
                url: `/projects/${projectId}/status`,
                type: 'POST',
                data: {
                    status: newStatus,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function() {
                showSuccessMessage('ステータスを更新しました');
                location.reload();
            })
            .fail(function() {
                showErrorMessage('ステータスの更新に失敗しました');
            });
        }
    }
    
    // 進捗更新
    function addProgressUpdate() {
        const projectId = {{ $project->id ?? 1 }};
        
        // 簡易的な進捗更新モーダル（実際はより詳細なフォームを作成）
        const progress = prompt('進捗率を入力してください（0-100）:');
        const comment = prompt('コメントを入力してください:');
        
        if (progress !== null && comment !== null) {
            $.ajax({
                url: `/projects/${projectId}/progress`,
                type: 'POST',
                data: {
                    progress: progress,
                    comment: comment,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function() {
                showSuccessMessage('進捗を更新しました');
                location.reload();
            })
            .fail(function() {
                showErrorMessage('進捗の更新に失敗しました');
            });
        }
    }
    
    // レポート生成
    function generateReport() {
        const projectId = {{ $project->id ?? 1 }};
        
        if (confirm('案件レポートを生成しますか？')) {
            showLoading('レポートを生成しています...');
            
            $.ajax({
                url: `/projects/${projectId}/report`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function(response) {
                hideLoading();
                window.open(response.download_url, '_blank');
                showSuccessMessage('レポートを生成しました');
            })
            .fail(function() {
                hideLoading();
                showErrorMessage('レポートの生成に失敗しました');
            });
        }
    }
    
    // ドキュメントアップロード
    function uploadDocument() {
        // ファイル選択ダイアログを表示（実際はより詳細なアップロード機能を実装）
        const input = document.createElement('input');
        input.type = 'file';
        input.multiple = true;
        input.accept = '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx';
        
        input.onchange = function(e) {
            const files = e.target.files;
            if (files.length > 0) {
                uploadFiles(files);
            }
        };
        
        input.click();
    }
    
    // ファイルアップロード処理
    function uploadFiles(files) {
        const projectId = {{ $project->id ?? 1 }};
        const formData = new FormData();
        
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        $.ajax({
            url: `/projects/${projectId}/documents`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false
        })
        .done(function() {
            showSuccessMessage('ドキュメントをアップロードしました');
            location.reload();
        })
        .fail(function() {
            showErrorMessage('ドキュメントのアップロードに失敗しました');
        });
    }
    
    // ローディング表示
    function showLoading(message = '処理中...') {
        // 簡易的なローディング表示
        $('body').append(`
            <div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                 style="background: rgba(0,0,0,0.5); z-index: 9999;">
                <div class="bg-white p-4 rounded">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border text-primary me-3" role="status"></div>
                        <span>${message}</span>
                    </div>
                </div>
            </div>
        `);
    }
    
    function hideLoading() {
        $('#loadingOverlay').remove();
    }
</script>

@php
    function getStatusClass($status) {
        $classes = [
            'planning' => 'bg-info',
            'active' => 'bg-success',
            'on_hold' => 'bg-warning',
            'completed' => 'bg-secondary',
            'cancelled' => 'bg-danger'
        ];
        return $classes[$status] ?? 'bg-secondary';
    }
    
    function getStatusText($status) {
        $texts = [
            'planning' => '計画中',
            'active' => '実行中',
            'on_hold' => '保留中',
            'completed' => '完了',
            'cancelled' => 'キャンセル'
        ];
        return $texts[$status] ?? $status;
    }
    
    function getPriorityClass($priority) {
        $classes = [
            'high' => 'bg-danger',
            'normal' => 'bg-warning',
            'low' => 'bg-success'
        ];
        return $classes[$priority] ?? 'bg-secondary';
    }
    
    function getPriorityText($priority) {
        $texts = [
            'high' => '高',
            'normal' => '標準',
            'low' => '低'
        ];
        return $texts[$priority] ?? $priority;
    }
    
    function getTypeText($type) {
        $texts = [
            'office_security' => 'オフィス警備',
            'construction_security' => '工事現場警備',
            'event_security' => 'イベント警備',
            'facility_security' => '施設警備',
            'traffic_control' => '交通誘導'
        ];
        return $texts[$type] ?? 'その他';
    }
    
    function getExperienceText($experience) {
        if ($experience == '0') return '未経験可';
        return $experience . '年以上';
    }
    
    function getQualificationText($qualification) {
        $texts = [
            'security_guard_1' => '警備員検定1級',
            'security_guard_2' => '警備員検定2級',
            'traffic_control' => '交通誘導警備業務検定',
            'facility_security' => '施設警備業務検定',
            'fire_prevention' => '防火管理者'
        ];
        return $texts[$qualification] ?? $qualification;
    }
    
    function getPaymentTermsText($terms) {
        $texts = [
            'monthly' => '月末締め翌月払い',
            'weekly' => '週次払い',
            'project_end' => '案件完了時一括',
            'custom' => 'その他'
        ];
        return $texts[$terms] ?? $terms;
    }
    
    function calculateWorkHours($start, $end) {
        $startTime = strtotime($start);
        $endTime = strtotime($end);
        return ($endTime - $startTime) / 3600;
    }
@endphp
@endpush
@endsection
