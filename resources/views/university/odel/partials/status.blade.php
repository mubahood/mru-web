{{-- Whether a claim is running today, built but not yet in routine use, or
     committed by policy. The section's central promise to the reader, so it is
     a component rather than a sentence. --}}
@php
  $s = $status ?? '';
  [$cls, $icon, $why] = match ($s) {
    \App\Support\Odel::IN_PLACE => ['is-place', 'fa-circle-check', 'Running today, and in routine use'],
    \App\Support\Odel::BUILT    => ['is-built', 'fa-screwdriver-wrench', 'The software exists and works, but is not yet used in production'],
    default                     => ['is-committed', 'fa-file-signature', 'Approved by the University Council; not asserted here as already built or staffed'],
  };
@endphp
<span class="od-status {{ $cls }}" title="{{ $why }}">
  <i class="fas {{ $icon }}" aria-hidden="true"></i>
  {{ \App\Support\Odel::statusLabel($s) }}
</span>
