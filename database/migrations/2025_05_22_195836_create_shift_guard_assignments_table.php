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
        Schema::create('shift_guard_assignments', function (Blueprint $table) {
            $table->id()->comment('シフト・警備員割り当てID');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade')->comment('シフトID');
            $table->foreignId('guard_id')->constrained('guards')->onDelete('cascade')->comment('警備員ID');
            $table->date('assignment_date')->comment('割り当て日');
            $table->string('role', 50)->comment('役割');
            $table->string('position', 100)->comment('配置ポジション');
            $table->json('responsibilities')->comment('責任・業務内容');
            $table->json('equipment_assigned')->comment('割り当て機材');
            $table->text('special_instructions')->nullable()->comment('特別指示事項');
            $table->enum('status', ['scheduled', 'confirmed', 'pending', 'completed', 'cancelled'])->default('scheduled')->comment('ステータス');
            $table->unsignedBigInteger('assigned_by')->comment('割り当て者ID');
            $table->datetime('assigned_at')->comment('割り当て日時');
            $table->timestamps();
            
            // インデックス
            $table->index('shift_id');
            $table->index('guard_id');
            $table->index('assignment_date');
            $table->index('role');
            $table->index('status');
            $table->index('assigned_by');
            
            // 外部キー
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');
            
            // ユニーク制約（同一シフト・同一日に同一警備員を重複割り当て防止）
            $table->unique(['shift_id', 'guard_id', 'assignment_date'], 'unique_shift_guard_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_guard_assignments');
    }
};
