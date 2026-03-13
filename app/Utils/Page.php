<?php
namespace App\Utils;


class Page
{
    /**
     * 数组分页
     * @param array $data
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function slice(array $data, int $page = 0, int $limit = 10): array
    {
        //设置偏移量
        $start   =  $page == 0 ?  0 : ( $page - 1 ) * $limit;
        return array_slice($data, $start, $limit,true);
    }


    /**
     * 生成分页 array
     * @param array $data
     * @param int $count
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public static  function info(array $data, int $count, int $page, int $pageSize = 10):array
    {
        return [
            'data'         => $data,
            'total'        => $count,
            'per_page'     => $pageSize,
            'current_page' => $page,
            'last_page'    => (int)ceil($count / $pageSize),
        ];
    }

}