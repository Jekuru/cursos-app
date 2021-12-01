<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\VideoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/** USUARIOS */
// Registrar
Route::prefix('users')->group(function(){
    Route::put('/register', [UserController::class, 'register']);
});
// Modificar
Route::prefix('users')->group(function(){
    Route::post('/modify/{id}', [UserController::class, 'modify']);
});
// Desactivar
Route::prefix('users')->group(function(){
    Route::post('/disable/{id}', [UserController::class, 'disable']);
});
// Activar
Route::prefix('users')->group(function(){
    Route::post('/enable/{id}', [UserController::class, 'enable']);
});
// Unirse a curso
Route::prefix('users')->group(function(){
    Route::post('/join', [UserController::class, 'join']);
});
// Ver cursos
Route::prefix('users')->group(function(){
    Route::get('/joined/{id}', [UserController::class, 'joined']);
});

/** CURSOS */
// Registrar
Route::prefix('courses')->group(function(){
    Route::put('/register', [CourseController::class, 'register']);
});
// Mostrar los cursos con su título, foto y número de vídeos
Route::prefix('courses')->group(function(){
    Route::get('/search', [CourseController::class, 'search']);
});

/** VIDEOS */
// Añadir
Route::prefix('videos')->group(function(){
    Route::put('/upload', [VideoController::class, 'upload']);
});
Route::prefix('videos')->group(function(){
    Route::post('/watch', [VideoController::class, 'watch']);
});
Route::prefix('courses')->group(function(){
    Route::get('/listVideos', [VideoController::class, 'listVideos']);
});