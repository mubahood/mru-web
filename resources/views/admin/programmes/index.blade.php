@extends('layouts.admin')
@section('title', 'Programmes')

@section('content')

<div class="tb-page-header">
  <div><h1>Programmes</h1><div class="tb-breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> Programmes</div></div>
  <a href="{{ route('admin.programmes.create') }}" class="btn-tb btn-tb-primary"><i class="fas fa-plus"></i> New Programme</a>
</div>

<form method="GET" class="tb-filter-bar">
  <select name="level" class="tb-input">
    <option value="">All levels</option>
    @foreach(\App\Models\Programme::LEVELS as $val => $label)
      <option value="{{ $val }}" @selected($filterLevel === $val)>{{ $label }}</option>
    @endforeach
  </select>
  <select name="faculty_id" class="tb-input">
    <option value="">All faculties</option>
    @foreach($faculties as $faculty)
      <option value="{{ $faculty->id }}" @selected($filterFaculty === (string) $faculty->id)>{{ $faculty->name }}</option>
    @endforeach
  </select>
  <button class="btn-tb btn-tb-ghost"><i class="fas fa-filter"></i> Filter</button>
</form>

<div class="tb-card">
  <div class="tb-table-wrap">
    <table class="tb-table">
      <thead><tr><th>Name</th><th>Level</th><th>Faculty</th><th>Tuition</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($items as $item)
        <tr>
          <td style="font-weight:500;">
            <a href="{{ route('admin.programmes.edit', $item) }}">{{ $item->name }}</a>
            @if($item->award_code)<div class="muted" style="font-size:11px;">{{ $item->award_code }}</div>@endif
          </td>
          <td>{{ $item->levelLabel() }}</td>
          <td>{{ $item->faculty?->name ?? '-' }}</td>
          <td class="muted" style="font-size:.8rem;">{{ $item->tuitionDisplay() ?? '-' }}</td>
          <td>
            @if($item->is_published)
              <span class="badge-tb badge-success">Published</span>
            @else
              <span class="badge-tb badge-neutral">Draft</span>
            @endif
          </td>
          <td>
            <div class="tb-table-actions">
              <a href="{{ route('admin.programmes.edit', $item) }}" class="btn-tb btn-tb-ghost btn-tb-icon" title="Edit"><i class="fas fa-pen"></i></a>
              <form method="POST" action="{{ route('admin.programmes.destroy', $item) }}" onsubmit="return confirm('Remove this programme?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-tb btn-tb-danger btn-tb-icon" title="Delete"><i class="fas fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6"><div class="tb-empty"><p>No programmes found.</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div style="margin-top:16px;">{{ $items->links() }}</div>

@endsection
