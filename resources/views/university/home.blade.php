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
  /* Section numbers are assigned as sections render, so an omitted band never
     leaves a hole in the numbering. */
  $n = 0;
  $idx = function () use (&$n) { return str_pad((string) ++$n, 2, '0', STR_PAD_LEFT); };
  $applyUrl = \App\Support\University::applyUrl();
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
           run before. Non-numeric values (NCHE) are left alone automatically. --}}
      <div class="stat-row" data-rise data-count>
        @foreach($stats as $stat)
          <div class="stat"><div class="v">{{ $stat['value'] }}</div><div class="l">{{ $stat['label'] }}</div></div>
        @endforeach
      </div>
    @endif

    @if(!empty($admissions['deadline_note']))
      <p class="intake-strip" data-rise>
        <i class="fas fa-calendar-check" aria-hidden="true"></i>
        <span>{{ $admissions['deadline_note'] }}</span>
        <a href="{{ route('admissions.intakes') }}" wire:navigate>Intake dates <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
      </p>
    @endif

    <div class="icon-row">
      <a href="{{ route('programmes.index') }}" wire:navigate data-rise>
        <span class="ic"><i class="fas fa-magnifying-glass" aria-hidden="true"></i></span>
        <span>View Programmes</span>
      </a>
      <a href="{{ route('admissions.apply') }}" wire:navigate data-rise>
        <span class="ic"><i class="fas fa-file-pen" aria-hidden="true"></i></span>
        <span>How to Apply</span>
      </a>
      <a href="{{ route('admissions.fees') }}" wire:navigate data-rise>
        <span class="ic"><i class="fas fa-money-bill-wave" aria-hidden="true"></i></span>
        <span>Fees Structure</span>
      </a>
      <a href="{{ route('admissions.intakes') }}" wire:navigate data-rise>
        <span class="ic"><i class="fas fa-calendar-days" aria-hidden="true"></i></span>
        <span>Intake Dates</span>
      </a>
    </div>
  </div>
</section>

<section class="band-surface tex-glow">
  <div class="wrap">
    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:40px;align-items:center;" class="about-split">
      <div>
        <div class="sec-head left" style="margin-bottom:18px;">
          <div class="sec-idx">{{ $idx() }} <span>About MRU</span></div>
          <h2>A royal university with a modern mission</h2>
        </div>
        @foreach(array_slice($identity['history'] ?? [], 0, 2) as $paragraph)
          <p class="lead" data-rise style="margin-bottom:14px;">{{ $paragraph }}</p>
        @endforeach
        <div data-rise style="margin-top:20px;">
          <a href="{{ route('about') }}" wire:navigate class="btn ghost">
            Discover MRU <i class="fas fa-arrow-right" aria-hidden="true"></i>
          </a>
        </div>
      </div>
      <div data-rise style="text-align:center;">
        <img src="{{ asset('images/logo-icon.png') }}" alt="The crest of Muteesa I Royal University"
             loading="lazy" decoding="async" style="max-width:250px;margin:0 auto;">
      </div>
    </div>
  </div>
</section>
@push('styles')<style>@media(max-width:820px){.about-split{grid-template-columns:1fr !important;}}</style>@endpush

@if($faculties->isNotEmpty())
<section>
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Faculties</span></div>
      <h2>Five faculties, one Graduate School</h2>
      <p>Every programme belongs to a faculty that teaches it, researches it, and walks you into a career with it.</p>
    </div>
    {{-- Wide tracks on purpose: five faculties fall into 3 + 2 rather than
         4 + 1, which leaves a single stranded card at the end. --}}
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(340px,1fr));">
      @foreach($faculties as $faculty)
        <a href="{{ route('faculties.show', $faculty) }}" wire:navigate class="card" data-rise>
          <span class="ic"><i class="fas {{ $faculty->icon }}" aria-hidden="true"></i></span>
          <h3>{{ $faculty->name }}</h3>
          <p>{{ $faculty->tagline ?: \Illuminate\Support\Str::limit($faculty->description, 80) }}</p>
          <p style="margin-top:8px;font-weight:600;color:var(--gold-d);font-size:12px;">
            {{ $faculty->programmes_count }} {{ \Illuminate\Support\Str::plural('programme', $faculty->programmes_count) }} <i class="fas fa-arrow-right" aria-hidden="true"></i>
          </p>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif

@if(!empty($campuses))
<section class="band-surface">
  <div class="wrap">
    <div class="sec-head left">
      <div class="sec-idx">{{ $idx() }} <span>Two Campuses</span></div>
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
      <div class="sec-idx">{{ $idx() }} <span>Programmes</span></div>
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
      <div class="sec-idx">{{ $idx() }} <span>Campus Life</span></div>
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
      <div class="sec-idx">{{ $idx() }} <span>News &amp; Events</span></div>
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
      <div class="sec-idx">{{ $idx() }} <span>MRU Scholar</span></div>
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
      <div class="sec-idx">{{ $idx() }} <span>Student Voices</span></div>
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
      <div class="sec-idx">{{ $idx() }} <span>Partners</span></div>
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
