<?php

namespace App\Support;

/**
 * The editable shape of the website's own copy.
 *
 * Ten `university.*` settings blobs drive nearly every word and picture on the
 * public site — the hero slides, the statistics row, contact details, the
 * admissions notices, halls, sports, downloads. All ten existed only as JSON
 * in the settings table, which meant changing the homepage headline or a phone
 * number required a developer. This declares each blob as a set of typed
 * fields so the back office can render a real form for it.
 *
 * Field types:
 *   text | url | email | textarea | image | lines (one item per line)
 *   repeater  — a list of rows, each with its own `fields`
 */
class SiteContent
{
    /** @return array<string,array<string,mixed>> */
    public static function sections(): array
    {
        return [
            'identity' => [
                'label' => 'Identity & story',
                'icon' => 'fa-landmark',
                'blurb' => 'The university’s name, motto and the paragraphs used across the site.',
                'type' => 'map',
                'fields' => [
                    'name' => ['label' => 'Full name', 'type' => 'text'],
                    'short' => ['label' => 'Short name', 'type' => 'text'],
                    'motto' => ['label' => 'Motto', 'type' => 'text'],
                    'strapline' => ['label' => 'Strapline', 'type' => 'text', 'help' => 'The homepage headline under the slider.'],
                    'namesake' => ['label' => 'Namesake line', 'type' => 'text'],
                    'accreditation' => ['label' => 'Accreditation line', 'type' => 'text'],
                    'vision' => ['label' => 'Vision', 'type' => 'textarea'],
                    'mission' => ['label' => 'Mission', 'type' => 'textarea'],
                    'history' => ['label' => 'History paragraphs', 'type' => 'lines', 'help' => 'One paragraph per line. The first two appear on the homepage.'],
                ],
            ],

            'hero_slides' => [
                'label' => 'Homepage slider',
                'icon' => 'fa-images',
                'blurb' => 'The full-screen slides at the top of the homepage.',
                'type' => 'repeater',
                'fields' => [
                    'image' => ['label' => 'Image base path', 'type' => 'text', 'help' => 'e.g. university/hero/hero-royal — the -700/-1100/-1600 files are built from this.'],
                    'alt' => ['label' => 'Image description (alt)', 'type' => 'text'],
                    'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
                    'title' => ['label' => 'Headline', 'type' => 'text'],
                    'text' => ['label' => 'Supporting line', 'type' => 'textarea'],
                ],
            ],

            'stats' => [
                'label' => 'Headline statistics',
                'icon' => 'fa-chart-simple',
                'blurb' => 'The four figures under the homepage headline. Faculty and programme counts are taken from the database automatically — their labels are editable here, the numbers are not.',
                'type' => 'repeater',
                'fields' => [
                    'value' => ['label' => 'Figure', 'type' => 'text'],
                    'label' => ['label' => 'Label', 'type' => 'text'],
                ],
            ],

            'contacts' => [
                'label' => 'Contact details',
                'icon' => 'fa-address-book',
                'blurb' => 'Phones, inboxes and campuses. These feed the contact page, the footer and the header.',
                'type' => 'map',
                'fields' => [
                    'phone' => ['label' => 'Main phone', 'type' => 'text'],
                    'phone_alt' => ['label' => 'Second phone', 'type' => 'text'],
                    'whatsapp' => ['label' => 'WhatsApp number', 'type' => 'text'],
                    'whatsapp_link' => ['label' => 'WhatsApp link', 'type' => 'url'],
                    'email' => ['label' => 'General email', 'type' => 'email'],
                    'admissions_email' => ['label' => 'Admissions email', 'type' => 'email'],
                    'accommodation_email' => ['label' => 'Accommodation email', 'type' => 'email'],
                    'careers_email' => ['label' => 'Careers email', 'type' => 'email'],
                    'pobox' => ['label' => 'Postal address', 'type' => 'text'],
                ],
            ],

            'admissions' => [
                'label' => 'Admissions notices',
                'icon' => 'fa-file-pen',
                'blurb' => 'The intake notice shown on the homepage and across the admissions pages, and the application fees.',
                'type' => 'map',
                'fields' => [
                    'deadline_note' => ['label' => 'Intake notice', 'type' => 'textarea', 'help' => 'Appears on the homepage strip and as the lead on four admissions pages.'],
                    'application_fee' => ['label' => 'Application fee', 'type' => 'text'],
                    'processing_fee' => ['label' => 'Processing fee', 'type' => 'text'],
                    'apply_url' => ['label' => 'Apply URL', 'type' => 'url'],
                ],
            ],

            'links' => [
                'label' => 'Portals & external links',
                'icon' => 'fa-link',
                'blurb' => 'The E-Portal, application and staff systems linked from the header and footer.',
                'type' => 'map',
                'fields' => [
                    'eportal' => ['label' => 'Student E-Portal', 'type' => 'url'],
                    'apply' => ['label' => 'Apply online', 'type' => 'url'],
                    'eadmin' => ['label' => 'Staff portal', 'type' => 'url'],
                    'library' => ['label' => 'Library system', 'type' => 'url'],
                ],
            ],

            'social' => [
                'label' => 'Social accounts',
                'icon' => 'fa-hashtag',
                'blurb' => 'Shown in the footer and used for the site’s structured data.',
                'type' => 'repeater',
                'fields' => [
                    'name' => ['label' => 'Network', 'type' => 'text'],
                    'icon' => ['label' => 'Icon class', 'type' => 'text', 'help' => 'Font Awesome brand class, e.g. fa-facebook-f'],
                    'url' => ['label' => 'Profile URL', 'type' => 'url'],
                    'handle' => ['label' => 'Handle', 'type' => 'text'],
                ],
            ],

            'accommodation' => [
                'label' => 'Halls of residence',
                'icon' => 'fa-bed',
                'blurb' => 'The halls listed on the accommodation page.',
                'type' => 'repeater',
                'fields' => [
                    'name' => ['label' => 'Hall', 'type' => 'text'],
                    'audience' => ['label' => 'Who it is for', 'type' => 'text'],
                    'campus' => ['label' => 'Campus', 'type' => 'text'],
                    'beds' => ['label' => 'Beds', 'type' => 'text'],
                    'price' => ['label' => 'Price', 'type' => 'text'],
                ],
            ],

            'sports' => [
                'label' => 'Sports & lifestyle',
                'icon' => 'fa-futbol',
                'blurb' => 'The disciplines listed on the sports page.',
                'type' => 'repeater',
                'fields' => [
                    'title' => ['label' => 'Discipline', 'type' => 'text'],
                    'icon' => ['label' => 'Icon class', 'type' => 'text'],
                    'description' => ['label' => 'Description', 'type' => 'textarea'],
                ],
            ],
        ];
    }

