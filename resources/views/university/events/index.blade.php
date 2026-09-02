@extends('layouts.marketing')
@section('title', 'University Events | Muteesa I Royal University')
@section('desc', 'What is happening at Muteesa I Royal University — upcoming talks, exhibitions, ceremonies and student events across the Kampala and Masaka campuses.')

@section('content')

@php
  $n = 0;
  $idx = function () use (&$n) { return str_pad((string) ++$n, 2, '0', STR_PAD_LEFT); };
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Campus Life',
  'title' => 'University Events',
  'lead' => 'Talks, exhibitions, ceremonies and student life across both campuses — see what is on and come along.',
  'chips' => [
    ['fa-calendar-days', $upcoming->count().' upcoming'],
    ['fa-location-dot', 'Kampala & Masaka campuses'],
  ],
])

<section>
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Coming Up</span></div>
      <h2>Upcoming events</h2>
    </div>
    @forelse($upcoming as $event)
      <div class="feature-box event-row" data-rise style="display:grid;grid-template-columns:190px 1fr;gap:20px;align-items:start;margin-bottom:14px;">
        <div style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;font-weight:700;color:var(--gold-d);line-height:1.5;">
          {{ $event->starts_at?->format('D, j M Y · g:i A') }}
        </div>
        <div>
          <h3 style="margin-bottom:4px;">
            <a href="{{ route('events.show', $event) }}" wire:navigate style="color:var(--tx);">{{ $event->title }}</a>
          </h3>
          @if($event->venue || $event->campus)
            <p style="font-size:12.5px;color:var(--tx3);margin-bottom:8px;">
              <i class="fas fa-location-dot" aria-hidden="true" style="color:var(--gold-d);"></i>
              {{ collect([$event->venue, $event->campus])->filter()->implode(' · ') }}
            </p>
          @endif
          @if($event->excerpt)
            <p style="margin-bottom:8px;">{{ \Illuminate\Support\Str::limit($event->excerpt, 180) }}</p>
          @endif
          <a href="{{ route('events.show', $event) }}" wire:navigate class="link" style="color:var(--pri);font-weight:600;font-size:13px;">
            Event details <i class="fas fa-arrow-right" aria-hidden="true"></i>
          </a>
        </div>
      </div>
    @empty
      <div class="feature-box" data-rise style="text-align:center;">
        <p style="margin-bottom:0;">
          Nothing is scheduled right now. New events are announced here — follow {{ '@MRU_Uganda' }} to hear first.
        </p>
      </div>
    @endforelse
  </div>
</section>
@push('styles')<style>@media(max-width:640px){.event-row{grid-template-columns:1fr !important;gap:8px !important;}}</style>@endpush

@if($past->isNotEmpty())
<section class="band-surface tex-glow">
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Archive</span></div>
      <h2>Past events</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
      @foreach($past as $event)
        <a href="{{ route('events.show', $event) }}" wire:navigate class="card" data-rise>
          <p style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11px;font-weight:700;color:var(--tx3);margin-bottom:6px;">
            {{ $event->starts_at?->format('j M Y') }}
          </p>
          <h3>{{ $event->title }}</h3>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif

@include('university.partials.cta-band')

@endsection
