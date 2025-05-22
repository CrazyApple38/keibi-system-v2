<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 顧客テーブルのダミーデータ生成
 * 
 * 警備サービスを利用する企業・法人・個人のダミー顧客データを生成します。
 * 建設会社、オフィスビル、イベント会社、商業施設等の顧客を想定。
 */
class CustomersSeeder extends Seeder
{
    /**
     * ダミーデータを生成して挿入
     *
     * @return void
     */
    public function run(): void
    {
        $customers = [
            // 建設・土木関連の顧客
            [
                'customer_code' => 'CUST001',
                'company_name' => '東京建設株式会社',
                'customer_type' => 'corporate',
                'contact_person' => '建設 太郎',
                'contact_title' => '工事部長',
                'phone' => '03-1111-2222',
                'mobile' => '090-1111-2222',
                'email' => 'kensetsu@tokyo-kensetsu.co.jp',
                'postal_code' => '100-0001',
                'prefecture' => '東京都',
                'city' => '千代田区',
                'address_line1' => '千代田1-1-1',
                'address_line2' => '東京建設ビル5F',
                'billing_address' => json_encode([
                    'postal_code' => '100-0001',
                    'prefecture' => '東京都',
                    'city' => '千代田区',
                    'address_line1' => '千代田1-1-1',
                    'address_line2' => '東京建設ビル経理部'
                ]),
                'business_type' => '建設業',
                'employee_count' => 150,
                'annual_revenue' => 500000000,
                'credit_limit' => 5000000,
                'payment_terms' => '月末締め翌月末払い',
                'tax_id' => '1234567890',
                'status' => 'active',
                'notes' => '大手建設会社。現場警備の需要が高い。',
                'created_at' => Carbon::now()->subMonths(8),
                'updated_at' => Carbon::now()
            ],
            [
                'customer_code' => 'CUST002',
                'company_name' => '関東土木工業株式会社',
                'customer_type' => 'corporate',
                'contact_person' => '土木 次郎',
                'contact_title' => '現場監督',
                'phone' => '03-2222-3333',
                'mobile' => '090-2222-3333',
                'email' => 'doboku@kanto-doboku.co.jp',
                'postal_code' => '130-0001',
                'prefecture' => '東京都',
                'city' => '墨田区',
                'address_line1' => '墨田2-2-2',
                'address_line2' => '関東土木ビル3F',
                'billing_address' => json_encode([
                    'postal_code' => '130-0001',
                    'prefecture' => '東京都',
                    'city' => '墨田区',
                    'address_line1' => '墨田2-2-2',
                    'address_line2' => '関東土木ビル経理課'
                ]),
                'business_type' => '土木工事業',
                'employee_count' => 80,
                'annual_revenue' => 300000000,
                'credit_limit' => 3000000,
                'payment_terms' => '月末締め翌月末払い',
                'tax_id' => '2345678901',
                'status' => 'active',
                'notes' => '道路工事、橋梁工事が主力。夜間工事も多い。',
                'created_at' => Carbon::now()->subMonths(6),
                'updated_at' => Carbon::now()
            ],
            // オフィスビル・商業施設関連
            [
                'customer_code' => 'CUST003',
                'company_name' => '新宿プロパティーズ株式会社',
                'customer_type' => 'corporate',
                'contact_person' => '不動産 三郎',
                'contact_title' => '管理部課長',
                'phone' => '03-3333-4444',
                'mobile' => '090-3333-4444',
                'email' => 'kanri@shinjuku-properties.co.jp',
                'postal_code' => '160-0022',
                'prefecture' => '東京都',
                'city' => '新宿区',
                'address_line1' => '新宿3-3-3',
                'address_line2' => '新宿センタービル20F',
                'billing_address' => json_encode([
                    'postal_code' => '160-0022',
                    'prefecture' => '東京都',
                    'city' => '新宿区',
                    'address_line1' => '新宿3-3-3',
                    'address_line2' => '新宿センタービル経理部'
                ]),
                'business_type' => '不動産管理業',
                'employee_count' => 200,
                'annual_revenue' => 800000000,
                'credit_limit' => 8000000,
                'payment_terms' => '月末締め翌月25日払い',
                'tax_id' => '3456789012',
                'status' => 'active',
                'notes' => '複数のオフィスビルを管理。24時間警備が必要。',
                'created_at' => Carbon::now()->subMonths(10),
                'updated_at' => Carbon::now()
            ],
            [
                'customer_code' => 'CUST004',
                'company_name' => '渋谷ショッピングモール株式会社',
                'customer_type' => 'corporate',
                'contact_person' => '商業 四郎',
                'contact_title' => 'テナント管理部長',
                'phone' => '03-4444-5555',
                'mobile' => '090-4444-5555',
                'email' => 'tenant@shibuya-mall.co.jp',
                'postal_code' => '150-0043',
                'prefecture' => '東京都',
                'city' => '渋谷区',
                'address_line1' => '道玄坂4-4-4',
                'address_line2' => '渋谷モール管理事務所',
                'billing_address' => json_encode([
                    'postal_code' => '150-0043',
                    'prefecture' => '東京都',
                    'city' => '渋谷区',
                    'address_line1' => '道玄坂4-4-4',
                    'address_line2' => '渋谷モール経理部'
                ]),
                'business_type' => '商業施設運営業',
                'employee_count' => 300,
                'annual_revenue' => 1200000000,
                'credit_limit' => 10000000,
                'payment_terms' => '月末締め翌月20日払い',
                'tax_id' => '4567890123',
                'status' => 'active',
                'notes' => '大型ショッピングモール。客数が多く警備重要。',
                'created_at' => Carbon::now()->subMonths(12),
                'updated_at' => Carbon::now()
            ],
            // イベント・エンターテイメント関連
            [
                'customer_code' => 'CUST005',
                'company_name' => '東京イベントプロダクション株式会社',
                'customer_type' => 'corporate',
                'contact_person' => 'イベント 五郎',
                'contact_title' => 'プロデューサー',
                'phone' => '03-5555-6666',
                'mobile' => '090-5555-6666',
                'email' => 'event@tokyo-event.co.jp',
                'postal_code' => '107-0052',
                'prefecture' => '東京都',
                'city' => '港区',
                'address_line1' => '赤坂5-5-5',
                'address_line2' => 'イベントプラザビル8F',
                'billing_address' => json_encode([
                    'postal_code' => '107-0052',
                    'prefecture' => '東京都',
                    'city' => '港区',
                    'address_line1' => '赤坂5-5-5',
                    'address_line2' => 'イベントプラザビル経理課'
                ]),
                'business_type' => 'イベント企画・運営業',
                'employee_count' => 50,
                'annual_revenue' => 200000000,
                'credit_limit' => 2000000,
                'payment_terms' => '月末締め翌月末払い',
                'tax_id' => '5678901234',
                'status' => 'active',
                'notes' => 'コンサート、展示会等のイベント警備が中心。',
                'created_at' => Carbon::now()->subMonths(4),
                'updated_at' => Carbon::now()
            ],
            [
                'customer_code' => 'CUST006',
                'company_name' => '株式会社フェスティバル企画',
                'customer_type' => 'corporate',
                'contact_person' => 'フェス 六郎',
                'contact_title' => '運営部長',
                'phone' => '03-6666-7777',
                'mobile' => '090-6666-7777',
                'email' => 'unei@festival-plan.co.jp',
                'postal_code' => '140-0002',
                'prefecture' => '東京都',
                'city' => '品川区',
                'address_line1' => '東品川6-6-6',
                'address_line2' => 'フェスティバルタワー12F',
                'billing_address' => json_encode([
                    'postal_code' => '140-0002',
                    'prefecture' => '東京都',
                    'city' => '品川区',
                    'address_line1' => '東品川6-6-6',
                    'address_line2' => 'フェスティバルタワー会計部'
                ]),
                'business_type' => 'イベント企画・運営業',
                'employee_count' => 30,
                'annual_revenue' => 150000000,
                'credit_limit' => 1500000,
                'payment_terms' => '月末締め翌月末払い',
                'tax_id' => '6789012345',
                'status' => 'active',
                'notes' => '音楽フェス、食イベントが専門。屋外イベント多数。',
                'created_at' => Carbon::now()->subMonths(3),
                'updated_at' => Carbon::now()
            ],
            // 製造業・物流関連
            [
                'customer_code' => 'CUST007',
                'company_name' => '東京製鉄株式会社',
                'customer_type' => 'corporate',
                'contact_person' => '製鉄 七郎',
                'contact_title' => '工場長',
                'phone' => '03-7777-8888',
                'mobile' => '090-7777-8888',
                'email' => 'factory@tokyo-steel.co.jp',
                'postal_code' => '120-0005',
                'prefecture' => '東京都',
                'city' => '足立区',
                'address_line1' => '綾瀬7-7-7',
                'address_line2' => '東京製鉄工場',
                'billing_address' => json_encode([
                    'postal_code' => '120-0005',
                    'prefecture' => '東京都',
                    'city' => '足立区',
                    'address_line1' => '綾瀬7-7-7',
                    'address_line2' => '東京製鉄本社経理部'
                ]),
                'business_type' => '製鉄業',
                'employee_count' => 500,
                'annual_revenue' => 2000000000,
                'credit_limit' => 15000000,
                'payment_terms' => '月末締め翌月15日払い',
                'tax_id' => '7890123456',
                'status' => 'active',
                'notes' => '大規模工場。24時間体制での警備が必要。',
                'created_at' => Carbon::now()->subMonths(15),
                'updated_at' => Carbon::now()
            ],
            [
                'customer_code' => 'CUST008',
                'company_name' => '関東ロジスティクス株式会社',
                'customer_type' => 'corporate',
                'contact_person' => '物流 八郎',
                'contact_title' => '倉庫管理部長',
                'phone' => '03-8888-9999',
                'mobile' => '090-8888-9999',
                'email' => 'souko@kanto-logistics.co.jp',
                'postal_code' => '134-0088',
                'prefecture' => '東京都',
                'city' => '江戸川区',
                'address_line1' => '西葛西8-8-8',
                'address_line2' => '関東ロジセンター',
                'billing_address' => json_encode([
                    'postal_code' => '134-0088',
                    'prefecture' => '東京都',
                    'city' => '江戸川区',
                    'address_line1' => '西葛西8-8-8',
                    'address_line2' => '関東ロジセンター本部'
                ]),
                'business_type' => '物流・倉庫業',
                'employee_count' => 120,
                'annual_revenue' => 400000000,
                'credit_limit' => 4000000,
                'payment_terms' => '月末締め翌月末払い',
                'tax_id' => '8901234567',
                'status' => 'active',
                'notes' => '大型物流倉庫複数運営。夜間配送対応のため警備重要。',
                'created_at' => Carbon::now()->subMonths(9),
                'updated_at' => Carbon::now()
            ],
            // 一時停止・非アクティブ顧客の例
            [
                'customer_code' => 'CUST009',
                'company_name' => '旧式商事株式会社',
                'customer_type' => 'corporate',
                'contact_person' => '旧式 九郎',
                'contact_title' => '代表取締役',
                'phone' => '03-9999-0000',
                'mobile' => '090-9999-0000',
                'email' => 'kyushiki@kyushiki-shoji.co.jp',
                'postal_code' => '110-0016',
                'prefecture' => '東京都',
                'city' => '台東区',
                'address_line1' => '台東9-9-9',
                'address_line2' => '旧式ビル1F',
                'billing_address' => json_encode([
                    'postal_code' => '110-0016',
                    'prefecture' => '東京都',
                    'city' => '台東区',
                    'address_line1' => '台東9-9-9',
                    'address_line2' => '旧式ビル1F'
                ]),
                'business_type' => '商事・貿易業',
                'employee_count' => 20,
                'annual_revenue' => 100000000,
                'credit_limit' => 500000,
                'payment_terms' => '月末締め翌月末払い',
                'tax_id' => '9012345678',
                'status' => 'inactive',
                'notes' => '一時的に業務縮小のため契約停止中。',
                'created_at' => Carbon::now()->subMonths(18),
                'updated_at' => Carbon::now()
            ],
            [
                'customer_code' => 'CUST010',
                'company_name' => '個人事業主 田園調布太郎',
                'customer_type' => 'individual',
                'contact_person' => '田園調布 太郎',
                'contact_title' => '代表',
                'phone' => '03-0000-1111',
                'mobile' => '090-0000-1111',
                'email' => 'denenchofu@personal.com',
                'postal_code' => '145-0071',
                'prefecture' => '東京都',
                'city' => '大田区',
                'address_line1' => '田園調布10-10-10',
                'address_line2' => '',
                'billing_address' => json_encode([
                    'postal_code' => '145-0071',
                    'prefecture' => '東京都',
                    'city' => '大田区',
                    'address_line1' => '田園調布10-10-10',
                    'address_line2' => ''
                ]),
                'business_type' => '個人事業',
                'employee_count' => 1,
                'annual_revenue' => 50000000,
                'credit_limit' => 500000,
                'payment_terms' => '月末締め翌月末払い',
                'tax_id' => '0123456789',
                'status' => 'active',
                'notes' => '高級住宅地での個人宅警備。特別対応が必要。',
                'created_at' => Carbon::now()->subMonths(2),
                'updated_at' => Carbon::now()
            ]
        ];

        // データを挿入
        DB::table('customers')->insert($customers);

        echo "Customers seeder completed. " . count($customers) . " customers created.\n";
    }
}
