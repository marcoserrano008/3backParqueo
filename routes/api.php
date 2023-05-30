<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\espacioController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\SalidaController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\IngresoController;


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

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {return $request->user();});
    //ver usuarios
    Route::apiResource('/users', UserController::class);
    //ver rol
    Route::get('rol', [AuthController::class,'getRol']);

    //Rutas para los vehiculos
    Route::post('create-vehiculo', [VehiculoController::class, 'createVehiculo']);
    Route::get('list-vehiculo', [VehiculoController::class, 'listVehiculo']);
    Route::get('show-vehiculo/{id}', [VehiculoController::class, 'showVehiculo']);
    Route::put('update-vehiculo/{id}', [VehiculoController::class, 'updateVehiculo']);
    // Route::delete('delete-vehiculo/{id}', [VehiculoController::class, 'deleteVehiculo']);
    Route::put('delete-vehiculo/{id}', [VehiculoController::class, 'deleteVehiculo']);

    //Rutas para las reservas
    Route::get('obtenerEspacio', [ReservaController::class, 'obtenerEspacio']);
    Route::post('create-reserva', [ReservaController::class, 'createReserva']);
    Route::get('list-active-reservas', [ReservaController::class, 'listActiveReservas']);
    Route::get('list-expired-reservas', [ReservaController::class, 'listExpiredReservas']);
    Route::get('list-reservas', [ReservaController::class, 'listReservas']);
    Route::get('show-reserva/{id}', [ReservaController::class, 'showReservaId']);
    Route::put('update-reserva/{id}', [ReservaController::class, 'updateReserva']);
    Route::delete('delete-reserva/{id}', [ReservaController::class, 'deleteReserva']);

    Route::get('list-reservas-placa/{placa}', [ReservaController::class, 'showReservasPlaca']);

    //Rutas para los los ingresos al parqueo
    Route::post('registrar-ingreso', [IngresoController::class, 'registrarIngresosPlaca']);
    Route::get('ver-ingresos-placa/{placa}', [IngresoController::class, 'verIngresosParqueoPlaca']);
    Route::get('ver-ingresos-todos', [IngresoController::class, 'verIngresosParqueoTodos']);

    

    //Ruta para registrar la salida del parqueo
    Route::post('registrar-salida', [SalidaController::class, 'registrarSalidaParqueo']);
    
    //rutas para ver los espacios
    Route::get('ver-espacios', [espacioController::class, 'listEspacios']);
    Route::get('espacio/{id}',[espacioController::class, 'estadoEspacio']);

});



Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
