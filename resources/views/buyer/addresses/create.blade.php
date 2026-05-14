@extends('layouts.buyer')

@section('title', 'Add Address')

@section('content')
    <section class="section">
        <div class="container">
            @if ($errors->any())
                <div class="panel" style="padding:10px 12px;margin-bottom:12px;border-color:rgba(255,77,77,0.35);background:rgba(255,77,77,0.08);">
                    <div style="display:grid;gap:6px;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div style="font-size:18px;font-weight:600;">Add Address</div>
                <a class="btn" href="{{ route('buyer.addresses.index') }}">Back</a>
            </div>

            <div style="height:12px;"></div>

            <form method="post" action="{{ route('buyer.addresses.store') }}" class="panel" style="padding:16px;">
                @csrf
                @include('buyer.addresses._form', ['address' => null])
                <div style="height:14px;"></div>
                <button class="btn btn-primary" type="submit">Save</button>
            </form>
        </div>
    </section>
@endsection
