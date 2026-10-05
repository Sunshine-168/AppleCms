#!/usr/bin/env bash
# 一条命令装好苹果v12。用法：
#   bash install.sh
#   bash install.sh YourPass#1
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT"
PASS="${1:-admin123}"

pick_php() {
    local c ver
    for c in php php84 php8.4 \
        /www/server/php/85/bin/php \
        /www/server/php/84/bin/php \
        /usr/bin/php; do
        if command -v "$c" >/dev/null 2>&1 || [ -x "$c" ]; then
            ver="$("$c" -r 'echo PHP_VERSION;' 2>/dev/null || true)"
            if [ -n "$ver" ] && "$c" -r 'exit(version_compare(PHP_VERSION, "8.4.0", ">=") ? 0 : 1);' 2>/dev/null; then
                echo "$c"
                return 0
            fi
        fi
    done
    return 1
}

PHP="$(pick_php || true)"
if [ -z "${PHP}" ]; then
    echo "需要 PHP 8.4 或更高。宝塔请把网站 PHP 切到 8.4。"
    exit 1
fi

COMPOSER_BIN=""
COMPOSER_PHAR=""
for c in composer \
    /usr/bin/composer /usr/local/bin/composer; do
    if command -v "$c" >/dev/null 2>&1 || [ -x "$c" ]; then
        COMPOSER_BIN="$c"
        break
    fi
done
if [ -z "${COMPOSER_BIN}" ]; then
    echo "正在下载 Composer…"
    "$PHP" -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    "$PHP" composer-setup.php --install-dir="$ROOT" --filename=composer.phar
    rm -f composer-setup.php
    COMPOSER_PHAR="$ROOT/composer.phar"
fi

echo "PHP  $($PHP -r 'echo PHP_VERSION;')"
echo "项目 $ROOT"

if [ -n "${COMPOSER_BIN}" ]; then
    "$COMPOSER_BIN" install --no-interaction --prefer-dist --no-ansi
else
    "$PHP" "$COMPOSER_PHAR" install --no-interaction --prefer-dist --no-ansi
fi
[ -f .env ] || cp .env.example .env
"$PHP" artisan key:generate --force --ansi >/dev/null

mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions storage/logs storage/app bootstrap/cache database
touch database/database.sqlite
chmod -R ug+rwx storage bootstrap/cache database || true

if [ -f storage/app/install.lock ]; then
    echo "已经装过（storage/app/install.lock 还在）。要重装先删掉这个文件。"
else
    "$PHP" artisan video:install --password="$PASS" --demo --ansi
fi

echo
echo "装好了。"
echo "  试用：  $PHP artisan serve --host=0.0.0.0 --port=8010"
echo "  前台：  http://127.0.0.1:8010/"
echo "  后台：  http://127.0.0.1:8010/admin/login"
echo "  账号：  admin"
echo "  密码：  $PASS"
echo
echo "宝塔：网站根目录指到 public，PHP 选 8.4，storage 和 bootstrap/cache 给 www 可写。"
echo "定时每分钟："
echo "  $PHP $ROOT/artisan schedule:run >> /dev/null 2>&1"
