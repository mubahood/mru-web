@extends('layouts.marketing')
@section('title', $vacancy->title.' | Muteesa I Royal University')
@section('desc', \Illuminate\Support\Str::limit(strip_tags((string) ($vacancy->summary ?: $vacancy->requirements)), 155) ?: 'A vacancy at Muteesa I Royal University.')

@section('content')

@php
  $careersEmail = \App\Support\University::contacts()['careers_email'] ?? 'careers@mru.ac.ug';
  $deadlineText = $vacancy->deadline_on ? 'Apply by '.$vacancy->deadline_on->format('j M Y') : 'Open until filled';
  $chips = array_values(array_filter([
    $vacancy->reference_no ? ['fa-hashtag', 'Ref: '.$vacancy->reference_no] : null,
    ['fa-calendar-day', $deadlineText],
    $vacancy->isOpen() ? ['fa-circle-check', 'Open'] : ['fa-circle-xmark', 'Closed'],
  ]));
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Vacancy',
  'mark' => 'Careers',
  'title' => $vacancy->title,
  'lead' => collect([$vacancy->department, $vacancy->type, $vacancy->location])->filter()->implode(' · ')
            ?: 'Muteesa I Royal University',
  'chips' => $chips,
])

<section>
  <div class="wrap">
    <div class="page">
      @if($vacancy->summary)
        <p class="lead" data-rise>{{ $vacancy->summary }}</p>
      @endif

      @if($vacancy->requirements)
        <h2 data-rise>Requirements</h2>
        <p data-rise>{!! nl2br(e($vacancy->requirements)) !!}</p>
      @endif

      @if($vacancy->attachment)
        <div data-rise style="margin-top:24px;">
          <a href="{{ asset('storage/'.$vacancy->attachment) }}" target="_blank" rel="noopener" class="btn gold lg">
            <i class="fas fa-file-pdf" aria-hidden="true"></i> Full advert (PDF)
          </a>
        </div>
      @endif

      <div class="feature-box" data-rise style="border-left:3px solid var(--gold);margin-top:28px;">
        <div class="sub">How to apply</div>
        <p style="margin-bottom:0;">
          Submit your application and CV to
          <a href="mailto:{{ $careersEmail }}" style="font-weight:600;color:var(--pri);">{{ $careersEmail }}</a>{{ $vacancy->reference_no ? ', quoting reference '.$vacancy->reference_no : ', quoting the reference number' }}.
          {{ $vacancy->deadline_on ? 'Applications close on '.$vacancy->deadline_on->format('j F Y').'.' : '' }}
        </p>
      </div>

      <div data-rise style="margin-top:24px;">
        <a href="{{ route('vacancies.index') }}" wire:navigate class="btn ghost">
          <i class="fas fa-arrow-left" aria-hidden="true"></i> All vacancies
        </a>
      </div>
    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
