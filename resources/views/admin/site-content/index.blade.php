@extends('layouts.admin')
@section('title', 'Website content')

@section('content')

<div class="tb-page-header">
  <div>
    <h1>Website content</h1>
    <div class="tb-breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> Website content</div>
  </div>
</div>

<p class="sc-intro">These are the words and pictures on the public site. Saving here publishes immediately.</p>

<div class="sc-grid">
  @foreach($sections as $key => $section)
    @php $count = count(\App\Support\SiteContent::value($key)); @endphp
    <a href="{{ route('admin.site-content.edit', $key) }}" wire:navigate class="tb-card sc-card">
      <span class="sc-ic"><i class="fas {{ $section['icon'] }}"></i></span>
      <h3>{{ $section['label'] }}</h3>
      <p>{{ $section['blurb'] }}</p>
      <span class="sc-meta">
        {{ $section['type'] === 'repeater'
            ? $count.' '.\Illuminate\Support\Str::plural('entry', $count)
            : count($section['fields']).' fields' }}
        <i class="fas fa-arrow-right"></i>
      </span>
    </a>
  @endforeach
</div>
@endsection
