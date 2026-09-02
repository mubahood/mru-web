@extends('layouts.marketing')
@section('title', 'Fees Structure | Muteesa I Royal University')
@section('desc', 'Tuition fees per semester at Muteesa I Royal University, listed by faculty and programme, with the application and processing fees and mobile money payment codes.')

@push('styles')
<style>
  .fees-scroll{overflow-x:auto;border:1px solid var(--line);background:var(--surface);}
  .fees-table{width:100%;border-collapse:collapse;font-size:13px;min-width:560px;}
  .fees-table th{text-align:left;padding:11px 14px;background:var(--pri);color:#fff;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;}
  .fees-table td{padding:11px 14px;border-bottom:1px solid var(--line);color:var(--tx2);vertical-align:top;}
  .fees-table tr:last-child td{border-bottom:0;}
  .fees-table tr:hover td{background:var(--gold-soft);}
  .fees-table td a{font-weight:600;color:var(--pri);}
  .fees-table td a:hover{color:var(--gold-d);}
  .fees-table .amt{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:600;color:var(--tx);white-space:nowrap;}
</style>
@endpush

@section('content')

@php
  $n = 0;
  $idx = function () use (&$n) { return str_pad((string) ++$n, 2, '0', STR_PAD_LEFT); };
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Admissions',
  'title' => 'Fees Structure',
  'lead' => "Estimated tuition per semester by faculty — always confirm the current schedule with the Bursar's office.",
  'chips' => array_values(array_filter([
    ! empty($admissions['application_fee']) ? ['fa-coins', 'Application fee '.$admissions['application_fee']] : null,
    ['fa-building-columns', 'Listed by faculty'],
    ['fa-mobile-screen', 'Pay by mobile money'],
  ])),
])

@foreach($faculties as $faculty)
  @continue($faculty->programmes->isEmpty())
  <section @if($loop->iteration % 2 === 0) class="band-surface tex-glow" @endif>
    <div class="wrap">
      <div class="sec-head left">
        <div class="sec-idx">{{ $idx() }} <span>Faculty</span></div>
        <h2>{{ $faculty->name }}</h2>
      </div>
      <div class="fees-scroll" data-rise>
        <table class="fees-table">
          <thead>
            <tr>
              <th scope="col">Programme</th>
              <th scope="col">Level</th>
              <th scope="col">Tuition per semester</th>
            </tr>
          </thead>
          <tbody>
            @foreach($faculty->programmes as $programme)
              <tr>
                <td><a href="{{ route('programmes.show', $programme) }}" wire:navigate>{{ $programme->name }}</a></td>
                <td>{{ $programme->levelLabel() }}</td>
                <td class="amt">{{ $programme->tuitionDisplay() ?? 'Contact the Graduate School' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </section>
@endforeach

<section class="band-surface tex-grid">
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Payment</span></div>
      <h2>How to pay</h2>
    </div>
    <div class="pay-split" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:stretch;">
      <div class="feature-box" data-rise style="margin-bottom:0;">
        <div class="sub">Mobile money codes</div>
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
      </div>
      <div class="feature-box" data-rise style="margin-bottom:0;">
        <div class="sub">One-off fees</div>
        @if(! empty($admissions['application_fee']))
          <p style="margin-bottom:10px;">Application fee: <strong style="color:var(--gold-d);">{{ $admissions['application_fee'] }}</strong></p>
        @endif
        @if(! empty($admissions['processing_fee']))
          <p style="margin-bottom:0;">Processing fee: <strong style="color:var(--gold-d);">{{ $admissions['processing_fee'] }}</strong></p>
        @endif
      </div>
    </div>
    <p data-rise style="margin-top:22px;font-size:12.5px;color:var(--tx3);max-width:720px;">
      Tuition is shown per semester as a guide and may be revised. Programmes listed without a
      figure are priced by the Graduate School — contact them for the current fees schedule.
      Always confirm the current schedule with the Bursar's office before making any payment.
    </p>
  </div>
</section>
@push('styles')<style>@media(max-width:720px){.pay-split{grid-template-columns:1fr !important;}}</style>@endpush

@include('university.partials.cta-band')

@endsection
