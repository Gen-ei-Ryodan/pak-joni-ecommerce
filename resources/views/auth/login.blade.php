@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-title">Login</div>
            <div class="auth-subtitle">Sign in to continue shopping and view your dashboard.</div>

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

            <form class="form" method="post" action="{{ route('auth.login.store') }}">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Login</button>
                    @if (Route::has('auth.password.request'))
                        <a class="text-link" href="{{ route('auth.password.request') }}">Forgot password?</a>
                    @endif
                </div>
                <div style="margin-top:14px;text-align:center;border-top:1px solid var(--line);padding-top:14px;">
                    <span class="muted" style="font-size:13px;">Don't have an account?</span>
                    <a class="text-link" href="{{ route('auth.register') }}" style="margin-left:4px;">Register</a>
                </div>
            </form>
        </div>
    </div>
@endsection
