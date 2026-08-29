<?php
require_once("db.php");

$id = isset($_GET['id']) ? $_GET['id'] : '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $qty = intval($_POST['qty']);

    // 「SELECT→チェック→UPDATE」をやめ、在庫が足りる場合だけ更新する
    // 条件付き UPDATE をアトミックに実行する。これにより TOCTOU
    // (チェックと更新の間に別リクエストが割り込むバグ)を解消し、
    // 同時出庫でも在庫がマイナスにならない。値のバインドで
    // SQLインジェクションも防ぐ。
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?"
    );
    mysqli_stmt_bind_param($stmt, 'iii', $qty, $id, $qty);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        header("Location: index.php");
        exit;
    }

    // 更新対象が無かった。現在庫を読み直して理由を判定する。
    $sel = mysqli_prepare($conn, "SELECT stock_quantity FROM products WHERE id = ?");
    mysqli_stmt_bind_param($sel, 'i', $id);
    mysqli_stmt_execute($sel);
    $res = mysqli_stmt_get_result($sel);
    $row = mysqli_fetch_assoc($res);
    $current_qty = $row ? $row['stock_quantity'] : 0;

    if ($current_qty - $qty < 0) {
        $message = "在庫が不足しています(現在庫: " . $current_qty . ")";
    } else {
        // qty=0 など、在庫は足りているが更新行が無かったケースは従来通りリダイレクト。
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>出庫処理</title>
</head>
<body>
    <h1>出庫処理</h1>

    <?php if ($message != '') { ?>
        <p style="color:red"><?php echo $message; ?></p>
    <?php } ?>

    <form method="post" action="stock_out.php">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        出庫数: <input type="text" name="qty">
        <input type="submit" value="出庫する">
    </form>
    <p><a href="index.php">← 一覧に戻る</a></p>
</body>
</html>
