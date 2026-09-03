@php
  /**
   * The full-height home slider.
   *
   * Content comes from the `university.hero_slides` setting, so the slides are
   * editable data rather than markup. Each slide names a base image path and
   * the view builds the responsive set from it (-700 / -1100 / -1600).
   *
   * The first slide is the page's LCP: it loads eagerly at high priority and
   * is the only one that does. The rest are lazy.
   */
  $slides = collect(\App\Support\University::get('hero_slides'))
      ->filter(fn ($s) => filled($s['title'] ?? null))
      ->values();

  $link = function (?array $cta) {
      if (! $cta || blank($cta['label'] ?? null)) {
          return null;
      }
      $url = ! empty($cta['route']) && \Illuminate\Support\Facades\Route::has($cta['route'])
          ? route($cta['route'])
          : ($cta['url'] ?? null);

      return $url ? ['label' => $cta['label'], 'url' => $url, 'external' => (bool) ($cta['external'] ?? false)] : null;
  };
@endphp

@if($slides->isNotEmpty())
<section class="hero-slider" data-hero-slider
         role="region" aria-roledescription="carousel"
         aria-label="Muteesa I Royal University highlights">

  @foreach($slides as $i => $slide)
    @php
      $img = $slide['image'] ?? null;
      $primary = $link($slide['primary'] ?? null);
      $secondary = $link($slide['secondary'] ?? null);
    @endphp
    <article class="hs-slide{{ $i === 0 ? ' is-active' : '' }}" data-hs-slide
             role="group" aria-roledescription="slide"
             aria-label="{{ $i + 1 }} of {{ $slides->count() }}"
             @if($i > 0) aria-hidden="true" inert @endif>

      @if($img)
        <img class="hs-media"
             src="{{ asset('storage/'.$img.'-1600.jpg') }}"
             srcset="{{ asset('storage/'.$img.'-700.jpg') }} 700w,
                     {{ asset('storage/'.$img.'-1100.jpg') }} 1100w,
                     {{ asset('storage/'.$img.'-1600.jpg') }} 1600w"
             sizes="100vw"
             alt="{{ $slide['alt'] ?? '' }}"
             width="1600" height="1067"
             loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
             fetchpriority="{{ $i === 0 ? 'high' : 'low' }}"
             decoding="{{ $i === 0 ? 'sync' : 'async' }}">
      @endif

      <span class="hs-scrim" aria-hidden="true"></span>

      <div class="hs-copy">
        <div class="wrap">
          @if(!empty($slide['eyebrow']))
            <p class="hs-eyebrow">{{ $slide['eyebrow'] }}</p>
          @endif
          <h1 class="hs-title">{{ $slide['title'] }}</h1>
          @if(!empty($slide['text']))
            <p class="hs-text">{{ $slide['text'] }}</p>
          @endif
          @if($primary || $secondary)
            <div class="hs-actions">
              @if($primary)
                <a href="{{ $primary['url'] }}"
                   @if($primary['external']) rel="external" @else wire:navigate @endif
                   class="btn gold lg">{{ $primary['label'] }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
              @endif
              @if($secondary)
                <a href="{{ $secondary['url'] }}"
                   @if($secondary['external']) rel="external" @else wire:navigate @endif
                   class="btn ghost lg hs-ghost">{{ $secondary['label'] }}</a>
              @endif
            </div>
          @endif
        </div>
      </div>
    </article>
  @endforeach

  @if($slides->count() > 1)
    {{-- Controls share one bar at the foot of the stage. The arrows used to
         sit at mid-height, where the left one landed on top of the headline
         the slide is trying to show. --}}
    <div class="hs-bar">
      <div class="wrap hs-bar-inner">
        <div class="hs-dots" role="tablist" aria-label="Choose a slide">
          @foreach($slides as $i => $slide)
            <button type="button" class="hs-dot{{ $i === 0 ? ' is-active' : '' }}" data-hs-dot
                    role="tab" aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                    aria-label="{{ \Illuminate\Support\Str::limit($slide['title'], 40) }}">
              <span aria-hidden="true"></span>
            </button>
          @endforeach
        </div>

        <div class="hs-arrows">
          <button type="button" class="hs-arrow" data-hs-prev aria-label="Previous slide">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
          </button>
          <button type="button" class="hs-arrow" data-hs-next aria-label="Next slide">
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
          </button>
        </div>

        {{-- Announced to assistive tech as slides change; visually hidden. --}}
        <p class="sr-only" aria-live="polite" data-hs-live></p>
      </div>
    </div>
  @endif

  <a class="hs-scroll" href="#main-content" aria-label="Skip to the page content">
    <i class="fas fa-chevron-down" aria-hidden="true"></i>
  </a>
</section>
@endif
