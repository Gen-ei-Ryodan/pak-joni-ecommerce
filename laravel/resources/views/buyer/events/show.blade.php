@extends('layouts.buyer')

@section('title', $event->title)

@section('content')
    <section class="section">
        <div class="container" style="max-width:900px;">
            <a href="{{ route('buyer.events.index') }}" class="back-link" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);text-decoration:none;margin-bottom:20px;">
                &#8592; Kembali ke Acara
            </a>

            @if($event->thumbnail_path)
                <div style="width:100%;height:350px;border-radius:var(--radius);background:url('{{ asset($event->thumbnail_path) }}') center/cover no-repeat;margin-bottom:24px;"></div>
            @endif

            <div class="article-meta" style="display:flex;flex-wrap:wrap;gap:12px;font-size:12px;color:var(--muted);margin-bottom:16px;">
                <span>{{ $event->event_date?->format('d M Y') }}</span>
                @if($event->location) <span>&#x1F4CD; {{ $event->location }}</span> @endif
            </div>

            <h1 style="font-size:clamp(22px,4vw,32px);font-weight:700;margin-bottom:8px;">{{ $event->title }}</h1>
            @if($event->description)
                <p style="color:var(--muted);font-size:15px;margin-bottom:24px;">{{ $event->description }}</p>
            @endif

            @if($event->content)
                <div class="article-content" style="color:var(--muted);line-height:1.9;font-size:15px;margin-bottom:30px;">
                    {!! $event->content !!}
                </div>
            @endif

            @if($event->galleries->count())
                <div style="margin-top:30px;">
                    <h3 style="font-size:18px;font-weight:600;margin-bottom:16px;">Galeri & Dokumentasi</h3>
                    <div class="event-gallery">
                        @foreach($event->galleries as $gallery)
                            @if($gallery->type === 'video')
                                <div class="event-gallery-item">
                                    <iframe src="{{ $gallery->path }}" allowfullscreen style="width:100%;height:220px;border-radius:8px;border:none;"></iframe>
                                </div>
                            @else
                                <div class="event-gallery-item" style="background-image:url('{{ asset($gallery->path) }}');background-size:cover;background-position:center;height:220px;border-radius:8px;"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('head')
    <style>
        .event-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 14px;
        }
    </style>
@endpush
