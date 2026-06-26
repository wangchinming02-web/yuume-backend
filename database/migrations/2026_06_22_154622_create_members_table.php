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
    Schema::create('members', function (Blueprint $table) {
        $table->id();
        $table->string('name_tw');                                     // 中文名
        $table->string('name_en')->nullable();                         // 英文/羅馬字
        $table->date('birthday')->nullable();                          // 生日
        $table->string('member_color', 7)->default('#FFFFFF');         // 應援色 (預設白色)
        $table->string('instagram')->nullable();                       // IG 連結
        $table->enum('status', ['現役', '畢業'])->default('現役');       // 狀態
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
