@include('admin.public.head')

<div class="page-container">

    <div class="showpic" style="display:none;"><img class="showpic_img" width="120" height="160" referrerPolicy="no-referrer"></div>

    <form class="layui-form layui-form-pane" action="{{ route('admin.system.config') }}" method="post">
        @csrf
        @php
            $cfg = $config ?? [];
        @endphp

        <div class="layui-tab" lay-filter="tb1">
            <ul class="layui-tab-title">
                <li class="layui-this" lay-id="config_1">{{ __('admin/system/config/base') }}</li>
                <li lay-id="config_2">{{ __('admin/system/config/performance') }}</li>
                <li lay-id="config_3">{{ __('admin/system/config/parameters') }}</li>
                <li lay-id="config_4">{{ __('admin/system/config/backstage') }}</li>
            </ul>
            <div class="layui-tab-content">

                <div class="layui-tab-item layui-show">
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/site_name') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="site[site_name]" placeholder="" value="{{ $config['site']['site_name'] ?? '' }}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/site_url') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="site[site_url]" placeholder="{{ __('admin/system/config/site_url_tip') }}" value="{{ $config['site']['site_url'] ?? '' }}" class="layui-input">
                    </div>
                </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/site_wapurl') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="site[site_wapurl]" placeholder="{{ __('admin/system/config/site_wapurl_tip') }}" value="{{ $config['site']['site_wapurl'] ?? '' }}" class="layui-input">
                        </div>
                    </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/site_keywords') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="site[site_keywords]" placeholder="" value="{{ $config['site']['site_keywords'] ?? '' }}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/site_description') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="site[site_description]" placeholder="" value="{{ $config['site']['site_description'] ?? '' }}" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/site_icp') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="site[site_icp]" placeholder="" value="{{ $config['site']['site_icp'] ?? '' }}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/site_qq') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="site[site_qq]" placeholder="" value="{{ $config['site']['site_qq'] ?? '' }}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/site_email') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="site[site_email]" placeholder="" value="{{ $config['site']['site_email'] ?? '' }}" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/install_dir') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="site[install_dir]" placeholder="{{ __('admin/system/config/install_dir_tip') }}" value="{{ $config['site']['install_dir'] ?? '' }}" class="layui-input">
                    </div>
                </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/site_logo') }}</label>
                        <div class="layui-input-inline w600">
                            <input type="text" name="site[site_logo]" placeholder="{{ __('admin/system/config/site_logo_tip') }}" value="{{ $config['site']['site_logo'] ?? '' }}" class="layui-input upload-input">
                        </div>
                        <div class="layui-input-inline ">
                            <button type="button" class="layui-btn layui-upload" lay-data="{data:{thumb:0,thumb_class:'site[site_logo]'}}" id="upload1">{{ __('admin.upload_pic') }}</button>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/site_waplogo') }}</label>
                        <div class="layui-input-inline w600">
                            <input type="text" name="site[site_waplogo]" placeholder="{{ __('admin/system/config/site_logo_tip') }}" value="{{ $config['site']['site_waplogo'] ?? '' }}" class="layui-input upload-input">
                        </div>
                        <div class="layui-input-inline ">
                            <button type="button" class="layui-btn layui-upload" lay-data="{data:{thumb:0,thumb_class:'upload-thumb'}}" id="upload2">{{ __('admin.upload_pic') }}</button>
                        </div>
                    </div>


                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/template_dir') }}</label>
                    <div class="layui-input-inline" >
                            <select class="w150" name="site[template_dir]">
                                @foreach($templates as $vo)
                                <option value="{{ $vo }}" @selected((($config['site']['template_dir'] ?? '') == $vo))>{{ $vo }}</option>
                                @endforeach
                            </select>
                    </div>
                    <label class="layui-form-label">{{ __('admin/system/config/html_dir') }}</label>
                    <div class="layui-input-inline">
                        <input type="text" name="site[html_dir]" placeholder="" value="{{ $config['site']['html_dir'] ?? '' }}" class="layui-input w150" >
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/site_polyfill') }}</label>
                    <div class="layui-input-inline w300">
                        <input type="radio" name="site[site_polyfill]" value="0" title="{{ __('admin.close') }}" @checked((($config['site']['site_polyfill'] ?? 1) == 0))>
                        <input type="radio" name="site[site_polyfill]" value="1" title="{{ __('admin.open') }}" @checked((!isset($config['site']['site_polyfill']) || ($config['site']['site_polyfill'] ?? 1) == 1))>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/site_polyfill_tip') }}</div>
                </div>
                
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/mob_status') }}</label>
                    <div class="layui-input-inline w600">
                        <input type="radio" name="site[mob_status]" value="0" title="{{ __('admin.close') }}" @checked((($config['site']['mob_status'] ?? 0) != 1))>
                        <input type="radio" name="site[mob_status]" value="1" title="{{ __('admin/system/config/mob_multiple') }}" @checked((($config['site']['mob_status'] ?? 0) == 1))>
                        <input type="radio" name="site[mob_status]" value="2" title="{{ __('admin/system/config/mob_one') }}" @checked((($config['site']['mob_status'] ?? 0) == 2))>
                        <span class="layui-form-mid layui-word-aux">{{ __('admin/system/config/mob_status_tip') }}</span>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/mob_template_dir') }}</label>
                    <div class="layui-input-inline">
                            <select class="w150" name="site[mob_template_dir]">
                                @foreach($templates as $vo)
                                <option value="{{ $vo }}" @selected((($config['site']['mob_template_dir'] ?? '') == $vo))>{{ $vo }}</option>
                                @endforeach
                            </select>
                    </div>
                    <label class="layui-form-label">{{ __('admin/system/config/html_dir') }}</label>
                    <div class="layui-input-inline">
                        <input type="text" name="site[mob_html_dir]" placeholder="" value="{{ $config['site']['mob_html_dir'] ?? '' }}" class="layui-input w150" >
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/site_tj') }}</label>
                    <div class="layui-input-block">
                        <textarea name="site[site_tj]" class="layui-textarea"  placeholder="">{{ $config['site']['site_tj'] ?? '' }}</textarea>
                    </div>
                </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/site_status') }}</label>
                        <div class="layui-input-block">
                            <input type="radio" name="site[site_status]" value="0" title="{{ __('admin.close') }}" @checked((($config['site']['site_status'] ?? 0) == 0))>
                            <input type="radio" name="site[site_status]" value="1" title="{{ __('admin.open') }}" @checked((($config['site']['site_status'] ?? 0) == 1))>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/site_close_tip') }}</label>
                        <div class="layui-input-block">
                            <textarea name="site[site_close_tip]" class="layui-textarea"  placeholder="">{{ $config['site']['site_close_tip'] ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/mainland_ip_limit') }}</label>
                        <div class="layui-input-block">
                            <input type="radio" name="site[mainland_ip_limit]" value="0" title="{{ __('admin.close') }}" @checked((!isset($config['site']['mainland_ip_limit']) || ($config['site']['mainland_ip_limit'] ?? 0) == 0))>
                            <input type="radio" name="site[mainland_ip_limit]" value="1" title="{{ __('admin/system/config/mainland_ip_only_allow') }}" @checked((($config['site']['mainland_ip_limit'] ?? 0) == 1))>
                            <input type="radio" name="site[mainland_ip_limit]" value="2" title="{{ __('admin/system/config/mainland_ip_not_allow') }}" @checked((($config['site']['mainland_ip_limit'] ?? 0) == 2))>
                        </div>
                    </div>
            </div>

                <div class="layui-tab-item">
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/pathinfo_depr') }}</label>
                        <div class="layui-input-inline w150">
                            <select class="w150" name="app[pathinfo_depr]">
                                <option value="/" @selected((($config['app']['pathinfo_depr'] ?? '/') == '/'))>{{ __('admin/system/config/xg') }}</option>
                                <option value="-" @selected((($config['app']['pathinfo_depr'] ?? '/') == '-'))>{{ __('admin/system/config/zhx') }}</option>
                                <option value="_" @selected((($config['app']['pathinfo_depr'] ?? '/') == '_'))>{{ __('admin/system/config/xhx') }}</option>
                            </select>
                        </div>
                        <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/pathinfo_depr_tip') }}</div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/suffix') }}</label>
                        <div class="layui-input-inline">
                            <select style="width:150px;" name="app[suffix]">
                                <option value="html" @selected((($config['app']['suffix'] ?? 'html') == 'html'))>html</option>
                                <option value="htm" @selected((($config['app']['suffix'] ?? 'html') == 'htm'))>htm</option>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/popedom_filter') }}</label>
                        <div class="layui-input-inline">
                            <input type="radio" name="app[popedom_filter]" value="0" title="{{ __('admin.close') }}" @checked((($config['app']['popedom_filter'] ?? 0) != 1))>
                            <input type="radio" name="app[popedom_filter]" value="1" title="{{ __('admin.open') }}" @checked((($config['app']['popedom_filter'] ?? 0) == 1))>
                        </div>
                        <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/popedom_filter_tip') }}</div>
                    </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/cache_type') }}</label>
                        <div class="layui-input-block">
                            @php $cacheType = (string)($config['app']['cache_type'] ?? 'file'); @endphp
                            <input type="radio" name="app[cache_type]" lay-filter="cache_type" value="file" title="file" @checked(($cacheType === 'file' || $cacheType === '0'))>
                            <input type="radio" name="app[cache_type]" lay-filter="cache_type" value="memcache" title="memcache" @checked(($cacheType === 'memcache' || $cacheType === '1'))>
                            <input type="radio" name="app[cache_type]" lay-filter="cache_type" value="redis" title="redis" @checked(($cacheType === 'redis' || $cacheType === '2'))>
                            <input type="radio" name="app[cache_type]" lay-filter="cache_type" value="memcached" title="memcached" @checked(($cacheType === 'memcached' || $cacheType === '3'))>
                        </div>
                    </div>

                    <div class="layui-form-item row_cache_server " @if(($cacheType === '0' || $cacheType === 'file')) style="display:none;" @endif>
                        <label class="layui-form-label">{{ __('admin/system/config/cache_host') }}</label>
                        <div class="layui-input-inline w150">
                            <input type="text" name="app[cache_host]" placeholder="{{ __('admin/system/config/cache_host_tip') }}" value="{{ $config['app']['cache_host'] ?? '' }}" class="layui-input" >
                        </div>
                        <label class="layui-form-label">{{ __('admin/system/config/cache_port') }}</label>
                        <div class="layui-input-inline w150">
                            <input type="text" name="app[cache_port]" placeholder="{{ __('admin/system/config/cache_port_tip') }}" value="{{ $config['app']['cache_port'] ?? '' }}" class="layui-input" >
                        </div>
                        <label class="layui-form-label">{{ __('admin/system/config/cache_username') }}</label>
                        <div class="layui-input-inline">
                            <input type="text" name="app[cache_username]" placeholder="{{ __('admin/system/config/cache_username_tip') }}" value="{{ $config['app']['cache_username'] ?? '' }}" class="layui-input" >
                        </div>
                        <label class="layui-form-label">{{ __('admin/system/config/cache_password') }}</label>
                        <div class="layui-input-inline">
                            <input type="text" name="app[cache_password]" placeholder="{{ __('admin/system/config/cache_password_tip') }}" value="{{ $config['app']['cache_password'] ?? '' }}" class="layui-input" >
                        </div>
                        <label class="layui-form-label">DB</label>
                        <div class="layui-input-inline w150">
                            <input type="text" name="app[cache_db]" placeholder="Redis数据库索引(默认0)" value="{{ $config['app']['cache_db'] ?? '0' }}" class="layui-input" >
                        </div>
                        <button type="button" class="layui-btn layui-btn-normal" onclick="test_cache()">{{ __('admin/system/config/cache_test') }}</button>
                    </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/cache_flag') }}</label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[cache_flag]" placeholder="{{ __('admin/system/config/cache_flag_auto') }}" value="{{ $config['app']['cache_flag'] ?? '' }}" class="layui-input w150" >
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/cache_flag_tip') }}</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/cache_core') }}</label>
                    <div class="layui-input-block">
                        <input type="radio" name="app[cache_core]" value="0" title="{{ __('admin.close') }}" @checked((($config['app']['cache_core'] ?? 0) != 1))>
                        <input type="radio" name="app[cache_core]" value="1" title="{{ __('admin.open') }}" @checked((($config['app']['cache_core'] ?? 0) == 1))>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/cache_time') }}</label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[cache_time]" placeholder="" value="{{ $config['app']['cache_time'] ?? '' }}" class="layui-input w150" >
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/cache_time_tip') }}</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/cache_page') }}</label>
                    <div class="layui-input-block">
                        <input type="radio" name="app[cache_page]" value="0" title="{{ __('admin.close') }}" @checked((($config['app']['cache_page'] ?? 0) != 1))>
                        <input type="radio" name="app[cache_page]" value="1" title="{{ __('admin.open') }}" @checked((($config['app']['cache_page'] ?? 0) == 1))>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/cache_time_page') }}</label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[cache_time_page]" placeholder="" value="{{ $config['app']['cache_time_page'] ?? '' }}" class="layui-input w150" >
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/cache_time_tip') }}</div>
                </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/compress') }}</label>
                        <div class="layui-input-block">
                            <input type="radio" name="app[compress]" value="0" title="{{ __('admin.close') }}" @checked((($config['app']['compress'] ?? 0) != 1))>
                            <input type="radio" name="app[compress]" value="1" title="{{ __('admin.open') }}" @checked((($config['app']['compress'] ?? 0) == 1))>
                        </div>
                    </div>
                <div class="layui-form-item layui-hide">
                    <label class="layui-form-label">{{ __('admin/system/config/input_type') }}</label>
                    <div class="layui-input-inline w600">
                        <input type="radio" name="app[input_type]" value="1" title="{{ __('admin.get+post') }}" @checked((($config['app']['input_type'] ?? 1) != 0))>
                        <input type="radio" name="app[input_type]" value="0" title="{{ __('admin.get') }}" @checked((($config['app']['input_type'] ?? 1) == 0))>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/input_type_tip') }}</div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/ajax_page') }}</label>
                    <div class="layui-input-inline w600">
                        <input type="radio" name="app[ajax_page]" value="0" title="{{ __('admin.close') }}" @checked((($config['app']['ajax_page'] ?? 0) != 1))>
                        <input type="radio" name="app[ajax_page]" value="1" title="{{ __('admin.open') }}" @checked((($config['app']['ajax_page'] ?? 0) == 1))>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/ajax_page_tip') }}</div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/wall_filter') }}</label>
                    <div class="layui-input-inline w600">
                        @php $wf = (int)($config['app']['wall_filter'] ?? 0); @endphp
                        <input type="radio" name="app[wall_filter]" value="0" title="{{ __('admin.close') }}" @checked(($wf !== 1 && $wf !== 2))>
                        <input type="radio" name="app[wall_filter]" value="1" title="{{ __('admin/system/config/wall_unicode') }}" @checked(($wf === 1))>
                        <input type="radio" name="app[wall_filter]" value="2" title="{{ __('admin/system/config/wall_blank') }}" @checked(($wf === 2))>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/wall_filter_tip') }}</div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/show') }}</label>
                    <div class="layui-input-block">
                        <input type="radio" name="app[show]" value="0" title="{{ __('admin.close') }}" @checked((($config['app']['show'] ?? 0) != 1))>
                        <input type="radio" name="app[show]" value="1" title="{{ __('admin.open') }}" @checked((($config['app']['show'] ?? 0) == 1))>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/show_verify') }}</label>
                    <div class="layui-input-block">
                        <input type="radio" name="app[show_verify]" value="0" title="{{ __('admin.close') }}" @checked((($config['app']['show_verify'] ?? 0) != 1))>
                        <input type="radio" name="app[show_verify]" value="1" title="{{ __('admin.open') }}" @checked((($config['app']['show_verify'] ?? 0) == 1))>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/search') }}</label>
                    <div class="layui-input-block">
                        <input type="radio" name="app[search]" value="0" title="{{ __('admin.close') }}" @checked((($config['app']['search'] ?? 0) != 1))>
                        <input type="radio" name="app[search]" value="1" title="{{ __('admin.open') }}" @checked((($config['app']['search'] ?? 0) == 1))>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/search_verify') }}</label>
                    <div class="layui-input-block">
                        <input type="radio" name="app[search_verify]" value="0" title="{{ __('admin.close') }}" @checked((($config['app']['search_verify'] ?? 0) != 1))>
                        <input type="radio" name="app[search_verify]" value="1" title="{{ __('admin.open') }}" @checked((($config['app']['search_verify'] ?? 0) == 1))>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/search_len') }}</label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[search_len]" placeholder="" value="{{ $config['app']['search_len'] ?? '' }}" class="layui-input w150">
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/search_len_tip') }}</div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/search_timespan') }}</label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[search_timespan]" placeholder="" value="{{ $config['app']['search_timespan'] ?? '' }}" class="layui-input w150">
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/search_timespan_tip') }}</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/search_vod_rule') }}</label>
                    <div class="layui-input-inline w600">
                        <input type="checkbox" lay-skin="primary" name="app[search_vod_rule][]" value="vod_name" title="{{ __('admin.name') }}" checked disabled>
                        @php $svr = (string)($config['app']['search_vod_rule'] ?? ''); @endphp
                        <input type="checkbox" lay-skin="primary" name="app[search_vod_rule][]" value="vod_en" title="{{ __('admin.en') }}" @checked(strpos($svr,'vod_en') !== false)>
                        <input type="checkbox" lay-skin="primary" name="app[search_vod_rule][]" value="vod_sub" title="{{ __('admin.sub') }}" @checked(strpos($svr,'vod_sub') !== false)>
                        <input type="checkbox" lay-skin="primary" name="app[search_vod_rule][]" value="vod_tag" title="{{ __('admin.tag') }}" @checked(strpos($svr,'vod_tag') !== false)>
                        <input type="checkbox" lay-skin="primary" name="app[search_vod_rule][]" value="vod_actor" title="{{ __('admin.actor') }}" @checked(strpos($svr,'vod_actor') !== false)>
                        <input type="checkbox" lay-skin="primary" name="app[search_vod_rule][]" value="vod_director" title="{{ __('admin.director') }}" @checked(strpos($svr,'vod_director') !== false)>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/search_rule_tip') }}</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/search_art_rule') }}</label>
                    <div class="layui-input-inline w600">
                        <input type="checkbox" lay-skin="primary" name="app[search_art_rule][]" value="art_name" title="{{ __('admin.name') }}" checked disabled>
                        @php $sar = (string)($config['app']['search_art_rule'] ?? ''); @endphp
                        <input type="checkbox" lay-skin="primary" name="app[search_art_rule][]" value="art_en" title="{{ __('admin.en') }}" @checked(strpos($sar,'art_en') !== false)>
                        <input type="checkbox" lay-skin="primary" name="app[search_art_rule][]" value="art_sub" title="{{ __('admin.sub') }}" @checked(strpos($sar,'art_sub') !== false)>
                        <input type="checkbox" lay-skin="primary" name="app[search_art_rule][]" value="art_tag" title="{{ __('admin.tag') }}" @checked(strpos($sar,'art_tag') !== false)>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/search_rule_tip') }}</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/vod_search_optimise') }}</label>
                    <div class="layui-input-inline">
                        @php $vso = (string)($config['app']['vod_search_optimise'] ?? ''); @endphp
                        <input type="checkbox" lay-skin="primary" name="app[vod_search_optimise][]" value="frontend" title="{{ __('admin/system/config/vod_search_optimise/frontend') }}" @checked(strpos($vso,'frontend') !== false)>
                        <input type="checkbox" lay-skin="primary" name="app[vod_search_optimise][]" value="collect" title="{{ __('admin/system/config/vod_search_optimise/collect') }}" @checked(strpos($vso,'collect') !== false)>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/vod_search_optimise_tip') }}</div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/vod_search_optimise_cache_minutes') }}</label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[vod_search_optimise_cache_minutes]" placeholder="" value="{{ $config['app']['vod_search_optimise_cache_minutes'] ?? '' }}" class="layui-input w150">
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/vod_search_optimise_cache_minutes_tip') }}</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/copyright_status') }}</label>
                    <div class="layui-input-block">
                        @php $cs = (int)($config['app']['copyright_status'] ?? 0); @endphp
                        <input type="radio" name="app[copyright_status]" value="0" title="{{ __('admin.close') }}" @checked(($cs === 0))>
                        <input type="radio" name="app[copyright_status]" value="1" title="{{ __('admin/system/config/copyright_msg') }}" @checked(($cs === 1))>
                        <input type="radio" name="app[copyright_status]" value="2" title="{{ __('admin/system/config/copyright_jump_detail') }}" @checked(($cs === 2))>
                        <input type="radio" name="app[copyright_status]" value="3" title="{{ __('admin/system/config/copyright_jump_play') }}" @checked(($cs === 3))>
                        <input type="radio" name="app[copyright_status]" value="4" title="{{ __('admin/system/config/copyright_jump_iframe') }}" @checked(($cs === 4))>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/copyright_notice') }}</label>
                    <div class="layui-input-inline w500">
                        <input type="text" name="app[copyright_notice]" placeholder="" value="{{ $config['app']['copyright_notice'] ?? '' }}" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/browser_junmp') }}</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="app[browser_junmp]" value="0" title="{{ __('admin.close') }}" @checked((($config['app']['browser_junmp'] ?? 0) == 0))>
                        <input type="radio" name="app[browser_junmp]" value="1" title="{{ __('admin.open') }}" @checked((($config['app']['browser_junmp'] ?? 0) == 1))>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/browser_junmp_tip') }}</div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/404') }}</label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[page_404]" placeholder="" value="{{ $config['app']['page_404'] ?? '' }}" class="layui-input w150">
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/404_tip') }}</div>
                </div>
            </div>

            <div class="layui-tab-item">
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/player_sort') }}</label>
                        <div class="layui-input-inline">
                            <input type="radio" name="app[player_sort]" value="0" title="{{ __('admin.add') }}" @checked((($config['app']['player_sort'] ?? 0) == 0))>
                            <input type="radio" name="app[player_sort]" value="1" title="{{ __('admin/system/config/global') }}" @checked((($config['app']['player_sort'] ?? 0) == 1))>
                        </div>
                        <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/player_sort_tip') }}</div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/encrypt') }}</label>
                        <div class="layui-input-inline">
                            <select style="width:150px;" name="app[encrypt]">
                                <option value="0">{{ __('admin/system/config/encrypt_not') }}</option>
                                <option value="1" @selected((($config['app']['encrypt'] ?? 0) == 1))>escape</option>
                                <option value="2" @selected((($config['app']['encrypt'] ?? 0) == 2))>base64</option>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/search_hot') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="app[search_hot]" placeholder="{{ __('admin.multi_separate_tip') }}" value="{{ $config['app']['search_hot'] ?? '' }}" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/art_extend_class') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="app[art_extend_class]" placeholder="" value="{{ $config['app']['art_extend_class'] ?? '' }}" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/vod_extend_class') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="app[vod_extend_class]" placeholder="" value="{{ $config['app']['vod_extend_class'] ?? '' }}" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/vod_extend_state') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="app[vod_extend_state]" placeholder="" value="{{ $config['app']['vod_extend_state'] ?? '' }}" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/vod_extend_version') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="app[vod_extend_version]" placeholder="" value="{{ $config['app']['vod_extend_version'] ?? '' }}" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/vod_extend_area') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="app[vod_extend_area]" placeholder="" value="{{ $config['app']['vod_extend_area'] ?? '' }}" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/vod_extend_lang') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="app[vod_extend_lang]" placeholder="" value="{{ $config['app']['vod_extend_lang'] ?? '' }}" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/vod_extend_year') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="app[vod_extend_year]" placeholder="" value="{{ $config['app']['vod_extend_year'] ?? '' }}" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/vod_extend_weekday') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="app[vod_extend_weekday]" placeholder="" value="{{ $config['app']['vod_extend_weekday'] ?? '' }}" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/actor_extend_area') }}</label>
                        <div class="layui-input-block">
                            <input type="text" name="app[actor_extend_area]" placeholder="" value="{{ $config['app']['actor_extend_area'] ?? '' }}" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/filter_words') }}</label>
                        <div class="layui-input-block">
                            <textarea name="app[filter_words]" class="layui-textarea" placeholder="{{ __('admin/system/config/filter_words_tip') }}">{{ $config['app']['filter_words'] ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{ __('admin/system/config/extra_var') }}</label>
                        <div class="layui-input-block">
                            <textarea name="app[extra_var]" class="layui-textarea" placeholder="{{ __('admin/system/config/extra_var_tip') }}">{{ $config['app']['extra_var'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>


            <div class="layui-tab-item">
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/collect_timespan') }}</label>
                    <div class="layui-input-inline">
                        <input type="text" name="app[collect_timespan]" placeholder="" value="{{ $config['app']['collect_timespan'] ?? '' }}" class="layui-input w150">
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/collect_timespan_tip') }}</div>
                </div>


                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/pagesize') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="app[pagesize]" placeholder="{{ __('admin/system/config/pagesize_tip') }}" value="{{ $config['app']['pagesize'] ?? '' }}" class="layui-input w150">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/makesize') }}</label>
                    <div class="layui-input-block">
                        <input type="text" name="app[makesize]" placeholder="{{ __('admin/system/config/makesize_tip') }}" value="{{ $config['app']['makesize'] ?? '' }}" class="layui-input w150">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/admin_login_verify') }}</label>
                    <div class="layui-input-block">
                        <input type="radio" name="app[admin_login_verify]" value="0" title="{{ __('admin.close') }}" @checked((($config['app']['admin_login_verify'] ?? '0') === '0'))>
                        <input type="radio" name="app[admin_login_verify]" value="1" title="{{ __('admin.open') }}" @checked((($config['app']['admin_login_verify'] ?? '0') !== '0'))>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/editor') }}</label>
                    <div class="layui-input-inline">
                        <select style="width:150px;" name="app[editor]">
                            @php $editorOptions = ['ueditor' => 'UEditor', 'markdown' => 'Markdown', 'wangEditor' => 'wangEditor']; @endphp
                            @foreach($editorOptions as $key => $vo)
                            <option value="{{ $key }}" @selected((($config['app']['editor'] ?? '') == $key))>{{ $vo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="layui-form-mid layui-word-aux">{{ __('admin/system/config/editor_tip') }}</div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">{{ __('admin/system/config/lang') }}</label>
                    <div class="layui-input-inline" >
                        <select class="w150" name="app[lang]">
                            @foreach($langs as $vo)
                            <option value="{{ $vo }}" @selected((($config['app']['lang'] ?? '') == $vo))>{{ $vo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

                <div class="layui-form-item center">
                    <div class="layui-input-block">
                        <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit">{{ __('admin.btn_save') }}</button>
                        <button class="layui-btn layui-btn-warm" type="reset">{{ __('admin.btn_reset') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@include('admin.public.foot')
<script type="text/javascript" src="{{ asset('static') }}/js/jquery.cookie.js"></script>
<script type="text/javascript">
    layui.use(['form','upload', 'layer'], function(){
        // 操作对象
        var element = layui.element
            ,form = layui.form
            , layer = layui.layer
            , upload = layui.upload;

        form.on('radio(cache_type)',function(data){
            $('.row_cache_server').hide();
           if(data.value=='memcache' || data.value=='redis' || data.value=='memcached'){
               $('.row_cache_server').show();
           }
        });

        element.on('tab(tb1)', function(){
            $.cookie('config_tab', this.getAttribute('lay-id'));
        });

        if( $.cookie('config_tab') !=null ) {
            element.tabChange('tb1', $.cookie('config_tab'));
        }

        upload.render({
            elem: '.layui-upload'
            ,url: "{{ route('admin.upload.upload') }}?flag=site"
            ,method: 'post'
            ,before: function(input) {
                layer.msg("{{ __('admin.upload_ing') }}", {time:3000000});
            },done: function(res, index, upload) {
                var obj = this.item;
                if (res.code == 0) {
                    layer.msg(res.msg);
                    return false;
                }
                layer.closeAll();
                var input = $(obj).parent().parent().find('.upload-input');
                if ($(obj).attr('lay-type') == 'image') {
                    input.siblings('img').attr('src', res.data.file).show();
                }
                input.val(res.data.file);

                if(res.data.thumb_class !=''){
                    $('.'+ res.data.thumb_class).val(res.data.thumb[0].file);
                }
            }
        });

        $('.upload-input').hover(function (e){
            var e = window.event || e;
            var imgsrc = $(this).val();
            if(imgsrc.trim()==""){ return; }
            var left = e.clientX+document.body.scrollLeft+20;
            var top = e.clientY+document.body.scrollTop+20;
            $(".showpic").css({left:left,top:top,display:""});
            $(".showpic_img").attr("src", mac_url_img(imgsrc));
        },function (e){
            $(".showpic").css("display","none");
        });


    });

    function test_cache(){
        var type = $("input[name='app[cache_type]']:checked").val();
        var host = $("input[name='app[cache_host]']").val();
        var port = $("input[name='app[cache_port]']").val();
        var user_name =  $("input[name='app[cache_username]']").val();
        var password = $("input[name='app[cache_password]']").val();
        var db = $("input[name='app[cache_db]']").val();
        layer.msg("{{ __('admin.wait_submit') }}",{time:500000});
        $.ajax({
            url: "{{ url('system/test_cache') }}",
            type: "post",
            dataType: "json",
            data: {type:type,host:host,port:port,username:user_name,password:password,db:db},
            beforeSend: function () {
            },
            error:function(r){
                layer.msg("{{ __('admin/system/config/test_err') }}",{time:1800});
            },
            success: function (r) {
                layer.msg(r.msg,{time:1800});
            },
            complete: function () {
            }
        });
    }


</script>

</body>
</html>
