<div class="filters">
    @vodFilter
        <div class="filter-row">
            <span class="filter-label">{{ $item->label }}</span>
            <div class="filter-choices">
                @foreach($item->choices as $choice)
                    <a class="{{ !empty($choice['active']) ? 'active' : '' }}" href="{{ $choice['url'] }}">{{ $choice['label'] }}</a>
                @endforeach
            </div>
        </div>
    @endvodFilter
</div>
