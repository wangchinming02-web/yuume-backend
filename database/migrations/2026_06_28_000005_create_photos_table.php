<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            
            // 完整關聯
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('album_categories')->onDelete('set null');
            
            // 欄位
            $table->string('path', 550); // 對應舊資料庫的 path，設長度 550 以免路徑過長
            $table->string('stage_name_snapshot')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};