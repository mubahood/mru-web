<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Faculty;
use App\Models\Post;
use App\Models\Programme;
use App\Models\Publication;
use App\Models\Scholar;
use App\Models\UniversityEvent;
use App\Models\Vacancy;
use Illuminate\Http\Response;

/**
 * /sitemap.xml generated from real content (never a static, go-stale file) and
 * /robots.txt generated from the app's actual URL. The URL set is the
 * university IA: every SiteNav destination plus each faculty, programme, news
 * article, event, vacancy, publication and scholar profile.
 */
class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $static = [
            // Trailing slash avoids an extra redirect hop some server configs
            // add for a bare subdirectory path.
            route('home').'/',
            route('about'), route('who-we-are'), route('governance'), route('council'),
            route('staff.directory'), route('contact'),
            route('admissions.index'), route('admissions.requirements'), route('admissions.apply'),
            route('admissions.fees'), route('admissions.scholarships'), route('admissions.intakes'),
            route('admissions.international'), route('admissions.faqs'),
            route('faculties.index'), route('programmes.index'), route('almanac'),
            route('courses.index'), route('library'), route('downloads'),
            route('scholar.home'), route('scholar.publications'), route('scholar.directory'),
            route('campus-life'), route('accommodation'), route('sports'), route('guild'), route('alumni'),
            route('insights.index'), route('events.index'), route('gallery.index'), route('vacancies.index'),
            route('certificates.lookup'),
            route('privacy'), route('terms'),
        ];

        $urls = array_map(fn (string $loc) => ['loc' => $loc, 'lastmod' => null], $static);

        foreach (Faculty::published()->get(['id', 'slug', 'updated_at']) as $faculty) {
            $urls[] = ['loc' => route('faculties.show', $faculty), 'lastmod' => $faculty->updated_at];
        }

        foreach (Programme::published()->get(['id', 'slug', 'updated_at']) as $programme) {
            $urls[] = ['loc' => route('programmes.show', $programme), 'lastmod' => $programme->updated_at];
        }

        foreach (Post::published()->get(['slug', 'updated_at']) as $post) {
            $urls[] = ['loc' => route('insights.show', $post), 'lastmod' => $post->updated_at];
        }

        foreach (UniversityEvent::published()->get(['id', 'slug', 'updated_at']) as $event) {
            $urls[] = ['loc' => route('events.show', $event), 'lastmod' => $event->updated_at];
        }

        foreach (Vacancy::published()->get(['id', 'slug', 'updated_at']) as $vacancy) {
            $urls[] = ['loc' => route('vacancies.show', $vacancy), 'lastmod' => $vacancy->updated_at];
        }

        foreach (Publication::published()->get(['id', 'slug', 'updated_at']) as $publication) {
            $urls[] = ['loc' => route('scholar.publication', $publication), 'lastmod' => $publication->updated_at];
        }

        foreach (Scholar::published()->get(['id', 'slug', 'updated_at']) as $scholar) {
            $urls[] = ['loc' => route('scholar.profile', $scholar), 'lastmod' => $scholar->updated_at];
        }

        foreach (Course::where('is_published', true)->get(['slug', 'updated_at']) as $course) {
            $urls[] = ['loc' => route('courses.show', $course), 'lastmod' => $course->updated_at];
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /learn',
            'Disallow: /portal',
            'Disallow: /dashboard',
            'Disallow: /e-learning/*/checkout',
            'Disallow: /courses/*/checkout',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }
}
