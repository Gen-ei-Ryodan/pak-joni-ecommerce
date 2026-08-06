<div style="max-height: 500px; overflow-y: auto;">
    <div style="margin-bottom: 16px; padding: 12px; background: rgba(59, 130, 246, 0.1); border-radius: 8px;">
        <strong>{{ $item->name }}</strong>
        <div style="font-size: 14px; color: var(--muted); margin-top: 4px;">
            Stok saat ini: <strong>{{ $item->stock }}</strong>
        </div>
    </div>

    @if($mutations->isEmpty())
        <div style="text-align: center; padding: 40px; color: var(--muted);">
            Belum ada riwayat perubahan stok
        </div>
    @else
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--line);">
                    <th style="padding: 8px; text-align: left; font-size: 12px; color: var(--muted);">Tanggal</th>
                    <th style="padding: 8px; text-align: left; font-size: 12px; color: var(--muted);">Perubahan</th>
                    <th style="padding: 8px; text-align: left; font-size: 12px; color: var(--muted);">Stok Awal</th>
                    <th style="padding: 8px; text-align: left; font-size: 12px; color: var(--muted);">Stok Akhir</th>
                    <th style="padding: 8px; text-align: left; font-size: 12px; color: var(--muted);">Tipe</th>
                    <th style="padding: 8px; text-align: left; font-size: 12px; color: var(--muted);">User</th>
                    <th style="padding: 8px; text-align: left; font-size: 12px; color: var(--muted);">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mutations as $mutation)
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding: 8px; font-size: 13px;">
                            {{ $mutation->created_at->format('d M Y H:i') }}
                        </td>
                        <td style="padding: 8px; font-size: 13px; font-weight: 600; color: {{ $mutation->quantity > 0 ? '#22c55e' : '#ef4444' }};">
                            {{ $mutation->quantity > 0 ? '+' : '' }}{{ $mutation->quantity }}
                        </td>
                        <td style="padding: 8px; font-size: 13px;">
                            {{ $mutation->previous_stock }}
                        </td>
                        <td style="padding: 8px; font-size: 13px; font-weight: 600;">
                            {{ $mutation->current_stock }}
                        </td>
                        <td style="padding: 8px; font-size: 13px;">
                            <span style="padding: 2px 8px; border-radius: 12px; font-size: 11px; background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                                {{ $mutation->typeLabel() }}
                            </span>
                        </td>
                        <td style="padding: 8px; font-size: 13px;">
                            {{ $mutation->user ? $mutation->user->name : 'System' }}
                        </td>
                        <td style="padding: 8px; font-size: 13px; color: var(--muted); max-width: 200px;">
                            {{ $mutation->notes ?: '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
