{{--
  The closing call to action, shared by every university page.
  One decision, two doors: apply now, or talk to a person first.
--}}
@php
  $applyUrl = \App\Support\University::applyUrl();
  $wa = (\App\Support\University::contacts()['whatsapp_link'] ?? null) ?: \App\Support\University::WHATSAPP_GROUP;
@endphp
<section class="band-deep">
  <div class="wrap" style="text-align:center;">
    <div class="sec-head">
      <p class="eyebrow">Join Us</p>
      <h2>Ready to join MRU?</h2>
      <p>Applications are open for the next intake. Apply on the E-Portal in under twenty minutes,
         or talk to the admissions team first — whichever helps you more.</p>
    </div>
    <div class="ctas">
      <a href="{{ $applyUrl }}" rel="external" class="btn gold lg">
        Apply Now <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </a>
      <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn ghost lg" style="color:#fff;border-color:rgba(255,255,255,.4);">
        <i class="fab fa-whatsapp" aria-hidden="true"></i> Ask Admissions on WhatsApp
      </a>
    </div>
  </div>
</section>
