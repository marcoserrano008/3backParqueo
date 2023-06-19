<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Reserva extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'Reservas';

    protected $primaryKey = 'id_reserva';
    protected $fillable = [
        'id_espacio',
        'id_vehiculo',
        'fecha_reservada',
        'hora_reservada',
        'fecha_creacion',
        'hora_creacion',
        'duracion_horas',
        'placa_vehiculo',
        'reservada_desde_fechaG1',
        'reservada_desde_horaG1',
        'reservada_desde_fechaG2',
        'reservada_desde_horaG2',
        'tipo',
        'costo',
        'pagada',
    ];

    public $timestamps = false;

    public function vehiculo()
    {
    return $this->belongsTo(Vehiculo::class, 'id_vehiculo');
    }
    public function salida()
    {
        return $this->hasOne('App\Models\Salida', 'id_ingreso');
    }
}
