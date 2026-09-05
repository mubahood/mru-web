@extends('layouts.marketing')
@section('title', 'Sports & Lifestyle | Muteesa I Royal University')
@section('desc', 'Sports at Muteesa I Royal University: football with Royals FC, netball, basketball, volleyball, athletics and indoor games — varsity teams, leagues and the annual sports gala.')

@section('content')

@php
  /* The flagship football club gets its own callout below the grid. */
  $football = collect($sports)->first(fn ($s) => str_contains($s['title'] ?? '', 'Royals FC'));
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Sports',
  'title' => 'Sports & Lifestyle',
  'lead' => 'Six disciplines, two campuses, one Royals spirit.',
  'mark' => 'Royals',
])

@if(!empty($sports))
<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Disciplines</p>
      <h2>Pick your game</h2>
      <p>Varsity teams, inter-faculty leagues and recreational play — every student finds a place on a team.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
      @foreach($sports as $sport)
        <div class="card" data-rise>
          <span class="ic"><i class="fas {{ $sport['icon'] ?? 'fa-medal' }}" aria-hidden="true"></i></span>
          <h3>{{ $sport['title'] }}</h3>
          <p>{{ $sport['description'] }}</p>
          @if(!empty($sport['tags']))
            <div class="pill-row" style="margin-top:10px;">
              @foreach($sport['tags'] as $tag)
                <span class="pill">{{ $tag }}</span>
              @endforeach
            </div>
          @endif
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

@if($football)
<section class="band-deep">
  <div class="wrap" style="text-align:center;">
    <div class="sec-head">
      <p class="eyebrow">The Flagship</p>
      <h2><i class="fas fa-futbol" aria-hidden="true" style="color:var(--gold);"></i> Royals FC</h2>
      <p>{{ $football['description'] }}</p>
    </div>
    @if(!empty($football['tags']))
      <div class="pill-row" data-rise style="justify-content:center;">
        @foreach($football['tags'] as $tag)
          <span class="pill" style="border-color:rgba(255,255,255,.3);color:rgba(255,255,255,.85);">{{ $tag }}</span>
        @endforeach
      </div>
    @endif
    <div style="margin-top:24px;" data-rise>
      <a href="{{ route('events.index') }}" wire:navigate class="btn gold">
        Fixtures &amp; events <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
    </div>
  </div>
</section>
@endif

@include('university.partials.cta-band')

@endsection
