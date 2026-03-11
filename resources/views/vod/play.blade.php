@extends('layouts.front')

@section('title', $video->vod_name . ' - ' . $currentEpisode['name'])

@section('content')
<div class="row">
    <div class="col-md-9">
        <h3>{{ $video->vod_name }} - {{ $currentEpisode['name'] }}</h3>
        
        <div class="ratio ratio-16x9 bg-dark">
            <iframe src="{{ $currentEpisode['url'] }}" title="{{ $video->vod_name }}" allowfullscreen></iframe>
        </div>
        
        <div class="mt-3">
            <h4>Description</h4>
            <p>{{ strip_tags($video->vod_content) }}</p>
        </div>
    </div>
    <div class="col-md-3">
        <h4>Episodes</h4>
        @foreach($playList as $pIndex => $player)
        <div class="mb-3">
            <h5>{{ $player['player_name'] }}</h5>
            <div class="list-group">
                @foreach($player['urls'] as $eIndex => $episode)
                <a href="{{ route('vod.play', ['id' => $video->vod_id, 'sid' => $pIndex + 1, 'nid' => $eIndex + 1]) }}" 
                   class="list-group-item list-group-item-action {{ ($sid == $pIndex + 1 && $nid == $eIndex + 1) ? 'active' : '' }}">
                   {{ $episode['name'] }}
                </a>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
