@extends('layouts.marketing')
@section('title', 'Intakes & Deadlines | Muteesa I Royal University')
@section('desc', 'Intake dates and application deadlines at Muteesa I Royal University — the August and January intakes, the admissions timeline, and the documents to have ready.')

@section('content')

@php
  $n = 0;
  $idx = function () use (&$n) { return str_pad((string) ++$n, 2, '0', STR_PAD_LEFT); };
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Admissions',
  'title' => 'Intakes & Deadlines',
  'lead' => $admissions['deadline_note'] ?? 'Applications are open for the next intake.',
  'chips' => collect($admissions['intakes'] ?? [])
      ->map(fn ($intake) => ['fa-calendar-days', ($intake['name'] ?? '').' — apply '.($intake['window'] ?? '')])
      ->values()->all(),
])

@if(! empty($admissions['intakes']))
<section>
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Intakes</span></div>
      <h2>Two intakes a year</h2>
      <p>Miss one window and the next is never far — both intakes admit across the university.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
      @foreach($admissions['intakes'] as $intake)
        <div class="feature-box" data-rise style="margin-bottom:0;">
          <div class="sub">Applications: {{ $intake['window'] ?? '' }}</div>
          <h3>{{ $intake['name'] ?? '' }}</h3>
          <p style="margin-bottom:0;">
            <i class="fas fa-play" aria-hidden="true" style="color:var(--gold-d);margin-right:8px;font-size:11px;"></i>
            Studies begin in <strong>{{ $intake['starts'] ?? '' }}</strong>.
          </p>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

@if(! empty($admissions['timeline']))
<section class="band-surface tex-glow">
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Timeline</span></div>
      <h2>From application to orientation</h2>
    </div>
    <div class="tl" data-rise style="--tl-w:150px;max-width:760px;">
      @foreach($admissions['timeline'] as $stage)
        <div class="tl-row">
          <div class="tl-when">{{ $stage['when'] ?? '' }}</div>
          <div class="tl-what">
            <h3>{{ $stage['stage'] ?? '' }}</h3>
            <p>{{ $stage['desc'] ?? '' }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<section>
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Get Ready</span></div>
      <h2>What you'll need ready</h2>
      <p>Have these at hand and the online application takes minutes, not days.</p>
    </div>
    <div class="feature-box" data-rise>
      <ul class="outcomes-list" style="gap:12px 24px;">
        <li>Academic documents and transcripts</li>
        <li>National ID or passport</li>
        <li>Passport photographs</li>
        <li>The application fee{{ ! empty($admissions['application_fee']) ? ' — '.$admissions['application_fee'] : '' }}</li>
      </ul>
    </div>
    <div class="ctas" data-rise style="margin-top:26px;">
      <a href="{{ route('admissions.apply') }}" wire:navigate class="btn cta">
        <span class="cta-a">How to Apply</span>
        <span class="cta-b" aria-hidden="true">The 7 steps <i class="fas fa-arrow-right"></i></span>
      </a>
      <a href="{{ route('admissions.requirements') }}" wire:navigate class="btn ghost cta">
        <span class="cta-a">Entry Requirements</span>
        <span class="cta-b" aria-hidden="true">Check your level <i class="fas fa-arrow-right"></i></span>
      </a>
    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
