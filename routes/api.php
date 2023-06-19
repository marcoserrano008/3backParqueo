<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\DeudaController;
use App\Http\Controllers\WhatsappController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\espacioController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\SalidaController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\IngresoController;

use App\Http\Controllers\PostController;
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
    
    Route::post('create-reserva', [ReservaController::class, 'createReserva']);
    Route::get('list-active-reservas', [ReservaController::class, 'listActiveReservas']);
    Route::get('list-expired-reservas', [ReservaController::class, 'listExpiredReservas']);
    Route::get('list-reservas', [ReservaController::class, 'listReservas']);

    Route::get('list-all-reservas', [ReservaController::class, 'listAllReservas']);

    Route::get('show-reserva/{id}', [ReservaController::class, 'showReservaId']);
    Route::put('update-reserva/{id}', [ReservaController::class, 'updateReserva']);
    Route::delete('delete-reserva/{id}', [ReservaController::class, 'deleteReserva']);
    Route::get('list-reservas-placa/{placa}', [ReservaController::class, 'showReservasPlaca']);

    Route::post('verificar-reserva-placa', [ReservaController::class, 'verificarReserva']);

    //Rutas para los los ingresos al parqueo
    Route::post('registrar-ingreso', [IngresoController::class, 'registrarIngresosPlaca']);
    Route::get('ver-ingresos-placa/{placa}', [IngresoController::class, 'verIngresosParqueoPlaca']);
    Route::get('ver-ingresos-todos', [IngresoController::class, 'verIngresosParqueoTodos']);

    

    //Ruta para registrar la salida del parqueo
    Route::post('registrar-salida', [SalidaController::class, 'registrarSalidaParqueo']);
    
    //rutas para ver los espacios
    Route::get('ver-espacios', [espacioController::class, 'listEspacios']);
    Route::get('espacio/{id}',[espacioController::class, 'estadoEspacio']);
    Route::get('espacios-libres',[espacioController::class, 'espaciosLibres']);
    Route::get('obtenerEspacio', [ReservaController::class, 'obtenerEspacio']);


    //DEUDAS
    Route::get('ver-deudas', [DeudaController::class, 'listDeudas']);
    
    //para pagar deuda
    Route::post('pagar-QR', [IngresoController::class, 'pagoQr']);
    Route::post('pagar-efectivo', [IngresoController::class, 'pagoEfectivo']);

    //para pagar una reserva
    
    Route::post('pagar-reserva', [ReservaController::class, 'pagarReserva']);

    //Subir comunicados
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts', [PostController::class, 'index']);

    //Reportes
    Route::post('ingresos-por-fecha', [IngresoController::class, 'obtenerIngresosPorFecha']);
    Route::post('ingresos-por-fecha-espacio', [IngresoController::class, 'obtenerIngresosPorFechaEspacio']);

    Route::post('cobros-por-fecha', [IngresoController::class, 'obtenerCobrosPorFecha']);
    Route::post('cobros-por-fecha-espacio', [IngresoController::class, 'obtenerCobrosPorFechaEspacio']);

    Route::get('/users/{start_date}/{end_date}', [UserController::class, 'getClientsByDate']);

    Route::post('reservas-por-fecha', [ReservaController::class, 'obtenerReservasPorFecha']);
    Route::post('reservas-por-fecha-espacio', [ReservaController::class, 'obtenerReservasPorFechaEspacio']);

    //Modificar Espacios
    Route::post('modificar-espacio', [espacioController::class, 'modificarEspacio']);
    //Mostrar espacios
    Route::get('dato-espacios',[espacioController::class, 'countEspacios']);

    //Posts
    Route::delete('delete-post/{id}', [PostController::class, 'deletePost']);
    Route::get('list-all-posts', [PostController::class, 'listAllPosts']);

    Route::get('list-all-usuarios', [UserController::class, 'listAllUsers']);

    Route::post('enviar-mesaje', [WhatsappController::class, 'sendMessage']);
});



Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
