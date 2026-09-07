@extends('layouts.odel')
@section('title', 'How ODEL works | Muteesa I Royal University')
@section('desc', 'Distance learning and flexible and blended learning at Muteesa I Royal University: what each means, how you enrol, induction, contact hours, and the learning platform your course runs on.')

@section('content')

@include('university.odel.partials.hero', [
  'eyebrow' => 'How It Works',
  'title' => 'What actually happens when you study this way',
  'lead' => 'Two terms do most of the work in the University\'s policies. They are not the same thing, and which one applies changes how your week looks.',
  'trail' => [['label' => 'How it works']],
])

<section style="padding-top:var(--s-6);">
  <div class="wrap">
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(min(320px,100%),1fr));gap:22px;">
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-tower-broadcast" aria-hidden="true"></i></span>
        <h3>Distance Learning</h3>
        <p>A formal educational process in which you receive instruction through an alternative
           delivery method. Teacher and student — or supervisor and research student — are
           physically in separate locations. Some or all of the teaching is delivered by that
           alternative method, in addition to or in place of face-to-face instruction.</p>
        <span class="od-clause">DLP §1.1</span>
      </div>
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-shuffle" aria-hidden="true"></i></span>
        <h3>Flexible and Blended Learning</h3>
        <p>A combination of distance learning and real-time educational practice. It involves
           spending a significant period of your studies away from the University on a project,
           fieldwork or research — either independently, or under another approved organisation
           which may be based elsewhere.</p>
        <span class="od-clause">DLP §1.1</span>
      </div>
    </div>
  </div>
</section>

<section class="band-surface">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">The Sequence</p>
      <h2>From enquiry to your first assessment</h2>
    </div>
    <div class="od-list" data-rise>
      <div class="od-item">
        <p><strong>Enrolment.</strong> Enrolment and re-enrolment are conducted by physical visit,
           by post, and/or online, as required by the Academic Registrar. The Registry provides
           tailored letters for distance students.</p>
        <span class="od-clause">DLP §6.1</span>
      </div>
      <div class="od-item">
        <p><strong>Induction.</strong> You may be required to attend a formal induction at either
           campus. Distance students are guided to online resources that induct them into studying
           this way.</p>
        <span class="od-clause">DLP §6.2</span>
      </div>
      <div class="od-item">
        <p><strong>Contact hours.</strong> How many contact-hour equivalents attach to each part of
           your programme is decided by the curriculum committees at Department, Faculty and Senate
           level — not by the delivery method.</p>
        <span class="od-clause">DLP §6.3</span>
      </div>
      <div class="od-item">
        <p><strong>Teaching and assessment.</strong> All communication runs through the secured
           learning management system: contact with your instructor, collaboration with peers,
           submission of assignments, and access to your grades.</p>
        <span class="od-clause">DLP §8.3</span>
      </div>
      <div class="od-item">
        <p><strong>Fees.</strong> Tuition fees are set by the University Council. Distance students
           should check the University website and the Admissions Office for current figures.</p>
        <span class="od-clause">DLP §7.1.6</span>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Where The Learning Lives</p>
      <h2>The platforms</h2>
      <p>Academic integrity depends on everything running through one secured system, so where you
         work matters as much as what you study.</p>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(min(280px,100%),1fr));">
      <a href="{{ $links['elearning'] ?? 'https://elearning.mru.ac.ug/' }}" rel="external" class="proj-card" data-rise>
        <span class="client">Learning platform</span>
        <h3>The e-Learning portal</h3>
        <p>The University's learning management system, where courses, materials, submissions and
           grades live.</p>
        <span class="link">Open the portal <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></span>
      </a>
      <a href="{{ route('courses.index') }}" wire:navigate class="proj-card" data-rise>
        <span class="client">Short courses</span>
        <h3>{{ $courses }} online courses</h3>
        <p>Short, self-paced courses with verifiable certificates — the quickest way to see how
           studying online with MRU feels before committing to a programme.</p>
        <span class="link">Browse the courses <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
      <a href="{{ route('odel.platform') }}" wire:navigate class="proj-card" data-rise>
        <span class="client">In detail</span>
        <h3>What the platform does</h3>
        <p>Course spaces, chapters and topics, online assignments and quizzes, attendance and your
           timetable — each with an honest note on whether it is in daily use.</p>
        <span class="link">The ODEL platform <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
      <a href="{{ route('library') }}" wire:navigate class="proj-card" data-rise>
        <span class="client">Research</span>
        <h3>The University Library</h3>
        <p>E-resources, databases and research support, reachable from wherever you are studying.</p>
        <span class="link">Library services <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
    </div>
  </div>
</section>

@endsection
