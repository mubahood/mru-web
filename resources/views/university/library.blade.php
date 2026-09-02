@extends('layouts.marketing')
@section('title', 'University Library | Muteesa I Royal University')
@section('desc', 'The Muteesa I Royal University library: lending and reference services, research support, e-resources and databases, and reading spaces on both campuses.')

@section('content')

@php
  $n = 0;
  $idx = function () use (&$n) { return str_pad((string) ++$n, 2, '0', STR_PAD_LEFT); };
  $libraryUrl = $links['library'] ?? null;
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Library',
  'title' => 'University Library',
  'lead' => 'Books, journals, databases and quiet places to work — with librarians who help you find what your coursework and research need.',
])

<section>
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Services</span></div>
      <h2>What the library does for you</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-book" aria-hidden="true"></i></span>
        <h3>Lending &amp; reference</h3>
        <p>Borrow from the print collection and consult reference works with help from the library team.</p>
      </div>
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-magnifying-glass-chart" aria-hidden="true"></i></span>
        <h3>Research support</h3>
        <p>Guidance on finding sources, citing them properly and navigating the literature for coursework and research.</p>
      </div>
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-database" aria-hidden="true"></i></span>
        <h3>E-resources &amp; databases</h3>
        <p>Electronic journals, e-books and databases, reachable through the online library portal.</p>
      </div>
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-book-open-reader" aria-hidden="true"></i></span>
        <h3>Reading spaces on both campuses</h3>
        <p>Quiet study space at Kakeeka in Kampala and Kirumba in Masaka, open through the teaching day.</p>
      </div>
    </div>
  </div>
</section>

<section class="band-surface tex-glow">
  <div class="wrap">
    <div class="feature-box" data-rise style="max-width:720px;margin:0 auto;text-align:center;">
      <div class="sub"><i class="fas fa-globe" aria-hidden="true"></i> Online Library</div>
      <h3>Search the collection from anywhere</h3>
      <p>The online library portal is the front door to the catalogue and e-resources.
         For help, write to the Head Librarian at
        <a href="mailto:librarian@mru.ac.ug" class="link" style="color:var(--pri);font-weight:600;">librarian@mru.ac.ug</a>.
      </p>
      @if($libraryUrl)
        <a href="{{ $libraryUrl }}" target="_blank" rel="noopener" class="btn cta">
          <span class="cta-a">Open the library portal</span>
          <span class="cta-b" aria-hidden="true">Search the catalogue <i class="fas fa-arrow-up-right-from-square"></i></span>
        </a>
      @endif
      <p style="margin:16px 0 0;font-size:12.5px;color:var(--tx3);">
        <i class="fas fa-laptop" aria-hidden="true" style="color:var(--gold-d);"></i>
        The University's ICT Centre supports e-learning access across both campuses.
      </p>
    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
