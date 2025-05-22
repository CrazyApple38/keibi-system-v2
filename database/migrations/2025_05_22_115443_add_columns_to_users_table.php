<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 基本情報追加
            $table->string('employee_id', 20)->unique()->after('id')->comment('従業員ID');
            $table->enum('role', ['system_admin', 'company_admin', 'manager', 'user', 'guard'])->default('guard')->after('email')->comment('役割');
            $table->unsignedBigInteger('company_id')->nullable()->after('role')->comment('所属会社ID');
            $table->string('department', 100)->nullable()->after('company_id')->comment('部署');
            $table->string('position', 100)->nullable()->after('department')->comment('役職');
            $table->datetime('hire_date')->nullable()->after('position')->comment('入社日');
            $table->string('phone', 20)->nullable()->after('hire_date')->comment('電話番号');
            $table->string('mobile', 20)->nullable()->after('phone')->comment('携帯電話番号');
            $table->text('address')->nullable()->after('mobile')->comment('住所');
            $table->json('emergency_contact')->nullable()->after('address')->comment('緊急連絡先');
            $table->json('permissions')->nullable()->after('emergency_contact')->comment('権限設定');
            $table->boolean('is_active')->default(true)->after('permissions')->comment('アクティブフラグ');
            $table->datetime('last_login_at')->nullable()->after('is_active')->comment('最終ログイン日時');
            
            // 外部キー制約
            $table->foreign('company_id')->references('id')->on('customers')->onDelete('set null');
            
            // インデックス
            $table->index('employee_id');
            $table->index('role');
            $table->index('company_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 外部キー制約削除
            $table->dropForeign(['company_id']);
            
            // インデックス削除
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['role']);
            $table->dropIndex(['company_id']);
            $table->dropIndex(['is_active']);
            
            // カラム削除
            $table->dropColumn([
                'employee_id', 'role', 'company_id', 'department', 'position', 
                'hire_date', 'phone', 'mobile', 'address', 'emergency_contact', 
                'permissions', 'is_active', 'last_login_at'
            ]);
        });
    }
};
