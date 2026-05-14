@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-title">Reset Password</div>
            <div class="auth-subtitle">Masukkan email, kami kirim link reset password.</div>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <div style="display:grid;gap:6px;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form class="form" method="post" action="{{ route('auth.password.email') }}">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Kirim Link</button>
                    @if (Route::has('auth.login'))
                        <a class="text-link" href="{{ route('auth.login') }}">Kembali ke login</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
