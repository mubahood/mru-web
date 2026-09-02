@extends('layouts.admin')
@section('title', 'Research Areas')

@section('content')

<div class="tb-page-header">
  <div><h1>Research Areas</h1><div class="tb-breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> Research Areas</div></div>
  <a href="{{ route('admin.research-areas.create') }}" class="btn-tb btn-tb-primary"><i class="fas fa-plus"></i> New Research Area</a>
</div>

<div class="tb-card">
  <div class="tb-table-wrap">
    <table class="tb-table">
      <thead><tr><th>Name</th><th>Description</th><th>Publications</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($items as $item)
        <tr>
          <td style="font-weight:500;">
            <a href="{{ route('admin.research-areas.edit', $item) }}">{{ $item->name }}</a>
            <div class="muted" style="font-size:11px;">{{ $item->slug }}</div>
          </td>
          <td class="muted" style="font-size:.8rem;">{{ \Illuminate\Support\Str::limit($item->description, 80) ?? '-' }}</td>
          <td>{{ $item->publications_count }}</td>
          <td>
            <div class="tb-table-actions">
              <a href="{{ route('admin.research-areas.edit', $item) }}" class="btn-tb btn-tb-ghost btn-tb-icon" title="Edit"><i class="fas fa-pen"></i></a>
              <form method="POST" action="{{ route('admin.research-areas.destroy', $item) }}" onsubmit="return confirm('Remove this research area?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-tb btn-tb-danger btn-tb-icon" title="Delete"><i class="fas fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="4"><div class="tb-empty"><p>No research areas yet.</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
