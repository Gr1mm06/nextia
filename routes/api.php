<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BienesController;
use Tymon\JWTAuth\Facades\JWTAuth;

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


Route::post('login', [AuthController::class, 'authenticate']);
Route::post('registro', [AuthController::class, 'register']);

Route::group(['middleware' => ['jwt.verify']], function() {
    //Todo lo que este dentro de este grupo requiere verificación de usuario.
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('guardar', [BienesController::class, 'store']);
    Route::put('actualizar/{id}', [BienesController::class, 'update']);
    Route::delete('eliminar/{id}', [BienesController::class, 'destroy']);
    Route::get('detalle', [BienesController::class, 'detalleBienes']);
});
