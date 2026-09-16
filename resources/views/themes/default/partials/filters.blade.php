<div class="filters">
    @vodFilter
        <div class="filter-row">
            <strong>{{ $item->label }}</strong>
            @foreach($item->choices as $choice)
                <a class="{{ $choice['active'] ? 'active' : '' }}" href="{{ $choice['url'] }}">{{ $choice['label'] }}</a>
            @endforeach
        </div>
    @endvodFilter
</div>
