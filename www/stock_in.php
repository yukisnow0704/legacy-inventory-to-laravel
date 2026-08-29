<?php
require_once("db.php");

$id = isset($_GET['id']) ? $_GET['id'] : '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $qty = intval($_POST['qty']);

    // 「SELECT→PHPで加算→UPDATE」をやめ、DB側でアトミックに加算する。
    // これにより同時リクエストでも更新の消失(lost update)が起きない。
    // 値のバインドで SQLインジェクションも防ぐ。
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'ii', $qty, $id);
    mysqli_stmt_execute($stmt);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>入庫処理</title>
</head>
<body>
    <h1>入庫処理</h1>
    <form method="post" action="stock_in.php">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        入庫数: <input type="text" name="qty">
        <input type="submit" value="入庫する">
    </form>
    <p><a href="index.php">← 一覧に戻る</a></p>
</body>
</html>
