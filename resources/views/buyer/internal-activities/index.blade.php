@extends('layouts.buyer')

@section('title', 'Kegiatan Internal')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-header center">
                <h2 class="section-title-text">Kegiatan Internal</h2>
                <div class="section-line center-line"></div>
                <p style="color:var(--muted);max-width:600px;margin:12px auto 0;">Dokumentasi kegiatan dan aktivitas internal perusahaan.</p>
            </div>

            <div class="grid grid-3">
                @forelse($activities as $activity)
                    <a class="card" href="{{ route('buyer.internal-activities.show', $activity->slug) }}">
                        <div class="card-media" style="background-image:url('{{ $activity->thumbnail_path ? asset($activity->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:200px;"></div>
                        <div class="card-body">
                            <div class="card-meta">{{ $activity->publish_date?->format('d M Y') }}</div>
                            <div class="card-title">{{ $activity->title }}</div>
                            <div class="card-meta" style="margin-top:6px;">{{ \Illuminate\Support\Str::limit(strip_tags($activity->content ?? ''), 100) }}</div>
                        </div>
                    </a>
                @empty
                    <div class="muted" style="text-align:center;grid-column:1/-1;padding:60px 0;">Belum ada kegiatan.</div>
                @endforelse
            </div>

            <div style="margin-top:30px;">
                {{ $activities->links('pagination.simple-dark') }}
            </div>
        </div>
    </section>
@endsection
