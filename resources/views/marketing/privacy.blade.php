@extends('layouts.marketing')
@section('title', 'Privacy Policy | Muteesa I Royal University')
@section('desc', 'How Muteesa I Royal University collects, uses and protects personal information on its website and online services.')

@section('content')
<section>
  <div class="wrap page">
    <h1>Privacy Policy</h1>
    <div class="updated">Last updated {{ date('F Y') }}</div>

    <p>This website is operated by Muteesa I Royal University ("MRU", "the University", "we").
       This policy explains what personal information is collected through the university website,
       the e-learning platform and other online services, and how it is used. It is written to be
       consistent with the Data Protection and Privacy Act, 2019 of Uganda.</p>

    <h2>What we collect</h2>
    <ul>
      <li><strong>Enquiries:</strong> when you send a message through the contact form, your name,
          email, subject and message are stored so the University can reply and keep a record of
          the conversation.</li>
      <li><strong>Newsletter:</strong> subscribing stores your email address, used only to send you
          university news. Every message includes a way to unsubscribe.</li>
      <li><strong>e-Learning accounts:</strong> creating an account stores your name, email, phone
          number and course activity (enrolments, progress, submissions, certificates earned). This
          data is used to run the courses and issue certificates.</li>
      <li><strong>Payments:</strong> fees paid online are processed by our payment provider; the
          University stores the transaction reference and amount, never your full card or mobile
          money credentials.</li>
      <li><strong>Site analytics:</strong> we measure page visits in aggregate (pages viewed,
          country, device type) to understand what visitors need. This measurement is first-party;
          we do not sell or share browsing data with advertisers.</li>
    </ul>

    <h2>Admissions</h2>
    <p>Applications for admission are made on the student E-Portal (eportal.mru.ac.ug), which is
       governed by the University's student records procedures. This website links to the portal
       but does not itself store application forms.</p>

    <h2>How data is protected</h2>
    <ul>
      <li>Passwords are hashed, never stored in plain text.</li>
      <li>Accounts are scoped so each person only ever sees their own records.</li>
      <li>Uploaded documents and certificates are stored on a private disk, never publicly
          accessible by URL.</li>
      <li>Access to administrative systems is limited to authorised university staff.</li>
    </ul>

    <h2>Sharing</h2>
    <p>The University does not sell personal data. Information is shared only with service
       providers needed to operate the site (such as the payment gateway), with regulators where
       the law requires it, and with employers verifying a certificate you have asked us to make
       verifiable.</p>

    <h2>Your rights</h2>
    <p>You may request a copy, correction or deletion of the personal data the University holds
       about you through this website by writing to
       <a class="link" href="mailto:info@mru.ac.ug">info@mru.ac.ug</a>. Records the University is
       legally required to keep (such as academic records) are governed by university policy.</p>

    <h2>Contact</h2>
    <p>Questions about this policy: <a class="link" href="mailto:info@mru.ac.ug">info@mru.ac.ug</a>
       · +256 200 903 000 · P.O. Box 1339, Kampala, Uganda.</p>
  </div>
</section>
@endsection
