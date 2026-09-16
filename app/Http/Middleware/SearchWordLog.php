<?php

namespace App\Http\Middleware;

use App\Models\Video\VideoSearchWord;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class SearchWordLog
{
    public function handle(Request $request, Closure $next): Response
    {
        $wd = trim((string) $request->query('wd', $request->query('q', '')));
        $wd = mb_substr($wd, 0, 80);
        if ($wd !== '' && ! $request->is('admin/*') && ! $request->is('install*') && ! $request->is('install/*')) {
            try {
                if (Schema::hasTable('video_search_words')) {
                    $now = time();
                    $row = VideoSearchWord::query()->where('word', $wd)->first();
                    if ($row) {
                        $row->hits = (int) $row->hits + 1;
                        $row->updated_at = $now;
                        $row->save();
                    } else {
                        VideoSearchWord::query()->create([
                            'word' => $wd,
                            'hits' => 1,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            } catch (\Throwable) {
            }
        }

        return $next($request);
    }
}
