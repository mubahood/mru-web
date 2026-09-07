@extends('layouts.odel')
@section('title', 'Who runs ODEL | Muteesa I Royal University')
@section('desc', 'The Senate, the Faculties, the Office of Distance Learning and the Centre for Flexible and Distance Learning — who is answerable for what in Muteesa I Royal University\'s ODEL system.')

@section('content')

@include('university.odel.partials.hero', [
  'eyebrow' => 'Governance',
  'title' => 'Who is answerable for what',
  'lead' => 'Distance provision is not run off to one side. It sits inside the same governance as
             everything else the University teaches — and the policies name exactly who carries
             which part of it.',
  'trail' => [['label' => 'Governance']],
])

<section style="padding-top:var(--s-6);">
  <div class="wrap" style="max-width:960px;">
    @foreach($bodies as $b)
      <div class="feature-box" data-rise style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;gap:14px;align-items:center;flex-wrap:wrap;margin-bottom:8px;">
          <h3 style="margin:0;font-size:18px;">{{ $b['name'] }}</h3>
          @include('university.odel.partials.status', ['status' => $b['status']])
        </div>
        <p style="margin:0 0 10px;font-size:14.5px;line-height:1.7;color:var(--tx2);">{{ $b['role'] }}</p>
        <span class="od-clause">{{ $b['clause'] }}</span>
      </div>
    @endforeach

    <div class="od-note" data-rise style="margin-top:var(--s-5);">
      <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
      <p><strong>Read the labels on this page carefully.</strong> The Senate, the Faculties and
         their committees are standing bodies of the University and govern ODEL as they govern
         everything else. The Office of Distance Learning, the Centre for Flexible and Distance
         Learning and the Assessment and Testing Unit are named and mandated by the two policies —
         the Centre is <em>established by</em> the Flexible Learning Policy rather than described
         as already at work. If your decision depends on one of them being staffed today,
         <a href="{{ route('odel.support') }}" class="link" style="color:#6B4A0C;font-weight:600;">ask us</a>
         rather than assuming.</p>
    </div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(min(260px,100%),1fr));margin-top:var(--s-6);">
      <a href="{{ route('governance') }}" wire:navigate class="proj-card" data-rise>
        <span class="client">University</span>
        <h3>University governance</h3>
        <p>The principal officers, the standing committees and the Council that sit above all of this.</p>
        <span class="link">Governance <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
      <a href="{{ route('downloads') }}" wire:navigate class="proj-card" data-rise>
        <span class="client">Documents</span>
        <h3>Policies and downloads</h3>
        <p>Both ODEL policies, and every other University policy, in full.</p>
        <span class="link">All downloads <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
    </div>
  </div>
</section>

@endsection
