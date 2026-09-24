@extends('frontend.layouts.app')
@section('title', 'Edit Videos — Vedrix Mentor')

@section('content')
<div class="dash-layout assess-page">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <a href="{{ route('mentor.videos.show', $item->id) }}" class="assess-back">← Back</a>
        <div class="dash-header">
            <div>
                <div class="dash-title">Edit collection</div>
                <div class="dash-subtitle">{{ $item->name }}</div>
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

        <form method="POST" action="{{ route('mentor.videos.update', $item->id) }}" enctype="multipart/form-data" class="card" style="padding:20px;display:grid;gap:16px;max-width:720px;">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label">Collection name *</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $item->name) }}" required maxlength="255">
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" class="form-input" rows="3">{{ old('description', $item->description) }}</textarea>
            </div>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))>
                Active (visible to mentees)
            </label>

            @if($item->files->isNotEmpty())
            <div>
                <div class="form-label" style="margin-bottom:8px;">Current videos</div>
                <div style="display:grid;gap:8px;">
                    @foreach($item->files as $file)
                    <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid var(--border);border-radius:10px;font-size:13px;">
                        <input type="checkbox" name="remove_video_ids[]" value="{{ $file->id }}">
                        <span style="flex:1;">{{ $file->file_name }}</span>
                        <span style="color:var(--text-3);font-size:11px;">Remove</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div>
                <label class="form-label">Add more videos</label>
                <input type="file" name="videos[]" class="form-input" accept="video/mp4,video/quicktime,video/x-msvideo,video/webm,video/mpeg" multiple>
            </div>

            <button type="submit" class="btn btn-primary" style="justify-self:start;">Save changes</button>
        </form>
    </div>
</div>
@endsection
