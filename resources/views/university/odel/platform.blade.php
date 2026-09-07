@extends('layouts.odel')
@section('title', 'The ODEL platform | Muteesa I Royal University')
@section('desc', 'What you actually get when you study through ODEL at Muteesa I Royal University: an online space for every course, material organised into chapters and topics, online assignments and quizzes, attendance, and your own timetable.')

@section('content')

@include('university.odel.partials.hero', [
  'eyebrow' => 'The Platform',
  'title' => 'What you actually get',
  'lead' => 'The policies say what the University committed to. This is the system that does it —
             the screens you will be working in, listed plainly, with an honest note on each about
             whether it is in daily use.',
  'trail' => [['label' => 'The platform']],
])

<section style="padding-top:var(--s-6);">
  <div class="wrap">
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(min(320px,100%),1fr));gap:20px;">
      @foreach($platform as $f)
        <div class="card" data-rise>
          <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:10px;">
            <span class="ic"><i class="fas {{ $f['icon'] }}" aria-hidden="true"></i></span>
            @include('university.odel.partials.status', ['status' => $f['status']])
          </div>
          <h3>{{ $f['title'] }}</h3>
          <p>{{ $f['body'] }}</p>
        </div>
      @endforeach
    </div>

    <div class="od-note" data-rise style="margin-top:var(--s-6);">
      <i class="fas fa-circle-info" aria-hidden="true"></i>
      <p><strong>One label is not green, and that is on purpose.</strong> Carrying ODEL marks
         through to your official coursework mark is built and works, but it has not been used in
         production yet — coursework is still recorded the established way. We would rather tell
         you that than let you assume otherwise.</p>
    </div>
  </div>
</section>

<section class="band-surface">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Where To Sign In</p>
      <h2>Two doors, and they are not the same</h2>
      <p>People lose time here, so it is worth being explicit.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(min(300px,100%),1fr));">
      <a href="{{ $eportal }}" rel="external" class="proj-card" data-rise>
        <span class="client">Your course work</span>
        <h3>The Student Portal</h3>
        <p>Where ODEL runs. Your course spaces, material, assignments, quizzes, lectures,
           attendance and timetable are all here, behind your student sign-in.</p>
        <span class="link">Open the portal <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></span>
      </a>
      <a href="{{ route('courses.index') }}" wire:navigate class="proj-card" data-rise>
        <span class="client">Open to anyone</span>
        <h3>Short online courses</h3>
        <p>Separate from your programme, and needing no admission: short self-paced courses with
           verifiable certificates. The quickest way to find out whether studying online suits you.</p>
        <span class="link">Browse the courses <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
    </div>
  </div>
</section>

@endsection
