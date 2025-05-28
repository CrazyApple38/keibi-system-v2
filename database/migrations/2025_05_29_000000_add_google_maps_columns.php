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
        // 警備員テーブルにGoogle Maps関連カラムを追加
        Schema::table('guards', function (Blueprint $table) {
            $table->decimal('location_lat', 10, 8)->nullable()->comment('現在位置（緯度）');
            $table->decimal('location_lng', 11, 8)->nullable()->comment('現在位置（経度）');
            $table->decimal('location_accuracy', 8, 2)->nullable()->comment('位置精度（メートル）');
            $table->string('location_address', 500)->nullable()->comment('現在位置住所');
            $table->timestamp('location_updated_at')->nullable()->comment('位置情報更新日時');
            $table->boolean('location_sharing_enabled')->default(true)->comment('位置情報共有有効');
            $table->json('location_history')->nullable()->comment('位置履歴（24時間分）');
            
            // インデックス追加
            $table->index(['location_lat', 'location_lng']);
            $table->index('location_updated_at');
            $table->index('location_sharing_enabled');
        });

        // プロジェクトテーブルにGoogle Maps関連カラムを追加
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('location_lat', 10, 8)->nullable()->comment('現場位置（緯度）');
            $table->decimal('location_lng', 11, 8)->nullable()->comment('現場位置（経度）');
            $table->string('location_address', 500)->nullable()->comment('現場住所');
            $table->string('location_building', 100)->nullable()->comment('建物名');
            $table->string('location_floor', 20)->nullable()->comment('階数');
            $table->string('location_room', 50)->nullable()->comment('部屋番号');
            $table->text('location_notes')->nullable()->comment('現場詳細情報');
            $table->decimal('location_radius', 8, 2)->default(100.00)->comment('有効範囲（メートル）');
            $table->json('parking_info')->nullable()->comment('駐車場情報');
            $table->json('access_info')->nullable()->comment('アクセス情報');
            
            // インデックス追加
            $table->index(['location_lat', 'location_lng']);
            $table->index('location_address');
        });

        // 顧客テーブルにGoogle Maps関連カラムを追加
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('headquarters_lat', 10, 8)->nullable()->comment('本社位置（緯度）');
            $table->decimal('headquarters_lng', 11, 8)->nullable()->comment('本社位置（経度）');
            $table->string('headquarters_address', 500)->nullable()->comment('本社住所');
            $table->json('branch_locations')->nullable()->comment('支店・事業所位置情報');
            
            // インデックス追加
            $table->index(['headquarters_lat', 'headquarters_lng']);
        });

        // シフトテーブルにGoogle Maps関連カラムを追加
        Schema::table('shifts', function (Blueprint $table) {
            $table->decimal('assembly_point_lat', 10, 8)->nullable()->comment('集合場所（緯度）');
            $table->decimal('assembly_point_lng', 11, 8)->nullable()->comment('集合場所（経度）');
            $table->string('assembly_point_address', 500)->nullable()->comment('集合場所住所');
            $table->text('assembly_point_notes')->nullable()->comment('集合場所詳細');
            $table->time('assembly_time')->nullable()->comment('集合時間');
            $table->json('patrol_route')->nullable()->comment('巡回ルート情報');
            $table->decimal('estimated_travel_time', 5, 2)->nullable()->comment('推定移動時間（分）');
            
            // インデックス追加
            $table->index(['assembly_point_lat', 'assembly_point_lng']);
            $table->index('assembly_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guards', function (Blueprint $table) {
            $table->dropIndex(['location_lat', 'location_lng']);
            $table->dropIndex(['location_updated_at']);
            $table->dropIndex(['location_sharing_enabled']);
            
            $table->dropColumn([
                'location_lat',
                'location_lng', 
                'location_accuracy',
                'location_address',
                'location_updated_at',
                'location_sharing_enabled',
                'location_history'
            ]);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['location_lat', 'location_lng']);
            $table->dropIndex(['location_address']);
            
            $table->dropColumn([
                'location_lat',
                'location_lng',
                'location_address',
                'location_building',
                'location_floor',
                'location_room',
                'location_notes',
                'location_radius',
                'parking_info',
                'access_info'
            ]);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['headquarters_lat', 'headquarters_lng']);
            
            $table->dropColumn([
                'headquarters_lat',
                'headquarters_lng',
                'headquarters_address',
                'branch_locations'
            ]);
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropIndex(['assembly_point_lat', 'assembly_point_lng']);
            $table->dropIndex(['assembly_time']);
            
            $table->dropColumn([
                'assembly_point_lat',
                'assembly_point_lng',
                'assembly_point_address',
                'assembly_point_notes',
                'assembly_time',
                'patrol_route',
                'estimated_travel_time'
            ]);
        });
    }
};
