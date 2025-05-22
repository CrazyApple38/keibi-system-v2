<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * ユーザーテーブルのダミーデータ生成
 * 
 * 警備会社システムの各種ユーザー（管理者、マネージャー、一般ユーザー）の
 * ダミーデータを生成します。
 */
class UsersSeeder extends Seeder
{
    /**
     * ダミーデータを生成して挿入
     *
     * @return void
     */
    public function run(): void
    {
        // システム管理者ユーザー
        $systemAdmin = [
            'name' => 'システム管理者',
            'email' => 'admin@keibi-system.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'employee_id' => 'ADM001',
            'role' => 'system_admin',
            'company_id' => 1,
            'department' => 'システム管理部',
            'position' => 'システム管理者',
            'hire_date' => Carbon::now()->subYears(3),
            'phone' => '03-1234-5678',
            'mobile' => '090-1234-5678',
            'address' => '東京都新宿区西新宿1-1-1',
            'emergency_contact' => json_encode([
                'name' => '緊急連絡先太郎',
                'relationship' => '配偶者',
                'phone' => '090-9999-8888'
            ]),
            'permissions' => json_encode([
                'user_management' => true,
                'system_settings' => true,
                'all_companies' => true,
                'financial_data' => true
            ]),
            'is_active' => true,
            'last_login_at' => Carbon::now()->subHours(2),
            'created_at' => Carbon::now()->subMonths(6),
            'updated_at' => Carbon::now()
        ];

        // 各会社の管理者・マネージャー・一般ユーザー
        $companyUsers = [
            // ㈲東央警備
            [
                'name' => '田中 太郎',
                'email' => 'tanaka@touo-keibi.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password'),
                'employee_id' => 'TOU001',
                'role' => 'company_admin',
                'company_id' => 1,
                'department' => '管理部',
                'position' => '部長',
                'hire_date' => Carbon::now()->subYears(5),
                'phone' => '03-2345-6789',
                'mobile' => '090-2345-6789',
                'address' => '東京都港区赤坂1-2-3',
                'emergency_contact' => json_encode([
                    'name' => '田中 花子',
                    'relationship' => '配偶者',
                    'phone' => '090-8888-7777'
                ]),
                'permissions' => json_encode([
                    'company_management' => true,
                    'user_management' => true,
                    'financial_data' => true,
                    'project_management' => true
                ]),
                'is_active' => true,
                'last_login_at' => Carbon::now()->subHours(1),
                'created_at' => Carbon::now()->subMonths(5),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => '佐藤 次郎',
                'email' => 'sato@touo-keibi.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password'),
                'employee_id' => 'TOU002',
                'role' => 'manager',
                'company_id' => 1,
                'department' => '営業部',
                'position' => '課長',
                'hire_date' => Carbon::now()->subYears(3),
                'phone' => '03-3456-7890',
                'mobile' => '090-3456-7890',
                'address' => '東京都渋谷区渋谷2-3-4',
                'emergency_contact' => json_encode([
                    'name' => '佐藤 美智子',
                    'relationship' => '母',
                    'phone' => '03-7777-6666'
                ]),
                'permissions' => json_encode([
                    'project_management' => true,
                    'guard_management' => true,
                    'shift_management' => true,
                    'quotation_management' => true
                ]),
                'is_active' => true,
                'last_login_at' => Carbon::now()->subHours(3),
                'created_at' => Carbon::now()->subMonths(4),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => '高橋 三郎',
                'email' => 'takahashi@touo-keibi.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password'),
                'employee_id' => 'TOU003',
                'role' => 'user',
                'company_id' => 1,
                'department' => '業務部',
                'position' => '一般社員',
                'hire_date' => Carbon::now()->subYears(2),
                'phone' => '03-4567-8901',
                'mobile' => '090-4567-8901',
                'address' => '東京都品川区大崎3-4-5',
                'emergency_contact' => json_encode([
                    'name' => '高橋 恵子',
                    'relationship' => '配偶者',
                    'phone' => '090-6666-5555'
                ]),
                'permissions' => json_encode([
                    'daily_report' => true,
                    'attendance_management' => true,
                    'shift_view' => true
                ]),
                'is_active' => true,
                'last_login_at' => Carbon::now()->subHours(5),
                'created_at' => Carbon::now()->subMonths(3),
                'updated_at' => Carbon::now()
            ],
            // ㈱Nikkeiホールディングス
            [
                'name' => '山田 四郎',
                'email' => 'yamada@nikkei-hd.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password'),
                'employee_id' => 'NIK001',
                'role' => 'company_admin',
                'company_id' => 2,
                'department' => '管理部',
                'position' => '部長',
                'hire_date' => Carbon::now()->subYears(6),
                'phone' => '03-5678-9012',
                'mobile' => '090-5678-9012',
                'address' => '東京都千代田区丸の内4-5-6',
                'emergency_contact' => json_encode([
                    'name' => '山田 由美',
                    'relationship' => '配偶者',
                    'phone' => '090-5555-4444'
                ]),
                'permissions' => json_encode([
                    'company_management' => true,
                    'user_management' => true,
                    'financial_data' => true,
                    'project_management' => true
                ]),
                'is_active' => true,
                'last_login_at' => Carbon::now()->subHours(2),
                'created_at' => Carbon::now()->subMonths(6),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => '鈴木 五郎',
                'email' => 'suzuki@nikkei-hd.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password'),
                'employee_id' => 'NIK002',
                'role' => 'manager',
                'company_id' => 2,
                'department' => '営業部',
                'position' => '課長',
                'hire_date' => Carbon::now()->subYears(4),
                'phone' => '03-6789-0123',
                'mobile' => '090-6789-0123',
                'address' => '東京都中央区銀座5-6-7',
                'emergency_contact' => json_encode([
                    'name' => '鈴木 正子',
                    'relationship' => '母',
                    'phone' => '03-4444-3333'
                ]),
                'permissions' => json_encode([
                    'project_management' => true,
                    'guard_management' => true,
                    'shift_management' => true,
                    'quotation_management' => true
                ]),
                'is_active' => true,
                'last_login_at' => Carbon::now()->subHours(4),
                'created_at' => Carbon::now()->subMonths(5),
                'updated_at' => Carbon::now()
            ],
            // ㈱全日本エンタープライズ
            [
                'name' => '伊藤 六郎',
                'email' => 'ito@zennihon-ent.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password'),
                'employee_id' => 'ZEN001',
                'role' => 'company_admin',
                'company_id' => 3,
                'department' => '管理部',
                'position' => '部長',
                'hire_date' => Carbon::now()->subYears(7),
                'phone' => '03-7890-1234',
                'mobile' => '090-7890-1234',
                'address' => '東京都台東区上野6-7-8',
                'emergency_contact' => json_encode([
                    'name' => '伊藤 京子',
                    'relationship' => '配偶者',
                    'phone' => '090-3333-2222'
                ]),
                'permissions' => json_encode([
                    'company_management' => true,
                    'user_management' => true,
                    'financial_data' => true,
                    'project_management' => true
                ]),
                'is_active' => true,
                'last_login_at' => Carbon::now()->subHours(1),
                'created_at' => Carbon::now()->subMonths(7),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => '渡辺 七郎',
                'email' => 'watanabe@zennihon-ent.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password'),
                'employee_id' => 'ZEN002',
                'role' => 'manager',
                'company_id' => 3,
                'department' => '営業部',
                'position' => '課長',
                'hire_date' => Carbon::now()->subYears(3),
                'phone' => '03-8901-2345',
                'mobile' => '090-8901-2345',
                'address' => '東京都墨田区押上7-8-9',
                'emergency_contact' => json_encode([
                    'name' => '渡辺 雅子',
                    'relationship' => '配偶者',
                    'phone' => '090-2222-1111'
                ]),
                'permissions' => json_encode([
                    'project_management' => true,
                    'guard_management' => true,
                    'shift_management' => true,
                    'quotation_management' => true
                ]),
                'is_active' => true,
                'last_login_at' => Carbon::now()->subHours(6),
                'created_at' => Carbon::now()->subMonths(4),
                'updated_at' => Carbon::now()
            ]
        ];

        // データを挿入
        DB::table('users')->insert($systemAdmin);
        DB::table('users')->insert($companyUsers);

        echo "Users seeder completed. " . (count($companyUsers) + 1) . " users created.\n";
    }
}
