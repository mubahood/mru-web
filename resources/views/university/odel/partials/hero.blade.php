{{-- The section's page header. $trail is [[label,url?],…]; the last is current. --}}
<section class="od-hero">
  <div class="wrap">
    @isset($trail)
      <nav class="od-crumbs" aria-label="Breadcrumb">
        <a href="{{ route('odel.index') }}" wire:navigate>ODEL</a>
        @foreach($trail as $c)
          <i class="fas fa-chevron-right" aria-hidden="true" style="font-size:9px;"></i>
          @if(!empty($c['url']))<a href="{{ $c['url'] }}" wire:navigate>{{ $c['label'] }}</a>
          @else<span aria-current="page">{{ $c['label'] }}</span>@endif
        @endforeach
      </nav>
    @endisset
    @isset($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endisset
    <h1>{{ $title }}</h1>
    @isset($lead)<p class="lead">{{ $lead }}</p>@endisset
    @isset($slot){{ $slot }}@endisset
  </div>
</section>
