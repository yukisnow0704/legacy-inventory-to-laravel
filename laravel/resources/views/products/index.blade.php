<!DOCTYPE html>
<html>
<head>
    <title>在庫管理システム</title>
</head>
<body>
    <h1>在庫一覧</h1>

    {{-- 検索フォーム。Blade の {{ }} は自動エスケープされるため、
         旧 index.php にあった value="<?php echo $keyword ?>" の XSS(S-3)は
         移行の副作用として自然に解消されている。 --}}
    <form method="get" action="/products">
        商品名: <input type="text" name="keyword" value="{{ $keyword }}">
        <input type="submit" value="検索">
    </form>

    {{-- 追加・入出庫・削除は未移行。リンクはレガシー側の URL を指す
         (リバースプロキシが *.php をレガシーへ振り分ける)。 --}}
    <p><a href="/add_product.php">+ 新規商品追加</a></p>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>SKU</th>
            <th>商品名</th>
            <th>価格</th>
            <th>在庫数</th>
            <th>操作</th>
        </tr>
        @foreach ($products as $product)
        <tr>
            <td>{{ $product->id }}</td>
            <td>{{ $product->sku }}</td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->price }}円</td>
            <td>
                {{ $product->stock_quantity }}
                @if ($product->stock_quantity <= 5)
                    <span style="color:red">(残りわずか)</span>
                @endif
            </td>
            <td>
                <a href="/stock_in.php?id={{ $product->id }}">入庫</a> |
                <a href="/stock_out.php?id={{ $product->id }}">出庫</a> |
                <a href="/delete_product.php?id={{ $product->id }}"
                   onclick="return confirm('本当に削除しますか?')">削除</a>
            </td>
        </tr>
        @endforeach
    </table>
</body>
</html>
