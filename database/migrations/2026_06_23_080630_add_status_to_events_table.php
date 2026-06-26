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
        Schema::table('events', function (Blueprint $table) {
            // 在原本的 table 裡新增 status 欄位，預設值為 '販售中'
            // after('location') 可以讓欄位排在 location 後面，方便資料庫閱讀（可加可不加）
            $table->string('status')->default('販售中')->after('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // 復原時刪除 status 欄位
            $table->dropColumn('status');
        });
    }
};