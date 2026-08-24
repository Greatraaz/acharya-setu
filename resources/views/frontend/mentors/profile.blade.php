@extends('frontend.layouts.app')
@section('title', ($mentor->name ?? 'Mentor') . ' — Vedrix')

@section('content')
@auth
    @if(auth()->user()->role === 'mentee')
        @include('frontend.mentee.partials.bottom-nav')
    @endif
@endauth
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
                <div style="padding-bottom:20px;display:flex;flex-direction:column;gap:8px;align-items:flex-end;">
                    <button class="btn btn-primary btn-lg" onclick="scrollToBook()">
                        📅 Book a Session
                    </button>
                    @auth
                        @if(auth()->user()->role === 'mentee')
                            @php
                                $isAssigned = (int) auth()->user()->assigned_mentor_id === (int) $mentor->id;
                                $hasPending = \App\Models\MentorRequest::where('mentee_id', auth()->id())
                                    ->where('mentor_id', $mentor->id)
                                    ->where('status', 'pending')
                                    ->exists();
                            @endphp
                            @if($isAssigned)
                                <span class="badge badge-success">Your mentor</span>
                            @elseif($hasPending)
                                <span class="badge badge-muted">Request pending</span>
                            @else
                                <form method="POST" action="{{ route('mentee.mentor-requests.store') }}">
                                    @csrf
                                    <input type="hidden" name="mentor_id" value="{{ $mentor->id }}">
                                    <button type="submit" class="btn btn-outline">
                                        {{ auth()->user()->assigned_mentor_id ? 'Request as my mentor' : 'Choose as my mentor' }}
                                    </button>
                                </form>
                            @endif
                        @endif
                    @endauth
                </div>
            </div>
            <div class="profile-hero-tabs">
                <div class="profile-tab active" onclick="showSection('about')">About</div>
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
                    <p style="font-size:12px;color:var(--text-2);margin-bottom:16px;">₹{{ $mentor->rate_per_minute ?? 10 }}/min · Free cancellation</p>

                    <div id="availabilitySummary"></div>

                    {{-- Date Grid --}}
                    <p class="label-caps" style="margin-bottom:10px;">Choose Date</p>
                    <div id="dateGrid" class="calendar-grid"></div>

                    {{-- Time Slots --}}
                    <p class="label-caps" style="margin:16px 0 10px;">Available Times</p>
                    <div id="timeGrid" class="time-grid">
                        <div style="grid-column:1/-1;text-align:center;padding:12px;font-size:12px;color:var(--text-3);">Select an available date</div>
                    </div>

                    {{-- Duration --}}
                    <p class="label-caps" style="margin:16px 0 10px;">Duration</p>
                    <div class="duration-btns">
                        <div class="duration-btn" data-min="15" onclick="BookingWidget.setDuration(15)">15 min<br><small style="font-size:10px;color:inherit;">₹{{ ($mentor->rate_per_minute ?? 10) * 15 }}</small></div>
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

function confirmBooking(paymentMethod) {
    const data = BookingWidget.getBookingData();
    if (!data) return;
    data.mentor_id = document.getElementById('booking-mentor-id').value;
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
    const bal = info.wallet_balance ?? 0;
    const shortfall = info.shortfall ?? Math.max(0, amount - bal);
    const opts = info.payment_options || ['wallet','razorpay'];
    document.getElementById('payment-choice-body').innerHTML = `
      <p style="font-size:13px;color:var(--text-2);margin-bottom:12px;">
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
    const options = {
        key: order.key,
        amount: order.amount,
        currency: order.currency || 'INR',
        name: order.name || 'Vedrix',
        description: order.description || 'Session booking',
        order_id: order.order_id,
        prefill: order.prefill || {},
        theme: { color: '#f59e0b' },
        handler: function (response) {
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
        showToast('error', 'Payment failed. Your slot hold will expire if unpaid.');
    });
    rzp.open();
}
</script>
@endpush