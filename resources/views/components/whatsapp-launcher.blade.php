{{--
  The floating WhatsApp launcher.

  It used to open a panel asking "what brings you here?" and compose an opening
  message through wa.me's ?text= parameter. That went when the destination
  became a group invite, which carries no prefilled message.

  What replaced it was a bare green circle — and a bare green circle says
  nothing about who it is for. The group is staffed by Admissions and answers
  people deciding whether to apply. An enrolled student arriving there with a
  fees, results or registration question is in the wrong queue, and will wait
  for an answer that was never going to come from it. So the button now carries
  its own label, and names the audience in the accessible name too rather than
  leaving it to the icon.

  Placement rules it must not break:
    header is z-index 60, mobile menu 55, the mobile action bar 50.
    This sits at 45, so an open menu covers it and the action bar (Apply Now)
    is never blocked. On mobile it lifts clear of that bar.
--}}
@props(['link' => null])
@php
    // Admin-editable, like every other contact detail; the constant is only the
    // floor under a setting somebody has emptied.
    $href = $link
        ?: (\App\Support\University::contacts()['whatsapp_link'] ?? null)
        ?: \App\Support\University::WHATSAPP_GROUP;
@endphp

<div class="wa">
  <a class="wa-btn" href="{{ $href }}" target="_blank" rel="noopener"
     data-a="cta.click" data-a-label="WhatsApp: admission enquiries"
     title="For people asking about admission. Already a student? Use the Student E-Portal or contact the Academic Registrar."
     aria-label="Ask about admission on WhatsApp — for prospective students; enrolled students should use the Student E-Portal">
    <i class="fab fa-whatsapp" aria-hidden="true"></i>
    <span class="wa-label">Admission enquiries</span>
  </a>
</div>
