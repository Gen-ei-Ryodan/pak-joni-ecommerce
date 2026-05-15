<div style="display:grid;gap:12px;">
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Group</label>
            @php($val = old('group', $category->group ?? 'part'))
            <select name="group" required
                style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                <option value="part" @selected($val === 'part')>part</option>
                <option value="refitting" @selected($val === 'refitting')>refitting</option>
                <option value="wearing" @selected($val === 'wearing')>wearing</option>
            </select>
        </div>
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Sort Order</label>
            <input name="sort_order" type="number" value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
        </div>
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Name</label>
        <input name="name" value="{{ old('name', $category->name ?? '') }}" required
            style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Slug (optional)</label>
        <input name="slug" value="{{ old('slug', $category->slug ?? '') }}"
            style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
    </div>
</div>
