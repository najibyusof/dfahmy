<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestRequest;
use App\Http\Requests\UpdateGuestRequest;
use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Guest::class);

        $filters = [
            'search' => (string) $request->string('search'),
        ];

        $guests = Guest::query()
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $term = '%' . $filters['search'] . '%';

                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('full_name', 'like', $term)
                        ->orWhere('phone_number', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('identification_number', 'like', $term);
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('guests.index', [
            'guests' => $guests,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Guest::class);

        return view('guests.create');
    }

    public function store(StoreGuestRequest $request): RedirectResponse
    {
        $this->authorize('create', Guest::class);

        Guest::query()->create($request->validated());

        return redirect()->route('guests.index')->with('status', 'guest-created');
    }

    public function show(Guest $guest): View
    {
        $this->authorize('view', $guest);

        $today = now()->toDateString();

        $upcomingBookings = $guest->bookings()
            ->with('bookingRoomItems.room:id,name,code')
            ->whereDate('check_in_date', '>=', $today)
            ->orderBy('check_in_date')
            ->get();

        $pastBookings = $guest->bookings()
            ->with('bookingRoomItems.room:id,name,code')
            ->whereDate('check_in_date', '<', $today)
            ->orderByDesc('check_in_date')
            ->get();

        return view('guests.show', [
            'guest' => $guest,
            'upcomingBookings' => $upcomingBookings,
            'pastBookings' => $pastBookings,
        ]);
    }

    public function edit(Guest $guest): View
    {
        $this->authorize('update', $guest);

        return view('guests.edit', [
            'guest' => $guest,
        ]);
    }

    public function update(UpdateGuestRequest $request, Guest $guest): RedirectResponse
    {
        $this->authorize('update', $guest);

        $guest->update($request->validated());

        return redirect()->route('guests.index')->with('status', 'guest-updated');
    }

    public function destroy(Guest $guest): RedirectResponse
    {
        $this->authorize('delete', $guest);

        if ($guest->bookings()->exists()) {
            return redirect()->route('guests.index')->with('status', 'guest-delete-blocked');
        }

        $guest->delete();

        return redirect()->route('guests.index')->with('status', 'guest-deleted');
    }
}
