<script>
(function () {
    var root = document.getElementById('notifBell');
    if (!root) return;

    var btn = document.getElementById('notifBellBtn');
    var panel = document.getElementById('notifPanel');
    var list = document.getElementById('notifList');
    var badge = document.getElementById('notifBadge');
    var markAll = document.getElementById('notifMarkAll');
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrf ? csrf.getAttribute('content') : '';
    var feedUrl = root.getAttribute('data-feed-url');
    var readAllUrl = root.getAttribute('data-read-all-url');
    var readUrlBase = root.getAttribute('data-read-url');
    var userId = root.getAttribute('data-user-id') || '0';
    var storageKey = 'vedrix_notif_seen_' + userId;
    var primed = false;
    var lastSeenId = parseInt(localStorage.getItem(storageKey) || '0', 10) || 0;

    function headers(json) {
        var h = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' };
        if (csrfToken) h['X-CSRF-TOKEN'] = csrfToken;
        if (json) h['Content-Type'] = 'application/json';
        return h;
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function setBadge(count) {
        var n = parseInt(count, 10) || 0;
        if (n <= 0) {
            badge.hidden = true;
            badge.textContent = '0';
            return;
        }
        badge.hidden = false;
        badge.textContent = n > 99 ? '99+' : String(n);
    }

    function render(items) {
        if (!items || !items.length) {
            list.innerHTML = '<div class="notif-panel__empty">No notifications yet.</div>';
            return;
        }
        list.innerHTML = items.map(function (item) {
            var unread = item.is_read ? '' : ' is-unread';
            var href = item.url ? escapeHtml(item.url) : '#';
            return '<a class="notif-item' + unread + '" data-id="' + item.id + '" href="' + href + '">' +
                '<div class="notif-item__title">' + escapeHtml(item.title) + '</div>' +
                '<div class="notif-item__body">' + escapeHtml(item.body || '') + '</div>' +
                '<div class="notif-item__time">' + escapeHtml(item.created_at_human || '') + '</div>' +
                '</a>';
        }).join('');
    }

    function maybeBrowserNotify(items) {
        if (!primed || !('Notification' in window) || Notification.permission !== 'granted') {
            return;
        }
        items.filter(function (item) {
            return !item.is_read && item.id > lastSeenId;
        }).slice(0, 3).forEach(function (item) {
            try {
                var n = new Notification(item.title || 'Vedrix', {
                    body: item.body || '',
                    tag: 'vedrix-' + item.id
                });
                n.onclick = function () {
                    window.focus();
                    if (item.url) window.location.href = item.url;
                    n.close();
                };
            } catch (e) {}
        });
    }

    function remember(items) {
        if (!items || !items.length) return;
        var maxId = items.reduce(function (max, item) {
            return item.id > max ? item.id : max;
        }, lastSeenId);
        if (maxId > lastSeenId) {
            lastSeenId = maxId;
            localStorage.setItem(storageKey, String(lastSeenId));
        }
    }

    function loadFeed() {
        return fetch(feedUrl, { headers: headers(false), credentials: 'same-origin' })
            .then(function (res) { return res.ok ? res.json() : null; })
            .then(function (data) {
                if (!data) return;
                var items = data.notifications || [];
                setBadge(data.unread_count || data.unreadCount || 0);
                render(items);
                maybeBrowserNotify(items);
                remember(items);
                primed = true;
            })
            .catch(function () {});
    }

    function togglePanel(open) {
        var willOpen = open === undefined ? panel.hidden : open;
        panel.hidden = !willOpen;
        root.classList.toggle('is-open', willOpen);
        btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        if (willOpen && 'Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        togglePanel();
        loadFeed();
    });

    document.addEventListener('click', function (e) {
        if (!root.contains(e.target)) {
            togglePanel(false);
        }
    });

    markAll.addEventListener('click', function (e) {
        e.preventDefault();
        fetch(readAllUrl, { method: 'POST', headers: headers(true), credentials: 'same-origin' })
            .then(function () { return loadFeed(); })
            .catch(function () {});
    });

    list.addEventListener('click', function (e) {
        var item = e.target.closest('.notif-item');
        if (!item) return;
        var id = item.getAttribute('data-id');
        if (!id) return;
        fetch(readUrlBase + '/' + id + '/read', {
            method: 'POST',
            headers: headers(true),
            credentials: 'same-origin'
        }).catch(function () {});
    });

    loadFeed();
    setInterval(loadFeed, 12000);
})();
</script>
