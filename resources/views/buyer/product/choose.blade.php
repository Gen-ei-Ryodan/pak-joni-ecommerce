@extends('layouts.buyer')

@section('title', 'Pilih Merk {{ $type->name }}')

@section('content')
    <section class="section section-category">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Pilih Merk</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">
                    Pilih merk {{ strtolower($type->name) }} favorit Anda.
                </p>
            </div>

            <style>
                .product-brand-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                    gap: 20px;
                    margin-top: 40px;
                }

                .product-brand-card {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 14px;
                    padding: 32px 20px;
                    background: #fff;
                    border: 1px solid var(--line);
                    border-radius: 16px;
                    text-align: center;
                    text-decoration: none;
                    color: inherit;
                    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
                }

                .product-brand-card:hover {
                    transform: translateY(-4px);
                    box-shadow: 0 16px 40px rgba(0, 0, 0, .08);
                    border-color: var(--primary, #1d4ed8);
                }

                .product-brand-logo {
                    width: 88px;
                    height: 88px;
                    display: grid;
                    place-items: center;
                    border-radius: 50%;
                    background: #f4f6fb;
                    overflow: hidden;
                }

                .product-brand-logo img {
                    max-width: 56px;
                    max-height: 56px;
                    object-fit: contain;
                }

                .product-brand-initial {
                    font-size: 30px;
                    font-weight: 800;
                    color: var(--primary, #1d4ed8);
                }

                .product-brand-card strong {
                    font-size: 17px;
                    font-weight: 700;
                }

                .product-brand-count {
                    color: var(--muted);
                    font-size: 13px;
                }
            </style>

            <div class="product-brand-grid">
                @forelse ($brands as $brand)
                    <a class="product-brand-card"
                       href="{{ route('buyer.product.categories', ['categoryType' => $type->slug, 'brand' => $brand->slug]) }}">
                        <span class="product-brand-logo">
                            @if($brand->logo_path)
                                <img src="{{ image_url($brand->logo_path) }}" alt="{{ $brand->name }}">
                            @else
                                <span class="product-brand-initial">{{ mb_substr($brand->name, 0, 1) }}</span>
                            @endif
                        </span>
                        <strong>{{ $brand->name }}</strong>
                        <span class="product-brand-count">{{ $brand->items_count ?? $brand->parts_count ?? 0 }} {{ strtolower($type->name) }}</span>
                    </a>
                @empty
                    <p style="color:var(--muted);">Belum ada merk tersedia.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection