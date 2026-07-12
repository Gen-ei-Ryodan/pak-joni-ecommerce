@extends('layouts.admin')

@section('title', 'Motors')

@section('content')
    @include('admin.partials.flash')

    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
        <div style="font-size:16px;font-weight:600;">Motor</div>
        <a class="btn btn-primary" href="{{ route('admin.motors.create') }}">Add Motor</a>
    </div>

    <div style="height:12px;"></div>

    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;">
        <input name="q" value="{{ $q }}" placeholder="Search motor..." style="flex:1;min-width:220px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
        <button class="btn" type="submit">Filter</button>
    </form>

    <div style="height:14px;"></div>

    <div class="panel" style="padding:10px;">
        <div style="overflow:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:860px;">
                <thead>
                    <tr style="text-align:left;color:var(--muted);font-size:12px;">
                        <th style="padding:10px;">Thumb</th>
                        <th style="padding:10px;">Name</th>
                        <th style="padding:10px;">Year</th>
                        <th style="padding:10px;">Status</th>
                        <th style="padding:10px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($motors as $motor)
                        <tr style="border-top:1px solid var(--line);">
                            <td style="padding:10px;">
                                @if($motor->thumbnail_path)
                                    <img src="{{ asset($motor->thumbnail_path) }}" alt="" style="width:80px;height:56px;object-fit:cover;border-radius:8px;border:1px solid var(--line);">
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td style="padding:10px;">{{ $motor->name }}</td>
                            <td style="padding:10px;">{{ $motor->year ?? '-' }}</td>
                            <td style="padding:10px;">{{ $motor->status }}</td>
                            <td style="padding:10px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                <a class="btn" href="{{ route('buyer.motors.show', ['categoryType' => $motor->type->slug ?? 'motor', 'slug' => $motor->slug]) }}" target="_blank">Preview</a>
                                <a class="btn" href="{{ route('admin.motors.edit', $motor) }}">Edit</a>
                                <form method="post" action="{{ route('admin.motors.destroy', $motor) }}" onsubmit="return confirm('Hapus motor?')">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:12px;color:var(--muted);">No data yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="height:12px;"></div>
    {{ $motors->links() }}
@endsection
