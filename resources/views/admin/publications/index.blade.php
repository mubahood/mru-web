@extends('layouts.admin')
@section('title', 'Publications')

@section('content')

<div class="tb-page-header">
  <div><h1>Publications</h1><div class="tb-breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> Publications</div></div>
  <a href="{{ route('admin.publications.create') }}" class="btn-tb btn-tb-primary"><i class="fas fa-plus"></i> New Publication</a>
</div>

<div class="tb-card">
  <div class="tb-table-wrap">
    <table class="tb-table">
      <thead><tr><th>Title</th><th>Type</th><th>Year</th><th>Status</th><th>Featured</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($items as $item)
        <tr>
          <td style="font-weight:500;"><a href="{{ route('admin.publications.edit', $item) }}">{{ \Illuminate\Support\Str::limit($item->title, 60) }}</a></td>
          <td>{{ $item->typeLabel() }}</td>
          <td>{{ $item->year ?? '-' }}</td>
          <td>
            @if($item->status === 'published')
              <span class="badge-tb badge-success">Published</span>
            @elseif($item->status === 'pending')
              <span class="badge-tb badge-warn">Pending</span>
            @else
              <span class="badge-tb badge-neutral">Draft</span>
            @endif
          </td>
          <td>
            @if($item->is_featured)
              <i class="fas fa-star" style="color:var(--warn);" title="Featured"></i>
            @else
              <span class="muted">-</span>
            @endif
          </td>
          <td>
            <div class="tb-table-actions">
              <a href="{{ route('admin.publications.edit', $item) }}" class="btn-tb btn-tb-ghost btn-tb-icon" title="Edit"><i class="fas fa-pen"></i></a>
              <form method="POST" action="{{ route('admin.publications.destroy', $item) }}" onsubmit="return confirm('Remove this publication?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-tb btn-tb-danger btn-tb-icon" title="Delete"><i class="fas fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6"><div class="tb-empty"><p>No publications yet.</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div style="margin-top:16px;">{{ $items->links() }}</div>

@endsection
