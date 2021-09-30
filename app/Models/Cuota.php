<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    use HasFactory;
    protected $fillable = [
                            'director',
                            'regional',
                            'region',
                            'id_gerente',
                            'udn',
                            'pdv',
                            'esquema',
                            'activaciones',
                            'aep',
                            'renovaciones',
                            'rep',
                            'calculo_id'
                        ];
}
