<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\View\View;

class FacultyController extends Controller
{
    public function index(): View
    {
        return view('university.faculties.index', [
            'faculties' => Faculty::published()->withCount(['programmes' => fn ($q) => $q->published()])->get(),
        ]);
    }

    public function show(Faculty $faculty): View
    {
        abort_unless($faculty->is_published, 404);

        $faculty->load([
            'programmes' => fn ($q) => $q->published()->orderBy('level')->orderBy('name'),
            'staff' => fn ($q) => $q->where('is_published', true)->limit(12),
            'dean',
        ]);

        return view('university.faculties.show', [
            'faculty' => $faculty,
            'programmesByLevel' => $faculty->programmes->groupBy(fn ($p) => $p->levelLabel()),
        ]);
    }
}
