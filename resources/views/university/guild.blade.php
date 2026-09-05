@extends('layouts.marketing')
@section('title', "The Students' Guild | Muteesa I Royal University")
@section('desc', "The Students' Guild is the elected student government of Muteesa I Royal University — meet the Guild cabinet and the portfolios its members hold.")

@section('content')

@php
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Student Life',
  'title' => "The Students' Guild",
  'lead' => 'Your elected student government — the voice of every MRU student.',
  'mark' => 'Guild',
  'photo' => asset('images/photos/hero-guild.jpg'),
  'photoAlt' => 'An incoming Guild office-holder receiving his instrument of office at the swearing-in ceremony',
  'trail' => [['label' => 'Student Life', 'url' => route('campus-life')], ['label' => 'Students\' Guild']],
])

<section style="padding-bottom:0;">
  <div class="wrap">
    <p class="lead" data-rise style="max-width:720px;">
      The Guild represents students in University governance — including a seat on the University
      Council — and runs student welfare, academic advocacy, sports, publicity and campus projects
      through its elected cabinet.
    </p>
  </div>
</section>

@if($cabinet->isNotEmpty())
<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Guild Cabinet</p>
      <h2>The Guild cabinet</h2>
      <p>Each office-holder carries a portfolio — this is who to talk to, and about what.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
      {{-- The shared person-card omits the bio, which on this page is the
           office-holder's portfolio — the one line a student needs. Same card
           markup, with that line added. --}}
      @foreach($cabinet as $person)
        <div class="proj-card" data-rise style="text-align:center;align-items:center;">
          @if($person->photo)
            <img src="{{ asset('storage/'.$person->photo) }}" alt="{{ $person->name }}" loading="lazy" decoding="async"
                 style="width:110px;height:110px;object-fit:cover;object-position:top;border:2px solid var(--gold);">
          @else
            <span style="width:110px;height:110px;display:flex;align-items:center;justify-content:center;background:var(--pri-soft);color:var(--pri);font-size:34px;" aria-hidden="true">
              <i class="fas fa-user"></i>
            </span>
          @endif
          <h3 style="font-size:14.5px;">{{ $person->name }}</h3>
          <span class="client">{{ $person->title }}</span>
          @if($person->bio)
            <p style="font-size:12px;">{{ $person->bio }}</p>
          @endif
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<section class="band-surface tex-glow">
  <div class="wrap">
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
      <a href="{{ route('council') }}" wire:navigate class="proj-card" data-rise>
        <span class="client">Governance</span>
        <h3>Students on the Council</h3>
        <p>Student representation reaches the University's supreme governing body — see the full Council.</p>
        <span class="link">The University Council <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
      <a href="{{ route('campus-life') }}" wire:navigate class="proj-card" data-rise>
        <span class="client">Student Life</span>
        <h3>Life on campus</h3>
        <p>Sports, culture, events and community — what the Guild helps make happen, every semester.</p>
        <span class="link">Campus life <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
