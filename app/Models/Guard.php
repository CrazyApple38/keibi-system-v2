<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 警備員モデル
 * 
 * 警備業務に従事する警備員の情報を管理
 * 
 * @property int $id
 * @property int|null $user_id 関連ユーザーID
 * @property string $guard_code 警備員コード
 * @property string $name 氏名
 * @property string $name_kana 氏名（カナ）
 * @property \DateTime|null $birth_date 生年月日
 * @property string|null $gender 性別（male, female, other）
 * @property string|null $postal_code 郵便番号
 * @property string|null $address 住所
 * @property string|null $phone_number 電話番号
 * @property string|null $mobile_number 携帯電話番号
 * @property string|null $email メールアドレス
 * @property string|null $emergency_contact 緊急連絡先
 * @property string|null $emergency_phone 緊急連絡先電話番号
 * @property \DateTime|null $hire_date 入社日
 * @property string $employment_type 雇用形態（full_time, part_time, contract）
 * @property array|null $qualifications 資格情報（JSON）
 * @property array|null $skills スキル・経験（JSON）
 * @property string $status ステータス（active, inactive, suspended, retired）
 * @property decimal|null $hourly_rate 時給
 * @property array|null $available_times 勤務可能時間帯（JSON）
 * @property array|null $health_info 健康情報（JSON）
 * @property string|null $notes 備考
 * @property int $created_by 作成者ID
 * @property \DateTime|null $deleted_at 削除日時
 * @property \DateTime $created_at 作成日時
 * @property \DateTime $updated_at 更新日時
 */
