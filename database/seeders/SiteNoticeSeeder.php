<?php

namespace Database\Seeders;

use App\Models\SiteNotice;
use Illuminate\Database\Seeder;

/**
 * The opening notice: the January intake.
 *
 * Seeded rather than typed into the admin so a fresh clone comes up with the
 * strip populated and the wiring demonstrably working. updateOrCreate on the
 * message keeps it idempotent — reseeding does not stack duplicates.
 */
class SiteNoticeSeeder extends Seeder
{
    public function run(): void
    {
        SiteNotice::updateOrCreate(
            ['message' => 'Applications are open for the January intake — apply online through the E-Portal.'],
            [
                'label' => 'Now open',
                'icon' => 'fa-graduation-cap',
                'link_url' => 'https://eportal.mru.ac.ug/apply/',
                'link_label' => 'Apply now',
                'template' => 'countdown',
                // No start: it is open now. It retires itself at the end of
                // January rather than waiting for somebody to remember.
                'starts_at' => null,
                'ends_at' => now()->setDate((int) now()->addMonths(4)->format('Y'), 1, 31)->endOfDay(),
                'deadline_at' => now()->setDate((int) now()->addMonths(4)->format('Y'), 1, 31)->endOfDay(),
                'is_published' => true,
                'is_dismissible' => true,
                'sort_order' => 0,
            ]
        );
    }
}
