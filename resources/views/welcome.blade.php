@extends('layouts.app')

@section('title', 'ホーム')

@section('content')
<div class="container-fluid">
    <!-- ヒーローセクション -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 bg-gradient text-white" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); min-height: 400px;">
                <div class="card-body d-flex align-items-center">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-lg-8 col-md-12">
                                <h1 class="display-4 fw-bold mb-4">
                                    <i class="bi bi-shield-check me-3"></i>
                                    警備グループ統合管理システム
                                </h1>
                                <p class="lead mb-4">
                                    3社の警備会社を統合する包括的な受注管理・シフト管理システム。<br>
                                    効率的な業務運営と売上向上を実現します。
                                </p>
                                <div class="d-flex flex-wrap gap-3">
                                    @auth
                                        <a href="{{ route('dashboard.index') }}" class="btn btn-light btn-lg">
                                            <i class="bi bi-speedometer2 me-2"></i>
                                            ダッシュボードへ
                                        </a>
                                    @else
                                        <a href="{{ route('auth.login.form') }}" class="btn btn-light btn-lg">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>
                                            ログイン
                                        </a>
                                        <a href="{{ route('auth.register.form') }}" class="btn btn-outline-light btn-lg">
                                            <i class="bi bi-person-plus me-2"></i>
                                            ユーザー登録
                                        </a>
                                    @endauth
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12 text-center">
                                <div class="position-relative">
                                    <i class="bi bi-shield-check display-1 opacity-25"></i>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="badge bg-light text-dark p-2 w-100">
                                                    <i class="bi bi-people d-block mb-1"></i>
                                                    <small>顧客管理</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="badge bg-light text-dark p-2 w-100">
                                                    <i class="bi bi-briefcase d-block mb-1"></i>
                                                    <small>案件管理</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="badge bg-light text-dark p-2 w-100">
                                                    <i class="bi bi-person-badge d-block mb-1"></i>
                                                    <small>警備員管理</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="badge bg-light text-dark p-2 w-100">
                                                    <i class="bi bi-calendar3 d-block mb-1"></i>
                                                    <small>シフト管理</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 対象会社紹介 -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">
                <i class="bi bi-building me-2"></i>
                対象会社
            </h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 80px; height: 80px;">
                                <i class="bi bi-shield-shaded display-6"></i>
                            </div>
                            <h4 class="card-title">㈲東央警備</h4>
                            <p class="card-text text-muted">
                                地域密着型の警備サービスを提供。<br>
                                商業施設・オフィスビル警備に特化。
                            </p>
                            <div class="d-flex justify-content-center gap-2">
                                <span class="badge bg-secondary">商業施設</span>
                                <span class="badge bg-secondary">オフィス</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 80px; height: 80px;">
                                <i class="bi bi-building display-6"></i>
                            </div>
                            <h4 class="card-title">㈱Nikkeiホールディングス</h4>
                            <p class="card-text text-muted">
                                企業向け総合セキュリティサービス。<br>
                                大規模施設・イベント警備を担当。
                            </p>
                            <div class="d-flex justify-content-center gap-2">
                                <span class="badge bg-secondary">大規模施設</span>
                                <span class="badge bg-secondary">イベント</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 80px; height: 80px;">
                                <i class="bi bi-globe display-6"></i>
                            </div>
                            <h4 class="card-title">㈱全日本エンタープライズ</h4>
                            <p class="card-text text-muted">
                                全国展開の警備サービス企業。<br>
                                工事現場・交通誘導警備に強み。
                            </p>
                            <div class="d-flex justify-content-center gap-2">
                                <span class="badge bg-secondary">工事現場</span>
                                <span class="badge bg-secondary">交通誘導</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 主要機能紹介 -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5">
                <i class="bi bi-gear me-2"></i>
                主要機能
            </h2>
            <div class="row g-4">
                <!-- 顧客・案件管理 -->
                <div class="col-lg-6 col-md-12">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-people me-2"></i>
                                顧客・案件管理
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-person-lines-fill text-primary me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">顧客管理</h6>
                                            <small class="text-muted">顧客情報の一元管理</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-briefcase text-success me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">案件管理</h6>
                                            <small class="text-muted">プロジェクト進捗管理</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-file-text text-warning me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">見積管理</h6>
                                            <small class="text-muted">見積作成・承認フロー</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-file-earmark-text text-info me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">契約管理</h6>
                                            <small class="text-muted">契約更新・アラート</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 人材・シフト管理 -->
                <div class="col-lg-6 col-md-12">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-person-badge me-2"></i>
                                人材・シフト管理
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-person-badge text-primary me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">警備員管理</h6>
                                            <small class="text-muted">人材情報・スキル管理</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-calendar3 text-success me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">シフト管理</h6>
                                            <small class="text-muted">最適化・自動割り当て</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-clock text-warning me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">勤怠管理</h6>
                                            <small class="text-muted">出退勤・承認フロー</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-journal-text text-info me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">日報管理</h6>
                                            <small class="text-muted">業務報告・品質管理</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 売上・請求管理 -->
                <div class="col-lg-6 col-md-12">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-currency-yen me-2"></i>
                                売上・請求管理
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-receipt text-primary me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">請求管理</h6>
                                            <small class="text-muted">請求書発行・入金管理</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-graph-up text-success me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">売上分析</h6>
                                            <small class="text-muted">収益レポート・分析</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-calendar-check text-warning me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">売掛管理</h6>
                                            <small class="text-muted">未収金・回収管理</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-pie-chart text-info me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">財務分析</h6>
                                            <small class="text-muted">利益率・コスト分析</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 統合ダッシュボード -->
                <div class="col-lg-6 col-md-12">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-speedometer2 me-2"></i>
                                統合ダッシュボード
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-bar-chart text-primary me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">KPI監視</h6>
                                            <small class="text-muted">重要指標のリアルタイム監視</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-bell text-success me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">アラート</h6>
                                            <small class="text-muted">重要な通知・警告</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-calendar-week text-warning me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">カレンダー</h6>
                                            <small class="text-muted">統合スケジュール管理</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="bi bi-file-earmark-pdf text-info me-3 fs-4"></i>
                                        <div>
                                            <h6 class="mb-1">レポート</h6>
                                            <small class="text-muted">各種統計・分析レポート</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- システム特徴 -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header text-center">
                    <h3 class="mb-0">
                        <i class="bi bi-star me-2"></i>
                        システムの特徴
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-3 col-md-6 text-center">
                            <div class="mb-3">
                                <i class="bi bi-phone display-4 text-primary"></i>
                            </div>
                            <h5>モバイル対応</h5>
                            <p class="text-muted">
                                スマートフォン・タブレットからいつでもアクセス可能
                            </p>
                        </div>
                        <div class="col-lg-3 col-md-6 text-center">
                            <div class="mb-3">
                                <i class="bi bi-cloud-check display-4 text-success"></i>
                            </div>
                            <h5>クラウドベース</h5>
                            <p class="text-muted">
                                安全で高可用性のクラウド環境で運用
                            </p>
                        </div>
                        <div class="col-lg-3 col-md-6 text-center">
                            <div class="mb-3">
                                <i class="bi bi-lightning display-4 text-warning"></i>
                            </div>
                            <h5>リアルタイム</h5>
                            <p class="text-muted">
                                情報の即座更新・リアルタイム通知機能
                            </p>
                        </div>
                        <div class="col-lg-3 col-md-6 text-center">
                            <div class="mb-3">
                                <i class="bi bi-shield-lock display-4 text-danger"></i>
                            </div>
                            <h5>セキュリティ</h5>
                            <p class="text-muted">
                                企業レベルの高度なセキュリティ対策
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @guest
    <!-- 利用開始セクション -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body text-center py-5">
                    <h3 class="mb-4">
                        <i class="bi bi-rocket-takeoff me-2"></i>
                        今すぐ利用を開始
                    </h3>
                    <p class="lead mb-4">
                        効率的な警備業務管理で、売上向上と業務品質の向上を実現しましょう。
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('auth.register.form') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-person-plus me-2"></i>
                            ユーザー登録
                        </a>
                        <a href="{{ route('auth.login.form') }}" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            ログイン
                        </a>
                    </div>
                    
                    <div class="mt-4">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            システム利用には管理者による承認が必要です
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endguest
</div>

