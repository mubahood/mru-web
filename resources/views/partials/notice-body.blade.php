{{-- One notice's content. Shared by every template, and rendered twice by the
     ticker, so it must stay free of anything with an id. --}}
@if($notice->icon)
  <i class="fas {{ $notice->icon }} ns-icon" aria-hidden="true"></i>
@endif

@if($notice->label)
  <span class="ns-tag">{{ $notice->label }}</span>
@endif

<span class="ns-msg">{{ $notice->message }}</span>

@if($days !== null)
  {{-- Rendered on the server so it is right without JavaScript; the script
       refreshes it past midnight for anyone who leaves a tab open. --}}
  <span class="ns-count" data-ns-deadline="{{ $notice->deadline_at->toIso8601String() }}">
    <i class="fas fa-hourglass-half" aria-hidden="true"></i>
    @if($days === 0) closes today
    @elseif($days === 1) 1 day left
    @else {{ $days }} days left
    @endif
  </span>
@endif

@if($notice->hasLink())
  <a class="ns-go" href="{{ $notice->link_url }}"
     @if($external) target="_blank" rel="noopener" @else wire:navigate @endif>
    {{ $notice->link_label }}
    <i class="fas {{ $external ? 'fa-arrow-up-right-from-square' : 'fa-arrow-right' }}" aria-hidden="true"></i>
  </a>
@endif
