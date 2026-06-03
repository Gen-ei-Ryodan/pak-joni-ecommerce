@extends('layouts.buyer')

@section('title', $activity->title)

@section('content')
    <section class="section">
        <div class="container" style="max-width:900px;">
            <a href="{{ route('buyer.internal-activities.index') }}" class="back-link" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);text-decoration:none;margin-bottom:20px;">
                &#8592; Kembali ke Kegiatan Internal
            </a>

            @if($activity->thumbnail_path)
                <div style="width:100%;height:350px;border-radius:var(--radius);background:url('{{ image_url($activity->thumbnail_path) }}') center/cover no-repeat;margin-bottom:24px;"></div>
            @endif

            <div class="article-meta" style="font-size:12px;color:var(--muted);margin-bottom:16px;">
                {{ $activity->publish_date?->format('d M Y') }}
            </div>

            <h1 style="font-size:clamp(22px,4vw,32px);font-weight:700;margin-bottom:20px;">{{ $activity->title }}</h1>

            @if($activity->content)
                <div class="article-content" style="color:var(--muted);line-height:1.9;font-size:15px;">
                    {!! $activity->content !!}
                </div>
            @endif

            @if($activity->galleries->count())
                <div style="margin-top:30px;">
                    <h3 style="font-size:18px;font-weight:600;margin-bottom:16px;">Galeri Dokumentasi</h3>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;">
                        @foreach($activity->galleries as $gallery)
                            <div style="background-image:url('{{ image_url($gallery->path) }}');background-size:cover;background-position:center;height:200px;border-radius:8px;"></div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
