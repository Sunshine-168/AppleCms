@include('admin.public.head')

<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="{{ route('admin.make.index') }}" id="form1">
        @csrf
        <input type="hidden" name="tab" id="make-tab" value="">
        <input type="hidden" name="scope" id="make-scope" value="">
        <input type="hidden" name="feed" id="make-feed" value="">
        <input type="hidden" name="with_type" id="make-with-type" value="0">

        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.vod') }}{{ __('admin.type') }}：</label>
            <div class="layui-input-inline">
                <select name="vodtype[]" multiple style="width:150px;height:150px;" lay-ignore>
                    @foreach($vodTypeList as $vo)
                        <option value="{{ $vo->type_id }}">{{ $vo->type_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="layui-input-inline w300">
                <div class="layui-btn-container">
                    <input type="button" value="{{ __('admin.admin/make/select_type') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.type') }}', 'vod', 'selected');"/>
                    <input type="button" value="{{ __('admin.admin/make/all_type') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.type') }}', 'vod', 'all');"/>
                    <input type="button" value="{{ __('admin.admin/make/today_type') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.type') }}', 'vod', 'today');"/>
                    <input type="button" value="{{ __('admin.admin/make/select_info') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.detail') }}', 'vod', 'selected');"/>
                    <input type="button" value="{{ __('admin.admin/make/all_info') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.detail') }}', 'vod', 'all');"/>
                    <input type="button" value="{{ __('admin.admin/make/today_info') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.detail') }}', 'vod', 'today');"/>
                    <input type="button" value="{{ __('admin.admin/make/no_make_info') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.detail') }}', 'vod', 'nomake');"/>
                    <input type="button" value="{{ __('admin.admin/make/one_today') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.detail') }}', 'vod', 'today_then_type', 1);"/>
                </div>
            </div>

            <label class="layui-form-label">{{ __('admin.art') }}{{ __('admin.type') }}：</label>
            <div class="layui-input-inline">
                <select name="arttype[]" multiple style="width:150px;height:150px;" lay-ignore>
                    @foreach($artTypeList as $vo)
                        <option value="{{ $vo->type_id }}">{{ $vo->type_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="layui-input-inline w300">
                <div class="layui-btn-container">
                    <input type="button" value="{{ __('admin.admin/make/select_type') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.type') }}', 'art', 'selected');"/>
                    <input type="button" value="{{ __('admin.admin/make/all_type') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.type') }}', 'art', 'all');"/>
                    <input type="button" value="{{ __('admin.admin/make/today_type') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.type') }}', 'art', 'today');"/>
                    <input type="button" value="{{ __('admin.admin/make/select_info') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.detail') }}', 'art', 'selected');"/>
                    <input type="button" value="{{ __('admin.admin/make/all_info') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.detail') }}', 'art', 'all');"/>
                    <input type="button" value="{{ __('admin.admin/make/today_info') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.detail') }}', 'art', 'today');"/>
                    <input type="button" value="{{ __('admin.admin/make/no_make_info') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.detail') }}', 'art', 'nomake');"/>
                    <input type="button" value="{{ __('admin.admin/make/one_today') }}" class="layui-btn layui-btn-primary" onclick="submitMake('{{ route('admin.make.detail') }}', 'art', 'today_then_type', 1);"/>
                </div>
            </div>
        </div>

        <hr class="layui-bg-gray">

        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/make/topic_list') }}：</label>
            <div class="layui-input-inline">
                <select name="topic[]" multiple style="width:150px;height:150px;" lay-ignore>
                    @foreach($topicList as $vo)
                        <option value="{{ $vo->topic_id }}">{{ $vo->topic_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="layui-input-inline w300">
                <div class="layui-btn-container">
                    <input type="button" value="{{ __('admin.admin/make/select_topic') }}" class="layui-btn layui-btn-primary" onclick="submitTopic('{{ route('admin.make.topic') }}', 'selected');"/>
                    <input type="button" value="{{ __('admin.admin/make/all_topic') }}" class="layui-btn layui-btn-primary" onclick="submitTopic('{{ route('admin.make.topic') }}', 'all');"/>
                    <input type="button" value="{{ __('admin.admin/make/topic_index') }}" class="layui-btn layui-btn-primary" onclick="submitTopic('{{ route('admin.make.topic') }}', 'index');"/>
                </div>
            </div>

            <label class="layui-form-label">{{ __('admin.admin/make/label_page') }}：</label>
            <div class="layui-input-inline">
                <select name="label[]" multiple style="width:150px;height:150px;" lay-ignore>
                    @foreach($labelList as $vo)
                        <option value="{{ $vo }}">{{ $vo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="layui-input-inline w300">
                <div class="layui-btn-container">
                    <input type="button" value="{{ __('admin.make_page') }}" class="layui-btn layui-btn-primary" onclick="submitLabel('selected');" {{ empty($labelList) ? 'disabled' : '' }}>
                    <input type="button" value="{{ __('admin.make_all') }}" class="layui-btn layui-btn-primary" onclick="submitLabel('all');" {{ empty($labelList) ? 'disabled' : '' }}>
                </div>
                <div class="layui-word-aux">
                    @if(empty($labelList))
                        当前未发现 `resources/views/label/*.blade.php` 自定义页面模板。
                    @else
                        将生成到 `public/label/*.html`。
                    @endif
                </div>
            </div>
        </div>

        <hr class="layui-bg-gray">

        <div class="layui-form-item">
            <label class="layui-form-label">{{ __('admin.admin/make/rss') }}：</label>
            <div class="layui-input-inline w800">
                <div class="layui-btn-container">
                    <input type="button" value="{{ __('admin.admin/make/rss') }}" class="layui-btn layui-btn-primary" onclick="submitRss('index');">
                    <input type="button" value="{{ __('admin.admin/make/google') }}" class="layui-btn layui-btn-primary" onclick="submitRss('google');">
                    <input type="button" value="{{ __('admin.admin/make/baidu') }}" class="layui-btn layui-btn-primary" onclick="submitRss('baidu');">
                    <input type="button" value="{{ __('admin.admin/make/so') }}" class="layui-btn layui-btn-primary" onclick="submitRss('so');">
                    <input type="button" value="{{ __('admin.admin/make/sogou') }}" class="layui-btn layui-btn-primary" onclick="submitRss('sogou');">
                    <input type="button" value="{{ __('admin.admin/make/bing') }}" class="layui-btn layui-btn-primary" onclick="submitRss('bing');">
                    <input type="button" value="{{ __('admin.admin/make/sm') }}" class="layui-btn layui-btn-primary" onclick="submitRss('sm');">
                    <input type="button" value="HTML SiteMap" class="layui-btn layui-btn-primary" onclick="submitMap();">
                    <input type="button" value="{{ __('admin.admin/make/title') }}" class="layui-btn" onclick="submitIndex();">
                </div>
            </div>
            <label class="layui-form-label">{{ __('admin.admin/make/make_page_num') }}：</label>
            <div class="layui-input-inline w200">
                <input type="text" name="ps" class="layui-input" value="1" />
            </div>
        </div>
    </form>
</div>

@include('admin.public.foot')

<script type="text/javascript">
    function submitMake(action, tab, scope, withType) {
        document.getElementById('form1').action = action;
        document.getElementById('make-tab').value = tab || '';
        document.getElementById('make-scope').value = scope || '';
        document.getElementById('make-feed').value = '';
        document.getElementById('make-with-type').value = withType ? '1' : '0';
        document.getElementById('form1').submit();
    }

    function submitTopic(action, scope) {
        document.getElementById('form1').action = action;
        document.getElementById('make-tab').value = '';
        document.getElementById('make-scope').value = scope || '';
        document.getElementById('make-feed').value = '';
        document.getElementById('make-with-type').value = '0';
        document.getElementById('form1').submit();
    }

    function submitRss(feed) {
        document.getElementById('form1').action = '{{ route('admin.make.rss') }}';
        document.getElementById('make-tab').value = '';
        document.getElementById('make-scope').value = '';
        document.getElementById('make-feed').value = feed;
        document.getElementById('make-with-type').value = '0';
        document.getElementById('form1').submit();
    }

    function submitMap() {
        document.getElementById('form1').action = '{{ route('admin.make.map') }}';
        document.getElementById('make-tab').value = '';
        document.getElementById('make-scope').value = '';
        document.getElementById('make-feed').value = '';
        document.getElementById('make-with-type').value = '0';
        document.getElementById('form1').submit();
    }

    function submitIndex() {
        document.getElementById('form1').action = '{{ route('admin.make.index') }}';
        document.getElementById('make-tab').value = '';
        document.getElementById('make-scope').value = '';
        document.getElementById('make-feed').value = '';
        document.getElementById('make-with-type').value = '0';
        document.getElementById('form1').submit();
    }

    function submitLabel(scope) {
        document.getElementById('form1').action = '{{ route('admin.make.label') }}';
        document.getElementById('make-tab').value = '';
        document.getElementById('make-scope').value = scope || '';
        document.getElementById('make-feed').value = '';
        document.getElementById('make-with-type').value = '0';

        if (scope === 'all') {
            var select = document.querySelector('select[name="label[]"]');
            if (select) {
                for (var i = 0; i < select.options.length; i++) {
                    select.options[i].selected = true;
                }
            }
        }

        document.getElementById('form1').submit();
    }
</script>
