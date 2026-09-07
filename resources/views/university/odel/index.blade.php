@extends('layouts.odel')
@section('title', 'ODEL — Open, Distance and E-Learning | Muteesa I Royal University')
@section('desc', 'Study with Muteesa I Royal University without giving up your job or your family. Six flexible study modes, drawn from the University\'s Council-approved Distance Learning and Flexible Learning policies.')

@section('content')

<section class="od-hero">
  <div class="wrap">
    <p class="eyebrow">Open, Distance &amp; E-Learning</p>
    <h1>A degree that fits around the life you already have</h1>
    <p class="lead">
      Most people who come to ODEL are already working, already raising a family, already
      committed somewhere else. The University's Flexible Learning Policy was written for exactly
      that reader — and it sets out six different ways to study, so the question is not whether you
      can afford to stop your life, but which of the six fits it.
    </p>
    <div class="hs-actions" style="margin-top:var(--s-5);display:flex;gap:12px;flex-wrap:wrap;">
      <a href="{{ route('odel.modes') }}" wire:navigate class="btn gold lg">
        Find your study mode <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
      <a href="{{ route('odel.how-it-works') }}" wire:navigate class="btn ghost lg">How ODEL works</a>
    </div>
  </div>
</section>

{{-- What the section can actually show, counted rather than claimed. --}}
<section style="padding-top:var(--s-6);padding-bottom:var(--s-5);">
  <div class="wrap">
    <div class="stat-row" style="margin-top:0;">
      <div><div class="v">{{ count($modes) }}</div><div class="l">Study modes</div></div>
      <div><div class="v">{{ $courses }}</div><div class="l">Online courses</div></div>
      <div><div class="v">2</div><div class="l">Council policies</div></div>
      <div><div class="v">2</div><div class="l">Campuses</div></div>
    </div>
  </div>
</section>

<section class="band-surface" style="padding-top:var(--s-6);">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">The Six Modes</p>
      <h2>Which of these is you?</h2>
      <p>Each mode is a different answer to the same question — how do I study without
         stopping everything else. Pick the one that sounds like your week.</p>
    </div>

    <div class="od-modes">
      @foreach($modes as $key => $mode)
        <a href="{{ route('odel.mode', $key) }}" wire:navigate class="od-mode" data-rise>
          <div class="od-mode-top">
            <span class="ic"><i class="fas {{ $mode['icon'] }}" aria-hidden="true"></i></span>
            @include('university.odel.partials.status', ['status' => $mode['status']])
          </div>
          <h3>{{ $mode['name'] }}</h3>
          <p class="od-suits">{{ $mode['suits'] }}</p>
          <p>{{ $mode['summary'] }}</p>
          <span class="link">How it works <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
        </a>
      @endforeach
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">What Flexible Learning Is For</p>
      <h2>Five things the policy commits the University to</h2>
      <p>Taken from section 4 of the Flexible Learning Policy, which the University Council
         approved in {{ \App\Support\Odel::APPROVED }}.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(min(260px,100%),1fr));">
      @foreach($promises as $p)
        <div class="card" data-rise>
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;margin-bottom:8px;">
            <span class="od-clause">{{ $p['clause'] }}</span>
            @include('university.odel.partials.status', ['status' => $p['status']])
          </div>
          <h3>{{ $p['title'] }}</h3>
          <p>{{ $p['body'] }}</p>
        </div>
      @endforeach
    </div>

    <div class="od-note" data-rise style="margin-top:var(--s-5);max-width:none;">
      <i class="fas fa-circle-info" aria-hidden="true"></i>
      <p><strong>Read the labels.</strong> Everything in this section is marked either
        <em>In place</em> — running today and demonstrable — or <em>Established by policy</em>,
        meaning the Council has approved and committed to it. We do not present the second as
        though it were the first. If a service matters to your decision, ask us before you pay
        anything, and we will tell you exactly where it stands.</p>
    </div>
  </div>
</section>

@if($dates->isNotEmpty())
<section class="band-surface">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Dates</p>
      <h2>What is coming up</h2>
    </div>
    <div class="od-list" data-rise>
      @foreach($dates as $d)
        <div class="od-item">
          <p style="grid-column:2;">{{ $d->activity }}</p>
          <span class="od-clause">{{ $d->starts_on?->format('j M Y') }}</span>
        </div>
      @endforeach
    </div>
    <div style="margin-top:var(--s-4);">
      <a href="{{ route('odel.calendar') }}" wire:navigate class="link">All ODEL dates <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
    </div>
  </div>
</section>
@endif

<section class="od-band">
  <div class="wrap" style="text-align:center;">
    <div class="sec-head">
      <p class="eyebrow">Next Step</p>
      <h2>Talk to someone before you decide</h2>
      <p style="max-width:60ch;margin-inline:auto;">The right mode depends on things a website
        cannot know — your hours, your employer, what you have already studied. That conversation
        is free, and it is the fastest way to an answer.</p>
    </div>
    <div style="margin-top:var(--s-5);display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a href="{{ route('odel.apply') }}" wire:navigate class="btn gold lg">Start here <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
      <a href="{{ route('odel.support') }}" wire:navigate class="btn ghost lg" style="border-color:rgba(255,255,255,.45);color:#fff;">Support &amp; contacts</a>
    </div>
  </div>
</section>

@endsection
