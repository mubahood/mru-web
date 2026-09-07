@extends('layouts.odel')
@section('title', 'ODEL support and contacts | Muteesa I Royal University')
@section('desc', 'Who to contact about open, distance and e-learning at Muteesa I Royal University, and what support a distance student can expect.')

@section('content')

@include('university.odel.partials.hero', [
  'eyebrow' => 'Support',
  'title' => 'Who to talk to',
  'lead' => 'Studying at a distance should not mean being on your own. Here is who to ask, and
             what you are entitled to expect from them.',
  'trail' => [['label' => 'Support']],
])

<section style="padding-top:var(--s-6);">
  <div class="wrap">
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(min(280px,100%),1fr));">
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-envelope" aria-hidden="true"></i></span>
        <h3>Academic Registrar's Office</h3>
        <p>Enrolment, registration, assessment and your academic record — the office the policies
           name for distance students.</p>
        <p><a href="mailto:{{ $contacts['email'] ?? 'aro@mru.ac.ug' }}" class="link">{{ $contacts['email'] ?? 'aro@mru.ac.ug' }}</a></p>
      </div>
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-phone" aria-hidden="true"></i></span>
        <h3>By phone</h3>
        <p>The University switchboard will route you to admissions or to a faculty.</p>
        <p><a href="tel:{{ str_replace(' ', '', $contacts['phone'] ?? '+256200903000') }}" class="link">{{ $contacts['phone'] ?? '+256 200 903 000' }}</a></p>
      </div>
      <div class="card" data-rise>
        <span class="ic"><i class="fab fa-whatsapp" aria-hidden="true"></i></span>
        <h3>On WhatsApp</h3>
        <p>The University's WhatsApp group — the quickest way to ask a short question.</p>
        <p><a href="{{ ($contacts['whatsapp_link'] ?? null) ?: \App\Support\University::WHATSAPP_GROUP }}" target="_blank" rel="noopener" class="link">Join the group <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a></p>
      </div>
    </div>
  </div>
</section>

<section class="band-surface">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Support You Are Owed</p>
      <h2>Not a favour — a commitment</h2>
      <p>The Distance Learning Policy requires that support staff have the right skills and receive
         appropriate training, and that the support you get meets the University's expectations for
         any programme leading to its awards.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(min(300px,100%),1fr));">
      <div class="card" data-rise>
        <h3>A named contact</h3>
        <p>Local or remote, reachable by email, telephone or post, who gives you constructive
           feedback on your academic performance and authoritative guidance on your progression.</p>
        <span class="od-clause">DLP §7.1.3(i)</span>
      </div>
      <div class="card" data-rise>
        <h3>Other learners</h3>
        <p>Regular opportunities for discussion with the people on your programme — both to learn
           together and to feed into the quality assurance of the course.</p>
        <span class="od-clause">DLP §7.1.3(ii)</span>
      </div>
      <div class="card" data-rise>
        <h3>A way to be heard</h3>
        <p>Proper opportunities to give formal feedback on your experience of the programme.</p>
        <span class="od-clause">DLP §7.1.3(iii)</span>
      </div>
    </div>
    <div style="margin-top:var(--s-5);">
      <a href="{{ route('odel.what-you-get') }}" wire:navigate class="link">All twelve entitlements <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(min(260px,100%),1fr));">
      <a href="{{ route('contact') }}" wire:navigate class="proj-card" data-rise>
        <span class="client">General</span><h3>Contact the University</h3>
        <p>Both campuses, departmental contacts, and a form that reaches a person.</p>
        <span class="link">Contact <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
      <a href="{{ route('odel.faqs') }}" wire:navigate class="proj-card" data-rise>
        <span class="client">Quick answers</span><h3>Questions</h3>
        <p>The things people ask before they enrol.</p>
        <span class="link">ODEL questions <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
      <a href="{{ $links['library'] ?? route('library') }}" rel="external" class="proj-card" data-rise>
        <span class="client">Research</span><h3>Library portal</h3>
        <p>E-resources and databases from wherever you are.</p>
        <span class="link">Open the library <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></span>
      </a>
    </div>
  </div>
</section>

@endsection
