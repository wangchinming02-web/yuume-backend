<?php
use Illuminate\Support\Facades\Route;

// 移除掉那些 file_get_contents 的路徑
// 改為簡單的回應，確認後端還活著就好
Route::get('/', function () {
    return response()->json(['message' => 'Yuume Backend API is running!']);
});