<div style="display:grid;gap:12px;">
    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Label (optional)</label>
        <input class="input" style="width:100%;min-width:0;" name="label" value="{{ old('label', $address->label ?? '') }}">
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Recipient Name</label>
            <input class="input" style="width:100%;min-width:0;" name="recipient_name" value="{{ old('recipient_name', $address->recipient_name ?? '') }}" required>
        </div>
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Phone</label>
            <input class="input" style="width:100%;min-width:0;" name="phone" value="{{ old('phone', $address->phone ?? '') }}" required>
        </div>
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Address Line 1</label>
        <input class="input" style="width:100%;min-width:0;" name="address_line1" value="{{ old('address_line1', $address->address_line1 ?? '') }}" required>
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Address Line 2 (optional)</label>
        <input class="input" style="width:100%;min-width:0;" name="address_line2" value="{{ old('address_line2', $address->address_line2 ?? '') }}">
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;">
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">City</label>
            <input class="input" style="width:100%;min-width:0;" name="city" value="{{ old('city', $address->city ?? '') }}" required>
        </div>
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Province</label>
            <input class="input" style="width:100%;min-width:0;" name="province" value="{{ old('province', $address->province ?? '') }}" required>
        </div>
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Postal Code</label>
            <input class="input" style="width:100%;min-width:0;" name="postal_code" value="{{ old('postal_code', $address->postal_code ?? '') }}" required>
        </div>
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Notes (optional)</label>
        <textarea class="input" style="width:100%;min-width:0;resize:vertical;" name="notes" rows="3">{{ old('notes', $address->notes ?? '') }}</textarea>
    </div>

    <label style="display:flex;gap:10px;align-items:center;color:var(--muted);font-size:13px;">
        <input type="checkbox" name="is_default" value="1" @checked((bool) old('is_default', $address->is_default ?? false))>
        <span>Set as default</span>
    </label>
</div>
