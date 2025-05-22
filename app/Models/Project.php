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
}
