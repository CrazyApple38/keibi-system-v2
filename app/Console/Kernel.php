<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * アプリケーションで提供するArtisanコマンド
     */
    protected $commands = [
        Commands\UpdateWeatherCommand::class,
    ];

    /**
     * アプリケーションのコマンドスケジュール定義
     */
    protected function schedule(Schedule $schedule): void
    {
        // 天気情報自動更新（30分毎）
        $schedule->command('weather:update --force')
                 ->everyThirtyMinutes()
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/weather-update.log'));

        // 天気データクリーンアップ（毎日午前2時）
        $schedule->command('weather:update --cleanup --force')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/weather-cleanup.log'));

        // Laravel標準のログローテーション（毎日午前1時）
        $schedule->command('log:clear')
                 ->dailyAt('01:00')
                 ->withoutOverlapping();
    }

    /**
     * アプリケーションのコマンド登録
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
