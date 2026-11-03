@extends('frontend.layouts.app')
@php
    $hostLabel = $mentor->hostLabel();
    $isExpert = $mentor->isExpert();
    $rate = (float) ($mentor->rate_per_minute ?? 0);
    $hourly = (int) round($rate * 60);
    $prefs = is_array($mentor->preferences) ? $mentor->preferences : [];
    $expertise = array_values(array_filter($mentor->expertise ?? []));
    $strengths = array_values(array_filter($mentor->strengths ?? []));
    $modes = array_values(array_filter((array) ($prefs['session_modes'] ?? [])));
    $modeLabels = ['video' => 'Video', 'audio' => 'Audio', 'chat' => 'Chat', 'in_person' => 'In person'];
    $preferredLength = (int) ($prefs['session_length'] ?? 0);
    $preferredTime = trim((string) ($prefs['preferred_time'] ?? ''));
    $weekly = $availabilityOverview['weekly_summary'] ?? [];
    $nextOpen = collect($availabilityOverview['days'] ?? [])->firstWhere('available', true);
    $bioPlain = \App\Support\RichText::plain($mentor->bio);
    $reviewCount = $reviews->count();
    $score = $reviewCount > 0
        ? round((float) $reviews->avg('overall_rating'), 1)
        : (float) ($mentor->rating ?? 0);
    $filledStars = max(0, min(5, (int) round($score)));
    $recommendCount = $reviews->where('would_recommend', true)->count();
    $dimAvgs = [];
    foreach (['Communication' => 'communication_rating', 'Knowledge' => 'knowledge_rating', 'Punctuality' => 'punctuality_rating', 'Helpfulness' => 'helpfulness_rating'] as $label => $col) {
        $avg = $reviews->avg($col);
        if ($avg !== null) {
            $dimAvgs[$label] = round((float) $avg, 1);
        }
    }
    $linkedin = trim((string) ($mentor->linkedin ?? ''));
    if ($linkedin !== '' && ! str_starts_with($linkedin, 'http')) {
        $linkedin = 'https://'.$linkedin;
    }
    $listingUrl = route('mentors.search', $isExpert ? ['type' => 'expert'] : ['type' => 'mentor']);
    $metaBio = $bioPlain !== '' ? \Illuminate\Support\Str::limit($bioPlain, 160) : $mentor->designation.' at Vedrix';
@endphp
@section('title', ($mentor->name ?? $hostLabel).' — Vedrix')
@section('meta_description', $mentor->name.' · '.$hostLabel.($mentor->designation ? ' · '.$mentor->designation : '').'. '.$metaBio)

@section('content')
@auth
    @if(auth()->user()->role === 'mentee')
        @include('frontend.mentee.partials.bottom-nav')
    @endif
@endauth

