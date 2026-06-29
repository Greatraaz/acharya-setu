@extends('frontend.layouts.app')
@section('title', 'Find Mentors — AcharyaSetu')

@section('content')
<div style="padding-top:var(--nav-h);">

    {{-- Search Header --}}
    <div style="background:var(--bg-2);border-bottom:1px solid var(--border);padding:32px 0;">
        <div class="container">
            <h1 style="font-size:28px;font-weight:800;margin-bottom:8px;">Find Your Perfect Mentor</h1>
            <p style="font-size:14px;color:var(--text-2);margin-bottom:20px;">Browse 2,400+ verified mentors across every domain</p>

            {{-- Search bar --}}
            <div class="search-hero" style="margin:0;max-width:100%;">
                <span style="font-size:18px;flex-shrink:0;">🔍</span>
                <input type="text" id="mentor-search-input" placeholder="Search by name, skill, company (e.g. DSA, Product Manager, Google)…">
                <div class="search-filters">
                    <select data-filter="experience" class="search-filter-btn" style="background:var(--bg-4);border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 12px;color:var(--text-2);font-size:12px;">
                        <option value="">Any Experience</option>
                        <option value="1-3">1–3 yrs</option>
                        <option value="3-7">3–7 yrs</option>
                        <option value="7+">7+ yrs</option>
                    </select>
                    <select data-filter="rate_max" class="search-filter-btn" style="background:var(--bg-4);border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 12px;color:var(--text-2);font-size:12px;">
                        <option value="">Any Price</option>
                        <option value="10">Under ₹10/min</option>
                        <option value="20">Under ₹20/min</option>
                        <option value="50">Under ₹50/min</option>
                    </select>
                    <button class="btn btn-primary" onclick="MentorSearch.submit()">Search</button>
                </div>
            </div>

            {{-- Quick chips --}}
            <div class="chip-wrap" style="margin-top:14px;">
                @foreach(['Product Manager','DSA & Algorithms','FAANG Prep','MBA','UX Design','Finance','Data Science','Marketing'] as $chip)
                <div class="chip" onclick="document.getElementById('mentor-search-input').value='{{ $chip }}'; MentorSearch.submit();">{{ $chip }}</div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="container" style="padding-top:32px;padding-bottom:60px;">
        <div style="display:grid;grid-template-columns:260px 1fr;gap:28px;align-items:start;">

            {{-- FILTERS SIDEBAR --}}
            <div class="filter-sidebar">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <span style="font-size:14px;font-weight:700;">Filters</span>
                    <button class="btn btn-ghost btn-sm" onclick="clearAllFilters()">Clear all</button>
                </div>

                {{-- Domain --}}
                <div class="filter-section">
                    <div class="filter-section-title">Domain</div>
                    @foreach([
                        ['engineering','Engineering & Tech'],
                        ['product','Product Management'],
                        ['design','Design & UX'],
                        ['finance','Finance & MBA'],
                        ['marketing','Marketing'],
                        ['law','Law'],
                        ['medicine','Medicine'],
                        ['arts','Arts & Humanities'],
                    ] as [$val, $label])
                    <label class="filter-option">
                        <input type="checkbox" data-filter="domain" value="{{ $val }}" @checked(request('domain') === $val)>
                        {{ $label }}
                    </label>
                    @endforeach
                </div>

                {{-- Price --}}
                <div class="filter-section">
                    <div class="filter-section-title">Price (₹/min)</div>
                    <label class="filter-option"><input type="radio" data-filter="rate_range" name="rate_range" value=""> Any</label>
                    <label class="filter-option"><input type="radio" data-filter="rate_range" name="rate_range" value="0-10"> Under ₹10</label>
                    <label class="filter-option"><input type="radio" data-filter="rate_range" name="rate_range" value="10-20"> ₹10–₹20</label>
                    <label class="filter-option"><input type="radio" data-filter="rate_range" name="rate_range" value="20-50"> ₹20–₹50</label>
                    <label class="filter-option"><input type="radio" data-filter="rate_range" name="rate_range" value="50+"> ₹50+</label>
                </div>

                {{-- Rating --}}
                <div class="filter-section">
                    <div class="filter-section-title">Minimum Rating</div>
                    <label class="filter-option"><input type="radio" data-filter="min_rating" name="min_rating" value=""> Any</label>
                    <label class="filter-option"><input type="radio" data-filter="min_rating" name="min_rating" value="4.5"> ⭐ 4.5+</label>
                    <label class="filter-option"><input type="radio" data-filter="min_rating" name="min_rating" value="4"> ⭐ 4.0+</label>
                </div>

                {{-- Experience --}}
                <div class="filter-section">
                    <div class="filter-section-title">Experience</div>
                    <label class="filter-option"><input type="checkbox" data-filter="exp" value="1-3"> 1–3 years</label>
                    <label class="filter-option"><input type="checkbox" data-filter="exp" value="3-7"> 3–7 years</label>
                    <label class="filter-option"><input type="checkbox" data-filter="exp" value="7-15"> 7–15 years</label>
                    <label class="filter-option"><input type="checkbox" data-filter="exp" value="15+"> 15+ years</label>
                </div>

                {{-- Session type --}}
                <div class="filter-section">
                    <div class="filter-section-title">Session Type</div>
                    <label class="filter-option"><input type="checkbox" data-filter="session_type" value="video"> 🎥 Video Call</label>
                    <label class="filter-option"><input type="checkbox" data-filter="session_type" value="audio"> 🎙️ Audio Only</label>
                    <label class="filter-option"><input type="checkbox" data-filter="session_type" value="chat"> 💬 Chat</label>
                </div>

                {{-- Availability --}}
                <div class="filter-section">
                    <div class="filter-section-title">Availability</div>
                    <label class="filter-option"><input type="checkbox" data-filter="availability" value="today"> Available Today</label>
                    <label class="filter-option"><input type="checkbox" data-filter="availability" value="weekend"> Weekends</label>
                    <label class="filter-option"><input type="checkbox" data-filter="availability" value="evening"> Evenings (6–10 PM)</label>
                </div>
            </div>

            {{-- RESULTS --}}
            <div>
                {{-- Results header --}}
                <div class="flex-between" style="margin-bottom:20px;flex-wrap:wrap;gap:12px;">
                    <div style="font-size:13px;color:var(--text-2);">
                        Showing <strong id="mentor-count" style="color:var(--text);">…</strong> mentors
                    </div>
                    <select data-sort-select style="background:var(--bg-3);border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 12px;font-size:13px;color:var(--text);cursor:pointer;">
                        <option value="best">Best Match</option>
                        <option value="rating">Highest Rated</option>
                        <option value="rate_asc">Lowest Price</option>
                        <option value="rate_desc">Highest Price</option>
                        <option value="sessions">Most Sessions</option>
                    </select>
                </div>

                {{-- MENTOR GRID --}}
                <div id="mentors-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
                    {{-- Initial load from server --}}
                    @foreach($mentors ?? [] as $mentor)
                    <div class="mentor-card">
                        <div class="mentor-card-head">
                            <div class="mentor-avatar-lg">
                                @if($mentor->avatar_url)<img src="{{ $mentor->avatar_url }}" alt="{{ $mentor->name }}">
                                @else{{ strtoupper(substr($mentor->name,0,1)) }}@endif
                            </div>
                            <div class="mentor-card-info">
                                <div class="mentor-card-name">{{ $mentor->name }}</div>
                                <div class="mentor-card-role">{{ $mentor->designation }}{{ $mentor->company ? ' · '.$mentor->company : '' }}</div>
                            </div>
                        </div>
                        <div class="mentor-card-bio">{{ Str::limit($mentor->bio, 85) }}</div>
                        <div class="mentor-tags">
                            @foreach(array_slice($mentor->expertise ?? [],0,4) as $tag)
                            <span class="tag">{{ $tag }}</span>
                            @endforeach
                        </div>
                        <div class="mentor-card-meta">
                            <span class="mentor-rate">₹{{ $mentor->rate_per_minute }}/min</span>
                            <span class="mentor-rating">⭐ {{ number_format($mentor->rating,1) }} ({{ $mentor->total_sessions }})</span>
                        </div>
                        <div class="mentor-card-actions">
                            <a href="/mentors/{{ $mentor->id }}" class="btn btn-outline btn-sm">View Profile</a>
                            <button class="btn btn-primary btn-sm" onclick="openBookingModal({{ $mentor->id }},'{{ addslashes($mentor->name) }}',{{ $mentor->rate_per_minute }})">Book Session</button>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div id="pagination-wrap" class="pagination" style="margin-top:32px;justify-content:center;"></div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════ BOOKING MODAL ═══════════════════ --}}
