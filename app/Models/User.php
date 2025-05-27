<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * ユーザーモデル
 * 
 * システムにアクセスするユーザー（管理者、オペレーター、警備員等）を管理
 * 
 * @property int $id
 * @property string $name ユーザー名
 * @property string $email メールアドレス
 * @property string $password パスワード（ハッシュ化）
 * @property string $role 役割（admin, operator, guard, customer）
 * @property array $permissions 権限情報（JSON）
 * @property string|null $phone_number 電話番号
 * @property string $status ステータス（active, inactive, suspended）
 * @property \DateTime|null $last_login_at 最終ログイン日時
 * @property \DateTime|null $email_verified_at メール認証日時
 * @property string|null $remember_token ログイン保持トークン
 * @property \DateTime $created_at 作成日時
 * @property \DateTime $updated_at 更新日時
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * テーブル名
     */
    protected $table = 'users';

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'phone_number',
        'status',
        'last_login_at',
    ];

    /**
     * 非表示にする属性
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'permissions' => 'array',
        'password' => 'hashed',
    ];

    /**
     * ユーザーの役割定数
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_OPERATOR = 'operator';
    public const ROLE_GUARD = 'guard';
    public const ROLE_CUSTOMER = 'customer';

    /**
     * ステータス定数
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * 利用可能な役割リスト
     */
    public static function getAvailableRoles(): array
    {
        return [
            self::ROLE_ADMIN => '管理者',
            self::ROLE_OPERATOR => 'オペレーター',
            self::ROLE_GUARD => '警備員',
            self::ROLE_CUSTOMER => '顧客',
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
     * 管理者かどうかを判定
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * オペレーターかどうかを判定
     */
    public function isOperator(): bool
    {
        return $this->role === self::ROLE_OPERATOR;
    }

    /**
     * 警備員かどうかを判定
     */
    public function isGuard(): bool
    {
        return $this->role === self::ROLE_GUARD;
    }

    /**
     * 顧客かどうかを判定
     */
    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    /**
     * 有効なユーザーかどうかを判定
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * 特定の権限を持っているかを判定
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true; // 管理者は全権限を持つ
        }

        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * 警備員情報とのリレーション
     */
    public function guardInfo()
    {
        return $this->hasOne(Guard::class, 'user_id');
    }

    /**
     * 顧客情報とのリレーション
     */
    public function customerInfo()
    {
        return $this->hasOne(Customer::class, 'user_id');
    }

    /**
     * 作成したプロジェクトとのリレーション
     */
    public function createdProjects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    /**
     * 作成したシフトとのリレーション
     */
    public function createdShifts()
    {
        return $this->hasMany(Shift::class, 'created_by');
    }

    /**
     * 作成した勤怠記録とのリレーション
     */
    public function createdAttendances()
    {
        return $this->hasMany(Attendance::class, 'created_by');
    }

    /**
     * 作成した見積とのリレーション
     */
    public function createdQuotations()
    {
        return $this->hasMany(Quotation::class, 'created_by');
    }

    /**
     * 作成した契約とのリレーション
     */
    public function createdContracts()
    {
        return $this->hasMany(Contract::class, 'created_by');
    }

    /**
     * 作成した請求とのリレーション
     */
    public function createdInvoices()
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    /**
     * 作成した日報とのリレーション
     */
    public function createdDailyReports()
    {
        return $this->hasMany(DailyReport::class, 'created_by');
    }
}