    public static function section(string $key): ?array
    {
        return self::sections()[$key] ?? null;
    }

    /** Current value of a section, always shaped as the editor expects. */
    public static function value(string $key): array
    {
        $section = self::section($key);
        $value = University::get($key);

        if (($section['type'] ?? 'map') === 'repeater') {
            return array_is_list($value) ? $value : [];
        }

        return is_array($value) ? $value : [];
    }

    /**
     * Merge submitted fields over what is already stored, so keys this editor
     * does not expose (a slide's CTA blocks, a document's file path) survive a
     * save instead of being silently dropped.
     */
    public static function save(string $key, array $submitted): void
    {
        $section = self::section($key);
        abort_if($section === null, 404);

        $existing = University::get($key);

        if (($section['type'] ?? 'map') === 'repeater') {
            $rows = [];
            foreach (array_values($submitted) as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $row = array_filter($row, fn ($v) => $v !== null && $v !== '');
                if ($row === []) {
                    continue;   // a row left entirely blank is a deletion
                }
                $rows[] = array_merge((array) ($existing[$i] ?? []), $row);
            }
            $merged = $rows;
        } else {
            $merged = array_merge((array) $existing, $submitted);
        }

        Settings::set("university.{$key}", json_encode($merged, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /** `lines` fields arrive as a textarea and are stored as an array. */
    public static function splitLines(?string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $value))));
    }
}
