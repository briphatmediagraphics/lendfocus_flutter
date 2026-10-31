<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\InquiriesController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::get('/storage/{path}', [\App\Http\Controllers\StorageController::class, 'show'])
    ->where('path', '.*')
    ->name('storage.show');

Route::get('/lang/{lang}', [PublicPageController::class, 'setLang'])->name('lang');

Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/properties', [PublicPageController::class, 'properties'])->name('properties');
Route::get('/properties/{id}', [PublicPageController::class, 'propertyDetail'])->name('property.detail');
Route::get('/services', [PublicPageController::class, 'services'])->name('services');
Route::get('/about', [PublicPageController::class, 'about'])->name('about');
Route::get('/history', [PublicPageController::class, 'history'])->name('history');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('contact');
Route::get('/terms', [PublicPageController::class, 'terms'])->name('terms');
Route::get('/book', [PublicPageController::class, 'book'])->name('book');
Route::get('/assistant', [AssistantController::class, 'index'])->name('assistant');
Route::post('/assistant', [AssistantController::class, 'ask'])->name('assistant.ask');

Route::post('/inquiries', [InquiriesController::class, 'store'])->name('inquiries.store');
Route::get('/appointment', [PublicPageController::class, 'book'])->name('appointment');
Route::post('/appointments', [InquiriesController::class, 'storeAppointment'])->name('appointments.store');
Route::post('/appointment', [InquiriesController::class, 'storeAppointment'])->name('appointment.store');

Route::get('/support', [App\Http\Controllers\SupportTicketController::class, 'createPublic'])->name('support.create');
Route::post('/support', [App\Http\Controllers\SupportTicketController::class, 'storePublic'])->name('support.store');
Route::get('/buyer/login', [App\Http\Controllers\BuyerAuthController::class, 'showLogin'])->name('buyer.login');
Route::post('/buyer/login', [App\Http\Controllers\BuyerAuthController::class, 'login'])->name('buyer.login.post');

// Public Content Routes
Route::get('/testimonials', [App\Http\Controllers\TestimonialController::class, 'index'])->name('testimonials.index');
Route::get('/gallery', [App\Http\Controllers\GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/project/{projectId}', [App\Http\Controllers\GalleryController::class, 'project'])->name('gallery.project');
Route::get('/team', [App\Http\Controllers\StaffController::class, 'index'])->name('staff.index');
Route::get('/team/{member}', [App\Http\Controllers\StaffController::class, 'show'])->name('staff.show');
Route::get('/team/department/{department}', [App\Http\Controllers\StaffController::class, 'department'])->name('staff.department');

// Projects & Plots Routes
Route::prefix('miradi')->group(function () {
    Route::get('/', [App\Http\Controllers\ProjectController::class, 'index'])->name('projects.index');
    Route::get('/{project}', [App\Http\Controllers\ProjectController::class, 'show'])->name('projects.show');
    Route::get('/{project}/plots', [App\Http\Controllers\ProjectController::class, 'plots'])->name('projects.plots');
    Route::get('/{project}/plots/{plot}', [App\Http\Controllers\ProjectController::class, 'plotDetail'])->name('projects.plot.detail');
    Route::get('/{project}/export/plots.csv', [App\Http\Controllers\ProjectController::class, 'exportPlots'])->name('projects.export.plots');

    // Project map view (Leaflet)
    Route::get('/{project}/map', [App\Http\Controllers\ProjectController::class, 'map'])->name('projects.map');
});

// Public API for project plots (used by the map)
Route::get('/api/miradi/{project}/plots', [App\Http\Controllers\ProjectController::class, 'plotsJson'])->name('api.projects.plots.json');

// Map booking and Stripe checkout
Route::post('/plots/{plot}/reserve', [App\Http\Controllers\MapBookingController::class, 'reserve'])->name('plots.reserve');
Route::post('/bookings/{booking}/stripe-checkout', [App\Http\Controllers\StripeController::class, 'createCheckout'])->name('bookings.stripe.checkout');

Route::middleware('buyer-auth')->group(function () {
    Route::prefix('buyer')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\BuyerDashboardController::class, 'dashboard'])->name('buyer.dashboard');
        Route::get('/plots', [App\Http\Controllers\BuyerDashboardController::class, 'plots'])->name('buyer.plots');
        Route::get('/plots/export', [App\Http\Controllers\BuyerDashboardController::class, 'exportBookings'])->name('buyer.plots.export');
        Route::get('/plots/{bookingId}', [App\Http\Controllers\BuyerDashboardController::class, 'plotDetail'])->name('buyer.plot.detail');
        Route::get('/payments', [App\Http\Controllers\BuyerDashboardController::class, 'payments'])->name('buyer.payments');
        Route::get('/payments/export', [App\Http\Controllers\BuyerDashboardController::class, 'exportPayments'])->name('buyer.payments.export');
        Route::get('/notifications', [App\Http\Controllers\BuyerDashboardController::class, 'notifications'])->name('buyer.notifications');
        Route::get('/support-tickets', [App\Http\Controllers\SupportTicketController::class, 'index'])->name('buyer.support.tickets');
        Route::get('/support-tickets/create', [App\Http\Controllers\SupportTicketController::class, 'create'])->name('buyer.support.tickets.create');
        Route::post('/support-tickets', [App\Http\Controllers\SupportTicketController::class, 'store'])->name('buyer.support.tickets.store');
        Route::get('/support-tickets/{supportTicket}', [App\Http\Controllers\SupportTicketController::class, 'show'])->name('buyer.support.tickets.show');
        Route::post('/support-tickets/{supportTicket}/reply', [App\Http\Controllers\SupportTicketController::class, 'reply'])->name('buyer.support.tickets.reply');
        Route::get('/payment/{paymentId}/receipt', [App\Http\Controllers\BuyerDashboardController::class, 'receipt'])->name('buyer.payment.receipt');
        Route::get('/profile', [App\Http\Controllers\BuyerDashboardController::class, 'profile'])->name('buyer.profile');
        Route::get('/profile/edit', [App\Http\Controllers\BuyerDashboardController::class, 'editProfile'])->name('buyer.profile.edit');
        Route::post('/profile', [App\Http\Controllers\BuyerDashboardController::class, 'updateProfile'])->name('buyer.profile.update');
        Route::post('/logout', [App\Http\Controllers\BuyerDashboardController::class, 'logout'])->name('buyer.logout');

        // Payment routes for buyer
        Route::prefix('payments')->group(function () {
            Route::get('/{booking}/create', [App\Http\Controllers\PaymentController::class, 'create'])->name('buyer.payment.create');
            Route::post('/{booking}', [App\Http\Controllers\PaymentController::class, 'store'])->name('buyer.payment.store');
        });
    });
});

