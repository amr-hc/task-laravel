<?php

use App\Http\Controllers\StatsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\PostController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/verify', [AuthController::class, 'verify'])->name('auth.verify');
    Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode'])->name('auth.resendVerificationCode');
});

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('tags', TagController::class)->names('tags');

    Route::prefix('posts')->group(function () {
        Route::get('/trashed', [PostController::class, 'deletedPosts'])->name('posts.deleted');
        Route::post('/{id}/restore', [PostController::class, 'restore'])->name('posts.restore');
        Route::apiResource('/', PostController::class)->parameters(['' => 'post'])->names('posts');
    });
});


Route::get('/stats', [StatsController ::class, 'index'])->name('stats');
