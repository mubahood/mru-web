<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <x-seo
    :title="trim($__env->yieldContent('title', 'Muteesa I Royal University | Seeking Greater Horizons in Thought and Action'))"
    :description="trim($__env->yieldContent('desc', 'Muteesa I Royal University (MRU) is an NCHE-accredited private university of the Buganda Kingdom offering career-focused certificates, diplomas, bachelors and masters programmes at its Kakeeka (Mengo, Kampala) and Kirumba (Masaka) campuses.'))"
    :image="trim($__env->yieldContent('og_image', '')) ?: null"
  >@stack('jsonld')</x-seo>
  {{-- Type system: Fraunces (display serif — heritage) + Plus Jakarta Sans
       (UI/body sans — modern). Both self-hosted variable fonts covering the
       full 300–800 range in 128KB total, so no third-party request and no
       weight is ever synthesised. Preloaded because they paint the first
       screen. --}}
  <link rel="preload" as="font" type="font/woff2" href="{{ asset('vendor/fonts/jakarta/jakarta-var-normal.woff2') }}" crossorigin>
  <link rel="preload" as="font" type="font/woff2" href="{{ asset('vendor/fonts/fraunces/fraunces-var-normal.woff2') }}" crossorigin>
  <link rel="stylesheet" href="{{ asset('vendor/fonts/jakarta/jakarta.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/fonts/fraunces/fraunces.css') }}">
  {{-- FontAwesome (74KB) isn't needed for first paint (icons are secondary to text/
       layout); loaded at print-media priority then swapped to all, so it never blocks
       render. noscript fallback covers JS-disabled visitors ('s "works without JS"). --}}
  <link rel="stylesheet" href="{{ asset('vendor/fa/css/all.min.css') }}" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="{{ asset('vendor/fa/css/all.min.css') }}"></noscript>
  @livewireStyles
  <link rel="stylesheet" href="{{ asset('css/mru.css') }}?v={{ filemtime(public_path('css/mru.css')) }}">
  @stack('styles')
</head>
<body>

@php
  $r = fn($n) => request()->routeIs($n) ? 'on' : '';
  $nav = \App\Support\SiteNav::items();
  $isOn = fn (array $item) => request()->routeIs(...($item['match'] ?? []));
  $uniLinks = json_decode((string) \App\Support\Settings::get('university.links', '{}'), true) ?: [];
  $eportalUrl = $uniLinks['eportal'] ?? 'https://eportal.mru.ac.ug/';
  $applyUrl = $uniLinks['apply'] ?? 'https://eportal.mru.ac.ug/apply';
  /* Role-aware account entry point: every signed-in visitor gets a direct door to
     THEIR side of the platform, from every public page. */
  $u = auth()->user();
  if ($u?->isAdmin()) {
      [$accountLabel, $accountUrl, $accountIcon] = ['Dashboard', route('dashboard'), 'fa-gauge'];
  } elseif ($u?->isClient()) {
      [$accountLabel, $accountUrl, $accountIcon] = ['My Projects', route('portal.index'), 'fa-diagram-project'];
  } else {
      [$accountLabel, $accountUrl, $accountIcon] = ['My Courses', route('learn.index'), 'fa-graduation-cap'];
  }
@endphp

<header class="site">
  {{-- Utility row: the doors people already hold keys to. It folds away on
       scroll so the main bar can stay with the reader without stealing
       height from the page. --}}
  <div class="topbar">
    <div class="wrap">
      <a href="{{ $eportalUrl }}" rel="external"><i class="fas fa-right-to-bracket" aria-hidden="true"></i> E-Portal</a>
      <a href="{{ route('courses.index') }}" wire:navigate><i class="fas fa-laptop" aria-hidden="true"></i> e-Learning</a>
      <a href="{{ route('library') }}" wire:navigate><i class="fas fa-book" aria-hidden="true"></i> Library</a>
      <a href="{{ route('scholar.home') }}" wire:navigate><i class="fas fa-flask" aria-hidden="true"></i> MRU Scholar</a>
      <span class="tb-right">
        <a href="tel:+256200903000"><i class="fas fa-phone" aria-hidden="true"></i> +256 200 903 000</a>
        <a href="{{ $uniLinks['eadmin'] ?? 'https://eadmin.mru.ac.ug/' }}" rel="external"><i class="fas fa-user-shield" aria-hidden="true"></i> Staff Login</a>
      </span>
    </div>
  </div>

  <div class="wrap bar">
    <a href="{{ route('home') }}" wire:navigate class="brand">
      <img class="crest" src="{{ asset('images/logo-icon.png') }}" alt="Muteesa I Royal University crest" width="34" height="36">
      <span class="brand-name">Muteesa I Royal University<small>Seeking Greater Horizons</small></span>
    </a>

    <nav class="nav" aria-label="Main">
      @foreach($nav as $item)
        @php $on = $isOn($item); @endphp
        @if(empty($item['children']))
          <div class="nav-item">
            <a href="{{ $item['url'] }}" wire:navigate class="nav-link {{ $on ? 'on' : '' }}"
               @if($on) aria-current="page" @endif>
              {{ $item['label'] }}@if(!empty($item['flag']))<span class="dot" aria-hidden="true"></span>@endif
            </a>
          </div>
        @else
          <div class="nav-item has-menu">
            <button type="button" class="nav-link {{ $on ? 'on' : '' }}"
                    aria-haspopup="true" aria-expanded="false"
                    aria-controls="mega-{{ Str::slug($item['label']) }}">
              {{ $item['label'] }} <i class="fas fa-chevron-down caret" aria-hidden="true"></i>
            </button>
            {{-- The panel spans the whole header and sits flush against its
                 bottom edge, so the pointer never crosses dead space on its
                 way down from the trigger. --}}
            <div class="mega" id="mega-{{ Str::slug($item['label']) }}">
              <div class="mega-inner">
                <div class="mega-intro">
                  <p class="eyebrow">{{ $item['label'] }}</p>
                  @if(!empty($item['blurb']))<p class="mega-blurb">{{ $item['blurb'] }}</p>@endif
                  <a href="{{ $item['url'] }}" wire:navigate class="btn ghost sm">
                    Go to {{ $item['label'] }} <i class="fas fa-arrow-right" aria-hidden="true"></i>
                  </a>
                </div>
                <div class="mega-grid">
                  @foreach($item['children'] as $child)
                    <a href="{{ $child['url'] }}" wire:navigate class="mega-link {{ $isOn($child) ? 'on' : '' }}">
                      <span class="mi"><i class="fas {{ $child['icon'] }}" aria-hidden="true"></i></span>
                      <span>
                        <span class="mt">{{ $child['label'] }}</span>
                        <span class="md">{{ $child['desc'] }}</span>
                      </span>
                    </a>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        @endif
      @endforeach
    </nav>

    <div class="hd-r">
      {{-- The basket leads: it is the only control here whose state the visitor
           has changed themselves, and something waiting to be paid for should be
           the first thing they can get back to. --}}
      @if(app(\App\Services\Shop\Cart::class)->count() > 0)
        <a href="{{ route('cart.show') }}" wire:navigate class="cart-link" aria-label="Basket, {{ app(\App\Services\Shop\Cart::class)->count() }} items">
          <i class="fas fa-basket-shopping" aria-hidden="true"></i>
          <span class="cart-count">{{ app(\App\Services\Shop\Cart::class)->count() }}</span>
        </a>
      @endif

      {{-- One standing action in the bar. The student portal already has its
           own entry in the utility row above, and two competing buttons here
           were what pushed the wordmark into the navigation. --}}
      <a href="{{ $applyUrl }}" rel="external" class="btn gold desk sm">
        Apply Now <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>

      @auth
        <div class="acct desk">
          <button type="button" class="acct-trigger" aria-expanded="false" aria-controls="acct-menu">
            <span class="acct-av" aria-hidden="true">{{ $u->initials }}</span>
            <span class="sr-only">Account menu for {{ $u->name }}</span>
            <i class="fas fa-chevron-down caret" aria-hidden="true"></i>
          </button>
          <div class="acct-menu" id="acct-menu">
            <div class="acct-who">
              <span class="nm">{{ $u->name }}</span>
              <span class="rl">{{ $u->accountTypeLabel() }}</span>
            </div>
            <a href="{{ $accountUrl }}"><i class="fas {{ $accountIcon }}"></i> {{ $accountLabel }}</a>
            <a href="{{ route('account.edit') }}"><i class="fas fa-user-pen"></i> Your account</a>
            <hr>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="danger"><i class="fas fa-right-from-bracket"></i> Sign out</button>
            </form>
          </div>
        </div>
      @else
        <a href="{{ route('login') }}" wire:navigate class="signin desk">Sign in</a>
      @endauth

      <button class="burger" id="burger" aria-label="Menu" aria-expanded="false" aria-controls="mmenu"><i class="fas fa-bars"></i></button>
    </div>
  </div>
</header>

<div class="mmenu" id="mmenu">
  @foreach($nav as $item)
    @if(empty($item['children']))
      <a href="{{ $item['url'] }}" wire:navigate class="{{ $isOn($item) ? 'on' : '' }}">{{ $item['label'] }}</a>
    @else
      {{-- <details> gives an accessible, keyboard-operable disclosure with no
           script at all, and keeps the section open on the page it belongs to. --}}
      <details class="mm-group" @if($isOn($item)) open @endif>
        <summary>{{ $item['label'] }} <i class="fas fa-chevron-down chev" aria-hidden="true"></i></summary>
        <div class="mm-sub">
          @foreach($item['children'] as $child)
            <a href="{{ $child['url'] }}" wire:navigate class="{{ $isOn($child) ? 'on' : '' }}">
              <span class="mi"><i class="fas {{ $child['icon'] }}" aria-hidden="true"></i></span>
              {{ $child['label'] }}
            </a>
          @endforeach
        </div>
      </details>
    @endif
  @endforeach

  <div class="mm-actions">
    {{-- Same order as the desktop header: the actions, then the account. --}}
    <a href="{{ $eportalUrl }}" rel="external" class="btn ghost"><i class="fas fa-door-open"></i> Student E-Portal</a>
    <a href="{{ $applyUrl }}" rel="external" class="btn gold"><i class="fas fa-graduation-cap"></i> Apply Now</a>

    @auth
      <a href="{{ $accountUrl }}" class="btn ghost"><i class="fas {{ $accountIcon }}"></i> {{ $accountLabel }}</a>
      <a href="{{ route('account.edit') }}" wire:navigate class="btn ghost"><i class="fas fa-user-pen"></i> Your account</a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn ghost" style="width:100%;justify-content:center;">Sign out</button>
      </form>
    @else
      <a href="{{ route('login') }}" wire:navigate class="btn ghost">Sign in</a>
    @endauth
  </div>
</div>

<main>
  @yield('content')
</main>

@php
  /* The footer speaks for the whole institution, so it reads the same
     university settings the pages do rather than repeating them in markup. */
  $footContacts = \App\Support\University::contacts();
  $footSocial = \App\Support\University::get('social');
  $footCampuses = $footContacts['campuses'] ?? [];
@endphp
<footer>
  <img class="foot-crest" src="{{ asset('images/logo-icon.png') }}" alt="" aria-hidden="true">
  <div class="wrap">

    <div class="foot">
      <div class="foot-brand">
        <a href="{{ route('home') }}" wire:navigate class="brand">
          <img class="crest" src="{{ asset('images/logo-icon.png') }}" alt="" width="52" height="56">
          <span class="brand-name">Muteesa I Royal University<small>Seeking Greater Horizons</small></span>
        </a>
        <p class="blurb">An NCHE-accredited private university of the Buganda Kingdom, offering career-focused education rooted in cultural heritage.</p>

        {{-- Contacts as one compact block rather than a stack of rows. --}}
        <ul class="foot-contact">
          <li><i class="fas fa-location-dot" aria-hidden="true"></i>
            <span>Kakeeka, Mengo — Kampala &middot; Kirumba — Masaka</span></li>
          <li><i class="fas fa-envelope" aria-hidden="true"></i>
            <a href="mailto:{{ $footContacts['email'] ?? 'info@mru.ac.ug' }}">{{ $footContacts['email'] ?? 'info@mru.ac.ug' }}</a></li>
          <li><i class="fas fa-phone" aria-hidden="true"></i>
            <a href="tel:{{ preg_replace('/\s+/', '', $footContacts['phone'] ?? '+256200903000') }}">{{ $footContacts['phone'] ?? '+256 200 903 000' }}</a>
            @if(!empty($footContacts['whatsapp_link']))
              &middot; <a href="{{ $footContacts['whatsapp_link'] }}" target="_blank" rel="noopener">WhatsApp</a>
            @endif
          </li>
        </ul>

        @if($footSocial)
          <div class="foot-social">
            @foreach($footSocial as $social)
              <a href="{{ $social['url'] }}" target="_blank" rel="noopener" title="{{ $social['name'] }}"
                 aria-label="{{ $social['name'] }}{{ !empty($social['handle']) ? ' — '.$social['handle'] : '' }}">
                <i class="fab {{ $social['icon'] }}" aria-hidden="true"></i>
              </a>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Columns come from SiteNav, so the footer cannot drift away from the
           menu. Three sections get a column each; the rest are reached from
           "More", so no part of the navigation is unreachable from here. --}}
      @foreach(collect($nav)->filter(fn ($i) => !empty($i['children']))->take(3) as $item)
        <div>
          <p class="foot-h">{{ $item['label'] }}</p>
          @foreach($item['children'] as $child)
            <a href="{{ $child['url'] }}" wire:navigate>{{ $child['label'] }}</a>
          @endforeach
        </div>
      @endforeach

      <div>
        <p class="foot-h">More</p>
        <a href="{{ route('scholar.home') }}" wire:navigate>MRU Scholar</a>
        <a href="{{ route('campus-life') }}" wire:navigate>Campus Life</a>
        <a href="{{ route('insights.index') }}" wire:navigate>News</a>
        <a href="{{ route('events.index') }}" wire:navigate>Events</a>
        <a href="{{ route('vacancies.index') }}" wire:navigate>Vacancies</a>
        <a href="{{ route('downloads') }}" wire:navigate>Downloads</a>
        {{-- An employer checking a certificate has no account and no reason to
             guess the URL; the address is printed on the document, but this is
             where somebody looks for it. --}}
        <a href="{{ route('certificates.lookup') }}" wire:navigate>Verify a certificate</a>
      </div>
    </div>

    {{-- One slim strip carrying the two things a footer can offer that a page
         cannot: a way to keep hearing from the university, and the doors for
         people who already belong to it. --}}
    <div class="foot-strip">
      <form method="POST" action="{{ route('newsletter.store') }}" class="foot-news">
        @csrf
        <label for="foot-email">Get university news</label>
        <div class="row">
          <input id="foot-email" type="email" name="email" required placeholder="you@example.com"
                 aria-label="Your email address" value="{{ old('email') }}">
          <button type="submit">Subscribe</button>
        </div>
        <div class="hp-field" aria-hidden="true">
          <label for="foot-{{ \App\Support\Spam\FormShield::HONEYPOT }}">Leave this empty</label>
          <input id="foot-{{ \App\Support\Spam\FormShield::HONEYPOT }}" type="text"
                 name="{{ \App\Support\Spam\FormShield::HONEYPOT }}" tabindex="-1" autocomplete="off">
        </div>
        @if(session('newsletter'))
          <p class="foot-news-msg ok"><i class="fas fa-circle-check" aria-hidden="true"></i> {{ session('newsletter') }}</p>
        @endif
        @error('email')<p class="foot-news-msg bad">{{ $message }}</p>@enderror
      </form>

      <div class="foot-portals">
        <a href="{{ $applyUrl }}" rel="external" class="btn gold sm">Apply Now <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        <a href="{{ $eportalUrl }}" rel="external">Student E-Portal</a>
        <a href="{{ route('courses.index') }}" wire:navigate>e-Learning</a>
        <a href="{{ $uniLinks['eadmin'] ?? 'https://eadmin.mru.ac.ug/' }}" rel="external">Staff Login</a>
      </div>
    </div>

    <div class="foot-bar">
      <span>&copy; {{ date('Y') }} Muteesa I Royal University. All rights reserved.</span>
      <span class="accred"><i class="fas fa-certificate" aria-hidden="true"></i>
        Accredited by the NCHE, Uganda</span>
      <span class="foot-legal">
        <a href="{{ route('privacy') }}" wire:navigate>Privacy</a>
        <a href="{{ route('terms') }}" wire:navigate>Terms</a>
        <a href="{{ route('contact') }}" wire:navigate>Contact</a>
      </span>
    </div>
  </div>
