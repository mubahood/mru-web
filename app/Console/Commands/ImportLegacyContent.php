<?php

namespace App\Console\Commands;

use App\Models\AlmanacEntry;
use App\Models\Faculty;
use App\Models\NewsletterSubscriber;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Programme;
use App\Models\Publication;
use App\Models\PublicationAuthor;
use App\Models\ResearchArea;
use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\StaffMember;
use App\Models\UniversityEvent;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * One-time migration of the legacy mru.ac.ug content into the new models.
 *
 * Sources (see docs/02-OLD-SITE-ANALYSIS.md):
 *  - `mru_legacy`    — the custom PHP CMS database (imported from mru_mru2.sql)
 *  - `mru_legacy_wp` — the live WordPress database (imported from mru_wp435.sql)
 *  - the backup tree at BACKUP_ROOT for images and PDFs
 *
 * Idempotent: every write is an updateOrCreate on a natural key, so the
 * command can be re-run after a fix without duplicating rows. Data-quality
 * rules applied here (placeholder emails dropped, localhost image URLs
 * stripped, casino spam filtered, fee bands instead of the mangled fee table)
 * are documented in docs/04-IMPLEMENTATION-LOG.md.
 */
class ImportLegacyContent extends Command
{
    protected $signature = 'mru:import-legacy {--skip-wp : Skip the WordPress news and scholar import}';

    protected $description = 'Import legacy MRU site content (custom CMS + WordPress) into the university tables';

    private const BACKUP_ROOT = '/Users/mac/Downloads/uploads-1.zip';

    /** Legacy CMS faculty id → new Faculty id */
    private array $facultyMap = [];

    /** Legacy programs_cat prefix → new Faculty id */
    private array $facultyByCategory = [];

    public function handle(): int
    {
        foreach (['mru_legacy', 'mru_legacy_wp'] as $name) {
            config(["database.connections.$name" => array_merge(
                config('database.connections.mysql'),
                ['database' => $name]
            )]);
        }

        $this->importFaculties();
        $this->importStaff();
        $this->importCouncil();
        $this->importCommittees();
        $this->importGuild();
        $this->importProgrammes();
        $this->importEvents();
        $this->importAlmanac();
        $this->importScholarships();
        $this->importPartners();
        $this->importSportsAndHero();
        $this->importNewsletter();

        if (! $this->option('skip-wp')) {
            $this->importWpScholars();
            $this->importWpPublications();
            $this->importWpNews();
        }

        $this->importCmsPublications();

        $this->info('Legacy import complete.');

        return self::SUCCESS;
    }

    private function legacy(): \Illuminate\Database\Connection
    {
        return DB::connection('mru_legacy');
    }

    private function wp(): \Illuminate\Database\Connection
    {
        return DB::connection('mru_legacy_wp');
    }

    /**
     * Copy a file out of the backup tree onto the public disk.
     * Returns the stored relative path, or null when the source is missing.
     */
    private function copyAsset(?string $legacyPath, string $destDir): ?string
    {
        if (blank($legacyPath)) {
            return null;
        }

        $legacyPath = ltrim((string) parse_url($legacyPath, PHP_URL_PATH), '/');
        // Placeholder artwork is worse than an empty slot the UI can style.
        if (str_contains($legacyPath, 'committee_unknown_user') || str_contains($legacyPath, 'dummy.png')) {
            return null;
        }

        $source = self::BACKUP_ROOT.'/'.$legacyPath;
        if (! File::exists($source)) {
            return null;
        }

        $basename = Str::limit(preg_replace('/[^A-Za-z0-9._-]/', '_', basename($legacyPath)), 120, '');
        $dest = $destDir.'/'.$basename;
        Storage::disk('public')->put($dest, File::get($source));

        return $dest;
    }

