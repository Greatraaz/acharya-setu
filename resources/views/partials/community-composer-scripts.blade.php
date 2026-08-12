<script>
document.addEventListener('change', function (e) {
    const input = e.target;
    if (!(input instanceof HTMLInputElement) || input.type !== 'file') return;
    const chipId = input.getAttribute('data-chip');
    if (!chipId) return;
    const chip = document.getElementById(chipId);
    const label = input.closest('.channel-composer__attach');
    if (!chip) return;
    if (input.files && input.files[0]) {
        chip.textContent = input.files[0].name;
        chip.classList.add('is-visible');
        label?.classList.add('is-active');
    } else {
        chip.textContent = '';
        chip.classList.remove('is-visible');
        label?.classList.remove('is-active');
    }
});
</script>
