{{--
  The standard page header. Props via @include:
    $eyebrow  — section word above the title
    $title    — the page's h1
    $lead     — one sentence under it (optional)
    $mark     — oversized watermark word (optional, defaults to eyebrow)
--}}
<section class="page-hero">
  <span class="hero-mark" aria-hidden="true">{{ $mark ?? $eyebrow ?? '' }}</span>
  <div class="wrap">
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
</section>
