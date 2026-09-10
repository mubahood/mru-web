<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <x-seo
    :title="trim($__env->yieldContent('title', 'ODEL — Open, Distance and E-Learning | Muteesa I Royal University'))"
    :description="trim($__env->yieldContent('desc', 'Study with Muteesa I Royal University without giving up your job or your family. Six flexible study modes, set out in the University\'s own Council-approved policies.'))"
    :image="trim($__env->yieldContent('og_image', '')) ?: null"
  >@stack('jsonld')</x-seo>
  <link rel="preload" as="font" type="font/woff2" href="{{ asset('vendor/fonts/jakarta/jakarta-var-normal.woff2') }}" crossorigin>
  <link rel="preload" as="font" type="font/woff2" href="{{ asset('vendor/fonts/fraunces/fraunces-var-normal.woff2') }}" crossorigin>
  <link rel="stylesheet" href="{{ asset('vendor/fonts/jakarta/jakarta.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/fonts/fraunces/fraunces.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/fa/css/all.min.css') }}" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="{{ asset('vendor/fa/css/all.min.css') }}"></noscript>
  {{-- The section inherits the site's tokens and components, then overrides
       its own chrome. Two files, not a fork: a change to the design system
       still reaches ODEL. --}}
  <link rel="stylesheet" href="{{ \App\Support\Asset::versioned('css/mru.css') }}">
  <link rel="stylesheet" href="{{ \App\Support\Asset::versioned('css/odel.css') }}">
  <link rel="stylesheet" href="{{ \App\Support\Asset::versioned('css/notices.css') }}">
  <x-analytics.google />
  @stack('styles')
</head>
<body class="odel">

<a href="#main-content" class="skip">Skip to content</a>

{{-- A thin line back to the University proper. ODEL is a section of MRU, not a
     separate institution, and a reader who lands here from a search must be
     able to tell that at a glance and get back. --}}
@include('partials.notice-strip')

<div class="od-tie">
  <div class="wrap">
    <a href="{{ route('home') }}" wire:navigate class="od-tie-back">
      <i class="fas fa-arrow-left" aria-hidden="true"></i> Muteesa I Royal University
    </a>
    <span class="od-tie-links">
      <a href="{{ route('programmes.index') }}" wire:navigate>All programmes</a>
      <a href="{{ route('admissions.index') }}" wire:navigate>Admissions</a>
      <a href="{{ \App\Support\University::links()['elearning'] ?? 'https://elearning.mru.ac.ug/' }}" rel="external">e-Learning portal</a>
    </span>
  </div>
</div>

<header class="od-head">
  <div class="wrap od-head-in">
    <a href="{{ route('odel.index') }}" wire:navigate class="od-brand">
      <img src="{{ asset('images/logo-icon.png') }}" alt="" width="38" height="38" aria-hidden="true">
      <span>
        <b>ODEL</b>
        <em>Open, Distance &amp; E-Learning</em>
      </span>
    </a>

    <nav class="od-nav" aria-label="ODEL sections">
      @foreach(\App\Support\OdelNav::items() as $item)
        <a href="{{ $item['url'] }}" wire:navigate
           class="{{ \App\Support\OdelNav::isOn($item) ? 'on' : '' }}"
           @if(\App\Support\OdelNav::isOn($item)) aria-current="page" @endif>{{ $item['label'] }}</a>
      @endforeach
    </nav>

    <a href="{{ route('odel.apply') }}" wire:navigate class="btn gold sm od-cta">Start here</a>

    <button class="od-burger" id="od-burger" aria-label="ODEL menu" aria-expanded="false" aria-controls="od-sheet">
      <i class="fas fa-bars" aria-hidden="true"></i>
    </button>
  </div>

  {{-- The mobile sheet is the same list, not a second source of truth. --}}
  <div class="od-sheet" id="od-sheet" hidden>
    @foreach(\App\Support\OdelNav::items() as $item)
      <a href="{{ $item['url'] }}" wire:navigate class="{{ \App\Support\OdelNav::isOn($item) ? 'on' : '' }}">{{ $item['label'] }}</a>
    @endforeach
    <a href="{{ route('odel.apply') }}" wire:navigate class="btn gold">Start here</a>
  </div>
</header>

<main id="main-content">
  @yield('content')
</main>

<footer class="od-foot">
  <div class="wrap">
    <div class="od-foot-top">
      <div>
        <p class="od-foot-h">ODEL at Muteesa I Royal University</p>
        <p>Open, Distance and E-Learning is governed by two policies approved by the
           University Council in {{ \App\Support\Odel::APPROVED }} and commenced in
           {{ \App\Support\Odel::COMMENCED }}. Everything in this section is drawn from them,
           and cites the clause it came from.</p>
        <div class="od-foot-docs">
          @foreach(\App\Support\Odel::policies() as $doc)
            <a href="{{ asset('storage/'.$doc['file']) }}" target="_blank" rel="noopener">
              <i class="fas fa-file-pdf" aria-hidden="true"></i> {{ $doc['title'] }} <span>{{ $doc['pages'] }}</span>
            </a>
          @endforeach
        </div>
      </div>
      <div class="od-foot-cols">
        @foreach(\App\Support\OdelNav::columns() as $col)
          <div>
            <p class="od-foot-h">{{ $col['label'] }}</p>
            @foreach($col['links'] as $l)
              <a href="{{ $l['url'] }}" wire:navigate>{{ $l['label'] }}</a>
            @endforeach
          </div>
        @endforeach
      </div>
    </div>

    <div class="od-foot-bar">
      <span>&copy; {{ date('Y') }} Muteesa I Royal University</span>
      <span class="od-foot-legal">
        <a href="{{ route('home') }}" wire:navigate>Main site</a>
        <a href="{{ route('contact') }}" wire:navigate>Contact</a>
        <a href="{{ route('downloads') }}" wire:navigate>Downloads</a>
        <a href="{{ route('privacy') }}" wire:navigate>Privacy</a>
      </span>
    </div>
  </div>
</footer>

<script>
  (function () {
    var b = document.getElementById('od-burger'), s = document.getElementById('od-sheet');
    if (!b || !s) return;
    b.addEventListener('click', function () {
      var open = s.hasAttribute('hidden');
      if (open) { s.removeAttribute('hidden'); } else { s.setAttribute('hidden', ''); }
      b.setAttribute('aria-expanded', open ? 'true' : 'false');
      b.querySelector('i').className = open ? 'fas fa-xmark' : 'fas fa-bars';
    });
    // A tap on a link inside the sheet should close it; wire:navigate swaps the
    // body without a reload, so nothing else would.
    s.addEventListener('click', function (e) {
      if (e.target.closest('a')) { s.setAttribute('hidden', ''); b.setAttribute('aria-expanded', 'false'); b.querySelector('i').className = 'fas fa-bars'; }
    });
  })();
</script>
@include('partials.whatsapp-gate')
<script src="{{ \App\Support\Asset::versioned('js/whatsapp-gate.js') }}" defer></script>
<script src="{{ \App\Support\Asset::versioned('js/notices.js') }}" defer></script>
<script src="{{ \App\Support\Asset::versioned('js/consent.js') }}" defer></script>
@stack('scripts')
@include('partials.consent-bar')
</body>
</html>
