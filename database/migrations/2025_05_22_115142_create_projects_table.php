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
        Schema::create('projects', function (Blueprint $table) {
            $table->id()->comment('案件ID');
            $table->string('project_code', 20)->unique()->comment('案件コード');
            $table->string('project_name', 100)->comment('案件名');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade')->comment('顧客ID');
            $table->enum('project_type', [
                'construction', 'facility', 'event', 'industrial', 'logistics', 'residential', 'traffic', 'patrol'
            ])->comment('案件種別');
            $table->text('description')->comment('案件概要');
            $table->json('location')->comment('警備場所情報');
            $table->date('start_date')->comment('開始日');
            $table->date('end_date')->nullable()->comment('終了日');
            $table->enum('status', ['planning', 'active', 'completed', 'cancelled', 'planned'])->default('planning')->comment('ステータス');
            $table->enum('priority', ['low', 'medium', 'high', 'very_high'])->default('medium')->comment('優先度');
            $table->bigInteger('contract_amount')->default(0)->comment('契約金額');
            $table->integer('monthly_amount')->default(0)->comment('月額金額');
            $table->json('guard_requirements')->comment('警備員要件');
            $table->json('equipment_needed')->comment('必要機材');
            $table->json('client_contact')->comment('顧客連絡先');
            $table->text('special_instructions')->nullable()->comment('特別指示事項');
            $table->json('risk_assessment')->comment('リスク評価');
            $table->timestamps();
            
            // インデックス
            $table->index('project_code');
            $table->index('customer_id');
            $table->index('project_type');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('status');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
