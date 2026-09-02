@extends('layouts.admin')
@section('title', $item->exists ? 'Edit Event' : 'New Event')

@section('content')

<div class="tb-page-header">
  <div><h1>{{ $item->exists ? 'Edit Event' : 'New Event' }}</h1>
    <div class="tb-breadcrumb"><a href="{{ route('admin.university-events.index') }}">Events</a> <span>/</span> {{ $item->exists ? $item->title : 'Create' }}</div>
  </div>
</div>

<form method="POST" enctype="multipart/form-data"
      action="{{ $item->exists ? route('admin.university-events.update', $item) : route('admin.university-events.store') }}">
@csrf
@if($item->exists) @method('PUT') @endif
<div class="tb-card">
  <div class="tb-card-body">
    <div class="tb-form-grid">
      <div class="tb-form-group full">
        <label class="tb-label">Title *</label>
        <input class="tb-input" type="text" name="title" value="{{ old('title', $item->title) }}" required maxlength="255">
        @error('title')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">URL slug</label>
        <input class="tb-input" type="text" name="slug" value="{{ old('slug', $item->slug) }}" maxlength="220" placeholder="auto from title">
        @error('slug')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Category</label>
        <input class="tb-input" type="text" name="category" value="{{ old('category', $item->category) }}" maxlength="50" placeholder="e.g. Graduation">
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Excerpt</label>
        <input class="tb-input" type="text" name="excerpt" value="{{ old('excerpt', $item->excerpt) }}" maxlength="300">
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Description</label>
        <textarea class="tb-textarea" name="description" rows="6">{{ old('description', $item->description) }}</textarea>
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Starts at *</label>
        <input class="tb-input" type="datetime-local" name="starts_at" value="{{ old('starts_at', $item->starts_at?->format('Y-m-d\TH:i')) }}" required>
        @error('starts_at')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Ends at</label>
        <input class="tb-input" type="datetime-local" name="ends_at" value="{{ old('ends_at', $item->ends_at?->format('Y-m-d\TH:i')) }}">
        @error('ends_at')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Venue</label>
        <input class="tb-input" type="text" name="venue" value="{{ old('venue', $item->venue) }}" maxlength="255">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Campus</label>
        <input class="tb-input" type="text" name="campus" value="{{ old('campus', $item->campus) }}" maxlength="50">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Faculty</label>
        <select class="tb-input" name="faculty_id">
          <option value="">— None —</option>
          @foreach($faculties as $faculty)
            <option value="{{ $faculty->id }}" @selected((string) old('faculty_id', $item->faculty_id) === (string) $faculty->id)>{{ $faculty->name }}</option>
          @endforeach
        </select>
        @error('faculty_id')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Image</label>
        <input class="tb-input" type="file" name="image" accept="image/jpeg,image/png,image/webp">
        @if($item->image)
          <img src="{{ asset('storage/'.$item->image) }}" alt="" style="margin-top:8px;max-height:90px;border:1px solid var(--line);">
        @endif
        @error('image')<p class="tb-field-error">{{ $message }}</p>@enderror
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
    <a href="{{ route('admin.university-events.index') }}" class="btn-tb btn-tb-ghost">Cancel</a>
    <button type="submit" class="btn-tb btn-tb-primary"><i class="fas fa-check"></i> Save</button>
  </div>
</div>
</form>
@endsection
