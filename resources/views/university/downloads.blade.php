@extends('layouts.marketing')
@section('title', 'Downloads | Muteesa I Royal University')
@section('desc', 'Official documents of Muteesa I Royal University — the strategic plan, academic almanac, application forms, student guides, research publications and university policies.')

@section('content')

@php
  /* Institutional first, policies last; a group not named here still renders,
     after the named ones, rather than disappearing. */
  $order = ['Institutional', 'Academic', 'Admissions', 'Students', 'Research', 'Culture', 'Policies'];
  $sorted = $groups->sortBy(function ($docs, $name) use ($order) {
      $at = array_search($name, $order, true);
      return $at === false ? count($order) : $at;
  });
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Downloads',
  'title' => 'Downloads',
  'lead' => 'Official university documents: plans, policies, forms and guides.',
  'trail' => [['label' => 'Academics', 'url' => route('programmes.index')], ['label' => 'Downloads']],
])

<section style="padding:22px 0 0;">
  <div class="wrap">
    <p data-rise style="font-size:13px;color:var(--tx2);">
      <i class="fas fa-circle-info" aria-hidden="true" style="color:var(--gold-d);"></i>
      Missing a document? Contact
      <a href="mailto:info@mru.ac.ug" class="link" style="color:var(--pri);font-weight:600;">info@mru.ac.ug</a>.
    </p>
  </div>
</section>

@forelse($sorted as $group => $docs)
<section class="{{ $loop->even ? 'band-surface tex-grid' : '' }}" style="padding:34px 0;">
  <div class="wrap">
    <div class="sec-head left" style="margin-bottom:16px;">
      <p class="eyebrow">{{ $docs->count() }} {{ \Illuminate\Support\Str::plural('document', $docs->count()) }}</p>
      <h2>{{ $group }}</h2>
    </div>
    <div data-rise style="border:1px solid var(--line);background:var(--surface);">
      @foreach($docs as $doc)
        <a class="link" href="{{ asset('storage/'.$doc['file']) }}" target="_blank" rel="noopener" download
           style="display:flex;align-items:center;gap:12px;padding:12px 16px;font-size:13.5px;font-weight:500;color:var(--tx);{{ $loop->last ? '' : 'border-bottom:1px solid var(--line);' }}">
          <i class="fas fa-file-pdf" aria-hidden="true" style="color:var(--gold-d);font-size:15px;flex-shrink:0;"></i>
          <span style="flex:1;min-width:0;">{{ $doc['title'] }}</span>
          <i class="fas fa-download" aria-hidden="true" style="font-size:11px;color:var(--tx3);flex-shrink:0;"></i>
        </a>
      @endforeach
    </div>
  </div>
</section>
@empty
<section>
  <div class="wrap">
    <p class="lead">Documents are being uploaded. Please check back shortly.</p>
  </div>
</section>
@endforelse

@include('university.partials.cta-band')

@endsection
