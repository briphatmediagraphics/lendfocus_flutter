<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Webhooks should be placed here
| so they are not subject to web CSRF protection.
|
*/

Route::post('/stripe/webhook', [App\Http\Controllers\StripeController::class, 'webhook'])->name('stripe.webhook');

// Save project drawn shapes (admin only)
Route::post('/projects/{project}/shapes', [App\Http\Controllers\ProjectController::class, 'saveMapShapes'])->name('api.projects.shapes');

// Return project shapes as JSON (used by admin map sidebar)
Route::get('/projects/{project}/shapes', [App\Http\Controllers\ProjectController::class, 'projectShapesJson'])->name('api.projects.shapes.get');

Route::prefix('public')->name('api.public.')->group(function (): void {
    Route::get('/properties', [PublicApiController::class, 'properties'])->name('properties.index');
    Route::get('/properties/{property}', [PublicApiController::class, 'property'])->name('properties.show');
    Route::get('/services', [PublicApiController::class, 'services'])->name('services.index');
    Route::post('/inquiries', [PublicApiController::class, 'storeInquiry'])
        ->middleware('throttle:10,1')
        ->name('inquiries.store');
    Route::post('/appointments', [PublicApiController::class, 'storeAppointment'])
        ->middleware('throttle:10,1')
        ->name('appointments.store');
});