// Admin staff route alias at the top-level for bn parity
Route::middleware('admin')->get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->name('admin.dashboard.alias');

// Staff aliases matching /bn
Route::redirect('/wanunuzi', '/admin/wanunuzi');
Route::redirect('/simamia', '/admin/properties');
Route::redirect('/simamia/{project}', '/admin/projects/{project}/plots');
Route::redirect('/watumiaji', '/admin/users');

// Public aliases for bn static/info pages and auth
Route::redirect('/auth', '/admin/login');
Route::redirect('/kuhusu', '/about');
Route::redirect('/mawasiliano', '/contact');

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');

Route::middleware('admin')->prefix('admin')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::redirect('/projects', '/properties')->name('admin.projects');
    Route::get('/properties', [AdminController::class, 'properties'])->name('admin.properties');
    Route::get('/projects/{project}/plots', [AdminController::class, 'projectPlots'])->name('admin.project.plots');
        Route::get('/projects/{project}/export/plots.csv', [AdminController::class, 'exportProjectPlots'])->name('admin.project.plots.export');
    Route::get('/support-tickets', [AdminController::class, 'supportTickets'])->name('admin.support-tickets');
    Route::get('/support-tickets/{supportTicket}', [AdminController::class, 'supportTicketDetail'])->name('admin.support-ticket.detail');
    Route::post('/support-tickets/{supportTicket}', [AdminController::class, 'updateSupportTicket'])->name('admin.support-ticket.update');
    Route::post('/support-tickets/{supportTicket}/reply', [App\Http\Controllers\SupportTicketController::class, 'adminReply'])->name('admin.support-ticket.reply');
    // User management
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/users/export', [AdminController::class, 'exportUsers'])->name('admin.users.export');
    Route::post('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('admin.users.update_role');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');

    // Buyers list and export
    Route::get('/wanunuzi', [AdminController::class, 'buyers'])->name('admin.wanunuzi');
    Route::get('/wanunuzi/export', [AdminController::class, 'exportBuyers'])->name('admin.wanunuzi.export');
    
    // Content Management
    Route::get('/testimonials', [AdminController::class, 'testimonials'])->name('admin.testimonials');
    Route::post('/testimonials', [AdminController::class, 'storeTestimonial'])->name('admin.testimonials.store');
    Route::put('/testimonials/{id}', [AdminController::class, 'updateTestimonial'])->name('admin.testimonials.update');
    Route::delete('/testimonials/{id}', [AdminController::class, 'deleteTestimonial'])->name('admin.testimonials.delete');

    Route::get('/gallery', [AdminController::class, 'gallery'])->name('admin.gallery');
    Route::post('/gallery', [AdminController::class, 'storeGalleryPhoto'])->name('admin.gallery.store');
    Route::put('/gallery/{id}', [AdminController::class, 'updateGalleryPhoto'])->name('admin.gallery.update');
    Route::delete('/gallery/{id}', [AdminController::class, 'deleteGalleryPhoto'])->name('admin.gallery.delete');

    Route::get('/staff', [AdminController::class, 'staffManagement'])->name('admin.staff');
    Route::post('/staff', [AdminController::class, 'storeStaff'])->name('admin.staff.store');
    Route::put('/staff/{id}', [AdminController::class, 'updateStaff'])->name('admin.staff.update');
    Route::delete('/staff/{id}', [AdminController::class, 'deleteStaff'])->name('admin.staff.delete');
    
    Route::get('/calendar', [AdminController::class, 'calendar'])->name('admin.calendar');
    
    // Admin project shapes
    Route::get('/projects/{project}/shapes', [App\Http\Controllers\ProjectController::class, 'adminShapes'])->name('admin.projects.shapes');
    Route::delete('/projects/{project}/shapes/{index}', [App\Http\Controllers\ProjectController::class, 'deleteMapShape'])->name('admin.projects.shapes.delete');

    // Web route for saving shapes (CSRF + session available) - used by admin map draw
    Route::post('/projects/{project}/shapes', [App\Http\Controllers\ProjectController::class, 'saveMapShapes'])->name('projects.shapes.save');

    // Admin dashboard
    Route::prefix('admin')->middleware(['web','auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::get('/', [App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/map', [App\Http\Controllers\AdminDashboardController::class, 'map'])->name('admin.map.select');
        Route::get('/map/{project}', [App\Http\Controllers\AdminDashboardController::class, 'map'])->name('admin.map');

        Route::get('/bookings', [App\Http\Controllers\AdminDashboardController::class, 'bookings'])->name('admin.bookings');
        Route::post('/bookings/{booking}/release', [App\Http\Controllers\AdminDashboardController::class, 'releaseBooking'])->name('admin.bookings.release');
        Route::post('/bookings/{booking}/mark-paid', [App\Http\Controllers\AdminDashboardController::class, 'markPaid'])->name('admin.bookings.mark_paid');
        Route::get('/bookings/export/csv', [App\Http\Controllers\AdminDashboardController::class, 'exportBookings'])->name('admin.bookings.export');

        Route::get('/tickets', [App\Http\Controllers\AdminDashboardController::class, 'tickets'])->name('admin.tickets');
        Route::get('/tickets/{id}', [App\Http\Controllers\AdminDashboardController::class, 'ticketView'])->name('admin.tickets.show');
        Route::post('/tickets/{id}/reply', [App\Http\Controllers\AdminDashboardController::class, 'ticketReply'])->name('admin.tickets.reply');

        Route::get('/payments', [App\Http\Controllers\AdminDashboardController::class, 'payments'])->name('admin.payments');
        Route::get('/payments/export/csv', [App\Http\Controllers\AdminDashboardController::class, 'exportPayments'])->name('admin.payments.export');

        Route::get('/notifications', [App\Http\Controllers\AdminDashboardController::class, 'notifications'])->name('admin.notifications');

        // Admin settings
        Route::get('/settings', [App\Http\Controllers\AdminDashboardController::class, 'settings'])->name('admin.settings');
        Route::post('/settings', [App\Http\Controllers\AdminDashboardController::class, 'updateSettings'])->name('admin.settings.update');
    });
    Route::get('/inquiries', [AdminController::class, 'inquiries'])->name('admin.inquiries');
    Route::get('/inquiries/{inquiry}', [App\Http\Controllers\InquiryController::class, 'show'])->name('admin.inquiries.show');
    Route::post('/inquiries/{inquiry}/reply', [App\Http\Controllers\InquiryController::class, 'addReply'])->name('admin.inquiries.reply');
    Route::post('/inquiries/{inquiry}/assign', [App\Http\Controllers\InquiryController::class, 'assign'])->name('admin.inquiries.assign');
    Route::get('/work-orders', [AdminController::class, 'workOrders'])->name('admin.work-orders');
    Route::get('/team', [AdminController::class, 'team'])->name('admin.team');
    Route::get('/workers', [AdminController::class, 'workers'])->name('admin.workers');
    Route::get('/contracts', [AdminController::class, 'contracts'])->name('admin.contracts');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/my-tasks', [AdminController::class, 'myTasks'])->name('admin.my-tasks');
    Route::get('/notifications', [AdminController::class, 'notifications'])->name('admin.notifications');

    // API Routes for dynamic CRUD operations
    Route::prefix('api')->group(function () {
        // Properties API
        Route::get('/properties', [AdminController::class, 'listProperties'])->name('api.properties.list');
        Route::post('/properties', [AdminController::class, 'storeProperty'])->name('api.properties.store');
        Route::post('/properties/{id}', [AdminController::class, 'updateProperty'])->name('api.properties.update');
        Route::delete('/properties/{id}', [AdminController::class, 'deleteProperty'])->name('api.properties.delete');
        Route::get('/properties/{id}', [AdminController::class, 'getProperty'])->name('api.properties.get');

        // Project Plots API
        Route::get('/projects/{project}/plots', [AdminController::class, 'listProjectPlots'])->name('api.projects.plots.list');
        Route::get('/projects/{project}/plots/{plot}', [AdminController::class, 'getProjectPlot'])->name('api.projects.plots.get');
        Route::post('/projects/{project}/plots', [AdminController::class, 'storeProjectPlot'])->name('api.projects.plots.store');
        Route::post('/projects/{project}/plots/{plot}', [AdminController::class, 'updateProjectPlot'])->name('api.projects.plots.update');
        Route::delete('/projects/{project}/plots/{plot}', [AdminController::class, 'deleteProjectPlot'])->name('api.projects.plots.delete');

        // Calendar Events API
        Route::post('/events', [AdminController::class, 'storeEvent'])->name('api.events.store');
        Route::post('/events/{id}', [AdminController::class, 'updateEvent'])->name('api.events.update');
        Route::delete('/events/{id}', [AdminController::class, 'deleteEvent'])->name('api.events.delete');
        Route::get('/events/{id}', [AdminController::class, 'getEvent'])->name('api.events.get');
        Route::get('/events', [AdminController::class, 'listEvents'])->name('api.events.list');

        // Documents API
        Route::post('/documents', [AdminController::class, 'uploadDocument'])->name('api.documents.upload');
        Route::delete('/documents/{id}', [AdminController::class, 'deleteDocument'])->name('api.documents.delete');
        Route::get('/properties/{id}/documents', [AdminController::class, 'getPropertyDocuments'])->name('api.properties.documents');

        // Inquiries API
        Route::post('/inquiries/{id}', [AdminController::class, 'updateInquiry'])->name('api.inquiries.update');
        Route::delete('/inquiries/{id}', [AdminController::class, 'deleteInquiry'])->name('api.inquiries.delete');

        // Task API
        Route::get('/tasks', [AdminController::class, 'listTasks'])->name('api.tasks.list');
        Route::get('/tasks/{id}', [AdminController::class, 'getTask'])->name('api.tasks.get');
        Route::post('/tasks', [AdminController::class, 'storeTask'])->name('api.tasks.store');
        Route::post('/tasks/{id}', [AdminController::class, 'updateTask'])->name('api.tasks.update');
        Route::delete('/tasks/{id}', [AdminController::class, 'deleteTask'])->name('api.tasks.delete');
        Route::get('/reports/export', [AdminController::class, 'exportTaskReport'])->name('api.reports.export');

        // Notification API
        Route::get('/notifications', [AdminController::class, 'listNotifications'])->name('api.notifications.list');
        Route::get('/notifications/{id}', [AdminController::class, 'getNotification'])->name('api.notifications.get');
        Route::post('/notifications', [AdminController::class, 'storeNotification'])->name('api.notifications.store');
        Route::post('/notifications/{id}', [AdminController::class, 'updateNotification'])->name('api.notifications.update');
        Route::delete('/notifications/{id}', [AdminController::class, 'deleteNotification'])->name('api.notifications.delete');

        // Workers API
        Route::get('/workers', [AdminController::class, 'listWorkers'])->name('api.workers.list');
        Route::get('/workers/{id}', [AdminController::class, 'getWorker'])->name('api.workers.get');
        Route::post('/workers', [AdminController::class, 'storeWorker'])->name('api.workers.store');
        Route::post('/workers/{id}', [AdminController::class, 'updateWorker'])->name('api.workers.update');
        Route::delete('/workers/{id}', [AdminController::class, 'deleteWorker'])->name('api.workers.delete');

        // Contracts API
        Route::get('/contracts', [AdminController::class, 'listContracts'])->name('api.contracts.list');
        Route::get('/contracts/{id}', [AdminController::class, 'getContract'])->name('api.contracts.get');
        Route::post('/contracts', [AdminController::class, 'storeContract'])->name('api.contracts.store');
        Route::post('/contracts/{id}', [AdminController::class, 'updateContract'])->name('api.contracts.update');
        Route::delete('/contracts/{id}', [AdminController::class, 'deleteContract'])->name('api.contracts.delete');
    });
});


