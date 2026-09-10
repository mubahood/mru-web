<?php

namespace Tests\Feature\University;

use App\Models\SiteNotice;
use App\Support\Notices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The notice strip.
 *
 * The point of it is that it schedules itself. A notice about an intake that
 * closed in January must not still be on the site in March because nobody
 * remembered to switch it off, so most of what is worth testing here is the
 * window arithmetic rather than the markup.
 */
class NoticeStripTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notices::forget();   // the resolver memoises per process
    }

    private function notice(array $attributes = []): SiteNotice
    {
        return SiteNotice::create(array_merge([
            'message' => 'Applications are open for the January intake.',
            'template' => 'banner',
            'is_published' => true,
        ], $attributes));
    }

    public function test_the_strip_is_absent_until_there_is_something_to_say(): void
    {
        $this->get('/')->assertOk()->assertDontSee('data-notice-strip', escape: false);
    }

    public function test_a_live_notice_appears_with_its_message(): void
    {
        $this->notice(['label' => 'Now open']);

        $this->get('/')->assertOk()
            ->assertSee('data-notice-strip', escape: false)
            ->assertSee('Applications are open for the January intake.')
            ->assertSee('Now open');
    }

    /** @dataProvider windows */
    public function test_the_window_decides_whether_it_shows(?string $starts, ?string $ends, bool $expected): void
    {
        $this->notice([
            'starts_at' => $starts ? now()->modify($starts) : null,
            'ends_at' => $ends ? now()->modify($ends) : null,
        ]);

        $live = SiteNotice::live()->count();

        $this->assertSame($expected ? 1 : 0, $live, sprintf(
            'starts %s, ends %s should %sbe live', $starts ?? 'null', $ends ?? 'null', $expected ? '' : 'not '
        ));
    }

    public static function windows(): array
    {
        return [
            'no dates at all runs' => [null, null, true],
            'started, no end' => ['-1 day', null, true],
            'starts later' => ['+2 days', null, false],
            'ends later' => [null, '+2 days', true],
            'already ended' => [null, '-1 day', false],
            'inside the window' => ['-1 day', '+1 day', true],
            'window not yet open' => ['+1 day', '+2 days', false],
            'window already closed' => ['-2 days', '-1 day', false],
        ];
    }

    public function test_an_unpublished_notice_never_shows_however_open_its_window(): void
    {
        $this->notice(['is_published' => false, 'starts_at' => now()->subDay(), 'ends_at' => now()->addYear()]);

        $this->assertSame(0, SiteNotice::live()->count());
        $this->get('/')->assertOk()->assertDontSee('data-notice-strip', escape: false);
    }

    /**
     * Counted from the start of today, so a deadline tomorrow evening reads
     * "1 day left" all day rather than flipping with the hour.
     */
    public function test_the_countdown_counts_whole_days_from_today(): void
    {
        $this->assertSame(0, $this->notice(['deadline_at' => now()->endOfDay()])->daysLeft());
        $this->assertSame(1, $this->notice(['deadline_at' => now()->addDay()->setTime(6, 0)])->daysLeft());
        $this->assertSame(7, $this->notice(['deadline_at' => now()->addDays(7)->setTime(23, 0)])->daysLeft());
        $this->assertNull($this->notice(['deadline_at' => now()->subDay()])->daysLeft(), 'a passed deadline has no count');
        $this->assertNull($this->notice(['deadline_at' => null])->daysLeft());
    }

    public function test_a_deadline_renders_as_words_rather_than_a_date(): void
    {
        $this->notice(['template' => 'countdown', 'deadline_at' => now()->addDays(5)]);

        $this->get('/')->assertOk()->assertSee('5 days left');
    }

    /** An off-site link must open in a new tab and be marked as leaving. */
    public function test_an_external_action_link_opens_in_a_new_tab(): void
    {
        $this->notice(['link_url' => 'https://eportal.mru.ac.ug/apply/', 'link_label' => 'Apply now']);

        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<a class="ns-go"\s+href="https:\/\/eportal\.mru\.ac\.ug\/apply\/"\s+target="_blank" rel="noopener"/s',
            $html
        );
    }

    public function test_an_internal_link_stays_in_the_tab(): void
    {
        $this->notice(['link_url' => url('/admissions'), 'link_label' => 'Admissions']);

        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Admissions', $html);
        $this->assertDoesNotMatchRegularExpression('/<a class="ns-go"[^>]*target="_blank"/s', $html);
    }

    /** A ticker duplicates its content to loop; the copy must not be read twice. */
    public function test_the_ticker_hides_its_duplicate_from_assistive_technology(): void
    {
        $this->notice(['template' => 'ticker']);

        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'class="ns-run"'), 'the ticker needs two runs to loop');
        // Matched loosely: the attribute is emitted by a Blade conditional, so
        // pinning its exact whitespace made this fail on a formatting change
        // rather than on the copy being announced twice.
        $this->assertMatchesRegularExpression('/class="ns-run"\s+aria-hidden="true"/', $html);
        $this->assertStringContainsString('data-ns-pause', $html, 'moving content needs a pause control');
    }

    /**
     * Several ticker notices share one moving lane, the way a real news ticker
     * does. A row each would stack 32px strips until the strip is taller than
     * the header it sits above.
     */
    public function test_ticker_notices_share_a_single_lane(): void
    {
        $this->notice(['template' => 'ticker', 'message' => 'Applications are open.']);
        $this->notice(['template' => 'ticker', 'message' => 'Graduation is in December.']);
        Notices::forget();

        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'ns-item ns-ticker'), 'one lane, not one per notice');
        $this->assertStringContainsString('Applications are open.', $html);
        $this->assertStringContainsString('Graduation is in December.', $html);
        // A divider between them, and only between them.
        $this->assertSame(2, substr_count($html, 'ns-sep'), 'one separator per run, two runs');
    }

    /** A banner or an urgent notice is a statement, so it keeps its own row. */
    public function test_non_ticker_notices_keep_their_own_row(): void
    {
        $this->notice(['template' => 'ticker', 'message' => 'Scrolling item.']);
        $this->notice(['template' => 'urgent', 'message' => 'Campus closed today.']);
        Notices::forget();

        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'ns-item ns-ticker'), 'the ticker lane');
        $this->assertSame(1, substr_count($html, 'ns-item ns-urgent'), 'plus a row of its own');
    }

    /** The lane's dismiss key must change when any notice in it changes. */
    public function test_the_lane_key_tracks_every_notice_in_it(): void
    {
        $this->notice(['template' => 'ticker', 'message' => 'One.']);
        Notices::forget();
        $before = Notices::tickerKey();

        $this->notice(['template' => 'ticker', 'message' => 'Two.']);
        Notices::forget();

        $this->assertNotSame($before, Notices::tickerKey());
    }

    /** One notice refusing dismissal keeps the whole lane on screen. */
    public function test_the_lane_is_only_dismissible_if_every_notice_allows_it(): void
    {
        $this->notice(['template' => 'ticker', 'is_dismissible' => true]);
        Notices::forget();
        $this->assertTrue(Notices::tickerDismissible());

        $this->notice(['template' => 'ticker', 'message' => 'Cannot be dismissed.', 'is_dismissible' => false]);
        Notices::forget();
        $this->assertFalse(Notices::tickerDismissible());
    }

    public function test_an_unknown_template_falls_back_rather_than_breaking_the_page(): void
    {
        $notice = $this->notice(['template' => 'confetti']);

        $this->assertSame('ticker', $notice->templateName());
        $this->get('/')->assertOk();
    }

    /** Editing a notice must reach a reader who dismissed the previous wording. */
    public function test_the_dismiss_key_changes_when_the_notice_is_edited(): void
    {
        $notice = $this->notice();
        $before = $notice->dismissKey();

        $this->travel(5)->minutes();
        $notice->update(['message' => 'Applications close on Friday.']);

        $this->assertNotSame($before, $notice->fresh()->dismissKey());
    }

    public function test_the_strip_is_on_the_odel_layout_too(): void
    {
        $this->notice(['label' => 'Now open']);

        $this->get('/odel')->assertOk()->assertSee('data-notice-strip', escape: false);
    }
}
