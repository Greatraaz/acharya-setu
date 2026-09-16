@extends('admin.layouts.app')
@section('title', 'Create Offer')
@section('heading', 'Create Offer')

@section('content')
<div class="space-y-4 max-w-4xl">
    <div>
        <a href="{{ route('admin.offers.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Offers</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">New Offer</h1>
        <p class="text-sm text-gray-500 mt-1">Credit new joinees on onboarding or assign session coupons to mentees.</p>
    </div>
    @include('admin.offers._form', ['offer' => $offer, 'mentees' => $mentees])
</div>
@endsection
