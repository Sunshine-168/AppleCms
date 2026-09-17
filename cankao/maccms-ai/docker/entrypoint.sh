#!/bin/sh
set -e

cd /var/www/html

mkdir -p runtime/cache runtime/log runtime/temp runtime/session \
    upload application/data/install application/data/backup

chmod -R 777 runtime upload application/data application/extra 2>/dev/null || true
chmod 666 application/database.php application/route.php 2>/dev/null || true

exec apache2-foreground
