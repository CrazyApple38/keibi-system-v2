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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id()->comment('勤怠ID');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade')->comment('シフトID');
            $table->foreignId('guard_id')->constrained('guards')->onDelete('cascade')->comment('警備員ID');
            $table->date('attendance_date')->comment('勤務日');
            $table->time('scheduled_start_time')->comment('予定開始時刻');
            $table->time('scheduled_end_time')->comment('予定終了時刻');
            $table->time('actual_start_time')->nullable()->comment('実際の開始時刻');
            $table->time('actual_end_time')->nullable()->comment('実際の終了時刻');
            $table->time('break_start_time')->nullable()->comment('休憩開始時刻');
            $table->time('break_end_time')->nullable()->comment('休憩終了時刻');
            $table->decimal('break_hours', 4, 2)->default(0)->comment('休憩時間');
            $table->decimal('total_work_hours', 4, 2)->default(0)->comment('総勤務時間');
            $table->decimal('overtime_hours', 4, 2)->default(0)->comment('残業時間');
            $table->json('location_checkin')->nullable()->comment('出勤位置情報');
            $table->json('location_checkout')->nullable()->comment('退勤位置情報');
            $table->json('incidents')->nullable()->comment('事件・事故記録');
            $table->json('weather_conditions')->nullable()->comment('天候情報');
            $table->decimal('performance_rating', 3, 1)->nullable()->comment('勤務評価');
            $table->text('notes')->nullable()->comment('勤務メモ');
            $table->text('supervisor_notes')->nullable()->comment('監督者コメント');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'absent', 'late', 'early_leave', 'no_show'])->default('scheduled')->comment('勤怠状況');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->comment('承認者ID');
            $table->datetime('approved_at')->nullable()->comment('承認日時');
            $table->timestamps();
            
            // インデックス
            $table->index('shift_id');
            $table->index('guard_id');
            $table->index('attendance_date');
            $table->index('actual_start_time');
            $table->index('status');
            $table->index('approved_by');
            $table->index('performance_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
