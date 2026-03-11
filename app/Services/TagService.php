<?php

namespace App\Services;

use App\Models\Link;
use App\Models\Type;
use App\Models\Vod;
use App\Models\Art;
use App\Models\Actor;
use App\Models\Topic;
use App\Models\Comment;
use App\Models\Gbook;
use App\Models\Role;
use App\Models\Manga;
use App\Models\Website;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * 模板标签服务类
 * 用于处理 maccms 模板标签的数据获取逻辑
 */
class TagService
{
    /**
     * 获取链接列表
     */
    public function getLinkList($params = [])
    {
        $order = $params['order'] ?? 'asc';
        $by = $params['by'] ?? 'sort';
        $type = $params['type'] ?? 'all';
        $not = $params['not'] ?? '';
        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);
        $cachetime = $params['cachetime'] ?? null;

        $query = Link::query();

        if ($type != 'all') {
            if ($type == 'font') {
                $query->where('link_type', 0);
            } elseif ($type == 'pic') {
                $query->where('link_type', 1);
            } else {
                $query->where('link_type', $type);
            }
        }

        if ($not) {
            $notIds = explode(',', $not);
            $query->whereNotIn('link_id', $notIds);
        }

        $orderBy = 'link_' . $by . ' ' . $order;
        $query->orderByRaw($orderBy);

        if ($start > 0) {
            $query->skip($start);
        }

        if ($num > 0) {
            $query->take($num);
        }

        $cacheKey = 'tag_link_' . md5(json_encode($params));
        
