<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Tampilkan detail event tertentu.
     */
    public function show(string $slug): View
    {
        $event = Event::with(['categories', 'packages', 'addOns.variants'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('events.show', compact('event'));
    }
}
