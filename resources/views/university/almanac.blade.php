@extends('layouts.marketing')
@section('title', 'Academic Almanac | Muteesa I Royal University')
@section('desc', 'The academic almanac of Muteesa I Royal University — semester dates, reporting and orientation weeks, examinations and every key activity of the academic year.')

@section('content')

@php
  $n = 0;
  $idx = function () use (&$n) { return str_pad((string) ++$n, 2, '0', STR_PAD_LEFT); };
  $cell = 'padding:10px 14px;border:1px solid var(--line);vertical-align:top;';
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Academics',
  'title' => 'Academic Almanac',
  'lead' => 'The rhythm of the academic year — when semesters open, when examinations run, and everything in between.',
  'mark' => 'Almanac',
])

@forelse($years as $year => $semesters)
<section class="{{ $loop->even ? 'band-surface tex-grid' : '' }}">
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Academic Year</span></div>
      <h2>{{ $year }}</h2>
    </div>

    @foreach($semesters as $semester => $entries)
      <h3 data-rise style="font-size:15px;font-weight:600;margin:{{ $loop->first ? '0' : '26px' }} 0 10px;color:var(--pri);">
        <i class="fas fa-calendar-week" aria-hidden="true" style="color:var(--gold-d);"></i> {{ $semester }}
      </h3>
      <div style="overflow-x:auto;" data-rise>
        <table style="width:100%;border-collapse:collapse;font-size:13px;background:var(--surface);">
          <thead>
            <tr>
              <th scope="col" style="{{ $cell }}text-align:left;background:var(--surface-2);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx3);width:34%;">Period</th>
              <th scope="col" style="{{ $cell }}text-align:left;background:var(--surface-2);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--tx3);">Activity</th>
            </tr>
          </thead>
          <tbody>
            @foreach($entries as $entry)
              <tr>
                @if($entry->period)
                  <td style="{{ $cell }}font-family:ui-monospace,Menlo,monospace;font-size:12px;font-weight:600;color:var(--gold-d);">{{ $entry->period }}</td>
                  <td style="{{ $cell }}color:var(--tx2);">{{ $entry->activity }}</td>
                @else
                  <td colspan="2" style="{{ $cell }}color:var(--tx2);">{{ $entry->activity }}</td>
                @endif
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endforeach
  </div>
</section>
@empty
<section>
  <div class="wrap">
    <p class="lead">The almanac for the coming academic year is being finalised. Please check back shortly.</p>
  </div>
</section>
@endforelse

<section>
  <div class="wrap">
    <p data-rise style="font-size:13px;color:var(--tx2);">
      <i class="fas fa-file-pdf" aria-hidden="true" style="color:var(--gold-d);"></i>
      Printed almanacs and other official documents are on the
      <a href="{{ route('downloads') }}" wire:navigate class="link" style="color:var(--pri);font-weight:600;">Downloads page <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
    </p>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
