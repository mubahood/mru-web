{{--
  The standard page header. Props via @include:
    $eyebrow  — section word above the title
    $title    — the page's h1
    $lead     — one sentence under it (optional)
    $mark     — oversized watermark word (optional, defaults to eyebrow)
    $chips    — trust chips: [[icon, label], …] (optional)
    $photo    — asset() path to a real photograph (optional)
    $photoAlt — its alt text; pass '' when the photo is purely decorative

  With $photo the header becomes a two-column band: words left, picture
  right. Without it the layout is untouched, so the 20-odd pages that have
  no photograph worth showing are unaffected rather than being given a
  placeholder — the whole point is that every picture on this site is a real
  one.
--}}
@php $hasPhoto = ! empty($photo ?? null); @endphp
<section class="page-hero{{ $hasPhoto ? ' has-photo' : '' }}">
  <span class="hero-mark" aria-hidden="true">{{ $mark ?? $eyebrow ?? '' }}</span>
  <div class="wrap">
    <div class="ph-inner">
      <div class="ph-copy">
        @isset($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endisset
        <h1>{{ $title }}</h1>
        @isset($lead)<p>{{ $lead }}</p>@endisset
        @isset($chips)
          <div class="trust-chips">
            @foreach($chips as $chip)
              <span><i class="fas {{ $chip[0] }}" aria-hidden="true"></i> {{ $chip[1] }}</span>
            @endforeach
          </div>
        @endisset
      </div>
      @if($hasPhoto)
        <div class="ph-media">
          <img src="{{ $photo }}" alt="{{ $photoAlt ?? '' }}"
               width="880" height="660" loading="eager" fetchpriority="high" decoding="async">
        </div>
      @endif
    </div>
  </div>
</section>
