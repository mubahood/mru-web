{{--
  The WhatsApp gate.

  The group is staffed by Admissions and answers people deciding whether to
  apply. Labelling the button helped, but a label is easy to skim past, and an
  enrolled student arriving with a results or fees question still ends up in a
  queue that cannot answer them — and waits.

  So joining is now a deliberate act: the reader is told what the group is for,
  what does not belong in it and where those questions go instead, and has to
  tick that they have read it before the join button will do anything.

  It intercepts by destination, not by class — every link whose href reaches
  the group is caught, including any added later — so there is one gate rather
  than nine templates each remembering to open it.

  Without JavaScript the links keep working. A gate that silently breaks the
  only route to Admissions for someone on a poor connection would cost more
  than it saves.
--}}
<div class="wg" id="wa-gate" hidden>
  <div class="wg-backdrop" data-wg-dismiss></div>

  <div class="wg-panel" role="dialog" aria-modal="true"
       aria-labelledby="wg-title" aria-describedby="wg-lead">
    <div class="wg-head">
      <span class="wg-mark" aria-hidden="true"><i class="fab fa-whatsapp"></i></span>
      <h2 id="wg-title">This group is for admission enquiries only</h2>
      <button type="button" class="wg-x" data-wg-dismiss aria-label="Close">
        <i class="fas fa-xmark" aria-hidden="true"></i>
      </button>
    </div>

    <div class="wg-body">
      <p id="wg-lead" class="wg-lead">
        The Muteesa I Royal University WhatsApp group is run by the <strong>Admissions
        Office</strong>. It is for people asking about <strong>joining the University</strong> —
        entry requirements, programmes, intakes, fees and how to apply.
      </p>

      <div class="wg-split">
        <div class="wg-col wg-yes">
          <p class="wg-col-h"><i class="fas fa-circle-check" aria-hidden="true"></i> Ask here</p>
          <ul>
            <li>Which programme suits me</li>
            <li>Entry requirements and intakes</li>
            <li>Tuition and what it covers</li>
            <li>How to apply, and what to send</li>
            <li>Scholarships and bursaries</li>
          </ul>
        </div>
        <div class="wg-col wg-no">
          <p class="wg-col-h"><i class="fas fa-circle-xmark" aria-hidden="true"></i> Not here</p>
          <ul>
            <li>Marks, results and transcripts</li>
            <li>Registration and course units</li>
            <li>Fees statements and balances</li>
            <li>Examinations and retakes</li>
            <li>Timetable and lecture questions</li>
          </ul>
        </div>
      </div>

      <div class="wg-note">
        <i class="fas fa-graduation-cap" aria-hidden="true"></i>
        <p><strong>Already an MRU student?</strong> Those questions belong with your lecturer, the
          Academic Registrar's Office, or the
          <a href="{{ \App\Support\University::links()['eportal'] ?? 'https://eportal.mru.ac.ug/' }}"
             target="_blank" rel="noopener">Student E-Portal</a>. Posting them in this group will
          not get them answered any faster — and usually not at all.</p>
      </div>

      <label class="wg-check" for="wg-agree">
        <input type="checkbox" id="wg-agree" data-wg-agree>
        <span>I have read this. I am asking about <strong>admission to the University</strong>,
          not about a course I am already registered for.</span>
      </label>
    </div>

    <div class="wg-foot">
      <button type="button" class="btn ghost" data-wg-dismiss>Cancel</button>
      <a href="#" class="btn gold wg-go" data-wg-go target="_blank" rel="noopener"
         aria-disabled="true" tabindex="-1">
        <i class="fab fa-whatsapp" aria-hidden="true"></i> Join the group
      </a>
    </div>
  </div>
</div>
