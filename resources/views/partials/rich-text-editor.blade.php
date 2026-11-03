@php
    $selector = $selector ?? '#bio-area';
    $height = (int) ($height ?? 280);
    $theme = $theme ?? 'auto';
    $minPlain = (int) ($minPlain ?? 50);
    $maxPlain = (int) ($maxPlain ?? 2000);
    $countId = $countId ?? 'bio-count';
@endphp
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function () {
    const selector = @json($selector);
    const countEl = document.getElementById(@json($countId));
    const minPlain = {{ $minPlain }};
    const maxPlain = {{ $maxPlain }};
    const themeMode = @json($theme);
    const editorHeight = {{ $height }};
    const tinymceBase = 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3';
    let mounting = false;

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
        countEl.style.color = n < minPlain
            ? (isLight() ? '#dc2626' : 'var(--error, #ef4444)')
            : '';
    }

    function getBoundEditor() {
        if (!window.tinymce) return null;
        const el = document.querySelector(selector);
        if (!el) return null;
        if (el.id && tinymce.get(el.id)) return tinymce.get(el.id);
        if (Array.isArray(tinymce.editors)) {
            return tinymce.editors.find(function (ed) {
                return ed && ed.targetElm === el;
            }) || null;
        }
        return null;
    }

    function editorOptions(light) {
        return {
            selector: selector,
            height: editorHeight,
            menubar: false,
            plugins: 'lists link',
            toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | removeformat',
            branding: false,
            promotion: false,
            convert_urls: false,
            base_url: tinymceBase,
            suffix: '.min',
            skin: light ? 'oxide' : 'oxide-dark',
            content_css: light ? 'default' : 'dark',
            content_style: light
                ? 'html,body{background:#f4f4f5!important;color:#18181b!important;}body{font-family:Plus Jakarta Sans,system-ui,sans-serif;font-size:14px;line-height:1.6;margin:12px;}a{color:#d97706;}'
                : 'html,body{background:#18181b!important;color:#fafafa!important;}body{font-family:Plus Jakarta Sans,system-ui,sans-serif;font-size:14px;line-height:1.6;margin:12px;}a{color:#fbbf24;}',
            setup: function (editor) {
                const sync = function () { updateCount(editor); };
                editor.on('init Change KeyUp SetContent Undo Redo', sync);
            },
        };
    }

    function mountEditor() {
        if (!window.tinymce || mounting) return;
        const el = document.querySelector(selector);
        if (!el) return;

        mounting = true;
        let html = el.value || '';
        const existing = getBoundEditor();
        if (existing) {
            html = existing.getContent({ format: 'html' });
            existing.remove();
        } else {
            try { tinymce.remove(selector); } catch (e) {}
        }

        Promise.resolve(tinymce.init(editorOptions(isLight())))
            .then(function (editors) {
                const editor = Array.isArray(editors) ? editors[0] : getBoundEditor();
                if (editor) {
                    if (html) editor.setContent(html);
                    updateCount(editor);
                }
            })
            .catch(function (err) {
                console.error('TinyMCE init failed', err);
            })
            .finally(function () {
                mounting = false;
            });
    }

    function boot() {
        if (!window.tinymce) {
            console.error('TinyMCE failed to load');
            return;
        }
        mountEditor();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    document.addEventListener('as:themechange', function () {
        mountEditor();
    });

    document.querySelectorAll('form').forEach(function (form) {
        if (!form.querySelector(selector)) return;
        form.addEventListener('submit', function () {
            if (window.tinymce) tinymce.triggerSave();
        });
    });
})();
</script>
