@php
    $selector = $selector ?? '#bio-area';
    $height = (int) ($height ?? 280);
    $theme = $theme ?? 'auto';
    $minPlain = (int) ($minPlain ?? 50);
    $maxPlain = (int) ($maxPlain ?? 2000);
    $countId = $countId ?? 'bio-count';
@endphp
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
<script>
(function () {
    const selector = @json($selector);
    const countEl = document.getElementById(@json($countId));
    const minPlain = {{ $minPlain }};
    const maxPlain = {{ $maxPlain }};
    const themeMode = @json($theme);

    function isLight() {
        if (themeMode === 'light') return true;
        if (themeMode === 'dark') return false;
        return document.documentElement.getAttribute('data-theme') === 'light';
    }

    function plainLength(html) {
        const d = document.createElement('div');
        d.innerHTML = html || '';
        return (d.textContent || d.innerText || '').replace(/\s+/g, ' ').trim().length;
    }

    function updateCount(editor) {
        if (!countEl) return;
        const n = plainLength(editor.getContent({ format: 'html' }));
        countEl.textContent = n + ' / ' + maxPlain;
        countEl.style.color = n < minPlain ? (isLight() ? '#dc2626' : 'var(--error, #ef4444)') : '';
    }

    const light = isLight();
    tinymce.init({
        selector: selector,
        height: {{ $height }},
        menubar: false,
        plugins: 'lists link',
        toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | removeformat',
        branding: false,
        promotion: false,
        convert_urls: false,
        skin: light ? 'oxide' : 'oxide-dark',
        content_css: light ? 'default' : 'dark',
        content_style: light
            ? 'body { font-family: Inter, system-ui, sans-serif; font-size: 14px; line-height: 1.6; }'
            : 'body { font-family: Inter, system-ui, sans-serif; font-size: 14px; line-height: 1.6; background-color: #18181b !important; color: #fafafa !important; }',
        setup: function (editor) {
            const sync = function () { updateCount(editor); };
            editor.on('init Change KeyUp SetContent Undo Redo', sync);
        }
    });

    document.querySelectorAll('form').forEach(function (form) {
        if (!form.querySelector(selector)) return;
        form.addEventListener('submit', function () {
            if (window.tinymce) tinymce.triggerSave();
        });
    });
})();
</script>
