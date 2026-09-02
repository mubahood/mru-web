@extends('layouts.admin')
@section('title', $item->exists ? 'Edit Scholarship' : 'New Scholarship')

@section('content')

<div class="tb-page-header">
  <div><h1>{{ $item->exists ? 'Edit Scholarship' : 'New Scholarship' }}</h1>
    <div class="tb-breadcrumb"><a href="{{ route('admin.scholarships.index') }}">Scholarships</a> <span>/</span> {{ $item->exists ? $item->name : 'Create' }}</div>
  </div>
</div>

<form method="POST" action="{{ $item->exists ? route('admin.scholarships.update', $item) : route('admin.scholarships.store') }}">
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
      <div class="tb-form-group">
        <label class="tb-label">Category</label>
        <input class="tb-input" type="text" name="category" value="{{ old('category', $item->category) }}" maxlength="50" placeholder="e.g. Merit">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Coverage</label>
        <input class="tb-input" type="text" name="coverage" value="{{ old('coverage', $item->coverage) }}" maxlength="120" placeholder="e.g. 50% of tuition">
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Criteria</label>
        <textarea class="tb-textarea" name="criteria" rows="4">{{ old('criteria', $item->criteria) }}</textarea>
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Amount note</label>
        <input class="tb-input" type="text" name="amount_note" value="{{ old('amount_note', $item->amount_note) }}" maxlength="255">
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Description</label>
        <textarea class="tb-textarea" name="description" rows="4">{{ old('description', $item->description) }}</textarea>
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Sort order</label>
        <input class="tb-input" type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order) }}">
      </div>
      <div class="tb-form-group" style="justify-content:flex-end;">
        <label class="tb-check-group">
          <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $item->exists ? $item->is_published : true))>
          Published, visible to everyone
        </label>
      </div>
    </div>
  </div>
  <div class="tb-card-footer" style="display:flex;gap:10px;justify-content:flex-end;">
    <a href="{{ route('admin.scholarships.index') }}" class="btn-tb btn-tb-ghost">Cancel</a>
    <button type="submit" class="btn-tb btn-tb-primary"><i class="fas fa-check"></i> Save</button>
  </div>
</div>
</form>
@endsection
