<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PhotoController extends Controller
{
    // 1. 撈取所有分類清單（供前端下拉選單呈現）
    public function getCategories()
    {
        $categories = DB::table('album_categories')->orderBy('id', 'asc')->get();
        return response()->json($categories);
    }

    // 2. 快速建立新相簿分類
    public function createCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'folder_slug' => 'required|string|max:255|unique:album_categories,folder_slug',
        ]);

        // 轉換為小寫英文、數字、連字號的網址規範格式
        $slug = Str::slug($request->folder_slug);

        $id = DB::table('album_categories')->insertGetId([
            'name' => $request->name,
            'folder_slug' => $slug,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'id' => $id,
            'name' => $request->name,
            'folder_slug' => $slug,
            'message' => '分類建立成功！'
        ]);
    }

    // 3. 處理圖片上傳核心邏輯：接收照片 -> 查分類 -> 推送 GitHub -> 存資料庫 -> 產出 jsDelivr 網址
    public function upload(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:4096', // 限制檔案最大 2MB
            'category_id' => 'required|integer'
        ]);

        // 透過前端傳來的 ID 查出對應的 folder_slug（GitHub 內的資料夾路徑）
        $category = DB::table('album_categories')->where('id', $request->category_id)->first();
        if (!$category) {
            return response()->json(['message' => '找不到對應的活動分類資料夾'], 404);
        }

        $file = $request->file('photo');
        
        // 生成一個唯一檔名防止重複 (例如: 20260624_a1b2c3.jpg)
        $extension = $file->getClientOriginalExtension();
        $fileName = date('Ymd') . '_' . Str::random(6) . '.' . $extension;

        // 將檔案二進位內容轉成 GitHub API 所要求的 Base64 編碼
        $fileContent = base64_encode(file_get_contents($file->getRealPath()));

        // 從剛剛填寫的 .env 中載入 GitHub 連線參數
        $token  = env('GITHUB_TOKEN');
        $user   = env('GITHUB_USER');
        $repo   = env('GITHUB_REPO');
        $branch = env('GITHUB_BRANCH', 'main');

        // 定義這張照片在 GitHub 倉庫內部的儲存路徑（例如：shuguang-2nd/20260624_a1b2c3.jpg）
        $githubPath = $category->folder_slug . '/' . $fileName;
        $apiUrl = "https://api.github.com/repos/{$user}/{$repo}/contents/{$githubPath}";

        // 發送 API 請求給 GitHub，直接將照片 push 進去
        // 💡 修正點：補上 'User-Agent' Header，避免被 GitHub API 拒絕訪問
        $response = Http::withHeaders([
            'Authorization' => "token {$token}",
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'Laravel-Yuume-App' 
        ])->put($apiUrl, [
            'message' => "Upload photo via Yuume-Backend for: {$category->name}",
            'content' => $fileContent,
            'branch'  => $branch
        ]);

        if ($response->successful()) {
            // ✨ 串接成功！完美組裝出 jsDelivr CDN 全球加速網址
            $cdnUrl = "https://cdn.jsdelivr.net/gh/{$user}/{$repo}@{$branch}/{$githubPath}";

            // 將 CDN 網址與對應的 category_id 寫入你的 photos 表中
            // ⚠️ 備註：請確認你 photos 資料表存網址的欄位是叫 image_url 還是 path。如果是 path，請把下面改成 'path' => $cdnUrl
            DB::table('photos')->insert([
                'category_id' => $category->id,
                'path'   => $cdnUrl, 
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return response()->json([
                'message' => '🎉 圖片上傳成功並已同步生成 CDN 網址！',
                'url'     => $cdnUrl
            ]);
        }

        // 如果 GitHub 回傳錯誤，將原因倒出來方便 debug
        return response()->json([
            'message' => 'GitHub API 上傳失敗，請確認 Token 與權限設定',
            'error'   => $response->json()
        ], 500);
    }
}