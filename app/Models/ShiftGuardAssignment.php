<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * シフト警備員アサインメントピボットモデル
 * 
 * シフトと警備員の中間テーブルを管理
 * 
 * @property int $id
 * @property int $shift_id シフトID
 * @property int $guard_id 警備員ID
 * @property string|null $position 配置ポジション
 * @property string|null $notes 備考
 * @property \DateTime $created_at 作成日時
 * @property \DateTime $updated_at 更新日時
 */
class ShiftGuardAssignment extends Pivot
{
    /**
     * テーブル名
     */
    protected $table = 'shift_guard_assignments';

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'shift_id',
        'guard_id',
        'position',
        'notes',
    ];

    /**
     * タイムスタンプを使用
     */
    public $timestamps = true;

    /**
     * シフトとのリレーション
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * 警備員とのリレーション
     */
    public function guard()
    {
        return $this->belongsTo(Guard::class);
    }

    /**
     * ポジション定数
     */
    public const POSITION_MAIN_ENTRANCE = 'main_entrance';
    public const POSITION_PATROL = 'patrol';
    public const POSITION_MONITORING = 'monitoring';
    public const POSITION_TRAFFIC_CONTROL = 'traffic_control';
    public const POSITION_BACKUP = 'backup';

    /**
     * 利用可能なポジションリスト
     */
    public static function getAvailablePositions(): array
    {
        return [
            self::POSITION_MAIN_ENTRANCE => '正面入口',
            self::POSITION_PATROL => '巡回',
            self::POSITION_MONITORING => '監視',
            self::POSITION_TRAFFIC_CONTROL => '交通誘導',
            self::POSITION_BACKUP => '予備',
        ];
    }

    /**
     * ポジション名を取得
     */
    public function getPositionNameAttribute(): string
    {
        $positions = static::getAvailablePositions();
        return $positions[$this->position] ?? $this->position ?? '未設定';
    }
}
