@extends('layouts.marketing')
@section('title', 'Contact Us | Muteesa I Royal University')
@section('desc', 'Talk to Muteesa I Royal University — phone, WhatsApp, email or a visit to the Kakeeka (Kampala) or Kirumba (Masaka) campus. We reply within one working day.')

@section('content')

@php
  $tel = fn (?string $number) => 'tel:'.str_replace(' ', '', (string) $number);
@endphp

@include('university.partials.page-hero', [
  'eyebrow' => 'Contact',
  'title' => 'Contact Us',
  'lead' => 'By phone, WhatsApp, email or in person on either campus — and messages sent here reach a person, not a mailbox nobody reads.',
])

<section>
  <div class="wrap">
    <div class="contact-grid">

      <div class="contact-info">
        @isset($contacts['phone'])
          <div class="item" data-rise>
            <h4><i class="fas fa-phone" aria-hidden="true"></i> Call us</h4>
            <a href="{{ $tel($contacts['phone']) }}">{{ $contacts['phone'] }}</a>
            @isset($contacts['phone_alt'])
              <br><a href="{{ $tel($contacts['phone_alt']) }}">{{ $contacts['phone_alt'] }}</a>
            @endisset
          </div>
        @endisset

        <div class="item" data-rise>
          <h4><i class="fas fa-envelope" aria-hidden="true"></i> Email</h4>
          <a href="mailto:{{ $contacts['email'] ?? 'info@mru.ac.ug' }}">{{ $contacts['email'] ?? 'info@mru.ac.ug' }}</a>
          @isset($contacts['admissions_email'])
            <br><a href="mailto:{{ $contacts['admissions_email'] }}">{{ $contacts['admissions_email'] }} <span style="font-size:11.5px;color:var(--tx3);">(admissions)</span></a>
          @endisset
        </div>

        @isset($contacts['whatsapp'])
          <div class="item" data-rise>
            <h4><i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp</h4>
            <a href="{{ $contacts['whatsapp_link'] ?? 'https://wa.me/'.str_replace([' ', '+'], '', $contacts['whatsapp']) }}"
               target="_blank" rel="noopener">{{ $contacts['whatsapp'] }}</a>
          </div>
        @endisset

        @isset($contacts['pobox'])
          <div class="item" data-rise>
            <h4><i class="fas fa-envelopes-bulk" aria-hidden="true"></i> Postal address</h4>
            <span>{{ $contacts['pobox'] }}</span>
          </div>
        @endisset

        @foreach($contacts['campuses'] ?? [] as $campus)
          <div class="item" data-rise>
            <h4><i class="fas fa-building-columns" aria-hidden="true"></i> {{ $campus['name'] }}</h4>
            <span>{{ $campus['location'] }}</span>
            @isset($campus['maps'])
              <br><a href="{{ $campus['maps'] }}" target="_blank" rel="noopener" class="link"
                     style="color:var(--pri);font-weight:600;font-size:12.5px;">View on map <i class="fas fa-arrow-up-right-from-square" aria-hidden="true" style="font-size:10px;"></i></a>
            @endisset
          </div>
        @endforeach

        @if(!empty($social))
          <div class="item" data-rise>
            <h4><i class="fas fa-hashtag" aria-hidden="true"></i> Follow us</h4>
            <div style="display:flex;gap:8px;margin-top:6px;">
              @foreach($social as $s)
                <a href="{{ $s['url'] }}" target="_blank" rel="noopener" aria-label="MRU on {{ $s['name'] }}" title="{{ $s['name'] }}"
                   style="width:34px;height:34px;display:flex;align-items:center;justify-content:center;background:var(--pri);color:var(--gold);font-size:14px;">
                  <i class="fab {{ $s['icon'] }}" aria-hidden="true"></i>
                </a>
              @endforeach
            </div>
          </div>
        @endif
      </div>

      <div>
        @if(session('success'))
          <div class="alert-success" role="status" data-rise>
            <i class="fas fa-circle-check" aria-hidden="true"></i> {{ session('success') }}
          </div>
        @endif

        <form method="POST" action="{{ route('contact.store') }}" class="contact-form">
          @csrf
          <x-form-shield id="contact" />

          <div class="row2">
            <div>
              <label for="cf-name">Your name</label>
              <input type="text" id="cf-name" name="name" required maxlength="120"
                     value="{{ old('name') }}" autocomplete="name"
                     @error('name') aria-invalid="true" @enderror>
              @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
              <label for="cf-email">Email address</label>
              <input type="email" id="cf-email" name="email" required maxlength="191"
                     value="{{ old('email') }}" autocomplete="email"
                     @error('email') aria-invalid="true" @enderror>
              @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>
          </div>

          <div>
            <label for="cf-subject">Subject</label>
            <input type="text" id="cf-subject" name="subject" required maxlength="191"
                   value="{{ old('subject') }}"
                   @error('subject') aria-invalid="true" @enderror>
            @error('subject')<p class="field-error">{{ $message }}</p>@enderror
          </div>

          <div>
            <label for="cf-message">Message</label>
            <textarea id="cf-message" name="message" required maxlength="5000"
                      @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
            @error('message')<p class="field-error">{{ $message }}</p>@enderror
          </div>

          <x-captcha />
          @error(\App\Support\Spam\FormShield::TIMESTAMP)<p class="field-error">{{ $message }}</p>@enderror

          <div>
            <button type="submit" class="btn gold lg">
              <i class="fas fa-paper-plane" aria-hidden="true"></i> Send message
            </button>
          </div>
          <p style="font-size:12px;color:var(--tx3);margin-top:-4px;">We reply within one working day.</p>
        </form>
      </div>

    </div>
  </div>
</section>

@include('university.partials.cta-band')

@endsection
