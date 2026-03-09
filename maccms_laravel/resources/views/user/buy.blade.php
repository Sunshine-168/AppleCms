@extends('user.layout')

@section('title', '在线充值')

@section('user_content')
<div class="card mb-4">
    <div class="card-header">创建充值订单</div>
    <div class="card-body">
        <form method="post" action="{{ route('user.buy') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">充值金额</label>
                <input type="number" step="0.01" min="0" class="form-control" name="price">
            </div>
            <button type="submit" class="btn btn-primary">创建订单</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">充值卡兑换</div>
    <div class="card-body">
        <form method="post" action="{{ route('user.buy') }}">
            @csrf
            <input type="hidden" name="flag" value="card">
            <div class="mb-3">
                <label class="form-label">卡号</label>
                <input type="text" class="form-control" name="card_no">
            </div>
            <div class="mb-3">
                <label class="form-label">卡密</label>
                <input type="text" class="form-control" name="card_pwd">
            </div>
            <button type="submit" class="btn btn-outline-primary">兑换</button>
        </form>
    </div>
</div>
@endsection
