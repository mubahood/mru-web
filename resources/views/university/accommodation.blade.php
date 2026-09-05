@extends('layouts.marketing')
@section('title', 'Student Accommodation | Muteesa I Royal University')
@section('desc', 'Halls of residence at Muteesa I Royal University — who each hall serves, bed capacity and semester pricing, plus how to book a room after admission.')

@section('content')

@php
  $accEmail = $contacts['accommodation_email'] ?? 'accommodation@mru.ac.ug';
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Student Life',
  'title' => 'Student Accommodation',
  'lead' => 'A room on campus puts you minutes from your first lecture. Here is where our students live, and how to book.',
  'mark' => 'Halls',
])

@if(!empty($halls))
<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Halls of Residence</p>
      <h2>Choose your hall</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
      @foreach($halls as $hall)
        <div class="feature-box" data-rise>
          <div class="sub"><i class="fas fa-building" aria-hidden="true"></i> {{ $hall['campus'] }}</div>
          <h3>{{ $hall['name'] }}</h3>
          <p style="margin-bottom:8px;"><i class="fas fa-user-group" aria-hidden="true" style="color:var(--gold-d);"></i> {{ $hall['audience'] }}</p>
          <p style="margin-bottom:8px;"><i class="fas fa-bed" aria-hidden="true" style="color:var(--gold-d);"></i> {{ $hall['beds'] }} beds</p>
          <p class="course-price" style="margin-bottom:0;">{{ $hall['price'] }}</p>
        </div>
      @endforeach
    </div>
    <p data-rise style="margin-top:16px;font-size:13px;color:var(--tx2);">
      <i class="fas fa-circle-info" aria-hidden="true" style="color:var(--gold-d);"></i>
      Private hostels also operate near both campuses, for students who prefer to live off campus.
    </p>
  </div>
</section>
@endif

<section class="band-surface tex-glow">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Booking</p>
      <h2>How to book a room</h2>
      <p>Rooms are allocated to admitted students, in three steps.</p>
    </div>
    <div class="steps">
      <div class="step" data-rise>
        <div class="n" aria-hidden="true">1</div>
        <h4>Apply for admission</h4>
        <p>Secure your place on a programme first — accommodation is reserved for admitted students.</p>
      </div>
      <div class="step" data-rise>
        <div class="n" aria-hidden="true">2</div>
        <h4>Email the accommodation office</h4>
        <p>Once admitted, write to
          <a href="mailto:{{ $accEmail }}" class="link" style="color:var(--pri);font-weight:600;">{{ $accEmail }}</a>
          with your name and preferred hall.</p>
      </div>
      <div class="step" data-rise>
        <div class="n" aria-hidden="true">3</div>
        <h4>Pay &amp; receive your allocation</h4>
        <p>Complete payment and receive your room allocation before the semester begins.</p>
      </div>
    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
