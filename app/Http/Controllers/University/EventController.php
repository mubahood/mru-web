<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\UniversityEvent;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        return view('university.events.index', [
            'upcoming' => UniversityEvent::published()->upcoming()->get(),
            'past' => UniversityEvent::published()->past()->limit(12)->get(),
        ]);
    }

    public function show(UniversityEvent $event): View
    {
        abort_unless($event->is_published, 404);

        return view('university.events.show', ['event' => $event]);
    }
}