    private function importFaculties(): void
    {
        foreach ($this->legacy()->table('faculties')->get() as $row) {
            $faculty = Faculty::updateOrCreate(['slug' => $row->slug], [
                'name' => self::properName($row->name),
                'short_name' => $row->short,
                'tagline' => $row->tagline,
                'description' => $row->description,
                'about' => $row->about,
                'vision' => $row->vision,
                'mission' => $row->mission,
                'color' => '#05275C',
                'icon' => $row->icon ? str_replace('fas ', '', $row->icon) : 'fa-building-columns',
                'departments' => self::decodeList($row->departments),
                'careers' => self::decodeList($row->careers),
                'sort_order' => $row->id,
                'is_published' => true,
            ]);

            $this->facultyMap[$row->id] = $faculty->id;
            if ($row->programs_cat) {
                $this->facultyByCategory[trim($row->programs_cat)] = $faculty->id;
            }
        }

        $this->line('Faculties: '.Faculty::count());
    }

    /** "FACULTY OF EDUCATION" → "Faculty of Education". */
    private static function properName(string $name): string
    {
        $name = Str::title(mb_strtolower(trim($name)));

        return preg_replace_callback(
            '/\b(Of|And|The|In|For|At|On|With)\b/',
            fn ($m) => mb_strtolower($m[1]),
            $name
        ) ?? $name;
    }

    private static function decodeList(?string $json): ?array
    {
        $decoded = json_decode((string) $json, true);

        return is_array($decoded) && $decoded !== [] ? array_values($decoded) : null;
    }

    private function importStaff(): void
    {
        $leadershipTitles = ['vice chancellor', 'academic registrar', 'dean of students', 'head librarian', 'finance officer', 'university secretary', 'bursar'];

        foreach ($this->legacy()->table('staff')->get() as $row) {
            $title = trim((string) $row->title);
            $isLeadership = collect($leadershipTitles)->contains(fn ($t) => str_contains(mb_strtolower($title), $t));

            StaffMember::updateOrCreate(
                ['name' => trim($row->name), 'group_label' => null],
                [
                    'title' => $title ?: null,
                    'staff_role' => $isLeadership ? 'leadership' : ($row->staff_role === 'lecturer' ? 'lecturer' : 'administrative'),
                    'faculty_id' => $this->facultyMap[$row->faculty_id] ?? null,
                    'department' => $row->department ?: null,
                    'email' => str_contains((string) $row->email, 'example.com') ? null : ($row->email ?: null),
                    'phone' => strlen(trim((string) $row->phone)) < 7 ? null : trim($row->phone),
                    'bio' => $row->bio ?: null,
                    'education' => $row->education ?: null,
                    'photo' => $this->copyAsset($row->image, 'university/staff'),
                    'sort_order' => $isLeadership ? $row->id : 100 + $row->id,
                    'is_published' => (bool) $row->status,
                ]
            );
        }

        $this->line('Staff: '.StaffMember::whereNull('group_label')->count());
    }

    private function importCouncil(): void
    {
        // Legacy data quirk: the member's NAME sits in `role` and their actual
        // role (Chairperson / Member) in `description`.
        foreach ($this->legacy()->table('council_members')->orderBy('display_order')->get() as $row) {
            StaffMember::updateOrCreate(
                ['name' => trim($row->role), 'group_label' => 'University Council'],
                [
                    'title' => trim((string) $row->description) ?: 'Member',
                    'staff_role' => 'council',
                    'department' => $row->category ?: null,
                    'education' => $row->education ?: null,
                    'photo' => $this->copyAsset($row->image, 'university/council'),
                    'sort_order' => $row->display_order,
                    'is_published' => (bool) $row->status,
                ]
            );
        }

        $this->line('Council: '.StaffMember::where('group_label', 'University Council')->count());
    }

    private function importCommittees(): void
    {
        $committees = $this->legacy()->table('committees')->pluck('title', 'id');

        foreach ($this->legacy()->table('committee_members')->orderBy('display_order')->get() as $row) {
            $committee = $committees[$row->committee_id] ?? 'University Committee';

            StaffMember::updateOrCreate(
                ['name' => trim($row->name), 'group_label' => $committee],
                [
                    'title' => $row->role ?: 'Member',
                    'staff_role' => 'committee',
                    'bio' => $row->description ?: null,
                    'education' => $row->education ?: null,
                    'photo' => $this->copyAsset($row->image, 'university/committees'),
                    'sort_order' => $row->display_order,
                    'is_published' => (bool) $row->status,
                ]
            );
        }

        $this->line('Committee members: '.StaffMember::where('staff_role', 'committee')->count());
    }

