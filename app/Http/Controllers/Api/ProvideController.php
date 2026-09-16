<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoTypeModel;
use App\Services\Video\VideoSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProvideController extends Controller
{
    public function vod(Request $request, VideoSettingService $settings): JsonResponse|Response
    {
        $need = trim((string) $settings->get('provide_key', ''));
        if ($need !== '') {
            $given = (string) $request->query('key', $request->header('X-Provide-Key', $request->input('key', '')));
            if ($given === '' || ! hash_equals($need, $given)) {
                $at = (string) $request->query('at', $request->segment(4) ?? 'json');
                if ($at === 'xml') {
                    return response('<?xml version="1.0" encoding="utf-8"?><rss version="5.0"><list></list></rss>', 403, [
                        'Content-Type' => 'text/xml; charset=utf-8',
                    ]);
                }

                return response()->json(['code' => 0, 'msg' => '密钥无效'], 403);
            }
        }
        $ac = (string) $request->query('ac', 'list');
        $at = (string) $request->query('at', $request->segment(4) ?? 'json');
        $page = max(1, (int) $request->query('pg', 1));
        $limit = 20;
        $typeId = (int) $request->query('t', 0);
        $wd = trim((string) $request->query('wd', ''));
        $ids = (string) $request->query('ids', '');
        $hours = (int) $request->query('h', 0);

        $query = VideoModel::query()->with(['type', 'sources.episodes', 'actors'])->published();
        if ($typeId > 0) {
            $type = VideoTypeModel::query()->find($typeId);
            $idsType = $type ? $type->descendantIds() : [$typeId];
            $query->where(function ($q) use ($idsType) {
                $q->whereIn('type_id', $idsType)->orWhereIn('type_pid', $idsType);
            });
        }
        if ($wd !== '') {
            $query->where('title', 'like', "%{$wd}%");
        }
        if ($ids !== '') {
            $query->whereIn('id', array_filter(array_map('intval', explode(',', $ids))));
        }
        if ($hours > 0) {
            $query->where('updated_at', '>=', time() - $hours * 3600);
        }

        $paginator = $query->orderByDesc('id')->paginate($limit, ['*'], 'pg', $page);
        $detail = $ac === 'detail' || $ac === 'videolist' || $ids !== '';
        $list = [];
        foreach ($paginator->items() as $video) {
            $list[] = $this->formatVideo($video, $detail);
        }

        $payload = [
            'code' => 1,
            'msg' => '数据列表',
            'page' => $paginator->currentPage(),
            'pagecount' => $paginator->lastPage(),
            'limit' => (string) $limit,
            'total' => $paginator->total(),
            'list' => $list,
        ];
        if ($ac === 'list') {
            $payload['class'] = VideoTypeModel::query()->active()->orderByDesc('sort')->get()->map(fn ($t) => [
                'type_id' => $t->id,
                'type_name' => $t->name,
            ])->values()->all();
        }

        if ($at === 'xml') {
            return response($this->toXml($payload, $detail), 200, ['Content-Type' => 'text/xml; charset=utf-8']);
        }

        return response()->json($payload);
    }

    /** @param  \App\Models\Video\VideoModel  $video */
    private function formatVideo($video, bool $detail): array
    {
        $froms = [];
        $urls = [];
        if ($detail) {
            foreach ($video->sources as $source) {
                $froms[] = $source->name;
                $eps = [];
                foreach ($source->episodes as $ep) {
                    $eps[] = $ep->display_name.'$'.$ep->url;
                }
                $urls[] = implode('#', $eps);
            }
        }
        $row = [
            'vod_id' => $video->id,
            'vod_name' => $video->title,
            'type_id' => (int) $video->type_id,
            'type_name' => (string) ($video->type?->name ?? ''),
            'vod_en' => (string) $video->slug,
            'vod_time' => $video->updated_at ? date('Y-m-d H:i:s', (int) $video->updated_at) : '',
            'vod_remarks' => (string) $video->remarks,
            'vod_play_from' => implode('$$$', $froms),
        ];
        if ($detail) {
            $row['vod_pic'] = (string) $video->cover;
            $row['vod_area'] = (string) $video->area;
            $row['vod_lang'] = (string) $video->lang;
            $row['vod_year'] = (string) $video->year;
            $row['vod_serial'] = (string) $video->serial;
            $row['vod_actor'] = $video->actors->pluck('name')->implode(',');
            $row['vod_director'] = (string) $video->director;
            $row['vod_content'] = (string) $video->description;
            $row['vod_play_url'] = implode('$$$', $urls);
        }

        return $row;
    }

    /** @param  array<string, mixed>  $payload */
    private function toXml(array $payload, bool $detail): string
    {
        $list = $payload['list'] ?? [];
        $xml = '<?xml version="1.0" encoding="utf-8"?><rss version="5.0">';
        $xml .= '<list page="'.(int) $payload['page'].'" pagecount="'.(int) $payload['pagecount'].'" pagesize="'.(int) $payload['limit'].'" recordcount="'.(int) $payload['total'].'">';
        foreach ($list as $row) {
            $xml .= '<video>';
            $xml .= '<last>'.$this->cdata((string) ($row['vod_time'] ?? '')).'</last>';
            $xml .= '<id>'.(int) $row['vod_id'].'</id>';
            $xml .= '<tid>'.(int) $row['type_id'].'</tid>';
            $xml .= '<name>'.$this->cdata((string) $row['vod_name']).'</name>';
            $xml .= '<type>'.$this->cdata((string) $row['type_name']).'</type>';
            $xml .= '<dt>'.$this->cdata((string) ($row['vod_play_from'] ?? '')).'</dt>';
            $xml .= '<note>'.$this->cdata((string) ($row['vod_remarks'] ?? '')).'</note>';
            if ($detail) {
                $xml .= '<pic>'.$this->cdata((string) ($row['vod_pic'] ?? '')).'</pic>';
                $xml .= '<lang>'.$this->cdata((string) ($row['vod_lang'] ?? '')).'</lang>';
                $xml .= '<area>'.$this->cdata((string) ($row['vod_area'] ?? '')).'</area>';
                $xml .= '<year>'.$this->cdata((string) ($row['vod_year'] ?? '')).'</year>';
                $xml .= '<actor>'.$this->cdata((string) ($row['vod_actor'] ?? '')).'</actor>';
                $xml .= '<director>'.$this->cdata((string) ($row['vod_director'] ?? '')).'</director>';
                $xml .= '<des>'.$this->cdata((string) ($row['vod_content'] ?? '')).'</des>';
            }
            $xml .= '</video>';
        }
        $xml .= '</list>';
        if (! empty($payload['class'])) {
            $xml .= '<class>';
            foreach ($payload['class'] as $ty) {
                $xml .= '<ty id="'.(int) $ty['type_id'].'">'.$this->cdata((string) $ty['type_name']).'</ty>';
            }
            $xml .= '</class>';
        }
        $xml .= '</rss>';

        return $xml;
    }

    private function cdata(string $value): string
    {
        return '<![CDATA['.str_replace(']]>', ']]]]><![CDATA[>', $value).']]>';
    }
}
