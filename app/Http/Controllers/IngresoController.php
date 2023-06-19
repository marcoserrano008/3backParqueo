<?php

namespace App\Http\Controllers;

use App\Models\Cobro;
use App\Models\Ingreso;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Vehiculo;
use App\Models\Espacio;
use App\Models\Salida;
use App\Models\Reserva;
use DateTime;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ReservaController;

class IngresoController extends Controller
{
    public function registrarIngresosPlaca(Request $request)
    {
        date_default_timezone_set('America/Manaus');
        $request->validate([
            'placa' => 'required',
        ]);
    
        $placa = $request->placa;
        $placa = strtoupper($placa);
        $placa = str_replace([' ', '-'], '', $placa);
    
        $vehiculo = Vehiculo::where('placa', $placa)->first();
    
        $ingreso = new Ingreso();
    
        if ($vehiculo) {
            $id_vehiculo = $vehiculo->id_vehiculo;
            $ingreso->id_vehiculo = $id_vehiculo;

        } else {
            $ingreso->placa_vehiculo = $placa;
        }
    
        $fechaHoraActual = new DateTime();
        $fechaIngreso = $fechaHoraActual->format('Y-m-d');
        $horaIngreso = $fechaHoraActual->format('H:i:s');
    
        $ingreso->hora_ingreso = $horaIngreso;
        $ingreso->fecha_ingreso = $fechaIngreso;
    

        //Ver si existe una reserva media hora antes
        if ($vehiculo) {
            $id_vehiculo = $vehiculo->id_vehiculo;
            $reserva = Reserva::where('id_vehiculo', $id_vehiculo)
            ->where('reservada_desde_fechaG1', '<=', $fechaIngreso)
            ->where('reservada_desde_horaG1', '<=', $horaIngreso)
            ->where('reservada_hasta_fecha', '>=', $fechaIngreso)
            ->where('reservada_hasta_hora', '>=', $horaIngreso)
            ->first();            
            
        } else {
            $reserva = Reserva::where('placa_vehiculo', $placa)
            ->where('reservada_desde_fechaG1', '<=', $fechaIngreso)
            ->where('reservada_desde_horaG1', '<=', $horaIngreso)
            ->where('reservada_hasta_fecha', '>=', $fechaIngreso)
            ->where('reservada_hasta_hora', '>=', $horaIngreso)
            ->first();
        }

        if ($reserva) {
            $ingreso->id_reserva = $reserva->id_reserva;
            $espacio=$reserva->id_espacio;
            $ingreso->id_espacio = $espacio;
            $ingreso->save();
            $ingreso->refresh();
            $vehiculo = Vehiculo::where('placa', $placa)->first();
            $salida = new Salida();
            if ($vehiculo) {
                $id_vehiculo = $vehiculo->id_vehiculo;
                $salida->id_vehiculo = $id_vehiculo;
            } else {
                $salida->placa_vehiculo = $placa;
            }
            $salida->id_ingreso = $ingreso->id_ingreso;
            $salida->save();
            $espacioOcupado = Espacio::where('id_espacio', $espacio)->first();
            $espacioOcupado->estado = 'ocupado';
            $espacioOcupado->id_ingreso = $ingreso->id_ingreso;
            $espacioOcupado->save();

            return response()->json(['id_ingreso' => $ingreso->id_ingreso, 'id_espacio' => $ingreso->id_espacio]);
            
        }else{
            $espacioLibre = null;
            try {
                DB::transaction(function () use ($ingreso, $placa, &$espacioLibre) {
                    // // Obtén los espacios libres de la tabla Espacios que se encuentren en el arreglo
                    // $espaciosLibres = Espacio::whereIn('id_espacio', ['8A', '8B'])
                    //     ->where('estado', 'libre')
                    //     ->get();
        
                    // if ($espaciosLibres->isNotEmpty()) {
                    //     // Si hay espacios libres, toma el primer espacio libre
                    //     $espacioLibre = $espaciosLibres->first();
                    //     $espacioLibre->estado = 'ocupado';
                    //     $espacioLibre->save();
                    // } else {
                    //     // Si no hay espacios libres, obtén el primer espacio libre de la tabla Espacios
                    //     $primerEspacioLibre = Espacio::where('estado', 'libre')->first();
                    //     $primerEspacioLibre->estado = 'ocupado';
                    //     $primerEspacioLibre->save();
                    //     $espacioLibre = $primerEspacioLibre;
                    // }
        

                    $reservaController = new ReservaController();
                    $espacioDisponible = $reservaController->obtenerEspacio();
                    
                    $espacio = Espacio::where('id_espacio',$espacioDisponible)->first();
                    $espacio->estado = 'ocupado';
                    

                    // Asigna el ID del espacio al ingreso
                    $ingreso->id_espacio = $espacioDisponible;
                    // Guarda el modelo de Ingreso
                    $ingreso->save();    
                    //Crear una salida para este ingreso
                    $ingreso->refresh();
                    $espacio->id_ingreso = $ingreso->id_ingreso;
                    $espacio->save();
                    $vehiculo = Vehiculo::where('placa', $placa)->first();
                    $salida = new Salida();
                    if ($vehiculo) {
                        $id_vehiculo = $vehiculo->id_vehiculo;
                        $salida->id_vehiculo = $id_vehiculo;
                    } else {
                        $salida->placa_vehiculo = $placa;
                    }

                    $salida->id_ingreso = $ingreso->id_ingreso;
                    $salida->save();

                });
                return response()->json(['id_ingreso' => $ingreso->id_ingreso, 'id_espacio' => $ingreso->id_espacio]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()]);
            }
        }


    }
    

    public function verIngresosParqueoPlaca($placa){

    }

    public function verIngresosParqueoTodos(){
        $primerEspacioLibre = Espacio::where('estado', 'libre')->first();
        return $primerEspacioLibre;        
    }

    public function verEspacios(){
        $primerEspacioLibre = Espacio::where('estado', 'libre')->get();
        return $primerEspacioLibre;        
    }
    public function obtenerIngresosPorFecha(Request $request)
    {
        $desde_fecha = $request->input('desde_fecha');
        $hasta_fecha = $request->input('hasta_fecha');
    
        $ingresos = Ingreso::with(['vehiculo', 'salida'])
            ->whereBetween('fecha_ingreso', [$desde_fecha, $hasta_fecha])
            ->get()
            ->map(function ($ingreso) {
                return [
                    'id_ingreso' => $ingreso->id_ingreso,
                    'hora_ingreso' => $ingreso->hora_ingreso,
                    'fecha_ingreso' => $ingreso->fecha_ingreso,
                    'hora_salida' => optional($ingreso->salida)->hora_salida ?? '-',
                    'fecha_salida' => optional($ingreso->salida)->fecha_salida ?? '-',
                    'id_espacio' => $ingreso->id_espacio,
                    'placa' => $ingreso->placa_vehiculo ?? $ingreso->vehiculo->placa,

                ];
            });
    
        return response()->json($ingresos);
    }

    public function obtenerIngresosPorFechaEspacio(Request $request)
    {
        $desde_fecha = $request->input('desde_fecha');
        $hasta_fecha = $request->input('hasta_fecha');
        $id_espacio = $request->input('id_espacio');
    
        $ingresos = Ingreso::with(['vehiculo', 'salida'])
            ->whereBetween('fecha_ingreso', [$desde_fecha, $hasta_fecha])->where('id_espacio',$id_espacio)
            ->get()
            ->map(function ($ingreso) {
                return [
                    'id_ingreso' => $ingreso->id_ingreso,
                    'hora_ingreso' => $ingreso->hora_ingreso,
                    'fecha_ingreso' => $ingreso->fecha_ingreso,
                    'hora_salida' => optional($ingreso->salida)->hora_salida ?? '-',
                    'fecha_salida' => optional($ingreso->salida)->fecha_salida ?? '-',
                    // 'id_espacio' => $ingreso->id_espacio,
                    'placa' => $ingreso->placa_vehiculo ?? $ingreso->vehiculo->placa,

                    // 'id_guardia' => $ingreso->id_guardia,
                    // 'id_bloque' => $ingreso->id_bloque,
                    // 'id_reserva' => $ingreso->id_reserva,
                    

                ];
            });
    
        return response()->json($ingresos);
    }

    public function obtenerCobrosPorFecha (Request $request) {
        $desde_fecha = $request->input('desde_fecha');
        $hasta_fecha = $request->input('hasta_fecha');
    
        $cobros = Cobro::whereBetween('fecha', [$desde_fecha, $hasta_fecha])->get();
        $total = Cobro::whereBetween('fecha', [$desde_fecha, $hasta_fecha])->sum('monto');
    
        return response()->json([
            "data" => $cobros,
            "total" => $total
        ]);
    }

    public function obtenerCobrosPorFechaEspacio (Request $request){
        $desde_fecha = $request->input('desde_fecha');
        $hasta_fecha = $request->input('hasta_fecha');
        $id_espacio = $request->input('id_espacio');

        $cobros = Cobro::whereBetween('fecha', [$desde_fecha, $hasta_fecha])
        ->where('id_espacio',$id_espacio)->get();
        $total = Cobro::whereBetween('fecha', [$desde_fecha, $hasta_fecha])
        ->where('id_espacio',$id_espacio)->sum('monto');
    
        return response()->json([
            "data" => $cobros,
            "total" => $total
        ]);
    }

    public function pagoEfectivo (Request $request) {
        date_default_timezone_set('America/Manaus');

        $id_usuario = auth()->user()->id;

        $datosUsuario = User::where('id', $id_usuario)->first();
        $nombre = $datosUsuario->name . ' ' .$datosUsuario->apellido_paterno . ' ' . $datosUsuario->apellido_materno;

        $fechaHoraActual = new DateTime();
        $fechaCreada = $fechaHoraActual->format('Y-m-d');
        $horaCreada = $fechaHoraActual->format('H:i:s');

        $cobro = new Cobro();
        $cobro->placa_vehiculo = $request->placa;
        $cobro->fecha = $fechaCreada;
        $cobro->hora = $horaCreada;
        $cobro->origen = 'salida';
        $cobro->metodo = 'Efectivo';
        $cobro->id_origen = $request->id_salida;
        $cobro->id_usuario = $nombre;
        $cobro->monto = $request->monto;
        $cobro->id_espacio = $request->id_espacio;
        $cobro->save();

        return response([
            'status' => '1',
            'msg' => 'reserva pagada',
        ]);
    }

    public function pagoQr (Request $request) {
        date_default_timezone_set('America/Manaus');

        $id_usuario = auth()->user()->id;

        $datosUsuario = User::where('id', $id_usuario)->first();
        $nombre = $datosUsuario->name . ' ' .$datosUsuario->apellido_paterno . ' ' . $datosUsuario->apellido_materno;

        $fechaHoraActual = new DateTime();
        $fechaCreada = $fechaHoraActual->format('Y-m-d');
        $horaCreada = $fechaHoraActual->format('H:i:s');

        $cobro = new Cobro();
        $cobro->placa_vehiculo = $request->placa;
        $cobro->fecha = $fechaCreada;
        $cobro->hora = $horaCreada;
        $cobro->origen = 'salida';
        $cobro->metodo = 'QR';
        $cobro->id_origen = $request->id_salida;
        $cobro->id_usuario = $nombre;
        $cobro->monto = $request->monto;
        $cobro->id_espacio = $request->id_espacio;
        $cobro->save();

        return response([
            'status' => '1',
            'msg' => 'reserva pagada',
        ]);
    }
}
