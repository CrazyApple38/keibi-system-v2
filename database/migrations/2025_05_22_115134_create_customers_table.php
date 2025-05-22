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
        Schema::create('customers', function (Blueprint $table) {
            $table->id()->comment('顧客ID');
            $table->string('customer_code', 20)->unique()->comment('顧客コード');
            $table->string('company_name', 100)->comment('会社名・個人名');
            $table->enum('customer_type', ['corporate', 'individual'])->default('corporate')->comment('顧客区分');
            $table->string('contact_person', 50)->nullable()->comment('担当者名');
            $table->string('contact_title', 50)->nullable()->comment('担当者役職');
            $table->string('phone', 20)->nullable()->comment('電話番号');
            $table->string('mobile', 20)->nullable()->comment('携帯電話番号');
            $table->string('email', 100)->nullable()->comment('メールアドレス');
            $table->string('postal_code', 8)->nullable()->comment('郵便番号');
            $table->string('prefecture', 20)->nullable()->comment('都道府県');
            $table->string('city', 50)->nullable()->comment('市区町村');
            $table->string('address_line1', 100)->nullable()->comment('住所1');
            $table->string('address_line2', 100)->nullable()->comment('住所2');
            $table->json('billing_address')->nullable()->comment('請求先住所');
            $table->string('business_type', 100)->nullable()->comment('業種');
            $table->integer('employee_count')->nullable()->comment('従業員数');
            $table->bigInteger('annual_revenue')->nullable()->comment('年商');
            $table->integer('credit_limit')->default(0)->comment('与信限度額');
            $table->string('payment_terms', 100)->nullable()->comment('支払条件');
            $table->string('tax_id', 20)->nullable()->comment('税務署番号');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->comment('ステータス');
            $table->text('notes')->nullable()->comment('備考');
            $table->timestamps();
            
            // インデックス
            $table->index('customer_code');
            $table->index('company_name');
            $table->index('customer_type');
            $table->index('status');
            $table->index('business_type');
            $table->index('prefecture');
            $table->index('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
