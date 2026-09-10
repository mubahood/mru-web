{{--
  The analytics consent bar.

  Deliberately not a wall. It does not block the page, does not dim it, and
  carries no "reject" buried behind a second click — a reader who came to find
  an entry requirement should be able to ignore this entirely and still read the
  page. Refusing is one tap, in the same place and the same weight as accepting.

  It says what it actually controls, which is Google Analytics. The University's
  own measurement is first-party, never leaves its servers, and is described in
  the privacy policy; conflating the two would make this bar a lie in one
  direction or the other.

  Hidden by default and revealed by script only when no choice has been stored,
  so a reader who has already answered never sees it again and someone with
  JavaScript off is not shown a control that cannot work.
--}}
<div class="cb" id="consent-bar" role="region" aria-label="Analytics consent" hidden>
  <div class="cb-in">
    <p class="cb-msg">
      <i class="fas fa-chart-simple" aria-hidden="true"></i>
      <span>We use <strong>Google Analytics</strong> to see which pages people find useful. It
        sets a cookie. You can say no and the site works exactly the same —
        <a href="{{ route('privacy') }}" wire:navigate>how we handle data</a>.</span>
    </p>
    <div class="cb-act">
      <button type="button" class="cb-btn cb-no" data-consent="denied">No thanks</button>
      <button type="button" class="cb-btn cb-yes" data-consent="granted">Allow</button>
    </div>
  </div>
</div>
