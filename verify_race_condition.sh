#!/usr/bin/env bash
# stock_in.php に対して同時に複数の入庫リクエストを送り、
# 「SELECT→PHP側で加算→UPDATE」という実装が実際に競合状態(lost update)を
# 起こすかどうかを、本物のPHP 5.6 + MySQL 5.6環境で確認するスクリプト。
#
# ★ このスクリプトは legacy-web コンテナの「中」で実行する前提です。
#    ホスト側(Windows)にbashが無くても、Docker Desktopさえあれば実行できます。
#
# 前提: docker-compose up -d --build 済みであること
#
# 使い方(ホスト側のターミナル/PowerShellから):
#   docker-compose exec legacy-web bash /var/www/verify_race_condition.sh [商品ID] [リクエスト数] [1回あたりの入庫数]
#   例: docker-compose exec legacy-web bash /var/www/verify_race_condition.sh 1 10 10

set -eu

PRODUCT_ID="${1:-1}"
REQUEST_COUNT="${2:-10}"
QTY_PER_REQUEST="${3:-10}"

# コンテナ内からは、Webサーバーは localhost(ポート80)、
# DBは docker-compose.yml のサービス名 legacy-mysql でそれぞれ到達できる。
BASE_URL="http://localhost"
DB_HOST="legacy-mysql"
DB_USER="root"
DB_PASS="my-secret-pw"
DB_NAME="legacy_inventory"

mysql_query() {
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "$1" 2>/dev/null
}

echo "=== 商品ID ${PRODUCT_ID} の開始前の在庫 ==="
BEFORE=$(mysql_query "SELECT stock_quantity FROM products WHERE id = ${PRODUCT_ID};")
echo "開始前の在庫: ${BEFORE}"

if [ -z "$BEFORE" ]; then
    echo "エラー: 商品ID ${PRODUCT_ID} が見つかりません。" >&2
    exit 1
fi

echo ""
echo "=== ${REQUEST_COUNT}本の入庫リクエスト(各+${QTY_PER_REQUEST})を同時実行 ==="

for i in $(seq 1 "$REQUEST_COUNT"); do
    curl -s -X POST "${BASE_URL}/stock_in.php" \
        -d "id=${PRODUCT_ID}&qty=${QTY_PER_REQUEST}" \
        -o /dev/null &
done
wait

echo "全リクエスト送信完了"

echo ""
echo "=== 結果確認 ==="
AFTER=$(mysql_query "SELECT stock_quantity FROM products WHERE id = ${PRODUCT_ID};")
EXPECTED=$((BEFORE + REQUEST_COUNT * QTY_PER_REQUEST))

echo "開始前の在庫: ${BEFORE}"
echo "終了後の在庫: ${AFTER}"
echo "期待値(${BEFORE} + ${REQUEST_COUNT} x ${QTY_PER_REQUEST}): ${EXPECTED}"

if [ "$AFTER" -ne "$EXPECTED" ]; then
    LOST=$((EXPECTED - AFTER))
    echo ""
    echo "★ lost update が発生しました(${LOST}個分が消失)"
else
    echo ""
    echo "今回はタイミングにより競合が発生しませんでした。もう一度実行してみてください。"
fi

echo ""
echo "=== 元の在庫数に戻す(再実行用) ==="
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    -e "UPDATE products SET stock_quantity = ${BEFORE} WHERE id = ${PRODUCT_ID};" 2>/dev/null
echo "在庫を ${BEFORE} に戻しました"
