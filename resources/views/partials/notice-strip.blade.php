{{--
  The notice strip, above the header.

  Content, not markup: an editor writes a notice, picks a template, sets a
  window, and it appears and retires on its own.

  Ticker notices share ONE lane, the way a real news ticker does — several
  items in a single moving band, separated by a divider. Giving each its own
  row would stack 32px strips until the strip is taller than the header it sits
  above. Anything that is not a ticker keeps its own row, because a banner or an
  urgent notice is a statement, not an item in a queue.

  On accessibility — a strip that moves is exactly what WCAG 2.2.2 is about, so
  the lane carries a real pause control, stops on hover and on focus, and does
  not move at all under prefers-reduced-motion, where it falls back to a static
  list. The countdown's figure is rendered server-side so it is correct with
  JavaScript off.
--}}
@php
  $lane = \App\Support\Notices::ticker();
  $rows = \App\Support\Notices::rows();
@endphp

@if($lane->isNotEmpty() || $rows->isNotEmpty())
<div class="ns" id="notice-strip" data-notice-strip>

  @if($lane->isNotEmpty())
    <section class="ns-item ns-ticker" data-notice
             data-key="{{ \App\Support\Notices::tickerKey() }}"
             aria-label="{{ $lane->count() === 1 ? 'Announcement' : 'Announcements' }}">
      {{-- Two identical runs so the loop has no seam; the second is hidden
           from assistive tech so nothing is announced twice. --}}
      <div class="ns-track" data-ns-track>
        @foreach([false, true] as $isCopy)
          <div class="ns-run" @if($isCopy) aria-hidden="true" @endif>
            @foreach($lane as $notice)
              @if(! $loop->first)
                <span class="ns-sep" aria-hidden="true"></span>
              @endif
              @include('partials.notice-body', [
                'notice' => $notice,
                'days' => $notice->daysLeft(),
                'external' => $notice->linkIsExternal(),
              ])
            @endforeach
          </div>
        @endforeach
      </div>

      <button type="button" class="ns-pause" data-ns-pause
              aria-label="Pause the scrolling announcements" aria-pressed="false">
        <i class="fas fa-pause" aria-hidden="true"></i>
      </button>

      @if(\App\Support\Notices::tickerDismissible())
        <button type="button" class="ns-x" data-ns-dismiss aria-label="Dismiss the announcements">
          <i class="fas fa-xmark" aria-hidden="true"></i>
        </button>
      @endif
    </section>
  @endif

  @foreach($rows as $notice)
    <section class="ns-item ns-{{ $notice->templateName() }}" data-notice
             data-key="{{ $notice->dismissKey() }}" aria-label="Announcement">
      <div class="ns-static">
        @include('partials.notice-body', [
          'notice' => $notice,
          'days' => $notice->daysLeft(),
          'external' => $notice->linkIsExternal(),
        ])
      </div>
      @if($notice->is_dismissible)
        <button type="button" class="ns-x" data-ns-dismiss aria-label="Dismiss this announcement">
          <i class="fas fa-xmark" aria-hidden="true"></i>
        </button>
      @endif
    </section>
  @endforeach

</div>
@endif
