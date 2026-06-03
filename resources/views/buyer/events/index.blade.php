@extends('layouts.buyer')

@section('title', 'Acara')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Acara & Event</h2>
                <div class="section-line center-line"></div>
            </div>

            <div class="grid grid-3">
                @forelse($events as $event)
                    <a class="card" href="{{ route('buyer.events.show', $event->slug) }}">
                        <div class="card-media" style="background-image:url('{{ $event->thumbnail_path ? image_url($event->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:200px;"></div>
                        <div class="card-body">
                            <div class="card-meta">{{ $event->event_date?->format('d M Y') }} @if($event->location) &middot; {{ $event->location }} @endif</div>
                            <div class="card-title">{{ $event->title }}</div>
                            <div class="card-meta" style="margin-top:6px;">{{ \Illuminate\Support\Str::limit($event->description, 100) }}</div>
                        </div>
                    </a>
                @empty
                    <div class="muted" style="text-align:center;grid-column:1/-1;padding:60px 0;">Belum ada acara.</div>
                @endforelse
            </div>

            <div style="margin-top:30px;">
                {{ $events->links('pagination.simple-dark') }}
            </div>
        </div>
    </section>
@endsection
