@extends('layouts.odel')
@section('title', 'Quality and assessment | ODEL, Muteesa I Royal University')
@section('desc', 'The eight tests a programme must pass before it can be taught by distance, and how Muteesa I Royal University assures quality, assessment and academic integrity in ODEL.')

@section('content')

@include('university.odel.partials.hero', [
  'eyebrow' => 'Quality',
  'title' => 'Is a distance degree worth the same?',
  'lead' => 'The policy answers that before the question is asked. A programme delivered by
             distance "is expected to be of equal quality to any other programme leading to a MRU
             award", and it is validated in exactly the same way.',
  'trail' => [['label' => 'Quality']],
])

<section style="padding-top:var(--s-6);">
  <div class="wrap" style="max-width:920px;">
    <div class="sec-head left">
      <p class="eyebrow">Before It Can Be Taught</p>
      <h2>Eight tests a programme must pass</h2>
      <p>From section 5.0 of the Distance Learning Policy. All eight must be met before a degree
         can be offered in an alternative delivery format.</p>
    </div>
    <div class="od-list" data-rise>
      @foreach($criteria as $c)
        <div class="od-item"><p>{{ $c['text'] }}</p><span class="od-clause">DLP §5.0</span></div>
      @endforeach
    </div>
  </div>
</section>

<section class="band-surface">
  <div class="wrap">
    <div class="sec-head left">
      <p class="eyebrow">Keeping It That Way</p>
      <h2>Assurance, assessment and integrity</h2>
    </div>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(min(300px,100%),1fr));">
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-clipboard-check" aria-hidden="true"></i></span>
        <h3>The Faculties' role</h3>
        <p>Faculties work with the administration to establish and monitor teaching through ongoing
           assessment and evaluation, and to keep courses updated. Instructors are to be given help
           converting materials for online delivery, training in the tools, guidance on copyright
           and fair use, and support for quality enhancement.</p>
        <span class="od-clause">DLP §8.1</span>
      </div>
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-file-shield" aria-hidden="true"></i></span>
        <h3>Assessment and testing</h3>
        <p>A DL Assessment and Testing Unit within the Academic Registrar's Office is to provide
           secure testing environments, run mentor, instructor and course evaluations for both
           distance and face-to-face courses, and support instructors in designing secure
           assessment.</p>
        <div style="margin-top:10px;">@include('university.odel.partials.status', ['status' => \App\Support\Odel::COMMITTED])</div>
        <span class="od-clause">DLP §8.2</span>
      </div>
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-lock" aria-hidden="true"></i></span>
        <h3>Academic integrity</h3>
        <p>All communication is managed through the secured learning management system — contact
           with the instructor, collaboration with peers, submission of assignments and access to
           grades. Instructors using high-stakes examinations coordinate with the Assessment and
           Testing Unit.</p>
        <span class="od-clause">DLP §8.3</span>
      </div>
      <div class="card" data-rise>
        <span class="ic"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
        <h3>Reporting and auditing</h3>
        <p>The Office of Distance Learning maintains aggregate data and answers requests from
           internal and external bodies, and works with the Academic Registrar's Office on reports
           covering retention rates, course sections, enrolments and trends.</p>
        <span class="od-clause">DLP §8.4</span>
      </div>
    </div>

    <div class="od-note" data-rise style="margin-top:var(--s-5);">
      <i class="fas fa-landmark" aria-hidden="true"></i>
      <p><strong>External standard.</strong> The eighth approval test is that the programme adheres
         to the National Council for Higher Education's guidelines on distance and correspondence
         education. MRU is an NCHE-accredited university, and ODEL provision is not exempt from
         that accreditation.</p>
    </div>
  </div>
</section>

@endsection