    private function importGuild(): void
    {
        foreach ($this->legacy()->table('guild_members')->orderBy('display_order')->get() as $row) {
            StaffMember::updateOrCreate(
                ['name' => trim($row->name), 'group_label' => "Students' Guild"],
                [
                    'title' => $row->role ?: 'Member',
                    'staff_role' => 'guild',
                    'bio' => $row->portfolio ?: null,
                    'photo' => $this->copyAsset($row->image, 'university/guild'),
                    'sort_order' => $row->display_order,
                    'is_published' => $row->status === 'published',
                ]
            );
        }

        $this->line('Guild: '.StaffMember::where('group_label', "Students' Guild")->count());
    }

    private function importProgrammes(): void
    {
        // Tuition uses the three verified faculty bands the university itself
        // publishes (fees_structure + the live /fees page). The detailed
        // 128-row programme_fees table is PDF-scrape damage and is deliberately
        // NOT imported — see docs/04-IMPLEMENTATION-LOG.md.
        $bands = [
            'Business' => 1200000,
            'Education' => 1000000,
            'Social Sciences' => 1000000,
            'Science Technology' => 1500000,
        ];

        foreach ($this->legacy()->table('programs')->get() as $row) {
            $category = trim((string) $row->category); // e.g. "Education Undergraduate"
            $level = self::levelFromCategory($category, $row->name);
            $facultyId = null;
            $band = null;

            foreach ($this->facultyByCategory as $prefix => $id) {
                if ($prefix !== '' && str_starts_with($category, $prefix)) {
                    $facultyId = $id;
                    break;
                }
            }
            foreach ($bands as $prefix => $amount) {
                if (str_starts_with($category, $prefix)) {
                    $band = $amount;
                    break;
                }
            }

            [$name, $awardCode] = self::splitAwardCode($row->name);
            $postgraduate = in_array($level, ['masters', 'postgraduate_diploma'], true);

            Programme::updateOrCreate(['name' => $name, 'level' => $level], [
                'faculty_id' => $facultyId,
                'award_code' => $awardCode,
                'duration' => self::defaultDuration($level, $name),
                'tuition_per_semester' => $postgraduate ? null : $band,
                'tuition_note' => $postgraduate
                    ? 'Contact the Graduate School for the current fees schedule.'
                    : 'Estimated; confirm the current fees schedule with the Bursar\'s office.',
                'entry_requirements' => self::defaultRequirements($level),
                'description' => $row->description ?: null,
                'intake_months' => ['August', 'January'],
                'image' => $this->copyAsset($row->image, 'university/programmes'),
                'sort_order' => $row->id,
                'is_published' => true,
            ]);
        }

        $this->line('Programmes: '.Programme::count());
    }

    private static function levelFromCategory(string $category, string $name): string
    {
        if (preg_match('/^(advanced )?certificate/i', $name)) {
            return 'certificate';
        }
        if (str_contains($category, 'Diploma') || preg_match('/^diploma/i', $name)) {
            return 'diploma';
        }
        if (str_contains($category, 'Postgraduate')) {
            return preg_match('/^postgraduate diploma/i', $name) ? 'postgraduate_diploma' : 'masters';
        }

        return 'bachelor';
    }

    /** "Bachelor of Education (BED/P)" → ["Bachelor of Education", "BED/P"] */
    private static function splitAwardCode(string $name): array
    {
        if (preg_match('/^(.*?)\s*\(([A-Z][A-Z0-9\/\s&.-]{1,20})\)\s*$/', trim($name), $m)) {
            return [trim($m[1]), trim($m[2])];
        }

        return [trim($name), null];
    }

