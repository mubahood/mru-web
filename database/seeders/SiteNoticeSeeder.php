<?php

namespace Database\Seeders;

use App\Models\AlmanacEntry;
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
        $this->januaryIntake();
        $this->graduation();
    }

    /**
     * The 14th Graduation Ceremony.
     *
     * Taken from the academic almanac, which records it as
     * "14th Graduation Ceremony (provisional)" on 4 December 2026 — and the
     * word provisional is carried through rather than quietly dropped. A
     * countdown to a date somebody then discovers was never fixed does more
     * damage than no countdown at all.
     *
     * It also gives the strip a second audience: the first notice speaks to
     * people deciding whether to apply, this one to the students, families and
     * staff already here.
     */
    private function graduation(): void
    {
        $date = AlmanacEntry::where('activity', 'like', '%raduation Ceremony%')
            ->whereNotNull('starts_on')->orderBy('starts_on')->first()?->starts_on;

        // Seeded from the almanac, so it cannot drift from it. No almanac
        // entry, no notice — rather than a hard-coded date going stale.
        if (! $date || $date->isPast()) {
            return;
        }

        SiteNotice::updateOrCreate(
            ['message' => '14th Graduation Ceremony — provisionally set for '.$date->format('j F Y').'.'],
            [
                'label' => 'Save the date',
                'icon' => 'fa-graduation-cap',
                'link_url' => url('/almanac'),
                'link_label' => 'See the academic year',
                'template' => 'ticker',
                'starts_at' => null,
                'ends_at' => $date->copy()->endOfDay(),
                'deadline_at' => $date->copy()->endOfDay(),
                'is_published' => true,
                'is_dismissible' => true,
                'sort_order' => 1,
            ]
        );
    }

    private function januaryIntake(): void
    {
        SiteNotice::updateOrCreate(
            ['message' => 'Applications are open for the January intake — apply online through the E-Portal.'],
            [
                'label' => 'Now open',
                'icon' => 'fa-graduation-cap',
                'link_url' => 'https://eportal.mru.ac.ug/apply/',
                'link_label' => 'Apply now',
                // Moving, not static: the strip is a marquee and this is its
                // headline notice. The countdown pill renders on any template.
                'template' => 'ticker',
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
