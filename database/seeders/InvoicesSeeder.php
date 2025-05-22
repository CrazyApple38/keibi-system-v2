<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 請求書テーブルのダミーデータ生成
 * 
 * 警備サービスの請求書情報を生成します。
 * 月次請求、イベント請求、追加請求等の多様な請求書を想定。
 */
class InvoicesSeeder extends Seeder
{
    /**
     * ダミーデータを生成して挿入
     *
     * @return void
     */
    public function run(): void
    {
        $invoices = [
            // 建設現場警備の月次請求書
            [
                'invoice_number' => 'INV2024-001',
                'contract_id' => 1, // C2024-001 新宿駅前再開発ビル建設現場警備
                'customer_id' => 1, // 東京建設株式会社
                'project_id' => 1,
                'invoice_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'due_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'billing_period_start' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'billing_period_end' => Carbon::now()->subMonths(2)->addDays(-1)->format('Y-m-d'),
                'subtotal' => 800000,
                'tax_amount' => 80000,
                'total_amount' => 880000,
                'items' => json_encode([
                    [
                        'description' => '建設現場24時間警備（1ヶ月分）',
                        'period' => Carbon::now()->subMonths(3)->format('Y年m月d日') . '～' . Carbon::now()->subMonths(2)->addDays(-1)->format('Y年m月d日'),
                        'unit_price' => 800000,
                        'quantity' => 1,
                        'amount' => 800000,
                        'details' => [
                            '警備員4名配置（24時間3交代）',
                            '稼働日数：30日',
                            '総稼働時間：720時間',
                            '交通誘導業務含む'
                        ]
                    ]
                ]),
                'payment_terms' => '月末締め翌月末払い',
                'payment_method' => '銀行振込',
                'bank_details' => json_encode([
                    'bank_name' => 'みずほ銀行',
                    'branch_name' => '新宿支店',
                    'account_type' => '普通',
                    'account_number' => '1234567',
                    'account_name' => '株式会社東央警備'
                ]),
                'notes' => '警備業務は契約通り適切に実施されました。\n次月も継続してサービス提供いたします。',
                'status' => 'paid',
                'paid_date' => Carbon::now()->subMonths(1)->addDays(-5)->format('Y-m-d'),
                'paid_amount' => 880000,
                'issued_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'created_at' => Carbon::now()->subMonths(2),
                'updated_at' => Carbon::now()->subMonths(1)->addDays(-5)
            ],
            [
                'invoice_number' => 'INV2024-002',
                'contract_id' => 1, // C2024-001
                'customer_id' => 1, // 東京建設株式会社
                'project_id' => 1,
                'invoice_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'due_date' => Carbon::now()->format('Y-m-d'),
                'billing_period_start' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'billing_period_end' => Carbon::now()->subMonths(1)->addDays(-1)->format('Y-m-d'),
                'subtotal' => 850000,
                'tax_amount' => 85000,
                'total_amount' => 935000,
                'items' => json_encode([
                    [
                        'description' => '建設現場24時間警備（1ヶ月分）',
                        'period' => Carbon::now()->subMonths(2)->format('Y年m月d日') . '～' . Carbon::now()->subMonths(1)->addDays(-1)->format('Y年m月d日'),
                        'unit_price' => 800000,
                        'quantity' => 1,
                        'amount' => 800000,
                        'details' => [
                            '警備員4名配置（24時間3交代）',
                            '稼働日数：31日',
                            '総稼働時間：744時間'
                        ]
                    ],
                    [
                        'description' => '残業代（交通事故対応）',
                        'period' => Carbon::now()->subMonths(1)->addDays(-10)->format('Y年m月d日'),
                        'unit_price' => 2500,
                        'quantity' => 20,
                        'amount' => 50000,
                        'details' => [
                            '現場近くでの交通事故対応',
                            '警察対応・現場保全',
                            '20時間延長勤務'
                        ]
                    ]
                ]),
                'payment_terms' => '月末締め翌月末払い',
                'payment_method' => '銀行振込',
                'bank_details' => json_encode([
                    'bank_name' => 'みずほ銀行',
                    'branch_name' => '新宿支店',
                    'account_type' => '普通',
                    'account_number' => '1234567',
                    'account_name' => '株式会社東央警備'
                ]),
                'notes' => '月中に交通事故が発生し、警備員が対応のため延長勤務を行いました。\n適切な対応により大きな問題に発展することを防ぎました。',
                'status' => 'pending',
                'paid_date' => null,
                'paid_amount' => 0,
                'issued_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'created_at' => Carbon::now()->subMonths(1),
                'updated_at' => Carbon::now()->subMonths(1)
            ],
            // 道路工事警備の請求書
            [
                'invoice_number' => 'INV2024-003',
                'contract_id' => 2, // C2024-002 環状7号線道路工事夜間警備
                'customer_id' => 2, // 関東土木工業株式会社
                'project_id' => 2,
                'invoice_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'due_date' => Carbon::now()->format('Y-m-d'),
                'billing_period_start' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'billing_period_end' => Carbon::now()->addDays(-1)->format('Y-m-d'),
                'subtotal' => 1080000,
                'tax_amount' => 108000,
                'total_amount' => 1188000,
                'items' => json_encode([
                    [
                        'description' => '道路工事夜間警備（1ヶ月分）',
                        'period' => Carbon::now()->subMonths(1)->format('Y年m月d日') . '～' . Carbon::now()->addDays(-1)->format('Y年m月d日'),
                        'unit_price' => 1200000,
                        'quantity' => 1,
                        'amount' => 1200000,
                        'details' => [
                            '警備員6名配置（夜間21:00-6:00）',
                            '稼働日数：30日',
                            '夜間危険手当含む'
                        ]
                    ],
                    [
                        'description' => '雨天中止による減額',
                        'period' => '雨天中止日：5日間',
                        'unit_price' => -24000,
                        'quantity' => 5,
                        'amount' => -120000,
                        'details' => [
                            '雨天による工事中止',
                            '契約に基づく日割り減額',
                            '中止日の警備員待機料含む'
                        ]
                    ]
                ]),
                'payment_terms' => '月末締め翌月末払い',
                'payment_method' => '銀行振込',
                'bank_details' => json_encode([
                    'bank_name' => 'みずほ銀行',
                    'branch_name' => '新宿支店',
                    'account_type' => '普通',
                    'account_number' => '1234567',
                    'account_name' => '株式会社東央警備'
                ]),
                'notes' => '雨天による工事中止が5日間ありました。\n契約条件に従い日割り計算にて減額いたします。',
                'status' => 'pending',
                'paid_date' => null,
                'paid_amount' => 0,
                'issued_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'created_at' => Carbon::now()->subMonths(1),
                'updated_at' => Carbon::now()->subMonths(1)
            ],
            // オフィスビル警備の請求書
            [
                'invoice_number' => 'INV2024-004',
                'contract_id' => 3, // C2024-003 新宿センタービル24時間警備
                'customer_id' => 3, // 新宿プロパティーズ株式会社
                'project_id' => 3,
                'invoice_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'due_date' => Carbon::now()->addDays(25)->format('Y-m-d'),
                'billing_period_start' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'billing_period_end' => Carbon::now()->subMonths(1)->addDays(-1)->format('Y-m-d'),
                'subtotal' => 1000000,
                'tax_amount' => 100000,
                'total_amount' => 1100000,
                'items' => json_encode([
                    [
                        'description' => 'オフィスビル24時間警備（1ヶ月分）',
                        'period' => Carbon::now()->subMonths(2)->format('Y年m月d日') . '～' . Carbon::now()->subMonths(1)->addDays(-1)->format('Y年m月d日'),
                        'unit_price' => 1000000,
                        'quantity' => 1,
                        'amount' => 1000000,
                        'details' => [
                            '警備員3名配置（24時間3交代）',
                            '受付業務・巡回警備',
                            '防災センター業務',
                            'VIP来館対応2件'
                        ]
                    ]
                ]),
                'payment_terms' => '月末締め翌月25日払い',
                'payment_method' => '銀行振込',
                'bank_details' => json_encode([
                    'bank_name' => 'みずほ銀行',
                    'branch_name' => '新宿支店',
                    'account_type' => '普通',
                    'account_number' => '1234567',
                    'account_name' => '株式会社東央警備'
                ]),
                'notes' => '月中にVIP来館が2件ありましたが、特別対応を適切に実施いたしました。\n継続して高品質なサービスを提供いたします。',
                'status' => 'paid',
                'paid_date' => Carbon::now()->addDays(-5)->format('Y-m-d'),
                'paid_amount' => 1100000,
                'issued_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'created_at' => Carbon::now()->subMonths(1),
                'updated_at' => Carbon::now()->addDays(-5)
            ],
            // ショッピングモール警備の請求書
            [
                'invoice_number' => 'INV2024-005',
                'contract_id' => 4, // C2024-004 渋谷モール巡回・監視警備
                'customer_id' => 4, // 渋谷ショッピングモール株式会社
                'project_id' => 4,
                'invoice_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'due_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'billing_period_start' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'billing_period_end' => Carbon::now()->subMonths(1)->addDays(-1)->format('Y-m-d'),
                'subtotal' => 820000,
                'tax_amount' => 82000,
                'total_amount' => 902000,
                'items' => json_encode([
                    [
                        'description' => 'ショッピングモール警備（1ヶ月分）',
                        'period' => Carbon::now()->subMonths(2)->format('Y年m月d日') . '～' . Carbon::now()->subMonths(1)->addDays(-1)->format('Y年m月d日'),
                        'unit_price' => 750000,
                        'quantity' => 1,
                        'amount' => 750000,
                        'details' => [
                            '警備員5名配置（10:00-22:00）',
                            'フロア巡回・監視業務',
                            '迷子対応5件、急病人対応3件',
                            '外国人観光客対応多数'
                        ]
                    ],
                    [
                        'description' => 'セール期間警備強化',
                        'period' => 'セール期間：7日間',
                        'unit_price' => 10000,
                        'quantity' => 7,
                        'amount' => 70000,
                        'details' => [
                            '年末セールでの警備員1名増員',
                            '混雑時の安全確保',
                            '万引き防止強化'
                        ]
                    ]
                ]),
                'payment_terms' => '月末締め翌月20日払い',
                'payment_method' => '銀行振込',
                'bank_details' => json_encode([
                    'bank_name' => 'みずほ銀行',
                    'branch_name' => '新宿支店',
                    'account_type' => '普通',
                    'account_number' => '1234567',
                    'account_name' => '株式会社東央警備'
                ]),
                'notes' => '年末セール期間中は混雑が予想以上でしたが、適切な警備強化により大きなトラブルはありませんでした。\n迷子・急病人対応も適切に実施いたしました。',
                'status' => 'overdue',
                'paid_date' => null,
                'paid_amount' => 0,
                'issued_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'created_at' => Carbon::now()->subMonths(1),
                'updated_at' => Carbon::now()->subMonths(1)
            ],
            // 製鉄工場警備の請求書
            [
                'invoice_number' => 'INV2024-006',
                'contract_id' => 5, // C2024-005 東京製鉄工場24時間警備
                'customer_id' => 7, // 東京製鉄株式会社
                'project_id' => 7,
                'invoice_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'due_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'billing_period_start' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'billing_period_end' => Carbon::now()->subMonths(1)->addDays(-1)->format('Y-m-d'),
                'subtotal' => 1500000,
                'tax_amount' => 150000,
                'total_amount' => 1650000,
                'items' => json_encode([
                    [
                        'description' => '製鉄工場24時間警備（1ヶ月分）',
                        'period' => Carbon::now()->subMonths(2)->format('Y年m月d日') . '～' . Carbon::now()->subMonths(1)->addDays(-1)->format('Y年m月d日'),
                        'unit_price' => 1500000,
                        'quantity' => 1,
                        'amount' => 1500000,
                        'details' => [
                            '警備員8名配置（24時間4交代）',
                            '危険物取扱い立会い',
                            '工場内巡回・設備監視',
                            '産業スパイ防止',
                            '消防設備点検立会い2回'
                        ]
                    ]
                ]),
                'payment_terms' => '月末締め翌月15日払い',
                'payment_method' => '銀行振込',
                'bank_details' => json_encode([
                    'bank_name' => 'みずほ銀行',
                    'branch_name' => '新宿支店',
                    'account_type' => '普通',
                    'account_number' => '1234567',
                    'account_name' => '株式会社全日本エンタープライズ'
                ]),
                'notes' => '高温・有害ガス環境での警備業務を適切に実施いたしました。\n消防設備点検にも立会い、異常なしを確認いたしました。',
                'status' => 'paid',
                'paid_date' => Carbon::now()->addDays(-5)->format('Y-m-d'),
                'paid_amount' => 1650000,
                'issued_by' => 6, // 渡辺 七郎
                'approved_by' => 5, // 伊藤 六郎
                'created_at' => Carbon::now()->subMonths(1),
                'updated_at' => Carbon::now()->addDays(-5)
            ],
            // 物流センター警備の請求書
            [
                'invoice_number' => 'INV2024-007',
                'contract_id' => 6, // C2024-006 関東ロジセンター夜間警備
                'customer_id' => 8, // 関東ロジスティクス株式会社
                'project_id' => 8,
                'invoice_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'due_date' => Carbon::now()->format('Y-m-d'),
                'billing_period_start' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'billing_period_end' => Carbon::now()->subMonths(1)->addDays(-1)->format('Y-m-d'),
                'subtotal' => 625000,
                'tax_amount' => 62500,
                'total_amount' => 687500,
                'items' => json_encode([
                    [
                        'description' => '物流センター夜間警備（1ヶ月分）',
                        'period' => Carbon::now()->subMonths(2)->format('Y年m月d日') . '～' . Carbon::now()->subMonths(1)->addDays(-1)->format('Y年m月d日'),
                        'unit_price' => 625000,
                        'quantity' => 1,
                        'amount' => 625000,
                        'details' => [
                            '警備員4名配置（18:00-8:00）',
                            '冷凍倉庫監視（-20℃環境）',
                            '夜間配送トラック管理',
                            '温度異常対応1件',
                            '低温環境手当含む'
                        ]
                    ]
                ]),
                'payment_terms' => '月末締め翌月末払い',
                'payment_method' => '銀行振込',
                'bank_details' => json_encode([
                    'bank_name' => 'みずほ銀行',
                    'branch_name' => '新宿支店',
                    'account_type' => '普通',
                    'account_number' => '1234567',
                    'account_name' => '株式会社全日本エンタープライズ'
                ]),
                'notes' => '月中に冷凍設備の温度異常が1回発生しましたが、迅速な対応により大きな損害を防ぎました。\n夜間配送業務も順調に進行しております。',
                'status' => 'pending',
                'paid_date' => null,
                'paid_amount' => 0,
                'issued_by' => 6, // 渡辺 七郎
                'approved_by' => 5, // 伊藤 六郎
                'created_at' => Carbon::now()->subMonths(1),
                'updated_at' => Carbon::now()->subMonths(1)
            ],
            // 高級住宅警備の請求書
            [
                'invoice_number' => 'INV2024-008',
                'contract_id' => 7, // C2024-007 田園調布高級住宅警備
                'customer_id' => 10, // 個人事業主 田園調布太郎
                'project_id' => 10,
                'invoice_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'due_date' => Carbon::now()->format('Y-m-d'),
                'billing_period_start' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'billing_period_end' => Carbon::now()->addDays(-1)->format('Y-m-d'),
                'subtotal' => 500000,
                'tax_amount' => 50000,
                'total_amount' => 550000,
                'items' => json_encode([
                    [
                        'description' => '高級住宅警備（1ヶ月分）',
                        'period' => Carbon::now()->subMonths(1)->format('Y年m月d日') . '～' . Carbon::now()->addDays(-1)->format('Y年m月d日'),
                        'unit_price' => 500000,
                        'quantity' => 1,
                        'amount' => 500000,
                        'details' => [
                            '警備員1名配置（9:00-18:00）',
                            '来訪者管理・敷地内巡回',
                            'プライバシー保護',
                            '報道関係者対応1件',
                            '近隣配慮の警備実施'
                        ]
                    ]
                ]),
                'payment_terms' => '月末締め翌月末払い',
                'payment_method' => '銀行振込',
                'bank_details' => json_encode([
                    'bank_name' => 'みずほ銀行',
                    'branch_name' => '新宿支店',
                    'account_type' => '普通',
                    'account_number' => '1234567',
                    'account_name' => '株式会社東央警備'
                ]),
                'notes' => '月中に報道関係者が1名訪問されましたが、適切に対応いたしました。\n近隣の皆様にもご迷惑をおかけすることなく警備業務を実施しております。',
                'status' => 'pending',
                'paid_date' => null,
                'paid_amount' => 0,
                'issued_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'created_at' => Carbon::now()->subMonths(1),
                'updated_at' => Carbon::now()->subMonths(1)
            ],
            // イベント警備の一括請求書（完了済み）
            [
                'invoice_number' => 'INV2024-009',
                'contract_id' => 8, // C2023-015 東京マラソン2024警備業務
                'customer_id' => 5, // 東京イベントプロダクション株式会社
                'project_id' => 9,
                'invoice_date' => Carbon::now()->subMonths(9)->format('Y-m-d'),
                'due_date' => Carbon::now()->subMonths(8)->format('Y-m-d'),
                'billing_period_start' => Carbon::now()->subMonths(10)->format('Y-m-d'),
                'billing_period_end' => Carbon::now()->subMonths(10)->addDays(1)->format('Y-m-d'),
                'subtotal' => 5000000,
                'tax_amount' => 500000,
                'total_amount' => 5500000,
                'items' => json_encode([
                    [
                        'description' => '東京マラソン2024警備業務',
                        'period' => Carbon::now()->subMonths(10)->format('Y年m月d日') . '～' . Carbon::now()->subMonths(10)->addDays(1)->format('Y年m月d日'),
                        'unit_price' => 5000000,
                        'quantity' => 1,
                        'amount' => 5000000,
                        'details' => [
                            '警備員100名配置（2日間）',
                            'コース全域での沿道警備',
                            'ランナー・観客安全確保',
                            '緊急医療対応連携',
                            '多言語対応実施',
                            '国際的イベント対応'
                        ]
                    ]
                ]),
                'payment_terms' => 'イベント終了後30日以内',
                'payment_method' => '銀行振込',
                'bank_details' => json_encode([
                    'bank_name' => 'みずほ銀行',
                    'branch_name' => '新宿支店',
                    'account_type' => '普通',
                    'account_number' => '1234567',
                    'account_name' => '株式会社東央警備'
                ]),
                'notes' => '東京マラソン2024において、100名の警備員で完璧な警備業務を実施いたしました。\n大きなトラブルもなく、国際的なイベントを成功に導くことができました。',
                'status' => 'paid',
                'paid_date' => Carbon::now()->subMonths(8)->addDays(-3)->format('Y-m-d'),
                'paid_amount' => 5500000,
                'issued_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'created_at' => Carbon::now()->subMonths(9),
                'updated_at' => Carbon::now()->subMonths(8)->addDays(-3)
            ],
            // 追加請求書の例
            [
                'invoice_number' => 'INV2024-010',
                'contract_id' => 3, // C2024-003 新宿センタービル24時間警備
                'customer_id' => 3, // 新宿プロパティーズ株式会社
                'project_id' => 3,
                'invoice_date' => Carbon::now()->subWeeks(2)->format('Y-m-d'),
                'due_date' => Carbon::now()->addDays(25)->format('Y-m-d'),
                'billing_period_start' => Carbon::now()->subWeeks(3)->format('Y-m-d'),
                'billing_period_end' => Carbon::now()->subWeeks(2)->addDays(-1)->format('Y-m-d'),
                'subtotal' => 150000,
                'tax_amount' => 15000,
                'total_amount' => 165000,
                'items' => json_encode([
                    [
                        'description' => '緊急事態対応追加業務',
                        'period' => Carbon::now()->subWeeks(3)->format('Y年m月d日'),
                        'unit_price' => 15000,
                        'quantity' => 10,
                        'amount' => 150000,
                        'details' => [
                            'ビル内火災報知機誤作動対応',
                            '消防署・テナント企業への連絡',
                            '避難誘導準備・現場対応',
                            '10時間の緊急対応業務',
                            '夜間緊急呼び出し手当含む'
                        ]
                    ]
                ]),
                'payment_terms' => '月末締め翌月25日払い',
                'payment_method' => '銀行振込',
                'bank_details' => json_encode([
                    'bank_name' => 'みずほ銀行',
                    'branch_name' => '新宿支店',
                    'account_type' => '普通',
                    'account_number' => '1234567',
                    'account_name' => '株式会社東央警備'
                ]),
                'notes' => '深夜に火災報知機の誤作動が発生し、緊急対応を実施いたしました。\n迅速な対応により、テナント企業・入居者の皆様にご迷惑をかけることなく解決いたしました。',
                'status' => 'pending',
                'paid_date' => null,
                'paid_amount' => 0,
                'issued_by' => 2, // 佐藤 次郎
                'approved_by' => 1, // 田中 太郎
                'created_at' => Carbon::now()->subWeeks(2),
                'updated_at' => Carbon::now()->subWeeks(2)
            ]
        ];

        // データを挿入
        DB::table('invoices')->insert($invoices);

        echo "Invoices seeder completed. " . count($invoices) . " invoices created.\n";
    }
}
