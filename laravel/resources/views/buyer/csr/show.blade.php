@extends('layouts.buyer')

@section('title', $article->title)

@section('content')
    <section class="section">
        <div class="container" style="max-width:900px;">
            <a href="{{ route('buyer.csr.index') }}" class="back-link" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);text-decoration:none;margin-bottom:20px;">
                &#8592; Kembali ke CSR
            </a>

            @if($article->thumbnail_path)
                <div style="width:100%;height:350px;border-radius:var(--radius);background:url('{{ image_url($article->thumbnail_path) }}') center/cover no-repeat;margin-bottom:24px;"></div>
            @endif

            <div class="article-meta" style="font-size:12px;color:var(--muted);margin-bottom:16px;">
                {{ $article->publish_date?->format('d M Y') }}
            </div>

            <h1 style="font-size:clamp(22px,4vw,32px);font-weight:700;margin-bottom:20px;">{{ $article->title }}</h1>

            <div class="article-content" style="color:var(--muted);line-height:1.9;font-size:15px;">
                {!! $article->content !!}
            </div>

            @if($article->documentation && count($article->documentation))
                <div style="margin-top:30px;padding-top:24px;border-top:1px solid var(--line);">
                    <h3 style="font-size:18px;font-weight:600;margin-bottom:16px;">Dokumentasi</h3>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;">
                        @foreach($article->documentation as $doc)
                            <div style="background-image:url('{{ asset($doc) }}');background-size:cover;background-position:center;height:180px;border-radius:8px;"></div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
