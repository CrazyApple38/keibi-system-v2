<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 見積モデル
 * 
 * プロジェクトに対する見積書を管理
 * 
 * @property int $id
 * @property int $customer_id 顧客ID
 * @property int $project_id プロジェクトID
 * @property string $quotation_number 見積番号
 * @property string $title 見積タイトル
 * @property \DateTime $quotation_date 見積日
 * @property \DateTime|null $valid_until 有効期限
 * @property array $quotation_items 見積項目（JSON）
 * @property decimal $subtotal_amount 小計金額
 * @property decimal $tax_amount 税額
 * @property decimal $total_amount 合計金額
 * @property decimal|null $discount_amount 割引金額
 * @property string|null $discount_reason 割引理由
 * @property string $status ステータス
 * @property string|null $terms_conditions 取引条件
 * @property string|null $notes 備考
 * @property \DateTime|null $sent_at 送付日時
 * @property \DateTime|null $accepted_at 承認日時
 * @property \DateTime|null $rejected_at 却下日時
 * @property string|null $rejection_reason 却下理由
 * @property int $created_by 作成者ID
 * @property \DateTime|null $deleted_at 削除日時
 * @property \DateTime $created_at 作成日時
 * @property \DateTime $updated_at 更新日時
 */
class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * テーブル名
     */
    protected $table = 'quotations';

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'customer_id',
        'project_id',
        'quotation_number',
        'title',
        'quotation_date',
        'valid_until',
        'quotation_items',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'discount_amount',
        'discount_reason',
        'status',
        'terms_conditions',
        'notes',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'rejection_reason',
        'created_by',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'quotation_items' => 'array',
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * ステータス定数
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * 利用可能なステータスリスト
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_DRAFT => '下書き',
            self::STATUS_SENT => '送付済み',
            self::STATUS_ACCEPTED => '承認済み',
            self::STATUS_REJECTED => '却下',
            self::STATUS_EXPIRED => '期限切れ',
            self::STATUS_CANCELLED => 'キャンセル',
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
     * 作成者とのリレーション
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 契約とのリレーション
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * 承認済み見積かどうかを判定
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * 却下された見積かどうかを判定
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * 期限切れの見積かどうかを判定
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               ($this->valid_until && $this->valid_until->isPast());
    }

    /**
     * 送付済み見積かどうかを判定
     */
    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    /**
     * 見積番号の自動生成
     */
    public static function generateQuotationNumber(int $customerId): string
    {
        $year = date('Y');
        $month = str_pad(date('n'), 2, '0', STR_PAD_LEFT);
        $customerCode = str_pad($customerId, 4, '0', STR_PAD_LEFT);
        $sequence = static::where('customer_id', $customerId)
            ->whereYear('quotation_date', $year)
            ->whereMonth('quotation_date', date('n'))
            ->count() + 1;
        $sequenceCode = str_pad($sequence, 3, '0', STR_PAD_LEFT);

        return "Q{$year}{$month}-{$customerCode}-{$sequenceCode}";
    }

    /**
     * 見積金額の計算・更新
     */
    public function calculateAmounts(): void
    {
        $items = $this->quotation_items ?? [];
        $subtotal = 0;

        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? 0;
            $subtotal += $quantity * $unitPrice;
        }

        $this->subtotal_amount = $subtotal;
        
        // 割引適用
        if ($this->discount_amount) {
            $subtotal -= $this->discount_amount;
        }

        // 税額計算（10%）
        $this->tax_amount = $subtotal * 0.10;
        $this->total_amount = $subtotal + $this->tax_amount;
    }

    /**
     * 見積項目の追加
     */
    public function addItem(string $description, int $quantity, float $unitPrice, string $unit = '式'): void
    {
        $items = $this->quotation_items ?? [];
        
        $items[] = [
            'description' => $description,
            'quantity' => $quantity,
            'unit' => $unit,
            'unit_price' => $unitPrice,
            'amount' => $quantity * $unitPrice,
        ];

        $this->quotation_items = $items;
        $this->calculateAmounts();
    }

    /**
     * 見積項目の削除
     */
    public function removeItem(int $index): bool
    {
        $items = $this->quotation_items ?? [];
        
        if (!isset($items[$index])) {
            return false;
        }

        unset($items[$index]);
        $this->quotation_items = array_values($items); // インデックスを再整理
        $this->calculateAmounts();
        
        return true;
    }

    /**
     * 見積の承認
     */
    public function accept(): bool
    {
        if ($this->status !== self::STATUS_SENT) {
            return false;
        }

        $this->status = self::STATUS_ACCEPTED;
        $this->accepted_at = now();
        $this->save();

        return true;
    }

    /**
     * 見積の却下
     */
    public function reject(string $reason = ''): bool
    {
        if ($this->status !== self::STATUS_SENT) {
            return false;
        }

        $this->status = self::STATUS_REJECTED;
        $this->rejected_at = now();
        $this->rejection_reason = $reason;
        $this->save();

        return true;
    }

    /**
     * 見積の送付
     */
    public function send(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        $this->status = self::STATUS_SENT;
        $this->sent_at = now();
        $this->save();

        return true;
    }

    /**
     * 有効期限の残り日数を取得
     */
    public function getDaysUntilExpiryAttribute(): int
    {
        if (!$this->valid_until) {
            return 0;
        }

        return max(0, now()->diffInDays($this->valid_until, false));
    }

    /**
     * 見積項目数を取得
     */
    public function getItemCountAttribute(): int
    {
        return count($this->quotation_items ?? []);
    }

    /**
     * 税率を取得（将来的な変更に対応）
     */
    public function getTaxRateAttribute(): float
    {
        return 0.10; // 10%
    }

    /**
     * 割引率を取得
     */
    public function getDiscountRateAttribute(): float
    {
        if ($this->subtotal_amount <= 0) {
            return 0;
        }

        return ($this->discount_amount / $this->subtotal_amount) * 100;
    }

    /**
     * 見積の詳細情報を配列で取得
     */
    public function getDetailedInfoAttribute(): array
    {
        return [
            'quotation_number' => $this->quotation_number,
            'customer_name' => $this->customer->name ?? '',
            'project_name' => $this->project->name ?? '',
            'status_label' => self::getAvailableStatuses()[$this->status] ?? '',
            'subtotal_amount' => $this->subtotal_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'discount_amount' => $this->discount_amount,
            'item_count' => $this->getItemCountAttribute(),
            'days_until_expiry' => $this->getDaysUntilExpiryAttribute(),
        ];
    }

    /**
     * 期限切れステータスの自動更新
     */
    public static function updateExpiredQuotations(): int
    {
        return static::where('status', self::STATUS_SENT)
            ->where('valid_until', '<', now())
            ->update([
                'status' => self::STATUS_EXPIRED,
                'updated_at' => now(),
            ]);
    }
}
