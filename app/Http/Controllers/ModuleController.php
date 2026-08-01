<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function rooms(Request $request): View
    {
        $this->authorize('manageRooms', $request->user());

        return $this->moduleView('Rooms', 'Manage room inventory, room types, and availability.');
    }

    public function bookings(Request $request): View
    {
        $this->authorize('manageBookings', $request->user());

        return $this->moduleView('Bookings', 'Manage booking lifecycle and reservation records.');
    }

    public function guests(Request $request): View
    {
        $this->authorize('manageGuests', $request->user());

        return $this->moduleView('Guests', 'Manage guest profiles and contact details.');
    }

    public function payments(Request $request): View
    {
        $this->authorize('managePayments', $request->user());

        return $this->moduleView('Payments', 'Manage payment collection, settlement, and auditing.');
    }

    public function checkinCheckout(Request $request): View
    {
        $this->authorize('manageCheckinCheckout', $request->user());

        return $this->moduleView('Check-In / Check-Out', 'Manage guest arrivals, departures, and room handovers.');
    }

    public function housekeeping(Request $request): View
    {
        $this->authorize('manageHousekeeping', $request->user());

        return $this->moduleView('Housekeeping Management', 'Manage cleaning assignments, priorities, and completion status.');
    }

    public function maintenance(Request $request): View
    {
        $this->authorize('manageMaintenance', $request->user());

        return $this->moduleView('Maintenance', 'Manage preventive and corrective maintenance requests.');
    }

    public function reports(Request $request): View
    {
        $this->authorize('viewReports', $request->user());

        return $this->moduleView('Reports', 'Access operational and performance reporting tools.');
    }

    private function moduleView(string $moduleName, string $description): View
    {
        return view('modules.index', [
            'moduleName' => $moduleName,
            'description' => $description,
        ]);
    }
}
