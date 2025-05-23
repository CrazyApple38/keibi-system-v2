@extends('layouts.app')

@section('title', '顧客詳細')

@section('content')
<div class="container-fluid">
    <!-- パンくずリスト -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">顧客一覧</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $customer->name ?? 'ABC商事株式会社' }}</li>
        </ol>
    </nav>
    
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center">
                    <div class="customer-avatar me-3" style="background-color: #3b82f6">
                        {{ substr($customer->name ?? 'ABC商事株式会社', 0, 1) }}
                    </div>
                    <div>
                        <h2 class="mb-1">{{ $customer->name ?? 'ABC商事株式会社' }}</h2>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge {{ ($customer->status ?? 'active') === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ($customer->status ?? 'active') === 'active' ? 'アクティブ' : '非アクティブ' }}
                            </span>
                            <span class="badge bg-primary">
                                {{ ($customer->type ?? 'corporate') === 'corporate' ? '法人' : '個人' }}
                            </span>
                            @if(($customer->priority ?? 'high') === 'high')
                                <span class="badge bg-warning">高優先度</span>
                            @endif
                        </div>
                        <p class="text-muted mb-0">
                            <i class="bi bi-person me-1"></i>
                            {{ $customer->contact_person ?? '田中 太郎' }}
                            @if($customer->contact_title ?? '総務部 部長')
                                - {{ $customer->contact_title ?? '総務部 部長' }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('customers.edit', $customer->id ?? 1) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>
                        編集
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportCustomerData()">
                                <i class="bi bi-download me-2"></i>データエクスポート
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="printCustomerInfo()">
                                <i class="bi bi-printer me-2"></i>印刷
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="archiveCustomer()">
                                <i class="bi bi-archive me-2"></i>アーカイブ
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 統計サマリー -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">総案件数</h6>
                            <h3 class="mb-0 text-primary">8</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-briefcase fs-4 text-primary"></i>
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
                            <h6 class="text-muted mb-1">アクティブ契約</h6>
                            <h3 class="mb-0 text-success">3</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-file-text fs-4 text-success"></i>
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
                            <h6 class="text-muted mb-1">月間売上</h6>
                            <h3 class="mb-0 text-warning">¥850万</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-currency-yen fs-4 text-warning"></i>
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
                            <h6 class="text-muted mb-1">顧客満足度</h6>
                            <h3 class="mb-0 text-info">4.8/5.0</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-star-fill fs-4 text-info"></i>
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
                                    <th class="text-muted" width="30%">顧客名:</th>
                                    <td>{{ $customer->name ?? 'ABC商事株式会社' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">顧客種別:</th>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ ($customer->type ?? 'corporate') === 'corporate' ? '法人' : '個人' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">担当者:</th>
                                    <td>{{ $customer->contact_person ?? '田中 太郎' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">部署・役職:</th>
                                    <td>{{ $customer->contact_title ?? '総務部 部長' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">業種:</th>
                                    <td>{{ $customer->business_type ?? '商社' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-muted" width="30%">ステータス:</th>
                                    <td>
                                        <span class="badge {{ ($customer->status ?? 'active') === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ($customer->status ?? 'active') === 'active' ? 'アクティブ' : '非アクティブ' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">優先度:</th>
                                    <td>
                                        <span class="badge {{ ($customer->priority ?? 'high') === 'high' ? 'bg-warning' : 'bg-secondary' }}">
                                            {{ ($customer->priority ?? 'high') === 'high' ? '高' : '標準' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">従業員数:</th>
                                    <td>{{ $customer->employee_count ?? '11-50名' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">年間売上:</th>
                                    <td>{{ $customer->annual_revenue ?? '1億円-5億円' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">登録日:</th>
                                    <td>{{ $customer->created_at ?? '2024-05-20' }}</td>
                                </tr>
                            </table>
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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="contact-item mb-3">
                                <h6 class="text-muted mb-1">
                                    <i class="bi bi-telephone me-1"></i>
                                    電話番号
                                </h6>
                                <div class="d-flex align-items-center">
                                    <span class="me-2">{{ $customer->phone ?? '03-1234-5678' }}</span>
                                    <a href="tel:{{ $customer->phone ?? '03-1234-5678' }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-telephone"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="contact-item mb-3">
                                <h6 class="text-muted mb-1">
                                    <i class="bi bi-printer me-1"></i>
                                    FAX番号
                                </h6>
                                <span>{{ $customer->fax ?? '03-1234-5679' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="contact-item mb-3">
                                <h6 class="text-muted mb-1">
                                    <i class="bi bi-envelope me-1"></i>
                                    メールアドレス
                                </h6>
                                <div class="d-flex align-items-center">
                                    <span class="me-2">{{ $customer->email ?? 'tanaka@abc-trading.co.jp' }}</span>
                                    <a href="mailto:{{ $customer->email ?? 'tanaka@abc-trading.co.jp' }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-envelope"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="contact-item mb-3">
                                <h6 class="text-muted mb-1">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    住所
                                </h6>
                                <div class="d-flex align-items-start">
                                    <span class="me-2">{{ $customer->address ?? '〒100-0001 東京都千代田区千代田1-1' }}</span>
                                    <a href="https://maps.google.com/?q={{ urlencode($customer->address ?? '東京都千代田区千代田1-1') }}" 
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-map"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- タグ・備考 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-tags me-2"></i>
                        タグ・備考
                    </h5>
                </div>
                <div class="card-body">
                    <!-- タグ -->
                    @if($customer->tags ?? 'VIP, 大口, 継続')
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">タグ</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(explode(',', $customer->tags ?? 'VIP, 大口, 継続') as $tag)
                                    <span class="badge bg-light text-dark border">{{ trim($tag) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- 備考 -->
                    @if($customer->notes ?? '大口顧客。月次契約更新。担当者との関係良好。')
                        <div>
                            <h6 class="text-muted mb-2">備考</h6>
                            <div class="bg-light p-3 rounded">
                                {{ $customer->notes ?? '大口顧客。月次契約更新。担当者との関係良好。' }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- 最近のアクティビティ -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        最近のアクティビティ
                    </h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">すべて表示</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle p-2 me-3">
                                    <i class="bi bi-file-text text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">新規契約締結</h6>
                                    <p class="mb-1 text-muted">オフィスビル警備契約を締結</p>
                                    <small class="text-muted">2024-05-23 14:30</small>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle p-2 me-3">
                                    <i class="bi bi-telephone text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">電話コンタクト</h6>
                                    <p class="mb-1 text-muted">月次報告書について打ち合わせ</p>
                                    <small class="text-muted">2024-05-22 10:15</small>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning rounded-circle p-2 me-3">
                                    <i class="bi bi-envelope text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">見積送付</h6>
                                    <p class="mb-1 text-muted">新規案件の見積書を送付</p>
                                    <small class="text-muted">2024-05-20 16:45</small>
                                </div>
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
                        <a href="{{ route('projects.create', ['customer_id' => $customer->id ?? 1]) }}" class="btn btn-primary">
                            <i class="bi bi-briefcase me-2"></i>
                            新規案件作成
                        </a>
                        <a href="{{ route('quotations.create', ['customer_id' => $customer->id ?? 1]) }}" class="btn btn-outline-primary">
                            <i class="bi bi-file-text me-2"></i>
                            見積作成
                        </a>
                        <a href="{{ route('contracts.create', ['customer_id' => $customer->id ?? 1]) }}" class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-text me-2"></i>
                            契約作成
                        </a>
                        <button class="btn btn-outline-info" onclick="sendEmail()">
                            <i class="bi bi-envelope me-2"></i>
                            メール送信
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- 関連データ -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-link-45deg me-2"></i>
                        関連データ
                    </h5>
                </div>
                <div class="card-body">
                    <!-- 案件 -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">案件</h6>
                            <small class="text-muted">8件（アクティブ: 3件）</small>
                        </div>
                        <a href="{{ route('projects.index', ['customer_id' => $customer->id ?? 1]) }}" class="btn btn-sm btn-outline-primary">
                            表示
                        </a>
                    </div>
                    
                    <!-- 契約 -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">契約</h6>
                            <small class="text-muted">5件（有効: 3件）</small>
                        </div>
                        <a href="{{ route('contracts.index', ['customer_id' => $customer->id ?? 1]) }}" class="btn btn-sm btn-outline-success">
                            表示
                        </a>
                    </div>
                    
                    <!-- 請求 -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">請求</h6>
                            <small class="text-muted">12件（未収: 2件）</small>
                        </div>
                        <a href="{{ route('invoices.index', ['customer_id' => $customer->id ?? 1]) }}" class="btn btn-sm btn-outline-warning">
                            表示
                        </a>
                    </div>
                    
                    <!-- 見積 -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">見積</h6>
                            <small class="text-muted">6件（有効: 1件）</small>
                        </div>
                        <a href="{{ route('quotations.index', ['customer_id' => $customer->id ?? 1]) }}" class="btn btn-sm btn-outline-info">
                            表示
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- 月間売上推移 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        月間売上推移
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="200"></canvas>
                </div>
            </div>
            
            <!-- 担当者情報 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>
                        担当者情報
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 40px; height: 40px;">
                            <i class="bi bi-person text-white"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">山田 太郎</h6>
                            <small class="text-muted">営業部</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">連絡先</small>
                        <div>📞 090-1234-5678</div>
                        <div>📧 yamada@security.co.jp</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="contactAssignee()">
                            <i class="bi bi-telephone me-1"></i>
                            担当者に連絡
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .customer-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        color: white;
    }
    
    .contact-item {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding-bottom: 1rem;
    }
    
    .contact-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .table-borderless th {
        font-weight: 500;
        padding: 0.5rem 0;
    }
    
    .table-borderless td {
        padding: 0.5rem 0;
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
        padding: 1rem;
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
    
    .bg-light.text-dark {
        background-color: #f8f9fa !important;
        border: 1px solid #e9ecef;
    }
    
    @media (max-width: 768px) {
        .customer-avatar {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
        }
        
        .card-body {
            padding: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        // 売上推移チャート初期化
        initRevenueChart();
        
        // ツールチップ初期化
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
    
    // 売上推移チャート
    function initRevenueChart() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['1月', '2月', '3月', '4月', '5月', '6月'],
                datasets: [{
                    label: '売上（万円）',
                    data: [650, 720, 580, 890, 850, 920],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + '万円';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // エクスポート機能
    function exportCustomerData() {
        const customerId = {{ $customer->id ?? 1 }};
        window.open(`/customers/${customerId}/export`, '_blank');
    }
    
    // 印刷機能
    function printCustomerInfo() {
        window.print();
    }
    
    // アーカイブ機能
    function archiveCustomer() {
        if (confirm('この顧客をアーカイブしますか？\nアーカイブされた顧客は一覧に表示されなくなります。')) {
            const customerId = {{ $customer->id ?? 1 }};
            
            $.ajax({
                url: `/customers/${customerId}/archive`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function() {
                showSuccessMessage('顧客をアーカイブしました');
                setTimeout(() => {
                    window.location.href = '{{ route("customers.index") }}';
                }, 1500);
            })
            .fail(function() {
                showErrorMessage('アーカイブに失敗しました');
            });
        }
    }
    
    // メール送信
    function sendEmail() {
        const email = '{{ $customer->email ?? "tanaka@abc-trading.co.jp" }}';
        const subject = encodeURIComponent('お世話になっております - 警備サービスについて');
        const body = encodeURIComponent(`${$customer->contact_person ?? '田中'} 様\n\nいつもお世話になっております。\n\n`);
        
        window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
    }
    
    // 担当者に連絡
    function contactAssignee() {
        const phone = '090-1234-5678';
        if (confirm(`担当者（山田）に電話をかけますか？\n${phone}`)) {
            window.location.href = `tel:${phone}`;
        }
    }
    
    // 住所をGoogleマップで表示
    function showOnMap() {
        const address = '{{ $customer->address ?? "東京都千代田区千代田1-1" }}';
        const url = `https://maps.google.com/?q=${encodeURIComponent(address)}`;
        window.open(url, '_blank');
    }
    
    // ステータス更新
    function updateStatus(newStatus) {
        const customerId = {{ $customer->id ?? 1 }};
        
        $.ajax({
            url: `/customers/${customerId}/status`,
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
    
    // 印刷用スタイル調整
    window.onbeforeprint = function() {
        // 印刷時にサイドバーを非表示
        $('.col-lg-4').hide();
        $('.col-lg-8').removeClass('col-lg-8').addClass('col-12');
    }
    
    window.onafterprint = function() {
        // 印刷後に元に戻す
        $('.col-lg-4').show();
        $('.col-12').removeClass('col-12').addClass('col-lg-8');
    }
</script>
@endpush
@endsection
