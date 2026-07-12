@extends('layouts.buyer')

@section('title', 'Karir')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Karir</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">Bergabunglah bersama tim {{ config('app.name') }} dan kembangkan karir Anda bersama kami.</p>
            </div>

            <div class="grid grid-2" style="max-width:900px;margin:0 auto;">
                @forelse($careers as $career)
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
                            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:8px;">
                                <span class="career-status" style="display:inline-block;padding:4px 10px;font-size:10px;font-weight:600;letter-spacing:0.5px;text-transform:uppercase;border-radius:4px;background:rgba(217,180,111,0.15);color:var(--accent);">Lowongan Aktif</span>
                                @if($career->display_end_date)
                                    <span style="display:inline-block;padding:4px 10px;font-size:10px;font-weight:500;border-radius:4px;background:rgba(100,100,100,0.1);color:var(--muted);">
                                        Berakhir {{ $career->display_end_date->format('d M Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @if($career->slug)
                        </a>
                    @else
                        </div>
                    @endif
                @empty
                    <div class="muted" style="text-align:center;grid-column:1/-1;padding:60px 0;">Belum ada lowongan tersedia saat ini.</div>
                @endforelse
            </div>

            <div style="margin-top:30px;">
                {{ $careers->links('pagination.simple-dark') }}
            </div>
        </div>
    </section>
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
