@extends('layouts.odel')
@section('title', $mode['name'].' — ODEL study modes | Muteesa I Royal University')
@section('desc', $mode['summary'])

@section('content')

@include('university.odel.partials.hero', [
  'eyebrow' => 'Study Mode',
  'title' => $mode['name'],
  'lead' => $mode['summary'],
  'trail' => [['label' => 'Study modes', 'url' => route('odel.modes')], ['label' => $mode['name']]],
])

<section style="padding-top:var(--s-5);">
  <div class="wrap">
    <div class="grid" style="grid-template-columns:minmax(0,2fr) minmax(0,1fr);gap:32px;align-items:start;">
      <div>
        <div class="feature-box" data-rise style="margin-bottom:var(--s-5);">
          <div class="sub"><i class="fas fa-user" aria-hidden="true"></i> Who this suits</div>
          <p style="font-size:16px;color:var(--tx);margin:0;">{{ $mode['suits'] }}</p>
        </div>

        <div class="sec-head left" style="margin-bottom:var(--s-4);">
          <h2>How it works</h2>
        </div>
        <div class="od-list" data-rise>
          @foreach($mode['detail'] as $line)
            <div class="od-item"><p>{{ $line }}</p><span class="od-clause">{{ $mode['clause'] }}</span></div>
          @endforeach
        </div>

        @if(!empty($mode['watch']))
          <div class="od-note" data-rise style="margin-top:var(--s-5);">
            <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
            <p><strong>Worth knowing.</strong> {{ $mode['watch'] }}</p>
          </div>
        @endif
      </div>

      <aside>
        <div class="feature-box" data-rise>
          <div class="sub">Status</div>
          @include('university.odel.partials.status', ['status' => $mode['status']])
          <p style="font-size:13px;color:var(--tx2);margin:12px 0 0;line-height:1.6;">
            @if($mode['status'] === \App\Support\Odel::IN_PLACE)
              This mode is described in policy as current provision.
            @else
              The Council has approved this mode. Parts of the support around it are established by
              the policy rather than described as already running — check with us before enrolling.
            @endif
          </p>
          <p style="font-size:12px;color:var(--tx3);margin:12px 0 0;">
            Source: <span class="od-clause">{{ $mode['clause'] }}</span>
          </p>
        </div>

        <div class="feature-box" data-rise style="margin-top:18px;">
          <div class="sub">Other modes</div>
          @foreach($others as $k => $m)
            <a href="{{ route('odel.mode', $k) }}" wire:navigate
               style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--line);font-size:14px;font-weight:600;color:var(--pri);">
              <i class="fas {{ $m['icon'] }}" aria-hidden="true" style="width:18px;color:var(--od);"></i> {{ $m['name'] }}
            </a>
          @endforeach
        </div>

        <div style="margin-top:18px;">
          <a href="{{ route('odel.apply') }}" wire:navigate class="btn gold" style="width:100%;justify-content:center;">
            Start here <i class="fas fa-arrow-right" aria-hidden="true"></i>
          </a>
        </div>
      </aside>
    </div>
  </div>
</section>

@endsection
