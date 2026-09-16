<?php
namespace app\admin\controller;

class Live extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    // ==================== 频道管理 ====================

    public function index()
    {
        $param = input();
        $param['page']  = intval($param['page']) < 1 ? 1 : $param['page'];
        $param['limit'] = intval($param['limit']) < 1 ? $this->_pagesize : $param['limit'];
        $where = [];

        if (!empty($param['wd'])) {
            $param['wd'] = htmlspecialchars(urldecode($param['wd']));
            $where['live_name'] = ['like', '%' . $param['wd'] . '%'];
        }
        if (isset($param['cate_id']) && $param['cate_id'] !== '') {
            $where['cate_id'] = ['eq', (int)$param['cate_id']];
        }
        if (isset($param['status']) && $param['status'] !== '') {
            $where['live_status'] = ['eq', (int)$param['status']];
        }

        $order = 'live_sort desc, live_id desc';
        $res = model('Live')->listData($where, $order, $param['page'], $param['limit']);

        // 分类列表（用于筛选下拉）
        $cate_list = model('Live')->categoryList(['cate_status' => 1]);

        $this->assign('list', $res['list']);
        $this->assign('total', $res['total']);
        $this->assign('page', $res['page']);
        $this->assign('limit', $res['limit']);
        $this->assign('cate_list', $cate_list);

        $param['page']  = '{page}';
        $param['limit'] = '{limit}';
        $this->assign('param', $param);
        $this->assign('title', lang('admin/live/title'));
        return $this->fetch('admin@live/index');
    }

    public function info()
    {
        if (Request()->isPost()) {
            $param = input();
            $contentLang = $param['content_lang'] ?? [];
            unset($param['content_lang']);
            $res = model('Live')->saveData($param);
            if ($res['code'] > 1) {
                return $this->error($res['msg']);
            }
            $liveId = (int)($res['live_id'] ?? 0);
            if ($liveId > 0 && is_array($contentLang)) {
                //默认语言以原始行为准，只写非默认语言的译文（见 mac_content_lang_overlay 注释）
                $defaultLang = mac_content_lang_default();
                foreach ($contentLang as $langCode => $fields) {
                    if ((string)$langCode === $defaultLang || !is_array($fields)) {
                        continue;
                    }
                    model('ContentLang')->saveFields('live', $liveId, (string)$langCode, $fields, 'manual', 1);
                }
            }
            return $this->success($res['msg']);
        }

        $id = input('id');
        $where = [];
        if (!empty($id)) {
            $where['live_id'] = ['eq', $id];
        }
        $res = model('Live')->infoData($where);

        $cate_list = model('Live')->categoryList(['cate_status' => 1]);

        $info = $res['info'];
        $this->assign('info', $info);
        $this->assign('cate_list', $cate_list);

        //内容级多语言：默认语言永远排第一位（即便后台没勾选它），其后跟已启用的其它语言
        $content_lang_default = mac_content_lang_default();
        $content_lang_enabled = array_values(array_unique(array_merge([$content_lang_default], mac_content_lang_allow_list())));
        $content_lang_data = [];
        if (!empty($info['live_id'])) {
            foreach ($content_lang_enabled as $langCode) {
                $content_lang_data[$langCode] = model('ContentLang')->getFields('live', $info['live_id'], $langCode);
            }
        }
        $this->assign('content_lang_default', $content_lang_default);
        $this->assign('content_lang_enabled', $content_lang_enabled);
        $this->assign('content_lang_multi', count($content_lang_enabled) > 1);
        $this->assign('content_lang_data', $content_lang_data);
        $this->assign('content_translate_available', mac_content_translate_available());

        $this->assign('title', lang('admin/live/title'));
        return $this->fetch('admin@live/info');
    }

    /**
     * 调用已安装的翻译插件（若有）翻译单个字段；没有插件注册时返回空字符串，前端据此禁用按钮。
     */
    public function contentLangTranslate()
    {
        $text = (string)input('post.text', '');
        $from = (string)input('post.from', mac_content_lang_default());
        $to = (string)input('post.to', '');
        if ($text === '' || $to === '') {
            return $this->error(lang('param_err'));
        }
        if (!mac_content_translate_rate_limit($this->_admin['admin_id'])) {
            return $this->error(lang('frequently'));
        }
        $result = mac_content_translate($text, $from, $to, ['content_type' => 'live']);
        if ($result === '') {
            return $this->error(lang('admin/vod/content_lang_translate_err'));
        }
        return json(['code' => 1, 'msg' => 'ok', 'data' => $result]);
    }

    public function del()
    {
        $param = input();
        $ids = $param['ids'];

        if (!empty($ids)) {
            $where = [];
            $where['live_id'] = ['in', $ids];
            $res = model('Live')->delData($where);
            if ($res['code'] > 1) {
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }
        return $this->error(lang('param_err'));
    }

    public function field()
    {
        $param = input();
        $ids = $param['ids'];
        $col = $param['col'];
        $val = $param['val'];

        if (!empty($ids) && isset($col) && isset($val)) {
            $where = [];
            if (is_array($ids)) {
                $where['live_id'] = ['in', $ids];
            } else {
                $where['live_id'] = ['eq', (int)$ids];
            }
            $res = model('Live')->fieldData($where, $col, $val);
            if ($res['code'] > 1) {
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }
        return $this->error(lang('param_err'));
    }

    // ==================== 分类管理 ====================

    public function category()
    {
        $cate_list = model('Live')->categoryList();

        $this->assign('list', $cate_list);
        $this->assign('title', lang('admin/live/cate_title'));
        return $this->fetch('admin@live/category');
    }

    public function category_info()
    {
        if (Request()->isPost()) {
            $param = input();
            $res = model('Live')->categorySave($param);
            if ($res['code'] > 1) {
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }

        $id = input('id');
        $info = [];
        if (!empty($id)) {
            $res = model('Live')->categoryInfo(['cate_id' => (int)$id]);
            $info = isset($res['info']) ? $res['info'] : [];
        }

        $this->assign('info', $info);
        $this->assign('title', lang('admin/live/cate_title'));
        return $this->fetch('admin@live/category_info');
    }

    public function category_del()
    {
        $param = input();
        $ids = $param['ids'];

        if (!empty($ids)) {
            $res = model('Live')->categoryDel($ids);
            if ($res['code'] > 1) {
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }
        return $this->error(lang('param_err'));
    }
}
