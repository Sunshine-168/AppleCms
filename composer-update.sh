#!/bin/bash

# 自动获取脚本所在目录（假设脚本放在项目根目录）
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"

# composer.json 路径
COMPOSER_FILE="$PROJECT_DIR/composer.json"

# 用于记录上次修改时间的文件（放在项目根目录或 runtime 目录都可以）
LAST_MODIFIED_FILE="$PROJECT_DIR/composer_modified.log"

# 检查 composer.json 是否存在
if [ ! -f "$COMPOSER_FILE" ]; then
    echo "composer.json 不存在: $COMPOSER_FILE"
    exit 1
fi

# 获取当前 composer.json 的修改时间（秒级时间戳）
CURRENT_MODIFIED_TIME=$(stat -c %Y "$COMPOSER_FILE")

# 如果 last_modified_time 文件不存在，初始化它
if [ ! -f "$LAST_MODIFIED_FILE" ]; then
    echo "$CURRENT_MODIFIED_TIME" > "$LAST_MODIFIED_FILE"
fi

# 读取上次修改的时间
LAST_MODIFIED_TIME=$(cat "$LAST_MODIFIED_FILE")

# 如果 composer.json 被修改过
if [ "$CURRENT_MODIFIED_TIME" -ne "$LAST_MODIFIED_TIME" ]; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') composer.json 发生变化，开始更新 Composer..."
    cd "$PROJECT_DIR" || { echo "项目目录不存在: $PROJECT_DIR"; exit 1; }
    composer update
    echo "$(date '+%Y-%m-%d %H:%M:%S') Composer 更新完成。"

    # 更新 last_modified_time 文件
    echo "$CURRENT_MODIFIED_TIME" > "$LAST_MODIFIED_FILE"
else
    echo "$(date '+%Y-%m-%d %H:%M:%S') composer.json 未变化，无需更新。"
fi
