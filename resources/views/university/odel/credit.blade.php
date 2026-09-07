@extends('layouts.odel')
@section('title', 'Credit for what you already know | ODEL, Muteesa I Royal University')
@section('desc', 'Recognition of prior and experiential learning, short awards and the MRU Certificate of Credit, under Muteesa I Royal University\'s Flexible Learning Policy.')

@section('content')

@include('university.odel.partials.hero', [
  'eyebrow' => 'Credit',
  'title' => 'You may already have done part of this',
  'lead' => 'The Flexible Learning Policy is unusually direct about it: "if the learner knows it
             and can show it, he/she can use it to earn credit." Where you learned it — an earlier
             course, your job, training — does not matter.',
  'trail' => [['label' => 'Credit']],
])

<section style="padding-top:var(--s-6);">
  <div class="wrap">
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(min(280px,100%),1fr));">
      @foreach($promises as $p)
        <div class="card" data-rise>
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;margin-bottom:8px;">
            <span class="od-clause">{{ $p['clause'] }}</span>
            @include('university.odel.partials.status', ['status' => $p['status']])
          </div>
          <h3>{{ $p['title'] }}</h3>
          <p>{{ $p['body'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

<section class="band-surface">
  <div class="wrap" style="max-width:920px;">
    <div class="sec-head left">
      <p class="eyebrow">Where Credit Can Come From</p>
      <h2>The scope of flexible provision</h2>
      <p>Section 5.1 of the Flexible Learning Policy lists what counts as flexible learning
         provision at the University.</p>
    </div>
    <div class="od-list" data-rise>
      <div class="od-item"><p>Bespoke or tailored courses built for a particular employer or sector.</p><span class="od-clause">FLP §5.1(a)</span></div>
      <div class="od-item"><p>Small, unit-credit-based learning opportunities.</p><span class="od-clause">FLP §5.1(b)</span></div>
      <div class="od-item"><p>Accreditation of prior experiential or certified learning.</p><span class="od-clause">FLP §5.1(c)</span></div>
      <div class="od-item"><p>Recognition of in-house training, by allocating University credit to it.</p><span class="od-clause">FLP §5.1(d)</span></div>
      <div class="od-item"><p>Learning situated in a workplace, or built around a work situation.</p><span class="od-clause">FLP §5.1(e)</span></div>
      <div class="od-item"><p>Individually negotiated learning contracts, for one learner or a cohort.</p><span class="od-clause">FLP §5.1(f)</span></div>
      <div class="od-item"><p>Distance delivery courses.</p><span class="od-clause">FLP §5.1(g)</span></div>
    </div>
  </div>
</section>

<section>
  <div class="wrap" style="max-width:920px;">
    <div class="sec-head left">
      <p class="eyebrow">Short Awards</p>
      <h2>The Certificate of Credit</h2>
    </div>
    <div class="feature-box" data-rise>
      <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:10px;">
        <span class="od-clause">FLP §10.0</span>
        @include('university.odel.partials.status', ['status' => \App\Support\Odel::COMMITTED])
      </div>
      <p style="margin:0;font-size:15px;line-height:1.75;">
        The policy commits the University to introducing a range of short qualifications, smaller
        in credit terms than a traditional course, so that continuing professional development,
        skills updating and work-based learning can carry recognised awards. Modules at any level,
        and at different levels, may contribute to a <strong>MRU Certificate of Credit</strong>.
      </p>
    </div>
    <div class="od-note" data-rise style="margin-top:var(--s-5);">
      <i class="fas fa-circle-info" aria-hidden="true"></i>
      <p>This is a Council commitment rather than a catalogue. Before counting on credit for prior
         learning, ask the Academic Registrar's Office what can be assessed for your specific
         programme — the answer depends on the qualification, not on this page.</p>
    </div>
  </div>
</section>

@endsection
