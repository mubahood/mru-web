/**
 * The notice strip.
 *
 * Three jobs, all of which the strip must survive without: pace the ticker to
 * its own content, let a reader stop it, and remember a dismissal. The markup
 * is correct and readable before any of this runs.
 */
(function () {
  'use strict';

  var strip = document.querySelector('[data-notice-strip]');
  if (!strip) return;

  var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function store(key, value) {
    try { window.localStorage.setItem(key, value); } catch (e) { /* private mode */ }
  }
  function stored(key) {
    try { return window.localStorage.getItem(key); } catch (e) { return null; }
  }

  Array.prototype.forEach.call(strip.querySelectorAll('[data-notice]'), function (item) {
    // --- dismissal ------------------------------------------------------
    // The key carries the notice's updated_at, so editing a notice brings it
    // back for someone who dismissed the previous wording.
    var key = item.getAttribute('data-key');
    if (key && stored(key) === 'off') { item.hidden = true; return; }

    var x = item.querySelector('[data-ns-dismiss]');
    if (x) {
      x.addEventListener('click', function () {
        item.hidden = true;
        if (key) store(key, 'off');
        if (!strip.querySelector('[data-notice]:not([hidden])')) strip.hidden = true;
      });
    }

    // --- the ticker -----------------------------------------------------
    var track = item.querySelector('[data-ns-track]');
    if (track && !reduced) {
      // Pace by distance, not by a fixed duration: a short lane should not
      // crawl and a long one should not sprint. ~118px per second reads
      // briskly and still tracks by eye; the floor stops a very short lane
      // looping frantically.
      var pace = function () {
        var half = track.scrollWidth / 2;
        if (!half) return;
        track.style.setProperty('--ns-dur', Math.max(9, Math.round(half / 118)) + 's');
      };
      pace();
      if (window.ResizeObserver) new ResizeObserver(pace).observe(item);
      // Web fonts land after first paint and change the width underneath us.
      if (document.fonts && document.fonts.ready) document.fonts.ready.then(pace);

      var pause = item.querySelector('[data-ns-pause]');
      if (pause) {
        pause.addEventListener('click', function () {
          var paused = item.classList.toggle('is-paused');
          pause.setAttribute('aria-pressed', paused ? 'true' : 'false');
          pause.setAttribute('aria-label', paused
            ? 'Resume the scrolling announcement'
            : 'Pause the scrolling announcement');
          pause.querySelector('i').className = paused ? 'fas fa-play' : 'fas fa-pause';
        });
      }
    }

    // --- the countdown --------------------------------------------------
    // Rendered correct by the server. This only matters to a tab left open
    // across midnight, so it re-checks on a slow timer rather than per second.
    var count = item.querySelector('[data-ns-deadline]');
    if (count) {
      var deadline = new Date(count.getAttribute('data-ns-deadline'));
      var refresh = function () {
        var today = new Date(); today.setHours(0, 0, 0, 0);
        var end = new Date(deadline); end.setHours(0, 0, 0, 0);
        var days = Math.round((end - today) / 86400000);
        if (days < 0) { item.hidden = true; return; }
        var text = days === 0 ? 'closes today' : (days === 1 ? '1 day left' : days + ' days left');
        var label = count.lastChild;
        if (label && label.nodeType === 3) label.nodeValue = ' ' + text;
      };
      setInterval(refresh, 300000);
    }
  });
})();
