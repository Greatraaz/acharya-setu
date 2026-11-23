@extends('admin.layouts.app')
@section('title', 'Create Offer')
@section('heading', 'Create Offer')

@section('content')
<div class="admin-page space-y-4 max-w-4xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Billing · Offers</span>
                <h2>New Offer</h2>
                <p>Credit new joinees on onboarding or assign session coupons to mentees.</p>
            </div>
            <a href="{{ route('admin.offers.index') }}" class="learn-btn-ghost self-start">← Back to Offers</a>
        </div>
    </div>
    @include('admin.offers._form', ['offer' => $offer, 'mentees' => $mentees])
</div>
@endsection