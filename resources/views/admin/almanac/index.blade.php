@extends('layouts.admin')
@section('title', 'Almanac')

@section('content')

<div class="tb-page-header">
  <div><h1>Almanac</h1><div class="tb-breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> Almanac</div></div>
  <a href="{{ route('admin.almanac.create') }}" class="btn-tb btn-tb-primary"><i class="fas fa-plus"></i> New Entry</a>
</div>

<div class="tb-card">
  <div class="tb-table-wrap">
    <table class="tb-table">
      <thead><tr><th>Activity</th><th>Period</th><th>Starts</th><th>Ends</th><th>Actions</th></tr></thead>
      <tbody>
        @php $group = null; @endphp
        @forelse($items as $item)
          @php $key = $item->academic_year.' — '.$item->semester; @endphp
          @if($key !== $group)
            @php $group = $key; @endphp
            <tr><td colspan="5" style="background:var(--surface-2);font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.04em;">{{ $key }}</td></tr>
          @endif
          <tr>
            <td style="font-weight:500;"><a href="{{ route('admin.almanac.edit', $item) }}">{{ $item->activity }}</a></td>
            <td class="muted" style="font-size:.8rem;">{{ $item->period ?? '-' }}</td>
            <td>{{ $item->starts_on?->format('d M Y') ?? '-' }}</td>
            <td>{{ $item->ends_on?->format('d M Y') ?? '-' }}</td>
            <td>
              <div class="tb-table-actions">
                <a href="{{ route('admin.almanac.edit', $item) }}" class="btn-tb btn-tb-ghost btn-tb-icon" title="Edit"><i class="fas fa-pen"></i></a>
                <form method="POST" action="{{ route('admin.almanac.destroy', $item) }}" onsubmit="return confirm('Remove this entry?');">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn-tb btn-tb-danger btn-tb-icon" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5"><div class="tb-empty"><p>No almanac entries yet.</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
