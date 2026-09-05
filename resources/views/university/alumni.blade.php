@extends('layouts.marketing')
@section('title', 'Alumni | Muteesa I Royal University')
@section('desc', 'Graduates of Muteesa I Royal University join a growing alumni network — networking, mentorship, events and reunions, and a representative on the University Council.')

@section('content')

@php
  $social = \App\Support\University::get('social');
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Alumni',
  'title' => 'Once a Royal, always a Royal',
  'lead' => 'Graduates of MRU join a growing alumni network that stretches across Uganda and beyond — and keeps a seat at the University\'s table.',
  'photo' => asset('storage/university/hero/hero-graduation-1100.jpg'),
  'photoAlt' => 'Graduands seated at a Muteesa I Royal University graduation ceremony',
])

<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">The Network</p>
      <h2>What the network gives you</h2>
      <p>An alumni representative sits on the University Council, so graduates keep a voice in how the University is governed.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-handshake" aria-hidden="true"></i></span>
        <h3>Networking</h3>
        <p>Stay connected to classmates and meet fellow graduates working across every field the University teaches.</p>
      </div>
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-user-graduate" aria-hidden="true"></i></span>
        <h3>Mentorship</h3>
        <p>Guide current students into the careers you have built — or find a mentor a few steps ahead of you.</p>
      </div>
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-calendar-days" aria-hidden="true"></i></span>
        <h3>Events &amp; reunions</h3>
        <p>Come back to campus for university events, graduation ceremonies and alumni reunions.</p>
      </div>
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-hand-holding-heart" aria-hidden="true"></i></span>
        <h3>Giving back</h3>
        <p>Support the students coming after you — through time, expertise or contributions to university initiatives.</p>
      </div>
    </div>
  </div>
</section>

<section class="band-surface tex-glow">
  <div class="wrap">
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
      <div class="feature-box" data-rise>
        <div class="sub"><i class="fas fa-envelope" aria-hidden="true"></i> Stay connected</div>
        <h3>Keep your details current</h3>
        <p>Write to the University at
          <a href="mailto:{{ $contacts['email'] ?? 'info@mru.ac.ug' }}" class="link" style="color:var(--pri);font-weight:600;">{{ $contacts['email'] ?? 'info@mru.ac.ug' }}</a>
          to stay on the alumni mailing list — and follow the University on social media for news, events and reunion announcements.</p>
        @if(!empty($social))
          <div class="pill-row">
            @foreach($social as $s)
              <a href="{{ $s['url'] }}" target="_blank" rel="noopener" class="pill" aria-label="MRU on {{ $s['name'] }}">
                <i class="fab {{ $s['icon'] }}" aria-hidden="true"></i>&nbsp;{{ $s['name'] }}
              </a>
            @endforeach
          </div>
        @endif
      </div>
      <div class="feature-box" data-rise>
        <div class="sub"><i class="fas fa-file-pdf" aria-hidden="true"></i> Documents</div>
        <h3>Alumni documents</h3>
        <p>Official university documents — including alumni resources such as the Alumni Constitution —
           are published on the Downloads page as they become available.</p>
        <a href="{{ route('downloads') }}" wire:navigate class="btn ghost">
          Downloads <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
      </div>
    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
