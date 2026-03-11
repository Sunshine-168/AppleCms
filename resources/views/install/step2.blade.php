@include('install.head')
<style type="text/css">
    .layui-table {
        border: 1px solid #E6E7EB;
    }

    .layui-table td,
    .layui-table th {
        text-align: center;
        font-size: 14px;
        color: #4B5563;
    }

    .layui-table th {
        background: #E6E7EB;
        color: #1F2937;
    }

    .layui-table tbody tr.yes td:last-child::before,
    .layui-table tbody tr.ok td:last-child::before {
        content: '';
        display: inline-block;
        width: 16px;
        vertical-align: middle;
        height: 16px;
        margin-right: 4px;
        background: url({{ asset('static/images/install/monitor_ic_check.png') }}) 100% 100%;
    }

    .layui-table tbody tr.no td:last-child::before {
        content: '';
        display: inline-block;
        vertical-align: middle;
        width: 16px;
        height: 16px;
        margin-right: 4px;
        background: url({{ asset('static/images/install/monitor_ic_wrong.png') }}) 100% 100%;
    }

    .install-box {
        width: 1400px;
    }

    .title-run {
        height: 28px;
        font-family: PingFangSC, PingFang SC;
        font-weight: 500;
        font-size: 20px;
        color: #1F2937;
        line-height: 28px;
        text-align: center;
        font-style: normal;
    }

    .word-box {
        display: flex;
        gap: 20px;
    }

    .step-btns .last {
        width: 300px;
        height: 40px;
        background: rgba(64, 204, 146, 0.2);
        border-radius: 6px;
        color: rgba(64, 204, 146, 1);
    }

    .step-btns .common {
        width: 300px;
        height: 40px;
        background: #FFFFFF;
        box-shadow: 0px 1px 3px 0px #EBEDF0;
        border-radius: 6px;
        border: 1px solid #E6E7EB;
        color: rgba(31, 41, 55, 1);
    }
</style>
<div class="install-box">
    <div class="title-run">
        {{ __('install.environment_title') }}
    </div>
    <table class="layui-table" lay-skin="line">
        <thead>
            <tr>
                <th>{{ __('install.environment_name') }}</th>
                <th>{{ __('install.required_config') }}</th>
                <th>{{ __('install.current_config') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['env'] as $key => $vo)
            <tr class="{{ $vo[4] }}">
                <td>{{ $vo[0] }}</td>
                <td>{{ $vo[2] }}</td>
                <td>{{ $vo[3] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="word-box">
        <table class="layui-table" lay-skin="line">
            <thead>
                <tr>
                    <th>{{ __('install.func_ext') }}</th>
                    <th>{{ __('install.type') }}</th>
                    <th>{{ __('install.result') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['func'] as $vo)
                <tr class="{{ $vo[2] }}">
                    <td>{{ $vo[0] }}</td>
                    <td>{{ $vo[3] }}</td>
                    <td>{{ $vo[1] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <table class="layui-table" lay-skin="line">
            <thead>
                <tr>
                    <th>{{ __('install.dir_file') }}</th>
                    <th>{{ __('install.required_popedom') }}</th>
                    <th>{{ __('install.current_popedom') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['dir'] as $vo)
                <tr class="{{ $vo[4] }}">
                    <td>{{ $vo[1] }}</td>
                    <td>{{ $vo[2] }}</td>
                    <td>{{ $vo[3] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="step-btns">
        <a href="{{ route('install.index') }}" class="last layui-btn layui-btn-primary layui-btn-big fl">{{ __('install.back_step') }}</a>

        <a href="{{ route('install.step3') }}" class="layui-btn layui-btn-big layui-btn-normal fl">{{ __('install.next_step') }}</a><div style="padding: 9px 9px !important;" class="layui-form-mid layui-word-aux">{{ __('install.next_step_tips') }}</div>

        <a target="_blank" href="http://www.maccms.la/doc/v10/faq.html"
            class="layui-btn common fr">{{ __('install.question') }}</a>
    </div>
</div>
@include('install.foot')
