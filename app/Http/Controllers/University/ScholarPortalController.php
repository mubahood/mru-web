<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Models\ResearchArea;
use App\Models\Scholar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * MRU Scholar — the public face of the university's research repository.
 */
class ScholarPortalController extends Controller
{
    public function home(): View
    {
        return view('university.scholar.home', [
            'featured' => Publication::published()->where('is_featured', true)
                ->with('authorRows.scholar')->limit(4)->get(),
            'latest' => Publication::published()->latest('publication_date')
                ->with('authorRows.scholar')->limit(6)->get(),
            'areas' => ResearchArea::withCount(['publications' => fn ($q) => $q->published()])
                ->orderByDesc('publications_count')->get(),
            'stats' => [
                'publications' => Publication::published()->count(),
                'scholars' => Scholar::published()->count(),
                'areas' => ResearchArea::count(),
                'downloads' => (int) Publication::published()->sum('downloads'),
            ],
        ]);
    }

    public function publications(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $type = (string) $request->query('type');
        $areaId = $request->integer('area') ?: null;
        $year = $request->integer('year') ?: null;

        $publications = Publication::published()
            ->with(['authorRows.scholar', 'researchAreas'])
            ->when(array_key_exists($type, Publication::TYPES), fn ($query) => $query->where('type', $type))
            ->when($areaId, fn ($query) => $query->whereHas('researchAreas', fn ($w) => $w->whereKey($areaId)))
            ->when($year, fn ($query) => $query->where('year', $year))
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('title', 'like', "%$q%")
                ->orWhere('abstract', 'like', "%$q%")
                ->orWhere('keywords', 'like', "%$q%")
                ->orWhere('journal_name', 'like', "%$q%")))
            ->orderByDesc('publication_date')->orderByDesc('id')
            ->paginate(12)->withQueryString();

        return view('university.scholar.publications', [
            'publications' => $publications,
            'types' => Publication::TYPES,
            'areas' => ResearchArea::orderBy('name')->get(),
            'years' => Publication::published()->whereNotNull('year')
                ->distinct()->orderByDesc('year')->pluck('year'),
            'filters' => ['q' => $q, 'type' => $type, 'area' => $areaId, 'year' => $year],
        ]);
    }

    public function publication(Publication $publication): View
    {
        abort_unless($publication->status === 'published', 404);

        $publication->load(['authorRows.scholar', 'researchAreas']);
        $publication->increment('views');

        return view('university.scholar.publication', ['publication' => $publication]);
    }

    public function download(Publication $publication): StreamedResponse
    {
        abort_unless($publication->status === 'published', 404);
        abort_unless($publication->pdf_path && Storage::disk('public')->exists($publication->pdf_path), 404);

        $publication->increment('downloads');

        return Storage::disk('public')->download(
            $publication->pdf_path,
            \Illuminate\Support\Str::slug(\Illuminate\Support\Str::limit($publication->title, 60, '')).'.pdf'
        );
    }

    public function scholarsDirectory(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        return view('university.scholar.directory', [
            'scholars' => Scholar::published()
                ->withCount(['publications' => fn ($query) => $query->published()])
                ->with('faculty')
                ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                    ->where('name', 'like', "%$q%")
                    ->orWhere('department', 'like', "%$q%")
                    ->orWhere('research_interests', 'like', "%$q%")))
                ->orderByDesc('publications_count')
                ->paginate(24)->withQueryString(),
            'q' => $q,
        ]);
    }

    public function profile(Scholar $scholar): View
    {
        abort_unless($scholar->is_published, 404);

        $scholar->load(['faculty', 'publications' => fn ($q) => $q->published()]);

        return view('university.scholar.profile', ['scholar' => $scholar]);
    }
}
