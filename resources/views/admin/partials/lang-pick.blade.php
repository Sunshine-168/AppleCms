@php
    $langCurrent = \App\Support\AdminUi::current();
    $langAsForm = $asForm ?? true;
@endphp
<details class="lang-pick">
    <summary>
        <span class="lang-pick-tag">{{ \App\Support\AdminUi::tag() }}</span>
        <span class="lang-pick-name">{{ \App\Support\AdminUi::label() }}</span>
    </summary>
    @if($langAsForm)
        <form method="post" action="{{ route('admin.ui-locale') }}" class="lang-pick-menu">
            @csrf
            @foreach(\App\Support\AdminUi::catalog() as $code => $meta)
                <button type="submit" name="ui_locale" value="{{ $code }}" class="{{ $langCurrent === $code ? 'is-on' : '' }}">
                    <span class="lang-pick-tag">{{ $meta['tag'] }}</span>
                    <span>{{ $meta['label'] }}</span>
                </button>
            @endforeach
        </form>
    @else
        <div class="lang-pick-menu">
            @foreach(\App\Support\AdminUi::catalog() as $code => $meta)
                <a href="{{ request()->fullUrlWithQuery(['ui' => $code]) }}" class="{{ $langCurrent === $code ? 'is-on' : '' }}">
                    <span class="lang-pick-tag">{{ $meta['tag'] }}</span>
                    <span>{{ $meta['label'] }}</span>
                </a>
            @endforeach
        </div>
    @endif
</details>
