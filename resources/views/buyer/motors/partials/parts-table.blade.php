<table class="part-table">
    <thead>
        <tr>
            <th class="part-table-no">No</th>
            <th class="part-table-name">Part Number</th>
            <th class="part-table-name">Nama Sparepart</th>
            <th class="part-table-price">SRP (Harga Rp)</th>
            <th class="part-table-stock">Status Stok</th>
            <th class="part-table-action">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($compatibleSpareparts ?? [] as $index => $sp)
            @php $spTotalStock = $sp->colors->sum('stock'); @endphp
            <tr>
                <td class="part-table-no">{{ $index + 1 }}</td>
                <td class="part-table-name">{{ $sp->part_number ?? '-' }}</td>
                <td class="part-table-name">{{ $sp->name }}</td>
                <td class="part-table-price">Rp {{ number_format($sp->price ?? 0, 0, ',', '.') }}</td>
                <td class="part-table-stock">
                    <span class="stock-tag {{ $spTotalStock > 0 ? 'ready' : 'indent' }}">{{ $spTotalStock > 0 ? 'Ready Stock' : 'Habis' }}</span>
                </td>
                <td class="part-table-action">
                    @php
                        $firstColor = $sp->colors->first();
                    @endphp
                    @if($firstColor)
                        <form method="post" action="{{ route('buyer.cart.store') }}" style="display:inline;">
                            @csrf
                            <input type="hidden" name="itemable_type" value="item_color">
                            <input type="hidden" name="itemable_id" value="{{ $firstColor->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-sm btn-primary" style="padding:6px 14px;font-size:12px;">
                                + Keranjang
                            </button>
                        </form>
                    @else
                        <span style="color:var(--muted);font-size:12px;">-</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td class="part-table-no" colspan="6">Tidak ada sparepart ditemukan.</td>
            </tr>
        @endforelse
    </tbody>
</table>