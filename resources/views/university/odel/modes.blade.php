@extends('layouts.odel')
@section('title', 'The six ODEL study modes | Muteesa I Royal University')
@section('desc', 'Part-time, distance, intensive, project-based, a single course unit, or learning through your employment — the six flexible study modes set out in Muteesa I Royal University\'s Flexible Learning Policy.')

@section('content')

@include('university.odel.partials.hero', [
  'eyebrow' => 'Study Modes',
  'title' => 'Six ways to study',
  'lead' => 'Section 8 of the Flexible Learning Policy sets out six modes. They exist because
             "learners might not always be able to study full-time on Campus" — many are working,
             many have family commitments, and some want one subject rather than a whole degree.',
  'trail' => [['label' => 'Study modes']],
])

<section style="padding-top:var(--s-6);">
  <div class="wrap">
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
          <span class="od-clause">{{ $mode['clause'] }}</span>
          <span class="link">In detail <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
        </a>
      @endforeach
    </div>

    <div class="od-note" data-rise style="margin-top:var(--s-6);">
      <i class="fas fa-circle-info" aria-hidden="true"></i>
      <p>Modes are not mutually exclusive. A part-time student may take a unit in intensive mode;
         a distance student may claim credit for prior learning. What you can combine depends on
         the programme, so <a href="{{ route('odel.support') }}" class="link" style="color:#6B4A0C;font-weight:600;">ask before you enrol</a>.</p>
    </div>
  </div>
</section>

@endsection
