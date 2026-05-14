@extends('layouts.buyer-dashboard')

@section('title', 'Add Address')

@section('dashboard-content')
    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
        <div style="font-size:16px;font-weight:600;">Add Address</div>
        <a class="btn" href="{{ route('buyer.addresses.index') }}">Back</a>
    </div>

    <div style="height:12px;"></div>

    <form method="post" action="{{ route('buyer.addresses.store') }}" class="panel" style="padding:16px;">
        @csrf
        @include('buyer.addresses._form', ['address' => null])
        <div style="height:14px;"></div>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>
@endsection
