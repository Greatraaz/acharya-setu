<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Template name <span class="text-red-400">*</span></label>
        <input type="text" name="name" required maxlength="100" placeholder="e.g. Engineering & Technology Path"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Industry / domain <span class="text-red-400">*</span></label>
        <div class="relative">
            <select name="expertise_field" required
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 bg-white appearance-none cursor-pointer transition-all">
                <option value="">— Select industry —</option>
                @foreach($industries as $industry)
                <option value="{{ $industry }}">{{ $industry }}</option>
                @endforeach
            </select>
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </span>
        </div>
        <p class="text-xs text-gray-400 mt-1.5">Same list mentors pick during onboarding.</p>
    </div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Icon (emoji)</label>
        <input type="text" name="icon" maxlength="4" placeholder="💻"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 text-center text-2xl transition-all">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Accent color</label>
        <input type="color" name="color" value="#4f46e5" class="h-11 w-full rounded-xl border border-gray-200 cursor-pointer p-1 bg-white">
        <p class="text-xs text-gray-400 mt-1.5">Tints the icon, month timeline, and week badges.</p>
    </div>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
    <textarea name="description" rows="3" placeholder="Shown to mentors when they pick a starter plan"
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 resize-none transition-all"></textarea>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">Skills (optional)</label>
    <input type="text" name="expertise_tags" placeholder="System Design, Product Sense, Financial Modelling"
           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
    <p class="text-xs text-gray-400 mt-1.5">Comma-separated. For admin reference — visibility is by industry.</p>
</div>
<label class="flex items-center justify-between gap-3 p-4 rounded-xl border border-gray-200 bg-gray-50/60 cursor-pointer">
    <span>
        <span class="block text-sm font-semibold text-gray-800">Active</span>
        <span class="block text-xs text-gray-500 mt-0.5">Visible to mentors in this industry</span>
    </span>
    <span class="inline-flex items-center">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 w-4 h-4">
    </span>
</label>
