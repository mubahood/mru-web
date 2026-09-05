@extends('layouts.marketing')
@section('title', \Illuminate\Support\Str::limit($publication->title, 55).' | MRU Scholar')
@section('desc', \Illuminate\Support\Str::limit(strip_tags((string) $publication->abstract) ?: $publication->typeLabel().' by '.implode(', ', array_slice($publication->authorNames(), 0, 3)).' — MRU Scholar.', 150))

@push('jsonld')
@php
  $scholarNode = array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'ScholarlyArticle',
    'headline' => \Illuminate\Support\Str::limit($publication->title, 110),
    'name' => $publication->title,
    'abstract' => \Illuminate\Support\Str::limit(strip_tags((string) $publication->abstract), 500) ?: null,
    'author' => array_map(fn ($name) => ['@type' => 'Person', 'name' => $name], $publication->authorNames()),
    'datePublished' => $publication->publication_date?->toDateString(),
    'publisher' => $publication->journal_name ? ['@type' => 'Organization', 'name' => $publication->journal_name] : null,
    'sameAs' => $publication->doi ? 'https://doi.org/'.ltrim(preg_replace('#^https?://doi\.org/#', '', $publication->doi), '/') : null,
  ]);
@endphp
<script type="application/ld+json">{!! json_encode($scholarNode, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

<section class="page-hero">
  <div class="wrap">
    <p class="eyebrow">{{ $publication->typeLabel() }}@if($publication->year) · {{ $publication->year }}@endif</p>
    <h1 style="font-size:26px;max-width:820px;">{{ $publication->title }}</h1>
    <div class="trust-chips">
      @if($publication->journal_name)<span><i class="fas fa-book-journal-whills" aria-hidden="true"></i> {{ \Illuminate\Support\Str::limit($publication->journal_name, 60) }}</span>@endif
      @if($publication->citations)<span><i class="fas fa-quote-right" aria-hidden="true"></i> {{ $publication->citations }} citations</span>@endif
      <span><i class="fas fa-eye" aria-hidden="true"></i> {{ number_format($publication->views) }} views</span>
      <span><i class="fas fa-download" aria-hidden="true"></i> {{ number_format($publication->downloads) }} downloads</span>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="course-layout">
      <div class="main">
        <p class="eyebrow">Authors</p>
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin:0 0 28px;">
          @foreach($publication->authorRows as $author)
            @if($author->scholar && $author->scholar->is_published)
              <a href="{{ route('scholar.profile', $author->scholar) }}" wire:navigate class="pill"
                 style="font-size:13px;padding:8px 14px;color:var(--pri);font-weight:600;">
                <i class="fas fa-user-graduate" aria-hidden="true"></i> {{ $author->scholar->displayName() }}
              </a>
            @else
              <span class="pill" style="font-size:13px;padding:8px 14px;">{{ $author->scholar?->name ?? $author->external_name }}</span>
            @endif
          @endforeach
        </div>

        @if($publication->abstract)
          <p class="eyebrow">Abstract</p>
          <div class="page" style="max-width:none;margin:0 0 28px;">
            @foreach(preg_split('/\n{2,}/', trim(strip_tags($publication->abstract))) as $paragraph)
              @if(trim($paragraph) !== '')<p>{{ trim($paragraph) }}</p>@endif
            @endforeach
          </div>
        @endif

        @if($publication->keywords)
          <p class="eyebrow">Keywords</p>
          <div class="pill-row" style="margin-bottom:28px;">
            @foreach(array_filter(array_map('trim', explode(',', $publication->keywords))) as $keyword)
              <span class="pill">{{ $keyword }}</span>
            @endforeach
          </div>
        @endif

        @if($publication->researchAreas->isNotEmpty())
          <p class="eyebrow">Research areas</p>
          <div class="pill-row">
            @foreach($publication->researchAreas as $area)
              <a href="{{ route('scholar.publications', ['area' => $area->id]) }}" wire:navigate class="pill" style="color:var(--pri);">{{ $area->name }}</a>
            @endforeach
          </div>
        @endif
      </div>

      <aside class="buy-box" aria-label="Access this publication">
        <ul class="includes" style="margin-top:0;">
          <li>{{ $publication->typeLabel() }}</li>
          @if($publication->publication_date)<li>Published {{ $publication->publication_date->format('j F Y') }}</li>@endif
          @if($publication->volume)<li>Volume {{ $publication->volume }}@if($publication->issue), Issue {{ $publication->issue }}@endif</li>@endif
          @if($publication->pages)<li>Pages {{ $publication->pages }}</li>@endif
          @if($publication->publisher)<li>{{ $publication->publisher }}</li>@endif
        </ul>
        @if($publication->pdf_path)
          <a href="{{ route('scholar.publication.download', $publication) }}" class="btn gold lg" style="width:100%;justify-content:center;" data-no-navigate>
            <i class="fas fa-file-arrow-down" aria-hidden="true"></i> Download PDF
          </a>
        @endif
        @if($publication->doi)
          @php $doiUrl = 'https://doi.org/'.ltrim(preg_replace('#^https?://doi\.org/#', '', $publication->doi), '/'); @endphp
          <a href="{{ $doiUrl }}" target="_blank" rel="noopener" class="btn ghost" style="width:100%;justify-content:center;margin-top:10px;">
            <i class="fas fa-link" aria-hidden="true"></i> View via DOI
          </a>
        @endif
        @if($publication->url)
          <a href="{{ $publication->url }}" target="_blank" rel="noopener" class="btn ghost" style="width:100%;justify-content:center;margin-top:10px;">
            <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i> Publisher's page
          </a>
        @endif
        <p class="money-comfort">Cite responsibly. Content remains © its authors.</p>
      </aside>
    </div>

    <div style="margin-top:30px;">
      <a href="{{ route('scholar.publications') }}" wire:navigate class="link" style="color:var(--pri);font-weight:600;">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> All publications
      </a>
    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
