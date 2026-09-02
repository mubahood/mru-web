@extends('layouts.admin')
@section('title', 'Events')

@section('content')

<div class="tb-page-header">
  <div><h1>Events</h1><div class="tb-breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> Events</div></div>
  <a href="{{ route('admin.university-events.create') }}" class="btn-tb btn-tb-primary"><i class="fas fa-plus"></i> New Event</a>
</div>

<div class="tb-card">
  <div class="tb-table-wrap">
    <table class="tb-table">
      <thead><tr><th>Title</th><th>Starts</th><th>Venue</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($items as $item)
        <tr>
          <td style="font-weight:500;">
            <a href="{{ route('admin.university-events.edit', $item) }}">{{ $item->title }}</a>
            @if($item->category)<div class="muted" style="font-size:11px;">{{ $item->category }}</div>@endif
          </td>
          <td>{{ $item->starts_at?->format('d M Y, H:i') }}</td>
          <td>{{ $item->venue ?? '-' }}</td>
          <td>
            @if($item->is_published)
              <span class="badge-tb badge-success">Published</span>
            @else
              <span class="badge-tb badge-neutral">Draft</span>
            @endif
          </td>
          <td>
            <div class="tb-table-actions">
              <a href="{{ route('admin.university-events.edit', $item) }}" class="btn-tb btn-tb-ghost btn-tb-icon" title="Edit"><i class="fas fa-pen"></i></a>
              <form method="POST" action="{{ route('admin.university-events.destroy', $item) }}" onsubmit="return confirm('Remove this event?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-tb btn-tb-danger btn-tb-icon" title="Delete"><i class="fas fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="5"><div class="tb-empty"><p>No events yet.</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div style="margin-top:16px;">{{ $items->links() }}</div>

@endsection
