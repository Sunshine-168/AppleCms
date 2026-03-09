@extends('layouts.front')

@section('title', 'Actors')

@section('content')
<h2 class="mb-4">Actors</h2>

<form class="d-flex mb-4" action="{{ route('actor.search') }}" method="GET">
    <input class="form-control me-2" type="search" name="wd" placeholder="Search Actor" aria-label="Search">
    <button class="btn btn-outline-success" type="submit">Search</button>
</form>

<div class="row">
    @foreach($actors as $actor)
    <div class="col-md-2 col-sm-4 mb-4">
        <div class="card h-100">
            <a href="{{ route('actor.detail', $actor->actor_id) }}">
                <img src="{{ $actor->actor_pic }}" class="card-img-top" alt="{{ $actor->actor_name }}" style="height: 200px; object-fit: cover;">
            </a>
            <div class="card-body text-center">
                <h5 class="card-title">
                    <a href="{{ route('actor.detail', $actor->actor_id) }}" class="text-decoration-none text-dark">{{ $actor->actor_name }}</a>
                </h5>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $actors->links() }}
</div>
@endsection
