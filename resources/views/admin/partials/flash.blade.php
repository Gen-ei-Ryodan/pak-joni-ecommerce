@if (session('status'))
    <div style="margin-bottom:14px;border:1px solid rgba(217,180,111,0.35);background:rgba(217,180,111,0.08);border-radius:12px;padding:10px 12px;">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div style="margin-bottom:14px;border:1px solid rgba(255,77,77,0.35);background:rgba(255,77,77,0.08);border-radius:12px;padding:10px 12px;">
        <div style="display:grid;gap:6px;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif
