<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The programme finder — the site's single most important conversion tool. */
class ProgrammeController extends Controller
{
    public function index(Request $request): View
    {
        $faculties = Faculty::published()->get();
        $level = (string) $request->query('level');
        $facultyId = $request->integer('faculty') ?: null;
        $q = trim((string) $request->query('q'));

        $programmes = Programme::published()
            ->with('faculty')
            ->when(array_key_exists($level, Programme::LEVELS), fn ($query) => $query->where('level', $level))
            ->when($facultyId, fn ($query) => $query->where('faculty_id', $facultyId))
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%$q%")
                ->orWhere('award_code', 'like', "%$q%")))
            ->orderBy('name')
            ->get();

        return view('university.programmes.index', [
            'programmes' => $programmes,
            'faculties' => $faculties,
            'levels' => Programme::LEVELS,
            'level' => $level,
            'facultyId' => $facultyId,
            'q' => $q,
        ]);
    }

    public function show(Programme $programme): View
    {
        abort_unless($programme->is_published, 404);

        $programme->load('faculty');

        return view('university.programmes.show', [
            'programme' => $programme,
            'related' => Programme::published()
                ->where('faculty_id', $programme->faculty_id)
                ->whereKeyNot($programme->id)
                ->limit(4)->get(),
        ]);
    }
}
