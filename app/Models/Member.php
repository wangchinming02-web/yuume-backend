<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    // 一個成員有很多張照片
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }
}