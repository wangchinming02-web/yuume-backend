<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Event;
use App\Models\Member;
use App\Models\AlbumCategory;

/*
|--------------------------------------------------------------------------
| 🔒 受保護的 API 路由 (需要 Token)
|--------------------------------------------------------------------------
*/
Route::middleware(['admin.token'])->group(function () {
    
    Route::post('/events', function (Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'location' => 'required|string|max:255',
            'category' => 'required|string',
            'status' => 'nullable|string|max:50',
        ]);
        $event = Event::create($validated);
        return response()->json(['message' => '活動新增成功！', 'event' => $event], 201);
    });

    Route::delete('/events/{id}', function ($id) {
        $event = Event::find($id);
        if (!$event) return response()->json(['message' => '找不到該活動'], 404);
        $event->delete();
        return response()->json(['message' => '活動已順利刪除！']);
    });

    Route::post('/album-categories', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'folder_slug' => 'required|string|max:255|unique:album_categories,folder_slug',
        ]);
        $category = AlbumCategory::create([
            'name' => $validated['name'],
            'folder_slug' => Str::slug($validated['folder_slug']),
        ]);
        return response()->json($category, 201);
    });

    Route::post('/photos/batch-upload', function (Request $request) {
        $request->validate(['photo' => 'required|image|max:2048', 'category_id' => 'required|integer']);
        $category = DB::table('album_categories')->where('id', $request->category_id)->first();
        if (!$category) return response()->json(['message' => '分類錯誤'], 404);

        $file = $request->file('photo');
        $fileName = date('Ymd') . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        $fileContent = base64_encode(file_get_contents($file->getRealPath()));

        $response = Http::withHeaders([
            'Authorization' => "token " . env('GITHUB_TOKEN'),
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'Laravel-Yuume-App'
        ])->put("https://api.github.com/repos/" . env('GITHUB_USER') . "/" . env('GITHUB_REPO') . "/contents/{$category->folder_slug}/{$fileName}", [
            'message' => "Upload photo",
            'content' => $fileContent,
            'branch'  => env('GITHUB_BRANCH', 'main')
        ]);

        if ($response->successful()) {
            $cdnUrl = "https://cdn.jsdelivr.net/gh/" . env('GITHUB_USER') . "/" . env('GITHUB_REPO') . "@" . env('GITHUB_BRANCH', 'main') . "/{$category->folder_slug}/{$fileName}";
            DB::table('photos')->insert([
                'category_id' => $category->id,
                'path'        => $cdnUrl,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            return response()->json(['message' => '🎉 上傳成功', 'url' => $cdnUrl]);
        }
        return response()->json(['message' => 'GitHub API 上傳失敗'], 500);
    });
});

/*
|--------------------------------------------------------------------------
| 🔓 公開 API 路由
|--------------------------------------------------------------------------
*/
Route::post('/admin-login', function (Request $request) {
    $password = $request->input('password');

    $storedPassword = null;
    try {
        if (DB::getSchemaBuilder()->hasTable('admin_settings')) {
            $storedPassword = DB::table('admin_settings')->where('id', 1)->value('password');
        }
    } catch (\Exception $e) {
        // 資料表不存在或連線異常時，改以環境變數作為備援
    }

    $validPassword = $storedPassword ?: env('ADMIN_PASSWORD');

    if ($password && $validPassword && $password === $validPassword) {
        return response()->json(['message' => '登入成功', 'token' => 'admin-secret-token']);
    }

    return response()->json(['message' => '密碼錯誤'], 401);
});

// 這裡驗證 Token 是否為 admin-secret-token
Route::get('/check-auth', function (Request $request) {
    $token = $request->header('Authorization');
    return ($token === 'Bearer admin-secret-token')
        ? response()->json(['status' => 'authenticated'], 200)
        : response()->json(['status' => 'unauthenticated'], 401);
});

Route::get('/events', fn() => Event::orderBy('event_date', 'desc')->get());
Route::get('/member/{id}', fn($id) => Member::find($id));
Route::get('/locations', fn() => Event::whereNotNull('location')->where('location', '!=', '')->distinct()->pluck('location'));
Route::get('/album-categories', fn() => response()->json(AlbumCategory::all(), 200, [], JSON_UNESCAPED_UNICODE));
Route::get('/photos-by-category/{slug}', function ($slug) {
    if ($slug === 'all') {
        return response()->json(DB::table('photos')->whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))->from('photos')->groupBy('category_id');
        })->get());
    }
    $category = DB::table('album_categories')->where('folder_slug', $slug)->first();
    return $category ? response()->json(DB::table('photos')->where('category_id', $category->id)->orderBy('id', 'desc')->get()) : response()->json([]);
});



Route::get('/db-test', function () {
    try {
        DB::connection()->getPdo();
        return response()->json(['status' => '✅ 資料庫連線成功！']);
    } catch (\Exception $e) {
        return response()->json([
            'status' => '❌ 資料庫連線失敗',
            'error' => $e->getMessage() // 這會告訴我們到底是密碼錯還是網路不通
        ], 500);
    }
});

use App\Models\Photo;

Route::get('/test-db', function () {
    // 嘗試撈出第一筆照片，順便帶出關聯的活動資料
    $photo = Photo::with(['event', 'member'])->first();
    return $photo; 
});