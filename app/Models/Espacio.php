<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
class Espacio extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'Espacios';

    protected $primaryKey = 'id_espacio';
    protected $keyType = 'string';
    protected $fillable = [
        'id_espacio',
        'estado',
        'bloque',
    ];

    public $timestamps = false;
}
