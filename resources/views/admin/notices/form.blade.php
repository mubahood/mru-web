@extends('layouts.admin')
@section('title', $item->exists ? 'Edit notice' : 'New notice')

@section('content')

<div class="tb-page-header">
  <div>
    <h1>{{ $item->exists ? 'Edit notice' : 'New notice' }}</h1>
    <div class="tb-breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span>
      <a href="{{ route('admin.notices.index') }}">Notice strip</a> <span>/</span> {{ $item->exists ? 'Edit' : 'New' }}</div>
  </div>
</div>

<form method="POST" action="{{ $item->exists ? route('admin.notices.update', $item) : route('admin.notices.store') }}">
  @csrf
  @if($item->exists) @method('PUT') @endif

  <div class="tb-card">
    <div class="tb-card-body">
      <div class="tb-form-group">
        <label for="message">Message <span style="color:var(--danger);">*</span></label>
        <input type="text" id="message" name="message" class="tb-input" maxlength="300" required
               value="{{ old('message', $item->message) }}"
               placeholder="Applications for the January 2027 intake are open.">
        <small class="muted">One sentence. The strip is a headline, not a paragraph.</small>
        @error('message')<div class="tb-error">{{ $message }}</div>@enderror
      </div>

      <div class="tb-form-row">
        <div class="tb-form-group">
          <label for="label">Tag</label>
          <input type="text" id="label" name="label" class="tb-input" maxlength="60"
                 value="{{ old('label', $item->label) }}" placeholder="Now open">
          <small class="muted">The small pill before the message. Optional.</small>
        </div>
        <div class="tb-form-group">
          <label for="icon">Icon</label>
          <input type="text" id="icon" name="icon" class="tb-input" maxlength="40"
                 value="{{ old('icon', $item->icon) }}" placeholder="fa-graduation-cap">
          <small class="muted">A Font Awesome name, e.g. <code>fa-bullhorn</code>.</small>
        </div>
      </div>

      <div class="tb-form-row">
        <div class="tb-form-group">
          <label for="link_url">Action link</label>
          <input type="url" id="link_url" name="link_url" class="tb-input" maxlength="500"
                 value="{{ old('link_url', $item->link_url) }}" placeholder="https://eportal.mru.ac.ug/apply/">
          @error('link_url')<div class="tb-error">{{ $message }}</div>@enderror
        </div>
        <div class="tb-form-group">
          <label for="link_label">Button text</label>
          <input type="text" id="link_label" name="link_label" class="tb-input" maxlength="60"
                 value="{{ old('link_label', $item->link_label) }}" placeholder="Apply now">
          <small class="muted">Left blank with a link set, this becomes “Read more”.</small>
        </div>
      </div>

      <div class="tb-form-group">
        <label for="template">Template <span style="color:var(--danger);">*</span></label>
        <select id="template" name="template" class="tb-input" required>
          @foreach(\App\Models\SiteNotice::TEMPLATES as $key => $desc)
            <option value="{{ $key }}" @selected(old('template', $item->template) === $key)>{{ $desc }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>

  <div class="tb-card" style="margin-top:16px;">
    <div class="tb-card-head"><b>When it runs</b></div>
    <div class="tb-card-body">
      <div class="tb-form-row">
        <div class="tb-form-group">
          <label for="starts_at">Starts</label>
          <input type="datetime-local" id="starts_at" name="starts_at" class="tb-input"
                 value="{{ old('starts_at', $item->starts_at?->format('Y-m-d\TH:i')) }}">
          <small class="muted">Blank means immediately.</small>
        </div>
        <div class="tb-form-group">
          <label for="ends_at">Ends</label>
          <input type="datetime-local" id="ends_at" name="ends_at" class="tb-input"
                 value="{{ old('ends_at', $item->ends_at?->format('Y-m-d\TH:i')) }}">
          <small class="muted">Blank means it runs until unpublished. Set this and the notice retires itself.</small>
          @error('ends_at')<div class="tb-error">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="tb-form-group">
        <label for="deadline_at">Deadline to count down to</label>
        <input type="datetime-local" id="deadline_at" name="deadline_at" class="tb-input"
               value="{{ old('deadline_at', $item->deadline_at?->format('Y-m-d\TH:i')) }}">
        <small class="muted">Shows “N days left”. Often earlier than the end date — the day applications
          close, not the day you stop mentioning it.</small>
      </div>

      <div class="tb-form-row">
        <div class="tb-form-group">
          <label for="sort_order">Sort order</label>
          <input type="number" id="sort_order" name="sort_order" class="tb-input" min="0" max="9999"
                 value="{{ old('sort_order', $item->sort_order ?? 0) }}">
          <small class="muted">Lower shows first. At most five run at once.</small>
        </div>
        <div class="tb-form-group">
          <label>Options</label>
          <label class="tb-check"><input type="checkbox" name="is_published" value="1"
            @checked(old('is_published', $item->is_published ?? true))> Published</label>
          <label class="tb-check"><input type="checkbox" name="is_dismissible" value="1"
            @checked(old('is_dismissible', $item->is_dismissible ?? true))> Reader can dismiss it</label>
        </div>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:10px;margin-top:16px;">
    <button type="submit" class="btn-tb btn-tb-primary"><i class="fas fa-save"></i> {{ $item->exists ? 'Save changes' : 'Create notice' }}</button>
    <a href="{{ route('admin.notices.index') }}" class="btn-tb btn-tb-ghost">Cancel</a>
  </div>
</form>

@endsection
