@extends('layouts.buyer-dashboard')

@section('title', 'Edit Address')

@section('dashboard-content')
    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
        <div style="font-size:16px;font-weight:600;">Edit Address</div>
        <a class="btn" href="{{ route('buyer.addresses.index') }}">Back</a>
    </div>

    <div style="height:12px;"></div>

    <form method="post" action="{{ route('buyer.addresses.update', $address) }}" class="panel" style="padding:16px;">
        @csrf
        @method('put')
        @include('buyer.addresses._form', compact('address'))
        <div style="height:14px;"></div>
        <button class="btn btn-primary" type="submit">Update</button>
    </form>
@endsection
