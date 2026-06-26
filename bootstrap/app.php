<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 在這裡註冊中間件別名，這樣你在 api.php 就能使用 'admin.token'
        $middleware->alias([
            'admin.token' => \App\Http\Middleware\AdminTokenMiddleware::class,
        ]);
        
        // 如果你發現還是有 CORS 錯誤，建議在這裡開啟 CORS
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();