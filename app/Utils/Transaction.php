<?php
namespace App\Utils;

use Illuminate\Support\Facades\DB;


/**
 * 是否在mysql事务里
 */
class Transaction
{
    public static function isInTransaction(string $connection = ''): bool
    {
        try {

            $pdo = Db::connect($connection)->getPdo();

            return $pdo ? $pdo->inTransaction() : false;

        } catch (\Throwable $e)
        {
            // 出现异常就认为不在事务中
            return false;
        }
    }


}
