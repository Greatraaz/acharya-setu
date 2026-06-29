<footer class="bg-white border-t border-slate-200 px-8 py-5">
    <div class="flex justify-between items-center text-sm text-slate-500">
        <p>© {{ date('Y') }} AcharyaSetu. All rights reserved.</p>

        <div class="flex gap-6">
            <a href="#" class="hover:text-orange-500">Privacy</a>
            <a href="#" class="hover:text-orange-500">Support</a>
            <a href="#" class="hover:text-orange-500">Docs</a>
        </div>
    </div>
</footer>

{{-- Global Reusable AJAX Modal --}}
<div id="globalModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">

    {{-- Backdrop --}}
    <div id="globalModalBackdrop" class="absolute inset-0 bg-black/50"></div>

    {{-- Modal Box --}}
    <div class="relative bg-white rounded-2xl shadow-xl w-full mx-4 z-10 flex flex-col"
         style="max-width:42rem; max-height:90vh;">

        {{-- Header — always visible --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200"
             style="flex-shrink:0;">
            <h5 id="globalModalTitle" class="text-lg font-semibold text-slate-800">Modal</h5>
            <button id="globalModalClose" class="text-slate-400 hover:text-slate-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body — scrollable --}}
        <div id="globalModalBody" class="p-6" style="overflow-y:auto; flex:1;">
            Loading...
        </div>

    </div>
</div>
