#!/bin/bash

# 自动获取脚本所在目录（假设脚本放在项目根目录）
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"

# 要执行的 CLI 命令（定时任务调度器）
COMMAND="php think ScheduledTask"

# 无限循环，每 1 秒执行一次命令
while true; do
    echo "$(date '+%Y-%m-%d %H:%M:%S') 执行命令: $COMMAND"

    # 进入项目目录执行命令
    cd "$PROJECT_DIR" || { echo "项目目录不存在: $PROJECT_DIR"; exit 1; }
    $COMMAND

    # 检查命令是否成功执行
    if [ $? -ne 0 ]; then
        echo "$(date '+%Y-%m-%d %H:%M:%S') 命令执行失败，等待 1 秒后重试..."
        sleep 1
    else
        echo "$(date '+%Y-%m-%d %H:%M:%S') 命令执行成功！"
    fi

    # 等待 1 秒
    sleep 1
done
