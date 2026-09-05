<?php

namespace Tests\Feature\University;

use App\Models\Faculty;
use App\Models\Programme;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The headline stat row is editable copy, and its two countable figures had
 * drifted from the database: "46+ Academic Programmes" against 44 published,
 * and "5 Faculties & Graduate School" when there are four faculties plus the
 * Graduate School. Those two are now counted rather than retyped; this holds
 * the line.
 */
class HomepageStatsTest extends TestCase
{
    use RefreshDatabase;

    private function seedStats(): void
    {
        Settings::set('university.stats', json_encode([
            ['value' => '99', 'label' => 'Faculties & Graduate School'],
            ['value' => '99+', 'label' => 'Academic Programmes'],
            ['value' => '2', 'label' => 'Campuses — Kampala & Masaka'],
            ['value' => 'NCHE', 'label' => 'Accredited University'],
        ]));
    }

    public function test_the_faculty_and_programme_figures_come_from_the_database(): void
    {
        $this->seedStats();

        Faculty::create(['name' => 'Graduate School', 'slug' => 'graduate-school', 'is_published' => true]);
        foreach (['Education', 'Business', 'Science'] as $n) {
            Faculty::create(['name' => "Faculty of {$n}", 'slug' => strtolower($n), 'is_published' => true]);
        }
        $faculty = Faculty::first();
        foreach (range(1, 7) as $i) {
            Programme::create([
                'name' => "Programme {$i}", 'slug' => "programme-{$i}",
                'faculty_id' => $faculty->id, 'is_published' => true,
            ]);
        }

        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        // Three faculties, because the Graduate School is counted separately
        // in the label, and seven programmes — not the stale 99s in settings.
        $this->assertStringContainsString('>3</div>', $html);
        $this->assertStringContainsString('>7+</div>', $html);
        $this->assertStringNotContainsString('>99</div>', $html);
        $this->assertStringNotContainsString('>99+</div>', $html);
    }

    public function test_figures_the_database_cannot_answer_are_left_alone(): void
    {
        $this->seedStats();

        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('NCHE', $html, 'the accreditation mark is editorial, not counted');
        $this->assertStringContainsString('Campuses', $html);
    }
}
