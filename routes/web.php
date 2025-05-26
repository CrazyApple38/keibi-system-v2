<?php

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

/*
|--------------------------------------------------------------------------
| 警備システム - Webルーティング設定
|--------------------------------------------------------------------------
|
| 警備グループ会社受注管理・シフト管理統合システムのWebルーティング定義
| 全機能へのアクセスルートを体系的に管理
|
*/

// =============================================================================
// パブリックルート（認証不要）
// =============================================================================

// ウェルカムページ
Route::get('/', function () {
    return view('welcome');
})->name('home');

// 認証関連ルート
Route::prefix('auth')->name('auth.')->group(function () {
    // ログイン
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    
    // ユーザー登録
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register.form');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    
    // パスワードリセット
    Route::get('/password/reset', [AuthController::class, 'showPasswordResetForm'])->name('password.reset.form');
    Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');
});

// =============================================================================
// 認証必須ルート
// =============================================================================

Route::middleware(['auth'])->group(function () {
    
    // ログアウト
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    
    // パスワード変更
    Route::get('/auth/password/change', [AuthController::class, 'showPasswordChangeForm'])->name('auth.password.change.form');
    Route::post('/auth/password/change', [AuthController::class, 'changePassword'])->name('auth.password.change');
    
    // =============================================================================
    // ダッシュボード
    // =============================================================================
    
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/stats', [DashboardController::class, 'getStats'])->name('stats');
        Route::get('/kpi', [DashboardController::class, 'getKpi'])->name('kpi');
        Route::get('/alerts', [DashboardController::class, 'getAlerts'])->name('alerts');
        Route::post('/alerts/{id}/dismiss', [DashboardController::class, 'dismissAlert'])->name('alerts.dismiss');
    });
    
    // =============================================================================
    // 顧客管理
    // =============================================================================
    
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
        
        // 顧客統計・フィルタリング
        Route::get('/stats/overview', [CustomerController::class, 'getStats'])->name('stats');
        Route::get('/filter/search', [CustomerController::class, 'search'])->name('search');
        Route::post('/{customer}/status', [CustomerController::class, 'updateStatus'])->name('status.update');
    });
    
    // =============================================================================
    // 案件管理
    // =============================================================================
    
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('/create', [ProjectController::class, 'create'])->name('create');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');
        
        // 案件統計・管理機能
        Route::get('/stats/overview', [ProjectController::class, 'getStats'])->name('stats');
        Route::get('/filter/search', [ProjectController::class, 'search'])->name('search');
        Route::post('/{project}/status', [ProjectController::class, 'updateStatus'])->name('status.update');
        Route::post('/{project}/guards/assign', [ProjectController::class, 'assignGuards'])->name('guards.assign');
        Route::delete('/{project}/guards/{guard}', [ProjectController::class, 'removeGuard'])->name('guards.remove');
    });
    
    // =============================================================================
    // 警備員管理
    // =============================================================================
    
    Route::prefix('guards')->name('guards.')->group(function () {
        Route::get('/', [GuardController::class, 'index'])->name('index');
        Route::get('/create', [GuardController::class, 'create'])->name('create');
        Route::post('/', [GuardController::class, 'store'])->name('store');
        Route::get('/{guard}', [GuardController::class, 'show'])->name('show');
        Route::get('/{guard}/edit', [GuardController::class, 'edit'])->name('edit');
        Route::put('/{guard}', [GuardController::class, 'update'])->name('update');
        Route::delete('/{guard}', [GuardController::class, 'destroy'])->name('destroy');
        
        // 警備員統計・管理機能
        Route::get('/stats/overview', [GuardController::class, 'getStats'])->name('stats');
        Route::get('/filter/search', [GuardController::class, 'search'])->name('search');
        Route::post('/{guard}/status', [GuardController::class, 'updateStatus'])->name('status.update');
        Route::post('/{guard}/skills', [GuardController::class, 'updateSkills'])->name('skills.update');
        Route::post('/{guard}/qualifications', [GuardController::class, 'updateQualifications'])->name('qualifications.update');
        Route::post('/{guard}/salary', [GuardController::class, 'updateSalary'])->name('salary.update');
        Route::get('/{guard}/performance', [GuardController::class, 'getPerformance'])->name('performance');
    });
    
    // =============================================================================
    // シフト管理
    // =============================================================================
    
    Route::prefix('shifts')->name('shifts.')->group(function () {
        Route::get('/', [ShiftController::class, 'index'])->name('index');
        Route::get('/create', [ShiftController::class, 'create'])->name('create');
        Route::post('/', [ShiftController::class, 'store'])->name('store');
        Route::get('/{shift}', [ShiftController::class, 'show'])->name('show');
        Route::get('/{shift}/edit', [ShiftController::class, 'edit'])->name('edit');
        Route::put('/{shift}', [ShiftController::class, 'update'])->name('update');
        Route::delete('/{shift}', [ShiftController::class, 'destroy'])->name('destroy');
        
        // シフト統計・管理機能
        Route::get('/stats/overview', [ShiftController::class, 'getStats'])->name('stats');
        Route::get('/filter/search', [ShiftController::class, 'search'])->name('search');
        Route::post('/{shift}/guards/assign', [ShiftController::class, 'assignGuards'])->name('guards.assign');
        Route::delete('/{shift}/guards/{guard}', [ShiftController::class, 'removeGuard'])->name('guards.remove');
        Route::post('/{shift}/optimize', [ShiftController::class, 'optimizeAssignments'])->name('optimize');
        
        // カレンダー表示
        Route::get('/calendar/view', [ShiftController::class, 'calendarView'])->name('calendar');
        Route::get('/calendar/data', [ShiftController::class, 'getCalendarData'])->name('calendar.data');
        Route::get('/calendar/stats', [ShiftController::class, 'getCalendarStats'])->name('calendar.stats');
        Route::get('/calendar/export', [ShiftController::class, 'exportCalendarData'])->name('calendar.export');
        
        // 定期シフト作成
        Route::get('/create/recurring', [ShiftController::class, 'createRecurring'])->name('create.recurring');
        
        // Ajax更新機能
        Route::post('/{shift}/update/date', [ShiftController::class, 'updateDate'])->name('update.date');
        Route::post('/{shift}/update/time', [ShiftController::class, 'updateTime'])->name('update.time');
    });
    
    // =============================================================================
    // 勤怠管理
    // =============================================================================
    
    Route::prefix('attendances')->name('attendances.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/create', [AttendanceController::class, 'create'])->name('create');
        Route::post('/', [AttendanceController::class, 'store'])->name('store');
        Route::get('/{attendance}', [AttendanceController::class, 'show'])->name('show');
        Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('edit');
        Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
        Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
        
        // 勤怠統計・管理機能
        Route::get('/stats/overview', [AttendanceController::class, 'getStats'])->name('stats');
        Route::get('/filter/search', [AttendanceController::class, 'search'])->name('search');
        Route::post('/{attendance}/approve', [AttendanceController::class, 'approve'])->name('approve');
        Route::post('/{attendance}/reject', [AttendanceController::class, 'reject'])->name('reject');
        Route::get('/reports/generate', [AttendanceController::class, 'generateReport'])->name('reports.generate');
        Route::get('/reports/export', [AttendanceController::class, 'exportReport'])->name('reports.export');
        
        // 打刻機能
        Route::post('/clock-in', [AttendanceController::class, 'clockIn'])->name('clock.in');
        Route::post('/clock-out', [AttendanceController::class, 'clockOut'])->name('clock.out');
    });
    
    // =============================================================================
    // 見積管理
    // =============================================================================
    
    Route::prefix('quotations')->name('quotations.')->group(function () {
        Route::get('/', [QuotationController::class, 'index'])->name('index');
        Route::get('/create', [QuotationController::class, 'create'])->name('create');
        Route::post('/', [QuotationController::class, 'store'])->name('store');
        Route::get('/{quotation}', [QuotationController::class, 'show'])->name('show');
        Route::get('/{quotation}/edit', [QuotationController::class, 'edit'])->name('edit');
        Route::put('/{quotation}', [QuotationController::class, 'update'])->name('update');
        Route::delete('/{quotation}', [QuotationController::class, 'destroy'])->name('destroy');
        
        // 見積統計・管理機能
        Route::get('/stats/overview', [QuotationController::class, 'getStats'])->name('stats');
        Route::get('/filter/search', [QuotationController::class, 'search'])->name('search');
        Route::post('/{quotation}/approve', [QuotationController::class, 'approve'])->name('approve');
        Route::post('/{quotation}/reject', [QuotationController::class, 'reject'])->name('reject');
        Route::post('/{quotation}/submit', [QuotationController::class, 'submit'])->name('submit');
        Route::post('/{quotation}/duplicate', [QuotationController::class, 'duplicate'])->name('duplicate');
        
        // テンプレート管理
        Route::get('/templates/list', [QuotationController::class, 'getTemplates'])->name('templates');
        Route::post('/templates/save', [QuotationController::class, 'saveTemplate'])->name('templates.save');
    });
    
    // =============================================================================
    // 契約管理
    // =============================================================================
    
    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::get('/create', [ContractController::class, 'create'])->name('create');
        Route::post('/', [ContractController::class, 'store'])->name('store');
        Route::get('/{contract}', [ContractController::class, 'show'])->name('show');
        Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('edit');
        Route::put('/{contract}', [ContractController::class, 'update'])->name('update');
        Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('destroy');
        
        // 契約統計・管理機能
        Route::get('/stats/overview', [ContractController::class, 'getStats'])->name('stats');
        Route::get('/filter/search', [ContractController::class, 'search'])->name('search');
        Route::post('/{contract}/approve', [ContractController::class, 'approve'])->name('approve');
        Route::post('/{contract}/reject', [ContractController::class, 'reject'])->name('reject');
        Route::post('/{contract}/activate', [ContractController::class, 'activate'])->name('activate');
        Route::post('/{contract}/renew', [ContractController::class, 'renew'])->name('renew');
        Route::post('/{contract}/terminate', [ContractController::class, 'terminate'])->name('terminate');
        
        // アラート管理
        Route::get('/alerts/expiring', [ContractController::class, 'getExpiringContracts'])->name('alerts.expiring');
        Route::get('/alerts/renewal', [ContractController::class, 'getRenewalAlerts'])->name('alerts.renewal');
    });
    
    // =============================================================================
    // 請求管理
    // =============================================================================
    
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
        
        // 請求統計・管理機能
        Route::get('/stats/overview', [InvoiceController::class, 'getStats'])->name('stats');
        Route::get('/filter/search', [InvoiceController::class, 'search'])->name('search');
        Route::post('/{invoice}/send', [InvoiceController::class, 'send'])->name('send');
        Route::post('/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('payment.record');
        Route::get('/reports/revenue', [InvoiceController::class, 'getRevenueReport'])->name('reports.revenue');
        Route::get('/reports/outstanding', [InvoiceController::class, 'getOutstandingReport'])->name('reports.outstanding');
        
        // 売掛金管理
        Route::get('/receivables/aging', [InvoiceController::class, 'getAgingReport'])->name('receivables.aging');
        Route::get('/receivables/overdue', [InvoiceController::class, 'getOverdueInvoices'])->name('receivables.overdue');
    });
    
    // =============================================================================
    // 日報管理
    // =============================================================================
    
    Route::prefix('daily-reports')->name('daily_reports.')->group(function () {
        Route::get('/', [DailyReportController::class, 'index'])->name('index');
        Route::get('/create', [DailyReportController::class, 'create'])->name('create');
        Route::post('/', [DailyReportController::class, 'store'])->name('store');
        Route::get('/{daily_report}', [DailyReportController::class, 'show'])->name('show');
        Route::get('/{daily_report}/edit', [DailyReportController::class, 'edit'])->name('edit');
        Route::put('/{daily_report}', [DailyReportController::class, 'update'])->name('update');
        Route::delete('/{daily_report}', [DailyReportController::class, 'destroy'])->name('destroy');
        
        // 日報統計・管理機能
        Route::get('/stats/overview', [DailyReportController::class, 'getStats'])->name('stats');
        Route::get('/filter/search', [DailyReportController::class, 'search'])->name('search');
        Route::post('/{daily_report}/approve', [DailyReportController::class, 'approve'])->name('approve');
        Route::post('/{daily_report}/reject', [DailyReportController::class, 'reject'])->name('reject');
        Route::get('/quality/analysis', [DailyReportController::class, 'getQualityAnalysis'])->name('quality.analysis');
        
        // テンプレート管理
        Route::get('/templates/list', [DailyReportController::class, 'getTemplates'])->name('templates');
        Route::post('/templates/save', [DailyReportController::class, 'saveTemplate'])->name('templates.save');
        Route::delete('/templates/{template}', [DailyReportController::class, 'deleteTemplate'])->name('templates.delete');
    });
});

