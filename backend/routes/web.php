<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return env('MY_NAME');
});

// Route::apiResource('jobs', JobController::class); // Đã xóa dòng này
