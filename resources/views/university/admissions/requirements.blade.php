@extends('layouts.marketing')
@section('title', 'Entry Requirements | Muteesa I Royal University')
@section('desc', 'Minimum entry requirements at Muteesa I Royal University — for bachelors degrees, diplomas and certificates, masters and postgraduate study, and international applicants.')

@section('content')

@php
  $req = $admissions['requirements'] ?? [];
  /* One band per level, alternating surfaces so the page reads as four clear
     answers rather than one long list. */
  $levels = [
    'bachelors' => ['label' => 'Bachelors', 'title' => 'Bachelors degrees', 'icon' => 'fa-graduation-cap', 'band' => ''],
    'diplomas' => ['label' => 'Diplomas', 'title' => 'Diplomas & certificates', 'icon' => 'fa-certificate', 'band' => 'band-surface tex-glow'],
    'masters' => ['label' => 'Postgraduate', 'title' => 'Masters & postgraduate', 'icon' => 'fa-user-graduate', 'band' => ''],
    'international' => ['label' => 'International', 'title' => 'International applicants', 'icon' => 'fa-earth-africa', 'band' => 'band-surface tex-grid'],
  ];
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Admissions',
  'title' => 'Entry Requirements',
  'lead' => 'What you need to qualify at each level of study — check your level below, then apply on the E-Portal.',
  'chips' => [
    ['fa-list-check', 'Requirements by level'],
    ['fa-stamp', 'NCHE-recognised equivalents accepted'],
  ],
])

@foreach($levels as $key => $level)
  @continue(empty($req[$key]))
  <section @if($level['band']) class="{{ $level['band'] }}" @endif>
    <div class="wrap">
      <div class="sec-head left">
        <p class="eyebrow">{{ $level['label'] }}</p>
        <h2>{{ $level['title'] }}</h2>
      </div>
      <div class="feature-box" data-rise>
        <div class="sub"><i class="fas {{ $level['icon'] }}" aria-hidden="true"></i> Minimum requirements</div>
        <ul class="outcomes-list" style="grid-template-columns:1fr;gap:12px;">
          @foreach($req[$key] as $item)
            <li>{{ $item }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  </section>
@endforeach

<section>
  <div class="wrap">
    <div class="feature-box" data-rise style="border-left:3px solid var(--gold);max-width:760px;">
      <div class="sub">Equivalent qualifications</div>
      <p style="margin-bottom:0;">
        Qualifications recognised by the Uganda National Council for Higher Education (NCHE) as
        equivalent to the above are accepted at every level. If you are unsure whether your
        certificates qualify, ask the admissions team before you apply.
      </p>
    </div>
    <div class="ctas" data-rise style="margin-top:28px;">
      <a href="{{ route('admissions.apply') }}" wire:navigate class="btn">
        How to Apply <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
      <a href="{{ route('programmes.index') }}" wire:navigate class="btn ghost">
        Browse Programmes <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
