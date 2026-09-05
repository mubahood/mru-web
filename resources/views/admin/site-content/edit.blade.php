@extends('layouts.admin')
@section('title', $section['label'])

@php
  $render = function (string $path, array $def, $val) {
      $type = $def['type'] ?? 'text';
      $id = 'f_'.substr(md5($path), 0, 10);
      $shown = is_array($val) ? implode("\n", $val) : (string) $val;
      ob_start(); ?>
      <div class="tb-form-group">
        <label class="tb-label" for="<?= $id ?>"><?= e($def['label']) ?></label>
        <?php if (in_array($type, ['textarea', 'lines'], true)): ?>
          <textarea class="tb-textarea" id="<?= $id ?>" name="<?= e($path) ?>" rows="<?= $type === 'lines' ? 5 : 3 ?>"><?= e($shown) ?></textarea>
        <?php else: ?>
          <input class="tb-input" id="<?= $id ?>" type="<?= in_array($type, ['url', 'email'], true) ? $type : 'text' ?>"
                 name="<?= e($path) ?>" value="<?= e($shown) ?>">
        <?php endif; ?>
        <?php if (! empty($def['help'])): ?><p class="sc-help"><?= e($def['help']) ?></p><?php endif; ?>
      </div>
      <?php return ob_get_clean();
  };
@endphp

@section('content')

<div class="tb-page-header">
  <div>
    <h1>{{ $section['label'] }}</h1>
    <div class="tb-breadcrumb">
      <a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span>
      <a href="{{ route('admin.site-content.index') }}">Website content</a> <span>/</span> {{ $section['label'] }}
    </div>
  </div>
</div>

@if(session('success'))
  <div class="tb-alert tb-alert-success"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
@endif

<p class="sc-intro">{{ $section['blurb'] }}</p>

<form method="POST" action="{{ route('admin.site-content.update', $key) }}">
  @csrf @method('PUT')
  <div class="tb-card">
    <div class="tb-card-body">
      @if($section['type'] === 'repeater')
        @foreach($value as $i => $row)
          <fieldset class="sc-row">
            <legend>Entry {{ $i + 1 }}</legend>
            @foreach($section['fields'] as $name => $def)
              {!! $render("content[$i][$name]", $def, $row[$name] ?? '') !!}
            @endforeach
            <p class="sc-help">Clear every field in this entry to remove it.</p>
          </fieldset>
        @endforeach
        <fieldset class="sc-row sc-row-new">
          <legend><i class="fas fa-plus"></i> Add a new entry</legend>
          @foreach($section['fields'] as $name => $def)
            {!! $render('content['.count($value).']['.$name.']', $def, '') !!}
          @endforeach
        </fieldset>
      @else
        @foreach($section['fields'] as $name => $def)
          {!! $render("content[$name]", $def, $value[$name] ?? '') !!}
        @endforeach
      @endif
    </div>
    <div class="tb-card-footer" style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('admin.site-content.index') }}" wire:navigate class="btn-tb btn-tb-ghost">Cancel</a>
      <button type="submit" class="btn-tb btn-tb-primary"><i class="fas fa-check"></i> Save changes</button>
    </div>
  </div>
</form>
@endsection
