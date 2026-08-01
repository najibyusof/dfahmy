<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookableUnitRequest;
use App\Http\Requests\UpdateBookableUnitRequest;
use App\Models\BookableUnit;
use App\Models\Building;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookableUnitController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', BookableUnit::class);

        return view('bookable-units.index', [
            'bookableUnits' => BookableUnit::query()
                ->with(['rooms:id,name,code'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', BookableUnit::class);

        return view('bookable-units.create', [
            'bookableUnit' => null,
            'buildings' => Building::query()->orderBy('name')->get(['id', 'name']),
            'rooms' => Room::query()->with('building:id,name')->where('is_active', true)->orderBy('code')->get(['id', 'building_id', 'name', 'code', 'floor']),
            'types' => BookableUnit::TYPES,
        ]);
    }

    public function store(StoreBookableUnitRequest $request): RedirectResponse
    {
        $this->authorize('create', BookableUnit::class);

        $validated = $request->validated();
        $roomIds = $validated['room_ids'];
        unset($validated['room_ids']);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $bookableUnit = BookableUnit::query()->create($validated);
        $bookableUnit->rooms()->sync($roomIds);

        return redirect()->route('bookable-units.index')->with('status', 'bookable-unit-created');
    }

    public function edit(BookableUnit $bookable_unit): View
    {
        $this->authorize('update', $bookable_unit);

        $bookable_unit->load('rooms:id,name,code');

        return view('bookable-units.edit', [
            'bookableUnit' => $bookable_unit,
            'buildings' => Building::query()->orderBy('name')->get(['id', 'name']),
            'rooms' => Room::query()->with('building:id,name')->where('is_active', true)->orderBy('code')->get(['id', 'building_id', 'name', 'code', 'floor']),
            'types' => BookableUnit::TYPES,
        ]);
    }

    public function update(UpdateBookableUnitRequest $request, BookableUnit $bookable_unit): RedirectResponse
    {
        $this->authorize('update', $bookable_unit);

        $validated = $request->validated();
        $roomIds = $validated['room_ids'];
        unset($validated['room_ids']);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $bookable_unit->update($validated);
        $bookable_unit->rooms()->sync($roomIds);

        return redirect()->route('bookable-units.index')->with('status', 'bookable-unit-updated');
    }

    public function destroy(BookableUnit $bookable_unit): RedirectResponse
    {
        $this->authorize('delete', $bookable_unit);

        $bookable_unit->delete();

        return redirect()->route('bookable-units.index')->with('status', 'bookable-unit-deleted');
    }
}
