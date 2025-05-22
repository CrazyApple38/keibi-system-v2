<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * シフトモデル
 * 
 * 警備業務のシフト・勤務予定を管理
 * 
 * @property int $id
 * @property int $project_id プロジェクトID
 * @property string $shift_code シフトコード
 * @property string $name シフト名
 * @property \DateTime $shift_date シフト日付
 * @property \DateTime $start_time 開始時間
 * @property \DateTime $end_time 終了時間
 * @property int $required_guards 必要警備員数
 * @property array|null $positions_required 必要ポジション（JSON）
 * @property string $shift_type シフト種別（day, night, full_day）
 * @property string $status ステータス
 * @property string|null $location 勤務場所
 * @property array|null $special_instructions 特別指示（JSON）
 * @property decimal|null $base_hourly_rate 基本時給
 * @property decimal|null $overtime_rate 時間外手当率
 * @property decimal|null $night_rate 深夜手当率
 * @property decimal|null $holiday_rate 休日手当率
 * @property string|null $weather_condition 天候条件
 * @property string|null $notes 備考
 * @property int $created_by 作成者ID
 * @property \DateTime|null $deleted_at 削除日時
 * @property \DateTime $created_at 作成日時
 * @property \DateTime $updated_at 更新日時
 */
class Shift extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * テーブル名
     */
    protected $table = 'shifts';

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'project_id',
        'shift_code',
        'name',
        'shift_date',
        'start_time',
        'end_time',
        'required_guards',
        'positions_required',
        'shift_type',
        'status',
        'location',
        'special_instructions',
        'base_hourly_rate',
        'overtime_rate',
        'night_rate',
        'holiday_rate',
        'weather_condition',
        'notes',
        'created_by',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'shift_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'positions_required' => 'array',
        'special_instructions' => 'array',
        'base_hourly_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'night_rate' => 'decimal:2',
        'holiday_rate' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    /**
     * シフト種別定数
     */
    public const TYPE_DAY = 'day';
    public const TYPE_NIGHT = 'night';
    public const TYPE_FULL_DAY = 'full_day';

    /**
     * ステータス定数
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * 利用可能なシフト種別リスト
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_DAY => '日勤',
            self::TYPE_NIGHT => '夜勤',
            self::TYPE_FULL_DAY => '24時間',
        ];
    }

    /**
     * 利用可能なステータスリスト
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_DRAFT => '下書き',
            self::STATUS_PUBLISHED => '公開済み',
            self::STATUS_CONFIRMED => '確定',
            self::STATUS_IN_PROGRESS => '実施中',
            self::STATUS_COMPLETED => '完了',
            self::STATUS_CANCELLED => 'キャンセル',
        ];
    }

    /**
     * プロジェクトとのリレーション
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * 作成者とのリレーション
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * アサインされた警備員とのリレーション
     */
    public function guards()
    {
        return $this->belongsToMany(Guard::class, 'shift_guard_assignments')
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
     * 日報とのリレーション
     */
    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }

    /**
     * 実施中のシフトかどうかを判定
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * 完了したシフトかどうかを判定
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * 確定済みシフトかどうかを判定
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * 夜勤シフトかどうかを判定
     */
    public function isNightShift(): bool
    {
        return $this->shift_type === self::TYPE_NIGHT;
    }

    /**
     * 休日シフトかどうかを判定
     */
    public function isHolidayShift(): bool
    {
        $date = Carbon::parse($this->shift_date);
        return $date->isWeekend(); // 土日を休日として判定（祝日判定は別途実装可能）
    }

    /**
     * シフトの勤務時間数を取得
     */
    public function getWorkingHoursAttribute(): float
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        // 日跨ぎの場合の処理
        if ($end->lt($start)) {
            $end->addDay();
        }

        return $start->diffInHours($end, true);
    }

    /**
     * 現在アサインされている警備員数を取得
     */
    public function getAssignedGuardsCountAttribute(): int
    {
        return $this->guards()->count();
    }

    /**
     * 警備員の充足率を取得（％）
     */
    public function getStaffingRateAttribute(): float
    {
        if ($this->required_guards === 0) {
            return 0;
        }
        return ($this->getAssignedGuardsCountAttribute() / $this->required_guards) * 100;
    }

    /**
     * シフトコードの自動生成
     */
    public static function generateShiftCode(int $projectId, string $shiftDate): string
    {
        $project = Project::find($projectId);
        $projectCode = $project ? substr($project->project_code, 0, 8) : 'PROJ';
        $date = Carbon::parse($shiftDate)->format('Ymd');
        $sequence = static::where('project_id', $projectId)
            ->whereDate('shift_date', $shiftDate)
            ->count() + 1;
        $sequenceCode = str_pad($sequence, 2, '0', STR_PAD_LEFT);

        return "{$projectCode}-{$date}-{$sequenceCode}";
    }

    /**
     * 警備員をシフトにアサイン
     */
    public function assignGuard(int $guardId, string $position = '', string $notes = ''): bool
    {
        // 既にアサインされている場合はfalseを返す
        if ($this->guards()->where('guard_id', $guardId)->exists()) {
            return false;
        }

        // 必要人数に達している場合はfalseを返す
        if ($this->getAssignedGuardsCountAttribute() >= $this->required_guards) {
            return false;
        }

        $this->guards()->attach($guardId, [
            'position' => $position,
            'notes' => $notes,
            'created_at' => now(),
        ]);

        return true;
    }

    /**
     * 警備員をシフトから解除
     */
    public function unassignGuard(int $guardId): bool
    {
        if (!$this->guards()->where('guard_id', $guardId)->exists()) {
            return false;
        }

        $this->guards()->detach($guardId);
        return true;
    }

    /**
     * 時間外勤務時間を計算
     */
    public function getOvertimeHoursAttribute(): float
    {
        $workingHours = $this->getWorkingHoursAttribute();
        $standardHours = 8; // 通常勤務時間

        return max(0, $workingHours - $standardHours);
    }

    /**
     * 深夜勤務時間を計算（22:00-05:00）
     */
    public function getNightHoursAttribute(): float
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        // 日跨ぎの場合の処理
        if ($end->lt($start)) {
            $end->addDay();
        }

        $nightStart = $start->copy()->setTime(22, 0, 0);
        $nightEnd = $start->copy()->addDay()->setTime(5, 0, 0);

        // 深夜時間帯との重複部分を計算
        $overlapStart = max($start, $nightStart);
        $overlapEnd = min($end, $nightEnd);

        if ($overlapEnd->gt($overlapStart)) {
            return $overlapStart->diffInHours($overlapEnd, true);
        }

        return 0;
    }

    /**
     * シフトの総労働コストを計算
     */
    public function getTotalLaborCostAttribute(): float
    {
        $baseCost = $this->getWorkingHoursAttribute() * $this->base_hourly_rate;
        $overtimeCost = $this->getOvertimeHoursAttribute() * $this->base_hourly_rate * $this->overtime_rate;
        $nightCost = $this->getNightHoursAttribute() * $this->base_hourly_rate * $this->night_rate;
        $holidayCost = $this->isHolidayShift() ? $baseCost * $this->holiday_rate : 0;

        return $baseCost + $overtimeCost + $nightCost + $holidayCost;
    }
}
