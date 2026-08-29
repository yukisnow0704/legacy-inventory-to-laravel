<?php
require_once("db.php");

// 検索キーワードをそのままSQLに連結している(SQLインジェクション脆弱性)
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

if ($keyword != '') {
    $sql = "SELECT * FROM products WHERE name LIKE '%" . $keyword . "%' ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM products ORDER BY id DESC";
}

$result = mysql_query($sql, $conn);

if (!$result) {
    // クエリ失敗時にエラー内容をそのまま画面に出力してしまっている(情報漏洩)
    die("クエリ失敗: " . mysql_error());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>在庫管理システム</title>
</head>
<body>
    <h1>在庫一覧</h1>

    <!-- フォームの入力値をそのまま value に埋め込んでおり、XSS脆弱性がある -->
    <form method="get" action="index.php">
        商品名: <input type="text" name="keyword" value="<?php echo $keyword; ?>">
        <input type="submit" value="検索">
    </form>

    <p><a href="add_product.php">+ 新規商品追加</a></p>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>SKU</th>
            <th>商品名</th>
            <th>価格</th>
            <th>在庫数</th>
            <th>操作</th>
        </tr>
        <?php while ($row = mysql_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['sku']; ?></td>
            <!-- 商品名をエスケープせずそのまま出力(XSS脆弱性)。
                 商品名に <script> 等が登録されると実行されてしまう -->
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['price']; ?>円</td>
            <td>
                <?php echo $row['stock_quantity']; ?>
                <?php if ($row['stock_quantity'] <= 5) { ?>
                    <span style="color:red">(残りわずか)</span>
                <?php } ?>
            </td>
            <td>
                <a href="stock_in.php?id=<?php echo $row['id']; ?>">入庫</a> |
                <a href="stock_out.php?id=<?php echo $row['id']; ?>">出庫</a> |
                <!-- GETリクエストだけで削除が実行できてしまう(CSRF脆弱性)。
                     このリンクを踏むだけ、あるいはこのURLを画像タグ等に埋め込まれるだけで削除される -->
                <a href="delete_product.php?id=<?php echo $row['id']; ?>"
                   onclick="return confirm('本当に削除しますか?')">削除</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
