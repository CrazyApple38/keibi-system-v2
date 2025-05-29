<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * 天気情報モデル
 * 
 * 警備業務における天気情報の管理を行う
 * OpenWeatherMap APIから取得したデータを保存・管理
 * 
 * @property int $id
 * @property string $location_name 場所名
 * @property float $latitude 緯度
 * @property float $longitude 経度
 * @property \Illuminate\Support\Carbon $weather_date 天気予報日時
 * @property string $weather_main 天気メイン
 * @property string $weather_description 天気詳細説明
 * @property string $weather_icon 天気アイコンコード
 * @property float $temperature 現在気温
 * @property float $feels_like 体感気温
 * @property float $temp_min 最低気温
 * @property float $temp_max 最高気温
 * @property int $humidity 湿度
 * @property int $pressure 気圧
 * @property int|null $sea_level 海面気圧
 * @property int|null $ground_level 地上気圧
 * @property float|null $wind_speed 風速
 * @property int|null $wind_deg 風向
 * @property float|null $wind_gust 突風
 * @property float|null $rain_1h 1時間降水量
 * @property float|null $rain_3h 3時間降水量
 * @property float|null $snow_1h 1時間降雪量
 * @property float|null $snow_3h 3時間降雪量
 * @property int $clouds 雲量
 * @property int|null $visibility 視程
 * @property float|null $uv_index UVインデックス
 * @property string $weather_risk_level 警備業務リスクレベル
 * @property array|null $weather_alerts 気象警報・注意報
 * @property bool $outdoor_work_suitable 屋外作業適性
 * @property string $data_type データタイプ
 * @property string $api_source APIソース
 * @property \Illuminate\Support\Carbon $api_fetched_at API取得日時
 * @property array|null $raw_data 生データ
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Weather extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * テーブル名
     */
    protected $table = 'weather_data';

    /**
     * マスアサインメント可能な属性
     */
    protected $fillable = [
        'location_name',
        'latitude',
        'longitude',
        'weather_date',
        'weather_main',
        'weather_description',
        'weather_icon',
        'temperature',
        'feels_like',
        'temp_min',
        'temp_max',
        'humidity',
        'pressure',
        'sea_level',
        'ground_level',
        'wind_speed',
        'wind_deg',
        'wind_gust',
        'rain_1h',
        'rain_3h',
        'snow_1h',
        'snow_3h',
        'clouds',
        'visibility',
        'uv_index',
        'weather_risk_level',
        'weather_alerts',
        'outdoor_work_suitable',
        'data_type',
        'api_source',
        'api_fetched_at',
        'raw_data',
    ];

    /**
     * キャスト設定
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'weather_date' => 'datetime',
        'temperature' => 'decimal:2',
        'feels_like' => 'decimal:2',
        'temp_min' => 'decimal:2',
        'temp_max' => 'decimal:2',
        'wind_speed' => 'decimal:2',
        'wind_gust' => 'decimal:2',
        'rain_1h' => 'decimal:2',
        'rain_3h' => 'decimal:2',
        'snow_1h' => 'decimal:2',
        'snow_3h' => 'decimal:2',
        'uv_index' => 'decimal:2',
        'weather_alerts' => 'array',
        'outdoor_work_suitable' => 'boolean',
        'api_fetched_at' => 'datetime',
        'raw_data' => 'array',
    ];

    /**
     * リスクレベル定数
     */
    const RISK_LEVELS = [
        'low' => '低',
        'medium' => '中',
        'high' => '高',
        'critical' => '危険',
    ];

    /**
     * データタイプ定数
     */
    const DATA_TYPES = [
        'current' => '現在',
        'forecast' => '予報',
    ];

    /**
     * 天気アイコンURL取得
     */
    public function getWeatherIconUrlAttribute(): string
    {
        return "https://openweathermap.org/img/wn/{$this->weather_icon}@2x.png";
    }

    /**
     * 風向文字列取得
     */
    public function getWindDirectionAttribute(): string
    {
        if (is_null($this->wind_deg)) {
            return '不明';
        }

        $directions = [
            '北', '北北東', '北東', '東北東',
            '東', '東南東', '南東', '南南東',
            '南', '南南西', '南西', '西南西',
            '西', '西北西', '北西', '北北西'
        ];

        $index = round($this->wind_deg / 22.5) % 16;
        return $directions[$index];
    }

    /**
     * リスクレベル日本語取得
     */
    public function getRiskLevelJapaneseAttribute(): string
    {
        return self::RISK_LEVELS[$this->weather_risk_level] ?? '不明';
    }

    /**
     * データタイプ日本語取得
     */
    public function getDataTypeJapaneseAttribute(): string
    {
        return self::DATA_TYPES[$this->data_type] ?? '不明';
    }

    /**
     * 体感温度差取得
     */
    public function getFeelsLikeDifferenceAttribute(): float
    {
        return round($this->feels_like - $this->temperature, 1);
    }

    /**
     * 警備業務への影響度計算
     */
    public function calculateSecurityImpact(): array
    {
        $impact = [
            'overall_score' => 0,
            'factors' => [],
            'recommendations' => [],
        ];

        // 気温による影響
        if ($this->temperature < 0) {
            $impact['overall_score'] += 30;
            $impact['factors'][] = '極低温（凍結・滑りやすさに注意）';
            $impact['recommendations'][] = '防寒装備の徹底、滑り止め対策';
        } elseif ($this->temperature > 35) {
            $impact['overall_score'] += 25;
            $impact['factors'][] = '猛暑（熱中症リスク）';
            $impact['recommendations'][] = 'こまめな水分補給、休憩時間の確保';
        }

        // 降水量による影響
        if ($this->rain_1h > 10) {
            $impact['overall_score'] += 20;
            $impact['factors'][] = '強雨（視界不良、滑りやすさ）';
            $impact['recommendations'][] = '雨具着用、注意深い巡回';
        }

        // 風速による影響
        if ($this->wind_speed > 10) {
            $impact['overall_score'] += 15;
            $impact['factors'][] = '強風（物の飛散、歩行困難）';
            $impact['recommendations'][] = '屋外警備時の安全確認、固定物チェック';
        }

        // 視程による影響
        if ($this->visibility && $this->visibility < 1000) {
            $impact['overall_score'] += 25;
            $impact['factors'][] = '視界不良（霧・濃霧）';
            $impact['recommendations'][] = '照明設備確認、警戒レベル向上';
        }

        // UVインデックスによる影響
        if ($this->uv_index && $this->uv_index > 8) {
            $impact['overall_score'] += 10;
            $impact['factors'][] = '紫外線強度高';
            $impact['recommendations'][] = '日焼け対策、帽子・サングラス着用';
        }

        return $impact;
    }

    /**
     * 指定場所の最新天気情報取得
     */
    public static function getLatestByLocation(string $locationName): ?self
    {
        return self::where('location_name', $locationName)
                   ->orderBy('weather_date', 'desc')
                   ->first();
    }

    /**
     * 指定日時範囲の天気予報取得
     */
    public static function getForecastByDateRange(
        string $locationName, 
        Carbon $startDate, 
        Carbon $endDate
    ): \Illuminate\Database\Eloquent\Collection {
        return self::where('location_name', $locationName)
                   ->where('data_type', 'forecast')
                   ->whereBetween('weather_date', [$startDate, $endDate])
                   ->orderBy('weather_date')
                   ->get();
    }

    /**
     * 高リスク天気条件の検索
     */
    public static function getHighRiskWeather(Carbon $date = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::whereIn('weather_risk_level', ['high', 'critical']);
        
        if ($date) {
            $query->whereDate('weather_date', $date);
        }
        
        return $query->orderBy('weather_date')->get();
    }

    /**
     * 天気統計情報取得
     */
    public static function getWeatherStats(string $locationName, int $days = 7): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $data = self::where('location_name', $locationName)
                    ->where('weather_date', '>=', $startDate)
                    ->get();

        return [
            'avg_temperature' => $data->avg('temperature'),
            'max_temperature' => $data->max('temperature'),
            'min_temperature' => $data->min('temperature'),
            'avg_humidity' => $data->avg('humidity'),
            'total_rainfall' => $data->sum('rain_1h'),
            'sunny_days' => $data->where('weather_main', 'Clear')->count(),
            'rainy_days' => $data->whereIn('weather_main', ['Rain', 'Drizzle'])->count(),
            'high_risk_days' => $data->whereIn('weather_risk_level', ['high', 'critical'])->count(),
        ];
    }
}
