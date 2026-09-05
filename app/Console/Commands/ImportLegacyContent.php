<?php

namespace App\Console\Commands;

use App\Models\UniversityEvent;
use App\Models\Vacancy;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Brings the genuine content out of the old WordPress site.
 *
 * What this deliberately does NOT do is write copy. The old site's events
 * carry no description worth the name — the body is usually the title again,
 * or a bare image tag — so events are imported with their real title, date and
 * venue and nothing invented to fill the gap. The vacancies DO carry real
 * text, written by the university's HR office, so that text is extracted and
 * cleaned rather than rewritten.
 *
 * The wider picture, established before writing any of this: of 238 published
 * posts in the WordPress database, 70 were already imported and every one of
 * the remaining 168 is casino spam, lorem-ipsum or a test row — the old site
 * had been compromised. Nothing in wp_posts is left to bring over, and this
 * command does not touch it.
 */
class ImportLegacyContent extends Command
{
    protected $signature = 'mru:import-legacy
                            {--events : Import events}
                            {--vacancies : Import vacancies}
                            {--tidy : Clean wording on content already in the database}
                            {--dry-run : Report what would change without writing}';

    protected $description = 'Import genuine events and vacancies from the legacy WordPress database';

    /** Venue IDs resolved from wp8a_posts (tribe_venue). */
    private array $venues = [];

    public function handle(): int
    {
        try {
            DB::connection('legacy_wp')->getPdo();
        } catch (\Throwable $e) {
            $this->error('Legacy database not reachable. Load the WordPress dump into `mru_wp_scratch` first.');

            return self::FAILURE;
        }

        $all = ! $this->option('events') && ! $this->option('vacancies') && ! $this->option('tidy');

        if ($this->option('tidy')) {
            $this->tidyExisting();
        }

        if ($all || $this->option('events')) {
            $this->importEvents();
        }
        if ($all || $this->option('vacancies')) {
            $this->importVacancies();
        }

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->warn('Dry run — nothing was written.');
        }

