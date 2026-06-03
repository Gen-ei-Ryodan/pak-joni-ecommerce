@extends('layouts.buyer')

@section('title', 'CSR')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Tanggung Jawab Sosial Perusahaan</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">Komitmen kami dalam memberikan dampak positif bagi masyarakat dan lingkungan.</p>
            </div>

            <div class="grid grid-3">
                @forelse($articles as $article)
                    <a class="card" href="{{ route('buyer.csr.show', $article->slug) }}">
                        <div class="card-media" style="background-image:url('{{ $article->thumbnail_path ? image_url($article->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:200px;"></div>
                        <div class="card-body">
                            <div class="card-meta">{{ $article->publish_date?->format('d M Y') }}</div>
                            <div class="card-title">{{ $article->title }}</div>
                            <div class="card-meta" style="margin-top:6px;">{{ \Illuminate\Support\Str::limit(strip_tags($article->content), 100) }}</div>
                        </div>
                    </a>
                @empty
                    <div class="muted" style="text-align:center;grid-column:1/-1;padding:60px 0;">Belum ada artikel CSR.</div>
                @endforelse
            </div>

            <div style="margin-top:30px;">
                {{ $articles->links('pagination.simple-dark') }}
            </div>
        </div>
    </section>
@endsection
