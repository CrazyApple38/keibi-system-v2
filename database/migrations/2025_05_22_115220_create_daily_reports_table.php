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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id()->comment('日報ID');
            $table->string('report_number', 20)->unique()->comment('日報番号');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade')->comment('案件ID');
            $table->foreignId('guard_id')->constrained('guards')->onDelete('cascade')->comment('警備員ID');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null')->comment('シフトID');
            $table->date('report_date')->comment('報告日');
            $table->string('shift_time', 20)->comment('勤務時間');
            $table->string('weather_condition', 50)->nullable()->comment('天候');
            $table->integer('temperature')->nullable()->comment('気温');
            $table->text('summary')->comment('業務概要');
            $table->json('activities')->comment('活動詳細');
            $table->json('incidents')->comment('インシデント');
            $table->json('observations')->comment('観察事項');
            $table->json('equipment_used')->comment('使用機材');
            $table->json('visitors')->comment('来訪者記録');
            $table->json('recommendations')->comment('改善提案');
            $table->text('next_shift_notes')->nullable()->comment('次シフトへの申し送り');
            $table->boolean('photos_attached')->default(false)->comment('写真添付フラグ');
            $table->integer('photo_count')->default(0)->comment('写真枚数');
            $table->unsignedBigInteger('submitted_by')->comment('提出者ID');
            $table->unsignedBigInteger('reviewed_by')->nullable()->comment('確認者ID');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('承認者ID');
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'approved', 'rejected'])->default('draft')->comment('ステータス');
            $table->timestamps();
            
            // インデックス
            $table->index('report_number');
            $table->index('project_id');
            $table->index('guard_id');
            $table->index('shift_id');
            $table->index('report_date');
            $table->index('status');
            $table->index('submitted_by');
            $table->index('reviewed_by');
            $table->index('approved_by');
            
            // 外部キー
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
