<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * データベースシーダーメインクラス
 * 
 * 警備グループ会社統合管理システムの全テーブルに
 * ダミーデータを投入するためのメインシーダークラス。
 * 外部キー制約を考慮した適切な順序でシーダーを実行。
 */
class DatabaseSeeder extends Seeder
{
    /**
     * データベースシードの実行
     * 
     * 外部キー制約を考慮し、以下の順序でシーダーを実行：
     * 1. Customers（顧客）- 外部キー参照される基盤テーブル
     * 2. Users（ユーザー）- 顧客テーブル参照
     * 3. Projects（案件・プロジェクト）
     * 4. Guards（警備員）
     * 5. Shifts（シフト）
     * 6. Attendances（出勤・勤怠）
     * 7. Quotations（見積書）
     * 8. Contracts（契約）
     * 9. Invoices（請求書）
     * 10. DailyReports（日報）
     * 11. ShiftGuardAssignments（シフト・警備員割り当て）
     *
     * @return void
     */
    public function run(): void
    {
        echo "=== 警備グループ会社統合管理システム データベースシーダー開始 ===\n\n";
        
        // 1. 顧客テーブル（基盤データ - 外部キー参照されるため最初）
        echo "1. 顧客データの投入...\n";
        $this->call(CustomersSeeder::class);
        echo "\n";
        
        // 2. ユーザーテーブル（顧客テーブル参照）
        echo "2. ユーザーデータの投入...\n";
        $this->call(UsersSeeder::class);
        echo "\n";
        
        // 3. 案件・プロジェクトテーブル
        echo "3. 案件・プロジェクトデータの投入...\n";
        $this->call(ProjectsSeeder::class);
        echo "\n";
        
        // 4. 警備員テーブル
        echo "4. 警備員データの投入...\n";
        $this->call(GuardsSeeder::class);
        echo "\n";
        
        // 5. シフトテーブル
        echo "5. シフトデータの投入...\n";
        $this->call(ShiftsSeeder::class);
        echo "\n";
        
        // 6. 出勤・勤怠テーブル
        echo "6. 出勤・勤怠データの投入...\n";
        $this->call(AttendancesSeeder::class);
        echo "\n";
        
        // 7. 見積書テーブル
        echo "7. 見積書データの投入...\n";
        $this->call(QuotationsSeeder::class);
        echo "\n";
        
        // 8. 契約テーブル
        echo "8. 契約データの投入...\n";
        $this->call(ContractsSeeder::class);
        echo "\n";
        
        // 9. 請求書テーブル
        echo "9. 請求書データの投入...\n";
        $this->call(InvoicesSeeder::class);
        echo "\n";
        
        // 10. 日報テーブル
        echo "10. 日報データの投入...\n";
        $this->call(DailyReportsSeeder::class);
        echo "\n";
        
        // 11. シフト・警備員割り当てテーブル
        echo "11. シフト・警備員割り当てデータの投入...\n";
        $this->call(ShiftGuardAssignmentsSeeder::class);
        echo "\n";
        
        echo "=== データベースシーダー完了 ===\n\n";
        echo "投入完了データ概要:\n";
        echo "- 顧客: 10社（建設・不動産・イベント・製造・物流・個人等）\n";
        echo "- ユーザー: 8名（システム管理者1名 + 各社管理者・マネージャー・一般ユーザー）\n";
        echo "- プロジェクト: 10件（建設現場・施設・イベント・工場・物流・住宅警備）\n";
        echo "- 警備員: 10名（ベテラン・中堅・新人・男女・専門スキル保有者）\n";
        echo "- シフト: 15パターン（日勤・夜勤・深夜・24時間体制等）\n";
        echo "- 出勤記録: 11件（正常・遅刻・早退・残業・緊急対応等）\n";
        echo "- 見積書: 10件（承認・保留・却下・各業界対応）\n";
        echo "- 契約: 8件（アクティブ・完了・多様な契約条件）\n";
        echo "- 請求書: 10件（支払済・未払・延滞・追加請求等）\n";
        echo "- 日報: 7件（現場状況・インシデント・業務完了報告）\n";
        echo "- シフト割り当て: 16件（リーダー・メンバー・交代・緊急対応）\n\n";
        echo "警備業界特有のリアルなデータが投入されました。\n";
        echo "テスト環境、デモ環境、開発環境でご活用ください。\n";
    }
}
