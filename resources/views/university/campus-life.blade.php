@extends('layouts.marketing')
@section('title', 'Campus Life | Muteesa I Royal University')
@section('desc', 'Life at Muteesa I Royal University: learning, sports, culture, events and community across the Kakeeka (Kampala) and Kirumba (Masaka) campuses.')

@section('content')

@php
  $n = 0;
  $idx = function () use (&$n) { return str_pad((string) ++$n, 2, '0', STR_PAD_LEFT); };
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Campus Life',
  'title' => 'Campus Life',
  'lead' => 'Learning, sports, culture, events and community — on two campuses.',
])

{{-- The five pillars of student life. Two of them have their own pages. --}}
<section style="padding-top:28px;">
  <div class="wrap">
    <div class="icon-row">
      <a data-rise>
        <span class="ic"><i class="fas fa-book-open" aria-hidden="true"></i></span>
        <span>Learning</span>
      </a>
      <a href="{{ route('sports') }}" wire:navigate data-rise>
        <span class="ic"><i class="fas fa-futbol" aria-hidden="true"></i></span>
        <span>Sports</span>
      </a>
      <a data-rise>
        <span class="ic"><i class="fas fa-drum" aria-hidden="true"></i></span>
        <span>Culture</span>
      </a>
      <a href="{{ route('events.index') }}" wire:navigate data-rise>
        <span class="ic"><i class="fas fa-calendar-days" aria-hidden="true"></i></span>
        <span>Events</span>
      </a>
      <a data-rise>
        <span class="ic"><i class="fas fa-people-group" aria-hidden="true"></i></span>
        <span>Community</span>
      </a>
    </div>
  </div>
</section>

@if(!empty($sports))
<section class="band-surface tex-glow">
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Sports</span></div>
      <h2>Play for the Royals</h2>
      <p>From the football pitch to the chessboard — varsity teams, inter-faculty leagues and the annual sports gala.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
      @foreach(array_slice($sports, 0, 3) as $sport)
        <div class="card" data-rise>
          <span class="ic"><i class="fas {{ $sport['icon'] ?? 'fa-medal' }}" aria-hidden="true"></i></span>
          <h3>{{ $sport['title'] }}</h3>
          <p>{{ $sport['description'] }}</p>
        </div>
      @endforeach
    </div>
    <div style="text-align:center;margin-top:26px;" data-rise>
      <a href="{{ route('sports') }}" wire:navigate class="btn ghost cta">
        <span class="cta-a">All sports</span>
        <span class="cta-b" aria-hidden="true">Six disciplines, one spirit <i class="fas fa-arrow-right"></i></span>
      </a>
    </div>
  </div>
</section>
@endif

<section>
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>On Campus</span></div>
      <h2>What's happening, and where you'll live</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));align-items:stretch;">
      @if($events->isNotEmpty())
        <div class="feature-box" data-rise style="height:100%;">
          <div class="sub">Upcoming events</div>
          @foreach($events as $event)
            <div style="padding:10px 0;border-bottom:1px solid var(--line);">
              <div style="font-family:ui-monospace,Menlo,monospace;font-size:11px;color:var(--gold-d);font-weight:700;">
                {{ $event->starts_at->format('D, j M Y · g:i A') }}
              </div>
              <a href="{{ route('events.show', $event) }}" wire:navigate style="font-weight:600;font-size:14px;color:var(--tx);">{{ $event->title }}</a>
              @if($event->venue)<div style="font-size:12px;color:var(--tx2);"><i class="fas fa-location-dot" aria-hidden="true"></i> {{ $event->venue }}</div>@endif
            </div>
          @endforeach
          <div style="margin-top:14px;">
            <a href="{{ route('events.index') }}" wire:navigate class="link" style="color:var(--pri);font-weight:600;">All events <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
          </div>
        </div>
      @endif
      <a href="{{ route('accommodation') }}" wire:navigate class="proj-card" data-rise>
        <span class="client">Accommodation</span>
        <h3>Halls of residence</h3>
        <p>Kabaka Hall, Princess Hall and the Graduate Residence — plus private hostels near both campuses.
           See who each hall serves, bed capacity and semester pricing.</p>
        <p style="font-size:12.5px;color:var(--tx2);"><i class="fas fa-envelope" aria-hidden="true"></i>
          Accommodation office: {{ $contacts['accommodation_email'] ?? 'accommodation@mru.ac.ug' }}</p>
        <span class="link">Student accommodation <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
    </div>
  </div>
</section>

@if($gallery->isNotEmpty())
<section class="band-surface tex-grid">
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Gallery</span></div>
      <h2>Campus in pictures</h2>
    </div>
    <div class="gal-grid">
      @foreach($gallery as $i => $photo)
        <div class="gal-item" data-rise style="aspect-ratio:{{ $photo->ratio() }};">
          <picture>
            @if($photo->webpUrl())<source srcset="{{ $photo->webpUrl() }}" type="image/webp">@endif
            <img src="{{ $photo->thumbUrl() }}" alt="{{ $photo->altText() }}"
                 loading="lazy" decoding="async">
          </picture>
          <span class="gal-cap">
            <span class="gal-t">{{ $photo->title }}</span>
            @if($photo->category)<span class="gal-c">{{ $photo->category }}</span>@endif
          </span>
        </div>
      @endforeach
    </div>
    <div style="text-align:center;margin-top:26px;" data-rise>
      <a href="{{ route('gallery.index') }}" wire:navigate class="btn ghost cta">
        <span class="cta-a">Full gallery</span>
        <span class="cta-b" aria-hidden="true">See campus life in pictures <i class="fas fa-arrow-right"></i></span>
      </a>
    </div>
  </div>
</section>
@endif

@include('university.partials.cta-band')

@endsection
