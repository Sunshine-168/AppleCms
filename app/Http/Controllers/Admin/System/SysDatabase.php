<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\System\SysDatabaseBackupService;
use App\Services\System\SysDatabaseReplaceService;
use App\Services\System\SysDatabaseService;
use App\Services\System\SysDatabaseSqlService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * 数据库字典
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
     * 显示数据库字典页
     * @return View|Factory
     */
    public function showDatabaseDict(): View|Factory
    {
        return view('admin.system.database.dict');
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
        return view('admin.system.database.backup');
    }

    /**
     * 执行数据库备份
     * @return JsonResponse
     */
    public function runDatabaseBackup(): JsonResponse
    {
        $data = $this->systemDatabaseBackupService->runBackup();
        return Ajax::message($data['code'], $data['msg'], $data['data']);
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
        return view('admin.system.database.restore');
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
        $data = $this->systemDatabaseBackupService->restoreBackup($file);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
     
    /**
     * 显示SQL执行页
     * @return View|Factory
     */
    public function showDatabaseSql(): View|Factory
    {
        return view('admin.system.database.sql');
    }

    /**
     * 执行SQL
     * @param Request $request
     * @return JsonResponse
     */
    public function runDatabaseSql(Request $request): JsonResponse
    {
        $sql = (string) $request->input('sql', '');
        $data = $this->systemDatabaseSqlService->run($sql);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 显示数据批量替换页
     * @return View|Factory
     */
    public function showDatabaseReplace(): View|Factory
    {
        return view('admin.system.database.replace');
    }

    /**
     * 执行数据批量替换
     * @param Request $request
     * @return JsonResponse
     */
    public function runDatabaseReplace(Request $request): JsonResponse
    {
        $table = (string) $request->input('table', '');
        $fields = $request->input('fields', []);
        if (!is_array($fields))
        {
            $fields = [];
        }
        $from = (string) $request->input('from', '');
        $to = (string) $request->input('to', '');
        $where = (string) $request->input('where', '');

        $data = $this->systemDatabaseReplaceService->run($table, $fields, $from, $to, $where);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
}
