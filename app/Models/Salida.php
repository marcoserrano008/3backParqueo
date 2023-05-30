<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Salida extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'SalidasParqueo';

    protected $primaryKey = 'id_salida';
    protected $fillable = [
        'id_salida',
        'id_vehiculo',
        'hora_salida',
        'fecha_salida',
        'id_ingreso',
    ];

    public $timestamps = false;
}
