@extends('layouts.marketing')
@section('title', 'Academic Almanac '.($year ?? '').' | Muteesa I Royal University')
@section('desc', 'The Muteesa I Royal University academic almanac — semester dates, orientation, examinations, graduation, governance meetings and every key activity of the academic year, month by month.')

@section('content')

@include('university.partials.page-hero', [
  'eyebrow' => 'Academics',
  'title' => 'Academic Almanac',
  'lead' => 'Every date of the academic year — when semesters open, when examinations run, when the Council sits, and who is answerable for each activity.',
  'mark' => 'Almanac',
  'trail' => [['label' => 'Academics', 'url' => route('programmes.index')], ['label' => 'Academic almanac']],
])

@if($total === 0)
  <section>
    <div class="wrap">
      <p class="lead">The almanac for the coming academic year is being finalised. Please check back shortly.</p>
    </div>
  </section>
@else

{{-- The year's shape first: milestones, then the filterable month tables. --}}
<section>
  <div class="wrap">
    <div class="alm-head">
      <div class="sec-head left" style="margin-bottom:0;">
        <h2>{{ $year }} at a glance</h2>
        <p>Prepared by the Office of the Academic Registrar. {{ $total }} scheduled activities across
           two semesters, the recess and the year's standing commitments.</p>
      </div>
      @if($next)
        <aside class="alm-next" aria-label="Next key date">
          <span class="alm-next-label">Next key date</span>
          <strong>{{ $next->activity }}</strong>
          <span class="alm-next-when">
            <i class="fas fa-calendar-day" aria-hidden="true"></i> {{ $next->period }}
          </span>
        </aside>
      @endif
    </div>

    @if($keyDates->isNotEmpty())
      <ol class="alm-milestones" data-rise>
        @foreach($keyDates as $stop)
          @php $isPast = ($stop->ends_on ?? $stop->starts_on)->lt(today()); @endphp
          <li class="alm-milestone {{ $isPast ? 'is-past' : '' }}">
            <span class="alm-dot" aria-hidden="true"></span>
            <span class="alm-ms-when">{{ $stop->starts_on->format('j M Y') }}</span>
            <span class="alm-ms-what">{{ $stop->activity }}</span>
            @if($isPast)<span class="alm-ms-tag">Completed</span>@endif
          </li>
        @endforeach
      </ol>
    @endif
  </div>
</section>

<section class="band-surface tex-grid">
  <div class="wrap">
    <div class="sec-head left">
      <h2>The year, month by month</h2>
      <p>Filter by the kind of activity, or read the year straight through.</p>
    </div>

    {{-- Filtering is a progressive enhancement: without script every row is
         visible, which is the state that matters for a calendar. --}}
    <div class="alm-filters" data-alm-filters role="group" aria-label="Filter activities by type">
      <button type="button" class="alm-chip is-active" data-alm-filter="all" aria-pressed="true">
        All activities
      </button>
      @foreach($categories as $key)
        @php $meta = \App\Models\AlmanacEntry::CATEGORIES[$key] ?? null; @endphp
        @if($meta)
          <button type="button" class="alm-chip" data-alm-filter="{{ $key }}" aria-pressed="false">
            <i class="fas {{ $meta[1] }}" aria-hidden="true"></i> {{ $meta[0] }}
          </button>
        @endif
      @endforeach
    </div>
    <p class="alm-empty" data-alm-empty hidden>No activities of that kind are scheduled.</p>

    @foreach($months as $key => $entries)
      @php $first = $entries->first(); @endphp
      <section class="alm-month" data-alm-month aria-labelledby="month-{{ $key }}">
        <h3 class="alm-month-head" id="month-{{ $key }}">
          <span>{{ $first->starts_on->format('F Y') }}</span>
          <span class="alm-month-sem">{{ $first->semester }}</span>
        </h3>
        <div class="alm-table-wrap">
          <table class="alm-table">
            <caption class="sr-only">{{ $first->starts_on->format('F Y') }} activities, with dates and the office in charge</caption>
            <thead>
              <tr>
                <th scope="col" style="width:20%;">Date</th>
                <th scope="col">Activity</th>
                <th scope="col" style="width:26%;">Office in charge</th>
              </tr>
            </thead>
            <tbody>
              @foreach($entries as $entry)
                <tr data-alm-row data-alm-cat="{{ $entry->category }}"
                    class="{{ $entry->category === 'holiday' ? 'is-holiday' : '' }}">
                  <td class="alm-when">{{ $entry->period }}</td>
                  <td>
                    <span class="alm-activity">{{ $entry->activity }}</span>
                    @if($entry->category)
                      <span class="alm-cat">
                        <i class="fas {{ $entry->categoryIcon() }}" aria-hidden="true"></i>
                        {{ $entry->categoryLabel() }}
                      </span>
                    @endif
                  </td>
                  <td class="alm-who">{{ $entry->responsible }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </section>
    @endforeach
  </div>
</section>

@if($ongoing->isNotEmpty())
<section>
  <div class="wrap">
    <div class="sec-head left">
      <h2>Running through the year</h2>
      <p>Activities that span more than one month or continue throughout the academic year.
         Detailed implementation dates sit in the responsible faculty's workplan.</p>
    </div>
    <div class="alm-ongoing">
      @foreach($ongoing as $item)
        <div class="alm-ongoing-item" data-rise>
          <span class="ic"><i class="fas {{ $item->categoryIcon() }}" aria-hidden="true"></i></span>
          <div>
            <span class="alm-ongoing-when">{{ $item->period }}</span>
            <p>{{ $item->activity }}</p>
            @if($item->responsible)<span class="alm-who">{{ $item->responsible }}</span>@endif
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- The almanac is dense with institutional shorthand; spelling it out costs
     one quiet block and saves a reader guessing at "FSTEAD" or "CGC". --}}
<section class="band-surface">
  <div class="wrap">
    <div class="sec-head left">
      <h2>Abbreviations</h2>
    </div>
    <div class="alm-abbr" data-rise>
      @foreach([
        'AR' => 'Academic Registrar', 'CBET' => 'Competency-Based Education and Training',
        'CGC / GC' => 'Graduate Centre', 'CSR' => 'Corporate Social Responsibility',
        'DoS' => 'Dean of Students', 'DVC' => 'Deputy Vice Chancellor',
        'EFRIS' => 'Electronic Fiscal Receipting and Invoicing Solution',
        'FBM' => 'Faculty of Business and Management', 'FoE' => 'Faculty of Education',
        'FSSAH' => 'Faculty of Social Sciences, Arts and Humanities',
        'FSTEAD' => 'Faculty of Science, Technology, Engineering, Art and Design',
        'GRC' => 'Guild Representative Council', 'HoD' => 'Head of Department',
        'ICT' => 'Information and Communication Technology',
        'MANCO' => 'Management Committee',
        'MEMA' => 'Master of Educational Management and Administration',
        'MoU' => 'Memorandum of Understanding', 'NCHE' => 'National Council for Higher Education',
        'ODEL' => 'Open, Distance and e-Learning', 'QA' => 'Quality Assurance',
        'SPSS' => 'Statistical Package for the Social Sciences', 'TBC' => 'To be confirmed',
        'WASH' => 'Water, Sanitation and Hygiene',
      ] as $short => $long)
        <div class="alm-abbr-row"><dfn>{{ $short }}</dfn><span>{{ $long }}</span></div>
      @endforeach
    </div>
    <p class="alm-note" data-rise>
      This almanac is the consolidated draft prepared by the Office of the Academic Registrar and
      remains subject to Senate approval; a small number of dates are still marked for
      confirmation. For the authoritative position on any single date, contact the Academic
      Registrar's office.
    </p>
  </div>
</section>
@endif

@include('university.partials.cta-band')

@endsection
