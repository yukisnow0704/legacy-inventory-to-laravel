<?php
require_once("db.php");

$id = isset($_GET['id']) ? $_GET['id'] : '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $qty = $_POST['qty'];

    $select_sql = "SELECT stock_quantity FROM products WHERE id = " . $id;
    $result = mysql_query($select_sql, $conn);
    $row = mysql_fetch_assoc($result);
    $current_qty = $row['stock_quantity'];

    // 在庫がマイナスにならないよう一応チェックしているが、
    // このチェック自体もSELECTからUPDATEまでの間に他のリクエストが割り込むと意味をなさない
    // (TOCTOU: Time-Of-Check to Time-Of-Use バグ)。
    // 例えば残り5個の商品に対して、同時に2つの「5個出庫」リクエストが来ると、
    // 両方とも「チェック時点では5個ある」と判定してしまい、在庫が-5個になり得る。
    if ($current_qty - intval($qty) < 0) {
        $message = "在庫が不足しています(現在庫: " . $current_qty . ")";
    } else {
        $new_qty = $current_qty - intval($qty);
        $update_sql = "UPDATE products SET stock_quantity = " . $new_qty . " WHERE id = " . $id;
        mysql_query($update_sql, $conn);

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
