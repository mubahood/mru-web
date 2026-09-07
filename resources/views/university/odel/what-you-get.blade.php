@extends('layouts.odel')
@section('title', 'What an ODEL student is entitled to | Muteesa I Royal University')
@section('desc', 'Twelve things Muteesa I Royal University commits to give a distance student, taken clause by clause from section 7.1 of the Distance Learning Policy.')

@section('content')

@include('university.odel.partials.hero', [
  'eyebrow' => 'What You Get',
  'title' => 'What the University owes you',
  'lead' => 'Studying away from campus should not mean getting less. Section 7.1 of the Distance
             Learning Policy lists what a distance student must have — set out here as
             entitlements, because that is what they are.',
  'trail' => [['label' => 'What you get']],
])

<section style="padding-top:var(--s-6);">
  <div class="wrap" style="max-width:920px;">
    <div class="od-list" data-rise>
      @foreach($entitlements as $e)
        <div class="od-item">
          <p>{{ $e['text'] }}</p>
          <span class="od-clause">{{ $e['clause'] }}</span>
        </div>
      @endforeach
    </div>

    <div class="od-note" data-rise style="margin-top:var(--s-6);">
      <i class="fas fa-scale-balanced" aria-hidden="true"></i>
      <p><strong>If you are not getting one of these, say so.</strong> The same policy gives you
         proper opportunities to give formal feedback on your programme, and a named contact who
         must respond to it. Start with
         <a href="{{ route('odel.support') }}" class="link" style="color:#6B4A0C;font-weight:600;">the contacts page</a>.</p>
    </div>
  </div>
</section>

@endsection
