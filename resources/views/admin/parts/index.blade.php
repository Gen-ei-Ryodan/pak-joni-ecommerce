@extends('layouts.admin')

@section('title', 'Parts')

@section('content')
    @include('admin.partials.flash')

    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
        <div style="font-size:16px;font-weight:600;">Parts</div>
        <a class="btn btn-primary" href="{{ route('admin.parts.create') }}">Tambah Part</a>
    </div>

    <div style="height:12px;"></div>

    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <input name="q" value="{{ $q }}" placeholder="Search part / SKU..." style="flex:1;min-width:180px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
        <select name="category" style="min-width:200px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
            <option value="">All Categories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected((string) $categoryId === (string) $cat->id)>{{ $cat->group }} — {{ $cat->name }}</option>
            @endforeach
        </select>
        <button class="btn" type="submit">Filter</button>
    </form>

    <div style="height:14px;"></div>

    <div class="panel" style="padding:10px;">
        <div style="overflow:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:960px;">
                <thead>
                    <tr style="text-align:left;color:var(--muted);font-size:12px;">
                        <th style="padding:10px;">Thumb</th>
                        <th style="padding:10px;">SKU</th>
                        <th style="padding:10px;">Name</th>
                        <th style="padding:10px;">Category</th>
                        <th style="padding:10px;">Status</th>
                        <th style="padding:10px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($parts as $part)
                        <tr style="border-top:1px solid var(--line);">
                            <td style="padding:10px;">
                                @if($part->thumbnail_path)
                                    <img src="{{ asset($part->thumbnail_path) }}" alt="" style="width:80px;height:56px;object-fit:cover;border-radius:8px;border:1px solid var(--line);">
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td style="padding:10px;">{{ $part->sku }}</td>
                            <td style="padding:10px;">{{ $part->name }}</td>
                            <td style="padding:10px;color:var(--muted);">{{ $part->category?->group }} — {{ $part->category?->name }}</td>
                            <td style="padding:10px;">{{ $part->status }}</td>
                            <td style="padding:10px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                <a class="btn" href="{{ route('buyer.parts.show', $part->slug) }}" target="_blank">Preview</a>
                                <a class="btn" href="{{ route('admin.parts.edit', $part) }}">Edit</a>
                                <form method="post" action="{{ route('admin.parts.destroy', $part) }}" onsubmit="return confirm('Hapus part?')">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:12px;color:var(--muted);">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="height:12px;"></div>
    {{ $parts->links() }}
@endsection