class Guard extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * テーブル名
     */
    protected $table = 'guards';

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'user_id',
        'guard_code',
        'name',
        'name_kana',
        'birth_date',
        'gender',
        'postal_code',
        'address',
        'phone_number',
        'mobile_number',
        'email',
        'emergency_contact',
        'emergency_phone',
        'hire_date',
        'employment_type',
        'qualifications',
        'skills',
        'status',
        'hourly_rate',
        'available_times',
        'health_info',
        'notes',
        'created_by',
        // Google Maps関連
        'location_lat',
        'location_lng',
        'location_accuracy',
        'location_address',
        'location_updated_at',
        'location_sharing_enabled',
        'location_history',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'qualifications' => 'array',
        'skills' => 'array',
        'hourly_rate' => 'decimal:2',
        'available_times' => 'array',
        'health_info' => 'array',
        'deleted_at' => 'datetime',
        // Google Maps関連
        'location_lat' => 'decimal:8',
        'location_lng' => 'decimal:8',
        'location_accuracy' => 'decimal:2',
        'location_updated_at' => 'datetime',
        'location_sharing_enabled' => 'boolean',
        'location_history' => 'array',
    ];

    /**
     * 性別定数
     */
    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';
    public const GENDER_OTHER = 'other';

    /**
     * 雇用形態定数
     */
    public const EMPLOYMENT_FULL_TIME = 'full_time';
    public const EMPLOYMENT_PART_TIME = 'part_time';
    public const EMPLOYMENT_CONTRACT = 'contract';

    /**
     * ステータス定数
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_RETIRED = 'retired';

    /**
     * 利用可能な性別リスト
     */
    public static function getAvailableGenders(): array
    {
        return [
            self::GENDER_MALE => '男性',
            self::GENDER_FEMALE => '女性',
            self::GENDER_OTHER => 'その他',
        ];
    }

    /**
     * 利用可能な雇用形態リスト
     */
    public static function getAvailableEmploymentTypes(): array
    {
        return [
            self::EMPLOYMENT_FULL_TIME => '正社員',
            self::EMPLOYMENT_PART_TIME => 'パート・アルバイト',
            self::EMPLOYMENT_CONTRACT => '契約社員',
        ];
    }

    /**
     * 利用可能なステータスリスト
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => '勤務中',
            self::STATUS_INACTIVE => '休職中',
            self::STATUS_SUSPENDED => '停職中',
            self::STATUS_RETIRED => '退職',
        ];
    }

    /**
     * 関連ユーザーとのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
        return $this->belongsToMany(Shift::class, 'shift_guard_assignments')
            ->withPivot(['position', 'notes', 'created_at'])
            ->withTimestamps();
    }

    /**
     * 勤怠記録とのリレーション
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * 作成した日報とのリレーション
     */
    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class, 'created_by');
    }

    /**
     * 有効な警備員かどうかを判定
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * 正社員かどうかを判定
     */
    public function isFullTime(): bool
    {
        return $this->employment_type === self::EMPLOYMENT_FULL_TIME;
    }

    /**
     * 年齢を取得
     */
    public function getAgeAttribute(): int
    {
        if (!$this->birth_date) {
            return 0;
        }
        return $this->birth_date->age;
    }

    /**
     * 勤続年月を取得
     */
    public function getYearsOfServiceAttribute(): float
    {
        if (!$this->hire_date) {
            return 0;
        }
        return $this->hire_date->diffInYears(now());
    }

    /**
     * 警備員コードの自動生成
     */
    public static function generateGuardCode(): string
    {
        $year = date('Y');
        $sequence = static::whereYear('created_at', $year)->count() + 1;
        $sequenceCode = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        return "G{$year}{$sequenceCode}";
    }

    /**
     * 特定の資格を持っているかを判定
     */
    public function hasQualification(string $qualification): bool
    {
        $qualifications = $this->qualifications ?? [];
        return in_array($qualification, array_column($qualifications, 'name'));
    }

    /**
     * 特定のスキルを持っているかを判定
     */
    public function hasSkill(string $skill): bool
    {
        $skills = $this->skills ?? [];
        return in_array($skill, array_column($skills, 'name'));
    }

    /**
     * 指定日時に勤務可能かを判定
     */
    public function isAvailableAt(string $dayOfWeek, string $startTime, string $endTime): bool
    {
        $availableTimes = $this->available_times ?? [];
        
        if (!isset($availableTimes[$dayOfWeek])) {
            return false;
        }

        $daySchedule = $availableTimes[$dayOfWeek];
        
        foreach ($daySchedule as $slot) {
            if ($startTime >= $slot['start'] && $endTime <= $slot['end']) {
                return true;
            }
        }

        return false;
    }

    /**
     * 月間勤務時間を取得
     */
    public function getMonthlyWorkingHours(int $year, int $month): float
    {
        return $this->attendances()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('status', 'completed')
            ->sum('working_hours');
    }

    /**
     * 月間勤務日数を取得
     */
    public function getMonthlyWorkingDays(int $year, int $month): int
    {
        return $this->attendances()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('status', 'completed')
            ->distinct('date')
            ->count('date');
    }

    /**
     * 表示用の名前（コード + 名前）を取得
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->guard_code} {$this->name}";
    }

    /**
     * 連絡先情報の表示用フォーマット
     */
    public function getContactInfoAttribute(): array
    {
        $info = [];
        if ($this->phone_number) {
            $info['phone'] = $this->phone_number;
        }
        if ($this->mobile_number) {
            $info['mobile'] = $this->mobile_number;
        }
        if ($this->email) {
            $info['email'] = $this->email;
        }
        return $info;
    }

    /*
    |--------------------------------------------------------------------------
    | Google Maps関連メソッド
    |--------------------------------------------------------------------------
    */

    /**
     * 現在のシフトを取得
     */
    public function getCurrentShift()
    {
        $now = now();
        return $this->shifts()
            ->where('shift_date', $now->format('Y-m-d'))
            ->where('start_time', '<=', $now->format('H:i:s'))
            ->where('end_time', '>=', $now->format('H:i:s'))
            ->where('status', 'active')
            ->with(['project', 'project.customer'])
            ->first();
    }

    /**
     * 現在位置を持っているかを判定
     */
    public function hasLocation(): bool
    {
        return !is_null($this->location_lat) && !is_null($this->location_lng);
    }

    /**
     * 位置情報の有効性を判定（最新性チェック）
     */
    public function hasValidLocation(int $maxAgeMinutes = 30): bool
    {
        if (!$this->hasLocation() || !$this->location_updated_at) {
            return false;
        }

        return $this->location_updated_at->diffInMinutes(now()) <= $maxAgeMinutes;
    }

    /**
     * 位置情報が共有可能かを判定
     */
    public function canShareLocation(): bool
    {
        return $this->location_sharing_enabled && $this->hasLocation();
    }

    /**
     * 座標から指定地点までの距離を計算（キロメートル）
     */
    public function getDistanceToLocation(float $lat, float $lng): ?float
    {
        if (!$this->hasLocation()) {
            return null;
        }

        // ハヴァサイン公式を使用した距離計算
        $earthRadius = 6371; // 地球の半径（km）
        
        $dLat = deg2rad($lat - $this->location_lat);
        $dLng = deg2rad($lng - $this->location_lng);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($this->location_lat)) * cos(deg2rad($lat)) * 
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    /**
     * 指定プロジェクトまでの距離を取得
     */
    public function getDistanceToProject(Project $project): ?float
    {
        if (!$project->location_lat || !$project->location_lng) {
            return null;
        }

        return $this->getDistanceToLocation($project->location_lat, $project->location_lng);
    }

    /**
     * 近隣の警備員を検索
     */
    public function getNearbyGuards(float $radiusKm = 5, bool $activeOnly = true)
    {
        if (!$this->hasLocation()) {
            return collect();
        }

        $query = static::where('id', '!=', $this->id)
            ->whereNotNull('location_lat')
            ->whereNotNull('location_lng')
            ->where('location_sharing_enabled', true);

        if ($activeOnly) {
            $query->where('status', 'active');
        }

        // 距離計算のクエリ（簡易版）
        $guards = $query->get()->filter(function($guard) use ($radiusKm) {
            $distance = $this->getDistanceToLocation($guard->location_lat, $guard->location_lng);
            return $distance !== null && $distance <= $radiusKm;
        });

        // 距離順でソート
        return $guards->sortBy(function($guard) {
            return $this->getDistanceToLocation($guard->location_lat, $guard->location_lng);
        });
    }

    /**
     * 近隣のプロジェクトを検索
     */
    public function getNearbyProjects(float $radiusKm = 10, bool $activeOnly = true)
    {
        if (!$this->hasLocation()) {
            return collect();
        }

        $query = Project::whereNotNull('location_lat')
            ->whereNotNull('location_lng');

        if ($activeOnly) {
            $query->where('status', 'active');
        }

        // 距離計算のクエリ（簡易版）
        $projects = $query->get()->filter(function($project) use ($radiusKm) {
            $distance = $this->getDistanceToLocation($project->location_lat, $project->location_lng);
            return $distance !== null && $distance <= $radiusKm;
        });

        // 距離順でソート
        return $projects->sortBy(function($project) {
            return $this->getDistanceToLocation($project->location_lat, $project->location_lng);
        })->map(function($project) {
            $project->distance = $this->getDistanceToLocation($project->location_lat, $project->location_lng);
            return $project;
        });
    }

    /**
     * 位置履歴を更新
     */
    public function updateLocationHistory(): void
    {
        if (!$this->hasLocation()) {
            return;
        }

        $history = $this->location_history ?? [];
        
        // 新しい位置情報を追加
        $newPoint = [
            'lat' => $this->location_lat,
            'lng' => $this->location_lng,
            'accuracy' => $this->location_accuracy,
            'address' => $this->location_address,
            'timestamp' => now()->toISOString(),
        ];

        array_unshift($history, $newPoint);

        // 24時間以内のデータのみ保持
        $cutoffTime = now()->subHours(24);
        $history = array_filter($history, function($point) use ($cutoffTime) {
            return Carbon::parse($point['timestamp'])->gte($cutoffTime);
        });

        // 最大100件まで保持
        if (count($history) > 100) {
            $history = array_slice($history, 0, 100);
        }

        $this->location_history = $history;
        $this->save();
    }

    /**
     * 最後の位置更新からの経過時間を取得
     */
    public function getLocationAgeAttribute(): ?string
    {
        if (!$this->location_updated_at) {
            return null;
        }

        $diff = $this->location_updated_at->diffForHumans();
        return $diff;
    }

    /**
     * 位置精度のランクを取得
     */
    public function getLocationAccuracyRankAttribute(): string
    {
        if (!$this->location_accuracy) {
            return 'unknown';
        }

        if ($this->location_accuracy <= 5) {
            return 'excellent';
        } elseif ($this->location_accuracy <= 15) {
            return 'good';
        } elseif ($this->location_accuracy <= 50) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Google Mapsで表示するための情報を取得
     */
    public function getMapInfoAttribute(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->display_name,
            'position' => [
                'lat' => $this->location_lat,
                'lng' => $this->location_lng,
            ],
            'accuracy' => $this->location_accuracy,
            'address' => $this->location_address,
            'updated_at' => $this->location_updated_at?->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'current_shift' => $this->getCurrentShift()?->only(['id', 'project.name', 'start_time', 'end_time']),
            'sharing_enabled' => $this->location_sharing_enabled,
            'accuracy_rank' => $this->location_accuracy_rank,
        ];
    }
}
