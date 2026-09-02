<?php

namespace Tests\Feature\University;

use App\Models\Publication;
use App\Models\PublicationAuthor;
use App\Models\ResearchArea;
use App\Models\Scholar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScholarPortalTest extends TestCase
{
    use RefreshDatabase;

    private function seedRepository(): array
    {
        $scholar = Scholar::create(['name' => 'Nakato Grace', 'title' => 'Dr.', 'is_published' => true]);
        $area = ResearchArea::create(['name' => 'Education Research']);

        $published = Publication::create([
            'title' => 'Literacy Outcomes in Rural Uganda',
            'abstract' => 'A study of literacy outcomes.',
            'type' => 'journal',
            'journal_name' => 'Journal of African Education',
            'publication_date' => '2025-06-01',
            'status' => 'published',
        ]);
        $published->researchAreas()->attach($area);
        PublicationAuthor::create(['publication_id' => $published->id, 'scholar_id' => $scholar->id, 'author_order' => 0]);
        PublicationAuthor::create(['publication_id' => $published->id, 'external_name' => 'Jane External', 'author_order' => 1]);

        $draft = Publication::create(['title' => 'Unfinished Manuscript', 'status' => 'draft']);

        return [$scholar, $area, $published, $draft];
    }

    public function test_the_repository_lists_published_work_only(): void
    {
        $this->seedRepository();

        $this->get(route('scholar.publications'))
            ->assertOk()
            ->assertSee('Literacy Outcomes in Rural Uganda')
            ->assertDontSee('Unfinished Manuscript');
    }

    public function test_a_publication_page_names_all_authors_in_order(): void
    {
        [, , $published] = $this->seedRepository();

        $this->get(route('scholar.publication', $published))
            ->assertOk()
            ->assertSeeInOrder(['Dr. Nakato Grace', 'Jane External'])
            ->assertSee('Journal of African Education');
    }

    public function test_reading_a_publication_counts_a_view(): void
    {
        [, , $published] = $this->seedRepository();

        $this->get(route('scholar.publication', $published));

        $this->assertSame(1, $published->fresh()->views);
    }

    public function test_a_draft_publication_is_not_reachable(): void
    {
        [, , , $draft] = $this->seedRepository();

        $this->get(route('scholar.publication', $draft))->assertNotFound();
    }

    public function test_downloading_streams_the_pdf_and_counts_it(): void
    {
        Storage::fake('public');
        [, , $published] = $this->seedRepository();
        Storage::disk('public')->put('scholar/publications/paper.pdf', '%PDF-1.4 test');
        $published->update(['pdf_path' => 'scholar/publications/paper.pdf']);

        $this->get(route('scholar.publication.download', $published))->assertOk();

        $this->assertSame(1, $published->fresh()->downloads);
    }

    public function test_a_scholar_profile_lists_their_publications(): void
    {
        [$scholar] = $this->seedRepository();

        $this->get(route('scholar.profile', $scholar))
            ->assertOk()
            ->assertSee('Nakato Grace')
            ->assertSee('Literacy Outcomes in Rural Uganda');
    }

    public function test_the_directory_searches_by_name(): void
    {
        $this->seedRepository();
        Scholar::create(['name' => 'Okello Peter', 'is_published' => true]);

        $this->get(route('scholar.directory', ['q' => 'Nakato']))
            ->assertOk()
            ->assertSee('Nakato Grace')
            ->assertDontSee('Okello Peter');
    }

    public function test_area_filter_narrows_the_list(): void
    {
        [, $area] = $this->seedRepository();
        Publication::create(['title' => 'Off-topic Paper', 'status' => 'published']);

        $this->get(route('scholar.publications', ['area' => $area->id]))
            ->assertOk()
            ->assertSee('Literacy Outcomes in Rural Uganda')
            ->assertDontSee('Off-topic Paper');
    }
}
