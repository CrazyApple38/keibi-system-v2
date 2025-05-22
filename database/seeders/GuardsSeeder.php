<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 警備員テーブルのダミーデータ生成
 * 
 * 警備業務に従事する警備員の詳細情報を生成します。
 * 各種資格、経験、スキル、配属会社等の多様な警備員データを想定。
 */
class GuardsSeeder extends Seeder
{
    /**
     * ダミーデータを生成して挿入
     *
     * @return void
     */
    public function run(): void
    {
        $guards = [
            // ベテラン警備員（㈲東央警備所属）
            [
                'guard_code' => 'G001',
                'company_id' => 1,
                'name' => '警備 太郎',
                'name_kana' => 'ケイビ タロウ',
                'gender' => 'male',
                'birth_date' => '1975-04-15',
                'age' => 49,
                'phone' => '03-1111-1111',
                'mobile' => '090-1111-1111',
                'email' => 'keibi.taro@touo-keibi.com',
                'postal_code' => '120-0003',
                'prefecture' => '東京都',
                'city' => '足立区',
                'address_line1' => '東和3-1-1',
                'address_line2' => 'パークマンション201',
                'emergency_contact' => json_encode([
                    'name' => '警備 花子',
                    'relationship' => '配偶者',
                    'phone' => '03-1111-2222',
                    'mobile' => '090-1111-2222'
                ]),
                'hire_date' => '2020-03-01',
                'employment_type' => 'full_time',
                'base_hourly_rate' => 1500,
                'overtime_rate' => 1875,
                'night_rate' => 1800,
                'holiday_rate' => 2000,
                'qualifications' => json_encode([
                    '警備員検定1級（施設警備業務）',
                    '警備員検定2級（交通誘導警備業務）',
                    '防災センター要員',
                    '普通自動車第一種運転免許',
                    '危険物取扱者乙種第4類'
                ]),
                'skills' => json_encode([
                    '施設警備20年経験',
                    'ビル管理業務',
                    '防災設備点検',
                    '英語日常会話レベル',
                    'PC操作（Word, Excel）'
                ]),
                'physical_condition' => json_encode([
                    'height' => 170,
                    'weight' => 70,
                    'blood_type' => 'A',
                    'health_status' => 'excellent',
                    'restrictions' => null
                ]),
                'availability' => json_encode([
                    'day_shift' => true,
                    'night_shift' => true,
                    'weekend' => true,
                    'holiday' => true,
                    'overtime' => true
                ]),
                'preferred_locations' => json_encode([
                    '東京都23区内',
                    '千葉県西部',
                    '埼玉県南部'
                ]),
                'security_clearance' => 'standard',
                'uniform_size' => json_encode([
                    'jacket' => 'L',
                    'pants' => 'L',
                    'shirt' => 'L',
                    'shoes' => '26.0'
                ]),
                'performance_rating' => 4.8,
                'status' => 'active',
                'notes' => 'ベテラン警備員。現場リーダーとして新人指導も担当。顧客からの信頼厚い。',
                'created_at' => Carbon::now()->subMonths(8),
                'updated_at' => Carbon::now()
            ],
            [
                'guard_code' => 'G002',
                'company_id' => 1,
                'name' => '守護 次郎',
                'name_kana' => 'シュゴ ジロウ',
                'gender' => 'male',
                'birth_date' => '1988-08-22',
                'age' => 36,
                'phone' => '03-2222-2222',
                'mobile' => '090-2222-2222',
                'email' => 'shugo.jiro@touo-keibi.com',
                'postal_code' => '111-0032',
                'prefecture' => '東京都',
                'city' => '台東区',
                'address_line1' => '浅草4-2-2',
                'address_line2' => '浅草ハイツ305',
                'emergency_contact' => json_encode([
                    'name' => '守護 正雄',
                    'relationship' => '父',
                    'phone' => '03-2222-3333',
                    'mobile' => '090-2222-3333'
                ]),
                'hire_date' => '2021-07-15',
                'employment_type' => 'full_time',
                'base_hourly_rate' => 1300,
                'overtime_rate' => 1625,
                'night_rate' => 1560,
                'holiday_rate' => 1730,
                'qualifications' => json_encode([
                    '警備員検定2級（交通誘導警備業務）',
                    '警備員検定2級（雑踏警備業務）',
                    '普通自動車第一種運転免許',
                    '大型自動車第一種運転免許'
                ]),
                'skills' => json_encode([
                    '交通誘導警備10年経験',
                    '建設現場警備',
                    '大型車両運転',
                    '現場作業経験',
                    'クレーン操作補助'
                ]),
                'physical_condition' => json_encode([
                    'height' => 175,
                    'weight' => 80,
                    'blood_type' => 'B',
                    'health_status' => 'good',
                    'restrictions' => null
                ]),
                'availability' => json_encode([
                    'day_shift' => true,
                    'night_shift' => true,
                    'weekend' => true,
                    'holiday' => true,
                    'overtime' => true
                ]),
                'preferred_locations' => json_encode([
                    '東京都全域',
                    '神奈川県東部'
                ]),
                'security_clearance' => 'standard',
                'uniform_size' => json_encode([
                    'jacket' => 'LL',
                    'pants' => 'LL',
                    'shirt' => 'LL',
                    'shoes' => '27.0'
                ]),
                'performance_rating' => 4.3,
                'status' => 'active',
                'notes' => '道路工事・建設現場での経験豊富。体力に自信あり、夜間勤務も得意。',
                'created_at' => Carbon::now()->subMonths(6),
                'updated_at' => Carbon::now()
            ],
            [
                'guard_code' => 'G003',
                'company_id' => 1,
                'name' => '安全 三郎',
                'name_kana' => 'アンゼン サブロウ',
                'gender' => 'male',
                'birth_date' => '1992-12-03',
                'age' => 32,
                'phone' => '03-3333-3333',
                'mobile' => '090-3333-3333',
                'email' => 'anzen.saburo@touo-keibi.com',
                'postal_code' => '130-0022',
                'prefecture' => '東京都',
                'city' => '墨田区',
                'address_line1' => '江東橋5-3-3',
                'address_line2' => 'リバーサイドマンション402',
                'emergency_contact' => json_encode([
                    'name' => '安全 美香',
                    'relationship' => '配偶者',
                    'phone' => '03-3333-4444',
                    'mobile' => '090-3333-4444'
                ]),
                'hire_date' => '2022-04-01',
                'employment_type' => 'full_time',
                'base_hourly_rate' => 1200,
                'overtime_rate' => 1500,
                'night_rate' => 1440,
                'holiday_rate' => 1600,
                'qualifications' => json_encode([
                    '警備員検定2級（施設警備業務）',
                    '普通自動車第一種運転免許',
                    '普通救命講習修了',
                    '防火管理者'
                ]),
                'skills' => json_encode([
                    '施設警備5年経験',
                    'オフィスビル勤務',
                    '接客対応',
                    '英語基礎レベル',
                    'IT機器操作'
                ]),
                'physical_condition' => json_encode([
                    'height' => 168,
                    'weight' => 65,
                    'blood_type' => 'O',
                    'health_status' => 'excellent',
                    'restrictions' => null
                ]),
                'availability' => json_encode([
                    'day_shift' => true,
                    'night_shift' => true,
                    'weekend' => true,
                    'holiday' => false,
                    'overtime' => true
                ]),
                'preferred_locations' => json_encode([
                    '東京都23区内',
                    '千葉県市川市・船橋市'
                ]),
                'security_clearance' => 'standard',
                'uniform_size' => json_encode([
                    'jacket' => 'M',
                    'pants' => 'M',
                    'shirt' => 'M',
                    'shoes' => '25.5'
                ]),
                'performance_rating' => 4.1,
                'status' => 'active',
                'notes' => '真面目で責任感強い。オフィスビル警備での接客対応が得意。IT関連の知識もある。',
                'created_at' => Carbon::now()->subMonths(4),
                'updated_at' => Carbon::now()
            ],
            // ㈱Nikkeiホールディングス所属警備員
            [
                'guard_code' => 'G004',
                'company_id' => 2,
                'name' => '監視 四郎',
                'name_kana' => 'カンシ シロウ',
                'gender' => 'male',
                'birth_date' => '1980-06-10',
                'age' => 44,
                'phone' => '03-4444-4444',
                'mobile' => '090-4444-4444',
                'email' => 'kanshi.shiro@nikkei-hd.com',
                'postal_code' => '101-0047',
                'prefecture' => '東京都',
                'city' => '千代田区',
                'address_line1' => '内神田6-4-4',
                'address_line2' => 'シティハイム501',
                'emergency_contact' => json_encode([
                    'name' => '監視 和子',
                    'relationship' => '配偶者',
                    'phone' => '03-4444-5555',
                    'mobile' => '090-4444-5555'
                ]),
                'hire_date' => '2019-09-01',
                'employment_type' => 'full_time',
                'base_hourly_rate' => 1400,
                'overtime_rate' => 1750,
                'night_rate' => 1680,
                'holiday_rate' => 1860,
                'qualifications' => json_encode([
                    '警備員検定1級（雑踏警備業務）',
                    '警備員検定2級（施設警備業務）',
                    '普通自動車第一種運転免許',
                    '中型自動車第一種運転免許',
                    '普通救命講習修了'
                ]),
                'skills' => json_encode([
                    'イベント警備15年経験',
                    'コンサート・スポーツイベント',
                    '群衆管理',
                    '応急救護',
                    '中国語日常会話レベル'
                ]),
                'physical_condition' => json_encode([
                    'height' => 178,
                    'weight' => 75,
                    'blood_type' => 'AB',
                    'health_status' => 'good',
                    'restrictions' => null
                ]),
                'availability' => json_encode([
                    'day_shift' => true,
                    'night_shift' => false,
                    'weekend' => true,
                    'holiday' => true,
                    'overtime' => true
                ]),
                'preferred_locations' => json_encode([
                    '東京都全域',
                    '神奈川県横浜市',
                    '埼玉県さいたま市'
                ]),
                'security_clearance' => 'high',
                'uniform_size' => json_encode([
                    'jacket' => 'L',
                    'pants' => 'L',
                    'shirt' => 'L',
                    'shoes' => '27.5'
                ]),
                'performance_rating' => 4.6,
                'status' => 'active',
                'notes' => 'イベント警備のスペシャリスト。外国人対応も可能で大型イベントには欠かせない人材。',
                'created_at' => Carbon::now()->subMonths(10),
                'updated_at' => Carbon::now()
            ],
            [
                'guard_code' => 'G005',
                'company_id' => 2,
                'name' => '巡回 五郎',
                'name_kana' => 'ジュンカイ ゴロウ',
                'gender' => 'male',
                'birth_date' => '1985-11-25',
                'age' => 39,
                'phone' => '03-5555-5555',
                'mobile' => '090-5555-5555',
                'email' => 'junkai.goro@nikkei-hd.com',
                'postal_code' => '104-0045',
                'prefecture' => '東京都',
                'city' => '中央区',
                'address_line1' => '築地7-5-5',
                'address_line2' => 'パレス築地803',
                'emergency_contact' => json_encode([
                    'name' => '巡回 母',
                    'relationship' => '母',
                    'phone' => '03-5555-6666',
                    'mobile' => '090-5555-6666'
                ]),
                'hire_date' => '2020-11-15',
                'employment_type' => 'full_time',
                'base_hourly_rate' => 1350,
                'overtime_rate' => 1688,
                'night_rate' => 1620,
                'holiday_rate' => 1800,
                'qualifications' => json_encode([
                    '警備員検定2級（施設警備業務）',
                    '普通自動車第一種運転免許',
                    '自動二輪車運転免許',
                    '防火管理者',
                    '第一種電気工事士'
                ]),
                'skills' => json_encode([
                    '商業施設警備8年経験',
                    'ショッピングモール勤務',
                    '電気設備知識',
                    '機械警備システム',
                    '二輪車による巡回'
                ]),
                'physical_condition' => json_encode([
                    'height' => 172,
                    'weight' => 68,
                    'blood_type' => 'A',
                    'health_status' => 'excellent',
                    'restrictions' => null
                ]),
                'availability' => json_encode([
                    'day_shift' => true,
                    'night_shift' => true,
                    'weekend' => true,
                    'holiday' => true,
                    'overtime' => false
                ]),
                'preferred_locations' => json_encode([
                    '東京都23区内',
                    '神奈川県川崎市'
                ]),
                'security_clearance' => 'standard',
                'uniform_size' => json_encode([
                    'jacket' => 'M',
                    'pants' => 'M',
                    'shirt' => 'M',
                    'shoes' => '26.5'
                ]),
                'performance_rating' => 4.2,
                'status' => 'active',
                'notes' => '電気関係の知識があり、設備トラブル時の初期対応が可能。バイクでの巡回業務も得意。',
                'created_at' => Carbon::now()->subMonths(7),
                'updated_at' => Carbon::now()
            ],
            // ㈱全日本エンタープライズ所属警備員
            [
                'guard_code' => 'G006',
                'company_id' => 3,
                'name' => '防衛 六郎',
                'name_kana' => 'ボウエイ ロクロウ',
                'gender' => 'male',
                'birth_date' => '1973-02-28',
                'age' => 51,
                'phone' => '03-6666-6666',
                'mobile' => '090-6666-6666',
                'email' => 'bouei.rokuro@zennihon-ent.com',
                'postal_code' => '110-0005',
                'prefecture' => '東京都',
                'city' => '台東区',
                'address_line1' => '上野8-6-6',
                'address_line2' => '上野グランドハイツ201',
                'emergency_contact' => json_encode([
                    'name' => '防衛 京子',
                    'relationship' => '配偶者',
                    'phone' => '03-6666-7777',
                    'mobile' => '090-6666-7777'
                ]),
                'hire_date' => '2018-01-10',
                'employment_type' => 'full_time',
                'base_hourly_rate' => 1600,
                'overtime_rate' => 2000,
                'night_rate' => 1920,
                'holiday_rate' => 2130,
                'qualifications' => json_encode([
                    '警備員検定1級（施設警備業務）',
                    '警備員検定1級（交通誘導警備業務）',
                    '機械警備業務管理者',
                    '普通自動車第一種運転免許',
                    '危険物取扱者乙種全類',
                    '消防設備士甲種'
                ]),
                'skills' => json_encode([
                    '工場警備25年経験',
                    '化学プラント勤務',
                    '危険物取扱',
                    '消防設備点検',
                    '管理者経験'
                ]),
                'physical_condition' => json_encode([
                    'height' => 173,
                    'weight' => 72,
                    'blood_type' => 'B',
                    'health_status' => 'good',
                    'restrictions' => '高所作業不可'
                ]),
                'availability' => json_encode([
                    'day_shift' => true,
                    'night_shift' => true,
                    'weekend' => true,
                    'holiday' => true,
                    'overtime' => true
                ]),
                'preferred_locations' => json_encode([
                    '東京都全域',
                    '千葉県北西部',
                    '埼玉県南部'
                ]),
                'security_clearance' => 'very_high',
                'uniform_size' => json_encode([
                    'jacket' => 'L',
                    'pants' => 'L',
                    'shirt' => 'L',
                    'shoes' => '26.0'
                ]),
                'performance_rating' => 4.9,
                'status' => 'active',
                'notes' => '最高レベルの警備員。工場・プラント警備の専門家。新人教育も担当するベテラン。',
                'created_at' => Carbon::now()->subMonths(12),
                'updated_at' => Carbon::now()
            ],
            [
                'guard_code' => 'G007',
                'company_id' => 3,
                'name' => '警戒 七郎',
                'name_kana' => 'ケイカイ シチロウ',
                'gender' => 'male',
                'birth_date' => '1990-09-14',
                'age' => 34,
                'phone' => '03-7777-7777',
                'mobile' => '090-7777-7777',
                'email' => 'keikai.shichiro@zennihon-ent.com',
                'postal_code' => '134-0013',
                'prefecture' => '東京都',
                'city' => '江戸川区',
                'address_line1' => '江戸川9-7-7',
                'address_line2' => 'エクセル江戸川605',
                'emergency_contact' => json_encode([
                    'name' => '警戒 良子',
                    'relationship' => '配偶者',
                    'phone' => '03-7777-8888',
                    'mobile' => '090-7777-8888'
                ]),
                'hire_date' => '2021-03-20',
                'employment_type' => 'full_time',
                'base_hourly_rate' => 1250,
                'overtime_rate' => 1563,
                'night_rate' => 1500,
                'holiday_rate' => 1665,
                'qualifications' => json_encode([
                    '警備員検定2級（施設警備業務）',
                    '普通自動車第一種運転免許',
                    'フォークリフト運転技能講習修了',
                    '玉掛け技能講習修了'
                ]),
                'skills' => json_encode([
                    '物流警備6年経験',
                    '倉庫・配送センター',
                    'フォークリフト操作',
                    '荷役作業',
                    '在庫管理'
                ]),
                'physical_condition' => json_encode([
                    'height' => 176,
                    'weight' => 78,
                    'blood_type' => 'O',
                    'health_status' => 'excellent',
                    'restrictions' => null
                ]),
                'availability' => json_encode([
                    'day_shift' => false,
                    'night_shift' => true,
                    'weekend' => true,
                    'holiday' => true,
                    'overtime' => true
                ]),
                'preferred_locations' => json_encode([
                    '東京都東部',
                    '千葉県西部',
                    '埼玉県南東部'
                ]),
                'security_clearance' => 'standard',
                'uniform_size' => json_encode([
                    'jacket' => 'LL',
                    'pants' => 'LL',
                    'shirt' => 'LL',
                    'shoes' => '28.0'
                ]),
                'performance_rating' => 4.0,
                'status' => 'active',
                'notes' => '夜間勤務専門。物流関係の知識豊富で、倉庫業務のサポートも可能。体力に自信あり。',
                'created_at' => Carbon::now()->subMonths(5),
                'updated_at' => Carbon::now()
            ],
            // 女性警備員
            [
                'guard_code' => 'G008',
                'company_id' => 1,
                'name' => '保安 花子',
                'name_kana' => 'ホアン ハナコ',
                'gender' => 'female',
                'birth_date' => '1987-05-18',
                'age' => 37,
                'phone' => '03-8888-8888',
                'mobile' => '090-8888-8888',
                'email' => 'hoan.hanako@touo-keibi.com',
                'postal_code' => '116-0013',
                'prefecture' => '東京都',
                'city' => '荒川区',
                'address_line1' => '西日暮里2-8-8',
                'address_line2' => 'サンライズマンション302',
                'emergency_contact' => json_encode([
                    'name' => '保安 太郎',
                    'relationship' => '配偶者',
                    'phone' => '03-8888-9999',
                    'mobile' => '090-8888-9999'
                ]),
                'hire_date' => '2020-06-01',
                'employment_type' => 'part_time',
                'base_hourly_rate' => 1200,
                'overtime_rate' => 1500,
                'night_rate' => 1440,
                'holiday_rate' => 1600,
                'qualifications' => json_encode([
                    '警備員検定2級（施設警備業務）',
                    '普通自動車第一種運転免許',
                    '普通救命講習修了',
                    '介護職員初任者研修修了'
                ]),
                'skills' => json_encode([
                    '受付警備7年経験',
                    'オフィスビル・病院',
                    '接客対応',
                    '医療知識',
                    '英語日常会話レベル'
                ]),
                'physical_condition' => json_encode([
                    'height' => 158,
                    'weight' => 52,
                    'blood_type' => 'A',
                    'health_status' => 'excellent',
                    'restrictions' => null
                ]),
                'availability' => json_encode([
                    'day_shift' => true,
                    'night_shift' => false,
                    'weekend' => false,
                    'holiday' => false,
                    'overtime' => false
                ]),
                'preferred_locations' => json_encode([
                    '東京都23区内',
                    '埼玉県南部'
                ]),
                'security_clearance' => 'standard',
                'uniform_size' => json_encode([
                    'jacket' => 'S',
                    'pants' => 'S',
                    'shirt' => 'S',
                    'shoes' => '23.5'
                ]),
                'performance_rating' => 4.4,
                'status' => 'active',
                'notes' => '女性ならではの細やかな対応が評価されている。医療施設での勤務経験もあり。',
                'created_at' => Carbon::now()->subMonths(6),
                'updated_at' => Carbon::now()
            ],
            [
                'guard_code' => 'G009',
                'company_id' => 2,
                'name' => '監督 美咲',
                'name_kana' => 'カントク ミサキ',
                'gender' => 'female',
                'birth_date' => '1983-07-30',
                'age' => 41,
                'phone' => '03-9999-9999',
                'mobile' => '090-9999-9999',
                'email' => 'kantoku.misaki@nikkei-hd.com',
                'postal_code' => '107-0062',
                'prefecture' => '東京都',
                'city' => '港区',
                'address_line1' => '南青山3-9-9',
                'address_line2' => 'ブルーハイツ401',
                'emergency_contact' => json_encode([
                    'name' => '監督 健太',
                    'relationship' => '息子',
                    'phone' => '03-9999-0000',
                    'mobile' => '090-9999-0000'
                ]),
                'hire_date' => '2019-04-01',
                'employment_type' => 'full_time',
                'base_hourly_rate' => 1450,
                'overtime_rate' => 1813,
                'night_rate' => 1740,
                'holiday_rate' => 1930,
                'qualifications' => json_encode([
                    '警備員検定1級（雑踏警備業務）',
                    '警備員検定2級（施設警備業務）',
                    '普通自動車第一種運転免許',
                    '警備業務管理者',
                    '英語検定2級'
                ]),
                'skills' => json_encode([
                    'イベント警備12年経験',
                    '女性客対応専門',
                    '管理業務',
                    '外国語対応',
                    'チームリーダー経験'
                ]),
                'physical_condition' => json_encode([
                    'height' => 165,
                    'weight' => 55,
                    'blood_type' => 'AB',
                    'health_status' => 'good',
                    'restrictions' => null
                ]),
                'availability' => json_encode([
                    'day_shift' => true,
                    'night_shift' => false,
                    'weekend' => true,
                    'holiday' => true,
                    'overtime' => true
                ]),
                'preferred_locations' => json_encode([
                    '東京都23区内',
                    '神奈川県横浜市'
                ]),
                'security_clearance' => 'high',
                'uniform_size' => json_encode([
                    'jacket' => 'M',
                    'pants' => 'M',
                    'shirt' => 'M',
                    'shoes' => '24.0'
                ]),
                'performance_rating' => 4.7,
                'status' => 'active',
                'notes' => '女性チームのリーダー的存在。イベント時の女性客対応や現場管理で高い評価。',
                'created_at' => Carbon::now()->subMonths(9),
                'updated_at' => Carbon::now()
            ],
            // 新人・研修中の警備員
            [
                'guard_code' => 'G010',
                'company_id' => 1,
                'name' => '新人 一郎',
                'name_kana' => 'シンジン イチロウ',
                'gender' => 'male',
                'birth_date' => '1998-03-12',
                'age' => 26,
                'phone' => '03-0000-0000',
                'mobile' => '090-0000-0000',
                'email' => 'shinjin.ichiro@touo-keibi.com',
                'postal_code' => '123-0851',
                'prefecture' => '東京都',
                'city' => '足立区',
                'address_line1' => '梅田7-10-10',
                'address_line2' => 'フレッシュハイム101',
                'emergency_contact' => json_encode([
                    'name' => '新人 母',
                    'relationship' => '母',
                    'phone' => '03-0000-1111',
                    'mobile' => '090-0000-1111'
                ]),
                'hire_date' => '2024-04-01',
                'employment_type' => 'full_time',
                'base_hourly_rate' => 1000,
                'overtime_rate' => 1250,
                'night_rate' => 1200,
                'holiday_rate' => 1330,
                'qualifications' => json_encode([
                    '普通自動車第一種運転免許',
                    '警備員基本教育修了'
                ]),
                'skills' => json_encode([
                    '新卒採用',
                    '体力自慢',
                    'スポーツ経験（野球部主将）',
                    'PC操作基本レベル',
                    '向学心旺盛'
                ]),
                'physical_condition' => json_encode([
                    'height' => 180,
                    'weight' => 75,
                    'blood_type' => 'A',
                    'health_status' => 'excellent',
                    'restrictions' => null
                ]),
                'availability' => json_encode([
                    'day_shift' => true,
                    'night_shift' => true,
                    'weekend' => true,
                    'holiday' => true,
                    'overtime' => true
                ]),
                'preferred_locations' => json_encode([
                    '東京都全域',
                    '埼玉県全域'
                ]),
                'security_clearance' => 'basic',
                'uniform_size' => json_encode([
                    'jacket' => 'L',
                    'pants' => 'L',
                    'shirt' => 'L',
                    'shoes' => '27.0'
                ]),
                'performance_rating' => 3.5,
                'status' => 'training',
                'notes' => '新卒で入社したばかり。体力とやる気は十分。現在各種資格取得に向けて勉強中。',
                'created_at' => Carbon::now()->subMonths(1),
                'updated_at' => Carbon::now()
            ]
        ];

        // データを挿入
        DB::table('guards')->insert($guards);

        echo "Guards seeder completed. " . count($guards) . " guards created.\n";
    }
}
