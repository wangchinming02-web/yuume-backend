<?php

use Illuminate\Support\Facades\Route;

// 直接回傳檔案內容
Route::get('/', function () {
    return file_get_contents(public_path('yuume-frontend/index.html'));
});

Route::get('/admin', function () {
    return file_get_contents(public_path('yuume-frontend/admin.html'));
});

Route::get('/admin_photo', function () {
    return file_get_contents(public_path('yuume-frontend/admin_photo.html'));
});