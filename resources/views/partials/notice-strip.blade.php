{{--
  The notice strip, above the header.

  Content, not markup: an editor writes a notice, picks a template, sets a
  window, and it appears and retires on its own.

  On accessibility — a strip that moves is exactly what WCAG 2.2.2 is about, so
  the ticker carries a real pause control, stops on hover and on focus, and does
  not move at all under prefers-reduced-motion, where it falls back to a static
  list. The countdown's live figure is rendered server-side so it is correct
  with JavaScript off.
--}}
@php $notices = \App\Support\Notices::live(); @endphp

@if($notices->isNotEmpty())
<div class="ns" id="notice-strip" data-notice-strip>
  @foreach($notices as $notice)
    @php
      $t = $notice->templateName();
      $days = $notice->daysLeft();
      $external = $notice->linkIsExternal();
    @endphp

    <section class="ns-item ns-{{ $t }}"
             data-notice
             data-key="{{ $notice->dismissKey() }}"
             aria-label="Announcement">

      {{-- The ticker repeats its content so the scroll can loop seamlessly;
           the copy is hidden from assistive tech so it is not read twice. --}}
      @if($t === 'ticker')
        <div class="ns-track" data-ns-track>
          <div class="ns-run">
            @include('partials.notice-body', ['notice' => $notice, 'days' => $days, 'external' => $external])
          </div>
          <div class="ns-run" aria-hidden="true">
            @include('partials.notice-body', ['notice' => $notice, 'days' => $days, 'external' => $external])
          </div>
        </div>
        <button type="button" class="ns-pause" data-ns-pause
                aria-label="Pause the scrolling announcement" aria-pressed="false">
          <i class="fas fa-pause" aria-hidden="true"></i>
        </button>
      @else
        <div class="ns-static">
          @include('partials.notice-body', ['notice' => $notice, 'days' => $days, 'external' => $external])
        </div>
      @endif

      @if($notice->is_dismissible)
        <button type="button" class="ns-x" data-ns-dismiss aria-label="Dismiss this announcement">
          <i class="fas fa-xmark" aria-hidden="true"></i>
        </button>
      @endif
    </section>
  @endforeach
</div>
@endif
