<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Ingreso extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'IngresosParqueo';

    protected $primaryKey = 'id_ingreso';
    protected $fillable = [
        'id_ingreso',
        'id_vehiculo',
        'hora_ingreso',
        'fecha_ingreso',
        'id_guardia',
        'id_espacio',
        'id_bloque',
        'id_reserva',
    ];

    public $timestamps = false;

    public function vehiculo()
    {
        return $this->belongsTo('App\Models\Vehiculo', 'id_vehiculo');
    }

    public function salida()
    {
        return $this->hasOne('App\Models\Salida', 'id_ingreso');
    }
}