<?php

namespace App\Imports;

use App\Models\Cuota;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class CuotasImport implements ToModel,WithHeadingRow,WithValidation,WithBatchInserts
{
    use Importable;

    private $calculo_id;

    public function setCalculoId(int $calculo_id)
    {
        $this->calculo_id=$calculo_id;
    }

    public function model(array $row)
    {
        return new Cuota([
            'director'=>$row['director'],
            'regional'=>$row['regional'],
            'region'=>$row['region'],
            'id_gerente'=>$row['gerente'],
            'udn'=>$row['udn'],
            'pdv'=>$row['pdv'],
            'esquema'=>$row['esquema'],
            'activaciones'=>$row['cuota_activaciones'],
            'aep'=>$row['cuota_aep'],
            'renovaciones'=>$row['cuota_renovaciones'],
            'rep'=>$row['cuota_rep'],
            'calculo_id'=>$this->calculo_id
        ]);
    }
    public function batchSize(): int
    {
        return 100;
    }
    public function rules(): array
    {
        return [
             '*.director' => ['required','numeric'],
             '*.regional' => ['required','numeric'],
             '*.region' => ['required'],
             '*.gerente' => ['required','numeric'],
             '*.udn' => ['required','numeric'],
             '*.pdv' => ['required'],
             '*.esquema' => ['required','numeric'],
             '*.cuota_activaciones' => ['required','numeric'],
             '*.cuota_aep' => ['required','numeric'],
             '*.cuota_renovaciones' => ['required','numeric'],
             '*.cuota_rep' => ['required','numeric'],

        ];
    }
}
