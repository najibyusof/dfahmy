<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBuildingRequest;
use App\Http\Requests\UpdateBuildingRequest;
use App\Models\Building;
use App\Services\BuildingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuildingController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Building::class);

        $filters = [
            'search' => (string) $request->string('search'),
            'active' => (string) $request->string('active'),
            'lifecycle' => (string) $request->string('lifecycle', 'active'),
        ];

        $countsBaseQuery = Building::query()
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $query->where(function ($subQuery) use ($filters) {
                    $subQuery->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('code', 'like', '%' . $filters['search'] . '%');
                });
            })
            ->when($filters['active'] !== '', function ($query) use ($filters) {
                $query->where('is_active', $filters['active'] === '1');
            });

        $lifecycleCounts = [
            'active' => (clone $countsBaseQuery)->count(),
            'archived' => (clone $countsBaseQuery)->onlyTrashed()->count(),
            'all' => (clone $countsBaseQuery)->withTrashed()->count(),
        ];

        $buildings = Building::query()
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
            ->when($filters['active'] !== '', function ($query) use ($filters) {
                $query->where('is_active', $filters['active'] === '1');
            })
            ->withCount('rooms')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('buildings.index', [
            'buildings' => $buildings,
            'filters' => $filters,
            'lifecycleCounts' => $lifecycleCounts,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Building::class);

        return view('buildings.create');
    }

    public function store(StoreBuildingRequest $request, BuildingService $buildingService): RedirectResponse
    {
        $this->authorize('create', Building::class);

        $buildingService->create($request->validated());

        return redirect()->route('buildings.index')->with('status', 'building-created');
    }

    public function edit(Building $building): View
    {
        $this->authorize('update', $building);

        return view('buildings.edit', [
            'building' => $building,
        ]);
    }

    public function update(UpdateBuildingRequest $request, Building $building, BuildingService $buildingService): RedirectResponse
    {
        $this->authorize('update', $building);

        $buildingService->update($building, $request->validated());

        return redirect()->route('buildings.index')->with('status', 'building-updated');
    }

    public function destroy(Building $building): RedirectResponse
    {
        $this->authorize('delete', $building);

        if ($building->rooms()->exists()) {
            return redirect()->route('buildings.index')->with('status', 'building-delete-blocked');
        }

        $building->delete();

        return redirect()->route('buildings.index')->with('status', 'building-deleted');
    }

    public function restore(int $building): RedirectResponse
    {
        $target = Building::withTrashed()->findOrFail($building);

        $this->authorize('restore', $target);

        $target->restore();

        return redirect()->route('buildings.index', ['lifecycle' => 'archived'])->with('status', 'building-restored');
    }
}
