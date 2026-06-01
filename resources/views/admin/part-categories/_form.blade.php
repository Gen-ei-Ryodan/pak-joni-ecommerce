<div style="display:grid;gap:12px;">
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
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Name</label>
        <input name="name" value="{{ old('name', $category->name ?? '') }}" required
            style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
    </div>
</div>
