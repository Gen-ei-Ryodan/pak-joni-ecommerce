@extends('layouts.buyer')

@section('title', 'Showroom')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Showroom {{ config('app.name') }}</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:0 auto;">Lihat koleksi produk dan suasana showroom kami</p>
            </div>

            @if($images->count())
                <div class="showroom-grid">
                    @foreach($images as $image)
                        <div class="showroom-item">
                            <a href="{{ image_url($image->image_path) }}" target="_blank" rel="noopener">
                                <div class="showroom-image" style="background-image:url('{{ image_url($image->image_path) }}');background-size:cover;background-position:center;"></div>
                            </a>
                            @if($image->caption)
                                <div class="showroom-caption">{{ $image->caption }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align:center;padding:60px 0;color:var(--muted);">
                    <p>Belum ada foto showroom. Silakan kembali lagi nanti.</p>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('head')
    <style>
        .showroom-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }
        .showroom-item {
            border-radius: var(--radius);
            overflow: hidden;
            border: 1px solid var(--line);
            background: var(--panel);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .showroom-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }
        .showroom-image {
            width: 100%;
            height: 240px;
            transition: transform 0.3s ease;
        }
        .showroom-item:hover .showroom-image {
            transform: scale(1.03);
        }
        .showroom-caption {
            padding: 10px 14px;
            font-size: 13px;
            color: var(--muted);
        }
    </style>
@endpush
