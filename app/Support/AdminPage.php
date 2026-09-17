<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * 后台列表给 AdminUi.table 用的分页结构。
 */
class AdminPage
{
    /**
     * @param  list<array<string, mixed>>|null  $rows
     * @return array{total:int,per_page:int,current_page:int,last_page:int,data:list<array<string, mixed>>}
     */
    public static function of(LengthAwarePaginator $page, ?array $rows = null): array
    {
        if ($rows === null) {
            $rows = collect($page->items())->map(static function ($row) {
                return is_array($row) ? $row : $row->toArray();
            })->all();
        }

        return [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => max(1, $page->lastPage()),
            'data' => $rows,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, mixed>  $params
     * @return array{total:int,per_page:int,current_page:int,last_page:int,data:list<array<string, mixed>>}
     */
    public static function slice(array $rows, array $params, int $defaultPer = 15): array
    {
        $total = count($rows);
        $per = (int) ($params['limit'] ?? $params['per_page'] ?? $defaultPer);
        if ($per < 1) {
            $per = $defaultPer;
        }
        $last = max(1, (int) ceil($total / $per));
        $page = (int) ($params['page'] ?? request()->input('page', 1));
        if ($page < 1) {
            $page = 1;
        }
        if ($page > $last) {
            $page = $last;
        }

        return [
            'total' => $total,
            'per_page' => $per,
            'current_page' => $page,
            'last_page' => $last,
            'data' => array_values(array_slice($rows, ($page - 1) * $per, $per)),
        ];
    }
}
