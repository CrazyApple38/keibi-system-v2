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
}
