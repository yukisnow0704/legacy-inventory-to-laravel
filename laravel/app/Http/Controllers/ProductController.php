<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * 在庫一覧(旧 index.php 相当)。
     * - キーワードによる商品名の部分一致検索
     * - id の降順で表示
     */
    public function index(Request $request)
    {
        $keyword = (string) $request->query('keyword', '');

        $query = Product::query()->orderByDesc('id');

        if ($keyword !== '') {
            // Eloquent がプレースホルダでバインドする(SQLインジェクション対策)
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        $products = $query->get();

        return view('products.index', [
            'products' => $products,
            'keyword' => $keyword,
        ]);
    }
}
