FROM php:5.6-apache

# mysql_* 関数群を使うための古典的な mysql 拡張と、
# 比較用に mysqli も入れておく(mysql拡張はPHP 5.5以降非推奨、PHP 7で完全削除された)
RUN docker-php-ext-install mysql mysqli

# ベースイメージ(Debian Stretch)は既にEOLで、標準のパッケージリポジトリ
# (deb.debian.org / security.debian.org)が404を返すようになっている。
# アーカイブリポジトリ(archive.debian.org)に向け直す。
# - stretch-updates はarchive.debian.org上に存在しないため行ごと削除
# - Release検証(期限切れ)とGPG署名検証(署名鍵自体が期限切れ)を無効化する
RUN sed -i \
    -e 's|deb.debian.org|archive.debian.org|g' \
    -e 's|security.debian.org/debian-security|archive.debian.org/debian-security|g' \
    -e '/stretch-updates/d' \
    /etc/apt/sources.list \
    && printf 'Acquire::Check-Valid-Until "false";\nAcquire::AllowInsecureRepositories "true";\nAPT::Get::AllowUnauthenticated "true";\n' \
    > /etc/apt/apt.conf.d/99no-check-valid-until

# コンテナ内から検証スクリプト(verify_race_condition.sh)を実行するために、
# curl と mysqlクライアントを追加でインストールしておく
RUN apt-get update && apt-get install -y --no-install-recommends --allow-unauthenticated \
    curl \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# エラーを画面に表示する設定(開発用。本番でこれをやってしまっているのもレガシーあるある)
RUN { \
    echo 'display_errors = On'; \
    echo 'error_reporting = E_ALL'; \
    } > /usr/local/etc/php/conf.d/legacy-error-display.ini