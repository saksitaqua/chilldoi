<?php

use App\Http\Controllers\Admin\AccommodationTypeController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\BookingRequestController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\StoryController as AdminStoryController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StoryFeedController;
use App\Http\Controllers\UnitShowController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoryFeedController::class, 'index'])->name('home');
Route::get('/stories/{story}', [StoryFeedController::class, 'show'])->name('stories.show');

Route::get('/availability', [AvailabilityController::class, 'index'])->name('availability.index');
Route::get('/units/{unit}', [UnitShowController::class, 'show'])->name('units.show');

Route::get('/book/{unit}', [BookingRequestController::class, 'create'])->name('booking.create');
Route::post('/book/{unit}', [BookingRequestController::class, 'store'])->name('booking.store');
Route::get('/book/thank-you/{booking}', [BookingRequestController::class, 'thankyou'])->name('booking.thankyou');
Route::post('/book/thank-you/{booking}/slip', [BookingRequestController::class, 'uploadSlip'])->name('booking.slip');

Route::get('/map', [MapController::class, 'index'])->name('map.index');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('types', AccommodationTypeController::class);
    Route::resource('units', UnitController::class);
    Route::resource('activities', ActivityController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('bookings', BookingController::class);
    Route::resource('stories', AdminStoryController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::get('reports/summary', [ReportController::class, 'summary'])->name('reports.summary');
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('banners', [BannerController::class, 'index'])->name('banners.index');
    Route::post('banners', [BannerController::class, 'store'])->name('banners.store');
    Route::put('banners', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('banners/{banner}', [BannerController::class, 'destroyImage'])->name('banners.destroy');
});

require __DIR__.'/auth.php';
