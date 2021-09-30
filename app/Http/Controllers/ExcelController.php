<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\TransaccionsImport;
use App\Imports\EmpleadosImport;
use App\Imports\CuotasImport;
use App\Imports\AjustesImport;

use Illuminate\Support\Facades\DB;

use App\Models\Ajuste;
use App\Models\ChargeBackInterno;
use App\Models\Calculo;
use App\Models\Transaccion;
use App\Models\Cuota;
use App\Models\Empleado;


class ExcelController extends Controller
{
    public function transaccions_import(Request $request) 
    {
        
        $validated = $request->validate([
            'file' => 'required',
        ]);
        Transaccion::where('calculo_id',$request->calculo_id)->delete();
        $file=$request->file('file');
    
        $import=new TransaccionsImport;
        $import->setCalculoId(intval($request->calculo_id));

        try{
        $import->import($file);
        }
        catch(\Maatwebsite\Excel\Validators\ValidationException $e) {
            return back()->withFailures($e->failures());
        }  
        return back()->withStatus('Archivo de ventas cargado con exito!');
    }
    public function empleados_import(Request $request) 
    {
        
        $validated = $request->validate([
            'file' => 'required',
        ]);
        Empleado::where('calculo_id',$request->calculo_id)->delete();

        $file=$request->file('file');
    
        $import=new EmpleadosImport;
        $import->setCalculoId(intval($request->calculo_id));

        try{
        $import->import($file);
        }
        catch(\Maatwebsite\Excel\Validators\ValidationException $e) {
            return back()->withFailures($e->failures());
        }  
        return back()->withStatus('Archivo empleados cargado con exito!');
    }
    public function cuotas_import(Request $request) 
    {
        
        $validated = $request->validate([
            'file' => 'required',
        ]);
        Cuota::where('calculo_id',$request->calculo_id)->delete();

        $file=$request->file('file');
    
        $import=new CuotasImport;
        $import->setCalculoId(intval($request->calculo_id));

        try{
        $import->import($file);
        }
        catch(\Maatwebsite\Excel\Validators\ValidationException $e) {
            return back()->withFailures($e->failures());
        }  
        return back()->withStatus('Archivo de cuotas cargado con exito!');
    }
    public function ajustes_import(Request $request) 
    {

        Ajuste::where('calculo_id',$request->calculo_id)->delete();
        $validated = $request->validate([
            'file' => 'required',
        ]);
        $calculo=Calculo::find($request->calculo_id);
        $file=$request->file('file');
    
        $import=new AjustesImport;
        $import->setCalculoId(intval($request->calculo_id));

        try{
        $import->import($file);
        $this->aplica_ajustes($request->calculo_id,$calculo->fecha_fin);
        }
        catch(\Maatwebsite\Excel\Validators\ValidationException $e) {
            return back()->withFailures($e->failures());
        }  
        return back()->withStatus('Archivo de ajustes cargado con exito!');
    }
    public function aplica_ajustes($id,$fecha)
    {
        $ajustes=Ajuste::where('calculo_id',$id)->where('monto','>',0)->get();
        ChargeBackInterno::where('calculo_id',$id)->delete();
        DB::table('retroactivos')->where('calculo_id',$id)->delete();
        foreach($ajustes as $ajuste)
        {
            if($ajuste->tipo=='CARGO')
            {
                $registro=new ChargeBackInterno;
                $registro->calculo_origen=0;
                $registro->pagado_en='';
                $registro->fecha=$fecha;
                $registro->servicio=$ajuste->razon;
                $registro->importe=0;
                $registro->pedido=0;
                $registro->contrato=0;
                $registro->tipo_venta='';
                $registro->udn=0;
                $registro->pdv='';
                $registro->numero_empleado=$ajuste->empleado;
                $registro->rol=$ajuste->rol;
                $registro->cb=$ajuste->monto;
                $registro->calculo_id=$id;
                $registro->save();
            }
            if($ajuste->tipo=='PAGO')
            {
                DB::insert('insert into retroactivos (calculo_id, numero_empleado,udn,retroactivo) values (?,?,?,?)', [$id, $ajuste->empleado,0,$ajuste->monto]);
            }
        }
        
    }
}
