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
    Schema::create('photos', function (Blueprint $table) {
        $table->id();
        // 建立外鍵並關聯到 members 表的 id
        $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
        // 建立外鍵並關聯到 events 表的 id
        $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
        
        $table->string('image_url', 550);               // 圖片路徑
        $table->string('stage_name_snapshot')->nullable(); // 拍照當下的藝名備註
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
