@extends('layouts.marketing')
@section('title', 'About MRU | Muteesa I Royal University')
@section('desc', 'The story, vision, mission and leadership of Muteesa I Royal University — an NCHE-accredited private university of the Buganda Kingdom, serving students at Kakeeka Campus in Kampala and Kirumba Campus in Masaka.')

@section('content')

@php
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'About MRU',
  'title' => 'A royal university with a modern mission',
  'lead' => $identity['motto'] ?? 'Seeking Greater Horizons in Thought and Action',
  'chips' => [
    ['fa-certificate', 'NCHE Accredited'],
    ['fa-crown', 'A university of the Buganda Kingdom'],
    ['fa-location-dot', 'Kampala & Masaka'],
  ],
  'photo' => asset('images/page-about.jpg'),
  'photoAlt' => "A speaker at the Buganda Partnership Symposium holding the Kingdom's Social Transformation booklet",
])

{{-- The founding story, told beside the crest it produced. --}}
<section class="band-surface tex-glow">
  <div class="wrap">
    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:40px;align-items:center;" class="about-split">
      <div>
        <div class="sec-head left" style="margin-bottom:18px;">
          <p class="eyebrow">Our History</p>
          <h2>{{ $identity['namesake'] ?? 'Named for Kabaka Muteesa I of Buganda' }}</h2>
        </div>
        @foreach($identity['history'] ?? [] as $paragraph)
          <p class="lead" data-rise style="margin-bottom:14px;">{{ $paragraph }}</p>
        @endforeach
      </div>
      <div data-rise style="text-align:center;">
        <img src="{{ asset('images/logo-icon.png') }}" alt="The crest of Muteesa I Royal University"
             loading="lazy" decoding="async" style="max-width:250px;margin:0 auto;">
      </div>
    </div>
  </div>
</section>
@push('styles')<style>@media(max-width:820px){.about-split{grid-template-columns:1fr !important;}}</style>@endpush

@if($stats)
<section style="padding:34px 0;">
  <div class="wrap">
    <div class="stat-row" data-rise style="margin-top:0;">
      @foreach($stats as $stat)
        <div class="stat"><div class="v">{{ $stat['value'] }}</div><div class="l">{{ $stat['label'] }}</div></div>
      @endforeach
    </div>
  </div>
</section>
@endif

<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Vision &amp; Mission</p>
      <h2>Where we are going, and how we get there</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
      <div class="feature-box" data-rise>
        <div class="sub"><i class="fas fa-eye" aria-hidden="true"></i> Our Vision</div>
        <h3>A premier African university of choice</h3>
        <p style="margin-bottom:0;">{{ $identity['vision'] ?? '' }}</p>
      </div>
      <div class="feature-box" data-rise>
        <div class="sub"><i class="fas fa-bullseye" aria-hidden="true"></i> Our Mission</div>
        <h3>Quality, career-focused education</h3>
        <p style="margin-bottom:0;">{{ $identity['mission'] ?? '' }}</p>
      </div>
    </div>
    <div data-rise style="margin-top:20px;">
      <a href="{{ route('who-we-are') }}" wire:navigate class="btn ghost">
        Who we are <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
    </div>
  </div>
</section>

@if($leadership->isNotEmpty())
<section class="band-surface tex-grid">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Leadership</p>
      <h2>University leadership</h2>
      <p>The principal officers responsible for the University's academic and administrative life.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
      @foreach($leadership as $member)
        @include('university.partials.person-card', ['person' => $member])
      @endforeach
    </div>
    <div style="text-align:center;margin-top:26px;" data-rise>
      <a href="{{ route('governance') }}" wire:navigate class="btn ghost">
        University governance <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
    </div>
  </div>
</section>
@endif

<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Campuses</p>
      <h2>Two campuses, one university</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-building-columns" aria-hidden="true"></i></span>
        <h3>Kakeeka Campus</h3>
        <p><i class="fas fa-location-dot" aria-hidden="true"></i> Mengo, Kampala</p>
        <p style="margin-top:6px;">Our Kampala home in the heart of Mengo, minutes from the centre of the capital.</p>
      </div>
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-building-columns" aria-hidden="true"></i></span>
        <h3>Kirumba Campus</h3>
        <p><i class="fas fa-location-dot" aria-hidden="true"></i> Kirumba, Masaka</p>
        <p style="margin-top:6px;">Our Masaka campus, serving students across the greater Masaka region.</p>
      </div>
    </div>
    <p data-rise style="margin-top:16px;font-size:13px;color:var(--tx2);">
      Directions and campus contacts are on the
      <a href="{{ route('contact') }}" wire:navigate class="link" style="color:var(--pri);font-weight:600;">contact page <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
    </p>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
