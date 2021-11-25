<?php

namespace App\Imports;

use App\Models\Empleado;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class EmpleadosImport implements ToModel,WithHeadingRow,WithValidation,WithBatchInserts
{
    use Importable;

    private $calculo_id;

    public function setCalculoId(int $calculo_id)
    {
        $this->calculo_id=$calculo_id;
    }
    public function model(array $row)
    {
        $fecha=$row['ingreso'];
        $fecha_db=\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fecha);

        $modalidad=2;
        if(strtoupper($row['puesto'])=="EJECUTIVO" || strtoupper($row['puesto'])=="RENOVADOR")
        {
            $modalidad=1;
            if(intval($row['sueldo'])==0)
            {
                $modalidad=3;
            }
        }

        return new Empleado([
            'numero_empleado'=>$row["numero_empleado"],
            'nombre'=>$row['nombre'],
            'udn'=>0,
            'pdv'=>$row['pdv'],
            'puesto'=>$row['puesto'],
            'ingreso'=>$fecha_db,
            'adeudo'=>0,
            'calculo_id'=>$this->calculo_id,
            'estatus'=>$row['estatus'],
            'sueldo'=>$row['sueldo'],
            'modalidad'=>$modalidad,
        ]);
    }
    public function batchSize(): int
    {
        return 100;
    }
    public function rules(): array
    {
        return [
             '*.numero_empleado' => ['required','numeric'],
             '*.nombre' => ['required'],
             '*.pdv' => ['required'],
             '*.puesto' => ['required'],
             '*.sueldo' => ['required','numeric'],

        ];
    }
}
