<?php

namespace Tests\Feature\University;

use App\Models\AlmanacEntry;
use App\Models\Faculty;
use App\Models\Programme;
use App\Support\Odel;
use App\Support\OdelNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The ODEL section.
 *
 * Its content is a transcription of two scanned, Council-approved policies, so
 * what matters is not that the pages render but that they keep faith with the
 * documents: every claim carries the clause it came from, commitments are not
 * dressed up as running services, and the programme list stays empty until
 * somebody with the authority to say so fills it in.
 */
class OdelTest extends TestCase
{
    use RefreshDatabase;

    public static function pages(): array
    {
        return [
            'hub' => ['odel.index'], 'modes' => ['odel.modes'], 'how it works' => ['odel.how-it-works'],
            'what you get' => ['odel.what-you-get'], 'credit' => ['odel.credit'],
            'programmes' => ['odel.programmes'], 'quality' => ['odel.quality'],
            'governance' => ['odel.governance'], 'calendar' => ['odel.calendar'],
            'support' => ['odel.support'], 'faqs' => ['odel.faqs'], 'start' => ['odel.apply'],
        ];
    }

    /** @dataProvider pages */
    public function test_the_page_renders_with_one_heading(string $route): void
    {
        $html = (string) $this->get(route($route))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'), "{$route} must have exactly one h1");
        $this->assertStringContainsString('od-head', $html, "{$route} must use the ODEL layout");
    }

    /** Every one of the six modes has its own page; anything else is not a mode. */
    public function test_each_mode_has_a_page_and_an_invented_one_does_not(): void
    {
        foreach (array_keys(Odel::modes()) as $key) {
            $this->get(route('odel.mode', $key))->assertOk();
        }

        $this->get('/odel/modes/correspondence-course')->assertNotFound();
    }

    /** The transcription has to stay complete, or a page renders a blank card. */
    public function test_every_mode_carries_the_fields_the_views_rely_on(): void
    {
        foreach (Odel::modes() as $key => $mode) {
            foreach (['name', 'clause', 'status', 'icon', 'suits', 'summary', 'detail'] as $field) {
                $this->assertArrayHasKey($field, $mode, "{$key} is missing {$field}");
                $this->assertNotEmpty($mode[$field], "{$key} has an empty {$field}");
            }
            $this->assertContains($mode['status'], [Odel::IN_PLACE, Odel::COMMITTED], $key);
            $this->assertIsArray($mode['detail']);
        }
    }

    /**
     * The section's whole argument. A reader must be able to tell a service
     * that exists from one the Council has merely approved.
     */
    public function test_a_committed_service_is_never_presented_as_running(): void
    {
        $html = (string) $this->get(route('odel.governance'))->assertOk()->getContent();

        // The Centre for Flexible and Distance Learning is established by the
        // policy, not described by it as already at work.
        $this->assertStringContainsString('Centre for Flexible and Distance Learning', $html);
        $this->assertStringContainsString(Odel::statusLabel(Odel::COMMITTED), $html);
        $this->assertStringContainsString(Odel::statusLabel(Odel::IN_PLACE), $html);
    }

    /** Nothing is asserted without saying where it came from. */
    public function test_policy_claims_cite_their_clause(): void
    {
        foreach (['odel.what-you-get', 'odel.quality', 'odel.governance', 'odel.how-it-works'] as $route) {
            $html = (string) $this->get(route($route))->assertOk()->getContent();
            $this->assertMatchesRegularExpression('/(DLP|FLP) §/', $html, "{$route} cites no clause");
        }
    }

    /**
     * The most important guard in this file. `study_modes` is unset on every
     * programme, and a list invented to fill the gap would be read by somebody
     * about to pay fees.
     */
    public function test_the_programme_page_says_so_rather_than_inventing_a_list(): void
    {
        $faculty = Faculty::create(['name' => 'Faculty of Education', 'slug' => 'fe', 'is_published' => true]);
        Programme::create(['faculty_id' => $faculty->id, 'name' => 'Bachelor of Education',
            'slug' => 'bed', 'level' => 'bachelor', 'is_published' => true]);

        $this->get(route('odel.programmes'))->assertOk()
            ->assertSee('We are not going to guess this one')
            ->assertDontSee('Bachelor of Education');
    }

    /** …and lists them the moment the Registrar's record says it can. */
    public function test_the_programme_page_lists_programmes_once_a_mode_is_set(): void
    {
        $faculty = Faculty::create(['name' => 'Faculty of Education', 'slug' => 'fe', 'is_published' => true]);
        Programme::create(['faculty_id' => $faculty->id, 'name' => 'Bachelor of Education',
            'slug' => 'bed', 'level' => 'bachelor', 'is_published' => true,
            'study_modes' => ['Distance']]);
        Programme::create(['faculty_id' => $faculty->id, 'name' => 'Bachelor of Nursing',
            'slug' => 'bn', 'level' => 'bachelor', 'is_published' => true,
            'study_modes' => ['Full-time']]);

        $this->get(route('odel.programmes'))->assertOk()
            ->assertSee('Bachelor of Education')
            ->assertDontSee('Bachelor of Nursing')
            ->assertDontSee('We are not going to guess this one');
    }

    /** The calendar reads the almanac rather than keeping a second set of dates. */
    public function test_the_calendar_shows_odel_dates_from_the_almanac(): void
    {
        AlmanacEntry::create(['academic_year' => '2026/2027', 'activity' => 'ODEL system training, Kakeeka Campus',
            'starts_on' => '2026-09-08', 'category' => 'academic', 'semester' => 'I', 'sort_order' => 1]);
        AlmanacEntry::create(['academic_year' => '2026/2027', 'activity' => 'Graduation ceremony',
            'starts_on' => '2026-10-30', 'category' => 'academic', 'semester' => 'I', 'sort_order' => 2]);

        $this->get(route('odel.calendar'))->assertOk()
            ->assertSee('ODEL system training, Kakeeka Campus')
            ->assertDontSee('Graduation ceremony');
    }

    /** An empty almanac must not leave a headed but empty band. */
    public function test_the_calendar_degrades_when_there_are_no_dates(): void
    {
        $this->get(route('odel.calendar'))->assertOk()->assertSee('Nothing scheduled yet');
    }

    public function test_the_faqs_publish_structured_data(): void
    {
        $html = (string) $this->get(route('odel.faqs'))->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"FAQPage"', $html);
    }

    /** Header, mobile sheet and footer are one list, so they cannot disagree. */
    public function test_every_navigation_destination_resolves(): void
    {
        foreach (OdelNav::items() as $item) {
            $this->get($item['url'])->assertOk();
        }

        foreach (OdelNav::columns() as $column) {
            foreach ($column['links'] as $link) {
                $this->get($link['url'])->assertOk();
            }
        }
    }

    /** Both policy PDFs are offered for download on every page; they must exist. */
    public function test_the_policy_documents_the_section_cites_are_on_disk(): void
    {
        foreach (Odel::policies() as $doc) {
            $this->assertFileExists(storage_path('app/public/'.$doc['file']), $doc['title']);
        }
    }
}
