@extends('layouts.buyer')

@section('title', 'Berita')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Berita dan Informasi</h2>
                <div class="section-line center-line"></div>
            </div>

            <div class="grid grid-3">
                @forelse($news as $item)
                    <a class="card" href="{{ $item->external_url ?: route('buyer.news.show', $item->slug) }}" @if($item->external_url) target="_blank" rel="noopener" @endif>
                        <div class="card-media" style="background-image:url('{{ $item->thumbnail_path ? image_url($item->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:200px;"></div>
                        <div class="card-body">
                            <div class="card-meta">{{ $item->publish_date?->format('d M Y') }} @if($item->category) &middot; {{ $item->category }} @endif</div>
                            <div class="card-title">{{ $item->title }}</div>
                            <div class="card-meta" style="margin-top:6px;">{{ \Illuminate\Support\Str::limit(strip_tags($item->content), 100) }}</div>
                            @if($item->external_url)
                                <div class="card-meta" style="margin-top:4px;color:var(--accent);font-size:11px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    Link External
                                </div>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="muted" style="text-align:center;grid-column:1/-1;padding:60px 0;">Belum ada berita.</div>
                @endforelse
            </div>

            <div style="margin-top:30px;">
                {{ $news->links('pagination.simple-dark') }}
            </div>
        </div>
    </section>
@endsection
