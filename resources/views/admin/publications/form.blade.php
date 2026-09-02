@extends('layouts.admin')
@section('title', $item->exists ? 'Edit Publication' : 'New Publication')

@section('content')

<div class="tb-page-header">
  <div><h1>{{ $item->exists ? 'Edit Publication' : 'New Publication' }}</h1>
    <div class="tb-breadcrumb"><a href="{{ route('admin.publications.index') }}">Publications</a> <span>/</span> {{ $item->exists ? \Illuminate\Support\Str::limit($item->title, 40) : 'Create' }}</div>
  </div>
</div>

<form method="POST" enctype="multipart/form-data"
      action="{{ $item->exists ? route('admin.publications.update', $item) : route('admin.publications.store') }}">
@csrf
@if($item->exists) @method('PUT') @endif
<div class="tb-card">
  <div class="tb-card-body">
    <div class="tb-form-grid">
      <div class="tb-form-group full">
        <label class="tb-label">Title *</label>
        <textarea class="tb-textarea" name="title" rows="2" required maxlength="500">{{ old('title', $item->title) }}</textarea>
        @error('title')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">URL slug</label>
        <input class="tb-input" type="text" name="slug" value="{{ old('slug', $item->slug) }}" maxlength="220" placeholder="auto from title">
        @error('slug')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Type *</label>
        <select class="tb-input" name="type" required>
          @foreach(\App\Models\Publication::TYPES as $val => $label)
            <option value="{{ $val }}" @selected(old('type', $item->type ?? 'journal') === $val)>{{ $label }}</option>
          @endforeach
        </select>
        @error('type')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Abstract</label>
        <textarea class="tb-textarea" name="abstract" rows="5">{{ old('abstract', $item->abstract) }}</textarea>
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Authors</label>
        <textarea class="tb-textarea" name="authors" rows="3" placeholder="One author per line" aria-describedby="authors-help">{{ old('authors', $item->authorRows->map(fn ($row) => $row->scholar?->name ?? $row->external_name)->implode("\n")) }}</textarea>
        <p class="tb-field-error" style="color:var(--mt);" id="authors-help">One per line, in order. A line matching an MRU scholar's name links their profile; anything else is kept as an external co-author.</p>
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Journal name</label>
        <input class="tb-input" type="text" name="journal_name" value="{{ old('journal_name', $item->journal_name) }}" maxlength="255">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Publisher</label>
        <input class="tb-input" type="text" name="publisher" value="{{ old('publisher', $item->publisher) }}" maxlength="255">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Volume</label>
        <input class="tb-input" type="text" name="volume" value="{{ old('volume', $item->volume) }}" maxlength="50">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Issue</label>
        <input class="tb-input" type="text" name="issue" value="{{ old('issue', $item->issue) }}" maxlength="50">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Pages</label>
        <input class="tb-input" type="text" name="pages" value="{{ old('pages', $item->pages) }}" maxlength="50" placeholder="e.g. 12-28">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Publication date</label>
        <input class="tb-input" type="date" name="publication_date" value="{{ old('publication_date', $item->publication_date?->format('Y-m-d')) }}">
        @error('publication_date')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">DOI</label>
        <input class="tb-input" type="text" name="doi" value="{{ old('doi', $item->doi) }}" maxlength="120" placeholder="10.xxxx/xxxxx">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">URL</label>
        <input class="tb-input" type="text" name="url" value="{{ old('url', $item->url) }}" maxlength="500" placeholder="https://...">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">PDF</label>
        <input class="tb-input" type="file" name="pdf" accept="application/pdf">
        @if($item->pdf_path)
          <a href="{{ asset('storage/'.$item->pdf_path) }}" target="_blank" rel="noopener" style="margin-top:8px;font-size:12px;"><i class="fas fa-file-pdf"></i> Current PDF</a>
        @endif
        @error('pdf')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Keywords</label>
        <input class="tb-input" type="text" name="keywords" value="{{ old('keywords', $item->keywords) }}" maxlength="500" placeholder="Comma separated">
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Citations</label>
        <input class="tb-input" type="number" name="citations" value="{{ old('citations', $item->citations) }}" min="0">
        @error('citations')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group">
        <label class="tb-label">Status *</label>
        <select class="tb-input" name="status" required>
          @foreach(\App\Models\Publication::STATUSES as $status)
            <option value="{{ $status }}" @selected(old('status', $item->status ?? 'published') === $status)>{{ ucfirst($status) }}</option>
          @endforeach
        </select>
        @error('status')<p class="tb-field-error">{{ $message }}</p>@enderror
      </div>
      <div class="tb-form-group full">
        <label class="tb-label">Research areas</label>
        <div style="display:flex;flex-wrap:wrap;gap:8px 18px;">
          @forelse($researchAreas as $area)
            <label class="tb-check-group">
              <input type="checkbox" name="research_areas[]" value="{{ $area->id }}"
                     @checked(in_array($area->id, old('research_areas', $item->researchAreas->pluck('id')->all())))>
              {{ $area->name }}
            </label>
          @empty
            <p class="muted" style="font-size:12px;">No research areas yet — add them under Research areas.</p>
          @endforelse
        </div>
      </div>
      <div class="tb-form-group" style="justify-content:flex-end;">
        <label class="tb-check-group">
          <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $item->is_featured))>
          Featured on the Scholar homepage
        </label>
      </div>
    </div>
  </div>
  <div class="tb-card-footer" style="display:flex;gap:10px;justify-content:flex-end;">
    <a href="{{ route('admin.publications.index') }}" class="btn-tb btn-tb-ghost">Cancel</a>
    <button type="submit" class="btn-tb btn-tb-primary"><i class="fas fa-check"></i> Save</button>
  </div>
</div>
</form>
@endsection
