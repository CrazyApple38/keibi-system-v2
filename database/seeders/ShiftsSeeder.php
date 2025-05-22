<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * シフトテーブルのダミーデータ生成
 * 
 * 各プロジェクトに対応したシフト情報を生成します。
 * 日勤、夜勤、24時間体制等、様々なシフトパターンを想定。
 */
class ShiftsSeeder extends Seeder
{
    /**
     * ダミーデータを生成して挿入
     *
     * @return void
     */
    public function run(): void
    {
        $shifts = [
            // PRJ001: 新宿駅前再開発ビル建設現場警備（24時間3交代）
            [
                'shift_code' => 'SH001',
                'project_id' => 1,
                'shift_name' => '新宿建設現場 日勤シフト',
                'shift_type' => 'day',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'break_time' => 60,
                'required_guards' => 2,
                'shift_date' => Carbon::today()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '新宿駅前再開発現場',
                    'floor' => '地上部分',
                    'area' => '正面入口・資材置場'
                ]),
                'special_instructions' => '朝の通勤ラッシュ時間帯（7:30-9:00）は特に歩行者への注意喚起を徹底。大型車両進入時は必ず誘導実施。',
                'required_qualifications' => json_encode([
                    '警備員検定2級',
                    '建設業関連講習修了',
                    '交通誘導経験'
                ]),
                'hourly_rate' => 1500,
                'overtime_rate' => 1875,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'rain' => '雨天時は滑りやすいため足元注意',
                    'wind' => '強風時は看板・資材の飛散注意',
                    'heat' => '夏場は熱中症対策必須'
                ]),
                'emergency_procedures' => json_encode([
                    'accident' => '119番通報後、現場監督へ連絡',
                    'fire' => '消防署通報、避難誘導実施',
                    'security' => '110番通報、本社警備部へ報告'
                ]),
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()
            ],
            [
                'shift_code' => 'SH002',
                'project_id' => 1,
                'shift_name' => '新宿建設現場 夜勤シフト',
                'shift_type' => 'night',
                'start_time' => '16:00:00',
                'end_time' => '00:00:00',
                'break_time' => 60,
                'required_guards' => 2,
                'shift_date' => Carbon::today()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '新宿駅前再開発現場',
                    'floor' => '全域',
                    'area' => '現場全体・機材保管エリア'
                ]),
                'special_instructions' => '夜間は資材盗難に特に注意。2時間おきの巡回必須。照明設備の点検も実施。',
                'required_qualifications' => json_encode([
                    '警備員検定2級',
                    '夜間警備経験',
                    '懐中電灯操作技能'
                ]),
                'hourly_rate' => 1800,
                'overtime_rate' => 2250,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'visibility' => '夜間の視界確保重要',
                    'temperature' => '夜間の気温変化に対応',
                    'lighting' => '照明設備の定期確認'
                ]),
                'emergency_procedures' => json_encode([
                    'intrusion' => '不審者発見時は110番通報',
                    'equipment_theft' => '盗難発見時は警察・顧客へ即座に連絡',
                    'fire' => '119番通報、近隣避難誘導'
                ]),
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()
            ],
            [
                'shift_code' => 'SH003',
                'project_id' => 1,
                'shift_name' => '新宿建設現場 深夜シフト',
                'shift_type' => 'midnight',
                'start_time' => '00:00:00',
                'end_time' => '08:00:00',
                'break_time' => 60,
                'required_guards' => 1,
                'shift_date' => Carbon::today()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '新宿駅前再開発現場',
                    'floor' => '全域',
                    'area' => '警備詰所中心の巡回警備'
                ]),
                'special_instructions' => '深夜帯の単独警備。緊急時は即座に応援要請。体調管理に十分注意。',
                'required_qualifications' => json_encode([
                    '警備員検定2級',
                    '深夜警備経験3年以上',
                    '緊急時対応能力'
                ]),
                'hourly_rate' => 2000,
                'overtime_rate' => 2500,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'safety' => '深夜の安全確保最優先',
                    'health' => '体調管理・仮眠時間確保',
                    'communication' => '定時連絡の徹底'
                ]),
                'emergency_procedures' => json_encode([
                    'emergency' => '緊急時は119/110番通報後、本社24時間受付へ連絡',
                    'health' => '体調不良時は即座に交代要請',
                    'communication' => '2時間おきの安否確認必須'
                ]),
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()
            ],
            // PRJ002: 環状7号線道路工事夜間警備
            [
                'shift_code' => 'SH004',
                'project_id' => 2,
                'shift_name' => '環状7号線工事 夜間シフトA',
                'shift_type' => 'night',
                'start_time' => '21:00:00',
                'end_time' => '06:00:00',
                'break_time' => 60,
                'required_guards' => 3,
                'shift_date' => Carbon::today()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '環状7号線道路工事現場',
                    'area' => '北側工事区間（1km区間）',
                    'positions' => '交通誘導3ポイント'
                ]),
                'special_instructions' => '夜間の幹線道路での交通誘導。反射材着用必須。大型車両通行多数のため細心の注意。',
                'required_qualifications' => json_encode([
                    '交通誘導警備業務検定1級',
                    '夜間工事経験',
                    '大型車両誘導経験'
                ]),
                'hourly_rate' => 2000,
                'overtime_rate' => 2500,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'rain' => '雨天時は工事中止の可能性あり',
                    'wind' => '強風時は看板・標識の固定確認',
                    'visibility' => '夜間の視界確保が最重要'
                ]),
                'emergency_procedures' => json_encode([
                    'accident' => '事故発生時は119番通報、工事中止、迂回路設定',
                    'traffic' => '交通渋滞発生時は警察へ連絡',
                    'equipment' => '機材故障時は工事責任者へ即座に連絡'
                ]),
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()
            ],
            [
                'shift_code' => 'SH005',
                'project_id' => 2,
                'shift_name' => '環状7号線工事 夜間シフトB',
                'shift_type' => 'night',
                'start_time' => '21:00:00',
                'end_time' => '06:00:00',
                'break_time' => 60,
                'required_guards' => 3,
                'shift_date' => Carbon::today()->addDay()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '環状7号線道路工事現場',
                    'area' => '南側工事区間（1km区間）',
                    'positions' => '交通誘導3ポイント'
                ]),
                'special_instructions' => '前日シフトAと同様の対応。工事進捗により誘導位置変更の可能性あり。',
                'required_qualifications' => json_encode([
                    '交通誘導警備業務検定1級',
                    '夜間工事経験',
                    '柔軟な対応能力'
                ]),
                'hourly_rate' => 2000,
                'overtime_rate' => 2500,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'flexibility' => '天候に応じた臨機応変な対応',
                    'safety' => '安全第一の判断',
                    'communication' => '工事責任者との密な連絡'
                ]),
                'emergency_procedures' => json_encode([
                    'continuation' => '前日と同様の緊急時対応',
                    'adaptation' => '状況変化への迅速な対応',
                    'coordination' => '関係者との連携強化'
                ]),
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()
            ],
            // PRJ003: 新宿センタービル24時間警備
            [
                'shift_code' => 'SH006',
                'project_id' => 3,
                'shift_name' => 'センタービル 日勤シフト',
                'shift_type' => 'day',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'break_time' => 60,
                'required_guards' => 1,
                'shift_date' => Carbon::today()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '新宿センタービル',
                    'floor' => '1F受付・防災センター',
                    'area' => 'エントランス・エレベーターホール'
                ]),
                'special_instructions' => 'ビジネス時間帯の受付業務中心。来客対応、入退館管理、郵便物受取等も担当。',
                'required_qualifications' => json_encode([
                    '施設警備業務検定1級',
                    '防災センター要員',
                    '接客経験',
                    'PC操作能力'
                ]),
                'hourly_rate' => 1400,
                'overtime_rate' => 1750,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'comfort' => '屋内勤務のため天候影響少',
                    'visitor' => '悪天候時の来客対応配慮',
                    'building' => 'ビル設備への天候影響監視'
                ]),
                'emergency_procedures' => json_encode([
                    'fire' => '火災報知器作動時は館内放送、避難誘導',
                    'earthquake' => '地震時はエレベーター停止、避難経路確保',
                    'security' => '不審者・不審物発見時は警察通報'
                ]),
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()
            ],
            [
                'shift_code' => 'SH007',
                'project_id' => 3,
                'shift_name' => 'センタービル 夜勤シフト',
                'shift_type' => 'night',
                'start_time' => '16:00:00',
                'end_time' => '00:00:00',
                'break_time' => 60,
                'required_guards' => 1,
                'shift_date' => Carbon::today()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '新宿センタービル',
                    'floor' => '全館（1F-25F）',
                    'area' => '巡回警備・防災センター監視'
                ]),
                'special_instructions' => '夜間の巡回警備が中心。各フロア2時間おきの巡回、防犯カメラ監視、設備点検。',
                'required_qualifications' => json_encode([
                    '施設警備業務検定1級',
                    '防災センター要員',
                    '夜間巡回経験',
                    '設備点検知識'
                ]),
                'hourly_rate' => 1600,
                'overtime_rate' => 2000,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'building' => 'ビル設備の夜間監視',
                    'security' => '夜間の防犯対策強化',
                    'maintenance' => '設備異常の早期発見'
                ]),
                'emergency_procedures' => json_encode([
                    'intrusion' => '不審者侵入時は110番通報、館内確認',
                    'equipment' => '設備異常時は管理会社へ連絡',
                    'medical' => '急病人発生時は119番通報、応急処置'
                ]),
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()
            ],
            [
                'shift_code' => 'SH008',
                'project_id' => 3,
                'shift_name' => 'センタービル 深夜シフト',
                'shift_type' => 'midnight',
                'start_time' => '00:00:00',
                'end_time' => '08:00:00',
                'break_time' => 60,
                'required_guards' => 1,
                'shift_date' => Carbon::today()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '新宿センタービル',
                    'floor' => '防災センター常駐',
                    'area' => '監視システム・緊急対応待機'
                ]),
                'special_instructions' => '深夜帯の防災センター常駐。監視カメラ確認、警報対応、清掃業者入館管理。',
                'required_qualifications' => json_encode([
                    '施設警備業務検定1級',
                    '防災センター要員',
                    '深夜勤務経験',
                    '緊急時対応能力'
                ]),
                'hourly_rate' => 1800,
                'overtime_rate' => 2250,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'monitoring' => '深夜の監視体制維持',
                    'response' => '緊急時の迅速な対応',
                    'health' => '夜勤者の健康管理'
                ]),
                'emergency_procedures' => json_encode([
                    'fire_alarm' => '火災警報時は消防署通報、現場確認',
                    'security_alarm' => '防犯警報時は警察通報、現場確認',
                    'medical' => '体調不良時は本部へ連絡、交代要請'
                ]),
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()
            ],
            // PRJ004: 渋谷ショッピングモール巡回・監視警備
            [
                'shift_code' => 'SH009',
                'project_id' => 4,
                'shift_name' => '渋谷モール 開店シフト',
                'shift_type' => 'day',
                'start_time' => '09:00:00',
                'end_time' => '15:00:00',
                'break_time' => 60,
                'required_guards' => 2,
                'shift_date' => Carbon::today()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '渋谷ショッピングモール',
                    'floor' => '1F-8F',
                    'area' => 'フロア巡回・客層監視'
                ]),
                'special_instructions' => '開店から午後まで。平日は比較的客数少なめ。万引き防止、迷子対応、店舗サポート。',
                'required_qualifications' => json_encode([
                    '雑踏警備業務検定2級',
                    '商業施設経験',
                    '接客対応能力',
                    '英語日常会話'
                ]),
                'hourly_rate' => 1200,
                'overtime_rate' => 1500,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'customer' => '悪天候時の客足変化対応',
                    'safety' => '雨天時の床面滑り注意',
                    'comfort' => '館内温度・湿度の快適性確保'
                ]),
                'emergency_procedures' => json_encode([
                    'shoplifting' => '万引き発見時は店舗責任者と連携',
                    'lost_child' => '迷子発見時は館内放送、保護者探索',
                    'medical' => '急病人発生時は救護室へ搬送、119番通報'
                ]),
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()
            ],
            [
                'shift_code' => 'SH010',
                'project_id' => 4,
                'shift_name' => '渋谷モール 夕方シフト',
                'shift_type' => 'evening',
                'start_time' => '15:00:00',
                'end_time' => '22:00:00',
                'break_time' => 60,
                'required_guards' => 3,
                'shift_date' => Carbon::today()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '渋谷ショッピングモール',
                    'floor' => '全館（B2F-8F）',
                    'area' => '全フロア巡回・閉店業務'
                ]),
                'special_instructions' => '夕方から閉店まで。最も混雑する時間帯。若者・外国人観光客多数。閉店時の店舗施錠確認。',
                'required_qualifications' => json_encode([
                    '雑踏警備業務検定2級',
                    '群衆管理経験',
                    '多言語対応',
                    '夜間業務経験'
                ]),
                'hourly_rate' => 1300,
                'overtime_rate' => 1625,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'crowd' => '天候による客数変動への対応',
                    'safety' => '混雑時の安全確保',
                    'closing' => '閉店時の確実な施錠確認'
                ]),
                'emergency_procedures' => json_encode([
                    'crowd_control' => '混雑時は入場制限、整理券配布',
                    'disturbance' => 'トラブル発生時は警察通報も検討',
                    'evacuation' => '緊急時は避難誘導、館内放送'
                ]),
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()
            ],
            // PRJ007: 東京製鉄工場24時間警備
            [
                'shift_code' => 'SH011',
                'project_id' => 7,
                'shift_name' => '製鉄工場 日勤A',
                'shift_type' => 'day',
                'start_time' => '06:00:00',
                'end_time' => '12:00:00',
                'break_time' => 30,
                'required_guards' => 2,
                'shift_date' => Carbon::today()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '東京製鉄工場',
                    'area' => '正門・第1工場棟',
                    'positions' => '入退場管理・工場内巡回'
                ]),
                'special_instructions' => '早朝から昼まで。作業員入場ラッシュ対応。危険物・機密情報管理厳重。ガス検知器携行。',
                'required_qualifications' => json_encode([
                    '施設警備業務検定1級',
                    '危険物取扱者乙種',
                    '工場警備経験5年以上',
                    '化学物質取扱知識'
                ]),
                'hourly_rate' => 1800,
                'overtime_rate' => 2250,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'safety' => '高温作業環境での安全確保',
                    'equipment' => '防護具の確実な着用',
                    'health' => '熱中症予防対策'
                ]),
                'emergency_procedures' => json_encode([
                    'fire' => '工場火災時は消防署通報、避難誘導',
                    'chemical' => '化学物質漏洩時はガス検知、立入禁止',
                    'injury' => '工場内事故時は119番通報、応急処置'
                ]),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()
            ],
            [
                'shift_code' => 'SH012',
                'project_id' => 7,
                'shift_name' => '製鉄工場 日勤B',
                'shift_type' => 'day',
                'start_time' => '12:00:00',
                'end_time' => '18:00:00',
                'break_time' => 30,
                'required_guards' => 2,
                'shift_date' => Carbon::today()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '東京製鉄工場',
                    'area' => '第2工場棟・倉庫エリア',
                    'positions' => '製品出荷・資材搬入管理'
                ]),
                'special_instructions' => '昼から夕方まで。製品出荷・資材搬入が活発。大型車両の入退場管理、積荷検査立会い。',
                'required_qualifications' => json_encode([
                    '施設警備業務検定1級',
                    '危険物取扱者乙種',
                    'フォークリフト運転経験',
                    '物流業務知識'
                ]),
                'hourly_rate' => 1800,
                'overtime_rate' => 2250,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'logistics' => '出荷業務への天候影響監視',
                    'safety' => '雨天時の荷役作業安全確保',
                    'equipment' => '屋外作業時の適切な防護'
                ]),
                'emergency_procedures' => json_encode([
                    'accident' => '荷役事故時は作業中止、応急処置',
                    'theft' => '製品盗難時は警察通報、現場保全',
                    'vehicle' => '車両事故時は119番通報、交通整理'
                ]),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()
            ],
            [
                'shift_code' => 'SH013',
                'project_id' => 7,
                'shift_name' => '製鉄工場 夜勤A',
                'shift_type' => 'night',
                'start_time' => '18:00:00',
                'end_time' => '00:00:00',
                'break_time' => 30,
                'required_guards' => 2,
                'shift_date' => Carbon::today()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '東京製鉄工場',
                    'area' => '全工場エリア',
                    'positions' => '夜間巡回・設備監視'
                ]),
                'special_instructions' => '夜間第1シフト。夜間操業の監視、設備異常の早期発見、防犯パトロール強化。',
                'required_qualifications' => json_encode([
                    '施設警備業務検定1級',
                    '危険物取扱者乙種',
                    '夜間工場警備経験',
                    '設備監視能力'
                ]),
                'hourly_rate' => 2100,
                'overtime_rate' => 2625,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'visibility' => '夜間の視界確保',
                    'temperature' => '夜間の気温変化対応',
                    'safety' => '暗所での安全確保'
                ]),
                'emergency_procedures' => json_encode([
                    'equipment_failure' => '設備異常時は工場長へ緊急連絡',
                    'intrusion' => '不審者侵入時は110番通報',
                    'fire' => '夜間火災時は消防署・工場管理者へ連絡'
                ]),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()
            ],
            [
                'shift_code' => 'SH014',
                'project_id' => 7,
                'shift_name' => '製鉄工場 夜勤B',
                'shift_type' => 'midnight',
                'start_time' => '00:00:00',
                'end_time' => '06:00:00',
                'break_time' => 30,
                'required_guards' => 2,
                'shift_date' => Carbon::today()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '東京製鉄工場',
                    'area' => '警備室・重要設備',
                    'positions' => '深夜監視・緊急対応'
                ]),
                'special_instructions' => '深夜帯の最終シフト。最高レベルの警戒必要。単独巡回禁止、必ず2名体制維持。',
                'required_qualifications' => json_encode([
                    '施設警備業務検定1級',
                    '危険物取扱者乙種全類',
                    '深夜工場警備経験5年以上',
                    '緊急時リーダー経験'
                ]),
                'hourly_rate' => 2300,
                'overtime_rate' => 2875,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'vigilance' => '深夜の最高警戒レベル維持',
                    'health' => '深夜勤務者の体調管理',
                    'communication' => '本部との定時連絡確実実施'
                ]),
                'emergency_procedures' => json_encode([
                    'major_incident' => '重大事故時は工場長・消防・警察へ同時通報',
                    'health_emergency' => '体調不良時は即座に交代要請',
                    'security_breach' => '重大セキュリティ事案時は本社警備部長へ直通連絡'
                ]),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()
            ],
            // PRJ008: 関東ロジセンター夜間警備
            [
                'shift_code' => 'SH015',
                'project_id' => 8,
                'shift_name' => 'ロジセンター 夜間シフト',
                'shift_type' => 'night',
                'start_time' => '18:00:00',
                'end_time' => '08:00:00',
                'break_time' => 120,
                'required_guards' => 2,
                'shift_date' => Carbon::today()->format('Y-m-d'),
                'location_details' => json_encode([
                    'building' => '関東ロジスティクスセンター',
                    'area' => '倉庫5棟・トラックヤード',
                    'positions' => '入退場管理・冷凍倉庫監視'
                ]),
                'special_instructions' => '夜間配送トラックの入退場管理。冷凍倉庫の温度監視。荷物の盗難防止パトロール。',
                'required_qualifications' => json_encode([
                    '施設警備業務検定2級',
                    'フォークリフト運転技能',
                    '物流業務経験',
                    '低温環境作業経験'
                ]),
                'hourly_rate' => 1400,
                'overtime_rate' => 1750,
                'status' => 'scheduled',
                'weather_considerations' => json_encode([
                    'temperature' => '冷凍倉庫内外の温度差対応',
                    'logistics' => '配送への天候影響監視',
                    'safety' => '滑りやすい床面での安全確保'
                ]),
                'emergency_procedures' => json_encode([
                    'temperature_alarm' => '冷凍設備異常時は管理者へ緊急連絡',
                    'theft' => '荷物盗難時は警察通報、現場保全',
                    'vehicle_accident' => 'トラック事故時は119番通報、交通整理'
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        // データを挿入
        DB::table('shifts')->insert($shifts);

        echo "Shifts seeder completed. " . count($shifts) . " shifts created.\n";
    }
}
