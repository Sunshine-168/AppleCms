<?php

namespace Plugins\Manga\Services;

use App\Models\Member\Member;
use App\Services\Video\VideoSettingService;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Models\MangaChapter;

class MangaAccessService
{
    public function __construct(private readonly VideoSettingService $settings) {}

    public function isVipChapter(MangaChapter $chapter): bool
    {
        if (! Schema::hasColumn($chapter->getTable(), 'vip')) {
            return false;
        }

        return (int) ($chapter->vip ?? 0) === 1;
    }

    public function isVipMember(?Member $member): bool
    {
        if (! $member) {
            return false;
        }
        $gid = $member->effectiveGroupId();
        if ($gid < 1) {
            return false;
        }
        $allow = $this->vipGroupIds();
        if ($allow === []) {
            return true;
        }

        return in_array($gid, $allow, true);
    }

    /** Free preview page count for VIP-locked chapters. */
    public function tryseePages(): int
    {
        return max(0, min(50, (int) $this->settings->get('manga_trysee_pages', '3')));
    }

    /**
     * @return array{
     *   locked:bool,
     *   vip:bool,
     *   can_full:bool,
     *   trysee:int,
     *   pics:list<string>,
     *   total:int,
     *   need_login:bool,
     *   need_vip:bool
     * }
     */
    public function gate(?Member $member, MangaChapter $chapter, array $pics): array
    {
        $total = count($pics);
        $vip = $this->isVipChapter($chapter);
        if (! $vip) {
            return [
                'locked' => false,
                'vip' => false,
                'can_full' => true,
                'trysee' => 0,
                'pics' => $pics,
                'total' => $total,
                'need_login' => false,
                'need_vip' => false,
            ];
        }
        if ($this->isVipMember($member)) {
            return [
                'locked' => false,
                'vip' => true,
                'can_full' => true,
                'trysee' => 0,
                'pics' => $pics,
                'total' => $total,
                'need_login' => false,
                'need_vip' => false,
            ];
        }
        $try = $this->tryseePages();
        $visible = $try > 0 ? array_slice($pics, 0, $try) : [];

        return [
            'locked' => true,
            'vip' => true,
            'can_full' => false,
            'trysee' => $try,
            'pics' => $visible,
            'total' => $total,
            'need_login' => $member === null,
            'need_vip' => $member !== null,
        ];
    }

    /** @return list<int> */
    private function vipGroupIds(): array
    {
        $raw = trim((string) $this->settings->get('manga_vip_group_ids', ''));
        if ($raw === '') {
            return [];
        }
        $out = [];
        foreach (preg_split('/[,，\s]+/u', $raw) ?: [] as $part) {
            $id = (int) $part;
            if ($id > 0) {
                $out[$id] = $id;
            }
        }

        return array_values($out);
    }
}
