@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <div style="display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;align-items:center;">
        <div>
            <div style="font-size:16px;font-weight:600;">Dashboard Admin</div>
            <div class="muted" style="margin-top:6px;">Halo, {{ auth()->user()->name }}</div>
        </div>
    </div>
@endsection