        return self::SUCCESS;
    }

    // ------------------------------------------------------------- events

    private function importEvents(): void
    {
        $this->info('Events');

        $this->venues = DB::connection('legacy_wp')->table('wp8a_posts')
            ->where('post_type', 'tribe_venue')->pluck('post_title', 'ID')
            ->map(fn ($v) => $this->cleanTitle($v))->all();

        // Only `tribe_events`. The `events` post type holds the WordPress
        // theme's demo content — "Falar's Annual Fall Festival", "Luva's
        // Athletic Showdown" — which is not this university's.
        $rows = DB::connection('legacy_wp')->table('wp8a_posts as p')
            ->where('p.post_type', 'tribe_events')
            ->where('p.post_status', 'publish')
            ->select('p.ID', 'p.post_title', 'p.post_name', 'p.post_content')
            ->selectRaw("(select meta_value from wp8a_postmeta where post_id=p.ID and meta_key='_EventStartDate' limit 1) as starts")
            ->selectRaw("(select meta_value from wp8a_postmeta where post_id=p.ID and meta_key='_EventEndDate' limit 1) as ends")
            ->selectRaw("(select meta_value from wp8a_postmeta where post_id=p.ID and meta_key='_EventVenueID' limit 1) as venue_id")
            ->orderByDesc('p.post_date')->get();

        $made = $updated = $skipped = 0;

        foreach ($rows as $r) {
            if (blank($r->starts)) {
                $skipped++;
                continue;
            }

            $title = $this->cleanTitle($r->post_title);
            $venue = $this->venues[$r->venue_id] ?? null;
            $slug = $r->post_name ?: Str::slug($title);

            $payload = [
                'title' => $title,
                'starts_at' => Carbon::parse($r->starts),
                'ends_at' => filled($r->ends) ? Carbon::parse($r->ends) : null,
                'venue' => $venue,
                'campus' => $this->campusFrom($venue),
                'category' => $this->categoryFor($title),
                'is_published' => true,
            ];

            $existing = UniversityEvent::where('slug', $slug)->first();
            $this->line(sprintf('  %-9s %s  %s%s',
                $existing ? 'update' : 'create',
                Carbon::parse($r->starts)->format('Y-m-d'),
                $title,
                $venue ? "  ({$venue})" : ''));

            if (! $this->option('dry-run')) {
                UniversityEvent::updateOrCreate(['slug' => $slug], $payload);
            }
            $existing ? $updated++ : $made++;
        }

        $this->comment("  {$made} new, {$updated} updated, {$skipped} skipped (no date)");
        $this->newLine();
    }

    // ---------------------------------------------------------- vacancies

    private function importVacancies(): void
    {
        $this->info('Vacancies');

        $rows = DB::connection('legacy_wp')->table('wp8a_posts')
            ->where('post_type', 'career')->where('post_status', 'publish')
            ->orderByDesc('post_date')->get();

        $made = $updated = 0;

        foreach ($rows as $r) {
            $title = $this->cleanTitle($r->post_title);
            $slug = $r->post_name ?: Str::slug($title);
            $body = $this->jobBody($r->post_content);

            $payload = [
                'title' => $title,
                'summary' => Str::limit($this->firstSentences($body), 240),
                'requirements' => $body,
                'deadline_on' => $this->deadlineFrom($body),
                'type' => $this->jobType($r->post_content),
                'location' => 'Muteesa I Royal University',
                // Everything here predates the current recruitment round; it is
                // brought over as a record, not republished as open.
                'is_published' => false,
            ];

            $existing = Vacancy::where('slug', $slug)->first();
            $this->line(sprintf('  %-9s %s%s', $existing ? 'update' : 'create', $title,
                $payload['deadline_on'] ? '  (closed '.$payload['deadline_on']->format('j M Y').')' : ''));

            if (! $this->option('dry-run')) {
                Vacancy::updateOrCreate(['slug' => $slug], $payload);
            }
            $existing ? $updated++ : $made++;
        }

        $this->comment("  {$made} new, {$updated} updated — imported unpublished for HR review");
        $this->newLine();
    }

    /**
     * Wording hygiene on content already imported.
     *
     * Titles arrived from WordPress carrying raw entities (&#8217;, &#8211;,
     * &#038;) and the old site's habit of shouting. This decodes and
     * sentence-cases them. It rewrites presentation only — no sentence is
     * reworded, because the copy is the university's, not mine to author.
     */
    private function tidyExisting(): void
    {
        $this->info('Tidying existing content');
        $changed = 0;

        foreach (\App\Models\Post::all() as $post) {
            $title = $this->cleanTitle($post->title);
            $excerpt = $this->text($post->excerpt);

            if ($title !== $post->title || $excerpt !== (string) $post->excerpt) {
                $this->line("  post  {$post->title}");
                $this->line("     -> {$title}");
                if (! $this->option('dry-run')) {
                    $post->update(['title' => $title, 'excerpt' => $excerpt ?: $post->excerpt]);
                }
                $changed++;
            }
        }

        foreach (UniversityEvent::all() as $event) {
            $title = $this->cleanTitle($event->title);
            if ($title !== $event->title) {
                $this->line("  event {$event->title}");
                $this->line("     -> {$title}");
                if (! $this->option('dry-run')) {
                    $event->update(['title' => $title]);
                }
                $changed++;
            }
        }

        $this->comment("  {$changed} records tidied");
        $this->newLine();
    }

    // ------------------------------------------------------------ helpers

    /** WordPress entities and stray whitespace out; nothing else changed. */
    private function text(?string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    /**
     * The old site shouts: "13TH GRADUATION CEREMONY", "BUGANDA SPORTS GALLA".
     * Sentence-cased, with the handful of tokens that must keep their own
     * casing preserved, and one real typo fixed.
     */
    /** Acronyms that are correctly uppercase and must never be case-folded. */
    private const ACRONYMS = [
        'MRU', 'MUBS', 'MUST', 'YMCA', 'NCHE', 'ICT', 'IT', 'HR', 'QA', 'UACE', 'ODEL',
        'CBET', 'MEMA', 'FBM', 'FOE', 'FSSAH', 'FSTEAD', 'EFRIS', 'SPSS', 'WASH', 'GRC',
        'DVC', 'AR', 'VC', 'PHD', 'CBS', 'PEWOSA', 'NGO', 'USA', 'UK', 'EU', 'AUUS', 'UFL',
    ];

    /** Words that stay lowercase inside a title. */
    private const MINOR = ['of', 'the', 'and', 'in', 'at', 'to', 'for', 'a', 'an', 'on', 'with', 'vs'];

    /**
     * The old site shouts — "13TH GRADUATION CEREMONY", "BUGANDA SPORTS GALLA".
     *
     * Folded per word rather than per title, because a whole-title test cuts
     * both ways: it leaves "SWEARING-IN OF THE 16th GUILD COUNCIL" alone (one
     * stray lowercase pair) while a looser ratio test would happily turn
     * "MRU Vs MUBS" into "Mru vs Mubs". A word is only folded when it is
     * longer than three letters, entirely uppercase, and not a known acronym.
     */
    private function cleanTitle(string $raw): string
    {
        $title = $this->text($raw);

        /*
         * Fold case only when the title as a whole is shouting.
         *
         * A first version folded word by word and was wrong in a way only real
         * data shows: it turned EACOP into Eacop, NEMRA into Nemra, IUEA into
         * Iuea and "Eid : A Message" into "a Message". A single uppercase word
         * inside an otherwise normal title is almost always an acronym or a
         * deliberate proper noun — the university's choice, not a defect. So a
         * title is only touched when most of its real words are shouting;
         * otherwise it keeps its casing and just loses its HTML entities.
         */
        $realWords = array_values(array_filter(
            preg_split('/\s+/u', preg_replace('/[^A-Za-z\s]/', ' ', $title)),
            fn ($w) => mb_strlen($w) > 3
        ));
        $shouted = count(array_filter($realWords, fn ($w) => $w === mb_strtoupper($w)));
        $isShouting = $realWords !== [] && ($shouted / count($realWords)) >= 0.6;

        if (! $isShouting) {
            return preg_replace('/\s+/', ' ', trim($title));
        }

        $words = preg_split('/(\s+)/u', $title, -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = [];

        foreach ($words as $i => $word) {
            if (trim($word) === '') {
                $out[] = $word;
                continue;
            }

            // Ordinals: "13TH" has only two letters, so the length rule below
            // would never reach it.
            $word = preg_replace_callback('/\b(\d+)(ST|ND|RD|TH)\b/i',
                fn ($m) => $m[1].mb_strtolower($m[2]), $word);

            $bare = preg_replace('/[^A-Za-z]/', '', $word);
            $isAcronym = $bare !== '' && in_array(mb_strtoupper($bare), self::ACRONYMS, true);

            if (! $isAcronym && mb_strlen($bare) > 3 && $bare === mb_strtoupper($bare)) {
                $word = Str::title(mb_strtolower($word));   // SHOUTING -> Shouting
            } elseif ($isAcronym) {
                $word = str_ireplace($bare, mb_strtoupper($bare), $word);
            }

            // Minor words stay lowercase unless they open the title.
            $plain = mb_strtolower(preg_replace('/[^A-Za-z]/', '', $word));
            if ($i > 0 && in_array($plain, self::MINOR, true)) {
                $word = mb_strtolower($word);
            }

            $out[] = $word;
        }

        $title = implode('', $out);
        $title = strtr($title, ['13Th' => '13th', '16Th' => '16th', 'Swearing-In' => 'Swearing-in']);
        $title = str_ireplace('Sports Galla', 'Sports Gala', $title);
        $title = preg_replace_callback('/^\p{Ll}/u', fn ($m) => mb_strtoupper($m[0]), $title);

        return preg_replace('/\s+/', ' ', trim($title));
    }

    private function campusFrom(?string $venue): ?string
    {
        if (! $venue) {
            return null;
        }
        $v = mb_strtolower($venue);

        return match (true) {
            str_contains($v, 'kirumba') && str_contains($v, 'kakeeka') => 'Both campuses',
            str_contains($v, 'kirumba') => 'Kirumba, Masaka',
            str_contains($v, 'kakeeka') || str_contains($v, 'mengo') => 'Kakeeka, Kampala',
            default => null,
        };
    }

    private function categoryFor(string $title): string
    {
        $t = mb_strtolower($title);

        return match (true) {
            str_contains($t, ' vs ') || str_contains($t, 'sports') || str_contains($t, 'gala') => 'sports',
            str_contains($t, 'graduation') || str_contains($t, 'charter') || str_contains($t, 'swearing') => 'ceremony',
            str_contains($t, 'election') || str_contains($t, 'ball') || str_contains($t, 'guild') => 'student',
            default => 'university',
        };
    }

    /**
     * The job text sits inside a full page render — navigation, partners and
     * footer included. The real body runs from "Job Description" to the
     * "Apply Now" button; everything outside that is chrome.
     */
    private function jobBody(string $html): string
    {
        $t = preg_replace('#<(script|style)\b.*?</\1>#is', ' ', $html);
        $t = preg_replace('/<!--.*?-->/s', ' ', $t);
        $t = preg_replace('#</(p|li|div|h[1-6]|tr)>#i', "\n", $t);
        $t = preg_replace('#<li[^>]*>#i', '• ', $t);
        $t = strip_tags($t);
        $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (preg_match('/Job Description(.*?)Apply Now/si', $t, $m)) {
            $t = $m[1];
        }

        $lines = [];
        foreach (preg_split('/\R/', $t) as $line) {
            $line = trim(preg_replace('/[ \t]+/', ' ', $line));
            if ($line !== '' && $line !== '•' && mb_strlen($line) > 2) {
                $lines[] = $line;
            }
        }

        return trim(implode("\n", $lines));
    }

    private function firstSentences(string $body): string
    {
        $first = trim(explode("\n", $body)[0] ?? '');

        return $first !== '' ? $first : Str::limit($body, 200);
    }

    private function deadlineFrom(string $body): ?Carbon
    {
        if (preg_match('/close[sd]?\s+on\s+\w*\s*(\d{1,2})(?:st|nd|rd|th)?\s+([A-Za-z]+)\s+(\d{4})/i', $body, $m)) {
            try {
                return Carbon::parse("{$m[1]} {$m[2]} {$m[3]}");
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function jobType(string $html): string
    {
        $t = mb_strtolower(strip_tags($html));

        return str_contains($t, 'part-time') ? 'Part-Time' : 'Full-Time';
    }
}
