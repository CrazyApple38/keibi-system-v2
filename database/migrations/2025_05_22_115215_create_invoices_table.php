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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id()->comment('請求書ID');
            $table->string('invoice_number', 20)->unique()->comment('請求書番号');
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade')->comment('契約ID');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade')->comment('顧客ID');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade')->comment('案件ID');
            $table->date('invoice_date')->comment('請求日');
            $table->date('due_date')->comment('支払期限');
            $table->date('billing_period_start')->nullable()->comment('請求期間開始日');
            $table->date('billing_period_end')->nullable()->comment('請求期間終了日');
            $table->decimal('subtotal', 12, 2)->default(0)->comment('小計金額');
            $table->decimal('tax_amount', 12, 2)->default(0)->comment('消費税額');
            $table->decimal('total_amount', 12, 2)->default(0)->comment('請求金額');
            $table->json('items')->comment('請求項目詳細');
            $table->string('payment_terms', 100)->nullable()->comment('支払条件');
            $table->string('payment_method', 50)->default('銀行振込')->comment('支払方法');
            $table->json('bank_details')->nullable()->comment('振込先銀行情報');
            $table->text('notes')->nullable()->comment('備考');
            $table->enum('status', ['pending', 'sent', 'paid', 'partial', 'overdue', 'cancelled'])->default('pending')->comment('支払状況');
            $table->date('paid_date')->nullable()->comment('入金日');
            $table->decimal('paid_amount', 12, 2)->default(0)->comment('入金金額');
            $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null')->comment('発行者ID');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->comment('承認者ID');
            $table->timestamps();
            
            // インデックス
            $table->index('invoice_number');
            $table->index('contract_id');
            $table->index('customer_id');
            $table->index('project_id');
            $table->index('invoice_date');
            $table->index('due_date');
            $table->index('billing_period_start');
            $table->index('status');
            $table->index('paid_date');
            $table->index('issued_by');
            $table->index('approved_by');
            $table->index('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