// =============================================================================
// Ajax/内部API ルート（Webページ用）
// =============================================================================
//
// 注意：完全なRESTful APIは routes/api.php に実装済み
// こちらはWebページからのAjax通信用の軽量ルート
//

Route::prefix('ajax')->name('ajax.')->middleware(['auth', 'web'])->group(function () {
    
    // ダッシュボード Ajax
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/kpi', [DashboardController::class, 'getKpi'])->name('dashboard.kpi');
    Route::get('/dashboard/alerts', [DashboardController::class, 'getAlerts'])->name('dashboard.alerts');
    
    // 各機能の検索・統計（Webページ用）
    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::get('/customers/stats', [CustomerController::class, 'getStats'])->name('customers.stats');
    Route::get('/projects/search', [ProjectController::class, 'search'])->name('projects.search');
    Route::get('/projects/stats', [ProjectController::class, 'getStats'])->name('projects.stats');
    Route::get('/guards/search', [GuardController::class, 'search'])->name('guards.search');
    Route::get('/guards/stats', [GuardController::class, 'getStats'])->name('guards.stats');
    Route::get('/shifts/search', [ShiftController::class, 'search'])->name('shifts.search');
    Route::get('/shifts/stats', [ShiftController::class, 'getStats'])->name('shifts.stats');
    Route::get('/shifts/calendar', [ShiftController::class, 'getCalendarData'])->name('shifts.calendar');
    Route::get('/attendances/search', [AttendanceController::class, 'search'])->name('attendances.search');
    Route::get('/attendances/stats', [AttendanceController::class, 'getStats'])->name('attendances.stats');
    Route::get('/quotations/search', [QuotationController::class, 'search'])->name('quotations.search');
    Route::get('/quotations/stats', [QuotationController::class, 'getStats'])->name('quotations.stats');
    Route::get('/contracts/search', [ContractController::class, 'search'])->name('contracts.search');
    Route::get('/contracts/stats', [ContractController::class, 'getStats'])->name('contracts.stats');
    Route::get('/invoices/search', [InvoiceController::class, 'search'])->name('invoices.search');
    Route::get('/invoices/stats', [InvoiceController::class, 'getStats'])->name('invoices.stats');
    Route::get('/daily-reports/search', [DailyReportController::class, 'search'])->name('daily_reports.search');
    Route::get('/daily-reports/stats', [DailyReportController::class, 'getStats'])->name('daily_reports.stats');
});
