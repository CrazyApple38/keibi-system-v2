<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Weather;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// 天気情報関連のクイックコマンド
Artisan::command('weather:status', function () {
    $this->info('=== 天気データベース状況 ===');
    
    $totalRecords = Weather::count();
    $currentData = Weather::where('data_type', 'current')->count();
    $forecastData = Weather::where('data_type', 'forecast')->count();
    $locations = Weather::distinct('location_name')->count();
    $latestUpdate = Weather::orderBy('api_fetched_at', 'desc')->first();
    
    $this->info("総レコード数: {$totalRecords}");
    $this->info("現在データ: {$currentData}");
    $this->info("予報データ: {$forecastData}");
    $this->info("監視地点数: {$locations}");
    
    if ($latestUpdate) {
        $this->info("最終更新: {$latestUpdate->api_fetched_at->format('Y-m-d H:i:s')} ({$latestUpdate->location_name})");
    }
    
    $this->info('=== 高リスク地点 ===');
    $highRiskWeather = Weather::whereIn('weather_risk_level', ['high', 'critical'])
        ->where('weather_date', '>=', now()->subHours(6))
        ->orderBy('weather_date', 'desc')
        ->limit(5)
        ->get();
    
    if ($highRiskWeather->count() > 0) {
        foreach ($highRiskWeather as $weather) {
            $this->warn("{$weather->location_name}: {$weather->risk_level_japanese}リスク - {$weather->weather_description}");
        }
    } else {
        $this->info('現在、高リスクの天気条件はありません。');
    }
})->purpose('天気データベースの状況を表示');

Artisan::command('weather:test {location}', function ($location) {
    $this->info("天気情報テスト: {$location}");
    
    // 最新の天気データを取得
    $weather = Weather::where('location_name', 'LIKE', "%{$location}%")
        ->orderBy('weather_date', 'desc')
        ->first();
    
    if (!$weather) {
        $this->error("地点が見つかりません: {$location}");
        return;
    }
    
    $this->info("地点名: {$weather->location_name}");
    $this->info("日時: {$weather->weather_date->format('Y-m-d H:i:s')}");
    $this->info("天気: {$weather->weather_description}");
    $this->info("気温: {$weather->temperature}°C");
    $this->info("湿度: {$weather->humidity}%");
    $this->info("リスクレベル: {$weather->risk_level_japanese}");
    
    if ($weather->weather_alerts) {
        $this->warn('警報・注意報:');
        foreach ($weather->weather_alerts as $alert) {
            $this->warn("  - {$alert['message']}");
        }
    }
    
    $impact = $weather->calculateSecurityImpact();
    if ($impact['overall_score'] > 0) {
        $this->warn("警備業務影響スコア: {$impact['overall_score']}点");
        if (!empty($impact['recommendations'])) {
            $this->info('推奨対策:');
            foreach ($impact['recommendations'] as $recommendation) {
                $this->info("  - {$recommendation}");
            }
        }
    }
})->purpose('指定地点の天気情報を表示');

Artisan::command('weather:alerts', function () {
    $this->info('=== 現在の天気アラート ===');
    
    $alerts = Weather::whereNotNull('weather_alerts')
        ->where('weather_date', '>=', now()->subHours(3))
        ->whereIn('weather_risk_level', ['high', 'critical'])
        ->orderBy('weather_date', 'desc')
        ->get();
    
    if ($alerts->count() === 0) {
        $this->info('現在、天気アラートはありません。');
        return;
    }
    
    foreach ($alerts as $weather) {
        $this->newLine();
        $this->warn("【{$weather->risk_level_japanese}リスク】{$weather->location_name}");
        $this->info("時刻: {$weather->weather_date->format('Y-m-d H:i')}");
        $this->info("天気: {$weather->weather_description}");
        $this->info("気温: {$weather->temperature}°C");
        
        if ($weather->weather_alerts) {
            foreach ($weather->weather_alerts as $alert) {
                $this->error("⚠️  {$alert['message']}");
            }
        }
        
        if (!$weather->outdoor_work_suitable) {
            $this->error('⚠️  屋外業務注意');
        }
    }
})->purpose('現在の天気アラートを表示');

