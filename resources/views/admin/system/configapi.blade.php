@include('../../../application/admin/view/public/head')

<div class="page-container">
        <form class="layui-form layui-form-pane" action="">
            <input type="hidden" name="__token__" value="{{ $Request.token }}" />
            <div class="layui-tab" lay-filter="tb1">
                <ul class="layui-tab-title">
                    <li class="layui-this" lay-id="configapi_1">{{ __('admin.admin/system/configapi/vod') }}</li>
                    <li lay-id="configapi_2">{{ __('admin.admin/system/configapi/art') }}</li>
                    <li lay-id="configapi_3">{{ __('admin.admin/system/configapi/actor') }}</li>
                    <li lay-id="configapi_4">{{ __('admin.admin/system/configapi/role') }}</li>
                    <li lay-id="configapi_5">{{ __('admin.admin/system/configapi/website') }}</li>
                </ul>
                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show">

                        <blockquote class="layui-elem-quote layui-quote-nm">
                            {{ __('admin.admin/system/configapi/vod_tip') }}
                        </blockquote>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configapi/status') }}：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="api[vod][status]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['api']['vod']['status'] != 1")checked @endif>
                        <input type="radio" name="api[vod][status]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['api']['vod']['status'] == 1")checked @endif>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configapi/charge') }}：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="api[vod][charge]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['api']['vod']['charge'] != 1")checked @endif>
                        <input type="radio" name="api[vod][charge]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['api']['vod']['charge'] == 1")checked @endif>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configapi/detail_inc_hits') }}：</label>
                    <div class="layui-input-inline w200">
                        <input type="radio" name="api[vod][detail_inc_hits]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['api']['vod']['detail_inc_hits'] != 1")checked @endif>
                        <input type="radio" name="api[vod][detail_inc_hits]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['api']['vod']['detail_inc_hits'] == 1")checked @endif>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin.admin/system/configapi/detail_inc_hits_tip') }}</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">
                        {{ __('admin.admin/system/configapi/pagesize') }}：</label>
                    <div class="layui-input-block">
                        <input type="text" name="api[vod][pagesize]" placeholder="{{ __('admin.admin/system/configapi/pagesize_tip') }}" value="{{ $config['api']['vod']['pagesize'] }}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">
                        {{ __('admin.admin/system/configapi/imgurl') }}：</label>
                    <div class="layui-input-block">
                        <input type="text" name="api[vod][imgurl]" placeholder="{{ __('admin.admin/system/configapi/imgurl_tip') }}" value="{{ $config['api']['vod']['imgurl'] }}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">
                        {{ __('admin.admin/system/configapi/typefilter') }}：</label>
                    <div class="layui-input-block">
                        <input type="text" name="api[vod][typefilter]" placeholder="{{ __('admin.admin/system/configapi/typefilter_tip') }}" value="{{ $config['api']['vod']['typefilter'] }}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">
                        {{ __('admin.admin/system/configapi/datafilter') }}：</label>
                    <div class="layui-input-block">
                        <input type="text" name="api[vod][datafilter]" placeholder="{{ __('admin.admin/system/configapi/datafilter_tip') }}" value="{{ $config['api']['vod']['datafilter'] }}" class="layui-input">
                    </div>
                </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/cachetime') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[vod][cachetime]" placeholder="{{ __('admin.admin/system/configapi/cachetime_tip') }}" value="{{ $config['api']['vod']['cachetime'] }}" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/from') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[vod][from]" placeholder="{{ __('admin.admin/system/configapi/from_tip') }}" value="{{ $config['api']['vod']['from'] }}" class="layui-input">
                            </div>
                        </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin.admin/system/configapi/auth') }}：</label>
                    <div class="layui-input-block">
                        <textarea name="api[vod][auth]" class="layui-textarea">{{ $config['api']['vod']['auth']|mac_replace_text }}</textarea>
                    </div>
                </div>

            </div>

                    <div class="layui-tab-item">

                        <blockquote class="layui-elem-quote layui-quote-nm">
                            {{ __('admin.admin/system/configapi/art_tip') }}
                        </blockquote>


                        <div class="layui-form-item">
                            <label class="layui-form-label">{{ __('admin.admin/system/configapi/status') }}：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="api[art][status]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['api']['art']['status'] != 1")checked @endif>
                                <input type="radio" name="api[art][status]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['api']['art']['status'] == 1")checked @endif>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">{{ __('admin.admin/system/configapi/charge') }}：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="api[art][charge]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['api']['art']['charge'] != 1")checked @endif>
                                <input type="radio" name="api[art][charge]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['api']['art']['charge'] == 1")checked @endif>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/pagesize') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[art][pagesize]" placeholder="{{ __('admin.admin/system/configapi/pagesize_tip') }}" value="{{ $config['api']['art']['pagesize'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/imgurl') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[art][imgurl]" placeholder="{{ __('admin.admin/system/configapi/imgurl_tip') }}" value="{{ $config['api']['art']['imgurl'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/typefilter') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[art][typefilter]" placeholder="{{ __('admin.admin/system/configapi/typefilter_tip') }}" value="{{ $config['api']['art']['typefilter'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/datafilter') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[art][datafilter]" placeholder="{{ __('admin.admin/system/configapi/datafilter_tip_art') }}" value="{{ $config['api']['art']['datafilter'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/cachetime') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[art][cachetime]" placeholder="{{ __('admin.admin/system/configapi/cachetime_tip') }}" value="{{ $config['api']['art']['cachetime'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">{{ __('admin.admin/system/configapi/auth') }}：</label>
                            <div class="layui-input-block">
                                <textarea name="api[art][auth]" class="layui-textarea">{{ $config['api']['art']['auth']|mac_replace_text }}</textarea>
                            </div>
                        </div>

                    </div>

                    <div class="layui-tab-item">

                        <blockquote class="layui-elem-quote layui-quote-nm">
                            {{ __('admin.admin/system/configapi/actor_tip') }}
                        </blockquote>


                        <div class="layui-form-item">
                            <label class="layui-form-label">{{ __('admin.admin/system/configapi/status') }}：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="api[actor][status]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['api']['actor']['status'] != 1")checked @endif>
                                <input type="radio" name="api[actor][status]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['api']['actor']['status'] == 1")checked @endif>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">{{ __('admin.admin/system/configapi/charge') }}：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="api[actor][charge]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['api']['actor']['charge'] != 1")checked @endif>
                                <input type="radio" name="api[actor][charge]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['api']['actor']['charge'] == 1")checked @endif>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/pagesize') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[actor][pagesize]" placeholder="{{ __('admin.admin/system/configapi/pagesize_tip') }}" value="{{ $config['api']['actor']['pagesize'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/imgurl') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[actor][imgurl]" placeholder="{{ __('admin.admin/system/configapi/imgurl_tip') }}" value="{{ $config['api']['actor']['imgurl'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/typefilter') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[actor][typefilter]" placeholder="{{ __('admin.admin/system/configapi/typefilter_tip') }}" value="{{ $config['api']['actor']['typefilter'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/datafilter') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[actor][datafilter]" placeholder="{{ __('admin.admin/system/configapi/datafilter_tip_actor') }}" value="{{ $config['api']['actor']['datafilter'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/cachetime') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[actor][cachetime]" placeholder="{{ __('admin.admin/system/configapi/cachetime_tip') }}" value="{{ $config['api']['actor']['cachetime'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">{{ __('admin.admin/system/configapi/auth') }}：</label>
                            <div class="layui-input-block">
                                <textarea name="api[actor][auth]" class="layui-textarea">{{ $config['api']['actor']['auth']|mac_replace_text }}</textarea>
                            </div>
                        </div>

                    </div>

                    <div class="layui-tab-item">

                        <blockquote class="layui-elem-quote layui-quote-nm">
                            {{ __('admin.admin/system/configapi/role_tip') }}
                        </blockquote>


                        <div class="layui-form-item">
                            <label class="layui-form-label">{{ __('admin.admin/system/configapi/status') }}：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="api[role][status]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['api']['role']['status'] != 1")checked @endif>
                                <input type="radio" name="api[role][status]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['api']['role']['status'] == 1")checked @endif>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">{{ __('admin.admin/system/configapi/charge') }}：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="api[role][charge]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['api']['role']['charge'] != 1")checked @endif>
                                <input type="radio" name="api[role][charge]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['api']['role']['charge'] == 1")checked @endif>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/pagesize') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[role][pagesize]" placeholder="{{ __('admin.admin/system/configapi/pagesize_tip') }}" value="{{ $config['api']['role']['pagesize'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/imgurl') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[role][imgurl]" placeholder="{{ __('admin.admin/system/configapi/imgurl_tip') }}" value="{{ $config['api']['role']['imgurl'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/typefilter') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[role][typefilter]" placeholder="{{ __('admin.admin/system/configapi/typefilter_tip') }}" value="{{ $config['api']['role']['typefilter'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/datafilter') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[role][datafilter]" placeholder="{{ __('admin.admin/system/configapi/datafilter_tip_role') }}" value="{{ $config['api']['role']['datafilter'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/cachetime') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[role][cachetime]" placeholder="{{ __('admin.admin/system/configapi/cachetime_tip') }}" value="{{ $config['api']['role']['cachetime'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">{{ __('admin.admin/system/configapi/auth') }}：</label>
                            <div class="layui-input-block">
                                <textarea name="api[role][auth]" class="layui-textarea">{{ $config['api']['role']['auth']|mac_replace_text }}</textarea>
                            </div>
                        </div>

                    </div>

                    <div class="layui-tab-item">

                        <blockquote class="layui-elem-quote layui-quote-nm">
                            {{ __('admin.admin/system/configapi/website_tip') }}
                        </blockquote>


                        <div class="layui-form-item">
                            <label class="layui-form-label">{{ __('admin.admin/system/configapi/status') }}：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="api[website][status]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['api']['website']['status'] != 1")checked @endif>
                                <input type="radio" name="api[website][status]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['api']['website']['status'] == 1")checked @endif>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">{{ __('admin.admin/system/configapi/charge') }}：</label>
                            <div class="layui-input-block">
                                <input type="radio" name="api[website][charge]" value="0" title="{{ __('admin.close') }}" @if(condition="$config['api']['website']['charge'] != 1")checked @endif>
                                <input type="radio" name="api[website][charge]" value="1" title="{{ __('admin.open') }}" @if(condition="$config['api']['website']['charge'] == 1")checked @endif>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/pagesize') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[website][pagesize]" placeholder="{{ __('admin.admin/system/configapi/pagesize_tip') }}" value="{{ $config['api']['website']['pagesize'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/imgurl') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[website][imgurl]" placeholder="{{ __('admin.admin/system/configapi/imgurl_tip') }}" value="{{ $config['api']['website']['imgurl'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/typefilter') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[website][typefilter]" placeholder="{{ __('admin.admin/system/configapi/typefilter_tip') }}" value="{{ $config['api']['website']['typefilter'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/datafilter') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[website][datafilter]" placeholder="{{ __('admin.admin/system/configapi/datafilter_tip_website') }}" value="{{ $config['api']['website']['datafilter'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ __('admin.admin/system/configapi/cachetime') }}：</label>
                            <div class="layui-input-block">
                                <input type="text" name="api[website][cachetime]" placeholder="{{ __('admin.admin/system/configapi/cachetime_tip') }}" value="{{ $config['api']['website']['cachetime'] }}" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">{{ __('admin.admin/system/configapi/auth') }}：</label>
                            <div class="layui-input-block">
                                <textarea name="api[website][auth]" class="layui-textarea">{{ $config['api']['website']['auth']|mac_replace_text }}</textarea>
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
            $.cookie('configapi_tab', this.getAttribute('lay-id'));
        });

        if( $.cookie('configapi_tab') !=null ) {
            element.tabChange('tb1', $.cookie('configapi_tab'));
        }

    });
</script>

</body>
</html>