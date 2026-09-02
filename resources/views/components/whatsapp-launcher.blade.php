@props(['phone' => '256752033889'])
{{--
  The WhatsApp launcher.

  One button, and one question before it opens WhatsApp: are you here to learn,
  or to hire. That single question is the whole point. A bare "chat with us"
  button produces "hi" and then nothing, because the visitor has to compose the
  awkward first sentence themselves and most will not. Asking first means the
  message writes itself, and it arrives already sorted into the two things this
  business does.

  The message knows what page it was pressed on, read off the route's bound
  models rather than passed down from each view: a course page names the
  course, a work page names the system. Nothing has to be wired per page, and
  a page added later gets the generic wording rather than a broken one.

  Placement rules it must not break:
    header is z-index 60, mobile menu 55, the mobile action bar 50.
    This sits at 45, so an open menu covers it and the action bar (Buy, Hire)
    is never blocked by a floating circle. On mobile it lifts clear of that bar.
--}}
@php
    use Illuminate\Support\Str;

    /*
     * What is this page about? Read from whatever model the route resolved,
     * which is the same trick the analytics tracker uses to know what a page
     * view was for.
     */
    $subject = null;
    $kind = null;

    foreach ((array) request()->route()?->parameters() as $parameter) {
        if ($parameter instanceof \App\Models\Course) {
            $subject = $parameter->title;
            $kind = 'course';
            break;
        }
        if ($parameter instanceof \App\Models\PortfolioProject) {
            $subject = $parameter->title;
            $kind = 'project';
            break;
        }
        if ($parameter instanceof \App\Models\Product) {
            $subject = $parameter->name;
            $kind = 'product';
            break;
        }
    }

    $me = 'MRU Admissions';

    // Two openers, each already specific enough that a reply can be useful.
    $learn = match ($kind) {
        'course' => "Hello {$me}, I am interested in your \"{$subject}\" short course. When does it open, and what does it cover?",
        default => "Hello {$me}, I am interested in MRU's short courses on the e-learning platform. Which one would you recommend for me?",
    };

    $apply = match ($kind) {
        'course' => "Hello {$me}, I found you through the \"{$subject}\" course. I would also like to ask about admission to the University's programmes.",
        default => "Hello {$me}, I would like to join Muteesa I Royal University. Could you guide me on the programmes, entry requirements, fees and how to apply?",
    };

    $link = fn (string $text) => 'https://wa.me/'.$phone.'?text='.rawurlencode($text);
@endphp

<div class="wa" x-data="{ open: false }" @keydown.escape.window="open = false">
  {{-- The panel is rendered above the button and animates from it, so the
       relationship between the two is obvious without a pointer or arrow. --}}
  <div class="wa-panel" x-show="open" x-cloak x-transition.origin.bottom.right
       @click.outside="open = false" role="dialog" aria-label="Start a WhatsApp chat">
    <div class="wa-head">
      <span class="wa-avatar">MRU</span>
      <span>
        <b>{{ $me }}</b>
        <em>Usually replies within a few hours</em>
      </span>
    </div>

    <p class="wa-q">What brings you here?</p>

    <a class="wa-opt" href="{{ $link($apply) }}" target="_blank" rel="noopener"
       data-a="cta.click" data-a-label="WhatsApp: admissions">
      <i class="fas fa-building-columns"></i>
      <span><b>I want to join MRU</b><em>Programmes, requirements, fees, how to apply</em></span>
      <i class="fas fa-chevron-right wa-go"></i>
    </a>

    <a class="wa-opt" href="{{ $link($learn) }}" target="_blank" rel="noopener"
       data-a="cta.click" data-a-label="WhatsApp: learn">
      <i class="fas fa-graduation-cap"></i>
      <span><b>Short courses</b><em>e-Learning, what to start with, what it costs</em></span>
      <i class="fas fa-chevron-right wa-go"></i>
    </a>

    @if($subject)
      <p class="wa-ctx"><i class="fas fa-link"></i> About {{ Str::limit($subject, 46) }}</p>
    @endif
  </div>

  <button type="button" class="wa-btn" @click="open = !open"
          :aria-expanded="open ? 'true' : 'false'"
          aria-label="Chat on WhatsApp">
    <i class="fab fa-whatsapp" x-show="!open"></i>
    <i class="fas fa-xmark" x-show="open" x-cloak></i>
  </button>
</div>
