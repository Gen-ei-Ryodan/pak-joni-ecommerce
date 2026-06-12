@extends('layouts.buyer')

@section('title', 'Tentang Kami')

@push('head')
    <style>
        .about-page { color: var(--text); }
        .about-banner {
            min-height: 300px;
            background: linear-gradient(135deg, #1a1a2e 0%, #2d2d44 50%, #3d3d5c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .about-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 20%, rgba(217,180,111,0.08), transparent 50%);
        }
        .about-banner-content { position: relative; z-index: 1; }
        .about-banner-title { font-size: 36px; font-weight: 700; margin-bottom: 8px; color: #fff; }
        .about-banner-sub { font-size: 14px; opacity: 0.7; color: rgba(255,255,255,0.7); }
        .about-grid { max-width: 900px; margin: 0 auto; }
        .about-section { margin-top: 40px; }
        .about-section h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 16px;
            letter-spacing: -0.01em;
        }
        .about-section p {
            color: var(--text);
            line-height: 1.9;
            font-size: 14px;
            text-align: justify;
            margin-bottom: 16px;
        }
        .about-section ul {
            color: var(--text);
            line-height: 1.9;
            font-size: 14px;
            padding-left: 24px;
            margin-bottom: 16px;
        }
        .about-section ul li {
            margin-bottom: 6px;
        }
        .about-values {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }
        .about-value-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 24px;
            text-align: center;
        }
        .about-value-card h4 { font-size: 16px; font-weight: 600; margin-bottom: 8px; color: var(--text); }
        .about-value-card p { font-size: 13px; color: var(--muted); line-height: 1.6; }
    </style>
@endpush

@section('content')
    <div class="about-page">
        <div class="about-banner">
            <div class="about-banner-content">
                <div class="about-banner-title">Tentang JOMOTO</div>
                <div class="about-banner-sub">Dealer resmi motor premium Indonesia</div>
            </div>
        </div>

        <div class="section">
            <div class="container about-grid">
                @if(!empty($profile) && isset($profile['sejarah']) && $profile['sejarah'])
                    <div class="about-section">
                        <h3>Sejarah Perusahaan</h3>
                        <p>{!! $profile['sejarah'] !!}</p>
                    </div>
                @endif

                @if(!empty($profile) && isset($profile['visi']) && $profile['visi'])
                    <div class="about-section">
                        <h3>Visi</h3>
                        <p>{!! $profile['visi'] !!}</p>
                    </div>
                @endif

                @if(!empty($profile) && isset($profile['misi']) && $profile['misi'])
                    <div class="about-section">
                        <h3>Misi</h3>
                        <p>{!! $profile['misi'] !!}</p>
                    </div>
                @endif

                @if(!empty($profile) && isset($profile['nilai']) && $profile['nilai'])
                    <div class="about-section">
                        <h3>Nilai Perusahaan</h3>
                        <div class="about-values">
                            @php $nilaiList = explode("\n", strip_tags($profile['nilai'])); @endphp
                            @foreach($nilaiList as $index => $nilai)
                                @if(trim($nilai))
                                    <div class="about-value-card">
                                        <h4>{{ trim($nilai) }}</h4>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="about-section" style="margin-top:50px;padding-top:30px;border-top:1px solid var(--line);text-align:center;color:var(--muted);">
                    <p>
                JOMOTO adalah dealer resmi untuk brand-brand motor premium: WMOTO, SM SPORT, CFMOTO, ZONTES, dan ZEEHO.
                Kami berkomitmen menyediakan produk berkualitas, suku cadang asli, dan layanan purna jual terbaik untuk pelanggan di seluruh Indonesia.
            </p>
                </div>
            </div>
        </div>
    </div>
@endsection
