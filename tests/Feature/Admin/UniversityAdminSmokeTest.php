<?php

namespace Tests\Feature\Admin;

use App\Models\Faculty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Every university CRUD screen renders for an admin and stays behind auth. */
class UniversityAdminSmokeTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private const INDEX_ROUTES = [
        'admin.faculties.index',
        'admin.programmes.index',
        'admin.staff-members.index',
        'admin.university-events.index',
        'admin.almanac.index',
        'admin.scholarships.index',
        'admin.vacancies.index',
        'admin.partners.index',
        'admin.scholars.index',
        'admin.publications.index',
        'admin.research-areas.index',
    ];

    private function admin(): User
    {
        $this->seed(\Database\Seeders\RbacSeeder::class);
        $admin = User::factory()->create(['role' => 'super_admin', 'is_admin' => true]);
        $admin->syncSpatieRole();

        return $admin;
    }

    public function test_every_university_index_page_renders_for_an_admin(): void
    {
        $admin = $this->admin();

        foreach (self::INDEX_ROUTES as $routeName) {
            $this->actingAs($admin)->get(route($routeName))->assertOk();
        }

        // The sidebar's University and Scholar groups are visible to an admin;
        // "Research areas" only occurs in the nav on this page.
        $this->actingAs($admin)->get(route('admin.faculties.index'))
            ->assertSee('University')
            ->assertSee('Research areas');
    }

    public function test_every_university_create_page_renders_for_an_admin(): void
    {
        $admin = $this->admin();

        foreach (self::INDEX_ROUTES as $routeName) {
            $createRoute = str_replace('.index', '.create', $routeName);
            $this->actingAs($admin)->get(route($createRoute))->assertOk();
        }
    }

    public function test_the_faculty_edit_page_renders(): void
    {
        $faculty = Faculty::create([
            'name' => 'Faculty of Science and Technology',
            'short_name' => 'FST',
            'departments' => ['Computing', 'Engineering'],
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.faculties.edit', $faculty))
            ->assertOk()
            ->assertSee('Faculty of Science and Technology');
    }

    public function test_the_public_cannot_reach_the_university_screens(): void
    {
        foreach (self::INDEX_ROUTES as $routeName) {
            $this->get(route($routeName))->assertRedirect();
        }
    }
}
