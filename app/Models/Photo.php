<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    // 指定對應的資料表名稱 (若類別名稱是 Photo，Laravel 預設會找 photos 表，這行可留可不留)
    protected $table = 'photos';

    // 1. 定義允許被大量寫入 (Mass Assignment) 的欄位
    // 這對 API 傳入資料很重要，沒寫在這裡的欄位會無法存入
    protected $fillable = [
        'member_id',
        'event_id',
        'category_id',
        'path',
        'stage_name_snapshot'
    ];

    // 2. 定義與 Event (活動) 的關聯
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    // 3. 定義與 Member (成員) 的關聯
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    // 4. 定義與 AlbumCategory (分類) 的關聯
    public function category(): BelongsTo
    {
        return $this->belongsTo(AlbumCategory::class, 'category_id');
    }
}