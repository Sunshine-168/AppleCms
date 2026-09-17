<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\Admin\System\SysDatabaseBackupService;
use App\Services\Admin\System\SysDatabaseReplaceService;
use App\Services\Admin\System\SysDatabaseService;
use App\Services\Admin\System\SysDatabaseSqlService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * 数据库：备份、恢复、SQL、替换、字段说明
 */
class SysDatabase extends Controller
{
    protected SysDatabaseService $systemDatabaseService;
    protected SysDatabaseBackupService $systemDatabaseBackupService;
    protected SysDatabaseSqlService $systemDatabaseSqlService;
    protected SysDatabaseReplaceService $systemDatabaseReplaceService;

    public function __construct()
    {
        $this->systemDatabaseService = new SysDatabaseService();
        $this->systemDatabaseBackupService = new SysDatabaseBackupService();
        $this->systemDatabaseSqlService = new SysDatabaseSqlService();
        $this->systemDatabaseReplaceService = new SysDatabaseReplaceService();
    }
    /**
     * 显示库表字段页
     * @return View|Factory
     */
    public function showDatabaseDict(Request $request): View|Factory
    {
        return view('admin.system.database.dict', $this->systemDatabaseService->pageBoard((string) $request->input('table', '')));
    }

    /**
     * 获取数据库表列表
     * @return JsonResponse
     */
    public function getDatabaseTables(): JsonResponse
    {
        $data = $this->systemDatabaseService->getDatabaseTables();
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 获取数据库表字段列表
     * @param Request $request
     * @return JsonResponse
     */
    public function getDatabaseColumns(Request $request): JsonResponse
    {
        $table = (string) $request->input('table', '');
        $data  = $this->systemDatabaseService->getDatabaseColumns($table);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 显示数据库备份页
     * @return View|Factory
     */
    public function showDatabaseBackup(): View|Factory
    {
        return view('admin.system.database.backup', $this->systemDatabaseBackupService->pageBoard());
    }

    /**
     * 执行数据库备份
     */
    public function runDatabaseBackup(Request $request): JsonResponse
    {
        $keep = (int) $request->input('keep', -1);
        if ($keep < 0) {
            $keep = $this->systemDatabaseBackupService->currentKeep();
        }
        $data = $this->systemDatabaseBackupService->runBackup($keep);

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    /**
     * 保存备份定时（写入计划任务）
     */
    public function saveDatabaseBackupSchedule(Request $request): JsonResponse
    {
        $on = $request->boolean('on');
        $cron = (string) $request->input('cron', '0 3 * * *');
        $keep = (int) $request->input('keep', 7);
        $data = $this->systemDatabaseBackupService->saveBackupSchedule($on, $cron, $keep);

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    /**
     * 获取备份文件列表
     * @return JsonResponse
     */
    public function getDatabaseBackupFiles(): JsonResponse
    {
        $data = $this->systemDatabaseBackupService->listBackupFiles();
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
    /**
     * 下载数据库备份文件
     * @param Request $request
     * @return BinaryFileResponse|JsonResponse
     */
    public function downloadDatabaseBackupFile(Request $request): BinaryFileResponse|JsonResponse
    {
        $file = (string) $request->input('file', '');
        $res  = $this->systemDatabaseBackupService->resolveBackupFilePath($file);
        if ($res['code'] !== 0)
        {
            return Ajax::message($res['code'], $res['msg'], $res['data'] ?? []);
        }

        return response()->download($res['data']['path'], $res['data']['name']);
    }
    /**
     * 删除数据库备份文件
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDatabaseBackupFile(Request $request): JsonResponse
    {
        $file = (string) $request->input('file', '');
        $data = $this->systemDatabaseBackupService->deleteBackupFile($file);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 显示数据库恢复页
     * @return View|Factory
     */
    public function showDatabaseRestore(): View|Factory
    {
        return view('admin.system.database.restore', $this->systemDatabaseBackupService->pageBoard());
    }

    /**
     * 获取可恢复的备份文件列表
     * @return JsonResponse
     */
    public function getDatabaseRestoreFiles(): JsonResponse
    {
        $data = $this->systemDatabaseBackupService->listBackupFiles();
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 执行数据库恢复
     * @param Request $request
     * @return JsonResponse
     */
    public function runDatabaseRestore(Request $request): JsonResponse
    {
        $file = (string) $request->input('file', '');
        $data = $this->systemDatabaseBackupService->restoreBackup($file, $request->boolean('snapshot'));
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 显示SQL执行页
     * @return View|Factory
     */
    public function showDatabaseSql(): View|Factory
    {
        return view('admin.system.database.sql', $this->systemDatabaseSqlService->pageBoard());
    }

    /**
     * 执行SQL
     * @param Request $request
     * @return JsonResponse
     */
    public function runDatabaseSql(Request $request): JsonResponse
    {
        $data = $this->systemDatabaseSqlService->run(
            (string) $request->input('sql', ''),
            (string) $request->input('word', '')
        );
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 显示数据批量替换页
     */
    public function showDatabaseReplace(): View|Factory
    {
        return view('admin.system.database.replace', $this->systemDatabaseReplaceService->pageBoard());
    }

    /**
     * 先数会改几条
     */
    public function previewDatabaseReplace(Request $request): JsonResponse
    {
        [$target, $fields, $from] = $this->replaceInput($request);
        $data = $this->systemDatabaseReplaceService->preview($target, $fields, $from);

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    /**
     * 执行数据批量替换
     */
    public function runDatabaseReplace(Request $request): JsonResponse
    {
        [$target, $fields, $from] = $this->replaceInput($request);
        $to = (string) $request->input('to', '');
        $data = $this->systemDatabaseReplaceService->run($target, $fields, $from, $to);

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    /**
     * @return array{0: string, 1: list<string>, 2: string}
     */
    private function replaceInput(Request $request): array
    {
        $target = trim((string) $request->input('target', ''));
        if ($target === '') {
            $target = $this->systemDatabaseReplaceService->targetIdFromTable((string) $request->input('table', ''));
        }
        $fields = $request->input('fields', []);
        if (! is_array($fields)) {
            $fields = [];
        }

        return [$target, $fields, (string) $request->input('from', '')];
    }
}
