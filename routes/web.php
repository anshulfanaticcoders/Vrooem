<?php

use App\Http\Controllers\Admin\PlansController;
use App\Http\Controllers\Admin\PopularPlacesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\VehicleAddonsController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\GeocodingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\VehicleCategoriesController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\VendorsDashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserDocumentController;
use App\Http\Controllers\VehicleController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// --------------*************-------------Route starts----------**************---------------
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/user', [ProfileController::class, 'show'])->name('user.profile');
    // Vehicle Listing 
    // Route::inertia('vehicle-listing', 'Auth/VehicleListing');

    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
});


// Open Routes for non-logged in users
Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
Route::get('/vehicle/{id}', [VehicleController::class, 'show'])->name('vehicle.show');
Route::get('/s', [SearchController::class, 'search']);
Route::get('/api/geocoding/autocomplete', [GeocodingController::class, 'autocomplete']);


// Stripe Routes
Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');




// These are the Routes for ---------*****ADMIN****-------------
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::inertia('admin-dashboard', 'AdminDashboard');
    Route::resource('vehicles-categories', VehicleCategoriesController::class);
    Route::resource('vendors', VendorsDashboardController::class);
    Route::put('/vendors/{vendorProfile}/status', [VendorsDashboardController::class, 'updateStatus'])->name('vendors.updateStatus');

    Route::resource('users', UsersController::class)->middleware(['auth']);
    // Route::inertia('vendors', 'AdminDashboardPages/Vendors/Index');
    Route::resource('booking-addons', VehicleAddonsController::class)->middleware(['auth']);
    Route::resource('popular-places', PopularPlacesController::class)->middleware(['auth']);
    Route::resource('plans', PlansController::class)->middleware(['auth']);
});



// These are the Routes for ---------*****VENDOR****-------------
Route::middleware(['auth', 'role:vendor'])->group(function () {
    // vendor Profile Web Routes
    Route::inertia('vendor-approved', 'Vendor/VendorApproved');
    //Route::inertia('booking-success/details', 'Booking/Success');
    Route::inertia('vendor-pending', 'Vendor/VendorPending');
    Route::inertia('vendor-rejected', 'Vendor/VendorRejected');

    // creating vehicle routes
    // Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/vehicle-categories', [VehicleController::class, 'getCategories'])->name('vehicle.categories');
    // this is for showing All Booking details of customer in vendor profile
    Route::get('/vendor/bookings', [BookingController::class, 'getAllBookings'])->name('vendor.bookings');
    // this is for showing All Vehicles of vendor in vendor profile
    Route::get('/vendor/vehicles', [VehicleController::class, 'vendorVehicle'])->name('vehicles.index');

});


// These are the Routes for ---------*****CUSTOMER****-------------
Route::middleware(['auth', 'role:customer'])->group(function () {

    // User Profile routes
    Route::inertia('travel-documents', 'Profile/TravelDocuments');
    Route::post('/documents/upload', [UserDocumentController::class, 'uploadDocuments'])->name('documents.upload');
    Route::inertia('completed-bookings', 'Profile/CompletedBookings');
    Route::inertia('confirmed-bookings', 'Profile/ConfirmedBookings');
    Route::inertia('pending-bookings', 'Profile/PendingBookings');
    Route::inertia('issued-payments', 'Profile/IssuedPayments');
    Route::inertia('review', 'Profile/Review');
    Route::inertia('favourites', 'Profile/Favourites');
    Route::inertia('inbox', 'Profile/Inbox');

    // apply for vendor
    Route::post('/vendor/store', [VendorController::class, 'store'])->name('vendor.store');
    Route::get('/vendor/register', [VendorController::class, 'create'])->name('vendor.register');
    // Booking routes
    // Booking Routes
    Route::get('/booking/{id}', [VehicleController::class, 'booking'])->name('booking.show');
    // Route::get('/booking-success', [BookingController::class, 'success'])->name('booking.success');
    Route::get('/booking-success/details', [BookingController::class, 'getBookingDetails'])->name('booking-success.details');
    Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::post('/payment/charge', [PaymentController::class, 'charge'])->name('payment.charge');
    // this route is to show customer booking in the customer profile
    Route::get('/customer/bookings', [BookingController::class, 'getCustomerBookingData'])->name('customer.bookings');
    //  this is route is for redirecting to the success page after payment done
    Route::get('/booking-success/details', function () {
        return Inertia::render('Booking/Success', [
            'payment_intent' => request('payment_intent'), // Pass payment intent ID
        ]);
    })->name('booking-success.details');

    Route::get('/profile/bookings/pending', [BookingController::class, 'getPendingBookings'])
    ->name('profile.bookings.pending');

Route::get('/profile/bookings/confirmed', [BookingController::class, 'getConfirmedBookings'])
    ->name('profile.bookings.confirmed');

    Route::get('/profile/bookings/completed', [BookingController::class, 'getCompletedBookings'])
    ->name('profile.bookings.completed');
});

Route::middleware(['auth', 'vendor.status'])->group(function () {
    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');

    // this is for showing All Booking details of customer in vendor profile
    Route::get('/vendor/bookings', [BookingController::class, 'getAllBookings'])->name('vendor.bookings');
    // this is for showing All Vehicles of vendor in vendor profile
    Route::get('/vendor/vehicles', [VehicleController::class, 'vendorVehicle'])->name('vehicles.index');
    Route::inertia('vehicle-listing', 'Auth/VehicleListing');
});
require __DIR__ . '/auth.php';