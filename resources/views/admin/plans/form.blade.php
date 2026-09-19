@extends('admin.layouts.app')
@section('title', $plan->exists ? 'Edit Plan' : 'Create Plan')
@section('heading', $plan->exists ? 'Edit Plan: ' . $plan->name : 'Create New Plan')
@section('content')

<style>
    .toggle-switch { position: relative; display: inline-flex; align-items: center; cursor: pointer; }
    .toggle-switch input { display: none; }
    .toggle-track { width: 44px; height: 24px; background: #d1d5db; border-radius: 9999px; transition: background .2s; position: relative; flex-shrink: 0; }
    .toggle-switch input:checked + .toggle-track { background: #2563eb; }
    .toggle-thumb { position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; background: white; border-radius: 50%; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
    .toggle-switch input:checked ~ .toggle-track .toggle-thumb { transform: translateX(20px); }
</style>

<form method="POST"
      action="{{ $plan->exists ? route('admin.plans.update', $plan) : route('admin.plans.store') }}"
      class="max-w-6xl">
    @csrf
    @if($plan->exists) @method('PUT') @endif

    {{-- Back link --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.plans.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Plans
        </a>
    </div>

    {{-- Validation errors --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-5">
        <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-3 gap-5">

        {{-- ══════════ LEFT 2/3 ══════════ --}}
        <div class="col-span-2 space-y-5">

            {{-- Plan Details --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Plan Details</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Plan Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $plan->name) }}"
                            placeholder="e.g. Essential, Growth, Premium"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                        >
                        @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                        <textarea
                            name="description"
                            rows="2"
                            placeholder="Short tagline shown below the plan name"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100 resize-none"
                        >{{ old('description', $plan->description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Accent Color</label>
                        <div class="flex items-center gap-2">
                            <input
                                type="color"
                                name="color"
                                value="{{ old('color', $plan->color ?? '#2563eb') }}"
                                class="h-10 w-14 rounded-lg border border-gray-200 cursor-pointer p-0.5 bg-white"
                                oninput="document.getElementById('color-text').value=this.value; updatePreview();"
                            >
                            <input
                                type="text"
                                id="color-text"
                                value="{{ old('color', $plan->color ?? '#2563eb') }}"
                                placeholder="#2563eb"
                                class="flex-1 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100 font-mono"
                                oninput="document.querySelector('[name=color]').value=this.value; updatePreview();"
                            >
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Used for the plan card accent and icon.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Sort Order</label>
                        <input
                            type="number"
                            name="sort_order"
                            value="{{ old('sort_order', $plan->sort_order ?? 0) }}"
                            min="0"
                            placeholder="0"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                        >
                        <p class="text-xs text-gray-400 mt-1">Lower number appears first.</p>
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Pricing</h3>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Monthly Price <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium pointer-events-none select-none">
                                {{ config_val('currency_symbol', '₹') }}
                            </span>
                            <input
                                type="number"
                                name="price_monthly"
                                step="0.01"
                                min="0"
                                value="{{ old('price_monthly', $plan->price_monthly ?? 0) }}"
                                placeholder="0"
                                class="w-full border border-gray-200 rounded-xl pl-8 pr-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                            >
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Set 0 for free plans.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Yearly Price <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium pointer-events-none select-none">
                                {{ config_val('currency_symbol', '₹') }}
                            </span>
                            <input
                                type="number"
                                name="price_yearly"
                                step="0.01"
                                min="0"
                                value="{{ old('price_yearly', $plan->price_yearly ?? 0) }}"
                                placeholder="0"
                                class="w-full border border-gray-200 rounded-xl pl-8 pr-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                            >
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Set 0 if yearly not offered.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Trial Days</label>
                        <input
                            type="number"
                            name="trial_days"
                            min="0"
                            value="{{ old('trial_days', $plan->trial_days ?? 0) }}"
                            placeholder="0"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                        >
                        <p class="text-xs text-gray-400 mt-1">0 = no trial period.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Discount %</label>
                        <input
                            type="number"
                            name="discount_percent"
                            step="0.01"
                            min="0"
                            max="100"
                            value="{{ old('discount_percent', $plan->discount_percent ?? 0) }}"
                            placeholder="e.g. 10"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                        >
                        <p class="text-xs text-gray-400 mt-1">Percentage off the plan price at checkout. GST is applied after discount.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Discount valid until</label>
                        <input
                            type="date"
                            name="discount_expires_at"
                            value="{{ old('discount_expires_at', optional($plan->discount_expires_at)->format('Y-m-d')) }}"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                        >
                        <p class="text-xs text-gray-400 mt-1">Purchase on or before this date gets the discount. Leave empty for no end date.</p>
                    </div>
                </div>
            </div>

            @include('admin.plans._benefits')

            {{-- Usage Limits & Entitlements --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Usage & Entitlements</h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Billing cycle (days)</label>
                    <input
                        type="number"
                        name="duration"
                        min="1"
                        value="{{ old('duration', $plan->duration ?? 30) }}"
                        class="w-full max-w-xs border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                    >
                    <p class="text-xs text-gray-400 mt-1">Subscription length after purchase (e.g. 30).</p>
                </div>

                @php
                    $planLimits = is_array($plan->limits) ? $plan->limits : [];
                    $freeMins = old('free_session_minutes', $planLimits['free_session_minutes'] ?? '');
                    $freeMax = old('free_session_max_duration', $planLimits['free_session_max_duration'] ?? 30);
                @endphp
                <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Free session minutes / period</label>
                        <input
                            type="number"
                            name="free_session_minutes"
                            min="0"
                            value="{{ $freeMins }}"
                            placeholder="e.g. 30"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                        >
                        <p class="text-xs text-gray-400 mt-1">Career counselling free minutes in the billing period. 0 = none.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Max free booking length</label>
                        <select name="free_session_max_duration"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            @foreach([30, 60, 90, 120] as $mins)
                                <option value="{{ $mins }}" @selected((int) $freeMax === $mins)>{{ $mins }} minutes</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Free minutes apply only to bookings up to this length (Essential: 30).</p>
                    </div>
                </div>
            </div>

            {{-- Tax / Invoice --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-1 pb-3 border-b border-gray-100">Tax &amp; Invoicing</h3>
                <p class="text-xs text-gray-400 mt-3 mb-4">Used for subscription invoices (India GST). Leave blank if not applicable.</p>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">CGST %</label>
                        <input type="number" step="0.01" min="0" max="100" name="cgst_percent"
                               value="{{ old('cgst_percent', $plan->cgst_percent) }}"
                               placeholder="e.g. 9"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">SGST %</label>
                        <input type="number" step="0.01" min="0" max="100" name="sgst_percent"
                               value="{{ old('sgst_percent', $plan->sgst_percent) }}"
                               placeholder="e.g. 9"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">IGST %</label>
                        <input type="number" step="0.01" min="0" max="100" name="igst_percent"
                               value="{{ old('igst_percent', $plan->igst_percent) }}"
                               placeholder="e.g. 18"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
            </div>

          

        </div>{{-- /col-span-2 --}}

        {{-- ══════════ RIGHT SIDEBAR ══════════ --}}
        <div class="space-y-5">

            {{-- Visibility toggles --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Visibility</h3>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">Active</p>
                        <p class="text-xs text-gray-400 mt-0.5">Show this plan to users</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
                        <div class="toggle-track"><div class="toggle-thumb"></div></div>
                    </label>
                </div>

                <div class="pt-4 mt-3 border-t border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">Featured</p>
                        <p class="text-xs text-gray-400 mt-0.5">Highlight as recommended</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1"
                            {{ old('is_featured', $plan->is_featured) ? 'checked' : '' }}>
                        <div class="toggle-track"><div class="toggle-thumb"></div></div>
                    </label>
                </div>
            </div>

            {{-- Badge --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Badge / Label</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Badge Text</label>
                        <input
                            type="text"
                            name="badge_label"
                            value="{{ old('badge_label', $plan->badge_label) }}"
                            placeholder="Most Popular, Best Value…"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Badge Color</label>
                        <div class="grid grid-cols-3 gap-2" id="badge-color-picker">
                            @foreach([
                                'blue'   => ['bg-blue-100 text-blue-700',   'border-blue-300'],
                                'green'  => ['bg-green-100 text-green-700',  'border-green-400'],
                                'orange' => ['bg-orange-100 text-orange-700','border-orange-400'],
                            ] as $col => [$cls, $activeBorder])
                            <label class="cursor-pointer">
                                <input type="radio" name="badge_color" value="{{ $col }}" class="sr-only"
                                    {{ old('badge_color', $plan->badge_color) === $col ? 'checked' : '' }}>
                                <div class="text-center px-2 py-2 rounded-lg border-2 text-xs font-semibold select-none transition-all {{ $cls }}
                                    {{ old('badge_color', $plan->badge_color) === $col ? $activeBorder . ' ring-1 ring-offset-1 ring-gray-400' : 'border-transparent' }}">
                                    {{ ucfirst($col) }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Live Preview --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6 sticky top-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Live Preview</h3>

                <div class="border-2 border-dashed border-gray-100 rounded-xl p-4 bg-gray-50 relative">
                    <div id="preview-badge"
                         class="absolute top-3 right-3 text-[10px] font-bold px-2 py-0.5 rounded-full"
                         style="display:none;">Badge</div>
                    <div id="preview-icon"
                         class="w-9 h-9 rounded-xl mb-3 flex items-center justify-center text-white font-bold text-base flex-shrink-0"
                         style="background:#2563eb;">P
                    </div>
                    <p id="preview-name" class="font-bold text-gray-900 text-sm mb-0.5">Plan Name</p>
                    <p id="preview-desc" class="text-xs text-gray-500 mb-3 leading-relaxed">Description goes here</p>
                    <div id="preview-price" class="text-2xl font-extrabold text-gray-900 mb-1">Free</div>
                    <div id="preview-discount" class="text-[11px] text-green-700 font-medium mb-3" style="display:none;"></div>
                </div>

                <div class="mt-5 flex gap-2">
                    <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        {{ $plan->exists ? 'Update Plan' : 'Create Plan' }}
                    </button>
                    <a href="{{ route('admin.plans.index') }}"
                       class="px-4 py-2.5 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                </div>
            </div>

        </div>{{-- /sidebar --}}
    </div>
</form>

<script>
(function () {
    const nameInput  = document.querySelector('[name="name"]');
    const descInput  = document.querySelector('[name="description"]');
    const priceInput = document.querySelector('[name="price_monthly"]');
    const colorInput = document.querySelector('[name="color"]');
    const colorText  = document.getElementById('color-text');
    const badgeLabelInput = document.querySelector('[name="badge_label"]');
    const discountInput = document.querySelector('[name="discount_percent"]');
    const discountUntilInput = document.querySelector('[name="discount_expires_at"]');
    const sym        = '{{ addslashes(config_val("currency_symbol", "₹")) }}';
    const badgePalettes = {
        blue:   { bg: '#dbeafe', text: '#1d4ed8' },
        green:  { bg: '#dcfce7', text: '#15803d' },
        orange: { bg: '#ffedd5', text: '#c2410c' },
    };

    function updatePreview() {
        const name  = nameInput.value.trim()       || 'Plan Name';
        const desc  = descInput.value.trim()       || 'Description goes here';
        const price = parseFloat(priceInput.value) || 0;
        const color = colorInput.value             || '#2563eb';

        // Icon
        const icon = document.getElementById('preview-icon');
        icon.textContent      = (name[0] || 'P').toUpperCase();
        icon.style.background = color;

        // Name & desc
        document.getElementById('preview-name').textContent = name;
        document.getElementById('preview-desc').textContent = desc;

        // Price
        const priceEl = document.getElementById('preview-price');
        const discountEl = document.getElementById('preview-discount');
        const discountPct = parseFloat(discountInput?.value) || 0;
        const until = (discountUntilInput?.value || '').trim();
        const sale = discountPct > 0 ? price * (1 - Math.min(discountPct, 100) / 100) : price;

        if (price === 0) {
            priceEl.innerHTML = '<span class="text-green-600 text-2xl font-extrabold">Free</span>';
            discountEl.style.display = 'none';
        } else if (discountPct > 0) {
            priceEl.innerHTML = `<span class="text-2xl font-extrabold text-gray-900">${sym}${Math.round(sale).toLocaleString('en-IN')}</span>`
                + `<span class="text-sm font-normal text-gray-400 ml-0.5">/mo</span>`
                + ` <span class="text-sm font-normal text-gray-400 line-through ml-1">${sym}${price.toLocaleString('en-IN')}</span>`;
            discountEl.style.display = '';
            discountEl.textContent = (Number.isInteger(discountPct) ? discountPct : discountPct.toFixed(2)) + '% off'
                + (until ? ' until ' + until : '');
        } else {
            priceEl.innerHTML = `<span class="text-2xl font-extrabold text-gray-900">${sym}${price.toLocaleString('en-IN')}</span>`
                + `<span class="text-sm font-normal text-gray-400 ml-0.5">/mo</span>`;
            discountEl.style.display = 'none';
        }

        const badgeEl = document.getElementById('preview-badge');
        const badgeText = (badgeLabelInput?.value || '').trim();
        const selectedColor = document.querySelector('[name="badge_color"]:checked')?.value || 'blue';
        const palette = badgePalettes[selectedColor] || badgePalettes.blue;
        if (badgeText) {
            badgeEl.style.display = '';
            badgeEl.style.background = palette.bg;
            badgeEl.style.color = palette.text;
            badgeEl.textContent = badgeText;
        } else {
            badgeEl.style.display = 'none';
        }
    }

    // Sync color picker ↔ hex text field
    colorInput?.addEventListener('input', () => {
        colorText.value = colorInput.value;
        updatePreview();
    });

    // Watch all live-preview inputs
    [nameInput, descInput, priceInput, badgeLabelInput, discountInput, discountUntilInput].forEach(el => {
        el?.addEventListener('input', updatePreview);
    });

    // Badge color picker — highlight the selected option
    function refreshBadgePicker() {
        document.querySelectorAll('[name="badge_color"]').forEach(radio => {
            const div = radio.nextElementSibling;
            if (radio.checked) {
                div.classList.add('ring-2', 'ring-offset-1', 'ring-gray-500');
                div.classList.remove('border-transparent');
            } else {
                div.classList.remove('ring-2', 'ring-offset-1', 'ring-gray-500');
                div.classList.add('border-transparent');
            }
        });
        updatePreview();
    }

    document.querySelectorAll('[name="badge_color"]').forEach(radio => {
        radio.addEventListener('change', refreshBadgePicker);
    });

    // Init
    refreshBadgePicker();
    updatePreview();

    const benefitRows = document.getElementById('benefit-rows');
    const benefitTpl = document.getElementById('benefit-row-template');
    document.getElementById('add-benefit-row')?.addEventListener('click', () => {
        if (!benefitRows || !benefitTpl) return;
        const html = benefitTpl.innerHTML.replaceAll('__INDEX__', String(Date.now()));
        benefitRows.insertAdjacentHTML('beforeend', html);
    });
    benefitRows?.addEventListener('click', (e) => {
        const btn = e.target.closest('.remove-benefit-row');
        if (!btn) return;
        const rows = benefitRows.querySelectorAll('.benefit-row');
        if (rows.length <= 1) {
            rows[0]?.querySelectorAll('input').forEach(input => { input.value = ''; });
            return;
        }
        btn.closest('.benefit-row')?.remove();
    });
})();
</script>

@endsection