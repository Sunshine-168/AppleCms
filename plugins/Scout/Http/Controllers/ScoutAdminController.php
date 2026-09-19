<?php

namespace Plugins\Scout\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Plugins\Scout\Services\ScoutSearchService;

class ScoutAdminController extends Controller
{
    /**
     * 立刻重建索引。
     */
    public function sync(ScoutSearchService $scout): JsonResponse
    {
        $res = $scout->syncAll();

        return response()->json([
            'code' => $res['ok'] ? 0 : 1,
            'msg' => $res['msg'],
            'data' => $res,
        ]);
    }
}
