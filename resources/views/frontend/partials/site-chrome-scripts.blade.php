    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            function updateThemeIcons(theme) {
                document.querySelectorAll('.theme-btn').forEach(function (btn) {
                    btn.innerHTML = theme === 'dark' ? '☀️' : '🌙';
                    btn.title = theme === 'dark' ? 'Switch to Light' : 'Switch to Dark';
                });
            }
            function syncHeaderLogos(theme) {
                document.querySelectorAll('.vj-brand .logo-for-light').forEach(function (img) {
                    img.hidden = theme === 'dark';
                });
                document.querySelectorAll('.vj-brand .logo-for-dark').forEach(function (img) {
                    img.hidden = theme !== 'dark';
                });
            }
            window.toggleTheme = function () {
                if (document.documentElement.getAttribute('data-app-theme') !== '1') return;
                var current = document.documentElement.getAttribute('data-theme') || 'dark';
                var next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                try { localStorage.setItem('as_theme', next); } catch (e) {}
                updateThemeIcons(next);
                syncHeaderLogos(next);
            };
            if (document.documentElement.getAttribute('data-app-theme') === '1') {
                var stored = null;
                try { stored = localStorage.getItem('as_theme'); } catch (e) {}
                var theme = stored || 'dark';
                document.documentElement.setAttribute('data-theme', theme);
                updateThemeIcons(theme);
                syncHeaderLogos(theme);
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                syncHeaderLogos('light');
            }
        })();
    </script>

    <!-- Interactive Components Script (HTML Tabs Switcher, Dashboard Sim, Carousel Helper) -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // General Tab Switcher for .vj-tabs-wrapper
            document.querySelectorAll('.vj-tabs-wrapper').forEach(wrapper => {
                const buttons = wrapper.querySelectorAll('.vj-tab-btn');
                const panes = wrapper.querySelectorAll('.vj-tab-pane');

                buttons.forEach(btn => {
                    btn.addEventListener('click', function () {
                        const targetId = this.getAttribute('data-tab-target');
                        buttons.forEach(b => b.classList.remove('active'));
                        panes.forEach(p => p.classList.remove('active'));

                        this.classList.add('active');
                        const targetPane = wrapper.querySelector('#' + targetId);
                        if (targetPane) {
                            targetPane.classList.add('active');
                        }
                    });
                });
            });

            // Interactive Dashboard Tab Switcher in Section 10
            const dashNavButtons = document.querySelectorAll('.vj-dash-nav-item');
            const dashPanes = document.querySelectorAll('.vj-dash-content-pane');
            if (dashNavButtons.length && dashPanes.length) {
                dashNavButtons.forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        const target = this.getAttribute('data-dash-target');
                        dashNavButtons.forEach(b => b.classList.remove('active'));
                        dashPanes.forEach(p => {
                            p.classList.remove('active');
                            p.style.display = 'none';
                        });

                        this.classList.add('active');
                        const targetPane = document.getElementById(target);
                        if (targetPane) {
                            targetPane.classList.add('active');
                            targetPane.style.display = 'block';
                        }
                    });
                });
            }

            // Mega Menu Smooth Hover Handler (Flicker-free transition & grace delay)
            const navItems = document.querySelectorAll('.vj-nav-item');
            navItems.forEach(item => {
                const megaMenu = item.querySelector('.vj-mega-menu');
                if (!megaMenu) return;

                let leaveTimer = null;

                const openMenu = () => {
                    if (leaveTimer) clearTimeout(leaveTimer);
                    navItems.forEach(other => {
                        if (other !== item) other.classList.remove('is-active');
                    });
                    item.classList.add('is-active');
                };

                const closeMenu = () => {
                    leaveTimer = setTimeout(() => {
                        item.classList.remove('is-active');
                    }, 220);
                };

                item.addEventListener('mouseenter', openMenu);
                item.addEventListener('mouseleave', closeMenu);
            });

            // Close mega menu if clicked outside
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.vj-nav-item')) {
                    navItems.forEach(item => item.classList.remove('is-active'));
                }
            });

            document.querySelectorAll('.vj-header .user-menu').forEach(function (menu) {
                var trigger = menu.querySelector('.user-trigger');
                var dd = menu.querySelector('.user-dropdown');
                if (!trigger || !dd) return;
                trigger.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var willOpen = !dd.classList.contains('open');
                    document.querySelectorAll('.user-dropdown').forEach(function (el) {
                        el.classList.remove('open');
                    });
                    dd.classList.toggle('open', willOpen);
                    trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                });
            });
            document.addEventListener('click', function (e) {
                if (e.target.closest('.user-menu')) return;
                document.querySelectorAll('.vj-header .user-dropdown').forEach(function (dd) {
                    dd.classList.remove('open');
                });
                document.querySelectorAll('.vj-header .user-trigger').forEach(function (btn) {
                    btn.setAttribute('aria-expanded', 'false');
                });
            });
        });
    </script>
