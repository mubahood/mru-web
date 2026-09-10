{{--
  Google Analytics (GA4), with Consent Mode v2.

  Four things the pasted snippet does not do, and this site needs:

  1. Consent, set BEFORE anything else. The order is not stylistic — a default
     that arrives after gtag has already configured is a default that never
     applied. Denied in the regions that require prior consent, granted
     elsewhere, and Google resolves the region from the request so no
     geolocation lookup is needed here.

  2. wire:navigate. Internal links swap the body without a reload, so gtag's
     one page_view on load would be the ONLY page_view of a visit — every
     journey through the site reading as a single-page bounce. A page_view is
     sent on each livewire:navigated, with send_page_view off on config so the
     first is not counted twice.

  3. The id comes from the environment. A staging copy carrying the production
     id pollutes the real numbers, and the only symptom is traffic that
     mysteriously doubled.

  4. It obeys the same exclusions as the first-party tracker — see
     AnalyticsScope. Measuring the back office tells you nothing about the
     audience, and one system excluding it while the other counts it means the
     two can never be reconciled.

  Nothing is emitted at all when there is no id or the request is excluded: no
  tag, no request to Google, rather than a script that loads and no-ops.
--}}
@php
  $gaId = \App\Support\AnalyticsScope::googleId(request());
  $consent = (bool) config('analytics.google.consent.enabled', true);
  $strict = (array) config('analytics.google.consent.strict_regions', []);
@endphp

@if($gaId)
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}

    @if($consent)
    /* Consent defaults come first, before the library is even requested.
       Denied here does not mean "no measurement": consent mode still sends a
       cookieless ping, so the visit is counted in aggregate with no
       identifiers and no cookies. */
    gtag('consent', 'default', {
      ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied',
      analytics_storage: 'denied',
      wait_for_update: 500,
      region: @json($strict)
    });
    gtag('consent', 'default', {
      ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied',
      analytics_storage: 'granted'
    });

    /* A choice already made outrides both defaults, and is applied before the
       library loads so the very first hit already reflects it. */
    (function () {
      try {
        var saved = localStorage.getItem('mru-analytics-consent');
        if (saved === 'granted' || saved === 'denied') {
          gtag('consent', 'update', { analytics_storage: saved });
        }
      } catch (e) { /* private mode: leave the defaults standing */ }
    })();
    @endif

    gtag('js', new Date());
    /* send_page_view:false, then an explicit page_view below. Left on, gtag
       fires one on load and this file fires another after navigation — the
       first page of every visit would be counted twice. */
    gtag('config', @json($gaId), { send_page_view: false });
  </script>
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
  <script>
    (function () {
      function view() {
        gtag('event', 'page_view', {
          page_title: document.title,
          page_location: location.href,
          page_path: location.pathname + location.search
        });
      }

      view();                                                   // first paint
      document.addEventListener('livewire:navigated', view);    // and every swap after it

      /* The site already marks its calls to action with data-a / data-a-label
         for the first-party tracker. Forwarding them costs nothing and means
         Google sees "Apply Now" and "WhatsApp: admission enquiries" as events
         rather than only knowing that a page was opened. Delegated from the
         document, so it keeps working after a body swap. */
      document.addEventListener('click', function (e) {
        var el = e.target && e.target.closest ? e.target.closest('[data-a]') : null;
        if (!el) return;
        gtag('event', el.getAttribute('data-a').replace(/[^a-z0-9_]+/gi, '_'), {
          label: el.getAttribute('data-a-label') || (el.textContent || '').trim().slice(0, 90),
          link_url: el.getAttribute('href') || undefined,
          page_path: location.pathname
        });
      }, true);
    })();
  </script>
@endif
