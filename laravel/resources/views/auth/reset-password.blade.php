@extends('layouts.auth')

@section('title', 'New Password')

@section('content')
    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-title">New Password</div>
            <div class="auth-subtitle">Create a new password for your account.</div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <div style="display:grid;gap:6px;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form class="form" method="post" action="{{ route('auth.password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required>
                </div>

                <div class="field">
                    <label for="password">New Password</label>
                    <div class="password-wrapper">
                        <input id="password" name="password" type="password" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" data-toggle="password" tabindex="-1" aria-label="Toggle password visibility">
                            <span class="eye-open">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </span>
                            <span class="eye-closed">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm New Password</label>
                    <div class="password-wrapper">
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" data-toggle="password_confirmation" tabindex="-1" aria-label="Toggle password visibility">
                            <span class="eye-open">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </span>
                            <span class="eye-closed">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Save Password</button>
                    @if (Route::has('auth.login'))
                        <a class="text-link" href="{{ route('auth.login') }}">Back</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
