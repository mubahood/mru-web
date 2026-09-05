{{--
  The floating WhatsApp launcher.

  It used to open a small panel asking "what brings you here?", because a bare
  "chat with us" button produces "hi" and then nothing — the visitor has to
  compose the awkward first sentence and most will not. The panel answered that
  by writing the opener for them, through wa.me's ?text= parameter, and even
  named the course or programme the button was pressed on.

  That whole design depended on ?text=. The destination is now a WhatsApp
  **group** invite (chat.whatsapp.com), and a group link carries no prefilled
  message: ?text= is ignored. Keeping the panel would mean asking a question and
  then ignoring the answer, so the button is what it now honestly is — one tap
  into the University's WhatsApp group.

  Placement rules it must not break:
    header is z-index 60, mobile menu 55, the mobile action bar 50.
    This sits at 45, so an open menu covers it and the action bar (Apply Now)
    is never blocked by a floating circle. On mobile it lifts clear of that bar.
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
     data-a="cta.click" data-a-label="WhatsApp: group"
     aria-label="Join the Muteesa I Royal University WhatsApp group">
    <i class="fab fa-whatsapp" aria-hidden="true"></i>
  </a>
</div>
