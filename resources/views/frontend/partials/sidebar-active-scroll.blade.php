<script>
(function () {
    function revealActiveSidebarItem() {
        var sidebar = document.getElementById('dashSidebar');
        if (!sidebar) return;

        var active = sidebar.querySelector('.sidebar-item.active, [data-nav-active="1"]');
        if (!active) return;

        try {
            active.scrollIntoView({ block: 'center', inline: 'nearest' });
        } catch (e) {
            active.scrollIntoView(true);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', revealActiveSidebarItem);
    } else {
        revealActiveSidebarItem();
    }
})();
</script>
