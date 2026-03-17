<?php
namespace App\Http\Controllers\Admin;
use App\Models\System\SysPermModel;
use App\Models\System\SysRolePermModel;
use App\Models\System\SysUserModel;
use App\Http\Controllers\Controller;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * 后台首页
 * @param Request $request
 * @return Factory|View
 */
class Index extends Controller
{
    /**
     * 后台首页
     * @param Request $request
     * @return Factory|View
     */
    public function index(Request $request): Factory|View
    {
        $menus = [];

        $uid = (int) session('admin_uid', 0);
        $user = $uid > 0 ? (new SysUserModel())->findById($uid) : [];
        $isSuperAdmin = ($uid === 1);

        $menuRows = (new SysPermModel())->selectByCondition([['type', '=', 1]], '*', ['sort' => 'desc', 'id' => 'asc']);
        if (!empty($menuRows)) {
            $childrenByPid = [];
            foreach ($menuRows as $row) {
                $row['id'] = (int) ($row['id'] ?? 0);
                $row['pid'] = (int) ($row['pid'] ?? 0);
                $childrenByPid[$row['pid']][] = $row;
            }

            $buildTree = function (int $pid) use (&$buildTree, $childrenByPid): array {
                $nodes = $childrenByPid[$pid] ?? [];
                $tree = [];
                foreach ($nodes as $n) {
                    $n['children'] = $buildTree((int) $n['id']);
                    $tree[] = $n;
                }
                return $tree;
            };

            $tree = $buildTree(0);

            if (!$isSuperAdmin) {
                $roleId = (int) ($user['role_id'] ?? 0);
                $permIds = $roleId > 0
                    ? (new SysRolePermModel())->uniqueColumnByCondition(['role_id' => $roleId], 'perm_id')
                    : [];
                $allowMap = [];
                foreach ($permIds as $pid) {
                    $allowMap[(int) $pid] = true;
                }

                $filterTree = function (array $nodes) use (&$filterTree, $allowMap): array {
                    $res = [];
                    foreach ($nodes as $n) {
                        $children = !empty($n['children']) && is_array($n['children']) ? $filterTree($n['children']) : [];
                        $id = (int) ($n['id'] ?? 0);
                        if (isset($allowMap[$id]) || !empty($children)) {
                            $n['children'] = $children;
                            $res[] = $n;
                        }
                    }
                    return $res;
                };

                $tree = $filterTree($tree);
            }

            $toMenu = function (array $nodes) use (&$toMenu): array {
                $res = [];
                foreach ($nodes as $n) {
                    $item = [
                        'name' => (string) ($n['name'] ?? ''),
                        'icon' => (string) ($n['icon'] ?? ''),
                    ];

                    $children = !empty($n['children']) && is_array($n['children']) ? $n['children'] : [];
                    if (!empty($children)) {
                        $item['sub'] = $toMenu($children);
                    } else {
                        $api = (string) ($n['api'] ?? '');
                        if (str_starts_with($api, 'route:')) {
                            $item['route'] = substr($api, 6);
                        } else {
                            $item['url'] = $api;
                        }
                    }
                    $res[] = $item;
                }
                return $res;
            };

            $menus = $toMenu($tree);
        } else {
            if ($isSuperAdmin) {
                $menus = config('system.menus');
            }
        }

        return view('admin.layouts.index', compact('menus'));
    }

    /**
     * 输出验证码图片
     * @param Request $request
     * @return Response
     */
    public function captcha(Request $request): Response
    {
        $builder = new CaptchaBuilder;

        $builder->build();

        // 保存验证码到 session
        session(['captcha' => $builder->getPhrase()]);

        return response($builder->get(), 200)

            ->header('Content-Type', 'image/jpeg');
    }
}
