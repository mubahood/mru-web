<?php

namespace App\Support;

/**
 * The public navigation, defined once.
 *
 * The desktop bar, the mega panel, the mobile sheet and the footer columns all
 * render from this, so the four cannot drift. It lives in PHP rather than a
 * Blade partial because a route list is worth asserting against in a test.
 *
 * Six topic items (About / Admissions / Academics / Research / Student Life /
 * News & Events), per the higher-ed IA research in
 * docs/03-RESEARCH-TRENDS-BEST-PRACTICES.md §1: topics in the bar, audience
 * shortcuts (E-Portal, Apply) as standing header actions.
 */
class SiteNav
{
    /**
     * @return list<array<string,mixed>>
     */
    public static function items(): array
    {
        return [
            [
                'label' => 'About',
                'url' => route('about'),
                'match' => [
                    'about', 'who-we-are', 'governance', 'council', 'staff.directory', 'contact',
                ],
                'icon' => 'fa-building-columns',
                'blurb' => 'A royal university of the Buganda Kingdom, accredited by NCHE.',
                'children' => [
                    ['label' => 'About MRU', 'url' => route('about'), 'icon' => 'fa-building-columns',
                        'desc' => 'Our story, our campuses and what we stand for.',
                        'match' => ['about']],
                    ['label' => 'Who we are', 'url' => route('who-we-are'), 'icon' => 'fa-compass',
                        'desc' => 'Mission, vision and the six core values.',
                        'match' => ['who-we-are']],
                    ['label' => 'Governance', 'url' => route('governance'), 'icon' => 'fa-scale-balanced',
                        'desc' => 'University officers and committees.',
                        'match' => ['governance']],
                    ['label' => 'University Council', 'url' => route('council'), 'icon' => 'fa-users',
                        'desc' => 'The governing council and its members.',
                        'match' => ['council']],
                    ['label' => 'Staff directory', 'url' => route('staff.directory'), 'icon' => 'fa-address-book',
                        'desc' => 'Find academic and administrative staff.',
                        'match' => ['staff.directory']],
                    ['label' => 'Contact us', 'url' => route('contact'), 'icon' => 'fa-envelope',
                        'desc' => 'Phones, emails, WhatsApp and campus addresses.',
                        'match' => ['contact']],
                ],
            ],
            [
                'label' => 'Admissions',
                'url' => route('admissions.index'),
                'match' => ['admissions.*'],
                'icon' => 'fa-graduation-cap',
                // The gold dot: the one thing a prospective student should see first.
                'flag' => true,
                'blurb' => 'Applications for the August intake close 31 May.',
                'children' => [
                    ['label' => 'Admissions overview', 'url' => route('admissions.index'), 'icon' => 'fa-door-open',
                        'desc' => 'How admission works, end to end.',
                        'match' => ['admissions.index']],
                    ['label' => 'Entry requirements', 'url' => route('admissions.requirements'), 'icon' => 'fa-list-check',
                        'desc' => 'What you need for each level of study.',
                        'match' => ['admissions.requirements']],
                    ['label' => 'How to apply', 'url' => route('admissions.apply'), 'icon' => 'fa-file-pen',
                        'desc' => 'The seven steps, on the E-Portal or on paper.',
                        'match' => ['admissions.apply']],
                    ['label' => 'Fees structure', 'url' => route('admissions.fees'), 'icon' => 'fa-money-bill-wave',
                        'desc' => 'Tuition per faculty, and how to pay.',
                        'match' => ['admissions.fees']],
                    ['label' => 'Scholarships & bursaries', 'url' => route('admissions.scholarships'), 'icon' => 'fa-hand-holding-heart',
                        'desc' => 'Six schemes, including the Kabaka\'s Scholarship.',
                        'match' => ['admissions.scholarships']],
                    ['label' => 'Intakes & deadlines', 'url' => route('admissions.intakes'), 'icon' => 'fa-calendar-days',
                        'desc' => 'August and January intakes, key dates.',
                        'match' => ['admissions.intakes']],
                    ['label' => 'International students', 'url' => route('admissions.international'), 'icon' => 'fa-globe',
                        'desc' => 'Requirements and support for students from abroad.',
                        'match' => ['admissions.international']],
                    ['label' => 'FAQs', 'url' => route('admissions.faqs'), 'icon' => 'fa-circle-question',
                        'desc' => 'Quick answers to the questions we hear most.',
                        'match' => ['admissions.faqs']],
                ],
            ],
            [
                'label' => 'Academics',
                'url' => route('programmes.index'),
                'match' => ['faculties.*', 'programmes.*', 'almanac', 'library', 'downloads', 'courses.*'],
                'icon' => 'fa-book-open',
                'blurb' => '46+ programmes across five faculties and the Graduate School.',
                'children' => [
                    ['label' => 'All programmes', 'url' => route('programmes.index'), 'icon' => 'fa-magnifying-glass',
                        'desc' => 'Find your programme by level, faculty or name.',
                        'match' => ['programmes.*']],
                    ['label' => 'Faculties & schools', 'url' => route('faculties.index'), 'icon' => 'fa-school',
                        'desc' => 'Five faculties and the Graduate School.',
                        'match' => ['faculties.*']],
                    ['label' => 'Academic almanac', 'url' => route('almanac'), 'icon' => 'fa-calendar-week',
                        'desc' => 'The academic year, week by week.',
                        'match' => ['almanac']],
                    ['label' => 'e-Learning', 'url' => route('courses.index'), 'icon' => 'fa-laptop',
                        'desc' => 'Short online courses with verifiable certificates.',
                        'match' => ['courses.*']],
                    ['label' => 'Library', 'url' => route('library'), 'icon' => 'fa-book',
                        'desc' => 'Library services on both campuses.',
                        'match' => ['library']],
                    ['label' => 'Downloads', 'url' => route('downloads'), 'icon' => 'fa-file-arrow-down',
                        'desc' => 'Prospectus, policies, forms and guides.',
                        'match' => ['downloads']],
                ],
            ],
            [
                'label' => 'Research',
                'url' => route('scholar.home'),
                'match' => ['scholar.*'],
                'icon' => 'fa-flask',
                'blurb' => 'MRU Scholar: the university\'s open research repository.',
                'children' => [
                    ['label' => 'MRU Scholar', 'url' => route('scholar.home'), 'icon' => 'fa-flask',
                        'desc' => 'The research repository, at a glance.',
                        'match' => ['scholar.home']],
                    ['label' => 'Publications', 'url' => route('scholar.publications'), 'icon' => 'fa-file-lines',
                        'desc' => 'Browse and download research publications.',
                        'match' => ['scholar.publications', 'scholar.publication']],
                    ['label' => 'Scholars directory', 'url' => route('scholar.directory'), 'icon' => 'fa-user-graduate',
                        'desc' => 'The researchers behind the work.',
                        'match' => ['scholar.directory', 'scholar.profile']],
                ],
            ],
            [
                'label' => 'Student Life',
                'url' => route('campus-life'),
                'match' => ['campus-life', 'accommodation', 'sports', 'guild', 'alumni'],
                'icon' => 'fa-people-group',
                'blurb' => 'Life at Kakeeka and Kirumba, in and out of class.',
                'children' => [
                    ['label' => 'Campus life', 'url' => route('campus-life'), 'icon' => 'fa-people-group',
                        'desc' => 'Learning, sports, culture and community.',
                        'match' => ['campus-life']],
                    ['label' => 'Accommodation', 'url' => route('accommodation'), 'icon' => 'fa-bed',
                        'desc' => 'Halls of residence and how to book a room.',
                        'match' => ['accommodation']],
                    ['label' => 'Sports & lifestyle', 'url' => route('sports'), 'icon' => 'fa-futbol',
                        'desc' => 'Royals FC and five more disciplines.',
                        'match' => ['sports']],
                    ['label' => 'Students\' Guild', 'url' => route('guild'), 'icon' => 'fa-landmark-flag',
                        'desc' => 'Your elected student government.',
                        'match' => ['guild']],
                    ['label' => 'Alumni', 'url' => route('alumni'), 'icon' => 'fa-user-clock',
                        'desc' => 'Stay connected after you graduate.',
                        'match' => ['alumni']],
                ],
            ],
            [
                'label' => 'News & Events',
                'url' => route('insights.index'),
                'match' => ['insights.*', 'events.*', 'gallery.*', 'vacancies.*'],
                'icon' => 'fa-newspaper',
                'blurb' => 'What is happening across the university.',
                'children' => [
                    ['label' => 'News', 'url' => route('insights.index'), 'icon' => 'fa-newspaper',
                        'desc' => 'Announcements and stories from MRU.',
                        'match' => ['insights.*']],
                    ['label' => 'Events', 'url' => route('events.index'), 'icon' => 'fa-calendar-days',
                        'desc' => 'Upcoming events on both campuses.',
                        'match' => ['events.*']],
                    ['label' => 'Gallery', 'url' => route('gallery.index'), 'icon' => 'fa-images',
                        'desc' => 'The university in pictures.',
                        'match' => ['gallery.*']],
                    ['label' => 'Vacancies', 'url' => route('vacancies.index'), 'icon' => 'fa-briefcase',
                        'desc' => 'Work with Muteesa I Royal University.',
                        'match' => ['vacancies.*']],
                ],
            ],
        ];
    }

    /** Every destination the menu can reach, for smoke-testing that none 404s. */
    public static function urls(): array
    {
        $urls = [];
        foreach (self::items() as $item) {
            $urls[] = $item['url'];
            foreach ($item['children'] ?? [] as $child) {
                $urls[] = $child['url'];
            }
        }

        return array_values(array_unique($urls));
    }
}
