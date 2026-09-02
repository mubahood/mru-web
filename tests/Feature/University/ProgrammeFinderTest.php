<?php

namespace Tests\Feature\University;

use App\Models\Faculty;
use App\Models\Programme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgrammeFinderTest extends TestCase
{
    use RefreshDatabase;

    private function seedProgrammes(): array
    {
        $business = Faculty::create(['name' => 'Faculty of Business & Management']);
        $stead = Faculty::create(['name' => 'Faculty of Science, Technology, Engineering, Art and Design']);

        $bba = Programme::create([
            'faculty_id' => $business->id, 'name' => 'Bachelor of Business Administration',
            'award_code' => 'BBA', 'level' => 'bachelor', 'duration' => '3 years',
            'tuition_per_semester' => 1200000, 'is_published' => true,
        ]);
        $dit = Programme::create([
            'faculty_id' => $stead->id, 'name' => 'Diploma in Information Technology',
            'level' => 'diploma', 'is_published' => true,
        ]);
        Programme::create([
            'faculty_id' => $stead->id, 'name' => 'Bachelor of Hidden Things',
            'level' => 'bachelor', 'is_published' => false,
        ]);

        return [$business, $stead, $bba, $dit];
    }

    public function test_the_finder_lists_published_programmes_only(): void
    {
        $this->seedProgrammes();

        $this->get(route('programmes.index'))
            ->assertOk()
            ->assertSee('Bachelor of Business Administration')
            ->assertSee('Diploma in Information Technology')
            ->assertDontSee('Bachelor of Hidden Things');
    }

    public function test_filters_narrow_by_level_faculty_and_search(): void
    {
        [$business] = $this->seedProgrammes();

        $this->get(route('programmes.index', ['level' => 'diploma']))
            ->assertOk()
            ->assertSee('Diploma in Information Technology')
            ->assertDontSee('Bachelor of Business Administration');

        $this->get(route('programmes.index', ['faculty' => $business->id]))
            ->assertOk()
            ->assertSee('Bachelor of Business Administration')
            ->assertDontSee('Diploma in Information Technology');

        $this->get(route('programmes.index', ['q' => 'BBA']))
            ->assertOk()
            ->assertSee('Bachelor of Business Administration')
            ->assertDontSee('Diploma in Information Technology');
    }

    public function test_a_programme_page_answers_the_decision_questions(): void
    {
        $this->seedProgrammes();
        $programme = Programme::where('award_code', 'BBA')->first();

        $this->get(route('programmes.show', $programme))
            ->assertOk()
            ->assertSee('Bachelor of Business Administration')
            ->assertSee('UGX 1,200,000 per semester')
            ->assertSee('Apply on the E-Portal')
            ->assertSee('Entry requirements');
    }

    public function test_an_unpublished_programme_is_not_reachable(): void
    {
        $this->seedProgrammes();
        $hidden = Programme::where('is_published', false)->first();

        $this->get(route('programmes.show', $hidden))->assertNotFound();
    }

    public function test_a_programme_page_emits_course_json_ld(): void
    {
        $this->seedProgrammes();
        $programme = Programme::where('award_code', 'BBA')->first();

        $html = (string) $this->get(route('programmes.show', $programme))->getContent();

        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
        $types = array_column(array_map(fn ($j) => json_decode($j, true), $matches[1]), '@type');

        $this->assertContains('Course', $types);
    }
}
