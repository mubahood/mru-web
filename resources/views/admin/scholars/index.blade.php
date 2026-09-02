@extends('layouts.admin')
@section('title', 'Scholars')

@section('content')

<div class="tb-page-header">
  <div><h1>Scholars</h1><div class="tb-breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> Scholars</div></div>
  <a href="{{ route('admin.scholars.create') }}" class="btn-tb btn-tb-primary"><i class="fas fa-plus"></i> New Scholar</a>
</div>

<div class="tb-card">
  <div class="tb-table-wrap">
    <table class="tb-table">
      <thead><tr><th>Name</th><th>Title</th><th>Faculty</th><th>Publications</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($items as $item)
        <tr>
          <td style="font-weight:500;"><a href="{{ route('admin.scholars.edit', $item) }}">{{ $item->name }}</a></td>
          <td>{{ $item->title ?? '-' }}</td>
          <td>{{ $item->faculty?->short_name ?? $item->faculty?->name ?? '-' }}</td>
          <td>{{ $item->publications_count }}</td>
          <td>
            @if($item->is_published)
              <span class="badge-tb badge-success">Published</span>
            @else
              <span class="badge-tb badge-neutral">Draft</span>
            @endif
          </td>
          <td>
            <div class="tb-table-actions">
              <a href="{{ route('admin.scholars.edit', $item) }}" class="btn-tb btn-tb-ghost btn-tb-icon" title="Edit"><i class="fas fa-pen"></i></a>
              <form method="POST" action="{{ route('admin.scholars.destroy', $item) }}" onsubmit="return confirm('Remove this scholar?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-tb btn-tb-danger btn-tb-icon" title="Delete"><i class="fas fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6"><div class="tb-empty"><p>No scholars yet.</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div style="margin-top:16px;">{{ $items->links() }}</div>

@endsection
