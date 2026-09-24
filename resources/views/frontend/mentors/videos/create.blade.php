@extends('frontend.layouts.app')
@section('title', 'Upload Videos — Vedrix Mentor')

@section('content')
<div class="dash-layout assess-page">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <a href="{{ route('mentor.videos.index') }}" class="assess-back">← Back to Videos</a>
        <div class="dash-header">
            <div>
                <div class="dash-title">Upload videos</div>
                <div class="dash-subtitle">MP4, MOV, AVI, WEBM or MPEG — max 10MB each.</div>
            </div>
        </div>

        @if(session('error'))
        <div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:16px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('mentor.videos.store') }}" enctype="multipart/form-data" class="card" style="padding:20px;display:grid;gap:16px;max-width:640px;">
            @csrf
            <div>
                <label class="form-label">Collection name *</label>
                <input type="text" name="name" class="form-input" value="{{ old('name') }}" required maxlength="255" placeholder="e.g. Interview tips">
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" class="form-input" rows="3" placeholder="Optional notes for mentees">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="form-label">Video files *</label>
                <input type="file" name="videos[]" class="form-input" accept="video/mp4,video/quicktime,video/x-msvideo,video/webm,video/mpeg" multiple required>
            </div>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                Active (visible to mentees)
            </label>
            <button type="submit" class="btn btn-primary" style="justify-self:start;">Create collection</button>
        </form>
    </div>
</div>
@endsection
