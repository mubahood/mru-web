@extends('layouts.marketing')
@section('title', 'How to Apply | Muteesa I Royal University')
@section('desc', 'Apply to Muteesa I Royal University in seven steps — create an E-Portal account, fill the form, upload documents, pay the application fee by mobile money, and track your admission.')

@section('content')

@php
  $applyUrl = \App\Support\University::applyUrl();
  $wa = $contacts['whatsapp_link'] ?? '#';
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Admissions',
  'title' => 'How to Apply',
  'lead' => $admissions['deadline_note'] ?? 'Applications are open for the next intake.',
  'chips' => array_values(array_filter([
    ! empty($admissions['application_fee']) ? ['fa-coins', 'Application fee '.$admissions['application_fee']] : null,
    ['fa-laptop', 'Apply online on the E-Portal'],
    ['fa-file-lines', 'Paper forms at either campus'],
  ])),
])

@if(! empty($admissions['steps']))
<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">The Steps</p>
      <h2>Seven steps, start to admission</h2>
      <p>The whole application happens on the E-Portal — most applicants finish in under twenty minutes.</p>
    </div>
    <div class="steps" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));margin-top:0;">
      @foreach($admissions['steps'] as $step)
        <div class="step" data-rise>
          <div class="n">{{ $loop->iteration }}</div>
          <h4>{{ $step['title'] ?? '' }}</h4>
          <p>{{ $step['desc'] ?? '' }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<section class="band-surface tex-glow">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Fees &amp; Payment</p>
      <h2>What it costs, and how to pay</h2>
    </div>
    <div class="apply-fees-split" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:stretch;">
      <div class="feature-box" data-rise style="margin-bottom:0;">
        <div class="sub">The two fees</div>
        @if(! empty($admissions['application_fee']))
          <p style="margin-bottom:10px;">Application fee: <strong style="color:var(--gold-d);">{{ $admissions['application_fee'] }}</strong></p>
        @endif
        @if(! empty($admissions['processing_fee']))
          <p style="margin-bottom:0;">Processing fee: <strong style="color:var(--gold-d);">{{ $admissions['processing_fee'] }}</strong></p>
        @endif
      </div>
      <div class="feature-box" data-rise style="margin-bottom:0;">
        <div class="sub">Pay by mobile money</div>
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
          <tbody>
            @foreach($admissions['payment_codes'] ?? [] as $code)
              <tr>
                <td style="padding:9px 12px;border:1px solid var(--line-2);color:var(--tx2);">{{ $code['provider'] ?? '' }}</td>
                <td style="padding:9px 12px;border:1px solid var(--line-2);font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:700;color:var(--gold-d);">{{ $code['code'] ?? '' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
        <p style="margin:12px 0 0;font-size:12px;color:var(--tx3);">Dial the code and pay using the payment reference from your E-Portal account.</p>
      </div>
    </div>
  </div>
</section>
@push('styles')<style>@media(max-width:720px){.apply-fees-split{grid-template-columns:1fr !important;}}</style>@endpush

<section>
  <div class="wrap" style="text-align:center;">
    <div class="sec-head">
      <p class="eyebrow">Start Now</p>
      <h2>Ready? The portal is open</h2>
    </div>
    <div class="ctas" data-rise style="justify-content:center;">
      <a href="{{ $applyUrl }}" rel="external" class="btn gold lg">
        Apply on the E-Portal <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
      <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn ghost lg">
        <i class="fab fa-whatsapp" aria-hidden="true"></i> Ask on WhatsApp
      </a>
    </div>
    <p data-rise style="margin-top:22px;font-size:13px;color:var(--tx2);max-width:560px;margin-left:auto;margin-right:auto;">
      Prefer paper? Application forms are also available from either campus and on the
      <a href="{{ route('downloads') }}" wire:navigate style="font-weight:600;color:var(--pri);">downloads page</a>.
    </p>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
