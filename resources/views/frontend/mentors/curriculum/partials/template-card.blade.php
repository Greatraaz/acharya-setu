<div class="card curriculum-template-card {{ !empty($template->matched) ? 'is-matched' : '' }}">
    <div class="curriculum-template-card__icon" style="background: {{ $template->color ?: '#7c3aed' }}18;">
        {{ $template->icon ?: '🗺️' }}
    </div>
    <div class="curriculum-template-card__body">
        <div class="curriculum-template-card__title-row">
            <div class="curriculum-template-card__title">{{ $template->name }}</div>
            @if(!empty($template->owned))
            <span class="curriculum-template-pill">Yours</span>
            @elseif(!empty($template->matched))
            <span class="curriculum-template-pill">For you</span>
            @endif
        </div>
        <div class="curriculum-template-card__meta">
            {{ $template->expertise_field ?: $template->expertise_stream ?: 'Your industry' }}
            · {{ $template->months_count }} months
            · {{ $template->weeks_count }} weeks
            · {{ $template->tasks_count }} tasks
        </div>
        @if($template->description)
        <p class="curriculum-template-card__desc">{{ \Illuminate\Support\Str::limit($template->description, 110) }}</p>
        @endif
    </div>
    <div class="curriculum-template-card__actions">
        <a href="{{ route('mentor.curriculum.templates.show', $template) }}" class="btn btn-outline btn-sm">Preview</a>
        <button type="button"
                class="btn btn-primary btn-sm js-assign-template"
                data-id="{{ $template->id }}"
                data-name="{{ $template->name }}"
                data-action="{{ route('mentor.curriculum.templates.assign', $template) }}">Assign</button>
    </div>
</div>
