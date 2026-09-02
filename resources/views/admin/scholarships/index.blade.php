@extends('layouts.admin')
@section('title', 'Scholarships')

@section('content')

<div class="tb-page-header">
  <div><h1>Scholarships</h1><div class="tb-breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> Scholarships</div></div>
  <a href="{{ route('admin.scholarships.create') }}" class="btn-tb btn-tb-primary"><i class="fas fa-plus"></i> New Scholarship</a>
</div>

<div class="tb-card">
  <div class="tb-table-wrap">
    <table class="tb-table">
      <thead><tr><th>Name</th><th>Category</th><th>Coverage</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($items as $item)
        <tr>
          <td style="font-weight:500;"><a href="{{ route('admin.scholarships.edit', $item) }}">{{ $item->name }}</a></td>
          <td>{{ $item->category ?? '-' }}</td>
          <td class="muted" style="font-size:.8rem;">{{ $item->coverage ?? '-' }}</td>
          <td>
            @if($item->is_published)
              <span class="badge-tb badge-success">Published</span>
            @else
              <span class="badge-tb badge-neutral">Draft</span>
            @endif
          </td>
          <td>
            <div class="tb-table-actions">
              <a href="{{ route('admin.scholarships.edit', $item) }}" class="btn-tb btn-tb-ghost btn-tb-icon" title="Edit"><i class="fas fa-pen"></i></a>
              <form method="POST" action="{{ route('admin.scholarships.destroy', $item) }}" onsubmit="return confirm('Remove this scholarship?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-tb btn-tb-danger btn-tb-icon" title="Delete"><i class="fas fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="5"><div class="tb-empty"><p>No scholarships yet.</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
