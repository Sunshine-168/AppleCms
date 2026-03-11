@extends('layouts.front')

@section('title', 'Guestbook')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <h2 class="mb-4">Guestbook</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('gbook.save') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <textarea class="form-control" name="gbook_content" rows="3" placeholder="Leave a message..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>

        <div class="list-group">
            @foreach($gbooks as $gbook)
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">{{ $gbook->gbook_name }}</h5>
                    <small>{{ date('Y-m-d H:i', $gbook->gbook_time) }}</small>
                </div>
                <p class="mb-1">{{ $gbook->gbook_content }}</p>
                @if($gbook->gbook_reply)
                    <div class="alert alert-secondary mt-2">
                        <strong>Reply:</strong> {{ $gbook->gbook_reply }}
                    </div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $gbooks->links() }}
        </div>
    </div>
</div>
@endsection
