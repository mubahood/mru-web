@extends('layouts.marketing')
@section('title', 'University Governance | Muteesa I Royal University')
@section('desc', 'How Muteesa I Royal University is governed: the principal officers, the University Council and the standing committees that run the institution.')

@section('content')

@php
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Governance',
  'title' => 'University Governance',
  'lead' => 'The University is led by its principal officers and standing committees, under the oversight of the University Council.',
])

@if($leadership->isNotEmpty())
<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Principal Officers</p>
      <h2>Principal officers</h2>
      <p>The officers responsible for the day-to-day academic and administrative life of the University.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
      @foreach($leadership as $member)
        @include('university.partials.person-card', ['person' => $member])
      @endforeach
    </div>
  </div>
</section>
@endif

@foreach($committees as $name => $members)
<section class="{{ $loop->odd ? 'band-surface tex-grid' : '' }}">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Committee</p>
      <h2>{{ $name }}</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
      @foreach($members as $member)
        @include('university.partials.person-card', ['person' => $member])
      @endforeach
    </div>
  </div>
</section>
@endforeach

<section>
  <div class="wrap">
    <a href="{{ route('council') }}" wire:navigate class="proj-card" data-rise
       style="max-width:640px;margin:0 auto;text-align:center;align-items:center;">
      <span class="client">Supreme governing body</span>
      <h3>The University Council</h3>
      <p>The Council is the supreme governing body of the University. Meet its members — from governance and academia to community and student representation.</p>
      <span class="link">View the Council <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
    </a>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
