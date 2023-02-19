<?php

use App\Http\Controllers\AlumnosController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PreregistroController;
use App\Http\Controllers\UsersController;
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


Route::group([ 'middleware' => 'api' ], function ($router) {
    // Authorization endpoints
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/me', [AuthController::class, 'me']);

    // Users and students endpoints
    Route::get('/usuarios', [UsersController::class, 'index']);
    Route::put('/alumno/activate', [AlumnosController::class, 'activate']);
    Route::delete('/alumnos', [AlumnosController::class, 'delete']);
});
