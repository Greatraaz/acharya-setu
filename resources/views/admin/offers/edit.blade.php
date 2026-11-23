@extends('admin.layouts.app')
@section('title', 'Edit Offer')
@section('heading', 'Edit Offer')

@section('content')
<div class="admin-page space-y-4 max-w-4xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <span class="learn-eyebrow">Billing · Offers</span>
                <h2>Edit Offer</h2>
                <p>Update wallet credits or session coupon settings for this offer.</p>
            </div>
            <a href="{{ route('admin.offers.index') }}" class="learn-btn-ghost self-start">← Back to Offers</a>
        </div>
    </div>
    @include('admin.offers._form', ['offer' => $offer, 'mentees' => $mentees])
</div>
@endsection