<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Support\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The public site's own copy — ten settings blobs driving the hero slides, the
 * statistics row, contact details and the admissions notices — had no editor
 * at all: changing a phone number or the homepage headline meant editing JSON
 * in the database. These cover the editor that replaced that.
 */
class SiteContentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'role' => 'super_admin']);
    }

    public function test_an_administrator_can_reach_the_content_index(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.site-content.index'))
            ->assertOk()
            ->assertSee('Website content')
            ->assertSee('Homepage slider');
    }

    public function test_a_visitor_cannot(): void
    {
        $this->get(route('admin.site-content.index'))->assertRedirect();
    }

    public function test_editing_a_map_section_changes_the_public_site(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.site-content.update', 'contacts'), [
                'content' => ['phone' => '+256 700 000 001', 'email' => 'hello@mru.ac.ug'],
            ])->assertRedirect();

        $this->assertSame('+256 700 000 001', University::contacts()['phone']);

        // and it is actually on the page a visitor sees
        $this->get(route('contact'))->assertOk()->assertSee('+256 700 000 001');
    }

    public function test_unlisted_keys_survive_a_save(): void
    {
        // The editor exposes some contact fields, not every one. A save must
        // not silently drop the campuses array it never showed.
        $before = University::contacts()['campuses'] ?? null;

        $this->actingAs($this->admin())
            ->put(route('admin.site-content.update', 'contacts'), [
                'content' => ['phone' => '+256 700 000 002'],
            ])->assertRedirect();

        $this->assertSame($before, University::contacts()['campuses'] ?? null);
    }

    public function test_a_repeater_row_can_be_added_and_an_emptied_row_removed(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.site-content.update', 'stats'), [
                'content' => [
                    ['value' => '7', 'label' => 'Faculties'],
                    ['value' => '', 'label' => ''],           // emptied → dropped
                    ['value' => '99', 'label' => 'Something new'],
                ],
            ])->assertRedirect();

        $stats = University::get('stats');

        $this->assertCount(2, $stats);
        $this->assertSame('Something new', $stats[1]['label']);
    }

    public function test_history_paragraphs_are_stored_as_a_list(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.site-content.update', 'identity'), [
                'content' => ['history' => "First paragraph.\nSecond paragraph."],
            ])->assertRedirect();

        $this->assertSame(
            ['First paragraph.', 'Second paragraph.'],
            University::identity()['history']
        );
    }

    public function test_an_unknown_section_is_a_404(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.site-content.edit', 'not-a-section'))
            ->assertNotFound();
    }
}
