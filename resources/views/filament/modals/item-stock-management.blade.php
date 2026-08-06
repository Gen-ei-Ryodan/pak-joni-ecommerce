<div style="max-height: 600px; overflow-y: auto;">
    <div style="margin-bottom: 16px; padding: 12px; background: rgba(59, 130, 246, 0.1); border-radius: 8px;">
        <strong>{{ $item->name }}</strong>
        <div style="font-size: 14px; color: var(--muted); margin-top: 4px;">
            Total Stok: <strong>{{ $item->colors->sum('stock') }}</strong> dari {{ $item->colors->count() }} varian
        </div>
    </div>

    @if($item->colors->isEmpty())
        <div style="text-align: center; padding: 40px; color: var(--muted);">
            Belum ada varian warna. Tambahkan varian di form edit untuk mengelola stok.
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @foreach($item->colors as $color)
                <div style="padding: 16px; border: 1px solid var(--line); border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        @if($color->color_code)
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $color->color_code }}; border: 2px solid var(--line); flex-shrink: 0;"></div>
                        @endif
                        <div style="flex: 1;">
                            <strong>{{ $color->name }}</strong>
                            <div style="font-size: 12px; color: var(--muted);">
                                Stok: <strong style="color: {{ $color->stock > 0 ? '#22c55e' : '#ef4444' }}">{{ $color->stock }}</strong>
                                @if($color->stock_updated_at)
                                    &middot; Diperbarui: {{ $color->stock_updated_at->format('d M Y H:i') }}
                                @endif
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 24px; font-weight: 700; color: {{ $color->stock > 0 ? '#22c55e' : '#ef4444' }};">
                                {{ $color->stock }}
                            </div>
                            <div style="font-size: 11px; color: var(--muted);">unit</div>
                        </div>
                    </div>

                    @if($color->stockMutations()->count() > 0)
                        <details style="margin-top: 12px;">
                            <summary style="cursor: pointer; font-size: 12px; color: var(--muted);">Lihat riwayat ({{ $color->stockMutations()->count() }} perubahan)</summary>
                            <div style="margin-top: 8px; font-size: 12px;">
                                @foreach($color->stockMutations()->orderByDesc('created_at')->limit(5)->get() as $mutation)
                                    <div style="padding: 4px 0; border-bottom: 1px solid var(--line);">
                                        <span style="color: {{ $mutation->quantity > 0 ? '#22c55e' : '#ef4444' }}; font-weight: 600;">
                                            {{ $mutation->quantity > 0 ? '+' : '' }}{{ $mutation->quantity }}
                                        </span>
                                        ({{ $mutation->previous_stock }} → {{ $mutation->current_stock }})
                                        @if($mutation->notes)
                                            &middot; {{ $mutation->notes }}
                                        @endif
                                        &middot; {{ $mutation->created_at->format('d M Y H:i') }}
                                        @if($mutation->user)
                                            &middot; {{ $mutation->user->name }}
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endif
                </div>
            @endforeach
        </div>

        <div style="margin-top: 16px; padding: 12px; background: rgba(234, 179, 8, 0.1); border-radius: 8px; font-size: 13px;">
            <strong>Cara mengubah stok:</strong> Edit item ini, lalu ubah nilai "Stok" pada masing-masing varian warna di section "Varian Warna & Stok".
        </div>
    @endif
</div>