    private static function defaultDuration(string $level, string $name): string
    {
        if (str_contains(mb_strtolower($name), 'engineering') && $level === 'bachelor') {
            return '4 years';
        }

        return match ($level) {
            'certificate' => '1 year',
            'diploma' => '2 years',
            'postgraduate_diploma' => '1 year',
            'masters' => '2 years',
            default => '3 years',
        };
    }

    private static function defaultRequirements(string $level): string
    {
        return match ($level) {
            'certificate', 'diploma' => 'Uganda Certificate of Education (UCE) with at least 5 passes, or equivalent qualifications recognised by NCHE.',
            'masters', 'postgraduate_diploma' => 'A bachelor\'s degree from a recognised university, or an equivalent qualification recognised by NCHE.',
            default => 'Uganda Advanced Certificate of Education (UACE) with at least 2 principal passes, or a relevant diploma, or an NCHE-recognised equivalent qualification.',
        };
    }

    private function importEvents(): void
    {
        // The legacy events table is mostly theme demo content ("adam",
        // "Falar's Career Fair", "Luva's Athletic Show...") — import only rows
        // that are dated and not recognisable demo junk.
        $junk = ['adam', 'falar', 'luva'];

        foreach ($this->legacy()->table('events')->get() as $row) {
            $startsAt = self::combineDateTime($row->event_date, $row->event_time);
            if (! $startsAt) {
                continue;
            }
            foreach ($junk as $needle) {
                if (str_contains(mb_strtolower($row->title), $needle)) {
                    continue 2;
                }
            }

            UniversityEvent::updateOrCreate(['title' => trim($row->title), 'starts_at' => $startsAt], [
                'excerpt' => Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $row->description)) ?? ''), 280),
                'description' => self::cleanLegacyHtml((string) $row->description),
                'venue' => $row->location ?: null,
                'category' => $row->category ?: null,
                'image' => $this->copyAsset($row->image, 'university/events'),
                'faculty_id' => $this->facultyMap[$row->faculty_id] ?? null,
                'is_published' => $row->status === 'published',
            ]);
        }

        $this->line('Events: '.UniversityEvent::count());
    }

    private static function combineDateTime(?string $date, ?string $time): ?\Illuminate\Support\Carbon
    {
        if (blank($date)) {
            return null;
        }

        try {
            $base = \Illuminate\Support\Carbon::parse($date)->setTime(9, 0);
            if (filled($time) && ($parsed = strtotime((string) $time)) !== false) {
                $base->setTimeFromTimeString(date('H:i', $parsed));
            }

            return $base;
        } catch (\Throwable) {
            return null;
        }
    }

    /** Strip dead localhost embeds and scripts the legacy WYSIWYG left behind. */
    private static function cleanLegacyHtml(string $html): string
    {
        $html = preg_replace('/<img[^>]+(localhost|127\.0\.0\.1)[^>]*>/i', '', $html) ?? $html;
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;

        return trim($html);
    }

    private function importAlmanac(): void
    {
        // The legacy table carries no year column; its rows were entered for
        // the 2026/2027 session (docs/02 §3, "News / events").
        foreach ($this->legacy()->table('academic_almanac')->orderBy('display_order')->get() as $row) {
            $period = trim((string) $row->week_name);
            if (filled($row->date_range)) {
                $period = trim($period.($period !== '' ? ' — ' : '').$row->date_range);
            }

            AlmanacEntry::updateOrCreate(
                ['academic_year' => '2026/2027', 'semester' => trim($row->semester), 'activity' => trim($row->activity)],
                ['period' => $period ?: null, 'sort_order' => $row->display_order]
            );
        }

        $this->line('Almanac entries: '.AlmanacEntry::count());
    }

    private function importScholarships(): void
    {
        foreach ($this->legacy()->table('scholarships')->orderBy('display_order')->get() as $row) {
            Scholarship::updateOrCreate(['name' => trim($row->name)], [
                'category' => $row->type ?: null,
                'coverage' => $row->coverage ?: null,
                'criteria' => $row->eligibility ?: null,
                'amount_note' => $row->amount ?: null,
                'description' => $row->description ?: null,
                'sort_order' => $row->display_order,
                'is_published' => $row->status === 'active',
            ]);
        }

        $this->line('Scholarships: '.Scholarship::count());
    }

    private function importPartners(): void
    {
        foreach ($this->legacy()->table('partners')->orderBy('display_order')->get() as $row) {
            Partner::updateOrCreate(['name' => trim($row->name)], [
                'logo' => $this->copyAsset($row->logo, 'university/partners'),
                'url' => $row->website ?: null,
                'sort_order' => $row->display_order,
            ]);
        }

        $this->line('Partners: '.Partner::count());
    }

    private function importSportsAndHero(): void
    {
        // Five of the six sports sat in draft on the old site with finished
        // copy; the discipline list itself is real, so status is ignored.
        $sports = $this->legacy()->table('sports')
            ->orderBy('display_order')->get()
            ->map(fn ($row) => [
                'title' => $row->title,
                'icon' => str_replace('fas ', '', (string) $row->icon_class),
                'description' => $row->description,
                'tags' => array_values(array_filter(array_map('trim', explode(',', (string) $row->tags)))),
            ])->values()->all();

        Settings::set('university.sports', json_encode($sports, JSON_UNESCAPED_UNICODE));

        $slides = $this->legacy()->table('hero_slides')->where('status', 'active')
            ->orderBy('display_order')->get()
            ->map(fn ($row) => [
                'title' => $row->title,
                'subtitle' => $row->subtitle,
                'image' => $this->copyAsset($row->bg_image, 'university/hero'),
            ])->values()->all();

        Settings::set('university.hero_slides', json_encode($slides, JSON_UNESCAPED_UNICODE));

        $this->line('Sports: '.count($sports).', hero slides: '.count($slides));
    }

    private function importNewsletter(): void
    {
        $imported = 0;
        foreach ($this->legacy()->table('newsletter_subscribers')->get() as $row) {
            $email = mb_strtolower(trim((string) $row->email));
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            NewsletterSubscriber::firstOrCreate(['email' => $email]);
            $imported++;
        }

        $this->line("Newsletter subscribers: $imported");
    }

    // ── WordPress side ────────────────────────────────────────────────────

    private function importWpScholars(): void
    {
        $rows = $this->wp()->table('wp8a_mru_scholar_users as su')
            ->leftJoin('wp8a_users as u', 'u.ID', '=', 'su.wp_user_id')
            ->select('su.*', 'u.display_name', 'u.user_email')
            ->get();

        foreach ($rows as $row) {
            $name = trim((string) $row->display_name);
            if ($name === '' || mb_strtolower($name) === 'test user') {
                continue;
            }

            $facultyId = null;
            if (filled($row->faculty)) {
                $facultyId = Faculty::query()
                    ->where('name', 'like', '%'.trim($row->faculty).'%')->value('id');
            }

            Scholar::updateOrCreate(['name' => $name], [
                'title' => $row->designation ?: null,
                'faculty_id' => $facultyId,
                'department' => $row->department ?: null,
                'bio' => $row->bio ?: null,
                'email' => filter_var((string) $row->user_email, FILTER_VALIDATE_EMAIL) ? $row->user_email : null,
                'is_published' => (bool) $row->is_active,
            ]);
        }

        $this->line('Scholars: '.Scholar::count());
    }

    private function importWpPublications(): void
    {
        $categories = $this->wp()->table('wp8a_mru_scholar_categories')->get()->keyBy('id');

        // The WP taxonomy doubles as both a type and a subject; carry it over
        // as a research area, and derive the publication type from it.
        $areaMap = [];
        foreach ($categories as $category) {
            $areaMap[$category->id] = ResearchArea::updateOrCreate(
                ['name' => trim($category->name)],
                ['description' => $category->description ?: null]
            )->id;
        }

        $typeFor = fn (?string $categoryName) => match (true) {
            $categoryName === null => 'journal',
            str_contains($categoryName, 'Thesis') => 'thesis',
            str_contains($categoryName, 'Conference') => 'conference',
            str_contains($categoryName, 'Book') => 'book_chapter',
            str_contains($categoryName, 'Technical'),
            str_contains($categoryName, 'Project Report') => 'report',
            default => 'journal',
        };

        // Scholar map keyed by the WP scholar-user id, resolved through names.
        $wpScholars = $this->wp()->table('wp8a_mru_scholar_users as su')
            ->leftJoin('wp8a_users as u', 'u.ID', '=', 'su.wp_user_id')
            ->pluck('u.display_name', 'su.id');
        $scholarIds = [];
        foreach ($wpScholars as $wpId => $name) {
            $scholarIds[$wpId] = Scholar::where('name', trim((string) $name))->value('id');
        }

        $files = $this->wp()->table('wp8a_mru_scholar_publication_files')
            ->orderByDesc('is_primary')->get()->groupBy('publication_id');
        $authors = $this->wp()->table('wp8a_mru_scholar_publication_authors')
            ->orderBy('author_order')->get()->groupBy('publication_id');

        foreach ($this->wp()->table('wp8a_mru_scholar_publications')->get() as $row) {
            $categoryName = $categories[$row->category_id]->name ?? null;
            $pdf = null;
            foreach ($files->get($row->id, collect()) as $file) {
                // file_path holds a bare filename; the plugin kept everything
                // in one uploads folder.
                $pdf = $this->copyAsset('wp-content/uploads/mru-scholar/'.ltrim((string) $file->file_path, '/'), 'scholar/publications');
                if ($pdf) {
                    break;
                }
            }

            $publication = Publication::updateOrCreate(['title' => trim($row->title)], [
                'abstract' => $row->abstract ?: null,
                'type' => $typeFor($categoryName),
                'journal_name' => $row->journal_name ?: null,
                'publisher' => $row->publisher ?: null,
                'volume' => $row->volume_number ?: null,
                'issue' => $row->issue_number ?: null,
                'pages' => $row->page_range ?: null,
                'publication_date' => $row->published_date ? substr((string) $row->published_date, 0, 10) : null,
                'doi' => Str::limit(trim((string) $row->doi), 120, '') ?: null,
                'keywords' => Str::limit((string) $row->keywords, 490, '') ?: null,
                'citations' => (int) $row->citations,
                'views' => (int) $row->views,
                'downloads' => (int) $row->downloads,
                'pdf_path' => $pdf,
                'status' => $row->status === 'approved' ? 'published' : 'draft',
                'is_featured' => (bool) $row->is_featured,
            ]);

            if (isset($areaMap[$row->category_id])) {
                $publication->researchAreas()->syncWithoutDetaching([$areaMap[$row->category_id]]);
            }

            $publication->authorRows()->delete();
            foreach ($authors->get($row->id, collect()) as $author) {
                PublicationAuthor::create([
                    'publication_id' => $publication->id,
                    'scholar_id' => $author->is_external ? null : ($scholarIds[$author->user_id] ?? null),
                    'external_name' => $author->is_external ? ($author->external_name ?: null) : null,
                    'author_order' => (int) $author->author_order,
                ]);
            }
        }

        $this->line('Publications: '.Publication::count().', research areas: '.ResearchArea::count());
    }

    private function importWpNews(): void
    {
        $spam = ['casino', 'gambl', 'betting', 'bookmaker', 'jackpot', 'slot machine', 'wager',
            'allyspin', 'betzino', 'binobet', 'bitstake', 'b7 casino', 'spins', 'sportsbook', 'kasyno', 'zaklad'];

        $author = User::where('role', 'super_admin')->first();

        $posts = $this->wp()->table('wp8a_posts')
            ->where('post_type', 'post')->where('post_status', 'publish')
            ->orderBy('post_date')
            ->get();

        $imported = 0;
        $skipped = 0;

        foreach ($posts as $row) {
            $haystack = mb_strtolower($row->post_title.' '.$row->post_content);
            foreach ($spam as $needle) {
                if (str_contains($haystack, $needle)) {
                    $skipped++;

                    continue 2;
                }
            }

            $cover = $this->wpFeaturedImage((int) $row->ID);

            Post::updateOrCreate(['slug' => $row->post_name ?: Str::slug($row->post_title)], [
                'title' => trim($row->post_title),
                'body' => self::htmlToMarkdown((string) $row->post_content),
                'category' => 'News',
                'cover_image' => $cover,
                'is_published' => true,
                'published_at' => $row->post_date,
                'author_id' => $author?->id,
            ]);
            $imported++;
        }

        $this->line("News posts: $imported imported, $skipped spam-filtered");
    }

    private function wpFeaturedImage(int $postId): ?string
    {
        $thumbId = $this->wp()->table('wp8a_postmeta')
            ->where('post_id', $postId)->where('meta_key', '_thumbnail_id')->value('meta_value');
        if (! $thumbId) {
            return null;
        }

        $file = $this->wp()->table('wp8a_postmeta')
            ->where('post_id', (int) $thumbId)->where('meta_key', '_wp_attached_file')->value('meta_value');
        if (! $file) {
            return null;
        }

        return $this->copyAsset('wp-content/uploads/'.$file, 'news');
    }

    /**
     * WordPress post_content → Markdown the escaping renderer can display.
     * Handles the classic-editor vocabulary; anything unrecognised is reduced
     * to its text. Good enough for an archive; new posts are written natively.
     */
    private static function htmlToMarkdown(string $html): string
    {
        // Gutenberg block comments and shortcodes carry no content.
        $html = preg_replace('/<!--\s*\/?wp:[^>]*-->/', '', $html) ?? $html;
        $html = preg_replace('/\[[^\]\n]{1,120}\]/', '', $html) ?? $html;
        $html = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $html) ?? $html;

        $html = preg_replace_callback('/<img[^>]*src="([^"]+)"[^>]*>/i', function ($m) {
            $src = $m[1];
            // Dead references to the old host's disk are dropped, not shipped.
            if (str_contains($src, 'localhost')) {
                return '';
            }

            return "\n";
        }, $html) ?? $html;

        $html = preg_replace('/<a[^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/is', '[$2]($1)', $html) ?? $html;
        $html = preg_replace('/<(strong|b)\b[^>]*>(.*?)<\/\1>/is', '**$2**', $html) ?? $html;
        $html = preg_replace('/<(em|i)\b[^>]*>(.*?)<\/\1>/is', '*$2*', $html) ?? $html;

        foreach ([1 => '#', 2 => '##', 3 => '###', 4 => '####', 5 => '#####', 6 => '######'] as $n => $hashes) {
            $html = preg_replace("/<h$n\b[^>]*>(.*?)<\/h$n>/is", "\n\n$hashes $1\n\n", $html) ?? $html;
        }

        $html = preg_replace('/<li\b[^>]*>(.*?)<\/li>/is', "\n- $1", $html) ?? $html;
        $html = preg_replace('/<\/(p|div|ul|ol|blockquote|figure|table|tr)>/i', "\n\n", $html) ?? $html;
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function importCmsPublications(): void
    {
        foreach ($this->legacy()->table('scholar_publications')->get() as $row) {
            $title = trim((string) $row->title);
            if ($title === '' || Publication::where('title', $title)->exists()) {
                continue; // already carried over from the richer WP records
            }

            Publication::updateOrCreate(['title' => $title], [
                'abstract' => $row->abstract ?: null,
                'type' => in_array($row->publication_type, array_keys(Publication::TYPES), true) ? $row->publication_type : 'journal',
                'journal_name' => $row->journal_name ?: null,
                'volume' => $row->volume ?: null,
                'issue' => $row->issue ?: null,
                'pages' => $row->pages ?: null,
                'publication_date' => $row->publication_date ?: null,
                'doi' => Str::limit(trim((string) $row->doi), 120, '') ?: null,
                'url' => $row->url ?: null,
                'citations' => (int) $row->citations,
                'status' => $row->status === 'draft' ? 'draft' : 'published',
                'is_featured' => $row->status === 'featured',
            ]);
        }

        $this->line('Publications after CMS merge: '.Publication::count());
    }
}
