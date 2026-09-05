@extends('layouts.marketing')
@section('title', 'Publications | MRU Scholar | Muteesa I Royal University')
@section('desc', 'Search and download research publications by Muteesa I Royal University academics — journal articles, conference papers, book chapters, theses and reports.')

@section('content')

@include('university.partials.page-hero', [
  'eyebrow' => 'MRU Scholar',
  'title' => 'Publications',
  'lead' => 'Search the repository by title, author keywords, journal, type, research area or year.',
  'trail' => [['label' => 'Research', 'url' => route('scholar.home')], ['label' => 'Publications']],
])

<section style="padding-top:30px;">
  <div class="wrap">
    <form method="GET" action="{{ route('scholar.publications') }}" class="filter-bar" role="search" aria-label="Filter publications">
      <label class="sr-only" for="pub-q">Search</label>
      <input id="pub-q" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search titles, abstracts, keywords, journals…">
      <label class="sr-only" for="pub-type">Type</label>
      <select id="pub-type" name="type">
        <option value="">All types</option>
        @foreach($types as $value => $label)
          <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
        @endforeach
      </select>
      <label class="sr-only" for="pub-area">Research area</label>
      <select id="pub-area" name="area">
        <option value="">All areas</option>
        @foreach($areas as $area)
          <option value="{{ $area->id }}" @selected($filters['area'] === $area->id)>{{ $area->name }}</option>
        @endforeach
      </select>
      <label class="sr-only" for="pub-year">Year</label>
      <select id="pub-year" name="year">
        <option value="">All years</option>
        @foreach($years as $year)
          <option value="{{ $year }}" @selected($filters['year'] === (int) $year)>{{ $year }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn sm"><i class="fas fa-filter" aria-hidden="true"></i> Filter</button>
      @if(array_filter($filters))
        <a href="{{ route('scholar.publications') }}" wire:navigate class="btn ghost sm">Clear</a>
      @endif
    </form>

    @if($publications->isEmpty())
      <div class="feature-box" style="text-align:center;">
        <h3>Nothing matched that search</h3>
        <p>Try fewer words, or clear the filters to browse the whole repository.</p>
      </div>
    @else
      <p class="sr-only" role="status">{{ $publications->total() }} publications found</p>
      <div style="display:flex;flex-direction:column;">
        @foreach($publications as $publication)
          <article data-rise style="padding:18px 4px;border-bottom:1px solid var(--line);">
            <div class="tag-row" style="margin-bottom:6px;">
              <span class="tag">{{ $publication->typeLabel() }}</span>
              @if($publication->year)<span class="tag">{{ $publication->year }}</span>@endif
              @foreach($publication->researchAreas as $area)
                <span class="pill" style="font-size:10.5px;">{{ $area->name }}</span>
              @endforeach
            </div>
            <h2 style="font-size:16px;font-weight:600;line-height:1.4;margin-bottom:4px;">
              <a href="{{ route('scholar.publication', $publication) }}" wire:navigate style="color:var(--tx);">{{ $publication->title }}</a>
            </h2>
            <p style="font-size:13px;color:var(--tx2);margin-bottom:4px;">
              {{ implode(', ', $publication->authorNames()) ?: 'Muteesa I Royal University' }}
              @if($publication->journal_name) · <em>{{ $publication->journal_name }}</em>@endif
            </p>
            @if($publication->abstract)
              <p style="font-size:13px;color:var(--tx2);line-height:1.6;">{{ \Illuminate\Support\Str::limit(strip_tags($publication->abstract), 220) }}</p>
            @endif
            <div style="display:flex;gap:16px;margin-top:8px;font-size:12.5px;font-weight:600;">
              <a href="{{ route('scholar.publication', $publication) }}" wire:navigate class="link" style="color:var(--pri);">Read <i class="fas fa-arrow-right"></i></a>
              @if($publication->pdf_path)
                <a href="{{ route('scholar.publication.download', $publication) }}" class="link" style="color:var(--gold-d);" data-no-navigate>
                  <i class="fas fa-file-arrow-down" aria-hidden="true"></i> PDF
                </a>
              @endif
              @if($publication->doi)
                <span class="pub-doi">DOI: {{ $publication->doi }}</span>
              @endif
            </div>
          </article>
        @endforeach
      </div>
      <div class="pagination">{{ $publications->links() }}</div>
    @endif
  </div>
</section>

@include('university.partials.cta-band')

@endsection
