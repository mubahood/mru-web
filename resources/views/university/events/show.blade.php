@extends('layouts.marketing')
@section('title', $event->title.' | Muteesa I Royal University')
@section('desc', \Illuminate\Support\Str::limit(strip_tags((string) ($event->excerpt ?: $event->description)), 155) ?: 'An event at Muteesa I Royal University.')

@php
  $chips = array_values(array_filter([
    $event->starts_at ? ['fa-calendar-day', $event->starts_at->format('D, j M Y')] : null,
    $event->starts_at ? ['fa-clock', $event->starts_at->format('g:i A').($event->ends_at ? ' – '.$event->ends_at->format('g:i A') : '')] : null,
    $event->venue ? ['fa-location-dot', $event->venue] : null,
    $event->campus ? ['fa-map-pin', $event->campus] : null,
    $event->category ? ['fa-tag', $event->category] : null,
    $event->faculty?->name ? ['fa-building-columns', $event->faculty->name] : null,
  ]));

  $gcalUrl = null;
  if ($event->starts_at) {
    $gcalEnd = $event->ends_at ?? $event->starts_at->copy()->addHours(2);
    $gcalUrl = 'https://calendar.google.com/calendar/render?'.http_build_query(array_filter([
      'action' => 'TEMPLATE',
      'text' => $event->title,
      'dates' => $event->starts_at->format('Ymd\THis').'/'.$gcalEnd->format('Ymd\THis'),
      'location' => collect([$event->venue, $event->campus])->filter()->implode(', '),
      'details' => (string) $event->excerpt,
    ]));
  }

  /* Rich descriptions keep a safe subset of their markup; plain text keeps its
     line breaks. */
  $rawDescription = (string) $event->description;
  $descriptionHtml = str_contains($rawDescription, '<')
    ? (string) \Illuminate\Support\Str::of($rawDescription)->stripTags('<p><br><ul><ol><li><strong><em><a><h3><h4>')
    : nl2br(e($rawDescription));
@endphp

@push('jsonld')
@php
  $place = array_filter(['@type' => 'Place', 'name' => $event->venue, 'address' => $event->campus]);
  $eventNode = array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Event',
    'name' => $event->title,
    'startDate' => $event->starts_at?->toIso8601String(),
    'endDate' => $event->ends_at?->toIso8601String(),
    'description' => $event->excerpt,
    'image' => $event->image ? asset('storage/'.$event->image) : null,
    'location' => isset($place['name']) ? $place : null,
    'organizer' => ['@type' => 'CollegeOrUniversity', 'name' => 'Muteesa I Royal University', 'url' => url('/')],
  ]);
@endphp
<script type="application/ld+json">{!! json_encode($eventNode, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

@include('university.partials.page-hero', [
  'eyebrow' => 'Event',
  'mark' => 'Events',
  'title' => $event->title,
  'lead' => trim(($event->starts_at?->format('l, j F Y · g:i A') ?? '').($event->venue ? ' · '.$event->venue : ''), ' ·'),
  'chips' => $chips,
])

<section>
  <div class="wrap">
    <div class="page">
      @if($event->image)
        <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}"
             loading="lazy" decoding="async" data-rise
             style="max-width:100%;height:auto;border:1px solid var(--line);margin-bottom:24px;">
      @endif

      <div data-rise>{!! $descriptionHtml !!}</div>

      <div class="ctas" data-rise style="margin-top:28px;">
        @if($gcalUrl)
          <a href="{{ $gcalUrl }}" target="_blank" rel="noopener" class="btn gold">
            <i class="fas fa-calendar-plus" aria-hidden="true"></i> Add to calendar
          </a>
        @endif
        <a href="{{ route('events.index') }}" wire:navigate class="btn ghost">
          <i class="fas fa-arrow-left" aria-hidden="true"></i> All events
        </a>
      </div>
    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
