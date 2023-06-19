<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Deuda;
use DateTime;
use Illuminate\Http\Request;

class DeudaController extends Controller
{
    public function pagoDeudaQr(Request $request){
        date_default_timezone_set('America/Manaus');
        $deuda = Deuda::where('id_deuda', $request->id_deuda)->first();
        if($deuda){
            $fechaHoraActual = new DateTime();

            $deuda->estado = "cancelada";
            $deuda->fecha_hora_pagado = $fechaHoraActual;  
            $deuda->tipo_pago = "QR";
            $deuda->save();
    
                return response([
                    'status' => '1',
                    'msg' => 'Pago con exito',
                ]);   
        }else{
            return response([
                'status' => '0',
                'msg' => 'Error',
            ],404);      
        }
    }

    public function pagoDeudaEfectivo(Request $request){
        date_default_timezone_set('America/Manaus');
        $deuda = Deuda::where('id_deuda', $request->id_deuda)->first();
        if($deuda){
            $fechaHoraActual = new DateTime();

            $deuda->estado = "cancelada";
            $deuda->fecha_hora_pagado = $fechaHoraActual;  
            $deuda->tipo_pago = "efectivo";
            $deuda->save();
    
                return response([
                    'status' => '1',
                    'msg' => 'Pago con exito',
                ]);   
        }else{
            return response([
                'status' => '0',
                'msg' => 'Error',
            ],404);      
        }
    }


    public function listDeudas()
    {
        $id_usuario = auth()->user()->id;
        $id_cliente = (Cliente::where('id_usuario', $id_usuario)->first())->id_cliente;
        $deudas = Deuda::where('id_cliente', $id_cliente)->where('estado','pendiente')->get();
    
        // Ocultar el campo "id_cliente" en cada vehículo
        $deudas->each(function ($deuda) {
            $deuda->makeHidden('estado');
            $deuda->makeHidden('fecha_hora_pagado');
            $deuda->makeHidden('tipo_pago');
        });
    
        return $deudas;
    }
}