@push('styles')
<style>
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
    
    .bg-gradient {
        position: relative;
        overflow: hidden;
    }
    
    .bg-gradient::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
        animation: shimmer 3s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .display-1 {
        font-size: 6rem;
    }
    
    @media (max-width: 768px) {
        .display-4 {
            font-size: 2.5rem;
        }
        
        .display-1 {
            font-size: 4rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // カード要素にホバーエフェクトを追加
        $('.card').hover(
            function() {
                $(this).addClass('shadow-lg');
            },
            function() {
                $(this).removeClass('shadow-lg');
            }
        );
        
        // 統計データを表示（デモ用）
        setTimeout(function() {
            updateStats();
        }, 1000);
    });
    
    function updateStats() {
        // 実際の統計データを取得してバッジに表示
        // このデモでは固定値を使用
        const stats = {
            customers: 150,
            projects: 89,
            guards: 234,
            shifts: 456
        };
        
        // アニメーション付きで数値を更新
        Object.keys(stats).forEach(key => {
            animateNumber($(`.${key}-count`), stats[key], 1000);
        });
    }
    
    function animateNumber(element, target, duration) {
        $({ counter: 0 }).animate({ counter: target }, {
            duration: duration,
            easing: 'swing',
            step: function() {
                element.text(Math.ceil(this.counter).toLocaleString());
            },
            complete: function() {
                element.text(target.toLocaleString());
            }
        });
    }
</script>
@endpush
@endsection
