<?php

namespace App\Traits;

use App\Ttls\QueueInxTTL;
use App\Ttls\QueueSetTTL;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 通用队列触发器 Trait
 */
trait QueueTriggerTrait
{
    /** 每次查询数量 */
    public int $batchSize = 10;

    /** 超时时间（秒） */
    public int $timeout = 1;

    /** 队列名称前缀（例如：ItemSettle） */
    protected string $queueName;

    /** 当前时间片标识 */
    protected string $timer;

    /** 是否启用日志 */
    protected bool $enableLog = true;

    /** 最小睡眠时间（微秒） 0.01秒 */
    protected int $minSleep = 10000;

    public function initTrigger(): void
    {
        $this->timer = date('YmdH');
    }

    /**
     * 触发器执行入口
     */
    public function runTrigger(): void
    {
        $this->initTrigger();

        $startTime = microtime(true);
        $QueueInx  = new QueueInxTTL();
        $QueueSet  = new QueueSetTTL();

        $keyPrefix = "{$this->queueName}:{$this->timer}";
        $QueueSet->exists($keyPrefix);
        $QueueSet->delOtherKey($this->queueName, $this->timer);

        $lastId = (int)($QueueInx->get($this->queueName) ?: 0);

        $this->log("🚀 启动任务：{$this->queueName}");

        // 初始化睡眠时间

        while (true)
        {
            try {

                // 超时退出
                if ((microtime(true) - $startTime) > $this->timeout)
                {
                    $this->log("⏱ 超时退出，lastIndex={$lastId}");
                    break;
                }

                // 查询数据
                $records = $this->getBatch($lastId);


                if (empty($records))
                {
                    break;
                }

                // 推送队列
                $this->pushToJob($records);

                $lastId = max($records);

                $QueueInx->set($this->queueName, $lastId,10);

                // 小间隔等待，防止过快循环
                usleep($this->minSleep);

            } catch (Throwable $e) {
                $this->log("❌ 错误：" . $e->getMessage());
                break;
            }
        }

        $this->log("✅ {$this->queueName} 执行完成。最后ID：{$lastId}");
    }


    /**
     * 获取后续数据
     */
    protected function getBatch(int $lastId): array
    {
        return $this->getList($lastId);
    }

    /**
     * 日志记录
     */
    protected function log(string $msg): void
    {
        if ($this->enableLog) {
            Log::info("[Trigger:{$this->queueName}] {$msg}");
        }
    }

    abstract protected function getList(int $lastId): array;

    abstract protected function pushToJob(array $records);
}
