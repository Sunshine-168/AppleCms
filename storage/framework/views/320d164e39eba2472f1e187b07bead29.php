<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container">
    <form class="layui-form " action="">
        <blockquote class="layui-elem-quote layui-quote-nm">
            提示信息：部分标签参数可能不全面
        </blockquote>
        
        <div class="layui-form-item">
            <label class="layui-form-label">标签类别：</label>
            <div class="layui-input-block">
                <input type="button" class="layui-btn layui-btn-primary" value="link(<?php echo e(lang('link')); ?>)" onclick="showex('link')"/>

                <input type="button" class="layui-btn layui-btn-primary" value="type(<?php echo e(lang('type')); ?>)" onclick="showex('type')"/>
                <input type="button" class="layui-btn layui-btn-primary" value="topic(<?php echo e(lang('topic')); ?>)" onclick="showex('topic')"/>
                <input type="button" class="layui-btn layui-btn-primary" value="art(<?php echo e(lang('art')); ?>)" onclick="showex('art')"/>
                <input type="button" class="layui-btn layui-btn-primary" value="vod(<?php echo e(lang('vod')); ?>)" onclick="showex('vod')"/>

                <input type="button" class="layui-btn layui-btn-primary" value="area(<?php echo e(lang('area')); ?>)" onclick="showex('area')"/>
                <input type="button" class="layui-btn layui-btn-primary" value="lang(<?php echo e(lang('lang')); ?>)" onclick="showex('lang')"/>
                <input type="button" class="layui-btn layui-btn-primary" value="year(<?php echo e(lang('years')); ?>)" onclick="showex('year')"/>
                <input type="button" class="layui-btn layui-btn-primary" value="letter(<?php echo e(lang('letter')); ?>)" onclick="showex('letter')"/>
                <input type="button" class="layui-btn layui-btn-primary" value="tag(Tag)" onclick="showex('tag')"/>

                <input type="button" class="layui-btn layui-btn-primary" value="gbook(<?php echo e(lang('gbook')); ?>)" onclick="showex('gbook')"/>
                <input type="button" class="layui-btn layui-btn-primary" value="comment(<?php echo e(lang('comment')); ?>)" onclick="showex('comment')"/>
            </div>
        </div>

        <div class="layui-form-item vs v_link">
            <label class="layui-form-label"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="order_link"><option value="desc"><?php echo e(lang('admin/template/reverse_order')); ?></option><option value="asc"><?php echo e(lang('admin/template/positive_order')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="by_link"><option value="sort"><?php echo e(lang('sort')); ?></option><option value="id"><?php echo e(lang('id')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('genre')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="type_link"><option value="all"><?php echo e(lang('all')); ?></option><option value="font"><?php echo e(lang('admin/link/text_link')); ?></option><option value="pic"><?php echo e(lang('admin/link/pic_link')); ?></option></select>
            </div>
        </div>



        <div class="layui-form-item vs v_tag" style="display: none;">
            <label class="layui-form-label"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="order_tag"><option value="asc"><?php echo e(lang('admin/template/positive_order')); ?></option><option value="desc"><?php echo e(lang('admin/template/reverse_order')); ?></option></select>
            </div>
            <label class="layui-form-label w50">tag：</label>
            <div class="layui-input-inline w200">
                <input id="tag_tag" type="text" class="layui-input" value="aa,bb,cc,dd">
            </div>
        </div>


        <div class="layui-form-item vs v_area" style="display: none;">
            <label class="layui-form-label"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="order_area"><option value="asc"><?php echo e(lang('admin/template/positive_order')); ?></option><option value="desc"><?php echo e(lang('admin/template/reverse_order')); ?></option></select>
            </div>
        </div>

        <div class="layui-form-item vs v_lang" style="display: none;">
            <label class="layui-form-label"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="order_lang"><option value="asc"><?php echo e(lang('admin/template/positive_order')); ?></option><option value="desc"><?php echo e(lang('admin/template/reverse_order')); ?></option></select>
            </div>
        </div>

        <div class="layui-form-item vs v_year" style="display: none;">
            <label class="layui-form-label"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="order_year"><option value="desc"><?php echo e(lang('admin/template/reverse_order')); ?></option><option value="asc"><?php echo e(lang('admin/template/positive_order')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('start')); ?>：</label>
            <div class="layui-input-inline w80">
                <input id="start_year" type="text" class="layui-input" value="2000">
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('end')); ?>：</label>
            <div class="layui-input-inline w80">
                <input id="end_year" type="text" class="layui-input" value="<?php echo e(date('Y')); ?>">
            </div>
        </div>


        <div class="layui-form-item vs v_letter" style="display: none;">
            <label class="layui-form-label"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="order_letter"><option value="asc"><?php echo e(lang('admin/template/positive_order')); ?></option><option value="desc"><?php echo e(lang('admin/template/reverse_order')); ?></option></select>
            </div>
        </div>

        <div class="layui-form-item vs v_type" style="display: none;">
            <label class="layui-form-label"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="order_type"><option value="asc"><?php echo e(lang('admin/template/positive_order')); ?></option><option value="desc"><?php echo e(lang('admin/template/reverse_order')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="by_type"><option value="sort"><?php echo e(lang('sort')); ?></option><option value="id"><?php echo e(lang('id')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('model')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="mid_type"><option value=""><?php echo e(lang('all')); ?></option><option value="1"><?php echo e(lang('vod')); ?></option><option value="2"><?php echo e(lang('art')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('data')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="ids_type"><option value="all"><?php echo e(lang('all')); ?></option><option value="parent">一级<?php echo e(lang('type')); ?></option><option value="child">二级<?php echo e(lang('type')); ?></option><option value="1,2,3"><?php echo e(lang('diy_ids')); ?></option></select>
            </div>
        </div>

        <div class="layui-form-item vs v_topic" style="display: none;">
            <label class="layui-form-label"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="order_topic"><option value="desc"><?php echo e(lang('admin/template/reverse_order')); ?></option><option value="asc"><?php echo e(lang('admin/template/positive_order')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="by_topic"><option value="time"><?php echo e(lang('update_time')); ?></option><option value="time_add"><?php echo e(lang('add_time')); ?></option><option value="id">ID</option><option value="hits"><?php echo e(lang('hits')); ?></option><option value="hits_day"><?php echo e(lang('hits_day')); ?></option><option value="hits_week"><?php echo e(lang('hits_week')); ?></option><option value="hits_month"><?php echo e(lang('hits_month')); ?></option><option value="up">顶数</option><option value="down">踩数</option><option value="level"><?php echo e(lang('level')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('data')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="ids_topic"><option value="all"><?php echo e(lang('all')); ?></option><option value="1,2,3"><?php echo e(lang('diy_ids')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('quantity')); ?>：</label>
            <div class="layui-input-inline w80">
                <input id="num_topic" type="text" class="layui-input" value="10">
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('paging')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="paging_topic"><option value="false"><?php echo e(lang('not')); ?></option><option value="true"><?php echo e(lang('yes')); ?></option></select>
            </div>
        </div>

        <div class="layui-form-item vs v_art" style="display: none;">
            <label class="layui-form-label">标签参数：</label>
            <div class="layui-input-inline w100">
                <select id="order_art"><option value="desc"><?php echo e(lang('admin/template/reverse_order')); ?></option><option value="asc"><?php echo e(lang('admin/template/positive_order')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="by_art"><option value="time"><?php echo e(lang('update_time')); ?></option><option value="time_add"><?php echo e(lang('add_time')); ?></option><option value="id">ID</option><option value="hits"><?php echo e(lang('hits')); ?></option><option value="hits_day"><?php echo e(lang('hits_day')); ?></option><option value="hits_week"><?php echo e(lang('hits_week')); ?></option><option value="hits_month"><?php echo e(lang('hits_month')); ?></option><option value="up">顶数</option><option value="down">踩数</option><option value="level"><?php echo e(lang('level')); ?></option><option value="rnd"><?php echo e(lang('rnd_data')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('level')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="level_art"><option value="all"><?php echo e(lang('all')); ?></option><option value="1"><?php echo e(lang('level')); ?>1</option><option value="2"><?php echo e(lang('level')); ?>2</option><option value="3"><?php echo e(lang('level')); ?>3</option><option value="4"><?php echo e(lang('level')); ?>4</option><option value="5"><?php echo e(lang('level')); ?>5</option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('data')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="ids_art"><option value="all"><?php echo e(lang('all')); ?></option><option value="1,2,3"><?php echo e(lang('diy_ids')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('quantity')); ?>：</label>
            <div class="layui-input-inline w80">
                <input id="num_art" type="text" class="layui-input" value="10">
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('paging')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="paging_art"><option value="false"><?php echo e(lang('not')); ?></option><option value="true"><?php echo e(lang('yes')); ?></option></select>
            </div>
        </div>


        <div class="layui-form-item vs v_vod" style="display: none;">
            <label class="layui-form-label"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="order_vod"><option value="desc"><?php echo e(lang('admin/template/reverse_order')); ?></option><option value="asc"><?php echo e(lang('admin/template/positive_order')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="by_vod"><option value="time"><?php echo e(lang('update_time')); ?></option><option value="time_add"><?php echo e(lang('add_time')); ?></option><option value="id">ID</option><option value="hits"><?php echo e(lang('hits')); ?></option><option value="hits_day"><?php echo e(lang('hits_day')); ?></option><option value="hits_week"><?php echo e(lang('hits_week')); ?></option><option value="hits_month"><?php echo e(lang('hits_month')); ?></option><option value="up">顶数</option><option value="down">踩数</option><option value="level"><?php echo e(lang('level')); ?></option><option value="rnd"><?php echo e(lang('rnd_data')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('level')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="level_vod"><option value="all"><?php echo e(lang('all')); ?></option><option value="1"><?php echo e(lang('level')); ?>1</option><option value="2"><?php echo e(lang('level')); ?>2</option><option value="3"><?php echo e(lang('level')); ?>3</option><option value="4"><?php echo e(lang('level')); ?>4</option><option value="5"><?php echo e(lang('level')); ?>5</option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('type')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="type_vod"><option value="all"><?php echo e(lang('all')); ?></option><option value="1,2"><?php echo e(lang('diy_ids')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('data')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="ids_vod"><option value="all"><?php echo e(lang('all')); ?></option><option value="1,2,3"><?php echo e(lang('diy_ids')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('quantity')); ?>：</label>
            <div class="layui-input-inline w80">
                <input id="num_vod" type="text" class="layui-input" value="10">
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('paging')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="paging_vod"><option value="false"><?php echo e(lang('not')); ?></option><option value="true"><?php echo e(lang('yes')); ?></option></select>
            </div>
        </div>


        <div class="layui-form-item vs v_gbook" style="display: none;">
            <label class="layui-form-label"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="order_gbook"><option value="desc"><?php echo e(lang('admin/template/reverse_order')); ?></option><option value="asc"><?php echo e(lang('admin/template/positive_order')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="by_gbook"><option value="time"><?php echo e(lang('time')); ?></option><option value="id"><?php echo e(lang('id')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('quantity')); ?>：</label>
            <div class="layui-input-inline w80">
                <input id="num_gbook" type="text" class="layui-input" value="10">
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('paging')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="paging_gbook"><option value="false"><?php echo e(lang('not')); ?></option><option value="true"><?php echo e(lang('yes')); ?></option></select>
            </div>
        </div>


        <div class="layui-form-item vs v_comment" style="display: none;">
            <label class="layui-form-label"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="order_comment"><option value="desc"><?php echo e(lang('admin/template/reverse_order')); ?></option><option value="asc"><?php echo e(lang('admin/template/positive_order')); ?></option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="by_comment"><option value="time"><?php echo e(lang('time')); ?></option><option value="id">ID</option></select>
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('quantity')); ?>：</label>
            <div class="layui-input-inline w80">
                <input id="num_comment" type="text" class="layui-input" value="10">
            </div>
            <label class="layui-form-label w50"><?php echo e(lang('paging')); ?>：</label>
            <div class="layui-input-inline w100">
                <select id="paging_comment"><option value="false"><?php echo e(lang('not')); ?></option><option value="true"><?php echo e(lang('yes')); ?></option></select>
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label"><span class="c-red">*</span><?php echo e(lang('content')); ?>：</label>
            <div class="layui-input-block">
                <textarea id="labels" name="labels" cols="" rows="" class="layui-textarea"  placeholder="" style="width:100%;height:500px;"></textarea>
            </div>
        </div>

    </form>

</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script>
    var mark='link',l_order='',l_by='';

    layui.use(['form','upload', 'layer'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery
                , upload = layui.upload;

        form.on('select', function(data){
            labelcreate();
        });

        labelcreate();
    });
    

    function showex(obj){
        $(".vs").hide();
        $(".v_"+obj).show();
        mark=obj;
        if(obj=='year'){
            var d = new Date();
            $("#end_year").val(d.getYear());
        }

        labelcreate();
    }
    function labelcreate()
    {
        var c,p;
        var s='',par='';
        var rc=false;
        
        switch(mark)
        {
            case 'link':
                p= ['order','by','type'];
                c= [ ['key',"<?php echo e(lang('serial_num')); ?>"],['link_id',"<?php echo e(lang('id')); ?>"],['link_name',"<?php echo e(lang('name')); ?>"],['link_type',"<?php echo e(lang('genre')); ?>"],['link_logo',"<?php echo e(lang('logo')); ?>"],['link_url',"<?php echo e(lang('url')); ?>"] ];
                break;
            case 'gbook':
                p= ['order','by','num'];
                c= [ ['key',"<?php echo e(lang('serial_num')); ?>"],['gbook_id',"<?php echo e(lang('id')); ?>"],['gbook_name',"<?php echo e(lang('nickname')); ?>"],['gbook_content',"<?php echo e(lang('content')); ?>"],['gbook_ip',"<?php echo e(lang('ip')); ?>"],['gbook_time',"<?php echo e(lang('time')); ?>"],['gbook_reply',"<?php echo e(lang('admin/template/reply_content')); ?>"],['gbook_reply_time',"<?php echo e(lang('reply_time')); ?>"] ]
                break;
            case 'comment':
                p= ['order','by','num'];
                c= [ ['key',"<?php echo e(lang('serial_num')); ?>"],['comment_id',"<?php echo e(lang('id')); ?>"],['comment_name',"<?php echo e(lang('nickname')); ?>"],['comment_content',"<?php echo e(lang('content')); ?>"],['comment_ip',"<?php echo e(lang('ip')); ?>"],['comment_time',"<?php echo e(lang('time')); ?>"] ]
                break;
            case 'letter':
                p= ['order','by'];
                c= [ ['key',"<?php echo e(lang('serial_num')); ?>"],['letter_name',"<?php echo e(lang('name')); ?>"],[':mac_url_vod_search(["letter"=>$vo.letter_name])',"<?php echo e(lang('admin/template/filter_search')); ?>"] ];
                break;
            case 'tag':
                p= ['order','by','tag','table'];
                c= [ ['key',"<?php echo e(lang('serial_num')); ?>"],['tag_name',"<?php echo e(lang('name')); ?>"],[':mac_url_vod_search(["tag"=>$vo.tag_name])',"<?php echo e(lang('admin/template/filter_search')); ?>"] ];
                break;
            case 'area':
                p= ['order','by'];
                c= [ ['key',"<?php echo e(lang('serial_num')); ?>"],['area_name',"<?php echo e(lang('name')); ?>"],[':mac_url_vod_search(["area"=>$vo.area_name])',"<?php echo e(lang('admin/template/filter_search')); ?>"] ];
                break;
            case 'lang':
                p= ['order','by'];
                c= [ ['key',"<?php echo e(lang('serial_num')); ?>"],['lang_name',"<?php echo e(lang('name')); ?>"],[':mac_url_vod_search(["lang"=>$vo.lang_name])',"<?php echo e(lang('admin/template/filter_search')); ?>"] ];
                break;
            case 'year':
                p= ['order','by','start','end'];
                c= [ ['key',"<?php echo e(lang('serial_num')); ?>"],['year_name',"<?php echo e(lang('name')); ?>"],[':mac_url_vod_search(["year"=>$vo.year_name])',"<?php echo e(lang('admin/template/filter_search')); ?>"] ];
                break;
            case 'type':
                p= ['order','by','mid','ids'];
                c= [ ['key',"<?php echo e(lang('serial_num')); ?>"],['type_id',"<?php echo e(lang('id')); ?>"],['type_name',"<?php echo e(lang('name')); ?>"],['type_en',"<?php echo e(lang('en')); ?>"],['type_pid',"<?php echo e(lang('parent_type_id')); ?>"],['type_sort',"<?php echo e(lang('sort')); ?>"],['type_title',"<?php echo e(lang('seo_title')); ?>"],['type_key',"<?php echo e(lang('seo_key')); ?>"],['type_des',"<?php echo e(lang('seo_des')); ?>"],[':mac_url_type($vo)',"<?php echo e(lang('url')); ?>"]  ];
                break;
            case 'topic':
                p= ['order','by','num','paging','ids'];
                c= [ ['key',"<?php echo e(lang('serial_num')); ?>"],['topic_id',"<?php echo e(lang('id')); ?>"],['topic_name',"<?php echo e(lang('name')); ?>"],['topic_en',"<?php echo e(lang('en')); ?>"],['topic_sort',"<?php echo e(lang('sort')); ?>"],['topic_title',"<?php echo e(lang('seo_title')); ?>"],['topic_key',"<?php echo e(lang('seo_key')); ?>"],['topic_des',"<?php echo e(lang('seo_des')); ?>"],['topic_link',"<?php echo e(lang('url')); ?>"],['topic_count',"<?php echo e(lang('admin/topic/count')); ?>"],['topic_pic',"<?php echo e(lang('pic')); ?>"],['topic_pic_thumb',"<?php echo e(lang('pic_thumb')); ?>"],['topic_pic_slide',"<?php echo e(lang('slide')); ?>"],['topic_time_add',"<?php echo e(lang('add_time')); ?>"],['topic_time',"<?php echo e(lang('update_time')); ?>"],['topic_level',"<?php echo e(lang('level')); ?>"],['topic_hits',"<?php echo e(lang('hits')); ?>"],['topic_hits_day',"<?php echo e(lang('hits_day')); ?>"],['topic_hits_week',"<?php echo e(lang('hits_week')); ?>"],['topic_hits_month',"<?php echo e(lang('hits_month')); ?>"],['topic_up',"<?php echo e(lang('up')); ?>"],['topic_down',"<?php echo e(lang('hate')); ?>"],['topic_score',"<?php echo e(lang('score')); ?>"],['topic_score_all',"<?php echo e(lang('score_all')); ?>"],['topic_score_num',"<?php echo e(lang('score_num')); ?>"],['topic_content',"<?php echo e(lang('content')); ?>"],['topic_remarks',"<?php echo e(lang('remarks')); ?>"],['topic_tag','tags'],[':mac_url_topic_detail($vo)',"<?php echo e(lang('url')); ?>"] ];
                break;
            case 'art':
                p= ['order','by','num','paging','ids','type','level'];
                c= [ ['key',"<?php echo e(lang('serial_num')); ?>"],['art_id',"<?php echo e(lang('id')); ?>"],['art_name',"<?php echo e(lang('name')); ?>"],['art_en',"<?php echo e(lang('en')); ?>"],['art_sub',"<?php echo e(lang('sub')); ?>"],['art_from',"<?php echo e(lang('from')); ?>"],['art_content',"<?php echo e(lang('content')); ?>"],['art_remarks',"<?php echo e(lang('remarks')); ?>"],['art_author',"<?php echo e(lang('author')); ?>"],['art_color',"<?php echo e(lang('color')); ?>"],['art_hits',"<?php echo e(lang('hits')); ?>"],['art_hits_day',"<?php echo e(lang('hits_day')); ?>"],['art_hits_week',"<?php echo e(lang('hits_week')); ?>"],['art_hits_month',"<?php echo e(lang('hits_month')); ?>"],['art_up',"<?php echo e(lang('up')); ?>"],['art_down',"<?php echo e(lang('hate')); ?>"],['art_pic',"<?php echo e(lang('pic')); ?>"],['art_pic_thumb',"<?php echo e(lang('pic_thumb')); ?>"],['art_pic_slide',"<?php echo e(lang('slide')); ?>"],['art_time_add',"<?php echo e(lang('add_time')); ?>"],['art_time',"<?php echo e(lang('update_time')); ?>"],['art_blurb',"<?php echo e(lang('blurb')); ?>"],['art_jumpurl',"<?php echo e(lang('jumpurl')); ?>"],['art_level',"<?php echo e(lang('level')); ?>"],['art_letter',"<?php echo e(lang('letter')); ?>"],['art_tag','tags'],['art_class',"<?php echo e(lang('class')); ?>"],[':mac_url_art_detail($vo)',"<?php echo e(lang('url')); ?>"],['type.type_id',"<?php echo e(lang('type_id')); ?>"],['type.type_id_1',"<?php echo e(lang('parent_type_id')); ?>"],['type.type_name',"<?php echo e(lang('type_name')); ?>"],['type.type_en',"<?php echo e(lang('en')); ?>"],['type.type_key',"<?php echo e(lang('seo_key')); ?>"],['type.type_des',"<?php echo e(lang('seo_des')); ?>"],['type.type_title',"<?php echo e(lang('seo_title')); ?>"],[':mac_url_type($vo.type)',"<?php echo e(lang('type')); ?><?php echo e(lang('url')); ?>"] ];
                break;
            case 'vod':
                p= ['order','by','num','paging','ids','type','level'];
                c= [ ['key',"<?php echo e(lang('serial_num')); ?>"],['vod_id',"<?php echo e(lang('id')); ?>"],['vod_name',"<?php echo e(lang('name')); ?>"],['vod_en',"<?php echo e(lang('en')); ?>"],['vod_sub',"<?php echo e(lang('sub')); ?>"],['vod_content',"<?php echo e(lang('content')); ?>"],['vod_remarks',"<?php echo e(lang('remarks')); ?>"],['vod_blurb',"<?php echo e(lang('blurb')); ?>"],['vod_letter',"<?php echo e(lang('letter')); ?>"],['vod_total',"<?php echo e(lang('admin/vod/total')); ?>"],['vod_serial',"<?php echo e(lang('admin/vod/serial')); ?>"],['vod_tv',"<?php echo e(lang('admin/vod/tv')); ?>"],['vod_weekday',"<?php echo e(lang('admin/vod/weekday')); ?>"],['vod_version',"<?php echo e(lang('admin/vod/version')); ?>"],['vod_isend',"<?php echo e(lang('admin/vod/isend')); ?>"],['vod_author',"<?php echo e(lang('author')); ?>"],['vod_jumpurl',"<?php echo e(lang('jumpurl')); ?>"],['vod_color',"<?php echo e(lang('color')); ?>"],['vod_hits',"<?php echo e(lang('hits')); ?>"],['vod_hits_day',"<?php echo e(lang('hits_day')); ?>"],['vod_hits_week',"<?php echo e(lang('hits_week')); ?>"],['vod_hits_month',"<?php echo e(lang('hits_month')); ?>"],['vod_up',"<?php echo e(lang('up')); ?>"],['vod_down',"<?php echo e(lang('hate')); ?>"],['vod_time_add',"<?php echo e(lang('add_time')); ?>"],['vod_time',"<?php echo e(lang('update_time')); ?>"],['vod_level',"<?php echo e(lang('level')); ?>"],['vod_state',"<?php echo e(lang('admin/vod/state')); ?>"],['vod_pic',"<?php echo e(lang('pic')); ?>"],['vod_pic_thumb',"<?php echo e(lang('pic_thumb')); ?>"],['vod_pic_slide',"<?php echo e(lang('slide')); ?>"],['vod_tag','tag'],['vod_actor',"<?php echo e(lang('actor')); ?>"],['vod_director',"<?php echo e(lang('director')); ?>"],['vod_area',"<?php echo e(lang('area')); ?>"],['vod_year',"<?php echo e(lang('years')); ?>"],['vod_stint_play',"<?php echo e(lang('admin/vod/stint_play')); ?>"],['vod_stint_down',"<?php echo e(lang('admin/vod/stint_down')); ?>"],['vod_score',"<?php echo e(lang('score')); ?>"],['vod_score_all',"<?php echo e(lang('score_all')); ?>"],['vod_score_num',"<?php echo e(lang('score_num')); ?>"],['vod_duration',"<?php echo e(lang('admin/vod/duration')); ?>"],['vod_play_from','播放器类型'],['vod_down_from','下载器类型'],[':mac_url_vod_detail($vo)',"<?php echo e(lang('url')); ?>"],[':mac_url_vod_play($vo,1,1)',"<?php echo e(lang('play')); ?><?php echo e(lang('url')); ?>"],[':mac_url_vod_down($vo,1,1)',"<?php echo e(lang('down')); ?><?php echo e(lang('url')); ?>"],['type.type_id',"<?php echo e(lang('type_id')); ?>"],['type.type_id_1',"<?php echo e(lang('parent_type_id')); ?>"],['type.type_name',"<?php echo e(lang('type_name')); ?>"],['type.type_en',"<?php echo e(lang('type')); ?><?php echo e(lang('en')); ?>"],['type.type_key',"<?php echo e(lang('type')); ?><?php echo e(lang('seo_key')); ?>"],['type.type_des',"<?php echo e(lang('type')); ?><?php echo e(lang('seo_des')); ?>"],['type.type_title',"<?php echo e(lang('type')); ?><?php echo e(lang('seo_title')); ?>"],[':mac_url_type($vo.type)',"<?php echo e(lang('type')); ?><?php echo e(lang('url')); ?>"] ];
                break;
        }

        for(i=0;i<p.length;i++){
            if($("#"+p[i]+'_'+mark).val() != undefined && $("#"+p[i]+'_'+mark).val() !=''){
                if(rc)par+=' ';
                par+= p[i]+ '="' +$("#"+p[i]+'_'+mark).val()+'"';
                rc=true;
            }
        }


        if($('#page_'+mark).val() != undefined){
            if($('#page_'+mark).attr("checked")) par= par.replace('num=','pagesize=');
        }
        s='{maccms:'+mark+' '+par+'}' + '\n';
        for(i=0;i<c.length;i++){
            if(c[i][0]=='key'){
                s+= '\t{\$key}' + '  ' + c[i][1] + '\n';
            }
            else{
                if(c[i][0].indexOf(':')==-1){
                    s+= '\t{\$vo.'+c[i][0]+'}' + '  ' + c[i][1] + '\n';
                }
                else{
                    s+= '\t{'+c[i][0]+'}' + '  ' + c[i][1] + '\n';
                }
            }

        }
        s+='{/maccms:'+mark+'}';
        $("#labels").val(s);
    }
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\template\wizard.blade.php ENDPATH**/ ?>