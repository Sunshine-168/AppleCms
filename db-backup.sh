#!/bin/bash
# MySQL 全量备份并上传 OSS（只保留最新版本）
# Author: sanpang

LOCKFILE="/tmp/db-backup.lock"

(
  flock -n 9 || { echo "[$(date '+%Y-%m-%d %H:%M:%S')] 上一个任务仍在运行，跳过本次执行"; exit 0; }

  ######################
  # MySQL 配置
  ######################
  MYSQL_USER="root"
  MYSQL_PASS="f10f2914a85c7f37"
  MYSQL_HOST="127.0.0.1"
  MYSQL_DB="facai"

  ######################
  # 自动获取项目根目录和 runtime 目录
  ######################
  PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
  RUNTIME_DIR="$PROJECT_DIR/runtime"
  BACKUP_DIR="$RUNTIME_DIR/db_backup"
  mkdir -p "$BACKUP_DIR"

  ######################
  # OSS 配置
  ######################
  OSS_BUCKET="fc-mysql"
  OSS_DIR="facai2"
  OSS_ENDPOINT="oss-cn-shenzhen.aliyuncs.com"

  ######################
  # 执行全量备份
  ######################
  DATE=$(date +%Y%m%d_%H%M%S)
  FULL_FILE="$BACKUP_DIR/${MYSQL_DB}.sql"
  META_FILE="$BACKUP_DIR/${MYSQL_DB}_meta.txt"

  echo "[$(date '+%Y-%m-%d %H:%M:%S')] 🔵 开始备份数据库：$MYSQL_DB"

  # 删除旧的备份（只保留最新文件）
  rm -f "$FULL_FILE" "$META_FILE"

  # 导出最新数据
  if mysqldump -h"$MYSQL_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASS" --single-transaction --routines --events "$MYSQL_DB" > "$FULL_FILE"; then
      echo "[$(date '+%Y-%m-%d %H:%M:%S')] 数据库导出成功: $FULL_FILE"
  else
      echo "[$(date '+%Y-%m-%d %H:%M:%S')] ❌ 数据库导出失败"
      exit 1
  fi

  # 写元信息
  {
      echo "Database: $MYSQL_DB"
      echo "Backup Time: $DATE"
      echo "Backup File: $(basename "$FULL_FILE")"
  } > "$META_FILE"

  # 上传 OSS
  for file in "$FULL_FILE" "$META_FILE"; do
      if ossutil cp "$file" "oss://$OSS_BUCKET/$OSS_DIR/$(basename "$file")" --update --endpoint="$OSS_ENDPOINT"; then
          echo "[$(date '+%Y-%m-%d %H:%M:%S')] 上传成功: $(basename "$file")"
      else
          echo "[$(date '+%Y-%m-%d %H:%M:%S')] ❌ 上传失败: $(basename "$file")"
      fi
  done

  echo "[$(date '+%Y-%m-%d %H:%M:%S')] ✅ 全量备份完成并上传 OSS（最新版本已覆盖）"

) 9>"$LOCKFILE"
