<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * シフト・警備員割り当てテーブルのダミーデータ生成
 * 
 * シフトと警備員の具体的な割り当て情報を生成します。
 * 実際の勤務体制、交代制、警備員の配置等を想定。
 */
class ShiftGuardAssignmentsSeeder extends Seeder
{
    /**
     * ダミーデータを生成して挿入
     *
     * @return void
     */
    public function run(): void
    {
        $assignments = [
            // 新宿建設現場 日勤シフト（SH001）の割り当て
            [
                'shift_id' => 1, // SH001: 新宿建設現場 日勤シフト
                'guard_id' => 1, // 警備 太郎
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'leader',
                'position' => '正面入口担当',
                'responsibilities' => json_encode([
                    '現場全体の安全管理',
                    '作業員入退場管理',
                    '来客対応',
                    '新人警備員の指導',
                    '緊急時の指揮統制'
                ]),
                'equipment_assigned' => json_encode([
                    'トランシーバー（リーダー用）',
                    'ヘルメット',
                    '安全ベスト',
                    '誘導棒',
                    '現場責任者連絡先リスト'
                ]),
                'special_instructions' => '新人警備員の指導を兼任。朝の通勤ラッシュ時は特に注意深く歩行者誘導を実施。',
                'status' => 'confirmed',
                'assigned_by' => 2, // 佐藤 次郎
                'assigned_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3)
            ],
            [
                'shift_id' => 1, // SH001: 新宿建設現場 日勤シフト
                'guard_id' => 10, // 新人 一郎
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'assistant',
                'position' => '資材置場担当',
                'responsibilities' => json_encode([
                    '資材盗難防止',
                    '搬入出車両の確認',
                    '先輩警備員の補助',
                    '研修実践',
                    '日報作成補助'
                ]),
                'equipment_assigned' => json_encode([
                    'トランシーバー',
                    'ヘルメット',
                    '安全ベスト',
                    '研修用資料',
                    'チェックリスト'
                ]),
                'special_instructions' => '研修中のため警備 太郎の指導下で業務実施。基本動作の習得に重点を置く。',
                'status' => 'confirmed',
                'assigned_by' => 2, // 佐藤 次郎
                'assigned_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3)
            ],
            // 新宿建設現場 夜勤シフト（SH002）の割り当て
            [
                'shift_id' => 2, // SH002: 新宿建設現場 夜勤シフト
                'guard_id' => 2, // 守護 次郎
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'leader',
                'position' => '現場全体巡回',
                'responsibilities' => json_encode([
                    '夜間の現場警備',
                    '資材盗難防止',
                    '設備監視',
                    '2時間おきの巡回',
                    '異常時の緊急対応'
                ]),
                'equipment_assigned' => json_encode([
                    'トランシーバー',
                    '防犯カメラ監視',
                    '懐中電灯',
                    '防寒着',
                    '緊急連絡先リスト'
                ]),
                'special_instructions' => '夜間の単独警備。照明設備の確認と2時間おきの定時連絡を徹底。',
                'status' => 'confirmed',
                'assigned_by' => 2, // 佐藤 次郎
                'assigned_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3)
            ],
            [
                'shift_id' => 2, // SH002: 新宿建設現場 夜勤シフト
                'guard_id' => 3, // 安全 三郎
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'partner',
                'position' => '機材保管エリア',
                'responsibilities' => json_encode([
                    '機材保管エリア警備',
                    '夜間巡回の補助',
                    '防犯システム監視',
                    '緊急時対応',
                    '日報作成'
                ]),
                'equipment_assigned' => json_encode([
                    'トランシーバー',
                    '懐中電灯',
                    '防犯センサー',
                    '記録用タブレット',
                    '応急処置キット'
                ]),
                'special_instructions' => '機材の価値が高いため盗難防止を最優先。定期的に守護次郎と連携。',
                'status' => 'confirmed',
                'assigned_by' => 2, // 佐藤 次郎
                'assigned_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3)
            ],
            // 環状7号線工事 夜間シフトA（SH004）の割り当て
            [
                'shift_id' => 4, // SH004: 環状7号線工事 夜間シフトA
                'guard_id' => 2, // 守護 次郎
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'leader',
                'position' => '北側誘導ポイント1',
                'responsibilities' => json_encode([
                    'チーム全体の統括',
                    '交通誘導（主要ポイント）',
                    '工事責任者との連絡',
                    '緊急時の判断・指示',
                    '警察との連携'
                ]),
                'equipment_assigned' => json_encode([
                    'トランシーバー（統括用）',
                    'LED誘導棒',
                    '夜光反射材',
                    '電光掲示板',
                    '警察連絡先'
                ]),
                'special_instructions' => '交通量の多い幹線道路のため最高レベルの注意。チーム全体の安全を統括。',
                'status' => 'confirmed',
                'assigned_by' => 2, // 佐藤 次郎
                'assigned_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2)
            ],
            [
                'shift_id' => 4, // SH004: 環状7号線工事 夜間シフトA
                'guard_id' => 1, // 警備 太郎
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'member',
                'position' => '中央誘導ポイント2',
                'responsibilities' => json_encode([
                    '車両誘導（中央ポイント）',
                    '工事車両の安全確保',
                    '交通渋滞の監視',
                    '歩行者安全確保',
                    '異常時の報告'
                ]),
                'equipment_assigned' => json_encode([
                    'トランシーバー',
                    'LED誘導棒',
                    '夜光反射材',
                    '誘導看板',
                    'メガホン'
                ]),
                'special_instructions' => '工事区間の中央部で重要ポイント。大型車両の誘導時は特に注意。',
                'status' => 'confirmed',
                'assigned_by' => 2, // 佐藤 次郎
                'assigned_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2)
            ],
            [
                'shift_id' => 4, // SH004: 環状7号線工事 夜間シフトA
                'guard_id' => 3, // 安全 三郎
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'member',
                'position' => '南側誘導ポイント3',
                'responsibilities' => json_encode([
                    '車両誘導（南側ポイント）',
                    '迂回路案内',
                    '通行止め区間管理',
                    '緊急車両対応',
                    '交通状況記録'
                ]),
                'equipment_assigned' => json_encode([
                    'トランシーバー',
                    'LED誘導棒',
                    '夜光反射材',
                    '迂回路案内看板',
                    '記録用端末'
                ]),
                'special_instructions' => '迂回路への誘導が主要業務。緊急車両優先通行の徹底。',
                'status' => 'confirmed',
                'assigned_by' => 2, // 佐藤 次郎
                'assigned_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2)
            ],
            // センタービル 日勤シフト（SH006）の割り当て
            [
                'shift_id' => 6, // SH006: センタービル 日勤シフト
                'guard_id' => 3, // 安全 三郎
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'receptionist',
                'position' => '1F受付・エントランス',
                'responsibilities' => json_encode([
                    '来館者受付対応',
                    '入退館管理',
                    '郵便物・宅配受取',
                    '電話対応',
                    '防災センター監視'
                ]),
                'equipment_assigned' => json_encode([
                    '内線電話',
                    '入退館管理システム',
                    '防犯カメラ監視',
                    'PC端末',
                    '来客記録簿'
                ]),
                'special_instructions' => 'ビジネス時間帯の顔として丁寧な接客対応。VIP来館時は特別プロトコル適用。',
                'status' => 'confirmed',
                'assigned_by' => 3, // 不動産 三郎
                'assigned_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1)
            ],
            [
                'shift_id' => 6, // SH006: センタービル 日勤シフト
                'guard_id' => 8, // 保安 花子
                'assignment_date' => Carbon::yesterday()->format('Y-m-d'),
                'role' => 'receptionist',
                'position' => '1F受付・エントランス',
                'responsibilities' => json_encode([
                    '来館者受付対応',
                    '女性来館者への配慮',
                    '外国人対応',
                    '緊急時応急処置',
                    '館内案内'
                ]),
                'equipment_assigned' => json_encode([
                    '内線電話',
                    '入退館管理システム',
                    '応急処置キット',
                    '多言語案内',
                    'AED'
                ]),
                'special_instructions' => '女性ならではの細やかな対応。医療知識を活かした緊急時対応。',
                'status' => 'completed',
                'assigned_by' => 3, // 不動産 三郎
                'assigned_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()
            ],
            // 渋谷モール 開店シフト（SH009）の割り当て
            [
                'shift_id' => 9, // SH009: 渋谷モール 開店シフト
                'guard_id' => 4, // 監視 四郎
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'patrol_leader',
                'position' => '1F-4F担当',
                'responsibilities' => json_encode([
                    'フロア巡回警備',
                    '万引き防止監視',
                    'チーム統括',
                    '店舗との連携',
                    '緊急時対応'
                ]),
                'equipment_assigned' => json_encode([
                    'ヘッドセット',
                    'ハンディカメラ',
                    '館内地図',
                    '店舗連絡先リスト',
                    'メガホン'
                ]),
                'special_instructions' => 'イベント警備経験を活かし群衆管理。外国人観光客への多言語対応。',
                'status' => 'confirmed',
                'assigned_by' => 4, // 商業 四郎
                'assigned_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1)
            ],
            [
                'shift_id' => 9, // SH009: 渋谷モール 開店シフト
                'guard_id' => 5, // 巡回 五郎
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'patrol_member',
                'position' => '5F-8F担当',
                'responsibilities' => json_encode([
                    '上層階巡回警備',
                    '設備点検',
                    '客層監視',
                    '迷子対応',
                    '店舗サポート'
                ]),
                'equipment_assigned' => json_encode([
                    'ヘッドセット',
                    '点検用具',
                    '迷子対応セット',
                    '応急処置キット',
                    '館内案内'
                ]),
                'special_instructions' => '電気設備の知識を活かし設備異常の早期発見。商業施設での経験を活用。',
                'status' => 'confirmed',
                'assigned_by' => 4, // 商業 四郎
                'assigned_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1)
            ],
            // 渋谷モール 夕方シフト（SH010）の割り当て
            [
                'shift_id' => 10, // SH010: 渋谷モール 夕方シフト
                'guard_id' => 5, // 巡回 五郎
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'closing_leader',
                'position' => '全館統括',
                'responsibilities' => json_encode([
                    '閉店業務統括',
                    '施錠確認',
                    '最終巡回',
                    '店舗連携',
                    '夜間警備への引継ぎ'
                ]),
                'equipment_assigned' => json_encode([
                    'ヘッドセット',
                    '施錠確認リスト',
                    '最終点検表',
                    '夜間連絡先',
                    'マスターキー'
                ]),
                'special_instructions' => '混雑する夕方時間帯から閉店まで。電気設備の最終確認も実施。',
                'status' => 'confirmed',
                'assigned_by' => 4, // 商業 四郎
                'assigned_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1)
            ],
            [
                'shift_id' => 10, // SH010: 渋谷モール 夕方シフト
                'guard_id' => 9, // 監督 美咲
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'crowd_control',
                'position' => 'B2F-3F担当',
                'responsibilities' => json_encode([
                    '混雑時群衆管理',
                    '女性客対応',
                    '外国人観光客案内',
                    '安全確保',
                    'トラブル対応'
                ]),
                'equipment_assigned' => json_encode([
                    'ヘッドセット',
                    '多言語案内',
                    '群衆整理用具',
                    '応急処置キット',
                    'メガホン'
                ]),
                'special_instructions' => '女性リーダーとして女性客・子供への配慮。混雑時の安全確保を最優先。',
                'status' => 'confirmed',
                'assigned_by' => 4, // 商業 四郎
                'assigned_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1)
            ],
            // 製鉄工場 日勤A（SH011）の割り当て
            [
                'shift_id' => 11, // SH011: 製鉄工場 日勤A
                'guard_id' => 6, // 防衛 六郎
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'shift_supervisor',
                'position' => '正門・第1工場棟',
                'responsibilities' => json_encode([
                    'シフト全体の統括',
                    '入退場管理',
                    '作業員安全管理',
                    '消防設備点検',
                    '緊急時指揮'
                ]),
                'equipment_assigned' => json_encode([
                    '防爆無線機',
                    'ガス検知器',
                    '消防設備点検表',
                    '緊急連絡先リスト',
                    '生体認証端末'
                ]),
                'special_instructions' => '最高レベルの警備責任者。危険物・機密情報の管理を統括。25年の経験を活用。',
                'status' => 'confirmed',
                'assigned_by' => 5, // 伊藤 六郎
                'assigned_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1)
            ],
            [
                'shift_id' => 11, // SH011: 製鉄工場 日勤A
                'guard_id' => 7, // 警戒 七郎（特別勤務）
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'patrol_specialist',
                'position' => '工場内巡回',
                'responsibilities' => json_encode([
                    '工場内巡回警備',
                    '設備監視',
                    '異常検知',
                    '安全確認',
                    '記録作成'
                ]),
                'equipment_assigned' => json_encode([
                    '防爆無線機',
                    'ガス検知器',
                    '工場内地図',
                    '点検記録表',
                    '緊急用酸素ボンベ'
                ]),
                'special_instructions' => '通常は夜勤専門だが特別に日勤応援。工場設備に詳しく巡回警備を担当。',
                'status' => 'confirmed',
                'assigned_by' => 5, // 伊藤 六郎
                'assigned_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1)
            ],
            // 物流センター 夜間シフト（SH015）の割り当て
            [
                'shift_id' => 15, // SH015: ロジセンター 夜間シフト
                'guard_id' => 7, // 警戒 七郎
                'assignment_date' => Carbon::today()->format('Y-m-d'),
                'role' => 'night_supervisor',
                'position' => '倉庫全体・トラックヤード',
                'responsibilities' => json_encode([
                    '夜間警備統括',
                    '配送トラック管理',
                    '冷凍倉庫監視',
                    '温度管理',
                    '緊急時対応'
                ]),
                'equipment_assigned' => json_encode([
                    '無線機',
                    '温度監視システム',
                    'フォークリフト',
                    '防寒装備',
                    '緊急連絡先'
                ]),
                'special_instructions' => '夜間の長時間勤務。冷凍設備の異常には特に注意。配送業者との連携重要。',
                'status' => 'confirmed',
                'assigned_by' => 5, // 伊藤 六郎
                'assigned_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1)
            ],
            // 急遽の変更・欠勤対応の例
            [
                'shift_id' => 1, // SH001: 新宿建設現場 日勤シフト
                'guard_id' => 2, // 守護 次郎（代行）
                'assignment_date' => Carbon::tomorrow()->format('Y-m-d'),
                'role' => 'replacement',
                'position' => '正面入口担当（代行）',
                'responsibilities' => json_encode([
                    '警備 太郎の代行業務',
                    '現場安全管理',
                    '作業員入退場管理',
                    '交通誘導',
                    '緊急時対応'
                ]),
                'equipment_assigned' => json_encode([
                    'トランシーバー',
                    'ヘルメット',
                    '安全ベスト',
                    '誘導棒',
                    '引継ぎ資料'
                ]),
                'special_instructions' => '警備 太郎の急病による代行。通常の夜勤から日勤へのシフト変更。',
                'status' => 'pending',
                'assigned_by' => 2, // 佐藤 次郎
                'assigned_at' => Carbon::now()->subHours(2),
                'created_at' => Carbon::now()->subHours(2),
                'updated_at' => Carbon::now()->subHours(2)
            ],
            // 週末・祝日の特別体制
            [
                'shift_id' => 9, // SH009: 渋谷モール 開店シフト
                'guard_id' => 9, // 監督 美咲（週末増員）
                'assignment_date' => Carbon::now()->addDays(2)->format('Y-m-d'), // 土曜日
                'role' => 'weekend_reinforcement',
                'position' => '全館統括（週末体制）',
                'responsibilities' => json_encode([
                    '週末混雑対応',
                    '警備員統括',
                    '群衆管理',
                    '安全確保',
                    '店舗連携強化'
                ]),
                'equipment_assigned' => json_encode([
                    'ヘッドセット',
                    '統括用端末',
                    '群衆整理用具',
                    'メガホン',
                    '週末対応マニュアル'
                ]),
                'special_instructions' => '週末の混雑に備えた増員配置。女性リーダーとして全体を統括。',
                'status' => 'scheduled',
                'assigned_by' => 4, // 商業 四郎
                'assigned_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3)
            ]
        ];

        // データを挿入
        DB::table('shift_guard_assignments')->insert($assignments);

        echo "Shift Guard Assignments seeder completed. " . count($assignments) . " assignments created.\n";
    }
}
