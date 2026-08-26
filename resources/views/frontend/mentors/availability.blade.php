{{-- resources/views/frontend/mentors/availability.blade.php --}}
@extends('frontend.layouts.app')
@section('title', 'Set Availability — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">

        <div class="dash-header flex-between">
            <div>
                <div class="dash-title">Set Availability ⏰</div>
                <div class="dash-subtitle">Add separate time windows per day (e.g. 9–10 and 11–12). Mentees only see bookable starts inside those windows.</div>
            </div>
            <div style="display:flex;gap:10px;">
                <form action="{{ route('mentor.availability.toggle-live') }}" method="POST">
                    @csrf
                    <button class="btn {{ auth()->user()->is_active ? 'btn-outline' : 'btn-success' }}" type="submit">
                        {{ auth()->user()->is_active ? '⏸ Go Unavailable' : '✅ Go Live' }}
                    </button>
                </form>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

            {{-- Weekly Schedule --}}
            <div class="card">
                <h3 style="font-size:15px;font-weight:700;margin-bottom:4px;">Weekly Schedule</h3>
                <p style="font-size:13px;color:var(--text-2);margin-bottom:24px;">
                    Enable a day, then add slots of any length. Example: <strong>09:00–10:00</strong> (60 min) then <strong>10:00–10:30</strong> (30 min). Adjacent times are allowed; overlapping times are not.
                </p>

                <form action="{{ route('mentor.availability.update') }}" method="POST"
                      data-ajax-form="{{ route('mentor.availability.update') }}"
                      data-success="Availability saved successfully!">
                    @csrf

                    @php
                    $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                    $defaultSlots = $availability ?? [];
                    @endphp

                    @foreach($days as $day)
                    @php
                    $dayKey = strtolower($day);
                    $dayData = $defaultSlots[$dayKey] ?? ['enabled' => false, 'ranges' => [['from' => '09:00', 'to' => '10:00']], 'slot_duration' => 30];
                    $ranges = $dayData['ranges'] ?? [['from' => $dayData['from'] ?? '09:00', 'to' => $dayData['to'] ?? '10:00']];
                    if ($ranges === []) {
                        $ranges = [['from' => '09:00', 'to' => '10:00']];
                    }
                    @endphp
                    <div class="avail-day" id="day-{{ $dayKey }}" data-day="{{ $dayKey }}"
                         style="padding:16px 0;border-bottom:1px solid var(--border);">
                        <div style="display:flex;align-items:center;gap:16px;margin-bottom:10px;">
                            <label class="toggle-switch" style="flex-shrink:0;">
                                <input type="hidden" name="days[{{ $dayKey }}][enabled]" value="0">
                                <input type="checkbox" name="days[{{ $dayKey }}][enabled]" value="1"
                                       {{ ($dayData['enabled'] ?? false) ? 'checked' : '' }}
                                       onchange="toggleDay('{{ $dayKey }}', this.checked)">
                                <span class="toggle-slider"></span>
                            </label>
                            <div style="min-width:90px;font-size:13px;font-weight:600;{{ !($dayData['enabled'] ?? false) ? 'color:var(--text-3)' : '' }}"
                                 id="daylabel-{{ $dayKey }}">{{ $day }}</div>
                            <div id="dayoff-{{ $dayKey }}" style="font-size:12px;color:var(--text-3);margin-left:auto;{{ ($dayData['enabled'] ?? false) ? 'display:none' : '' }}">Off</div>
                        </div>

                        <div id="dayslots-{{ $dayKey }}" class="avail-ranges"
                             style="{{ !($dayData['enabled'] ?? false) ? 'opacity:.35;pointer-events:none' : '' }}">
                            @foreach($ranges as $ri => $range)
                            @php
                                $mins = (int) ($range['duration'] ?? 0);
                                if ($mins <= 0 && !empty($range['from']) && !empty($range['to'])) {
                                    try {
                                        $mins = \Carbon\Carbon::createFromFormat('H:i', $range['from'])->diffInMinutes(\Carbon\Carbon::createFromFormat('H:i', $range['to']));
                                    } catch (\Throwable) { $mins = 0; }
                                }
                            @endphp
                            <div class="avail-range-row" data-range-row>
                                <input type="time" name="days[{{ $dayKey }}][ranges][{{ $ri }}][from]"
                                       class="form-input" style="width:120px;padding:7px 10px;"
                                       value="{{ $range['from'] ?? '09:00' }}" required
                                       onchange="refreshDurationBadge(this)">
                                <span style="font-size:13px;color:var(--text-3);">to</span>
                                <input type="time" name="days[{{ $dayKey }}][ranges][{{ $ri }}][to]"
                                       class="form-input" style="width:120px;padding:7px 10px;"
                                       value="{{ $range['to'] ?? '10:00' }}" required
                                       onchange="refreshDurationBadge(this)">
                                <span class="avail-duration-badge">{{ $mins }} min</span>
                                <button type="button" class="btn btn-ghost btn-sm avail-remove-range"
                                        onclick="removeRange(this)" title="Remove slot"
                                        style="color:var(--error);{{ count($ranges) <= 1 ? 'visibility:hidden' : '' }}">✕</button>
                            </div>
                            @endforeach
                            <button type="button" class="btn btn-outline btn-sm" style="margin-top:4px;"
                                    onclick="addRange('{{ $dayKey }}')">+ Add slot</button>
                        </div>
                    </div>
                    @endforeach

                    <div style="display:flex;justify-content:flex-end;margin-top:20px;gap:10px;">
                        <button type="button" class="btn btn-ghost" onclick="applyWeekdays()">Apply sample Mon–Fri</button>
                        <button type="submit" class="btn btn-primary">💾 Save Schedule</button>
                    </div>
                </form>
            </div>

            {{-- Right panel --}}
            <div style="display:flex;flex-direction:column;gap:20px;">

                {{-- Temporarily hidden — Session Settings
                <div class="card">
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Session Settings</h3>
                    <form action="{{ route('mentor.availability.settings') }}" method="POST"
                          data-ajax-form="{{ route('mentor.availability.settings') }}"
                          data-success="Settings saved!">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Buffer between sessions</label>
                            <select name="buffer_minutes" class="form-select">
                                <option value="0" {{ ($settings['buffer_minutes'] ?? 0) == 0 ? 'selected' : '' }}>No buffer</option>
                                <option value="15" {{ ($settings['buffer_minutes'] ?? 0) == 15 ? 'selected' : '' }}>15 minutes</option>
                                <option value="30" {{ ($settings['buffer_minutes'] ?? 0) == 30 ? 'selected' : '' }}>30 minutes</option>
                                <option value="60" {{ ($settings['buffer_minutes'] ?? 0) == 60 ? 'selected' : '' }}>1 hour</option>
                            </select>
                            <div class="form-hint">Time gap between back-to-back sessions.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Advance booking window</label>
                            <select name="advance_days" class="form-select">
                                <option value="3"  {{ ($settings['advance_days'] ?? 7) == 3  ? 'selected' : '' }}>3 days ahead</option>
                                <option value="7"  {{ ($settings['advance_days'] ?? 7) == 7  ? 'selected' : '' }}>7 days ahead</option>
                                <option value="14" {{ ($settings['advance_days'] ?? 7) == 14 ? 'selected' : '' }}>14 days ahead</option>
                                <option value="30" {{ ($settings['advance_days'] ?? 7) == 30 ? 'selected' : '' }}>30 days ahead</option>
                            </select>
                            <div class="form-hint">How far in advance mentees can book.</div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Min notice before session</label>
                            <select name="min_notice_hours" class="form-select">
                                <option value="1"  {{ ($settings['min_notice_hours'] ?? 2) == 1  ? 'selected' : '' }}>1 hour</option>
                                <option value="2"  {{ ($settings['min_notice_hours'] ?? 2) == 2  ? 'selected' : '' }}>2 hours</option>
                                <option value="6"  {{ ($settings['min_notice_hours'] ?? 2) == 6  ? 'selected' : '' }}>6 hours</option>
                                <option value="12" {{ ($settings['min_notice_hours'] ?? 2) == 12 ? 'selected' : '' }}>12 hours</option>
                                <option value="24" {{ ($settings['min_notice_hours'] ?? 2) == 24 ? 'selected' : '' }}>24 hours</option>
                            </select>
                            <div class="form-hint">Minimum notice required to book a session.</div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-full" style="margin-top:16px;">Save Settings</button>
                    </form>
                </div>
                --}}

                <div class="card">
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Block Specific Dates</h3>
                    <p style="font-size:12px;color:var(--text-2);margin-bottom:14px;">Mark dates when you're unavailable (vacations, holidays, etc.)</p>
                    <form action="{{ route('mentor.availability.block') }}" method="POST"
                          data-ajax-form="{{ route('mentor.availability.block') }}"
                          data-success="Date blocked!"
                          data-redirect="{{ route('mentor.availability') }}"
                          data-reset-on-success id="block-form">
                        @csrf
                        <div style="display:flex;gap:8px;margin-bottom:12px;">
                            <input type="date" name="blocked_date" class="form-input" id="block-date-input" min="{{ date('Y-m-d') }}" required>
                            <button type="submit" class="btn btn-outline btn-sm">Block</button>
                        </div>
                    </form>
                    <div id="blocked-dates-list">
                        @forelse($blockedDates ?? [] as $blocked)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px;">
                            <span>{{ \Carbon\Carbon::parse($blocked->date)->format('D, d M Y') }}</span>
                            <form action="{{ route('mentor.availability.unblock', $blocked->date) }}" method="POST"
                                  data-ajax-form="{{ route('mentor.availability.unblock', $blocked->date) }}"
                                  data-success="Date unblocked."
                                  data-redirect="{{ route('mentor.availability') }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-ghost btn-sm" style="color:var(--error);padding:2px 8px;" type="submit">Remove</button>
                            </form>
                        </div>
                        @empty
                        <p style="font-size:12px;color:var(--text-3);text-align:center;padding:12px 0;">No dates blocked</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<style>
.toggle-switch { position:relative; display:inline-block; width:42px; height:24px; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; cursor:pointer; inset:0; background:var(--border); border-radius:999px; transition:.3s; }
.toggle-slider::before { content:''; position:absolute; left:3px; bottom:3px; width:18px; height:18px; background:#fff; border-radius:50%; transition:.3s; }
.toggle-switch input:checked + .toggle-slider { background:var(--brand); }
.toggle-switch input:checked + .toggle-slider::before { transform:translateX(18px); }
.avail-ranges { display:flex; flex-direction:column; gap:8px; padding-left:58px; }
.avail-range-row { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.avail-duration-badge {
    display:inline-flex; align-items:center; min-height:28px; padding:0 10px;
    border-radius:8px; background:rgba(245,158,11,.12); color:var(--brand);
    font-size:12px; font-weight:700; white-space:nowrap;
}
@media (max-width: 900px) {
    .dash-content > div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
    .avail-ranges { padding-left: 0; }
}
</style>
@endsection

@push('scripts')
<script>
function toggleDay(day, enabled) {
    const slots = document.getElementById('dayslots-' + day);
    const label = document.getElementById('daylabel-' + day);
    const off   = document.getElementById('dayoff-' + day);
    if (enabled) {
        slots.style.opacity = '1';
        slots.style.pointerEvents = 'auto';
        label.style.color = '';
        off.style.display = 'none';
    } else {
        slots.style.opacity = '.35';
        slots.style.pointerEvents = 'none';
        label.style.color = 'var(--text-3)';
        off.style.display = 'block';
    }
}

function minutesBetween(from, to) {
    if (!from || !to) return 0;
    const [fh, fm] = from.split(':').map(Number);
    const [th, tm] = to.split(':').map(Number);
    return (th * 60 + tm) - (fh * 60 + fm);
}

function refreshDurationBadge(el) {
    const row = el.closest('[data-range-row]');
    if (!row) return;
    const times = row.querySelectorAll('input[type="time"]');
    const badge = row.querySelector('.avail-duration-badge');
    if (!times[0] || !times[1] || !badge) return;
    const mins = minutesBetween(times[0].value, times[1].value);
    badge.textContent = (mins > 0 ? mins : 0) + ' min';
}

function reindexRanges(day) {
    const wrap = document.getElementById('dayslots-' + day);
    const rows = wrap.querySelectorAll('[data-range-row]');
    rows.forEach((row, i) => {
        const times = row.querySelectorAll('input[type="time"]');
        if (times[0]) times[0].name = `days[${day}][ranges][${i}][from]`;
        if (times[1]) times[1].name = `days[${day}][ranges][${i}][to]`;
        const removeBtn = row.querySelector('.avail-remove-range');
        if (removeBtn) removeBtn.style.visibility = rows.length <= 1 ? 'hidden' : 'visible';
        refreshDurationBadge(times[0] || row);
    });
}

function addRange(day) {
    const wrap = document.getElementById('dayslots-' + day);
    const rows = wrap.querySelectorAll('[data-range-row]');
    const lastTo = rows.length
        ? rows[rows.length - 1].querySelectorAll('input[type="time"]')[1]?.value || '10:00'
        : '09:00';

    let nextFrom = lastTo;
    let nextTo = '10:30';
    try {
        const [h, m] = lastTo.split(':').map(Number);
        const start = new Date(2000, 0, 1, h, m);
        const end = new Date(start.getTime());
        end.setMinutes(end.getMinutes() + 30);
        const fmt = d => String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        nextFrom = fmt(start);
        nextTo = fmt(end);
        if (nextFrom >= '23:30') { nextFrom = '09:00'; nextTo = '10:00'; }
    } catch (e) {}

    const idx = rows.length;
    const mins = minutesBetween(nextFrom, nextTo);
    const row = document.createElement('div');
    row.className = 'avail-range-row';
    row.setAttribute('data-range-row', '');
    row.innerHTML = `
        <input type="time" name="days[${day}][ranges][${idx}][from]" class="form-input" style="width:120px;padding:7px 10px;" value="${nextFrom}" required onchange="refreshDurationBadge(this)">
        <span style="font-size:13px;color:var(--text-3);">to</span>
        <input type="time" name="days[${day}][ranges][${idx}][to]" class="form-input" style="width:120px;padding:7px 10px;" value="${nextTo}" required onchange="refreshDurationBadge(this)">
        <span class="avail-duration-badge">${mins} min</span>
        <button type="button" class="btn btn-ghost btn-sm avail-remove-range" onclick="removeRange(this)" title="Remove slot" style="color:var(--error);">✕</button>
    `;
    const addBtn = wrap.querySelector('button.btn-outline');
    wrap.insertBefore(row, addBtn);
    reindexRanges(day);
}

function removeRange(btn) {
    const row = btn.closest('[data-range-row]');
    const wrap = row?.closest('.avail-ranges');
    const day = wrap?.id?.replace('dayslots-', '');
    if (!row || !wrap || !day) return;
    if (wrap.querySelectorAll('[data-range-row]').length <= 1) return;
    row.remove();
    reindexRanges(day);
}

function applyWeekdays() {
    ['monday','tuesday','wednesday','thursday','friday'].forEach(day => {
        const cb = document.querySelector(`input[type="checkbox"][name="days[${day}][enabled]"]`);
        if (cb && !cb.checked) { cb.checked = true; toggleDay(day, true); }
        const wrap = document.getElementById('dayslots-' + day);
        wrap.querySelectorAll('[data-range-row]').forEach((row, i) => { if (i > 0) row.remove(); });
        const times = wrap.querySelectorAll('[data-range-row] input[type="time"]');
        if (times[0]) times[0].value = '09:00';
        if (times[1]) times[1].value = '10:00';
        refreshDurationBadge(times[0]);
        if (wrap.querySelectorAll('[data-range-row]').length < 2) {
            addRange(day);
            const rows = wrap.querySelectorAll('[data-range-row]');
            const last = rows[rows.length - 1]?.querySelectorAll('input[type="time"]');
            if (last?.[0]) last[0].value = '10:00';
            if (last?.[1]) last[1].value = '10:30';
            refreshDurationBadge(last?.[0]);
        }
        reindexRanges(day);
    });
    ['saturday','sunday'].forEach(day => {
        const cb = document.querySelector(`input[type="checkbox"][name="days[${day}][enabled]"]`);
        if (cb && cb.checked) { cb.checked = false; toggleDay(day, false); }
    });
    if (typeof showToast === 'function') {
        showToast('info', 'Mon–Fri sample slots (9–10 & 10–10:30) applied. Save to confirm.');
    }
}
</script>
@endpush
