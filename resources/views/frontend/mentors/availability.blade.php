{{-- resources/views/frontend/mentor/availability.blade.php --}}
@extends('frontend.layouts.app')
@section('title', 'Set Availability — AcharyaSetu')

@section('content')
<div class="dash-layout">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="sidebar-section-label">Overview</div>
        <a href="{{ route('mentor.dashboard') }}" class="sidebar-item">
            <span class="si-icon">📊</span> Dashboard
        </a>
        <div class="sidebar-section-label">Sessions</div>
        <a href="{{ route('mentor.sessions') }}" class="sidebar-item">
            <span class="si-icon">📅</span> My Sessions
            @if($pendingCount ?? 0)<span class="si-badge">{{ $pendingCount }}</span>@endif
        </a>
        <a href="{{ route('mentor.availability') }}" class="sidebar-item active">
            <span class="si-icon">⏰</span> Set Availability
        </a>
        <a href="#" class="sidebar-item">
            <span class="si-icon">📝</span> Session Notes
        </a>
        <div class="sidebar-section-label">Mentees</div>
        <a href="{{ route('mentor.mentees') }}" class="sidebar-item">
            <span class="si-icon">🎓</span> My Mentees
        </a>
        <a href="#" class="sidebar-item">
            <span class="si-icon">🗺️</span> Journey Tracker
        </a>
        <div class="sidebar-section-label">Content</div>
        <a href="#" class="sidebar-item"><span class="si-icon">💬</span> Community</a>
        <a href="#" class="sidebar-item"><span class="si-icon">🧠</span> Assessments</a>
        <div class="sidebar-section-label">Account</div>
        <a href="{{ route('mentor.wallet') }}" class="sidebar-item">
            <span class="si-icon">💰</span> Earnings
            <span style="margin-left:auto;font-size:11px;color:var(--success);">₹{{ number_format(auth()->user()->wallet_balance ?? 0, 0) }}</span>
        </a>
        <a href="{{ route('mentor.profile.edit') }}" class="sidebar-item">
            <span class="si-icon">✏️</span> Edit Profile
        </a>
        <form action="{{ route('logout') }}" method="POST" style="margin-top:auto;">
            @csrf
            <button class="sidebar-item w-full" style="background:none;cursor:pointer;color:var(--error);">
                <span class="si-icon">🚪</span> Sign Out
            </button>
        </form>
    </aside>

    <div class="dash-content">

        <div class="dash-header flex-between">
            <div>
                <div class="dash-title">Set Availability ⏰</div>
                <div class="dash-subtitle">Define when mentees can book sessions with you.</div>
            </div>
            <div style="display:flex;gap:10px;">
                <form action="{{ route('mentor.toggle-availability') }}" method="POST">
                    @csrf @method('PATCH')
                    <button class="btn {{ (auth()->user()->is_available ?? false) ? 'btn-outline' : 'btn-success' }}" type="submit">
                        {{ (auth()->user()->is_available ?? false) ? '⏸ Go Unavailable' : '✅ Go Live' }}
                    </button>
                </form>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

            {{-- Weekly Schedule --}}
            <div class="card">
                <h3 style="font-size:15px;font-weight:700;margin-bottom:4px;">Weekly Schedule</h3>
                <p style="font-size:13px;color:var(--text-2);margin-bottom:24px;">Set your regular availability for each day. Mentees will see these slots when booking.</p>

                <form action="{{ route('mentor.availability.save') }}" method="POST"
                      data-ajax-form="{{ route('mentor.availability.save') }}"
                      data-success="Availability saved successfully!">
                    @csrf

                    @php
                    $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                    $defaultSlots = $availability ?? [];
                    @endphp

                    @foreach($days as $day)
                    @php
                    $dayKey = strtolower($day);
                    $dayData = $defaultSlots[$dayKey] ?? ['enabled' => false, 'from' => '09:00', 'to' => '18:00'];
                    @endphp
                    <div class="avail-day" id="day-{{ $dayKey }}" style="display:flex;align-items:center;gap:16px;padding:14px 0;border-bottom:1px solid var(--border);">
                        {{-- Toggle --}}
                        <label class="toggle-switch" style="flex-shrink:0;">
                            <input type="checkbox" name="days[{{ $dayKey }}][enabled]" value="1"
                                   {{ ($dayData['enabled'] ?? false) ? 'checked' : '' }}
                                   onchange="toggleDay('{{ $dayKey }}', this.checked)">
                            <span class="toggle-slider"></span>
                        </label>
                        {{-- Day name --}}
                        <div style="min-width:80px;font-size:13px;font-weight:600;{{ !($dayData['enabled'] ?? false) ? 'color:var(--text-3)' : '' }}" id="daylabel-{{ $dayKey }}">{{ $day }}</div>
                        {{-- Time range --}}
                        <div id="dayslots-{{ $dayKey }}" style="display:flex;align-items:center;gap:10px;flex:1;{{ !($dayData['enabled'] ?? false) ? 'opacity:.35;pointer-events:none' : '' }}">
                            <input type="time" name="days[{{ $dayKey }}][from]" class="form-input" style="width:120px;padding:7px 10px;"
                                   value="{{ $dayData['from'] ?? '09:00' }}">
                            <span style="font-size:13px;color:var(--text-3);">to</span>
                            <input type="time" name="days[{{ $dayKey }}][to]" class="form-input" style="width:120px;padding:7px 10px;"
                                   value="{{ $dayData['to'] ?? '18:00' }}">
                            {{-- Slot duration --}}
                            <select name="days[{{ $dayKey }}][slot_duration]" class="form-select" style="width:130px;font-size:12px;">
                                <option value="30" {{ ($dayData['slot_duration'] ?? 30) == 30 ? 'selected' : '' }}>30-min slots</option>
                                <option value="60" {{ ($dayData['slot_duration'] ?? 30) == 60 ? 'selected' : '' }}>60-min slots</option>
                                <option value="90" {{ ($dayData['slot_duration'] ?? 30) == 90 ? 'selected' : '' }}>90-min slots</option>
                            </select>
                        </div>
                        {{-- Off label --}}
                        <div id="dayoff-{{ $dayKey }}" style="font-size:12px;color:var(--text-3);{{ ($dayData['enabled'] ?? false) ? 'display:none' : '' }}">Off</div>
                    </div>
                    @endforeach

                    <div style="display:flex;justify-content:flex-end;margin-top:20px;gap:10px;">
                        <button type="button" class="btn btn-ghost" onclick="applyWeekdays()">Apply Mon–Fri</button>
                        <button type="submit" class="btn btn-primary">💾 Save Schedule</button>
                    </div>
                </form>
            </div>

            {{-- Right panel --}}
            <div style="display:flex;flex-direction:column;gap:20px;">

                {{-- Buffer & Settings --}}
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

                {{-- Block specific dates --}}
                <div class="card">
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Block Specific Dates</h3>
                    <p style="font-size:12px;color:var(--text-2);margin-bottom:14px;">Mark dates when you're unavailable (vacations, holidays, etc.)</p>
                    <form action="{{ route('mentor.availability.block') }}" method="POST"
                          data-ajax-form="{{ route('mentor.availability.block') }}"
                          data-success="Date blocked!" id="block-form">
                        @csrf
                        <div style="display:flex;gap:8px;margin-bottom:12px;">
                            <input type="date" name="blocked_date" class="form-input" id="block-date-input" min="{{ date('Y-m-d') }}">
                            <button type="submit" class="btn btn-outline btn-sm">Block</button>
                        </div>
                    </form>
                    <div id="blocked-dates-list">
                        @forelse($blockedDates ?? [] as $blocked)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px;">
                            <span>{{ \Carbon\Carbon::parse($blocked->date)->format('D, d M Y') }}</span>
                            <form action="{{ route('mentor.availability.unblock', $blocked->id) }}" method="POST">
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

function applyWeekdays() {
    ['monday','tuesday','wednesday','thursday','friday'].forEach(day => {
        const cb = document.querySelector(`input[name="days[${day}][enabled]"]`);
        if (cb && !cb.checked) { cb.checked = true; toggleDay(day, true); }
        const from = document.querySelector(`input[name="days[${day}][from]"]`);
        const to   = document.querySelector(`input[name="days[${day}][to]"]`);
        if (from) from.value = '09:00';
        if (to)   to.value   = '18:00';
    });
    ['saturday','sunday'].forEach(day => {
        const cb = document.querySelector(`input[name="days[${day}][enabled]"]`);
        if (cb && cb.checked) { cb.checked = false; toggleDay(day, false); }
    });
    showToast('info', 'Mon–Fri 9am–6pm applied. Save to confirm.');
}
</script>
@endpush