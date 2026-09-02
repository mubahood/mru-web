@extends('layouts.admin')
@section('title', $item->exists ? 'Edit Faculty' : 'New Faculty')

@section('content')

<div class="tb-page-header">
  <div><h1>{{ $item->exists ? 'Edit Faculty' : 'New Faculty' }}</h1>
    <div class="tb-breadcrumb"><a href="{{ route('admin.faculties.index') }}">Faculties</a> <span>/</span> {{ $item->exists ? $item->name : 'Create' }}</div>
  </div>
</div>

<form method="POST" enctype="multipart/form-data"
      action="{{ $item->exists ? route('admin.faculties.update', $item) : route('admin.faculties.store') }}">
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
        <label class="tb-label">Short name</label>
        <input class="tb-input" type="text" name="short_name" value="{{ old('short_name', $item->short_name) }}" maxlength="20" placeholder="e.g. FST">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Tagline</label>
        <input class="tb-input" type="text" name="tagline" value="{{ old('tagline', $item->tagline) }}" maxlength="255">
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Description</label>
        <textarea class="tb-textarea" name="description" rows="3">{{ old('description', $item->description) }}</textarea>
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">About</label>
        <textarea class="tb-textarea" name="about" rows="6">{{ old('about', $item->about) }}</textarea>
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Vision</label>
        <textarea class="tb-textarea" name="vision" rows="3">{{ old('vision', $item->vision) }}</textarea>
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Mission</label>
        <textarea class="tb-textarea" name="mission" rows="3">{{ old('mission', $item->mission) }}</textarea>
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Color</label>
        <input class="tb-input" type="text" name="color" value="{{ old('color', $item->color) }}" maxlength="20" placeholder="#05275C">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Icon (Font Awesome class)</label>
        <input class="tb-input" type="text" name="icon" value="{{ old('icon', $item->icon) }}" maxlength="50" placeholder="fa-building-columns">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Departments</label>
        <textarea class="tb-textarea" name="departments" rows="4" placeholder="One department per line">{{ old('departments', implode("\n", $item->departments ?? [])) }}</textarea>
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Careers</label>
        <textarea class="tb-textarea" name="careers" rows="4" placeholder="One career path per line">{{ old('careers', implode("\n", $item->careers ?? [])) }}</textarea>
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Dean</label>
        <select class="tb-input" name="dean_staff_id">
          <option value="">— None —</option>
          @foreach($staff as $person)
            <option value="{{ $person->id }}" @selected((string) old('dean_staff_id', $item->dean_staff_id) === (string) $person->id)>{{ $person->name }}</option>
          @endforeach
        </select>
        @error('dean_staff_id')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Cover image</label>
        <input class="tb-input" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp">
        @if($item->cover_image)
          <img src="{{ asset('storage/'.$item->cover_image) }}" alt="" style="margin-top:8px;max-height:90px;border:1px solid var(--line);">
        @endif
        @error('cover_image')<p class="tb-field-error">{{ $message }}</p>@enderror
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
    <a href="{{ route('admin.faculties.index') }}" class="btn-tb btn-tb-ghost">Cancel</a>
    <button type="submit" class="btn-tb btn-tb-primary"><i class="fas fa-check"></i> Save</button>
  </div>
</div>
</form>
@endsection
