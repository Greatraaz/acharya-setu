@php
    $isEdit = isset($offer) && $offer->exists;
    $formAction = $isEdit ? route('admin.offers.update', $offer) : route('admin.offers.store');
    $selectedMenteeIds = old('mentee_ids', $offer->relationLoaded('mentees') ? $offer->mentees->pluck('id')->map(fn ($id) => (string) $id)->all() : []);
    $audience = old('audience', $offer->audience ?? \App\Models\Offer::AUDIENCE_NEW_JOINEE);
@endphp

<form method="POST" action="{{ $formAction }}" class="space-y-6" id="offer-form">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Offer Title *</label>
            <input type="text" name="title" value="{{ old('title', $offer->title ?? '') }}" required
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                   placeholder="e.g. Welcome ₹500 credit">
            @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Audience *</label>
            <select name="audience" id="offer-audience" required
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="{{ \App\Models\Offer::AUDIENCE_NEW_JOINEE }}" @selected($audience === \App\Models\Offer::AUDIENCE_NEW_JOINEE)>
                    New joinee — auto wallet credit on onboarding
                </option>
                <option value="{{ \App\Models\Offer::AUDIENCE_SELECTED_MENTEES }}" @selected($audience === \App\Models\Offer::AUDIENCE_SELECTED_MENTEES)>
                    Selected mentees — session booking coupon
                </option>
            </select>
            @error('audience')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div id="mentee-picker" class="space-y-3 {{ $audience === \App\Models\Offer::AUDIENCE_SELECTED_MENTEES ? '' : 'hidden' }}">
            <label class="block text-sm font-medium text-gray-700">Select Mentees *</label>
            <p class="text-xs text-gray-500">Search by name or email, then click to add. You can select multiple mentees.</p>

            <div class="relative">
                <input type="text" id="mentee-search" autocomplete="off"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Type to search mentees…">
                <div id="mentee-dropdown"
                     class="hidden absolute z-20 left-0 right-0 mt-1 max-h-52 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg"></div>
            </div>

            <div id="mentee-chips" class="flex flex-wrap gap-2 min-h-[36px]"></div>
            <div id="mentee-hidden-inputs"></div>
            @error('mentee_ids')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            @error('mentee_ids.*')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div id="coupon-fields" class="space-y-4 {{ $audience === \App\Models\Offer::AUDIENCE_SELECTED_MENTEES ? '' : 'hidden' }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Coupon Code</label>
                <input type="text" name="coupon_code" value="{{ old('coupon_code', $offer->coupon_code ?? '') }}"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Leave blank to auto-generate">
                @error('coupon_code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Usage Limit *</label>
                    <input type="number" name="usage_limit" min="1" id="offer-usage-limit"
                           value="{{ old('usage_limit', $offer->usage_limit ?? 1) }}"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('usage_limit')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Min. Session Booking (₹) *</label>
                    <input type="number" name="min_session_amount" min="0" step="0.01" id="offer-min-session"
                           value="{{ old('min_session_amount', $offer->min_session_amount ?? 0) }}"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('min_session_amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    <span id="amount-label">{{ $audience === \App\Models\Offer::AUDIENCE_SELECTED_MENTEES ? 'Discount Amount (₹)' : 'Wallet Credit Amount (₹)' }}</span> *
                </label>
                <input type="number" name="amount" min="1" step="0.01" required
                       value="{{ old('amount', $offer->amount ?? '') }}"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <label class="inline-flex items-center gap-2 mt-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                           @checked(old('is_active', $offer->is_active ?? true))>
                    Active
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Start Date *</label>
                <input type="date" name="starts_at" required
                       value="{{ old('starts_at', optional($offer->starts_at)->format('Y-m-d') ?? now()->toDateString()) }}"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('starts_at')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Expiry Date *</label>
                <input type="date" name="expires_at" required
                       value="{{ old('expires_at', optional($offer->expires_at)->format('Y-m-d') ?? now()->addMonth()->toDateString()) }}"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('expires_at')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div id="offer-hint-new" class="rounded-xl bg-orange-50 border border-orange-100 px-4 py-3 text-sm text-orange-900 {{ $audience === \App\Models\Offer::AUDIENCE_NEW_JOINEE ? '' : 'hidden' }}">
            New mentees who complete onboarding before the expiry date receive this amount in their wallet automatically (once per offer).
        </div>
        <div id="offer-hint-coupon" class="rounded-xl bg-blue-50 border border-blue-100 px-4 py-3 text-sm text-blue-900 {{ $audience === \App\Models\Offer::AUDIENCE_SELECTED_MENTEES ? '' : 'hidden' }}">
            Assigned mentees can optionally apply this coupon when booking a session. Minimum booking amount, usage limit, and expiry are enforced at checkout.
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.offers.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50">Cancel</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition">
            {{ $isEdit ? 'Update Offer' : 'Create Offer' }}
        </button>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const AUDIENCE_NEW = @json(\App\Models\Offer::AUDIENCE_NEW_JOINEE);
    const AUDIENCE_COUPON = @json(\App\Models\Offer::AUDIENCE_SELECTED_MENTEES);
    const mentees = @json($mentees->map(fn ($m) => ['id' => (string) $m->id, 'name' => $m->name, 'email' => $m->email])->values());
    let selected = new Set(@json($selectedMenteeIds));

    const audienceEl = document.getElementById('offer-audience');
    const menteePicker = document.getElementById('mentee-picker');
    const couponFields = document.getElementById('coupon-fields');
    const hintNew = document.getElementById('offer-hint-new');
    const hintCoupon = document.getElementById('offer-hint-coupon');
    const amountLabel = document.getElementById('amount-label');
    const searchEl = document.getElementById('mentee-search');
    const dropdownEl = document.getElementById('mentee-dropdown');
    const chipsEl = document.getElementById('mentee-chips');
    const hiddenEl = document.getElementById('mentee-hidden-inputs');
    const usageLimit = document.getElementById('offer-usage-limit');
    const minSession = document.getElementById('offer-min-session');

    function toggleAudience() {
        const isCoupon = audienceEl.value === AUDIENCE_COUPON;
        menteePicker.classList.toggle('hidden', !isCoupon);
        couponFields.classList.toggle('hidden', !isCoupon);
        hintNew.classList.toggle('hidden', isCoupon);
        hintCoupon.classList.toggle('hidden', !isCoupon);
        amountLabel.textContent = isCoupon ? 'Discount Amount (₹)' : 'Wallet Credit Amount (₹)';
        if (usageLimit) usageLimit.required = isCoupon;
        if (minSession) minSession.required = isCoupon;
    }

    function renderSelected() {
        chipsEl.innerHTML = '';
        hiddenEl.innerHTML = '';
        selected.forEach(function (id) {
            const m = mentees.find(function (x) { return x.id === id; });
            if (!m) return;
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center gap-1.5 bg-blue-50 text-blue-800 text-xs font-medium px-3 py-1.5 rounded-full border border-blue-100';
            chip.innerHTML = m.name + ' <button type="button" class="text-blue-600 hover:text-blue-900" data-remove="' + id + '">×</button>';
            chipsEl.appendChild(chip);
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'mentee_ids[]';
            input.value = id;
            hiddenEl.appendChild(input);
        });
    }

    function renderDropdown(query) {
        const q = (query || '').trim().toLowerCase();
        const matches = mentees.filter(function (m) {
            if (selected.has(m.id)) return false;
            if (!q) return true;
            return m.name.toLowerCase().includes(q) || m.email.toLowerCase().includes(q);
        }).slice(0, 12);

        if (!matches.length) {
            dropdownEl.classList.add('hidden');
            dropdownEl.innerHTML = '';
            return;
        }

        dropdownEl.innerHTML = matches.map(function (m) {
            return '<button type="button" class="w-full text-left px-3.5 py-2.5 text-sm hover:bg-gray-50 border-b border-gray-50 last:border-0" data-id="' + m.id + '">' +
                '<span class="font-medium text-gray-900">' + m.name + '</span>' +
                '<span class="block text-xs text-gray-500">' + m.email + '</span></button>';
        }).join('');
        dropdownEl.classList.remove('hidden');
    }

    audienceEl.addEventListener('change', toggleAudience);

    searchEl.addEventListener('input', function () { renderDropdown(searchEl.value); });
    searchEl.addEventListener('focus', function () { renderDropdown(searchEl.value); });

    dropdownEl.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-id]');
        if (!btn) return;
        selected.add(btn.dataset.id);
        searchEl.value = '';
        renderSelected();
        renderDropdown('');
    });

    chipsEl.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-remove]');
        if (!btn) return;
        selected.delete(btn.dataset.remove);
        renderSelected();
    });

    document.addEventListener('click', function (e) {
        if (!menteePicker.contains(e.target)) {
            dropdownEl.classList.add('hidden');
        }
    });

    renderSelected();
    toggleAudience();
})();
</script>
@endpush
