<nav class="manga-sub" aria-label="小说栏目">
    <a href="{{ url('/novel') }}" @class(['on' => ($page ?? '') === 'index'])>小说</a>
    <a href="{{ url('/novel/shelf') }}" @class(['on' => ($page ?? '') === 'shelf'])>书架</a>
    <a href="{{ url('/novel/history') }}" @class(['on' => ($page ?? '') === 'history'])>历史</a>
</nav>
