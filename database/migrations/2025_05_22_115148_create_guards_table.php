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
        Schema::create('guards', function (Blueprint $table) {
            $table->id()->comment('警備員ID');
            $table->string('guard_code', 20)->unique()->comment('警備員コード');
            $table->string('name', 50)->comment('氏名');
            $table->string('name_kana', 50)->comment('フリガナ');
            $table->integer('age')->comment('年齢');
            $table->date('birth_date')->comment('生年月日');
            $table->enum('gender', ['male', 'female'])->comment('性別');
            $table->string('postal_code', 8)->nullable()->comment('郵便番号');
            $table->string('prefecture', 20)->nullable()->comment('都道府県');
            $table->string('city', 50)->nullable()->comment('市区町村');
            $table->string('address_line1', 100)->nullable()->comment('住所1');
            $table->string('address_line2', 100)->nullable()->comment('住所2');
            $table->string('phone', 20)->comment('電話番号');
            $table->string('mobile', 20)->nullable()->comment('携帯電話番号');
            $table->string('email', 100)->nullable()->comment('メールアドレス');
            $table->json('emergency_contact')->nullable()->comment('緊急連絡先');
            $table->json('physical_condition')->nullable()->comment('身体的状況');
            $table->date('hire_date')->comment('入社日');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'temporary'])->comment('雇用形態');
            $table->unsignedBigInteger('company_id')->nullable()->comment('所属会社ID');
            $table->json('qualifications')->nullable()->comment('所持資格');
            $table->json('skills')->nullable()->comment('特殊技能');
            $table->json('preferred_locations')->nullable()->comment('勤務希望エリア');
            $table->json('availability')->nullable()->comment('勤務可能時間');
            $table->integer('base_hourly_rate')->default(0)->comment('基本時給');
            $table->integer('night_rate')->default(0)->comment('夜勤時給');
            $table->integer('holiday_rate')->default(0)->comment('休日時給');
            $table->integer('overtime_rate')->default(0)->comment('残業時給');
            $table->decimal('performance_rating', 3, 1)->nullable()->comment('評価点数');
            $table->enum('security_clearance', ['basic', 'standard', 'high', 'very_high'])->default('basic')->comment('機密レベル');
            $table->json('uniform_size')->nullable()->comment('制服サイズ');
            $table->text('notes')->nullable()->comment('備考');
            $table->enum('status', ['active', 'inactive', 'training', 'retired'])->default('active')->comment('在籍状況');
            $table->timestamps();
            
            // 外部キー制約
            $table->foreign('company_id')->references('id')->on('customers')->onDelete('set null');
            
            // インデックス
            $table->index('guard_code');
            $table->index('name');
            $table->index('gender');
            $table->index('employment_type');
            $table->index('company_id');
            $table->index('hire_date');
            $table->index('status');
            $table->index('base_hourly_rate');
            $table->index('security_clearance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guards');
    }
};
