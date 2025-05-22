<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 見積書テーブルのダミーデータ生成
 * 
 * 警備サービスの見積書情報を生成します。
 * 建設現場、施設警備、イベント警備等の多様な見積書を想定。
 */
class QuotationsSeeder extends Seeder
{
    /**
     * ダミーデータを生成して挿入
     *
     * @return void
     */
    public function run(): void
    {
        $quotations = [
            // 建設現場警備の見積書
            [
                'quotation_number' => 'Q2024-001',
                'customer_id' => 1, // 東京建設株式会社
                'project_id' => 1, // 新宿駅前再開発ビル建設現場警備
                'quotation_date' => Carbon::now()->subMonths(4)->format('Y-m-d'),
                'valid_until' => Carbon::now()->subMonths(4)->addDays(30)->format('Y-m-d'),
                'total_amount' => 12000000,
                'tax_amount' => 1200000,
                'subtotal' => 10800000,
                'items' => json_encode([
                    [
                        'item_name' => '建設現場24時間警備',
                        'description' => '新宿駅前再開発現場における24時間警備業務',
                        'unit_price' => 800000,
                        'quantity' => 15,
                        'unit' => '月',
                        'amount' => 12000000,
                        'details' => [
                            '警備員4名体制（24時間3交代）',
                            '交通誘導業務',
                            '資材盗難防止',
                            '作業員入退場管理',
                            '緊急時対応'
                        ]
                    ]
                ]),
                'notes' => '工事期間：15ヶ月予定\n警備員は全員建設業関連資格保有者を配置\n天候不良時の対応も含む\n月次報告書作成',
                'terms_conditions' => '支払条件：月末締め翌月末払い\n契約期間：工事開始から完成まで\n警備員の交代は事前に相談\n緊急時は24時間対応',
                'prepared_by' => 2, // 佐藤 次郎（営業部課長）
                'approved_by' => 1, // 田中 太郎（部長）
                'status' => 'accepted',
                'response_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(4),
                'updated_at' => Carbon::now()->subMonths(3)
            ],
            [
                'quotation_number' => 'Q2024-002',
                'customer_id' => 2, // 関東土木工業株式会社
                'project_id' => 2, // 環状7号線道路工事夜間警備
                'quotation_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'valid_until' => Carbon::now()->subMonths(2)->addDays(14)->format('Y-m-d'),
                'total_amount' => 6600000,
                'tax_amount' => 600000,
                'subtotal' => 6000000,
                'items' => json_encode([
                    [
                        'item_name' => '道路工事夜間警備',
                        'description' => '環状7号線道路工事における夜間交通誘導警備',
                        'unit_price' => 1200000,
                        'quantity' => 5,
                        'unit' => '月',
                        'amount' => 6000000,
                        'details' => [
                            '警備員6名体制（夜間21:00-6:00）',
                            '交通誘導業務（延長2km）',
                            '工事車両誘導',
                            '緊急時対応',
                            '警察・消防との連携'
                        ]
                    ]
                ]),
                'notes' => '工事期間：5ヶ月予定\n雨天時は工事中止の可能性あり\n交通誘導警備業務検定1級保有者のみ配置\n夜間の危険手当含む',
                'terms_conditions' => '支払条件：月末締め翌月末払い\n天候による工事中止日は日割り精算\n警備員の緊急時交代対応\n工事スケジュール変更時は要相談',
                'prepared_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'status' => 'accepted',
                'response_date' => Carbon::now()->subMonths(1)->addDays(10)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(2),
                'updated_at' => Carbon::now()->subMonths(1)
            ],
            // 施設警備の見積書
            [
                'quotation_number' => 'Q2024-003',
                'customer_id' => 3, // 新宿プロパティーズ株式会社
                'project_id' => 3, // 新宿センタービル24時間警備
                'quotation_date' => Carbon::now()->subMonths(7)->format('Y-m-d'),
                'valid_until' => Carbon::now()->subMonths(7)->addDays(30)->format('Y-m-d'),
                'total_amount' => 26400000,
                'tax_amount' => 2400000,
                'subtotal' => 24000000,
                'items' => json_encode([
                    [
                        'item_name' => 'オフィスビル24時間警備',
                        'description' => '新宿センタービルにおける24時間常駐警備業務',
                        'unit_price' => 1000000,
                        'quantity' => 24,
                        'unit' => '月',
                        'amount' => 24000000,
                        'details' => [
                            '警備員3名体制（24時間3交代）',
                            '受付業務',
                            '巡回警備（全25階）',
                            '入退館管理',
                            '防災センター業務',
                            '緊急時対応'
                        ]
                    ]
                ]),
                'notes' => '契約期間：2年間（24ヶ月）\n警備員は施設警備業務検定1級・防災センター要員資格保有者\n月次・年次報告書作成\nVIP来館時の特別対応',
                'terms_conditions' => '支払条件：月末締め翌月25日払い\n2年契約（自動更新あり）\n警備員の制服・設備は当社負担\n緊急時24時間体制',
                'prepared_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'status' => 'accepted',
                'response_date' => Carbon::now()->subMonths(6)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(7),
                'updated_at' => Carbon::now()->subMonths(6)
            ],
            [
                'quotation_number' => 'Q2024-004',
                'customer_id' => 4, // 渋谷ショッピングモール株式会社
                'project_id' => 4, // 渋谷モール巡回・監視警備
                'quotation_date' => Carbon::now()->subMonths(9)->format('Y-m-d'),
                'valid_until' => Carbon::now()->subMonths(9)->addDays(21)->format('Y-m-d'),
                'total_amount' => 19800000,
                'tax_amount' => 1800000,
                'subtotal' => 18000000,
                'items' => json_encode([
                    [
                        'item_name' => 'ショッピングモール警備',
                        'description' => '渋谷モールにおける開館時間中の巡回・監視警備',
                        'unit_price' => 750000,
                        'quantity' => 24,
                        'unit' => '月',
                        'amount' => 18000000,
                        'details' => [
                            '警備員5名体制（10:00-22:00）',
                            'フロア巡回警備',
                            '万引き防止監視',
                            '迷子・急病人対応',
                            '外国人観光客対応',
                            '閉店時施錠確認'
                        ]
                    ]
                ]),
                'notes' => '契約期間：2年間（24ヶ月）\n土日祝日・セール期間は警備強化\n多言語対応可能な警備員を配置\n制服はモール指定デザイン',
                'terms_conditions' => '支払条件：月末締め翌月20日払い\n2年契約（1年毎見直し）\n繁忙期の警備員増員は別途相談\n年末年始・GW等の特別対応',
                'prepared_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'status' => 'accepted',
                'response_date' => Carbon::now()->subMonths(8)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(9),
                'updated_at' => Carbon::now()->subMonths(8)
            ],
            // イベント警備の見積書
            [
                'quotation_number' => 'Q2024-005',
                'customer_id' => 5, // 東京イベントプロダクション株式会社
                'project_id' => 5, // 東京ドーム年末コンサート警備
                'quotation_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'valid_until' => Carbon::now()->subMonths(1)->addDays(14)->format('Y-m-d'),
                'total_amount' => 3300000,
                'tax_amount' => 300000,
                'subtotal' => 3000000,
                'items' => json_encode([
                    [
                        'item_name' => '大型コンサート警備',
                        'description' => '東京ドーム年末コンサートにおける会場警備業務',
                        'unit_price' => 3000000,
                        'quantity' => 1,
                        'unit' => '式',
                        'amount' => 3000000,
                        'details' => [
                            '警備員50名体制（3日間）',
                            '入場管理・観客誘導',
                            'ステージ周辺警備',
                            '緊急時避難誘導',
                            '著名人警護',
                            '報道陣対応'
                        ]
                    ]
                ]),
                'notes' => '開催期間：3日間\n雑踏警備業務検定1級保有者を中心に配置\n著名アーティストのため最高レベルの警備\n警察・消防との密接な連携',
                'terms_conditions' => '支払条件：イベント終了後30日以内\n天候・災害によるイベント中止時は50%支払い\n警備員の宿泊・食事は別途\n緊急時の警備員増員は要相談',
                'prepared_by' => 4, // 鈴木 五郎（Nikkei営業部課長）
                'approved_by' => 3, // 山田 四郎（Nikkei部長）
                'status' => 'pending',
                'response_date' => null,
                'created_at' => Carbon::now()->subMonths(1),
                'updated_at' => Carbon::now()->subMonths(1)
            ],
            [
                'quotation_number' => 'Q2024-006',
                'customer_id' => 6, // 株式会社フェスティバル企画
                'project_id' => 6, // お台場音楽フェスティバル警備
                'quotation_date' => Carbon::now()->subWeeks(2)->format('Y-m-d'),
                'valid_until' => Carbon::now()->subWeeks(2)->addDays(21)->format('Y-m-d'),
                'total_amount' => 2750000,
                'tax_amount' => 250000,
                'subtotal' => 2500000,
                'items' => json_encode([
                    [
                        'item_name' => '音楽フェスティバル警備',
                        'description' => 'お台場音楽フェスティバルにおける屋外イベント警備',
                        'unit_price' => 2500000,
                        'quantity' => 1,
                        'unit' => '式',
                        'amount' => 2500000,
                        'details' => [
                            '警備員30名体制（6日間）',
                            '設営・撤収期間の警備',
                            '観客エリア警備',
                            'ステージ周辺警備',
                            '駐車場管理',
                            '酔客対応'
                        ]
                    ]
                ]),
                'notes' => '開催期間：6日間（設営2日+開催3日+撤収1日）\n屋外イベントのため天候対応重要\nアルコール販売ありのため酔客対応必須\n環境配慮でゴミ分別指導も実施',
                'terms_conditions' => '支払条件：イベント終了後30日以内\n雨天決行・荒天中止\n警備員の屋外装備は当社負担\n緊急時の医療対応連携',
                'prepared_by' => 4, // 鈴木 五郎
                'approved_by' => 3, // 山田 四郎
                'status' => 'pending',
                'response_date' => null,
                'created_at' => Carbon::now()->subWeeks(2),
                'updated_at' => Carbon::now()->subWeeks(2)
            ],
            // 工場・産業警備の見積書
            [
                'quotation_number' => 'Q2024-007',
                'customer_id' => 7, // 東京製鉄株式会社
                'project_id' => 7, // 東京製鉄工場24時間警備
                'quotation_date' => Carbon::now()->subMonths(15)->format('Y-m-d'),
                'valid_until' => Carbon::now()->subMonths(15)->addDays(30)->format('Y-m-d'),
                'total_amount' => 39600000,
                'tax_amount' => 3600000,
                'subtotal' => 36000000,
                'items' => json_encode([
                    [
                        'item_name' => '製鉄工場24時間警備',
                        'description' => '東京製鉄工場における24時間警備業務',
                        'unit_price' => 1500000,
                        'quantity' => 24,
                        'unit' => '月',
                        'amount' => 36000000,
                        'details' => [
                            '警備員8名体制（24時間4交代）',
                            '入退場管理',
                            '工場内巡回警備',
                            '火災予防・設備監視',
                            '産業スパイ防止',
                            '危険物取扱い立会い'
                        ]
                    ]
                ]),
                'notes' => '契約期間：2年間（24ヶ月）\n危険物取扱者資格保有者のみ配置\n高温・有害ガス環境での作業\n機密情報保持誓約書必須\n月1回安全講習受講義務',
                'terms_conditions' => '支払条件：月末締め翌月15日払い\n2年契約（自動更新あり）\n警備員の身元調査必須\n特殊環境手当含む\n24時間緊急対応体制',
                'prepared_by' => 6, // 渡辺 七郎（全日本営業部課長）
                'approved_by' => 5, // 伊藤 六郎（全日本部長）
                'status' => 'accepted',
                'response_date' => Carbon::now()->subMonths(14)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(15),
                'updated_at' => Carbon::now()->subMonths(14)
            ],
            [
                'quotation_number' => 'Q2024-008',
                'customer_id' => 8, // 関東ロジスティクス株式会社
                'project_id' => 8, // 関東ロジセンター夜間警備
                'quotation_date' => Carbon::now()->subMonths(6)->format('Y-m-d'),
                'valid_until' => Carbon::now()->subMonths(6)->addDays(21)->format('Y-m-d'),
                'total_amount' => 16500000,
                'tax_amount' => 1500000,
                'subtotal' => 15000000,
                'items' => json_encode([
                    [
                        'item_name' => '物流センター夜間警備',
                        'description' => '関東ロジセンターにおける夜間警備業務',
                        'unit_price' => 625000,
                        'quantity' => 24,
                        'unit' => '月',
                        'amount' => 15000000,
                        'details' => [
                            '警備員4名体制（18:00-8:00）',
                            '夜間配送トラック管理',
                            '倉庫内巡回警備',
                            '冷凍倉庫監視',
                            '荷物盗難防止',
                            '温度管理監視'
                        ]
                    ]
                ]),
                'notes' => '契約期間：2年間（24ヶ月）\nフォークリフト運転技能講習修了者配置\n冷凍環境での作業（-20℃）\n夜間配送業務の立会い\n防寒装備支給',
                'terms_conditions' => '支払条件：月末締め翌月末払い\n2年契約（1年毎見直し）\n低温環境手当含む\n夜間勤務手当含む\n緊急時対応体制',
                'prepared_by' => 6, // 渡辺 七郎
                'approved_by' => 5, // 伊藤 六郎
                'status' => 'accepted',
                'response_date' => Carbon::now()->subMonths(5)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(6),
                'updated_at' => Carbon::now()->subMonths(5)
            ],
            // 個人向けサービスの見積書
            [
                'quotation_number' => 'Q2024-009',
                'customer_id' => 10, // 個人事業主 田園調布太郎
                'project_id' => 10, // 田園調布高級住宅警備
                'quotation_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'valid_until' => Carbon::now()->subMonths(2)->addDays(14)->format('Y-m-d'),
                'total_amount' => 6600000,
                'tax_amount' => 600000,
                'subtotal' => 6000000,
                'items' => json_encode([
                    [
                        'item_name' => '高級住宅警備',
                        'description' => '田園調布邸宅における日中常駐警備業務',
                        'unit_price' => 500000,
                        'quantity' => 12,
                        'unit' => '月',
                        'amount' => 6000000,
                        'details' => [
                            '警備員1名体制（9:00-18:00）',
                            '来訪者管理',
                            '敷地内巡回警備',
                            'プライバシー保護',
                            '高級住宅地対応',
                            '機密保持'
                        ]
                    ]
                ]),
                'notes' => '契約期間：1年間（12ヶ月）\n高級住宅地での特別対応\n近隣への配慮必須\n報道関係者対応\n家族のプライバシー最優先',
                'terms_conditions' => '支払条件：月末締め翌月末払い\n1年契約（自動更新あり）\n機密保持誓約書必須\n控えめな警備表示\n緊急時24時間対応',
                'prepared_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'status' => 'accepted',
                'response_date' => Carbon::now()->subMonths(1)->addDays(5)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(2),
                'updated_at' => Carbon::now()->subMonths(1)
            ],
            // 却下・失注の見積書例
            [
                'quotation_number' => 'Q2024-010',
                'customer_id' => 9, // 旧式商事株式会社（非アクティブ顧客）
                'project_id' => null,
                'quotation_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'valid_until' => Carbon::now()->subMonths(3)->addDays(30)->format('Y-m-d'),
                'total_amount' => 3300000,
                'tax_amount' => 300000,
                'subtotal' => 3000000,
                'items' => json_encode([
                    [
                        'item_name' => '倉庫・事務所警備',
                        'description' => '旧式商事倉庫および事務所における夜間警備',
                        'unit_price' => 250000,
                        'quantity' => 12,
                        'unit' => '月',
                        'amount' => 3000000,
                        'details' => [
                            '警備員1名体制（20:00-6:00）',
                            '倉庫巡回警備',
                            '事務所警備',
                            '盗難防止',
                            '火災予防'
                        ]
                    ]
                ]),
                'notes' => '契約期間：1年間予定\n夜間のみの警備\n予算の制約があるため最小限の警備体制\n必要に応じて警備員増員可能',
                'terms_conditions' => '支払条件：月末締め翌月末払い\n1年契約\n警備員の交代は1週間前までに連絡\n緊急時対応',
                'prepared_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'status' => 'rejected',
                'response_date' => Carbon::now()->subMonths(2)->addDays(15)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(3),
                'updated_at' => Carbon::now()->subMonths(2)
            ]
        ];

        // データを挿入
        DB::table('quotations')->insert($quotations);

        echo "Quotations seeder completed. " . count($quotations) . " quotations created.\n";
    }
}
