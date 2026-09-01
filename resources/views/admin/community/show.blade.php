@extends('admin.layouts.app')
@section('title', '#' . $channel->name)
@section('content')
@include('partials.community-thread-show')
@endsection
