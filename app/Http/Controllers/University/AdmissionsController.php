<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Programme;
use App\Models\Scholarship;
use App\Support\University;
use Illuminate\View\View;

/** The admissions funnel: overview, requirements, fees, intakes, FAQs. */
class AdmissionsController extends Controller
{
    public function index(): View
    {
        return view('university.admissions.index', [
            'admissions' => University::get('admissions'),
            'contacts' => University::contacts(),
            'programmeCount' => Programme::published()->count(),
        ]);
    }

    public function requirements(): View
    {
        return view('university.admissions.requirements', [
            'admissions' => University::get('admissions'),
        ]);
    }

    public function howToApply(): View
    {
        return view('university.admissions.how-to-apply', [
            'admissions' => University::get('admissions'),
            'contacts' => University::contacts(),
        ]);
    }

    public function fees(): View
    {
        // The banded figures the university actually publishes, shown per
        // faculty with each faculty's programmes beneath.
        return view('university.admissions.fees', [
            'admissions' => University::get('admissions'),
            'faculties' => Faculty::published()->with(['programmes' => fn ($q) => $q->published()])->get(),
        ]);
    }

    public function scholarships(): View
    {
        return view('university.admissions.scholarships', [
            'scholarships' => Scholarship::published()->get(),
            'contacts' => University::contacts(),
        ]);
    }

    public function intakes(): View
    {
        return view('university.admissions.intakes', [
            'admissions' => University::get('admissions'),
        ]);
    }

    public function international(): View
    {
        return view('university.admissions.international', [
            'admissions' => University::get('admissions'),
            'contacts' => University::contacts(),
        ]);
    }

    public function faqs(): View
    {
        return view('university.admissions.faqs', [
            'admissions' => University::get('admissions'),
            'contacts' => University::contacts(),
        ]);
    }
}
