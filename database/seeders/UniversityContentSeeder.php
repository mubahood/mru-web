<?php

namespace Database\Seeders;

use App\Support\Settings;
use Illuminate\Database\Seeder;

/**
 * University identity & site copy for Muteesa I Royal University.
 *
 * Everything here is migrated fact from the legacy site (custom CMS `settings`
 * table, the WordPress options, and the university crest) — see
 * docs/02-OLD-SITE-ANALYSIS.md. Re-runnable: every key is simply overwritten.
 *
 * The motto exists in two wordings in the legacy data; the crest ribbon is the
 * authoritative one and is what we use: "Seeking Greater Horizons in Thought
 * and Action" (WordPress carried "Seeking Greater Horizon In Thoughts and
 * Actions").
 */
class UniversityContentSeeder extends Seeder
{
    public function run(): void
    {
        // Simple keys read by the admin settings screen and layout chrome.
        Settings::set('site_name', 'Muteesa I Royal University');
        Settings::set('tagline', 'Seeking Greater Horizons in Thought and Action');
        Settings::set('contact_email', 'info@mru.ac.ug');
        Settings::set('contact_phone', '+256 200 903 000');

        Settings::set('university.identity', json_encode([
            'name' => 'Muteesa I Royal University',
            'short' => 'MRU',
            'motto' => 'Seeking Greater Horizons in Thought and Action',
            'strapline' => 'Rooted in Heritage. Focused on the Future.',
            'vision' => 'To be a premier African university of choice, recognized for academic excellence, cultural pride, and producing graduates who are leaders in their fields.',
            'mission' => 'To provide quality, career-focused education rooted in Buganda\'s cultural heritage, nurturing innovative graduates who serve their communities with excellence and integrity.',
            'values' => [
                ['name' => 'Excellence', 'desc' => 'We pursue the highest standards in teaching, learning, research and service.'],
                ['name' => 'Integrity', 'desc' => 'We act honestly, ethically and transparently in everything we do.'],
                ['name' => 'Cultural Pride', 'desc' => 'We are rooted in the heritage of Buganda and celebrate the cultures of all our people.'],
                ['name' => 'Inclusivity', 'desc' => 'We welcome students and staff of every background, faith and ability.'],
                ['name' => 'Innovation', 'desc' => 'We embrace creativity, research and technology to solve real problems.'],
                ['name' => 'Community Service', 'desc' => 'We give back to the communities that surround and sustain us.'],
            ],
            'history' => [
                'Muteesa I Royal University (MRU) was both inspired and motivated by Ssabasajja Kabaka Muwenda Mutebi II, with strong support from the Executive Committee of the Buganda Kingdom, led by the Katikkiro. The University is named in honour of Kabaka Muteesa I of Buganda (1856–1884), a monarch celebrated for opening his kingdom to learning and new ideas.',
                'True to that legacy, MRU provides career-focused higher education rooted in cultural heritage, serving students at its Kirumba Campus in Masaka and Kakeeka Campus in Mengo, Kampala. The University is accredited by the Uganda National Council for Higher Education (NCHE).',
            ],
            'accreditation' => 'Accredited by the Uganda National Council for Higher Education (NCHE)',
            'namesake' => 'Named for Kabaka Muteesa I of Buganda (1856–1884)',
        ], JSON_UNESCAPED_UNICODE));

        Settings::set('university.contacts', json_encode([
            'phone' => '+256 200 903 000',
            'phone_alt' => '+256 414 670 109',
            'whatsapp' => '+256 752 033 889',
            'whatsapp_link' => 'https://wa.me/256752033889?text=Hello%2C+I+have+an+inquiry+about+Muteesa+I+Royal+University.',
            'email' => 'info@mru.ac.ug',
            'admissions_email' => 'admissions@mru.ac.ug',
            'careers_email' => 'careers@mru.ac.ug',
            'accommodation_email' => 'accommodation@mru.ac.ug',
            'pobox' => 'P.O. Box 1339, Kampala, Uganda',
            'campuses' => [
                ['name' => 'Kakeeka Campus', 'location' => 'Mengo, Kampala', 'maps' => 'https://maps.google.com/?q=Muteesa+I+Royal+University+Kakeeka+Mengo+Kampala'],
                ['name' => 'Kirumba Campus', 'location' => 'Kirumba, Masaka', 'maps' => 'https://maps.google.com/?q=Muteesa+I+Royal+University+Kirumba+Masaka'],
            ],
        ], JSON_UNESCAPED_UNICODE));

        Settings::set('university.social', json_encode([
            ['name' => 'X (Twitter)', 'icon' => 'fa-x-twitter', 'url' => 'https://twitter.com/MRU_Uganda', 'handle' => '@MRU_Uganda'],
            ['name' => 'Facebook', 'icon' => 'fa-facebook-f', 'url' => 'https://facebook.com/MuteesaRoyalUniversity', 'handle' => 'MuteesaRoyalUniversity'],
            ['name' => 'Instagram', 'icon' => 'fa-instagram', 'url' => 'https://instagram.com/muteesa_royal_university', 'handle' => '@muteesa_royal_university'],
            ['name' => 'LinkedIn', 'icon' => 'fa-linkedin-in', 'url' => 'https://linkedin.com/company/muteesa-royal-university', 'handle' => 'Muteesa I Royal University'],
        ], JSON_UNESCAPED_UNICODE));

        Settings::set('university.links', json_encode([
            'eportal' => 'https://eportal.mru.ac.ug/',
            'apply' => 'https://eportal.mru.ac.ug/apply',
            'eadmin' => 'https://eadmin.mru.ac.ug/',
            'library' => 'https://mru.ac.ug/library/',
        ], JSON_UNESCAPED_UNICODE));

        Settings::set('university.stats', json_encode([
            ['value' => '5', 'label' => 'Faculties & Graduate School'],
            ['value' => '46+', 'label' => 'Academic Programmes'],
            ['value' => '2', 'label' => 'Campuses — Kampala & Masaka'],
            ['value' => 'NCHE', 'label' => 'Accredited University'],
        ], JSON_UNESCAPED_UNICODE));

        Settings::set('university.admissions', json_encode([
            'application_fee' => 'UGX 50,000',
            'processing_fee' => 'UGX 60,000 (admission processing)',
            'apply_url' => 'https://eportal.mru.ac.ug/apply',
            'deadline_note' => 'Applications for the August intake close on 31 May. Late applications are considered on merit.',
            'intakes' => [
                ['name' => 'August Intake', 'window' => 'January – May', 'starts' => 'August'],
                ['name' => 'January Intake', 'window' => 'September – December', 'starts' => 'January'],
            ],
            'timeline' => [
                ['stage' => 'Apply', 'when' => 'January – February', 'desc' => 'Submit your application on the E-Portal or pick a form from any campus.'],
                ['stage' => 'Deadline', 'when' => 'April – May', 'desc' => 'Applications close. Late submissions are considered on available capacity.'],
                ['stage' => 'Admission results', 'when' => 'June – July', 'desc' => 'Admission lists are published on this website and the E-Portal.'],
                ['stage' => 'Enrolment', 'when' => 'August', 'desc' => 'Pay fees, register, and attend orientation at your campus.'],
            ],
            'requirements' => [
                'bachelors' => [
                    'Uganda Advanced Certificate of Education (UACE) with at least 2 principal passes, or',
                    'A relevant diploma of at least 2 years from a recognised institution, or',
                    'An NCHE-recognised professional qualification.',
                ],
                'diplomas' => [
                    'Uganda Certificate of Education (UCE) with at least 5 passes,',
                    'School leaving certificate,',
                    'National ID or passport,',
                    'Academic transcripts and 2 passport photographs.',
                ],
                'masters' => [
                    'A bachelor\'s degree from a recognised university (minimum second class or equivalent experience),',
                    'Academic transcripts and certificates,',
                    'National ID or passport and passport photographs.',
                ],
                'international' => [
                    'Valid passport,',
                    'UACE / A-Level equivalent qualifications (authenticated),',
                    'Certified academic transcripts,',
                    'Proof of funds for tuition and living costs,',
                    'English proficiency: IELTS 6.0+, TOEFL 79+ iBT, or CAE grade C.',
                ],
            ],
            'steps' => [
                ['title' => 'Choose your programme', 'desc' => 'Browse the programmes directory and confirm the entry requirements and fees.'],
                ['title' => 'Create an E-Portal account', 'desc' => 'Register at eportal.mru.ac.ug with a working email address and phone number.'],
                ['title' => 'Fill the application form', 'desc' => 'Complete your biodata, academic history and programme choices.'],
                ['title' => 'Upload your documents', 'desc' => 'Attach transcripts, certificates, ID and passport photos.'],
                ['title' => 'Pay the application fee', 'desc' => 'UGX 50,000 via MTN MoMo (*165*80#) or Airtel Money (*185*6*2#) using your payment code.'],
                ['title' => 'Submit and track', 'desc' => 'Submit the application and track its status on the E-Portal.'],
                ['title' => 'Receive your admission', 'desc' => 'Admitted students receive a letter and joining instructions by email and on the portal.'],
            ],
            'payment_codes' => [
                ['provider' => 'MTN Mobile Money', 'code' => '*165*80#'],
                ['provider' => 'Airtel Money', 'code' => '*185*6*2#'],
            ],
        ], JSON_UNESCAPED_UNICODE));

        Settings::set('university.accommodation', json_encode([
            ['name' => 'Kabaka Hall', 'audience' => 'Male students', 'campus' => 'Main Campus', 'beds' => 400, 'price' => 'UGX 800,000 – 1,200,000 per semester'],
            ['name' => 'Princess Hall', 'audience' => 'Female students', 'campus' => 'Main Campus', 'beds' => 350, 'price' => 'UGX 800,000 – 1,200,000 per semester'],
            ['name' => 'Graduate Residence', 'audience' => 'Postgraduate students', 'campus' => 'Central', 'beds' => 150, 'price' => 'UGX 1,500,000 – 2,000,000 per semester'],
        ], JSON_UNESCAPED_UNICODE));

        // Interim identity for surfaces still reading the model's portfolio keys
        // (retired fully in the public-pages phase).
        Settings::set('portfolio.identity', json_encode([
            'name' => 'Muteesa I Royal University',
            'title' => 'Seeking Greater Horizons in Thought and Action',
            'tagline' => 'Rooted in Heritage. Focused on the Future.',
            'initials' => 'MRU',
            'location' => 'Kampala & Masaka, Uganda',
            'subtitle' => 'A chartered private university of the Buganda Kingdom',
        ], JSON_UNESCAPED_UNICODE));

        Settings::set('portfolio.contact', json_encode([
            'email' => 'info@mru.ac.ug',
            'phone' => '+256 200 903 000',
            'whatsapp' => '+256752033889',
            'location' => 'Kakeeka, Mengo (Kampala) & Kirumba (Masaka)',
        ], JSON_UNESCAPED_UNICODE));
    }
}
