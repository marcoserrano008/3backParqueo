<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use DateTime;
use App\Models\Salida;
use App\Models\Espacio;
use App\Models\Ingreso;
use App\Models\Deuda;

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

            $fechaHoraIngreso = new DateTime($ingreso->fecha_ingreso . ' ' . $ingreso->hora_ingreso);
            $fechaHoraSalida = new DateTime($registro->fecha_salida . ' ' . $registro->hora_salida);

            $intervalo = $fechaHoraIngreso->diff($fechaHoraSalida);
            $horasTranscurridas = $intervalo->format('%H:%I:%S');

            $SalidaControler = new SalidaController();


            //verificar si existe una reserva para ese ingreso
            $conReserva = 0;
            if ($ingreso->id_reserva) {
                $reserva = Reserva::where('id_reserva', $ingreso->id_reserva)->first();
                if ($reserva) {
                    $conReserva = 1;
                }
                $reservadaDesdeHora = $reserva->reservada_desde_horaG1;
                $reservadaDesdeFecha = $reserva->reservada_desde_fechaG1;
                $reservaDesde = new DateTime($reservadaDesdeFecha . ' ' . $reservadaDesdeHora);

                $reservadaHastaHora = $reserva->reservada_hasta_hora;
                $reservadaHastaFecha = $reserva->reservada_hasta_fecha;
                $reservaHasta = new DateTime($reservadaHastaFecha . ' ' . $reservadaHastaHora);

                $costo = $SalidaControler->costoConReserva($fechaHoraIngreso, $fechaHoraSalida, $reservaDesde, $reservaHasta);



            } else {
                $costo = $SalidaControler->costoSinReserva($fechaHoraIngreso, $fechaHoraSalida);
            }

            //Registramos la deuda
            $deuda = new Deuda();
            $deuda->id_salida = $registro->id_salida;
            $deuda->monto = $costo;
            if ($vehiculo) {
                $deuda->id_cliente = $vehiculo->id_cliente;
            }
            $deuda->estado = "pendiente";
            $deuda->save();
            $deuda->refresh();

            //calcular la duracion de la estadia:

            $fechaHoraIngreso = Carbon::parse($ingreso->fecha_ingreso . ' ' . $ingreso->hora_ingreso);
            $fechaHoraSalida = Carbon::parse($registro->fecha_salida . ' ' . $registro->hora_salida);
            
            $estadia = $fechaHoraSalida->diff($fechaHoraIngreso);
            
            $dias = $estadia->d;
            $horas = $estadia->h;
            $minutos = $estadia->i;
            $segundos = $estadia->s;
            
            $tiempoEstadia = '';
            
            if($dias > 0) {
                $tiempoEstadia .= $dias . ' dias';
                if($horas > 0 || $minutos > 0) $tiempoEstadia .= ', ';
            }
            
            if($horas > 0) {
                $tiempoEstadia .= $horas . ' horas';
                if($minutos > 0) $tiempoEstadia .= ' y ';
            }
            
            if($minutos > 0) {
                $tiempoEstadia .= $minutos . ' minutos';
            } else if ($segundos > 0) {
                $tiempoEstadia .= $segundos . ' segundos';
            }

            //

            return response()->json([
                'id_salida' => $registro->id_salida,
                'id_espacio' => $ingreso->id_espacio,
                'hora_ingreso' => $ingreso->hora_ingreso,
                'hora_salida' => $registro->hora_salida,
                'fecha_ingreso' => $ingreso->fecha_ingreso,
                'fecha_salida' => $registro->fecha_salida,
                'horas_transcurridas' => $horasTranscurridas,
                'costo' => $costo,
                'con_reserva' => $conReserva,
                'id_deuda' => $deuda->id_deuda,
                'tiempo_estadia' => $tiempoEstadia,
            ]);
        } else {
            return response()->json(['error' => 'No existe ingreso']);
        }



    }

    function costoConReserva($fechaHoraIngreso, $fechaHoraSalida, $reservaDesde, $reservaHasta)
    {
        // Obtener la diferencia de tiempo entre el ingreso y la salida
        $diferenciaTiempo = $fechaHoraSalida->diff($fechaHoraIngreso);
        $diasTotales = $diferenciaTiempo->days;
        $horasTotales = $diferenciaTiempo->h;
        $minutosTotales = $diferenciaTiempo->i;

        // Obtener la diferencia de tiempo entre la reserva y el ingreso
        $diferenciaReservaIngreso = $fechaHoraIngreso->diff($reservaDesde);
        $minutosAntesReserva = ($diferenciaReservaIngreso->days * 24 * 60) + ($diferenciaReservaIngreso->h * 60) + $diferenciaReservaIngreso->i;

        // Obtener la diferencia de tiempo entre la reserva y la salida
        $diferenciaReservaSalida = $fechaHoraSalida->diff($reservaHasta);
        $minutosDespuesReserva = ($diferenciaReservaSalida->days * 24 * 60) + ($diferenciaReservaSalida->h * 60) + $diferenciaReservaSalida->i;

        // Calcular el costo total considerando las condiciones mencionadas
        if ($diasTotales > 0) {
            $costoTotal = $diasTotales * 30; // Costo por día
        } else {
            $horasTotales += ceil($minutosTotales / 60); // Redondear hacia arriba si hay minutos adicionales

            if ($minutosAntesReserva > 0) {
                $horasTotales -= ceil($minutosAntesReserva / 60); // Restar las horas antes de la reserva
            }

            if ($minutosDespuesReserva > 0) {
                $horasTotales -= ceil($minutosDespuesReserva / 60); // Restar las horas después de la reserva
            }

            if ($horasTotales <= 0) {
                $costoTotal = 0; // Si el tiempo total es negativo o cero, no se cobra
            } else {
                $costoTotal = 3 + ($horasTotales - 1) * 2; // Costo de la primera hora + costo de las horas adicionales
            }
        }

        return $costoTotal;
    }


    function costoSinReserva($fechaIngreso, $fechaSalida)
    {
        $costoPrimeraHora = 3;
        $costoHoraAdicional = 2;
        $costoPorDia = 30;

        $intervalo = $fechaIngreso->diff($fechaSalida);
        $horasTranscurridas = $intervalo->h;

        // Calcular el costo total
        if ($horasTranscurridas < 1) {
            // Si el tiempo es menor a una hora, cobrar una hora completa
            $costoTotal = $costoPrimeraHora;
        } elseif ($horasTranscurridas >= 24) {
            // Si el tiempo es mayor o igual a 24 horas, cobrar por día
            $diasCompletos = floor($horasTranscurridas / 24);
            $costoTotal = ($diasCompletos * $costoPorDia) + $costoPorDia;
        } else {
            // Cobrar por hora
            $costoTotal = ($costoPrimeraHora + ($horasTranscurridas - 1) * $costoHoraAdicional);
        }

        return $costoTotal;
    }





}