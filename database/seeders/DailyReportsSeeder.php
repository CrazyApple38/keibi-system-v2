<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 日報テーブルのダミーデータ生成
 * 
 * 警備員の日々の業務報告書を生成します。
 * 現場での状況報告、インシデント報告、業務完了報告等を想定。
 */
class DailyReportsSeeder extends Seeder
{
    /**
     * ダミーデータを生成して挿入
     *
     * @return void
     */
    public function run(): void
    {
        $dailyReports = [
            // 建設現場警備の日報
            [
                'report_number' => 'DR2024-001',
                'project_id' => 1, // 新宿駅前再開発ビル建設現場警備
                'guard_id' => 1, // 警備 太郎
                'shift_id' => 1,
                'report_date' => Carbon::today()->format('Y-m-d'),
                'shift_time' => '08:00-16:00',
                'weather_condition' => '晴れ',
                'temperature' => 22,
                'summary' => '建設現場日勤警備を実施。朝の通勤ラッシュ時の歩行者誘導、大型車両進入時の交通整理を適切に行った。現場作業員との連携も良好で、安全な作業環境を維持できた。',
                'activities' => json_encode([
                    [
                        'time' => '08:00',
                        'activity' => '勤務開始・現場確認',
                        'details' => '夜勤者からの引継ぎ、現場の安全確認、機材点検'
                    ],
                    [
                        'time' => '08:30',
                        'activity' => '通勤ラッシュ対応',
                        'details' => '歩行者への注意喚起、安全な通路確保'
                    ],
                    [
                        'time' => '10:15',
                        'activity' => '大型車両誘導',
                        'details' => 'コンクリートミキサー車3台の安全誘導'
                    ],
                    [
                        'time' => '12:00',
                        'activity' => '昼休憩・現場巡回',
                        'details' => '資材置場の確認、不審者侵入チェック'
                    ],
                    [
                        'time' => '14:30',
                        'activity' => '資材搬入立会い',
                        'details' => '鉄骨資材搬入時の安全確保、交通整理'
                    ],
                    [
                        'time' => '16:00',
                        'activity' => '勤務終了・引継ぎ',
                        'details' => '夜勤者への業務引継ぎ、日報作成'
                    ]
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '13:45',
                        'type' => '軽微事故',
                        'description' => '作業員が足場から工具を落下させた',
                        'action_taken' => '即座に下部の安全確認、工具回収、作業員への注意喚起',
                        'injury' => 'なし',
                        'damage' => 'なし'
                    ]
                ]),
                'observations' => json_encode([
                    '朝の通勤時間帯の歩行者が多く、特に注意が必要',
                    '現場北側の街灯が1箇所点灯していない',
                    '近隣住民からの工事音に関する軽微な苦情1件',
                    '作業員の安全意識は高く、ヘルメット着用率100%'
                ]),
                'equipment_used' => json_encode([
                    'トランシーバー',
                    '誘導棒',
                    'ヘルメット',
                    '安全ベスト',
                    'カラーコーン'
                ]),
                'visitors' => json_encode([
                    [
                        'time' => '11:30',
                        'name' => '現場監督 田中様',
                        'company' => '東京建設株式会社',
                        'purpose' => '工事進捗確認'
                    ],
                    [
                        'time' => '15:20',
                        'name' => '安全管理者 佐藤様',
                        'company' => '東京建設株式会社',
                        'purpose' => '安全パトロール'
                    ]
                ]),
                'recommendations' => json_encode([
                    '北側街灯の修理を管理会社に連絡する必要あり',
                    '朝の通勤時間帯の誘導体制を2名に増強することを検討',
                    '近隣住民への騒音配慮をより徹底する'
                ]),
                'next_shift_notes' => '夜勤担当者は北側街灯の消灯箇所を重点的に警戒してください。また、明日朝は大型クレーン車の搬入予定があります。',
                'photos_attached' => true,
                'photo_count' => 3,
                'submitted_by' => 1, // 警備 太郎
                'reviewed_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'status' => 'approved',
                'created_at' => Carbon::today()->addHours(8),
                'updated_at' => Carbon::today()->addHours(10)
            ],
            // 道路工事夜間警備の日報
            [
                'report_number' => 'DR2024-002',
                'project_id' => 2, // 環状7号線道路工事夜間警備
                'guard_id' => 2, // 守護 次郎
                'shift_id' => 4,
                'report_date' => Carbon::today()->format('Y-m-d'),
                'shift_time' => '21:00-06:00',
                'weather_condition' => '小雨',
                'temperature' => 15,
                'summary' => '環状7号線道路工事現場での夜間警備を実施。小雨の影響で路面が滑りやすく、特に注意深い交通誘導を行った。工事は予定通り進行し、大きなトラブルはなかった。',
                'activities' => json_encode([
                    [
                        'time' => '21:00',
                        'activity' => '夜勤開始・現場設営',
                        'details' => '工事区間の安全確認、誘導看板設置、照明器具点検'
                    ],
                    [
                        'time' => '21:30',
                        'activity' => '工事開始立会い',
                        'details' => '道路舗装工事開始、交通規制開始'
                    ],
                    [
                        'time' => '23:00',
                        'activity' => '交通量監視',
                        'details' => '夜間交通量のピーク、大型車両誘導多数'
                    ],
                    [
                        'time' => '02:00',
                        'activity' => '休憩・現場確認',
                        'details' => '工事進捗確認、安全状況チェック'
                    ],
                    [
                        'time' => '04:30',
                        'activity' => '工事完了・撤収',
                        'details' => '工事機材撤収立会い、道路清掃確認'
                    ],
                    [
                        'time' => '06:00',
                        'activity' => '勤務終了',
                        'details' => '交通規制解除、日勤者への引継ぎ'
                    ]
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '23:45',
                        'type' => '軽微事故',
                        'description' => '工事区間手前で乗用車がスリップ',
                        'action_taken' => '車両確認、運転者安全確認、警察への連絡',
                        'injury' => 'なし',
                        'damage' => '車両軽微な擦り傷'
                    ]
                ]),
                'observations' => json_encode([
                    '小雨により路面が滑りやすく、スリップ事故のリスクが高い',
                    '夜間の視界が悪く、反射材の重要性を再認識',
                    '工事責任者の安全管理が徹底されている',
                    '近隣住民からの騒音苦情なし'
                ]),
                'equipment_used' => json_encode([
                    'トランシーバー',
                    '誘導棒（LED付き）',
                    '反射材付き警備服',
                    '投光器',
                    '電光掲示板'
                ]),
                'visitors' => json_encode([
                    [
                        'time' => '22:15',
                        'name' => '工事責任者 土木様',
                        'company' => '関東土木工業株式会社',
                        'purpose' => '工事進捗確認'
                    ]
                ]),
                'recommendations' => json_encode([
                    '雨天時の滑り止め対策を強化する必要あり',
                    '視界不良時の照明設備を増設することを検討',
                    '緊急時の連絡体制を再確認する'
                ]),
                'next_shift_notes' => '明日夜も小雨の予報です。路面状況に十分注意し、照明を増設して視界確保に努めてください。',
                'photos_attached' => true,
                'photo_count' => 5,
                'submitted_by' => 2, // 守護 次郎
                'reviewed_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'status' => 'approved',
                'created_at' => Carbon::today()->addHours(6),
                'updated_at' => Carbon::today()->addHours(8)
            ],
            // オフィスビル警備の日報
            [
                'report_number' => 'DR2024-003',
                'project_id' => 3, // 新宿センタービル24時間警備
                'guard_id' => 3, // 安全 三郎
                'shift_id' => 6,
                'report_date' => Carbon::today()->format('Y-m-d'),
                'shift_time' => '08:00-16:00',
                'weather_condition' => '晴れ',
                'temperature' => 24,
                'summary' => '新宿センタービルの日勤警備を実施。受付業務、来館者対応、巡回警備を適切に行った。VIP来館が1件あり、特別対応を実施した。エレベーター1台の一時停止があったが、迅速に対応した。',
                'activities' => json_encode([
                    [
                        'time' => '08:00',
                        'activity' => '勤務開始・受付業務',
                        'details' => '夜勤者からの引継ぎ、1階受付での来館者対応開始'
                    ],
                    [
                        'time' => '09:30',
                        'activity' => '館内巡回（1回目）',
                        'details' => '1-10階の巡回、異常なし'
                    ],
                    [
                        'time' => '10:30',
                        'activity' => 'VIP来館対応',
                        'details' => '外国人VIPの来館、通訳付き案内を実施'
                    ],
                    [
                        'time' => '12:00',
                        'activity' => '昼休憩・受付継続',
                        'details' => '休憩交代で受付業務継続'
                    ],
                    [
                        'time' => '14:15',
                        'activity' => 'エレベーター故障対応',
                        'details' => '3号機停止、保守会社連絡、利用者案内'
                    ],
                    [
                        'time' => '15:30',
                        'activity' => '館内巡回（2回目）',
                        'details' => '11-25階の巡回、清掃業者との連携'
                    ],
                    [
                        'time' => '16:00',
                        'activity' => '勤務終了・引継ぎ',
                        'details' => '夜勤者への業務引継ぎ、受付業務終了'
                    ]
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '14:15',
                        'type' => '設備故障',
                        'description' => 'エレベーター3号機が15階で停止',
                        'action_taken' => '保守会社への連絡、利用者への他機への案内、館内放送',
                        'injury' => 'なし',
                        'damage' => 'エレベーター1台使用不可'
                    ]
                ]),
                'observations' => json_encode([
                    '外国人来館者が増加傾向、英語対応の重要性',
                    'エレベーターの定期点検が必要な時期',
                    'テナント企業の機密書類の取扱いに注意',
                    '受付での荷物受取が増加している'
                ]),
                'equipment_used' => json_encode([
                    '防犯カメラ監視システム',
                    'インターホン',
                    '巡回記録システム',
                    'AED',
                    '館内放送設備'
                ]),
                'visitors' => json_encode([
                    [
                        'time' => '10:30',
                        'name' => 'Mr. Johnson',
                        'company' => 'ABC Corporation',
                        'purpose' => '12階テナント企業との商談'
                    ],
                    [
                        'time' => '13:20',
                        'name' => '配送業者 ヤマト運輸',
                        'company' => 'ヤマト運輸',
                        'purpose' => '各階への荷物配送'
                    ]
                ]),
                'recommendations' => json_encode([
                    'エレベーターの定期点検スケジュールを確認する',
                    '外国人対応のための英語表示を増やす',
                    'VIP来館時の特別対応マニュアルを見直す'
                ]),
                'next_shift_notes' => 'エレベーター3号機は修理中です。利用者への案内をお願いします。明日はテナント企業の重要会議があります。',
                'photos_attached' => false,
                'photo_count' => 0,
                'submitted_by' => 3, // 安全 三郎
                'reviewed_by' => 3, // 不動産 三郎
                'approved_by' => 3, // 不動産 三郎
                'status' => 'approved',
                'created_at' => Carbon::today()->addHours(8),
                'updated_at' => Carbon::today()->addHours(9)
            ],
            // ショッピングモール警備の日報
            [
                'report_number' => 'DR2024-004',
                'project_id' => 4, // 渋谷モール巡回・監視警備
                'guard_id' => 4, // 監視 四郎
                'shift_id' => 9,
                'report_date' => Carbon::today()->format('Y-m-d'),
                'shift_time' => '09:00-15:00',
                'weather_condition' => '晴れ',
                'temperature' => 26,
                'summary' => '渋谷モールの開店時間帯警備を実施。平日のため客足は比較的少なめだったが、迷子対応1件、万引き疑い1件に対応した。外国人観光客への案内も多数実施した。',
                'activities' => json_encode([
                    [
                        'time' => '09:00',
                        'activity' => '開店準備・館内確認',
                        'details' => '各フロアの安全確認、店舗開店準備確認'
                    ],
                    [
                        'time' => '10:00',
                        'activity' => '開店・巡回開始',
                        'details' => '1-8階の定期巡回開始、客層監視'
                    ],
                    [
                        'time' => '11:20',
                        'activity' => '迷子対応',
                        'details' => '5歳男児の迷子、館内放送で母親呼び出し'
                    ],
                    [
                        'time' => '12:30',
                        'activity' => '外国人観光客案内',
                        'details' => '中国人観光客グループへの館内案内（英語・中国語）'
                    ],
                    [
                        'time' => '13:45',
                        'activity' => '万引き疑い対応',
                        'details' => '若い女性の不審行動、店舗スタッフと連携し監視'
                    ],
                    [
                        'time' => '15:00',
                        'activity' => '勤務終了・引継ぎ',
                        'details' => '夕方シフトへの業務引継ぎ'
                    ]
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '11:20',
                        'type' => '迷子',
                        'description' => '5歳の男児が母親とはぐれる',
                        'action_taken' => '館内放送、保護、母親との再会',
                        'injury' => 'なし',
                        'damage' => 'なし'
                    ],
                    [
                        'time' => '13:45',
                        'type' => '万引き疑い',
                        'description' => '20代女性の行動が不審',
                        'action_taken' => '店舗スタッフと連携し監視、最終的に問題なし',
                        'injury' => 'なし',
                        'damage' => 'なし'
                    ]
                ]),
                'observations' => json_encode([
                    '平日は客足が少なく、警備しやすい環境',
                    '外国人観光客が増加傾向、多言語対応が重要',
                    '迷子の発生パターンを分析する必要あり',
                    '店舗スタッフとの連携が良好'
                ]),
                'equipment_used' => json_encode([
                    'ヘッドセット',
                    'ハンディカメラ',
                    '館内放送システム',
                    '応急処置キット',
                    '迷子対応セット'
                ]),
                'visitors' => json_encode([
                    [
                        'time' => '12:30',
                        'name' => '中国人観光客グループ（8名）',
                        'company' => '個人',
                        'purpose' => 'ショッピング・観光'
                    ]
                ]),
                'recommendations' => json_encode([
                    '迷子防止のための案内表示を増やす',
                    '外国人観光客向けの多言語案内を充実させる',
                    '万引き防止の監視カメラ配置を見直す'
                ]),
                'next_shift_notes' => '夕方シフトは客足が増えます。特に若い客層の監視を強化してください。迷子対応セットの場所を確認してください。',
                'photos_attached' => true,
                'photo_count' => 2,
                'submitted_by' => 4, // 監視 四郎
                'reviewed_by' => 4, // 商業 四郎
                'approved_by' => 4, // 商業 四郎
                'status' => 'approved',
                'created_at' => Carbon::today()->addHours(6),
                'updated_at' => Carbon::today()->addHours(7)
            ],
            // 製鉄工場警備の日報
            [
                'report_number' => 'DR2024-005',
                'project_id' => 7, // 東京製鉄工場24時間警備
                'guard_id' => 6, // 防衛 六郎
                'shift_id' => 11,
                'report_date' => Carbon::today()->format('Y-m-d'),
                'shift_time' => '06:00-12:00',
                'weather_condition' => '曇り',
                'temperature' => 20,
                'summary' => '東京製鉄工場の早朝シフト警備を実施。作業員の入場ラッシュ対応、消防設備定期点検立会い、VIP来客対応を行った。高温作業環境での安全管理を徹底した。',
                'activities' => json_encode([
                    [
                        'time' => '06:00',
                        'activity' => '早朝勤務開始・現場確認',
                        'details' => '深夜勤務者からの引継ぎ、工場設備の安全確認'
                    ],
                    [
                        'time' => '06:30',
                        'activity' => '作業員入場ラッシュ対応',
                        'details' => '身元確認、入場記録、安全装備チェック'
                    ],
                    [
                        'time' => '07:30',
                        'activity' => '消防設備点検立会い',
                        'details' => '第2工場の消火設備定期点検、異常なし'
                    ],
                    [
                        'time' => '09:00',
                        'activity' => '工場内巡回（1回目）',
                        'details' => '高温エリアの安全確認、ガス検知器による測定'
                    ],
                    [
                        'time' => '10:15',
                        'activity' => 'VIP来客対応',
                        'details' => '取引先重役の来訪、身元確認後工場長室へ案内'
                    ],
                    [
                        'time' => '11:30',
                        'activity' => '工場内巡回（2回目）',
                        'details' => '製品出荷エリアの確認、搬出作業立会い'
                    ],
                    [
                        'time' => '12:00',
                        'activity' => '勤務終了・引継ぎ',
                        'details' => '日勤Bシフトへの詳細引継ぎ'
                    ]
                ]),
                'incidents' => json_encode([]),
                'observations' => json_encode([
                    '作業員の安全意識が非常に高い',
                    '高温作業エリアでの熱中症対策が徹底されている',
                    '機密情報の管理が適切に行われている',
                    '消防設備の状態は良好'
                ]),
                'equipment_used' => json_encode([
                    'ガス検知器',
                    '防爆無線機',
                    '防毒マスク',
                    '生体認証システム',
                    '消火器'
                ]),
                'visitors' => json_encode([
                    [
                        'time' => '10:15',
                        'name' => '田中専務',
                        'company' => '○○重工業株式会社',
                        'purpose' => '工場長との重要会議'
                    ]
                ]),
                'recommendations' => json_encode([
                    '夏季の熱中症対策をさらに強化する',
                    'ガス検知器の定期校正を確実に実施する',
                    'VIP来客時の身元確認手順を再確認する'
                ]),
                'next_shift_notes' => '午後から製品出荷が活発になります。搬出エリアの安全管理を重点的にお願いします。',
                'photos_attached' => true,
                'photo_count' => 4,
                'submitted_by' => 6, // 防衛 六郎
                'reviewed_by' => 5, // 伊藤 六郎
                'approved_by' => 5, // 伊藤 六郎
                'status' => 'approved',
                'created_at' => Carbon::today()->addHours(6),
                'updated_at' => Carbon::today()->addHours(7)
            ],
            // 物流センター夜間警備の日報
            [
                'report_number' => 'DR2024-006',
                'project_id' => 8, // 関東ロジセンター夜間警備
                'guard_id' => 7, // 警戒 七郎
                'shift_id' => 15,
                'report_date' => Carbon::yesterday()->format('Y-m-d'),
                'shift_time' => '18:00-08:00',
                'weather_condition' => '曇り',
                'temperature' => 16,
                'summary' => '関東ロジセンターの夜間警備を実施。冷凍倉庫の温度異常が1回発生したが迅速に対応。深夜配送トラック5台の入退場管理を適切に行った。低温環境での長時間勤務を完了した。',
                'activities' => json_encode([
                    [
                        'time' => '18:00',
                        'activity' => '夜勤開始・現場確認',
                        'details' => '日勤者からの引継ぎ、冷凍倉庫温度確認'
                    ],
                    [
                        'time' => '19:30',
                        'activity' => '倉庫内巡回（1回目）',
                        'details' => '5棟すべての巡回、異常なし'
                    ],
                    [
                        'time' => '22:30',
                        'activity' => '冷凍設備異常対応',
                        'details' => 'A棟温度上昇、管理者連絡、継続監視開始'
                    ],
                    [
                        'time' => '00:00',
                        'activity' => '深夜休憩・監視継続',
                        'details' => '2時間休憩、温度監視システム確認'
                    ],
                    [
                        'time' => '03:45',
                        'activity' => '深夜配送対応',
                        'details' => 'トラック5台の入退場管理、荷物搬入立会い'
                    ],
                    [
                        'time' => '06:30',
                        'activity' => '倉庫内巡回（2回目）',
                        'details' => '最終巡回、冷凍設備正常復旧確認'
                    ],
                    [
                        'time' => '08:00',
                        'activity' => '勤務終了・引継ぎ',
                        'details' => '日勤者への詳細引継ぎ、冷凍設備状況報告'
                    ]
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '22:30',
                        'type' => 'equipment_malfunction',
                        'description' => '冷凍倉庫A棟の温度が-15℃から-12℃に上昇',
                        'action_taken' => '管理者への緊急連絡、温度記録、継続監視、保守会社連絡',
                        'injury' => 'なし',
                        'damage' => '冷凍商品への影響軽微'
                    ]
                ]),
                'observations' => json_encode([
                    '冷凍設備の定期メンテナンスが必要な時期',
                    '深夜配送の頻度が増加傾向',
                    '低温環境での作業時間管理が重要',
                    '配送業者との連携が良好'
                ]),
                'equipment_used' => json_encode([
                    '温度監視システム',
                    '防寒作業服',
                    'フォークリフト',
                    '台車',
                    '検品用具'
                ]),
                'visitors' => json_encode([
                    [
                        'time' => '03:45',
                        'name' => '配送業者（5社）',
                        'company' => '各運送会社',
                        'purpose' => '深夜配送・集荷'
                    ]
                ]),
                'recommendations' => json_encode([
                    '冷凍設備の予防保守を強化する',
                    '温度異常時の対応マニュアルを見直す',
                    '深夜配送の増加に対応した体制を検討する'
                ]),
                'next_shift_notes' => '冷凍倉庫A棟の温度は正常に復旧しましたが、念のため重点監視をお願いします。',
                'photos_attached' => true,
                'photo_count' => 3,
                'submitted_by' => 7, // 警戒 七郎
                'reviewed_by' => 6, // 物流 八郎
                'approved_by' => 5, // 伊藤 六郎
                'status' => 'approved',
                'created_at' => Carbon::yesterday()->addHours(8),
                'updated_at' => Carbon::yesterday()->addHours(9)
            ],
            // 高級住宅警備の日報
            [
                'report_number' => 'DR2024-007',
                'project_id' => 10, // 田園調布高級住宅警備
                'guard_id' => 8, // 保安 花子
                'shift_id' => null,
                'report_date' => Carbon::yesterday()->format('Y-m-d'),
                'shift_time' => '09:00-18:00',
                'weather_condition' => '晴れ',
                'temperature' => 25,
                'summary' => '田園調布邸宅の日中警備を実施。来訪者管理、敷地内巡回を適切に行った。報道関係者1名への対応を実施。近隣住民への配慮を徹底し、プライバシー保護を最優先に業務を行った。',
                'activities' => json_encode([
                    [
                        'time' => '09:00',
                        'activity' => '勤務開始・敷地確認',
                        'details' => '敷地内の安全確認、前日の状況確認'
                    ],
                    [
                        'time' => '10:30',
                        'activity' => '来訪者対応',
                        'details' => '宅配業者への対応、荷物受取'
                    ],
                    [
                        'time' => '12:00',
                        'activity' => '昼休憩・敷地巡回',
                        'details' => '敷地境界の確認、防犯カメラ確認'
                    ],
                    [
                        'time' => '14:00',
                        'activity' => '報道関係者対応',
                        'details' => '新聞記者1名への適切な対応、退去要請'
                    ],
                    [
                        'time' => '16:30',
                        'activity' => '近隣挨拶回り',
                        'details' => '隣接住宅への定期挨拶、関係維持'
                    ],
                    [
                        'time' => '18:00',
                        'activity' => '勤務終了・報告',
                        'details' => '1日の業務報告、セキュリティシステム確認'
                    ]
                ]),
                'incidents' => json_encode([
                    [
                        'time' => '14:00',
                        'type' => 'media_contact',
                        'description' => '新聞記者が取材のため訪問',
                        'action_taken' => '丁寧な対応で取材をお断り、退去を要請',
                        'injury' => 'なし',
                        'damage' => 'なし'
                    ]
                ]),
                'observations' => json_encode([
                    '近隣住民との関係は良好',
                    'プライバシー保護が最重要課題',
                    '報道関係者の訪問が時々ある',
                    '高級住宅地での控えめな警備が効果的'
                ]),
                'equipment_used' => json_encode([
                    '携帯電話',
                    'インターホン',
                    '防犯カメラ4台',
                    '来客記録システム',
                    '懐中電灯'
                ]),
                'visitors' => json_encode([
                    [
                        'time' => '10:30',
                        'name' => '宅配業者',
                        'company' => 'ヤマト運輸',
                        'purpose' => '荷物配達'
                    ],
                    [
                        'time' => '14:00',
                        'name' => '記者',
                        'company' => '○○新聞社',
                        'purpose' => '取材（お断り）'
                    ]
                ]),
                'recommendations' => json_encode([
                    '報道関係者対応マニュアルを再確認する',
                    '近隣住民との良好な関係を継続する',
                    'プライバシー保護措置をさらに強化する'
                ]),
                'next_shift_notes' => '報道関係者の訪問があります。適切な対応をお願いします。近隣への配慮を忘れずに。',
                'photos_attached' => false,
                'photo_count' => 0,
                'submitted_by' => 8, // 保安 花子
                'reviewed_by' => 1, // 田中 太郎
                'approved_by' => 1, // 田中 太郎
                'status' => 'approved',
                'created_at' => Carbon::yesterday()->addHours(9),
                'updated_at' => Carbon::yesterday()->addHours(10)
            ]
        ];

        // データを挿入
        DB::table('daily_reports')->insert($dailyReports);

        echo "Daily Reports seeder completed. " . count($dailyReports) . " daily reports created.\n";
    }
}
