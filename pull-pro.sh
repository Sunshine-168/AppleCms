#!/bin/bash

# 获取脚本所在目录（绝对路径）
WORK_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$WORK_DIR" || { echo "目录不存在"; exit 1; }

echo "工作目录：$WORK_DIR"

# 保存当前 commit
OLD_COMMIT=$(git rev-parse HEAD)

# 拉取最新代码
echo "拉取最新的代码..."
git pull origin main

# 保存拉取后的 commit
NEW_COMMIT=$(git rev-parse HEAD)

if [[ "$OLD_COMMIT" == "$NEW_COMMIT" ]]; then
    echo "没有更新，跳过权限设置。"
    exit 0
fi

# 检查是否有未提交的更改
if [[ $(git status --porcelain) ]]; then
    echo "存在未提交的更改，请先处理这些更改。"
    exit 1
fi

echo "代码更新完成！"

# 修改属主为 www:www
echo "设置属主 www:www ..."
chown -R www:www "$WORK_DIR"

# 所有文件设置为 755
echo "设置权限为 755 ..."
chmod -R 755 "$WORK_DIR"

echo "全部完成！"
