<div style="display:grid;gap:12px;">
    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Name</label>
        <input name="name" value="{{ old('name', $motor->name ?? '') }}" required
            style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Year</label>
            <input name="year" type="number" value="{{ old('year', $motor->year ?? '') }}"
                style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
        </div>
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Status</label>
            <select name="status" required
                style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                @php($val = old('status', $motor->status ?? 'published'))
                <option value="published" @selected($val === 'published')>published</option>
                <option value="draft" @selected($val === 'draft')>draft</option>
            </select>
        </div>
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Short Description</label>
        <input name="short_description" value="{{ old('short_description', $motor->short_description ?? '') }}"
            style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Description</label>
        <textarea name="description" rows="6"
            style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);resize:vertical;">{{ old('description', $motor->description ?? '') }}</textarea>
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Thumbnail</label>
        <input name="thumbnail" type="file" accept="image/*">
        @if (!empty($motor?->thumbnail_path))
            <div style="height:10px;"></div>
            <img src="{{ asset($motor->thumbnail_path) }}" alt="" style="width:180px;border-radius:12px;border:1px solid var(--line);">
        @endif
    </div>

    @if (!empty($motor?->images) && $motor->images->count())
        <div>
            <div style="color:var(--muted);font-size:12px;margin-bottom:8px;">Gallery Existing (centang untuk hapus)</div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                @foreach ($motor->images as $img)
                    <label style="display:grid;gap:6px;align-items:start;">
                        <img src="{{ asset($img->path) }}" alt="" style="width:140px;border-radius:12px;border:1px solid var(--line);">
                        <div style="display:flex;gap:8px;align-items:center;color:var(--muted);font-size:12px;">
                            <input type="checkbox" name="delete_images[]" value="{{ $img->id }}">
                            <span>hapus</span>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Tambah Gallery Images</label>
        <input name="gallery[]" type="file" accept="image/*" multiple>
    </div>
</div>
