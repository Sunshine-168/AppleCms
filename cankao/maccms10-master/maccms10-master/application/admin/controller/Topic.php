<?php
namespace app\admin\controller;
use think\Db;

class Topic extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function data()
    {
        $param = input();
        $param['page'] = intval($param['page']) <1 ? 1 : $param['page'];
        $param['limit'] = intval($param['limit']) <1 ? $this->_pagesize : $param['limit'];

        $where=[];
        if(in_array($param['status'],['0','1'],true)){
            $where['topic_status'] = ['eq',$param['status']];
        }
        if(!empty($param['wd'])){
            $param['wd'] = htmlspecialchars(urldecode($param['wd']));
            $like = mac_search_wd_like($param['wd']);
            if ($like) {
                $where['topic_name'] = $like;
            }
        }

        $order='topic_time desc';
        $res = model('Topic')->listData($where,$order,$param['page'],$param['limit']);

        foreach($res['list'] as $k=>&$v){
            $v['ismake'] = 1;
            if($GLOBALS['config']['view']['topic_detail'] >0 && $v['topic_time_make'] < $v['topic_time']){
                $v['ismake'] = 0;
            }
        }

        $this->assign('list',$res['list']);
        $this->assign('total',$res['total']);
        $this->assign('page',$res['page']);
        $this->assign('limit',$res['limit']);

        $param['page'] = '{page}';
        $param['limit'] = '{limit}';
        $this->assign('param',$param);
        $this->assign('title',lang('admin/topic/title'));
        return $this->fetch('admin@topic/index');
    }

    public function info()
    {
        if (Request()->isPost()) {
            $param = input('post.');
            $contentLang = $param['content_lang'] ?? [];
            unset($param['content_lang']);
            $res = model('Topic')->saveData($param);
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            $topicId = (int)($res['topic_id'] ?? 0);
            if ($topicId > 0 && is_array($contentLang)) {
                //默认语言以原始行为准，只写非默认语言的译文（见 mac_content_lang_overlay 注释）
                $defaultLang = mac_content_lang_default();
                foreach ($contentLang as $langCode => $fields) {
                    if ((string)$langCode === $defaultLang || !is_array($fields)) {
                        continue;
                    }
                    model('ContentLang')->saveFields('topic', $topicId, (string)$langCode, $fields, 'manual', 1);
                }
            }
            return $this->success($res['msg']);
        }


        $id = input('id');
        $where=[];
        $where['topic_id'] = ['eq',$id];
        $res = model('Topic')->infoData($where);

        //内容级多语言：默认语言永远排第一位（即便后台没勾选它），其后跟已启用的其它语言
        $content_lang_default = mac_content_lang_default();
        $content_lang_enabled = array_values(array_unique(array_merge([$content_lang_default], mac_content_lang_allow_list())));
        $content_lang_data = [];
        if (!empty($res['info']['topic_id'])) {
            foreach ($content_lang_enabled as $langCode) {
                $content_lang_data[$langCode] = model('ContentLang')->getFields('topic', $res['info']['topic_id'], $langCode);
            }
        }
        $this->assign('info',$res['info']);
        $this->assign('content_lang_default', $content_lang_default);
        $this->assign('content_lang_enabled', $content_lang_enabled);
        $this->assign('content_lang_multi', count($content_lang_enabled) > 1);
        $this->assign('content_lang_data', $content_lang_data);
        $this->assign('content_translate_available', mac_content_translate_available());

        $config = config('maccms.site');
        $this->assign('install_dir',$config['install_dir']);
        $this->assign('title',lang('admin/topic/title'));
        return $this->fetch('admin@topic/info');
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
        $result = mac_content_translate($text, $from, $to, ['content_type' => 'topic']);
        if ($result === '') {
            return $this->error(lang('admin/vod/content_lang_translate_err'));
        }
        return json(['code' => 1, 'msg' => 'ok', 'data' => $result]);
    }

    public function del()
    {
        $param = input();
        $ids = $param['ids'];

        if(!empty($ids)){
            $where=[];
            $where['topic_id'] = ['in',$ids];
            $res = model('Topic')->delData($where);
            if($res['code']>1){
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

        if(!empty($ids) && in_array($col,['topic_status','topic_level']) ){
            $where=[];
            $where['topic_id'] = ['in',$ids];

            $res = model('Topic')->fieldData($where,$col,$val);
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }
        return $this->error(lang('param_err'));
    }

}
