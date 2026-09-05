@extends('layouts.marketing')
@section('title', 'International Students | Muteesa I Royal University')
@section('desc', 'International admissions at Muteesa I Royal University — entry requirements for students from outside Uganda, English proficiency, and support with accommodation and arrival.')

@section('content')

@php
  $admissionsEmail = $contacts['admissions_email'] ?? ($contacts['email'] ?? 'admissions@mru.ac.ug');
  $intlRequirements = collect($admissions['requirements']['international'] ?? []);
  $english = $intlRequirements->first(fn ($item) => \Illuminate\Support\Str::startsWith($item, 'English'));
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Admissions',
  'title' => 'International Students',
  'lead' => 'Students from across East Africa and beyond are welcome at MRU.',
  'chips' => [
    ['fa-earth-africa', 'Applicants from every country'],
    ['fa-passport', 'Valid passport required'],
    ['fa-language', 'English-taught programmes'],
  ],
  'photo' => asset('storage/university/hero/hero-international-1100.jpg'),
  'photoAlt' => 'International visitors with Muteesa I Royal University staff outside a campus building',
  'trail' => [['label' => 'Admissions', 'url' => route('admissions.index')], ['label' => 'International students']],
])

@if($intlRequirements->isNotEmpty())
<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Requirements</p>
      <h2>What international applicants need</h2>
    </div>
    <div class="feature-box" data-rise>
      <div class="sub"><i class="fas fa-earth-africa" aria-hidden="true"></i> Minimum requirements</div>
      <ul class="outcomes-list" style="grid-template-columns:1fr;gap:12px;">
        @foreach($intlRequirements as $item)
          <li>{{ $item }}</li>
        @endforeach
      </ul>
    </div>
    @if($english)
      <div class="feature-box" data-rise style="border-left:3px solid var(--gold);margin-top:18px;">
        <div class="sub">English proficiency</div>
        <p style="margin-bottom:0;font-weight:600;color:var(--tx);">{{ $english }}</p>
      </div>
    @endif
  </div>
</section>
@endif

<section class="band-surface tex-glow">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Support</p>
      <h2>You won't land here alone</h2>
      <p>From your first email to your first night on campus, there is an office answering.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
      <a href="mailto:{{ $admissionsEmail }}" class="card" data-rise>
        <span class="ic"><i class="fas fa-envelope-open-text" aria-hidden="true"></i></span>
        <h3>Admissions guidance</h3>
        <p>Questions on equivalence, documents or deadlines — write to {{ $admissionsEmail }} and the team walks you through it.</p>
      </a>
      <a href="{{ route('accommodation') }}" wire:navigate class="card" data-rise>
        <span class="ic"><i class="fas fa-bed" aria-hidden="true"></i></span>
        <h3>Accommodation on campus</h3>
        <p>Halls of residence at the university — see the options and book a bed before you travel.</p>
      </a>
      <a href="mailto:{{ $admissionsEmail }}" class="card" data-rise>
        <span class="ic"><i class="fas fa-plane-arrival" aria-hidden="true"></i></span>
        <h3>Arriving in Uganda</h3>
        <p>Contact the International Office through admissions before you fly for airport-to-campus arrival guidance.</p>
      </a>
    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
