@extends('layouts.admin')
@section('title', $item->exists ? 'Edit Staff Member' : 'New Staff Member')

@section('content')

<div class="tb-page-header">
  <div><h1>{{ $item->exists ? 'Edit Staff Member' : 'New Staff Member' }}</h1>
    <div class="tb-breadcrumb"><a href="{{ route('admin.staff-members.index') }}">Staff</a> <span>/</span> {{ $item->exists ? $item->name : 'Create' }}</div>
  </div>
</div>

<form method="POST" enctype="multipart/form-data"
      action="{{ $item->exists ? route('admin.staff-members.update', $item) : route('admin.staff-members.store') }}">
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
        <label class="tb-label">Title / position</label>
        <input class="tb-input" type="text" name="title" value="{{ old('title', $item->title) }}" maxlength="255" placeholder="e.g. Senior Lecturer">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Role *</label>
        <select class="tb-input" name="staff_role" required>
          @foreach(\App\Models\StaffMember::ROLES as $val => $label)
            <option value="{{ $val }}" @selected(old('staff_role', $item->staff_role ?? 'other') === $val)>{{ $label }}</option>
          @endforeach
        </select>
        @error('staff_role')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Group label</label>
        <input class="tb-input" type="text" name="group_label" value="{{ old('group_label', $item->group_label) }}" maxlength="100" placeholder="e.g. University Council">
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
        <label class="tb-label">Department</label>
        <input class="tb-input" type="text" name="department" value="{{ old('department', $item->department) }}" maxlength="255">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Email</label>
        <input class="tb-input" type="email" name="email" value="{{ old('email', $item->email) }}" maxlength="255">
        @error('email')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Phone</label>
        <input class="tb-input" type="text" name="phone" value="{{ old('phone', $item->phone) }}" maxlength="32">
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Bio</label>
        <textarea class="tb-textarea" name="bio" rows="4">{{ old('bio', $item->bio) }}</textarea>
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Education</label>
        <textarea class="tb-textarea" name="education" rows="3">{{ old('education', $item->education) }}</textarea>
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Photo</label>
        <input class="tb-input" type="file" name="photo" accept="image/jpeg,image/png,image/webp">
        @if($item->photo)
          <img src="{{ asset('storage/'.$item->photo) }}" alt="" style="margin-top:8px;max-height:90px;border:1px solid var(--line);">
        @endif
        @error('photo')<p class="tb-field-error">{{ $message }}</p>@enderror
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
    <a href="{{ route('admin.staff-members.index') }}" class="btn-tb btn-tb-ghost">Cancel</a>
    <button type="submit" class="btn-tb btn-tb-primary"><i class="fas fa-check"></i> Save</button>
  </div>
</div>
</form>
@endsection
