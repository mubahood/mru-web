@extends('layouts.admin')
@section('title', 'Vacancies')

@section('content')

<div class="tb-page-header">
  <div><h1>Vacancies</h1><div class="tb-breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> Vacancies</div></div>
  <a href="{{ route('admin.vacancies.create') }}" class="btn-tb btn-tb-primary"><i class="fas fa-plus"></i> New Vacancy</a>
</div>

<div class="tb-card">
  <div class="tb-table-wrap">
    <table class="tb-table">
      <thead><tr><th>Title</th><th>Reference</th><th>Deadline</th><th>State</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($items as $item)
        <tr>
          <td style="font-weight:500;">
            <a href="{{ route('admin.vacancies.edit', $item) }}">{{ $item->title }}</a>
            @if($item->department)<div class="muted" style="font-size:11px;">{{ $item->department }}</div>@endif
          </td>
          <td>{{ $item->reference_no ?? '-' }}</td>
          <td>{{ $item->deadline_on?->format('d M Y') ?? '-' }}</td>
          <td>
            @if($item->isOpen())
              <span class="badge-tb badge-success">Open</span>
            @else
              <span class="badge-tb badge-danger">Closed</span>
            @endif
          </td>
          <td>
            @if($item->is_published)
              <span class="badge-tb badge-success">Published</span>
            @else
              <span class="badge-tb badge-neutral">Draft</span>
            @endif
          </td>
          <td>
            <div class="tb-table-actions">
              <a href="{{ route('admin.vacancies.edit', $item) }}" class="btn-tb btn-tb-ghost btn-tb-icon" title="Edit"><i class="fas fa-pen"></i></a>
              <form method="POST" action="{{ route('admin.vacancies.destroy', $item) }}" onsubmit="return confirm('Remove this vacancy?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-tb btn-tb-danger btn-tb-icon" title="Delete"><i class="fas fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6"><div class="tb-empty"><p>No vacancies yet.</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
