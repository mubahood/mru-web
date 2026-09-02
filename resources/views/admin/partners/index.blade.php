@extends('layouts.admin')
@section('title', 'Partners')

@section('content')

<div class="tb-page-header">
  <div><h1>Partners</h1><div class="tb-breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> Partners</div></div>
  <a href="{{ route('admin.partners.create') }}" class="btn-tb btn-tb-primary"><i class="fas fa-plus"></i> New Partner</a>
</div>

<div class="tb-card">
  <div class="tb-table-wrap">
    <table class="tb-table">
      <thead><tr><th>Logo</th><th>Name</th><th>URL</th><th>Sort</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($items as $item)
        <tr>
          <td>
            @if($item->logo)
              <img src="{{ asset('storage/'.$item->logo) }}" alt="" style="width:40px;height:40px;object-fit:contain;border:1px solid var(--line);background:#fff;">
            @else
              <div style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;background:var(--surface-2);color:var(--mt);"><i class="fas fa-handshake"></i></div>
            @endif
          </td>
          <td style="font-weight:500;"><a href="{{ route('admin.partners.edit', $item) }}">{{ $item->name }}</a></td>
          <td class="muted" style="font-size:.8rem;">{{ $item->url ?? '-' }}</td>
          <td>{{ $item->sort_order }}</td>
          <td>
            <div class="tb-table-actions">
              <a href="{{ route('admin.partners.edit', $item) }}" class="btn-tb btn-tb-ghost btn-tb-icon" title="Edit"><i class="fas fa-pen"></i></a>
              <form method="POST" action="{{ route('admin.partners.destroy', $item) }}" onsubmit="return confirm('Remove this partner?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-tb btn-tb-danger btn-tb-icon" title="Delete"><i class="fas fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="5"><div class="tb-empty"><p>No partners yet.</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
