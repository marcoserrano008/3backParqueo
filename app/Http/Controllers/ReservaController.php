<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Models\Espacio;
use DateTime;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    public function createReserva(Request $request)
    {
        date_default_timezone_set('America/Manaus');
        $request->validate([
            'id_espacio' => 'required',
            'placa_vehiculo' => 'required',
            'reservada_desde_fecha' => 'required',
            'reservada_desde_hora' => 'required',
            'duracion_minutos' => 'required',
        ]);

        $id_espacio = $request->id_espacio;
        $id_usuario = auth()->user()->id;
        $reserva = new Reserva();
        $reserva->id_espacio = $id_espacio;

        $placa_vehiculo = $request->placa_vehiculo;

        // Convertir a mayúsculas
        $placa_vehiculo = strtoupper($placa_vehiculo);

        // Eliminar caracteres no alfanuméricos y espacios
        $placa_vehiculo = preg_replace('/[^A-Z0-9]/', '', $placa_vehiculo);
        $placa_vehiculo = str_replace(' ', '', $placa_vehiculo);
        $vehiculo = Vehiculo::where('placa', $placa_vehiculo)->first();

        if ($vehiculo) {
            $id_vehiculo = $vehiculo->id_vehiculo;
            $reserva->id_vehiculo = $id_vehiculo;
        } else {
            $reserva->placa_vehiculo = $placa_vehiculo;
        }

        //obtener la fecha y hora ingresados
        $reserva->reservada_desde_fecha = $request->reservada_desde_fecha;
        $reserva->reservada_desde_hora = $request->reservada_desde_hora;

        $reservada_desde_fecha = $request->reservada_desde_fecha;
        $reservada_desde_hora = $request->reservada_desde_hora;

        $fechaHoraReserva = new DateTime($reservada_desde_fecha . ' ' . $reservada_desde_hora);
        $duracionMinutos = $request->duracion_minutos;

        //sumar los minutos a la fecha y hora
        $fechaHoraFinReserva = clone $fechaHoraReserva;
        $fechaHoraFinReserva->modify('+' . $duracionMinutos . 'minutes');


        // Verificar si existe un choque de horarios
        $reservaChoque = Reserva::where('id_espacio', $id_espacio)
            ->where(function ($query) use ($reservada_desde_fecha, $fechaHoraReserva, $fechaHoraFinReserva) {
                $query->where(function ($query) use ($reservada_desde_fecha, $fechaHoraReserva, $fechaHoraFinReserva) {
                    $query->where('reservada_desde_fecha', $reservada_desde_fecha)
                        ->where(function ($query) use ($fechaHoraReserva, $fechaHoraFinReserva) {
                            $query->where(function ($query) use ($fechaHoraReserva, $fechaHoraFinReserva) {
                                $query->whereRaw("CONCAT(reservada_desde_fecha, ' ', reservada_desde_hora) <= ?", [$fechaHoraFinReserva->format('Y-m-d H:i:s')])
                                    ->whereRaw("CONCAT(reservada_hasta_fecha, ' ', reservada_hasta_hora) >= ?", [$fechaHoraReserva->format('Y-m-d H:i:s')]);
                            })
                                ->orWhere(function ($query) use ($fechaHoraReserva, $fechaHoraFinReserva) {
                                    $query->whereRaw("CONCAT(reservada_desde_fecha, ' ', reservada_desde_hora) <= ?", [$fechaHoraReserva->format('Y-m-d H:i:s')])
                                        ->whereRaw("CONCAT(reservada_hasta_fecha, ' ', reservada_hasta_hora) >= ?", [$fechaHoraReserva->format('Y-m-d H:i:s')]);
                                });
                        });
                })
                    ->orWhere(function ($query) use ($reservada_desde_fecha, $fechaHoraReserva, $fechaHoraFinReserva) {
                        $query->whereRaw("CONCAT(reservada_desde_fecha, ' ', reservada_desde_hora) <= ?", [$fechaHoraFinReserva->format('Y-m-d H:i:s')])
                            ->whereRaw("CONCAT(reservada_hasta_fecha, ' ', reservada_hasta_hora) >= ?", [$fechaHoraReserva->format('Y-m-d H:i:s')]);
                    });
            })
            ->first();



        if ($reservaChoque) {
            return response([
                'status' => '0',
                'msg' => 'Choque de horarios con la reserva existente',
                'reserva_choque' => $reservaChoque,
            ]);
        }


        //insetar resto de los datos
        $reserva->reservada_desde_fecha = $reservada_desde_fecha;
        $reserva->reservada_desde_hora = $reservada_desde_hora;
        $reserva->reservada_hasta_fecha = $fechaHoraFinReserva->format('Y-m-d');
        $reserva->reservada_hasta_hora = $fechaHoraFinReserva->format('H:i:s');

        $fechaHoraActual = new DateTime();
        $fechaCreada = $fechaHoraActual->format('Y-m-d');
        $horaCreada = $fechaHoraActual->format('H:i:s');

        $reserva->fecha_creada = $fechaCreada;
        $reserva->hora_creada = $horaCreada;
        $reserva->id_usuario = $id_usuario;

        //Añadir los Gaps
        //G1->(-15minutos)  G2->(-6horas=-360minutos)
        $G1 = 15;
        $G2 = 360;
        $fechaHoraReserva = new DateTime($reservada_desde_fecha . ' ' . $reservada_desde_hora);
 //sumar los minutos a la fecha y hora
        $fechaHoraG1 = clone $fechaHoraReserva;
        $fechaHoraG1->modify('-' . $G1 . 'minutes');
        $reserva->reservada_desde_fechaG1 = $fechaHoraG1->format('Y-m-d');
        $reserva->reservada_desde_horaG1 = $fechaHoraG1->format('H:i:s');

        $fechaHoraG2 = clone $fechaHoraReserva;
        $fechaHoraG2->modify('-' . $G2 . 'minutes');
        $reserva->reservada_desde_fechaG2 = $fechaHoraG2->format('Y-m-d');
        $reserva->reservada_desde_horaG2 = $fechaHoraG2->format('H:i:s');

        $reserva->save();

        return response([
            'status' => '1',
            'msg' => 'Reserva satisfactoria',
        ]);
    }

    public function obtenerEspacio()
    {
        date_default_timezone_set('America/Manaus');
        $fechaHoraActual = new DateTime();
        $fechaActual = $fechaHoraActual->format('Y-m-d');
        $horaActual = $fechaHoraActual->format('H:i:s');
    
        $espacios = ['5A', '6A', '7A', '8A', '9A'];
        $espacioDisponible = null;
    
        foreach ($espacios as $espacio) {
            $espacioLibre = Espacio::where('id_espacio', $espacio)
            ->where('estado', 'libre')
            ->exists();

            
            if($espacioLibre){
                $reserva = Reserva::where('id_espacio', $espacio)
                ->where('reservada_desde_fecha', '<=', $fechaActual)
                ->where('reservada_desde_hora', '<=', $horaActual)
                ->where('reservada_hasta_fecha', '>=', $fechaActual)
                ->where('reservada_hasta_hora', '>=', $horaActual)
                ->first();
    
                if (!$reserva) {
                    $espacioDisponible = $espacio;
                    return $espacioDisponible;
                }
            }

        }
    
        if (!$espacioDisponible) {
            $espacioDisponible = Espacio::where('estado', 'libre')
                ->whereNotExists(function ($query) use ($fechaActual, $horaActual) {
                    $query->select(DB::raw(1))
                        ->from('Reservas')
                        ->whereColumn('Reservas.id_espacio', 'Espacios.id_espacio')
                        ->where('reservada_desde_fecha', '<=', $fechaActual)
                        ->where('reservada_desde_hora', '<=', $horaActual)
                        ->where('reservada_hasta_fecha', '>=', $fechaActual)
                        ->where('reservada_hasta_hora', '>=', $horaActual)
                        ->whereBetween('reservada_desde_fecha', [$fechaActual, date('Y-m-d', strtotime('+1 day', strtotime($fechaActual)))]);
                })
                ->orderBy('id_espacio')
                ->first();
    
            if (!$espacioDisponible) {
                $espacioDisponible = 'C7';
            }
        }
    
        return $espacioDisponible->id_espacio;
    }
    

    public function listActiveReservas()
    {
        $id_usuario = auth()->user()->id;

        $fechaHoraActual = new DateTime();    
        $reservas = Reserva::where('id_usuario', $id_usuario)
        ->where(function ($query) use ($fechaHoraActual){
              $query-> whereRaw("CONCAT(reservada_desde_fecha, ' ', reservada_desde_hora) >= ?", [$fechaHoraActual->format('Y-m-d H:i:s')]);  
            })->get();
        
        $reservas->each(function ($reserva){
            $reserva->makeHidden('id_vehiculo');
            $reserva->makeHidden('fecha_creada');
            $reserva->makeHidden('hora_creada');
            $reserva->makeHidden('id_usuario');
            $idVehiculo = $reserva->id_vehiculo;
            if($idVehiculo){
                $vehiculo = Vehiculo::where('id_vehiculo', $idVehiculo)->first();
                $placaVehiculo = $vehiculo->placa;
            }else{
                $placaVehiculo = $reserva->placa_vehiculo;
            }
            $reserva->placa_vehiculo = $placaVehiculo;

        });

        return $reservas;
    }

    public function listExpiredReservas()
    {
        $id_usuario = auth()->user()->id;

        $fechaHoraActual = new DateTime();    
        $reservas = Reserva::where('id_usuario', $id_usuario)
        ->where(function ($query) use ($fechaHoraActual){
              $query-> whereRaw("CONCAT(reservada_desde_fecha, ' ', reservada_desde_hora) <= ?", [$fechaHoraActual->format('Y-m-d H:i:s')]);  
            })->get();
        
        $reservas->each(function ($reserva){
            $reserva->makeHidden('id_vehiculo');
            $reserva->makeHidden('fecha_creada');
            $reserva->makeHidden('hora_creada');
            $reserva->makeHidden('id_usuario');
            $idVehiculo = $reserva->id_vehiculo;
            if($idVehiculo){
                $vehiculo = Vehiculo::where('id_vehiculo', $idVehiculo)->first();
                $placaVehiculo = $vehiculo->placa;
            }else{
                $placaVehiculo = $reserva->placa_vehiculo;
            }
            $reserva->placa_vehiculo = $placaVehiculo;

        });    

        return $reservas;
    }

    public function listReservas()
    {
        $id_usuario = auth()->user()->id; 
        $reservas = Reserva::where('id_usuario', $id_usuario)->get();     
        
        $reservas->each(function ($reserva){
            $reserva->makeHidden('id_vehiculo');
            $reserva->makeHidden('fecha_creada');
            $reserva->makeHidden('hora_creada');
            $reserva->makeHidden('id_usuario');
            $idVehiculo = $reserva->id_vehiculo;
            if($idVehiculo){
                $vehiculo = Vehiculo::where('id_vehiculo', $idVehiculo)->first();
                $placaVehiculo = $vehiculo->placa;
            }else{
                $placaVehiculo = $reserva->placa_vehiculo;
            }
            $reserva->placa_vehiculo = $placaVehiculo;

        });

        return $reservas;       
    }



    public function showReservaId($idReserva)
    {
        $id_usuario = auth()->user()->id;
        $reserva = Reserva::where(['id_usuario'=>$id_usuario, 'id_reserva' => $idReserva])->first();
        
        $idVehiculo = $reserva->id_vehiculo;
        if($idVehiculo){
            $vehiculo = Vehiculo::where('id_vehiculo', $idVehiculo)->first();
            $placaVehiculo = $vehiculo->placa;
        }else{
            $placaVehiculo = $reserva->placa_vehiculo;
        }

        $resultado = [
            "id_reserva" => $reserva->id_reserva,
            "id_espacio" => $reserva->id_espacio,
            "reservada_desde_fecha" => $reserva->reservada_desde_fecha,
            "reservada_desde_hora" => $reserva->reservada_desde_hora,
            "reservada_hasta_fecha" => $reserva->reservada_hasta_fecha,
            "reservada_hasta_hora" => $reserva->reservada_hasta_hora,
            "placa_vehiculo" => $placaVehiculo,
        ];
        return $resultado;

    }

    public function showReservasPlaca($placa)
    {

        $placa = strtoupper($placa);

        // Eliminar caracteres no alfanuméricos y espacios
        $placa = preg_replace('/[^A-Z0-9]/', '', $placa);
        $placa = str_replace(' ', '', $placa);

        $id_usuario = auth()->user()->id;
    
        // Obtener las reservas de vehículos registrados con la placa correspondiente
        $reservasVehiculoRegistrado = Reserva::where('id_usuario', $id_usuario)
            ->whereHas('vehiculo', function ($query) use ($placa) {
                $query->where('placa', $placa);
            })
            ->get();
    
        // Obtener las reservas de vehículos no registrados con la placa correspondiente
        $reservasVehiculoNoRegistrado = Reserva::where('id_usuario', $id_usuario)
            ->whereNull('id_vehiculo')
            ->where('placa_vehiculo', $placa)
            ->get();
    
        // Combinar las reservas de ambos casos
        $reservas = $reservasVehiculoRegistrado->concat($reservasVehiculoNoRegistrado);
    
        $reservas->each(function ($reserva){
            $reserva->makeHidden('id_vehiculo');
            $reserva->makeHidden('fecha_creada');
            $reserva->makeHidden('hora_creada');
            $reserva->makeHidden('id_usuario');
            $idVehiculo = $reserva->id_vehiculo;
            if($idVehiculo){
                $vehiculo = Vehiculo::where('id_vehiculo', $idVehiculo)->first();
                $placaVehiculo = $vehiculo->placa;
            }else{
                $placaVehiculo = $reserva->placa_vehiculo;
            }
            $reserva->placa_vehiculo = $placaVehiculo;

        });

        return $reservas;
    }


    public function updateReserva(Request $request, $idReserva)
    {

    }

    public function deleteReserva($idReserva)
    {
        $id_usuario = auth()->user()->id;
        
        if(Reserva::where(['id_usuario'=>$id_usuario, 'id_reserva'=>$idReserva])->exists()){
            $reserva = Reserva::where(['id_usuario'=>$id_usuario, 'id_reserva'=>$idReserva])->first();
            $reserva->delete();
        }else{
            return response([
                'status' => '0',
                'msg' => 'Error',
            ],404);
        }
    }

}