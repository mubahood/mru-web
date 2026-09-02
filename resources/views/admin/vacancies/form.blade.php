@extends('layouts.admin')
@section('title', $item->exists ? 'Edit Vacancy' : 'New Vacancy')

@section('content')

<div class="tb-page-header">
  <div><h1>{{ $item->exists ? 'Edit Vacancy' : 'New Vacancy' }}</h1>
    <div class="tb-breadcrumb"><a href="{{ route('admin.vacancies.index') }}">Vacancies</a> <span>/</span> {{ $item->exists ? $item->title : 'Create' }}</div>
  </div>
</div>

<form method="POST" enctype="multipart/form-data"
      action="{{ $item->exists ? route('admin.vacancies.update', $item) : route('admin.vacancies.store') }}">
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
        <label class="tb-label">Reference no</label>
        <input class="tb-input" type="text" name="reference_no" value="{{ old('reference_no', $item->reference_no) }}" maxlength="50">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Department</label>
        <input class="tb-input" type="text" name="department" value="{{ old('department', $item->department) }}" maxlength="255">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Type</label>
        <input class="tb-input" type="text" name="type" value="{{ old('type', $item->type) }}" maxlength="40" placeholder="e.g. Full-time">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Location</label>
        <input class="tb-input" type="text" name="location" value="{{ old('location', $item->location) }}" maxlength="80">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Application deadline</label>
        <input class="tb-input" type="date" name="deadline_on" value="{{ old('deadline_on', $item->deadline_on?->format('Y-m-d')) }}">
        @error('deadline_on')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Summary</label>
        <textarea class="tb-textarea" name="summary" rows="3">{{ old('summary', $item->summary) }}</textarea>
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Requirements</label>
        <textarea class="tb-textarea" name="requirements" rows="5">{{ old('requirements', $item->requirements) }}</textarea>
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Attachment (PDF)</label>
        <input class="tb-input" type="file" name="attachment" accept="application/pdf">
        @if($item->attachment)
          <a href="{{ asset('storage/'.$item->attachment) }}" target="_blank" rel="noopener" style="margin-top:8px;font-size:12px;"><i class="fas fa-file-pdf"></i> Current attachment</a>
        @endif
        @error('attachment')<p class="tb-field-error">{{ $message }}</p>@enderror
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
    <a href="{{ route('admin.vacancies.index') }}" class="btn-tb btn-tb-ghost">Cancel</a>
    <button type="submit" class="btn-tb btn-tb-primary"><i class="fas fa-check"></i> Save</button>
  </div>
</div>
</form>
@endsection
