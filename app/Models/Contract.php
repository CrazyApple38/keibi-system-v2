<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 契約モデル
 * 
 * 顧客との警備業務契約を管理
 * 
 * @property int $id
 * @property int $customer_id 顧客ID
 * @property int $project_id プロジェクトID
 * @property int|null $quotation_id 見積ID
 * @property string $contract_number 契約番号
 * @property string $title 契約タイトル
 * @property \DateTime $contract_date 契約日
 * @property \DateTime $start_date 契約開始日
 * @property \DateTime $end_date 契約終了日
 * @property decimal $contract_amount 契約金額
 * @property string $payment_terms 支払条件
 * @property array|null $contract_terms 契約条項（JSON）
 * @property string $status ステータス
 * @property string|null $contract_file_path 契約書ファイルパス
 * @property \DateTime|null $signed_at 署名日時
 * @property string|null $signed_by 署名者
 * @property array|null $renewal_terms 更新条件（JSON）
 * @property \DateTime|null $renewal_notice_date 更新通知期限
 * @property string|null $termination_clause 解約条項
 * @property string|null $notes 備考
 * @property int $created_by 作成者ID
 * @property \DateTime|null $deleted_at 削除日時
 * @property \DateTime $created_at 作成日時
 * @property \DateTime $updated_at 更新日時
 */
