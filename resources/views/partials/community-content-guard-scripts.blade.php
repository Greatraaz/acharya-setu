<script>
window.CommunityContentGuard = (function () {
    const BANNED_WORDS = @json(\App\Models\Channel::bannedWordsList());

    const MESSAGE = @json(\App\Models\Channel::abusiveContentMessage('body'));
    const NAME_MESSAGE = @json(\App\Models\Channel::abusiveContentMessage('name'));
    const DESCRIPTION_MESSAGE = @json(\App\Models\Channel::abusiveContentMessage('description'));

    function escapeRegex(value) {
        return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function findBannedWord(text) {
        text = (text || '').toLowerCase().trim();
        if (!text) return null;

        for (const word of BANNED_WORDS) {
            if (word.includes(' ')) {
                if (text.includes(word)) return word;
                continue;
            }

            const pattern = new RegExp('\\b' + escapeRegex(word) + '\\b', 'iu');
            if (pattern.test(text)) return word;
        }

        return null;
    }

    function removeInlineWarning(form) {
        form?.querySelector('[data-community-inline-warning]')?.remove();
    }

    function showInlineWarning(form, message) {
        if (!form) return;

        removeInlineWarning(form);

        const warning = document.createElement('div');
        warning.className = 'community-content-warning community-content-warning--inline';
        warning.setAttribute('data-community-inline-warning', '');
        warning.setAttribute('role', 'alert');
        warning.innerHTML =
            '<svg xmlns="http://www.w3.org/2000/svg" class="community-content-warning__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">' +
            '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>' +
            '</svg>' +
            '<div><p class="community-content-warning__title">Please keep it respectful</p>' +
            '<p class="community-content-warning__text"></p></div>';

        warning.querySelector('.community-content-warning__text').textContent = message;

        const anchor = form.querySelector('.community-thread__composer')
            || form.querySelector('.community-thread__composer-wrap')
            || form.firstElementChild;

        if (anchor) {
            anchor.insertAdjacentElement('beforebegin', warning);
        } else {
            form.prepend(warning);
        }

        warning.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function validateForm(form) {
        const bodyInput = form.querySelector('[name="body"], [name="message"]');
        const nameInput = form.querySelector('[name="name"]');
        const descriptionInput = form.querySelector('[name="description"]');

        if (bodyInput) {
            const body = bodyInput.value.trim();
            if (body && findBannedWord(body)) {
                return { field: bodyInput, message: MESSAGE };
            }
        }

        if (nameInput) {
            const name = nameInput.value.trim();
            if (name && findBannedWord(name)) {
                return { field: nameInput, message: NAME_MESSAGE };
            }
        }

        if (descriptionInput) {
            const description = descriptionInput.value.trim();
            if (description && findBannedWord(description)) {
                return { field: descriptionInput, message: DESCRIPTION_MESSAGE };
            }
        }

        return null;
    }

    function attach() {
        document.querySelectorAll('.channel-composer-form, .community-content-guarded').forEach((form) => {
            if (form.dataset.contentGuardBound === '1') return;
            form.dataset.contentGuardBound = '1';

            form.addEventListener('submit', (event) => {
                const violation = validateForm(form);
                if (!violation) {
                    removeInlineWarning(form);
                    return;
                }

                event.preventDefault();
                showInlineWarning(form, violation.message);
                violation.field.focus();
            });

            form.querySelectorAll('[name="body"], [name="message"], [name="name"], [name="description"]').forEach((input) => {
                input.addEventListener('input', () => removeInlineWarning(form));
            });
        });
    }

    document.addEventListener('DOMContentLoaded', attach);

    return { findBannedWord, attach };
})();
</script>
