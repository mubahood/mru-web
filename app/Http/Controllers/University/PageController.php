<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\AlmanacEntry;
use App\Models\ContactMessage;
use App\Models\Faculty;
use App\Models\GalleryPhoto;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Publication;
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
            'stats' => University::get('stats'),
            'admissions' => University::get('admissions'),
            'faculties' => Faculty::published()->withCount('programmes')->get(),
            'news' => Post::published()->latest('published_at')->limit(3)->get(),
            'events' => UniversityEvent::published()->upcoming()->limit(3)->get(),
            'publications' => Publication::published()->where('is_featured', true)->with('authorRows.scholar')->limit(3)->get(),
            'partners' => Partner::showOnHome()->orderBy('sort_order')->get(),
            'testimonials' => collect(json_decode((string) \App\Support\Settings::get('portfolio.testimonials', '[]'), true) ?: []),
        ]);
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
        $entries = AlmanacEntry::orderBy('sort_order')->get()
            ->groupBy('academic_year')
            ->map(fn ($year) => $year->groupBy('semester'));

        return view('university.almanac', ['years' => $entries]);
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
