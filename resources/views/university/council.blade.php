@extends('layouts.marketing')
@section('title', 'The University Council | Muteesa I Royal University')
@section('desc', 'The University Council is the supreme governing body of Muteesa I Royal University — its members span governance, academia, the professions, the community and the student body.')

@section('content')

@php
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Governance',
  'title' => 'The University Council',
  'lead' => 'The Council is the supreme governing body of the University.',
  'mark' => 'Council',
  'trail' => [['label' => 'About MRU', 'url' => route('about')], ['label' => 'University Council']],
  'photo' => asset('images/photos/hero-council.jpg'),
  'photoAlt' => 'Members and guests of the University assembled for a group photograph',
])

@forelse($members as $group => $people)
<section class="{{ $loop->even ? 'band-surface tex-grid' : '' }}">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Council</p>
      <h2>{{ $group }}</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
      @foreach($people as $member)
        @include('university.partials.person-card', ['person' => $member])
      @endforeach
    </div>
  </div>
</section>
@empty
<section>
  <div class="wrap">
    <p class="lead">Council membership is being updated. Please check back shortly.</p>
  </div>
</section>
@endforelse

<section>
  <div class="wrap">
    <a href="{{ route('governance') }}" wire:navigate class="proj-card" data-rise
       style="max-width:640px;margin:0 auto;text-align:center;align-items:center;">
      <span class="client">Governance</span>
      <h3>Officers &amp; committees</h3>
      <p>See the principal officers and the standing committees that carry the Council's decisions into daily university life.</p>
      <span class="link">University governance <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
    </a>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
