<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\AlmanacEntry;
use App\Models\ContactMessage;
use App\Models\Faculty;
use App\Models\GalleryPhoto;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Programme;
use App\Models\Publication;
use App\Models\Scholarship;
use App\Models\StaffMember;
use App\Models\UniversityEvent;
use App\Support\Spam\Captcha;
use App\Support\Spam\FormShield;
use App\Support\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The university's institutional pages: home, about, governance, student life. */
class PageController extends Controller
{
    public function home(): View
    {
        return view('university.home', [
            'identity' => University::identity(),
            'stats' => $this->liveStats(),
            'admissions' => University::get('admissions'),
            'faculties' => Faculty::published()->withCount('programmes')->get(),
            'news' => Post::published()->latest('published_at')->limit(3)->get(),
            'events' => UniversityEvent::published()->upcoming()->limit(3)->get(),
            'publications' => Publication::published()->where('is_featured', true)->with('authorRows.scholar')->limit(3)->get(),
            'partners' => Partner::showOnHome()->orderBy('sort_order')->get(),
            'scholarships' => Scholarship::where('is_published', true)->orderBy('sort_order')->get(),
            'leaders' => StaffMember::where('is_published', true)->where('staff_role', 'leadership')
                ->whereNotNull('photo')->orderBy('sort_order')->limit(4)->get(),
            'yearGlance' => $this->yearGlance(),
            'testimonials' => collect(json_decode((string) \App\Support\Settings::get('portfolio.testimonials', '[]'), true) ?: []),
        ]);
    }

    /**
     * The headline stat row, with its two countable figures taken from the
     * database rather than from the settings copy.
     *
     * Those two had drifted: the row claimed "46+ Academic Programmes" against
     * 44 published, and "5 Faculties & Graduate School" when there are four
     * faculties plus the Graduate School. A number a human retypes after every
     * curriculum change is a number that will be wrong again next term, so the
     * label stays editable and the figure is counted. Anything the database
     * cannot answer (campuses, the NCHE mark) passes through untouched.
     */
    private function liveStats(): array
    {
        $faculties = Faculty::published()->count();
        $gradSchools = Faculty::published()->where('name', 'like', '%Graduate School%')->count();

        return array_map(function (array $stat) use ($faculties, $gradSchools) {
            $label = strtolower($stat['label'] ?? '');

            if (str_contains($label, 'facult')) {
                $stat['value'] = (string) max($faculties - $gradSchools, 0);
            } elseif (str_contains($label, 'programme')) {
                $stat['value'] = Programme::published()->count().'+';
            }

            return $stat;
        }, University::get('stats'));
    }

    /**
     * The homepage's academic-year strip. Entries now carry real dates and an
     * editor-set `is_key_date` flag, so this asks the data rather than
     * guessing from activity wording as the first version had to: the four
     * key dates still ahead, falling back to the last four of the year once
     * the year is over, so the strip is never empty mid-August.
     */
    private function yearGlance(): \Illuminate\Support\Collection
    {
        $key = AlmanacEntry::where('is_key_date', true)->dated();

        $upcoming = (clone $key)->upcoming()->orderBy('starts_on')->limit(4)->get();

        return $upcoming->isNotEmpty()
            ? $upcoming
            : $key->orderByDesc('starts_on')->limit(4)->get()->sortBy('starts_on')->values();
    }

    public function about(): View
    {
        return view('university.about', [
            'identity' => University::identity(),
            'stats' => University::get('stats'),
            'leadership' => StaffMember::published()->where('staff_role', 'leadership')->get(),
        ]);
    }

    public function whoWeAre(): View
    {
        return view('university.who-we-are', ['identity' => University::identity()]);
    }

    public function governance(): View
    {
        return view('university.governance', [
            'leadership' => StaffMember::published()->where('staff_role', 'leadership')->get(),
            'committees' => StaffMember::published()->where('staff_role', 'committee')
                ->get()->groupBy('group_label'),
        ]);
    }

