@push('head')
<style>
.gallery-main {
    aspect-ratio: 1/1;
    border-radius: 12px;
    border: 1px solid var(--line);
    overflow: hidden;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.gallery-main img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: opacity .15s ease;
}
.gallery-thumbs {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: thin;
}
.gallery-thumbs::-webkit-scrollbar {
    height: 4px;
}
.gallery-thumbs::-webkit-scrollbar-thumb {
    background: var(--line);
    border-radius: 4px;
}
.gallery-thumb {
    flex: 0 0 auto;
    width: 72px;
    height: 72px;
    border-radius: 10px;
    border: 2px solid transparent;
    overflow: hidden;
    cursor: pointer;
    transition: border-color .2s, opacity .2s;
    background: #f0f0f0;
}
.gallery-thumb:hover {
    opacity: .75;
}
.gallery-thumb.active {
    border-color: #d9b46f;
}
.gallery-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>
@endpush

@extends('layouts.buyer')

@section('title', $motor->name)

@section('content')
    <section class="section">
        <div class="container">
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div>
                    <div style="font-size:18px;font-weight:600;">{{ $motor->name }}</div>
                    <div class="muted" style="margin-top:6px;">{{ $motor->year ?? '' }} {{ $motor->short_description ? '— '.$motor->short_description : '' }}</div>
                </div>
                <a class="btn" href="{{ route('buyer.motors.index') }}">Kembali</a>
            </div>

            <div style="height:16px;"></div>

            <div style="display:grid;grid-template-columns:1fr 420px;gap:16px;">
                <div class="panel" style="padding:12px;">
                    <div class="gallery-main">
                        @php
                            $allImages = collect();
                            if ($motor->thumbnail_path) {
                                $allImages->push(['url' => asset($motor->thumbnail_path), 'label' => 'Thumbnail']);
                            }
                            foreach ($motor->images as $img) {
                                $allImages->push(['url' => asset($img->path), 'label' => 'Gallery']);
                            }
                        @endphp
                        @if ($allImages->count())
                            <img id="mainPreview" src="{{ $allImages->first()['url'] }}" alt="">
                        @endif
                    </div>

                    @if ($allImages->count() > 1)
                        <div style="height:10px;"></div>
                        <div class="gallery-thumbs" id="galleryThumbs">
                            @foreach ($allImages as $i => $item)
                                <div class="gallery-thumb {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}" data-src="{{ $item['url'] }}">
                                    <img src="{{ $item['url'] }}" alt="">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="panel" style="padding:14px;">
                    <div style="font-weight:600;">Parts</div>
                    <div style="height:10px;"></div>

                    @if ($parts->isEmpty())
                        <div class="muted">No parts for this motorcycle yet.</div>
                    @else
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        @foreach ($parts->keys() as $idx => $groupKey)
                            <button class="btn {{ $idx === 0 ? 'btn-primary' : '' }}" type="button" data-tab-btn="{{ $groupKey }}">{{ ucfirst($groupKey) }}</button>
                        @endforeach
                    </div>

                    <div style="height:12px;"></div>

                    @foreach ($parts as $groupKey => $groupParts)
                        @php($catList = $categories[$groupKey] ?? collect())
                        <div data-tab-panel="{{ $groupKey }}" style="{{ $loop->first ? '' : 'display:none;' }}">
                            <div class="muted" style="font-size:13px;margin-bottom:10px;">Kategori: {{ $catList->pluck('name')->join(', ') }}</div>

                            <div style="display:grid;gap:10px;">
                                @foreach ($groupParts as $p)
                                        <a class="card" href="{{ route('buyer.parts.show', $p->slug) }}" style="display:flex;gap:12px;align-items:center;">
                                            <div class="card-media" style="width:130px;flex:0 0 130px;aspect-ratio:4/3;background-image:url('{{ $p->thumbnail_path ? asset($p->thumbnail_path) : '' }}');background-size:cover;background-position:center;"></div>
                                            <div class="card-body" style="flex:1;">
                                                <div class="card-title">{{ $p->name }}</div>
                                                <div class="card-meta">{{ $p->category?->name }}</div>
                                                <div class="price">{{ $p->defaultVariant ? number_format((float) $p->defaultVariant->price, 2, '.', ',') : number_format((float) $p->base_price, 2, '.', ',') }}</div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                        </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </section>

@push('scripts')
    <script>
        (function () {
            var thumbs = document.querySelectorAll('.gallery-thumb');
            var mainImg = document.getElementById('mainPreview');

            if (thumbs.length && mainImg) {
                thumbs.forEach(function (el) {
                    el.addEventListener('click', function () {
                        thumbs.forEach(function (t) { t.classList.remove('active'); });
                        el.classList.add('active');
                        mainImg.style.opacity = '0';
                        setTimeout(function () {
                            mainImg.src = el.getAttribute('data-src');
                            mainImg.style.opacity = '1';
                        }, 100);
                    });
                });
            }
        })();
    </script>
    <script>
        (function () {
            const btns = document.querySelectorAll('[data-tab-btn]');
            const panels = document.querySelectorAll('[data-tab-panel]');
            if (!btns.length || !panels.length) return;

            function setTab(key) {
                btns.forEach((b) => {
                    b.classList.add('btn');
                    b.classList.toggle('btn-primary', b.dataset.tabBtn === key);
                });
                panels.forEach((p) => {
                    p.style.display = p.dataset.tabPanel === key ? '' : 'none';
                });
            }

            btns.forEach((b) => b.addEventListener('click', () => setTab(b.dataset.tabBtn)));
        })();
    </script>
@endpush
@endsection
