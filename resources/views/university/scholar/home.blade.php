@extends('layouts.marketing')
@section('title', 'MRU Scholar | Muteesa I Royal University')
@section('desc', 'MRU Scholar is Muteesa I Royal University\'s open research repository: publications by our academics, free to read, search and download.')

@section('content')

@php
@endphp

{{-- Not the shared page-hero partial: this header carries the repository's
     four live figures and its own subnav, neither of which that partial takes.
     It borrows the partial's two-column .has-photo structure so the photograph
     sits and crops identically to every other page's. --}}
<section class="page-hero has-photo">
  <span class="hero-mark" aria-hidden="true">Scholar</span>
  <div class="wrap">
    @include('university.partials.breadcrumbs', ['trail' => [['label' => 'Research']]])
    <div class="ph-inner">
      <div class="ph-copy">
        <p class="eyebrow">Research at MRU</p>
        <h1>MRU <b style="font-weight:600;color:var(--pri);">Scholar</b></h1>
        <p>The university's open research repository — publications by MRU academics, free to read, search and download.</p>
        <div class="trust-chips">
          <span><i class="fas fa-file-lines" aria-hidden="true"></i> {{ $stats['publications'] }} publications</span>
          <span><i class="fas fa-user-graduate" aria-hidden="true"></i> {{ $stats['scholars'] }} scholars</span>
          <span><i class="fas fa-tags" aria-hidden="true"></i> {{ $stats['areas'] }} research areas</span>
          <span><i class="fas fa-download" aria-hidden="true"></i> {{ number_format($stats['downloads']) }} downloads</span>
        </div>
        <div class="subnav">
          <a href="{{ route('scholar.publications') }}" wire:navigate>Publications</a>
          <a href="{{ route('scholar.directory') }}" wire:navigate>Scholars directory</a>
        </div>
      </div>
      <div class="ph-media">
        <img src="{{ asset('images/photos/hero-research.jpg') }}"
             alt="Two academics discussing research posters on crop nutrient deficiency"
             width="880" height="660" loading="eager" fetchpriority="high" decoding="async">
      </div>
    </div>
  </div>
</section>

{{-- One search box straight into the repository. --}}
<section style="padding:30px 0;">
  <div class="wrap">
    <form method="GET" action="{{ route('scholar.publications') }}" class="filter-bar" role="search" aria-label="Search publications">
      <label class="sr-only" for="sch-q">Search publications</label>
      <input id="sch-q" type="text" name="q" placeholder="Search titles, abstracts, keywords, journals…">
      <button type="submit" class="btn sm"><i class="fas fa-magnifying-glass" aria-hidden="true"></i> Search</button>
    </form>
  </div>
</section>

@if($featured->isNotEmpty())
<section class="band-surface tex-glow" style="padding-top:34px;">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Featured</p>
      <h2>Featured research</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
      @foreach($featured as $publication)
        <a href="{{ route('scholar.publication', $publication) }}" wire:navigate class="proj-card" data-rise>
          <div class="tag-row">
            <span class="tag">{{ $publication->typeLabel() }}</span>
            @if($publication->year)<span class="tag">{{ $publication->year }}</span>@endif
          </div>
          <h3>{{ \Illuminate\Support\Str::limit($publication->title, 110) }}</h3>
          <p>{{ implode(', ', array_slice($publication->authorNames(), 0, 3)) }}</p>
          <span class="link">Read <i class="fas fa-arrow-right"></i></span>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif

<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Latest</p>
      <h2>Latest publications</h2>
    </div>
    <div style="display:flex;flex-direction:column;gap:0;">
      @foreach($latest as $publication)
        <a href="{{ route('scholar.publication', $publication) }}" wire:navigate data-rise
           style="display:flex;gap:16px;align-items:baseline;padding:14px 4px;border-bottom:1px solid var(--line);">
          <span style="font-family:ui-monospace,Menlo,monospace;font-size:11.5px;font-weight:700;color:var(--gold-d);flex-shrink:0;width:44px;">
            {{ $publication->year ?: '—' }}
          </span>
          <span style="flex:1;min-width:0;">
            <span style="display:block;font-weight:600;font-size:14px;color:var(--tx);">{{ $publication->title }}</span>
            <span style="display:block;font-size:12.5px;color:var(--tx2);margin-top:2px;">
              {{ implode(', ', array_slice($publication->authorNames(), 0, 4)) }}
              @if($publication->journal_name) · <em>{{ \Illuminate\Support\Str::limit($publication->journal_name, 60) }}</em>@endif
            </span>
          </span>
          <i class="fas fa-arrow-right" aria-hidden="true" style="color:var(--line-2);flex-shrink:0;"></i>
        </a>
      @endforeach
    </div>
    <div style="margin-top:24px;" data-rise>
      <a href="{{ route('scholar.publications') }}" wire:navigate class="btn ghost">
        All publications <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
    </div>
  </div>
</section>

@if($areas->isNotEmpty())
<section class="band-surface">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Research areas</p>
      <h2>Browse by area</h2>
    </div>
    <div class="pill-row" data-rise>
      @foreach($areas as $area)
        <a href="{{ route('scholar.publications', ['area' => $area->id]) }}" wire:navigate class="pill"
           style="font-size:13px;padding:8px 14px;color:var(--tx);">
          {{ $area->name }} <span style="color:var(--gold-d);font-weight:700;">{{ $area->publications_count }}</span>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif

<section class="band-deep">
  <div class="wrap" style="text-align:center;">
    <div class="sec-head">
      <h2>Are you an MRU academic?</h2>
      <p>MRU Scholar grows one publication at a time. Send your published work to the repository team
         and it will be reviewed and added under your profile.</p>
    </div>
    <div class="ctas">
      <a href="mailto:{{ \App\Support\University::contacts()['email'] ?? 'info@mru.ac.ug' }}?subject=MRU%20Scholar%20submission" class="btn gold lg">
        <i class="fas fa-paper-plane" aria-hidden="true"></i> Submit a publication
      </a>
    </div>
  </div>
</section>

@endsection
