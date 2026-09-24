@php
    $benefits = old('benefits', $plan->resolvedBenefits());
    $benefits = \App\Models\Plan::normalizeBenefits(is_array($benefits) ? $benefits : []);
    if ($benefits === []) {
        $benefits = \App\Models\Plan::defaultBenefits();
    }
    $inp = 'w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100';
@endphp

<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden" id="plan-benefits">
    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">Plan benefits</h3>
            <p class="text-xs text-gray-500 mt-1">Tick which billing period each benefit appears on (Monthly / Yearly).</p>
        </div>
        <button type="button" id="add-benefit-row"
                class="text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg">
            + Add benefit
        </button>
    </div>

    <div class="px-6 py-5 space-y-3" id="benefit-rows">
        @foreach($benefits as $i => $row)
        <div class="benefit-row grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Label</label>
                <input type="text" name="benefits[{{ $i }}][label]" value="{{ $row['label'] }}"
                       maxlength="120" class="{{ $inp }}" placeholder="Benefit name">
            </div>
            <div class="sm:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Value</label>
                <input type="text" name="benefits[{{ $i }}][value]" value="{{ $row['value'] }}"
                       maxlength="255" class="{{ $inp }}" placeholder="Shown on frontend">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Included in</label>
                <div class="flex items-center gap-4 h-[42px]">
                    <label class="inline-flex items-center gap-1.5 text-sm text-gray-700 cursor-pointer">
                        <input type="hidden" name="benefits[{{ $i }}][monthly]" value="0">
                        <input type="checkbox" name="benefits[{{ $i }}][monthly]" value="1"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               @checked(!empty($row['monthly']))>
                        Monthly
                    </label>
                    <label class="inline-flex items-center gap-1.5 text-sm text-gray-700 cursor-pointer">
                        <input type="hidden" name="benefits[{{ $i }}][yearly]" value="0">
                        <input type="checkbox" name="benefits[{{ $i }}][yearly]" value="1"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               @checked(!empty($row['yearly']))>
                        Yearly
                    </label>
                </div>
            </div>
            <div class="sm:col-span-1 pb-0.5">
                <button type="button" class="remove-benefit-row w-full h-[42px] text-red-500 hover:text-red-700 hover:bg-red-50 rounded-xl border border-transparent hover:border-red-100"
                        title="Remove">&times;</button>
            </div>
        </div>
        @endforeach
    </div>
</div>

<template id="benefit-row-template">
    <div class="benefit-row grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
        <div class="sm:col-span-4">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Label</label>
            <input type="text" name="benefits[__INDEX__][label]" maxlength="120"
                   class="{{ $inp }}" placeholder="Benefit name">
        </div>
        <div class="sm:col-span-4">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Value</label>
            <input type="text" name="benefits[__INDEX__][value]" maxlength="255"
                   class="{{ $inp }}" placeholder="Shown on frontend">
        </div>
        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Included in</label>
            <div class="flex items-center gap-4 h-[42px]">
                <label class="inline-flex items-center gap-1.5 text-sm text-gray-700 cursor-pointer">
                    <input type="hidden" name="benefits[__INDEX__][monthly]" value="0">
                    <input type="checkbox" name="benefits[__INDEX__][monthly]" value="1"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                    Monthly
                </label>
                <label class="inline-flex items-center gap-1.5 text-sm text-gray-700 cursor-pointer">
                    <input type="hidden" name="benefits[__INDEX__][yearly]" value="0">
                    <input type="checkbox" name="benefits[__INDEX__][yearly]" value="1"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                    Yearly
                </label>
            </div>
        </div>
        <div class="sm:col-span-1 pb-0.5">
            <button type="button" class="remove-benefit-row w-full h-[42px] text-red-500 hover:text-red-700 hover:bg-red-50 rounded-xl border border-transparent hover:border-red-100"
                    title="Remove">&times;</button>
        </div>
    </div>
</template>
