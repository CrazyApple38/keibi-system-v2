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
        // SoftDeletesを使用しているテーブルにdeleted_atカラムを追加
        
        Schema::table('customers', function (Blueprint $table) {
            $table->softDeletes()->comment('論理削除日時');
        });
        
        Schema::table('projects', function (Blueprint $table) {
            $table->softDeletes()->comment('論理削除日時');
        });
        
        Schema::table('guards', function (Blueprint $table) {
            $table->softDeletes()->comment('論理削除日時');
        });
        
        Schema::table('shifts', function (Blueprint $table) {
            $table->softDeletes()->comment('論理削除日時');
        });
        
        Schema::table('quotations', function (Blueprint $table) {
            $table->softDeletes()->comment('論理削除日時');
        });
        
        Schema::table('contracts', function (Blueprint $table) {
            $table->softDeletes()->comment('論理削除日時');
        });
        
        Schema::table('invoices', function (Blueprint $table) {
            $table->softDeletes()->comment('論理削除日時');
        });
        
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->softDeletes()->comment('論理削除日時');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('projects', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('guards', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
