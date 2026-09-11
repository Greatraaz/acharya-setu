/**
 * Vedrix — Install app (PWA) helper
 * Native prompt when available; otherwise browser-specific install guide.
 */
(function () {
  const STORAGE_KEY = 'vedrix_a2hs_dismissed_until';
  const DISMISS_DAYS = 14;

  const ua = navigator.userAgent || '';

  function isStandalone() {
    return (
      window.matchMedia('(display-mode: standalone)').matches ||
      window.matchMedia('(display-mode: fullscreen)').matches ||
      window.navigator.standalone === true
    );
  }

  function isIos() {
    return (
      /iPhone|iPad|iPod/i.test(ua) ||
      (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)
    );
  }

  function isAndroid() {
    return /Android/i.test(ua);
  }

  function isMobileLike() {
    return isIos() || isAndroid() || window.matchMedia('(max-width: 900px)').matches;
  }

  function isCriOS() {
    return /CriOS/i.test(ua);
  }

  function isFxiOS() {
    return /FxiOS/i.test(ua);
  }

  function isEdgiOS() {
    return /EdgiOS/i.test(ua);
  }

  function isSafariIOS() {
    return isIos() && /Safari/i.test(ua) && !isCriOS() && !isFxiOS() && !isEdgiOS() && !/OPiOS|OPT\//i.test(ua);
  }

  function isDismissed() {
    try {
      return Number(localStorage.getItem(STORAGE_KEY) || 0) > Date.now();
    } catch (e) {
      return false;
    }
  }

  function dismiss() {
    try {
      localStorage.setItem(
        STORAGE_KEY,
        String(Date.now() + DISMISS_DAYS * 24 * 60 * 60 * 1000)
      );
    } catch (e) { /* ignore */ }
    hideSheet();
    hideChip();
  }

  function guideForEnv() {
    if (isIos()) {
      if (isCriOS()) {
        return {
          subtitle: 'Install from Chrome’s Share button (next to the address bar).',
          steps: [
            'Tap the <strong>Share</strong> icon on the <em>right of the address bar</em> (box with an arrow). Do <strong>not</strong> use the ⋯ menu at the bottom.',
            'In the share sheet, <strong>scroll down</strong> past apps until you see actions.',
            'Tap <strong>Add to Home Screen</strong>, then tap <strong>Add</strong>.',
          ],
          hint: 'If you don’t see it: scroll to the bottom → <strong>Edit Actions</strong> → enable <strong>Add to Home Screen</strong>. Or open this site in Safari and install from there.',
        };
      }
      if (isFxiOS()) {
        return {
          subtitle: 'Install from Firefox’s menu on iPhone/iPad.',
          steps: [
            'Tap the <strong>⋯</strong> menu in Firefox.',
            'Tap <strong>Share</strong>.',
            'Scroll and tap <strong>Add to Home Screen</strong>, then <strong>Add</strong>.',
          ],
          hint: 'If it’s missing, open this page in Safari → Share → Add to Home Screen.',
        };
      }
      if (isEdgiOS()) {
        return {
          subtitle: 'Install from Edge’s Share sheet.',
          steps: [
            'Tap <strong>Share</strong> (near the address bar).',
            'Scroll the sheet and tap <strong>Add to Home Screen</strong>.',
            'Tap <strong>Add</strong>.',
          ],
          hint: 'If you don’t see the action, enable it under Edit Actions, or use Safari.',
        };
      }
      // Safari (and other WebKit browsers)
      return {
        subtitle: 'Install from Safari’s Share menu.',
        steps: [
          'Tap the <strong>Share</strong> button <svg class="a2hs-share-ico" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path fill="currentColor" d="M12 3l4 4h-3v6h-2V7H8l4-4zm-7 14h14v2H5v-2z"/></svg> at the bottom of Safari.',
          'Scroll the share sheet and tap <strong>Add to Home Screen</strong>.',
          'Tap <strong>Add</strong> in the top-right.',
        ],
        hint: 'Don’t see it? Scroll to the bottom → Edit Actions → turn on Add to Home Screen.',
      };
    }

    if (isAndroid()) {
      return {
        subtitle: 'Install from your browser menu.',
        steps: [
          'Tap the browser <strong>menu</strong> (⋮).',
          'Choose <strong>Install app</strong>, <strong>Add to Home screen</strong>, or <strong>Install</strong>.',
          'Confirm to add the Vedrix shortcut.',
        ],
        hint: 'Wording varies by browser (Chrome, Samsung Internet, Firefox, Edge).',
      };
    }

    // Desktop fallback
    return {
      subtitle: 'Install from the browser address bar or menu.',
      steps: [
        'Look for an <strong>install</strong> icon in the address bar (Chrome / Edge).',
        'Or open the browser menu and choose <strong>Install Vedrix</strong> / <strong>Apps → Install this site as an app</strong>.',
        'On Mac Safari: <strong>File → Add to Dock</strong>.',
      ],
      hint: 'Firefox desktop does not support one-click web app install; bookmark the site instead.',
    };
  }

  function fillGuide() {
    const guide = guideForEnv();
    const subtitle = document.querySelector('[data-a2hs-subtitle]');
    const stepsEl = document.querySelector('[data-a2hs-steps]');
    const hintEl = document.querySelector('[data-a2hs-hint]');
    if (subtitle) subtitle.innerHTML = guide.subtitle;
    if (stepsEl) {
      stepsEl.innerHTML = guide.steps.map((s) => `<li>${s}</li>`).join('');
    }
    if (hintEl) {
      if (guide.hint) {
        hintEl.hidden = false;
        hintEl.innerHTML = guide.hint;
      } else {
        hintEl.hidden = true;
        hintEl.textContent = '';
      }
    }
  }

  function setPanel(mode) {
    const sheet = document.getElementById('a2hs-banner');
    if (!sheet) return;
    sheet.dataset.mode = mode;
    const native = sheet.querySelector('[data-a2hs-panel="native"]');
    const guide = sheet.querySelector('[data-a2hs-panel="guide"]');
    if (native) native.hidden = mode !== 'native';
    if (guide) guide.hidden = mode !== 'guide';
    if (mode === 'guide') fillGuide();
  }

  function showSheet(mode) {
    const sheet = document.getElementById('a2hs-banner');
    if (!sheet) return;
    setPanel(mode);
    sheet.hidden = false;
  }

  function hideSheet() {
    const sheet = document.getElementById('a2hs-banner');
    if (sheet) sheet.hidden = true;
  }

  function showChip() {
    const chip = document.getElementById('a2hs-chip');
    if (chip) chip.hidden = false;
  }

  function hideChip() {
    const chip = document.getElementById('a2hs-chip');
    if (chip) chip.hidden = true;
  }

  function openInstallUi() {
    if (deferredPrompt) {
      showSheet('native');
      return;
    }
    showSheet('guide');
  }

  let deferredPrompt = null;

  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    if (isStandalone() || isDismissed()) return;
    showChip();
    // Soft prompt after engagement delay (standard UX; not forced immediately)
    setTimeout(() => {
      if (!isDismissed() && !isStandalone() && deferredPrompt) {
        showSheet('native');
      }
    }, 2500);
  });

  window.addEventListener('appinstalled', () => {
    deferredPrompt = null;
    dismiss();
  });

  document.addEventListener('DOMContentLoaded', () => {
    if ('serviceWorker' in navigator) {
      const swUrl = (document.querySelector('link[rel="manifest"]') && '/sw.js') || '/sw.js';
      navigator.serviceWorker.register(swUrl).catch(() => {});
    }

    const installBtn = document.querySelector('[data-a2hs-install]');
    const dismissBtns = document.querySelectorAll('[data-a2hs-dismiss]');
    const chip = document.getElementById('a2hs-chip');

    if (installBtn) {
      installBtn.addEventListener('click', async () => {
        if (!deferredPrompt) {
          showSheet('guide');
          return;
        }
        deferredPrompt.prompt();
        try {
          await deferredPrompt.userChoice;
        } catch (e) { /* ignore */ }
        deferredPrompt = null;
        hideSheet();
        hideChip();
      });
    }

    dismissBtns.forEach((btn) => btn.addEventListener('click', dismiss));

    if (chip) {
      chip.addEventListener('click', openInstallUi);
    }

    if (isStandalone() || isDismissed()) {
      return;
    }

    // Environments without beforeinstallprompt still need a discoverable Install control.
    if (isIos() || (isAndroid() && !deferredPrompt) || isMobileLike()) {
      showChip();
      if (isIos()) {
        setTimeout(() => {
          if (!isDismissed() && !isStandalone()) showSheet('guide');
        }, 2200);
      }
    }

    // Expose for rare manual triggers
    window.VedrixInstall = { open: openInstallUi, dismiss };
  });
})();
