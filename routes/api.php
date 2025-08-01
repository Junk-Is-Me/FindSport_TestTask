<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BookingController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('auth.api_token')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']); // список бронирований
    Route::post('/bookings', [BookingController::class, 'store']); // создание брони

    Route::patch('/bookings/{booking}/slots/{slot}' , [BookingController::class, 'updateSlot']); // обновление конкретного слота
    Route::post('/bookings/{booking}/slots', [BookingController::class, 'addSlot']); // добавление нового слота
    Route::delete('/bookings/{booking}', [BookingController::class, 'deleteBooking']); // удаление всей брони
});

