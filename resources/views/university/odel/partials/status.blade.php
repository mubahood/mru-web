{{-- Whether a claim is running today or committed by policy. The section's
     central promise to the reader, so it is a component, not a sentence. --}}
@php $isPlace = ($status ?? '') === \App\Support\Odel::IN_PLACE; @endphp
<span class="od-status {{ $isPlace ? 'is-place' : 'is-committed' }}"
      title="{{ $isPlace ? 'Running today' : 'Approved by the University Council; not asserted here as already staffed and running' }}">
  <i class="fas {{ $isPlace ? 'fa-circle-check' : 'fa-file-signature' }}" aria-hidden="true"></i>
  {{ \App\Support\Odel::statusLabel($status) }}
</span>
