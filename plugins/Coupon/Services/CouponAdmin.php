<?php

namespace Plugins\Coupon\Services;

use App\Models\Member\Member;
use App\Support\AdminOpLog;
use App\Support\AdminPage;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Schema;
use Plugins\Coupon\Models\PluginCoupon;
use Plugins\Coupon\Models\PluginCouponUser;

class CouponAdmin
{
    public function __construct(private readonly CouponService $coupons) {}

    /** @param  array<string, mixed>  $payload */
    public function boardPayload(array $payload): array
    {
        $payload['groups'] = $this->coupons->adminGroups();

        return $payload;
    }

    /** @param  array<string, mixed>  $params */
    public function lists(array $params): array
    {
        if (! $this->coupons->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $desk = $this->desk($params);
        if ($desk === 'received') {
            return Result::success($this->receivedPage($params));
        }
        $q = PluginCoupon::query()->orderByDesc('id');
        $kw = trim((string) ($params['q'] ?? $params['name'] ?? ''));
        if ($kw !== '') {
            $q->where('name', 'like', '%'.$kw.'%');
        }
        if (in_array((string) ($params['status'] ?? ''), ['0', '1'], true)) {
            $q->where('status', (int) $params['status']);
        }
        if (in_array((string) ($params['scene'] ?? ''), CouponService::SCENES, true)) {
            $q->where('scene', (string) $params['scene']);
        }
        if (in_array((string) ($params['type'] ?? ''), CouponService::TYPES, true)) {
            $q->where('type', (string) $params['type']);
        }
        $now = time();
        if (($params['validity'] ?? '') === 'active') {
            $q->where(function ($inner) use ($now) {
                $inner->where('end_at', 0)->orWhere('end_at', '>=', $now);
            });
        } elseif (($params['validity'] ?? '') === 'expired') {
            $q->where('end_at', '>', 0)->where('end_at', '<', $now);
        }

        return Result::success($this->page($q, $params, fn (PluginCoupon $row) => $this->coupons->present($row)));
    }

    /** @param  array<string, mixed>  $data */
    public function save(array $data, ?int $id = null): array
    {
        if (! $this->coupons->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        if ($this->desk($data) === 'received') {
            return Result::fail('领取记录由前台产生，不能手添或改。');
        }
        $ok = $this->coupons->saveCampaign($data, $id);
        if (($ok['code'] ?? 1) !== 0) {
            return $ok;
        }

        return AdminOpLog::ifOk($ok, $id ? 'update' : 'create', ($id ? '改了优惠券 ' : '发了优惠券 ').trim((string) ($data['name'] ?? '')), [
            'module' => 'coupons',
            'target_id' => (int) ($ok['data']['id'] ?? $id ?? 0),
        ]);
    }

    public function delete(int $id): array
    {
        if ($this->desk(request()->all()) === 'received') {
            return Result::fail('领取记录由前台产生，不能手添或改。');
        }
        $row = PluginCoupon::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        if ((int) $row->received > 0) {
            return Result::fail('已有领取记录，请停用，不要删');
        }
        $name = (string) $row->name;
        $row->delete();

        return AdminOpLog::ifOk(Result::success(), 'delete', '删除优惠券 '.$name, [
            'module' => 'coupons',
            'target_id' => $id,
        ]);
    }

    /** @param  array<string, mixed>  $params */
    private function receivedPage(array $params): array
    {
        if (! Schema::hasTable('plugin_coupon_users')) {
            return AdminPage::slice([], $params);
        }
        $q = PluginCouponUser::query()->orderByDesc('id');
        $kw = trim((string) ($params['q'] ?? ''));
        if ($kw !== '') {
            $q->where(function ($inner) use ($kw) {
                $inner->where('order_no', 'like', '%'.$kw.'%');
                if (ctype_digit($kw)) {
                    $inner->orWhere('member_id', (int) $kw)->orWhere('coupon_id', (int) $kw);
                }
            });
        }

        return $this->page($q, $params, function (PluginCouponUser $row): array {
            $coupon = PluginCoupon::query()->find((int) $row->coupon_id);
            $memberName = '';
            if (Schema::hasTable('members')) {
                $memberName = (string) (Member::query()->where('id', (int) $row->member_id)->value('name') ?? '');
            }

            return [
                'id' => (int) $row->id,
                'coupon_id' => (int) $row->coupon_id,
                'coupon_name' => $coupon ? (string) $coupon->name : '',
                'member_id' => (int) $row->member_id,
                'member_name' => $memberName,
                'status' => (int) $row->status,
                'status_label' => (int) $row->status === 1 ? '已用' : '未用',
                'order_no' => (string) $row->order_no,
                'received_at' => (int) $row->received_at > 0 ? date('Y-m-d H:i', (int) $row->received_at) : '',
                'used_at' => (int) $row->used_at > 0 ? date('Y-m-d H:i', (int) $row->used_at) : '',
            ];
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $q
     * @param  array<string, mixed>  $params
     * @param  callable(\Illuminate\Database\Eloquent\Model): array<string, mixed>  $map
     * @return array{total:int,per_page:int,current_page:int,last_page:int,data:list<array<string, mixed>>}
     */
    private function page($q, array $params, callable $map): array
    {
        $limit = max(1, (int) ($params['limit'] ?? 15));
        $pageNo = max(1, (int) ($params['page'] ?? request()->input('page', 1)));
        $page = $q->paginate($limit, ['*'], 'page', $pageNo);
        $rows = [];
        foreach ($page->items() as $row) {
            $rows[] = $map($row);
        }

        return AdminPage::of($page, $rows);
    }

    /** @param  array<string, mixed>  $params */
    private function desk(array $params): string
    {
        $desk = strtolower(trim((string) ($params['desk'] ?? request()->input('desk', ''))));

        return in_array($desk, ['campaigns', 'received'], true) ? $desk : 'campaigns';
    }
}
