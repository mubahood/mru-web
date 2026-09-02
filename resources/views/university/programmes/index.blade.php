@extends('layouts.marketing')
@section('title', 'All Programmes | Muteesa I Royal University')
@section('desc', 'Find your programme at MRU: certificates, diplomas, bachelors and postgraduate study across five faculties. Filter by level, faculty or name.')

@section('content')

@include('university.partials.page-hero', [
  'eyebrow' => 'Programme Finder',
  'title' => 'Find your programme',
  'lead' => 'Filter the full directory by level, faculty or name. Every programme page carries entry requirements, tuition and intakes.',
  'chips' => [['fa-book-open', $programmes->count().' programmes shown'], ['fa-school', 'Five faculties + Graduate School'], ['fa-calendar-days', 'August & January intakes']],
])

<section style="padding-top:30px;">
  <div class="wrap">
    {{-- GET form: the URL is the filter state, so results can be shared. --}}
    <form method="GET" action="{{ route('programmes.index') }}" class="filter-bar" role="search" aria-label="Filter programmes">
      <label class="sr-only" for="pf-q">Search programmes</label>
      <input id="pf-q" type="text" name="q" value="{{ $q }}" placeholder="Search by name or award code — e.g. Business, BIT…">
      <label class="sr-only" for="pf-level">Level</label>
      <select id="pf-level" name="level">
        <option value="">All levels</option>
        @foreach($levels as $value => $label)
          <option value="{{ $value }}" @selected($level === $value)>{{ $label }}</option>
        @endforeach
      </select>
      <label class="sr-only" for="pf-faculty">Faculty</label>
      <select id="pf-faculty" name="faculty">
        <option value="">All faculties</option>
        @foreach($faculties as $faculty)
          <option value="{{ $faculty->id }}" @selected($facultyId === $faculty->id)>{{ $faculty->name }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn sm"><i class="fas fa-filter" aria-hidden="true"></i> Filter</button>
      @if($q !== '' || $level !== '' || $facultyId)
        <a href="{{ route('programmes.index') }}" wire:navigate class="btn ghost sm">Clear</a>
      @endif
    </form>

    @if($programmes->isEmpty())
      <div class="feature-box" style="text-align:center;">
        <h3>No matching programmes found</h3>
        <p>Try a broader search — or ask us on WhatsApp and we will point you to the right programme.</p>
        <a href="{{ \App\Support\University::contacts()['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener" class="btn gold">
          <i class="fab fa-whatsapp" aria-hidden="true"></i> Ask Admissions
        </a>
      </div>
    @else
      <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
        @foreach($programmes as $programme)
          @include('university.partials.programme-card', ['programme' => $programme])
        @endforeach
      </div>
    @endif
  </div>
</section>

@include('university.partials.cta-band')

@endsection
