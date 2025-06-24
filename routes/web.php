<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostDemoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// N+1クエリ問題デモ用ルート
Route::prefix('posts')->group(function () {
    Route::get('n1', [PostDemoController::class, 'indexN1'])->name('posts.n1');
    Route::get('with', [PostDemoController::class, 'indexWith'])->name('posts.with');
    Route::get('with-count', [PostDemoController::class, 'indexWithCount'])->name('posts.with-count');
    Route::get('join', [PostDemoController::class, 'indexJoin'])->name('posts.join');
    Route::get('chunk', [PostDemoController::class, 'indexChunk'])->name('posts.chunk');
    Route::get('dashboard', [PostDemoController::class, 'dashboard'])->name('posts.dashboard');
});

// 設計書に記載されているルート（別名）
Route::get('posts-n1', [PostDemoController::class, 'indexN1']);
Route::get('posts-with', [PostDemoController::class, 'indexWith']);
Route::get('posts-join', [PostDemoController::class, 'indexJoin']);
Route::get('posts-chunk', [PostDemoController::class, 'indexChunk']);
