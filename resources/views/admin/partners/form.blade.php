@extends('layouts.admin')
@section('title', $item->exists ? 'Edit Partner' : 'New Partner')

@section('content')

<div class="tb-page-header">
  <div><h1>{{ $item->exists ? 'Edit Partner' : 'New Partner' }}</h1>
    <div class="tb-breadcrumb"><a href="{{ route('admin.partners.index') }}">Partners</a> <span>/</span> {{ $item->exists ? $item->name : 'Create' }}</div>
  </div>
</div>

<form method="POST" enctype="multipart/form-data"
      action="{{ $item->exists ? route('admin.partners.update', $item) : route('admin.partners.store') }}">
@csrf
@if($item->exists) @method('PUT') @endif
<div class="tb-card">
  <div class="tb-card-body">
    <div class="tb-form-grid">
      <div class="tb-form-group">
        <label class="tb-label">Name *</label>
        <input class="tb-input" type="text" name="name" value="{{ old('name', $item->name) }}" required maxlength="200">
        @error('name')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Website URL</label>
        <input class="tb-input" type="text" name="url" value="{{ old('url', $item->url) }}" maxlength="255" placeholder="https://...">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Logo</label>
        <input class="tb-input" type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/svg+xml">
        @if($item->logo)
          <img src="{{ asset('storage/'.$item->logo) }}" alt="" style="margin-top:8px;max-height:60px;border:1px solid var(--line);background:#fff;">
        @endif
        @error('logo')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Sort order</label>
        <input class="tb-input" type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order) }}">
      </div>
      <div class="tb-form-group" style="justify-content:flex-end;">
        <label class="tb-check-group">
          <input type="checkbox" name="show_on_home" value="1" @checked(old('show_on_home', $item->exists ? $item->show_on_home : true))>
          Show on the homepage
        </label>
      </div>
    </div>
  </div>
  <div class="tb-card-footer" style="display:flex;gap:10px;justify-content:flex-end;">
    <a href="{{ route('admin.partners.index') }}" class="btn-tb btn-tb-ghost">Cancel</a>
    <button type="submit" class="btn-tb btn-tb-primary"><i class="fas fa-check"></i> Save</button>
  </div>
</div>
</form>
@endsection
