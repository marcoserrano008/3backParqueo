<?php

namespace App\Http\Controllers;

use App\Models\Ingreso;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use App\Models\Espacio;

class espacioController extends Controller
{
    public function listEspacios()
    {
        $espacios = Espacio::get();
        $espacios->each(function ($espacio) {
            $espacio->makeHidden('bloque');
        });
        return $espacios;
    }

    function estadoEspacio($id)
    {

        $espacio = Espacio::where('id_espacio', $id)->first();
        $idIngreso = $espacio -> id_ingreso;
        if($idIngreso){
            $datosIngreso = Ingreso::where('id_ingreso', $idIngreso)->first();
            $resultado = [
                "id_espacio" => $espacio->id_espacio,
                "hora" => $datosIngreso->hora_ingreso,
                "fecha" =>  $datosIngreso -> fecha_ingreso,
                "estado" => $espacio->estado,
            ];
            if(!$datosIngreso->id_vehiculo){
                    $resultado['placa_vehiculo'] = $datosIngreso->placa_vehiculo; 
            }else{
                $vehiculo = Vehiculo::where('id_vehiculo', $datosIngreso->id_vehiculo);
                $resultado['placa_vehiculo'] = $vehiculo->placa_vechiculo;
            }
        }else{
            $resultado = [
                "id_espacio" => $espacio->id_espacio,
                "hora" => null,
                "fecha" =>  null,
                "estado" => $espacio->estado,
                "placa_vehiculo" => null,
            ];
        }
        
        return $resultado;
        
    }


    
}
