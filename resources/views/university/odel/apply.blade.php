@extends('layouts.odel')
@section('title', 'Start with ODEL | Muteesa I Royal University')
@section('desc', 'How to begin studying with Muteesa I Royal University through open, distance and e-learning — the four steps, and what to have ready.')

@section('content')

@include('university.odel.partials.hero', [
  'eyebrow' => 'Start Here',
  'title' => 'Four steps, and the first one is free',
  'lead' => 'The order matters. Most people who struggle with distance study picked the mode
             before they talked to anyone about their week.',
  'trail' => [['label' => 'Start here']],
])

<section style="padding-top:var(--s-6);">
  <div class="wrap" style="max-width:900px;">
    <div class="od-list" data-rise>
      <div class="od-item">
        <p><strong>Work out which mode fits your week.</strong> Six of them, and they are genuinely
           different — one or two units a semester is not the same commitment as an intensive block
           or a learning contract with your employer.
           <a href="{{ route('odel.modes') }}" wire:navigate class="link">Compare the six modes</a></p>
      </div>
      <div class="od-item">
        <p><strong>Ask about the qualification you want.</strong> Not every programme is offered in
           every mode, and which ones are is a matter of record rather than guesswork. Tell us the
           qualification and we will tell you where it stands.
           <a href="{{ route('odel.support') }}" wire:navigate class="link">Ask about a programme</a></p>
      </div>
      <div class="od-item">
        <p><strong>Raise anything you have already studied.</strong> Prior courses, work experience
           and in-house training may count towards credit — but that has to be assessed, and it is
           far easier before you enrol than after.
           <a href="{{ route('odel.credit') }}" wire:navigate class="link">How credit works</a></p>
      </div>
      <div class="od-item">
        <p><strong>Apply.</strong> Enrolment is by physical visit, by post or online, as required by
           the Academic Registrar. Distance students are issued tailored letters by the Registry.</p>
      </div>
    </div>

    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:var(--s-6);">
      <a href="{{ $applyUrl }}" rel="external" class="btn gold lg">Apply now <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
      <a href="{{ route('odel.support') }}" wire:navigate class="btn ghost lg">Talk to someone first</a>
    </div>

    <div class="od-note" data-rise style="margin-top:var(--s-6);">
      <i class="fas fa-circle-question" aria-hidden="true"></i>
      <p><strong>Before you pay anything.</strong> Parts of the ODEL system are running today and
         parts are commitments the Council has approved — this section labels which is which on
         every page. If your decision depends on a particular service being staffed right now, ask
         us and we will tell you plainly where it stands.</p>
    </div>
  </div>
</section>

<section class="band-surface">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Try It First</p>
      <h2>You can start learning today, for free</h2>
      <p>Short online courses need no admission process. They run on the same platform your
         programme would, so they are the cheapest way to find out whether studying this way suits
         you at all.</p>
    </div>
    <a href="{{ route('courses.index') }}" wire:navigate class="btn">Browse the short courses <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
  </div>
</section>

@endsection
