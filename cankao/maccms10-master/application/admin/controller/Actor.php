<?php
namespace app\admin\controller;
use think\Db;
use app\common\util\Pinyin;

class Actor extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function data()
    {
        $param = input();
        $param['page'] = intval($param['page']) < 1 ? 1 : $param['page'];
        $param['limit'] = intval($param['limit']) < 1 ? $this->_pagesize : $param['limit'];

        $where = [];
        if (!empty($param['type'])) {
            $where['type_id|type_id_1'] = ['eq', $param['type']];
        }
        if (!empty($param['level'])) {
            $where['actor_level'] = ['eq', $param['level']];
        }
        if (in_array($param['status'], ['0', '1'])) {
            $where['actor_status'] = ['eq', $param['status']];
        }
        if(!empty($param['pic'])){
            if($param['pic'] == '1'){
                $where['actor_pic'] = ['eq',''];
            }
            elseif($param['pic'] == '2'){
                $where['actor_pic'] = ['like','http%'];
            }
            elseif($param['pic'] == '3'){
                $where['actor_pic'] = ['like','%#err%'];
            }
        }
        if(!empty($param['wd'])){
            $param['wd'] = htmlspecialchars(urldecode($param['wd']));
            $like = mac_search_wd_like($param['wd']);
            if ($like) {
                $where['actor_name'] = $like;
            }
        }

        $order='actor_time desc';
        $res = model('Actor')->listData($where,$order,$param['page'],$param['limit']);

        $this->assign('list', $res['list']);
        $this->assign('total', $res['total']);
        $this->assign('page', $res['page']);
        $this->assign('limit', $res['limit']);

        $param['page'] = '{page}';
        $param['limit'] = '{limit}';
        $this->assign('param', $param);

        $type_tree = model('Type')->getCache('type_tree');
        $this->assign('type_tree', $type_tree);

        $this->assign('title', lang('admin/actor/title'));
        return $this->fetch('admin@actor/index');
    }

    public function info()
    {
        if (Request()->isPost()) {
            $param = input('post.');
            $contentLang = $param['content_lang'] ?? [];
            unset($param['content_lang']);
            $res = model('Actor')->saveData($param);
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            $actorId = (int)($res['actor_id'] ?? 0);
            if ($actorId > 0 && is_array($contentLang)) {
                //默认语言以原始行为准，只写非默认语言的译文（见 mac_content_lang_overlay 注释）
                $defaultLang = mac_content_lang_default();
                foreach ($contentLang as $langCode => $fields) {
                    if ((string)$langCode === $defaultLang || !is_array($fields)) {
                        continue;
                    }
                    model('ContentLang')->saveFields('actor', $actorId, (string)$langCode, $fields, 'manual', 1);
                }
            }
            return $this->success($res['msg']);
        }

        $id = input('id');
        $where=[];
        $where['actor_id'] = ['eq',$id];
        $res = model('Actor')->infoData($where);
        $info = $res['info'];
        $this->assign('info',$info);

        $type_tree = model('Type')->getCache('type_tree');
        $this->assign('type_tree', $type_tree);

        //内容级多语言：默认语言永远排第一位（即便后台没勾选它），其后跟已启用的其它语言
        $content_lang_default = mac_content_lang_default();
        $content_lang_enabled = array_values(array_unique(array_merge([$content_lang_default], mac_content_lang_allow_list())));
        $content_lang_data = [];
        if (!empty($info['actor_id'])) {
            foreach ($content_lang_enabled as $langCode) {
                $content_lang_data[$langCode] = model('ContentLang')->getFields('actor', $info['actor_id'], $langCode);
            }
        }
        $this->assign('content_lang_default', $content_lang_default);
        $this->assign('content_lang_enabled', $content_lang_enabled);
        $this->assign('content_lang_multi', count($content_lang_enabled) > 1);
        $this->assign('content_lang_data', $content_lang_data);
        $this->assign('content_translate_available', mac_content_translate_available());

        $this->assign('title',lang('admin/actor/title'));
        return $this->fetch('admin@actor/info');
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
        $result = mac_content_translate($text, $from, $to, ['content_type' => 'actor']);
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
            $where['actor_id'] = ['in',$ids];
            $res = model('Actor')->delData($where);
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
        $start = $param['start'];
        $end = $param['end'];


        if(!empty($ids) && in_array($col,['actor_status','actor_lock','actor_level','type_id','actor_hits'])){
            $where=[];
            $update = [];
            $where['actor_id'] = ['in',$ids];
            if(empty($start)){
                $update[$col] = $val;
                if($col == 'type_id'){
                    $type_list = model('Type')->getCache();
                    $id1 = intval($type_list[$val]['type_pid']);
                    $update['type_id_1'] = $id1;
                }
                $res = model('Actor')->fieldData($where, $update);
            }
            else{
                if(empty($end)){$end = 9999;}
                $ids = explode(',',$ids);
                foreach($ids as $k=>$v){
                    $val = rand($start,$end);
                    $where['actor_id'] = ['eq',$v];
                    $update[$col] = $val;
                    $res = model('Actor')->fieldData($where, $update);
                }
            }
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }
        return $this->error(lang('param_err'));
    }

}
