<?php

namespace addons\mpt\service;

/**
 * MoneyPrinterTurbo REST 客户端。
 * 路由前缀 /api/v1，鉴权走 x-api-key 请求头。
 *
 * 所有方法返回 ['code'=>0|1,'msg'=>'','data'=>[]]，不抛异常。
 * 对外 msg 一律经过 Safety::safeMessage() 脱敏。
 */
class MptClient
{
    /**
     * MPT 的 state 字段是数字，不是字符串（实测）。
     *
     * 这里**只声明用得上的两个**。「完成」那一档（state==1）刻意不给常量：
     * queryTask() 的完成判据是 progress==100 且 videos[] 非空，从不看 state 字面
     * （理由见该方法的说明）。摆一个 STATE_DONE 在这儿没有任何调用点，只会让人
     * 以为还存在一条按 state 判完成的路径，进而照着它写出一个会拿到空 videos 的分支。
     */
    const STATE_FAILED  = -1;
    const STATE_RUNNING = 4;

    protected $base = '';
    protected $key = '';
    protected $timeout = 20;
    /** 证书校验，默认开；只有站长在设置里显式关掉才是 false */
    protected $verifySsl = true;

    public function __construct(array $cfg)
    {
        $this->base = Safety::normalizeBase(isset($cfg['api_base']) ? $cfg['api_base'] : '');
        // 不是 trim() 就够：这个值直接拼进 `x-api-key: {key}` 交给 CURLOPT_HTTPHEADER，
        // 值里的 \r\n 会截断请求头并注入新头。TaskRunner::config() 在读取处已经收过
        // 一次口，但本类是 public 构造、收任意数组，够不着那一层 —— 自己再收一次，
        // 不依赖「调用方恰好传的是 config() 的结果」。见 Safety::sanitizeHeaderValue()。
        $this->key = Safety::sanitizeHeaderValue(isset($cfg['api_key']) ? $cfg['api_key'] : '');
        $this->timeout = Safety::clampTimeout(isset($cfg['timeout']) ? $cfg['timeout'] : 20);
        $this->verifySsl = !isset($cfg['verify_ssl']) || (bool) $cfg['verify_ssl'];
    }

    public function isConfigured()
    {
        return $this->base !== '' && $this->key !== '';
    }

    protected function headers()
    {
        return array('x-api-key: ' . $this->key);
    }

    protected function fail($detail)
    {
        return array('code' => 0, 'msg' => Safety::safeMessage($detail, array($this->key)), 'data' => array());
    }

    /**
     * 提交生成任务。
     * @return array data.remote_id
     */
    public function submit(array $payload)
    {
        if (!$this->isConfigured()) {
            return $this->fail(lang('mpt/err_not_configured'));
        }
        $url = Safety::joinUrl($this->base, 'api/v1/videos');
        $resp = Safety::postJson($url, $payload, $this->headers(), $this->timeout, $this->verifySsl);
        if ($resp === null) {
            return $this->fail(lang('mpt/err_unreachable'));
        }
        if (!isset($resp['data']['task_id']) || !is_string($resp['data']['task_id'])) {
            // message 可能是对象/数组，裸 (string) 会吐一句 notice 加一个 "Array"，
            // 见 Safety::scalarText()。取不到可读文案就回落到通用错误。
            $msg = isset($resp['message']) ? Safety::scalarText($resp['message']) : '';
            if ($msg === '') {
                $msg = lang('mpt/err_bad_response');
            }

            return $this->fail($msg);
        }
        $remoteId = trim($resp['data']['task_id']);
        if ($remoteId === '' || !preg_match('/^[A-Za-z0-9_\-]{8,64}$/', $remoteId)) {
            return $this->fail(lang('mpt/err_bad_response'));
        }

        return array('code' => 1, 'msg' => '', 'data' => array('remote_id' => $remoteId));
    }

