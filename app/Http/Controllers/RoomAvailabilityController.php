<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomAvailabilityFilterRequest;
use App\Models\Building;
use App\Models\Room;
use Illuminate\View\View;

class RoomAvailabilityController extends Controller
{
    public function index(RoomAvailabilityFilterRequest $request): View
    {
        $this->authorize('viewAny', Room::class);

        $filters = $request->filters();

        $rooms = Room::query()
            ->with('building:id,name')
            ->when($filters['building_id'] !== '', function ($query) use ($filters) {
                $query->where('building_id', (int) $filters['building_id']);
            })
            ->where('is_active', true)
            ->orderBy('building_id')
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        $statusStyles = [
            'available' => 'bg-emerald-100 text-emerald-800',
            'occupied' => 'bg-rose-100 text-rose-800',
            'reserved' => 'bg-amber-100 text-amber-800',
            'cleaning' => 'bg-sky-100 text-sky-800',
            'maintenance' => 'bg-orange-100 text-orange-800',
            'out_of_service' => 'bg-slate-200 text-slate-800',
        ];

        return view('rooms.availability-calendar', [
            'rooms' => $rooms,
            'buildings' => Building::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
            'statusStyles' => $statusStyles,
        ]);
    }
}
