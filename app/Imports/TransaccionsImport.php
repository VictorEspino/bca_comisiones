<?php

namespace App\Imports;

use App\Models\Transaccion;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class TransaccionsImport implements ToModel,WithHeadingRow,WithValidation,WithBatchInserts
{
    use Importable;

    private $calculo_id;

    public function setCalculoId(int $calculo_id)
    {
        $this->calculo_id=$calculo_id;
    }

    public function model(array $row)
    {
        $fecha=$row['fecha'];
        $fecha_db=\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fecha);

        return new Transaccion([
            'pedido'=> $row['pedido'],
            'numero_empleado'=>$row['numero_empleado'],
            'empleado'=> $row['empleado'],
            'fecha'=> $fecha_db,
            'region'=> $row['region'],
            'udn'=> $row['udn'],
            'pdv'=> $row['pdv'],
            'tipo_venta'=> $row['tipo_venta'],
            'transaccion'=> $row['transaccion'],
            'contrato'=> $row['contrato'],
            'importe'=> $row['importe'],
            'servicio'=> $row['servicio'],
            'producto'=> $row['producto'],
            'plazo'=> $row['plazo'],
            'eq_sin_costo'=>$row['eq_sin_costo'],
            'credito'=> $row['credito'],
            'razon_cr0'=> $row['razon_cr0'],
            'periodo'=>$fecha_db->format('Y-m'),
            'calculo_id'=>$this->calculo_id,
        ]);
    }
    public function batchSize(): int
    {
        return 100;
    }
    public function rules(): array
    {
        return [
             '*.pedido'=>['required','numeric'],
             '*.numero_empleado' => ['required','numeric'],
             '*.fecha' => ['required'],
             '*.region' => ['required'],
             '*.udn' => ['required','numeric'],
             '*.pdv' => ['required'],
             '*.tipo_venta' => ['required'],
             '*.importe' => ['required','numeric'],
             '*.servicio' => ['required'],
             '*.plazo' => ['required','numeric'],
             '*.eq_sin_costo'=>['required','numeric'],
             '*.credito' => ['required','numeric'],
             '*.razon_cr0' => ['exclude_unless:credito,0']
        ];
    }
}
