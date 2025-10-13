<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;

use App\Http\Controllers\API\VehicleController;
use App\Http\Controllers\API\RentalController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\Admin\StatisticsController;

# Vehicle rute- javne
Route::get('/vehicles', [VehicleController::class, 'index']);   // Prikaz svih vozila
Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])
    ->name('vehicles.show'); ; // Prikaz jednog vozila

#vehicle rute- zasticene
Route::middleware(['auth:sanctum', 'can:admin'])->group(function () {
    Route::apiResource('vehicles', VehicleController::class)
        ->except(['index', 'show']); // bez javnih ruta
});


#rental rute- zasticene
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/rentals', [RentalController::class, 'index']);
    Route::get('/rentals/{id}', [RentalController::class, 'show']);
    Route::post('/rentals', [RentalController::class, 'store']);
    Route::put('/rentals/{id}', [RentalController::class, 'update']);
    Route::delete('/rentals/{id}', [RentalController::class, 'destroy']);

    Route::get('/my-rentals', [RentalController::class, 'myRentals']);
    Route::put('/my-rentals/{id}', [RentalController::class, 'updateMyRental']);
    Route::put('/rentals/{id}/cancel', [RentalController::class, 'cancel']);
});

#javne rute
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

#grupa za zasticene rute (treba im autentifikacija korisnika)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

#rute za reset password-a
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::fallback(function () {
    return response()->json(['message' => 'Stranica nije pronadjena.'], 404);
});

#rute za payments
Route::middleware(['auth:sanctum', 'can:admin'])->group(function () {
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments/{rentalId}', [PaymentController::class, 'store']);
});


#statistika za admine
Route::middleware(['auth:sanctum', 'can:admin'])->prefix('admin')->group(function () {
    Route::get('/statistics/vehicles', [StatisticsController::class, 'vehicleStatistics']);
    Route::get('/statistics/vehicles/{vehicle}/rentals', [StatisticsController::class, 'vehicleRentalDetails']);
});