    public function council(): View
    {
        return view('university.council', [
            'members' => StaffMember::published()->where('group_label', 'University Council')
                ->get()->groupBy('department'),
        ]);
    }

    public function staffDirectory(Request $request): View
    {
        $faculties = Faculty::published()->get();
        $facultyId = $request->integer('faculty') ?: null;
        $q = trim((string) $request->query('q'));

        $staff = StaffMember::published()
            ->whereIn('staff_role', ['leadership', 'dean', 'lecturer', 'administrative'])
            ->when($facultyId, fn ($query) => $query->where('faculty_id', $facultyId))
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%$q%")
                ->orWhere('title', 'like', "%$q%")
                ->orWhere('department', 'like', "%$q%")))
            ->with('faculty')
            ->paginate(24)->withQueryString();

        return view('university.staff-directory', compact('staff', 'faculties', 'facultyId', 'q'));
    }

    public function campusLife(): View
    {
        return view('university.campus-life', [
            'contacts' => University::contacts(),
            'sports' => University::get('sports'),
            'gallery' => GalleryPhoto::query()->where('is_published', true)->limit(8)->get(),
            'events' => UniversityEvent::published()->upcoming()->limit(3)->get(),
        ]);
    }

    public function accommodation(): View
    {
        return view('university.accommodation', [
            'halls' => University::get('accommodation'),
            'contacts' => University::contacts(),
        ]);
    }

    public function sports(): View
    {
        return view('university.sports', ['sports' => University::get('sports')]);
    }

    public function guild(): View
    {
        return view('university.guild', [
            'cabinet' => StaffMember::published()->where('group_label', "Students' Guild")->get(),
        ]);
    }

    public function alumni(): View
    {
        return view('university.alumni', ['contacts' => University::contacts()]);
    }

    public function library(): View
    {
        return view('university.library', [
            'links' => University::links(),
            'contacts' => University::contacts(),
        ]);
    }

    public function almanac(): View
    {
        $all = AlmanacEntry::orderBy('sort_order')->get();

        // Months in chronological order; the undated "ongoing" work is pulled
        // out so it can be presented as what it is rather than sorted into a
        // month it does not belong to.
        $dated = $all->filter(fn ($e) => $e->starts_on !== null)
            ->sortBy([['starts_on', 'asc'], ['sort_order', 'asc']]);

        return view('university.almanac', [
            'year' => $all->first()?->academic_year,
            'months' => $dated->groupBy(fn ($e) => $e->monthKey()),
            'ongoing' => $all->filter(fn ($e) => $e->starts_on === null)->values(),
            'keyDates' => $dated->where('is_key_date', true)->values(),
            // The next milestone that has yet to *begin*. Deliberately not "the
            // first entry still running", which today would surface a sports
            // fixture that opened in August — true, but not what a reader
            // means by "next".
            'next' => $dated->where('is_key_date', true)->first(fn ($e) => $e->starts_on->gte(today())),
            'categories' => $dated->pluck('category')->filter()->unique()->values(),
            'total' => $all->count(),
        ]);
    }

    public function downloads(): View
    {
        $documents = collect(University::get('documents'))->groupBy('group');

        return view('university.downloads', ['groups' => $documents]);
    }

    public function contact(): View
    {
        return view('university.contact', ['contacts' => University::contacts(), 'social' => University::get('social')]);
    }

    public function sendMessage(Request $request): RedirectResponse
    {
        // Answered as success — a bot that is told it failed simply retries.
        if (FormShield::looksAutomated($request->all(), 'contact')) {
            return back()->with('success', 'Thank you — your message has been received.');
        }

        FormShield::assertHumanTiming($request->all());

        $data = $request->validate([
            'name' => 'required|string|min:2|max:120',
            'email' => 'required|email:rfc|max:191',
            'subject' => 'required|string|max:191',
            'message' => 'required|string|max:5000',
        ] + Captcha::rules(), Captcha::messages());

        ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]);

        return back()->with('success', 'Thank you — your message has been received. We reply within one working day.');
    }
}
