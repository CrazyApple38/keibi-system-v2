<?php

namespace App\Http\Controllers;

use App\Models\Weather;
use App\Services\WeatherService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;

/**
 * 天気情報管理コントローラー
 * 
 * 警備業務における天気情報の管理機能を提供
 * - 天気情報の表示・検索
 * - 天気予報の取得・更新
 * - 警備業務への影響分析
 * - 統計情報・レポート生成
 */
class WeatherController extends BaseController
{
    /**
     * 天気予報サービス
     */
    private WeatherService $weatherService;

    /**
     * コンストラクタ
     */
    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
        parent::__construct();
    }

    /**
     * 天気情報一覧表示
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        try {
            $query = Weather::with([]);

            // 検索フィルター
            if ($request->filled('location')) {
                $query->where('location_name', 'LIKE', "%{$request->location}%");
            }

            if ($request->filled('date_from')) {
                $query->whereDate('weather_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('weather_date', '<=', $request->date_to);
            }

            if ($request->filled('risk_level')) {
                $query->where('weather_risk_level', $request->risk_level);
            }

            if ($request->filled('data_type')) {
                $query->where('data_type', $request->data_type);
            }

            if ($request->filled('weather_main')) {
                $query->where('weather_main', $request->weather_main);
            }

            // ソート設定
            $sortField = $request->get('sort', 'weather_date');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            // ページネーション
            $perPage = $request->get('per_page', 15);
            $weatherData = $query->paginate($perPage);

            // 統計情報
            $stats = $this->getWeatherStats($request);

            return view('weather.index', compact('weatherData', 'stats'));
        } catch (\Exception $e) {
            return $this->handleError($e, '天気情報一覧の取得に失敗しました。');
        }
    }

    /**
     * 天気情報詳細表示
     * 
     * @param Weather $weather
     * @return View
     */
    public function show(Weather $weather): View
    {
        try {
            // 警備業務への影響分析
            $securityImpact = $weather->calculateSecurityImpact();

            // 同じ場所の最近の天気データ
            $recentWeather = Weather::where('location_name', $weather->location_name)
                ->where('id', '!=', $weather->id)
                ->orderBy('weather_date', 'desc')
                ->limit(10)
                ->get();

            // 同日の他地点の天気データ
            $sameDate = Weather::whereDate('weather_date', $weather->weather_date)
                ->where('id', '!=', $weather->id)
                ->orderBy('location_name')
                ->get();

            return view('weather.show', compact('weather', 'securityImpact', 'recentWeather', 'sameDate'));
        } catch (\Exception $e) {
            return $this->handleError($e, '天気情報詳細の取得に失敗しました。');
        }
    }

    /**
     * 天気情報手動更新
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateWeather(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'location_name' => 'required|string|max:255',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'include_forecast' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->apiResponse(null, 'バリデーションエラー', 422, $validator->errors());
            }

            $locationName = $request->location_name;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $includeForecast = $request->boolean('include_forecast', true);

            // 現在の天気情報取得
            $currentWeather = $this->weatherService->getCurrentWeather($locationName, $latitude, $longitude);
            
            $result = [
                'current' => $currentWeather,
                'forecast' => []
            ];

            // 天気予報も取得
            if ($includeForecast) {
                $forecast = $this->weatherService->getForecastWeather($locationName, $latitude, $longitude);
                $result['forecast'] = $forecast;
            }

            if ($currentWeather) {
                return $this->apiResponse($result, '天気情報を正常に更新しました。');
            } else {
                return $this->apiResponse(null, '天気情報の取得に失敗しました。', 500);
            }
        } catch (\Exception $e) {
            return $this->apiErrorResponse($e, '天気情報の更新に失敗しました。');
        }
    }

    /**
     * 全警備地点の天気情報一括更新
     * 
     * @return JsonResponse
     */
    public function updateAllSecurityLocations(): JsonResponse
    {
        try {
            $results = $this->weatherService->updateSecurityLocationWeather();
            
            $successCount = count(array_filter($results));
            $totalCount = count($results);

            return $this->apiResponse([
                'updated_locations' => $successCount,
                'total_locations' => $totalCount,
                'results' => $results
            ], "{$successCount}/{$totalCount} 地点の天気情報を更新しました。");
        } catch (\Exception $e) {
            return $this->apiErrorResponse($e, '天気情報の一括更新に失敗しました。');
        }
    }

    /**
     * 天気予報ダッシュボード
     * 
     * @param Request $request
     * @return View
     */
    public function dashboard(Request $request): View
    {
        try {
            // 現在の天気情報
            $currentWeather = Weather::where('data_type', 'current')
                ->orderBy('weather_date', 'desc')
                ->limit(10)
                ->get();

            // 高リスク地点
            $highRiskWeather = Weather::getHighRiskWeather(Carbon::today());

            // 今日の予報
            $todayForecast = Weather::where('data_type', 'forecast')
                ->whereDate('weather_date', Carbon::today())
                ->orderBy('weather_date')
                ->get();

            // 週間統計
            $weeklyStats = $this->getWeeklyWeatherStats();

            // 警備地点別天気サマリー
            $locationSummary = $this->getLocationWeatherSummary();

            return view('weather.dashboard', compact(
                'currentWeather',
                'highRiskWeather', 
                'todayForecast',
                'weeklyStats',
                'locationSummary'
            ));
        } catch (\Exception $e) {
            return $this->handleError($e, '天気予報ダッシュボードの表示に失敗しました。');
        }
    }

    /**
     * 天気統計API
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getWeatherStatsApi(Request $request): JsonResponse
    {
        try {
            $stats = $this->getWeatherStats($request);
            return $this->apiResponse($stats, '天気統計情報を取得しました。');
        } catch (\Exception $e) {
            return $this->apiErrorResponse($e, '天気統計情報の取得に失敗しました。');
        }
    }

    /**
     * 指定場所の天気予報取得
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getLocationForecast(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'location_name' => 'required|string',
                'days' => 'integer|min:1|max:5',
            ]);

            if ($validator->fails()) {
                return $this->apiResponse(null, 'バリデーションエラー', 422, $validator->errors());
            }

            $locationName = $request->location_name;
            $days = $request->get('days', 3);
            
            $startDate = Carbon::now();
            $endDate = Carbon::now()->addDays($days);

            $forecast = Weather::getForecastByDateRange($locationName, $startDate, $endDate);

            return $this->apiResponse([
                'location' => $locationName,
                'forecast_days' => $days,
                'forecast' => $forecast
            ], '天気予報情報を取得しました。');
        } catch (\Exception $e) {
            return $this->apiErrorResponse($e, '天気予報の取得に失敗しました。');
        }
    }

    /**
     * 天気アラート取得
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getWeatherAlerts(Request $request): JsonResponse
    {
        try {
            $alerts = Weather::whereNotNull('weather_alerts')
                ->where('weather_date', '>=', Carbon::now())
                ->whereIn('weather_risk_level', ['high', 'critical'])
                ->orderBy('weather_date')
                ->get()
                ->map(function ($weather) {
                    return [
                        'id' => $weather->id,
                        'location' => $weather->location_name,
                        'date' => $weather->weather_date->format('Y-m-d H:i'),
                        'risk_level' => $weather->weather_risk_level,
                        'risk_level_japanese' => $weather->risk_level_japanese,
                        'alerts' => $weather->weather_alerts,
                        'weather_main' => $weather->weather_main,
                        'weather_description' => $weather->weather_description,
                        'temperature' => $weather->temperature,
                        'security_impact' => $weather->calculateSecurityImpact(),
                    ];
                });

            return $this->apiResponse($alerts, '天気アラート情報を取得しました。');
        } catch (\Exception $e) {
            return $this->apiErrorResponse($e, '天気アラートの取得に失敗しました。');
        }
    }

    /**
     * 天気データエクスポート
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportWeatherData(Request $request)
    {
        try {
            $format = $request->get('format', 'csv');
            $dateFrom = $request->get('date_from', Carbon::now()->subDays(7));
            $dateTo = $request->get('date_to', Carbon::now());

            $query = Weather::whereBetween('weather_date', [$dateFrom, $dateTo]);

            // フィルター適用
            if ($request->filled('location')) {
                $query->where('location_name', 'LIKE', "%{$request->location}%");
            }

            if ($request->filled('risk_level')) {
                $query->where('weather_risk_level', $request->risk_level);
            }

            $weatherData = $query->orderBy('weather_date')->get();

            switch ($format) {
                case 'excel':
                    return $this->exportToExcel($weatherData, '天気データ');
                case 'pdf':
                    return $this->exportToPdf($weatherData, '天気データレポート');
                case 'csv':
                default:
                    return $this->exportToCsv($weatherData, '天気データ');
            }
        } catch (\Exception $e) {
            return $this->handleError($e, '天気データのエクスポートに失敗しました。');
        }
    }

    /**
     * 古い天気データクリーンアップ
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function cleanupOldData(Request $request): JsonResponse
    {
        try {
            $daysToKeep = $request->get('days_to_keep', 30);
            $deletedCount = $this->weatherService->cleanupOldWeatherData($daysToKeep);

            return $this->apiResponse([
                'deleted_count' => $deletedCount,
                'days_kept' => $daysToKeep
            ], "{$deletedCount}件の古い天気データを削除しました。");
        } catch (\Exception $e) {
            return $this->apiErrorResponse($e, '天気データのクリーンアップに失敗しました。');
        }
    }

    /**
     * 天気統計情報取得
     */
    private function getWeatherStats(Request $request): array
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subDays(7));
        $dateTo = $request->get('date_to', Carbon::now());

        $query = Weather::whereBetween('weather_date', [$dateFrom, $dateTo]);

        if ($request->filled('location')) {
            $query->where('location_name', 'LIKE', "%{$request->location}%");
        }

        $data = $query->get();

        return [
            'total_records' => $data->count(),
            'locations_count' => $data->unique('location_name')->count(),
            'avg_temperature' => round($data->avg('temperature'), 1),
            'max_temperature' => $data->max('temperature'),
            'min_temperature' => $data->min('temperature'),
            'avg_humidity' => round($data->avg('humidity')),
            'total_rainfall' => round($data->sum('rain_1h'), 1),
            'high_risk_count' => $data->whereIn('weather_risk_level', ['high', 'critical'])->count(),
            'outdoor_unsuitable_count' => $data->where('outdoor_work_suitable', false)->count(),
            'weather_distribution' => $data->groupBy('weather_main')->map->count()->toArray(),
        ];
    }

    /**
     * 週間天気統計取得
     */
    private function getWeeklyWeatherStats(): array
    {
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        $data = Weather::whereBetween('weather_date', [$startDate, $endDate])->get();

        return [
            'week_avg_temp' => round($data->avg('temperature'), 1),
            'week_rainfall' => round($data->sum('rain_1h'), 1),
            'high_risk_days' => $data->whereIn('weather_risk_level', ['high', 'critical'])->count(),
            'outdoor_work_days' => $data->where('outdoor_work_suitable', true)->count(),
            'daily_stats' => $data->groupBy(function($item) {
                return $item->weather_date->format('Y-m-d');
            })->map(function($dayData) {
                return [
                    'avg_temp' => round($dayData->avg('temperature'), 1),
                    'rainfall' => round($dayData->sum('rain_1h'), 1),
                    'risk_level' => $dayData->max('weather_risk_level'),
                ];
            })->toArray(),
        ];
    }

    /**
     * 警備地点別天気サマリー取得
     */
    private function getLocationWeatherSummary(): array
    {
        $locations = Weather::select('location_name')
            ->distinct()
            ->orderBy('location_name')
            ->pluck('location_name');

        $summary = [];

        foreach ($locations as $location) {
            $latest = Weather::getLatestByLocation($location);
            if ($latest) {
                $stats = Weather::getWeatherStats($location, 7);
                $summary[$location] = [
                    'latest' => $latest,
                    'stats' => $stats,
                ];
            }
        }

        return $summary;
    }
}
