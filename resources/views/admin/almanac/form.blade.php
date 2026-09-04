@extends('layouts.admin')
@section('title', $item->exists ? 'Edit Almanac Entry' : 'New Almanac Entry')

@section('content')

<div class="tb-page-header">
  <div><h1>{{ $item->exists ? 'Edit Almanac Entry' : 'New Almanac Entry' }}</h1>
    <div class="tb-breadcrumb"><a href="{{ route('admin.almanac.index') }}">Almanac</a> <span>/</span> {{ $item->exists ? 'Edit' : 'Create' }}</div>
  </div>
</div>

<form method="POST" action="{{ $item->exists ? route('admin.almanac.update', $item) : route('admin.almanac.store') }}">
@csrf
@if($item->exists) @method('PUT') @endif
<div class="tb-card">
  <div class="tb-card-body">
    <div class="tb-form-grid">
      <div class="tb-form-group">
        <label class="tb-label">Academic year *</label>
        <input class="tb-input" type="text" name="academic_year" value="{{ old('academic_year', $item->academic_year) }}" required maxlength="20" placeholder="e.g. 2026/2027">
        @error('academic_year')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Semester *</label>
        <input class="tb-input" type="text" name="semester" value="{{ old('semester', $item->semester) }}" required maxlength="40" placeholder="e.g. Semester One">
        @error('semester')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Activity *</label>
        <input class="tb-input" type="text" name="activity" value="{{ old('activity', $item->activity) }}" required maxlength="300">
        @error('activity')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Period</label>
        <input class="tb-input" type="text" name="period" value="{{ old('period', $item->period) }}" maxlength="80" placeholder="e.g. Week 1 - Week 3">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Starts on</label>
        <input class="tb-input" type="date" name="starts_on" value="{{ old('starts_on', $item->starts_on?->format('Y-m-d')) }}">
        @error('starts_on')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Ends on</label>
        <input class="tb-input" type="date" name="ends_on" value="{{ old('ends_on', $item->ends_on?->format('Y-m-d')) }}">
        @error('ends_on')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Person(s) / office in charge</label>
        <input class="tb-input" type="text" name="responsible" value="{{ old('responsible', $item->responsible) }}" maxlength="255" placeholder="e.g. Academic Registrar / Examinations Office">
        @error('responsible')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Category</label>
        <select class="tb-input" name="category">
          <option value="">— None —</option>
          @foreach(\App\Models\AlmanacEntry::CATEGORIES as $key => $meta)
            <option value="{{ $key }}" @selected(old('category', $item->category) === $key)>{{ $meta[0] }}</option>
          @endforeach
        </select>
        @error('category')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Sort order</label>
        <input class="tb-input" type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order) }}">
      </div>
      <div class="tb-form-group">
        <label class="tb-label" style="display:flex;align-items:center;gap:8px;">
          <input type="checkbox" name="is_key_date" value="1" @checked(old('is_key_date', $item->is_key_date))>
          Key date (may appear on the homepage)
        </label>
      </div>
    </div>
  </div>
  <div class="tb-card-footer" style="display:flex;gap:10px;justify-content:flex-end;">
    <a href="{{ route('admin.almanac.index') }}" class="btn-tb btn-tb-ghost">Cancel</a>
    <button type="submit" class="btn-tb btn-tb-primary"><i class="fas fa-check"></i> Save</button>
  </div>
</div>
</form>
@endsection
