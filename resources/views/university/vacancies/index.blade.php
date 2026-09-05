@extends('layouts.marketing')
@section('title', 'Vacancies | Muteesa I Royal University')
@section('desc', 'Career opportunities at Muteesa I Royal University — current academic and administrative vacancies across the Kampala and Masaka campuses, with deadlines and how to apply.')

@section('content')

@php
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Careers',
  'title' => 'Vacancies',
  'lead' => 'Work with Muteesa I Royal University.',
  'chips' => [
    ['fa-briefcase', $open->count().' open '.\Illuminate\Support\Str::plural('role', $open->count())],
    ['fa-location-dot', 'Kampala & Masaka campuses'],
  ],
])

<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Open Roles</p>
      <h2>Current openings</h2>
    </div>
    @forelse($open as $vacancy)
      <div class="feature-box" data-rise style="margin-bottom:14px;">
        <div style="display:flex;flex-wrap:wrap;gap:10px 16px;align-items:baseline;justify-content:space-between;">
          <h3 style="margin-bottom:0;">
            <a href="{{ route('vacancies.show', $vacancy) }}" wire:navigate style="color:var(--tx);">{{ $vacancy->title }}</a>
          </h3>
          <span style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11.5px;font-weight:700;color:var(--gold-d);white-space:nowrap;">
            <i class="fas fa-calendar-day" aria-hidden="true"></i>
            {{ $vacancy->deadline_on ? 'Apply by '.$vacancy->deadline_on->format('j M Y') : 'Open until filled' }}
          </span>
        </div>
        <div class="sub" style="margin:6px 0 10px;">
          {{ collect([$vacancy->reference_no, $vacancy->department, $vacancy->type, $vacancy->location])->filter()->implode(' · ') }}
        </div>
        @if($vacancy->summary)
          <p style="margin-bottom:10px;">{{ \Illuminate\Support\Str::limit($vacancy->summary, 200) }}</p>
        @endif
        <a href="{{ route('vacancies.show', $vacancy) }}" wire:navigate class="link" style="color:var(--pri);font-weight:600;font-size:13px;">
          Full details &amp; how to apply <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
      </div>
    @empty
      <div class="feature-box" data-rise style="text-align:center;">
        <p style="margin-bottom:0;">
          There are no open vacancies right now — check back, or follow our social channels for new openings.
        </p>
      </div>
    @endforelse
  </div>
</section>

@if($closed->isNotEmpty())
<section class="band-surface tex-glow">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Archive</p>
      <h2>Recently closed</h2>
    </div>
    <div style="max-width:760px;" data-rise>
      @foreach($closed as $vacancy)
        <div style="display:flex;flex-wrap:wrap;gap:6px 16px;justify-content:space-between;align-items:baseline;padding:12px 0;border-bottom:1px solid var(--line);">
          <div>
            <a href="{{ route('vacancies.show', $vacancy) }}" wire:navigate style="font-weight:600;font-size:13.5px;color:var(--tx2);">{{ $vacancy->title }}</a>
            @if($vacancy->department)
              <span style="font-size:12px;color:var(--tx3);"> · {{ $vacancy->department }}</span>
            @endif
          </div>
          <span style="font-size:12px;color:var(--tx3);">Closed {{ $vacancy->deadline_on?->format('j M Y') }}</span>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

@include('university.partials.cta-band')

@endsection
