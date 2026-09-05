@extends('layouts.marketing')
@section('title', $scholar->displayName().' | MRU Scholar')
@section('desc', \Illuminate\Support\Str::limit(strip_tags((string) ($scholar->bio ?: $scholar->research_interests)) ?: $scholar->displayName().' — researcher at Muteesa I Royal University.', 150))

@section('content')

<section class="page-hero">
  <div class="wrap">
    <div style="display:flex;gap:22px;align-items:center;flex-wrap:wrap;">
      @if($scholar->photo)
        <img src="{{ asset('storage/'.$scholar->photo) }}" alt="{{ $scholar->displayName() }}"
             style="width:110px;height:110px;object-fit:cover;object-position:top;border:3px solid var(--gold);border-radius:50%;">
      @else
        <span style="width:110px;height:110px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--pri);color:var(--gold);font-size:36px;" aria-hidden="true">
          <i class="fas fa-user-graduate"></i>
        </span>
      @endif
      <div style="flex:1;min-width:240px;">
        <p class="eyebrow">MRU Scholar</p>
        <h1 style="font-size:26px;">{{ $scholar->displayName() }}</h1>
        <div class="trust-chips" style="margin-top:8px;">
          @if($scholar->faculty)<span><i class="fas fa-school" aria-hidden="true"></i> {{ $scholar->faculty->name }}</span>@endif
          @if($scholar->department)<span><i class="fas fa-sitemap" aria-hidden="true"></i> {{ $scholar->department }}</span>@endif
          <span><i class="fas fa-file-lines" aria-hidden="true"></i> {{ $scholar->publications->count() }} {{ \Illuminate\Support\Str::plural('publication', $scholar->publications->count()) }}</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="course-layout">
      <div class="main">
        @if($scholar->bio)
          <p class="eyebrow">About</p>
          <div class="page" style="max-width:none;margin:0 0 28px;">
            @foreach(preg_split('/\n{2,}/', trim(strip_tags($scholar->bio))) as $paragraph)
              @if(trim($paragraph) !== '')<p>{{ trim($paragraph) }}</p>@endif
            @endforeach
          </div>
        @endif

        @if($scholar->research_interests)
          <p class="eyebrow">Research interests</p>
          <div class="pill-row" style="margin-bottom:28px;">
            @foreach(array_filter(array_map('trim', preg_split('/[,;\n]/', $scholar->research_interests))) as $interest)
              <span class="pill">{{ $interest }}</span>
            @endforeach
          </div>
        @endif

        <p class="eyebrow">Publications</p>
        @forelse($scholar->publications as $publication)
          <article style="padding:14px 4px;border-bottom:1px solid var(--line);">
            <div class="tag-row" style="margin-bottom:5px;">
              <span class="tag">{{ $publication->typeLabel() }}</span>
              @if($publication->year)<span class="tag">{{ $publication->year }}</span>@endif
            </div>
            <a href="{{ route('scholar.publication', $publication) }}" wire:navigate
               style="font-weight:600;font-size:14.5px;color:var(--tx);display:block;">{{ $publication->title }}</a>
            @if($publication->journal_name)
              <p style="font-size:12.5px;color:var(--tx2);margin-top:3px;"><em>{{ $publication->journal_name }}</em></p>
            @endif
          </article>
        @empty
          <p class="lead">Publications by this scholar are being added to the repository.</p>
        @endforelse
      </div>

      <aside class="buy-box" aria-label="Contact this scholar">
        <ul class="includes" style="margin-top:0;">
          @if($scholar->email)<li><a href="mailto:{{ $scholar->email }}" class="link">{{ $scholar->email }}</a></li>@endif
          @if($scholar->orcid)<li>ORCID: {{ $scholar->orcid }}</li>@endif
        </ul>
        @if($scholar->google_scholar_url)
          <a href="{{ $scholar->google_scholar_url }}" target="_blank" rel="noopener" class="btn ghost" style="width:100%;justify-content:center;">
            <i class="fas fa-graduation-cap" aria-hidden="true"></i> Google Scholar profile
          </a>
        @endif
        @if($scholar->cv_path)
          <a href="{{ asset('storage/'.$scholar->cv_path) }}" target="_blank" rel="noopener" class="btn ghost" style="width:100%;justify-content:center;margin-top:10px;">
            <i class="fas fa-file-lines" aria-hidden="true"></i> Curriculum vitae
          </a>
        @endif
        <p class="money-comfort">Profile maintained by the MRU Scholar team.</p>
      </aside>
    </div>

    <div style="margin-top:30px;">
      <a href="{{ route('scholar.directory') }}" wire:navigate class="link" style="color:var(--pri);font-weight:600;">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> All scholars
      </a>
    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
