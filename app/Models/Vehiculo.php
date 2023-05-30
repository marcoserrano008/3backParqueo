<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Vehiculo extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'Vehiculos';

    protected $primaryKey = 'id_vehiculo';
    protected $fillable = [
        'id_cliente',
        'marca',
        'color',
        'modelo',
        'placa',
        'observacion',
    ];

    public $timestamps = false;
}