<div id="booking-modal" class="modal-overlay">
    <div class="modal modal-lg">
        <div class="modal-header">
            <span class="modal-title" id="booking-mentor-name">Book a Session</span>
            <button class="modal-close" onclick="closeModal('booking-modal')">✕</button>
        </div>
        <div class="modal-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:28px;">
                {{-- Left: Picker --}}
                <div>
                    <p class="label-caps" style="margin-bottom:12px;">Select Date</p>
                    <div id="dateGrid" class="calendar-grid"></div>

                    <p class="label-caps" style="margin:20px 0 12px;">Select Time</p>
                    <div id="timeGrid" class="time-grid">
                        <div class="text-sm text-muted" style="grid-column:1/-1;">Pick a date first</div>
                    </div>

                    <p class="label-caps" style="margin:20px 0 12px;">Duration</p>
                    <div class="duration-btns">
                        <div class="duration-btn selected" data-min="30" onclick="BookingWidget.setDuration(30)">30 min</div>
                        <div class="duration-btn" data-min="60" onclick="BookingWidget.setDuration(60)">60 min</div>
                        <div class="duration-btn" data-min="90" onclick="BookingWidget.setDuration(90)">90 min</div>
                    </div>

                    <div class="form-group" style="margin-top:20px;">
                        <label class="form-label">Session Goal (optional)</label>
                        <textarea name="agenda" class="form-input" rows="2" id="booking-agenda"
                                  placeholder="What do you want to achieve in this session?"></textarea>
                    </div>
                </div>

                {{-- Right: Summary --}}
                <div>
                    <p class="label-caps" style="margin-bottom:12px;">Booking Summary</p>
                    <div class="booking-summary">
                        <div class="booking-summary-row"><span>Mentor</span><strong id="bk-mentor">—</strong></div>
                        <div class="booking-summary-row"><span>Date</span><strong id="bk-date">—</strong></div>
                        <div class="booking-summary-row"><span>Time</span><strong id="bk-time">—</strong></div>
                        <div class="booking-summary-row"><span>Duration</span><strong id="bk-duration">30 min</strong></div>
                        <div class="booking-summary-row"><span>Rate</span><strong id="bk-rate">—</strong></div>
                        <div class="booking-summary-row"><span>Total</span><strong id="bk-total" style="color:var(--brand);font-size:18px;">—</strong></div>
                    </div>

                    <div style="background:var(--success-muted);border:1px solid rgba(34,197,94,.25);border-radius:var(--radius);padding:12px;margin-top:16px;font-size:12px;color:var(--text-2);">
                        ✅ Deducted from your wallet balance. Free cancellation up to 2 hours before the session.
                    </div>

                    <input type="hidden" name="booking_mentor_id" id="booking-mentor-id">
                    <input type="hidden" name="booking_date">
                    <input type="hidden" name="booking_time">
                    <input type="hidden" name="booking_duration">
                    <input type="hidden" name="booking_amount">
                </div>
            </div>

            <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border);display:flex;gap:12px;justify-content:flex-end;">
                <button class="btn btn-ghost" onclick="closeModal('booking-modal')">Cancel</button>
                <button class="btn btn-primary btn-lg" id="confirm-booking-btn" onclick="confirmBooking()">
                    ✓ Confirm & Book Session
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Init search on page load
MentorSearch.init();
document.getElementById('mentor-count').textContent = '{{ $mentors->total() ?? count($mentors ?? []) }}';

