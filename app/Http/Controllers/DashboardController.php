<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Project;
use App\Models\Guard;
use App\Models\Shift;
use App\Models\Attendance;
use App\Models\Quotation;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\DailyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ダッシュボードController
 * 
 * システム全体の概要情報とKPIを提供
 * 警備グループシステムの統合ダッシュボード機能
 */
class DashboardController extends Controller
{
    /**
     * ダッシュボードページを表示
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $dashboardData = $this->getDashboardData($user);

        if ($request->expectsJson()) {
            return $this->successResponse($dashboardData, 'ダッシュボードデータを取得しました');
        }

        return view('dashboard', compact('dashboardData'));
    }

    /**
     * ダッシュボード用の統計データを取得
     * 
     * @param User $user
     * @return array
     */
    private function getDashboardData(User $user): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        // 基本統計
        $basicStats = $this->getBasicStats($user);
        
        // 月次比較データ
        $monthlyComparison = $this->getMonthlyComparison($thisMonth, $lastMonth, $user);
        
        // 進行中の案件
        $activeProjects = $this->getActiveProjects($user);
        
        // 本日のシフト
        $todayShifts = $this->getTodayShifts($today, $user);
        
        // 最近の活動
        $recentActivities = $this->getRecentActivities($user);
        
        // 売上サマリー
        $revenueSummary = $this->getRevenueSummary($user);
        
        // アラート・通知
        $alerts = $this->getAlerts($user);

