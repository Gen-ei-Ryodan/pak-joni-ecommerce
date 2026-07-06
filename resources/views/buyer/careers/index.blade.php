@extends('layouts.buyer')

@section('title', 'Karir')

@section('content')
    @if($careers->count())
        <section class="section">
            <div class="container">
                <div class="section-header center">
                    <h2 class="section-title-text">Karir</h2>
                    <div class="section-line center-line"></div>
                    <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">Bergabunglah bersama tim {{ config('app.name') }} dan kembangkan karir Anda bersama kami.</p>
                </div>

                <div class="grid grid-2" style="max-width:900px;margin:0 auto;">
                    @foreach($careers as $career)
                        @if($career->slug)
                            <a class="card" href="{{ route('buyer.careers.show', $career->slug) }}" style="text-decoration:none;">
                        @else
                            <div class="card" style="cursor:default;">
                        @endif
                            <div class="card-body">
                                <div class="card-meta">{{ $career->publish_date?->format('d M Y') }}</div>
                                <div class="card-title" style="font-size:16px;">{{ $career->title }}</div>
                                @if($career->location)
                                    <div class="card-meta" style="margin-top:4px;">&#x1F4CD; {{ $career->location }}</div>
                                @endif
                                <span class="career-status" style="display:inline-block;margin-top:8px;padding:4px 10px;font-size:10px;font-weight:600;letter-spacing:0.5px;text-transform:uppercase;border-radius:4px;background:rgba(217,180,111,0.15);color:var(--accent);">Lowongan Aktif</span>
                            </div>
                        @if($career->slug)
                            </a>
                        @else
                            </div>
                        @endif
                    @endforeach
                </div>

                <div style="margin-top:30px;">
                    {{ $careers->links('pagination.simple-dark') }}
                </div>
            </div>
        </section>
    @else
        <section class="section">
            <div class="container" style="text-align:center;padding:80px 0;">
                <h2 class="section-title-text">Karir</h2>
                <div class="section-line center-line" style="margin:12px auto 24px;"></div>
                <p style="color:var(--muted);">Belum ada lowongan tersedia saat ini. Silakan cek kembali nanti.</p>
            </div>
        </section>
    @endif
@endsection

@push('head')
    <style>
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }
        @media (max-width: 600px) {
            .grid-2 { grid-template-columns: 1fr; }
        }
    </style>
@endpush
