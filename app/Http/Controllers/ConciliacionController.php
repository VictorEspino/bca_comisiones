<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conciliacion;
use App\Models\Reclamo;
use App\Models\Transaccion;
use App\Models\SecuenciaPeriodo;
use App\Models\Alerta;
use Illuminate\Support\Facades\DB;

class ConciliacionController extends Controller
{
    public function setup_conciliacion(Request $request)
    {
        
        $conciliaciones=Conciliacion::where('periodo',$request->periodo)->get()->first();
        if(is_null($conciliaciones))
        {
            $conciliacion=new Conciliacion();
            $conciliacion->periodo=$request->periodo;
            $conciliacion->comisiones_att=false;
            $conciliacion->residual_att=false;
            $conciliacion->charge_back_att=false;
            $conciliacion->terminado=false;
            $conciliacion->user_id=auth()->id();
            $conciliacion->save();
            return($conciliacion);
        }
        else
        {
            $conciliacion=array('id'=>0);
            return($conciliacion);
            
        }
    }

    public function conciliacion_erp_att(Request $request)
    {
        return(0);
        $respuesta=array(
            'success'=>'Archivo cargado con exito',
            'registros'=>0,
        );
        if(Conciliacion::find($request->conciliacion_id)->comisiones_att==0)
        {return($respuesta);}

        $x=0;
        $registros_no_encontrados=DB::select(DB::raw("
        select * from (select t_transaccions.contrato erp,t_transaccions.periodo as periodo, t_comisiones_att.contrato as att from
        (select DISTINCT contrato,periodo from transaccions where periodo='$request->periodo') as t_transaccions
        LEFT JOIN 
        (select contrato,periodo from comision_att where periodo='$request->periodo') as t_comisiones_att
        ON t_transaccions.contrato=t_comisiones_att.contrato) as a where a.att is null"
        ));
        $resultset=collect($registros_no_encontrados);
        $deletedRows = Reclamo::where('conciliacion_id', $request->conciliacion_id)
                ->where('observacion','Comision no Pagada')
                ->delete();
        foreach($resultset as $no_encontrado)
        {
            $transacciones=Transaccion::where('periodo',$request->periodo)
                    ->where('contrato',$no_encontrado->erp)
                    ->where('tipo_venta','not like','%rotecc%')
                    ->where('tipo_venta','not like','%ADD%')
                    ->get();
            foreach($transacciones as $transaccion)
            {
                $propiedad="NUEVO";
                if(strpos($transaccion->tipo_venta,"ropio")!== false)
                {
                    $propiedad="PROPIO";
                }
                $reclamo=new Reclamo();
                $reclamo->telefono='';
                $reclamo->plan=$transaccion->servicio;
                $reclamo->renta=$transaccion->importe;
                $reclamo->propiedad=$propiedad;
                $reclamo->iccid='';
                $reclamo->fecha=$transaccion->fecha;
                $reclamo->plazo=$transaccion->plazo;
                $reclamo->contrato=$transaccion->contrato;
                $reclamo->cuenta='';
                $reclamo->marca='AT&T';
                $reclamo->periodo=$request->periodo;
                $reclamo->observacion='Comision no Pagada';
                $reclamo->comision=$transaccion->importe/1.16*5;
                $reclamo->mes=$request->periodo;
                $reclamo->conciliacion_id=$request->conciliacion_id;
                $reclamo->save();
                $x=$x+1;
            }
        }
        $respuesta["registros"]=$x;
        return($respuesta);
    }
    public function residual_45dias(Request $request)
    {
        return(0);
        $respuesta=array(
            'success'=>'Archivo cargado con exito',
            'registros'=>0,
        );
        $x=0;
        $periodo_anterior=$this->getPeriodo($request->periodo,-2);
        $registros_no_encontrados=DB::select(DB::raw(
        "
        select a.contrato as erp,b.contrato as att,b.comision as comision from
        (select DISTINCT contrato from transaccions where periodo='$periodo_anterior' and tipo_venta not like '%rotec%' and tipo_venta not like '%ADD%') a
        LEFT JOIN
        (select contrato,comision from residuals where periodo='$request->periodo') b
        ON a.contrato=b.contrato
        where b.comision is null
        " 
        ));
        $resultset=collect($registros_no_encontrados);
        $deletedRows = Reclamo::where('conciliacion_id', $request->conciliacion_id)
                ->where('observacion','Residual Inicial NO Pagado')
                ->delete();
        foreach($resultset as $no_encontrado)
        {
            $transacciones=Transaccion::where('periodo',$periodo_anterior)
                    ->where('contrato',$no_encontrado->erp)
                    ->where('tipo_venta','not like','%rotecc%')
                    ->where('tipo_venta','not like','%ADD%')
                    ->get();
            foreach($transacciones as $transaccion)
            {
                $propiedad="NUEVO";
                if(strpos($transaccion->tipo_venta,"ropio")!== false)
                {
                    $propiedad="PROPIO";
                }
                $reclamo=new Reclamo();
                $reclamo->telefono='';
                $reclamo->plan=$transaccion->servicio;
                $reclamo->renta=$transaccion->importe;
                $reclamo->propiedad=$propiedad;
                $reclamo->iccid='';
                $reclamo->fecha=$transaccion->fecha;
                $reclamo->plazo=$transaccion->plazo;
                $reclamo->contrato=$transaccion->contrato;
                $reclamo->cuenta='';
                $reclamo->marca='AT&T';
                $reclamo->periodo=$request->periodo;
                $reclamo->observacion='Residual Inicial NO Pagado';
                $reclamo->comision=$transaccion->importe/1.16*0.05;
                $reclamo->mes=$request->periodo;
                $reclamo->conciliacion_id=$request->conciliacion_id;
                $reclamo->save();
                $x=$x+1;
            }
        }
        $respuesta["registros"]=$x;
        return($respuesta);
    }
    public function fraude_aviso1(Request $request)
    {
        $respuesta=array(
            'registros'=>0,
        );
        $x=0;
        $periodo_menos_1=$this->getPeriodo($request->periodo,-1);
        $periodo_menos_2=$this->getPeriodo($request->periodo,-2);
        $transacciones_menos_2=DB::select(DB::raw(
        "
        select c.contrato,c.contrato1,c.comision1,c.estatus1,d.contrato2,d.comision2,d.estatus2 from 
	    (   
	        select a.contrato,b.contrato as contrato1,b.comision as comision1,b.estatus as estatus1 from 
		        (select DISTINCT contrato from transaccions where cb_att=0 and periodo='$periodo_menos_2' and tipo_venta not like '%rotec%' and tipo_venta not like '%ADD%') as a
			        LEFT JOIN
		        (select contrato,comision,estatus from residuals where periodo='$periodo_menos_1') as b
			        ON a.contrato=b.contrato
        ) as c 
    	LEFT JOIN
        (select contrato as contrato2,comision as comision2,estatus as estatus2 from residuals where periodo='$request->periodo') as d 
    	ON c.contrato=d.contrato2
        "
        ));
        $resultset=collect($transacciones_menos_2);
        $deletedRows = Alerta::where('conciliacion_id', $request->conciliacion_id)
                ->where('tipo','like','1%')
                ->delete();
        $alertas=$resultset->where('estatus2','SUSPENDIDO');
        foreach($alertas as $alerta)
        {
            
            $tipo=0;
            if($alerta->estatus1=="SUSPENDIDO")
            {
                $tipo=1;
            }
            if(is_null($alerta->comision1))
            {
                $tipo=12;
            }
            if($tipo!=0)
            {
                $transaccion=Transaccion::where('periodo',$periodo_menos_2)
                    ->where('contrato',$alerta->contrato)
                    ->where('tipo_venta','not like','%rotecc%')
                    ->where('tipo_venta','not like','%ADD%')
                    ->get()
                    ->first();

                $registro_alerta=new Alerta();
                $registro_alerta->contrato=$alerta->contrato;
                $registro_alerta->tipo_venta=$transaccion->tipo_venta;
                $registro_alerta->plan=$transaccion->servicio;
                $registro_alerta->fecha=$transaccion->fecha;
                $registro_alerta->importe=$transaccion->importe;
                $registro_alerta->numero_empleado=$transaccion->numero_empleado;
                $registro_alerta->empleado=$transaccion->empleado;
                $registro_alerta->udn=$transaccion->udn;
                $registro_alerta->pdv=$transaccion->pdv;
                $registro_alerta->tipo=$tipo;
                $registro_alerta->periodo=$request->periodo;
                $registro_alerta->conciliacion_id=$request->conciliacion_id;
                $registro_alerta->save();
                $x=$x+1;
            }
        }
        $respuesta["registros"]=$x;
        return($respuesta);
    }
    public function fraude_aviso2(Request $request)
    {
        $respuesta=array(
            'registros'=>0,
        );
        $x=0;
        $periodo_menos_1=$this->getPeriodo($request->periodo,-1);
        $periodo_menos_2=$this->getPeriodo($request->periodo,-2);
        $periodo_menos_3=$this->getPeriodo($request->periodo,-3);
        $transacciones_menos_3=DB::select(DB::raw(
        "
        select e.contrato,e.contrato1,e.comision1,e.estatus1,e.contrato2,e.comision2,e.estatus2,f.contrato3,f.comision3,f.estatus3 from 
        (
        select c.contrato,c.contrato1,c.comision1,c.estatus1,d.contrato2,d.comision2,d.estatus2 from 
            (   
                select a.contrato,b.contrato as contrato1,b.comision as comision1,b.estatus as estatus1 from 
                    (select DISTINCT contrato from transaccions where cb_att=0 and periodo='$periodo_menos_3' and tipo_venta not like '%rotec%' and tipo_venta not like '%ADD%') as a
                        LEFT JOIN
                    (select contrato,comision,estatus from residuals where periodo='$periodo_menos_2') as b
                        ON a.contrato=b.contrato
            ) as c 
            LEFT JOIN
            (select contrato as contrato2,comision as comision2,estatus as estatus2 from residuals where periodo='$periodo_menos_1') as d 
            ON c.contrato=d.contrato2
        ) as e
        LEFT JOIN
        (select contrato as contrato3,comision as comision3,estatus as estatus3 from residuals where periodo='$request->periodo') as f
        ON e.contrato=f.contrato3
        "
        ));
        $resultset=collect($transacciones_menos_3);
        $deletedRows = Alerta::where('conciliacion_id', $request->conciliacion_id)
                ->where('tipo','like','2%')
                ->delete();
        $alertas=$resultset->where('estatus2','SUSPENDIDO')->where('estatus3','SUSPENDIDO');
        foreach($alertas as $alerta)
        {
            $tipo=0;
            if($alerta->estatus1=="SUSPENDIDO")
            {
                $tipo=2;
            }
            if(is_null($alerta->comision1))
            {
                $tipo=22;
            }
            if($tipo!=0)
            {
                $transaccion=Transaccion::where('periodo',$periodo_menos_3)
                    ->where('contrato',$alerta->contrato)
                    ->where('tipo_venta','not like','%rotecc%')
                    ->where('tipo_venta','not like','%ADD%')
                    ->get()
                    ->first();
        
                $registro_alerta=new Alerta();
                $registro_alerta->contrato=$alerta->contrato;
                $registro_alerta->tipo_venta=$transaccion->tipo_venta;
                $registro_alerta->plan=$transaccion->servicio;
                $registro_alerta->fecha=$transaccion->fecha;
                $registro_alerta->importe=$transaccion->importe;
                $registro_alerta->numero_empleado=$transaccion->numero_empleado;
                $registro_alerta->empleado=$transaccion->empleado;
                $registro_alerta->udn=$transaccion->udn;
                $registro_alerta->pdv=$transaccion->pdv;
                $registro_alerta->tipo=$tipo;
                $registro_alerta->periodo=$request->periodo;
                $registro_alerta->conciliacion_id=$request->conciliacion_id;
                $registro_alerta->save();
                $x=$x+1;
            }
        }
        $respuesta["registros"]=$x;
        return($respuesta);
    }
    public function alerta_cb(Request $request)
    {
        $respuesta=array(
            'registros'=>0,
        );
        $x=0;
        $periodo_menos_1=$this->getPeriodo($request->periodo,-1);
        $periodo_menos_2=$this->getPeriodo($request->periodo,-2);
        $periodo_menos_3=$this->getPeriodo($request->periodo,-3);
        $periodo_menos_4=$this->getPeriodo($request->periodo,-4);
        $transacciones_menos_4=DB::select(DB::raw(
        "
        select g.contrato,g.contrato1,g.comision1,g.estatus1,g.contrato2,g.comision2,g.estatus2,g.contrato3,g.comision3,g.estatus3,h.contrato4,h.comision4,h.estatus4 FROM
(
	select e.contrato,e.contrato1,e.comision1,e.estatus1,e.contrato2,e.comision2,e.estatus2,f.contrato3,f.comision3,f.estatus3 from 
        (
        select c.contrato,c.contrato1,c.comision1,c.estatus1,d.contrato2,d.comision2,d.estatus2 from 
            (   
                select a.contrato,b.contrato as contrato1,b.comision as comision1,b.estatus as estatus1 from 
                    (select DISTINCT contrato from transaccions where cb_att=0 and periodo='$periodo_menos_4' and tipo_venta not like '%rotec%' and tipo_venta not like '%ADD%') as a
                        LEFT JOIN
                    (select contrato,comision,estatus from residuals where periodo='$periodo_menos_3') as b
                        ON a.contrato=b.contrato
            ) as c 
            LEFT JOIN
            (select contrato as contrato2,comision as comision2,estatus as estatus2 from residuals where periodo='$periodo_menos_2') as d 
            ON c.contrato=d.contrato2
        ) as e
        LEFT JOIN
        (select contrato as contrato3,comision as comision3,estatus as estatus3 from residuals where periodo='$periodo_menos_1') as f
        ON e.contrato=f.contrato3) as g
LEFT JOIN
	(select contrato as contrato4,comision as comision4,estatus as estatus4 from residuals where periodo='$request->periodo') as h 
    ON g.contrato=h.contrato4
        "
        ));
        $resultset=collect($transacciones_menos_4);
        $deletedRows = Alerta::where('conciliacion_id', $request->conciliacion_id)
                ->where('tipo','like','3%')
                ->delete();
        $alertas=$resultset->where('estatus2','SUSPENDIDO')->where('estatus3','SUSPENDIDO')->where('estatus4','SUSPENDIDO');
        foreach($alertas as $alerta)
        {

            $tipo=0;
            if($alerta->estatus1=="SUSPENDIDO")
            {
                $tipo=3;
            }
            if(is_null($alerta->comision1))
            {
                $tipo=32;
            }
            if($tipo!=0)
            {
            $transaccion=Transaccion::where('periodo',$periodo_menos_4)
                    ->where('contrato',$alerta->contrato)
                    ->where('tipo_venta','not like','%rotecc%')
                    ->where('tipo_venta','not like','%ADD%')
                    ->get()
                    ->first();

                $registro_alerta=new Alerta();
                $registro_alerta->contrato=$alerta->contrato;
                $registro_alerta->tipo_venta=$transaccion->tipo_venta;
                $registro_alerta->plan=$transaccion->servicio;
                $registro_alerta->fecha=$transaccion->fecha;
                $registro_alerta->importe=$transaccion->importe;
                $registro_alerta->numero_empleado=$transaccion->numero_empleado;
                $registro_alerta->empleado=$transaccion->empleado;
                $registro_alerta->udn=$transaccion->udn;
                $registro_alerta->pdv=$transaccion->pdv;
                $registro_alerta->tipo=$tipo;
                $registro_alerta->periodo=$request->periodo;
                $registro_alerta->conciliacion_id=$request->conciliacion_id;
                $registro_alerta->save();
                $x=$x+1;
            }
        }
        $respuesta["registros"]=$x;
        return($respuesta);
    }
    public function getPeriodo($periodo,$offset)
    {
        $periodos=SecuenciaPeriodo::where('periodo',$periodo)
                ->get()
                ->first();
        $periodos2=SecuenciaPeriodo::where('id',$periodos->id+$offset)
                ->get()
                ->first();
        return($periodos2->periodo);
    }
    public function detalle_alertas(Request $request)
    {
        $conciliacion_id=$request->conciliacion_id;
        $periodo=$request->periodo;
        $reclamos=DB::table('reclamos')
            ->select(DB::raw('count(*) as erp_att'))
            ->where('conciliacion_id',$conciliacion_id)
            ->where('observacion','Comision no Pagada')
            ->get()
            ->first();
        $erp_att=$reclamos->erp_att;

        $reclamos=DB::table('reclamos')
            ->select(DB::raw('count(*) as regla_45d'))
            ->where('conciliacion_id',$conciliacion_id)
            ->where('observacion','Residual Inicial NO Pagado')
            ->get()
            ->first();
        $regla_45d=$reclamos->regla_45d;

        $alertas=DB::table('alertas')
            ->select(DB::raw('count(*) as fraude_aviso1'))
            ->where('conciliacion_id',$conciliacion_id)
            ->where('tipo','like','1%')
            ->get()
            ->first();
        $fraude_aviso1=$alertas->fraude_aviso1;

        $alertas=DB::table('alertas')
            ->select(DB::raw('count(*) as fraude_aviso2'))
            ->where('conciliacion_id',$conciliacion_id)
            ->where('tipo','like','2%')
            ->get()
            ->first();
        $fraude_aviso2=$alertas->fraude_aviso2;

        $alertas=DB::table('alertas')
            ->select(DB::raw('count(*) as alerta_cb'))
            ->where('conciliacion_id',$conciliacion_id)
            ->where('tipo','like','3%')
            ->get()
            ->first();
        $alerta_cb=$alertas->alerta_cb;

        $periodo_menos_1=$this->getPeriodo($periodo,-1);
        $periodo_menos_2=$this->getPeriodo($periodo,-2);
        $periodo_menos_3=$this->getPeriodo($periodo,-3);
        $periodo_menos_4=$this->getPeriodo($periodo,-4);

        return view('detalle_conciliacion',['periodo'=>$periodo,
                                       'conciliacion_id'=>$conciliacion_id,
                                       'erp_att'=>$erp_att,
                                       'regla_45d'=>$regla_45d,
                                       'fraude_aviso1'=>$fraude_aviso1,
                                       'fraude_aviso2'=>$fraude_aviso2,
                                       'alerta_cb'=>$alerta_cb,
                                       'periodo_menos_1'=>$periodo_menos_1,
                                       'periodo_menos_2'=>$periodo_menos_2,
                                       'periodo_menos_3'=>$periodo_menos_3,
                                       'periodo_menos_4'=>$periodo_menos_4
                                      ]);
    }
    public function conciliacion_terminar(Request $request)
    {
        Conciliacion::where('id',$request->conciliacion_id)
                ->update(['terminado'=>true]);
    }
    public function conciliacion_consulta(Request $request)
    {
        $calculoRow=Conciliacion::where('id',$request->conciliacion_id)
        ->get();
        return($calculoRow);
    }
}
