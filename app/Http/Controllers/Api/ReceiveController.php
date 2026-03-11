<?php

namespace App\Http\Controllers\Api;

use App\Models\Actor;
use App\Models\Art;
use App\Models\Comment;
use App\Models\Role;
use App\Models\Type;
use App\Models\Vod;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ReceiveController extends BaseController
{
    protected array $modelMap = [
        'vod' => Vod::class,
        'art' => Art::class,
        'actor' => Actor::class,
        'role' => Role::class,
        'website' => Website::class,
        'comment' => Comment::class,
    ];

    public function index()
    {
        return $this->response(1, '接口可用');
    }

    public function vod(Request $request) { return $this->storeSection('vod', $request); }
    public function art(Request $request) { return $this->storeSection('art', $request); }
    public function actor(Request $request) { return $this->storeSection('actor', $request); }
    public function role(Request $request) { return $this->storeSection('role', $request); }
    public function website(Request $request) { return $this->storeSection('website', $request); }
    public function comment(Request $request) { return $this->storeSection('comment', $request); }

    protected function storeSection(string $section, Request $request)
    {
        if ($resp = $this->ensureReceiveEnabled($request)) {
            return $resp;
        }

        $data = $request->all();
        $error = $this->validateReceivePayload($section, $data);
        if ($error) {
            return $this->response($error['code'], $error['msg']);
        }

        $modelClass = $this->modelMap[$section];
        $model = new $modelClass();
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);

        $resolved = $this->resolveSpecialFields($section, $data);
        $payload = array_intersect_key($resolved, array_flip($columns));

        $primaryKey = $model->getKeyName();
        unset($payload[$primaryKey], $payload['pass'], $payload['type_name'], $payload['rel_name'], $payload['vod_name'], $payload['douban_id']);

        if (empty($payload)) {
            return $this->response(2005, '没有可入库字段');
        }

        $saved = $modelClass::query()->create($payload);

        return $this->response(1, '入库成功', [
            'id' => $saved->getKey(),
            'section' => $section,
        ]);
    }

    protected function ensureReceiveEnabled(Request $request)
    {
        $interfaceConfig = config('maccms.interface', []);
        if (($interfaceConfig['status'] ?? 0) != 1) {
            return $this->response(3001, '接口已关闭');
        }

        $pass = (string) ($request->input('pass', ''));
        if (($interfaceConfig['pass'] ?? '') !== $pass) {
            return $this->response(3002, '接口密码错误');
        }

        if (strlen((string) ($interfaceConfig['pass'] ?? '')) < 16) {
            return $this->response(3003, '接口密码长度不足16位');
        }

        return null;
    }

    protected function validateReceivePayload(string $section, array $data): ?array
    {
        return match ($section) {
            'vod' => empty($data['vod_name']) ? ['code' => 2001, 'msg' => '名称不能为空'] :
                ((empty($data['type_id']) && empty($data['type_name'])) ? ['code' => 2002, 'msg' => '分类不能为空'] : null),
            'art' => empty($data['art_name']) ? ['code' => 2001, 'msg' => '名称不能为空'] :
                ((empty($data['type_id']) && empty($data['type_name'])) ? ['code' => 2002, 'msg' => '分类不能为空'] : null),
            'actor' => empty($data['actor_name']) ? ['code' => 2001, 'msg' => '演员名不能为空'] :
                (empty($data['actor_sex']) ? ['code' => 2002, 'msg' => '性别不能为空'] :
                ((empty($data['type_id']) && empty($data['type_name'])) ? ['code' => 2003, 'msg' => '分类不能为空'] : null)),
            'role' => empty($data['role_name']) ? ['code' => 2001, 'msg' => '角色名不能为空'] :
                (empty($data['role_actor']) ? ['code' => 2002, 'msg' => '演员不能为空'] :
                ((empty($data['vod_name']) && empty($data['douban_id']) && empty($data['role_rid'])) ? ['code' => 2003, 'msg' => '缺少关联视频'] : null)),
            'website' => empty($data['website_name']) ? ['code' => 2001, 'msg' => '名称不能为空'] :
                ((empty($data['type_id']) && empty($data['type_name'])) ? ['code' => 2002, 'msg' => '分类不能为空'] : null),
            'comment' => empty($data['comment_name']) ? ['code' => 2001, 'msg' => '评论昵称不能为空'] :
                (empty($data['comment_content']) ? ['code' => 2002, 'msg' => '评论内容不能为空'] :
                (empty($data['comment_mid']) ? ['code' => 2004, 'msg' => '模块ID不能为空'] :
                ((empty($data['rel_name']) && empty($data['douban_id']) && empty($data['comment_rid'])) ? ['code' => 2003, 'msg' => '缺少关联数据'] : null))),
            default => ['code' => 1001, 'msg' => '不支持的接口'],
        };
    }

    protected function resolveSpecialFields(string $section, array $data): array
    {
        if (empty($data['type_id']) && !empty($data['type_name'])) {
            $data['type_id'] = $this->resolveTypeId($section, $data['type_name']);
        }

        if ($section === 'role' && empty($data['role_rid']) && !empty($data['vod_name'])) {
            $vod = Vod::query()->where('vod_name', $data['vod_name'])->first();
            if ($vod) {
                $data['role_rid'] = $vod->vod_id;
            }
        }

        if ($section === 'comment' && empty($data['comment_rid']) && !empty($data['rel_name']) && !empty($data['comment_mid'])) {
            $target = $this->resolveCommentTarget($data['comment_mid'], $data['rel_name']);
            if ($target) {
                $data['comment_rid'] = $target['id'];
            }
        }

        return $data;
    }

    protected function resolveTypeId(string $section, string $typeName): int
    {
        $mapKey = match ($section) {
            'vod' => 'vodtype',
            'art' => 'arttype',
            'actor' => 'actortype',
            'website' => 'websitetype',
            default => null,
        };

        if ($mapKey) {
            $pairs = explode('#', (string) config('maccms.interface.' . $mapKey, ''));
            foreach ($pairs as $pair) {
                [$remote, $local] = array_pad(explode('=', $pair, 2), 2, null);
                if ($remote !== null && trim($remote) === trim($typeName)) {
                    $type = Type::query()->where('type_name', trim((string) $local))->first();
                    if ($type) {
                        return (int) $type->type_id;
                    }
                }
            }
        }

        $type = Type::query()->where('type_name', trim($typeName))->first();
        return $type ? (int) $type->type_id : 0;
    }

    protected function resolveCommentTarget($mid, string $name): ?array
    {
        $map = [
            1 => [Vod::class, 'vod_id', 'vod_name'],
            2 => [Art::class, 'art_id', 'art_name'],
            8 => [Actor::class, 'actor_id', 'actor_name'],
            9 => [Role::class, 'role_id', 'role_name'],
            11 => [Website::class, 'website_id', 'website_name'],
        ];

        if (!isset($map[(int) $mid])) {
            return null;
        }

        [$modelClass, $idField, $nameField] = $map[(int) $mid];
        $item = $modelClass::query()->where($nameField, $name)->first();
        if (!$item) {
            return null;
        }

        return ['id' => $item->{$idField}];
    }
}
