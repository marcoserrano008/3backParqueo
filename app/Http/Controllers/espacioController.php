<?php

namespace App\Http\Controllers;

use App\Models\Ingreso;
use App\Models\Vehiculo;
use DateTime;
use Illuminate\Http\Request;
use App\Models\Espacio;
use Illuminate\Support\Facades\DB;

class espacioController extends Controller
{
    public function listEspacios()
    {
        date_default_timezone_set('America/Manaus');
    
        $now = new DateTime();
    
        $espacios = Espacio::with(['reservas' => function($query) use ($now) {
            $query->whereRaw("CONCAT(reservada_desde_fecha, ' ', reservada_desde_hora) <= ?", [$now->format('Y-m-d H:i:s')])
                  ->whereRaw("CONCAT(reservada_hasta_fecha, ' ', reservada_hasta_hora) >= ?", [$now->format('Y-m-d H:i:s')]);
        }])->get();
    
        $espacios->each(function ($espacio) use ($now) {
            $reserva = $espacio->reservas->first();
            if ($espacio->estado == 'ocupado') {
                $espacio->estado = 'ocupado';
            } elseif ($espacio->estado == 'deshabilitado') {
                $espacio->estado = 'deshabilitado';
            } elseif ($reserva && new DateTime($reserva->reservada_desde_fecha . ' ' . $reserva->reservada_desde_hora) <= $now && new DateTime($reserva->reservada_hasta_fecha . ' ' . $reserva->reservada_hasta_hora) >= $now) {
                $espacio->estado = 'reservado';
            } else {
                $espacio->estado = 'libre';
            }
            $espacio->makeHidden(['bloque', 'reservas']);
        });
    
        return $espacios;
    }
    

    public function countEspacios()
{
    $espacios = $this->listEspacios();  // reusamos el método que ya tienes

    // Filtra para excluir los espacios deshabilitados
    $espaciosHabilitados = $espacios->where('estado', '!=', 'deshabilitado');

    $totalEspacios = count($espaciosHabilitados);  // cuenta total de espacios
    $espaciosLibres = $espaciosHabilitados->where('estado', 'libre')->count();  // cuenta de espacios libres
    $espaciosOcupados = $espaciosHabilitados->where('estado', 'ocupado')->count();  // cuenta de espacios ocupados

    // Retorna un JSON con la información requerida
    return response()->json([
        'totalEspacios' => $totalEspacios,
        'espaciosLibres' => $espaciosLibres,
        'espaciosOcupados' => $espaciosOcupados,
    ]);
}

    function estadoEspacio($id)
    {

        $espacio = Espacio::where('id_espacio', $id)->first();
        $idIngreso = $espacio->id_ingreso;
        if ($idIngreso) {
            $datosIngreso = Ingreso::where('id_ingreso', $idIngreso)->first();
            $resultado = [
                "id_espacio" => $espacio->id_espacio,
                "hora" => $datosIngreso->hora_ingreso,
                "fecha" => $datosIngreso->fecha_ingreso,
                "estado" => $espacio->estado,
            ];
            if (!$datosIngreso->id_vehiculo) {
                $resultado['placa_vehiculo'] = $datosIngreso->placa_vehiculo;
            } else {
                $vehiculo = Vehiculo::where('id_vehiculo', $datosIngreso->id_vehiculo);
                $resultado['placa_vehiculo'] = $vehiculo->placa_vechiculo;
            }
        } else {
            $resultado = [
                "id_espacio" => $espacio->id_espacio,
                "hora" => null,
                "fecha" => null,
                "estado" => $espacio->estado,
                "placa_vehiculo" => null,
            ];
        }

        return $resultado;

    }

    function espaciosLibres()
    {
        date_default_timezone_set('America/Manaus');
        $fechaHoraActual = new DateTime();
        $fechaActual = $fechaHoraActual->format('Y-m-d');
        $horaActual = $fechaHoraActual->format('H:i:s');

        $espaciosDisponibles = null;
        $espaciosDisponibles = Espacio::where('estado', 'libre')
            ->whereNotExists(function ($query) use ($fechaActual, $horaActual) {
                $query->select(DB::raw(1))
                    ->from('Reservas')
                    ->whereColumn('Reservas.id_espacio', 'Espacios.id_espacio')
                    ->where('reservada_desde_fechaG2', '<=', $fechaActual)
                    ->where('reservada_desde_horaG2', '<=', $horaActual)
                    ->where('reservada_hasta_fecha', '>=', $fechaActual)
                    ->where('reservada_hasta_hora', '>=', $horaActual);

            })
            ->orderBy('id_espacio')
            ->get();

        // if (!$espaciosDisponibles) {
        //     $espaciosDisponibles = Espacio::where('estado', 'libre')
        //     ->whereNotExists(function ($query) use ($fechaActual, $horaActual) {
        //         $query->select(DB::raw(1))
        //             ->from('Reservas')
        //             ->whereColumn('Reservas.id_espacio', 'Espacios.id_espacio')
        //             ->where('reservada_desde_fecha', '<=', $fechaActual)
        //             ->where('reservada_desde_hora', '<=', $horaActual)
        //             ->where('reservada_hasta_fecha', '>=', $fechaActual)
        //             ->where('reservada_hasta_hora', '>=', $horaActual);
        //     })
        //     ->orderBy('id_espacio')
        //     ->get();
        // }

        return $espaciosDisponibles->pluck('id_espacio')->all();
    }

    function modificarEspacio (Request $request)
    {
        $accion = $request->accion;
        $id_espacio = $request->espacio;
        if($accion == 'Eliminar'){
            $espacio = Espacio::where('id_espacio',$id_espacio)->first();
            $espacio->estado = 'deshabilitado';
            $espacio->save();
        }elseif($accion == 'Mostrar'){
            $espacio = Espacio::where('id_espacio',$id_espacio)->first();
            $espacio->estado = 'libre';
            $espacio->save();
        }
        return ([
            "msg" => "Modificado con exito"
        ]);

    }
}