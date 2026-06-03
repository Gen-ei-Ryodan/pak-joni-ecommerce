@extends('layouts.auth')

@section('title', 'Register')

@section('content')
    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-title">Register</div>
            <div class="auth-subtitle">Buat akun pembeli untuk mulai berbelanja.</div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <div style="display:grid;gap:6px;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form class="form" method="post" action="{{ route('auth.register.store') }}">
                @csrf

                <div class="field">
                    <label for="name">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required>
                </div>

                <div class="field">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Daftar</button>
                    @if (Route::has('auth.login'))
                        <a class="text-link" href="{{ route('auth.login') }}">Sudah punya akun?</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
