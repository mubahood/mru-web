@extends('layouts.marketing')
@section('title', 'Scholars Directory | MRU Scholar | Muteesa I Royal University')
@section('desc', 'The researchers behind MRU Scholar — academic profiles of Muteesa I Royal University lecturers and their publications.')

@section('content')

@include('university.partials.page-hero', [
  'eyebrow' => 'MRU Scholar',
  'title' => 'Scholars Directory',
  'lead' => 'The researchers behind the repository. Open a profile to see a scholar\'s publications.',
])

<section style="padding-top:30px;">
  <div class="wrap">
    <form method="GET" action="{{ route('scholar.directory') }}" class="filter-bar" role="search" aria-label="Search scholars">
      <label class="sr-only" for="dir-q">Search scholars</label>
      <input id="dir-q" type="text" name="q" value="{{ $q }}" placeholder="Search by name, department or research interest…">
      <button type="submit" class="btn sm"><i class="fas fa-magnifying-glass" aria-hidden="true"></i> Search</button>
      @if($q !== '')
        <a href="{{ route('scholar.directory') }}" wire:navigate class="btn ghost sm">Clear</a>
      @endif
    </form>

    @if($scholars->isEmpty())
      <div class="feature-box" style="text-align:center;">
        <h3>No scholars matched that search</h3>
        <p>Try fewer words, or browse all publications instead.</p>
      </div>
    @else
      <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
        @foreach($scholars as $scholar)
          <a href="{{ route('scholar.profile', $scholar) }}" wire:navigate class="proj-card" data-rise style="text-align:center;align-items:center;">
            @if($scholar->photo)
              <img src="{{ asset('storage/'.$scholar->photo) }}" alt="" loading="lazy" decoding="async"
                   style="width:96px;height:96px;object-fit:cover;object-position:top;border:2px solid var(--gold);border-radius:50%;">
            @else
              <span style="width:96px;height:96px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--pri-soft);color:var(--pri);font-size:30px;" aria-hidden="true">
                <i class="fas fa-user-graduate"></i>
              </span>
            @endif
            <h3 style="font-size:14.5px;">{{ $scholar->displayName() }}</h3>
            @if($scholar->department || $scholar->faculty)
              <span class="client" style="text-transform:none;letter-spacing:0;">{{ $scholar->department ?: $scholar->faculty?->name }}</span>
            @endif
            <span class="link" style="margin-top:auto;">
              {{ $scholar->publications_count }} {{ \Illuminate\Support\Str::plural('publication', $scholar->publications_count) }}
              <i class="fas fa-arrow-right"></i>
            </span>
          </a>
        @endforeach
      </div>
      <div class="pagination">{{ $scholars->links() }}</div>
    @endif
  </div>
</section>

@include('university.partials.cta-band')

@endsection
