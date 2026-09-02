{{-- One programme in a grid. Expects $programme (with faculty loaded). --}}
<a href="{{ route('programmes.show', $programme) }}" wire:navigate class="proj-card" data-rise>
  <div class="tag-row">
    <span class="tag">{{ $programme->levelLabel() }}</span>
    @if($programme->duration)<span class="tag">{{ $programme->duration }}</span>@endif
  </div>
  <h3>{{ $programme->name }}</h3>
  @if($programme->faculty)
    <span class="client">{{ $programme->faculty->name }}</span>
  @endif
  <p>{{ \Illuminate\Support\Str::limit(strip_tags((string) $programme->description), 110) ?: 'Entry requirements, fees and intakes on the programme page.' }}</p>
  <span class="link">
    {{ $programme->tuitionDisplay() ?? 'View programme' }} <i class="fas fa-arrow-right"></i>
  </span>
</a>
