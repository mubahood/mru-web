@extends('layouts.marketing')
@section('title', 'Scholarships & Bursaries | Muteesa I Royal University')
@section('desc', "Scholarships and bursaries at Muteesa I Royal University — merit, need-based, sports and special-category schemes including the Kabaka's Scholarship, with coverage and eligibility criteria.")

@section('content')

@php
  $admissionsEmail = $contacts['admissions_email'] ?? ($contacts['email'] ?? 'admissions@mru.ac.ug');
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Admissions',
  'title' => 'Scholarships & Bursaries',
  'lead' => "Six schemes reward merit, need, talent and service — including the Kabaka's Scholarship.",
  'chips' => [
    ['fa-award', $scholarships->count().' schemes'],
    ['fa-hand-holding-heart', 'Merit, need & special categories'],
  ],
])

<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">The Schemes</p>
      <h2>Support that follows the student</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));">
      @forelse($scholarships as $scholarship)
        <div class="feature-box" data-rise style="margin-bottom:0;display:flex;flex-direction:column;">
          @if($scholarship->category)
            <div class="sub">{{ $scholarship->category }}</div>
          @endif
          <h3>{{ $scholarship->name }}</h3>
          @if($scholarship->coverage)
            <p style="font-weight:700;color:var(--gold-d);margin-bottom:10px;">
              <i class="fas fa-award" aria-hidden="true"></i> {{ $scholarship->coverage }}
            </p>
          @endif
          @if($scholarship->description)
            <p>{{ $scholarship->description }}</p>
          @endif
          @if($scholarship->criteria)
            <p style="margin-bottom:10px;"><strong>Who qualifies:</strong> {{ $scholarship->criteria }}</p>
          @endif
          @if($scholarship->amount_note)
            <p style="margin:auto 0 0;font-size:12px;color:var(--tx3);">{{ $scholarship->amount_note }}</p>
          @endif
        </div>
      @empty
        <p style="color:var(--tx2);">Scholarship schemes for the next intake are being published — contact the admissions office for what is currently available.</p>
      @endforelse
    </div>
  </div>
</section>

<section class="band-surface tex-glow">
  <div class="wrap">
    <div class="feature-box" data-rise style="border-left:3px solid var(--gold);max-width:760px;margin:0 auto;">
      <div class="sub">How to apply for support</div>
      <p style="margin-bottom:12px;">
        Apply for admission first, then contact the admissions office about the scheme that fits
        you — the team will confirm eligibility and what to submit.
      </p>
      <p style="margin-bottom:0;">
        <i class="fas fa-envelope" aria-hidden="true" style="color:var(--gold-d);margin-right:8px;"></i>
        <a href="mailto:{{ $admissionsEmail }}" style="font-weight:600;color:var(--pri);">{{ $admissionsEmail }}</a>
      </p>
    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
