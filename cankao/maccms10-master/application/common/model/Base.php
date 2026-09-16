<?php

namespace app\common\model;

use think\Config as ThinkConfig;
use think\Model;
use think\Db;
use think\Cache;

class Base extends Model
{
    protected $tablePrefix;
    protected $primaryId;
    protected $readFromMaster;

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        // 自定义的初始化
        $this->tablePrefix = isset($this->tablePrefix) ? $this->tablePrefix : ThinkConfig::get('database.prefix');
        $this->primaryId = isset($this->primaryId) ? $this->primaryId : $this->name . '_id';
        $this->readFromMaster = isset($this->readFromMaster) ? $this->readFromMaster : false;
        // 表创建或修改
        if (method_exists($this, 'createTableIfNotExists')) {
            $this->createTableIfNotExists();
        }
    }

    public function getCountByCond($cond)
    {
        $query_object = $this;
        if ($this->readFromMaster === true) {
            $query_object = $query_object->master();
        }
        return (int)$query_object->where($cond)->count();
    }

    public function getListByCond($offset, $limit, $cond, $orderby = '', $fields = "*", $transform = false)
    {
        $offset = max(0, (int)$offset);
        $limit = max(1, (int)$limit);

        if (empty($orderby)) {
            $orderby = $this->primaryId . " DESC";
        } else {
            if (strpos($orderby, $this->primaryId) === false) {
                $orderby .= ", " . $this->primaryId . " DESC";
            }
        }

        $query_object = $this;
        if ($this->readFromMaster === true) {
            $query_object = $query_object->master();
        }
        $list = $query_object->where($cond)->field($fields)->order($orderby)->limit($offset, $limit)->select();
        if (!$list) {
            return [];
        }
        $final = [];
        foreach ($list as $row) {
            $row_array = $row->getData();
            if ($transform !== false) {
                $row_array = $this->transformRow($row_array, $transform);
            }
            $final[] = $row_array;
        }
        return $final;
    }

    public function transformRow($row, $extends = []) {
        return $row;
    }

    /**
     * 原子 upsert：按唯一键 INSERT ... ON DUPLICATE KEY UPDATE，一条 SQL 完成插入或更新，
     * 取代「先 find 再 insert/update」的两次查询，并规避并发首插撞唯一键。
     * 列名取自入参数组，占位符参数化，无 SQL 注入面。
     * @param array $row        插入并在冲突时更新的列
     * @param array $insertOnly 仅插入时写入、冲突时保留的列（如 time_add）
     * @return int 影响行数（新插入=1，更新=2，无变化=0）
     */
    protected function upsertByUnique(array $row, array $insertOnly = [])
    {
        $all = array_merge($row, $insertOnly);
        $cols = array_keys($all);
        $table = $this->tablePrefix . $this->name;
        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $colSql = '`' . implode('`,`', $cols) . '`';
        $updates = [];
        foreach (array_keys($row) as $c) {
            $updates[] = "`{$c}`=VALUES(`{$c}`)";
        }
        $sql = "INSERT INTO `{$table}` ({$colSql}) VALUES ({$placeholders}) "
             . "ON DUPLICATE KEY UPDATE " . implode(',', $updates);
        return Db::execute($sql, array_values($all));
    }
}