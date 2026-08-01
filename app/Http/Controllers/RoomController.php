<?php

namespace App\Http\Controllers;

use App\Actions\ExportRoomsCsvAction;
use App\Actions\ImportRoomsCsvAction;
use App\Http\Requests\ImportRoomsRequest;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Building;
use App\Models\Room;
use App\Services\InAppNotificationService;
use App\Services\RoomBedService;
use App\Services\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Room::class);

        $filters = [
            'search' => (string) $request->string('search'),
            'building_id' => (string) $request->string('building_id'),
            'status' => (string) $request->string('status'),
            'active' => (string) $request->string('active'),
            'lifecycle' => (string) $request->string('lifecycle', 'active'),
        ];

        $countsBaseQuery = Room::query()
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $query->where(function ($subQuery) use ($filters) {
                    $subQuery->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('code', 'like', '%' . $filters['search'] . '%');
                });
            })
            ->when($filters['building_id'] !== '', function ($query) use ($filters) {
                $query->where('building_id', (int) $filters['building_id']);
            })
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when($filters['active'] !== '', function ($query) use ($filters) {
                $query->where('is_active', $filters['active'] === '1');
            });

        $lifecycleCounts = [
            'active' => (clone $countsBaseQuery)->count(),
            'archived' => (clone $countsBaseQuery)->onlyTrashed()->count(),
            'all' => (clone $countsBaseQuery)->withTrashed()->count(),
        ];

        $rooms = Room::query()
            ->with('building:id,name')
            ->when($filters['lifecycle'] === 'archived', function ($query) {
                $query->onlyTrashed();
            })
            ->when($filters['lifecycle'] === 'all', function ($query) {
                $query->withTrashed();
            })
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            })
            ->when($filters['building_id'] !== '', function ($query) use ($filters) {
                $query->where('building_id', (int) $filters['building_id']);
            })
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when($filters['active'] !== '', function ($query) use ($filters) {
                $query->where('is_active', $filters['active'] === '1');
            })
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('rooms.index', [
            'rooms' => $rooms,
            'buildings' => Building::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => Room::STATUSES,
            'filters' => $filters,
            'lifecycleCounts' => $lifecycleCounts,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Room::class);

        return view('rooms.create', [
            'buildings' => Building::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => Room::STATUSES,
        ]);
    }

    public function store(StoreRoomRequest $request, RoomService $roomService): RedirectResponse
    {
        $this->authorize('create', Room::class);

        $roomService->create($request->validated());

        return redirect()->route('rooms.index')->with('status', 'room-created');
    }

    public function edit(Room $room): View
    {
        $this->authorize('update', $room);

        return view('rooms.edit', [
            'room' => $room,
            'buildings' => Building::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => Room::STATUSES,
        ]);
    }

    public function update(UpdateRoomRequest $request, Room $room, RoomService $roomService, InAppNotificationService $notificationService): RedirectResponse
    {
        $this->authorize('update', $room);

        $oldStatus = (string) $room->status;

        $roomService->update($room, $request->validated());
        $room->refresh();

        $notificationService->notifyRoomStatusChangedToMaintenanceOrOutOfService($room, $oldStatus);

        return redirect()->route('rooms.index')->with('status', 'room-updated');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);

        $room->delete();

        return redirect()->route('rooms.index')->with('status', 'room-deleted');
    }

    public function restore(int $room): RedirectResponse
    {
        $target = Room::withTrashed()->findOrFail($room);

        $this->authorize('restore', $target);

        $target->restore();

        return redirect()->route('rooms.index', ['lifecycle' => 'archived'])->with('status', 'room-restored');
    }

    public function importForm(): View
    {
        $this->authorize('create', Room::class);

        return view('rooms.import');
    }

    public function import(ImportRoomsRequest $request, ImportRoomsCsvAction $importRoomsCsvAction, RoomBedService $roomBedService): RedirectResponse
    {
        $this->authorize('create', Room::class);

        try {
            $processed = $importRoomsCsvAction->execute($request->file('csv_file'), $roomBedService);
        } catch (InvalidArgumentException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['csv_file' => $exception->getMessage()]);
        }

        return redirect()->route('rooms.index')->with('status', 'rooms-imported:' . $processed);
    }

    public function export(ExportRoomsCsvAction $exportRoomsCsvAction): StreamedResponse
    {
        $this->authorize('viewAny', Room::class);

        return $exportRoomsCsvAction->execute();
    }
}
