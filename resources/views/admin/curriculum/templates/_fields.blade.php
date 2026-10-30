<div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">Template name <span class="text-red-500">*</span></label>
    <input type="text" name="name" required maxlength="100" placeholder="e.g. Engineering & Technology Path"
           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">Industry / domain <span class="text-red-500">*</span></label>
    <select name="expertise_field" required
            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 bg-white">
        <option value="">— Select industry —</option>
        @foreach($industries as $industry)
        <option value="{{ $industry }}">{{ $industry }}</option>
        @endforeach
    </select>
    <p class="text-xs text-gray-400 mt-1">Same list mentors pick during onboarding. Only mentors in this industry will see the template.</p>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Icon (emoji)</label>
        <input type="text" name="icon" maxlength="4" placeholder="💻"
               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 text-center text-2xl">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Accent color</label>
        <input type="color" name="color" value="#7c3aed" class="h-10 w-full rounded-lg border border-gray-200 cursor-pointer p-0.5">
        <p class="text-xs text-gray-400 mt-1">Tints the template icon, month timeline dots, and week badges. Not shown as a color name to mentees.</p>
    </div>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
    <textarea name="description" rows="2" placeholder="Shown to mentors when they pick a starter plan"
              class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 resize-none"></textarea>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">Skills (optional)</label>
    <input type="text" name="expertise_tags" placeholder="System Design, Product Sense, Financial Modelling"
           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
    <p class="text-xs text-gray-400 mt-1">Comma-separated. For admin reference only — visibility is by industry.</p>
</div>
<label class="flex items-center gap-2 cursor-pointer">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" checked class="rounded">
    <span class="text-sm text-gray-700 font-medium">Active (visible to mentors in this industry)</span>
</label>
