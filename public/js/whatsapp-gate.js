/**
 * The WhatsApp gate.
 *
 * Intercepts every link that reaches the Admissions WhatsApp group and makes
 * the reader confirm they have read what the group is for before it will open.
 *
 * Interception is by destination rather than by class, so a link added to a
 * template later is gated without anyone remembering to mark it up.
 *
 * If this script never runs the links keep working. A gate that silently breaks
 * the only route to Admissions for somebody on a bad connection would cost more
 * than it saves.
 */
(function () {
  'use strict';

  var gate = document.getElementById('wa-gate');
  if (!gate) return;

  var panel = gate.querySelector('.wg-panel');
  var agree = gate.querySelector('[data-wg-agree]');
  var go = gate.querySelector('[data-wg-go]');
  var opener = null;   // what to put focus back on
  var target = '';     // where this open is headed

  var FOCUSABLE = 'a[href]:not([tabindex="-1"]), button:not([disabled]), input, [tabindex]:not([tabindex="-1"])';

  function isGroupLink(a) {
    if (!a || !a.getAttribute) return false;
    var href = a.getAttribute('href') || '';
    return href.indexOf('chat.whatsapp.com') !== -1;
  }

  function setReady(ready) {
    go.setAttribute('aria-disabled', ready ? 'false' : 'true');
    if (ready) { go.setAttribute('href', target); go.setAttribute('tabindex', '0'); }
    else { go.setAttribute('href', '#'); go.setAttribute('tabindex', '-1'); }
  }

  function open(href, from) {
    target = href;
    opener = from || null;
    agree.checked = false;          // every join is its own confirmation
    setReady(false);
    gate.hidden = false;
    document.documentElement.style.overflow = 'hidden';
    // Focus the dialog itself, not the first control: a screen reader should
    // hear the heading and the lead before it hears a checkbox.
    panel.setAttribute('tabindex', '-1');
    panel.focus();
  }

  function close() {
    gate.hidden = true;
    document.documentElement.style.overflow = '';
    if (opener && document.contains(opener)) opener.focus();
    opener = null;
  }

  // --- interception -------------------------------------------------------
  document.addEventListener('click', function (e) {
    if (!gate.hidden) return;                       // already inside the dialog
    var a = e.target.closest && e.target.closest('a');
    if (!isGroupLink(a)) return;
    if (a.hasAttribute('data-wg-go')) return;       // the dialog's own button
    e.preventDefault();
    open(a.getAttribute('href'), a);
  });

  // --- the confirmation ---------------------------------------------------
  agree.addEventListener('change', function () { setReady(agree.checked); });

  go.addEventListener('click', function (e) {
    if (go.getAttribute('aria-disabled') === 'true') {
      e.preventDefault();
      // On a phone the checkbox is below the fold. Doing nothing here would be
      // a dead end, so point at what is missing instead.
      var box = gate.querySelector('.wg-check');
      box.scrollIntoView({ block: 'center', behavior: 'smooth' });
      box.classList.remove('is-wanted');
      void box.offsetWidth;                 // restart the animation
      box.classList.add('is-wanted');
      agree.focus();
      return;
    }
    close();
  });

  agree.addEventListener('change', function () {
    gate.querySelector('.wg-check').classList.remove('is-wanted');
  });

  // --- dismissal ----------------------------------------------------------
  gate.addEventListener('click', function (e) {
    if (e.target.closest('[data-wg-dismiss]')) { e.preventDefault(); close(); }
  });

  document.addEventListener('keydown', function (e) {
    if (gate.hidden) return;

    if (e.key === 'Escape') { e.preventDefault(); close(); return; }

    // Keep Tab inside the dialog. Without this the reader tabs onto the page
    // behind, which is still scrolled and still there, and loses the dialog.
    if (e.key !== 'Tab') return;
    var items = Array.prototype.filter.call(
      panel.querySelectorAll(FOCUSABLE),
      function (el) { return el.offsetParent !== null || el === document.activeElement; }
    );
    if (!items.length) return;
    var first = items[0], last = items[items.length - 1];
    if (e.shiftKey && (document.activeElement === first || document.activeElement === panel)) {
      e.preventDefault(); last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault(); first.focus();
    }
  });
})();
