@extends('layouts.admin')
@section('title', 'Notice strip')

@section('content')

<div class="tb-page-header">
  <div><h1>Notice strip</h1><div class="tb-breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> Notice strip</div></div>
  <a href="{{ route('admin.notices.create') }}" class="btn-tb btn-tb-primary"><i class="fas fa-plus"></i> New notice</a>
</div>

<div class="tb-card">
  <div class="tb-table-wrap">
    <table class="tb-table">
      <thead><tr><th>State</th><th>Message</th><th>Template</th><th>Window</th><th>Sort</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($items as $item)
        @php
          /* "Live" is the only state that matters, and it is not the same as
             published: a published notice whose window has not opened, or has
             closed, is invisible. Showing why saves an editor wondering. */
          $live = in_array($item->id, $liveIds, true);
          $why = ! $item->is_published ? 'Unpublished'
               : ($item->starts_at && $item->starts_at->isFuture() ? 'Scheduled'
               : ($item->ends_at && $item->ends_at->isPast() ? 'Expired' : 'Live'));
        @endphp
        <tr>
          <td>
            <span class="tb-badge {{ $live ? 'tb-badge-success' : ($why === 'Scheduled' ? 'tb-badge-warning' : '') }}">{{ $live ? 'Live' : $why }}</span>
          </td>
          <td style="font-weight:500;max-width:360px;">
            <a href="{{ route('admin.notices.edit', $item) }}">{{ \Illuminate\Support\Str::limit($item->message, 90) }}</a>
            @if($item->link_url)
              <div class="muted" style="font-size:.75rem;"><i class="fas fa-link"></i> {{ $item->link_label }}</div>
            @endif
          </td>
          <td class="muted" style="font-size:.8rem;">{{ ucfirst($item->template) }}</td>
          <td class="muted" style="font-size:.78rem;">
            {{ $item->starts_at?->format('j M Y') ?? 'now' }} &rarr; {{ $item->ends_at?->format('j M Y') ?? 'no end' }}
            @if($item->deadline_at)
              <div><i class="fas fa-hourglass-half"></i> deadline {{ $item->deadline_at->format('j M Y') }}</div>
            @endif
          </td>
          <td>{{ $item->sort_order }}</td>
          <td>
            <div class="tb-table-actions">
              <a href="{{ route('admin.notices.edit', $item) }}" class="btn-tb btn-tb-ghost btn-tb-icon" title="Edit"><i class="fas fa-pen"></i></a>
              <form method="POST" action="{{ route('admin.notices.destroy', $item) }}" onsubmit="return confirm('Remove this notice?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-tb btn-tb-danger btn-tb-icon" title="Delete"><i class="fas fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6"><div class="tb-empty"><p>No notices yet. The strip stays hidden until there is one.</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
