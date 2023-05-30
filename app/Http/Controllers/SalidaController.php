<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;
use DateTime;
use App\Models\Salida;
use App\Models\Espacio;
use App\Models\Ingreso;

use Illuminate\Http\Request;

class SalidaController extends Controller
{
    public function registrarSalidaParqueo(Request $request)
    {
        date_default_timezone_set('America/Manaus');
        $request->validate([
            'placa' => 'required',
        ]);

        $placa = $request->placa;
        $placa = strtoupper($placa);
        $placa = str_replace([' ', '-'], '', $placa);


        $vehiculo = Vehiculo::where('placa', $placa)->first();

        if ($vehiculo) {
            $idVehiculo = $vehiculo->id_vehiculo;
            $registro = Salida::where('id_vehiculo', $idVehiculo)
            ->whereNull('fecha_salida')
            ->orderBy('id_ingreso', 'desc')
            ->first();            

        } else {

            $registro = Salida::where('placa_vehiculo', $placa)
                ->whereNull('fecha_salida')
                ->orderBy('id_ingreso', 'desc')
                ->first();
        }

        if ($registro) {
            $fechaHoraActual = new DateTime();
            $fechaSalida = $fechaHoraActual->format('Y-m-d');
            $horaSalida = $fechaHoraActual->format('H:i:s');

            $registro->hora_salida = $horaSalida;
            $registro->fecha_salida = $fechaSalida;

            $registro->save();

            $ingreso = Ingreso::where('id_ingreso', $registro->id_ingreso)->first();
            $espacioLiberado = Espacio::where('id_espacio', $ingreso->id_espacio)->first();
            $espacioLiberado->estado = 'libre';
            $espacioLiberado->id_ingreso = null; 
            $espacioLiberado->save();

            
            return response()->json(['id_salida' => $registro->id_salida, 'id_espacio' => $ingreso->id_espacio]);
        } else {
            return response()->json(['error' => 'No existe ingreso']);
        }


    }






}