<div class="mp" data-mentor-id="{{ $mentor->id }}">
    <div class="container mp-wrap">
        <nav class="mp-crumb" aria-label="Breadcrumb">
            <a href="{{ $listingUrl }}">{{ $isExpert ? 'Experts' : 'Mentors' }}</a>
            <span aria-hidden="true">/</span>
            <span>{{ $mentor->name }}</span>
        </nav>

        <header class="mp-hero">
            <div class="mp-hero__avatar profile-avatar-xl">
                @if($mentor->avatar_url)
                    <img src="{{ $mentor->avatar_url }}" alt="{{ $mentor->name }}">
                @else
                    {{ strtoupper(substr($mentor->name ?? 'M', 0, 1)) }}
                @endif
            </div>

            <div class="mp-hero__body">
                <div class="mp-hero__badges">
                    <span class="mp-pill {{ $isExpert ? 'mp-pill--violet' : 'mp-pill--brand' }}">{{ $hostLabel }}</span>
                    @if(($mentor->mentor_status ?? '') === 'approved')
                    <span class="mp-pill mp-pill--ok">Verified</span>
                    @endif
                    @if((int) ($mentor->experience_years ?? 0) > 0)
                    <span class="mp-pill">{{ (int) $mentor->experience_years }}+ yrs experience</span>
                    @endif
                </div>
                <h1 class="mp-hero__name">{{ $mentor->name }}</h1>
                <p class="mp-hero__role">
                    {{ $mentor->designation ?: $hostLabel }}
                    @if($mentor->company) <span>· {{ $mentor->company }}</span> @endif
                    @if($mentor->field) <span>· {{ $mentor->field }}</span> @endif
                </p>
                @if($mentor->location || $nextOpen)
                <div class="mp-hero__meta">
                    @if($mentor->location)
                    <span>{{ $mentor->location }}</span>
                    @endif
                    @if($nextOpen)
                    <span>Next opening {{ \Carbon\Carbon::parse($nextOpen['date'])->timezone('Asia/Kolkata')->format('D, j M') }}</span>
                    @endif
                </div>
                @endif
            </div>

            <div class="mp-hero__actions">
                <button type="button" class="btn btn-primary btn-lg" onclick="scrollToBook()">Book a session</button>
                <div class="mp-hero__links">
                    @if($linkedin)
                    <a class="btn btn-outline btn-sm" href="{{ $linkedin }}" target="_blank" rel="noopener">LinkedIn</a>
                    @endif
                    <button type="button" class="btn btn-ghost btn-sm" id="mp-share-btn">Share</button>
                    @auth
                        @if(auth()->user()->role === 'mentee' && ! $isExpert)
                            @php
                                $isAssigned = (int) auth()->user()->assigned_mentor_id === (int) $mentor->id;
                                $hasPending = \App\Models\MentorRequest::where('mentee_id', auth()->id())
                                    ->where('mentor_id', $mentor->id)
                                    ->where('status', 'pending')
                                    ->exists();
                            @endphp
                            @if($isAssigned)
                                <span class="mp-pill mp-pill--ok">Your mentor</span>
                            @elseif($hasPending)
                                <span class="mp-pill">Request pending</span>
                            @else
                                <form method="POST" action="{{ route('mentee.mentor-requests.store') }}">
                                    @csrf
                                    <input type="hidden" name="mentor_id" value="{{ $mentor->id }}">
                                    <button type="submit" class="btn btn-outline btn-sm">
                                        {{ auth()->user()->assigned_mentor_id ? 'Request as my mentor' : 'Choose as my mentor' }}
                                    </button>
                                </form>
                            @endif
                        @endif
                    @endauth
                </div>
            </div>
        </header>

        <div class="mp-stats" role="list">
            <div class="mp-stat" role="listitem">
                <span class="mp-stat__value">{{ number_format($score, 1) }}</span>
                <span class="mp-stat__label">Rating{{ $reviewCount ? ' · '.$reviewCount.' '. \Illuminate\Support\Str::plural('review', $reviewCount) : '' }}</span>
            </div>
            <div class="mp-stat" role="listitem">
                <span class="mp-stat__value">{{ number_format((int) ($mentor->total_sessions ?? 0)) }}</span>
                <span class="mp-stat__label">Sessions completed</span>
            </div>
            <div class="mp-stat" role="listitem">
                <span class="mp-stat__value">{{ (int) ($mentor->experience_years ?? 0) }}+</span>
                <span class="mp-stat__label">Years experience</span>
            </div>
            <div class="mp-stat" role="listitem">
                <span class="mp-stat__value">₹{{ rtrim(rtrim(number_format($rate, 2), '0'), '.') }}</span>
                <span class="mp-stat__label">Per minute</span>
            </div>
            <div class="mp-stat" role="listitem">
                <span class="mp-stat__value">₹{{ number_format($hourly) }}</span>
                <span class="mp-stat__label">Per hour</span>
            </div>
        </div>

        <div class="mp-layout">
            <div class="mp-main">
                <div class="mp-tabs" role="tablist">
                    <a class="mp-tab is-active" href="#about">About</a>
                    <a class="mp-tab" href="#profile-details">Profile</a>
                    <a class="mp-tab" href="#availability-overview">Availability</a>
                    <a class="mp-tab" href="#reviews">Reviews</a>
                </div>

                <section id="about" class="mp-section">
                    <div class="card mp-card">
                        <h2>About</h2>
                        @if($bioPlain !== '')
                        <div class="rich-text mp-bio">{!! $mentor->bio !!}</div>
                        @else
                        <p class="mp-empty">{{ $mentor->name }} hasn’t added a bio yet.</p>
                        @endif
                    </div>

                    @if($expertise !== [])
                    <div class="card mp-card">
                        <h2>Expertise</h2>
                        <div class="chip-wrap">
                            @foreach($expertise as $skill)
                            <span class="chip selected">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($strengths !== [])
                    <div class="card mp-card">
                        <h2>Strengths</h2>
                        <div class="chip-wrap">
                            @foreach($strengths as $item)
                            <span class="chip">{{ $item }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="card mp-card">
                        <h2>What you’ll get</h2>
                        <ul class="mp-perks">
                            <li>
                                <span class="mp-perks__icon">1:1</span>
                                <div>
                                    <strong>Focused {{ strtolower($hostLabel) }} session</strong>
                                    <p>Live video call on the slot you pick — 30, 60, 90, or 120 minutes.</p>
                                </div>
                            </li>
                            <li>
                                <span class="mp-perks__icon">₹</span>
                                <div>
                                    <strong>Clear pricing</strong>
                                    <p>₹{{ rtrim(rtrim(number_format($rate, 2), '0'), '.') }}/min · ₹{{ number_format($hourly) }}/hour. You only pay for the duration you book.</p>
                                </div>
                            </li>
                            <li>
                                <span class="mp-perks__icon">✎</span>
                                <div>
                                    <strong>Session notes</strong>
                                    <p>Shared notes and next steps after the call so nothing is lost.</p>
                                </div>
                            </li>
                            <li>
                                <span class="mp-perks__icon">→</span>
                                <div>
                                    <strong>Practical takeaways</strong>
                                    <p>Action items and a short roadmap you can use in the next 30–90 days.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </section>

                <section id="profile-details" class="mp-section">
                    <div class="card mp-card">
                        <h2>Professional profile</h2>
                        <dl class="mp-facts">
                            <div>
                                <dt>Title</dt>
                                <dd>{{ $mentor->designation ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt>Company</dt>
                                <dd>{{ $mentor->company ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt>Domain</dt>
                                <dd>{{ $mentor->field ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt>Experience</dt>
                                <dd>{{ (int) ($mentor->experience_years ?? 0) }} years</dd>
                            </div>
                            @if($mentor->education_stream)
                            <div>
                                <dt>Education stream</dt>
                                <dd>{{ $mentor->education_stream }}</dd>
                            </div>
                            @endif
                            @if($mentor->location)
                            <div>
                                <dt>Location</dt>
                                <dd>{{ $mentor->location }}</dd>
                            </div>
                            @endif
                            <div>
                                <dt>Session format</dt>
                                <dd>
                                    @if($modes !== [])
                                        {{ collect($modes)->map(fn ($m) => $modeLabels[$m] ?? ucfirst($m))->join(', ') }}
                                    @else
                                        1:1 video
                                    @endif
                                </dd>
                            </div>
                            @if($preferredLength)
                            <div>
                                <dt>Preferred length</dt>
                                <dd>{{ $preferredLength }} minutes</dd>
                            </div>
                            @endif
                            @if($preferredTime !== '')
                            <div>
                                <dt>Preferred time</dt>
                                <dd>{{ $preferredTime }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </section>

                <section id="availability-overview" class="mp-section">
                    <div class="card mp-card">
                        <h2>Weekly availability</h2>
                        <p class="mp-card__hint">Times are in IST. Green days are when {{ explode(' ', $mentor->name)[0] }} usually takes sessions.</p>
                        @if($weekly !== [])
                        <div class="mp-week">
                            @foreach($weekly as $day)
                            <div class="mp-week__day {{ !empty($day['enabled']) ? 'is-on' : 'is-off' }}">
                                <strong>{{ $day['label'] }}</strong>
                                <span>
                                    @if(!empty($day['enabled']))
                                        {{ $day['windows'] ?: (($day['from'] && $day['to']) ? $day['from'].'–'.$day['to'] : 'Open') }}
                                    @else
                                        Off
                                    @endif
                                </span>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="mp-empty">Weekly hours will appear here once this {{ strtolower($hostLabel) }} publishes a schedule.</p>
                        @endif
                    </div>
                </section>

                <section id="reviews" class="mp-section">
                    <div class="card mp-card mp-reviews-head">
                        <div class="mp-score">
                            <div class="mp-score__num">{{ number_format($score, 1) }}</div>
                            <div class="stars" aria-hidden="true">{{ str_repeat('★', $filledStars) }}{{ str_repeat('☆', 5 - $filledStars) }}</div>
                            <p>{{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</p>
                            @if($reviewCount > 0)
                            <p class="mp-score__rec">{{ $recommendCount }} of {{ $reviewCount }} would recommend</p>
                            @endif
                        </div>
                        @if($dimAvgs !== [])
                        <div class="mp-bars">
                            @foreach($dimAvgs as $label => $value)
                            <div class="mp-bar">
                                <div class="mp-bar__row">
                                    <span>{{ $label }}</span>
                                    <strong>{{ number_format($value, 1) }}</strong>
                                </div>
                                <div class="mp-bar__track"><i style="width: {{ min(100, ($value / 5) * 100) }}%"></i></div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    @forelse($reviews as $review)
                    @php
                        $reviewerName = $review->reviewer->name ?? 'Mentee';
                        $overall = max(0, min(5, (int) $review->overall_rating));
                        $detailScores = array_filter([
                            'Communication' => $review->communication_rating,
                            'Knowledge' => $review->knowledge_rating,
                            'Punctuality' => $review->punctuality_rating,
                            'Helpfulness' => $review->helpfulness_rating,
                        ], fn ($value) => $value !== null && $value !== '');
                    @endphp
                    <article class="testimonial-card mp-review">
                        <div class="mp-review__top">
                            <div class="stars">{{ str_repeat('★', $overall) }}{{ str_repeat('☆', 5 - $overall) }}</div>
                            <time>{{ $review->submitted_at?->diffForHumans() ?? $review->created_at?->diffForHumans() }}</time>
                        </div>
                        @if(filled($review->review_text))
                        <p class="testimonial-text">“{{ $review->review_text }}”</p>
                        @endif
                        @if($detailScores !== [])
                        <div class="mp-review__chips">
                            @foreach($detailScores as $label => $value)
                            <span>{{ $label }} {{ (int) $value }}/5</span>
                            @endforeach
                        </div>
                        @endif
                        <div class="testimonial-author">
                            <div class="author-avatar">{{ strtoupper(substr($reviewerName, 0, 1)) }}</div>
                            <div>
                                <div class="author-name">{{ $reviewerName }}</div>
                                <div class="author-role">{{ $review->would_recommend ? 'Would recommend' : 'Verified mentee' }}</div>
                            </div>
                        </div>
                    </article>
                    @empty
                    <div class="card mp-card mp-empty-card">
                        <p>No reviews yet. Feedback from mentees will show up here after completed sessions.</p>
                    </div>
                    @endforelse
                </section>
            </div>

            <aside id="book-section" class="mp-aside">
                <div class="card mp-book">
                    <div class="mp-book__head">
                        <div>
                            <p class="mp-book__kicker">Book a session</p>
                            <p class="mp-book__price">₹{{ rtrim(rtrim(number_format($rate, 2), '0'), '.') }}<small>/min</small></p>
                        </div>
                        <span class="mp-book__hour">₹{{ number_format($hourly) }}/hr</span>
                    </div>
                    <p class="mp-book__note">Pick a date, duration, and time. Payment is only taken when you confirm.</p>

                    <div class="booking-card mp-book__block" style="padding:0;border:0;background:transparent;box-shadow:none;">
                        <p class="label-caps" style="margin-bottom:10px;">{{ $hostLabel }} availability</p>
                        <div id="availabilitySummary"></div>
                    </div>

                    <div class="booking-cal-head">
                        <p class="label-caps" style="margin:0;">Select a date</p>
                        <div class="booking-cal-nav">
                            <button type="button" class="btn btn-ghost btn-sm" id="cal-prev-month" aria-label="Previous month">‹</button>
                            <strong id="cal-month-label"></strong>
                            <button type="button" class="btn btn-ghost btn-sm" id="cal-next-month" aria-label="Next month">›</button>
                        </div>
                    </div>
                    <div id="dateGrid" class="calendar-grid calendar-grid--month"></div>

                    <p class="label-caps mp-book__label">Session duration</p>
                    <div class="duration-btns">
                        @foreach(\App\Models\ConsultationSession::BOOKING_DURATIONS as $mins)
                        <button type="button"
                                class="duration-btn{{ $mins === 30 ? ' selected' : '' }}"
                                data-min="{{ $mins }}"
                                onclick="BookingWidget.setDuration({{ $mins }})">
                            <span class="duration-btn__time">{{ $mins }}m</span>
                            <span class="duration-btn__price">₹{{ number_format($rate * $mins) }}</span>
                        </button>
                        @endforeach
                    </div>

                    <p class="label-caps mp-book__label" id="slots-heading">Available slots</p>
                    <div id="timeGrid" class="time-grid">
                        <div style="grid-column:1/-1;text-align:center;padding:12px;font-size:12px;color:var(--text-3);">Select an available date</div>
                    </div>

                    <p class="label-caps mp-book__label">Session details</p>
                    <div class="form-group">
                        <label class="form-label" for="booking-topic">Topic <span style="color:var(--error)">*</span></label>
                        <input type="text" class="form-input" id="booking-topic" placeholder="What would you like to discuss?" maxlength="255">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="booking-agenda">Notes (optional)</label>
                        <textarea class="form-input" rows="2" id="booking-agenda" placeholder="Any additional context…"></textarea>
                    </div>

                    @include('frontend.partials.booking-coupon-picker')

                    <div class="booking-summary" style="margin-top:16px;">
                        <div class="booking-summary-row"><span>Date</span><span id="bk-date">—</span></div>
                        <div class="booking-summary-row"><span>Time</span><span id="bk-time">—</span></div>
                        <div class="booking-summary-row"><span>Duration</span><span id="bk-duration">30 min</span></div>
                        <div class="booking-summary-row"><span>Subtotal</span><strong id="bk-subtotal">₹{{ number_format($rate * 30) }}</strong></div>
                        <div class="booking-summary-row booking-summary-row--discount" id="bk-discount-row" hidden>
                            <span>Coupon</span><strong id="bk-discount">—</strong>
                        </div>
                        <div class="booking-summary-row booking-summary-row--total" style="padding-top:12px;">
                            <span>Total</span><strong id="bk-total">₹{{ number_format($rate * 30) }}</strong>
                        </div>
                    </div>

                    <input type="hidden" id="booking-mentor-id" value="{{ $mentor->id }}">
                    <input type="hidden" name="booking_date">
                    <input type="hidden" name="booking_time">
                    <input type="hidden" name="booking_duration">
                    <input type="hidden" name="booking_amount">

                    <button type="button" class="btn btn-primary btn-full btn-lg mp-book__cta" onclick="confirmBooking()">
                        Confirm booking
                    </button>
                    <p class="mp-book__fine">You’ll choose Wallet or Razorpay on the next step if the session is paid.</p>
                </div>
            </aside>
        </div>
    </div>

    <div class="mp-dock" aria-hidden="false">
        <div class="mp-dock__price">
            <strong>₹{{ rtrim(rtrim(number_format($rate, 2), '0'), '.') }}/min</strong>
            <span>{{ $mentor->name }} · {{ $hostLabel }}</span>
        </div>
        <button type="button" class="btn btn-primary" onclick="scrollToBook()">Book</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
BookingWidget.init({{ $rate }}, {{ (int) $mentor->id }});

function scrollToBook() {
    document.getElementById('book-section')?.scrollIntoView({ behavior:'smooth', block:'start' });
}

(function () {
    const btn = document.getElementById('mp-share-btn');
    if (!btn) return;

    function toast(type, message) {
        if (typeof window.showToast === 'function') window.showToast(type, message);
    }

    function copied() {
        const original = btn.textContent;
        btn.textContent = 'Copied';
        toast('success', 'Profile link copied');
        setTimeout(() => { btn.textContent = original; }, 2000);
    }

    function fallbackCopy(text) {
        const input = document.createElement('textarea');
        input.value = text;
        input.setAttribute('readonly', '');
        input.style.position = 'fixed';
        input.style.top = '0';
        input.style.left = '0';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.focus();
        input.select();
        input.setSelectionRange(0, text.length);
        let ok = false;
        try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
        document.body.removeChild(input);
        return ok;
    }

    btn.addEventListener('click', async function (e) {
        e.preventDefault();
        const url = window.location.href;
        const payload = { title: document.title, text: document.title, url: url };

        if (navigator.share) {
            try {
                await navigator.share(payload);
                return;
            } catch (err) {
                if (err && err.name === 'AbortError') return;
            }
        }

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(url);
                copied();
                return;
            }
        } catch (e) {}

        if (fallbackCopy(url)) {
            copied();
            return;
        }

        toast('error', 'Could not copy the link. Copy it from the address bar.');
    });
})();

(function () {
    const tabs = document.querySelectorAll('.mp-tab');
    const sections = ['about', 'profile-details', 'availability-overview', 'reviews']
        .map((id) => document.getElementById(id))
        .filter(Boolean);

    tabs.forEach((tab) => {
        tab.addEventListener('click', (e) => {
            const target = document.querySelector(tab.getAttribute('href'));
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    if (!('IntersectionObserver' in window) || !sections.length) return;
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            tabs.forEach((tab) => tab.classList.toggle('is-active', tab.getAttribute('href') === '#' + entry.target.id));
        });
    }, { rootMargin: '-20% 0px -65% 0px', threshold: 0.1 });
    sections.forEach((el) => io.observe(el));
})();

function confirmBooking(paymentMethod) {
    const data = BookingWidget.getBookingData();
    if (!data) return;
    data.mentor_id = document.getElementById('booking-mentor-id').value;
    data.title = document.getElementById('booking-topic')?.value?.trim() || '';
    data.agenda = document.getElementById('booking-agenda')?.value || '';
    const couponCode = (typeof BookingCoupon !== 'undefined' && BookingCoupon.getCode)
        ? BookingCoupon.getCode()
        : (document.getElementById('booking-coupon')?.value || '').trim();
    if (couponCode) {
        data.coupon_code = couponCode;
    }
    if (!data.title) {
        showToast('error', 'Please enter a topic for the session.');
        return;
    }
    if (paymentMethod) data.payment_method = paymentMethod;

    @guest
    showToast('info','Please sign in to book a session.');
    setTimeout(() => window.location.href = '/login?redirect={{ request()->path() }}', 1500);
    return;
    @endguest

    AjaxPost("{{ route('mentee.sessions.book') }}", data, {
        loader: true,
        onSuccess: res => {
            if (res.requires_payment_choice) {
                openPaymentChoice(res);
                return;
            }
            if (res.requires_payment) {
                closePaymentChoice();
                openSessionPayment(res);
                return;
            }
            closePaymentChoice();
            showToast('success', res.message || '🎉 Session booked!');
            setTimeout(() => window.location.href = res.redirect || '{{ route('mentee.sessions') }}', 1500);
        },
        onError: err => {
            if (err.status === 401) window.location.href = '/login?redirect={{ request()->path() }}';
            else if (err.insufficient_wallet || err.needs_topup) openPaymentChoice(err, true);
            else if (err.topup_url) {
                showToast('error', err.message || 'Insufficient wallet balance.');
                setTimeout(() => window.location.href = err.topup_url, 1800);
            } else {
                showToast('error', err.message || 'Could not book. Please try again.');
            }
        }
    });
}

function openPaymentChoice(info, fromError) {
    let box = document.getElementById('payment-choice-modal');
    if (!box) {
        box = document.createElement('div');
        box.id = 'payment-choice-modal';
        box.className = 'modal-overlay open';
        box.innerHTML = `
          <div class="modal" style="max-width:420px;">
            <div class="modal-header"><h3>Choose payment method</h3><button type="button" class="modal-close" onclick="closePaymentChoice()">×</button></div>
            <div class="modal-body" id="payment-choice-body"></div>
          </div>`;
        document.body.appendChild(box);
    }
    box.classList.add('open');
    const amount = info.amount ?? info.required_amount ?? 0;
    const base = info.base_amount ?? amount;
    const discount = info.coupon_discount ?? 0;
    const bal = info.wallet_balance ?? 0;
    const shortfall = info.shortfall ?? Math.max(0, amount - bal);
    const opts = info.payment_options || ['wallet','razorpay'];
    document.getElementById('payment-choice-body').innerHTML = `
      <p style="font-size:13px;color:var(--text-2);margin-bottom:12px;">
        ${discount > 0 ? `Original fee: <strong>₹${Number(base).toLocaleString()}</strong><br>Coupon discount: <strong>-₹${Number(discount).toLocaleString()}</strong><br>` : ''}
        Session fee: <strong>₹${Number(amount).toLocaleString()}</strong><br>
        Wallet balance: <strong>₹${Number(bal).toLocaleString()}</strong>
        ${shortfall > 0 ? `<br>Shortfall: <strong>₹${Number(shortfall).toLocaleString()}</strong>` : ''}
      </p>
      <div style="display:grid;gap:8px;">
        ${opts.includes('wallet') ? `<button type="button" class="btn btn-primary" onclick="confirmBooking('wallet')">Pay with Wallet</button>` : ''}
        ${opts.includes('razorpay') ? `<button type="button" class="btn btn-ghost" onclick="confirmBooking('razorpay')">Pay with Razorpay</button>` : ''}
        ${opts.includes('hybrid') ? `<button type="button" class="btn btn-ghost" onclick="confirmBooking('hybrid')">Use Wallet + Razorpay (₹${Number(shortfall).toLocaleString()} online)</button>` : ''}
        ${(opts.includes('topup') || (fromError && shortfall > 0)) ? `<a class="btn btn-ghost" href="{{ route('mentee.wallet') }}">Top up wallet</a>` : ''}
      </div>`;
}

function closePaymentChoice() {
    const box = document.getElementById('payment-choice-modal');
    if (box) box.classList.remove('open');
}

function openSessionPayment(order) {
    if (typeof Razorpay === 'undefined') {
        const s = document.createElement('script');
        s.src = 'https://checkout.razorpay.com/v1/checkout.js';
        s.onload = () => launchSessionRzp(order);
        s.onerror = () => showToast('error', 'Could not load payment gateway.');
        document.head.appendChild(s);
        return;
    }
    launchSessionRzp(order);
}

function launchSessionRzp(order) {
    const draft = order.booking_draft || {};
    if (typeof armUnpaidSessionHold === 'function') {
        armUnpaidSessionHold({
            mentor_id: draft.mentor_id || document.getElementById('booking-mentor-id')?.value,
            date: draft.date,
            time: draft.time,
        });
    }
    const options = {
        key: order.key,
        amount: order.amount,
        currency: order.currency || 'INR',
        name: order.name || 'Vedrix',
        description: order.description || 'Session booking',
        order_id: order.order_id,
        prefill: order.prefill || {},
        theme: { color: '#f59e0b' },
        modal: {
            ondismiss: function () {
                if (typeof releaseUnpaidSessionHold === 'function') releaseUnpaidSessionHold(false);
                showToast('info', 'Payment was not completed. That time slot is available again.');
            },
        },
        handler: function (response) {
            if (typeof markSessionPaymentSettled === 'function') markSessionPaymentSettled();
            AjaxPost('{{ route('mentee.sessions.verify') }}', {
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature,
            }, {
                loader: true,
                onSuccess: (data) => {
                    showToast('success', data.message || 'Payment successful!');
                    setTimeout(() => window.location.href = data.redirect || '{{ route('mentee.sessions') }}', 1200);
                },
                onError: (err) => showToast('error', err.message || 'Payment verification failed.'),
            });
        },
    };
    const rzp = new Razorpay(options);
    rzp.on('payment.failed', function () {
        if (typeof releaseUnpaidSessionHold === 'function') releaseUnpaidSessionHold(false);
        showToast('error', 'Payment failed. That time slot is available again.');
    });
    rzp.open();
}
</script>
@endpush
