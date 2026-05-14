@extends('layouts.buyer')

@section('title', 'Addresses')

@section('content')
    <section class="section">
        <div class="container">
            @if (session('status'))
                <div class="panel" style="padding:10px 12px;margin-bottom:12px;border-color:rgba(217,180,111,0.35);background:rgba(217,180,111,0.08);">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="panel" style="padding:10px 12px;margin-bottom:12px;border-color:rgba(255,77,77,0.35);background:rgba(255,77,77,0.08);">
                    <div style="display:grid;gap:6px;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                <div style="font-size:18px;font-weight:600;">Addresses</div>
                <a class="btn btn-primary" href="{{ route('buyer.addresses.create') }}">Add Address</a>
            </div>

            <div style="height:14px;"></div>

            <div style="display:grid;gap:12px;">
                @forelse ($addresses as $a)
                    <div class="panel" style="padding:14px;">
                        <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:start;">
                            <div>
                                <div style="font-weight:600;">
                                    {{ $a->label ?: 'Alamat' }}
                                    @if ($a->is_default)
                                        <span class="muted" style="margin-left:10px;">(default)</span>
                                    @endif
                                </div>
                                <div class="muted" style="margin-top:8px;line-height:1.8;">
                                    {{ $a->recipient_name }} — {{ $a->phone }}<br>
                                    {{ $a->address_line1 }} {{ $a->address_line2 }}<br>
                                    {{ $a->city }}, {{ $a->province }} {{ $a->postal_code }}
                                </div>
                            </div>
                            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                <a class="btn" href="{{ route('buyer.addresses.edit', $a) }}">Edit</a>
                                <form method="post" action="{{ route('buyer.addresses.destroy', $a) }}" onsubmit="return confirm('Hapus alamat?')">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="muted">No addresses yet.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
