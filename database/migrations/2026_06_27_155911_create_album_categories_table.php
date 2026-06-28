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
        Schema::create('album_categories', function (Blueprint $table) {
            $table->id(); // 對應 bigint(20) UNSIGNED PRIMARY KEY
            $table->string('name')->comment('活動分類顯示名稱');
            $table->string('folder_slug')->comment('對應你的資料夾名稱/英文網址');
            $table->timestamps(); // 自動建立 created_at 與 updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('album_categories');
    }
};