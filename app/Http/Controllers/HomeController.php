<?php

namespace App\Http\Controllers;

use App\Models\AddOn;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Tampilkan landing page utama VIRA.
     */
    public function index(Request $request): View
    {
        $activityFilter = $request->query('type'); // RUN, RIDE, WALK

        $eventsQuery = Event::with(['categories', 'packages'])
            ->where('is_active', true);

        if ($activityFilter && in_array(strtoupper($activityFilter), ['RUN', 'RIDE', 'WALK'], true)) {
            $eventsQuery->where('activity_type', strtoupper($activityFilter));
        }

        $events = $eventsQuery->orderBy('race_start', 'asc')->get();

        // Ambil beberapa add-on unggulan untuk etalase preview
        $featuredAddons = AddOn::where('is_active', true)
            ->where('stock', '>', 0)
            ->limit(4)
            ->get();

        return view('home', compact('events', 'featuredAddons', 'activityFilter'));
    }
}
