@foreach($menus as $menu)

    @if(!empty($menu['sub']))

        <dd>

            <a href="javascript:;">
                @if(!empty($menu['icon']))
                    <i class="layui-icon {{$menu['icon']}}"></i>
                @endif
                {{$menu['name']}}
            </a>

            <dl class="layui-nav-child">
                @include('admin.components.menu', ['menus' => $menu['sub']])
            </dl>

        </dd>

    @else

        <dd>
            <a href="{{ (!empty($menu['route']) && Route::has($menu['route'])) ? route($menu['route']) : 'javascript:;' }}">
                {{$menu['name']}}
            </a>
        </dd>

    @endif

@endforeach



{{--<li data-name="home" class="layui-nav-item layui-nav-itemed">--}}
{{--    <a href="javascript:;" lay-tips="主页" lay-direction="2">--}}
{{--        <i class="layui-icon layui-icon-home"></i>--}}
{{--        <cite>主页</cite>--}}
{{--    </a>--}}
{{--    <dl class="layui-nav-child">--}}
{{--        <dd data-name="console" class="layui-this">--}}
{{--            <a lay-href="home/console.html">控制台</a>--}}
{{--        </dd>--}}
{{--        <dd data-name="console">--}}
{{--            <a lay-href="home/homepage1.html">主页一</a>--}}
{{--        </dd>--}}
{{--        <dd data-name="console">--}}
{{--            <a lay-href="home/homepage2.html">主页二</a>--}}
{{--        </dd>--}}
{{--    </dl>--}}
{{--</li>--}}
