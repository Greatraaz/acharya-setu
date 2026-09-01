@extends('frontend.layouts.app')
@section('title', $channel->name.' — Community')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        @include('partials.community-thread-show')
    </div>
</div>
@endsection
