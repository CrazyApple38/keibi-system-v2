<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 出勤・勤怠テーブルのダミーデータ生成
 * 
 * 警備員の実際の出勤状況、勤怠記録を生成します。
 * 正常出勤、遅刻、早退、欠勤等の様々なパターンを想定。
 */
class AttendancesSeeder extends Seeder
{
    /**
     * ダミーデータを生成して挿入
     *
     * @return void
     */
    public function run(): void
    {
        $attendances = [
            // 警備員1（警備 太郎）の出勤記録
            [
                'guard_id' => 1,
                'shift_id' => 1,
                'attendance_date' => Carbon::today()->format('Y-m-d'),
                'scheduled_start_time' => '08:00:00',
                'scheduled_end_time' => '16:00:00',
                'actual_start_time' => '07:55:00',
                'actual_end_time' => '16:05:00',
                'break_start_time' => '12:00:00',
                'break_end_time' => '13:00:00',
                'total_work_hours' => 8.0,
                'overtime_hours' => 0.0,
                'break_hours' => 1.0,
                'status' => 'completed',
                'location_checkin' => json_encode([
                    'latitude' => 35.6895,
                    'longitude' => 139.6917,
                    'address' => '東京都新宿区西新宿1-15-20',
                    'timestamp' => '07:55:00'
                ]),
                'location_checkout' => json_encode([
                    'latitude' => 35.6895,
                    'longitude' => 139.6917,
                    'address' => '東京都新宿区西新宿1-15-20',
                    'timestamp' => '16:05:00'
                ]),
                'notes' => '5分早く到着、5分遅く退勤。現場での引き継ぎのため。',
                'weather_conditions' => json_encode([
                    'temperature' => 22,
                    'humidity' => 65,
                    'condition' => '晴れ',
                    'wind_speed' => 5
                ]),
                'incidents' => json_encode([]),
                'performance_rating' => 5,
                'supervisor_notes' => '責任感の強い勤務態度。現場での指導も適切に実施。',
                'approved_by' => 2,
                'approved_at' => Carbon::now()->subHours(2),
                'created_at' => Carbon::now()->subHours(10),
                'updated_at' => Carbon::now()->subHours(2)
            ],
            [
                'guard_id' => 1,
                'shift_id' => 2,
                'attendance_date' => Carbon::yesterday()->format('Y-m-d'),
                'scheduled_start_time' => '16:00:00',
                'scheduled_end_time' => '00:00:00',
                'actual_start_time' => '16:00:00',
                'actual_end_time' => '00:00:00',
                'break_start_time' => '20:00:00',
                'break_end_time' => '21:00:00',
                'total_work_hours' => 8.0,
                'overtime_hours' => 0.0,
                'break_hours' => 1.0,
                'status' => 'completed',
                'location_checkin' => json_encode([
                    'latitude' => 35.6895,
                    'longitude' => 139.6917,
                    'address' => '東京都新宿区西新宿1-15-20',
                    'timestamp' => '16:00:00'
                ]),
                'location_checkout' => json_encode([
                    'latitude' => 35.6895,
                    'longitude' => 139.6917,
                    'address' => '東京都新宿区西新宿1-15-20',
                    'timestamp' => '00:00:00'
                ]),
                'notes' => '正常勤務。夜間の巡回業務を適切に実施。',
                'weather_conditions' => json_encode([
                    'temperature' => 18,
                    'humidity' => 70,
                    'condition' => '曇り',
                    'wind_speed' => 3
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '22:30:00',
                        'type' => '巡回中発見',
                        'description' => '現場周辺の街灯1箇所が点灯していない状況を発見。翌朝報告予定。',
                        'action_taken' => '写真撮影、位置記録'
                    ]
                ]),
                'performance_rating' => 5,
                'supervisor_notes' => '夜間勤務中も細かい点まで気づく観察力。優秀。',
                'approved_by' => 2,
                'approved_at' => Carbon::yesterday()->addHours(8),
                'created_at' => Carbon::yesterday()->addHours(8),
                'updated_at' => Carbon::yesterday()->addHours(8)
            ],
            // 警備員2（守護 次郎）の出勤記録
            [
                'guard_id' => 2,
                'shift_id' => 4,
                'attendance_date' => Carbon::today()->format('Y-m-d'),
                'scheduled_start_time' => '21:00:00',
                'scheduled_end_time' => '06:00:00',
                'actual_start_time' => '21:05:00',
                'actual_end_time' => '06:00:00',
                'break_start_time' => '02:00:00',
                'break_end_time' => '03:00:00',
                'total_work_hours' => 8.9,
                'overtime_hours' => 0.0,
                'break_hours' => 1.0,
                'status' => 'completed',
                'location_checkin' => json_encode([
                    'latitude' => 35.7210,
                    'longitude' => 139.8107,
                    'address' => '東京都墨田区環状7号線工事現場',
                    'timestamp' => '21:05:00'
                ]),
                'location_checkout' => json_encode([
                    'latitude' => 35.7210,
                    'longitude' => 139.8107,
                    'address' => '東京都墨田区環状7号線工事現場',
                    'timestamp' => '06:00:00'
                ]),
                'notes' => '交通渋滞により5分遅刻。工事責任者と連絡を取り、問題なし。',
                'weather_conditions' => json_encode([
                    'temperature' => 15,
                    'humidity' => 80,
                    'condition' => '小雨',
                    'wind_speed' => 8
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '23:45:00',
                        'type' => '交通事故軽微',
                        'description' => '工事区間手前で追突事故発生。警察到着まで交通整理実施。',
                        'action_taken' => '110番通報、現場保全、交通誘導'
                    ]
                ]),
                'performance_rating' => 4,
                'supervisor_notes' => '緊急時の対応が適切。交通事故への初期対応も良好。',
                'approved_by' => 2,
                'approved_at' => Carbon::now()->subHours(1),
                'created_at' => Carbon::now()->subHours(12),
                'updated_at' => Carbon::now()->subHours(1)
            ],
            [
                'guard_id' => 2,
                'shift_id' => 5,
                'attendance_date' => Carbon::yesterday()->format('Y-m-d'),
                'scheduled_start_time' => '21:00:00',
                'scheduled_end_time' => '06:00:00',
                'actual_start_time' => '20:55:00',
                'actual_end_time' => '06:15:00',
                'break_start_time' => '02:00:00',
                'break_end_time' => '03:00:00',
                'total_work_hours' => 9.25,
                'overtime_hours' => 0.25,
                'break_hours' => 1.0,
                'status' => 'completed',
                'location_checkin' => json_encode([
                    'latitude' => 35.7210,
                    'longitude' => 139.8107,
                    'address' => '東京都墨田区環状7号線工事現場',
                    'timestamp' => '20:55:00'
                ]),
                'location_checkout' => json_encode([
                    'latitude' => 35.7210,
                    'longitude' => 139.8107,
                    'address' => '東京都墨田区環状7号線工事現場',
                    'timestamp' => '06:15:00'
                ]),
                'notes' => '工事が予定より遅れたため15分延長勤務。残業代支給対象。',
                'weather_conditions' => json_encode([
                    'temperature' => 13,
                    'humidity' => 75,
                    'condition' => '晴れ',
                    'wind_speed' => 6
                ]),
                'incidents' => json_encode([]),
                'performance_rating' => 5,
                'supervisor_notes' => '工事遅延に柔軟に対応。責任感が強い。',
                'approved_by' => 2,
                'approved_at' => Carbon::yesterday()->addHours(8),
                'created_at' => Carbon::yesterday()->addHours(8),
                'updated_at' => Carbon::yesterday()->addHours(8)
            ],
            // 警備員3（安全 三郎）の出勤記録
            [
                'guard_id' => 3,
                'shift_id' => 6,
                'attendance_date' => Carbon::today()->format('Y-m-d'),
                'scheduled_start_time' => '08:00:00',
                'scheduled_end_time' => '16:00:00',
                'actual_start_time' => '08:00:00',
                'actual_end_time' => '16:00:00',
                'break_start_time' => '12:00:00',
                'break_end_time' => '13:00:00',
                'total_work_hours' => 8.0,
                'overtime_hours' => 0.0,
                'break_hours' => 1.0,
                'status' => 'completed',
                'location_checkin' => json_encode([
                    'latitude' => 35.6938,
                    'longitude' => 139.7034,
                    'address' => '東京都新宿区新宿3-3-3',
                    'timestamp' => '08:00:00'
                ]),
                'location_checkout' => json_encode([
                    'latitude' => 35.6938,
                    'longitude' => 139.7034,
                    'address' => '東京都新宿区新宿3-3-3',
                    'timestamp' => '16:00:00'
                ]),
                'notes' => '時間通りの勤務。受付業務を中心に実施。',
                'weather_conditions' => json_encode([
                    'temperature' => 24,
                    'humidity' => 60,
                    'condition' => '晴れ',
                    'wind_speed' => 4
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '10:30:00',
                        'type' => '来客対応',
                        'description' => '外国人観光客から道案内を求められ、英語で対応。',
                        'action_taken' => '目的地への案内、地図提供'
                    ],
                    [
                        'time' => '14:15:00',
                        'type' => '設備点検',
                        'description' => 'エレベーター1台が一時停止。保守会社へ連絡。',
                        'action_taken' => '保守会社通報、利用者への案内'
                    ]
                ]),
                'performance_rating' => 4,
                'supervisor_notes' => '接客対応が丁寧。設備トラブルへの対応も適切。',
                'approved_by' => 3,
                'approved_at' => Carbon::now()->subHours(1),
                'created_at' => Carbon::now()->subHours(8),
                'updated_at' => Carbon::now()->subHours(1)
            ],
            // 警備員4（監視 四郎）の出勤記録
            [
                'guard_id' => 4,
                'shift_id' => 9,
                'attendance_date' => Carbon::today()->format('Y-m-d'),
                'scheduled_start_time' => '09:00:00',
                'scheduled_end_time' => '15:00:00',
                'actual_start_time' => '08:55:00',
                'actual_end_time' => '15:10:00',
                'break_start_time' => '12:00:00',
                'break_end_time' => '13:00:00',
                'total_work_hours' => 6.25,
                'overtime_hours' => 0.0,
                'break_hours' => 1.0,
                'status' => 'completed',
                'location_checkin' => json_encode([
                    'latitude' => 35.6584,
                    'longitude' => 139.7016,
                    'address' => '東京都渋谷区道玄坂4-4-4',
                    'timestamp' => '08:55:00'
                ]),
                'location_checkout' => json_encode([
                    'latitude' => 35.6584,
                    'longitude' => 139.7016,
                    'address' => '東京都渋谷区道玄坂4-4-4',
                    'timestamp' => '15:10:00'
                ]),
                'notes' => '5分早く到着。引き継ぎのため10分延長。平日で客足は普通。',
                'weather_conditions' => json_encode([
                    'temperature' => 26,
                    'humidity' => 58,
                    'condition' => '晴れ',
                    'wind_speed' => 3
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '11:20:00',
                        'type' => '迷子対応',
                        'description' => '5歳の男児が母親とはぐれた。館内放送で母親を呼び出し、無事再会。',
                        'action_taken' => '館内放送、保護、母親との再会'
                    ],
                    [
                        'time' => '13:45:00',
                        'type' => '万引き疑い',
                        'description' => '若い女性の行動が不審。店舗スタッフと連携し、最終的に問題なし。',
                        'action_taken' => '慎重な観察、店舗スタッフとの連携'
                    ]
                ]),
                'performance_rating' => 4,
                'supervisor_notes' => '観察力が鋭い。迷子対応も適切で家族に感謝された。',
                'approved_by' => 4,
                'approved_at' => Carbon::now()->subHours(1),
                'created_at' => Carbon::now()->subHours(6),
                'updated_at' => Carbon::now()->subHours(1)
            ],
            // 警備員5（巡回 五郎）の出勤記録
            [
                'guard_id' => 5,
                'shift_id' => 10,
                'attendance_date' => Carbon::today()->format('Y-m-d'),
                'scheduled_start_time' => '15:00:00',
                'scheduled_end_time' => '22:00:00',
                'actual_start_time' => '15:00:00',
                'actual_end_time' => '22:30:00',
                'break_start_time' => '18:30:00',
                'break_end_time' => '19:30:00',
                'total_work_hours' => 7.5,
                'overtime_hours' => 0.5,
                'break_hours' => 1.0,
                'status' => 'completed',
                'location_checkin' => json_encode([
                    'latitude' => 35.6584,
                    'longitude' => 139.7016,
                    'address' => '東京都渋谷区道玄坂4-4-4',
                    'timestamp' => '15:00:00'
                ]),
                'location_checkout' => json_encode([
                    'latitude' => 35.6584,
                    'longitude' => 139.7016,
                    'address' => '東京都渋谷区道玄坂4-4-4',
                    'timestamp' => '22:30:00'
                ]),
                'notes' => '閉店後の施錠確認作業のため30分延長。全店舗の施錠を確認。',
                'weather_conditions' => json_encode([
                    'temperature' => 23,
                    'humidity' => 62,
                    'condition' => '晴れ',
                    'wind_speed' => 2
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '17:30:00',
                        'type' => '設備故障',
                        'description' => '3階の照明が一部点灯しない。電気工事士資格を活かし初期対応実施。',
                        'action_taken' => '電気系統点検、管理会社への連絡'
                    ],
                    [
                        'time' => '21:15:00',
                        'type' => '酔客対応',
                        'description' => '泥酔した男性客をタクシーまで案内。トラブルなく解決。',
                        'action_taken' => 'タクシー手配、安全な退館誘導'
                    ]
                ]),
                'performance_rating' => 5,
                'supervisor_notes' => '電気系統の知識を活かした対応が素晴らしい。酔客対応も適切。',
                'approved_by' => 4,
                'approved_at' => Carbon::now()->subMinutes(30),
                'created_at' => Carbon::now()->subHours(1),
                'updated_at' => Carbon::now()->subMinutes(30)
            ],
            // 警備員6（防衛 六郎）の出勤記録
            [
                'guard_id' => 6,
                'shift_id' => 11,
                'attendance_date' => Carbon::today()->format('Y-m-d'),
                'scheduled_start_time' => '06:00:00',
                'scheduled_end_time' => '12:00:00',
                'actual_start_time' => '05:55:00',
                'actual_end_time' => '12:00:00',
                'break_start_time' => '09:00:00',
                'break_end_time' => '09:30:00',
                'total_work_hours' => 6.0,
                'overtime_hours' => 0.0,
                'break_hours' => 0.5,
                'status' => 'completed',
                'location_checkin' => json_encode([
                    'latitude' => 35.7749,
                    'longitude' => 139.8107,
                    'address' => '東京都足立区綾瀬7-7-7',
                    'timestamp' => '05:55:00'
                ]),
                'location_checkout' => json_encode([
                    'latitude' => 35.7749,
                    'longitude' => 139.8107,
                    'address' => '東京都足立区綾瀬7-7-7',
                    'timestamp' => '12:00:00'
                ]),
                'notes' => '5分早く到着。作業員の入場ラッシュに備えて準備。',
                'weather_conditions' => json_encode([
                    'temperature' => 20,
                    'humidity' => 70,
                    'condition' => '曇り',
                    'wind_speed' => 7
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '07:30:00',
                        'type' => '安全点検',
                        'description' => '第2工場の消火設備定期点検を実施。異常なし。',
                        'action_taken' => '消火設備点検、記録作成'
                    ],
                    [
                        'time' => '10:15:00',
                        'type' => '来客対応',
                        'description' => '取引先の重役来訪。身元確認後、工場長室へ案内。',
                        'action_taken' => '身元確認、来客記録、案内'
                    ]
                ]),
                'performance_rating' => 5,
                'supervisor_notes' => 'ベテランの安定した勤務。消防設備の知識も豊富で頼もしい。',
                'approved_by' => 5,
                'approved_at' => Carbon::now()->subHours(2),
                'created_at' => Carbon::now()->subHours(6),
                'updated_at' => Carbon::now()->subHours(2)
            ],
            // 警備員7（警戒 七郎）の出勤記録
            [
                'guard_id' => 7,
                'shift_id' => 15,
                'attendance_date' => Carbon::today()->format('Y-m-d'),
                'scheduled_start_time' => '18:00:00',
                'scheduled_end_time' => '08:00:00',
                'actual_start_time' => '17:55:00',
                'actual_end_time' => '08:00:00',
                'break_start_time' => '00:00:00',
                'break_end_time' => '02:00:00',
                'total_work_hours' => 12.0,
                'overtime_hours' => 0.0,
                'break_hours' => 2.0,
                'status' => 'completed',
                'location_checkin' => json_encode([
                    'latitude' => 35.6828,
                    'longitude' => 139.9069,
                    'address' => '東京都江戸川区西葛西8-8-8',
                    'timestamp' => '17:55:00'
                ]),
                'location_checkout' => json_encode([
                    'latitude' => 35.6828,
                    'longitude' => 139.9069,
                    'address' => '東京都江戸川区西葛西8-8-8',
                    'timestamp' => '08:00:00'
                ]),
                'notes' => '夜間の長時間勤務。配送トラックの入退場管理を中心に実施。',
                'weather_conditions' => json_encode([
                    'temperature' => 16,
                    'humidity' => 75,
                    'condition' => '曇り',
                    'wind_speed' => 5
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '22:30:00',
                        'type' => '冷凍設備点検',
                        'description' => '冷凍倉庫A棟の温度が-15℃から-12℃に上昇。管理者へ連絡。',
                        'action_taken' => '温度記録、管理者連絡、継続監視'
                    ],
                    [
                        'time' => '03:45:00',
                        'type' => '配送業務',
                        'description' => '深夜配送のトラック5台の入退場管理。荷物の搬入立会い。',
                        'action_taken' => '車両確認、荷物確認、記録作成'
                    ]
                ]),
                'performance_rating' => 4,
                'supervisor_notes' => '夜間の長時間勤務を着実にこなす。冷凍設備への対応も適切。',
                'approved_by' => 6,
                'approved_at' => Carbon::now()->subHours(3),
                'created_at' => Carbon::now()->subHours(12),
                'updated_at' => Carbon::now()->subHours(3)
            ],
            // 警備員8（保安 花子）の出勤記録
            [
                'guard_id' => 8,
                'shift_id' => 6,
                'attendance_date' => Carbon::yesterday()->format('Y-m-d'),
                'scheduled_start_time' => '08:00:00',
                'scheduled_end_time' => '16:00:00',
                'actual_start_time' => '08:00:00',
                'actual_end_time' => '16:00:00',
                'break_start_time' => '12:00:00',
                'break_end_time' => '13:00:00',
                'total_work_hours' => 8.0,
                'overtime_hours' => 0.0,
                'break_hours' => 1.0,
                'status' => 'completed',
                'location_checkin' => json_encode([
                    'latitude' => 35.6938,
                    'longitude' => 139.7034,
                    'address' => '東京都新宿区新宿3-3-3',
                    'timestamp' => '08:00:00'
                ]),
                'location_checkout' => json_encode([
                    'latitude' => 35.6938,
                    'longitude' => 139.7034,
                    'address' => '東京都新宿区新宿3-3-3',
                    'timestamp' => '16:00:00'
                ]),
                'notes' => '女性ならではの細やかな対応で来館者に好評。',
                'weather_conditions' => json_encode([
                    'temperature' => 25,
                    'humidity' => 58,
                    'condition' => '晴れ',
                    'wind_speed' => 4
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '09:30:00',
                        'type' => '体調不良者対応',
                        'description' => '妊婦の方が気分が悪くなった。休憩室で休んでいただき、回復を確認。',
                        'action_taken' => '休憩室案内、水分補給、回復確認'
                    ],
                    [
                        'time' => '14:00:00',
                        'type' => '英語対応',
                        'description' => '外国人観光客から館内施設の説明を求められ、英語で対応。',
                        'action_taken' => '英語での館内案内、地図提供'
                    ]
                ]),
                'performance_rating' => 5,
                'supervisor_notes' => '女性特有の細やかな配慮が光る。英語対応も上手で観光客に好評。',
                'approved_by' => 3,
                'approved_at' => Carbon::yesterday()->addHours(8),
                'created_at' => Carbon::yesterday()->addHours(8),
                'updated_at' => Carbon::yesterday()->addHours(8)
            ],
            // 警備員10（新人 一郎）の出勤記録（研修中）
            [
                'guard_id' => 10,
                'shift_id' => 1,
                'attendance_date' => Carbon::today()->format('Y-m-d'),
                'scheduled_start_time' => '08:00:00',
                'scheduled_end_time' => '16:00:00',
                'actual_start_time' => '07:50:00',
                'actual_end_time' => '16:00:00',
                'break_start_time' => '12:00:00',
                'break_end_time' => '13:00:00',
                'total_work_hours' => 8.0,
                'overtime_hours' => 0.0,
                'break_hours' => 1.0,
                'status' => 'completed',
                'location_checkin' => json_encode([
                    'latitude' => 35.6895,
                    'longitude' => 139.6917,
                    'address' => '東京都新宿区西新宿1-15-20',
                    'timestamp' => '07:50:00'
                ]),
                'location_checkout' => json_encode([
                    'latitude' => 35.6895,
                    'longitude' => 139.6917,
                    'address' => '東京都新宿区西新宿1-15-20',
                    'timestamp' => '16:00:00'
                ]),
                'notes' => '研修中のため先輩警備員の指導の下で勤務。意欲的に学習中。',
                'weather_conditions' => json_encode([
                    'temperature' => 22,
                    'humidity' => 65,
                    'condition' => '晴れ',
                    'wind_speed' => 5
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '10:00:00',
                        'type' => '研修実践',
                        'description' => '先輩指導の下、来客対応の実践研修を実施。',
                        'action_taken' => '来客案内、記録作成の練習'
                    ],
                    [
                        'time' => '15:00:00',
                        'type' => '資格学習',
                        'description' => '警備員検定2級の学習時間。交通誘導の基礎を学習。',
                        'action_taken' => 'テキスト学習、実技練習'
                    ]
                ]),
                'performance_rating' => 3,
                'supervisor_notes' => '新人ながら非常に意欲的。体力もあり、将来有望。指導を継続。',
                'approved_by' => 1,
                'approved_at' => Carbon::now()->subHours(1),
                'created_at' => Carbon::now()->subHours(8),
                'updated_at' => Carbon::now()->subHours(1)
            ],
            // 欠勤・遅刻・早退の例
            [
                'guard_id' => 3,
                'shift_id' => 7,
                'attendance_date' => Carbon::yesterday()->format('Y-m-d'),
                'scheduled_start_time' => '16:00:00',
                'scheduled_end_time' => '00:00:00',
                'actual_start_time' => '16:25:00',
                'actual_end_time' => '00:00:00',
                'break_start_time' => '20:00:00',
                'break_end_time' => '21:00:00',
                'total_work_hours' => 7.58,
                'overtime_hours' => 0.0,
                'break_hours' => 1.0,
                'status' => 'completed',
                'location_checkin' => json_encode([
                    'latitude' => 35.6938,
                    'longitude' => 139.7034,
                    'address' => '東京都新宿区新宿3-3-3',
                    'timestamp' => '16:25:00'
                ]),
                'location_checkout' => json_encode([
                    'latitude' => 35.6938,
                    'longitude' => 139.7034,
                    'address' => '東京都新宿区新宿3-3-3',
                    'timestamp' => '00:00:00'
                ]),
                'notes' => '電車遅延により25分遅刻。遅延証明書提出済み。',
                'weather_conditions' => json_encode([
                    'temperature' => 19,
                    'humidity' => 72,
                    'condition' => '雨',
                    'wind_speed' => 6
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '16:25:00',
                        'type' => '遅刻',
                        'description' => 'JR中央線の人身事故による電車遅延。遅延証明書あり。',
                        'action_taken' => '遅延証明書提出、管理者への報告'
                    ]
                ]),
                'performance_rating' => 3,
                'supervisor_notes' => '電車遅延は不可抗力だが、余裕を持った出勤を心がけてほしい。',
                'approved_by' => 3,
                'approved_at' => Carbon::yesterday()->addHours(10),
                'created_at' => Carbon::yesterday()->addHours(10),
                'updated_at' => Carbon::yesterday()->addHours(10)
            ]
        ];

        // データを挿入
        DB::table('attendances')->insert($attendances);

        echo "Attendances seeder completed. " . count($attendances) . " attendance records created.\n";
    }
}
