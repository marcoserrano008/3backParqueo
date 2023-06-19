<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
class Deuda extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'Deudas';

    protected $primaryKey = 'id_deuda';
    protected $fillable = [
        'id_deuda',
        'id_salida',
        'monto',
        'id_cliente',
        'estado',
        'fecha_hora_pagado',
        'tipo_pago',
    ];

    public $timestamps = false;
}
