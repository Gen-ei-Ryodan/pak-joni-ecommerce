@extends('layouts.buyer')

@section('title', $career->title . ' - Karir')

@section('content')
    <section class="section">
        <div class="container" style="max-width:800px;">
            <a href="{{ route('buyer.careers.index') }}" class="back-link" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);text-decoration:none;margin-bottom:20px;">
                &#8592; Kembali ke Karir
            </a>

            <div class="article-meta" style="font-size:12px;color:var(--muted);margin-bottom:8px;">
                {{ $career->publish_date?->format('d M Y') }}
            </div>

            <h1 style="font-size:clamp(22px,4vw,30px);font-weight:700;margin-bottom:8px;">{{ $career->title }}</h1>
            @if($career->location)
                <p style="color:var(--muted);font-size:14px;margin-bottom:24px;">&#x1F4CD; {{ $career->location }}</p>
            @endif

            @if($career->description)
                <div style="background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:24px;margin-bottom:24px;">
                    <h3 style="font-size:16px;font-weight:600;margin-bottom:12px;">Deskripsi Pekerjaan</h3>
                    <div style="color:var(--muted);line-height:1.8;font-size:14px;">{!! $career->description !!}</div>
                </div>
            @endif

            @if($career->requirements)
                <div style="background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:24px;margin-bottom:24px;">
                    <h3 style="font-size:16px;font-weight:600;margin-bottom:12px;">Persyaratan</h3>
                    <div style="color:var(--muted);line-height:1.8;font-size:14px;">{!! $career->requirements !!}</div>
                </div>
            @endif

            <button class="btn btn-accent btn-full" style="max-width:300px;">Lamar Sekarang</button>
        </div>
    </section>
@endsection
