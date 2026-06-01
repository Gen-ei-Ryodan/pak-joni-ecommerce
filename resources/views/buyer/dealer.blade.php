@extends('layouts.buyer')

@section('title', 'Diler')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Diler Resmi MOTOMART</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">Temukan diler resmi MOTOMART terdekat di kota Anda.</p>
            </div>

            <form class="dealer-search" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:30px;">
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama dealer, alamat, atau kota..." class="form-input" style="flex:1;min-width:200px;">
                <select name="province" class="form-input" style="max-width:180px;" onchange="this.form.submit()">
                    <option value="">Semua Provinsi</option>
                    @foreach($provinces as $p)
                        <option value="{{ $p }}" {{ $province === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
                <select name="city" class="form-input" style="max-width:180px;" onchange="this.form.submit()">
                    <option value="">Semua Kota</option>
                    @foreach($cities as $c)
                        <option value="{{ $c }}" {{ $city === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-accent">Cari</button>
            </form>

            <div class="grid grid-3">
                @forelse($dealers as $dealer)
                    <div class="dealer-card">
                        <h3 class="dealer-name">{{ $dealer->name }}</h3>
                        <p class="dealer-address">{{ $dealer->address }}, {{ $dealer->city }}, {{ $dealer->province }}</p>
                        <div class="dealer-contacts">
                            @if($dealer->phone)
                                <a href="tel:{{ $dealer->phone }}" class="dealer-contact-item">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    {{ $dealer->phone }}
                                </a>
                            @endif
                            @if($dealer->whatsapp)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $dealer->whatsapp) }}" target="_blank" rel="noopener" class="dealer-contact-item dealer-wa">WhatsApp</a>
                            @endif
                            @if($dealer->email)
                                <a href="mailto:{{ $dealer->email }}" class="dealer-contact-item">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    {{ $dealer->email }}
                                </a>
                            @endif
                        </div>
                        @if($dealer->google_maps_url)
                            <a href="{{ $dealer->google_maps_url }}" target="_blank" rel="noopener" class="btn btn-outline" style="margin-top:12px;width:100%;justify-content:center;">Lihat di Google Maps</a>
                        @endif
                    </div>
                @empty
                    <div class="muted" style="text-align:center;grid-column:1/-1;padding:60px 0;">Dealer tidak ditemukan.</div>
                @endforelse
            </div>

            <div style="margin-top:30px;">
                {{ $dealers->appends(['q' => $q, 'province' => $province, 'city' => $city])->links('pagination.simple-dark') }}
            </div>
        </div>
    </section>
@endsection

@push('head')
    <style>
        .dealer-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 24px;
            transition: all 0.2s ease;
        }
        .dealer-card:hover {
            border-color: var(--accent);
        }
        .dealer-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .dealer-address {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 14px;
        }
        .dealer-contacts {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .dealer-contact-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--muted);
            text-decoration: none;
            padding: 6px 12px;
            border: 1px solid var(--line);
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        .dealer-contact-item:hover {
            border-color: var(--accent);
            color: var(--accent);
        }
        .dealer-wa {
            color: #25D366;
            border-color: rgba(37, 211, 102, 0.3);
        }
        .dealer-wa:hover {
            border-color: #25D366;
            color: #25D366;
            background: rgba(37, 211, 102, 0.1);
        }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--line);
            border-radius: 10px;
            font-size: 14px;
            color: var(--text);
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s ease;
            box-sizing: border-box;
        }
        .form-input:focus {
            border-color: var(--accent);
        }
    </style>
@endpush
