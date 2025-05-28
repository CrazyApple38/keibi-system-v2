<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * プロジェクト（案件）モデル
 * 
 * 警備業務の案件・プロジェクトを管理
 * 
 * @property int $id
 * @property int $customer_id 顧客ID
 * @property string $project_code プロジェクトコード
 * @property string $name プロジェクト名
 * @property string|null $description 詳細説明
 * @property string $project_type プロジェクト種別
 * @property string $priority 優先度（high, medium, low）
 * @property string $status ステータス
 * @property \DateTime|null $start_date 開始日
 * @property \DateTime|null $end_date 終了日
 * @property string|null $site_address 現場住所
 * @property array|null $site_coordinates 現場座標（JSON）
 * @property string|null $contact_person 現場責任者
 * @property string|null $contact_phone 現場責任者電話番号
 * @property array|null $required_skills 必要スキル（JSON）
 * @property int|null $required_guards 必要警備員数
 * @property array|null $special_instructions 特別指示（JSON）
 * @property decimal $budget_amount 予算金額
 * @property string|null $notes 備考
 * @property int $created_by 作成者ID
 * @property \DateTime|null $deleted_at 削除日時
 * @property \DateTime $created_at 作成日時
 * @property \DateTime $updated_at 更新日時
 */
class Project extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * テーブル名
     */
    protected $table = 'projects';

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'customer_id',
        'project_code',
        'name',
        'description',
        'project_type',
        'priority',
        'status',
        'start_date',
        'end_date',
        'site_address',
        'site_coordinates',
        'contact_person',
        'contact_phone',
        'required_skills',
        'required_guards',
        'special_instructions',
        'budget_amount',
        'notes',
        'created_by',
        // Google Maps関連
        'location_lat',
        'location_lng',
        'location_address',
        'location_building',
        'location_floor',
        'location_room',
        'location_notes',
        'location_radius',
        'parking_info',
        'access_info',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'site_coordinates' => 'array',
        'required_skills' => 'array',
        'special_instructions' => 'array',
        'budget_amount' => 'decimal:2',
        'deleted_at' => 'datetime',
        // Google Maps関連
        'location_lat' => 'decimal:8',
        'location_lng' => 'decimal:8',
        'location_radius' => 'decimal:2',
        'parking_info' => 'array',
        'access_info' => 'array',
    ];

    /**
     * プロジェクト種別定数
     */
    public const TYPE_EVENT_SECURITY = 'event_security';
    public const TYPE_FACILITY_SECURITY = 'facility_security';
    public const TYPE_CONSTRUCTION_SECURITY = 'construction_security';
    public const TYPE_TRAFFIC_CONTROL = 'traffic_control';
    public const TYPE_PATROL = 'patrol';
    public const TYPE_OTHER = 'other';

    /**
     * 優先度定数
     */
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_LOW = 'low';

    /**
     * ステータス定数
     */
    public const STATUS_PLANNING = 'planning';
    public const STATUS_QUOTATION = 'quotation';
    public const STATUS_CONTRACTED = 'contracted';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_ON_HOLD = 'on_hold';

    /**
     * 利用可能なプロジェクト種別リスト
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_EVENT_SECURITY => 'イベント警備',
            self::TYPE_FACILITY_SECURITY => '施設警備',
            self::TYPE_CONSTRUCTION_SECURITY => '工事現場警備',
            self::TYPE_TRAFFIC_CONTROL => '交通誘導',
            self::TYPE_PATROL => '巡回警備',
            self::TYPE_OTHER => 'その他',
        ];
    }

    /**
     * 利用可能な優先度リスト
     */
    public static function getAvailablePriorities(): array
    {
        return [
            self::PRIORITY_HIGH => '高',
            self::PRIORITY_MEDIUM => '中',
            self::PRIORITY_LOW => '低',
        ];
    }

    /**
     * 利用可能なステータスリスト
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_PLANNING => '企画中',
            self::STATUS_QUOTATION => '見積中',
            self::STATUS_CONTRACTED => '契約済',
            self::STATUS_IN_PROGRESS => '実施中',
            self::STATUS_COMPLETED => '完了',
            self::STATUS_CANCELLED => 'キャンセル',
            self::STATUS_ON_HOLD => '保留',
        ];
    }

    /**
     * 顧客とのリレーション
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * 作成者とのリレーション
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * シフトとのリレーション
     */
    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    /**
     * 見積とのリレーション
     */
    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    /**
     * 契約とのリレーション
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * 請求とのリレーション
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * 日報とのリレーション
     */
    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }

    /**
     * 進行中のプロジェクトかどうかを判定
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * 完了したプロジェクトかどうかを判定
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * 契約済みプロジェクトかどうかを判定
     */
    public function isContracted(): bool
    {
        return $this->status === self::STATUS_CONTRACTED;
    }

    /**
     * プロジェクトの期間日数を取得
     */
    public function getDurationDaysAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * プロジェクトの進捗率を取得
     */
    public function getProgressPercentageAttribute(): float
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        $now = Carbon::now();
        if ($now->lt($this->start_date)) {
            return 0; // 開始前
        }
        if ($now->gt($this->end_date)) {
            return 100; // 終了後
        }

        $totalDays = $this->start_date->diffInDays($this->end_date);
        $elapsedDays = $this->start_date->diffInDays($now);
        
        return $totalDays > 0 ? ($elapsedDays / $totalDays) * 100 : 0;
    }

    /**
     * プロジェクトコードの自動生成
     */
    public static function generateProjectCode(int $customerId, string $projectType): string
    {
        $typeCode = strtoupper(substr($projectType, 0, 3));
        $customerCode = str_pad($customerId, 4, '0', STR_PAD_LEFT);
        $year = date('Y');
        $sequence = static::where('customer_id', $customerId)
            ->whereYear('created_at', $year)
            ->count() + 1;
        $sequenceCode = str_pad($sequence, 3, '0', STR_PAD_LEFT);

        return "{$typeCode}-{$customerCode}-{$year}-{$sequenceCode}";
    }

    /**
     * 必要警備員数の総計を取得
     */
    public function getTotalRequiredGuardsAttribute(): int
    {
        return $this->shifts()->sum('required_guards') ?: $this->required_guards ?: 0;
    }

    /**
     * 現在アサインされている警備員数を取得
     */
    public function getAssignedGuardsCountAttribute(): int
    {
        return $this->shifts()->whereHas('guardAssignments')->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Google Maps関連メソッド
    |--------------------------------------------------------------------------
    */

    /**
     * 位置情報を持っているかを判定
     */
    public function hasLocation(): bool
    {
        return !is_null($this->location_lat) && !is_null($this->location_lng);
    }

    /**
     * 完全な住所を取得
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->location_address,
            $this->location_building,
            $this->location_floor ? $this->location_floor . '階' : null,
            $this->location_room ? $this->location_room . '号室' : null,
        ]);

        return implode(' ', $parts);
    }

    /**
     * 指定座標から現場までの距離を計算（キロメートル）
     */
    public function getDistanceFromLocation(float $lat, float $lng): ?float
    {
        if (!$this->hasLocation()) {
            return null;
        }

        // ハヴァサイン公式を使用した距離計算
        $earthRadius = 6371; // 地球の半径（km）
        
        $dLat = deg2rad($this->location_lat - $lat);
        $dLng = deg2rad($this->location_lng - $lng);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat)) * cos(deg2rad($this->location_lat)) * 
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    /**
     * 指定警備員から現場までの距離を取得
     */
    public function getDistanceFromGuard(Guard $guard): ?float
    {
        if (!$guard->hasLocation()) {
            return null;
        }

        return $this->getDistanceFromLocation($guard->location_lat, $guard->location_lng);
    }

    /**
     * 近隣の警備員を検索
     */
    public function getNearbyGuards(float $radiusKm = 10, bool $availableOnly = true)
    {
        if (!$this->hasLocation()) {
            return collect();
        }

        $query = Guard::whereNotNull('location_lat')
            ->whereNotNull('location_lng')
            ->where('location_sharing_enabled', true)
            ->where('status', 'active');

        if ($availableOnly) {
            // 現在シフトに入っていない警備員のみ
            $query->whereDoesntHave('shifts', function($q) {
                $now = now();
                $q->where('shift_date', $now->format('Y-m-d'))
                  ->where('start_time', '<=', $now->format('H:i:s'))
                  ->where('end_time', '>=', $now->format('H:i:s'))
                  ->where('status', 'active');
            });
        }

        // 距離計算してフィルタリング
        $guards = $query->get()->filter(function($guard) use ($radiusKm) {
            $distance = $this->getDistanceFromGuard($guard);
            return $distance !== null && $distance <= $radiusKm;
        });

        // 距離順でソート
        return $guards->sortBy(function($guard) {
            return $this->getDistanceFromGuard($guard);
        })->map(function($guard) {
            $guard->distance_to_project = $this->getDistanceFromGuard($guard);
            return $guard;
        });
    }

    /**
     * プロジェクトが指定範囲内にあるかを判定
     */
    public function isWithinRange(float $lat, float $lng, float $radiusKm): bool
    {
        $distance = $this->getDistanceFromLocation($lat, $lng);
        return $distance !== null && $distance <= $radiusKm;
    }

    /**
     * プロジェクトの有効範囲内に警備員がいるかを判定
     */
    public function isGuardInRange(Guard $guard): bool
    {
        if (!$this->hasLocation() || !$guard->hasLocation()) {
            return false;
        }

        $distance = $this->getDistanceFromGuard($guard);
        $allowedRadius = $this->location_radius / 1000; // メートルをキロメートルに変換

        return $distance !== null && $distance <= $allowedRadius;
    }

    /**
     * Google Mapsで表示するための情報を取得
     */
    public function getMapInfoAttribute(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'project_code' => $this->project_code,
            'position' => [
                'lat' => $this->location_lat,
                'lng' => $this->location_lng,
            ],
            'address' => $this->full_address,
            'radius' => $this->location_radius,
            'project_type' => $this->project_type,
            'status' => $this->status,
            'priority' => $this->priority,
            'customer' => $this->customer?->name,
            'contact_person' => $this->contact_person,
            'contact_phone' => $this->contact_phone,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'required_guards' => $this->required_guards,
            'assigned_guards' => $this->assigned_guards_count,
            'parking_info' => $this->parking_info,
            'access_info' => $this->access_info,
        ];
    }

    /**
     * マーカーの色を取得（プロジェクト状況に応じて）
     */
    public function getMarkerColorAttribute(): string
    {
        switch ($this->status) {
            case self::STATUS_IN_PROGRESS:
                return '#28a745'; // 緑
            case self::STATUS_PLANNING:
                return '#ffc107'; // 黄色
            case self::STATUS_CONTRACTED:
                return '#007bff'; // 青
            case self::STATUS_COMPLETED:
                return '#6c757d'; // グレー
            case self::STATUS_CANCELLED:
                return '#dc3545'; // 赤
            case self::STATUS_ON_HOLD:
                return '#fd7e14'; // オレンジ
            default:
                return '#17a2b8'; // 水色
        }
    }

    /**
     * 最適な警備員配置を計算
     */
    public function getOptimalGuardPlacement(int $guardCount = null): array
    {
        $guardCount = $guardCount ?? $this->required_guards ?? 1;
        
        if (!$this->hasLocation() || $guardCount <= 0) {
            return [];
        }

        $placements = [];
        $radius = $this->location_radius / 1000; // メートルをキロメートルに変換
        
        // 現場を中心とした配置パターンを計算
        for ($i = 0; $i < $guardCount; $i++) {
            $angle = ($i / $guardCount) * 2 * M_PI;
            $distance = $radius * 0.8; // 有効範囲の80%の位置に配置
            
            $lat = $this->location_lat + ($distance / 111.32) * cos($angle);
            $lng = $this->location_lng + ($distance / (111.32 * cos(deg2rad($this->location_lat)))) * sin($angle);
            
            $placements[] = [
                'position' => $i + 1,
                'lat' => $lat,
                'lng' => $lng,
                'description' => "配置ポイント" . ($i + 1),
                'patrol_area' => [
                    'center' => ['lat' => $lat, 'lng' => $lng],
                    'radius' => $radius / $guardCount,
                ],
            ];
        }

        return $placements;
    }
}
