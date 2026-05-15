<div style="display:grid;gap:12px;">
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">SKU</label>
            <input name="sku" value="{{ old('sku', $part->sku ?? '') }}" required
                style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
        </div>
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Status</label>
            @php($val = old('status', $part->status ?? 'active'))
            <select name="status" required
                style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                <option value="active" @selected($val === 'active')>active</option>
                <option value="inactive" @selected($val === 'inactive')>inactive</option>
            </select>
        </div>
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Name</label>
        <input name="name" value="{{ old('name', $part->name ?? '') }}" required
            style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Slug (optional)</label>
        <input name="slug" value="{{ old('slug', $part->slug ?? '') }}"
            style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Category</label>
            @php($catVal = old('part_category_id', $part->part_category_id ?? null))
            <select name="part_category_id" required
                style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                <option value="" disabled @selected(!$catVal)>Pilih category</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) $catVal === (string) $cat->id)>{{ $cat->group }} — {{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Base Price</label>
            <input name="base_price" type="number" step="0.01" value="{{ old('base_price', $part->base_price ?? 0) }}" required
                style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
        </div>
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Short Description</label>
        <input name="short_description" value="{{ old('short_description', $part->short_description ?? '') }}"
            style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Description</label>
        <textarea name="description" rows="5"
            style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);resize:vertical;">{{ old('description', $part->description ?? '') }}</textarea>
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Specification</label>
        <textarea name="specification" rows="5"
            style="width:100%;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);resize:vertical;">{{ old('specification', $part->specification ?? '') }}</textarea>
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Compatible Motors</label>
        @php($selectedMotors = collect(old('motor_ids', isset($part) ? $part->motors->pluck('id')->all() : []))->map(fn ($v) => (int) $v)->all())
        <div style="display:flex;gap:10px;flex-wrap:wrap;border:1px solid var(--line);border-radius:12px;padding:10px 12px;background:rgba(255,255,255,0.02);">
            @foreach ($motors as $m)
                <label style="display:flex;gap:8px;align-items:center;color:var(--muted);font-size:13px;">
                    <input type="checkbox" name="motor_ids[]" value="{{ $m->id }}" @checked(in_array($m->id, $selectedMotors, true))>
                    <span>{{ $m->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="field">
        <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px;">Thumbnail</label>
        <input name="thumbnail" type="file" accept="image/*">
        @if (!empty($part?->thumbnail_path))
            <div style="height:10px;"></div>
            <img src="{{ asset($part->thumbnail_path) }}" alt="" style="width:180px;border-radius:12px;border:1px solid var(--line);">
        @endif
    </div>

    @if (!empty($part?->images) && $part->images->count())
        <div>
            <div style="color:var(--muted);font-size:12px;margin-bottom:8px;">Gallery Existing (centang untuk hapus)</div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                @foreach ($part->images as $img)
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

    <div class="panel" style="padding:14px;">
        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;">
            <div style="font-weight:600;">Variants</div>
            <button class="btn" type="button" data-variant-add>Tambah Variant</button>
        </div>

        <div style="height:10px;"></div>

        <div style="overflow:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:760px;">
                <thead>
                    <tr style="text-align:left;color:var(--muted);font-size:12px;">
                        <th style="padding:10px;">Default</th>
                        <th style="padding:10px;">SKU</th>
                        <th style="padding:10px;">Name</th>
                        <th style="padding:10px;">Price</th>
                        <th style="padding:10px;">Stock</th>
                        <th style="padding:10px;">Action</th>
                    </tr>
                </thead>
                <tbody data-variant-body>
                    @php
                        $rows = old('variants');
                        if (!$rows && isset($part)) {
                            $rows = $part->variants->map(fn ($v) => [
                                'id' => $v->id,
                                'sku' => $v->sku,
                                'name' => $v->name,
                                'price' => (string) $v->price,
                                'stock' => $v->stock,
                                'is_default' => $v->is_default ? '1' : '0',
                            ])->all();
                        }
                        if (!$rows) {
                            $rows = [[
                                'id' => '',
                                'sku' => '',
                                'name' => 'Default',
                                'price' => old('base_price', $part->base_price ?? 0),
                                'stock' => 0,
                                'is_default' => '1',
                            ]];
                        }
                    @endphp

                    @foreach ($rows as $i => $row)
                        <tr style="border-top:1px solid var(--line);" data-variant-row>
                            <td style="padding:10px;">
                                <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
                                <input type="radio" name="variants_default" value="{{ $i }}" @checked(($row['is_default'] ?? '') == '1') data-default-radio>
                                <input type="hidden" name="variants[{{ $i }}][is_default]" value="{{ ($row['is_default'] ?? '') == '1' ? 1 : 0 }}" data-default-hidden>
                            </td>
                            <td style="padding:10px;">
                                <input name="variants[{{ $i }}][sku]" value="{{ $row['sku'] ?? '' }}" required
                                    style="width:100%;min-width:160px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                            </td>
                            <td style="padding:10px;">
                                <input name="variants[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}" required
                                    style="width:100%;min-width:180px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                            </td>
                            <td style="padding:10px;">
                                <input name="variants[{{ $i }}][price]" type="number" step="0.01" value="{{ $row['price'] ?? 0 }}" required
                                    style="width:100%;min-width:140px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                            </td>
                            <td style="padding:10px;">
                                <input name="variants[{{ $i }}][stock]" type="number" value="{{ $row['stock'] ?? 0 }}" required
                                    style="width:100%;min-width:110px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                            </td>
                            <td style="padding:10px;">
                                <button class="btn btn-danger" type="button" data-variant-remove>Remove</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const tbody = document.querySelector('[data-variant-body]');
            const addBtn = document.querySelector('[data-variant-add]');
            if (!tbody || !addBtn) return;

            function syncDefaultRadios() {
                const rows = Array.from(tbody.querySelectorAll('[data-variant-row]'));
                let anyChecked = false;
                rows.forEach((row, idx) => {
                    const radio = row.querySelector('[data-default-radio]');
                    const hidden = row.querySelector('[data-default-hidden]');
                    if (!radio || !hidden) return;
                    if (radio.checked) anyChecked = true;
                    hidden.value = radio.checked ? '1' : '0';
                });
                if (!anyChecked && rows.length) {
                    const radio = rows[0].querySelector('[data-default-radio]');
                    if (radio) radio.checked = true;
                    syncDefaultRadios();
                }
            }

            tbody.addEventListener('change', (e) => {
                if (e.target && e.target.matches('[data-default-radio]')) {
                    const radios = tbody.querySelectorAll('[data-default-radio]');
                    radios.forEach((r) => {
                        if (r !== e.target) r.checked = false;
                    });
                    syncDefaultRadios();
                }
            });

            tbody.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-variant-remove]');
                if (!btn) return;
                const row = btn.closest('[data-variant-row]');
                if (!row) return;
                row.remove();
                reindex();
                syncDefaultRadios();
            });

            function reindex() {
                const rows = Array.from(tbody.querySelectorAll('[data-variant-row]'));
                rows.forEach((row, idx) => {
                    row.querySelectorAll('input[name^="variants["]').forEach((inp) => {
                        inp.name = inp.name.replace(/variants\[\d+\]/, `variants[${idx}]`);
                    });
                    const radio = row.querySelector('[data-default-radio]');
                    if (radio) radio.value = String(idx);
                });
            }

            addBtn.addEventListener('click', () => {
                const idx = tbody.querySelectorAll('[data-variant-row]').length;
                const tr = document.createElement('tr');
                tr.setAttribute('data-variant-row', '');
                tr.style.borderTop = '1px solid var(--line)';
                tr.innerHTML = `
                    <td style="padding:10px;">
                        <input type="hidden" name="variants[${idx}][id]" value="">
                        <input type="radio" name="variants_default" value="${idx}" data-default-radio>
                        <input type="hidden" name="variants[${idx}][is_default]" value="0" data-default-hidden>
                    </td>
                    <td style="padding:10px;">
                        <input name="variants[${idx}][sku]" value="" required style="width:100%;min-width:160px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                    </td>
                    <td style="padding:10px;">
                        <input name="variants[${idx}][name]" value="" required style="width:100%;min-width:180px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                    </td>
                    <td style="padding:10px;">
                        <input name="variants[${idx}][price]" type="number" step="0.01" value="0" required style="width:100%;min-width:140px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                    </td>
                    <td style="padding:10px;">
                        <input name="variants[${idx}][stock]" type="number" value="0" required style="width:100%;min-width:110px;border-radius:12px;border:1px solid var(--line);background:rgba(255,255,255,0.03);padding:10px 12px;color:var(--text);">
                    </td>
                    <td style="padding:10px;">
                        <button class="btn btn-danger" type="button" data-variant-remove>Remove</button>
                    </td>
                `;
                tbody.appendChild(tr);
                syncDefaultRadios();
            });

            syncDefaultRadios();
        })();
    </script>
@endpush
