<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\HousekeepingTaskController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicHealthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\OperationsHealthController;
use App\Http\Controllers\RoomBedConfigurationController;
use App\Http\Controllers\RoomAvailabilityController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoleMatrixController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SuperAdminTelegramAlertSettingController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/healthz', [PublicHealthController::class, 'basic'])
    ->middleware('throttle:health-check')
    ->name('health.basic');

Route::get('/readyz', [PublicHealthController::class, 'ready'])
    ->middleware('throttle:health-check')
    ->name('health.ready');

Route::get('/healthz/ops', [PublicHealthController::class, 'ops'])
    ->middleware('throttle:health-check')
    ->name('health.ops');

Route::get('/readyz/ops', [PublicHealthController::class, 'readyOps'])
    ->middleware('throttle:health-check')
    ->name('health.ready.ops');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', 'permission.access:dashboard.view'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->middleware('throttle:notification-actions')
        ->name('notifications.mark-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->middleware('throttle:notification-actions')
        ->name('notifications.mark-all-read');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/modules/rooms', function () {
        return redirect()->route('rooms.index');
    })
        ->middleware('permission.access:rooms.manage')
        ->name('modules.rooms.index');
    Route::get('/modules/bookings', function () {
        return redirect()->route('bookings.index');
    })
        ->middleware('permission.access:bookings.manage')
        ->name('modules.bookings.index');
    Route::get('/modules/guests', function () {
        return redirect()->route('guests.index');
    })
        ->middleware('permission.access:guests.manage')
        ->name('modules.guests.index');
    Route::get('/modules/payments', function () {
        return redirect()->route('payments.index');
    })
        ->middleware('permission.access:payments.manage')
        ->name('modules.payments.index');
    Route::get('/modules/checkin-checkout', [ModuleController::class, 'checkinCheckout'])
        ->middleware('permission.access:checkin_checkout.manage')
        ->name('modules.checkin-checkout.index');
    Route::get('/modules/housekeeping', function () {
        return redirect()->route('housekeeping.manage.index');
    })
        ->middleware('permission.access:housekeeping.manage')
        ->name('modules.housekeeping.index');
    Route::get('/modules/maintenance', [ModuleController::class, 'maintenance'])
        ->middleware('permission.access:maintenance.manage')
        ->name('modules.maintenance.index');
    Route::get('/modules/reports', function () {
        return redirect()->route('reports.index');
    })
        ->middleware('permission.access:reports.view')
        ->name('modules.reports.index');

    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('permission.access:reports.view')
        ->name('reports.index');
    Route::get('/reports/export/{type}', [ReportController::class, 'export'])
        ->middleware(['permission.access:reports.view', 'throttle:report-exports'])
        ->name('reports.export');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->middleware('auth')
        ->name('audit-logs.index');

    Route::resource('buildings', BuildingController::class)
        ->except(['show'])
        ->middleware('permission.access:rooms.manage');
    Route::post('/buildings/{building}/restore', [BuildingController::class, 'restore'])
        ->middleware('permission.access:rooms.manage')
        ->name('buildings.restore');
    Route::resource('rooms', RoomController::class)
        ->except(['show'])
        ->middleware('permission.access:rooms.manage');
    Route::resource('bookings', BookingController::class)
        ->middleware('permission.access:bookings.manage');
    Route::get('/bookings/{booking}/invoice', [PaymentController::class, 'invoice'])
        ->middleware('permission.access:payments.manage')
        ->name('bookings.invoice');
    Route::get('/bookings/{booking}/cancel', [BookingController::class, 'cancelPage'])
        ->middleware('permission.access:bookings.manage')
        ->name('bookings.cancel.page');
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])
        ->middleware(['permission.access:bookings.manage', 'throttle:state-mutations'])
        ->name('bookings.cancel');
    Route::get('/bookings/{booking}/check-in', [BookingController::class, 'checkInPage'])
        ->middleware('permission.access:bookings.manage')
        ->name('bookings.check-in.page');
    Route::patch('/bookings/{booking}/check-in', [BookingController::class, 'checkIn'])
        ->middleware(['permission.access:bookings.manage', 'throttle:state-mutations'])
        ->name('bookings.check-in');
    Route::get('/bookings/{booking}/check-out', [BookingController::class, 'checkOutPage'])
        ->middleware('permission.access:bookings.manage')
        ->name('bookings.check-out.page');
    Route::patch('/bookings/{booking}/check-out', [BookingController::class, 'checkOut'])
        ->middleware(['permission.access:bookings.manage', 'throttle:state-mutations'])
        ->name('bookings.check-out');
    Route::resource('guests', GuestController::class)
        ->middleware('permission.access:guests.manage');
    Route::resource('payments', PaymentController::class)
        ->middleware('permission.access:payments.manage');
    Route::get('/payments/{payment}/refund', [PaymentController::class, 'refundPage'])
        ->middleware('permission.access:payments.manage')
        ->name('payments.refund.page');
    Route::patch('/payments/{payment}/refund', [PaymentController::class, 'refund'])
        ->middleware(['permission.access:payments.manage', 'throttle:state-mutations'])
        ->name('payments.refund');
    Route::get('/payments/{payment}/void', [PaymentController::class, 'voidPage'])
        ->middleware('permission.access:payments.manage')
        ->name('payments.void.page');
    Route::patch('/payments/{payment}/void', [PaymentController::class, 'void'])
        ->middleware(['permission.access:payments.manage', 'throttle:state-mutations'])
        ->name('payments.void');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])
        ->middleware('permission.access:payments.manage')
        ->name('payments.receipt');
    Route::post('/rooms/{room}/restore', [RoomController::class, 'restore'])
        ->middleware('permission.access:rooms.manage')
        ->name('rooms.restore');
    Route::get('/rooms/import', [RoomController::class, 'importForm'])
        ->middleware('permission.access:rooms.manage')
        ->name('rooms.import.form');
    Route::post('/rooms/import', [RoomController::class, 'import'])
        ->middleware('permission.access:rooms.manage')
        ->name('rooms.import');
    Route::get('/rooms/export', [RoomController::class, 'export'])
        ->middleware('permission.access:rooms.manage')
        ->name('rooms.export');
    Route::get('/rooms/availability-calendar', [RoomAvailabilityController::class, 'index'])
        ->middleware('permission.access:rooms.manage')
        ->name('rooms.availability-calendar');
    Route::get('/room-bed-configurations', [RoomBedConfigurationController::class, 'index'])
        ->middleware('permission.access:rooms.manage')
        ->name('room-bed-configurations.index');
    Route::patch('/room-bed-configurations/{room}', [RoomBedConfigurationController::class, 'update'])
        ->middleware('permission.access:rooms.manage')
        ->name('room-bed-configurations.update');

    Route::get('/housekeeping/my-tasks', [HousekeepingTaskController::class, 'index'])
        ->middleware('permission.access:housekeeping.assigned.view')
        ->name('housekeeping.tasks.index');
    Route::patch('/housekeeping/my-tasks/{housekeepingTask}', [HousekeepingTaskController::class, 'update'])
        ->middleware('permission.access:housekeeping.assigned.update')
        ->name('housekeeping.tasks.update');
    Route::get('/housekeeping/tasks', [HousekeepingTaskController::class, 'managementIndex'])
        ->middleware('permission.access:housekeeping.manage')
        ->name('housekeeping.manage.index');
    Route::get('/housekeeping/tasks/create', [HousekeepingTaskController::class, 'create'])
        ->middleware('permission.access:housekeeping.manage')
        ->name('housekeeping.manage.create');
    Route::post('/housekeeping/tasks', [HousekeepingTaskController::class, 'store'])
        ->middleware('permission.access:housekeeping.manage')
        ->name('housekeeping.manage.store');
    Route::get('/housekeeping/tasks/{housekeepingTask}/edit', [HousekeepingTaskController::class, 'edit'])
        ->middleware('permission.access:housekeeping.manage')
        ->name('housekeeping.manage.edit');
    Route::patch('/housekeeping/tasks/{housekeepingTask}', [HousekeepingTaskController::class, 'managementUpdate'])
        ->middleware('permission.access:housekeeping.manage')
        ->name('housekeeping.manage.update');
    Route::delete('/housekeeping/tasks/{housekeepingTask}', [HousekeepingTaskController::class, 'destroy'])
        ->middleware('permission.access:housekeeping.manage')
        ->name('housekeeping.manage.destroy');

    Route::get('/admin/users', [UserManagementController::class, 'index'])
        ->middleware('permission.access:users.manage')
        ->name('admin.users.index');
    Route::patch('/admin/users/{user}/role', [UserManagementController::class, 'updateRole'])
        ->middleware(['permission.access:users.manage', 'throttle:state-mutations'])
        ->name('admin.users.role.update');
    Route::get('/admin/users/role-audits/export', [UserManagementController::class, 'exportAuditCsv'])
        ->middleware('permission.access:users.manage')
        ->name('admin.users.audit.export');

    Route::get('/admin/roles-permissions-matrix', [RoleMatrixController::class, 'index'])
        ->middleware('permission.access:users.manage')
        ->name('admin.roles-matrix.index');

    Route::get('/admin/operations-health', OperationsHealthController::class)
        ->middleware('permission.access:users.manage')
        ->name('admin.operations-health.index');

    Route::get('/admin/telegram-alert-settings', [SuperAdminTelegramAlertSettingController::class, 'index'])
        ->middleware('permission.access:users.manage')
        ->name('admin.telegram-alert-settings.index');
    Route::patch('/admin/telegram-alert-settings', [SuperAdminTelegramAlertSettingController::class, 'update'])
        ->middleware(['permission.access:users.manage', 'throttle:state-mutations'])
        ->name('admin.telegram-alert-settings.update');
    Route::post('/admin/telegram-alert-settings/test', [SuperAdminTelegramAlertSettingController::class, 'sendTest'])
        ->middleware(['permission.access:users.manage', 'throttle:state-mutations'])
        ->name('admin.telegram-alert-settings.test');
});

require __DIR__ . '/auth.php';
