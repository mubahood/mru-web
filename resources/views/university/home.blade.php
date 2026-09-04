@extends('layouts.marketing')
@section('title', ($identity['name'] ?? 'Muteesa I Royal University').' | '.($identity['motto'] ?? 'Seeking Greater Horizons in Thought and Action'))
@section('desc', 'Muteesa I Royal University (MRU) — an NCHE-accredited private university of the Buganda Kingdom. 46+ programmes across five faculties at Kakeeka (Kampala) and Kirumba (Masaka). Apply for the next intake.')

@push('jsonld')
@php
  $contacts = \App\Support\University::contacts();
  $social = collect(\App\Support\University::get('social'))->pluck('url')->all();
  $orgNode = [
    '@context' => 'https://schema.org',
    '@type' => 'CollegeOrUniversity',
    'name' => $identity['name'] ?? 'Muteesa I Royal University',
    'alternateName' => 'MRU',
    'url' => url('/'),
    'logo' => asset('images/logo-icon.png'),
    'slogan' => $identity['motto'] ?? null,
    'email' => $contacts['email'] ?? null,
    'telephone' => $contacts['phone'] ?? null,
    'address' => [
      ['@type' => 'PostalAddress', 'addressLocality' => 'Mengo, Kampala', 'addressCountry' => 'UG', 'streetAddress' => 'Kakeeka Campus'],
      ['@type' => 'PostalAddress', 'addressLocality' => 'Kirumba, Masaka', 'addressCountry' => 'UG', 'streetAddress' => 'Kirumba Campus'],
    ],
    'sameAs' => $social,
  ];
