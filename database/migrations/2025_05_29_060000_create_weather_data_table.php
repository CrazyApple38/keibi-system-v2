<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーション実行
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('weather_data', function (Blueprint $table) {
            $table->id();
            
            // 場所情報
            $table->string('location_name')->comment('場所名');
            $table->decimal('latitude', 10, 8)->comment('緯度');
            $table->decimal('longitude', 11, 8)->comment('経度');
            
            // 天気基本情報
            $table->datetime('weather_date')->comment('天気予報日時');
            $table->string('weather_main')->comment('天気メイン（晴れ、雨等）');
            $table->string('weather_description')->comment('天気詳細説明');
            $table->string('weather_icon')->comment('天気アイコンコード');
            
            // 気温情報
            $table->decimal('temperature', 5, 2)->comment('現在気温（℃）');
            $table->decimal('feels_like', 5, 2)->comment('体感気温（℃）');
            $table->decimal('temp_min', 5, 2)->comment('最低気温（℃）');
            $table->decimal('temp_max', 5, 2)->comment('最高気温（℃）');
            
            // 湿度・気圧情報
            $table->integer('humidity')->comment('湿度（%）');
            $table->integer('pressure')->comment('気圧（hPa）');
            $table->integer('sea_level')->nullable()->comment('海面気圧（hPa）');
            $table->integer('ground_level')->nullable()->comment('地上気圧（hPa）');
            
            // 風情報
            $table->decimal('wind_speed', 5, 2)->nullable()->comment('風速（m/s）');
            $table->integer('wind_deg')->nullable()->comment('風向（度）');
            $table->decimal('wind_gust', 5, 2)->nullable()->comment('突風（m/s）');
            
            // 降水量・雲量
            $table->decimal('rain_1h', 8, 2)->nullable()->comment('1時間降水量（mm）');
            $table->decimal('rain_3h', 8, 2)->nullable()->comment('3時間降水量（mm）');
            $table->decimal('snow_1h', 8, 2)->nullable()->comment('1時間降雪量（mm）');
            $table->decimal('snow_3h', 8, 2)->nullable()->comment('3時間降雪量（mm）');
            $table->integer('clouds')->comment('雲量（%）');
            
            // 可視性・UVインデックス
            $table->integer('visibility')->nullable()->comment('視程（m）');
            $table->decimal('uv_index', 4, 2)->nullable()->comment('UVインデックス');
            
            // 警備業務関連指標
            $table->enum('weather_risk_level', ['low', 'medium', 'high', 'critical'])
                  ->default('low')->comment('警備業務リスクレベル');
            $table->json('weather_alerts')->nullable()->comment('気象警報・注意報');
            $table->boolean('outdoor_work_suitable')->default(true)->comment('屋外作業適性');
            
            // データソース・管理情報
            $table->enum('data_type', ['current', 'forecast'])->comment('データタイプ');
            $table->string('api_source')->default('openweather')->comment('APIソース');
            $table->timestamp('api_fetched_at')->comment('API取得日時');
            $table->json('raw_data')->nullable()->comment('生データ（JSON）');
            
            // 標準タイムスタンプ
            $table->timestamps();
            $table->softDeletes();
            
            // インデックス設定
            $table->index(['location_name', 'weather_date']);
            $table->index(['latitude', 'longitude']);
            $table->index(['weather_date', 'data_type']);
            $table->index(['weather_risk_level']);
            $table->index(['api_fetched_at']);
        });
    }

    /**
     * マイグレーション巻き戻し
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_data');
    }
};
