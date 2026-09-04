<?php

namespace Tests\Feature\University;

use App\Models\AlmanacEntry;
use App\Models\Faculty;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Publication;
use App\Models\Scholarship;
use App\Models\StaffMember;
use App\Models\UniversityEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The homepage is assembled from nine independent content sources, every one
 * of which an administrator can empty by unpublishing its rows. A section that
 * assumed its data existed would take the whole front page down with it, so
 * each is guarded — and this asserts the guards actually hold rather than
 * trusting that they do.
 */
class HomepageResilienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_survives_every_section_being_empty(): void
    {
        // A database with the schema but no publishable content at all.
        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('</footer>', $html, 'the page must still finish rendering');

        // Asserted on each section's own markup rather than its heading text:
        // several headings share wording with the mega menu, which renders on
        // every page regardless of what the homepage has to show.
        foreach ([
            'faculty-grid' => 'faculties',
            'scholarship-bento' => 'scholarships',
            'band-news' => 'news & events',
            'year-line' => 'academic year strip',
            'lead-row' => 'leadership',
            'clients-strip' => 'partners',
        ] as $marker => $section) {
            $this->assertStringNotContainsString(
                $marker, $html,
                "an empty section must be omitted, not rendered empty: {$section}"
            );
        }
    }

    public function test_the_homepage_survives_content_being_unpublished_after_it_existed(): void
    {
        Faculty::create(['name' => 'Faculty of Testing', 'slug' => 'faculty-of-testing', 'is_published' => false]);
        Post::create(['title' => 'Hidden', 'slug' => 'hidden', 'body' => 'x', 'is_published' => false]);
        Scholarship::create(['name' => 'Hidden award', 'slug' => 'hidden-award', 'is_published' => false]);
        StaffMember::create(['name' => 'Hidden Officer', 'staff_role' => 'leadership', 'is_published' => false]);
        Partner::create(['name' => 'Hidden partner', 'show_on_home' => false]);

        $this->get(route('home'))->assertOk();
    }

    public function test_a_leader_without_a_portrait_is_not_shown_as_a_broken_medallion(): void
    {
        StaffMember::create([
            'name' => 'Officer Without Photo', 'title' => 'Registrar',
            'staff_role' => 'leadership', 'is_published' => true, 'photo' => null,
        ]);

        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('Officer Without Photo', $html);
    }

    public function test_the_year_strip_falls_back_when_every_key_date_has_passed(): void
    {
        // Only past key dates: the strip must still render rather than vanish
        // or fatal on an empty collection.
        AlmanacEntry::create([
            'academic_year' => '2020/2021', 'semester' => 'Semester I',
            'period' => '1 January 2020', 'starts_on' => '2020-01-01',
            'activity' => 'A milestone long past', 'is_key_date' => true, 'sort_order' => 1,
        ]);

        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('A milestone long past', $html);
    }

    public function test_publications_and_events_missing_do_not_break_the_bands_that_use_them(): void
    {
        Publication::create(['title' => 'Unfeatured', 'slug' => 'unfeatured', 'is_published' => true, 'is_featured' => false]);
        UniversityEvent::create([
            'title' => 'Past event', 'slug' => 'past-event',
            'starts_at' => now()->subYear(), 'is_published' => true,
        ]);

        $this->get(route('home'))->assertOk();
    }
}
