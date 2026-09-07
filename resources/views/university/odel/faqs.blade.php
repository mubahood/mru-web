@extends('layouts.odel')
@section('title', 'ODEL questions | Muteesa I Royal University')
@section('desc', 'Answers to what people ask before enrolling in open, distance or e-learning at Muteesa I Royal University — quality, credit, attendance, fees and support.')

@php
  /* Answers drawn from the two policies, so the page cannot drift away from
     what the University has actually approved. Each carries its clause. */
  $faqs = [
    ['q' => 'Is a distance qualification worth the same as an on-campus one?',
     'a' => 'Yes. The policy requires a distance programme to be "of equal quality to any other programme or module leading to a MRU award", and it is validated in exactly the same way. One of the eight approval tests is explicitly that it be comparable in quality to the on-campus version.',
     'c' => 'DLP §6.0, §5.0(vi)'],
    ['q' => 'Do I ever have to come to campus?',
     'a' => 'Possibly, for induction — the policy says students may be required to attend a formal induction at an MRU campus. Intensive-mode units also carry compulsory attendance, because they have comparatively fewer classes. Distance study itself does not require you to be on campus.',
     'c' => 'DLP §6.2, FLP §8.3'],
    ['q' => 'Can I get credit for work I have already done?',
     'a' => 'The Flexible Learning Policy provides for accreditation of prior experiential or certified learning, and for recognising in-house training by allocating University credit to it. What can be assessed depends on the programme, so ask the Academic Registrar\'s Office about yours.',
     'c' => 'FLP §5.1(c)–(d), §4(a)'],
    ['q' => 'How much does it cost?',
     'a' => 'Tuition fees are set by the University Council, not by the mode of study. Distance students should check the University website and the Admissions Office for current figures.',
     'c' => 'DLP §7.1.6'],
    ['q' => 'Can I switch from full-time to part-time?',
     'a' => 'Yes. A full-time student who cannot commit enough time may change to part-time study — but you must withdraw before a set date to avoid an academic or financial penalty.',
     'c' => 'FLP §8.1'],
    ['q' => 'Can I study one subject without doing a whole degree?',
     'a' => 'Yes. You may be able to take an individual course unit as a visiting or cross-institutional student, for professional or personal development. It may even count towards a degree elsewhere, and it is a way to test whether the study suits you before committing.',
     'c' => 'FLP §8.5'],
    ['q' => 'How are exams handled if I am not on campus?',
     'a' => 'All communication runs through the secured learning management system, including submission of assignments and access to grades. The policy assigns secure testing environments to a DL Assessment and Testing Unit in the Academic Registrar\'s Office; instructors using high-stakes examinations coordinate with it.',
     'c' => 'DLP §8.3, §8.2'],
    ['q' => 'What support will I actually get?',
     'a' => 'A named contact who gives you feedback on your performance and guidance on progression, a schedule of tutorials or web-based support, regular opportunities to talk with other learners, and a way to give formal feedback. Twelve entitlements are listed in full on this site.',
     'c' => 'DLP §7.1'],
    ['q' => 'Which programmes can I take this way?',
     'a' => 'That record is held by the Academic Registrar and is not yet published here. Rather than guess, we ask you to tell us the qualification you have in mind — you will get a definite answer for it faster than a list could give you.',
     'c' => null],
  ];
@endphp

@push('jsonld')
<script type="application/ld+json">{!! json_encode([
  '@context' => 'https://schema.org', '@type' => 'FAQPage',
  'mainEntity' => array_map(fn ($f) => [
    '@type' => 'Question', 'name' => $f['q'],
    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
  ], $faqs),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

@include('university.odel.partials.hero', [
  'eyebrow' => 'Questions',
  'title' => 'What people ask before they enrol',
  'lead' => 'Answered from the University\'s own policies, with the clause beside each one so you
             can check it.',
  'trail' => [['label' => 'Questions']],
])

<section style="padding-top:var(--s-6);">
  <div class="wrap" style="max-width:860px;">
    @foreach($faqs as $f)
      <details class="feature-box" data-rise style="margin-bottom:12px;">
        <summary style="cursor:pointer;font-weight:600;font-size:16px;color:var(--pri);list-style:none;display:flex;justify-content:space-between;gap:14px;align-items:center;">
          {{ $f['q'] }}
          <i class="fas fa-chevron-down" aria-hidden="true" style="font-size:12px;color:var(--od);flex-shrink:0;"></i>
        </summary>
        <p style="margin:12px 0 0;font-size:14.5px;line-height:1.75;color:var(--tx2);">{{ $f['a'] }}</p>
        @if($f['c'])<span class="od-clause" style="display:block;margin-top:10px;">{{ $f['c'] }}</span>@endif
      </details>
    @endforeach

    <div class="od-note" data-rise style="margin-top:var(--s-5);">
      <i class="fas fa-comments" aria-hidden="true"></i>
      <p>Not here? <a href="{{ route('odel.support') }}" class="link" style="color:#6B4A0C;font-weight:600;">Ask us directly</a> —
         and if the answer turns out to be one more people need, it will end up on this page.</p>
    </div>
  </div>
</section>

@endsection
