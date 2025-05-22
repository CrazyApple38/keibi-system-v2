<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 顧客モデル
 * 
 * 警備サービスを利用する顧客企業・団体を管理
 * 
 * @property int $id
 * @property int|null $user_id 関連ユーザーID
 * @property string $name 顧客名
 * @property string $name_kana 顧客名（カナ）
 * @property string|null $company_name 会社名
 * @property string|null $department 部署名
 * @property string|null $position 役職
 * @property string|null $postal_code 郵便番号
 * @property string|null $address 住所
 * @property string|null $phone_number 電話番号
 * @property string|null $mobile_number 携帯電話番号
 * @property string|null $email メールアドレス
 * @property string|null $fax_number FAX番号
 * @property string $customer_type 顧客種別（corporate, individual, government）
 * @property string $status ステータス（active, inactive, suspended）
 * @property array|null $contact_preferences 連絡希望設定（JSON）
 * @property string|null $notes 備考
 * @property int $created_by 作成者ID
 * @property \DateTime|null $deleted_at 削除日時
 * @property \DateTime $created_at 作成日時
 * @property \DateTime $updated_at 更新日時
 */
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * テーブル名
     */
    protected $table = 'customers';

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'user_id',
        'name',
        'name_kana',
        'company_name',
        'department',
        'position',
        'postal_code',
        'address',
        'phone_number',
        'mobile_number',
        'email',
        'fax_number',
        'customer_type',
        'status',
        'contact_preferences',
        'notes',
        'created_by',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'contact_preferences' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * 顧客種別定数
     */
    public const TYPE_CORPORATE = 'corporate';
    public const TYPE_INDIVIDUAL = 'individual';
    public const TYPE_GOVERNMENT = 'government';

    /**
     * ステータス定数
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * 利用可能な顧客種別リスト
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_CORPORATE => '法人',
            self::TYPE_INDIVIDUAL => '個人',
            self::TYPE_GOVERNMENT => '官公庁',
        ];
    }

    /**
     * 利用可能なステータスリスト
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => '有効',
            self::STATUS_INACTIVE => '無効',
            self::STATUS_SUSPENDED => '停止',
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
     * プロジェクトとのリレーション
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
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
     * 有効な顧客かどうかを判定
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * 法人顧客かどうかを判定
     */
    public function isCorporate(): bool
    {
        return $this->customer_type === self::TYPE_CORPORATE;
    }

    /**
     * 個人顧客かどうかを判定
     */
    public function isIndividual(): bool
    {
        return $this->customer_type === self::TYPE_INDIVIDUAL;
    }

    /**
     * 官公庁顧客かどうかを判定
     */
    public function isGovernment(): bool
    {
        return $this->customer_type === self::TYPE_GOVERNMENT;
    }

    /**
     * 表示用名前を取得
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->company_name) {
            return $this->company_name . ' ' . $this->name;
        }
        return $this->name;
    }

    /**
     * 住所の表示用フォーマット
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = [];
        if ($this->postal_code) {
            $parts[] = '〒' . $this->postal_code;
        }
        if ($this->address) {
            $parts[] = $this->address;
        }
        return implode(' ', $parts);
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
        if ($this->fax_number) {
            $info['fax'] = $this->fax_number;
        }
        return $info;
    }
}
