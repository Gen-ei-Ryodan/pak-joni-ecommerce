<div style="display:grid;gap:12px;">
    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Title</label>
        <input name="title" value="{{ old('title', $banner->title ?? '') }}" required
            style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Link URL (optional)</label>
            <input name="link_url" value="{{ old('link_url', $banner->link_url ?? '') }}"
                style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
        </div>
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Sort Order</label>
            <input name="sort_order" type="number" value="{{ old('sort_order', $banner->sort_order ?? 0) }}"
                style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
        </div>
    </div>

    <div class="field">
        <label style="display:flex;gap:10px;align-items:center;color:var(--muted);font-size:13px;">
            <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $banner->is_active ?? true))>
            <span>Active</span>
        </label>
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Image</label>
        <input name="image" type="file" accept="image/*" {{ isset($banner) ? '' : 'required' }}>
        @if (!empty($banner?->image_path))
            <div style="height:10px;"></div>
            <img src="{{ asset($banner->image_path) }}" alt="" style="width:220px;border-radius:12px;border:1px solid var(--line);">
        @endif
    </div>
</div>
