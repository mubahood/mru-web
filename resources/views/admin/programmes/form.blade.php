@extends('layouts.admin')
@section('title', $item->exists ? 'Edit Programme' : 'New Programme')

@section('content')

<div class="tb-page-header">
  <div><h1>{{ $item->exists ? 'Edit Programme' : 'New Programme' }}</h1>
    <div class="tb-breadcrumb"><a href="{{ route('admin.programmes.index') }}">Programmes</a> <span>/</span> {{ $item->exists ? $item->name : 'Create' }}</div>
  </div>
</div>

<form method="POST" enctype="multipart/form-data"
      action="{{ $item->exists ? route('admin.programmes.update', $item) : route('admin.programmes.store') }}">
@csrf
@if($item->exists) @method('PUT') @endif
<div class="tb-card">
  <div class="tb-card-body">
    <div class="tb-form-grid">
      <div class="tb-form-group full">
        <label class="tb-label">Name *</label>
        <input class="tb-input" type="text" name="name" value="{{ old('name', $item->name) }}" required maxlength="200">
        @error('name')<p class="tb-field-error">{{ $message }}</p>@enderror
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
        <label class="tb-label">URL slug</label>
        <input class="tb-input" type="text" name="slug" value="{{ old('slug', $item->slug) }}" maxlength="220" placeholder="auto from name">
        @error('slug')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Award code</label>
        <input class="tb-input" type="text" name="award_code" value="{{ old('award_code', $item->award_code) }}" maxlength="30" placeholder="e.g. BIT">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Level *</label>
        <select class="tb-input" name="level" required>
          @foreach(\App\Models\Programme::LEVELS as $val => $label)
            <option value="{{ $val }}" @selected(old('level', $item->level ?? 'bachelor') === $val)>{{ $label }}</option>
          @endforeach
        </select>
        @error('level')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Duration</label>
        <input class="tb-input" type="text" name="duration" value="{{ old('duration', $item->duration) }}" maxlength="50" placeholder="e.g. 3 Years">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Study modes</label>
        <textarea class="tb-textarea" name="study_modes" rows="3" placeholder="One mode per line, e.g. Full-time">{{ old('study_modes', implode("\n", $item->study_modes ?? [])) }}</textarea>
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Intake months</label>
        <textarea class="tb-textarea" name="intake_months" rows="3" placeholder="One month per line, e.g. August">{{ old('intake_months', implode("\n", $item->intake_months ?? [])) }}</textarea>
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Tuition per semester</label>
        <input class="tb-input" type="number" name="tuition_per_semester" value="{{ old('tuition_per_semester', $item->tuition_per_semester) }}" min="0">
        @error('tuition_per_semester')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Tuition currency</label>
        <input class="tb-input" type="text" name="tuition_currency" value="{{ old('tuition_currency', $item->tuition_currency ?? 'UGX') }}" maxlength="3">
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Tuition note</label>
        <input class="tb-input" type="text" name="tuition_note" value="{{ old('tuition_note', $item->tuition_note) }}" maxlength="255" placeholder="e.g. Verify with the Bursar's office">
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Entry requirements</label>
        <textarea class="tb-textarea" name="entry_requirements" rows="4">{{ old('entry_requirements', $item->entry_requirements) }}</textarea>
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Description</label>
        <textarea class="tb-textarea" name="description" rows="6">{{ old('description', $item->description) }}</textarea>
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Career prospects</label>
        <textarea class="tb-textarea" name="career_prospects" rows="4">{{ old('career_prospects', $item->career_prospects) }}</textarea>
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Image</label>
        <input class="tb-input" type="file" name="image" accept="image/jpeg,image/png,image/webp">
        @if($item->image)
          <img src="{{ asset('storage/'.$item->image) }}" alt="" style="margin-top:8px;max-height:90px;border:1px solid var(--line);">
        @endif
        @error('image')<p class="tb-field-error">{{ $message }}</p>@enderror
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
    <a href="{{ route('admin.programmes.index') }}" class="btn-tb btn-tb-ghost">Cancel</a>
    <button type="submit" class="btn-tb btn-tb-primary"><i class="fas fa-check"></i> Save</button>
  </div>
</div>
</form>
@endsection
