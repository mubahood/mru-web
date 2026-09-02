@extends('layouts.admin')
@section('title', $item->exists ? 'Edit Research Area' : 'New Research Area')

@section('content')

<div class="tb-page-header">
  <div><h1>{{ $item->exists ? 'Edit Research Area' : 'New Research Area' }}</h1>
    <div class="tb-breadcrumb"><a href="{{ route('admin.research-areas.index') }}">Research Areas</a> <span>/</span> {{ $item->exists ? $item->name : 'Create' }}</div>
  </div>
</div>

<form method="POST" action="{{ $item->exists ? route('admin.research-areas.update', $item) : route('admin.research-areas.store') }}">
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
        <label class="tb-label">URL slug</label>
        <input class="tb-input" type="text" name="slug" value="{{ old('slug', $item->slug) }}" maxlength="220" placeholder="auto from name">
        @error('slug')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Description</label>
        <textarea class="tb-textarea" name="description" rows="3" maxlength="500">{{ old('description', $item->description) }}</textarea>
        @error('description')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
    </div>
  </div>
  <div class="tb-card-footer" style="display:flex;gap:10px;justify-content:flex-end;">
    <a href="{{ route('admin.research-areas.index') }}" class="btn-tb btn-tb-ghost">Cancel</a>
    <button type="submit" class="btn-tb btn-tb-primary"><i class="fas fa-check"></i> Save</button>
  </div>
</div>
</form>
@endsection
