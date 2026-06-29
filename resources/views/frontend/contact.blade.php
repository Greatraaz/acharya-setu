@extends('frontend.layouts.app')
@section('title', 'Contact Us — AcharyaSetu')
@section('meta_description', 'Get in touch with the AcharyaSetu team. We respond within 24 hours.')

@section('content')
<div style="padding-top:var(--nav-h);">

    {{-- ── HERO ──────────────────────────────────────────── --}}
    <section style="padding:64px 0 48px; background:var(--bg-2); border-bottom:1px solid var(--border);">
        <div class="container text-center">
            <div class="hero-eyebrow" style="justify-content:center; margin-bottom:16px;">✉️ &nbsp;Get in Touch</div>
            <h1 style="font-size:clamp(26px,4vw,44px); font-weight:800; margin-bottom:14px;">
                We'd love to <span class="text-brand">hear from you</span>
            </h1>
            <p style="font-size:15px; color:var(--text-2); max-width:500px; margin:0 auto; line-height:1.75;">
                Have a question, need support, or want to partner with us? Our team responds within 24 hours.
            </p>
        </div>
    </section>

    {{-- ── MAIN ──────────────────────────────────────────── --}}
    <section style="padding:64px 0 80px;">
        <div class="container">
            <div style="display:grid; grid-template-columns:1fr 1.4fr; gap:48px; align-items:start;">

                {{-- LEFT: Info --}}
                <div>
                    <h2 style="font-size:18px; font-weight:800; margin-bottom:24px;">Contact Information</h2>

                    @foreach([
                        ['📧','Email',        'hello@acharyasetu.com',   'For general enquiries & partnerships'],
                        ['📱','WhatsApp',     '+91 98765 43210',         'Mon–Sat, 9 AM – 8 PM IST'],
                        ['🏢','Our Office',   'Koramangala, Bangalore',  'Karnataka, India – 560034'],
                        ['🕐','Working Hours','Mon – Sat, 9 AM – 8 PM', 'IST (UTC +5:30)'],
                    ] as [$icon,$title,$value,$sub])
                    <div style="display:flex; gap:16px; align-items:flex-start; padding:16px 0; border-bottom:1px solid var(--border);">
                        <div style="width:44px; height:44px; border-radius:var(--radius); background:var(--brand-muted); border:1px solid rgba(245,158,11,.2); display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0;">{{ $icon }}</div>
                        <div>
                            <div style="font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--text-3); margin-bottom:2px;">{{ $title }}</div>
                            <div style="font-size:14px; font-weight:600; margin-bottom:1px;">{{ $value }}</div>
                            <div style="font-size:12px; color:var(--text-2);">{{ $sub }}</div>
                        </div>
                    </div>
                    @endforeach

                    {{-- Social --}}
                    <div style="margin-top:28px; margin-bottom:32px;">
                        <div style="font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--text-3); margin-bottom:14px;">Follow Us</div>
                        <div style="display:flex; gap:10px;">
                            @foreach([['𝕏','Twitter'],['in','LinkedIn'],['📷','Instagram'],['▶️','YouTube']] as [$i,$l])
                            <a href="#" title="{{ $l }}" class="social-btn">{{ $i }}</a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Quick help --}}
                    <div style="background:var(--bg-3); border:1px solid var(--border); border-radius:var(--radius-lg); padding:20px;">
                        <div style="font-size:13px; font-weight:700; margin-bottom:12px;">Quick Help</div>
                        @foreach([
                            ['How do I book a session?',  route('mentors.search')],
                            ['How does billing work?',    route('terms')],
                            ['Become a mentor',           route('register').'?role=mentor'],
                            ['Privacy & data policy',     route('privacy')],
                        ] as [$q,$link])
                        <a href="{{ $link }}" style="display:flex; align-items:center; justify-content:space-between; padding:9px 0; border-bottom:1px solid var(--border); font-size:13px; color:var(--text-2); text-decoration:none; transition:color .2s;"
                           onmouseover="this.style.color='var(--brand)'" onmouseout="this.style.color='var(--text-2)'">
                            {{ $q }} <span style="color:var(--brand);">→</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- RIGHT: Form --}}
                <div class="card" style="padding:32px;">
                    <h2 style="font-size:19px; font-weight:800; margin-bottom:6px;">Send a Message</h2>
                    <p style="font-size:13px; color:var(--text-2); margin-bottom:28px;">We'll get back to you within 24 hours.</p>

                    @if(session('success'))
                    <div class="alert alert-success" style="margin-bottom:20px;">
                        <span class="alert-icon">✅</span>
                        <div>{{ session('success') }}</div>
                    </div>
                    @endif
                    @if($errors->any())
                    <div class="alert alert-error" style="margin-bottom:20px;">
                        <span class="alert-icon">❌</span>
                        <div>{{ $errors->first() }}</div>
                    </div>
                    @endif

                    <form
                        action="{{ route('contact.send') }}"
                        method="POST"
                        data-ajax-form="{{ route('contact.send') }}"
                        data-success="Message sent! We'll reply within 24 hours."
                        data-reset-on-success
                    >
                        @csrf

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-input @error('name') error @enderror"
                                    placeholder="Rahul Sharma" value="{{ old('name') }}" required>
                                @error('name')<div class="form-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-input @error('email') error @enderror"
                                    placeholder="rahul@example.com" value="{{ old('email', auth()->user()?->email) }}" required>
                                @error('email')<div class="form-error">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone <span style="font-weight:400; color:var(--text-3);">(Optional)</span></label>
                            <div class="input-prefix">
                                <span class="input-prefix-label">🇮🇳 +91</span>
                                <input type="tel" name="phone" class="form-input" placeholder="98765 43210" maxlength="10" value="{{ old('phone') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Subject *</label>
                            <select name="subject" class="form-select @error('subject') error @enderror" required>
                                <option value="">— Select a topic —</option>
                                <optgroup label="General">
                                    <option @selected(old('subject')==='General Inquiry')>General Inquiry</option>
                                    <option @selected(old('subject')==='Partnership / Media')>Partnership / Media</option>
                                    <option @selected(old('subject')==='Feedback')>Feedback</option>
                                </optgroup>
                                <optgroup label="Mentor">
                                    <option @selected(old('subject')==='Mentor Application')>Mentor Application</option>
                                    <option @selected(old('subject')==='Mentor Profile Issue')>Mentor Profile Issue</option>
                                </optgroup>
                                <optgroup label="Support">
                                    <option @selected(old('subject')==='Technical Support')>Technical Support</option>
                                    <option @selected(old('subject')==='Billing / Refund')>Billing / Refund</option>
                                    <option @selected(old('subject')==='Session Issue')>Session Issue</option>
                                    <option @selected(old('subject')==='Report an Issue')>Report an Issue</option>
                                </optgroup>
                            </select>
                            @error('subject')<div class="form-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Message *</label>
                            <textarea name="message" class="form-textarea @error('message') error @enderror"
                                rows="5" placeholder="Tell us how we can help…" required maxlength="3000">{{ old('message') }}</textarea>
                            <div style="display:flex; justify-content:space-between; margin-top:4px;">
                                @error('message')<div class="form-error">{{ $message }}</div>@enderror
                                <div style="margin-left:auto; font-size:11px; color:var(--text-3);"><span id="char-count">0</span>/3000</div>
                            </div>
                        </div>

                        {{-- Honeypot anti-spam --}}
                        <div style="display:none;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

                        <button type="submit" class="btn btn-primary btn-full btn-lg" id="contact-submit-btn">
                            Send Message →
                        </button>

                        <p style="font-size:11px; color:var(--text-3); text-align:center; margin-top:12px; line-height:1.6;">
                            By submitting, you agree to our
                            <a href="{{ route('privacy') }}" style="color:var(--brand);">Privacy Policy</a>.
                        </p>
                    </form>
                </div>

            </div>
        </div>
    </section>

    {{-- ── TESTIMONIALS / TRUST ──────────────────────────── --}}
    <section style="padding:0 0 80px;">
        <div class="container">
            <div style="background:linear-gradient(135deg, rgba(245,158,11,.06) 0%, transparent 100%); border:1px solid rgba(245,158,11,.15); border-radius:var(--radius-xl); padding:40px; text-align:center;">
                <h3 style="font-size:20px; font-weight:800; margin-bottom:8px;">Trusted by 45,000+ learners across India</h3>
                <p style="font-size:14px; color:var(--text-2); margin-bottom:24px;">From Mumbai to Chennai, our team supports every single one.</p>
                <div style="display:flex; justify-content:center; gap:48px; flex-wrap:wrap;">
                    @foreach([['⚡','Fast','Avg. reply in 4 hrs'],['🔒','Private','Data never shared'],['🤝','Friendly','Real humans, always']] as [$i,$t,$d])
                    <div style="text-align:center;">
                        <div style="font-size:32px; margin-bottom:6px;">{{ $i }}</div>
                        <div style="font-size:14px; font-weight:700; margin-bottom:2px;">{{ $t }}</div>
                        <div style="font-size:12px; color:var(--text-2);">{{ $d }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
const msgArea = document.querySelector('textarea[name="message"]');
const counter = document.getElementById('char-count');
if (msgArea && counter) {
    counter.textContent = msgArea.value.length;
    msgArea.addEventListener('input', () => {
        counter.textContent = msgArea.value.length;
        counter.style.color = msgArea.value.length > 2800 ? 'var(--error)' : 'var(--text-3)';
    });
}
</script>
@endpush