@endphp
<script type="application/ld+json">{!! json_encode(array_filter($orgNode), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

@php
  $applyUrl = \App\Support\University::applyUrl();
  $eportalUrl = \App\Support\University::links()['eportal'] ?? 'https://eportal.mru.ac.ug/';
@endphp

{{--
  The page answers a prospective student's four questions, in order, and hands
  each off to a deeper page rather than trying to finish it here:
    what is this place       → hero, crest, stats
    can I study here         → quick actions, faculties, programme teaser
    is it alive              → news, events, scholar band
    how do I get in          → the closing CTA band
--}}

@include('university.partials.hero-slider')

{{-- The slider carries the photography and the moment; this band carries the
     institutional statement, the facts, and the four things a prospective
     student came to do. --}}
<section class="home-intro">
  <div class="wrap">
    <div class="sec-head" data-rise>
      <p class="eyebrow">{{ $identity['motto'] ?? 'Seeking Greater Horizons in Thought and Action' }}</p>
      <h2>{{ $identity['strapline'] ?? 'Rooted in Heritage. Focused on the Future.' }}</h2>
    </div>

    @if($stats)
      {{-- data-count: the layout's count-up-on-scroll-into-view already knows
           what to do with a .v inside here — it just never had anywhere to
           run before. Non-numeric values (NCHE) are left alone automatically.
           Two of the four are real links: the ones with an obvious next page
           to send a curious click to. Forcing all four to be "clickable" for
           its own sake would have meant inventing a destination for the
           other two that doesn't actually exist. --}}
      <div class="stat-row" data-rise data-count>
        <a href="{{ route('faculties.index') }}" wire:navigate class="stat stat-link">
          <div class="v">{{ $stats[0]['value'] ?? '' }}</div><div class="l">{{ $stats[0]['label'] ?? '' }}</div>
        </a>
        <a href="{{ route('programmes.index') }}" wire:navigate class="stat stat-link">
          <div class="v">{{ $stats[1]['value'] ?? '' }}</div><div class="l">{{ $stats[1]['label'] ?? '' }}</div>
        </a>
        @foreach(array_slice($stats, 2) as $stat)
          <div class="stat"><div class="v">{{ $stat['value'] }}</div><div class="l">{{ $stat['label'] }}</div></div>
        @endforeach
      </div>
    @endif

    {{-- "I am a…" — a cheap, well-evidenced personalisation pattern (the
         University of Arizona's version is the model this follows) rather
         than one generic set of four links pretending to serve a prospective
         student, a parent, a current student and an international applicant
         equally. The four panels below are real destinations, not the same
         links relabelled. --}}
    <div class="audience-picker" data-rise>
      <p class="audience-label">I am a…</p>
      <div class="audience-tabs" role="tablist" aria-label="Choose who you are">
        <button type="button" class="audience-tab is-active" role="tab" aria-selected="true" aria-controls="audience-prospective" tabindex="0" data-audience="prospective">Prospective Student</button>
        <button type="button" class="audience-tab" role="tab" aria-selected="false" aria-controls="audience-current" tabindex="-1" data-audience="current">Current Student</button>
        <button type="button" class="audience-tab" role="tab" aria-selected="false" aria-controls="audience-parent" tabindex="-1" data-audience="parent">Parent / Guardian</button>
        <button type="button" class="audience-tab" role="tab" aria-selected="false" aria-controls="audience-international" tabindex="-1" data-audience="international">International</button>
      </div>

      {{-- All four panels stack in one grid cell, so the block is always as
           tall as its tallest panel — switching (especially by hover) never
           reflows the page below it. Hiding is visibility, not display, for
           the same reason. --}}
      <div class="audience-panels">
      <div class="audience-panel is-active" id="audience-prospective" data-audience-panel="prospective" role="tabpanel">
        @if(!empty($admissions['deadline_note']))
          <p class="intake-strip">
            <i class="fas fa-calendar-check" aria-hidden="true"></i>
            <span>{{ $admissions['deadline_note'] }}</span>
            <a href="{{ $applyUrl }}" rel="external">Apply now <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
          </p>
        @endif
        <div class="icon-row">
          <a href="{{ route('programmes.index') }}" wire:navigate>
            <span class="ic"><i class="fas fa-magnifying-glass" aria-hidden="true"></i></span>
            <span>View Programmes</span>
          </a>
          <a href="{{ route('admissions.apply') }}" wire:navigate>
            <span class="ic"><i class="fas fa-file-pen" aria-hidden="true"></i></span>
            <span>How to Apply</span>
          </a>
          <a href="{{ route('admissions.fees') }}" wire:navigate>
            <span class="ic"><i class="fas fa-money-bill-wave" aria-hidden="true"></i></span>
            <span>Fees Structure</span>
          </a>
          <a href="{{ route('admissions.intakes') }}" wire:navigate>
            <span class="ic"><i class="fas fa-calendar-days" aria-hidden="true"></i></span>
            <span>Intake Dates</span>
          </a>
        </div>
      </div>

      <div class="audience-panel" id="audience-current" data-audience-panel="current" role="tabpanel">
        <div class="icon-row">
          <a href="{{ $eportalUrl }}" rel="external">
            <span class="ic"><i class="fas fa-right-to-bracket" aria-hidden="true"></i></span>
            <span>E-Portal</span>
          </a>
          <a href="{{ route('courses.index') }}" wire:navigate>
            <span class="ic"><i class="fas fa-laptop" aria-hidden="true"></i></span>
            <span>e-Learning</span>
          </a>
          <a href="{{ route('library') }}" wire:navigate>
            <span class="ic"><i class="fas fa-book" aria-hidden="true"></i></span>
            <span>Library</span>
          </a>
          <a href="{{ route('almanac') }}" wire:navigate>
            <span class="ic"><i class="fas fa-calendar-week" aria-hidden="true"></i></span>
            <span>Academic Calendar</span>
          </a>
        </div>
      </div>

      <div class="audience-panel" id="audience-parent" data-audience-panel="parent" role="tabpanel">
        <div class="icon-row">
          <a href="{{ route('admissions.fees') }}" wire:navigate>
            <span class="ic"><i class="fas fa-money-bill-wave" aria-hidden="true"></i></span>
            <span>Fees Structure</span>
          </a>
          <a href="{{ route('accommodation') }}" wire:navigate>
            <span class="ic"><i class="fas fa-bed" aria-hidden="true"></i></span>
            <span>Accommodation</span>
          </a>
          <a href="{{ route('admissions.scholarships') }}" wire:navigate>
            <span class="ic"><i class="fas fa-hand-holding-dollar" aria-hidden="true"></i></span>
            <span>Scholarships</span>
          </a>
          <a href="{{ route('contact') }}" wire:navigate>
            <span class="ic"><i class="fas fa-phone" aria-hidden="true"></i></span>
            <span>Contact Us</span>
          </a>
        </div>
      </div>

      <div class="audience-panel" id="audience-international" data-audience-panel="international" role="tabpanel">
        <div class="icon-row">
          <a href="{{ route('admissions.international') }}" wire:navigate>
            <span class="ic"><i class="fas fa-earth-africa" aria-hidden="true"></i></span>
            <span>International Admissions</span>
          </a>
          <a href="{{ route('admissions.requirements') }}" wire:navigate>
            <span class="ic"><i class="fas fa-clipboard-check" aria-hidden="true"></i></span>
            <span>Entry Requirements</span>
          </a>
          <a href="{{ route('admissions.apply') }}" wire:navigate>
            <span class="ic"><i class="fas fa-file-pen" aria-hidden="true"></i></span>
            <span>How to Apply</span>
          </a>
          <a href="{{ route('contact') }}" wire:navigate>
            <span class="ic"><i class="fas fa-comments" aria-hidden="true"></i></span>
            <span>Contact Us</span>
          </a>
        </div>
      </div>
      </div>
    </div>
  </div>
</section>

<section class="band-surface tex-glow">
  <div class="wrap">
    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:40px;align-items:center;" class="about-split">
      <div>
        <div class="sec-head left" style="margin-bottom:18px;">
          <h2>A royal university with a modern mission</h2>
        </div>
        @foreach(array_slice($identity['history'] ?? [], 0, 2) as $paragraph)
          <p class="lead" data-rise style="margin-bottom:14px;">{{ $paragraph }}</p>
        @endforeach

        {{-- Two facts that were already sitting in Settings, written once for
             the /about page, and never surfaced here: the namesake and the
             accreditation line. Full sentences rather than clipped to a pill
             — neither is short enough to compress without losing what it
             actually says. --}}
        <div class="credential-row" data-rise>
          @if(!empty($identity['namesake']))
            <div class="credential-item">
              <i class="fas fa-crown" aria-hidden="true"></i>
              <span>{{ $identity['namesake'] }}</span>
            </div>
          @endif
          @if(!empty($identity['accreditation']))
            <div class="credential-item">
              <i class="fas fa-certificate" aria-hidden="true"></i>
              <span>{{ $identity['accreditation'] }}</span>
            </div>
          @endif
        </div>

        <div data-rise style="margin-top:24px;">
          <a href="{{ route('about') }}" wire:navigate class="btn ghost">
            Discover MRU <i class="fas fa-arrow-right" aria-hidden="true"></i>
          </a>
        </div>
      </div>
      <div data-rise style="text-align:center;">
        <img src="{{ asset('images/logo-icon.png') }}" alt="The crest of Muteesa I Royal University"
             loading="lazy" decoding="async" style="max-width:260px;margin:0 auto;">
      </div>
    </div>
  </div>
</section>
@push('styles')<style>@media(max-width:820px){.about-split{grid-template-columns:1fr !important;}}</style>@endpush

@if($faculties->isNotEmpty())
  @php
    /* The Graduate School is a different kind of thing from the other four
       — postgraduate, not undergraduate — so it earns a different shape
       below rather than a 5th identical box. Matched by name rather than a
       schema flag: this template is already this specific, not a generic
       component (see "Kampala for the capital's energy" a few sections
       down). The heading's count is computed, not hardcoded, so it can
       never again claim a number the database doesn't back — that's
       exactly the bug this section shipped with before this pass. */
    $gradSchool = $faculties->first(fn ($f) => str_contains($f->name, 'Graduate School'));
    $mainFaculties = $faculties->reject(fn ($f) => $gradSchool && $f->is($gradSchool))->values();
    $countWords = ['One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten'];
    $facultyCount = $mainFaculties->count();
    $facultyWord = $countWords[$facultyCount - 1] ?? $facultyCount;
  @endphp
<section>
  <div class="wrap">
    <div class="sec-head left">
      <h2>{{ $facultyWord }} {{ \Illuminate\Support\Str::plural('faculty', $facultyCount) }}{{ $gradSchool ? ', one Graduate School' : '' }}</h2>
      <p>Every programme belongs to a faculty that teaches it, researches it, and walks you into a career with it.</p>
    </div>

    <div class="faculty-grid">
      @foreach($mainFaculties as $faculty)
        <a href="{{ route('faculties.show', $faculty) }}" wire:navigate class="card faculty-card" data-rise>
          <div class="faculty-card-body">
            <div class="faculty-card-top">
              <span class="ic"><i class="fas {{ $faculty->icon }}" aria-hidden="true"></i></span>
              @if($faculty->short_name)<span class="tag">{{ $faculty->short_name }}</span>@endif
            </div>
            <h3>{{ $faculty->name }}</h3>
            <p>{{ \Illuminate\Support\Str::limit($faculty->description ?: $faculty->tagline, 100) }}</p>
            @if(!empty($faculty->careers))
              <div class="tag-row">
                @foreach(array_slice($faculty->careers, 0, 2) as $career)
                  <span class="pill">{{ $career }}</span>
                @endforeach
              </div>
            @endif
            <span class="link">
              {{ $faculty->programmes_count }} {{ \Illuminate\Support\Str::plural('programme', $faculty->programmes_count) }} <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </span>
          </div>
          {{-- alt is intentionally empty: the link's own text already names the
               faculty, so a described image would just be announced twice. --}}
          @if($faculty->cover_image)
            <img class="faculty-card-photo" src="{{ asset('storage/'.$faculty->cover_image) }}"
                 alt="" width="800" height="1000" loading="lazy" decoding="async">
          @endif
        </a>
      @endforeach
    </div>

    @if($gradSchool)
      <a href="{{ route('faculties.show', $gradSchool) }}" wire:navigate class="proj-card grad-school-strip" data-rise>
        <span class="ic"><i class="fas {{ $gradSchool->icon }}" aria-hidden="true"></i></span>
        <div class="grad-school-body">
          <span class="tag">Postgraduate</span>
          <h3>{{ $gradSchool->name }}</h3>
          <p>{{ \Illuminate\Support\Str::limit($gradSchool->description ?: $gradSchool->tagline, 130) }}</p>
          <span class="link">
            {{ $gradSchool->programmes_count }} {{ \Illuminate\Support\Str::plural('programme', $gradSchool->programmes_count) }} <i class="fas fa-arrow-right" aria-hidden="true"></i>
          </span>
        </div>
        @if($gradSchool->cover_image)
          <img class="grad-school-photo" src="{{ asset('storage/'.$gradSchool->cover_image) }}"
               alt="" width="800" height="1000" loading="lazy" decoding="async">
        @endif
      </a>
    @endif
  </div>
</section>
@endif

@if(!empty($campuses))
<section class="band-surface">
  <div class="wrap">
    <div class="sec-head left">
      <h2>One university, two homes</h2>
      <p>Kampala for the capital's energy, Masaka for a quieter pace — the same MRU education at both.</p>
    </div>
    <div class="campus-grid">
      @foreach($campuses as $campus)
        <a href="{{ $campus['maps'] ?? '#' }}" target="_blank" rel="noopener external" class="card campus-card" data-rise>
          <span class="ic"><i class="fas fa-location-dot" aria-hidden="true"></i></span>
          <h3>{{ $campus['name'] }}</h3>
          <p>{{ $campus['location'] }}</p>
          <span class="link">Get directions <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></span>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif

@if($programmes->isNotEmpty())
<section class="band-surface tex-grid">
  <div class="wrap">
    <div class="sec-head left">
      <h2>Find the programme that fits you</h2>
      <p>From one-year certificates to masters degrees — filter the full directory by level, faculty or name.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
      @foreach($programmes as $programme)
        @include('university.partials.programme-card', ['programme' => $programme])
      @endforeach
    </div>
    <div style="text-align:center;margin-top:30px;" data-rise>
      <a href="{{ route('programmes.index') }}" wire:navigate class="btn ghost">
        All programmes <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
    </div>
  </div>
</section>
@endif

@if($gallery->isNotEmpty())
<section>
  <div class="wrap">
    <div class="sec-head left">
      <h2>A closer look at who we are</h2>
      <p>Moments from across the university — its people, its ceremonies, its everyday work.</p>
    </div>
    <div class="gallery-wall">
      @foreach($gallery as $photo)
        <a href="{{ route('campus-life') }}" wire:navigate class="gallery-tile" data-rise
           style="aspect-ratio:{{ $photo->ratio() }};">
          <img src="{{ $photo->thumbUrl() }}" alt="{{ $photo->altText() }}" loading="lazy" decoding="async">
          <span class="gallery-caption">{{ $photo->title }}</span>
        </a>
      @endforeach
    </div>
    <div style="text-align:center;margin-top:26px;" data-rise>
      <a href="{{ route('campus-life') }}" wire:navigate class="btn ghost">
        See more of campus life <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
    </div>
  </div>
</section>
@endif

@if($news->isNotEmpty() || $events->isNotEmpty())
<section>
  <div class="wrap">
    <div class="sec-head left">
      <h2>Life at the university, this week</h2>
    </div>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;" class="news-split">
      <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));align-content:start;">
        @foreach($news as $post)
          <a href="{{ route('insights.show', $post) }}" wire:navigate class="proj-card" data-rise>
            @if($post->cover_image)
              <div class="course-cover"><img src="{{ asset('storage/'.$post->cover_image) }}" alt="" loading="lazy" decoding="async" width="400" height="225"></div>
            @endif
            <span class="client">{{ $post->published_at?->diffForHumans() }}</span>
            <h3>{{ $post->title }}</h3>
            <p>{{ \Illuminate\Support\Str::limit($post->excerpt, 100) }}</p>
            <span class="link">Read <i class="fas fa-arrow-right"></i></span>
          </a>
        @endforeach
      </div>
      <div data-rise>
        <div class="feature-box" style="height:100%;">
          <div class="sub">Upcoming events</div>
          @forelse($events as $event)
            <div style="padding:10px 0;border-bottom:1px solid var(--line);">
              <div style="font-family:ui-monospace,Menlo,monospace;font-size:11px;color:var(--gold-d);font-weight:700;">
                {{ $event->starts_at->format('D, j M Y · g:i A') }}
                <span style="color:var(--tx3);font-weight:500;">· {{ $event->starts_at->diffForHumans() }}</span>
              </div>
              <a href="{{ route('events.show', $event) }}" wire:navigate style="font-weight:600;font-size:14px;color:var(--tx);">{{ $event->title }}</a>
              @if($event->venue)<div style="font-size:12px;color:var(--tx2);"><i class="fas fa-location-dot" aria-hidden="true"></i> {{ $event->venue }}</div>@endif
            </div>
          @empty
            <p style="font-size:13px;color:var(--tx2);">New events are announced here and on our social channels.</p>
          @endforelse
          <div style="margin-top:14px;">
            <a href="{{ route('events.index') }}" wire:navigate class="link" style="color:var(--pri);font-weight:600;">All events <i class="fas fa-arrow-right"></i></a>
          </div>
        </div>
      </div>
    </div>
    <div style="text-align:center;margin-top:26px;" data-rise>
      <a href="{{ route('insights.index') }}" wire:navigate class="btn ghost">
        All news <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
    </div>
  </div>
</section>
@push('styles')<style>@media(max-width:900px){.news-split{grid-template-columns:1fr !important;}}</style>@endpush
@endif

<section class="band-deep">
  <div class="wrap">
    <div class="sec-head left">
      <h2>Research that leaves the library</h2>
      <p>MRU Scholar is the university's open repository — publications by our academics, free to read and verify.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
      @forelse($publications as $publication)
        <a href="{{ route('scholar.publication', $publication) }}" wire:navigate class="proj-card" data-rise style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.16);">
          <div class="tag-row"><span class="tag" style="background:rgba(212,168,67,.18);color:var(--gold);">{{ $publication->typeLabel() }}</span></div>
          <h3 style="color:#fff;">{{ \Illuminate\Support\Str::limit($publication->title, 90) }}</h3>
          <p style="color:rgba(255,255,255,.66);">{{ implode(', ', array_slice($publication->authorNames(), 0, 3)) }}</p>
          <span class="link" style="color:var(--gold);">Read <i class="fas fa-arrow-right"></i></span>
        </a>
      @empty
        <p style="color:rgba(255,255,255,.66);">Publications are being added to the repository.</p>
      @endforelse
    </div>
    <div style="text-align:center;margin-top:28px;" data-rise>
      <a href="{{ route('scholar.home') }}" wire:navigate class="btn gold">
        Visit MRU Scholar <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
    </div>
  </div>
</section>

{{-- Omitted entirely until real student quotes exist: an empty "what our
     students say" band is worse than none. --}}
@if($testimonials->isNotEmpty())
<section class="band-surface tex-glow">
  <div class="wrap">
    <div class="sec-head left">
      <h2>What our students say</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
      @foreach($testimonials as $testimonial)
        <figure class="feature-box" data-rise style="margin:0;">
          <blockquote style="font-size:14px;line-height:1.65;color:var(--tx2);border:none;padding:0;font-style:italic;">
            “{{ $testimonial['quote'] ?? '' }}”
          </blockquote>
          <figcaption style="margin-top:12px;display:flex;align-items:center;gap:10px;">
            @if(!empty($testimonial['photo']))
              <img src="{{ asset('storage/'.$testimonial['photo']) }}" alt="" loading="lazy" decoding="async"
                   style="width:40px;height:40px;object-fit:cover;border-radius:50%;border:2px solid var(--gold);">
            @endif
            <span>
              <strong style="display:block;font-size:13px;">{{ $testimonial['name'] ?? '' }}</strong>
              <span style="font-size:12px;color:var(--tx3);">{{ trim(($testimonial['role'] ?? '').(isset($testimonial['org']) && $testimonial['org'] !== '' ? ', '.$testimonial['org'] : '')) }}</span>
            </span>
          </figcaption>
        </figure>
      @endforeach
    </div>
  </div>
</section>
@endif

@if($partners->isNotEmpty())
<section>
  <div class="wrap">
    <div class="sec-head">
      <h2>Recognised and connected</h2>
    </div>
    <div class="clients-strip" data-rise style="align-items:center;">
      @foreach($partners as $partner)
        @if($partner->logo)
          <img src="{{ asset('storage/'.$partner->logo) }}" alt="{{ $partner->name }}" title="{{ $partner->name }}"
               loading="lazy" decoding="async" style="height:52px;width:auto;filter:grayscale(1);opacity:.75;">
        @else
          <span>{{ $partner->name }}</span>
        @endif
      @endforeach
    </div>
  </div>
</section>
@endif

@include('university.partials.cta-band')

{{-- The phone-only bar keeps the two decisions in reach through the scroll. --}}
<x-action-bar>
  <a href="{{ route('programmes.index') }}" wire:navigate class="btn ghost">Programmes</a>
  <a href="{{ $applyUrl }}" rel="external" class="btn gold">Apply Now</a>
</x-action-bar>

@endsection
