<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * 勤怠記録モデル
 * 
 * 警備員の勤怠状況を管理
 * 
 * @property int $id
 * @property int $guard_id 警備員ID
 * @property int $shift_id シフトID
 * @property \DateTime $date 勤務日
 * @property \DateTime|null $check_in_time 出勤時間
 * @property \DateTime|null $check_out_time 退勤時間
 * @property \DateTime|null $break_start_time 休憩開始時間
 * @property \DateTime|null $break_end_time 休憩終了時間
 * @property float|null $working_hours 勤務時間数
 * @property float|null $overtime_hours 時間外勤務時間数
 * @property float|null $night_hours 深夜勤務時間数
 * @property string $status ステータス
 * @property string|null $location 勤務場所
 * @property array|null $gps_coordinates GPS座標（JSON）
 * @property string|null $attendance_type 勤怠種別
 * @property string|null $absence_reason 欠勤理由
 * @property string|null $notes 備考
 * @property int $created_by 作成者ID
 * @property \DateTime $created_at 作成日時
 * @property \DateTime $updated_at 更新日時
 */
class Attendance extends Model
{
    use HasFactory;

    /**
     * テーブル名
     */
    protected $table = 'attendances';

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'guard_id',
        'shift_id',
        'date',
        'check_in_time',
        'check_out_time',
        'break_start_time',
        'break_end_time',
        'working_hours',
        'overtime_hours',
        'night_hours',
        'status',
        'location',
        'gps_coordinates',
        'attendance_type',
        'absence_reason',
        'notes',
        'created_by',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'break_start_time' => 'datetime',
        'break_end_time' => 'datetime',
        'working_hours' => 'float',
        'overtime_hours' => 'float',
        'night_hours' => 'float',
        'gps_coordinates' => 'array',
    ];

    /**
     * ステータス定数
     */
    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LATE = 'late';
    public const STATUS_EARLY_LEAVE = 'early_leave';
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';

    /**
     * 勤怠種別定数
     */
    public const TYPE_NORMAL = 'normal';
    public const TYPE_OVERTIME = 'overtime';
    public const TYPE_HOLIDAY = 'holiday';
    public const TYPE_SICK_LEAVE = 'sick_leave';
    public const TYPE_PERSONAL_LEAVE = 'personal_leave';

    /**
     * 利用可能なステータスリスト
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_PRESENT => '出勤',
            self::STATUS_ABSENT => '欠勤',
            self::STATUS_LATE => '遅刻',
            self::STATUS_EARLY_LEAVE => '早退',
            self::STATUS_PENDING => '申請中',
            self::STATUS_COMPLETED => '完了',
        ];
    }

    /**
     * 利用可能な勤怠種別リスト
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_NORMAL => '通常勤務',
            self::TYPE_OVERTIME => '時間外勤務',
            self::TYPE_HOLIDAY => '休日出勤',
            self::TYPE_SICK_LEAVE => '病気休暇',
            self::TYPE_PERSONAL_LEAVE => '有給休暇',
        ];
    }

    /**
     * 警備員とのリレーション
     */
    public function guard()
    {
        return $this->belongsTo(Guard::class);
    }

    /**
     * シフトとのリレーション
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * 作成者とのリレーション
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 出勤済みかどうかを判定
     */
    public function isPresent(): bool
    {
        return $this->status === self::STATUS_PRESENT;
    }

    /**
     * 欠勤かどうかを判定
     */
    public function isAbsent(): bool
    {
        return $this->status === self::STATUS_ABSENT;
    }

    /**
     * 遅刻かどうかを判定
     */
    public function isLate(): bool
    {
        return $this->status === self::STATUS_LATE;
    }

    /**
     * 早退かどうかを判定
     */
    public function isEarlyLeave(): bool
    {
        return $this->status === self::STATUS_EARLY_LEAVE;
    }

    /**
     * 勤務時間を自動計算
     */
    public function calculateWorkingHours(): float
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return 0;
        }

        $checkIn = Carbon::parse($this->check_in_time);
        $checkOut = Carbon::parse($this->check_out_time);
        
        // 日跨ぎの場合の処理
        if ($checkOut->lt($checkIn)) {
            $checkOut->addDay();
        }

        $totalHours = $checkIn->diffInHours($checkOut, true);
        
        // 休憩時間を除外
        $breakHours = $this->calculateBreakHours();
        
        return max(0, $totalHours - $breakHours);
    }

    /**
     * 休憩時間を計算
     */
    public function calculateBreakHours(): float
    {
        if (!$this->break_start_time || !$this->break_end_time) {
            return 0;
        }

        $breakStart = Carbon::parse($this->break_start_time);
        $breakEnd = Carbon::parse($this->break_end_time);
        
        if ($breakEnd->lt($breakStart)) {
            $breakEnd->addDay();
        }

        return $breakStart->diffInHours($breakEnd, true);
    }

    /**
     * 時間外勤務時間を計算
     */
    public function calculateOvertimeHours(): float
    {
        $workingHours = $this->working_hours ?? $this->calculateWorkingHours();
        $standardHours = 8; // 標準勤務時間

        return max(0, $workingHours - $standardHours);
    }

    /**
     * 深夜勤務時間を計算（22:00-05:00）
     */
    public function calculateNightHours(): float
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return 0;
        }

        $checkIn = Carbon::parse($this->check_in_time);
        $checkOut = Carbon::parse($this->check_out_time);
        
        if ($checkOut->lt($checkIn)) {
            $checkOut->addDay();
        }

        $nightStart = $checkIn->copy()->setTime(22, 0, 0);
        $nightEnd = $checkIn->copy()->addDay()->setTime(5, 0, 0);

        // 深夜時間帯との重複部分を計算
        $overlapStart = max($checkIn, $nightStart);
        $overlapEnd = min($checkOut, $nightEnd);

        if ($overlapEnd->gt($overlapStart)) {
            return $overlapStart->diffInHours($overlapEnd, true);
        }

        return 0;
    }

    /**
     * 遅刻時間を計算（分）
     */
    public function getLateMinutesAttribute(): int
    {
        if (!$this->check_in_time || !$this->shift || !$this->shift->start_time) {
            return 0;
        }

        $checkIn = Carbon::parse($this->check_in_time);
        $shiftStart = Carbon::parse($this->shift->start_time);
        
        if ($checkIn->gt($shiftStart)) {
            return $shiftStart->diffInMinutes($checkIn);
        }

        return 0;
    }

    /**
     * 早退時間を計算（分）
     */
    public function getEarlyLeaveMinutesAttribute(): int
    {
        if (!$this->check_out_time || !$this->shift || !$this->shift->end_time) {
            return 0;
        }

        $checkOut = Carbon::parse($this->check_out_time);
        $shiftEnd = Carbon::parse($this->shift->end_time);
        
        if ($checkOut->lt($shiftEnd)) {
            return $checkOut->diffInMinutes($shiftEnd);
        }

        return 0;
    }

    /**
     * 勤怠データの自動更新
     */
    public function updateCalculatedFields(): void
    {
        $this->working_hours = $this->calculateWorkingHours();
        $this->overtime_hours = $this->calculateOvertimeHours();
        $this->night_hours = $this->calculateNightHours();
        
        // ステータスの自動判定
        $this->updateStatus();
        
        $this->save();
    }

    /**
     * ステータスの自動判定・更新
     */
    public function updateStatus(): void
    {
        if (!$this->check_in_time) {
            $this->status = self::STATUS_ABSENT;
            return;
        }

        $isLate = $this->getLateMinutesAttribute() > 0;
        $isEarlyLeave = $this->getEarlyLeaveMinutesAttribute() > 0;

        if ($isLate && $isEarlyLeave) {
            $this->status = self::STATUS_LATE; // 遅刻を優先
        } elseif ($isLate) {
            $this->status = self::STATUS_LATE;
        } elseif ($isEarlyLeave) {
            $this->status = self::STATUS_EARLY_LEAVE;
        } else {
            $this->status = self::STATUS_PRESENT;
        }
    }

    /**
     * 月間勤怠サマリーを取得
     */
    public static function getMonthlySummary(int $guardId, int $year, int $month): array
    {
        $attendances = static::where('guard_id', $guardId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        return [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->where('status', self::STATUS_PRESENT)->count(),
            'absent_days' => $attendances->where('status', self::STATUS_ABSENT)->count(),
            'late_days' => $attendances->where('status', self::STATUS_LATE)->count(),
            'early_leave_days' => $attendances->where('status', self::STATUS_EARLY_LEAVE)->count(),
            'total_working_hours' => $attendances->sum('working_hours'),
            'total_overtime_hours' => $attendances->sum('overtime_hours'),
            'total_night_hours' => $attendances->sum('night_hours'),
        ];
    }

    /**
     * 勤怠記録の検証
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->check_in_time && $this->check_out_time) {
            $checkIn = Carbon::parse($this->check_in_time);
            $checkOut = Carbon::parse($this->check_out_time);
            
            if ($checkOut->lt($checkIn) && $checkOut->diffInHours($checkIn) > 12) {
                $errors[] = '出勤時間と退勤時間の設定に不整合があります';
            }
        }

        if ($this->break_start_time && $this->break_end_time) {
            $breakStart = Carbon::parse($this->break_start_time);
            $breakEnd = Carbon::parse($this->break_end_time);
            
            if ($breakEnd->lt($breakStart)) {
                $errors[] = '休憩時間の設定に不整合があります';
            }
        }

        return $errors;
    }
}
