<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Sửa lại cách xử lý CORS sử dụng Laravel request
        app()->afterResolving(Request::class, function (Request $request) {
            // Luôn thêm CORS headers cho mọi response
            header('Access-Control-Allow-Origin: http://localhost:5173');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
            header('Access-Control-Allow-Credentials: true');

            // Xử lý OPTIONS request (preflight)
            if ($request->isMethod('OPTIONS')) {
                header('Access-Control-Max-Age: 86400');
                exit(0);
            }
        });
    }
}
