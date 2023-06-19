<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
class Cobro extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'Cobros';

    protected $primaryKey = 'id_cobro';
    protected $fillable = [
        'id_cobro',
        'placa_vehiculo',
        'hora',
        'fecha',
        'origen',
        'metodo',
        'id_origen',
    ];

    public $timestamps = false;
}
