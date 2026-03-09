@include('../../../application/admin/view/public/head')
<style>
    .layui-form-pane .layui-form-label { width:140px; }
    .layui-form-pane .layui-input-block { margin-left:140px; }
</style>
<div class="page-container">
    <form class="layui-form layui-form-pane" action="">
        <input type="hidden" name="__token__" value="{{ $Request.token }}" />
        <div class="layui-tab" lay-filter="tb1">
            <ul class="layui-tab-title">
                <li class="layui-this" lay-id="configcollect_1">{{ __('admin.admin/system/configcollect/vod') }}</li>
                <li lay-id="configcollect_2">{{ __('admin.admin/system/configcollect/art') }}</li>
                <li lay-id="configcollect_3">{{ __('admin.admin/system/configcollect/actor') }}</li>
                <li lay-id="configcollect_4">{{ __('admin.admin/system/configcollect/role') }}</li>
                <li lay-id="configcollect_5">{{ __('admin.admin/system/configcollect/website') }}</li>
                <li lay-id="configcollect_6">{{ __('admin.admin/system/configcollect/comment') }}</li>
                <li lay-id="configcollect_7">{{ __('admin.admin/system/configcollect/words') }}</li>
            </ul>

            <div class="layui-tab-content">

                <div class="layui-tab-item layui-show">

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/status') }}：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="collect[vod][status]" value="0" title="{{ __('admin.reviewed_not') }}" @if(condition="$config['collect']['vod']['status'] != 1")checked @endif>
                        <input type="radio" name="collect[vod][status]" value="1" title="{{ __('admin.reviewed') }}" @if(condition="$config['collect']['vod']['status'] == 1")checked @endif>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/hits_rnd') }}：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[vod][hits_start]" placeholder="{{ __('admin.min_val') }}" value="{{ $config['collect']['vod']['hits_start'] }}" class="layui-input">
                    </div>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[vod][hits_end]" placeholder="{{ __('admin.max_val') }}" value="{{ $config['collect']['vod']['hits_end'] }}" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/updown_rnd') }}：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[vod][updown_start]" placeholder="{{ __('admin.min_val') }}" value="{{ $config['collect']['vod']['updown_start'] }}" class="layui-input">
                    </div>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[vod][updown_end]" placeholder="{{ __('admin.max_val') }}" value="{{ $config['collect']['vod']['updown_end'] }}" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/score_rnd') }}：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][score]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['vod']['score'] != 1")checked @endif>
                        <input type="radio" name="collect[vod][score]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['vod']['score'] == 1")checked @endif>
                    </div>
                </div>


                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/sync_pic') }}：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][pic]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['vod']['pic'] != 1")checked @endif>
                        <input type="radio" name="collect[vod][pic]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['vod']['pic'] == 1")checked @endif>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/auto_tag') }}：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][tag]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['vod']['tag'] != 1")checked @endif>
                        <input type="radio" name="collect[vod][tag]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['vod']['tag'] == 1")checked @endif>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/class_filter') }}：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][class_filter]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['vod']['class_filter'] == '0'")checked @endif>
                        <input type="radio" name="collect[vod][class_filter]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['vod']['class_filter'] != '0'")checked @endif>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin.admin/system/configcollect/class_filter_tip') }}</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/psename') }}：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][psename]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['vod']['psename'] != 1")checked @endif>
                        <input type="radio" name="collect[vod][psename]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['vod']['psename'] == 1")checked @endif>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin.admin/system/configcollect/psename_tip') }}</div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/psernd') }}：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][psernd]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['vod']['psernd'] != 1")checked @endif>
                        <input type="radio" name="collect[vod][psernd]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['vod']['psernd'] == 1")checked @endif>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/psesyn') }}：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][psesyn]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['vod']['psesyn'] != 1")checked @endif>
                        <input type="radio" name="collect[vod][psesyn]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['vod']['psesyn'] == 1")checked @endif>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/pseplayer') }}：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][pseplayer]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['vod']['pseplayer'] != 1")checked @endif>
                        <input type="radio" name="collect[vod][pseplayer]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['vod']['pseplayer'] == 1")checked @endif>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/psearea') }}：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][psearea]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['vod']['psearea'] != 1")checked @endif>
                        <input type="radio" name="collect[vod][psearea]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['vod']['psearea'] == 1")checked @endif>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/pselang') }}：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][pselang]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['vod']['pselang'] != 1")checked @endif>
                        <input type="radio" name="collect[vod][pselang]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['vod']['pselang'] == 1")checked @endif>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/urlrole') }}：</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="collect[vod][urlrole]" value="0" title="{{ __('admin.replace') }}" @if(condition="$config['collect']['vod']['urlrole'] != 1")checked @endif>
                        <input type="radio" name="collect[vod][urlrole]" value="1" title="{{ __('admin.merge') }}" @if(condition="$config['collect']['vod']['urlrole'] == 1")checked @endif>
                        <!-- <input type="radio" name="collect[vod][urlrole]" value="2" title="{{ __('admin.admin/system/configcollect/urlrole/use_more') }}" @if(condition="$config['collect']['vod']['urlrole'] == 2")checked @endif> -->
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin.admin/system/configcollect/urlrole_tip') }}</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">
                        {{ __('admin.admin/system/configcollect/inrule') }}：</label>
                    <div class="layui-input-block">
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="a" title="{{ __('admin.name') }}" @if(condition="strpos($config['collect']['vod']['inrule'],'a') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="b" title="{{ __('admin.type') }}" @if(condition="strpos($config['collect']['vod']['inrule'],'b') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="c" title="{{ __('admin.years') }}" @if(condition="strpos($config['collect']['vod']['inrule'],'c') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="d" title="{{ __('admin.area') }}" @if(condition="strpos($config['collect']['vod']['inrule'],'d') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="e" title="{{ __('admin.lang') }}" @if(condition="strpos($config['collect']['vod']['inrule'],'e') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="f" title="{{ __('admin.actor') }}" @if(condition="strpos($config['collect']['vod']['inrule'],'f') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="g" title="{{ __('admin.director') }}" @if(condition="strpos($config['collect']['vod']['inrule'],'g') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][inrule][]" value="h" title="{{ __('admin.douban_id') }}" @if(condition="strpos($config['collect']['vod']['inrule'],'h') !==false")checked @endif>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">
                        {{ __('admin.admin/system/configcollect/uprule') }}：</label>
                    <div class="layui-input-block">
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="a" title="{{ __('admin.playurl') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'a') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="b" title="{{ __('admin.downurl') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'b') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="c" title="{{ __('admin.serial') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'c') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="d" title="{{ __('admin.remarks') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'d') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="e" title="{{ __('admin.director') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'e') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="f" title="{{ __('admin.actor') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'f') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="g" title="{{ __('admin.years') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'g') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="h" title="{{ __('admin.area') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'h') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="i" title="{{ __('admin.lang') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'i') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="j" title="{{ __('admin.pic') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'j') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="k" title="{{ __('admin.content') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'k') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="l" title="TAG" @if(condition="strpos($config['collect']['vod']['uprule'],'l') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="m" title="{{ __('admin.sub') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'m') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="n" title="{{ __('admin.class') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'n') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="o" title="{{ __('admin.writer') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'o') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="p" title="{{ __('admin.version') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'p') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="q" title="{{ __('admin.state') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'q') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="r" title="{{ __('admin.blurb') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'r') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="s" title="{{ __('admin.tv') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'s') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="t" title="{{ __('admin.weekday') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'t') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="u" title="{{ __('admin.total') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'u') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="v" title="{{ __('admin.isend') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'v') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[vod][uprule][]" value="w" title="{{ __('admin.plot') }}" @if(condition="strpos($config['collect']['vod']['uprule'],'w') !==false")checked @endif>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/filter') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[vod][filter]" class="layui-textarea" placeholder="{{ __('admin.multi_separate_tip') }}">{{ $config['collect']['vod']['filter'] }}</textarea>
                    </div>
                </div>
            </div>

                <div class="layui-tab-item">

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/status') }}：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="collect[art][status]" value="0" title="{{ __('admin.reviewed_not') }}" @if(condition="$config['collect']['art']['status'] != 1")checked @endif>
                        <input type="radio" name="collect[art][status]" value="1" title="{{ __('admin.reviewed') }}" @if(condition="$config['collect']['art']['status'] == 1")checked @endif>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/hits_rnd') }}：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[art][hits_start]" placeholder="{{ __('admin.min_val') }}" value="{{ $config['collect']['art']['hits_start'] }}" class="layui-input">
                    </div>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[art][hits_end]" placeholder="{{ __('admin.max_val') }}" value="{{ $config['collect']['art']['hits_end'] }}" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/updown_rnd') }}：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[art][updown_start]" placeholder="{{ __('admin.min_val') }}" value="{{ $config['collect']['art']['updown_start'] }}" class="layui-input">
                    </div>
                    <div class="layui-input-inline">
                        <input type="text" name="collect[art][updown_end]" placeholder="{{ __('admin.max_val') }}" value="{{ $config['collect']['art']['updown_end'] }}" class="layui-input">
                    </div>
                </div>


                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/score_rnd') }}：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="collect[art][score]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['art']['score'] != 1")checked @endif>
                        <input type="radio" name="collect[art][score]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['art']['score'] == 1")checked @endif>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/sync_pic') }}：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="collect[art][pic]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['art']['pic'] != 1")checked @endif>
                        <input type="radio" name="collect[art][pic]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['art']['pic'] == 1")checked @endif>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/auto_tag') }}：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="collect[art][tag]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['art']['tag'] != 1")checked @endif>
                        <input type="radio" name="collect[art][tag]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['art']['tag'] == 1")checked @endif>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/psernd') }}：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="collect[art][psernd]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['art']['psernd'] != 1")checked @endif>
                        <input type="radio" name="collect[art][psernd]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['art']['psernd'] == 1")checked @endif>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/psesyn') }}：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="collect[art][psesyn]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['art']['psesyn'] != 1")checked @endif>
                        <input type="radio" name="collect[art][psesyn]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['art']['psesyn'] == 1")checked @endif>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/inrule') }}：</label>
                    <div class="layui-input-block">
                        <input type="checkbox" lay-skin="primary" name="collect[art][inrule][]" value="a" title="{{ __('admin.name') }}" checked disabled>
                        <input type="checkbox" lay-skin="primary" name="collect[art][inrule][]" value="b" title="{{ __('admin.type') }}" @if(condition="strpos($config['collect']['art']['inrule'],'b') !==false")checked @endif>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/uprule') }}：</label>
                    <div class="layui-input-block">
                        <input type="checkbox" lay-skin="primary" name="collect[art][uprule][]" value="a" title="{{ __('admin.content') }}" @if(condition="strpos($config['collect']['art']['uprule'],'a') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[art][uprule][]" value="b" title="{{ __('admin.author') }}" @if(condition="strpos($config['collect']['art']['uprule'],'b') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[art][uprule][]" value="c" title="{{ __('admin.from') }}" @if(condition="strpos($config['collect']['art']['uprule'],'c') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[art][uprule][]" value="d" title="{{ __('admin.pic') }}" @if(condition="strpos($config['collect']['art']['uprule'],'d') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[art][uprule][]" value="e" title="TAG" @if(condition="strpos($config['collect']['art']['uprule'],'e') !==false")checked @endif>
                        <input type="checkbox" lay-skin="primary" name="collect[art][uprule][]" value="f" title="{{ __('admin.blurb') }}" @if(condition="strpos($config['collect']['art']['uprule'],'f') !==false")checked @endif>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/filter') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[art][filter]" class="layui-textarea" placeholder="{{ __('admin.multi_separate_tip') }}">{{ $config['collect']['art']['filter'] }}</textarea>
                    </div>
                </div>

            </div>

                <div class="layui-tab-item">

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/status') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[actor][status]" value="0" title="{{ __('admin.reviewed_not') }}" @if(condition="$config['collect']['actor']['status'] != 1")checked @endif>
                            <input type="radio" name="collect[actor][status]" value="1" title="{{ __('admin.reviewed') }}" @if(condition="$config['collect']['actor']['status'] == 1")checked @endif>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/hits_rnd') }}：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[actor][hits_start]" placeholder="{{ __('admin.min_val') }}" value="{{ $config['collect']['actor']['hits_start'] }}" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[actor][hits_end]" placeholder="{{ __('admin.max_val') }}" value="{{ $config['collect']['actor']['hits_end'] }}" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/updown_rnd') }}：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[actor][updown_start]" placeholder="{{ __('admin.min_val') }}" value="{{ $config['collect']['actor']['updown_start'] }}" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[actor][updown_end]" placeholder="{{ __('admin.max_val') }}" value="{{ $config['collect']['actor']['updown_end'] }}" class="layui-input">
                        </div>
                    </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/score_rnd') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[actor][score]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['actor']['score'] != 1")checked @endif>
                            <input type="radio" name="collect[actor][score]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['actor']['score'] == 1")checked @endif>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/sync_pic') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[actor][pic]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['actor']['pic'] != 1")checked @endif>
                            <input type="radio" name="collect[actor][pic]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['actor']['pic'] == 1")checked @endif>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/psernd') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[actor][psernd]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['actor']['psernd'] != 1")checked @endif>
                            <input type="radio" name="collect[actor][psernd]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['actor']['psernd'] == 1")checked @endif>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/psesyn') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[actor][psesyn]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['actor']['psesyn'] != 1")checked @endif>
                            <input type="radio" name="collect[actor][psesyn]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['actor']['psesyn'] == 1")checked @endif>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/inrule') }}：</label>
                        <div class="layui-input-block">
                            <input type="checkbox" lay-skin="primary" name="collect[actor][inrule][]" value="a" title="{{ __('admin.actor_name') }}" checked disabled>
                            <input type="checkbox" lay-skin="primary" name="collect[actor][inrule][]" value="c" title="{{ __('admin.type') }}" @if(condition="strpos($config['collect']['actor']['inrule'],'c') !==false")checked @endif>
                            <input type="checkbox" lay-skin="primary" name="collect[actor][inrule][]" value="b" title="{{ __('admin.sex') }}" @if(condition="strpos($config['collect']['actor']['inrule'],'b') !==false")checked @endif>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/uprule') }}：</label>
                        <div class="layui-input-block">
                            <input type="checkbox" lay-skin="primary" name="collect[actor][uprule][]" value="a" title="{{ __('admin.content') }}" @if(condition="strpos($config['collect']['actor']['uprule'],'a') !==false")checked @endif>
                            <input type="checkbox" lay-skin="primary" name="collect[actor][uprule][]" value="b" title="{{ __('admin.blurb') }}" @if(condition="strpos($config['collect']['actor']['uprule'],'b') !==false")checked @endif>
                            <input type="checkbox" lay-skin="primary" name="collect[actor][uprule][]" value="c" title="{{ __('admin.remarks') }}" @if(condition="strpos($config['collect']['actor']['uprule'],'c') !==false")checked @endif>
                            <input type="checkbox" lay-skin="primary" name="collect[actor][uprule][]" value="d" title="{{ __('admin.works') }}" @if(condition="strpos($config['collect']['actor']['uprule'],'d') !==false")checked @endif>
                            <input type="checkbox" lay-skin="primary" name="collect[actor][uprule][]" value="e" title="{{ __('admin.pic') }}" @if(condition="strpos($config['collect']['actor']['uprule'],'e') !==false")checked @endif>

                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/filter') }}：</label>
                        <div class="layui-input-block">
                            <textarea name="collect[actor][filter]" class="layui-textarea" placeholder="{{ __('admin.multi_separate_tip') }}">{{ $config['collect']['actor']['filter'] }}</textarea>
                        </div>
                    </div>

                </div>

                <div class="layui-tab-item">

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/status') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[role][status]" value="0" title="{{ __('admin.reviewed_not') }}" @if(condition="$config['collect']['role']['status'] != 1")checked @endif>
                            <input type="radio" name="collect[role][status]" value="1" title="{{ __('admin.reviewed') }}" @if(condition="$config['collect']['role']['status'] == 1")checked @endif>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/hits_rnd') }}：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[role][hits_start]" placeholder="{{ __('admin.min_val') }}" value="{{ $config['collect']['role']['hits_start'] }}" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[role][hits_end]" placeholder="{{ __('admin.max_val') }}" value="{{ $config['collect']['role']['hits_end'] }}" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/updown_rnd') }}：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[role][updown_start]" placeholder="{{ __('admin.min_val') }}" value="{{ $config['collect']['role']['updown_start'] }}" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[role][updown_end]" placeholder="{{ __('admin.max_val') }}" value="{{ $config['collect']['role']['updown_end'] }}" class="layui-input">
                        </div>
                    </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/score_rnd') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[role][score]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['role']['score'] != 1")checked @endif>
                            <input type="radio" name="collect[role][score]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['role']['score'] == 1")checked @endif>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/sync_pic') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[role][pic]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['role']['pic'] != 1")checked @endif>
                            <input type="radio" name="collect[role][pic]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['role']['pic'] == 1")checked @endif>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/psernd') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[role][psernd]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['actor']['psernd'] != 1")checked @endif>
                            <input type="radio" name="collect[role][psernd]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['actor']['psernd'] == 1")checked @endif>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/psesyn') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[role][psesyn]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['role']['psesyn'] != 1")checked @endif>
                            <input type="radio" name="collect[role][psesyn]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['role']['psesyn'] == 1")checked @endif>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/inrule') }}：</label>
                        <div class="layui-input-block">
                            <input type="checkbox" lay-skin="primary" name="collect[role][inrule][]" value="a" title="{{ __('admin.role_name') }}" checked disabled>
                            <input type="checkbox" lay-skin="primary" name="collect[role][inrule][]" value="b" title="{{ __('admin.vod_name') }}{{ __('admin.or') }}{{ __('admin.douban_id') }}" checked disabled>
                            <input type="checkbox" lay-skin="primary" name="collect[role][inrule][]" value="c" title="{{ __('admin.actor_name') }}" @if(condition="strpos($config['collect']['role']['inrule'],'c') !==false")checked @endif >
                            <input type="checkbox" lay-skin="primary" name="collect[role][inrule][]" value="d" title="{{ __('admin.director') }}" @if(condition="strpos($config['collect']['role']['inrule'],'d') !==false")checked @endif >
                        </div>
                        <div class="layui-form-mid layui-word-aux">{{ __('admin.admin/system/configcollect/inrule_tip_role') }}</div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/uprule') }}：</label>
                        <div class="layui-input-block">
                            <input type="checkbox" lay-skin="primary" name="collect[role][uprule][]" value="a" title="{{ __('admin.content') }}" @if(condition="strpos($config['collect']['role']['uprule'],'a') !==false")checked @endif>
                            <input type="checkbox" lay-skin="primary" name="collect[role][uprule][]" value="b" title="{{ __('admin.remarks') }}" @if(condition="strpos($config['collect']['role']['uprule'],'b') !==false")checked @endif>
                            <input type="checkbox" lay-skin="primary" name="collect[role][uprule][]" value="c" title="{{ __('admin.pic') }}" @if(condition="strpos($config['collect']['role']['uprule'],'c') !==false")checked @endif>

                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/filter') }}：</label>
                        <div class="layui-input-block">
                            <textarea name="collect[role][filter]" class="layui-textarea" placeholder="{{ __('admin.multi_separate_tip') }}">{{ $config['collect']['role']['filter'] }}</textarea>
                        </div>
                    </div>

                </div>

                <div class="layui-tab-item">

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/status') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[website][status]" value="0" title="{{ __('admin.reviewed_not') }}" @if(condition="$config['collect']['website']['status'] != 1")checked @endif>
                            <input type="radio" name="collect[website][status]" value="1" title="{{ __('admin.reviewed') }}" @if(condition="$config['collect']['website']['status'] == 1")checked @endif>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/hits_rnd') }}：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[website][hits_start]" placeholder="{{ __('admin.min_val') }}" value="{{ $config['collect']['website']['hits_start'] }}" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[website][hits_end]" placeholder="{{ __('admin.max_val') }}" value="{{ $config['collect']['website']['hits_end'] }}" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/updown_rnd') }}：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[website][updown_start]" placeholder="{{ __('admin.min_val') }}" value="{{ $config['collect']['website']['updown_start'] }}" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[website][updown_end]" placeholder="{{ __('admin.max_val') }}" value="{{ $config['collect']['website']['updown_end'] }}" class="layui-input">
                        </div>
                    </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/score_rnd') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[website][score]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['website']['score'] != 1")checked @endif>
                            <input type="radio" name="collect[website][score]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['website']['score'] == 1")checked @endif>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/sync_pic') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[website][pic]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['website']['pic'] != 1")checked @endif>
                            <input type="radio" name="collect[website][pic]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['website']['pic'] == 1")checked @endif>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/psernd') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[website][psernd]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['website']['psernd'] != 1")checked @endif>
                            <input type="radio" name="collect[website][psernd]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['website']['psernd'] == 1")checked @endif>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/psesyn') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[website][psesyn]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['website']['psesyn'] != 1")checked @endif>
                            <input type="radio" name="collect[website][psesyn]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['website']['psesyn'] == 1")checked @endif>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/inrule') }}：</label>
                        <div class="layui-input-block">
                            <input type="checkbox" lay-skin="primary" name="collect[website][inrule][]" value="a" title="{{ __('admin.name') }}" checked disabled>
                            <input type="checkbox" lay-skin="primary" name="collect[website][inrule][]" value="b" title="{{ __('admin.type') }}" @if(condition="strpos($config['collect']['website']['inrule'],'b') !==false")checked @endif>
                            <input type="checkbox" lay-skin="primary" name="collect[website][inrule][]" value="c" title="{{ __('admin.jumpurl') }}" @if(condition="strpos($config['collect']['website']['inrule'],'c') !==false")checked @endif>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/uprule') }}：</label>
                        <div class="layui-input-block">
                            <input type="checkbox" lay-skin="primary" name="collect[website][uprule][]" value="a" title="{{ __('admin.content') }}" @if(condition="strpos($config['collect']['website']['uprule'],'a') !==false")checked @endif>
                            <input type="checkbox" lay-skin="primary" name="collect[website][uprule][]" value="b" title="{{ __('admin.blurb') }}" @if(condition="strpos($config['collect']['website']['uprule'],'b') !==false")checked @endif>
                            <input type="checkbox" lay-skin="primary" name="collect[website][uprule][]" value="c" title="{{ __('admin.remarks') }}" @if(condition="strpos($config['collect']['website']['uprule'],'c') !==false")checked @endif>
                            <input type="checkbox" lay-skin="primary" name="collect[website][uprule][]" value="d" title="{{ __('admin.jumpurl') }}" @if(condition="strpos($config['collect']['website']['uprule'],'d') !==false")checked @endif>
                            <input type="checkbox" lay-skin="primary" name="collect[website][uprule][]" value="e" title="{{ __('admin.pic') }}" @if(condition="strpos($config['collect']['website']['uprule'],'e') !==false")checked @endif>

                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/filter') }}：</label>
                        <div class="layui-input-block">
                            <textarea name="collect[website][filter]" class="layui-textarea" placeholder="{{ __('admin.multi_separate_tip') }}">{{ $config['collect']['website']['filter'] }}</textarea>
                        </div>
                    </div>

                </div>

                <div class="layui-tab-item">

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/status') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[comment][status]" value="0" title="{{ __('admin.reviewed_not') }}" @if(condition="$config['collect']['comment']['status'] != 1")checked @endif>
                            <input type="radio" name="collect[comment][status]" value="1" title="{{ __('admin.reviewed') }}" @if(condition="$config['collect']['comment']['status'] == 1")checked @endif>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/updown_rnd') }}：</label>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[comment][updown_start]" placeholder="{{ __('admin.min_val') }}" value="{{ $config['collect']['comment']['updown_start'] }}" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" name="collect[comment][updown_end]" placeholder="{{ __('admin.max_val') }}" value="{{ $config['collect']['comment']['updown_end'] }}" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/psernd') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[comment][psernd]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['actor']['psernd'] != 1")checked @endif>
                            <input type="radio" name="collect[comment][psernd]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['actor']['psernd'] == 1")checked @endif>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/psesyn') }}：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="collect[comment][psesyn]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['collect']['comment']['psesyn'] != 1")checked @endif>
                            <input type="radio" name="collect[comment][psesyn]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['collect']['comment']['psesyn'] == 1")checked @endif>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/inrule') }}：</label>
                        <div class="layui-input-block">
                            <input type="checkbox" lay-skin="primary" name="collect[comment][inrule][]" value="a" title="{{ __('admin.rel_name') }}{{ __('admin.or') }}{{ __('admin.douban_id') }}" checked disabled>
                            <input type="checkbox" lay-skin="primary" name="collect[comment][inrule][]" value="b" title="{{ __('admin.comment_content') }}" @if(condition="strpos($config['collect']['comment']['inrule'],'b') !==false")checked @endif>
                            <input type="checkbox" lay-skin="primary" name="collect[comment][inrule][]" value="c" title="{{ __('admin.comment_name') }}" @if(condition="strpos($config['collect']['comment']['inrule'],'c') !==false")checked @endif >
                        </div>
                        <div class="layui-form-mid layui-word-aux">{{ __('admin.admin/system/configcollect/inrule_tip_comment') }}</div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/uprule') }}：</label>
                        <div class="layui-input-block">


                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/filter') }}：</label>
                        <div class="layui-input-block">
                            <textarea name="collect[comment][filter]" class="layui-textarea" placeholder="{{ __('admin.multi_separate_tip') }}">{{ $config['collect']['comment']['filter'] }}</textarea>
                        </div>
                    </div>

                </div>

                <div class="layui-tab-item">
                <blockquote class="layui-elem-quote layui-quote-nm">
                    {{ __('admin.admin/system/configcollect/words_tip') }}
                </blockquote>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/vod_namewords') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[vod][namewords]" class="layui-textarea">{{ $config['collect']['vod']['namewords']|mac_replace_text }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/vod_thesaurus') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[vod][thesaurus]" class="layui-textarea">{{ $config['collect']['vod']['thesaurus']|mac_replace_text }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/vod_playerwords') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[vod][playerwords]" class="layui-textarea">{{ $config['collect']['vod']['playerwords']|mac_replace_text }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/vod_areawords') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[vod][areawords]" class="layui-textarea">{{ $config['collect']['vod']['areawords']|mac_replace_text }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/vod_langwords') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[vod][langwords]" class="layui-textarea">{{ $config['collect']['vod']['langwords']|mac_replace_text }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/vod_words') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[vod][words]" class="layui-textarea">{{ $config['collect']['vod']['words']|mac_replace_text }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/art_thesaurus') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[art][thesaurus]" class="layui-textarea">{{ $config['collect']['art']['thesaurus']|mac_replace_text }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/art_words') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[art][words]" class="layui-textarea">{{ $config['collect']['art']['words']|mac_replace_text }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/actor_thesaurus') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[actor][thesaurus]" class="layui-textarea">{{ $config['collect']['actor']['thesaurus']|mac_replace_text }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/actor_words') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[actor][words]" class="layui-textarea">{{ $config['collect']['actor']['words']|mac_replace_text }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/role_thesaurus') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[role][thesaurus]" class="layui-textarea">{{ $config['collect']['role']['thesaurus']|mac_replace_text }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/role_words') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[role][words]" class="layui-textarea">{{ $config['collect']['role']['words']|mac_replace_text }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/website_thesaurus') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[website][thesaurus]" class="layui-textarea">{{ $config['collect']['website']['thesaurus']|mac_replace_text }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configcollect/website_words') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="collect[website][words]" class="layui-textarea">{{ $config['collect']['website']['words']|mac_replace_text }}</textarea>
                    </div>
                </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/comment_thesaurus') }}：</label>
                        <div class="layui-input-block">
                            <textarea name="collect[comment][thesaurus]" class="layui-textarea">{{ $config['collect']['comment']['thesaurus']|mac_replace_text }}</textarea>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin.admin/system/configcollect/comment_words') }}：</label>
                        <div class="layui-input-block">
                            <textarea name="collect[comment][words]" class="layui-textarea">{{ $config['collect']['comment']['words']|mac_replace_text }}</textarea>
                        </div>
                    </div>

            </div>
            </div>
        </div>
        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit">{{ __('admin.btn_save') }}</button>
                <button class="layui-btn layui-btn-warm" type="reset">{{ __('admin.btn_reset') }}</button>
            </div>
        </div>
    </form>
</div>

@include('../../../application/admin/view/public/foot')
<script type="text/javascript" src="{{ asset('static') }}/js/jquery.cookie.js"></script>
<script type="text/javascript">
    layui.use(['element', 'form', 'layer'], function() {
        var element = layui.element
            ,form = layui.form
            , layer = layui.layer;


        element.on('tab(tb1)', function(){
            $.cookie('configcollect_tab', this.getAttribute('lay-id'));
        });

        if( $.cookie('configcollect_tab') !=null ) {
            element.tabChange('tb1', $.cookie('configcollect_tab'));
        }

    });
</script>

</body>
</html>