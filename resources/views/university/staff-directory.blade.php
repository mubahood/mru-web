@extends('layouts.marketing')
@section('title', 'Staff Directory | Muteesa I Royal University')
@section('desc', 'Find academic and administrative staff of Muteesa I Royal University — search by name, title or department, or filter by faculty.')

@section('content')

@include('university.partials.page-hero', [
  'eyebrow' => 'Staff',
  'title' => 'Staff Directory',
  'lead' => 'The people who teach, research and run the University — search by name, title or department, or browse a faculty at a time.',
  'mark' => 'Staff',
])

<section>
  <div class="wrap">
    <form method="GET" action="{{ route('staff.directory') }}" class="filter-bar" role="search">
      <label for="sd-q" class="sr-only">Search staff by name, title or department</label>
      <input type="text" id="sd-q" name="q" value="{{ $q }}" maxlength="120"
             placeholder="Search by name, title or department">
      <label for="sd-faculty" class="sr-only">Filter by faculty</label>
      <select id="sd-faculty" name="faculty">
        <option value="">All faculties</option>
        @foreach($faculties as $faculty)
          <option value="{{ $faculty->id }}" @selected($facultyId === $faculty->id)>{{ $faculty->name }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn"><i class="fas fa-magnifying-glass" aria-hidden="true"></i> Search</button>
    </form>

    @if($staff->isEmpty())
      <div class="feature-box" data-rise style="text-align:center;">
        <div class="sub">No results</div>
        <h3>No staff match that search.</h3>
        <p>Try a shorter name, a different spelling, or clear the filters to browse everyone.</p>
        <a href="{{ route('staff.directory') }}" wire:navigate class="btn ghost">Clear search</a>
      </div>
    @else
      <p style="font-size:12.5px;color:var(--tx3);margin-bottom:16px;">
        {{ $staff->total() }} {{ \Illuminate\Support\Str::plural('staff member', $staff->total()) }}
        @if($q !== '' || $facultyId) matching your search — <a href="{{ route('staff.directory') }}" wire:navigate class="link" style="color:var(--pri);font-weight:600;">clear</a>@endif
      </p>
      <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
        @foreach($staff as $member)
          @include('university.partials.person-card', ['person' => $member])
        @endforeach
      </div>
      <div style="margin-top:26px;">{{ $staff->links() }}</div>
    @endif
  </div>
</section>

@include('university.partials.cta-band')

@endsection
