<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Models\Espacio;
use App\Models\Cobro;
use DateTime;
use Illuminate\Support\Facades\DB;
use Webmozart\Assert\InvalidArgumentException;
use App\Models\User;

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
            'tipo' => 'required',
            'costo' => 'required'
        ]);

        $id_espacio = $request->id_espacio;
        $id_usuario = auth()->user()->id;
        $reserva = new Reserva();
        $reserva->id_espacio = $id_espacio;

        $reserva->costo = $request->costo;

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


        $fechaHoraInicioReserva = $fechaHoraReserva;


        $reservaController = new ReservaController();

        $tipoReserva = $request->tipo;
        switch ($tipoReserva) {
            case 'hora':
                $costo = $reservaController->calcularCosto('hora', $fechaHoraInicioReserva, $fechaHoraFinReserva);
                break;
            case 'diario':
                $costo = $reservaController->calcularCosto('diario', $fechaHoraInicioReserva, $fechaHoraFinReserva);
                break;
            case 'semanal':
                $costo = $reservaController->calcularCosto('semanal', $fechaHoraInicioReserva, $fechaHoraFinReserva);
                break;
            case 'mensual':
                $costo = $reservaController->calcularCosto('mensual', $fechaHoraInicioReserva, $fechaHoraFinReserva);
                break;
            default:
                $costo = 69;
        }

        $reserva->costo = $costo;
        $reserva->tipo = $tipoReserva;
        $reserva->pagada = 'no';

        $reserva->save();
        $reserva->refresh();

        $datosUsuario = User::where('id', $id_usuario)->first();
        $nombre = $datosUsuario->name . ' ' . $datosUsuario->apellido_paterno . ' ' . $datosUsuario->apellido_materno;
        $rolUsuario = $datosUsuario->rol;

        return response([
            'status' => '1',
            'msg' => 'reserva exitosa',
            'reserva' => [
                'id_reserva' => $reserva->id_reserva,
                // 'costo' => $reserva->costo,
                'nombre_usuario' => $nombre,
                'rol' => $rolUsuario,
            ],
        ]);
    }

    public function createReservaRango(Request $request)
    {
        date_default_timezone_set('America/Manaus');

        $request->validate([
            'id_espacio' => 'required',
            'placa_vehiculo' => 'required',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'duracion_minutos' => 'required',
            'tipo' => 'required',
            'costo' => 'required'
        ]);

        $id_espacio = $request->id_espacio;
        $id_usuario = auth()->user()->id;

        $placa_vehiculo = $request->placa_vehiculo;

        // Convertir a mayúsculas
        $placa_vehiculo = strtoupper($placa_vehiculo);

        // Eliminar caracteres no alfanuméricos y espacios
        $placa_vehiculo = preg_replace('/[^A-Z0-9]/', '', $placa_vehiculo);
        $placa_vehiculo = str_replace(' ', '', $placa_vehiculo);
        $vehiculo = Vehiculo::where('placa', $placa_vehiculo)->first();

        $id_vehiculo = $vehiculo ? $vehiculo->id_vehiculo : null;

        $fecha_inicio = new DateTime($request->fecha_inicio);
        $fecha_fin = new DateTime($request->fecha_fin);
        $hora_inicio = $request->hora_inicio;
        $duracionMinutos = $request->duracion_minutos;
        $tipoReserva = $request->tipo;
        $costo = $request->costo;

        $reservasConflictivas = [];

        // Iterar a través de cada día en el rango de fechas
        for ($fecha = clone $fecha_inicio; $fecha <= $fecha_fin; $fecha->modify('+1 day')) {
            //Crear el objeto de la reserva
            $reserva = new Reserva();
            $reserva->id_espacio = $id_espacio;
            $reserva->id_usuario = $id_usuario;
            $reserva->id_vehiculo = $id_vehiculo;
            $reserva->placa_vehiculo = $placa_vehiculo;

            //Definir las horas de inicio y fin
            $reserva->reservada_desde_fecha = $fecha->format('Y-m-d');
            $reserva->reservada_desde_hora = $hora_inicio;
            $fechaHoraReserva = new DateTime($reserva->reservada_desde_fecha . ' ' . $reserva->reservada_desde_hora);

            $fechaHoraFinReserva = clone $fechaHoraReserva;
            $fechaHoraFinReserva->modify('+' . $duracionMinutos . 'minutes');
            $reserva->reservada_hasta_fecha = $fechaHoraFinReserva->format('Y-m-d');
            $reserva->reservada_hasta_hora = $fechaHoraFinReserva->format('H:i:s');

            // Verificar si existe un choque de horarios
            $reservaChoque = Reserva::where('id_espacio', $id_espacio)
                ->where(function ($query) use ($fecha, $fechaHoraReserva, $fechaHoraFinReserva) {
                    $query->where('reservada_desde_fecha', $fecha->format('Y-m-d'))
                        ->where(function ($query) use ($fechaHoraReserva, $fechaHoraFinReserva) {
                            $query->whereRaw("CONCAT(reservada_desde_fecha, ' ', reservada_desde_hora) <= ?", [$fechaHoraFinReserva->format('Y-m-d H:i:s')])
                                ->whereRaw("CONCAT(reservada_hasta_fecha, ' ', reservada_hasta_hora) >= ?", [$fechaHoraReserva->format('Y-m-d H:i:s')]);
                        });
                })
                ->first();

            if ($reservaChoque) {
                $reservasConflictivas[] = $reservaChoque;
            }
        }

        if (!empty($reservasConflictivas)) {
            return response([
                'status' => '0',
                'msg' => 'Existen conflictos con otras reservas',
                'reservas_conflictivas' => $reservasConflictivas
            ]);
        }
        $Idreservas = [];
        // Si no hubo conflictos, procedemos a crear las reservas
        for ($fecha = clone $fecha_inicio; $fecha <= $fecha_fin; $fecha->modify('+1 day')) {
            $reserva = new Reserva();
            $reserva->id_espacio = $id_espacio;
            $reserva->id_usuario = $id_usuario;
            $reserva->id_vehiculo = $id_vehiculo;
            $reserva->placa_vehiculo = $placa_vehiculo;
            $reserva->reservada_desde_fecha = $fecha->format('Y-m-d');
            $reserva->reservada_desde_hora = $hora_inicio;
            $fechaHoraReserva = new DateTime($reserva->reservada_desde_fecha . ' ' . $reserva->reservada_desde_hora);
            $fechaHoraFinReserva = clone $fechaHoraReserva;
            $fechaHoraFinReserva->modify('+' . $duracionMinutos . 'minutes');
            $reserva->reservada_hasta_fecha = $fechaHoraFinReserva->format('Y-m-d');
            $reserva->reservada_hasta_hora = $fechaHoraFinReserva->format('H:i:s');
            $reserva->tipo = $tipoReserva;
            $reserva->costo = $costo;
            $reserva->pagada = 'no';

            //Añadir los Gaps
            //G1->(-15minutos)  G2->(-6horas=-360minutos)
            $G1 = 15;
            $G2 = 360;
            $fechaHoraReserva = new DateTime($fecha->format('Y-m-d') . ' ' . $hora_inicio);

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
            $reserva->refresh();
            array_push($Idreservas, $reserva->id_reserva);


        }
        $id_usuario = auth()->user()->id;
        $datosUsuario = User::where('id', $id_usuario)->first();
        $nombre = $datosUsuario->name . ' ' . $datosUsuario->apellido_paterno . ' ' . $datosUsuario->apellido_materno;
        $rolUsuario = $datosUsuario->rol;

        return response([
            'status' => '1',
            'msg' => 'Reservas creadas con éxito',
            'reservas_creadas' => $Idreservas,
            'nombre_usuario' => $nombre,
            'rol' => $rolUsuario,
        ]);
    }



    public function obtenerReservasPorFecha(Request $request)
    {
        $desde_fecha = $request->input('desde_fecha');
        $hasta_fecha = $request->input('hasta_fecha');

        $reservas = Reserva::with(['vehiculo'])
            ->whereBetween('reservada_desde_fecha', [$desde_fecha, $hasta_fecha])
            ->get()
            ->map(function ($reserva) {
                $usuario = User::where('id', $reserva->id_usuario)->first();
                $nombre = $usuario->name . " " . $usuario->apellido_paterno . " " . $usuario->apellido_materno;
                return [
                    'id_reserva' => $reserva->id_reserva,
                    'reservada_desde_fecha' => $reserva->reservada_desde_fecha,
                    'reservada_desde_hora' => $reserva->reservada_desde_hora,
                    'reservada_hasta_fecha' => $reserva->reservada_hasta_fecha,
                    'reservada_hasta_hora' => $reserva->reservada_hasta_hora,
                    'costo' => $reserva->costo,
                    'placa' => $reserva->placa_vehiculo ?? $reserva->vehiculo->placa,
                    'id_espacio' => $reserva->id_espacio,
                    'usuario' => $nombre,
                ];
            });

        return response()->json($reservas);
    }

    public function obtenerReservasPorUsuario(Request $request)
    {
        $id_usuario = auth()->user()->id;
        $cliente = Cliente::where('id_usuario', $id_usuario)->first();

        $desde_fecha = $request->input('desde_fecha');
        $hasta_fecha = $request->input('hasta_fecha');

        // Obtén los ids de los vehículos del cliente
        $ids_vehiculos_cliente = Vehiculo::where('id_cliente', $cliente->id_cliente)->pluck('id_vehiculo');

        $reservas = Reserva::whereIn('id_vehiculo', $ids_vehiculos_cliente) // Filtra las reservas por los vehículos del cliente
            ->whereBetween('reservada_desde_fecha', [$desde_fecha, $hasta_fecha]) // Filtra por el intervalo de fechas
            ->get()
            ->map(function ($reserva) {
                return [
                    'id_reserva' => $reserva->id_reserva,
                    'id_espacio' => $reserva->id_espacio,
                    'reservada_desde_fecha' => $reserva->reservada_desde_fecha,
                    'reservada_desde_hora' => $reserva->reservada_desde_hora,
                    'reservada_hasta_fecha' => $reserva->reservada_hasta_fecha,
                    'reservada_hasta_hora' => $reserva->reservada_hasta_hora,
                    'fecha_creada' => $reserva->fecha_creada,
                    'hora_creada' => $reserva->hora_creada,
                    'placa' => $reserva->placa_vehiculo,
                ];
            });

        return response()->json($reservas);
    }


    public function obtenerReservasPorFechaEspacio(Request $request)
    {
        $desde_fecha = $request->input('desde_fecha');
        $hasta_fecha = $request->input('hasta_fecha');
        $id_espacio = $request->input('id_espacio');

        $reservas = Reserva::with(['vehiculo'])->where('id_espacio', $id_espacio)
            ->whereBetween('reservada_desde_fecha', [$desde_fecha, $hasta_fecha])
            ->get()
            ->map(function ($reserva) {
                $usuario = User::where('id', $reserva->id_usuario)->first();
                $nombre = $usuario->name . " " . $usuario->apellido_paterno . " " . $usuario->apellido_materno;
                return [
                    'id_reserva' => $reserva->id_reserva,
                    'reservada_desde_fecha' => $reserva->reservada_desde_fecha,
                    'reservada_desde_hora' => $reserva->reservada_desde_hora,
                    'reservada_hasta_fecha' => $reserva->reservada_hasta_fecha,
                    'reservada_hasta_hora' => $reserva->reservada_hasta_hora,
                    'costo' => $reserva->costo,
                    'placa' => $reserva->placa_vehiculo ?? $reserva->vehiculo->placa,
                    'id_espacio' => $reserva->id_espacio,
                    'usuario' => $nombre,
                ];
            });

        return response()->json($reservas);
    }

    public function calcularCosto($tipoReserva, DateTime $fechaHoraInicioReserva, DateTime $fechaHoraFinReserva)
    {
        $diferencia = $fechaHoraInicioReserva->diff($fechaHoraFinReserva);

        switch ($tipoReserva) {
            case 'hora':
                $horas = $diferencia->h;
                if ($horas == 0) {
                    $costo = 3;
                } else {
                    $costo = 3 + ($horas - 1) * 2;
                    $minutos = $diferencia->i;
                    if ($minutos > 1) {
                        $costo = $costo + 2;
                    }
                }
                break;
            case 'diario':
                $dias = $diferencia->d;
                $costo = $dias * 30;
                break;
            case 'semanal':
                $semanas = floor($diferencia->d / 7);
                $costo = $semanas * 120;
                break;
            case 'mensual':
                $meses = $diferencia->m + $diferencia->y * 12;
                $costo = $meses * 350;
                break;
            default:
                throw new InvalidArgumentException("Tipo de reserva desconocido: $tipoReserva");
        }

        return $costo;
    }

    public function pagarReserva(Request $request)
    {
        date_default_timezone_set('America/Manaus');

        $id_reserva = $request->id_reserva;
        $reserva = Reserva::where('id_reserva', $id_reserva)->first();
        $reserva->pagada = 'si';
        $reserva->save();

        $fechaHoraActual = new DateTime();
        $fechaCreada = $fechaHoraActual->format('Y-m-d');
        $horaCreada = $fechaHoraActual->format('H:i:s');

        $cobro = new Cobro();
        $cobro->placa_vehiculo = $request->placa;
        $cobro->fecha = $fechaCreada;
        $cobro->hora = $horaCreada;
        $cobro->origen = 'reserva';
        $cobro->metodo = 'QR';
        $cobro->id_origen = $id_reserva;
        $cobro->id_usuario = $request->nombre_usuario;
        $cobro->monto = $request->monto;
        $cobro->save();

        return response([
            'status' => '1',
            'msg' => 'reserva pagada',
        ]);
    }

    public function pagarVariasReservas(Request $request)
    {
        date_default_timezone_set('America/Manaus');

        $id_reservas = $request->id_reservas;

        foreach ($id_reservas as $id_reserva) {
            $reserva = Reserva::where('id_reserva', $id_reserva)->first();
            $reserva->pagada = 'si';
            $reserva->save();

            $fechaHoraActual = new DateTime();
            $fechaCreada = $fechaHoraActual->format('Y-m-d');
            $horaCreada = $fechaHoraActual->format('H:i:s');

            $cobro = new Cobro();
            $cobro->placa_vehiculo = $request->placa;
            $cobro->fecha = $fechaCreada;
            $cobro->hora = $horaCreada;
            $cobro->origen = 'reserva';
            $cobro->metodo = 'QR';
            $cobro->id_origen = $id_reserva;
            $cobro->id_usuario = $request->nombre_usuario;
            $cobro->monto = $request->monto;
            $cobro->save();
        }

        return response([
            'status' => '1',
            'msg' => 'reservas pagadas',
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


            if ($espacioLibre) {
                $reserva = Reserva::where('id_espacio', $espacio)
                    ->where('reservada_desde_fechaG2', '<=', $fechaActual)
                    ->where('reservada_desde_horaG2', '<=', $horaActual)
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
                        ->where('reservada_desde_fechaG2', '<=', $fechaActual)
                        ->where('reservada_desde_horaG2', '<=', $horaActual)
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
            ->where(function ($query) use ($fechaHoraActual) {
                $query->whereRaw("CONCAT(reservada_desde_fecha, ' ', reservada_desde_hora) >= ?", [$fechaHoraActual->format('Y-m-d H:i:s')]);
            })->get();

        $reservas->each(function ($reserva) {
            $reserva->makeHidden('id_vehiculo');
            $reserva->makeHidden('fecha_creada');
            $reserva->makeHidden('hora_creada');
            $reserva->makeHidden('id_usuario');
            $idVehiculo = $reserva->id_vehiculo;
            if ($idVehiculo) {
                $vehiculo = Vehiculo::where('id_vehiculo', $idVehiculo)->first();
                $placaVehiculo = $vehiculo->placa;
            } else {
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
            ->where(function ($query) use ($fechaHoraActual) {
                $query->whereRaw("CONCAT(reservada_desde_fecha, ' ', reservada_desde_hora) <= ?", [$fechaHoraActual->format('Y-m-d H:i:s')]);
            })->get();

        $reservas->each(function ($reserva) {
            $reserva->makeHidden('id_vehiculo');
            $reserva->makeHidden('fecha_creada');
            $reserva->makeHidden('hora_creada');
            $reserva->makeHidden('id_usuario');
            $idVehiculo = $reserva->id_vehiculo;
            if ($idVehiculo) {
                $vehiculo = Vehiculo::where('id_vehiculo', $idVehiculo)->first();
                $placaVehiculo = $vehiculo->placa;
            } else {
                $placaVehiculo = $reserva->placa_vehiculo;
            }
            $reserva->placa_vehiculo = $placaVehiculo;

        });

        return $reservas;
    }

    public function listReservas()
    {
        $id_usuario = auth()->user()->id;
        $reservas = Reserva::where(['id_usuario' => $id_usuario, 'pagada' => 'si'])->get();

        $reservas->each(function ($reserva) {
            $reserva->makeHidden('id_vehiculo');
            $reserva->makeHidden('fecha_creada');
            $reserva->makeHidden('hora_creada');
            $reserva->makeHidden('reservada_desde_fechaG1');
            $reserva->makeHidden('reservada_desde_horaG1');
            $reserva->makeHidden('reservada_desde_fechaG2');
            $reserva->makeHidden('reservada_desde_horaG2');
            $reserva->makeHidden('pagada');
            $idVehiculo = $reserva->id_vehiculo;
            if ($idVehiculo) {
                $vehiculo = Vehiculo::where('id_vehiculo', $idVehiculo)->first();
                $placaVehiculo = $vehiculo->placa;
            } else {
                $placaVehiculo = $reserva->placa_vehiculo;
            }
            $reserva->placa_vehiculo = $placaVehiculo;

        });

        return $reservas;
    }

    public function listAllReservas()
    {
        $id_usuario = auth()->user()->id;
        $reservas = Reserva::where('pagada', 'si')->get();

        $reservas->each(function ($reserva) {
            $reserva->makeHidden('id_vehiculo');
            $reserva->makeHidden('fecha_creada');
            $reserva->makeHidden('hora_creada');
            $reserva->makeHidden('reservada_desde_fechaG1');
            $reserva->makeHidden('reservada_desde_horaG1');
            $reserva->makeHidden('reservada_desde_fechaG2');
            $reserva->makeHidden('reservada_desde_horaG2');
            $reserva->makeHidden('pagada');
            $idVehiculo = $reserva->id_vehiculo;
            if ($idVehiculo) {
                $vehiculo = Vehiculo::where('id_vehiculo', $idVehiculo)->first();
                $placaVehiculo = $vehiculo->placa;
            } else {
                $placaVehiculo = $reserva->placa_vehiculo;
            }
            $reserva->placa_vehiculo = $placaVehiculo;

        });

        return $reservas;
    }


    public function showReservaId($idReserva)
    {
        $id_usuario = auth()->user()->id;
        $reserva = Reserva::where(['id_usuario' => $id_usuario, 'id_reserva' => $idReserva])->first();

        $idVehiculo = $reserva->id_vehiculo;
        if ($idVehiculo) {
            $vehiculo = Vehiculo::where('id_vehiculo', $idVehiculo)->first();
            $placaVehiculo = $vehiculo->placa;
        } else {
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

        $reservas->each(function ($reserva) {
            $reserva->makeHidden('id_vehiculo');
            $reserva->makeHidden('fecha_creada');
            $reserva->makeHidden('hora_creada');
            $reserva->makeHidden('id_usuario');
            $idVehiculo = $reserva->id_vehiculo;
            if ($idVehiculo) {
                $vehiculo = Vehiculo::where('id_vehiculo', $idVehiculo)->first();
                $placaVehiculo = $vehiculo->placa;
            } else {
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

        if (Reserva::where('id_reserva', $idReserva)->exists()) {
            $reserva = Reserva::where('id_reserva', $idReserva)->first();
            $reserva->delete();
        } else {
            return response([
                'status' => '0',
                'msg' => 'Error',
            ], 404);
        }
    }


    public function deleteMultipleReservas(Request $request)
    {
        $id_usuario = auth()->user()->id;
        $id_reservas = $request->id_reserva;

        if (Reserva::where('id_reserva', $id_reservas)->exists()) {
            DB::table('Reservas')->whereIn('id_reserva', $id_reservas)->delete();

            return response([
                'status' => '1',
                'msg' => 'Reservas eliminadas correctamente',
            ], 200);
        } else {
            return response([
                'status' => '0',
                'msg' => 'Error, las reservas no existen',
            ], 404);
        }
    }



    public function verificarReserva(Request $request)
    {
        date_default_timezone_set('America/Manaus');

        $placa = $request->placa;
        $placa = strtoupper($placa);
        $placa = str_replace([' ', '-'], '', $placa);

        $fechaHoraActual = new DateTime();
        $fechaActual = $fechaHoraActual->format('Y-m-d');
        $horaActual = $fechaHoraActual->format('H:i:s');


        $vehiculo = Vehiculo::where('placa', $placa)->first();
        if ($vehiculo) {
            $reserva = Reserva::where('id_vehiculo', $vehiculo->id_vehiculo)
                ->where('reservada_desde_fechaG1', '<=', $fechaActual)
                ->where('reservada_desde_horaG1', '<=', $horaActual)
                ->first();


        } else {
            $reserva = Reserva::where('placa_vehiculo', $placa)
                ->where('reservada_desde_fechaG1', '<=', $fechaActual)
                ->where('reservada_desde_horaG1', '<=', $horaActual)
                ->first();
        }
        if ($reserva) {
            return [
                "reserva" => 1,
                "id_espacio" => $reserva->id_espacio,
            ];
        } else {
            return [
                "reserva" => 0,
            ];
        }
    }


}