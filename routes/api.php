<?php

use App\Http\Controllers\AlumnosController;
use App\Http\Controllers\PreregistroController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Preregister routes
Route::get('/auth/preregister', [PreregistroController::class, 'index']);
Route::post('/auth/preregister', [PreregistroController::class, 'store']);
Route::post('/auth/otp/resend', [PreregistroController::class, 'update']);
Route::post('/auth/otp/verify', [PreregistroController::class, 'verify']);
Route::post('/auth/register', [PreregistroController::class, 'register']);

Route::put('/alumno/activate', [AlumnosController::class, 'activate']);
