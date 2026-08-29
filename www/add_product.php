<?php
require_once("db.php");

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 入力値のバリデーションは今回のスコープ外(フェーズ2)。
    $sku = $_POST['sku'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];

    // ユーザー入力はプレースホルダでバインドする(SQLインジェクション対策)。
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO products (sku, name, price, stock_quantity) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, 'ssii', $sku, $name, $price, $stock_quantity);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        header("Location: index.php");
        exit;
    } else {
        $error = "登録に失敗しました";
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
