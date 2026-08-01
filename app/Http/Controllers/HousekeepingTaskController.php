<?php

namespace App\Http\Controllers;

use App\Http\Requests\HousekeepingTaskStatusRequest;
use App\Http\Requests\StoreHousekeepingTaskRequest;
use App\Http\Requests\UpdateHousekeepingTaskRequest;
use App\Models\Booking;
use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use App\Services\HousekeepingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HousekeepingTaskController extends Controller
{
    public function managementIndex(Request $request): View
    {
        $this->authorize('viewAny', HousekeepingTask::class);

        $filters = [
            'status' => (string) $request->string('status'),
            'priority' => (string) $request->string('priority'),
            'assigned_to_user_id' => (string) $request->string('assigned_to_user_id'),
            'search' => (string) $request->string('search'),
        ];

        $tasks = HousekeepingTask::query()
            ->with(['assignee:id,name', 'room:id,name,code'])
            ->when($filters['status'] !== '', function ($query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->when($filters['priority'] !== '', function ($query) use ($filters): void {
                $query->where('priority', $filters['priority']);
            })
            ->when($filters['assigned_to_user_id'] !== '', function ($query) use ($filters): void {
                $query->where('assigned_to_user_id', (int) $filters['assigned_to_user_id']);
            })
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $term = '%' . $filters['search'] . '%';
                $query->where(function ($subQuery) use ($term): void {
                    $subQuery->where('room_label', 'like', $term)
                        ->orWhere('task_type', 'like', $term)
                        ->orWhere('notes', 'like', $term)
                        ->orWhere('checklist_notes', 'like', $term)
                        ->orWhereHas('room', function ($roomQuery) use ($term): void {
                            $roomQuery->where('name', 'like', $term)
                                ->orWhere('code', 'like', $term);
                        })
                        ->orWhereHas('assignee', function ($assigneeQuery) use ($term): void {
                            $assigneeQuery->where('name', 'like', $term);
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $housekeepers = User::query()
            ->whereHas('roles.permissions', function ($query): void {
                $query->where('name', 'housekeeping.assigned.view');
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('housekeeping.index', [
            'tasks' => $tasks,
            'filters' => $filters,
            'statuses' => HousekeepingTask::STATUSES,
            'priorities' => HousekeepingTask::PRIORITIES,
            'housekeepers' => $housekeepers,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', HousekeepingTask::class);

        return view('housekeeping.create', [
            'task' => null,
            'rooms' => Room::query()->orderBy('code')->get(['id', 'name', 'code']),
            'bookings' => Booking::query()->orderByDesc('id')->limit(100)->get(['id', 'booking_reference']),
            'housekeepers' => User::query()
                ->whereHas('roles.permissions', function ($query): void {
                    $query->where('name', 'housekeeping.assigned.view');
                })
                ->orderBy('name')
                ->get(['id', 'name']),
            'statuses' => HousekeepingTask::STATUSES,
            'priorities' => HousekeepingTask::PRIORITIES,
            'taskTypes' => HousekeepingTask::TASK_TYPES,
        ]);
    }

    public function store(StoreHousekeepingTaskRequest $request, HousekeepingService $housekeepingService): RedirectResponse
    {
        $this->authorize('create', HousekeepingTask::class);

        $housekeepingService->createTask($request->validated());

        return redirect()->route('housekeeping.manage.index')->with('status', 'housekeeping-task-created');
    }

    public function edit(HousekeepingTask $housekeepingTask): View
    {
        $this->authorize('update', $housekeepingTask);

        return view('housekeeping.edit', [
            'task' => $housekeepingTask,
            'rooms' => Room::query()->orderBy('code')->get(['id', 'name', 'code']),
            'bookings' => Booking::query()->orderByDesc('id')->limit(100)->get(['id', 'booking_reference']),
            'housekeepers' => User::query()
                ->whereHas('roles.permissions', function ($query): void {
                    $query->where('name', 'housekeeping.assigned.view');
                })
                ->orderBy('name')
                ->get(['id', 'name']),
            'statuses' => HousekeepingTask::STATUSES,
            'priorities' => HousekeepingTask::PRIORITIES,
            'taskTypes' => HousekeepingTask::TASK_TYPES,
        ]);
    }

    public function managementUpdate(UpdateHousekeepingTaskRequest $request, HousekeepingTask $housekeepingTask, HousekeepingService $housekeepingService): RedirectResponse
    {
        $this->authorize('update', $housekeepingTask);

        $housekeepingService->updateTask($housekeepingTask, $request->validated());

        return redirect()->route('housekeeping.manage.index')->with('status', 'housekeeping-task-updated');
    }

    public function destroy(HousekeepingTask $housekeepingTask): RedirectResponse
    {
        $this->authorize('delete', $housekeepingTask);

        $housekeepingTask->delete();

        return redirect()->route('housekeeping.manage.index')->with('status', 'housekeeping-task-deleted');
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAssignedHousekeepingTasks', $request->user());

        $tasks = HousekeepingTask::query()
            ->with(['room:id,name,code'])
            ->where('assigned_to_user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('modules.housekeeping-assigned', [
            'tasks' => $tasks,
        ]);
    }

    public function update(HousekeepingTaskStatusRequest $request, HousekeepingTask $housekeepingTask, HousekeepingService $housekeepingService): RedirectResponse
    {
        $this->authorize('update', $housekeepingTask);

        $housekeepingService->updateAssignedTask($housekeepingTask, $request->validated());

        return redirect()
            ->route('housekeeping.tasks.index')
            ->with('status', 'housekeeping-task-updated');
    }
}
