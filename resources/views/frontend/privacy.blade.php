{{-- resources/views/pages/privacy.blade.php --}}
@extends('frontend.layouts.app')
@section('title', 'Privacy Policy — AcharyaSetu')

@section('content')
<div style="padding-top:var(--nav-h);">
<section style="padding:80px 0;">
<div class="container-sm">
    <h1 style="font-size:36px;font-weight:800;margin-bottom:6px;">Privacy Policy</h1>
    <p style="font-size:13px;color:var(--text-3);margin-bottom:48px;padding-bottom:24px;border-bottom:1px solid var(--border);">Last updated: January 2025</p>

    @php
    $sections = [
        ['Information We Collect','We collect information you provide during registration (name, email, phone, profile photo), session booking (date, time, mentor preferences), payments (processed securely via Razorpay — we never store card numbers), and usage analytics (pages visited, sessions viewed).'],
        ['How We Use Your Information','We use your information to provide and improve our platform, match you with relevant mentors, process payments, send session reminders and notifications, and prevent fraud. We do not sell your personal data to advertisers or third parties.'],
        ['OTP Verification','For account security, we use 6-digit OTPs sent to your registered email and mobile number. OTPs expire in 10 minutes and are single-use only. We do not store OTP values after verification. Phone OTPs are sent via trusted SMS providers (MSG91, AWS SNS).'],
        ['Data Security','All data is encrypted in transit using TLS 1.3 and at rest using AES-256 encryption. Payment data is handled by Razorpay (PCI-DSS Level 1 certified). Video calls are end-to-end encrypted via Agora, Zoom, or Google Meet.'],
        ['Data Sharing','We share data only with service providers necessary to operate the platform (payment processors, SMS gateways, video call providers). We may disclose data when required by law. We never sell data to third parties for advertising purposes.'],
        ['Cookies','We use essential cookies for session management, theme preferences, and security. Analytics cookies help us improve the platform. You can disable non-essential cookies in your browser settings.'],
        ['Your Rights','You may request access to, correction of, or deletion of your personal data at any time by emailing privacy@acharyasetu.com. We will respond within 30 days. You may also deactivate your account from Settings.'],
        ['Children\'s Privacy','AcharyaSetu is intended for users 16 years and older. We do not knowingly collect personal information from children under 16. If you believe a child has registered, contact us immediately.'],
        ['Changes to This Policy','We may update this policy from time to time. Significant changes will be communicated via email or an in-app notification. Continued use of the platform after changes constitutes acceptance.'],
        ['Contact Us','Questions about privacy? Email privacy@acharyasetu.com or write to: AcharyaSetu, Koramangala, Bangalore – 560034, Karnataka, India.'],
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