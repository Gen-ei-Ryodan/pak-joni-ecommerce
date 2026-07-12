@extends('layouts.buyer')

@section('title', $news->title)

@section('content')
    <section class="section">
        <div class="container" style="max-width:900px;">
            <a href="{{ route('buyer.news.index') }}" class="back-link" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);text-decoration:none;margin-bottom:20px;">
                &#8592; Kembali ke Berita
            </a>

            @if($news->thumbnail_path)
                <div style="width:100%;height:350px;border-radius:var(--radius);background:url('{{ image_url($news->thumbnail_path) }}') center/cover no-repeat;margin-bottom:24px;"></div>
            @endif

            <div class="article-meta" style="display:flex;flex-wrap:wrap;gap:12px;font-size:12px;color:var(--muted);margin-bottom:16px;">
                <span>{{ $news->publish_date?->format('d M Y') }}</span>
                @if($news->author) <span>Oleh {{ $news->author }}</span> @endif
                @if($news->category) <span>{{ $news->category }}</span> @endif
            </div>

            <h1 style="font-size:clamp(22px,4vw,32px);font-weight:700;margin-bottom:20px;">{{ $news->title }}</h1>

            <div class="article-content" style="color:var(--muted);line-height:1.9;font-size:15px;">
                {!! $news->content !!}
            </div>

            @if($news->external_url)
                <div style="margin-top:24px;">
                    <a href="{{ $news->external_url }}" target="_blank" rel="noopener" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        Baca Selengkapnya
                    </a>
                </div>
            @endif

            @if(!empty($related) && $related->count())
                <div style="margin-top:50px;padding-top:30px;border-top:1px solid var(--line);">
                    <h3 style="font-size:18px;font-weight:600;margin-bottom:20px;">Berita Terkait</h3>
                    <div class="grid grid-4">
                        @foreach($related as $item)
                            <a class="card" href="{{ route('buyer.news.show', $item->slug) }}">
                                <div class="card-media" style="background-image:url('{{ $item->thumbnail_path ? image_url($item->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:140px;"></div>
                                <div class="card-body">
                                    <div class="card-meta">{{ $item->publish_date?->format('d M Y') }}</div>
                                    <div class="card-title" style="font-size:13px;">{{ $item->title }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
