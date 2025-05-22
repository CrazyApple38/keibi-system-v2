<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 日報モデル
 * 
 * 警備業務の日次報告書を管理
 * 
 * @property int $id
 * @property int $project_id プロジェクトID
 * @property int $shift_id シフトID
 * @property string $report_number 日報番号
 * @property \DateTime $report_date 報告日
 * @property string $shift_time シフト時間
 * @property string $weather_condition 天候
 * @property array|null $assigned_guards 配置警備員（JSON）
 * @property string|null $site_condition 現場状況
 * @property array|null $incidents 事件・事故（JSON）
 * @property array|null $patrol_records 巡回記録（JSON）
 * @property array|null $visitor_records 来訪者記録（JSON）
 * @property string|null $equipment_status 機器状況
 * @property string|null $handover_notes 引継事項
 * @property string|null $special_notes 特記事項
 * @property array|null $photos 写真（JSON）
 * @property string $status ステータス
 * @property \DateTime|null $submitted_at 提出日時
 * @property \DateTime|null $approved_at 承認日時
 * @property int|null $approved_by 承認者ID
 * @property string|null $approval_notes 承認メモ
 * @property int $created_by 作成者ID
 * @property \DateTime|null $deleted_at 削除日時
 * @property \DateTime $created_at 作成日時
 * @property \DateTime $updated_at 更新日時
 */
