<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 案件・プロジェクトテーブルのダミーデータ生成
 * 
 * 警備サービスの具体的な案件・プロジェクト情報を生成します。
 * 建設現場警備、施設警備、イベント警備等の多様な案件を想定。
 */
class ProjectsSeeder extends Seeder
{
    /**
     * ダミーデータを生成して挿入
     *
     * @return void
     */
    public function run(): void
    {
        $projects = [
            // 建設現場警備案件
            [
                'project_code' => 'PRJ001',
                'customer_id' => 1, // 東京建設株式会社
                'project_name' => '新宿駅前再開発ビル建設現場警備',
                'project_type' => 'construction',
                'description' => '新宿駅前大型再開発プロジェクトの建設現場における24時間警備業務。通行人の安全確保、資材盗難防止、作業員入退場管理を実施。',
                'location' => json_encode([
                    'postal_code' => '160-0023',
                    'prefecture' => '東京都',
                    'city' => '新宿区',
                    'address' => '西新宿1-15-20',
                    'building' => '新宿駅前再開発現場',
                    'access_info' => 'JR新宿駅南口徒歩3分'
                ]),
                'start_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(15)->format('Y-m-d'),
                'status' => 'active',
                'priority' => 'high',
                'contract_amount' => 12000000,
                'monthly_amount' => 800000,
                'guard_requirements' => json_encode([
                    'required_guards' => 4,
                    'shift_pattern' => '24時間3交代',
                    'required_qualifications' => ['警備員検定2級', '建設業関連講習修了'],
                    'special_skills' => ['交通誘導', '重機操作立会い'],
                    'uniform_requirements' => '工事現場用ヘルメット・安全ベスト着用'
                ]),
                'equipment_needed' => json_encode([
                    'communication_devices' => 'トランシーバー4台',
                    'safety_equipment' => 'ヘルメット、安全ベスト、安全靴',
                    'monitoring_equipment' => '防犯カメラ8台、録画装置',
                    'lighting' => '投光器4台',
                    'barriers' => 'カラーコーン50個、バリケード20台'
                ]),
                'client_contact' => json_encode([
                    'primary' => [
                        'name' => '建設 太郎',
                        'title' => '工事部長',
                        'phone' => '03-1111-2222',
                        'mobile' => '090-1111-2222',
                        'email' => 'kensetsu@tokyo-kensetsu.co.jp'
                    ],
                    'site_manager' => [
                        'name' => '現場 次郎',
                        'title' => '現場監督',
                        'phone' => '03-1111-3333',
                        'mobile' => '090-1111-3333',
                        'email' => 'genba@tokyo-kensetsu.co.jp'
                    ]
                ]),
                'special_instructions' => '朝夕の通勤ラッシュ時は特に注意。大型車両進入時は必ず誘導員配置。月1回安全講習会実施。',
                'risk_assessment' => json_encode([
                    'traffic_risk' => 'high',
                    'theft_risk' => 'medium',
                    'accident_risk' => 'high',
                    'weather_risk' => 'medium'
                ]),
                'created_at' => Carbon::now()->subMonths(4),
                'updated_at' => Carbon::now()
            ],
            [
                'project_code' => 'PRJ002',
                'customer_id' => 2, // 関東土木工業株式会社
                'project_name' => '環状7号線道路工事夜間警備',
                'project_type' => 'construction',
                'description' => '環状7号線の舗装工事に伴う夜間交通規制および工事現場警備。車両誘導、歩行者安全確保、作業員安全管理を実施。',
                'location' => json_encode([
                    'postal_code' => '130-0005',
                    'prefecture' => '東京都',
                    'city' => '墨田区',
                    'address' => '環状7号線 墨田区内',
                    'building' => '道路工事現場（延長約2km）',
                    'access_info' => '複数箇所での交通誘導ポイント'
                ]),
                'start_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(4)->format('Y-m-d'),
                'status' => 'active',
                'priority' => 'high',
                'contract_amount' => 6000000,
                'monthly_amount' => 1200000,
                'guard_requirements' => json_encode([
                    'required_guards' => 6,
                    'shift_pattern' => '夜間のみ（21:00-6:00）',
                    'required_qualifications' => ['交通誘導警備業務検定1級'],
                    'special_skills' => ['夜間工事立会い', '大型車両誘導'],
                    'uniform_requirements' => '夜光反射材付き警備服、LED点滅ライト'
                ]),
                'equipment_needed' => json_encode([
                    'communication_devices' => 'トランシーバー8台',
                    'safety_equipment' => '夜光ベスト、ヘルメット、LEDライト',
                    'traffic_control' => '電光掲示板2台、誘導棒12本',
                    'barriers' => 'カラーコーン100個、保安柵50m',
                    'vehicle' => '移動式警備車両1台'
                ]),
                'client_contact' => json_encode([
                    'primary' => [
                        'name' => '土木 次郎',
                        'title' => '現場監督',
                        'phone' => '03-2222-3333',
                        'mobile' => '090-2222-3333',
                        'email' => 'doboku@kanto-doboku.co.jp'
                    ]
                ]),
                'special_instructions' => '交通量の多い幹線道路のため細心の注意が必要。警察との連携必須。天候不良時は工事中止の場合あり。',
                'risk_assessment' => json_encode([
                    'traffic_risk' => 'very_high',
                    'theft_risk' => 'low',
                    'accident_risk' => 'high',
                    'weather_risk' => 'high'
                ]),
                'created_at' => Carbon::now()->subMonths(2),
                'updated_at' => Carbon::now()
            ],
            // 施設警備案件
            [
                'project_code' => 'PRJ003',
                'customer_id' => 3, // 新宿プロパティーズ株式会社
                'project_name' => '新宿センタービル24時間警備',
                'project_type' => 'facility',
                'description' => '25階建てオフィスビルの24時間常駐警備。受付業務、巡回警備、入退館管理、防災センター業務を実施。',
                'location' => json_encode([
                    'postal_code' => '160-0022',
                    'prefecture' => '東京都',
                    'city' => '新宿区',
                    'address' => '新宿3-3-3',
                    'building' => '新宿センタービル',
                    'access_info' => 'JR新宿駅東口徒歩5分、地下街直結'
                ]),
                'start_date' => Carbon::now()->subMonths(6)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(18)->format('Y-m-d'),
                'status' => 'active',
                'priority' => 'high',
                'contract_amount' => 24000000,
                'monthly_amount' => 1000000,
                'guard_requirements' => json_encode([
                    'required_guards' => 3,
                    'shift_pattern' => '24時間3交代（8時間×3シフト）',
                    'required_qualifications' => ['施設警備業務検定1級', '防災センター要員'],
                    'special_skills' => ['受付対応', '英語対応可能', 'PC操作'],
                    'uniform_requirements' => 'ビジネススーツ、名札、無線機'
                ]),
                'equipment_needed' => json_encode([
                    'communication_devices' => '無線機3台、内線電話',
                    'monitoring_equipment' => '防犯カメラ監視システム、火災報知機',
                    'access_control' => 'ICカード管理システム、金属探知機',
                    'patrol_equipment' => '巡回記録システム、懐中電灯',
                    'emergency_equipment' => 'AED、救急箱、防災用品'
                ]),
                'client_contact' => json_encode([
                    'primary' => [
                        'name' => '不動産 三郎',
                        'title' => '管理部課長',
                        'phone' => '03-3333-4444',
                        'mobile' => '090-3333-4444',
                        'email' => 'kanri@shinjuku-properties.co.jp'
                    ],
                    'facility_manager' => [
                        'name' => '施設 四郎',
                        'title' => '施設管理責任者',
                        'phone' => '03-3333-5555',
                        'mobile' => '090-3333-5555',
                        'email' => 'shisetsu@shinjuku-properties.co.jp'
                    ]
                ]),
                'special_instructions' => 'VIP来訪時は特別対応。テナント企業の機密保持厳守。月1回避難訓練実施。',
                'risk_assessment' => json_encode([
                    'security_risk' => 'medium',
                    'fire_risk' => 'medium',
                    'earthquake_risk' => 'high',
                    'terrorism_risk' => 'low'
                ]),
                'created_at' => Carbon::now()->subMonths(7),
                'updated_at' => Carbon::now()
            ],
            [
                'project_code' => 'PRJ004',
                'customer_id' => 4, // 渋谷ショッピングモール株式会社
                'project_name' => '渋谷モール巡回・監視警備',
                'project_type' => 'facility',
                'description' => '大型ショッピングモールの開館時間中の巡回警備および監視業務。万引き防止、客層管理、緊急時対応を実施。',
                'location' => json_encode([
                    'postal_code' => '150-0043',
                    'prefecture' => '東京都',
                    'city' => '渋谷区',
                    'address' => '道玄坂4-4-4',
                    'building' => '渋谷ショッピングモール（地下2階～地上8階）',
                    'access_info' => 'JR渋谷駅ハチ公口徒歩3分'
                ]),
                'start_date' => Carbon::now()->subMonths(8)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(16)->format('Y-m-d'),
                'status' => 'active',
                'priority' => 'medium',
                'contract_amount' => 18000000,
                'monthly_amount' => 750000,
                'guard_requirements' => json_encode([
                    'required_guards' => 5,
                    'shift_pattern' => '開館中（10:00-22:00）',
                    'required_qualifications' => ['雑踏警備業務検定2級'],
                    'special_skills' => ['接客対応', '英語・中国語対応', '店舗管理'],
                    'uniform_requirements' => 'カジュアルスーツ、モール制服'
                ]),
                'equipment_needed' => json_encode([
                    'communication_devices' => 'ヘッドセット5台、スマートフォン',
                    'monitoring_equipment' => 'モバイル監視システム、ハンディカメラ',
                    'safety_equipment' => '応急処置キット、迷子対応セット',
                    'patrol_equipment' => 'チェックポイントシステム',
                    'crowd_control' => 'ロープ、立て看板'
                ]),
                'client_contact' => json_encode([
                    'primary' => [
                        'name' => '商業 四郎',
                        'title' => 'テナント管理部長',
                        'phone' => '03-4444-5555',
                        'mobile' => '090-4444-5555',
                        'email' => 'tenant@shibuya-mall.co.jp'
                    ]
                ]),
                'special_instructions' => '土日祝日は特に混雑。セール期間中は警備強化。外国人観光客対応重要。',
                'risk_assessment' => json_encode([
                    'shoplifting_risk' => 'high',
                    'crowd_risk' => 'high',
                    'fire_risk' => 'medium',
                    'earthquake_risk' => 'high'
                ]),
                'created_at' => Carbon::now()->subMonths(9),
                'updated_at' => Carbon::now()
            ],
            // イベント警備案件
            [
                'project_code' => 'PRJ005',
                'customer_id' => 5, // 東京イベントプロダクション株式会社
                'project_name' => '東京ドーム年末コンサート警備',
                'project_type' => 'event',
                'description' => '年末の大型コンサートイベントにおける会場警備。入場管理、観客誘導、ステージ警備、緊急時対応を実施。',
                'location' => json_encode([
                    'postal_code' => '112-0004',
                    'prefecture' => '東京都',
                    'city' => '文京区',
                    'address' => '後楽1-3-61',
                    'building' => '東京ドーム',
                    'access_info' => 'JR水道橋駅徒歩5分、地下鉄後楽園駅徒歩3分'
                ]),
                'start_date' => Carbon::now()->addMonths(7)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(7)->addDays(2)->format('Y-m-d'),
                'status' => 'planned',
                'priority' => 'high',
                'contract_amount' => 3000000,
                'monthly_amount' => 3000000,
                'guard_requirements' => json_encode([
                    'required_guards' => 50,
                    'shift_pattern' => '3日間集中配置',
                    'required_qualifications' => ['雑踏警備業務検定1級', 'イベント警備経験'],
                    'special_skills' => ['群衆心理理解', '緊急時対応', '英語対応'],
                    'uniform_requirements' => 'イベント専用制服、IDカード'
                ]),
                'equipment_needed' => json_encode([
                    'communication_devices' => '無線機50台、ヘッドセット',
                    'crowd_control' => 'バリケード100台、ロープ500m',
                    'safety_equipment' => 'メガホン10台、応急処置キット',
                    'monitoring_equipment' => '移動式カメラ5台',
                    'emergency_equipment' => 'AED5台、担架3台'
                ]),
                'client_contact' => json_encode([
                    'primary' => [
                        'name' => 'イベント 五郎',
                        'title' => 'プロデューサー',
                        'phone' => '03-5555-6666',
                        'mobile' => '090-5555-6666',
                        'email' => 'event@tokyo-event.co.jp'
                    ]
                ]),
                'special_instructions' => '著名アーティストのため警備レベル最高。ファンの熱狂に注意。報道陣対応も重要。',
                'risk_assessment' => json_encode([
                    'crowd_risk' => 'very_high',
                    'security_risk' => 'high',
                    'weather_risk' => 'low',
                    'terrorism_risk' => 'medium'
                ]),
                'created_at' => Carbon::now()->subMonths(1),
                'updated_at' => Carbon::now()
            ],
            [
                'project_code' => 'PRJ006',
                'customer_id' => 6, // 株式会社フェスティバル企画
                'project_name' => 'お台場音楽フェスティバル警備',
                'project_type' => 'event',
                'description' => '3日間のアウトドア音楽フェスティバル警備。会場設営時から撤収まで、ステージ周辺、観客エリア、駐車場の総合警備。',
                'location' => json_encode([
                    'postal_code' => '135-0091',
                    'prefecture' => '東京都',
                    'city' => '港区',
                    'address' => 'お台場海浜公園',
                    'building' => '特設会場',
                    'access_info' => 'ゆりかもめお台場海浜公園駅徒歩3分'
                ]),
                'start_date' => Carbon::now()->addMonths(4)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(4)->addDays(5)->format('Y-m-d'),
                'status' => 'planned',
                'priority' => 'medium',
                'contract_amount' => 2500000,
                'monthly_amount' => 2500000,
                'guard_requirements' => json_encode([
                    'required_guards' => 30,
                    'shift_pattern' => '6日間（設営2日+開催3日+撤収1日）',
                    'required_qualifications' => ['雑踏警備業務検定2級'],
                    'special_skills' => ['屋外イベント経験', '音響機材知識', '天候対応'],
                    'uniform_requirements' => '屋外作業服、雨具、フェス特製Tシャツ'
                ]),
                'equipment_needed' => json_encode([
                    'communication_devices' => '防水無線機30台',
                    'weather_protection' => 'テント5張、雨具一式',
                    'crowd_control' => 'フェンス200m、ゲート10箇所',
                    'safety_equipment' => '応急処置テント、熱中症対策用品',
                    'vehicle' => 'ATV（四輪バギー）2台'
                ]),
                'client_contact' => json_encode([
                    'primary' => [
                        'name' => 'フェス 六郎',
                        'title' => '運営部長',
                        'phone' => '03-6666-7777',
                        'mobile' => '090-6666-7777',
                        'email' => 'unei@festival-plan.co.jp'
                    ]
                ]),
                'special_instructions' => '屋外のため天候対応重要。アルコール提供あり酔客対応注意。環境配慮でゴミ分別指導も実施。',
                'risk_assessment' => json_encode([
                    'weather_risk' => 'high',
                    'crowd_risk' => 'medium',
                    'alcohol_risk' => 'medium',
                    'environmental_risk' => 'medium'
                ]),
                'created_at' => Carbon::now()->subWeeks(2),
                'updated_at' => Carbon::now()
            ],
            // 工場・物流警備案件
            [
                'project_code' => 'PRJ007',
                'customer_id' => 7, // 東京製鉄株式会社
                'project_name' => '東京製鉄工場24時間警備',
                'project_type' => 'industrial',
                'description' => '大規模製鉄工場の24時間警備。入退場管理、設備監視、火災予防、産業スパイ防止、安全管理を実施。',
                'location' => json_encode([
                    'postal_code' => '120-0005',
                    'prefecture' => '東京都',
                    'city' => '足立区',
                    'address' => '綾瀬7-7-7',
                    'building' => '東京製鉄工場（敷地面積50万㎡）',
                    'access_info' => 'JR常磐線綾瀬駅バス15分'
                ]),
                'start_date' => Carbon::now()->subMonths(12)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(12)->format('Y-m-d'),
                'status' => 'active',
                'priority' => 'very_high',
                'contract_amount' => 36000000,
                'monthly_amount' => 1500000,
                'guard_requirements' => json_encode([
                    'required_guards' => 8,
                    'shift_pattern' => '24時間4交代（6時間×4シフト）',
                    'required_qualifications' => ['施設警備業務検定1級', '危険物取扱者乙種'],
                    'special_skills' => ['工場設備知識', '緊急時対応', '化学物質取扱'],
                    'uniform_requirements' => '工場専用作業服、安全靴、ヘルメット、防毒マスク'
                ]),
                'equipment_needed' => json_encode([
                    'communication_devices' => '防爆無線機8台、緊急通報システム',
                    'monitoring_equipment' => '赤外線カメラ20台、ガス検知器',
                    'safety_equipment' => '消火器、防毒マスク、酸素ボンベ',
                    'access_control' => '生体認証システム、車両ゲート',
                    'patrol_equipment' => '防爆懐中電灯、ガス検知器'
                ]),
                'client_contact' => json_encode([
                    'primary' => [
                        'name' => '製鉄 七郎',
                        'title' => '工場長',
                        'phone' => '03-7777-8888',
                        'mobile' => '090-7777-8888',
                        'email' => 'factory@tokyo-steel.co.jp'
                    ],
                    'safety_manager' => [
                        'name' => '安全 八郎',
                        'title' => '安全管理責任者',
                        'phone' => '03-7777-9999',
                        'mobile' => '090-7777-9999',
                        'email' => 'safety@tokyo-steel.co.jp'
                    ]
                ]),
                'special_instructions' => '高温設備・有害ガス注意。機密情報多数のため身元調査必須。月1回安全講習受講義務。',
                'risk_assessment' => json_encode([
                    'fire_risk' => 'very_high',
                    'chemical_risk' => 'high',
                    'security_risk' => 'high',
                    'industrial_accident_risk' => 'high'
                ]),
                'created_at' => Carbon::now()->subMonths(15),
                'updated_at' => Carbon::now()
            ],
            [
                'project_code' => 'PRJ008',
                'customer_id' => 8, // 関東ロジスティクス株式会社
                'project_name' => '関東ロジセンター夜間警備',
                'project_type' => 'logistics',
                'description' => '大型物流センターの夜間警備業務。荷物の盗難防止、トラック入退場管理、冷凍倉庫監視を実施。',
                'location' => json_encode([
                    'postal_code' => '134-0088',
                    'prefecture' => '東京都',
                    'city' => '江戸川区',
                    'address' => '西葛西8-8-8',
                    'building' => '関東ロジスティクスセンター（倉庫5棟）',
                    'access_info' => '東西線西葛西駅バス10分'
                ]),
                'start_date' => Carbon::now()->subMonths(5)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(19)->format('Y-m-d'),
                'status' => 'active',
                'priority' => 'high',
                'contract_amount' => 15000000,
                'monthly_amount' => 625000,
                'guard_requirements' => json_encode([
                    'required_guards' => 4,
                    'shift_pattern' => '夜間のみ（18:00-8:00）',
                    'required_qualifications' => ['施設警備業務検定2級', 'フォークリフト運転技能'],
                    'special_skills' => ['物流業務知識', '低温環境作業', '荷役作業'],
                    'uniform_requirements' => '防寒作業服、安全靴、防寒帽'
                ]),
                'equipment_needed' => json_encode([
                    'communication_devices' => '無線機4台、IP電話',
                    'monitoring_equipment' => '防犯カメラ30台、温度監視システム',
                    'safety_equipment' => '防寒具、滑り止め靴、応急処置キット',
                    'access_control' => 'トラックスケール、車両認証システム',
                    'patrol_equipment' => '台車、検品用具'
                ]),
                'client_contact' => json_encode([
                    'primary' => [
                        'name' => '物流 八郎',
                        'title' => '倉庫管理部長',
                        'phone' => '03-8888-9999',
                        'mobile' => '090-8888-9999',
                        'email' => 'souko@kanto-logistics.co.jp'
                    ]
                ]),
                'special_instructions' => '冷凍食品取扱いのため温度管理重要。夜間配送トラック多数のため交通整理も実施。',
                'risk_assessment' => json_encode([
                    'theft_risk' => 'high',
                    'temperature_risk' => 'medium',
                    'traffic_risk' => 'medium',
                    'slip_risk' => 'high'
                ]),
                'created_at' => Carbon::now()->subMonths(6),
                'updated_at' => Carbon::now()
            ],
            // 完了済み案件の例
            [
                'project_code' => 'PRJ009',
                'customer_id' => 5, // 東京イベントプロダクション株式会社
                'project_name' => '東京マラソン2024警備業務',
                'project_type' => 'event',
                'description' => '東京マラソン大会における沿道警備、ランナー安全確保、観客誘導業務。42.195kmのコース全域をカバー。',
                'location' => json_encode([
                    'postal_code' => '100-0005',
                    'prefecture' => '東京都',
                    'city' => '千代田区',
                    'address' => '東京都庁前～東京駅前',
                    'building' => '東京マラソンコース全域',
                    'access_info' => '都内各所（複数ポイント）'
                ]),
                'start_date' => Carbon::now()->subMonths(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->subMonths(10)->addDays(1)->format('Y-m-d'),
                'status' => 'completed',
                'priority' => 'high',
                'contract_amount' => 5000000,
                'monthly_amount' => 5000000,
                'guard_requirements' => json_encode([
                    'required_guards' => 100,
                    'shift_pattern' => '1日集中配置',
                    'required_qualifications' => ['雑踏警備業務検定1級'],
                    'special_skills' => ['マラソン大会経験', '応急処置', '多言語対応'],
                    'uniform_requirements' => '大会公式ベスト、IDカード'
                ]),
                'equipment_needed' => json_encode([
                    'communication_devices' => '無線機100台、ヘッドセット',
                    'medical_equipment' => 'AED20台、救急セット',
                    'crowd_control' => 'バリケード500台、立て看板',
                    'safety_equipment' => '熱中症対策用品、給水セット'
                ]),
                'client_contact' => json_encode([
                    'primary' => [
                        'name' => 'イベント 五郎',
                        'title' => 'プロデューサー',
                        'phone' => '03-5555-6666',
                        'mobile' => '090-5555-6666',
                        'email' => 'event@tokyo-event.co.jp'
                    ]
                ]),
                'special_instructions' => '国際的大会のため多言語対応必須。報道関係者多数。警察・消防との連携重要。',
                'risk_assessment' => json_encode([
                    'crowd_risk' => 'very_high',
                    'medical_risk' => 'high',
                    'weather_risk' => 'medium',
                    'terrorism_risk' => 'medium'
                ]),
                'created_at' => Carbon::now()->subMonths(12),
                'updated_at' => Carbon::now()->subMonths(10)
            ],
            [
                'project_code' => 'PRJ010',
                'customer_id' => 10, // 個人事業主 田園調布太郎
                'project_name' => '田園調布高級住宅警備',
                'project_type' => 'residential',
                'description' => '高級住宅地における個人宅警備。来訪者管理、敷地内巡回、プライバシー保護を重視した警備業務。',
                'location' => json_encode([
                    'postal_code' => '145-0071',
                    'prefecture' => '東京都',
                    'city' => '大田区',
                    'address' => '田園調布10-10-10',
                    'building' => '田園調布邸宅',
                    'access_info' => '東急東横線田園調布駅徒歩8分'
                ]),
                'start_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(11)->format('Y-m-d'),
                'status' => 'active',
                'priority' => 'medium',
                'contract_amount' => 6000000,
                'monthly_amount' => 500000,
                'guard_requirements' => json_encode([
                    'required_guards' => 1,
                    'shift_pattern' => '日中のみ（9:00-18:00）',
                    'required_qualifications' => ['施設警備業務検定2級'],
                    'special_skills' => ['高級住宅地対応', '接客マナー', '機密保持'],
                    'uniform_requirements' => 'スーツ、控えめな警備表示'
                ]),
                'equipment_needed' => json_encode([
                    'communication_devices' => '携帯電話、インターホン',
                    'monitoring_equipment' => '防犯カメラ4台、センサー',
                    'access_control' => '電子錠、来客記録システム',
                    'patrol_equipment' => '懐中電灯、巡回記録'
                ]),
                'client_contact' => json_encode([
                    'primary' => [
                        'name' => '田園調布 太郎',
                        'title' => '代表',
                        'phone' => '03-0000-1111',
                        'mobile' => '090-0000-1111',
                        'email' => 'denenchofu@personal.com'
                    ]
                ]),
                'special_instructions' => '近隣への配慮必須。報道関係者対応注意。家族のプライバシー最優先。',
                'risk_assessment' => json_encode([
                    'privacy_risk' => 'high',
                    'intrusion_risk' => 'medium',
                    'stalker_risk' => 'medium',
                    'paparazzi_risk' => 'low'
                ]),
                'created_at' => Carbon::now()->subMonths(2),
                'updated_at' => Carbon::now()
            ]
        ];

        // データを挿入
        DB::table('projects')->insert($projects);

        echo "Projects seeder completed. " . count($projects) . " projects created.\n";
    }
}
