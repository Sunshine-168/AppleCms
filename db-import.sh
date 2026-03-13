#!/bin/bash

# === 自动获取脚本所在目录 ===
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
LOCAL_SQL="${SCRIPT_DIR}/latest.sql"

# === 配置区 ===
OSS_BUCKET="oss://fc-mysql/facai2/full"
SQL_FILE_NAME="facai_latest.sql"
DB_HOST="127.0.0.1"
DB_USER="facai2_v2"
DB_PASS="WrZm7E25wnPR4H6W"
DB_NAME="facai2_v2"

# ⛔ 最小有效 SQL 文件大小(单位: KB，防止下载坏文件)
MIN_SIZE_KB=100

# === 1. 从 OSS 下载最新 SQL 文件 ===
echo "==> 下载最新 SQL 文件..."
ossutil cp -f "${OSS_BUCKET}/${SQL_FILE_NAME}" "${LOCAL_SQL}"
if [ $? -ne 0 ]; then
    echo "❌ SQL 文件下载失败！请检查 OSS 路径和权限。"
    exit 1
fi
echo "✅ SQL 文件下载完成：${LOCAL_SQL}"

# === 1.1 校验 SQL 文件大小 ===
if [ ! -f "${LOCAL_SQL}" ]; then
    echo "❌ SQL 文件不存在: ${LOCAL_SQL}"
    exit 1
fi

FILE_SIZE_KB=$(du -k "${LOCAL_SQL}" | cut -f1)

echo "📌 SQL 文件大小：${FILE_SIZE_KB} KB"

if [ "${FILE_SIZE_KB}" -lt "${MIN_SIZE_KB}" ]; then
    echo "❌ SQL 文件过小（${FILE_SIZE_KB} KB < ${MIN_SIZE_KB} KB）"
    echo "可能是下载失败、OSS 文件错误或内容损坏，为安全起见已停止导入。"
    exit 1
fi

echo "✅ SQL 文件大小校验通过"

# === 2. 删除并重建数据库 ===
echo "==> 删除并重建数据库..."
mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASS}" -e "
DROP DATABASE IF EXISTS \`${DB_NAME}\`;
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;
"
if [ $? -ne 0 ]; then
    echo "❌ 数据库操作失败！"
    exit 1
fi
echo "✅ 数据库已重建：${DB_NAME}"

# === 3. 导入 SQL 文件 ===
echo "==> 导入 SQL 文件到数据库..."
if ! command -v pv &> /dev/null; then
    mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "${LOCAL_SQL}"
else
    pv "${LOCAL_SQL}" | mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}"
fi

if [ $? -ne 0 ]; then
    echo "❌ SQL 导入失败！"
    exit 1
fi

echo "✅ SQL 导入完成！"
echo "📌 SQL 文件位置：${LOCAL_SQL}"
