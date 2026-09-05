{{--
  Breadcrumbs: the visible trail and its BreadcrumbList structured data from
  one source, so the two can never disagree.

  Props: $trail — [['label' => 'Admissions', 'url' => route(…)], …]
  The current page is the last item and is not a link.

  Google renders BreadcrumbList in place of the raw URL in results, which on a
  deep page like /admissions/scholarships is the difference between a result
  reading "mru.ac.ug › admissions › scholarships" and one reading as a bare
  slug.
--}}
@php
  $crumbs = array_values(array_filter($trail ?? []));
  $home = ['label' => 'Home', 'url' => route('home')];
  $all = array_merge([$home], $crumbs);
@endphp
@if(count($all) > 1)
  <nav class="crumbs" aria-label="Breadcrumb">
    <ol>
      @foreach($all as $i => $c)
        <li>
          @if(! $loop->last && ! empty($c['url']))
            <a href="{{ $c['url'] }}" wire:navigate>{{ $c['label'] }}</a>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
          @else
            <span aria-current="page">{{ $c['label'] }}</span>
          @endif
        </li>
      @endforeach
    </ol>
  </nav>
  @push('jsonld')
  <script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => array_map(fn ($i, $c) => array_filter([
        '@type' => 'ListItem',
        'position' => $i + 1,
        'name' => $c['label'],
        'item' => $c['url'] ?? null,
    ]), array_keys($all), $all),
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
  @endpush
@endif
