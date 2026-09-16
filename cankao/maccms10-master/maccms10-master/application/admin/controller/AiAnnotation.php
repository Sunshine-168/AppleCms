<?php
namespace app\admin\controller;

use app\common\util\AiProvider;
use app\common\util\AnnotationAdopter;
use app\common\util\ContentAnnotator;
use think\Db;

/**
 * AI 内容标注 —— 待采纳队列。
 *
 * 新后台控制器：view_path 指向 view_new，且 fetch() 必须用模块相对形式。
 * 用跨模块前缀形式（module 前缀 + @ + controller/action）的 fetch 会绕过 view_path，硬解析到旧的 application/admin/view/。
 */
class AiAnnotation extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->view->config('view_path', APP_PATH . 'admin/view_new/');
    }

    public function index()
    {
        $param = input('param.');
        $mid = intval(isset($param['mid']) ? $param['mid'] : 1);
        if ($mid !== 1 && $mid !== 2) {
            $mid = 1;
        }
        $status = isset($param['status']) && $param['status'] !== '' ? intval($param['status']) : 0;
        $page = max(1, intval(isset($param['page']) ? $param['page'] : 1));
        $limit = 20;

        $where = ['mid' => $mid, 'status' => $status];
        $total = intval(Db::name('ContentAiAnnotation')->where($where)->count());
        $list = Db::name('ContentAiAnnotation')
            ->where($where)
            ->order('time_update desc')
            ->page($page, $limit)
            ->select();

        // 把「当前值」查出来，和 AI 建议并排展示 —— 采纳前必须让人看见会改掉什么
        $ids = [];
        foreach ($list as $r) {
            $ids[] = intval($r['content_id']);
        }
        $curMap = [];
        if (!empty($ids)) {
            $tbl = $mid === 1 ? 'Vod' : 'Art';
            $pk = $mid === 1 ? 'vod_id' : 'art_id';
            $rows = Db::name($tbl)->where($pk, 'in', $ids)->select();
            foreach ($rows as $r) {
                $curMap[intval($r[$pk])] = [
                    'name' => $mid === 1 ? (string)$r['vod_name'] : (string)$r['art_name'],
                    'tag' => $mid === 1 ? (string)$r['vod_tag'] : (string)$r['art_tag'],
                    'blurb' => $mid === 1 ? (string)$r['vod_blurb'] : (string)$r['art_blurb'],
                    'type_id' => intval($r['type_id']),
                ];
            }
        }

        $this->assign('list', $list);
        $this->assign('cur_map', $curMap);
        $this->assign('mid', $mid);
        $this->assign('status', $status);
        $this->assign('page', $page);
        $this->assign('total', $total);
        $this->assign('limit', $limit);
        $this->assign('title', lang('admin/aiannotation/title'));
        return $this->fetch('ai_annotation/index');
    }

    /**
     * 批量生成：只取还没有标注、或源文本已变的内容。分块，每次最多 batch_size 条。
     */
    public function generate()
    {
        $param = input('post.');
        if (!$this->checkAjaxToken($param)) {
            return json(['code' => 0, 'msg' => lang('param_err')]);
        }
        $mid = intval(isset($param['mid']) ? $param['mid'] : 1);
        if ($mid !== 1 && $mid !== 2) {
            return json(['code' => 0, 'msg' => lang('param_err')]);
        }

        $cfg = AiProvider::resolveConfig();
        if (empty($cfg['enabled'])) {
            return json(['code' => 0, 'msg' => lang('admin/aiannotation/not_enabled')]);
        }

        $ids = isset($param['ids']) && is_array($param['ids']) ? array_map('intval', $param['ids']) : [];
        if (empty($ids)) {
            $ids = self::pickPending($mid, $cfg['batch_size']);
        }
        $ids = array_slice($ids, 0, $cfg['batch_size']);

        $ok = 0;
        $fail = 0;
        foreach ($ids as $id) {
            try {
                $res = ContentAnnotator::annotate($mid, $id, false);
                if (intval($res['code']) === 1) {
                    $ok++;
                } else {
                    $fail++;
                }
            } catch (\Exception $e) {
                $fail++;
            }
        }
        return json(['code' => 1, 'msg' => 'ok', 'data' => ['ok' => $ok, 'fail' => $fail]]);
    }

    public function adopt()
    {
        $param = input('post.');
        if (!$this->checkAjaxToken($param)) {
            return json(['code' => 0, 'msg' => lang('param_err')]);
        }
        $mid = intval(isset($param['mid']) ? $param['mid'] : 0);
        $contentId = intval(isset($param['content_id']) ? $param['content_id'] : 0);
        $fields = isset($param['fields']) && is_array($param['fields']) ? $param['fields'] : ['tags', 'summary', 'type_id'];

        $whitelist = ['tags', 'summary', 'type_id'];
        $fields = array_values(array_intersect($fields, $whitelist));
        if (empty($fields)) {
            return json(['code' => 0, 'msg' => lang('param_err')]);
        }

        $res = AnnotationAdopter::adopt($mid, $contentId, $fields);
        if (intval($res['code']) !== 1) {
            return json(['code' => 0, 'msg' => $res['msg']]);
        }
        return json(['code' => 1, 'msg' => lang('admin/aiannotation/adopt_ok')]);
    }

    public function reject()
    {
        $param = input('post.');
        if (!$this->checkAjaxToken($param)) {
            return json(['code' => 0, 'msg' => lang('param_err')]);
        }
        $res = AnnotationAdopter::reject(intval(isset($param['mid']) ? $param['mid'] : 0), intval(isset($param['content_id']) ? $param['content_id'] : 0));
        if (intval($res['code']) !== 1) {
            return json(['code' => 0, 'msg' => $res['msg']]);
        }
        return json(['code' => 1, 'msg' => 'ok']);
    }

    /**
     * 校验 CSRF token。本页一个页面会多次提交，共用一个令牌，所以只比对不销毁。
     */
    private function checkAjaxToken($param)
    {
        $token = isset($param['__token__']) ? (string)$param['__token__'] : '';
        $sess = (string)\think\Session::get('__token__');
        return $token !== '' && $sess !== '' && hash_equals($sess, $token);
    }

    /**
     * 挑出还没标注过的内容。定时任务与后台批量按钮共用。
     */
    public static function pickPending($mid, $limit)
    {
        $mid = intval($mid);
        $limit = max(1, intval($limit));
        $tbl = $mid === 1 ? 'vod' : 'art';
        $pk = $mid === 1 ? 'vod_id' : 'art_id';
        $pre = Db::getConfig('prefix');

        $rows = Db::name($tbl)
            ->alias('c')
            ->join($pre . 'content_ai_annotation a', 'a.mid = ' . $mid . ' and a.content_id = c.' . $pk, 'left')
            ->field('c.' . $pk . ' as id')
            ->where('c.' . ($mid === 1 ? 'vod_status' : 'art_status'), 1)
            ->whereNull('a.id')
            ->order('c.' . $pk . ' desc')
            ->limit($limit)
            ->select();

        $out = [];
        foreach ($rows as $r) {
            $out[] = intval($r['id']);
        }
        return $out;
    }
}
