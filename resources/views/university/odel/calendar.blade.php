@extends('layouts.odel')
@section('title', 'ODEL dates | Muteesa I Royal University')
@section('desc', 'Key dates for open, distance and e-learning students at Muteesa I Royal University, taken from the academic almanac.')

@section('content')

@include('university.odel.partials.hero', [
  'eyebrow' => 'Dates',
  'title' => 'ODEL dates',
  'lead' => 'Taken from the University\'s academic almanac, so these are the same dates the rest of
             the institution works to — not a separate calendar that can drift out of step.',
  'trail' => [['label' => 'Dates']],
])

<section style="padding-top:var(--s-6);">
  <div class="wrap" style="max-width:860px;">
    @if($dates->isNotEmpty())
      <div class="od-list" data-rise>
        @foreach($dates as $d)
          <div class="od-item">
            <p>
              {{ $d->activity }}
              @if($d->ends_on && $d->starts_on && ! $d->ends_on->isSameDay($d->starts_on))
                <span style="color:var(--tx3);">— until {{ $d->ends_on->format('j M Y') }}</span>
              @endif
            </p>
            <span class="od-clause">{{ $d->starts_on?->format('j M Y') }}</span>
          </div>
        @endforeach
      </div>
    @else
      <div class="feature-box" data-rise>
        <div class="sub">Nothing scheduled yet</div>
        <p style="margin:0;">No ODEL dates are currently set in the academic almanac. The full
           University calendar is published in the almanac.</p>
      </div>
    @endif

    <div style="margin-top:var(--s-5);display:flex;gap:12px;flex-wrap:wrap;">
      <a href="{{ route('almanac') }}" wire:navigate class="btn ghost">The full academic almanac <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
    </div>
  </div>
</section>

@endsection
