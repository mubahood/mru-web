{{-- One person in a people grid. Expects $person (StaffMember). --}}
<div class="proj-card" data-rise style="text-align:center;align-items:center;">
  @if($person->photo)
    <img src="{{ asset('storage/'.$person->photo) }}" alt="{{ $person->name }}" loading="lazy" decoding="async"
         style="width:110px;height:110px;object-fit:cover;object-position:top;border:2px solid var(--gold);">
  @else
    <span style="width:110px;height:110px;display:flex;align-items:center;justify-content:center;background:var(--pri-soft);color:var(--pri);font-size:34px;" aria-hidden="true">
      <i class="fas fa-user"></i>
    </span>
  @endif
  <h3 style="font-size:14.5px;">{{ $person->name }}</h3>
  <span class="client">{{ $person->title }}</span>
  @if($person->department && $person->staff_role !== 'council')
    <p style="font-size:12px;">{{ $person->department }}</p>
  @endif
  @if($person->email)
    <p style="font-size:12px;"><a class="link" href="mailto:{{ $person->email }}">{{ $person->email }}</a></p>
  @endif
</div>
