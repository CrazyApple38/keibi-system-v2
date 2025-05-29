<?php

namespace App\Console\Commands;

use App\Services\WeatherService;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * 天気情報自動更新コマンド
 * 
 * 全警備地点の天気情報を定期的に更新するコマンド
 * cron設定で30分毎に実行することを推奨
 */
class UpdateWeatherCommand extends Command
{
    /**
     * コマンド名
     */
    protected $signature = 'weather:update
                            {--locations=* : 特定の地点のみ更新 (location_name)}
                            {--force : 強制実行（確認なし）}
                            {--cleanup : 古いデータの削除も実行}';

    /**
     * コマンド説明
     */
    protected $description = '警備地点の天気情報を自動更新します';

    /**
     * 天気予報サービス
     */
    private WeatherService $weatherService;

    /**
     * コンストラクタ
     */
    public function __construct(WeatherService $weatherService)
    {
        parent::__construct();
        $this->weatherService = $weatherService;
    }

    /**
     * コマンド実行
     */
    public function handle(): int
    {
        $this->info('=== 天気情報自動更新開始 ===');
        $this->info('開始時刻: ' . now()->format('Y-m-d H:i:s'));

        // 設定確認
        if (!config('services.weather.enabled')) {
            $this->error('天気予報APIが無効になっています。.envファイルでWEATHER_API_ENABLED=trueに設定してください。');
            return Command::FAILURE;
        }

        if (!config('services.weather.api_key')) {
            $this->error('天気予報APIキーが設定されていません。.envファイルでWEATHER_API_KEYを設定してください。');
            return Command::FAILURE;
        }

        try {
            // 特定地点のみ更新する場合
            $specificLocations = $this->option('locations');
            if (!empty($specificLocations)) {
                return $this->updateSpecificLocations($specificLocations);
            }

            // 全警備地点の天気情報更新
            return $this->updateAllSecurityLocations();

        } catch (\Exception $e) {
            $this->error('天気情報更新中にエラーが発生しました: ' . $e->getMessage());
            Log::error('Weather update command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * 全警備地点の天気情報更新
     */
    private function updateAllSecurityLocations(): int
    {
        $this->info('全警備地点の天気情報を更新します...');

        // アクティブなプロジェクトから警備地点を取得
        $projects = Project::where('status', 'active')
            ->whereNotNull('location_address')
            ->get();

        if ($projects->isEmpty()) {
            $this->warn('更新対象の警備地点が見つかりませんでした。');
            return Command::SUCCESS;
        }

        $totalProjects = $projects->count();
        $successCount = 0;
        $failureCount = 0;

        $this->info("更新対象: {$totalProjects}地点");

        // 確認プロンプト
        if (!$this->option('force')) {
            if (!$this->confirm("{$totalProjects}地点の天気情報を更新しますか？")) {
                $this->info('更新をキャンセルしました。');
                return Command::SUCCESS;
            }
        }

        // プログレスバー表示
        $progressBar = $this->output->createProgressBar($totalProjects);
        $progressBar->start();

        foreach ($projects as $project) {
            try {
                // 住所から座標を取得
                $coordinates = $this->getCoordinatesFromAddress($project->location_address);
                
                if (!$coordinates) {
                    $this->newLine();
                    $this->warn("座標取得失敗: {$project->location_address}");
                    $failureCount++;
                    $progressBar->advance();
                    continue;
                }

                // 現在の天気情報を取得
                $weather = $this->weatherService->getCurrentWeather(
                    $project->location_address,
                    $coordinates['lat'],
                    $coordinates['lng']
                );

                if ($weather) {
                    // 天気予報も取得
                    $this->weatherService->getForecastWeather(
                        $project->location_address,
                        $coordinates['lat'],
                        $coordinates['lng']
                    );
                    $successCount++;
                } else {
                    $failureCount++;
                }

                $progressBar->advance();

                // APIレート制限を避けるため少し待機
                usleep(100000); // 0.1秒

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("エラー [{$project->location_address}]: " . $e->getMessage());
                $failureCount++;
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine();

        // 結果表示
        $this->info('=== 更新結果 ===');
        $this->info("成功: {$successCount}地点");
        if ($failureCount > 0) {
            $this->warn("失敗: {$failureCount}地点");
        }

        // 古いデータのクリーンアップ
        if ($this->option('cleanup')) {
            $this->cleanupOldData();
        }

        $this->info('終了時刻: ' . now()->format('Y-m-d H:i:s'));
        $this->info('=== 天気情報自動更新完了 ===');

        return $successCount > 0 ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * 特定地点の天気情報更新
     */
    private function updateSpecificLocations(array $locations): int
    {
        $this->info('特定地点の天気情報を更新します...');
        $this->info('対象地点: ' . implode(', ', $locations));

        $successCount = 0;
        $failureCount = 0;

        foreach ($locations as $locationName) {
            try {
                $this->info("更新中: {$locationName}");

                // 既存の天気データから座標を取得
                $existingWeather = \App\Models\Weather::where('location_name', $locationName)
                    ->orderBy('weather_date', 'desc')
                    ->first();

                if (!$existingWeather) {
                    $this->warn("地点が見つかりません: {$locationName}");
                    $failureCount++;
                    continue;
                }

                // 天気情報を更新
                $weather = $this->weatherService->getCurrentWeather(
                    $locationName,
                    $existingWeather->latitude,
                    $existingWeather->longitude
                );

                if ($weather) {
                    $this->weatherService->getForecastWeather(
                        $locationName,
                        $existingWeather->latitude,
                        $existingWeather->longitude
                    );
                    $this->info("更新完了: {$locationName}");
                    $successCount++;
                } else {
                    $this->error("更新失敗: {$locationName}");
                    $failureCount++;
                }

                // APIレート制限を避けるため待機
                usleep(100000); // 0.1秒

            } catch (\Exception $e) {
                $this->error("エラー [{$locationName}]: " . $e->getMessage());
                $failureCount++;
            }
        }

        // 結果表示
        $this->info('=== 更新結果 ===');
        $this->info("成功: {$successCount}地点");
        if ($failureCount > 0) {
            $this->warn("失敗: {$failureCount}地点");
        }

        return $successCount > 0 ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * 住所から座標取得
     */
    private function getCoordinatesFromAddress(string $address): ?array
    {
        try {
            $apiKey = config('services.google_maps.api_key');
            if (!$apiKey) {
                return null;
            }

            $response = \Illuminate\Support\Facades\Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['results'][0]['geometry']['location'])) {
                    return [
                        'lat' => $data['results'][0]['geometry']['location']['lat'],
                        'lng' => $data['results'][0]['geometry']['location']['lng'],
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Geocoding error', [
                'address' => $address,
                'error' => $e->getMessage(),
            ]);
        }
        
        return null;
    }

    /**
     * 古いデータのクリーンアップ
     */
    private function cleanupOldData(): void
    {
        $this->info('古い天気データをクリーンアップしています...');

        try {
            $daysToKeep = config('services.weather.data_retention.current_data_days', 30);
            $deletedCount = $this->weatherService->cleanupOldWeatherData($daysToKeep);
            
            $this->info("クリーンアップ完了: {$deletedCount}件の古いデータを削除しました");
        } catch (\Exception $e) {
            $this->error('クリーンアップ中にエラーが発生しました: ' . $e->getMessage());
        }
    }
}
