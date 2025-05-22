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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id()->comment('シフトID');
            $table->string('shift_code', 20)->unique()->comment('シフトコード');
            $table->string('shift_name', 100)->comment('シフト名');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade')->comment('案件ID');
            $table->date('shift_date')->comment('勤務日');
            $table->time('start_time')->comment('開始時刻');
            $table->time('end_time')->comment('終了時刻');
            $table->integer('break_time')->default(0)->comment('休憩時間（分）');
            $table->enum('shift_type', ['day', 'night', 'midnight', 'evening', 'full_day'])->comment('シフト区分');
            $table->integer('required_guards')->default(1)->comment('必要警備員数');
            $table->json('required_qualifications')->nullable()->comment('必要資格');
            $table->integer('hourly_rate')->default(0)->comment('時給');
            $table->integer('overtime_rate')->default(0)->comment('残業時給');
            $table->json('location_details')->nullable()->comment('勤務場所詳細');
            $table->text('special_instructions')->nullable()->comment('特別指示事項');
            $table->json('emergency_procedures')->nullable()->comment('緊急時対応手順');
            $table->json('weather_considerations')->nullable()->comment('天候対応事項');
            $table->enum('status', ['draft', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('draft')->comment('ステータス');
            $table->timestamps();
            
            // インデックス
            $table->index('shift_code');
            $table->index(['project_id', 'shift_date']);
            $table->index('shift_date');
            $table->index('shift_type');
            $table->index('status');
            $table->index('required_guards');
            $table->index('hourly_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
