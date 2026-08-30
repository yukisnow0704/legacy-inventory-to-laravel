<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// 在庫一覧(旧 index.php)。Strangler Fig でこの画面だけを Laravel へ移行。
// リバースプロキシは "/" と "/products" を Laravel に振り分ける。
Route::get('/', [ProductController::class, 'index']);
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
