<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\AlmanacEntry;
use App\Models\Course;
use App\Models\Programme;
use App\Support\Odel;
use App\Support\University;
use Illuminate\View\View;

/**
 * The ODEL section.
 *
 * The content comes from App\Support\Odel, which is a transcription of the two
 * Council-approved policies with the clause and the in-place/committed status
 * on every claim. This controller's only real work is joining that to what the
 * database can actually show — the programmes, the e-learning courses, and the
 * ODEL dates already in the almanac.
 */
class OdelController extends Controller
{
    public function index(): View
    {
        return view('university.odel.index', [
            'modes' => Odel::modes(),
            'promises' => Odel::promises(),
            'courses' => Course::where('is_published', true)->count(),
            'dates' => $this->dates()->take(3),
            'programmes' => $this->odelProgrammes()->count(),
        ]);
    }

    public function modes(): View
    {
        return view('university.odel.modes', ['modes' => Odel::modes()]);
    }

    public function mode(string $mode): View
    {
        $all = Odel::modes();

        return view('university.odel.mode', [
            'key' => $mode,
            'mode' => $all[$mode],
            'others' => collect($all)->except($mode),
        ]);
    }

    /**
     * What the delivered system actually does.
     *
     * Separate from howItWorks(), which explains the policy model. This page
     * is the software: the screens a student works in, each carrying whether
     * it is in routine use, built but unused, or a policy commitment.
     */
    public function platform(): View
    {
        return view('university.odel.platform', [
            'platform' => Odel::platform(),
            'eportal' => University::links()['eportal'] ?? 'https://eportal.mru.ac.ug/',
        ]);
    }

    public function howItWorks(): View
    {
        return view('university.odel.how-it-works', [
            'links' => University::links(),
            'courses' => Course::where('is_published', true)->count(),
        ]);
    }

    public function whatYouGet(): View
    {
        return view('university.odel.what-you-get', ['entitlements' => Odel::entitlements()]);
    }

    public function credit(): View
    {
        return view('university.odel.credit', ['promises' => Odel::promises()]);
    }

    public function programmes(): View
    {
        return view('university.odel.programmes', [
            'programmes' => $this->odelProgrammes(),
            'total' => Programme::published()->count(),
        ]);
    }

    public function quality(): View
    {
        return view('university.odel.quality', ['criteria' => Odel::approvalCriteria()]);
    }

    public function governance(): View
    {
        return view('university.odel.governance', ['bodies' => Odel::bodies()]);
    }

    public function calendar(): View
    {
        return view('university.odel.calendar', ['dates' => $this->dates()]);
    }

    public function support(): View
    {
        return view('university.odel.support', [
            'contacts' => University::contacts(),
            'links' => University::links(),
        ]);
    }

    public function faqs(): View
    {
        return view('university.odel.faqs', ['modes' => Odel::modes()]);
    }

    public function apply(): View
    {
        return view('university.odel.apply', [
            'modes' => Odel::modes(),
            'applyUrl' => University::applyUrl(),
            'contacts' => University::contacts(),
        ]);
    }

    /**
     * Programmes the university has marked as available in an ODEL mode.
     *
     * `study_modes` is empty for every programme today, so this returns
     * nothing and the page says so. That is deliberate: which qualifications
     * are actually offered by distance is a fact only the Academic Registrar
     * holds, and inventing a list would be the single most damaging thing this
     * section could do to somebody about to pay fees.
     */
    private function odelProgrammes(): \Illuminate\Support\Collection
    {
        $wanted = ['distance', 'odel', 'online', 'part-time', 'part time', 'evening',
            'weekend', 'flexible', 'blended', 'intensive'];

        return Programme::published()->with('faculty')
            ->whereNotNull('study_modes')->where('study_modes', '!=', '')->where('study_modes', '!=', '[]')
            ->get()
            ->filter(function (Programme $p) use ($wanted) {
                $modes = strtolower(is_array($p->study_modes) ? implode(' ', $p->study_modes) : (string) $p->study_modes);

                foreach ($wanted as $w) {
                    if (str_contains($modes, $w)) {
                        return true;
                    }
                }

                return false;
            })
            ->values();
    }

    /** ODEL dates already in the almanac — real entries, not a second calendar. */
    private function dates(): \Illuminate\Support\Collection
    {
        return AlmanacEntry::dated()
            ->where(fn ($q) => $q->where('activity', 'like', '%ODEL%')
                ->orWhere('activity', 'like', '%distance%')
                ->orWhere('activity', 'like', '%weekend%')
                ->orWhere('activity', 'like', '%evening%'))
            ->orderBy('starts_on')->get();
    }
}