</footer>

<script>
  function initBurgerMenu(){
    var b=document.getElementById('burger'), m=document.getElementById('mmenu');
    if(!b || b.dataset.wired) return;
    b.dataset.wired = '1';
    b.addEventListener('click',function(){ var o=m.classList.toggle('open'); b.setAttribute('aria-expanded',o); b.querySelector('i').className=o?'fas fa-xmark':'fas fa-bars'; document.body.style.overflow=o?'hidden':''; });
  }
  initBurgerMenu();

  /* The compact header.

     Past ~40px of scroll the utility row folds away and the bar tightens, so
     the reader keeps the navigation without giving up a fifth of the screen
     to it. The class goes on <html> so CSS can reach every part of the header
     from one hook, and the scroll listener is passive because it only ever
     reads scroll position. A hysteresis gap (40 down / 10 up) stops the bar
     flickering when a page is resting near the threshold. */
  (function(){
    if (window.__mruScrollWired) return;
    window.__mruScrollWired = true;
    var root = document.documentElement, ticking = false;
    function apply(){
      var y = window.scrollY || window.pageYOffset;
      if (y > 40) root.classList.add('is-scrolled');
      else if (y < 10) root.classList.remove('is-scrolled');
      ticking = false;
    }
    window.addEventListener('scroll', function(){
      if (!ticking) { ticking = true; window.requestAnimationFrame(apply); }
    }, { passive: true });
    apply();
  })();
  // wire:navigate swaps <body> for internal links (pjax-style. No full reload, every
  // page is still a real server-rendered URL, so SEO is unaffected); re-wire the burger
  // button and close any menu left open after each swap.
  /* CSS already opens the mega panel on hover and on focus-within, which
     covers mouse, keyboard and no-JS. Script adds only what CSS cannot say:
     Escape closes, and aria-expanded tells the truth about the panel's state
     for anyone listening rather than looking. */
  /*
     The main menu.

     It has to answer to four different people at once: someone with a mouse
     who expects it to open on hover, someone who expects a click to toggle
     it, someone on a touch screen who has no hover at all, and someone on a
     keyboard. CSS alone cannot do that — a click cannot dismiss a :hover
     state, and clicking the trigger focuses it, so :focus-within pinned the
     panel open and the second click appeared to do nothing. That is why the
     menu felt click-only once it had been clicked.

     So the open state is a class, set here, and the stylesheet only reacts to
     it (see "html.js-nav" in mru.css). Without script the CSS falls back to
     :hover and :focus-within on its own.
  */
  function mruMenuState(item, open){
    item.classList.toggle('is-open', open);
    var trigger = item.querySelector('.nav-link');
    if (trigger) trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    // Queried fresh rather than remembered: wire:navigate replaces the nodes.
    document.documentElement.classList.toggle('menu-open',
      !!document.querySelector('.nav-item.has-menu.is-open'));
  }

  function mruCloseMenus(except){
    document.querySelectorAll('.nav-item.has-menu.is-open').forEach(function(i){
      if (i !== except) mruMenuState(i, false);
    });
  }

  function initMegaMenus(){
    var items = [].slice.call(document.querySelectorAll('.nav-item.has-menu'));
    if (!items.length) return;

    document.documentElement.classList.add('js-nav');   // hands control to the class

    // Touch screens report no hover; they get the click toggle only.
    var canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var openTimer = null, closeTimer = null, viaPointer = false;

    function openMenu(item, now){
      clearTimeout(openTimer); clearTimeout(closeTimer);
      var go = function(){ mruCloseMenus(item); mruMenuState(item, true); };
      // Instant when a panel is already open (moving along the bar should
      // feel like one menu), a beat's hesitation otherwise so sweeping the
      // bar on the way somewhere else does not flash every section.
      if (now) { go(); } else { openTimer = setTimeout(go, 70); }
    }

    function closeSoon(item){
      clearTimeout(openTimer); clearTimeout(closeTimer);
      // A grace period, so clipping a corner on the way into the panel or
      // back to the trigger does not dismiss it.
      closeTimer = setTimeout(function(){ mruMenuState(item, false); }, 180);
    }

    items.forEach(function(item){
      if (item.dataset.wired) return;
      item.dataset.wired = '1';
      var trigger = item.querySelector('.nav-link');

      if (canHover) {
        item.addEventListener('mouseenter', function(){
          openMenu(item, !!document.querySelector('.nav-item.has-menu.is-open'));
        });
        item.addEventListener('mouseleave', function(){ closeSoon(item); });
      }

      if (trigger) {
        /* A click always toggles, and always beats a pending hover timer, so
           the panel can be dismissed without moving the pointer away. The
           flag stops the focus that the click itself causes from re-opening
           what the click just closed. */
        trigger.addEventListener('pointerdown', function(){ viaPointer = true; });
        trigger.addEventListener('click', function(e){
          e.preventDefault();
          clearTimeout(openTimer); clearTimeout(closeTimer);
          var isOpen = item.classList.contains('is-open');
          mruCloseMenus(item);
          mruMenuState(item, !isOpen);
          setTimeout(function(){ viaPointer = false; }, 0);
        });
      }

      /* Keyboard only. A mouse click focuses the trigger too, and that must
         not re-open the panel the click just closed, so this defers to
         :focus-visible — true when focus arrived by keyboard, false after a
         click. */
      item.addEventListener('focusin', function(e){
        if (viaPointer) return;
        var byKeyboard = true;
        try { byKeyboard = e.target.matches(':focus-visible'); } catch (err) { byKeyboard = true; }
        if (byKeyboard) openMenu(item, true);
      });
      item.addEventListener('focusout', function(e){
        if (!item.contains(e.relatedTarget)) mruMenuState(item, false);
      });

      // Following a link closes the menu behind it: wire:navigate swaps the
      // page without a reload, so nothing else would.
      item.addEventListener('click', function(e){
        if (e.target.closest('.mega a')) mruCloseMenus(null);
      });
    });

    if (!window.__mruMenuGlobals) {
      window.__mruMenuGlobals = true;

      document.addEventListener('keydown', function(e){
        if (e.key !== 'Escape') return;
        var open = document.querySelector('.nav-item.has-menu.is-open');
        if (!open) return;
        mruMenuState(open, false);
        var trigger = open.querySelector('.nav-link');
        if (trigger) trigger.focus();
      });

      document.addEventListener('click', function(e){
        if (!e.target.closest('.nav-item.has-menu')) mruCloseMenus(null);
      });
    }
  }
  initMegaMenus();

  /* Reserves room under the fixed chapter bar so it never covers the last
     line of the page. Paired with a :has() rule for anyone whose script does
     not run; the class is what keeps it correct across wire:navigate, where a
     one-shot per-page script would leave the padding behind on the next
     page. */
  /* Two body classes derived from what the page actually contains, rather
     than from a per-page script. Re-run on every wire:navigate, because a
     one-shot script would leave the padding (or a hidden footer) behind on
     the next page. */
  function syncPageChrome(){
    document.body.classList.toggle('has-act-bar', !!document.querySelector('.act-bar'));
    document.body.classList.toggle('about-chapter', !!document.querySelector('.rail'));
  }
  syncPageChrome();

  document.addEventListener('livewire:navigated', function(){
    initBurgerMenu();
    initMegaMenus();
    syncPageChrome();
    var m=document.getElementById('mmenu'), b=document.getElementById('burger');
    if(m && m.classList.contains('open')){ m.classList.remove('open'); document.body.style.overflow=''; if(b){ b.setAttribute('aria-expanded','false'); b.querySelector('i').className='fas fa-bars'; } }
  });

  /* Motion
     Sections rise into place as they're reached, and the hero's numbers
     count up once.

     The hiding is applied by JS (the .js class), never by the stylesheet, so
     the content is visible to anyone whose script fails, whose observer is
     unsupported, or who asked their system for reduced motion. There is no
     state in which this can leave the page blank. */
  (function(){
    var still = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (still.matches || !('IntersectionObserver' in window)) return;

    document.documentElement.classList.add('js');

    var seen = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if (!e.isIntersecting) return;
        e.target.classList.add('in');
        seen.unobserve(e.target);                 // rise once, not on every pass
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });

    /* Counts up to whatever the number already says, so the markup stays the
       single source of truth.

       These numbers are someone's credentials, so the animation is built to be
       incapable of leaving a wrong one on screen. requestAnimationFrame stops
       being delivered in a background tab, in low-power mode, and under some
       automation, and a count-up that stalls mid-flight leaves "9+ years"
       reading "1+ years" permanently. So the true text is restored by a timer
       that does not depend on frames arriving, and again if the tab is hidden
       mid-count. Worst case the number simply appears without counting. */
    function countUp(el){
      // The real value is copied out of the node before anything writes to it,
      // and every restore reads from there. Reading it back off the element
      // would be reading whatever frame the animation happens to be on, which
      // is how a second run once captured "1+" as the true value of "9+" and
      // left it there.
      if (el.dataset.trueValue === undefined) el.dataset.trueValue = el.textContent.trim();
      if (el.dataset.counted) return;             // never animate the same node twice
      el.dataset.counted = '1';

      var text = el.dataset.trueValue;
      var target = parseFloat(text.replace(/[^0-9.]/g, ''));
      if (!isFinite(target) || target === 0) return;

      var suffix = text.replace(/[0-9.,]/g, '');
      var ms = 900, start = null, done = false;

      function settle(){
        if (done) return;
        done = true;
        el.textContent = el.dataset.trueValue;    // the exact original, always
        document.removeEventListener('visibilitychange', onHide);
      }
      function onHide(){ if (document.hidden) settle(); }

      document.addEventListener('visibilitychange', onHide);
      setTimeout(settle, ms + 120);               // independent of rAF delivery

      requestAnimationFrame(function frame(now){
        if (done) return;
        if (start === null) start = now;
        var p = Math.min(1, (now - start) / ms);
        el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString() + suffix;
        if (p < 1) requestAnimationFrame(frame);
        else settle();
      });
    }

    /* Anything still hidden becomes visible, unconditionally. The reveal is
       decoration; the content is the point. Printing, find-in-page and
       deep-link anchors all reach content the observer hasn't got to yet, and
       any failure inside wire() would otherwise leave a page of invisible
       text. */
    function revealAll(){
      document.documentElement.classList.remove('js');
      document.querySelectorAll('[data-rise]').forEach(function(el){ el.classList.add('in'); });
    }
    window.addEventListener('beforeprint', revealAll);

    function wire(){
      document.querySelectorAll('[data-rise]').forEach(function(el, i){
        // Stagger within a group, capped so a long list never crawls.
        if (!el.style.getPropertyValue('--d')) {
          el.style.setProperty('--d', Math.min(i, 6) * 60 + 'ms');
        }
        seen.observe(el);
      });

      /* Anything already on screen is revealed straight away rather than left
         to the observer. The observer's negative bottom margin creates a dead
         band at the foot of the viewport, and on a screen tall enough to show
         the whole page there is no scroll to move anything out of it, so that
         content would stay invisible for good. */
      requestAnimationFrame(function () {
        document.querySelectorAll('[data-rise]:not(.in)').forEach(function (el) {
          if (el.getBoundingClientRect().top < window.innerHeight) el.classList.add('in');
        });
      });

      var stats = document.querySelector('[data-count]');
      if (stats) {
        var once = new IntersectionObserver(function(entries){
          entries.forEach(function(e){
            if (!e.isIntersecting) return;
            e.target.querySelectorAll('.v').forEach(countUp);
            once.disconnect();
          });
        }, { threshold: 0.4 });
        once.observe(stats);
      }
    }

    function safeWire(){
      /* Arriving on a deep link jumps straight past everything above the
         target, and those sections never come back into view for the observer
         to notice, so the visitor lands on a page of invisible text. Anyone
         who followed a link to a specific section gets the whole page revealed
         at once instead. */
      if (location.hash && document.getElementById(location.hash.slice(1))) {
        revealAll();

        return;
      }

      try { wire(); } catch (e) { revealAll(); }   // never trade content for an effect
    }

    safeWire();
    document.addEventListener('livewire:navigated', safeWire);
  })();
</script>
@stack('scripts')
@livewireScripts
<x-whatsapp-launcher />
<x-analytics.beacon />
</body>
</html>
