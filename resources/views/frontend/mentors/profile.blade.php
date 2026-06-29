@extends('frontend.layouts.app')
@section('title', ($mentor->name ?? 'Mentor') . ' — AcharyaSetu')

@section('content')
<div style="padding-top:var(--nav-h);" data-mentor-id="{{ $mentor->id ?? 1 }}">

    {{-- Profile Hero --}}
    <div class="profile-hero">
        <div class="container">
            <div class="profile-hero-inner">
                <div class="profile-avatar-xl">
                    @if($mentor->avatar_url ?? false)
                        <img src="{{ $mentor->avatar_url }}" alt="{{ $mentor->name }}">
                    @else
                        {{ strtoupper(substr($mentor->name ?? 'M', 0, 1)) }}
                    @endif
                </div>
                <div style="flex:1;padding-bottom:20px;">
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:6px;">
                        <h1 style="font-size:22px;font-weight:800;">{{ $mentor->name ?? 'Mentor Name' }}</h1>
                        @if(($mentor->mentor_status ?? '') === 'approved')
                        <span class="badge badge-success">✓ Verified</span>
                        @endif
                        <span class="badge badge-brand">{{ $mentor->experience_years ?? 0 }}+ yrs exp</span>
                    </div>
                    <div style="font-size:14px;color:var(--text-2);margin-bottom:10px;">
                        {{ $mentor->designation ?? 'Mentor' }}{{ ($mentor->company ?? '') ? ' · '.$mentor->company : '' }}
                    </div>
                    <div style="display:flex;gap:24px;flex-wrap:wrap;font-size:13px;color:var(--text-2);">
                        <span>⭐ <strong>{{ number_format($mentor->rating ?? 4.9, 1) }}</strong> rating</span>
                        <span>📅 <strong>{{ $mentor->total_sessions ?? 0 }}</strong> sessions</span>
                        <span>💰 <strong>₹{{ $mentor->rate_per_minute ?? 10 }}/min</strong></span>
                    </div>
                </div>
                <div style="padding-bottom:20px;">
                    <button class="btn btn-primary btn-lg" onclick="scrollToBook()">
                        📅 Book a Session
                    </button>
                </div>
            </div>
            <div class="profile-hero-tabs">
                <div class="profile-tab active" onclick="showSection('about')">About</div>
                <div class="profile-tab" onclick="showSection('sessions')">Sessions</div>
                <div class="profile-tab" onclick="showSection('reviews')">Reviews</div>
            </div>
        </div>
    </div>

    {{-- Main --}}
    <div class="container" style="padding-top:32px;padding-bottom:60px;">
        <div style="display:grid;grid-template-columns:1fr 340px;gap:32px;align-items:start;">

            {{-- Left content --}}
            <div>
                {{-- About --}}
                <div id="section-about">
                    <div class="card" style="margin-bottom:20px;">
                        <h3 style="font-size:16px;font-weight:700;margin-bottom:14px;">About</h3>
                        <p style="font-size:14px;color:var(--text-2);line-height:1.8;">
                            {{ $mentor->bio ?? 'Experienced professional passionate about mentoring the next generation of talent. With over a decade in the industry, I\'ve helped hundreds of mentees achieve their career goals.' }}
                        </p>
                    </div>

                    <div class="card" style="margin-bottom:20px;">
                        <h3 style="font-size:16px;font-weight:700;margin-bottom:14px;">Expertise</h3>
                        <div class="chip-wrap">
                            @foreach(($mentor->expertise ?? ['Product Strategy','User Research','Agile','SQL','Python','Leadership']) as $skill)
                            <span class="chip selected">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="card" style="margin-bottom:20px;">
                        <h3 style="font-size:16px;font-weight:700;margin-bottom:14px;">What You'll Get</h3>
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            @foreach([
                                ['🎯','Personalized guidance tailored to your specific goals and challenges'],
                                ['📋','Action items and roadmap for your next 30-90 days'],
                                ['📁','Session notes and resources shared after every call'],
                                ['💡','Industry insights and network introductions where applicable'],
                            ] as [$icon, $text])
                            <div style="display:flex;gap:12px;align-items:flex-start;">
                                <span style="font-size:18px;flex-shrink:0;">{{ $icon }}</span>
                                <span style="font-size:13px;color:var(--text-2);">{{ $text }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Reviews --}}
                <div id="section-reviews" class="hidden">
                    <div class="card" style="margin-bottom:16px;">
                        <div style="display:flex;align-items:center;gap:24px;margin-bottom:20px;">
                            <div style="text-align:center;">
                                <div style="font-size:48px;font-weight:900;color:var(--brand);line-height:1;">{{ number_format($mentor->rating ?? 4.9, 1) }}</div>
                                <div class="stars">★★★★★</div>
                                <div style="font-size:12px;color:var(--text-2);">{{ $mentor->total_sessions ?? 0 }} reviews</div>
                            </div>
                        </div>
                    </div>

                    @foreach($reviews ?? [] as $review)
                    <div class="testimonial-card" style="margin-bottom:12px;">
                        <div class="stars">{{ str_repeat('★', $review->overall_rating) }}</div>
                        <p class="testimonial-text">{{ $review->review_text }}</p>
                        <div class="testimonial-author">
                            <div class="author-avatar">{{ strtoupper(substr($review->reviewer->name, 0, 1)) }}</div>
                            <div>
                                <div class="author-name">{{ $review->reviewer->name }}</div>
                                <div class="author-role">{{ $review->submitted_at?->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Booking Widget (sticky) --}}
            <div id="book-section" style="position:sticky;top:calc(var(--nav-h) + 20px);">
                <div class="card">
                    <h3 style="font-size:16px;font-weight:700;margin-bottom:4px;">Book a Session</h3>
                    <p style="font-size:12px;color:var(--text-2);margin-bottom:20px;">₹{{ $mentor->rate_per_minute ?? 10 }}/min · Free cancellation</p>

                    {{-- Date Grid --}}
                    <p class="label-caps" style="margin-bottom:10px;">Choose Date</p>
                    <div id="dateGrid" class="calendar-grid"></div>

                    {{-- Time Slots --}}
                    <p class="label-caps" style="margin:16px 0 10px;">Available Times</p>
                    <div id="timeGrid" class="time-grid">
                        <div style="grid-column:1/-1;text-align:center;padding:12px;font-size:12px;color:var(--text-3);">Select a date first</div>
                    </div>

                    {{-- Duration --}}
                    <p class="label-caps" style="margin:16px 0 10px;">Duration</p>
                    <div class="duration-btns">
                        <div class="duration-btn selected" data-min="30" onclick="BookingWidget.setDuration(30)">30 min<br><small style="font-size:10px;color:inherit;">₹{{ ($mentor->rate_per_minute ?? 10) * 30 }}</small></div>
                        <div class="duration-btn" data-min="60" onclick="BookingWidget.setDuration(60)">60 min<br><small style="font-size:10px;color:inherit;">₹{{ ($mentor->rate_per_minute ?? 10) * 60 }}</small></div>
                        <div class="duration-btn" data-min="90" onclick="BookingWidget.setDuration(90)">90 min<br><small style="font-size:10px;color:inherit;">₹{{ ($mentor->rate_per_minute ?? 10) * 90 }}</small></div>
                    </div>

                    {{-- Summary --}}
                    <div class="booking-summary" style="margin-top:16px;">
                        <div class="booking-summary-row"><span>Date</span><span id="bk-date">—</span></div>
                        <div class="booking-summary-row"><span>Time</span><span id="bk-time">—</span></div>
                        <div class="booking-summary-row"><span>Duration</span><span id="bk-duration">30 min</span></div>
                        <div class="booking-summary-row" style="padding-top:12px;"><span>Total</span><strong id="bk-total" style="color:var(--brand);">₹{{ ($mentor->rate_per_minute ?? 10) * 30 }}</strong></div>
                    </div>

                    <input type="hidden" id="booking-mentor-id" value="{{ $mentor->id ?? 1 }}">
                    <input type="hidden" name="booking_date">
                    <input type="hidden" name="booking_time">
                    <input type="hidden" name="booking_duration">
                    <input type="hidden" name="booking_amount">

                    <button class="btn btn-primary btn-full btn-lg" style="margin-top:16px;" onclick="confirmBooking()">
                        ✓ Book This Session
                    </button>
                    <p style="font-size:11px;color:var(--text-3);text-align:center;margin-top:8px;">Free cancellation up to 2 hrs before</p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
BookingWidget.init({{ $mentor->rate_per_minute ?? 10 }});

function scrollToBook() {
    document.getElementById('book-section')?.scrollIntoView({ behavior:'smooth', block:'start' });
}

function showSection(name) {
    document.querySelectorAll('[id^="section-"]').forEach(el => el.classList.add('hidden'));
    document.getElementById('section-' + name)?.classList.remove('hidden');
    document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
}

function confirmBooking() {
    const data = BookingWidget.getBookingData();
    if (!data) return;
    data.mentor_id = document.getElementById('booking-mentor-id').value;

    @guest
    showToast('info','Please sign in to book a session.');
    setTimeout(() => window.location.href = '/login?redirect={{ request()->path() }}', 1500);
    return;
    @endguest

    AjaxPost("{{ route('mentee.sessions.book') }}", data, {
        loader: true,
        onSuccess: res => {
            showToast('success','🎉 Session booked! Check your email for confirmation.');
            setTimeout(() => window.location.href = res.redirect || '/mentee/sessions', 2000);
        },
        onError: err => {
            if (err.status === 401) window.location.href = '/login?redirect={{ request()->path() }}';
            else showToast('error', err.message || 'Could not book. Please try again.');
        }
    });
}
</script>
@endpush