        return [
            'basic_stats' => $basicStats,
            'monthly_comparison' => $monthlyComparison,
            'active_projects' => $activeProjects,
            'today_shifts' => $todayShifts,
            'recent_activities' => $recentActivities,
            'revenue_summary' => $revenueSummary,
            'alerts' => $alerts,
            'user_info' => [
                'name' => $user->name,
                'role' => $user->role,
                'company' => $user->company->name ?? 'N/A',
                'last_login' => $user->last_login_at?->format('Y-m-d H:i'),
            ]
        ];
    }

    /**
     * 基本統計データを取得
     * 
     * @param User $user
     * @return array
     */
    private function getBasicStats(User $user): array
    {
        $query = $this->applyUserFilter(null, $user);

        return [
            'total_customers' => Customer::when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('id', $user->company_id);
            })->count(),
            
            'active_projects' => Project::where('status', 'active')
                ->when($user->role !== 'admin', function($q) use ($user) {
                    return $q->where('customer_id', $user->company_id);
                })->count(),
                
            'total_guards' => Guard::where('status', 'active')
                ->when($user->role !== 'admin', function($q) use ($user) {
                    return $q->where('company_id', $user->company_id);
                })->count(),
                
            'monthly_revenue' => Invoice::where('status', 'paid')
                ->whereMonth('created_at', Carbon::now()->month)
                ->when($user->role !== 'admin', function($q) use ($user) {
                    return $q->whereHas('contract.project', function($subQ) use ($user) {
                        $subQ->where('customer_id', $user->company_id);
                    });
                })->sum('total_amount'),
        ];
    }

    /**
     * 月次比較データを取得
     * 
     * @param Carbon $thisMonth
     * @param Carbon $lastMonth
     * @param User $user
     * @return array
     */
    private function getMonthlyComparison(Carbon $thisMonth, Carbon $lastMonth, User $user): array
    {
        $thisMonthRevenue = Invoice::where('status', 'paid')
            ->where('created_at', '>=', $thisMonth)
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->whereHas('contract.project', function($subQ) use ($user) {
                    $subQ->where('customer_id', $user->company_id);
                });
            })->sum('total_amount');

        $lastMonthRevenue = Invoice::where('status', 'paid')
            ->whereBetween('created_at', [$lastMonth, $thisMonth])
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->whereHas('contract.project', function($subQ) use ($user) {
                    $subQ->where('customer_id', $user->company_id);
                });
            })->sum('total_amount');

        $revenueGrowth = $lastMonthRevenue > 0 
            ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 
            : 0;

        return [
            'this_month_revenue' => $thisMonthRevenue,
            'last_month_revenue' => $lastMonthRevenue,
            'revenue_growth_percentage' => round($revenueGrowth, 1),
        ];
    }

    /**
     * 進行中の案件を取得
     * 
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getActiveProjects(User $user)
    {
        return Project::with(['customer', 'guards'])
            ->where('status', 'active')
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->where('customer_id', $user->company_id);
            })
            ->orderBy('start_date', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * 本日のシフトを取得
     * 
     * @param Carbon $today
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTodayShifts(Carbon $today, User $user)
    {
        return Shift::with(['project.customer', 'assignments.guard'])
            ->whereDate('shift_date', $today)
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->whereHas('project', function($subQ) use ($user) {
                    $subQ->where('customer_id', $user->company_id);
                });
            })
            ->when($user->role === 'guard', function($q) use ($user) {
                return $q->whereHas('assignments', function($subQ) use ($user) {
                    $subQ->where('guard_id', $user->guard_id);
                });
            })
            ->orderBy('start_time')
            ->get();
    }

    /**
     * 最近の活動を取得
     * 
     * @param User $user
     * @return array
     */
    private function getRecentActivities(User $user): array
    {
        $activities = [];

        // 最近の日報
        $recentReports = DailyReport::with(['shift.project.customer', 'guard'])
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->whereHas('shift.project', function($subQ) use ($user) {
                    $subQ->where('customer_id', $user->company_id);
                });
            })
            ->when($user->role === 'guard', function($q) use ($user) {
                return $q->where('guard_id', $user->guard_id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentReports as $report) {
            $activities[] = [
                'type' => 'daily_report',
                'title' => '日報提出',
                'description' => $report->shift->project->customer->name . ' - ' . $report->shift->location,
                'time' => $report->created_at,
                'icon' => 'document-text',
                'color' => 'blue'
            ];
        }

        // 最近の契約
        $recentContracts = Contract::with(['project.customer'])
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->whereHas('project', function($subQ) use ($user) {
                    $subQ->where('customer_id', $user->company_id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        foreach ($recentContracts as $contract) {
            $activities[] = [
                'type' => 'contract',
                'title' => '契約締結',
                'description' => $contract->project->customer->name . ' - ' . $contract->project->name,
                'time' => $contract->created_at,
                'icon' => 'document-check',
                'color' => 'green'
            ];
        }

        // 時系列でソート
        usort($activities, function($a, $b) {
            return $b['time'] <=> $a['time'];
        });

        return array_slice($activities, 0, 5);
    }

    /**
     * 売上サマリーを取得
     * 
     * @param User $user
     * @return array
     */
    private function getRevenueSummary(User $user): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // 月別売上（過去12ヶ月）
        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenue = Invoice::where('status', 'paid')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->when($user->role !== 'admin', function($q) use ($user) {
                    return $q->whereHas('contract.project', function($subQ) use ($user) {
                        $subQ->where('customer_id', $user->company_id);
                    });
                })
                ->sum('total_amount');

            $monthlyRevenue[] = [
                'month' => $month->format('Y-m'),
                'revenue' => $revenue
            ];
        }

        return [
            'monthly_revenue' => $monthlyRevenue,
            'total_pending' => Invoice::where('status', 'pending')
                ->when($user->role !== 'admin', function($q) use ($user) {
                    return $q->whereHas('contract.project', function($subQ) use ($user) {
                        $subQ->where('customer_id', $user->company_id);
                    });
                })
                ->sum('total_amount'),
        ];
    }

    /**
     * アラート・通知を取得
     * 
     * @param User $user
     * @return array
     */
    private function getAlerts(User $user): array
    {
        $alerts = [];

        // 未提出の日報
        $pendingReports = Shift::whereDate('shift_date', '<', Carbon::today())
            ->whereDoesntHave('dailyReports')
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->whereHas('project', function($subQ) use ($user) {
                    $subQ->where('customer_id', $user->company_id);
                });
            })
            ->count();

        if ($pendingReports > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => '未提出日報',
                'message' => "{$pendingReports}件の日報が未提出です",
                'action_url' => route('daily-reports.index', ['status' => 'pending'])
            ];
        }

        // 期限切れ間近の契約
        $expiringContracts = Contract::where('end_date', '<=', Carbon::now()->addDays(30))
            ->where('status', 'active')
            ->when($user->role !== 'admin', function($q) use ($user) {
                return $q->whereHas('project', function($subQ) use ($user) {
                    $subQ->where('customer_id', $user->company_id);
                });
            })
            ->count();

        if ($expiringContracts > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => '契約期限注意',
                'message' => "{$expiringContracts}件の契約が30日以内に期限を迎えます",
                'action_url' => route('contracts.index', ['expiring' => 'true'])
            ];
        }

        return $alerts;
    }

    /**
     * ユーザーの権限に基づいてクエリにフィルターを適用
     * 
     * @param mixed $query
     * @param User $user
     * @return mixed
     */
    private function applyUserFilter($query, User $user)
    {
        return $query;
    }

    /**
     * ダッシュボードデータのリフレッシュ
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        $user = Auth::user();
        $dashboardData = $this->getDashboardData($user);

        return $this->successResponse($dashboardData, 'ダッシュボードデータを更新しました');
    }
}