function clearAllFilters() {
    document.querySelectorAll('[data-filter]').forEach(el => {
        if (el.type === 'checkbox' || el.type === 'radio') el.checked = false;
        else if (el.tagName === 'SELECT') el.selectedIndex = 0;
    });
    document.getElementById('mentor-search-input').value = '';
    MentorSearch.submit();
}

function openBookingModal(mentorId, mentorName, ratePerMin) {
    document.getElementById('booking-mentor-id').value = mentorId;
    document.getElementById('booking-mentor-name').textContent = `Book: ${mentorName}`;
    document.getElementById('bk-mentor').textContent = mentorName;
    document.getElementById('bk-rate').textContent = `₹${ratePerMin}/min`;
    BookingWidget.init(ratePerMin);
    openModal('booking-modal');
}

function confirmBooking() {
    @guest
    showToast('info','Please sign in to book a session.');
    setTimeout(() => window.location.href = '/login?redirect=/mentors', 1500);
    return;
    @endguest

    const data = BookingWidget.getBookingData();
    if (!data) return;

    data.mentor_id = document.getElementById('booking-mentor-id').value;
    data.agenda    = document.getElementById('booking-agenda').value;

    AjaxPost('/sessions', data, {
        btn: document.getElementById('confirm-booking-btn'), loader: true,
        onSuccess: res => {
            closeModal('booking-modal');
            showToast('success','🎉 Session booked! Check your email for confirmation.');
            setTimeout(() => window.location.href = res.redirect || '/dashboard', 2000);
        },
        onError: err => {
            if (err.status === 401) {
                window.location.href = '/login?redirect=/mentors';
            } else {
                showToast('error', err.message || 'Could not book session. Please try again.');
            }
        }
    });
}
</script>
@endpush