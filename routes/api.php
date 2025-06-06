<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\GuardController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\WeatherController;

/*
|--------------------------------------------------------------------------
| 警備システム - API ルーティング設定
|--------------------------------------------------------------------------
|
| 警備グループ会社受注管理・シフト管理統合システムのRESTful API定義
| JSON形式でのデータ交換専用ルート
| モバイルアプリ、外部システム連携、Ajax通信に対応
|
*/

// =============================================================================
// 認証API（認証不要）
// =============================================================================

Route::prefix('auth')->name('api.auth.')->group(function () {
    // ログイン・認証
    Route::post('/login', [AuthController::class, 'apiLogin'])->name('login');
    Route::post('/register', [AuthController::class, 'apiRegister'])->name('register');
    Route::post('/password/reset', [AuthController::class, 'apiResetPassword'])->name('password.reset');
    
    // 認証必須
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'apiLogout'])->name('logout');
        Route::get('/user', [AuthController::class, 'getAuthenticatedUser'])->name('user');
        Route::post('/password/change', [AuthController::class, 'apiChangePassword'])->name('password.change');
    });
});

// =============================================================================
// 認証必須 API グループ
// =============================================================================

Route::middleware('auth:sanctum')->group(function () {
    
    // ユーザー情報
    Route::get('/user', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ]);
    });
    
    // =============================================================================
    // 統合ダッシュボード API
    // =============================================================================
    
    Route::prefix('dashboard')->name('api.dashboard.')->group(function () {
        // 基本データ取得
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/integrated-data', [DashboardController::class, 'index'])->name('integrated_data');
        
        // KPI・統計情報
        Route::get('/stats', [DashboardController::class, 'apiGetStats'])->name('stats');
        Route::get('/kpi', [DashboardController::class, 'apiGetKpi'])->name('kpi');
        Route::get('/overview', [DashboardController::class, 'apiGetOverview'])->name('overview');
        
        // 3社統合データ
        Route::get('/company-integration', [DashboardController::class, 'getCompanyIntegrationData'])->name('company_integration');
        Route::get('/company/{companyId}/performance', [DashboardController::class, 'getCompanyPerformance'])->name('company.performance');
        Route::get('/cross-company-projects', [DashboardController::class, 'getCrossCompanyProjects'])->name('cross_company_projects');
        Route::get('/shared-resources', [DashboardController::class, 'getSharedResources'])->name('shared_resources');
        
        // チャート・グラフデータ
        Route::get('/charts/data', [DashboardController::class, 'chartData'])->name('charts.data');
        Route::get('/charts/revenue-trend', [DashboardController::class, 'getRevenueTrendData'])->name('charts.revenue_trend');
        Route::get('/charts/performance', [DashboardController::class, 'getPerformanceChartData'])->name('charts.performance');
        Route::get('/charts/company-comparison', [DashboardController::class, 'getCompanyComparisonData'])->name('charts.company_comparison');
        
        // リアルタイムデータ
        Route::get('/real-time/data', [DashboardController::class, 'realTimeData'])->name('real_time.data');
        Route::get('/real-time/guard-locations', [DashboardController::class, 'getGuardLocations'])->name('real_time.guard_locations');
        Route::get('/real-time/active-shifts', [DashboardController::class, 'getActiveShifts'])->name('real_time.active_shifts');
        Route::get('/real-time/system-status', [DashboardController::class, 'getSystemStatus'])->name('real_time.system_status');
        Route::get('/real-time/security-monitoring', [DashboardController::class, 'getSecurityMonitoring'])->name('real_time.security_monitoring');
        
        // 緊急時対応
        Route::post('/emergency/alert', [DashboardController::class, 'emergencyAlert'])->name('emergency.alert');
        Route::get('/emergency/status', [DashboardController::class, 'getEmergencyStatus'])->name('emergency.status');
        Route::post('/emergency/resolve/{alertId}', [DashboardController::class, 'resolveEmergencyAlert'])->name('emergency.resolve');
        
        // アラート・通知管理
        Route::get('/alerts', [DashboardController::class, 'apiGetAlerts'])->name('alerts');
        Route::get('/alerts/critical', [DashboardController::class, 'getCriticalAlerts'])->name('alerts.critical');
        Route::get('/alerts/warnings', [DashboardController::class, 'getWarningAlerts'])->name('alerts.warnings');
        Route::post('/alerts/{id}/dismiss', [DashboardController::class, 'apiDismissAlert'])->name('alerts.dismiss');
        Route::post('/alerts/{id}/acknowledge', [DashboardController::class, 'acknowledgeAlert'])->name('alerts.acknowledge');
        
        // 高度分析・レポート
        Route::get('/analytics/advanced', [DashboardController::class, 'getAdvancedAnalytics'])->name('analytics.advanced');
        Route::get('/analytics/revenue', [DashboardController::class, 'getRevenueAnalytics'])->name('analytics.revenue');
        Route::get('/analytics/guard-performance', [DashboardController::class, 'getGuardPerformanceAnalytics'])->name('analytics.guard_performance');
        Route::get('/analytics/customer', [DashboardController::class, 'getCustomerAnalytics'])->name('analytics.customer');
        Route::get('/analytics/operational', [DashboardController::class, 'getOperationalAnalytics'])->name('analytics.operational');
        
        // 売上分析機能（詳細API）
        Route::prefix('revenue-analysis')->name('revenue_analysis.')->group(function () {
            Route::get('/', [DashboardController::class, 'revenueAnalysis'])->name('index');
            Route::get('/overview', [DashboardController::class, 'getRevenueOverviewAnalysis'])->name('overview');
            Route::get('/trend', [DashboardController::class, 'getDetailedRevenueTrendAnalysis'])->name('trend');
            Route::get('/composition', [DashboardController::class, 'getRevenueCompositionAnalysis'])->name('composition');
            Route::get('/customer', [DashboardController::class, 'getCustomerRevenueAnalysis'])->name('customer');
            Route::get('/project', [DashboardController::class, 'getProjectRevenueAnalysis'])->name('project');
            Route::get('/guard', [DashboardController::class, 'getGuardRevenueAnalysis'])->name('guard');
            Route::get('/company', [DashboardController::class, 'getCompanyRevenueAnalysis'])->name('company');
            Route::get('/forecast', [DashboardController::class, 'getRevenueForecastAnalysis'])->name('forecast');
            Route::get('/profitability', [DashboardController::class, 'getProfitabilityAnalysis'])->name('profitability');
            Route::get('/seasonality', [DashboardController::class, 'getSeasonalityAnalysis'])->name('seasonality');
            Route::get('/performance', [DashboardController::class, 'getRevenuePerformanceAnalysis'])->name('performance');
            Route::get('/export', [DashboardController::class, 'getRevenueExportData'])->name('export');
        });
        
        // 予測・トレンド分析
        Route::get('/trends/analysis', [DashboardController::class, 'getTrendAnalysis'])->name('trends.analysis');
        Route::get('/trends/market', [DashboardController::class, 'getMarketTrends'])->name('trends.market');
        Route::get('/trends/business', [DashboardController::class, 'getBusinessTrends'])->name('trends.business');
        Route::get('/trends/predictions', [DashboardController::class, 'getPredictions'])->name('trends.predictions');
        
        // セキュリティ・コンプライアンス
        Route::get('/security/compliance', [DashboardController::class, 'getSecurityComplianceData'])->name('security.compliance');
        Route::get('/security/industry-law', [DashboardController::class, 'getSecurityIndustryLawCompliance'])->name('security.industry_law');
        Route::get('/security/data-protection', [DashboardController::class, 'getDataProtectionStatus'])->name('security.data_protection');
        Route::get('/security/audit-results', [DashboardController::class, 'getAuditResults'])->name('security.audit_results');
        
        // パフォーマンス指標
        Route::get('/performance/metrics', [DashboardController::class, 'getPerformanceMetrics'])->name('performance.metrics');
        Route::get('/performance/guards', [DashboardController::class, 'getGuardPerformanceMetrics'])->name('performance.guards');
        Route::get('/performance/projects', [DashboardController::class, 'getProjectPerformanceMetrics'])->name('performance.projects');
        Route::get('/performance/financial', [DashboardController::class, 'getFinancialPerformanceMetrics'])->name('performance.financial');
        
        // カスタマイズ・設定
        Route::get('/user-context', [DashboardController::class, 'getUserContext'])->name('user_context');
        Route::post('/preferences/save', [DashboardController::class, 'saveUserPreferences'])->name('preferences.save');
        Route::get('/preferences/load', [DashboardController::class, 'loadUserPreferences'])->name('preferences.load');
        
        // エクスポート機能
        Route::get('/export/comprehensive-report', [DashboardController::class, 'exportComprehensiveReport'])->name('export.comprehensive_report');
        Route::get('/export/kpi-summary', [DashboardController::class, 'exportKpiSummary'])->name('export.kpi_summary');
        Route::get('/export/company-comparison', [DashboardController::class, 'exportCompanyComparison'])->name('export.company_comparison');
        
        // フィルタリング・期間設定
        Route::get('/filter/period/{period}', [DashboardController::class, 'getDataByPeriod'])->name('filter.period');
        Route::get('/filter/company/{companyId}', [DashboardController::class, 'getDataByCompany'])->name('filter.company');
        Route::get('/filter/date-range', [DashboardController::class, 'getDataByDateRange'])->name('filter.date_range');
    });
    
    // =============================================================================
    // 顧客管理 API
    // =============================================================================
    
    Route::prefix('customers')->name('api.customers.')->group(function () {
        // 基本CRUD
        Route::get('/', [CustomerController::class, 'apiIndex'])->name('index');
        Route::post('/', [CustomerController::class, 'apiStore'])->name('store');
        Route::get('/{customer}', [CustomerController::class, 'apiShow'])->name('show');
        Route::put('/{customer}', [CustomerController::class, 'apiUpdate'])->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'apiDestroy'])->name('destroy');
        
        // 追加機能
        Route::get('/search/query', [CustomerController::class, 'apiSearch'])->name('search');
        Route::get('/stats/overview', [CustomerController::class, 'apiGetStats'])->name('stats');
        Route::post('/{customer}/status', [CustomerController::class, 'apiUpdateStatus'])->name('status.update');
        Route::get('/filter/active', [CustomerController::class, 'apiGetActiveCustomers'])->name('filter.active');
        Route::get('/filter/by-type', [CustomerController::class, 'apiGetCustomersByType'])->name('filter.type');
    });
    
    // =============================================================================
    // 案件管理 API
    // =============================================================================
    
    Route::prefix('projects')->name('api.projects.')->group(function () {
        // 基本CRUD
        Route::get('/', [ProjectController::class, 'apiIndex'])->name('index');
        Route::post('/', [ProjectController::class, 'apiStore'])->name('store');
        Route::get('/{project}', [ProjectController::class, 'apiShow'])->name('show');
        Route::put('/{project}', [ProjectController::class, 'apiUpdate'])->name('update');
        Route::delete('/{project}', [ProjectController::class, 'apiDestroy'])->name('destroy');
        
        // 追加機能
        Route::get('/search/query', [ProjectController::class, 'apiSearch'])->name('search');
        Route::get('/stats/overview', [ProjectController::class, 'apiGetStats'])->name('stats');
        Route::post('/{project}/status', [ProjectController::class, 'apiUpdateStatus'])->name('status.update');
        Route::post('/{project}/guards/assign', [ProjectController::class, 'apiAssignGuards'])->name('guards.assign');
        Route::delete('/{project}/guards/{guard}', [ProjectController::class, 'apiRemoveGuard'])->name('guards.remove');
        Route::get('/filter/by-status', [ProjectController::class, 'apiGetProjectsByStatus'])->name('filter.status');
        Route::get('/filter/by-customer/{customer}', [ProjectController::class, 'apiGetProjectsByCustomer'])->name('filter.customer');
        
        // Google Maps API機能
        Route::get('/map/view', [ProjectController::class, 'mapView'])->name('map.view');
        Route::get('/map/data', [ProjectController::class, 'mapView'])->name('map.data');
        Route::post('/{project}/location/update', [ProjectController::class, 'updateLocation'])->name('location.update');
        Route::get('/{project}/guards/nearby', [ProjectController::class, 'findNearbyGuards'])->name('guards.nearby');
        Route::post('/{project}/placement/optimal', [ProjectController::class, 'calculateOptimalPlacement'])->name('placement.optimal');
        Route::post('/routes/multi-project', [ProjectController::class, 'optimizeMultiProjectRoute'])->name('routes.multi_project');
        Route::get('/{project}/area/monitor', [ProjectController::class, 'monitorGuardsInArea'])->name('area.monitor');
        
        // 位置情報管理API
        Route::get('/locations/all', function() {
            return response()->json([
                'status' => 'success',
                'data' => Project::whereNotNull('location_lat')
                    ->whereNotNull('location_lng')
                    ->with(['customer'])
                    ->get()
                    ->map(function($project) {
                        return $project->map_info;
                    })
            ]);
        })->name('locations.all');
        
        Route::get('/locations/active', function() {
            return response()->json([
                'status' => 'success',
                'data' => Project::where('status', 'active')
                    ->whereNotNull('location_lat')
                    ->whereNotNull('location_lng')
                    ->with(['customer'])
                    ->get()
                    ->map(function($project) {
                        return $project->map_info;
                    })
            ]);
        })->name('locations.active');
    });
    
    // =============================================================================
    // 警備員管理 API
    // =============================================================================
    
    Route::prefix('guards')->name('api.guards.')->group(function () {
        // 基本CRUD
        Route::get('/', [GuardController::class, 'apiIndex'])->name('index');
        Route::post('/', [GuardController::class, 'apiStore'])->name('store');
        Route::get('/{guard}', [GuardController::class, 'apiShow'])->name('show');
        Route::put('/{guard}', [GuardController::class, 'apiUpdate'])->name('update');
        Route::delete('/{guard}', [GuardController::class, 'apiDestroy'])->name('destroy');
        
        // 追加機能
        Route::get('/search/query', [GuardController::class, 'apiSearch'])->name('search');
        Route::get('/stats/overview', [GuardController::class, 'apiGetStats'])->name('stats');
        Route::post('/{guard}/status', [GuardController::class, 'apiUpdateStatus'])->name('status.update');
        Route::post('/{guard}/skills', [GuardController::class, 'apiUpdateSkills'])->name('skills.update');
        Route::post('/{guard}/qualifications', [GuardController::class, 'apiUpdateQualifications'])->name('qualifications.update');
        Route::post('/{guard}/salary', [GuardController::class, 'apiUpdateSalary'])->name('salary.update');
        Route::get('/{guard}/performance', [GuardController::class, 'apiGetPerformance'])->name('performance');
        Route::get('/filter/available', [GuardController::class, 'apiGetAvailableGuards'])->name('filter.available');
        Route::get('/filter/by-skills', [GuardController::class, 'apiGetGuardsBySkills'])->name('filter.skills');
        Route::get('/filter/by-qualifications', [GuardController::class, 'apiGetGuardsByQualifications'])->name('filter.qualifications');
        
        // Google Maps API機能
        Route::get('/map/view', [GuardController::class, 'mapView'])->name('map.view');
        Route::get('/map/data', [GuardController::class, 'mapView'])->name('map.data');
        Route::post('/{guard}/location/update', [GuardController::class, 'updateLocation'])->name('location.update');
        Route::get('/{guard}/location/history', [GuardController::class, 'getLocationHistory'])->name('location.history');
        Route::get('/{guard}/projects/nearby', [GuardController::class, 'findNearbyProjects'])->name('projects.nearby');
        Route::post('/routes/optimize', [GuardController::class, 'calculateOptimizedRoute'])->name('routes.optimize');
        Route::post('/distance/calculate', [GuardController::class, 'calculateDistance'])->name('distance.calculate');
        Route::post('/geocoding/address', [GuardController::class, 'geocodeAddress'])->name('geocoding.address');
        Route::post('/geocoding/reverse', [GuardController::class, 'reverseGeocode'])->name('geocoding.reverse');
        
        // 位置情報管理API
        Route::get('/locations/active', function() {
            return response()->json([
                'status' => 'success',
                'data' => Guard::where('status', 'active')
                    ->whereNotNull('location_lat')
                    ->whereNotNull('location_lng')
                    ->where('location_sharing_enabled', true)
                    ->with(['user', 'company'])
                    ->get()
                    ->map(function($guard) {
                        return $guard->map_info;
                    })
            ]);
        })->name('locations.active');
        
        Route::get('/locations/all', function() {
            return response()->json([
                'status' => 'success', 
                'data' => Guard::whereNotNull('location_lat')
                    ->whereNotNull('location_lng')
                    ->with(['user', 'company'])
                    ->get()
                    ->map(function($guard) {
                        return $guard->map_info;
                    })
            ]);
        })->name('locations.all');
    });
    
    // =============================================================================
    // シフト管理 API
    // =============================================================================
    
    Route::prefix('shifts')->name('api.shifts.')->group(function () {
        // 基本CRUD
        Route::get('/', [ShiftController::class, 'apiIndex'])->name('index');
        Route::post('/', [ShiftController::class, 'apiStore'])->name('store');
        Route::get('/{shift}', [ShiftController::class, 'apiShow'])->name('show');
        Route::put('/{shift}', [ShiftController::class, 'apiUpdate'])->name('update');
        Route::delete('/{shift}', [ShiftController::class, 'apiDestroy'])->name('destroy');
        
        // 追加機能
        Route::get('/search/query', [ShiftController::class, 'apiSearch'])->name('search');
        Route::get('/stats/overview', [ShiftController::class, 'apiGetStats'])->name('stats');
        Route::post('/{shift}/guards/assign', [ShiftController::class, 'apiAssignGuards'])->name('guards.assign');
        Route::delete('/{shift}/guards/{guard}', [ShiftController::class, 'apiRemoveGuard'])->name('guards.remove');
        Route::post('/{shift}/optimize', [ShiftController::class, 'apiOptimizeAssignments'])->name('optimize');
        
        // カレンダー・日程管理
        Route::get('/calendar/data', [ShiftController::class, 'apiGetCalendarData'])->name('calendar.data');
        Route::get('/calendar/month/{year}/{month}', [ShiftController::class, 'apiGetMonthlyShifts'])->name('calendar.month');
        Route::get('/calendar/week/{year}/{week}', [ShiftController::class, 'apiGetWeeklyShifts'])->name('calendar.week');
        Route::get('/calendar/day/{date}', [ShiftController::class, 'apiGetDailyShifts'])->name('calendar.day');
        
        // フィルタリング
        Route::get('/filter/by-project/{project}', [ShiftController::class, 'apiGetShiftsByProject'])->name('filter.project');
        Route::get('/filter/by-guard/{guard}', [ShiftController::class, 'apiGetShiftsByGuard'])->name('filter.guard');
        Route::get('/filter/by-date-range', [ShiftController::class, 'apiGetShiftsByDateRange'])->name('filter.date_range');
    });
    
    // =============================================================================
    // 勤怠管理 API
    // =============================================================================
    
    Route::prefix('attendances')->name('api.attendances.')->group(function () {
        // 基本CRUD
        Route::get('/', [AttendanceController::class, 'apiIndex'])->name('index');
        Route::post('/', [AttendanceController::class, 'apiStore'])->name('store');
        Route::get('/{attendance}', [AttendanceController::class, 'apiShow'])->name('show');
        Route::put('/{attendance}', [AttendanceController::class, 'apiUpdate'])->name('update');
        Route::delete('/{attendance}', [AttendanceController::class, 'apiDestroy'])->name('destroy');
        
        // 追加機能
        Route::get('/search/query', [AttendanceController::class, 'apiSearch'])->name('search');
        Route::get('/stats/overview', [AttendanceController::class, 'apiGetStats'])->name('stats');
        Route::post('/{attendance}/approve', [AttendanceController::class, 'apiApprove'])->name('approve');
        Route::post('/{attendance}/reject', [AttendanceController::class, 'apiReject'])->name('reject');
        
        // 打刻機能
        Route::post('/clock-in', [AttendanceController::class, 'apiClockIn'])->name('clock.in');
        Route::post('/clock-out', [AttendanceController::class, 'apiClockOut'])->name('clock.out');
        Route::get('/clock-status', [AttendanceController::class, 'apiGetClockStatus'])->name('clock.status');
        
        // レポート機能
        Route::get('/reports/generate', [AttendanceController::class, 'apiGenerateReport'])->name('reports.generate');
        Route::get('/reports/export', [AttendanceController::class, 'apiExportReport'])->name('reports.export');
        Route::get('/reports/monthly/{year}/{month}', [AttendanceController::class, 'apiGetMonthlyReport'])->name('reports.monthly');
        Route::get('/reports/guard/{guard}', [AttendanceController::class, 'apiGetGuardReport'])->name('reports.guard');
    });
    
    // =============================================================================
    // 見積管理 API
    // =============================================================================
    
    Route::prefix('quotations')->name('api.quotations.')->group(function () {
        // 基本CRUD
        Route::get('/', [QuotationController::class, 'apiIndex'])->name('index');
        Route::post('/', [QuotationController::class, 'apiStore'])->name('store');
        Route::get('/{quotation}', [QuotationController::class, 'apiShow'])->name('show');
        Route::put('/{quotation}', [QuotationController::class, 'apiUpdate'])->name('update');
        Route::delete('/{quotation}', [QuotationController::class, 'apiDestroy'])->name('destroy');
        
        // 追加機能
        Route::get('/search/query', [QuotationController::class, 'apiSearch'])->name('search');
        Route::get('/stats/overview', [QuotationController::class, 'apiGetStats'])->name('stats');
        Route::post('/{quotation}/approve', [QuotationController::class, 'apiApprove'])->name('approve');
        Route::post('/{quotation}/reject', [QuotationController::class, 'apiReject'])->name('reject');
        Route::post('/{quotation}/submit', [QuotationController::class, 'apiSubmit'])->name('submit');
        Route::post('/{quotation}/duplicate', [QuotationController::class, 'apiDuplicate'])->name('duplicate');
        
        // テンプレート管理
        Route::get('/templates/list', [QuotationController::class, 'apiGetTemplates'])->name('templates');
        Route::post('/templates/save', [QuotationController::class, 'apiSaveTemplate'])->name('templates.save');
        Route::delete('/templates/{template}', [QuotationController::class, 'apiDeleteTemplate'])->name('templates.delete');
        
        // フィルタリング
        Route::get('/filter/by-status', [QuotationController::class, 'apiGetQuotationsByStatus'])->name('filter.status');
        Route::get('/filter/by-customer/{customer}', [QuotationController::class, 'apiGetQuotationsByCustomer'])->name('filter.customer');
    });
    
    // =============================================================================
    // 契約管理 API
    // =============================================================================
    
    Route::prefix('contracts')->name('api.contracts.')->group(function () {
        // 基本CRUD
        Route::get('/', [ContractController::class, 'apiIndex'])->name('index');
        Route::post('/', [ContractController::class, 'apiStore'])->name('store');
        Route::get('/{contract}', [ContractController::class, 'apiShow'])->name('show');
        Route::put('/{contract}', [ContractController::class, 'apiUpdate'])->name('update');
        Route::delete('/{contract}', [ContractController::class, 'apiDestroy'])->name('destroy');
        
        // 追加機能
        Route::get('/search/query', [ContractController::class, 'apiSearch'])->name('search');
        Route::get('/stats/overview', [ContractController::class, 'apiGetStats'])->name('stats');
        Route::post('/{contract}/approve', [ContractController::class, 'apiApprove'])->name('approve');
        Route::post('/{contract}/reject', [ContractController::class, 'apiReject'])->name('reject');
        Route::post('/{contract}/activate', [ContractController::class, 'apiActivate'])->name('activate');
        Route::post('/{contract}/renew', [ContractController::class, 'apiRenew'])->name('renew');
        Route::post('/{contract}/terminate', [ContractController::class, 'apiTerminate'])->name('terminate');
        
        // アラート・通知管理
        Route::get('/alerts/expiring', [ContractController::class, 'apiGetExpiringContracts'])->name('alerts.expiring');
        Route::get('/alerts/renewal', [ContractController::class, 'apiGetRenewalAlerts'])->name('alerts.renewal');
        Route::get('/alerts/termination', [ContractController::class, 'apiGetTerminationAlerts'])->name('alerts.termination');
        
        // フィルタリング
        Route::get('/filter/by-status', [ContractController::class, 'apiGetContractsByStatus'])->name('filter.status');
        Route::get('/filter/by-customer/{customer}', [ContractController::class, 'apiGetContractsByCustomer'])->name('filter.customer');
        Route::get('/filter/expiring-soon', [ContractController::class, 'apiGetExpiringSoonContracts'])->name('filter.expiring_soon');
    });
    
    // =============================================================================
    // 請求管理 API
    // =============================================================================
    
    Route::prefix('invoices')->name('api.invoices.')->group(function () {
        // 基本CRUD
        Route::get('/', [InvoiceController::class, 'apiIndex'])->name('index');
        Route::post('/', [InvoiceController::class, 'apiStore'])->name('store');
        Route::get('/{invoice}', [InvoiceController::class, 'apiShow'])->name('show');
        Route::put('/{invoice}', [InvoiceController::class, 'apiUpdate'])->name('update');
        Route::delete('/{invoice}', [InvoiceController::class, 'apiDestroy'])->name('destroy');
        
        // 追加機能
        Route::get('/search/query', [InvoiceController::class, 'apiSearch'])->name('search');
        Route::get('/stats/overview', [InvoiceController::class, 'apiGetStats'])->name('stats');
        Route::post('/{invoice}/send', [InvoiceController::class, 'apiSend'])->name('send');
        Route::post('/{invoice}/payment', [InvoiceController::class, 'apiRecordPayment'])->name('payment.record');
        
        // レポート機能
        Route::get('/reports/revenue', [InvoiceController::class, 'apiGetRevenueReport'])->name('reports.revenue');
        Route::get('/reports/outstanding', [InvoiceController::class, 'apiGetOutstandingReport'])->name('reports.outstanding');
        Route::get('/reports/monthly/{year}/{month}', [InvoiceController::class, 'apiGetMonthlyRevenue'])->name('reports.monthly');
        Route::get('/reports/customer/{customer}', [InvoiceController::class, 'apiGetCustomerInvoices'])->name('reports.customer');
        
        // 売掛金管理
        Route::get('/receivables/aging', [InvoiceController::class, 'apiGetAgingReport'])->name('receivables.aging');
        Route::get('/receivables/overdue', [InvoiceController::class, 'apiGetOverdueInvoices'])->name('receivables.overdue');
        Route::get('/receivables/summary', [InvoiceController::class, 'apiGetReceivablesSummary'])->name('receivables.summary');
        
        // フィルタリング
        Route::get('/filter/by-status', [InvoiceController::class, 'apiGetInvoicesByStatus'])->name('filter.status');
        Route::get('/filter/by-customer/{customer}', [InvoiceController::class, 'apiGetInvoicesByCustomer'])->name('filter.customer');
        Route::get('/filter/overdue', [InvoiceController::class, 'apiGetOverdueInvoices'])->name('filter.overdue');
    });
    
    // =============================================================================
    // 日報管理 API
    // =============================================================================
    
    Route::prefix('daily-reports')->name('api.daily_reports.')->group(function () {
        // 基本CRUD
        Route::get('/', [DailyReportController::class, 'apiIndex'])->name('index');
        Route::post('/', [DailyReportController::class, 'apiStore'])->name('store');
        Route::get('/{daily_report}', [DailyReportController::class, 'apiShow'])->name('show');
        Route::put('/{daily_report}', [DailyReportController::class, 'apiUpdate'])->name('update');
        Route::delete('/{daily_report}', [DailyReportController::class, 'apiDestroy'])->name('destroy');
        
        // 追加機能
        Route::get('/search/query', [DailyReportController::class, 'apiSearch'])->name('search');
        Route::get('/stats/overview', [DailyReportController::class, 'apiGetStats'])->name('stats');
        Route::post('/{daily_report}/approve', [DailyReportController::class, 'apiApprove'])->name('approve');
        Route::post('/{daily_report}/reject', [DailyReportController::class, 'apiReject'])->name('reject');
        
        // 品質分析
        Route::get('/quality/analysis', [DailyReportController::class, 'apiGetQualityAnalysis'])->name('quality.analysis');
        Route::get('/quality/trends', [DailyReportController::class, 'apiGetQualityTrends'])->name('quality.trends');
        Route::get('/quality/guard/{guard}', [DailyReportController::class, 'apiGetGuardQuality'])->name('quality.guard');
        
        // テンプレート管理
        Route::get('/templates/list', [DailyReportController::class, 'apiGetTemplates'])->name('templates');
        Route::post('/templates/save', [DailyReportController::class, 'apiSaveTemplate'])->name('templates.save');
        Route::delete('/templates/{template}', [DailyReportController::class, 'apiDeleteTemplate'])->name('templates.delete');
        
        // フィルタリング・レポート
        Route::get('/filter/by-project/{project}', [DailyReportController::class, 'apiGetReportsByProject'])->name('filter.project');
        Route::get('/filter/by-guard/{guard}', [DailyReportController::class, 'apiGetReportsByGuard'])->name('filter.guard');
        Route::get('/filter/by-date-range', [DailyReportController::class, 'apiGetReportsByDateRange'])->name('filter.date_range');
        Route::get('/reports/monthly/{year}/{month}', [DailyReportController::class, 'apiGetMonthlyReports'])->name('reports.monthly');
    });
    
    // =============================================================================
    // 天気予報管理 API
    // =============================================================================
    
    Route::prefix('weather')->name('api.weather.')->group(function () {
        // 基本データ取得
        Route::get('/', [WeatherController::class, 'index'])->name('index');
        Route::get('/dashboard', [WeatherController::class, 'dashboard'])->name('dashboard');
        Route::get('/{weather}', [WeatherController::class, 'show'])->name('show');
        
        // 天気情報更新・取得機能
        Route::post('/update', [WeatherController::class, 'updateWeather'])->name('update');
        Route::post('/update-manual', [WeatherController::class, 'updateWeather'])->name('update.manual');
        Route::post('/update-all-locations', [WeatherController::class, 'updateAllSecurityLocations'])->name('update.all_locations');
        Route::post('/refresh-current', [WeatherController::class, 'updateAllSecurityLocations'])->name('refresh.current');
        
        // 天気予報・統計機能
        Route::get('/forecast/location', [WeatherController::class, 'getLocationForecast'])->name('forecast.location');
        Route::get('/forecast/{location}/{days?}', [WeatherController::class, 'getLocationForecast'])->name('forecast.get');
        Route::get('/current/location/{location}', function($location) {
            $weather = \App\Models\Weather::getLatestByLocation($location);
            return response()->json([
                'status' => 'success',
                'data' => $weather
            ]);
        })->name('current.location');
        
        // 天気アラート・警報機能
        Route::get('/alerts', [WeatherController::class, 'getWeatherAlerts'])->name('alerts');
        Route::get('/alerts/active', [WeatherController::class, 'getWeatherAlerts'])->name('alerts.active');
        Route::get('/alerts/critical', function() {
            $alerts = \App\Models\Weather::whereIn('weather_risk_level', ['critical'])
                ->where('weather_date', '>=', now())
                ->orderBy('weather_date')
                ->get();
            return response()->json(['status' => 'success', 'data' => $alerts]);
        })->name('alerts.critical');
        Route::get('/alerts/high-risk', function() {
            $alerts = \App\Models\Weather::whereIn('weather_risk_level', ['high', 'critical'])
                ->where('weather_date', '>=', now())
                ->orderBy('weather_date')
                ->get();
            return response()->json(['status' => 'success', 'data' => $alerts]);
        })->name('alerts.high_risk');
        
        // 統計・分析API
        Route::get('/stats', [WeatherController::class, 'getWeatherStatsApi'])->name('stats');
        Route::get('/stats/weekly', function() {
            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();
            $data = \App\Models\Weather::whereBetween('weather_date', [$startDate, $endDate])->get();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'avg_temperature' => round($data->avg('temperature'), 1),
                    'total_rainfall' => round($data->sum('rain_1h'), 1),
                    'high_risk_count' => $data->whereIn('weather_risk_level', ['high', 'critical'])->count(),
                    'outdoor_suitable_count' => $data->where('outdoor_work_suitable', true)->count(),
                ]
            ]);
        })->name('stats.weekly');
        Route::get('/stats/monthly', function() {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
            $data = \App\Models\Weather::whereBetween('weather_date', [$startDate, $endDate])->get();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'avg_temperature' => round($data->avg('temperature'), 1),
                    'max_temperature' => $data->max('temperature'),
                    'min_temperature' => $data->min('temperature'),
                    'total_rainfall' => round($data->sum('rain_1h'), 1),
                    'high_risk_days' => $data->whereIn('weather_risk_level', ['high', 'critical'])->unique('weather_date')->count(),
                    'weather_distribution' => $data->groupBy('weather_main')->map->count(),
                ]
            ]);
        })->name('stats.monthly');
        
        // チャート・トレンドデータ
        Route::get('/trend-data', function() {
            $data = \App\Models\Weather::where('weather_date', '>=', now()->subDays(7))
                ->orderBy('weather_date')
                ->get()
                ->groupBy(function($item) {
                    return $item->weather_date->format('Y-m-d');
                });
            
            $labels = [];
            $temperatures = [];
            $rainfall = [];
            
            foreach($data as $date => $dayData) {
                $labels[] = \Carbon\Carbon::parse($date)->format('m/d');
                $temperatures[] = round($dayData->avg('temperature'), 1);
                $rainfall[] = round($dayData->sum('rain_1h'), 1);
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'labels' => $labels,
                    'temperatures' => $temperatures,
                    'rainfall' => $rainfall,
                ]
            ]);
        })->name('chart.trend');
        
        // 地点別天気情報
        Route::get('/locations/all', function() {
            $locations = \App\Models\Weather::select('location_name')
                ->distinct()
                ->orderBy('location_name')
                ->pluck('location_name');
            
            $locationData = [];
            foreach($locations as $location) {
                $latest = \App\Models\Weather::getLatestByLocation($location);
                if($latest) {
                    $locationData[] = [
                        'location' => $location,
                        'latest' => $latest,
                        'stats' => \App\Models\Weather::getWeatherStats($location, 7)
                    ];
                }
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $locationData
            ]);
        })->name('locations.all');
        
        Route::get('/locations/summary', function() {
            $locations = \App\Models\Weather::select('location_name')
                ->distinct()
                ->orderBy('location_name')
                ->pluck('location_name');
            
            $summary = [];
            foreach($locations as $location) {
                $latest = \App\Models\Weather::getLatestByLocation($location);
                if($latest) {
                    $summary[$location] = [
                        'temperature' => $latest->temperature,
                        'weather_main' => $latest->weather_main,
                        'weather_icon' => $latest->weather_icon,
                        'risk_level' => $latest->weather_risk_level,
                        'outdoor_suitable' => $latest->outdoor_work_suitable,
                        'last_updated' => $latest->weather_date->toISOString(),
                    ];
                }
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $summary
            ]);
        })->name('locations.summary');
        
        // データエクスポート・管理機能
        Route::get('/export', [WeatherController::class, 'exportWeatherData'])->name('export');
        Route::post('/cleanup', [WeatherController::class, 'cleanupOldData'])->name('cleanup');
        
        // 業務影響分析
        Route::get('/security-impact/{weather}', function(\App\Models\Weather $weather) {
            return response()->json([
                'status' => 'success',
                'data' => $weather->calculateSecurityImpact()
            ]);
        })->name('security.impact');
        
        Route::get('/security-impact/current', function() {
            $currentWeather = \App\Models\Weather::where('data_type', 'current')
                ->where('weather_date', '>=', now()->subHours(3))
                ->get();
            
            $impacts = [];
            foreach($currentWeather as $weather) {
                $impacts[] = [
                    'location' => $weather->location_name,
                    'impact' => $weather->calculateSecurityImpact(),
                    'weather' => $weather
                ];
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $impacts
            ]);
        })->name('security.impact.current');
        
        // 高度な検索・フィルタリング
        Route::get('/search', function(\Illuminate\Http\Request $request) {
            $query = \App\Models\Weather::query();
            
            if($request->has('location')) {
                $query->where('location_name', 'LIKE', '%' . $request->location . '%');
            }
            
            if($request->has('risk_level')) {
                $query->where('weather_risk_level', $request->risk_level);
            }
            
            if($request->has('date_from')) {
                $query->whereDate('weather_date', '>=', $request->date_from);
            }
            
            if($request->has('date_to')) {
                $query->whereDate('weather_date', '<=', $request->date_to);
            }
            
            if($request->has('weather_main')) {
                $query->where('weather_main', $request->weather_main);
            }
            
            if($request->has('outdoor_suitable')) {
                $query->where('outdoor_work_suitable', $request->boolean('outdoor_suitable'));
            }
            
            $results = $query->orderBy('weather_date', 'desc')
                ->paginate($request->get('per_page', 15));
            
            return response()->json([
                'status' => 'success',
                'data' => $results
            ]);
        })->name('search');
    });
    
    // =============================================================================
    // システム管理・設定 API
    // =============================================================================
    
    Route::prefix('system')->name('api.system.')->group(function () {
        // システム情報
        Route::get('/info', function () {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'app_name' => config('app.name'),
                    'version' => '1.0.0',
                    'environment' => config('app.env'),
                    'timezone' => config('app.timezone'),
                    'locale' => config('app.locale'),
                    'server_time' => now()->toISOString(),
                ]
            ]);
        })->name('info');
        
        // ヘルスチェック
        Route::get('/health', function () {
            return response()->json([
                'status' => 'success',
                'message' => 'API is healthy',
                'timestamp' => now()->toISOString(),
                'uptime' => 'OK'
            ]);
        })->name('health');
        
        // API バージョン情報
        Route::get('/version', function () {
            return response()->json([
                'api_version' => 'v1.0',
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
                'database' => 'MySQL'
            ]);
        })->name('version');
    });
});

// =============================================================================
// API エラーハンドリング
// =============================================================================

// 404 Not Found for API routes
Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'API endpoint not found',
        'code' => 404
    ], 404);
});
