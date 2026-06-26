<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlbumCategory extends Model
{
    use HasFactory;

    // 🎯 強制指定對應到你在 phpMyAdmin 看到的那個資料表
    protected $table = 'album_categories';

    // 🎯 允許批量寫入的欄位欄位（配合你的資料表結構）
    protected $fillable = [
        'name',
        'folder_slug'
    ];
}