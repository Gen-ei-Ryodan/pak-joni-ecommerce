@forelse ($items as $item)
    @php
        $totalStock = $item->colors->sum('stock');
    @endphp
    <div class="card motor-card">
        <a class="card-media-link" href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug]) }}" style="display:block;text-decoration:none;">
            <div class="card-media" style="background-image:url('{{ $item->thumbnail_path ? image_url($item->thumbnail_path) : '' }}');background-size:cover;background-position:center;height:220px;"></div>
        </a>
        <div class="card-body">
            @if($item->brand)
                <div class="card-meta">{{ $item->brand->name }}</div>
            @endif
            <a href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug]) }}" style="text-decoration:none;color:inherit;">
                <div class="card-title">{{ $item->name }}</div>
            </a>
            @if($item->price)
                <div class="price">Rp {{ number_format($item->price, 0, ',', '.') }}</div>
            @endif
            @if($item->stock_status === 'indent')
                <span class="stock-badge indent">Indent</span>
            @elseif($item->stock_status === 'ready')
                <span class="stock-badge ready">Ready Stock</span>
                @if($totalStock > 0)
                    <span class="stock-badge ready">({{ $totalStock }} unit)</span>
                @else
                    <span class="stock-badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">(Habis)</span>
                @endif
                <span class="stock-badge otr">OTR SURABAYA</span>
            @endif
        </div>
        <div class="card-actions">
            <a href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug]) }}" class="card-action-btn primary">Lihat {{ $type->name }}</a>
            <a href="{{ route('buyer.motors.show', ['categoryType' => $item->type->slug, 'slug' => $item->slug, 'tab' => 'parts']) }}" class="card-action-btn">Sparepart</a>
        </div>
    </div>
@empty
    <div class="empty-state">Belum ada {{ $type->name }} tersedia {{ $brandModel ? 'untuk brand ini' : '' }}.</div>
@endforelse
