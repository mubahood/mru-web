<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\Vacancy;
use Illuminate\View\View;

class VacancyController extends Controller
{
    public function index(): View
    {
        return view('university.vacancies.index', [
            'open' => Vacancy::open()->orderBy('deadline_on')->get(),
            'closed' => Vacancy::published()
                ->whereNotNull('deadline_on')
                ->where('deadline_on', '<', now()->toDateString())
                ->latest('deadline_on')->limit(10)->get(),
        ]);
    }

    public function show(Vacancy $vacancy): View
    {
        abort_unless($vacancy->is_published, 404);

        return view('university.vacancies.show', ['vacancy' => $vacancy]);
    }
}
