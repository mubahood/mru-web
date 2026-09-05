@extends('layouts.marketing')
@section('title', 'Faculties & Schools | Muteesa I Royal University')
@section('desc', 'Five faculties and the Graduate School: Education; Business & Management; Social Sciences, Arts and Humanities; Science, Technology, Engineering, Art and Design; and postgraduate study.')

@section('content')

@include('university.partials.page-hero', [
  'eyebrow' => 'Academics',
  'title' => 'Faculties & Schools',
  'lead' => 'Every programme belongs to a faculty that teaches it, researches it, and walks you into a career with it.',
])

<section>
  <div class="wrap">
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:18px;">
      @foreach($faculties as $faculty)
        <a href="{{ route('faculties.show', $faculty) }}" wire:navigate class="proj-card faculty-index-card" data-rise>
          {{-- Each faculty already carries a cover photograph, set through the
               admin uploader and used on the homepage; this page had been
               ignoring it and showing an icon instead. --}}
          @if($faculty->cover_image)
            <span class="fi-media">
              <img src="{{ asset('storage/'.$faculty->cover_image) }}" alt=""
                   width="800" height="1000" loading="lazy" decoding="async">
            </span>
          @endif
          <div style="display:flex;align-items:center;gap:12px;">
            <span class="ic" style="width:44px;height:44px;background:var(--pri);color:var(--gold);display:flex;align-items:center;justify-content:center;font-size:18px;">
              <i class="fas {{ $faculty->icon }}" aria-hidden="true"></i>
            </span>
            @if($faculty->short_name)<span class="tag">{{ $faculty->short_name }}</span>@endif
          </div>
          <h3>{{ $faculty->name }}</h3>
          @if($faculty->tagline)<span class="client" style="text-transform:none;letter-spacing:0;">{{ ucfirst($faculty->tagline) }}</span>@endif
          <p>{{ \Illuminate\Support\Str::limit($faculty->description ?: $faculty->about, 140) }}</p>
          @if($faculty->departments)
            <div class="tag-row">
              @foreach(array_slice($faculty->departments, 0, 3) as $department)
                <span class="pill">{{ $department }}</span>
              @endforeach
            </div>
          @endif
          <span class="link" style="margin-top:auto;">
            {{ $faculty->programmes_count }} {{ \Illuminate\Support\Str::plural('programme', $faculty->programmes_count) }}
            <i class="fas fa-arrow-right"></i>
          </span>
        </a>
      @endforeach
    </div>
  </div>
</section>

<section class="band-surface tex-glow">
  <div class="wrap">
    <div class="sec-head">
      <h2>Two campuses, one university</h2>
      <p>Faculties teach at the Kakeeka Campus in Mengo, Kampala and the Kirumba Campus in Masaka.
         Your admission letter names your campus.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));max-width:680px;margin:0 auto;">
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-city" aria-hidden="true"></i></span>
        <h3>Kakeeka Campus</h3>
        <p>Mengo, Kampala</p>
      </div>
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-mountain-sun" aria-hidden="true"></i></span>
        <h3>Kirumba Campus</h3>
        <p>Kirumba, Masaka</p>
      </div>
    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
