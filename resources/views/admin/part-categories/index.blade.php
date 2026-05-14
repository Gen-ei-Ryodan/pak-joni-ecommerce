@extends('layouts.admin')

@section('title', 'Part Categories')

@section('content')
    @include('admin.partials.flash')

    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
        <div style="font-size:16px;font-weight:600;">Part Categories</div>
        <a class="btn btn-primary" href="{{ route('admin.part-categories.create') }}">Tambah Category</a>
    </div>

    <div style="height:12px;"></div>

    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;">
        <input name="q" value="{{ $q }}" placeholder="Search category..." style="flex:1;min-width:220px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
        <button class="btn" type="submit">Filter</button>
    </form>

    <div style="height:14px;"></div>

    <div class="panel" style="padding:10px;">
        <div style="overflow:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:720px;">
                <thead>
                    <tr style="text-align:left;color:var(--muted);font-size:12px;">
                        <th style="padding:10px;">Group</th>
                        <th style="padding:10px;">Name</th>
                        <th style="padding:10px;">Slug</th>
                        <th style="padding:10px;">Sort</th>
                        <th style="padding:10px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $cat)
                        <tr style="border-top:1px solid var(--line);">
                            <td style="padding:10px;">{{ $cat->group }}</td>
                            <td style="padding:10px;">{{ $cat->name }}</td>
                            <td style="padding:10px;color:var(--muted);">{{ $cat->slug }}</td>
                            <td style="padding:10px;">{{ $cat->sort_order }}</td>
                            <td style="padding:10px;display:flex;gap:8px;align-items:center;">
                                <a class="btn" href="{{ route('admin.part-categories.edit', $cat) }}">Edit</a>
                                <form method="post" action="{{ route('admin.part-categories.destroy', $cat) }}" onsubmit="return confirm('Hapus category?')">
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
    {{ $categories->links() }}
@endsection
