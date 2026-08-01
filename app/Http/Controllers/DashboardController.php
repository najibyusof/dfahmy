<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the authenticated user's home dashboard.
     */
    public function __invoke(Request $request, DashboardService $dashboardService): View
    {
        $this->authorize('viewDashboard', $request->user());

        return view('dashboard', [
            'stats' => $dashboardService->forUser($request->user()),
        ]);
    }
}