    /**
     * 查询任务进度。
     *
     * 完成的判定**不看 state 字面**：以 progress == 100 且 videos[] 非空为准。
     * MPT 在部分阶段会先把 state 置 1 但产物还没落盘，只认 state 会拿到空 videos。
     *
     * @return array data: done(bool) failed(bool) progress(int) path(string 已规范化的相对路径)
     */
    public function queryTask($remoteId)
    {
        if (!$this->isConfigured()) {
            return $this->fail(lang('mpt/err_not_configured'));
        }
        $remoteId = trim((string) $remoteId);
        if (!preg_match('/^[A-Za-z0-9_\-]{8,64}$/', $remoteId)) {
            return $this->fail(lang('mpt/err_bad_task_id'));
        }
        $url = Safety::joinUrl($this->base, 'api/v1/tasks/' . rawurlencode($remoteId));
        $resp = Safety::getJson($url, $this->headers(), $this->timeout, $this->verifySsl);
        if ($resp === null) {
            return $this->fail(lang('mpt/err_unreachable'));
        }
        $d = isset($resp['data']) && is_array($resp['data']) ? $resp['data'] : array();

        $state = isset($d['state']) ? intval($d['state']) : self::STATE_RUNNING;
        $progress = isset($d['progress']) ? intval($d['progress']) : 0;
        if ($progress < 0) {
            $progress = 0;
        }
        if ($progress > 100) {
            $progress = 100;
        }

        // ★ videos 在前，combined_videos 只做回退 ★
        // MPT 侧这两个字段不是同一份产物（app/services/task.py 的
        // generate_final_videos 返回 final_video_paths, combined_video_paths）：
        // videos = final-N.mp4，字幕烧录、BGM 混音都在这一步之后才有；
        // combined_videos = combined-N.mp4，只是素材拼接完的中间件。
        // 取错的后果不是拿不到片子，而是站长把「烧录字幕」开着、成片里却没有字幕，
        // 且完全没有报错——只能靠肉眼看出来。
        $videos = array();
        if (!empty($d['videos']) && is_array($d['videos'])) {
            $videos = $d['videos'];
        } elseif (!empty($d['combined_videos']) && is_array($d['combined_videos'])) {
            $videos = $d['combined_videos'];
        }

        $path = '';
        foreach ($videos as $v) {
            $p = Safety::normalizeResultPath($v);
            if ($p !== '') {
                $path = $p;
                break;
            }
        }

        $done = ($progress >= 100 && $path !== '');
        $failed = ($state === self::STATE_FAILED);

        $err = '';
        if ($failed) {
            // error / failed_stage 都直接来自对端 JSON，可能是对象或数组；
            // 裸 (string) 会吐一句 notice 加一个 "Array"，见 Safety::scalarText()。
            $err = isset($d['error']) ? Safety::scalarText($d['error']) : '';
            if ($err === '' && isset($d['failed_stage'])) {
                $stage = Safety::scalarText($d['failed_stage']);
                if ($stage !== '') {
                    $err = 'failed_stage=' . $stage;
                }
            }
            if ($err === '') {
                $err = lang('mpt/err_remote_failed');
            }
            $err = Safety::safeMessage($err, array($this->key));
        }

        return array('code' => 1, 'msg' => '', 'data' => array(
            'state' => $state,
            'progress' => $progress,
            'done' => $done,
            'failed' => $failed,
            'path' => $path,
            'error' => $err,
        ));
    }

    /**
     * 下载成片到本地绝对路径。
     * $relPath 必须是 queryTask() 返回的、已经过 Safety::normalizeResultPath() 的路径。
     */
    public function download($relPath, $absSavePath)
    {
        if (!$this->isConfigured()) {
            return $this->fail(lang('mpt/err_not_configured'));
        }
        $relPath = Safety::normalizeResultPath($relPath);
        if ($relPath === '') {
            return $this->fail(lang('mpt/err_bad_result_path'));
        }
        $url = Safety::joinUrl($this->base, 'api/v1/download/' . $relPath);
        if (!Safety::downloadToFile($url, $this->headers(), $this->timeout, $absSavePath, $this->verifySsl)) {
            return $this->fail(lang('mpt/err_download_failed'));
        }

        return array('code' => 1, 'msg' => '', 'data' => array());
    }

    /**
     * 探活：用一个不存在的 task id 打一次 tasks 接口。
     * 能拿到 JSON 就说明地址通且 key 被接受（key 错会是 401，解析不出 JSON 或带错误码）。
     */
    public function ping()
    {
        if (!$this->isConfigured()) {
            return $this->fail(lang('mpt/err_not_configured'));
        }
        $url = Safety::joinUrl($this->base, 'api/v1/tasks/mpt-probe-0000');
        $resp = Safety::getJson($url, $this->headers(), $this->timeout, $this->verifySsl);
        if ($resp === null) {
            return $this->fail(lang('mpt/err_unreachable'));
        }
        $status = isset($resp['status']) ? intval($resp['status']) : 0;
        if ($status === 401 || $status === 403) {
            return $this->fail(lang('mpt/err_unauthorized'));
        }

        return array('code' => 1, 'msg' => lang('mpt/ping_ok'), 'data' => array());
    }
}
