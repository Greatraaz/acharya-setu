@extends('admin.layouts.app')
@section('title', 'Edit Offer')
@section('heading', 'Edit Offer')

@section('content')
<div class="space-y-4 max-w-4xl">
    <div>
        <a href="{{ route('admin.offers.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Offers</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Offer</h1>
    </div>
    @include('admin.offers._form', ['offer' => $offer, 'mentees' => $mentees])
</div>
@endsection
