<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ajuste extends Model
{
    use HasFactory;

    protected $fillable = [
                        'tipo',
                        'rol',
                        'empleado',
                        'razon',
                        'monto',
                        'calculo_id'
                        ];
}
