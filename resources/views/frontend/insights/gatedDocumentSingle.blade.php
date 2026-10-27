@extends('frontend.layouts.frontend')
@section('title', $item->title.' — '.$meta['label'])
@section('meta_description', $item->excerpt(40))

@section('content')
<div class="insights-page">
    <section class="insights-banner insights-banner--detail">
        <div class="insights-banner__bg" aria-hidden="true"></div>
        <div class="insights-banner__overlay" aria-hidden="true"></div>
        <div class="container insights-banner__inner">
            <nav class="insights-breadcrumb">
                <a href="{{ route('home') }}"><i class="bi bi-house-door-fill me-1"></i> Home</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <a href="{{ route('insights.index') }}">Insights</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <a href="{{ route($meta['public_index']) }}">{{ $meta['label'] }}</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <span>{{ \Illuminate\Support\Str::limit($item->title, 48) }}</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi {{ $meta['eyebrow_icon'] }} me-1"></i> {{ $meta['singular'] }}
            </div>
            <h1 class="insights-banner__title insights-banner__title--sm">{{ $item->title }}</h1>
            <div class="lain"></div>
            <div class="blog-detail-meta">
                <span><i class="bi bi-journal-richtext me-1"></i> {{ $meta['team_label'] }}</span>
                <span><i class="bi bi-calendar3 me-1"></i> {{ optional($item->created_at)->format('M j, Y') ?: '—' }}</span>
                <span><i class="bi bi-clock-history me-1"></i> {{ $item->readTimeMinutes() }} Min Read</span>
            </div>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container blog-detail-layout">
            <article class="blog-detail-main">
                @if($item->imageUrl())
                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}" class="blog-detail-hero">
                @endif
                <div class="blog-detail-content prose-blog">
                    {!! $item->description !!}
                </div>
            </article>

            <aside class="blog-detail-aside">
                @if(!empty($meta['gated']))
                    <div class="session-register-card" id="download-form">
                        <div class="session-register-card__head">
                            <span><i class="bi bi-download text-warning"></i></span>
                            <h3>{{ $meta['form_title'] }}</h3>
                        </div>
                        <p class="session-register-card__sub">Fill in your details and we will start the PDF download.</p>

                        @if($errors->any())
                            <div class="session-register-error mb-2">Please check the form and try again.</div>
                        @endif

                        <form method="POST" action="{{ route($meta['public_download'], $item->slug) }}" class="session-register-form">
                            @csrf
                            <div>
                                <label for="gated-full-name">Full Name *</label>
                                <input id="gated-full-name" type="text" name="full_name" value="{{ old('full_name', auth()->user()?->name ?? '') }}" required placeholder="Your name">
                                @error('full_name')<p class="session-register-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="gated-email">Email *</label>
                                <input id="gated-email" type="email" name="email" value="{{ old('email', auth()->user()?->email ?? '') }}" required placeholder="you@email.com">
                                @error('email')<p class="session-register-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="gated-phone">Phone *</label>
                                <input id="gated-phone" type="text" name="phone" value="{{ old('phone') }}" required placeholder="+91">
                                @error('phone')<p class="session-register-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="gated-org">Organisation</label>
                                <input id="gated-org" type="text" name="organization" value="{{ old('organization') }}" placeholder="College / company">
                                @error('organization')<p class="session-register-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="gated-role">I am a</label>
                                <select id="gated-role" name="role">
                                    <option value="">Select</option>
                                    @foreach(['Student', 'Working professional', 'Mentor', 'Other'] as $role)
                                        <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                                    @endforeach
                                </select>
                                @error('role')<p class="session-register-error">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="session-register-btn">
                                Download PDF <i class="bi bi-arrow-down-circle ms-1"></i>
                            </button>
                        </form>
                    </div>
                @endif

                <div class="blog-aside-card">
                    <h3>{{ $meta['more_title'] }}</h3>
                    @forelse($recent as $row)
                        <a href="{{ route($meta['public_show'], $row->slug) }}" class="blog-aside-item">
                            <div class="blog-aside-thumb">
                                @if($row->imageUrl())
                                    <img src="{{ $row->imageUrl() }}" alt="{{ $row->title }}" loading="lazy">
                                @else
                                    <span><i class="bi {{ $meta['placeholder_icon'] }} text-muted"></i></span>
                                @endif
                            </div>
                            <div>
                                <div class="blog-aside-title">{{ $row->title }}</div>
                                <div class="blog-aside-date"><i class="bi bi-calendar3 me-1"></i> {{ optional($row->created_at)->format('M j, Y') }}</div>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-muted">No other {{ strtolower($meta['label']) }} yet.</p>
                    @endforelse
                </div>

                <div class="blog-aside-cta">
                    <h3>{{ $meta['cta_title'] }}</h3>
                    <p>{{ $meta['cta_body'] }}</p>
                    <a href="{{ route('mentors.search') }}" class="session-register-btn">Find a Mentor <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </aside>
        </div>
    </section>
</div>
@endsection
