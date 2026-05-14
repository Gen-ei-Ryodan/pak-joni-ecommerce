@extends('layouts.admin')

@section('title', 'Banners')

@section('content')
    @include('admin.partials.flash')

    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
        <div style="font-size:16px;font-weight:600;">Banners</div>
        <a class="btn btn-primary" href="{{ route('admin.banners.create') }}">Tambah Banner</a>
    </div>

    <div style="height:14px;"></div>

    <div class="panel" style="padding:10px;">
        <div style="overflow:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:760px;">
                <thead>
                    <tr style="text-align:left;color:var(--muted);font-size:12px;">
                        <th style="padding:10px;">Image</th>
                        <th style="padding:10px;">Title</th>
                        <th style="padding:10px;">Active</th>
                        <th style="padding:10px;">Sort</th>
                        <th style="padding:10px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($banners as $banner)
                        <tr style="border-top:1px solid var(--line);">
                            <td style="padding:10px;">
                                <img src="{{ asset($banner->image_path) }}" alt="" style="width:140px;border-radius:12px;border:1px solid var(--line);">
                            </td>
                            <td style="padding:10px;">{{ $banner->title }}</td>
                            <td style="padding:10px;">{{ $banner->is_active ? 'yes' : 'no' }}</td>
                            <td style="padding:10px;">{{ $banner->sort_order }}</td>
                            <td style="padding:10px;display:flex;gap:8px;align-items:center;">
                                <a class="btn" href="{{ route('admin.banners.edit', $banner) }}">Edit</a>
                                <form method="post" action="{{ route('admin.banners.destroy', $banner) }}" onsubmit="return confirm('Hapus banner?')">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:12px;color:var(--muted);">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="height:12px;"></div>
    {{ $banners->links() }}
@endsection
