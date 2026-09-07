@extends('layouts.odel')
@section('title', 'Programmes available through ODEL | Muteesa I Royal University')
@section('desc', 'Which Muteesa I Royal University qualifications can be studied part-time, by distance or in another flexible mode.')

@section('content')

@include('university.odel.partials.hero', [
  'eyebrow' => 'Programmes',
  'title' => 'What you can study this way',
  'lead' => 'Not every qualification is offered in every mode. A programme has to pass eight tests
             before it can be taught by distance, so the list is deliberately shorter than the full
             catalogue.',
  'trail' => [['label' => 'Programmes']],
])

<section style="padding-top:var(--s-6);">
  <div class="wrap">
    @if($programmes->isNotEmpty())
      <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(min(300px,100%),1fr));">
        @foreach($programmes as $p)
          <a href="{{ route('programmes.show', $p) }}" wire:navigate class="proj-card" data-rise>
            <span class="client">{{ $p->faculty?->name }}</span>
            <h3>{{ $p->name }}</h3>
            @if($p->duration)<p style="font-size:13px;color:var(--tx2);">{{ $p->duration }}</p>@endif
            <span class="link">Programme details <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
          </a>
        @endforeach
      </div>
    @else
      {{-- The honest empty state. `study_modes` is unset on every programme, so
           there is no list to show — and inventing one would be the single most
           damaging thing this section could do to somebody about to pay fees. --}}
      <div class="feature-box" data-rise style="max-width:760px;">
        <div class="sub"><i class="fas fa-circle-info" aria-hidden="true"></i> Being confirmed</div>
        <h3 style="margin-top:0;">We are not going to guess this one</h3>
        <p style="font-size:15px;line-height:1.75;">
          The University teaches {{ $total }} published programmes. Which of them are currently
          offered part-time, by distance or in another flexible mode is a matter of record held by
          the Academic Registrar — and that record is not yet reflected here. Rather than publish a
          list we cannot stand behind, this page will stay empty until it is confirmed.
        </p>
        <p style="font-size:15px;line-height:1.75;">
          If you have a specific qualification in mind, ask. You will get a definite answer for
          that programme faster than any list could give you.
        </p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:var(--s-4);">
          <a href="{{ route('odel.support') }}" wire:navigate class="btn gold">Ask about a programme <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
          <a href="{{ route('programmes.index') }}" wire:navigate class="btn ghost">Browse all {{ $total }} programmes</a>
        </div>
      </div>
    @endif

    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(min(260px,100%),1fr));margin-top:var(--s-6);">
      <a href="{{ route('courses.index') }}" wire:navigate class="proj-card" data-rise>
        <span class="client">Available now</span>
        <h3>Short online courses</h3>
        <p>Self-paced courses with verifiable certificates, open to anyone — no admission process,
           and the quickest way to find out how studying online with MRU actually feels.</p>
        <span class="link">Browse courses <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
      <a href="{{ route('odel.credit') }}" wire:navigate class="proj-card" data-rise>
        <span class="client">Before you enrol</span>
        <h3>Credit for what you know</h3>
        <p>Prior study, work experience and in-house training may already count towards a
           qualification.</p>
        <span class="link">How credit works <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
    </div>
  </div>
</section>

@endsection
