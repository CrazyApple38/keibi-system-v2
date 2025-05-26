@extends('layouts.app')

@section('title', '警備員管理')

@section('content')
<div class="container-fluid">
    <!-- ページヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">ダッシュボード</a></li>
                            <li class="breadcrumb-item active">警備員管理</li>
                        </ol>
                    </nav>
                    <h2 class="mb-1">
                        <i class="bi bi-person-badge me-2"></i>
                        警備員管理
                    </h2>
                    <p class="text-muted mb-0">警備員の一覧表示・検索・管理</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" id="exportGuards">
                        <i class="bi bi-download me-1"></i>
                        エクスポート
                    </button>
                    <a href="{{ route('guards.create') }}" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>
                        新規登録
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 検索・フィルターセクション -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="searchForm" class="row g-3">
                        <!-- 検索キーワード -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">検索キーワード</label>
                            <input type="text" class="form-control search-input" name="search" 
                                   placeholder="名前、ID、電話番号で検索" value="{{ request('search') }}">
                        </div>
                        
                        <!-- ステータス -->
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">ステータス</label>
                            <select class="form-select" name="status">
                                <option value="">全て</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>稼働中</option>
                                <option value="standby" {{ request('status') === 'standby' ? 'selected' : '' }}>待機中</option>
                                <option value="break" {{ request('status') === 'break' ? 'selected' : '' }}>休憩中</option>
                                <option value="off" {{ request('status') === 'off' ? 'selected' : '' }}>退勤</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>非稼働</option>
                            </select>
                        </div>
                        
                        <!-- 所属会社 -->
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">所属会社</label>
                            <select class="form-select" name="company">
                                <option value="">全て</option>
                                <option value="touo_security" {{ request('company') === 'touo_security' ? 'selected' : '' }}>東央警備</option>
                                <option value="nikkei_hd" {{ request('company') === 'nikkei_hd' ? 'selected' : '' }}>Nikkei HD</option>
                                <option value="zennichi_ep" {{ request('company') === 'zennichi_ep' ? 'selected' : '' }}>全日本EP</option>
                            </select>
                        </div>
                        
                        <!-- 資格 -->
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">保有資格</label>
                            <select class="form-select" name="qualification">
                                <option value="">全て</option>
                                <option value="guard_license" {{ request('qualification') === 'guard_license' ? 'selected' : '' }}>警備員検定</option>
                                <option value="traffic_control" {{ request('qualification') === 'traffic_control' ? 'selected' : '' }}>交通誘導警備</option>
                                <option value="facility_security" {{ request('qualification') === 'facility_security' ? 'selected' : '' }}>施設警備</option>
                                <option value="bodyguard" {{ request('qualification') === 'bodyguard' ? 'selected' : '' }}>身辺警備</option>
                                <option value="machine_security" {{ request('qualification') === 'machine_security' ? 'selected' : '' }}>機械警備</option>
                            </select>
                        </div>
                        
                        <!-- 経験年数 -->
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">経験年数</label>
                            <select class="form-select" name="experience">
                                <option value="">全て</option>
                                <option value="0-1" {{ request('experience') === '0-1' ? 'selected' : '' }}>1年未満</option>
                                <option value="1-3" {{ request('experience') === '1-3' ? 'selected' : '' }}>1-3年</option>
                                <option value="3-5" {{ request('experience') === '3-5' ? 'selected' : '' }}>3-5年</option>
                                <option value="5-10" {{ request('experience') === '5-10' ? 'selected' : '' }}>5-10年</option>
                                <option value="10+" {{ request('experience') === '10+' ? 'selected' : '' }}>10年以上</option>
                            </select>
                        </div>
                        
                        <!-- 年齢範囲 -->
                        <div class="col-lg-1 col-md-6">
                            <label class="form-label">年齢（最小）</label>
                            <input type="number" class="form-control" name="age_min" placeholder="20" 
                                   value="{{ request('age_min') }}" min="18" max="100">
                        </div>
                        
                        <!-- 検索・リセットボタン -->
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>
                                    検索
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="resetSearch">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    リセット
                                </button>
                                <button type="button" class="btn btn-outline-info" id="advancedSearch">
                                    <i class="bi bi-funnel me-1"></i>
                                    詳細検索
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 統計情報セクション -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-people display-6 text-primary mb-2"></i>
                    <h4 class="mb-1" id="totalGuards">{{ $guards->total() ?? 0 }}</h4>
                    <small class="text-muted">総数</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-person-check display-6 text-success mb-2"></i>
                    <h4 class="mb-1" id="activeGuards">0</h4>
                    <small class="text-muted">稼働中</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-person-dash display-6 text-warning mb-2"></i>
                    <h4 class="mb-1" id="standbyGuards">0</h4>
                    <small class="text-muted">待機中</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-award display-6 text-info mb-2"></i>
                    <h4 class="mb-1" id="qualifiedGuards">0</h4>
                    <small class="text-muted">有資格者</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 警備員一覧テーブル -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list me-2"></i>
                            警備員一覧
                        </h5>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center">
                                <label class="me-2">表示件数:</label>
                                <select class="form-select form-select-sm" style="width: auto;" id="perPage">
                                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10件</option>
                                    <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25件</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50件</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100件</option>
                                </select>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="compactView">
                                <label class="form-check-label" for="compactView">コンパクト表示</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-sortable" id="guardsTable">
                            <thead>
                                <tr>
                                    <th data-sort="employee_id">
                                        <i class="bi bi-sort-alpha-down me-1"></i>
                                        社員ID
                                    </th>
                                    <th data-sort="name">
                                        <i class="bi bi-sort-alpha-down me-1"></i>
                                        氏名
                                    </th>
                                    <th data-sort="age">年齢</th>
                                    <th data-sort="company">所属</th>
                                    <th>保有資格</th>
                                    <th data-sort="experience_years">経験年数</th>
                                    <th data-sort="status">ステータス</th>
                                    <th data-sort="hourly_rate">時給</th>
                                    <th>連絡先</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($guards ?? [] as $guard)
                                <tr>
                                    <td>
                                        <strong>{{ $guard->employee_id }}</strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-3">
                                                @if($guard->profile_photo)
                                                    <img src="{{ Storage::url($guard->profile_photo) }}" 
                                                         class="rounded-circle" width="40" height="40" alt="プロフィール">
                                                @else
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <span class="text-white fw-bold">{{ mb_substr($guard->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $guard->name }}</div>
                                                <small class="text-muted">{{ $guard->name_kana }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $guard->age ?? '不明' }}歳</td>
                                    <td>
                                        <span class="badge bg-{{ $guard->company === 'touo_security' ? 'primary' : ($guard->company === 'nikkei_hd' ? 'success' : 'warning') }}">
                                            {{ $guard->getCompanyName() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($guard->qualifications && count($guard->qualifications) > 0)
                                            @foreach(array_slice($guard->qualifications, 0, 2) as $qual)
                                                <span class="badge bg-info me-1">{{ $qual }}</span>
                                            @endforeach
                                            @if(count($guard->qualifications) > 2)
                                                <span class="badge bg-secondary">+{{ count($guard->qualifications) - 2 }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">なし</span>
                                        @endif
                                    </td>
                                    <td>{{ $guard->experience_years ?? 0 }}年</td>
                                    <td>
                                        <span class="badge bg-{{ $guard->getStatusColor() }}">
                                            {{ $guard->getStatusText() }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>¥{{ number_format($guard->hourly_rate ?? 0) }}</strong>
                                        <small class="text-muted">/時</small>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            @if($guard->phone)
                                                <small><i class="bi bi-telephone me-1"></i>{{ $guard->phone }}</small>
                                            @endif
                                            @if($guard->email)
                                                <small><i class="bi bi-envelope me-1"></i>{{ Str::limit($guard->email, 20) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('guards.show', $guard) }}" 
                                               class="btn btn-sm btn-outline-info" title="詳細表示">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('guards.edit', $guard) }}" 
                                               class="btn btn-sm btn-outline-warning" title="編集">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteGuard({{ $guard->id }})" title="削除">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <i class="bi bi-inbox display-1 text-muted"></i>
                                        <div class="mt-3">
                                            <h5 class="text-muted">警備員が登録されていません</h5>
                                            <p class="text-muted">新しい警備員を登録してください。</p>
                                            <a href="{{ route('guards.create') }}" class="btn btn-primary">
                                                <i class="bi bi-person-plus me-1"></i>
                                                警備員を登録
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if(isset($guards) && $guards->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            {{ $guards->firstItem() }}～{{ $guards->lastItem() }}件 / {{ $guards->total() }}件中
                        </div>
                        {{ $guards->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 詳細検索モーダル -->
<div class="modal fade" id="advancedSearchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">詳細検索</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="advancedSearchForm">
                    <div class="row g-3">
                        <!-- 生年月日範囲 -->
                        <div class="col-md-6">
                            <label class="form-label">生年月日（開始）</label>
                            <input type="date" class="form-control" name="birth_date_from">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">生年月日（終了）</label>
                            <input type="date" class="form-control" name="birth_date_to">
                        </div>
                        
                        <!-- 入社日範囲 -->
                        <div class="col-md-6">
                            <label class="form-label">入社日（開始）</label>
                            <input type="date" class="form-control" name="hire_date_from">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">入社日（終了）</label>
                            <input type="date" class="form-control" name="hire_date_to">
                        </div>
                        
                        <!-- 時給範囲 -->
                        <div class="col-md-6">
                            <label class="form-label">時給（最小）</label>
                            <input type="number" class="form-control" name="hourly_rate_min" placeholder="1000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">時給（最大）</label>
                            <input type="number" class="form-control" name="hourly_rate_max" placeholder="3000">
                        </div>
                        
                        <!-- 住所 -->
                        <div class="col-12">
                            <label class="form-label">住所</label>
                            <input type="text" class="form-control" name="address" placeholder="都道府県または市区町村">
                        </div>
                        
                        <!-- スキル -->
                        <div class="col-12">
                            <label class="form-label">スキル</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="skills[]" value="patrol" id="skill_patrol">
                                        <label class="form-check-label" for="skill_patrol">巡回警備</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="skills[]" value="traffic" id="skill_traffic">
                                        <label class="form-check-label" for="skill_traffic">交通誘導</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="skills[]" value="event" id="skill_event">
                                        <label class="form-check-label" for="skill_event">イベント警備</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" onclick="applyAdvancedSearch()">検索実行</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table-sortable th[data-sort] {
        cursor: pointer;
        user-select: none;
        transition: background-color 0.2s;
    }
    
    .table-sortable th[data-sort]:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .table-sortable th.asc::after {
        content: " ↑";
        color: var(--bs-primary);
    }
    
    .table-sortable th.desc::after {
        content: " ↓";
        color: var(--bs-primary);
    }
    
    .avatar img, .avatar div {
        object-fit: cover;
    }
    
    .compact-view .table td {
        padding: 0.5rem;
        font-size: 0.875rem;
    }
    
    .compact-view .avatar img,
    .compact-view .avatar div {
        width: 30px !important;
        height: 30px !important;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // 統計情報の更新
        updateStatistics();
        
        // 検索フォーム送信
        $('#searchForm').on('submit', function(e) {
            e.preventDefault();
            performSearch();
        });
        
        // リセットボタン
        $('#resetSearch').click(function() {
            $('#searchForm')[0].reset();
            window.location.href = '{{ route("guards.index") }}';
        });
        
        // 詳細検索モーダル表示
        $('#advancedSearch').click(function() {
            $('#advancedSearchModal').modal('show');
        });
        
        // 表示件数変更
        $('#perPage').change(function() {
            updateUrlParameter('per_page', $(this).val());
        });
        
        // コンパクト表示切り替え
        $('#compactView').change(function() {
            if ($(this).is(':checked')) {
                $('#guardsTable').addClass('compact-view');
            } else {
                $('#guardsTable').removeClass('compact-view');
            }
        });
        
        // エクスポートボタン
        $('#exportGuards').click(function() {
            const params = new URLSearchParams($('#searchForm').serialize());
            window.open(`{{ route('guards.export') }}?${params.toString()}`, '_blank');
        });
    });
    
    // 検索実行
    function performSearch() {
        const formData = $('#searchForm').serialize();
        window.location.href = `{{ route('guards.index') }}?${formData}`;
    }
    
    // 詳細検索適用
    function applyAdvancedSearch() {
        const basicForm = $('#searchForm').serialize();
        const advancedForm = $('#advancedSearchForm').serialize();
        const combinedParams = basicForm + '&' + advancedForm;
        
        window.location.href = `{{ route('guards.index') }}?${combinedParams}`;
    }
    
    // 統計情報更新
    function updateStatistics() {
        $.get('{{ route("guards.stats") }}')
            .done(function(data) {
                $('#totalGuards').text(data.total || 0);
                $('#activeGuards').text(data.active || 0);
                $('#standbyGuards').text(data.standby || 0);
                $('#qualifiedGuards').text(data.qualified || 0);
            })
            .fail(function() {
                console.error('統計情報の取得に失敗しました');
            });
    }
    
    // 警備員削除
    function deleteGuard(guardId) {
        if (confirm('この警備員を削除してもよろしいですか？\n関連するシフトや勤怠情報も削除される可能性があります。')) {
            $.ajax({
                url: `{{ route('guards.destroy', '') }}/${guardId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showSuccessMessage('警備員を削除しました');
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    showErrorMessage('警備員の削除に失敗しました');
                }
            });
        }
    }
    
    // テーブルソート
    function sortTable(column, order) {
        updateUrlParameter('sort', column);
        updateUrlParameter('order', order);
    }
    
    // URLパラメータ更新
    function updateUrlParameter(param, value) {
        const url = new URL(window.location);
        url.searchParams.set(param, value);
        window.location.href = url.toString();
    }
</script>
@endpush
@endsection