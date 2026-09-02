@extends('layouts.marketing')
@section('title', 'Admissions FAQs | Muteesa I Royal University')
@section('desc', 'Answers to the questions every MRU applicant asks — application fees, deadlines, intakes, entry requirements, mobile money payment, international admission and who to contact.')

@php
  /* Both the page and the FAQPage JSON-LD render from this one list, so the
     structured data can never drift from what the visitor reads. */
  $admissionsEmail = $contacts['admissions_email'] ?? ($contacts['email'] ?? 'admissions@mru.ac.ug');
  $fee = $admissions['application_fee'] ?? null;
  $processing = $admissions['processing_fee'] ?? null;
  $deadline = $admissions['deadline_note'] ?? null;
  $intakeText = collect($admissions['intakes'] ?? [])
      ->map(fn ($i) => ($i['name'] ?? '').' (applications '.($i['window'] ?? '').'; studies start in '.($i['starts'] ?? '').')')
      ->implode(', and the ');
  $payText = collect($admissions['payment_codes'] ?? [])
      ->map(fn ($p) => ($p['provider'] ?? '').' ('.($p['code'] ?? '').')')
      ->implode(' or ');
  $campuses = collect($contacts['campuses'] ?? []);
  $campusText = $campuses->map(fn ($c) => ($c['name'] ?? '').' in '.($c['location'] ?? ''))->implode(', and ');
  $phone = $contacts['phone'] ?? null;
  $waNumber = $contacts['whatsapp'] ?? null;

  $faqs = array_values(array_filter([
      $fee ? [
        'q' => 'How much is the application fee?',
        'a' => 'The application fee is '.$fee.'.'.($processing ? ' There is also a processing fee of '.$processing.'.' : ''),
      ] : null,
      $deadline ? [
        'q' => 'When do applications close?',
        'a' => $deadline,
      ] : null,
      $intakeText ? [
        'q' => 'Which intakes can I join?',
        'a' => 'MRU admits twice a year: the '.$intakeText.'.',
      ] : null,
      [
        'q' => 'How do I apply?',
        'a' => 'Apply online at eportal.mru.ac.ug: create an account, fill the application form, upload your documents, pay the application fee and submit. Admitted students receive a letter and joining instructions by email and on the portal.',
      ],
      [
        'q' => 'What are the minimum entry requirements?',
        'a' => "Bachelors: UACE with at least 2 principal passes, a relevant diploma of at least 2 years, or an NCHE-recognised professional qualification. Diplomas and certificates: UCE with at least 5 passes. Masters: a bachelor's degree from a recognised university. The full lists are on the Entry Requirements page.",
      ],
      $payText ? [
        'q' => 'How do I pay the application fee?',
        'a' => 'Pay by mobile money — '.$payText.' — using the payment code from your E-Portal account.',
      ] : null,
      [
        'q' => 'Can international students apply?',
        'a' => 'Yes. You will need a valid passport, authenticated UACE or A-Level equivalent qualifications, certified academic transcripts, proof of funds for tuition and living costs, and English proficiency (IELTS 6.0+, TOEFL 79+ iBT, or CAE grade C).',
      ],
      $campusText ? [
        'q' => 'Where are the campuses?',
        'a' => 'MRU teaches at '.($campuses->count() === 2 ? 'two campuses' : $campuses->count().' campuses').': '.$campusText.'.',
      ] : null,
      [
        'q' => 'Can I apply on paper instead of online?',
        'a' => 'Yes. Application forms are available from either campus and on the downloads page — though the E-Portal is the fastest route.',
      ],
      [
        'q' => 'Who do I contact with admissions questions?',
        'a' => 'Email '.$admissionsEmail
               .($phone ? ', call '.$phone : '')
               .($waNumber ? ', or message the admissions team on WhatsApp at '.$waNumber : '').'.',
      ],
  ]));
@endphp

@push('jsonld')
<script type="application/ld+json">{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'FAQPage',
  'mainEntity' => collect($faqs)->map(fn ($f) => [
    '@type' => 'Question',
    'name' => $f['q'],
    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
  ])->values()->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

@php
  $n = 0;
  $idx = function () use (&$n) { return str_pad((string) ++$n, 2, '0', STR_PAD_LEFT); };
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Admissions',
  'title' => 'Admissions FAQs',
  'lead' => 'The questions every applicant asks, answered in one place — and a human on WhatsApp for everything else.',
  'chips' => [
    ['fa-circle-question', count($faqs).' questions answered'],
    ['fa-bolt', 'Straight answers, no jargon'],
  ],
])

<section>
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>FAQs</span></div>
      <h2>Before you ask — it's probably here</h2>
    </div>
    <div style="max-width:760px;" data-rise>
      @foreach($faqs as $faq)
        <div class="faq-item">
          <h3>{{ $faq['q'] }}</h3>
          <p>{{ $faq['a'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

<section class="band-surface tex-glow">
  <div class="wrap" style="text-align:center;">
    <div class="sec-head">
      <div class="sec-idx">{{ $idx() }} <span>Dig Deeper</span></div>
      <h2>The full guides behind these answers</h2>
    </div>
    <div class="ctas" data-rise style="justify-content:center;">
      <a href="{{ route('admissions.apply') }}" wire:navigate class="btn cta">
        <span class="cta-a">How to Apply</span>
        <span class="cta-b" aria-hidden="true">The 7 steps <i class="fas fa-arrow-right"></i></span>
      </a>
      <a href="{{ route('admissions.requirements') }}" wire:navigate class="btn ghost cta">
        <span class="cta-a">Entry Requirements</span>
        <span class="cta-b" aria-hidden="true">Check your level <i class="fas fa-arrow-right"></i></span>
      </a>
      <a href="{{ route('admissions.international') }}" wire:navigate class="btn ghost cta">
        <span class="cta-a">International Students</span>
        <span class="cta-b" aria-hidden="true">Coming from abroad <i class="fas fa-arrow-right"></i></span>
      </a>
    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
