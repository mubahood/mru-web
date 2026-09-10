{{--
  Google Analytics (GA4).

  Three things the pasted snippet does not do, and this site needs:

  1. wire:navigate. Internal links swap the body without a reload, so gtag's
     one page_view on load would be the ONLY page_view of a visit — every
     journey through the site would read as a single-page bounce. A page_view
     is sent on each livewire:navigated instead, with send_page_view disabled
     on config so the first one is not counted twice.

  2. The id comes from the environment. A staging copy carrying the production
     id pollutes the real numbers, and the only symptom is traffic that
     mysteriously doubled.

  3. It obeys the same exclusions as the first-party tracker — see
     AnalyticsScope. Measuring the back office tells you nothing about your
     audience, and having one system exclude it while the other counts it means
     the two can never be reconciled.

  Nothing is emitted at all when there is no id or the request is excluded: no
  tag, no request to Google, rather than a script that loads and no-ops.
--}}
@php $gaId = \App\Support\AnalyticsScope::googleId(request()); @endphp

@if($gaId)
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    /* send_page_view:false, then an explicit page_view below. Left on, gtag
       fires one on load and this file fires another after navigation — the
       first page of every visit would be counted twice. */
    gtag('config', @json($gaId), { send_page_view: false });

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
