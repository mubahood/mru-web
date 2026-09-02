@extends('layouts.admin')
@section('title', 'Staff')

@section('content')

<div class="tb-page-header">
  <div><h1>Staff</h1><div class="tb-breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> Staff</div></div>
  <a href="{{ route('admin.staff-members.create') }}" class="btn-tb btn-tb-primary"><i class="fas fa-plus"></i> New Staff Member</a>
</div>

<form method="GET" class="tb-filter-bar">
  <select name="staff_role" class="tb-input">
    <option value="">All roles</option>
    @foreach(\App\Models\StaffMember::ROLES as $val => $label)
      <option value="{{ $val }}" @selected($filterRole === $val)>{{ $label }}</option>
    @endforeach
  </select>
  <button class="btn-tb btn-tb-ghost"><i class="fas fa-filter"></i> Filter</button>
</form>

<div class="tb-card">
  <div class="tb-table-wrap">
    <table class="tb-table">
      <thead><tr><th>Photo</th><th>Name</th><th>Title</th><th>Role</th><th>Faculty</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($items as $item)
        <tr>
          <td>
            @if($item->photo)
              <img src="{{ asset('storage/'.$item->photo) }}" alt="" style="width:40px;height:40px;object-fit:cover;border:1px solid var(--line);">
            @else
              <div style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;background:var(--surface-2);color:var(--mt);"><i class="fas fa-user"></i></div>
            @endif
          </td>
          <td style="font-weight:500;"><a href="{{ route('admin.staff-members.edit', $item) }}">{{ $item->name }}</a></td>
          <td class="muted" style="font-size:.8rem;">{{ \Illuminate\Support\Str::limit($item->title, 40) ?? '-' }}</td>
          <td>{{ $item->roleLabel() }}</td>
          <td>{{ $item->faculty?->short_name ?? $item->faculty?->name ?? '-' }}</td>
          <td>
            @if($item->is_published)
              <span class="badge-tb badge-success">Published</span>
            @else
              <span class="badge-tb badge-neutral">Draft</span>
            @endif
          </td>
          <td>
            <div class="tb-table-actions">
              <a href="{{ route('admin.staff-members.edit', $item) }}" class="btn-tb btn-tb-ghost btn-tb-icon" title="Edit"><i class="fas fa-pen"></i></a>
              <form method="POST" action="{{ route('admin.staff-members.destroy', $item) }}" onsubmit="return confirm('Remove this staff member?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-tb btn-tb-danger btn-tb-icon" title="Delete"><i class="fas fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7"><div class="tb-empty"><p>No staff members found.</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div style="margin-top:16px;">{{ $items->links() }}</div>

@endsection
