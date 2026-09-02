@extends('layouts.marketing')
@section('title', $programme->name.' | Programmes | Muteesa I Royal University')
@section('desc', \Illuminate\Support\Str::limit(strip_tags($programme->description) ?: $programme->levelLabel().' at Muteesa I Royal University — entry requirements, tuition and intakes for '.$programme->name.'.', 150))

@push('jsonld')
@php
  /* schema.org Course + CourseInstance so the programme is legible to search
     engines and AI answer surfaces (docs/03 §5). */
  $courseNode = array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Course',
    'name' => $programme->name,
    'description' => \Illuminate\Support\Str::limit(strip_tags((string) $programme->description), 300) ?: $programme->levelLabel().' at Muteesa I Royal University.',
    'provider' => [
      '@type' => 'CollegeOrUniversity',
      'name' => 'Muteesa I Royal University',
      'sameAs' => url('/'),
    ],
    'educationalCredentialAwarded' => $programme->levelLabel(),
    'timeRequired' => $programme->duration,
    'offers' => $programme->tuition_per_semester ? [
      '@type' => 'Offer',
      'category' => 'Tuition per semester (estimated)',
      'price' => (string) $programme->tuition_per_semester,
      'priceCurrency' => $programme->tuition_currency,
    ] : null,
  ]);
@endphp
<script type="application/ld+json">{!! json_encode($courseNode, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

@php $applyUrl = \App\Support\University::applyUrl(); @endphp

<section class="page-hero">
  <span class="hero-mark" aria-hidden="true">{{ $programme->award_code ?: strtoupper(substr($programme->level, 0, 3)) }}</span>
  <div class="wrap">
    <p class="eyebrow">{{ $programme->levelLabel() }}@if($programme->faculty) · {{ $programme->faculty->name }}@endif</p>
    <h1>{{ $programme->name }}@if($programme->award_code) <span style="color:var(--gold-d);font-weight:500;">({{ $programme->award_code }})</span>@endif</h1>
    <div class="trust-chips">
      @if($programme->duration)<span><i class="fas fa-clock" aria-hidden="true"></i> {{ $programme->duration }}</span>@endif
      @if($programme->intake_months)<span><i class="fas fa-calendar-days" aria-hidden="true"></i> {{ implode(' & ', $programme->intake_months) }} intakes</span>@endif
      @if($programme->study_modes)<span><i class="fas fa-sun" aria-hidden="true"></i> {{ implode(' / ', $programme->study_modes) }}</span>@endif
      <span><i class="fas fa-certificate" aria-hidden="true"></i> NCHE accredited university</span>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="course-layout">
      <div class="main">
        @if($programme->description)
          <div class="sec-idx">01 <span>About the programme</span></div>
          <div class="page" style="max-width:none;margin:0 0 30px;">
            @foreach(preg_split('/\n{2,}|\r\n\r\n/', trim(strip_tags($programme->description))) as $paragraph)
              @if(trim($paragraph) !== '')<p>{{ trim($paragraph) }}</p>@endif
            @endforeach
          </div>
        @endif

        <div class="sec-idx">{{ $programme->description ? '02' : '01' }} <span>Entry requirements</span></div>
        <div class="feature-box" style="margin-bottom:30px;">
          <p style="margin-bottom:0;">{{ $programme->entry_requirements ?: 'Contact the admissions office for the entry requirements of this programme.' }}</p>
        </div>

        @if($programme->career_prospects)
          <div class="sec-idx">03 <span>Career prospects</span></div>
          <div class="page" style="max-width:none;margin:0 0 30px;">
            <p>{{ $programme->career_prospects }}</p>
          </div>
        @endif

        @if($programme->faculty)
          <div class="feature-box">
            <div class="sub">Taught by</div>
            <h3 style="margin-bottom:6px;">{{ $programme->faculty->name }}</h3>
            @if($programme->faculty->tagline)<p>{{ ucfirst($programme->faculty->tagline) }}.</p>@endif
            <a href="{{ route('faculties.show', $programme->faculty) }}" wire:navigate class="link" style="color:var(--pri);font-weight:600;">
              Visit the faculty <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        @endif
      </div>

      {{-- The decision box: everything needed to say yes, in one column. --}}
      <aside class="buy-box" aria-label="Apply for this programme">
        <div class="price" style="font-size:20px;">
          {{ $programme->tuitionDisplay() ?? 'Fees: contact the Graduate School' }}
        </div>
        @if($programme->tuition_note)
          <p style="font-size:12px;color:var(--tx3);margin-bottom:12px;">{{ $programme->tuition_note }}</p>
        @endif
        <ul class="includes">
          <li>{{ $programme->levelLabel() }}</li>
          @if($programme->duration)<li>{{ $programme->duration }} of study</li>@endif
          @if($programme->intake_months)<li>{{ implode(' and ', $programme->intake_months) }} intakes</li>@endif
          <li>Application fee UGX 50,000</li>
          <li>Kakeeka (Kampala) or Kirumba (Masaka) campus</li>
        </ul>
        <a href="{{ $applyUrl }}" rel="external" class="btn gold lg" style="width:100%;justify-content:center;">
          Apply on the E-Portal <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
        <a href="{{ \App\Support\University::contacts()['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener"
           class="btn ghost" style="width:100%;justify-content:center;margin-top:10px;">
          <i class="fab fa-whatsapp" aria-hidden="true"></i> Ask about this programme
        </a>
        <p class="money-comfort">Admissions: {{ \App\Support\University::contacts()['admissions_email'] ?? 'admissions@mru.ac.ug' }}</p>
      </aside>
    </div>
  </div>
</section>

@if($related->isNotEmpty())
<section class="band-surface tex-grid">
  <div class="wrap">
    <div class="sec-head left">
      <h2>Related programmes</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
      @foreach($related as $relatedProgramme)
        @include('university.partials.programme-card', ['programme' => $relatedProgramme])
      @endforeach
    </div>
  </div>
</section>
@endif

@include('university.partials.cta-band')

@endsection
