@extends('layouts.marketing')
@section('title', 'Sports & Lifestyle | Muteesa I Royal University')
@section('desc', 'Sports at Muteesa I Royal University: football with Royals FC, netball, basketball, volleyball, athletics and indoor games — varsity teams, leagues and the annual sports gala.')

@section('content')

@php
  $n = 0;
  $idx = function () use (&$n) { return str_pad((string) ++$n, 2, '0', STR_PAD_LEFT); };
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
      <div class="sec-idx">{{ $idx() }} <span>Disciplines</span></div>
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
      <div class="sec-idx">&nbsp;<span>The Flagship</span></div>
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
      <a href="{{ route('events.index') }}" wire:navigate class="btn gold cta">
        <span class="cta-a">Fixtures &amp; events</span>
        <span class="cta-b" aria-hidden="true">See what's on <i class="fas fa-arrow-right"></i></span>
      </a>
    </div>
  </div>
</section>
@endif

@include('university.partials.cta-band')

@endsection
