<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 請求モデル
 * 
 * 顧客への請求書を管理
 * 
 * @property int $id
 * @property int $customer_id 顧客ID
 * @property int $project_id プロジェクトID
 * @property int|null $contract_id 契約ID
 * @property string $invoice_number 請求書番号
 * @property string $title 請求書タイトル
 * @property \DateTime $invoice_date 請求日
 * @property \DateTime $due_date 支払期限
 * @property array $invoice_items 請求項目（JSON）
 * @property decimal $subtotal_amount 小計金額
 * @property decimal $tax_amount 税額
 * @property decimal $total_amount 合計金額
 * @property decimal|null $discount_amount 割引金額
 * @property string|null $discount_reason 割引理由
 * @property decimal|null $paid_amount 入金済み金額
 * @property decimal|null $balance_amount 残高
 * @property string $status ステータス
 * @property string|null $payment_method 支払方法
 * @property \DateTime|null $paid_at 入金日時
 * @property string|null $payment_reference 支払参照番号
 * @property string|null $bank_info 振込先情報
 * @property string|null $notes 備考
 * @property int $created_by 作成者ID
 * @property \DateTime|null $deleted_at 削除日時
 * @property \DateTime $created_at 作成日時
 * @property \DateTime $updated_at 更新日時
 */
class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * テーブル名
     */
    protected $table = 'invoices';

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'customer_id',
        'project_id',
        'contract_id',
        'invoice_number',
        'title',
        'invoice_date',
        'due_date',
        'invoice_items',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'discount_amount',
        'discount_reason',
        'paid_amount',
        'balance_amount',
        'status',
        'payment_method',
        'paid_at',
        'payment_reference',
        'bank_info',
        'notes',
        'created_by',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'invoice_items' => 'array',
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * ステータス定数
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_PARTIAL_PAID = 'partial_paid';

    /**
     * 支払方法定数
     */
    public const PAYMENT_BANK_TRANSFER = 'bank_transfer';
    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_CHECK = 'check';
    public const PAYMENT_CREDIT_CARD = 'credit_card';
    public const PAYMENT_OTHER = 'other';

    /**
     * 利用可能なステータスリスト
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_DRAFT => '下書き',
            self::STATUS_SENT => '送付済み',
            self::STATUS_PAID => '入金済み',
            self::STATUS_OVERDUE => '期限超過',
            self::STATUS_CANCELLED => 'キャンセル',
            self::STATUS_PARTIAL_PAID => '一部入金',
        ];
    }

    /**
     * 利用可能な支払方法リスト
     */
    public static function getAvailablePaymentMethods(): array
    {
        return [
            self::PAYMENT_BANK_TRANSFER => '銀行振込',
            self::PAYMENT_CASH => '現金',
            self::PAYMENT_CHECK => '小切手',
            self::PAYMENT_CREDIT_CARD => 'クレジットカード',
            self::PAYMENT_OTHER => 'その他',
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
     * 契約とのリレーション
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * 作成者とのリレーション
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 入金済みかどうかを判定
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * 一部入金かどうかを判定
     */
    public function isPartialPaid(): bool
    {
        return $this->status === self::STATUS_PARTIAL_PAID;
    }

    /**
     * 期限超過かどうかを判定
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE ||
               ($this->due_date && $this->due_date->isPast() && !$this->isPaid());
    }

    /**
     * 送付済みかどうかを判定
     */
    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    /**
     * 請求書番号の自動生成
     */
    public static function generateInvoiceNumber(int $customerId): string
    {
        $year = date('Y');
        $month = str_pad(date('n'), 2, '0', STR_PAD_LEFT);
        $customerCode = str_pad($customerId, 4, '0', STR_PAD_LEFT);
        $sequence = static::where('customer_id', $customerId)
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', date('n'))
            ->count() + 1;
        $sequenceCode = str_pad($sequence, 3, '0', STR_PAD_LEFT);

        return "I{$year}{$month}-{$customerCode}-{$sequenceCode}";
    }

    /**
     * 請求金額の計算・更新
     */
    public function calculateAmounts(): void
    {
        $items = $this->invoice_items ?? [];
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
        
        // 残高計算
        $this->balance_amount = $this->total_amount - ($this->paid_amount ?? 0);
    }

    /**
     * 請求項目の追加
     */
    public function addItem(string $description, int $quantity, float $unitPrice, string $unit = '式'): void
    {
        $items = $this->invoice_items ?? [];
        
        $items[] = [
            'description' => $description,
            'quantity' => $quantity,
            'unit' => $unit,
            'unit_price' => $unitPrice,
            'amount' => $quantity * $unitPrice,
        ];

        $this->invoice_items = $items;
        $this->calculateAmounts();
    }

    /**
     * 請求項目の削除
     */
    public function removeItem(int $index): bool
    {
        $items = $this->invoice_items ?? [];
        
        if (!isset($items[$index])) {
            return false;
        }

        unset($items[$index]);
        $this->invoice_items = array_values($items); // インデックスを再整理
        $this->calculateAmounts();
        
        return true;
    }

    /**
     * 入金記録
     */
    public function recordPayment(float $amount, string $method = '', string $reference = ''): bool
    {
        if ($this->isPaid()) {
            return false;
        }

        $previousPaidAmount = $this->paid_amount ?? 0;
        $newPaidAmount = $previousPaidAmount + $amount;

        $this->paid_amount = $newPaidAmount;
        $this->balance_amount = $this->total_amount - $newPaidAmount;
        
        if ($method) {
            $this->payment_method = $method;
        }
        
        if ($reference) {
            $this->payment_reference = $reference;
        }

        // ステータス更新
        if ($this->balance_amount <= 0) {
            $this->status = self::STATUS_PAID;
            $this->paid_at = now();
        } else {
            $this->status = self::STATUS_PARTIAL_PAID;
        }

        $this->save();
        return true;
    }

    /**
     * 支払期限までの残り日数を取得
     */
    public function getDaysUntilDueAttribute(): int
    {
        if (!$this->due_date) {
            return 0;
        }

        return now()->diffInDays($this->due_date, false);
    }

    /**
     * 請求項目数を取得
     */
    public function getItemCountAttribute(): int
    {
        return count($this->invoice_items ?? []);
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
     * 入金率を取得
     */
    public function getPaymentRateAttribute(): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        return (($this->paid_amount ?? 0) / $this->total_amount) * 100;
    }

    /**
     * 請求書の送付
     */
    public function send(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        $this->status = self::STATUS_SENT;
        $this->save();

        return true;
    }

    /**
     * 請求書のキャンセル
     */
    public function cancel(): bool
    {
        if ($this->isPaid()) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        $this->save();

        return true;
    }

    /**
     * 入金残高があるかを判定
     */
    public function hasBalance(): bool
    {
        return ($this->balance_amount ?? 0) > 0;
    }

    /**
     * 請求書の詳細情報を配列で取得
     */
    public function getDetailedInfoAttribute(): array
    {
        return [
            'invoice_number' => $this->invoice_number,
            'customer_name' => $this->customer->name ?? '',
            'project_name' => $this->project->name ?? '',
            'status_label' => self::getAvailableStatuses()[$this->status] ?? '',
            'subtotal_amount' => $this->subtotal_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount ?? 0,
            'balance_amount' => $this->balance_amount ?? 0,
            'discount_amount' => $this->discount_amount,
            'item_count' => $this->getItemCountAttribute(),
            'days_until_due' => $this->getDaysUntilDueAttribute(),
            'payment_rate' => $this->getPaymentRateAttribute(),
            'is_overdue' => $this->isOverdue(),
        ];
    }

    /**
     * 月間売上集計
     */
    public static function getMonthlySales(int $year, int $month): array
    {
        $invoices = static::whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->get();

        return [
            'total_invoices' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'paid_amount' => $invoices->sum('paid_amount'),
            'balance_amount' => $invoices->sum('balance_amount'),
            'paid_invoices' => $invoices->where('status', self::STATUS_PAID)->count(),
            'overdue_invoices' => $invoices->where('status', self::STATUS_OVERDUE)->count(),
        ];
    }

    /**
     * 顧客別売上集計
     */
    public static function getCustomerSales(int $customerId, int $year = null): array
    {
        $query = static::where('customer_id', $customerId);
        
        if ($year) {
            $query->whereYear('invoice_date', $year);
        }
        
        $invoices = $query->get();

        return [
            'total_invoices' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'paid_amount' => $invoices->sum('paid_amount'),
            'balance_amount' => $invoices->sum('balance_amount'),
            'average_amount' => $invoices->count() > 0 ? $invoices->avg('total_amount') : 0,
        ];
    }

    /**
     * 期限超過請求書の自動更新
     */
    public static function updateOverdueInvoices(): int
    {
        return static::where('status', self::STATUS_SENT)
            ->where('due_date', '<', now())
            ->update([
                'status' => self::STATUS_OVERDUE,
                'updated_at' => now(),
            ]);
    }

    /**
     * 期限間近の請求書を取得
     */
    public static function getUpcomingDueInvoices(int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereIn('status', [self::STATUS_SENT, self::STATUS_PARTIAL_PAID])
            ->whereBetween('due_date', [now(), now()->addDays($days)])
            ->with(['customer', 'project'])
            ->get();
    }
}
