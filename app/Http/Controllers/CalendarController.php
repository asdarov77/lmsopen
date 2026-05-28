<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        $events = Event::orderBy('start')->get();
        return response()->json($events);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
        ]);

        $event = Event::create([
            'title' => $validated['event_name'],
            'start' => $validated['start_date'],
            'end' => $validated['end_date'] ?? $validated['start_date'],
        ]);

        return response()->json($event, 201);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
        ]);

        $event->update([
            'title' => $validated['event_name'],
            'start' => $validated['start_date'],
            'end' => $validated['end_date'] ?? $validated['start_date'],
        ]);

        return response()->json($event);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json(null, 204);
    }
}
