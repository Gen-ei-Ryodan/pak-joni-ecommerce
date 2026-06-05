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

    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Province</label>
            <select id="province-select" class="input" style="width:100%;min-width:0;" name="province" required>
                <option value="">Pilih Provinsi...</option>
            </select>
        </div>
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">City / Regency</label>
            <select id="regency-select" class="input" style="width:100%;min-width:0;" name="city" required disabled>
                <option value="">Pilih Kota / Kabupaten...</option>
            </select>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">District</label>
            <select id="district-select" class="input" style="width:100%;min-width:0;" name="district" required disabled>
                <option value="">Pilih Kecamatan...</option>
            </select>
        </div>
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Village</label>
            <select id="village-select" class="input" style="width:100%;min-width:0;" name="village" required disabled>
                <option value="">Pilih Kelurahan / Desa...</option>
            </select>
        </div>
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Postal Code</label>
        <input class="input" id="postal-code-input" style="width:100%;min-width:0;" name="postal_code" value="{{ old('postal_code', $address->postal_code ?? '') }}" required>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const provinceSelect = document.getElementById('province-select');
    const regencySelect = document.getElementById('regency-select');
    const districtSelect = document.getElementById('district-select');
    const villageSelect = document.getElementById('village-select');
    const postalCodeInput = document.getElementById('postal-code-input');

    // Load provinces
    fetch('/regions/provinces')
        .then(response => response.json())
        .then(data => {
            data.data.forEach(province => {
                const option = document.createElement('option');
                option.value = province.name;
                option.dataset.code = province.code;
                option.textContent = province.name;
                @if(isset($address) && $address->province)
                    if (province.name === '{{ $address->province }}') {
                        option.selected = true;
                        loadRegencies(province.code);
                    }
                @endif
                provinceSelect.appendChild(option);
            });
        });

    // On province change
    provinceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const provinceCode = selectedOption.dataset.code;
        resetSelects(regencySelect, districtSelect, villageSelect);
        if (provinceCode) loadRegencies(provinceCode);
    });

    // On regency change
    regencySelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const regencyCode = selectedOption.dataset.code;
        resetSelects(districtSelect, villageSelect);
        if (regencyCode) loadDistricts(regencyCode);
    });

    // On district change
    districtSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const districtCode = selectedOption.dataset.code;
        resetSelects(villageSelect);
        if (districtCode) loadVillages(districtCode);
    });

    function loadRegencies(provinceCode) {
        fetch(`/regions/regencies/${provinceCode}`)
            .then(response => response.json())
            .then(data => {
                regencySelect.disabled = false;
                data.data.forEach(regency => {
                    const option = document.createElement('option');
                    option.value = regency.name;
                    option.dataset.code = regency.code;
                    option.textContent = regency.name;
                    @if(isset($address) && $address->city)
                        if (regency.name === '{{ $address->city }}') {
                            option.selected = true;
                            loadDistricts(regency.code);
                        }
                    @endif
                    regencySelect.appendChild(option);
                });
            });
    }

    function loadDistricts(regencyCode) {
        fetch(`/regions/districts/${regencyCode}`)
            .then(response => response.json())
            .then(data => {
                districtSelect.disabled = false;
                data.data.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.name;
                    option.dataset.code = district.code;
                    option.textContent = district.name;
                    @if(isset($address) && $address->district)
                        if (district.name === '{{ $address->district }}') {
                            option.selected = true;
                            loadVillages(district.code);
                        }
                    @endif
                    districtSelect.appendChild(option);
                });
            });
    }

    function loadVillages(districtCode) {
        fetch(`/regions/villages/${districtCode}`)
            .then(response => response.json())
            .then(data => {
                villageSelect.disabled = false;
                data.data.forEach(village => {
                    const option = document.createElement('option');
                    option.value = village.name;
                    option.dataset.code = village.code;
                    option.textContent = village.name;
                    @if(isset($address) && $address->village)
                        if (village.name === '{{ $address->village }}') {
                            option.selected = true;
                        }
                    @endif
                    villageSelect.appendChild(option);
                });
            });
    }

    function resetSelects(...selects) {
        selects.forEach(select => {
            select.innerHTML = '<option value="">Pilih...</option>';
            select.disabled = true;
        });
    }
});
</script>
