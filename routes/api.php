<?php

use App\Http\Controllers\AlumnosController;
use App\Http\Controllers\AlumnosTrabajosController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PreregistroController;
use App\Http\Controllers\ProfesorsController;
use App\Http\Controllers\ProfesorTrabajosController;
use App\Http\Controllers\TrabajosController;
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


Route::group([ 'middleware' => 'api' ], function ($router) {
    // Preregister routes
    Route::get('/auth/preregister', [PreregistroController::class, 'index']);
    Route::post('/auth/preregister', [PreregistroController::class, 'store']);
    Route::post('/auth/otp/resend', [PreregistroController::class, 'update']);
    Route::post('/auth/otp/verify', [PreregistroController::class, 'verify']);
    Route::post('/auth/register', [PreregistroController::class, 'register']);

    // Authorization endpoints
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/me', [AuthController::class, 'me']);

    // Forgot password
    Route::post('/password/forgot', [PasswordResetController::class, 'forgot']);
    Route::post('/password/reset', [PasswordResetController::class, 'reset']);

    // Users and students endpoints
    Route::get('/usuarios', [UsersController::class, 'index']);
    Route::put('/alumno/activate', [AlumnosController::class, 'activate']);
    Route::put('/alumnos/activate', [AlumnosController::class, 'activateAlumnos']);
    Route::delete('/alumnos', [AlumnosController::class, 'delete']);
    Route::delete('/preregistros', [AlumnosController::class, 'deletePreregister']);
    Route::get('/profesors', [UsersController::class, 'getProfesors']);
    Route::get('/profesor/detail/{user}', [UsersController::class, 'getProfesorDetail']);
    Route::get('/profesor/trabajos/{user}', [ProfesorTrabajosController::class, 'getProfesorTrabajos']);
    Route::get('/profesor/github', [ProfesorsController::class, 'addLinkProfesor']);
    Route::get('/profesor/files', [ProfesorsController::class, 'addLinkProfesor']);
    Route::get('/profesor/location', [ProfesorsController::class, 'addLinkProfesor']);
    Route::get('/profesor/phone', [ProfesorsController::class, 'addLinkProfesor']);

    // TTs endpoints
    Route::get('/trabajo', [TrabajosController::class, 'index']);
    Route::get('/trabajo/{trabajo}', [TrabajosController::class, 'get']);
    Route::post('/trabajo', [TrabajosController::class, 'store']);
    Route::delete('/trabajo', [TrabajosController::class, 'delete']);
    Route::put('/trabajo', [TrabajosController::class, 'update']);
    Route::put('/trabajo/new/student', [AlumnosTrabajosController::class, 'add']);
    Route::put('/trabajo/delete/student', [AlumnosTrabajosController::class, 'remove']);
    Route::put('/trabajo/update/student', [AlumnosTrabajosController::class, 'update']);
});
