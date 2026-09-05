@extends('layouts.marketing')
@section('title', 'Who We Are | Muteesa I Royal University')
@section('desc', 'The identity of Muteesa I Royal University: our mission, vision, six core values and the story of a university named in honour of Kabaka Muteesa I of Buganda.')

@section('content')

@php
  /* One icon per core value, mapped by position; anything unexpected gets a star. */
  $valueIcons = ['fa-award', 'fa-scale-balanced', 'fa-drum', 'fa-hands-holding-circle', 'fa-lightbulb', 'fa-hand-holding-heart'];
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Who We Are',
  'title' => $identity['strapline'] ?? 'Rooted in Heritage. Focused on the Future.',
  'lead' => $identity['motto'] ?? 'Seeking Greater Horizons in Thought and Action',
  'trail' => [['label' => 'About MRU', 'url' => route('about')], ['label' => 'Who we are']],
])

<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Mission &amp; Vision</p>
      <h2>What drives {{ $identity['short'] ?? 'MRU' }}</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
      <div class="feature-box" data-rise>
        <div class="sub"><i class="fas fa-bullseye" aria-hidden="true"></i> Our Mission</div>
        <p style="margin-bottom:0;">{{ $identity['mission'] ?? '' }}</p>
      </div>
      <div class="feature-box" data-rise>
        <div class="sub"><i class="fas fa-eye" aria-hidden="true"></i> Our Vision</div>
        <p style="margin-bottom:0;">{{ $identity['vision'] ?? '' }}</p>
      </div>
    </div>
  </div>
</section>

@if(!empty($identity['values']))
<section class="band-surface tex-grid">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Core Values</p>
      <h2>Six values we live by</h2>
      <p>Every classroom, office and playing field at the University is held to the same standard.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
      @foreach($identity['values'] as $i => $value)
        <div class="card" data-rise>
          <span class="ic"><i class="fas {{ $valueIcons[$i] ?? 'fa-star' }}" aria-hidden="true"></i></span>
          <h3>{{ $value['name'] }}</h3>
          <p>{{ $value['desc'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

@if(!empty($identity['history']))
<section>
  <div class="wrap">
    <div class="page" style="margin:0;">
      <div class="sec-head left">
        <p class="eyebrow">Our Story</p>
        <h2>How the University came to be</h2>
      </div>
      @foreach($identity['history'] as $paragraph)
        <p data-rise class="lead" style="margin-bottom:14px;">{{ $paragraph }}</p>
      @endforeach
      <p data-rise style="margin-top:18px;">
        <a href="{{ route('about') }}" wire:navigate class="link" style="color:var(--pri);font-weight:600;">More about MRU <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
      </p>
    </div>
  </div>
</section>
@endif

{{-- The namesake, set apart the way an inscription is. Kept on the light
     surface because the navy closing band follows immediately below. --}}
<section class="band-surface tex-glow">
  <div class="wrap" style="text-align:center;">
    <i class="fas fa-crown" aria-hidden="true" style="color:var(--gold-d);font-size:22px;"></i>
    <blockquote data-rise style="margin:14px auto 0;max-width:640px;border:0;padding:0;">
      <p style="font-size:21px;font-weight:300;line-height:1.5;color:var(--pri);">
        {{ $identity['namesake'] ?? 'Named for Kabaka Muteesa I of Buganda (1856–1884)' }}
      </p>
      <footer style="margin-top:12px;font-size:12.5px;color:var(--gold-d);letter-spacing:.04em;">
        A monarch celebrated for opening his kingdom to learning and new ideas
      </footer>
    </blockquote>
    @isset($identity['accreditation'])
      <p data-rise style="margin-top:22px;font-size:12.5px;color:var(--tx2);">
        <i class="fas fa-certificate" aria-hidden="true" style="color:var(--gold-d);"></i>
        {{ $identity['accreditation'] }}
      </p>
    @endisset
  </div>
</section>

@include('university.partials.cta-band')

@endsection
