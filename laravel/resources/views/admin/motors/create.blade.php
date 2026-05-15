@extends('layouts.admin')

@section('title', 'Tambah Motor')

@section('content')
    @include('admin.partials.flash')

    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
        <div style="font-size:16px;font-weight:600;">Tambah Motor</div>
        <a class="btn" href="{{ route('admin.motors.index') }}">Kembali</a>
    </div>

    <div style="height:12px;"></div>

    <form method="post" action="{{ route('admin.motors.store') }}" enctype="multipart/form-data" class="panel" style="padding:16px;">
        @csrf
        @include('admin.motors._form', ['motor' => null])
        <div style="height:14px;"></div>
        <button class="btn btn-primary" type="submit">Simpan</button>
    </form>
@endsection