class Contract extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * テーブル名
     */
    protected $table = 'contracts';

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'customer_id',
        'project_id',
        'quotation_id',
        'contract_number',
        'title',
        'contract_date',
        'start_date',
        'end_date',
        'contract_amount',
        'payment_terms',
        'contract_terms',
        'status',
        'contract_file_path',
        'signed_at',
        'signed_by',
        'renewal_terms',
        'renewal_notice_date',
        'termination_clause',
        'notes',
        'created_by',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'contract_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_amount' => 'decimal:2',
        'contract_terms' => 'array',
        'signed_at' => 'datetime',
        'renewal_terms' => 'array',
        'renewal_notice_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    /**
     * ステータス定数
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_TERMINATED = 'terminated';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * 支払条件定数
     */
    public const PAYMENT_MONTHLY = 'monthly';
    public const PAYMENT_QUARTERLY = 'quarterly';
    public const PAYMENT_SEMI_ANNUAL = 'semi_annual';
    public const PAYMENT_ANNUAL = 'annual';
    public const PAYMENT_ONE_TIME = 'one_time';

    /**
     * 利用可能なステータスリスト
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_DRAFT => '下書き',
            self::STATUS_PENDING => '承認待ち',
            self::STATUS_ACTIVE => '有効',
            self::STATUS_COMPLETED => '完了',
            self::STATUS_TERMINATED => '解約',
            self::STATUS_EXPIRED => '期限切れ',
            self::STATUS_SUSPENDED => '停止',
        ];
    }

    /**
     * 利用可能な支払条件リスト
     */
    public static function getAvailablePaymentTerms(): array
    {
        return [
            self::PAYMENT_MONTHLY => '月払い',
            self::PAYMENT_QUARTERLY => '四半期払い',
            self::PAYMENT_SEMI_ANNUAL => '半年払い',
            self::PAYMENT_ANNUAL => '年払い',
            self::PAYMENT_ONE_TIME => '一括払い',
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
     * プロジェクトとのリレーション
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * 見積とのリレーション
     */
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * 作成者とのリレーション
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 請求とのリレーション
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * 有効な契約かどうかを判定
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * 完了した契約かどうかを判定
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * 解約された契約かどうかを判定
     */
    public function isTerminated(): bool
    {
        return $this->status === self::STATUS_TERMINATED;
    }

    /**
     * 期限切れの契約かどうかを判定
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               ($this->end_date && $this->end_date->isPast());
    }

    /**
     * 署名済みかどうかを判定
     */
    public function isSigned(): bool
    {
        return !is_null($this->signed_at);
    }

    /**
     * 契約番号の自動生成
     */
    public static function generateContractNumber(int $customerId): string
    {
        $year = date('Y');
        $customerCode = str_pad($customerId, 4, '0', STR_PAD_LEFT);
        $sequence = static::where('customer_id', $customerId)
            ->whereYear('contract_date', $year)
            ->count() + 1;
        $sequenceCode = str_pad($sequence, 3, '0', STR_PAD_LEFT);

        return "C{$year}-{$customerCode}-{$sequenceCode}";
    }

    /**
     * 契約期間（日数）を取得
     */
    public function getContractPeriodDaysAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * 契約残期間（日数）を取得
     */
    public function getRemainingDaysAttribute(): int
    {
        if (!$this->end_date) {
            return 0;
        }

        return max(0, now()->diffInDays($this->end_date, false));
    }

    /**
     * 契約進捗率を取得
     */
    public function getProgressPercentageAttribute(): float
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        $now = now();
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
     * 月額契約金額を取得
     */
    public function getMonthlyAmountAttribute(): float
    {
        $periodMonths = $this->getContractPeriodMonths();
        
        if ($periodMonths <= 0) {
            return 0;
        }

        return $this->contract_amount / $periodMonths;
    }

    /**
     * 契約期間（月数）を取得
     */
    public function getContractPeriodMonths(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        return $this->start_date->diffInMonths($this->end_date) + 1;
    }

    /**
     * 契約の署名
     */
    public function sign(string $signedBy): bool
    {
        if ($this->isSigned()) {
            return false;
        }

        $this->signed_at = now();
        $this->signed_by = $signedBy;
        $this->status = self::STATUS_ACTIVE;
        $this->save();

        return true;
    }

    /**
     * 契約の解約
     */
    public function terminate(string $reason = ''): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $this->status = self::STATUS_TERMINATED;
        if ($reason) {
            $this->termination_clause = $reason;
        }
        $this->save();

        return true;
    }

    /**
     * 契約の更新通知が必要かを判定
     */
    public function needsRenewalNotice(): bool
    {
        if (!$this->renewal_notice_date || $this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        return now()->gte($this->renewal_notice_date);
    }

    /**
     * 更新条件の追加
     */
    public function addRenewalTerm(string $term, $value): void
    {
        $renewalTerms = $this->renewal_terms ?? [];
        $renewalTerms[$term] = $value;
        $this->renewal_terms = $renewalTerms;
    }

    /**
     * 契約条項の追加
     */
    public function addContractTerm(string $category, string $content): void
    {
        $contractTerms = $this->contract_terms ?? [];
        
        if (!isset($contractTerms[$category])) {
            $contractTerms[$category] = [];
        }
        
        $contractTerms[$category][] = $content;
        $this->contract_terms = $contractTerms;
    }

    /**
     * 次回請求予定日を取得
     */
    public function getNextBillingDateAttribute(): ?\DateTime
    {
        if (!$this->isActive()) {
            return null;
        }

        $lastInvoice = $this->invoices()->latest('invoice_date')->first();
        
        if (!$lastInvoice) {
            return $this->start_date; // 初回請求
        }

        switch ($this->payment_terms) {
            case self::PAYMENT_MONTHLY:
                return $lastInvoice->invoice_date->addMonth();
            case self::PAYMENT_QUARTERLY:
                return $lastInvoice->invoice_date->addMonths(3);
            case self::PAYMENT_SEMI_ANNUAL:
                return $lastInvoice->invoice_date->addMonths(6);
            case self::PAYMENT_ANNUAL:
                return $lastInvoice->invoice_date->addYear();
            default:
                return null;
        }
    }

    /**
     * 契約詳細情報を配列で取得
     */
    public function getDetailedInfoAttribute(): array
    {
        return [
            'contract_number' => $this->contract_number,
            'customer_name' => $this->customer->name ?? '',
            'project_name' => $this->project->name ?? '',
            'status_label' => self::getAvailableStatuses()[$this->status] ?? '',
            'contract_amount' => $this->contract_amount,
            'monthly_amount' => $this->getMonthlyAmountAttribute(),
            'period_days' => $this->getContractPeriodDaysAttribute(),
            'remaining_days' => $this->getRemainingDaysAttribute(),
            'progress_percentage' => $this->getProgressPercentageAttribute(),
            'is_signed' => $this->isSigned(),
            'needs_renewal_notice' => $this->needsRenewalNotice(),
        ];
    }

    /**
     * 期限切れ契約の自動更新
     */
    public static function updateExpiredContracts(): int
    {
        return static::where('status', self::STATUS_ACTIVE)
            ->where('end_date', '<', now())
            ->update([
                'status' => self::STATUS_EXPIRED,
                'updated_at' => now(),
            ]);
    }
}
