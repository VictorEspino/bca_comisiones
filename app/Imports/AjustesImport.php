<?php

namespace App\Imports;

use App\Models\Ajuste;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class AjustesImport implements ToModel,WithHeadingRow,WithValidation,WithBatchInserts
{
    use Importable;

    private $calculo_id;

    public function setCalculoId(int $calculo_id)
    {
        $this->calculo_id=$calculo_id;
    }

    public function model(array $row)
    {
        return new Ajuste([
            'tipo'=>$row['tipo'],
            'rol'=>$row['rol'],
            'empleado'=>$row['empleado'],
            'razon'=>$row['razon'],
            'monto'=>$row['monto'],
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
             '*.tipo' => ['required',Rule::in(['CARGO','PAGO'])],
             '*.rol' => ['required'],
             '*.empleado' => ['required','numeric'],
             '*.razon'=>['required'],
             '*.monto'=>['required','numeric']
        ];
    }
}
