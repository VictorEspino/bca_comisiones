<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaccion extends Model
{
    use HasFactory;

    protected $fillable = ['pedido',
                           'numero_empleado',
                           'empleado',
                           'fecha',
                           'region',
                           'udn',
                           'pdv',
                           'tipo_venta',
                           'transaccion',
                           'contrato',
                           'importe',
                           'servicio',
                           'producto',
                           'plazo',
                           'credito',
                           'eq_sin_costo',
                           'razon_cr0',
                           'periodo',
                           'calculo_id',
                        ];
}
