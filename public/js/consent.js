/**
 * The analytics consent bar.
 *
 * Shows only when no choice has been stored, applies the choice to Google
 * Consent Mode immediately, and remembers it.
 *
 * The bar ships hidden and is revealed here rather than the other way round: a
 * reader with JavaScript off would otherwise be shown two buttons that cannot
 * do anything, and consent mode would already be sitting at its default, which
 * is the safe state anyway.
 */
(function () {
  'use strict';

  var KEY = 'mru-analytics-consent';
  var bar = document.getElementById('consent-bar');
  if (!bar) return;

  function read() {
    try { return localStorage.getItem(KEY); } catch (e) { return null; }
  }
  function write(v) {
    try { localStorage.setItem(KEY, v); } catch (e) { /* private mode */ }
  }

  function apply(choice) {
    // gtag may be absent: no measurement id, an excluded page, or a blocker.
    // The choice is still recorded so the bar does not come back.
    if (typeof window.gtag === 'function') {
      window.gtag('consent', 'update', { analytics_storage: choice });
    }
  }

  var saved = read();
  if (saved === 'granted' || saved === 'denied') {
    apply(saved);            // the inline tag applies it too; this covers a late load
    return;                  // and the bar stays hidden
  }

  // Nothing decided yet. Ask, without getting in the way.
  bar.hidden = false;

  bar.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('[data-consent]');
    if (!btn) return;
    var choice = btn.getAttribute('data-consent');
    write(choice);
    apply(choice);
    bar.hidden = true;
  });

  // Escape dismisses without deciding — the bar returns on the next visit,
  // which is the honest outcome of not having answered.
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !bar.hidden) bar.hidden = true;
  });
})();
