<?php
namespace app\admin\controller;

use think\Db;

/**
 * 内容质量看板 —— 分数列表 + 待清理 + 推荐位建议。
 *
 * 新后台控制器：view_path 指向 view_new，且 fetch() 必须用模块相对形式。
 * 用跨模块前缀形式（module 前缀 + @ + controller/action）的 fetch 会绕过 view_path，
 * 硬解析到旧的 application/admin/view/。
 */
class ContentQuality extends Base
{
    /** 待清理 / 推荐位建议列表各取多少条 */
    const SIDE_LIST_LIMIT = 10;

    public function __construct()
    {
        parent::__construct();
        $this->view->config('view_path', APP_PATH . 'admin/view_new/');
    }

    public function index()
    {
        $param = input('param.');
        $mid = isset($param['mid']) && in_array(intval($param['mid']), [1, 2], true) ? intval($param['mid']) : 0;
        $order = isset($param['order']) && $param['order'] === 'asc' ? 'asc' : 'desc';
        $minScore = isset($param['min_score']) && $param['min_score'] !== '' ? floatval($param['min_score']) : null;
        $maxScore = isset($param['max_score']) && $param['max_score'] !== '' ? floatval($param['max_score']) : null;
        $page = max(1, intval(isset($param['page']) ? $param['page'] : 1));
        $limit = 20;

        $where = [];
        if ($mid > 0) {
            $where['mid'] = $mid;
        }
        if ($minScore !== null && $maxScore !== null) {
            $where['score_total'] = [['egt', $minScore], ['elt', $maxScore]];
        } elseif ($minScore !== null) {
            $where['score_total'] = ['egt', $minScore];
        } elseif ($maxScore !== null) {
            $where['score_total'] = ['elt', $maxScore];
        }

        $total = intval(Db::name('ContentQuality')->where($where)->count());
        $list = Db::name('ContentQuality')
            ->where($where)
            ->order('score_total ' . $order)
            ->page($page, $limit)
            ->select();

        // 待清理：分数最低的 N 条；推荐位建议：分数最高的 N 条。均只受 mid 筛选影响，不受分数区间筛选影响。
        // 冷启动内容（is_cold_start=1）尚无行为/互动数据，总分是完整度+新鲜度重新归一得出，不具备参考意义，
        // 两个建议侧列表都只应基于有真实信号的数据，故排除冷启动行；主列表不受影响，仍展示全部记录。
        $sideWhere = $mid > 0 ? ['mid' => $mid] : [];
        $sideWhere['is_cold_start'] = 0;
        $cleanList = Db::name('ContentQuality')
            ->where($sideWhere)
            ->order('score_total asc')
            ->limit(self::SIDE_LIST_LIMIT)
            ->select();
        $recommendList = Db::name('ContentQuality')
            ->where($sideWhere)
            ->order('score_total desc')
            ->limit(self::SIDE_LIST_LIMIT)
            ->select();

        $nameMap = $this->buildNameMap(array_merge($list, $cleanList, $recommendList));

        $this->assign('list', $list);
        $this->assign('clean_list', $cleanList);
        $this->assign('recommend_list', $recommendList);
        $this->assign('name_map', $nameMap);
        $this->assign('mid', $mid);
        $this->assign('order', $order);
        $this->assign('min_score', $minScore === null ? '' : $minScore);
        $this->assign('max_score', $maxScore === null ? '' : $maxScore);
        $this->assign('page', $page);
        $this->assign('total', $total);
        $this->assign('limit', $limit);
        $this->assign('title', lang('admin/content_quality/title'));
        return $this->fetch('content_quality/index');
    }

    /**
     * 批量查出内容名称（vod/art 各自 IN 查询一次），供模板按 mid_contentId 取名展示。
     */
    private function buildNameMap($rows)
    {
        $vodIds = [];
        $artIds = [];
        foreach ($rows as $row) {
            if (intval($row['mid']) === 1) {
                $vodIds[intval($row['content_id'])] = true;
            } else {
                $artIds[intval($row['content_id'])] = true;
            }
        }

        $map = [];
        if (!empty($vodIds)) {
            $vodRows = Db::name('Vod')->where('vod_id', 'in', array_keys($vodIds))->field('vod_id,vod_name')->select();
            foreach ($vodRows as $r) {
                $map['1_' . intval($r['vod_id'])] = $r['vod_name'];
            }
        }
        if (!empty($artIds)) {
            $artRows = Db::name('Art')->where('art_id', 'in', array_keys($artIds))->field('art_id,art_name')->select();
            foreach ($artRows as $r) {
                $map['2_' . intval($r['art_id'])] = $r['art_name'];
            }
        }
        return $map;
    }

    /**
     * 「标为推荐」——硬性产品规则：推荐位建议只能由人工在 vod/art 编辑页确认，
     * 本方法绝不写 vod_level / art_level，只确认该内容确有质量评分记录并返回提示。
     * 人工采纳请通过推荐列表中的编辑链接跳转到内容编辑页手动调整推荐位。
     */
    public function markRecommend()
    {
        $param = input('post.');
        if (!$this->checkAjaxToken($param)) {
            return json(['code' => 0, 'msg' => lang('param_err')]);
        }
        $mid = intval(isset($param['mid']) ? $param['mid'] : 0);
        $contentId = intval(isset($param['content_id']) ? $param['content_id'] : 0);

        if (($mid !== 1 && $mid !== 2) || $contentId < 1) {
            return json(['code' => 0, 'msg' => lang('param_err')]);
        }

        $row = Db::name('ContentQuality')->where(['mid' => $mid, 'content_id' => $contentId])->find();
        if (empty($row)) {
            return json(['code' => 0, 'msg' => lang('admin/content_quality/not_found')]);
        }

        // 注意：此处刻意不做任何写操作（不建表记录、不改 vod_level/art_level）。
        return json(['code' => 1, 'msg' => lang('admin/content_quality/mark_recommend_ok')]);
    }

    /**
     * 校验 CSRF token。本页可连续点多行提交且不刷新，共用一个令牌，所以只比对不销毁。
     */
    private function checkAjaxToken($param)
    {
        $token = isset($param['__token__']) ? (string)$param['__token__'] : '';
        $sess = (string)\think\Session::get('__token__');
        return $token !== '' && $sess !== '' && hash_equals($sess, $token);
    }
}
