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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id()->comment('見積ID');
            $table->string('quotation_number', 20)->unique()->comment('見積番号');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade')->comment('顧客ID');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null')->comment('案件ID');
            $table->date('quotation_date')->comment('見積日');
            $table->date('valid_until')->comment('有効期限');
            $table->decimal('subtotal', 12, 2)->default(0)->comment('小計金額');
            $table->decimal('tax_amount', 12, 2)->default(0)->comment('消費税額');
            $table->decimal('total_amount', 12, 2)->default(0)->comment('見積金額');
            $table->json('items')->comment('見積項目詳細');
            $table->text('notes')->nullable()->comment('備考');
            $table->text('terms_conditions')->nullable()->comment('見積条件');
            $table->foreignId('prepared_by')->nullable()->constrained('users')->onDelete('set null')->comment('作成者ID');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->comment('承認者ID');
            $table->enum('status', ['draft', 'submitted', 'pending', 'accepted', 'rejected', 'expired'])->default('draft')->comment('見積状況');
            $table->date('response_date')->nullable()->comment('回答日');
            $table->timestamps();
            
            // インデックス
            $table->index('quotation_number');
            $table->index('customer_id');
            $table->index('project_id');
            $table->index('quotation_date');
            $table->index('valid_until');
            $table->index('status');
            $table->index('prepared_by');
            $table->index('approved_by');
            $table->index('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
