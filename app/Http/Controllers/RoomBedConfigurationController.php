<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRoomBedsRequest;
use App\Models\Building;
use App\Models\Room;
use App\Services\RoomBedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomBedConfigurationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Room::class);

        $filters = [
            'search' => (string) $request->string('search'),
            'building_id' => (string) $request->string('building_id'),
        ];

        $rooms = Room::query()
            ->with(['building:id,name', 'beds'])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            })
            ->when($filters['building_id'] !== '', function ($query) use ($filters) {
                $query->where('building_id', (int) $filters['building_id']);
            })
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('room_beds.index', [
            'rooms' => $rooms,
            'buildings' => Building::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function update(UpdateRoomBedsRequest $request, Room $room, RoomBedService $roomBedService): RedirectResponse
    {
        $this->authorize('updateBeds', $room);

        $roomBedService->sync($room, $request->validated());

        $filters = array_filter([
            'search' => $request->string('search')->toString(),
            'building_id' => $request->string('building_id')->toString(),
        ], static fn(string $value): bool => $value !== '');

        return redirect()->route('room-bed-configurations.index', $filters)
            ->with('status', 'room-beds-updated');
    }
}
