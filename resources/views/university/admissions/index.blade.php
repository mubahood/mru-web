@extends('layouts.marketing')
@section('title', 'Admissions | Muteesa I Royal University')
@section('desc', 'How to join Muteesa I Royal University — the application steps, timeline, entry requirements, fees, scholarships and intake deadlines, plus how to reach the admissions office.')

@section('content')

@php
  $applyUrl = \App\Support\University::applyUrl();
  $wa = $contacts['whatsapp_link'] ?? '#';
  $admissionsEmail = $contacts['admissions_email'] ?? ($contacts['email'] ?? 'admissions@mru.ac.ug');
  $phone = $contacts['phone'] ?? null;
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Admissions',
  'title' => 'Your place at MRU starts here',
  'lead' => ($admissions['deadline_note'] ?? 'Applications are open for the next intake.')
            .' The requirements, fees, scholarships and deadlines are all one click below.',
  'chips' => array_values(array_filter([
    ! empty($admissions['deadline_note']) ? ['fa-calendar-check', $admissions['deadline_note']] : null,
    ! empty($admissions['application_fee']) ? ['fa-coins', 'Application fee '.$admissions['application_fee']] : null,
    ['fa-graduation-cap', $programmeCount.'+ programmes'],
  ])),
])

{{-- Three moves from reading this page to a submitted application. --}}
<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Getting In</p>
      <h2>Three steps to a submitted application</h2>
    </div>
    <div class="steps" style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr));margin-top:0;">
      <div class="step" data-rise>
        <div class="n">1</div>
        <h4>Choose a programme</h4>
        <p>Browse the {{ $programmeCount }}+ certificates, diplomas, degrees and postgraduate programmes to find your fit.</p>
        <p style="margin-top:10px;"><a href="{{ route('programmes.index') }}" wire:navigate class="link" style="color:var(--pri);font-weight:600;">View programmes <i class="fas fa-arrow-right" aria-hidden="true"></i></a></p>
      </div>
      <div class="step" data-rise>
        <div class="n">2</div>
        <h4>Check the requirements</h4>
        <p>Confirm you meet the minimum entry requirements for your level — bachelors, diploma, masters or international.</p>
        <p style="margin-top:10px;"><a href="{{ route('admissions.requirements') }}" wire:navigate class="link" style="color:var(--pri);font-weight:600;">Entry requirements <i class="fas fa-arrow-right" aria-hidden="true"></i></a></p>
      </div>
      <div class="step" data-rise>
        <div class="n">3</div>
        <h4>Apply online</h4>
        <p>Complete your application on the E-Portal{{ ! empty($admissions['application_fee']) ? ' — the application fee is '.$admissions['application_fee'] : '' }}.</p>
        <p style="margin-top:10px;"><a href="{{ $applyUrl }}" rel="external" class="link" style="color:var(--pri);font-weight:600;">Apply on the E-Portal <i class="fas fa-arrow-right" aria-hidden="true"></i></a></p>
      </div>
    </div>
  </div>
</section>

@if(! empty($admissions['timeline']))
<section class="band-surface tex-glow">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Timeline</p>
      <h2>The admissions year at a glance</h2>
      <p>From opening day to orientation — where each stage of the cycle falls.</p>
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

{{-- Every admissions guide, one hop away. --}}
<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Guides</p>
      <h2>Go straight to what you need</h2>
    </div>
    <div class="icon-row">
      <a href="{{ route('admissions.requirements') }}" wire:navigate data-rise>
        <span class="ic"><i class="fas fa-list-check" aria-hidden="true"></i></span>
        <span>Entry Requirements</span>
      </a>
      <a href="{{ route('admissions.fees') }}" wire:navigate data-rise>
        <span class="ic"><i class="fas fa-money-bill-wave" aria-hidden="true"></i></span>
        <span>Fees Structure</span>
      </a>
      <a href="{{ route('admissions.scholarships') }}" wire:navigate data-rise>
        <span class="ic"><i class="fas fa-award" aria-hidden="true"></i></span>
        <span>Scholarships</span>
      </a>
      <a href="{{ route('admissions.intakes') }}" wire:navigate data-rise>
        <span class="ic"><i class="fas fa-calendar-days" aria-hidden="true"></i></span>
        <span>Intakes &amp; Deadlines</span>
      </a>
      <a href="{{ route('admissions.faqs') }}" wire:navigate data-rise>
        <span class="ic"><i class="fas fa-circle-question" aria-hidden="true"></i></span>
        <span>Admissions FAQs</span>
      </a>
      <a href="{{ route('admissions.international') }}" wire:navigate data-rise>
        <span class="ic"><i class="fas fa-earth-africa" aria-hidden="true"></i></span>
        <span>International Students</span>
      </a>
    </div>
  </div>
</section>

<section class="band-surface tex-grid">
  <div class="wrap">
    <div class="adm-contact-split" style="display:grid;grid-template-columns:1.2fr 1fr;gap:36px;align-items:center;">
      <div>
        <div class="sec-head left" style="margin-bottom:14px;">
          <p class="eyebrow">Talk To Us</p>
          <h2>The admissions office is a message away</h2>
        </div>
        <p class="lead" data-rise>Not sure which programme fits, or stuck on a portal step? Ask — the team answers by email, phone and WhatsApp.</p>
      </div>
      <div class="feature-box" data-rise>
        <div class="sub">Admissions Office</div>
        <p style="margin-bottom:8px;">
          <i class="fas fa-envelope" aria-hidden="true" style="color:var(--gold-d);margin-right:8px;"></i>
          <a href="mailto:{{ $admissionsEmail }}" style="font-weight:600;color:var(--pri);">{{ $admissionsEmail }}</a>
        </p>
        @if($phone)
          <p style="margin-bottom:16px;">
            <i class="fas fa-phone" aria-hidden="true" style="color:var(--gold-d);margin-right:8px;"></i>
            <a href="tel:{{ str_replace(' ', '', $phone) }}" style="font-weight:600;color:var(--pri);">{{ $phone }}</a>
          </p>
        @endif
        <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn gold">
          <i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp Admissions
        </a>
      </div>
    </div>
  </div>
</section>
@push('styles')<style>@media(max-width:820px){.adm-contact-split{grid-template-columns:1fr !important;}}</style>@endpush

@include('university.partials.cta-band')

@endsection
