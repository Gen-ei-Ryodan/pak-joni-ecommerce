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
                    <div style="aspect-ratio:16/10;border-radius:12px;border:1px solid var(--line);overflow:hidden;background:rgba(255,255,255,0.03);">
                        @if ($motor->thumbnail_path)
                            <img src="{{ asset($motor->thumbnail_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                        @endif
                    </div>

                    @if ($motor->images->count())
                        <div style="height:10px;"></div>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;">
                            @foreach ($motor->images as $img)
                                <img src="{{ asset($img->path) }}" alt="" style="width:120px;border-radius:12px;border:1px solid var(--line);">
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
