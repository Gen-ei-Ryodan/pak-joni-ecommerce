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
                    <a class="card" href="{{ route('buyer.news.show', $item->slug) }}">
                        <div class="card-media" style="background-image:url('{{ $item->thumbnail_path ? asset($item->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:200px;"></div>
                        <div class="card-body">
                            <div class="card-meta">{{ $item->publish_date?->format('d M Y') }} @if($item->category) &middot; {{ $item->category }} @endif</div>
                            <div class="card-title">{{ $item->title }}</div>
                            <div class="card-meta" style="margin-top:6px;">{{ \Illuminate\Support\Str::limit(strip_tags($item->content), 100) }}</div>
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
