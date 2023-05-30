<?php

namespace App\Http\Controllers;

use App\Models\Ingreso;
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
            ->where('reservada_hasta_fechaG1', '>=', $fechaIngreso)
            ->where('reservada_hasta_horaG1', '>=', $horaIngreso)
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

}
