@extends('layouts.marketing')
@section('title', $faculty->name.' | Muteesa I Royal University')
@section('desc', \Illuminate\Support\Str::limit(strip_tags($faculty->description ?: $faculty->about ?: $faculty->name.' at Muteesa I Royal University: programmes, departments and staff.'), 150))

@section('content')

@php
  $n = 0;
  $idx = function () use (&$n) { return str_pad((string) ++$n, 2, '0', STR_PAD_LEFT); };
@endphp

<section class="page-hero">
  <span class="hero-mark" aria-hidden="true">{{ $faculty->short_name ?: 'Faculty' }}</span>
  <div class="wrap">
    <p class="eyebrow">Faculties &amp; Schools</p>
    <h1>{{ $faculty->name }}</h1>
    @if($faculty->tagline)<p>{{ ucfirst($faculty->tagline) }}.</p>@endif
    <div class="trust-chips">
      <span><i class="fas fa-book-open" aria-hidden="true"></i> {{ $faculty->programmes->count() }} programmes</span>
      @if($faculty->departments)<span><i class="fas fa-sitemap" aria-hidden="true"></i> {{ count($faculty->departments) }} departments</span>@endif
      @if($faculty->dean)<span><i class="fas fa-user-tie" aria-hidden="true"></i> Dean: {{ $faculty->dean->name }}</span>@endif
    </div>
  </div>
</section>

@if($faculty->about || $faculty->description)
<section>
  <div class="wrap">
    <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:36px;align-items:start;" class="fac-split">
      <div>
        <div class="sec-idx">{{ $idx() }} <span>About the faculty</span></div>
        <div class="page" style="max-width:none;margin:0;">
          @foreach(preg_split('/\n{2,}|\r\n\r\n/', trim(strip_tags($faculty->about ?: $faculty->description))) as $paragraph)
            @if(trim($paragraph) !== '')<p>{{ trim($paragraph) }}</p>@endif
          @endforeach
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:14px;">
        @if($faculty->vision)
          <div class="feature-box" data-rise>
            <div class="sub">Vision</div>
            <p style="margin-bottom:0;">{{ $faculty->vision }}</p>
          </div>
        @endif
        @if($faculty->mission)
          <div class="feature-box" data-rise>
            <div class="sub">Mission</div>
            <p style="margin-bottom:0;">{{ $faculty->mission }}</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
@push('styles')<style>@media(max-width:860px){.fac-split{grid-template-columns:1fr !important;}}</style>@endpush
@endif

@if($faculty->departments)
<section class="band-surface tex-grid">
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Departments</span></div>
      <h2>Inside the faculty</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
      @foreach($faculty->departments as $department)
        <div class="card" data-rise>
          <span class="ic"><i class="fas fa-sitemap" aria-hidden="true"></i></span>
          <h3>{{ $department }}</h3>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<section>
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Programmes</span></div>
      <h2>Study in this faculty</h2>
      <p>Grouped by award level. Every programme page carries its requirements, fees and intakes.</p>
    </div>
    @forelse($programmesByLevel as $level => $programmes)
      <h3 style="font-size:15px;font-weight:600;color:var(--gold-d);margin:22px 0 12px;text-transform:uppercase;letter-spacing:.06em;">{{ $level }}</h3>
      <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
        @foreach($programmes as $programme)
          @include('university.partials.programme-card', ['programme' => $programme])
        @endforeach
      </div>
    @empty
      <p class="lead">Programmes for this faculty are being added.</p>
    @endforelse
  </div>
</section>

@if($faculty->careers)
<section class="band-surface">
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Careers</span></div>
      <h2>Where this faculty takes you</h2>
    </div>
    <div class="pill-row" data-rise>
      @foreach($faculty->careers as $career)
        <span class="pill" style="font-size:13px;padding:8px 14px;">{{ $career }}</span>
      @endforeach
    </div>
  </div>
</section>
@endif

@if($faculty->staff->isNotEmpty())
<section>
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>People</span></div>
      <h2>Staff of the faculty</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(210px,1fr));">
      @foreach($faculty->staff as $person)
        @include('university.partials.person-card', ['person' => $person])
      @endforeach
    </div>
    <div style="margin-top:20px;" data-rise>
      <a href="{{ route('staff.directory', ['faculty' => $faculty->id]) }}" wire:navigate class="link" style="color:var(--pri);font-weight:600;">
        Full staff directory <i class="fas fa-arrow-right"></i>
      </a>
    </div>
  </div>
</section>
@endif

@include('university.partials.cta-band')

@endsection
