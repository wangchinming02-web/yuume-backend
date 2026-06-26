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
   Schema::create('events', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->date('event_date')->nullable();
    $table->string('location')->nullable();
    // 新增這行：用來區分是舞台、直播、女僕執勤還是攝影會
    $table->string('type')->default('舞台出演'); 
    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
