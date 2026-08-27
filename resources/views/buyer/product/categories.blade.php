@extends('layouts.buyer')

@section('title', '{{ $brandModel->name }} - Kategori {{ $type->name }}')

@section('content')
    <section class="section section-category">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">{{ $brandModel->name }}</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">
                    Pilih kategori {{ strtolower($type->name) }} {{ $brandModel->name }} sesuai kebutuhan Anda.
                </p>
            </div>

            <style>
                .product-cat-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                    gap: 20px;
                    margin-top: 40px;
                }

                .product-cat-card {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 8px;
                    padding: 32px 20px;
                    background: #fff;
                    border: 1px solid var(--line);
                    border-radius: 16px;
                    text-align: center;
                    text-decoration: none;
                    color: inherit;
                    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
                }

                .product-cat-card:hover {
                    transform: translateY(-4px);
                    box-shadow: 0 16px 40px rgba(0, 0, 0, .08);
                    border-color: var(--primary, #1d4ed8);
                }

                .product-cat-card strong {
                    font-size: 17px;
                    font-weight: 700;
                }

                .product-cat-count {
                    color: var(--muted);
                    font-size: 13px;
                }

                .product-cat-group-header {
                    grid-column: 1 / -1;
                    padding: 12px 20px;
                    font-size: 12px;
                    font-weight: 700;
                    color: var(--muted);
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    text-align: left;
                    background: var(--panel);
                    border-radius: 12px;
                    border: 1px solid var(--line);
                    margin-top: 8px;
                }
            </style>

            <div class="product-cat-grid">
                @forelse ($categories as $cat)
                    <a class="product-cat-card"
                       href="{{ route('buyer.category-brand', ['categoryType' => $type->slug, 'brand' => $brandModel->slug, 'category' => $cat->slug]) }}">
                        <strong>{{ $cat->name }}</strong>
                        <span class="product-cat-count">{{ $cat->items_count }} {{ strtolower($type->name) }}</span>
                    </a>
                @empty
                    <p style="color:var(--muted);grid-column:1/-1;">Belum ada kategori untuk merk ini.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection