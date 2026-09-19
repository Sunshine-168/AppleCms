<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Video\VideoPlayerModel;
use App\Services\Video\InteractionService;
use App\Services\Video\SiteFrontService;
use App\Cms\CmsViewContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PlayerController extends Controller
{
    public function __construct(
        private readonly SiteFrontService $front,
        private readonly CmsViewContext $context,
        private readonly InteractionService $interaction,
    ) {}

    public function show(int $id, ?int $sid = null, ?int $nid = null): View
    {
        $site = $this->front->bootSite();
        $video = $this->front->findVideo($id);
        if (! $video) {
            throw new NotFoundHttpException();
        }
        $member = Auth::guard('member')->user();
        $paid = $this->interaction->consumePlayPoints($member, $video);
        $trysee = (int) ($paid['data']['trysee'] ?? $paid['data']['trysee_seconds'] ?? 0);
        $payError = ($paid['code'] ?? 1) !== 0 ? (string) $paid['msg'] : '';
        [$source, $episode] = $this->front->resolvePlay($video, $sid, $nid, 'play');
        $this->context->setVideo($video);
        $this->context->setSource($source);
        $this->context->setEpisode($episode);
        if ($member && $payError === '' && $trysee < 1) {
            $had = \App\Models\Member\MemberHistory::query()
                ->where('member_id', $member->id)
                ->where('video_id', $video->id)
                ->exists();
            if (! $had) {
                $this->interaction->recordHistory((int) $member->id, $video, (int) ($source?->id ?: 0), (int) ($episode?->id ?: 0));
            }
        }

        $rawUrl = $payError !== '' ? '' : $this->front->resolvePlayUrl($source, $episode);
        $playUrl = $rawUrl;
        $playerCode = trim((string) ($source?->player ?: $source?->name ?: ''));
        $parser = null;
        if ($playerCode !== '') {
            $parser = VideoPlayerModel::query()->where('status', 1)->where('code', $playerCode)->orderByDesc('sort')->first();
        }
        if (! $parser) {
            $parser = VideoPlayerModel::query()->where('status', 1)->orderByDesc('sort')->first();
        }
        if ($parser && trim((string) $parser->parse) !== '' && $rawUrl !== '') {
            $playUrl = str_replace(['{url}', '{id}'], [rawurlencode($rawUrl), (string) $id], (string) $parser->parse);
        }
        $engine = VideoPlayerModel::resolveEngine($parser, $playUrl, $rawUrl);

        // Cloud mirrors often ship HEVC in m3u8; Chromium plays audio only.
        // Their /play/{id} HTML player handles that better via iframe.
        if (preg_match('#^(https?://[^\s]+/play/[A-Za-z0-9_-]+)/index\.m3u8(?:\?.*)?$#i', $rawUrl, $m)) {
            $playUrl = $m[1];
            $rawUrl = $m[1];
            $engine = 'iframe';
        }

        $settings = app(\App\Services\Video\VideoSettingService::class);
        $playEncrypt = (int) $settings->get('play_encrypt', '0');
        $playBuffer = (int) $settings->get('play_buffer', '5');

        return view($this->front->themeView('vod.player'), compact(
            'site', 'video', 'source', 'episode', 'playUrl', 'rawUrl', 'parser', 'engine',
            'trysee', 'payError', 'playEncrypt', 'playBuffer'
        ));
    }
}