class DailyReport extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * テーブル名
     */
    protected $table = 'daily_reports';

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'project_id',
        'shift_id',
        'report_number',
        'report_date',
        'shift_time',
        'weather_condition',
        'assigned_guards',
        'site_condition',
        'incidents',
        'patrol_records',
        'visitor_records',
        'equipment_status',
        'handover_notes',
        'special_notes',
        'photos',
        'status',
        'submitted_at',
        'approved_at',
        'approved_by',
        'approval_notes',
        'created_by',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'report_date' => 'date',
        'assigned_guards' => 'array',
        'incidents' => 'array',
        'patrol_records' => 'array',
        'visitor_records' => 'array',
        'photos' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * ステータス定数
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REVISION_REQUIRED = 'revision_required';

    /**
     * 天候定数
     */
    public const WEATHER_CLEAR = 'clear';
    public const WEATHER_CLOUDY = 'cloudy';
    public const WEATHER_RAINY = 'rainy';
    public const WEATHER_SNOWY = 'snowy';
    public const WEATHER_STORMY = 'stormy';

    /**
     * 事件・事故レベル定数
     */
    public const INCIDENT_LEVEL_LOW = 'low';
    public const INCIDENT_LEVEL_MEDIUM = 'medium';
    public const INCIDENT_LEVEL_HIGH = 'high';
    public const INCIDENT_LEVEL_CRITICAL = 'critical';

    /**
     * 利用可能なステータスリスト
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_DRAFT => '下書き',
            self::STATUS_SUBMITTED => '提出済み',
            self::STATUS_APPROVED => '承認済み',
            self::STATUS_REJECTED => '却下',
            self::STATUS_REVISION_REQUIRED => '修正要求',
        ];
    }

    /**
     * 利用可能な天候リスト
     */
    public static function getAvailableWeatherConditions(): array
    {
        return [
            self::WEATHER_CLEAR => '晴れ',
            self::WEATHER_CLOUDY => '曇り',
            self::WEATHER_RAINY => '雨',
            self::WEATHER_SNOWY => '雪',
            self::WEATHER_STORMY => '嵐',
        ];
    }

    /**
     * 利用可能な事件・事故レベルリスト
     */
    public static function getAvailableIncidentLevels(): array
    {
        return [
            self::INCIDENT_LEVEL_LOW => '軽微',
            self::INCIDENT_LEVEL_MEDIUM => '中度',
            self::INCIDENT_LEVEL_HIGH => '重大',
            self::INCIDENT_LEVEL_CRITICAL => '緊急',
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
     * 承認者とのリレーション
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * 提出済みかどうかを判定
     */
    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    /**
     * 承認済みかどうかを判定
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * 却下されたかどうかを判定
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * 修正要求があるかどうかを判定
     */
    public function needsRevision(): bool
    {
        return $this->status === self::STATUS_REVISION_REQUIRED;
    }

    /**
     * 日報番号の自動生成
     */
    public static function generateReportNumber(int $projectId, string $reportDate): string
    {
        $project = Project::find($projectId);
        $projectCode = $project ? substr($project->project_code, 0, 8) : 'PROJ';
        $date = \Carbon\Carbon::parse($reportDate)->format('Ymd');
        $sequence = static::where('project_id', $projectId)
            ->whereDate('report_date', $reportDate)
            ->count() + 1;
        $sequenceCode = str_pad($sequence, 2, '0', STR_PAD_LEFT);

        return "DR-{$projectCode}-{$date}-{$sequenceCode}";
    }

    /**
     * 事件・事故の追加
     */
    public function addIncident(string $type, string $description, string $level = self::INCIDENT_LEVEL_LOW, string $action = ''): void
    {
        $incidents = $this->incidents ?? [];
        
        $incidents[] = [
            'time' => now()->format('H:i'),
            'type' => $type,
            'description' => $description,
            'level' => $level,
            'action_taken' => $action,
            'reported_by' => auth()->user()->name ?? '',
            'created_at' => now()->toISOString(),
        ];

        $this->incidents = $incidents;
    }

    /**
     * 巡回記録の追加
     */
    public function addPatrolRecord(string $time, string $location, string $status = '異常なし', string $notes = ''): void
    {
        $patrolRecords = $this->patrol_records ?? [];
        
        $patrolRecords[] = [
            'time' => $time,
            'location' => $location,
            'status' => $status,
            'notes' => $notes,
            'guard_name' => auth()->user()->name ?? '',
            'created_at' => now()->toISOString(),
        ];

        $this->patrol_records = $patrolRecords;
    }

    /**
     * 来訪者記録の追加
     */
    public function addVisitorRecord(string $time, string $name, string $company = '', string $purpose = '', string $action = ''): void
    {
        $visitorRecords = $this->visitor_records ?? [];
        
        $visitorRecords[] = [
            'time' => $time,
            'name' => $name,
            'company' => $company,
            'purpose' => $purpose,
            'action' => $action,
            'recorded_by' => auth()->user()->name ?? '',
            'created_at' => now()->toISOString(),
        ];

        $this->visitor_records = $visitorRecords;
    }

    /**
     * 写真の追加
     */
    public function addPhoto(string $filePath, string $description = '', string $category = 'general'): void
    {
        $photos = $this->photos ?? [];
        
        $photos[] = [
            'file_path' => $filePath,
            'description' => $description,
            'category' => $category,
            'taken_at' => now()->toISOString(),
            'uploaded_by' => auth()->user()->name ?? '',
        ];

        $this->photos = $photos;
    }

    /**
     * 警備員の配置
     */
    public function assignGuard(int $guardId, string $position = '', string $startTime = '', string $endTime = ''): void
    {
        $assignedGuards = $this->assigned_guards ?? [];
        
        // 既存の配置を更新または新規追加
        $found = false;
        foreach ($assignedGuards as &$guard) {
            if ($guard['guard_id'] == $guardId) {
                $guard['position'] = $position;
                $guard['start_time'] = $startTime;
                $guard['end_time'] = $endTime;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $guardModel = Guard::find($guardId);
            $assignedGuards[] = [
                'guard_id' => $guardId,
                'guard_name' => $guardModel ? $guardModel->name : '',
                'guard_code' => $guardModel ? $guardModel->guard_code : '',
                'position' => $position,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        }

        $this->assigned_guards = $assignedGuards;
    }

    /**
     * 日報の提出
     */
    public function submit(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        $this->status = self::STATUS_SUBMITTED;
        $this->submitted_at = now();
        $this->save();

        return true;
    }

    /**
     * 日報の承認
     */
    public function approve(int $approverId, string $notes = ''): bool
    {
        if ($this->status !== self::STATUS_SUBMITTED) {
            return false;
        }

        $this->status = self::STATUS_APPROVED;
        $this->approved_at = now();
        $this->approved_by = $approverId;
        $this->approval_notes = $notes;
        $this->save();

        return true;
    }

    /**
     * 日報の却下
     */
    public function reject(int $approverId, string $reason): bool
    {
        if ($this->status !== self::STATUS_SUBMITTED) {
            return false;
        }

        $this->status = self::STATUS_REJECTED;
        $this->approved_by = $approverId;
        $this->approval_notes = $reason;
        $this->save();

        return true;
    }

    /**
     * 修正要求
     */
    public function requestRevision(int $approverId, string $reason): bool
    {
        if ($this->status !== self::STATUS_SUBMITTED) {
            return false;
        }

        $this->status = self::STATUS_REVISION_REQUIRED;
        $this->approved_by = $approverId;
        $this->approval_notes = $reason;
        $this->save();

        return true;
    }

    /**
     * 事件・事故の総数を取得
     */
    public function getIncidentCountAttribute(): int
    {
        return count($this->incidents ?? []);
    }

    /**
     * 重要な事件・事故があるかを判定
     */
    public function hasCriticalIncidents(): bool
    {
        $incidents = $this->incidents ?? [];
        
        foreach ($incidents as $incident) {
            if (in_array($incident['level'], [self::INCIDENT_LEVEL_HIGH, self::INCIDENT_LEVEL_CRITICAL])) {
                return true;
            }
        }

        return false;
    }

    /**
     * 巡回記録の総数を取得
     */
    public function getPatrolCountAttribute(): int
    {
        return count($this->patrol_records ?? []);
    }

    /**
     * 来訪者記録の総数を取得
     */
    public function getVisitorCountAttribute(): int
    {
        return count($this->visitor_records ?? []);
    }

    /**
     * 配置警備員数を取得
     */
    public function getAssignedGuardCountAttribute(): int
    {
        return count($this->assigned_guards ?? []);
    }

    /**
     * 日報の完成度を取得（％）
     */
    public function getCompletenessAttribute(): float
    {
        $requiredFields = [
            'shift_time', 'weather_condition', 'assigned_guards', 
            'site_condition', 'equipment_status'
        ];
        
        $completedFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($this->$field)) {
                $completedFields++;
            }
        }

        return ($completedFields / count($requiredFields)) * 100;
    }

    /**
     * 月間日報統計
     */
    public static function getMonthlySummary(int $projectId, int $year, int $month): array
    {
        $reports = static::where('project_id', $projectId)
            ->whereYear('report_date', $year)
            ->whereMonth('report_date', $month)
            ->get();

        return [
            'total_reports' => $reports->count(),
            'submitted_reports' => $reports->where('status', self::STATUS_SUBMITTED)->count(),
            'approved_reports' => $reports->where('status', self::STATUS_APPROVED)->count(),
            'total_incidents' => $reports->sum(function($report) {
                return count($report->incidents ?? []);
            }),
            'critical_incidents' => $reports->filter(function($report) {
                return $report->hasCriticalIncidents();
            })->count(),
            'average_completeness' => $reports->avg('completeness'),
        ];
    }

    /**
     * 日報の詳細情報を配列で取得
     */
    public function getDetailedInfoAttribute(): array
    {
        return [
            'report_number' => $this->report_number,
            'project_name' => $this->project->name ?? '',
            'shift_name' => $this->shift->name ?? '',
            'status_label' => self::getAvailableStatuses()[$this->status] ?? '',
            'weather_label' => self::getAvailableWeatherConditions()[$this->weather_condition] ?? '',
            'incident_count' => $this->getIncidentCountAttribute(),
            'patrol_count' => $this->getPatrolCountAttribute(),
            'visitor_count' => $this->getVisitorCountAttribute(),
            'assigned_guard_count' => $this->getAssignedGuardCountAttribute(),
            'completeness' => $this->getCompletenessAttribute(),
            'has_critical_incidents' => $this->hasCriticalIncidents(),
        ];
    }
}
