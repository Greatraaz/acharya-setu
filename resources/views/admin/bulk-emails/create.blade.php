@extends('admin.layouts.app')
@section('title', 'Bulk Email')
@section('heading', 'Bulk Email')

@php
    $audience = old('audience', 'all');
    $selectedIds = collect(old('user_ids', []))->map(fn ($id) => (int) $id)->filter()->values();
    $selectedUsers = $selectedIds->isNotEmpty()
        ? \App\Models\User::query()->whereIn('id', $selectedIds)->get(['id', 'name', 'email', 'role'])->keyBy('id')
        : collect();
@endphp

@section('content')
<style>
.preview-email-body p { margin: 0 0 10px; }
.preview-email-body p:last-child { margin-bottom: 0; }
.preview-email-body a { color: #d97706; text-decoration: underline; }
.preview-email-body ul, .preview-email-body ol { margin: 0 0 10px; padding-left: 1.1rem; }
.preview-email-body h1, .preview-email-body h2, .preview-email-body h3 { color: #18181b; font-weight: 700; margin: 12px 0 6px; }
.preview-email-body h1 { font-size: 1.1rem; }
.preview-email-body h2 { font-size: 1rem; }
.preview-email-body h3 { font-size: 0.95rem; }
</style>
<div class="min-w-0 max-w-full space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h6 class="text-lg font-semibold text-gray-800">Send email to users</h6>
            <p class="text-sm text-gray-500 mt-0.5">
                Choose mentors, mentees, everyone, or a specific list. Each person receives their own copy.
            </p>
        </div>
        <nav class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-blue-600 transition-colors">Dashboard</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-700 font-medium">Bulk Email</span>
        </nav>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Mentors with email</p>
            <p class="text-2xl font-bold text-indigo-600">{{ number_format($counts['mentors']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Mentees with email</p>
            <p class="text-2xl font-bold text-emerald-600">{{ number_format($counts['mentees']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">All (mentors + mentees)</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($counts['all']) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
        <div class="xl:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h6 class="font-semibold text-gray-800">Compose</h6>
                <p class="text-xs text-gray-400 mt-0.5">Use <code class="text-[11px] bg-gray-50 px-1 rounded">@{{name}}</code>, <code class="text-[11px] bg-gray-50 px-1 rounded">@{{first_name}}</code>, or <code class="text-[11px] bg-gray-50 px-1 rounded">@{{email}}</code> in the subject or message.</p>
            </div>

            <form method="POST" action="{{ route('admin.bulk-emails.store') }}" class="p-5 space-y-5" id="bulk-email-form"
                  onsubmit="if (window.tinymce) { tinymce.triggerSave(); }">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Send to *</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2" id="audience-options">
                        @foreach([
                            'all' => 'All users',
                            'mentors' => 'Mentors',
                            'mentees' => 'Mentees',
                            'specific' => 'Specific users',
                        ] as $value => $label)
                        <label class="audience-card cursor-pointer rounded-xl border px-3 py-2.5 text-sm font-medium text-center transition-colors
                                      {{ $audience === $value ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                            <input type="radio" name="audience" value="{{ $value }}" class="sr-only" {{ $audience === $value ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <div id="specific-picker" class="{{ $audience === 'specific' ? '' : 'hidden' }}">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Search users *</label>
                    <div class="relative">
                        <input type="text" id="user-search" autocomplete="off"
                               placeholder="Search by name, email or phone…"
                               class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div id="user-dropdown"
                             class="hidden absolute z-20 left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg"></div>
                    </div>
                    <div id="selected-users" class="mt-3 flex flex-wrap gap-2">
                        @foreach($selectedIds as $id)
                            @php $u = $selectedUsers->get($id); @endphp
                            @if($u)
                            <span class="selected-chip inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 text-xs font-medium px-2.5 py-1.5 rounded-lg border border-indigo-100" data-id="{{ $u->id }}">
                                {{ $u->name }}
                                <button type="button" class="remove-user text-indigo-400 hover:text-indigo-700" aria-label="Remove">&times;</button>
                                <input type="hidden" name="user_ids[]" value="{{ $u->id }}">
                            </span>
                            @endif
                        @endforeach
                    </div>
                    @error('user_ids')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5" for="subject">Subject *</label>
                    <input id="subject" type="text" name="subject" value="{{ old('subject') }}" maxlength="180" required
                           placeholder="e.g. Platform update for @{{first_name}}"
                           class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5" for="body">Message *</label>
                    <textarea id="body" name="body" rows="12" class="tinymce-email w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5">{{ old('body') }}</textarea>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-gray-500">
                        Will send to <strong id="recipient-count" class="text-gray-800">{{ number_format($counts['all']) }}</strong>
                        <span id="recipient-label">all mentors and mentees</span>
                    </p>
                    <button type="submit" id="send-btn"
                            class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl">
                        Send email
                    </button>
                </div>
            </form>
        </div>

        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h6 class="font-semibold text-gray-800">Email preview</h6>
                    <p class="text-xs text-gray-400 mt-0.5">How recipients will see this message.</p>
                </div>
                <div class="p-4 bg-gray-100">
                    <div class="mx-auto max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <div class="h-1.5 bg-amber-500"></div>
                        <div class="bg-zinc-950 px-5 py-3.5 text-center">
                            <img src="{{ $emailLogo }}" alt="{{ $appName }}" class="mx-auto h-9 w-auto object-contain">
                        </div>
                        <div class="px-5 py-5">
                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-amber-600">Message from {{ $appName }}</p>
                            <h3 id="preview-headline" class="mt-1 text-base font-extrabold leading-snug text-zinc-900">Your subject will appear here</h3>
                            <p class="mt-3 text-sm font-semibold text-zinc-800">Hi there,</p>
                            <div id="preview-body" class="preview-email-body mt-3 text-sm leading-relaxed text-zinc-600">
                                <p class="text-gray-400">Start writing to see the preview.</p>
                            </div>
                        </div>
                        <div class="px-5 pb-4 text-center">
                            <span class="inline-block rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-zinc-950">Open {{ $appName }}</span>
                        </div>
                        <div class="border-t border-gray-100 bg-gray-50 px-5 py-3 text-center">
                            <p class="text-[11px] text-gray-400">© {{ date('Y') }} {{ $appName }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h6 class="font-semibold text-gray-800">Recent sends</h6>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recent as $log)
                @php
                    $meta = $log->meta ?? [];
                    $subject = trim((string) ($meta['subject'] ?? ''));
                    $body = (string) ($meta['body'] ?? '');
                    $audienceKey = (string) ($meta['audience'] ?? '');
                    $audienceLabel = (string) ($meta['audience_label'] ?? \App\Support\BulkEmailAudience::label($audienceKey ?: 'all'));
                    $recipientCount = (int) ($meta['recipient_count'] ?? 0);
                    $recipients = collect($meta['recipients'] ?? []);
                    $plainBody = trim(preg_replace('/\s+/', ' ', html_entity_decode(str_ireplace(['&nbsp;', '&#160;', '&#xA0;'], ' ', strip_tags($body)), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                @endphp
                <div class="px-5 py-4 space-y-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-semibold text-gray-900 leading-snug">
                            {{ $subject !== '' ? $subject : ($log->description ?: 'Bulk email') }}
                        </p>
                        <span class="flex-shrink-0 text-[11px] font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">Sent</span>
                    </div>
                    <p class="text-xs text-gray-500">
                        To {{ $recipientCount > 0 ? number_format($recipientCount).' ' : '' }}{{ $audienceLabel }}
                        · {{ $log->causer_name ?: 'Admin' }}
                        · {{ optional($log->logged_at)->timezone(config('app.timezone'))->format('d M Y, g:i A') }}
                        <span class="text-gray-400">({{ optional($log->logged_at)->diffForHumans() }})</span>
                    </p>
                    @if($recipients->isNotEmpty())
                    <p class="text-xs text-gray-600 leading-relaxed">
                        {{ $recipients->pluck('name')->filter()->implode(', ') }}
                        @if($recipientCount > $recipients->count())
                            <span class="text-gray-400">and {{ number_format($recipientCount - $recipients->count()) }} more</span>
                        @endif
                    </p>
                    @endif
                    @if($plainBody !== '')
                    <details class="group rounded-xl border border-gray-100 bg-gray-50">
                        <summary class="cursor-pointer list-none px-3 py-2 text-xs font-medium text-gray-600 hover:text-gray-800">
                            <span class="group-open:hidden">Show message</span>
                            <span class="hidden group-open:inline">Hide message</span>
                            <span class="ml-1 font-normal text-gray-400">— {{ \Illuminate\Support\Str::limit($plainBody, 72) }}</span>
                        </summary>
                        <div class="preview-email-body border-t border-gray-100 px-3 py-3 text-sm text-gray-700 leading-relaxed bg-white rounded-b-xl">
                            {!! $body !!}
                        </div>
                    </details>
                    @endif
                </div>
                @empty
                <div class="px-5 py-10 text-center text-sm text-gray-400">No bulk emails sent yet.</div>
                @endforelse
            </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
<script>
function updateEmailPreview() {
    const headline = document.getElementById('preview-headline');
    const body = document.getElementById('preview-body');
    const subject = document.getElementById('subject')?.value.trim();
    headline.textContent = subject || 'Your subject will appear here';
    let html = '';
    if (window.tinymce && tinymce.get('body')) {
        html = tinymce.get('body').getContent() || '';
    } else {
        html = document.getElementById('body')?.value || '';
    }
    const text = html.replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ').trim();
    body.innerHTML = text ? html : '<p class="text-gray-400">Start writing to see the preview.</p>';
}
tinymce.init({
    selector: 'textarea.tinymce-email',
    height: 360,
    menubar: false,
    plugins: 'lists link wordcount',
    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | removeformat',
    branding: false,
    entity_encoding: 'raw',
    placeholder: 'Write your message here. You can use @{{name}}, @{{first_name}}, or @{{email}}.',
    content_style: 'body { font-family: Segoe UI, Tahoma, sans-serif; font-size: 15px; line-height: 1.75; color: #3f3f46; } a { color: #d97706; }',
    setup: function (editor) {
        editor.on('Change KeyUp SetContent Undo Redo', updateEmailPreview);
    }
});
document.getElementById('subject')?.addEventListener('input', updateEmailPreview);
(function () {
    const form = document.getElementById('bulk-email-form');
    const picker = document.getElementById('specific-picker');
    const searchEl = document.getElementById('user-search');
    const dropdown = document.getElementById('user-dropdown');
    const chips = document.getElementById('selected-users');
    const countEl = document.getElementById('recipient-count');
    const labelEl = document.getElementById('recipient-label');
    const previewUrl = @json(route('admin.bulk-emails.preview'));
    const searchUrl = @json(route('admin.bulk-emails.search'));
    let debounceTimer = null;

    function selectedIds() {
        return Array.from(chips.querySelectorAll('input[name="user_ids[]"]')).map((el) => el.value);
    }

    function audience() {
        const checked = form.querySelector('input[name="audience"]:checked');
        return checked ? checked.value : 'all';
    }

    function paintAudienceCards() {
        document.querySelectorAll('.audience-card').forEach((card) => {
            const on = card.querySelector('input').checked;
            card.classList.toggle('border-indigo-500', on);
            card.classList.toggle('bg-indigo-50', on);
            card.classList.toggle('text-indigo-700', on);
            card.classList.toggle('border-gray-200', !on);
            card.classList.toggle('text-gray-600', !on);
        });
        picker.classList.toggle('hidden', audience() !== 'specific');
    }

    async function refreshCount() {
        const params = new URLSearchParams({ audience: audience() });
        selectedIds().forEach((id) => params.append('user_ids[]', id));
        try {
            const res = await fetch(previewUrl + '?' + params.toString(), {
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) return;
            const data = await res.json();
            countEl.textContent = Number(data.count || 0).toLocaleString('en-IN');
            labelEl.textContent = data.label || '';
        } catch (e) {}
    }

    function addUser(user) {
        if (selectedIds().includes(String(user.id))) return;
        const chip = document.createElement('span');
        chip.className = 'selected-chip inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 text-xs font-medium px-2.5 py-1.5 rounded-lg border border-indigo-100';
        chip.dataset.id = user.id;
        chip.innerHTML = `${escapeHtml(user.name || user.email)} <button type="button" class="remove-user text-indigo-400 hover:text-indigo-700" aria-label="Remove">&times;</button><input type="hidden" name="user_ids[]" value="${user.id}">`;
        chips.appendChild(chip);
        searchEl.value = '';
        dropdown.classList.add('hidden');
        refreshCount();
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (ch) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[ch]));
    }

    async function searchUsers(term) {
        const params = new URLSearchParams({ search: term || '' });
        const res = await fetch(searchUrl + '?' + params.toString(), { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return [];
        return await res.json();
    }

    function renderDropdown(users) {
        const picked = new Set(selectedIds());
        const remaining = users.filter((u) => !picked.has(String(u.id)));
        if (!remaining.length) {
            dropdown.innerHTML = '<div class="px-3 py-3 text-xs text-gray-400">No matching users</div>';
            dropdown.classList.remove('hidden');
            return;
        }
        dropdown.innerHTML = remaining.map((u) => `
            <button type="button" class="user-option w-full text-left px-3 py-2.5 hover:bg-indigo-50 transition-colors border-b border-gray-50 last:border-0"
                    data-id="${u.id}" data-name="${escapeHtml(u.name)}" data-email="${escapeHtml(u.email)}" data-role="${escapeHtml(u.role)}">
                <p class="text-sm font-medium text-gray-800 truncate">${escapeHtml(u.name)}</p>
                <p class="text-xs text-gray-400 truncate">${escapeHtml(u.email)} · ${escapeHtml(u.role)}</p>
            </button>
        `).join('');
        dropdown.classList.remove('hidden');
    }

    document.getElementById('audience-options').addEventListener('change', function () {
        paintAudienceCards();
        refreshCount();
    });

    chips.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-user');
        if (!btn) return;
        btn.closest('.selected-chip')?.remove();
        refreshCount();
    });

    searchEl.addEventListener('input', function () {
        const term = this.value.trim();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            if (term.length < 1) {
                dropdown.classList.add('hidden');
                return;
            }
            renderDropdown(await searchUsers(term));
        }, 250);
    });

    searchEl.addEventListener('focus', async function () {
        renderDropdown(await searchUsers(this.value.trim()));
    });

    dropdown.addEventListener('click', function (e) {
        const btn = e.target.closest('.user-option');
        if (!btn) return;
        addUser({ id: btn.dataset.id, name: btn.dataset.name, email: btn.dataset.email, role: btn.dataset.role });
    });

    document.addEventListener('click', function (e) {
        if (!picker.contains(e.target)) dropdown.classList.add('hidden');
    });

    form.addEventListener('submit', function (e) {
        if (window.tinymce) tinymce.triggerSave();
        const raw = (document.getElementById('body')?.value || '').replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ').trim();
        if (!raw) {
            e.preventDefault();
            alert('Enter a message.');
            return;
        }
        const n = parseInt(String(countEl.textContent).replace(/,/g, ''), 10) || 0;
        if (audience() === 'specific' && selectedIds().length === 0) {
            e.preventDefault();
            alert('Select at least one user.');
            return;
        }
        if (n < 1) {
            e.preventDefault();
            alert('No users with an email match this selection.');
            return;
        }
        if (!confirm(`Send this email to ${n} ${labelEl.textContent}? Each person gets their own copy.`)) {
            e.preventDefault();
        }
    });

    paintAudienceCards();
    refreshCount();
    updateEmailPreview();
})();
</script>
@endpush
