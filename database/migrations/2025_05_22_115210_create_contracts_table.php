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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id()->comment('契約ID');
            $table->string('contract_number', 20)->unique()->comment('契約番号');
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->onDelete('set null')->comment('見積ID');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade')->comment('顧客ID');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade')->comment('案件ID');
            $table->date('contract_date')->comment('契約日');
            $table->date('start_date')->comment('契約開始日');
            $table->date('end_date')->nullable()->comment('契約終了日');
            $table->decimal('contract_amount', 12, 2)->default(0)->comment('契約金額');
            $table->string('payment_terms', 100)->comment('支払条件');
            $table->json('service_details')->comment('サービス詳細');
            $table->text('terms_conditions')->nullable()->comment('契約条件');
            $table->text('renewal_conditions')->nullable()->comment('更新条件');
            $table->text('cancellation_terms')->nullable()->comment('解約条件');
            $table->json('penalty_clauses')->nullable()->comment('違約金条項');
            $table->json('insurance_coverage')->nullable()->comment('保険内容');
            $table->string('signed_by_customer', 100)->nullable()->comment('顧客側署名者');
            $table->string('signed_by_company', 100)->nullable()->comment('自社側署名者');
            $table->enum('status', ['active', 'completed', 'cancelled', 'suspended', 'expired'])->default('active')->comment('契約状況');
            $table->date('next_review_date')->nullable()->comment('次回見直し日');
            $table->text('notes')->nullable()->comment('備考');
            $table->timestamps();
            
            // インデックス
            $table->index('contract_number');
            $table->index('quotation_id');
            $table->index('customer_id');
            $table->index('project_id');
            $table->index('contract_date');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('status');
            $table->index('next_review_date');
            $table->index('contract_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
