@extends('frontend.layouts.app')
@section('title', 'Terms & Conditions — AcharyaSetu')

@section('content')
<div style="padding-top:var(--nav-h);">
<section style="padding:80px 0;">
<div class="container-sm">
    <h1 style="font-size:36px;font-weight:800;margin-bottom:6px;">Terms & Conditions</h1>
    <p style="font-size:13px;color:var(--text-3);margin-bottom:48px;padding-bottom:24px;border-bottom:1px solid var(--border);">Effective: January 2025 | Governing Law: India</p>

    @php
    $sections = [
        ['Acceptance','By registering on or using AcharyaSetu, you agree to these Terms. If you disagree with any part, please do not use our platform.'],
        ['Eligibility','You must be at least 16 years old to use AcharyaSetu as a mentee, and at least 21 years old to register as a mentor. By using the platform, you represent that you meet these requirements.'],
        ['Mentor Obligations','Mentors must provide accurate professional information, attend all confirmed sessions on time, maintain professional and respectful conduct, not solicit mentees for off-platform arrangements, and not misrepresent qualifications or experience.'],
        ['Mentee Obligations','Mentees must treat mentors with professionalism and respect, cancel sessions at least 2 hours before the scheduled time for a full refund, not record sessions without the mentor\'s explicit consent, and use guidance for personal development only.'],
        ['Payments & Wallet','Sessions are billed at the mentor\'s stated per-minute rate, deducted from your AcharyaSetu wallet. Wallet top-ups are processed via Razorpay. Unused wallet balance may be refunded within 7 business days of a written request. Session payments are final once a session is completed.'],
        ['Cancellation & Refunds','Free cancellation up to 2 hours before the session. Late cancellations (within 2 hours) are non-refundable. No-shows by mentors result in a full refund. Platform disputes must be raised within 48 hours of the session.'],
        ['Intellectual Property','All content on AcharyaSetu — platform design, curriculum material, blog posts — is owned by AcharyaSetu or its licensors. Session recordings (if enabled) are private and may not be shared without written consent from both parties.'],
        ['Prohibited Activities','Users may not: impersonate others, distribute malware or spam, engage in fraudulent payment activity, harass other users, attempt to reverse-engineer the platform, or use the platform for any unlawful purpose.'],
        ['Limitation of Liability','AcharyaSetu is a marketplace connecting mentors and mentees. We are not responsible for the accuracy of mentor-provided advice or career outcomes. Mentors are independent contractors, not employees. Our liability is limited to the amount paid for the disputed session.'],
        ['Termination','We reserve the right to suspend or terminate accounts that violate these Terms, without prior notice. Users may deactivate their account at any time from Settings.'],
        ['Governing Law & Disputes','These Terms are governed by Indian law. Disputes shall first be resolved through good-faith negotiation. If unresolved, disputes will be subject to arbitration under the Arbitration and Conciliation Act, 1996, in Bangalore, Karnataka.'],
        ['Updates to Terms','We may update these Terms. Significant changes will be communicated via email or in-app notification 7 days before they take effect. Continued use constitutes acceptance.'],
    ];
    @endphp

    @foreach($sections as [$title, $text])
    <div style="margin-bottom:32px;">
        <h2 style="font-size:18px;font-weight:700;color:var(--brand);margin-bottom:10px;">{{ $loop->iteration }}. {{ $title }}</h2>
        <p style="font-size:15px;color:var(--text-2);line-height:1.85;">{{ $text }}</p>
    </div>
    @endforeach
</div>
</section>
</div>
@endsection