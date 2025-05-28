
    
    // === 売上分析ヘルパーメソッド実装 ===
    
    private function getAnalysisDateRange(string $period): array
    {
        switch ($period) {
            case 'week':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'quarter':
                return [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()];
            case 'year':
                return [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()];
            case 'last_month':
                return [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()];
            case 'last_quarter':
                return [Carbon::now()->subQuarter()->startOfQuarter(), Carbon::now()->subQuarter()->endOfQuarter()];
            case 'last_year':
                return [Carbon::now()->subYear()->startOfYear(), Carbon::now()->subYear()->endOfYear()];
            default:
                return [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()];
        }
    }

    private function getRevenueByDateRange($companiesQuery, Carbon $startDate, Carbon $endDate): float
    {
        $query = Invoice::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($companiesQuery && $companiesQuery !== 'all') {
            $query->whereHas('contract.project.customer', function($subQ) use ($companiesQuery) {
                $subQ->where('company_id', $companiesQuery);
            });
        }

        return (float) $query->sum('total_amount');
    }

    private function getRevenueOverviewAnalysis(User $user, array $dateRange, string $companyFilter): array
    {
        $companiesQuery = $this->getCompanyFilterQuery($companyFilter, $user);
        
        $currentPeriodRevenue = $this->getRevenueByDateRange($companiesQuery, $dateRange[0], $dateRange[1]);
        $previousPeriodRevenue = $this->getRevenueByDateRange($companiesQuery, 
            $dateRange[0]->copy()->subDays($dateRange[1]->diffInDays($dateRange[0])), 
            $dateRange[0]->copy()->subDay()
        );
        
        $growthRate = $previousPeriodRevenue > 0 
            ? (($currentPeriodRevenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100 
            : 0;
        
        return [
            'current_period' => [
                'total_revenue' => $currentPeriodRevenue,
                'average_daily' => $currentPeriodRevenue / max(1, $dateRange[1]->diffInDays($dateRange[0])),
                'invoice_count' => $this->getInvoiceCountByDateRange($companiesQuery, $dateRange[0], $dateRange[1]),
                'average_invoice_amount' => $this->getAverageInvoiceAmount($companiesQuery, $dateRange[0], $dateRange[1])
            ],
            'previous_period' => [
                'total_revenue' => $previousPeriodRevenue,
                'growth_rate' => round($growthRate, 2),
                'growth_amount' => $currentPeriodRevenue - $previousPeriodRevenue
            ],
            'metrics' => [
                'highest_daily_revenue' => $this->getHighestDailyRevenue($companiesQuery, $dateRange[0], $dateRange[1]),
                'lowest_daily_revenue' => $this->getLowestDailyRevenue($companiesQuery, $dateRange[0], $dateRange[1]),
                'revenue_volatility' => $this->getRevenueVolatility($companiesQuery, $dateRange[0], $dateRange[1]),
                'payment_completion_rate' => $this->getPaymentCompletionRate($companiesQuery, $dateRange[0], $dateRange[1])
            ]
        ];
    }

    private function getDetailedRevenueTrendAnalysis(User $user, array $dateRange, string $companyFilter): array
    {
        $companiesQuery = $this->getCompanyFilterQuery($companyFilter, $user);
        
        $dailyRevenue = $this->getDailyRevenueData($companiesQuery, $dateRange[0], $dateRange[1]);
        $weeklyRevenue = $this->getWeeklyRevenueData($companiesQuery, $dateRange[0], $dateRange[1]);
        $monthlyRevenue = $this->getMonthlyRevenueData($companiesQuery, $dateRange[0], $dateRange[1]);
        
        return [
            'daily_trend' => [
                'data' => $dailyRevenue,
                'moving_average_7' => $this->calculateMovingAverage($dailyRevenue, 7),
                'moving_average_30' => $this->calculateMovingAverage($dailyRevenue, 30)
            ],
            'weekly_trend' => [
                'data' => $weeklyRevenue,
                'growth_rates' => $this->calculatePeriodGrowthRates($weeklyRevenue)
            ],
            'monthly_trend' => [
                'data' => $monthlyRevenue,
                'growth_rates' => $this->calculatePeriodGrowthRates($monthlyRevenue),
                'seasonality_index' => $this->calculateSeasonalityIndex($monthlyRevenue)
            ],
            'trend_indicators' => [
                'overall_trend' => $this->calculateTrendDirection($dailyRevenue),
                'trend_strength' => $this->calculateTrendStrength($dailyRevenue),
                'volatility_index' => $this->calculateVolatilityIndex($dailyRevenue)
            ]
        ];
    }

    private function getRevenueCompositionAnalysis(User $user, array $dateRange, string $companyFilter): array
    {
        $companiesQuery = $this->getCompanyFilterQuery($companyFilter, $user);
        
        return [
            'by_service_type' => $this->getRevenueByServiceType($companiesQuery, $dateRange[0], $dateRange[1]),
            'by_customer_segment' => $this->getRevenueByCustomerSegment($companiesQuery, $dateRange[0], $dateRange[1]),
            'by_project_scale' => $this->getRevenueByProjectScale($companiesQuery, $dateRange[0], $dateRange[1]),
            'by_contract_type' => $this->getRevenueByContractType($companiesQuery, $dateRange[0], $dateRange[1]),
            'by_payment_terms' => $this->getRevenueByPaymentTerms($companiesQuery, $dateRange[0], $dateRange[1]),
            'concentration_analysis' => [
                'top_10_customers_share' => $this->getTop10CustomersRevenueShare($companiesQuery, $dateRange[0], $dateRange[1]),
                'herfindahl_index' => $this->calculateRevenueHerfindahlIndex($companiesQuery, $dateRange[0], $dateRange[1]),
                'diversification_score' => $this->calculateRevenueDiversificationScore($companiesQuery, $dateRange[0], $dateRange[1])
            ]
        ];
    }

    private function getCustomerRevenueAnalysis(User $user, array $dateRange, string $companyFilter): array
    {
        $companiesQuery = $this->getCompanyFilterQuery($companyFilter, $user);
        
        $customerRevenueData = Invoice::where('status', 'paid')
            ->whereBetween('created_at', [$dateRange[0], $dateRange[1]])
            ->when($companiesQuery && $companiesQuery !== 'all', function($query) use ($companiesQuery) {
                $query->whereHas('contract.project.customer', function($subQ) use ($companiesQuery) {
                    $subQ->where('company_id', $companiesQuery);
                });
            })
            ->with('contract.project.customer')
            ->get()
            ->groupBy('contract.project.customer.id')
            ->map(function($invoices, $customerId) {
                $customer = $invoices->first()->contract->project->customer;
                $totalRevenue = $invoices->sum('total_amount');
                $invoiceCount = $invoices->count();
                $avgInvoiceAmount = $invoiceCount > 0 ? $totalRevenue / $invoiceCount : 0;
                
                return [
                    'customer_id' => $customerId,
                    'customer_name' => $customer->name,
                    'customer_type' => $customer->type,
                    'total_revenue' => $totalRevenue,
                    'invoice_count' => $invoiceCount,
                    'average_invoice_amount' => $avgInvoiceAmount,
                    'first_invoice_date' => $invoices->min('created_at'),
                    'last_invoice_date' => $invoices->max('created_at'),
                    'revenue_trend' => $this->getCustomerRevenueTrend($customerId, $dateRange[0], $dateRange[1])
                ];
            })
            ->sortByDesc('total_revenue')
            ->values();
        
        return [
            'top_customers' => $customerRevenueData->take(20),
            'customer_segments' => $this->analyzeCustomerSegments($customerRevenueData),
            'customer_lifecycle' => $this->analyzeCustomerLifecycle($customerRevenueData),
            'churn_risk_analysis' => $this->analyzeCustomerChurnRisk($customerRevenueData)
        ];
    }

    // === 売上分析データ取得メソッド ===
    
    private function getInvoiceCountByDateRange($companiesQuery, Carbon $startDate, Carbon $endDate): int
    {
        $query = Invoice::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($companiesQuery && $companiesQuery !== 'all') {
            $query->whereHas('contract.project.customer', function($subQ) use ($companiesQuery) {
                $subQ->where('company_id', $companiesQuery);
            });
        }

        return $query->count();
    }

    private function getAverageInvoiceAmount($companiesQuery, Carbon $startDate, Carbon $endDate): float
    {
        $totalRevenue = $this->getRevenueByDateRange($companiesQuery, $startDate, $endDate);
        $invoiceCount = $this->getInvoiceCountByDateRange($companiesQuery, $startDate, $endDate);
        
        return $invoiceCount > 0 ? round($totalRevenue / $invoiceCount, 0) : 0.0;
    }

    private function getDailyRevenueData($companiesQuery, Carbon $startDate, Carbon $endDate): array
    {
        $query = Invoice::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue');

        if ($companiesQuery && $companiesQuery !== 'all') {
            $query->whereHas('contract.project.customer', function($subQ) use ($companiesQuery) {
                $subQ->where('company_id', $companiesQuery);
            });
        }

        return $query->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->pluck('revenue', 'date')
            ->toArray();
    }

    private function getWeeklyRevenueData($companiesQuery, Carbon $startDate, Carbon $endDate): array
    {
        $query = Invoice::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('YEARWEEK(created_at) as week, SUM(total_amount) as revenue');

        if ($companiesQuery && $companiesQuery !== 'all') {
            $query->whereHas('contract.project.customer', function($subQ) use ($companiesQuery) {
                $subQ->where('company_id', $companiesQuery);
            });
        }

        return $query->groupByRaw('YEARWEEK(created_at)')
            ->orderBy('week')
            ->get()
            ->pluck('revenue', 'week')
            ->toArray();
    }

    private function getMonthlyRevenueData($companiesQuery, Carbon $startDate, Carbon $endDate): array
    {
        $query = Invoice::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_amount) as revenue');

        if ($companiesQuery && $companiesQuery !== 'all') {
            $query->whereHas('contract.project.customer', function($subQ) use ($companiesQuery) {
                $subQ->where('company_id', $companiesQuery);
            });
        }

        return $query->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
            ->orderBy('month')
            ->get()
            ->pluck('revenue', 'month')
            ->toArray();
    }

    // === 売上分析計算メソッド ===
    
    private function calculateMovingAverage(array $data, int $window): array
    {
        $movingAverage = [];
        $values = array_values($data);
        
        for ($i = $window - 1; $i < count($values); $i++) {
            $sum = array_sum(array_slice($values, $i - $window + 1, $window));
            $movingAverage[array_keys($data)[$i]] = $sum / $window;
        }
        
        return $movingAverage;
    }

    private function calculatePeriodGrowthRates(array $data): array
    {
        $growthRates = [];
        $values = array_values($data);
        $keys = array_keys($data);
        
        for ($i = 1; $i < count($values); $i++) {
            $currentValue = $values[$i];
            $previousValue = $values[$i - 1];
            
            $growthRate = $previousValue > 0 ? (($currentValue - $previousValue) / $previousValue) * 100 : 0;
            $growthRates[$keys[$i]] = round($growthRate, 2);
        }
        
        return $growthRates;
    }

    private function calculateSeasonalityIndex(array $monthlyData): array
    {
        if (count($monthlyData) < 12) {
            return [];
        }
        
        $average = array_sum($monthlyData) / count($monthlyData);
        $seasonalityIndex = [];
        
        foreach ($monthlyData as $month => $revenue) {
            $seasonalityIndex[$month] = $average > 0 ? round(($revenue / $average) * 100, 1) : 100;
        }
        
        return $seasonalityIndex;
    }

    private function calculateTrendDirection(array $data): string
    {
        if (count($data) < 2) {
            return 'insufficient_data';
        }
        
        $values = array_values($data);
        $firstHalf = array_slice($values, 0, floor(count($values) / 2));
        $secondHalf = array_slice($values, floor(count($values) / 2));
        
        $firstHalfAvg = array_sum($firstHalf) / count($firstHalf);
        $secondHalfAvg = array_sum($secondHalf) / count($secondHalf);
        
        if ($secondHalfAvg > $firstHalfAvg * 1.05) {
            return 'upward';
        } elseif ($secondHalfAvg < $firstHalfAvg * 0.95) {
            return 'downward';
        } else {
            return 'stable';
        }
    }

    private function calculateTrendStrength(array $data): float
    {
        if (count($data) < 2) {
            return 0.0;
        }
        
        $values = array_values($data);
        $n = count($values);
        $sumX = array_sum(range(1, $n));
        $sumY = array_sum($values);
        $sumXY = 0;
        $sumXX = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $x = $i + 1;
            $y = $values[$i];
            $sumXY += $x * $y;
            $sumXX += $x * $x;
        }
        
        $denominator = sqrt(($n * $sumXX - $sumX * $sumX) * ($n * array_sum(array_map(function($y) { return $y * $y; }, $values)) - $sumY * $sumY));
        
        if ($denominator == 0) {
            return 0.0;
        }
        
        $correlation = ($n * $sumXY - $sumX * $sumY) / $denominator;
        
        return round(abs($correlation), 3);
    }

    private function calculateVolatilityIndex(array $data): float
    {
        if (count($data) < 2) {
            return 0.0;
        }
        
        $values = array_values($data);
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(function($x) use ($mean) { return pow($x - $mean, 2); }, $values)) / count($values);
        $standardDeviation = sqrt($variance);
        
        return $mean > 0 ? round(($standardDeviation / $mean) * 100, 2) : 0.0;
    }

    // === 売上分析サポートメソッド（ダミー実装） ===
    
    private function getHighestDailyRevenue($companiesQuery, Carbon $startDate, Carbon $endDate): array
    {
        return ['date' => '2025-05-15', 'revenue' => 2500000];
    }

    private function getLowestDailyRevenue($companiesQuery, Carbon $startDate, Carbon $endDate): array
    {
        return ['date' => '2025-05-03', 'revenue' => 850000];
    }

    private function getRevenueVolatility($companiesQuery, Carbon $startDate, Carbon $endDate): float
    {
        return 12.5;
    }

    private function getPaymentCompletionRate($companiesQuery, Carbon $startDate, Carbon $endDate): float
    {
        return 96.8;
    }

    private function getRevenueByServiceType($companiesQuery, Carbon $startDate, Carbon $endDate): array
    {
        return [
            ['type' => '施設警備', 'revenue' => 15000000, 'percentage' => 45.5],
            ['type' => 'イベント警備', 'revenue' => 8000000, 'percentage' => 24.2],
            ['type' => '交通誘導', 'revenue' => 6000000, 'percentage' => 18.2],
            ['type' => '身辺警護', 'revenue' => 4000000, 'percentage' => 12.1]
        ];
    }

    private function getRevenueByCustomerSegment($companiesQuery, Carbon $startDate, Carbon $endDate): array
    {
        return [
            ['segment' => '大企業', 'revenue' => 20000000, 'percentage' => 60.6],
            ['segment' => '中小企業', 'revenue' => 8000000, 'percentage' => 24.2],
            ['segment' => '官公庁', 'revenue' => 5000000, 'percentage' => 15.2]
        ];
    }

    private function getRevenueByProjectScale($companiesQuery, Carbon $startDate, Carbon $endDate): array
    {
        return [
            ['scale' => '大規模（月500万円以上）', 'revenue' => 18000000, 'percentage' => 54.5],
            ['scale' => '中規模（月100-500万円）', 'revenue' => 10000000, 'percentage' => 30.3],
            ['scale' => '小規模（月100万円未満）', 'revenue' => 5000000, 'percentage' => 15.2]
        ];
    }

    private function getRevenueByContractType($companiesQuery, Carbon $startDate, Carbon $endDate): array
    {
        return [
            ['type' => '年間契約', 'revenue' => 25000000, 'percentage' => 75.8],
            ['type' => '短期契約', 'revenue' => 5000000, 'percentage' => 15.2],
            ['type' => 'スポット契約', 'revenue' => 3000000, 'percentage' => 9.0]
        ];
    }

    private function getRevenueByPaymentTerms($companiesQuery, Carbon $startDate, Carbon $endDate): array
    {
        return [
            ['terms' => '月末締め翌月末払い', 'revenue' => 20000000, 'percentage' => 60.6],
            ['terms' => '月末締め翌々月末払い', 'revenue' => 10000000, 'percentage' => 30.3],
            ['terms' => '即時払い', 'revenue' => 3000000, 'percentage' => 9.1]
        ];
    }

    private function getTop10CustomersRevenueShare($companiesQuery, Carbon $startDate, Carbon $endDate): float
    {
        return 68.5;
    }

    private function calculateRevenueHerfindahlIndex($companiesQuery, Carbon $startDate, Carbon $endDate): float
    {
        return 0.245;
    }

    private function calculateRevenueDiversificationScore($companiesQuery, Carbon $startDate, Carbon $endDate): float
    {
        return 78.9;
    }

    private function getCustomerRevenueTrend($customerId, Carbon $startDate, Carbon $endDate): array
    {
        return [
            'trend' => 'increasing',
            'growth_rate' => 12.5,
            'volatility' => 8.2
        ];
    }

    private function analyzeCustomerSegments($customerRevenueData): array
    {
        return [
            'high_value' => ['count' => 5, 'revenue_share' => 65.2],
            'medium_value' => ['count' => 15, 'revenue_share' => 28.8],
            'low_value' => ['count' => 35, 'revenue_share' => 6.0]
        ];
    }

    private function analyzeCustomerLifecycle($customerRevenueData): array
    {
        return [
            'new_customers' => 3,
            'growing_customers' => 8,
            'stable_customers' => 25,
            'declining_customers' => 5,
            'at_risk_customers' => 2
        ];
    }

    private function analyzeCustomerChurnRisk($customerRevenueData): array
    {
        return [
            'high_risk' => ['count' => 2, 'potential_revenue_loss' => 2500000],
            'medium_risk' => ['count' => 5, 'potential_revenue_loss' => 1800000],
            'low_risk' => ['count' => 36, 'potential_revenue_loss' => 500000]
        ];
    }

    private function getProjectRevenueAnalysis(User $user, array $dateRange, string $companyFilter): array
    {
        return [
            'top_projects' => [
                ['project_name' => '東京スカイツリー警備', 'revenue' => 12000000, 'profitability' => 25.5],
                ['project_name' => '羽田空港セキュリティ', 'revenue' => 8500000, 'profitability' => 22.8],
                ['project_name' => '新宿イベント警備', 'revenue' => 6200000, 'profitability' => 28.3]
            ],
            'project_types_analysis' => [
                '施設警備' => ['count' => 15, 'revenue' => 18000000, 'avg_revenue' => 1200000],
                'イベント警備' => ['count' => 8, 'revenue' => 8000000, 'avg_revenue' => 1000000]
            ]
        ];
    }

    private function getGuardRevenueAnalysis(User $user, array $dateRange, string $companyFilter): array
    {
        return [
            'top_performers' => [
                ['guard_name' => '佐藤一郎', 'attributed_revenue' => 2800000, 'hours' => 180, 'efficiency' => 15556],
                ['guard_name' => '田中次郎', 'attributed_revenue' => 2450000, 'hours' => 165, 'efficiency' => 14848]
            ],
            'revenue_per_hour_analysis' => [
                'average_revenue_per_hour' => 4500,
                'top_performer_revenue_per_hour' => 6200
            ]
        ];
    }

    // === その他の売上分析ダミー実装 ===
    
    private function getRevenueForecastAnalysis(User $user, string $companyFilter): array
    {
        return [
            'next_month' => ['forecast' => 12500000, 'confidence' => 85.2],
            'next_quarter' => ['forecast' => 38000000, 'confidence' => 78.9],
            'next_year' => ['forecast' => 145000000, 'confidence' => 65.5]
        ];
    }

    private function getProfitabilityAnalysis(User $user, array $dateRange, string $companyFilter): array
    {
        return [
            'gross_profit_margin' => 32.5,
            'operating_profit_margin' => 18.2,
            'net_profit_margin' => 12.8
        ];
    }

    private function getSeasonalityAnalysis(User $user, string $companyFilter): array
    {
        return [
            'seasonal_pattern' => 'Strong winter peak',
            'peak_months' => ['December', 'January', 'February'],
            'low_months' => ['June', 'July', 'August']
        ];
    }

    private function getRevenuePerformanceAnalysis(User $user, array $dateRange, string $companyFilter): array
    {
        return [
            'vs_target' => 108.5,
            'vs_last_year' => 112.3,
            'vs_industry_average' => 95.8,
            'performance_score' => 'Above Average'
        ];
    }

    private function getRevenueExportData(User $user, array $dateRange, string $companyFilter, string $analysisType): array
    {
        return [
            'export_formats' => ['CSV', 'Excel', 'PDF'],
            'data_points' => 1250,
            'last_updated' => Carbon::now()->format('Y-m-d H:i:s')
        ];
    }
    
    /**
     * 売上分析データのエクスポート機能
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportRevenueAnalysis(Request $request)
    {
        $user = Auth::user();
        
        $validatedData = $request->validate([
            'format' => 'required|in:excel,csv,pdf',
            'period' => 'string',
            'company' => 'string',
            'analysis_type' => 'string',
            'data' => 'array'
        ]);
        
        $period = $validatedData['period'] ?? 'year';
        $companyFilter = $validatedData['company'] ?? 'all';
        $analysisType = $validatedData['analysis_type'] ?? 'overview';
        $format = $validatedData['format'];
        $selectedData = $validatedData['data'] ?? ['summary'];
        
        // 分析データを取得
        $revenueAnalysisData = $this->getRevenueAnalysisData($user, $period, $companyFilter, $analysisType);
        
        // エクスポートデータを準備
        $exportData = $this->prepareExportData($revenueAnalysisData, $selectedData);
        
        // フォーマットに応じてエクスポート実行
        switch ($format) {
            case 'excel':
                return $this->exportToExcel($exportData, $period, $companyFilter);
            case 'csv':
                return $this->exportToCsv($exportData, $period, $companyFilter);
            case 'pdf':
                return $this->exportToPdf($exportData, $period, $companyFilter);
            default:
                return $this->errorResponse('サポートされていないフォーマットです', 400);
        }
    }

    /**
     * エクスポート用データの準備
     */
    private function prepareExportData(array $analysisData, array $selectedData): array
    {
        $exportData = [];
        
        // 概要データ
        if (in_array('summary', $selectedData)) {
            $exportData['overview'] = [
                'title' => '売上分析概要',
                'data' => [
                    ['項目', '値'],
                    ['今期売上合計', '¥' . number_format($analysisData['overview']['current_period']['total_revenue'] ?? 0)],
                    ['平均日次売上', '¥' . number_format($analysisData['overview']['current_period']['average_daily'] ?? 0)],
                    ['請求件数', number_format($analysisData['overview']['current_period']['invoice_count'] ?? 0) . '件'],
                    ['平均請求額', '¥' . number_format($analysisData['overview']['current_period']['average_invoice_amount'] ?? 0)],
                    ['前期比成長率', ($analysisData['overview']['previous_period']['growth_rate'] ?? 0) . '%'],
                    ['売上変動率', ($analysisData['overview']['metrics']['revenue_volatility'] ?? 0) . '%'],
                    ['支払完了率', ($analysisData['overview']['metrics']['payment_completion_rate'] ?? 0) . '%']
                ]
            ];
        }
        
        // トレンドデータ
        if (in_array('trend', $selectedData)) {
            $exportData['trend'] = [
                'title' => '売上トレンド分析',
                'daily_data' => $this->formatTrendDataForExport($analysisData['trend_analysis']['daily_trend']['data'] ?? []),
                'indicators' => [
                    ['指標', '値'],
                    ['全体トレンド', $analysisData['trend_analysis']['trend_indicators']['overall_trend'] ?? 'N/A'],
                    ['トレンド強度', ($analysisData['trend_analysis']['trend_indicators']['trend_strength'] ?? 0)],
                    ['変動指数', ($analysisData['trend_analysis']['trend_indicators']['volatility_index'] ?? 0) . '%']
                ]
            ];
        }
        
        // 詳細データ
        if (in_array('detail', $selectedData)) {
            $exportData['details'] = [
                'title' => '詳細分析データ',
                'customer_analysis' => $this->formatCustomerDataForExport($analysisData['customer_analysis'] ?? []),
                'composition_analysis' => $this->formatCompositionDataForExport($analysisData['composition_analysis'] ?? []),
                'forecast_analysis' => $this->formatForecastDataForExport($analysisData['forecast_analysis'] ?? [])
            ];
        }
        
        return $exportData;
    }

    /**
     * Excelファイルとしてエクスポート
     */
    private function exportToExcel(array $exportData, string $period, string $companyFilter)
    {
        $filename = "売上分析_{$period}_{$companyFilter}_" . date('Y-m-d_H-i-s') . ".xlsx";
        
        // 簡易Excel出力（実際にはSpout等のライブラリを使用推奨）
        $csvContent = $this->convertToCSV($exportData);
        
        return response($csvContent)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * CSVファイルとしてエクスポート
     */
    private function exportToCsv(array $exportData, string $period, string $companyFilter)
    {
        $filename = "売上分析_{$period}_{$companyFilter}_" . date('Y-m-d_H-i-s') . ".csv";
        $csvContent = $this->convertToCSV($exportData);
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * PDFファイルとしてエクスポート
     */
    private function exportToPdf(array $exportData, string $period, string $companyFilter)
    {
        $filename = "売上分析_{$period}_{$companyFilter}_" . date('Y-m-d_H-i-s') . ".pdf";
        
        // PDF生成（実際にはDompdf等のライブラリを使用推奨）
        $htmlContent = $this->convertToHTML($exportData);
        
        // 簡易HTML形式でのレスポンス（実際のPDF生成は実装時に調整）
        return response($htmlContent)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * データをCSV形式に変換
     */
    private function convertToCSV(array $exportData): string
    {
        $output = fopen('php://temp', 'r+');
        
        // BOM付きUTF-8
        fputs($output, "\xEF\xBB\xBF");
        
        foreach ($exportData as $section => $sectionData) {
            // セクションタイトル
            fputcsv($output, [$sectionData['title']]);
            fputcsv($output, []); // 空行
            
            // データ行
            if (isset($sectionData['data'])) {
                foreach ($sectionData['data'] as $row) {
                    fputcsv($output, $row);
                }
            }
            
            // トレンドデータの場合
            if (isset($sectionData['daily_data'])) {
                fputcsv($output, ['日付', '売上']);
                foreach ($sectionData['daily_data'] as $date => $revenue) {
                    fputcsv($output, [$date, $revenue]);
                }
            }
            
            // 指標データの場合
            if (isset($sectionData['indicators'])) {
                fputcsv($output, []); // 空行
                foreach ($sectionData['indicators'] as $row) {
                    fputcsv($output, $row);
                }
            }
            
            fputcsv($output, []); // セクション間の空行
            fputcsv($output, []); // セクション間の空行
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }

    /**
     * データをHTML形式に変換（PDF用）
     */
    private function convertToHTML(array $exportData): string
    {
        $html = '<!DOCTYPE html>
        <html lang="ja">
        <head>
            <meta charset="UTF-8">
            <title>売上分析レポート</title>
            <style>
                body { font-family: "Yu Gothic", "Hiragino Sans", sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 30px; }
                .section { margin-bottom: 30px; }
                .section-title { font-size: 16px; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 5px; margin-bottom: 15px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f5f5f5; font-weight: bold; }
                .number { text-align: right; }
                .report-meta { font-size: 10px; color: #666; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>売上分析レポート</h1>
                <p>生成日時: ' . date('Y年m月d日 H:i:s') . '</p>
            </div>';
        
        foreach ($exportData as $section => $sectionData) {
            $html .= '<div class="section">';
            $html .= '<h2 class="section-title">' . $sectionData['title'] . '</h2>';
            
            if (isset($sectionData['data'])) {
                $html .= '<table>';
                foreach ($sectionData['data'] as $index => $row) {
                    if ($index === 0) {
                        $html .= '<thead><tr>';
                        foreach ($row as $cell) {
                            $html .= '<th>' . htmlspecialchars($cell) . '</th>';
                        }
                        $html .= '</tr></thead><tbody>';
                    } else {
                        $html .= '<tr>';
                        foreach ($row as $cell) {
                            $class = is_numeric(str_replace(['¥', ',', '%'], '', $cell)) ? ' class="number"' : '';
                            $html .= '<td' . $class . '>' . htmlspecialchars($cell) . '</td>';
                        }
                        $html .= '</tr>';
                    }
                }
                $html .= '</tbody></table>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '<div class="report-meta">
                <p>このレポートは警備グループ会社受注管理・シフト管理統合システムにより自動生成されました。</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }

    // === エクスポート用データフォーマット関数 ===
    
    private function formatTrendDataForExport(array $trendData): array
    {
        $formattedData = [];
        foreach ($trendData as $date => $revenue) {
            $formattedData[$date] = number_format($revenue);
        }
        return $formattedData;
    }

    private function formatCustomerDataForExport(array $customerData): array
    {
        if (!isset($customerData['top_customers'])) {
            return [];
        }
        
        $formatted = [['顧客名', '売上金額', '請求件数', '平均請求額', '成長率']];
        
        foreach ($customerData['top_customers'] as $customer) {
            $formatted[] = [
                $customer['customer_name'] ?? 'N/A',
                '¥' . number_format($customer['total_revenue'] ?? 0),
                number_format($customer['invoice_count'] ?? 0),
                '¥' . number_format($customer['average_invoice_amount'] ?? 0),
                ($customer['revenue_trend']['growth_rate'] ?? 0) . '%'
            ];
        }
        
        return $formatted;
    }

    private function formatCompositionDataForExport(array $compositionData): array
    {
        if (!isset($compositionData['by_service_type'])) {
            return [];
        }
        
        $formatted = [['サービス種別', '売上金額', '構成比']];
        
        foreach ($compositionData['by_service_type'] as $service) {
            $formatted[] = [
                $service['type'] ?? 'N/A',
                '¥' . number_format($service['revenue'] ?? 0),
                ($service['percentage'] ?? 0) . '%'
            ];
        }
        
        return $formatted;
    }

    private function formatForecastDataForExport(array $forecastData): array
    {
        return [
            ['期間', '予測売上', '信頼度'],
            ['来月', '¥' . number_format($forecastData['next_month']['forecast'] ?? 0), ($forecastData['next_month']['confidence'] ?? 0) . '%'],
            ['来四半期', '¥' . number_format($forecastData['next_quarter']['forecast'] ?? 0), ($forecastData['next_quarter']['confidence'] ?? 0) . '%'],
            ['来年', '¥' . number_format($forecastData['next_year']['forecast'] ?? 0), ($forecastData['next_year']['confidence'] ?? 0) . '%']
        ];
    }
}