        return Cache::remember($cacheKey, $cachetime ?? config('maccms.app.cache_time', 3600), function () use ($query) {
            return $query->get()->toArray();
        });
    }

    /**
     * 获取地区列表
     */
    public function getAreaList($params = [])
    {
        $order = $params['order'] ?? 'desc';
        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);
        $tid = intval($params['tid'] ?? 0);

        $dataStr = config('maccms.app.vod_extend_area', '');
        
        // 如果指定了分类，从分类扩展中获取
        if ($tid > 0) {
            $type = Type::find($tid);
            if ($type && $type->type_extend) {
                $extend = is_string($type->type_extend) ? json_decode($type->type_extend, true) : $type->type_extend;
                if (isset($extend['area']) && !empty($extend['area'])) {
                    $dataStr = $extend['area'];
                }
            }
        }

        $areas = explode(',', $dataStr);
        $list = [];

        foreach ($areas as $index => $area) {
            if (trim($area)) {
                $list[] = [
                    'area_id' => $index + 1,
                    'area_name' => trim($area),
                ];
            }
        }

        if ($order == 'desc') {
            $list = array_reverse($list);
        }

        if ($start > 0) {
            $list = array_slice($list, $start);
        }

        if ($num > 0) {
            $list = array_slice($list, 0, $num);
        }

        return $list;
    }

    /**
     * 获取语言列表
     */
    public function getLangList($params = [])
    {
        $order = $params['order'] ?? 'desc';
        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);
        $tid = intval($params['tid'] ?? 0);

        $dataStr = config('maccms.app.vod_extend_lang', '');
        
        // 如果指定了分类，从分类扩展中获取
        if ($tid > 0) {
            $type = Type::find($tid);
            if ($type && $type->type_extend) {
                $extend = is_string($type->type_extend) ? json_decode($type->type_extend, true) : $type->type_extend;
                if (isset($extend['lang']) && !empty($extend['lang'])) {
                    $dataStr = $extend['lang'];
                }
            }
        }

        $langs = explode(',', $dataStr);
        $list = [];

        foreach ($langs as $index => $lang) {
            if (trim($lang)) {
                $list[] = [
                    'lang_id' => $index + 1,
                    'lang_name' => trim($lang),
                ];
            }
        }

        if ($order == 'desc') {
            $list = array_reverse($list);
        }

        if ($start > 0) {
            $list = array_slice($list, $start);
        }

        if ($num > 0) {
            $list = array_slice($list, 0, $num);
        }

        return $list;
    }

    /**
     * 获取年份列表
     */
    public function getYearList($params = [])
    {
        $order = $params['order'] ?? 'desc';
        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);

        $currentYear = date('Y');
        $list = [];

        for ($i = $currentYear; $i >= $currentYear - 50; $i--) {
            $list[] = [
                'year_id' => $i,
                'year_name' => $i,
            ];
        }

        if ($order == 'asc') {
            $list = array_reverse($list);
        }

        if ($start > 0) {
            $list = array_slice($list, $start);
        }

        if ($num > 0) {
            $list = array_slice($list, 0, $num);
        }

        return $list;
    }

    /**
     * 获取分类列表
     */
    public function getTypeList($params = [])
    {
        $order = $params['order'] ?? 'asc';
        $by = $params['by'] ?? 'id';
        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);
        $id = $params['id'] ?? '';
        $ids = $params['ids'] ?? '';
        $not = $params['not'] ?? '';
        $parent = $params['parent'] ?? '';
        $flag = $params['flag'] ?? '';
        $mid = $params['mid'] ?? '';
        $format = $params['format'] ?? '';
        $cachetime = $params['cachetime'] ?? null;

        $query = Type::query();

        if ($id) {
            $query->where('type_id', $id);
        }

        if ($ids) {
            $idsArray = explode(',', $ids);
            $query->whereIn('type_id', $idsArray);
        }

        if ($not) {
            $notIds = explode(',', $not);
            $query->whereNotIn('type_id', $notIds);
        }

        if ($parent !== '') {
            if ($parent == '0') {
                $query->where('type_pid', 0);
            } else {
                $query->where('type_pid', $parent);
            }
        }

        if ($flag) {
            $query->where('type_flag', 'like', '%' . $flag . '%');
        }

        if ($mid) {
            $query->where('type_mid', $mid);
        }

        $query->where('type_status', 1);

        $orderBy = 'type_pid asc, type_' . $by . ' ' . $order;
        $query->orderByRaw($orderBy);

        if ($start > 0) {
            $query->skip($start);
        }

        if ($num > 0) {
            $query->take($num);
        }

        $cacheKey = 'tag_type_' . md5(json_encode($params));
        
        return Cache::remember($cacheKey, $cachetime ?? config('maccms.app.cache_time', 3600), function () use ($query, $format) {
            $list = $query->get()->toArray();
            
            if ($format == 'tree') {
                // 构建树形结构
                return $this->buildTypeTree($list);
            }
            
            return $list;
        });
    }

    /**
     * 构建分类树
     */
    protected function buildTypeTree($list)
    {
        $tree = [];
        $map = [];

        foreach ($list as $item) {
            $map[$item['type_id']] = $item;
            $map[$item['type_id']]['child'] = [];
        }

        foreach ($map as $id => $item) {
            if ($item['type_pid'] == 0) {
                $tree[] = &$map[$id];
            } else {
                if (isset($map[$item['type_pid']])) {
                    $map[$item['type_pid']]['child'][] = &$map[$id];
                }
            }
        }

        return $tree;
    }

    /**
     * 获取视频列表
     */
    public function getVodList($params = [])
    {
        $query = Vod::query();
        
        // 处理各种参数
        if (!empty($params['type'])) {
            $typeIds = explode(',', $params['type']);
            $query->whereIn('type_id', $typeIds);
        }

        if (!empty($params['ids'])) {
            $ids = explode(',', $params['ids']);
            $query->whereIn('vod_id', $ids);
        }

        if (!empty($params['not'])) {
            $notIds = explode(',', $params['not']);
            $query->whereNotIn('vod_id', $notIds);
        }

        if (!empty($params['typenot'])) {
            $typeNotIds = explode(',', $params['typenot']);
            $query->whereNotIn('type_id', $typeNotIds);
        }

        if (!empty($params['area'])) {
            $areas = explode(',', $params['area']);
            $query->where(function($q) use ($areas) {
                foreach ($areas as $area) {
                    $q->orWhere('vod_area', 'like', '%' . trim($area) . '%');
                }
            });
        }

        if (!empty($params['lang'])) {
            $langs = explode(',', $params['lang']);
            $query->where(function($q) use ($langs) {
                foreach ($langs as $lang) {
                    $q->orWhere('vod_lang', 'like', '%' . trim($lang) . '%');
                }
            });
        }

        if (!empty($params['year'])) {
            $years = explode(',', $params['year']);
            $query->whereIn('vod_year', $years);
        }

        if (!empty($params['level'])) {
            $levels = explode(',', $params['level']);
            $query->whereIn('vod_level', $levels);
        }

        if (!empty($params['letter'])) {
            $letters = explode(',', $params['letter']);
            $query->whereIn('vod_letter', $letters);
        }

        if (!empty($params['version'])) {
            $versions = explode(',', $params['version']);
            $query->where(function($q) use ($versions) {
                foreach ($versions as $version) {
                    $q->orWhere('vod_version', 'like', '%' . trim($version) . '%');
                }
            });
        }

        if (!empty($params['state'])) {
            $states = explode(',', $params['state']);
            $query->where(function($q) use ($states) {
                foreach ($states as $state) {
                    $q->orWhere('vod_state', 'like', '%' . trim($state) . '%');
                }
            });
        }

        if (isset($params['isend'])) {
            $query->where('vod_isend', $params['isend']);
        }

        // 状态筛选
        if (!isset($params['status']) || $params['status'] !== 'all') {
            $query->where('vod_status', 1);
        }

        $order = $params['order'] ?? 'desc';
        $by = $params['by'] ?? 'time';
        
        // 验证排序字段
        $allowedBy = ['id', 'time', 'time_add', 'score', 'hits', 'hits_day', 'hits_week', 'hits_month', 'up', 'down', 'level'];
        if (!in_array($by, $allowedBy)) {
            $by = 'time';
        }
        
        $orderBy = 'vod_' . $by . ' ' . $order;
        $query->orderByRaw($orderBy);

        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);

        if ($start > 0) {
            $query->skip($start);
        }

        if ($num > 0) {
            $query->take($num);
        }

        $cacheKey = 'tag_vod_' . md5(json_encode($params));
        $cachetime = $params['cachetime'] ?? config('maccms.app.cache_time', 3600);

        return Cache::remember($cacheKey, $cachetime, function () use ($query) {
            return $query->get()->toArray();
        });
    }

    /**
     * 获取文章列表
     */
    public function getArtList($params = [])
    {
        $query = Art::query();

        if (!empty($params['type'])) {
            $typeIds = explode(',', $params['type']);
            $query->whereIn('type_id', $typeIds);
        }

        if (!empty($params['ids'])) {
            $ids = explode(',', $params['ids']);
            $query->whereIn('art_id', $ids);
        }

        if (!empty($params['not'])) {
            $notIds = explode(',', $params['not']);
            $query->whereNotIn('art_id', $notIds);
        }

        if (!isset($params['status']) || $params['status'] !== 'all') {
            $query->where('art_status', 1);
        }

        $order = $params['order'] ?? 'desc';
        $by = $params['by'] ?? 'time';
        $orderBy = 'art_' . $by . ' ' . $order;
        $query->orderByRaw($orderBy);

        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);

        if ($start > 0) {
            $query->skip($start);
        }

        if ($num > 0) {
            $query->take($num);
        }

        $cacheKey = 'tag_art_' . md5(json_encode($params));
        $cachetime = $params['cachetime'] ?? config('maccms.app.cache_time', 3600);

        return Cache::remember($cacheKey, $cachetime, function () use ($query) {
            return $query->get()->toArray();
        });
    }

    /**
     * 获取演员列表
     */
    public function getActorList($params = [])
    {
        $query = Actor::query();

        if (!empty($params['ids'])) {
            $ids = explode(',', $params['ids']);
            $query->whereIn('actor_id', $ids);
        }

        if (!empty($params['not'])) {
            $notIds = explode(',', $params['not']);
            $query->whereNotIn('actor_id', $notIds);
        }

        if (!empty($params['area'])) {
            $query->where('actor_area', 'like', '%' . $params['area'] . '%');
        }

        if (!empty($params['name'])) {
            $query->where('actor_name', 'like', '%' . $params['name'] . '%');
        }

        if (!empty($params['letter'])) {
            $query->where('actor_letter', $params['letter']);
        }

        if (!isset($params['status']) || $params['status'] !== 'all') {
            $query->where('actor_status', 1);
        }

        $order = $params['order'] ?? 'desc';
        $by = $params['by'] ?? 'time';
        $orderBy = 'actor_' . $by . ' ' . $order;
        $query->orderByRaw($orderBy);

        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);

        if ($start > 0) {
            $query->skip($start);
        }

        if ($num > 0) {
            $query->take($num);
        }

        $cacheKey = 'tag_actor_' . md5(json_encode($params));
        $cachetime = $params['cachetime'] ?? config('maccms.app.cache_time', 3600);

        return Cache::remember($cacheKey, $cachetime, function () use ($query) {
            return $query->get()->toArray();
        });
    }

    /**
     * 获取评论列表
     */
    public function getCommentList($params = [])
    {
        $query = Comment::query();

        if (!empty($params['id'])) {
            $query->where('comment_id', $params['id']);
        }

        if (!empty($params['mid'])) {
            $query->where('comment_mid', $params['mid']);
        }

        if (!empty($params['rid'])) {
            $query->where('comment_rid', $params['rid']);
        }

        if (!empty($params['uid'])) {
            $query->where('comment_uid', $params['uid']);
        }

        $order = $params['order'] ?? 'desc';
        $by = $params['by'] ?? 'time';
        $orderBy = 'comment_' . $by . ' ' . $order;
        $query->orderByRaw($orderBy);

        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);

        if ($start > 0) {
            $query->skip($start);
        }

        if ($num > 0) {
            $query->take($num);
        }

        $cacheKey = 'tag_comment_' . md5(json_encode($params));
        $cachetime = $params['cachetime'] ?? config('maccms.app.cache_time', 3600);

        return Cache::remember($cacheKey, $cachetime, function () use ($query) {
            return $query->get()->toArray();
        });
    }

    /**
     * 获取留言列表
     */
    public function getGbookList($params = [])
    {
        $query = Gbook::query();

        if (!empty($params['rid'])) {
            $query->where('gbook_rid', $params['rid']);
        }

        if (!empty($params['uid'])) {
            $query->where('gbook_uid', $params['uid']);
        }

        $order = $params['order'] ?? 'desc';
        $by = $params['by'] ?? 'time';
        $orderBy = 'gbook_' . $by . ' ' . $order;
        $query->orderByRaw($orderBy);

        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);

        if ($start > 0) {
            $query->skip($start);
        }

        if ($num > 0) {
            $query->take($num);
        }

        $cacheKey = 'tag_gbook_' . md5(json_encode($params));
        $cachetime = $params['cachetime'] ?? config('maccms.app.cache_time', 3600);

        return Cache::remember($cacheKey, $cachetime, function () use ($query) {
            return $query->get()->toArray();
        });
    }

    /**
     * 获取专题列表
     */
    public function getTopicList($params = [])
    {
        $query = Topic::query();

        if (!empty($params['ids'])) {
            $ids = explode(',', $params['ids']);
            $query->whereIn('topic_id', $ids);
        }

        if (!empty($params['not'])) {
            $notIds = explode(',', $params['not']);
            $query->whereNotIn('topic_id', $notIds);
        }

        if (!isset($params['status']) || $params['status'] !== 'all') {
            $query->where('topic_status', 1);
        }

        $order = $params['order'] ?? 'desc';
        $by = $params['by'] ?? 'time';
        $orderBy = 'topic_' . $by . ' ' . $order;
        $query->orderByRaw($orderBy);

        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);

        if ($start > 0) {
            $query->skip($start);
        }

        if ($num > 0) {
            $query->take($num);
        }

        $cacheKey = 'tag_topic_' . md5(json_encode($params));
        $cachetime = $params['cachetime'] ?? config('maccms.app.cache_time', 3600);

        return Cache::remember($cacheKey, $cachetime, function () use ($query) {
            return $query->get()->toArray();
        });
    }

    /**
     * 获取角色列表
     */
    public function getRoleList($params = [])
    {
        $query = Role::query();

        if (!empty($params['ids'])) {
            $ids = explode(',', $params['ids']);
            $query->whereIn('role_id', $ids);
        }

        if (!empty($params['not'])) {
            $notIds = explode(',', $params['not']);
            $query->whereNotIn('role_id', $notIds);
        }

        if (!empty($params['rid'])) {
            $query->where('role_rid', $params['rid']);
        }

        if (!empty($params['actor'])) {
            $query->where('role_actor', 'like', '%' . $params['actor'] . '%');
        }

        if (!isset($params['status']) || $params['status'] !== 'all') {
            $query->where('role_status', 1);
        }

        $order = $params['order'] ?? 'desc';
        $by = $params['by'] ?? 'time';
        $orderBy = 'role_' . $by . ' ' . $order;
        $query->orderByRaw($orderBy);

        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);

        if ($start > 0) {
            $query->skip($start);
        }

        if ($num > 0) {
            $query->take($num);
        }

        $cacheKey = 'tag_role_' . md5(json_encode($params));
        $cachetime = $params['cachetime'] ?? config('maccms.app.cache_time', 3600);

        return Cache::remember($cacheKey, $cachetime, function () use ($query) {
            return $query->get()->toArray();
        });
    }

    /**
     * 获取版本列表
     */
    public function getVersionList($params = [])
    {
        $order = $params['order'] ?? 'desc';
        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);

        $versions = explode(',', config('maccms.app.vod_extend_version', ''));
        $list = [];

        foreach ($versions as $index => $version) {
            if (trim($version)) {
                $list[] = [
                    'version_id' => $index + 1,
                    'version_name' => trim($version),
                ];
            }
        }

        if ($order == 'desc') {
            $list = array_reverse($list);
        }

        if ($start > 0) {
            $list = array_slice($list, $start);
        }

        if ($num > 0) {
            $list = array_slice($list, 0, $num);
        }

        return $list;
    }

    /**
     * 获取状态列表
     */
    public function getStateList($params = [])
    {
        $order = $params['order'] ?? 'desc';
        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);

        $states = explode(',', config('maccms.app.vod_extend_state', ''));
        $list = [];

        foreach ($states as $index => $state) {
            if (trim($state)) {
                $list[] = [
                    'state_id' => $index + 1,
                    'state_name' => trim($state),
                ];
            }
        }

        if ($order == 'desc') {
            $list = array_reverse($list);
        }

        if ($start > 0) {
            $list = array_slice($list, $start);
        }

        if ($num > 0) {
            $list = array_slice($list, 0, $num);
        }

        return $list;
    }

    /**
     * 获取字母列表
     */
    public function getLetterList($params = [])
    {
        $order = $params['order'] ?? 'asc';
        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 26);

        $letters = range('A', 'Z');
        $list = [];

        foreach ($letters as $index => $letter) {
            $list[] = [
                'letter_id' => $index + 1,
                'letter_name' => $letter,
            ];
        }

        if ($order == 'desc') {
            $list = array_reverse($list);
        }

        if ($start > 0) {
            $list = array_slice($list, $start);
        }

        if ($num > 0) {
            $list = array_slice($list, 0, $num);
        }

        return $list;
    }

    /**
     * 获取网站列表
     */
    public function getWebsiteList($params = [])
    {
        $query = Website::query();

        if (!empty($params['ids'])) {
            $ids = explode(',', $params['ids']);
            $query->whereIn('website_id', $ids);
        }

        if (!empty($params['not'])) {
            $notIds = explode(',', $params['not']);
            $query->whereNotIn('website_id', $notIds);
        }

        if (!isset($params['status']) || $params['status'] !== 'all') {
            $query->where('website_status', 1);
        }

        $order = $params['order'] ?? 'desc';
        $by = $params['by'] ?? 'time';
        $orderBy = 'website_' . $by . ' ' . $order;
        $query->orderByRaw($orderBy);

        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);

        if ($start > 0) {
            $query->skip($start);
        }

        if ($num > 0) {
            $query->take($num);
        }

        $cacheKey = 'tag_website_' . md5(json_encode($params));
        $cachetime = $params['cachetime'] ?? config('maccms.app.cache_time', 3600);

        return Cache::remember($cacheKey, $cachetime, function () use ($query) {
            return $query->get()->toArray();
        });
    }

    /**
     * 获取分类列表（class 是 type 的别名）
     */
    public function getClassList($params = [])
    {
        return $this->getTypeList($params);
    }

    /**
     * 获取漫画列表
     */
    public function getMangaList($params = [])
    {
        $query = Manga::query();
        
        // 处理各种参数（与 vod 类似）
        if (!empty($params['type'])) {
            $typeIds = explode(',', $params['type']);
            $query->whereIn('type_id', $typeIds);
        }

        if (!empty($params['ids'])) {
            $ids = explode(',', $params['ids']);
            $query->whereIn('manga_id', $ids);
        }

        if (!empty($params['not'])) {
            $notIds = explode(',', $params['not']);
            $query->whereNotIn('manga_id', $notIds);
        }

        if (!empty($params['typenot'])) {
            $typeNotIds = explode(',', $params['typenot']);
            $query->whereNotIn('type_id', $typeNotIds);
        }

        if (!empty($params['area'])) {
            $areas = explode(',', $params['area']);
            $query->where(function($q) use ($areas) {
                foreach ($areas as $area) {
                    $q->orWhere('manga_area', 'like', '%' . trim($area) . '%');
                }
            });
        }

        if (!empty($params['lang'])) {
            $langs = explode(',', $params['lang']);
            $query->where(function($q) use ($langs) {
                foreach ($langs as $lang) {
                    $q->orWhere('manga_lang', 'like', '%' . trim($lang) . '%');
                }
            });
        }

        if (!empty($params['year'])) {
            $years = explode(',', $params['year']);
            $query->whereIn('manga_year', $years);
        }

        if (!empty($params['level'])) {
            $levels = explode(',', $params['level']);
            $query->whereIn('manga_level', $levels);
        }

        if (!empty($params['letter'])) {
            $letters = explode(',', $params['letter']);
            $query->whereIn('manga_letter', $letters);
        }

        if (!empty($params['version'])) {
            $versions = explode(',', $params['version']);
            $query->where(function($q) use ($versions) {
                foreach ($versions as $version) {
                    $q->orWhere('manga_version', 'like', '%' . trim($version) . '%');
                }
            });
        }

        if (!empty($params['state'])) {
            $states = explode(',', $params['state']);
            $query->where(function($q) use ($states) {
                foreach ($states as $state) {
                    $q->orWhere('manga_state', 'like', '%' . trim($state) . '%');
                }
            });
        }

        if (!empty($params['tag'])) {
            $tags = explode(',', $params['tag']);
            $query->where(function($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhere('manga_tag', 'like', '%' . trim($tag) . '%');
                }
            });
        }

        if (!empty($params['class'])) {
            $classes = explode(',', $params['class']);
            $query->where(function($q) use ($classes) {
                foreach ($classes as $class) {
                    $q->orWhere('manga_class', 'like', '%' . trim($class) . '%');
                }
            });
        }

        if (isset($params['isend'])) {
            $query->where('manga_isend', $params['isend']);
        }

        // 状态筛选
        if (!isset($params['status']) || $params['status'] !== 'all') {
            $query->where('manga_status', 1);
        }

        $order = $params['order'] ?? 'desc';
        $by = $params['by'] ?? 'time';
        
        // 验证排序字段
        $allowedBy = ['id', 'time', 'time_add', 'score', 'hits', 'hits_day', 'hits_week', 'hits_month', 'up', 'down', 'level'];
        if (!in_array($by, $allowedBy)) {
            $by = 'time';
        }
        
        $orderBy = 'manga_' . $by . ' ' . $order;
        $query->orderByRaw($orderBy);

        $start = intval($params['start'] ?? 0);
        $num = intval($params['num'] ?? 20);

        if ($start > 0) {
            $query->skip($start);
        }

        if ($num > 0) {
            $query->take($num);
        }

        $cacheKey = 'tag_manga_' . md5(json_encode($params));
        $cachetime = $params['cachetime'] ?? config('maccms.app.cache_time', 3600);

        return Cache::remember($cacheKey, $cachetime, function () use ($query) {
            return $query->get()->toArray();
        });
    }
}
