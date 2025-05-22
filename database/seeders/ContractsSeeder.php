<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 契約テーブルのダミーデータ生成
 * 
 * 警備サービスの契約情報を生成します。
 * 見積書から正式契約に至った案件の契約書データを想定。
 */
class ContractsSeeder extends Seeder
{
    /**
     * ダミーデータを生成して挿入
     *
     * @return void
     */
    public function run(): void
    {
        $contracts = [
            // 建設現場警備契約
            [
                'contract_number' => 'C2024-001',
                'quotation_id' => 1, // Q2024-001
                'customer_id' => 1, // 東京建設株式会社
                'project_id' => 1, // 新宿駅前再開発ビル建設現場警備
                'contract_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'start_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(12)->format('Y-m-d'),
                'contract_amount' => 12000000,
                'payment_terms' => '月末締め翌月末払い',
                'service_details' => json_encode([
                    'service_type' => '建設現場24時間警備',
                    'location' => '東京都新宿区西新宿1-15-20',
                    'guard_count' => 4,
                    'shift_pattern' => '24時間3交代制',
                    'services' => [
                        '交通誘導業務',
                        '資材盗難防止',
                        '作業員入退場管理',
                        '緊急時対応',
                        '月次報告書作成'
                    ],
                    'special_requirements' => [
                        '警備員検定2級以上',
                        '建設業関連講習修了',
                        '交通誘導経験必須'
                    ]
                ]),
                'terms_conditions' => '1. 警備員の資格要件を満たすこと\n2. 天候不良時でも警備継続\n3. 緊急時は24時間体制で対応\n4. 月次報告書を翌月5日までに提出\n5. 契約期間中の警備員変更は事前相談',
                'renewal_conditions' => '工事期間延長時は契約自動延長\n料金は同条件を維持\n大幅な工事内容変更時は再協議',
                'cancellation_terms' => '30日前の事前通知により解約可能\n工事中止の場合は即時解約可能\n解約時の精算は日割り計算',
                'penalty_clauses' => json_encode([
                    'service_failure' => '警備不備による事故時は損害賠償責任',
                    'guard_absence' => '無断欠勤時は当日料金の50%減額',
                    'report_delay' => '報告書提出遅延時は警告後減額対象'
                ]),
                'insurance_coverage' => json_encode([
                    'liability_insurance' => '1億円',
                    'accident_insurance' => '警備員1名につき3000万円',
                    'property_insurance' => '5000万円'
                ]),
                'signed_by_customer' => '建設 太郎（工事部長）',
                'signed_by_company' => '田中 太郎（部長）',
                'status' => 'active',
                'next_review_date' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(3),
                'updated_at' => Carbon::now()->subMonths(3)
            ],
            [
                'contract_number' => 'C2024-002',
                'quotation_id' => 2, // Q2024-002
                'customer_id' => 2, // 関東土木工業株式会社
                'project_id' => 2, // 環状7号線道路工事夜間警備
                'contract_date' => Carbon::now()->subMonths(1)->addDays(10)->format('Y-m-d'),
                'start_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(4)->format('Y-m-d'),
                'contract_amount' => 6000000,
                'payment_terms' => '月末締め翌月末払い',
                'service_details' => json_encode([
                    'service_type' => '道路工事夜間警備',
                    'location' => '東京都墨田区環状7号線（延長2km）',
                    'guard_count' => 6,
                    'shift_pattern' => '夜間のみ（21:00-6:00）',
                    'services' => [
                        '交通誘導業務',
                        '工事車両誘導',
                        '緊急時対応',
                        '警察・消防との連携',
                        '工事進捗報告'
                    ],
                    'special_requirements' => [
                        '交通誘導警備業務検定1級',
                        '夜間工事経験',
                        '大型車両誘導経験'
                    ]
                ]),
                'terms_conditions' => '1. 夜間の危険作業のため最高レベルの注意\n2. 雨天時は工事中止の可能性あり\n3. 警察との事前協議必須\n4. 緊急時は工事中止権限を持つ\n5. 交通渋滞発生時は即座に対応',
                'renewal_conditions' => '工事期間短縮時は契約短縮\n天候による工事延期時は契約延長\n料金変更なし',
                'cancellation_terms' => '工事中止時は即時解約可能\n天候による工事中止日は日割り精算\n15日前通知で解約可能',
                'penalty_clauses' => json_encode([
                    'traffic_accident' => '交通事故発生時は重大な責任問題',
                    'police_violation' => '交通規制違反時は契約解除',
                    'safety_violation' => '安全基準違反時は即時停止'
                ]),
                'insurance_coverage' => json_encode([
                    'traffic_insurance' => '2億円',
                    'accident_insurance' => '警備員1名につき5000万円',
                    'third_party_insurance' => '1億円'
                ]),
                'signed_by_customer' => '土木 次郎（現場監督）',
                'signed_by_company' => '田中 太郎（部長）',
                'status' => 'active',
                'next_review_date' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(1)->addDays(10),
                'updated_at' => Carbon::now()->subMonths(1)->addDays(10)
            ],
            // 施設警備契約
            [
                'contract_number' => 'C2024-003',
                'quotation_id' => 3, // Q2024-003
                'customer_id' => 3, // 新宿プロパティーズ株式会社
                'project_id' => 3, // 新宿センタービル24時間警備
                'contract_date' => Carbon::now()->subMonths(6)->format('Y-m-d'),
                'start_date' => Carbon::now()->subMonths(6)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(18)->format('Y-m-d'),
                'contract_amount' => 24000000,
                'payment_terms' => '月末締め翌月25日払い',
                'service_details' => json_encode([
                    'service_type' => 'オフィスビル24時間警備',
                    'location' => '東京都新宿区新宿3-3-3 新宿センタービル',
                    'guard_count' => 3,
                    'shift_pattern' => '24時間3交代制（8時間×3シフト）',
                    'services' => [
                        '受付業務',
                        '巡回警備（全25階）',
                        '入退館管理',
                        '防災センター業務',
                        'VIP来館時特別対応',
                        '緊急時対応'
                    ],
                    'special_requirements' => [
                        '施設警備業務検定1級',
                        '防災センター要員',
                        '接客対応能力',
                        'PC操作能力'
                    ]
                ]),
                'terms_conditions' => '1. 24時間365日の継続警備\n2. VIP来館時は特別プロトコル適用\n3. テナント企業の機密保持厳守\n4. 月1回避難訓練実施\n5. 年次防災計画策定',
                'renewal_conditions' => '2年契約（自動更新あり）\n1年毎に料金見直し\nサービス内容は継続',
                'cancellation_terms' => '6ヶ月前の事前通知により解約可能\nテナント退去等による解約は3ヶ月前通知\n解約時は引継ぎ期間1ヶ月',
                'penalty_clauses' => json_encode([
                    'security_breach' => '重大なセキュリティ事故時は損害賠償',
                    'confidentiality_breach' => '機密情報漏洩時は契約解除',
                    'service_interruption' => 'サービス中断時は日割り減額'
                ]),
                'insurance_coverage' => json_encode([
                    'building_insurance' => '10億円',
                    'liability_insurance' => '3億円',
                    'cyber_insurance' => '1億円'
                ]),
                'signed_by_customer' => '不動産 三郎（管理部課長）',
                'signed_by_company' => '田中 太郎（部長）',
                'status' => 'active',
                'next_review_date' => Carbon::now()->addMonths(12)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(6),
                'updated_at' => Carbon::now()->subMonths(6)
            ],
            [
                'contract_number' => 'C2024-004',
                'quotation_id' => 4, // Q2024-004
                'customer_id' => 4, // 渋谷ショッピングモール株式会社
                'project_id' => 4, // 渋谷モール巡回・監視警備
                'contract_date' => Carbon::now()->subMonths(8)->format('Y-m-d'),
                'start_date' => Carbon::now()->subMonths(8)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(16)->format('Y-m-d'),
                'contract_amount' => 18000000,
                'payment_terms' => '月末締め翌月20日払い',
                'service_details' => json_encode([
                    'service_type' => 'ショッピングモール警備',
                    'location' => '東京都渋谷区道玄坂4-4-4 渋谷ショッピングモール',
                    'guard_count' => 5,
                    'shift_pattern' => '開館時間中（10:00-22:00）',
                    'services' => [
                        'フロア巡回警備',
                        '万引き防止監視',
                        '迷子・急病人対応',
                        '外国人観光客対応',
                        '閉店時施錠確認',
                        'イベント時警備強化'
                    ],
                    'special_requirements' => [
                        '雑踏警備業務検定2級',
                        '接客対応能力',
                        '多言語対応（英語・中国語）',
                        '店舗管理知識'
                    ]
                ]),
                'terms_conditions' => '1. 土日祝日・セール期間は警備強化\n2. 外国人観光客への適切な対応\n3. 万引き犯への適切な対処\n4. 年末年始・GW等特別対応\n5. モール指定制服着用',
                'renewal_conditions' => '2年契約（1年毎見直し）\n繁忙期の警備員増員は別途協議\n料金は年次見直し',
                'cancellation_terms' => '6ヶ月前の事前通知により解約可能\nモール改装等による一時停止あり\n解約時は制服等返却',
                'penalty_clauses' => json_encode([
                    'shoplifting_miss' => '万引き見逃し時は再発防止策提出',
                    'customer_complaint' => '重大なクレーム時は改善計画提出',
                    'uniform_violation' => '制服規定違反時は注意・指導'
                ]),
                'insurance_coverage' => json_encode([
                    'public_liability' => '2億円',
                    'product_liability' => '1億円',
                    'employee_insurance' => '警備員1名につき3000万円'
                ]),
                'signed_by_customer' => '商業 四郎（テナント管理部長）',
                'signed_by_company' => '田中 太郎（部長）',
                'status' => 'active',
                'next_review_date' => Carbon::now()->addMonths(8)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(8),
                'updated_at' => Carbon::now()->subMonths(8)
            ],
            // 工場警備契約
            [
                'contract_number' => 'C2024-005',
                'quotation_id' => 7, // Q2024-007
                'customer_id' => 7, // 東京製鉄株式会社
                'project_id' => 7, // 東京製鉄工場24時間警備
                'contract_date' => Carbon::now()->subMonths(14)->format('Y-m-d'),
                'start_date' => Carbon::now()->subMonths(12)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(12)->format('Y-m-d'),
                'contract_amount' => 36000000,
                'payment_terms' => '月末締め翌月15日払い',
                'service_details' => json_encode([
                    'service_type' => '製鉄工場24時間警備',
                    'location' => '東京都足立区綾瀬7-7-7 東京製鉄工場',
                    'guard_count' => 8,
                    'shift_pattern' => '24時間4交代制（6時間×4シフト）',
                    'services' => [
                        '入退場管理',
                        '工場内巡回警備',
                        '火災予防・設備監視',
                        '産業スパイ防止',
                        '危険物取扱い立会い',
                        '緊急時対応'
                    ],
                    'special_requirements' => [
                        '施設警備業務検定1級',
                        '危険物取扱者乙種',
                        '工場警備経験5年以上',
                        '機密保持誓約書',
                        '身元調査クリア'
                    ]
                ]),
                'terms_conditions' => '1. 最高レベルのセキュリティクリアランス必須\n2. 高温・有害ガス環境での作業\n3. 機密情報の絶対的保護\n4. 月1回安全講習受講義務\n5. 24時間緊急対応体制',
                'renewal_conditions' => '2年契約（自動更新あり）\n警備員の再身元調査定期実施\n料金は2年毎見直し',
                'cancellation_terms' => '1年前の事前通知により解約可能\n機密保持義務は契約終了後も継続\n設備返却・引継ぎ期間3ヶ月',
                'penalty_clauses' => json_encode([
                    'security_breach' => '機密漏洩時は損害賠償および契約解除',
                    'safety_violation' => '安全基準違反時は即時業務停止',
                    'industrial_espionage' => '産業スパイ行為時は刑事告発'
                ]),
                'insurance_coverage' => json_encode([
                    'industrial_insurance' => '50億円',
                    'environmental_insurance' => '10億円',
                    'cyber_security_insurance' => '5億円'
                ]),
                'signed_by_customer' => '製鉄 七郎（工場長）',
                'signed_by_company' => '伊藤 六郎（部長）',
                'status' => 'active',
                'next_review_date' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(14),
                'updated_at' => Carbon::now()->subMonths(14)
            ],
            [
                'contract_number' => 'C2024-006',
                'quotation_id' => 8, // Q2024-008
                'customer_id' => 8, // 関東ロジスティクス株式会社
                'project_id' => 8, // 関東ロジセンター夜間警備
                'contract_date' => Carbon::now()->subMonths(5)->format('Y-m-d'),
                'start_date' => Carbon::now()->subMonths(5)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(19)->format('Y-m-d'),
                'contract_amount' => 15000000,
                'payment_terms' => '月末締め翌月末払い',
                'service_details' => json_encode([
                    'service_type' => '物流センター夜間警備',
                    'location' => '東京都江戸川区西葛西8-8-8 関東ロジスティクスセンター',
                    'guard_count' => 4,
                    'shift_pattern' => '夜間のみ（18:00-8:00）',
                    'services' => [
                        '夜間配送トラック管理',
                        '倉庫内巡回警備',
                        '冷凍倉庫監視',
                        '荷物盗難防止',
                        '温度管理監視',
                        '緊急時対応'
                    ],
                    'special_requirements' => [
                        '施設警備業務検定2級',
                        'フォークリフト運転技能',
                        '物流業務経験',
                        '低温環境作業経験'
                    ]
                ]),
                'terms_conditions' => '1. 冷凍環境（-20℃）での作業\n2. 夜間配送業務の立会い\n3. 温度管理の厳重監視\n4. 盗難防止の徹底\n5. 防寒装備の適切な使用',
                'renewal_conditions' => '2年契約（1年毎見直し）\n物流量変動時は警備員数調整\n低温環境手当継続',
                'cancellation_terms' => '3ヶ月前の事前通知により解約可能\n冷凍設備停止時は一時中断\n解約時は防寒装備返却',
                'penalty_clauses' => json_encode([
                    'temperature_management' => '温度管理不備時は損害賠償',
                    'theft_incident' => '盗難発生時は再発防止策提出',
                    'equipment_damage' => '設備損傷時は修理費負担'
                ]),
                'insurance_coverage' => json_encode([
                    'cold_storage_insurance' => '5億円',
                    'cargo_insurance' => '3億円',
                    'equipment_insurance' => '1億円'
                ]),
                'signed_by_customer' => '物流 八郎（倉庫管理部長）',
                'signed_by_company' => '伊藤 六郎（部長）',
                'status' => 'active',
                'next_review_date' => Carbon::now()->addMonths(7)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(5),
                'updated_at' => Carbon::now()->subMonths(5)
            ],
            // 個人向けサービス契約
            [
                'contract_number' => 'C2024-007',
                'quotation_id' => 9, // Q2024-009
                'customer_id' => 10, // 個人事業主 田園調布太郎
                'project_id' => 10, // 田園調布高級住宅警備
                'contract_date' => Carbon::now()->subMonths(1)->addDays(5)->format('Y-m-d'),
                'start_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(11)->format('Y-m-d'),
                'contract_amount' => 6000000,
                'payment_terms' => '月末締め翌月末払い',
                'service_details' => json_encode([
                    'service_type' => '高級住宅警備',
                    'location' => '東京都大田区田園調布10-10-10',
                    'guard_count' => 1,
                    'shift_pattern' => '日中のみ（9:00-18:00）',
                    'services' => [
                        '来訪者管理',
                        '敷地内巡回警備',
                        'プライバシー保護',
                        '報道関係者対応',
                        '緊急時対応',
                        '近隣配慮'
                    ],
                    'special_requirements' => [
                        '施設警備業務検定2級',
                        '高級住宅地対応経験',
                        '接客マナー',
                        '機密保持能力',
                        '控えめな対応'
                    ]
                ]),
                'terms_conditions' => '1. 家族のプライバシー最優先\n2. 近隣住民への配慮必須\n3. 報道関係者への適切な対応\n4. 機密保持の絶対遵守\n5. 控えめで上品な警備',
                'renewal_conditions' => '1年契約（自動更新あり）\n家族の要望に応じてサービス調整\n料金は年次見直し',
                'cancellation_terms' => '1ヶ月前の事前通知により解約可能\n緊急時は即時解約可能\n機密保持義務は継続',
                'penalty_clauses' => json_encode([
                    'privacy_breach' => 'プライバシー侵害時は即時契約解除',
                    'media_leak' => '情報漏洩時は損害賠償',
                    'neighbor_complaint' => '近隣クレーム時は改善策提出'
                ]),
                'insurance_coverage' => json_encode([
                    'personal_liability' => '1億円',
                    'property_insurance' => '5000万円',
                    'privacy_insurance' => '3000万円'
                ]),
                'signed_by_customer' => '田園調布 太郎（代表）',
                'signed_by_company' => '田中 太郎（部長）',
                'status' => 'active',
                'next_review_date' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                'created_at' => Carbon::now()->subMonths(1)->addDays(5),
                'updated_at' => Carbon::now()->subMonths(1)->addDays(5)
            ],
            // 完了した契約の例
            [
                'contract_number' => 'C2023-015',
                'quotation_id' => null,
                'customer_id' => 5, // 東京イベントプロダクション株式会社
                'project_id' => 9, // 東京マラソン2024警備業務（完了済み）
                'contract_date' => Carbon::now()->subMonths(12)->format('Y-m-d'),
                'start_date' => Carbon::now()->subMonths(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->subMonths(10)->addDays(1)->format('Y-m-d'),
                'contract_amount' => 5000000,
                'payment_terms' => 'イベント終了後30日以内',
                'service_details' => json_encode([
                    'service_type' => 'マラソン大会警備',
                    'location' => '東京都内マラソンコース全域',
                    'guard_count' => 100,
                    'shift_pattern' => '1日集中配置',
                    'services' => [
                        '沿道警備',
                        'ランナー安全確保',
                        '観客誘導',
                        '緊急時対応',
                        '交通整理',
                        '医療連携'
                    ],
                    'special_requirements' => [
                        '雑踏警備業務検定1級',
                        'マラソン大会経験',
                        '応急処置能力',
                        '多言語対応'
                    ]
                ]),
                'terms_conditions' => '1. 国際的大会のため最高レベルの警備\n2. 報道関係者多数のため対応重要\n3. 警察・消防との密接な連携\n4. 熱中症対策の徹底\n5. 多言語対応の実施',
                'renewal_conditions' => '単発イベントのため更新なし\n翌年大会での優先契約権あり',
                'cancellation_terms' => '大会中止時は50%支払い\n天候による中止は25%支払い',
                'penalty_clauses' => json_encode([
                    'security_incident' => '重大事故時は損害賠償',
                    'international_image' => '日本の国際的評価に影響する事案は重大責任'
                ]),
                'insurance_coverage' => json_encode([
                    'event_insurance' => '10億円',
                    'international_insurance' => '5億円',
                    'medical_insurance' => '3億円'
                ]),
                'signed_by_customer' => 'イベント 五郎（プロデューサー）',
                'signed_by_company' => '田中 太郎（部長）',
                'status' => 'completed',
                'next_review_date' => null,
                'created_at' => Carbon::now()->subMonths(12),
                'updated_at' => Carbon::now()->subMonths(10)
            ]
        ];

        // データを挿入
        DB::table('contracts')->insert($contracts);

        echo "Contracts seeder completed. " . count($contracts) . " contracts created.\n";
    }
}
