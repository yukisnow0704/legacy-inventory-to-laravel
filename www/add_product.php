<?php
require_once("db.php");

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 入力値のバリデーションが一切ない。
    // 空文字・マイナス値・文字列(価格や在庫数に文字列が来た場合)などを弾く処理がない。
    $sku = $_POST['sku'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];

    // ユーザー入力を文字列連結で直接SQLに埋め込んでいる(SQLインジェクション脆弱性)。
    // 例えば name に \', (SELECT password FROM admin_users LIMIT 1), 0, 0) -- のような
    // 値を送ると、任意のSQLが実行できてしまう。
    $sql = "INSERT INTO products (sku, name, price, stock_quantity) VALUES ('"
        . $sku . "', '" . $name . "', " . $price . ", " . $stock_quantity . ")";

    $result = mysql_query($sql, $conn);

    if ($result) {
        header("Location: index.php");
        exit;
    } else {
        $error = "登録に失敗しました: " . mysql_error();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>商品追加</title>
</head>
<body>
    <h1>商品追加</h1>

    <?php if ($error != '') { ?>
        <p style="color:red"><?php echo $error; ?></p>
    <?php } ?>

    <form method="post" action="add_product.php">
        SKU: <input type="text" name="sku"><br>
        商品名: <input type="text" name="name"><br>
        価格: <input type="text" name="price"><br>
        初期在庫数: <input type="text" name="stock_quantity"><br>
        <input type="submit" value="登録">
    </form>

    <p><a href="index.php">← 一覧に戻る</a></p>
</body>
</html>
