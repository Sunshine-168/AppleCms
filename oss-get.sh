#!/bin/bash
# 从 OSS 下载指定目录到当前目录（递归覆盖）
# Author: sanpang

# === 配置区 ===
OSS_BUCKET="oss://fc-mysql/"

# 当前脚本所在目录作为下载目录
LOCAL_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] ==> 开始下载 OSS 上传目录到当前目录: ${LOCAL_DIR}"

# === 递归下载所有文件 ===
if ossutil cp -r "${OSS_BUCKET}" "${LOCAL_DIR}"; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ✅ 下载完成，保存到：${LOCAL_DIR}"
else
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ❌ 下载失败！请检查 OSS 路径和权限。"
    exit 1
fi
