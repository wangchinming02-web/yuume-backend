<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    // 允許透過 API 批量指派寫入的資料庫欄位
    protected $fillable = [
        'title',
        'event_date',
        'location',
        'category',
        'status',
    ];

    // 💡 加入這個：自動將資料庫的 event_date 轉換為特定的日期字串格式
    protected $casts = [
        'event_date' => 'date:Y-m-d',
    ];

    public static function markExpiredAsEnded(): int
    {
        $today = now('Asia/Taipei')->toDateString();

        return static::query()
            ->whereNotNull('event_date')
            ->where('event_date', '<', $today)
            ->where('status', '!=', '已結束')
            ->update(['status' => '已結束']);
    }
}