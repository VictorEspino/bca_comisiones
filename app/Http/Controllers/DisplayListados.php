<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuota;
use App\Models\Transaccion;
use App\Models\Calculo;
use App\Models\User;
use App\Models\CalculoDistribuidores;
use App\Models\Empleado;
use App\Models\BalanceComisionGerente;
use App\Models\BalanceComisionesVenta;
use App\Models\BalanceComisionesRegional;
use App\Models\BalanceComisionesDirector;
use App\Models\PaymentDistribuidor;
use App\Models\Conciliacion;
use App\Models\LogConsulta;
use App\Models\TransaccionDistribuidor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DisplayListados extends Controller
{
    public function listado_calculos(Request $request)
    {
        return view('lista_calculos');
    }
    public function detalle_calculo(Request $request)
    {
        $cuotas=DB::table('cuotas')
                ->select('calculo_id',DB::raw('sum(activaciones) as activaciones'),DB::raw('sum(aep) as aep'),DB::raw('sum(renovaciones) as renovaciones'),DB::raw('sum(rep) as rep'))
                ->where('calculo_id',$request->id)
                ->groupBy('calculo_id')
                ->get()
                ->first();

        $calculo=Calculo::find($request->id);

        $transacciones=DB::table('transaccions')
                ->select('calculo_id','tipo_venta',DB::raw('count(tipo_venta) as unidades'),DB::raw('sum(importe) as rentas'))
                ->where('calculo_id',$request->id)
                //->where('credito',1)
                ->groupBy('calculo_id','tipo_venta')
                ->get();
        $tr_activaciones=0;
        $tr_renta_activ=0;
        $tr_aep=0;
        $tr_renta_aep=0;
        $tr_renovaciones=0;
        $tr_renta_renov=0;
        $tr_rep=0;
        $tr_renta_rep=0;
        $tr_seguros=0;
        $tr_renta_seguros=0;
        $tr_addon=0;
        $tr_renta_addon=0;
        foreach($transacciones as $transaccion)
        {
            if($transaccion->tipo_venta=="Activación" || $transaccion->tipo_venta=="Activacion"){
                $tr_activaciones=$tr_activaciones+$transaccion->unidades;
                $tr_renta_activ=$tr_renta_activ+$transaccion->rentas;
            }
            if($transaccion->tipo_venta=="Activación Equipo Propio" || $transaccion->tipo_venta=="Activacion Equipo Propio"){
                $tr_aep=$tr_aep+$transaccion->unidades;
                $tr_renta_aep=$tr_renta_aep+$transaccion->rentas;
            }
            if($transaccion->tipo_venta=="Renovación" || $transaccion->tipo_venta=="Renovacion" ||
               $transaccion->tipo_venta=="Renovación Empresarial" || $transaccion->tipo_venta=="Renovacion Empresarial"){
                $tr_renovaciones=$tr_renovaciones+$transaccion->unidades;
                $tr_renta_renov=$tr_renta_renov+$transaccion->rentas;
               }
            if($transaccion->tipo_venta=="Renovación Equipo Propio" || $transaccion->tipo_venta=="Renovacion Equipo Propio"){
                $tr_rep=$tr_rep+$transaccion->unidades;
                $tr_renta_rep=$tr_renta_rep+$transaccion->rentas;
            }
            if($transaccion->tipo_venta=="Protección de equipo" || $transaccion->tipo_venta=="Proteccion de equipo"){
                $tr_seguros=$tr_seguros+$transaccion->unidades;
                $tr_renta_seguros=$tr_renta_seguros+$transaccion->rentas;
            }
            if($transaccion->tipo_venta=="ADD ON"){
                $tr_addon=$tr_addon+$transaccion->unidades;
                $tr_renta_addon=$tr_renta_addon+$transaccion->rentas;
            }
        }

        return view('detalle_calculo',['id_calculo'=>$request->id,
                                       'fecha_inicio'=>$calculo->fecha_inicio,
                                       'fecha_fin'=>$calculo->fecha_fin,
                                       'descripcion'=>$calculo->descripcion,
                                       'terminado'=>$calculo->terminado,
                                       'activaciones'=>$cuotas->activaciones,
                                       'aep'=>$cuotas->aep,
                                       'renovaciones'=>$cuotas->renovaciones,
                                       'rep'=>$cuotas->rep,
                                       'tr_activaciones'=>$tr_activaciones,
                                       'tr_renta_activ'=>$tr_renta_activ,
                                       'tr_aep'=>$tr_aep,
                                       'tr_renta_aep'=>$tr_renta_aep,
                                       'tr_renovaciones'=>$tr_renovaciones,
                                       'tr_renta_renov'=>$tr_renta_renov,
                                       'tr_rep'=>$tr_rep,
                                       'tr_renta_rep'=>$tr_renta_rep,
                                       'tr_seguros'=>$tr_seguros,
                                       'tr_renta_seguros'=>$tr_renta_seguros,
                                       'tr_addon'=>$tr_addon,
                                       'tr_renta_addon'=>$tr_renta_addon
                                      ]);
    }
    public function transacciones_empleado(Request $request)
    {
        return view('transacciones_empleado',['id_calculo'=>$request->id,'id_empleado'=>$request->id_empleado]);
    }
    public function transacciones(Request $request)
    {
        return view('transacciones',['id_calculo'=>$request->id]);
    }
    public function transacciones_sucursal(Request $request)
    {
        return view('transacciones_gerente',['id_calculo'=>$request->id,'udn'=>$request->udn]);
    }
    public function balance_ejecutivos(Request $request)
    {
        return view('balance_ejecutivos',['id_calculo'=>$request->id]);
    }
    public function balance_gerentes(Request $request)
    {
        return view('balance_gerentes',['id_calculo'=>$request->id]);
    }
    public function balance_regionales(Request $request)
    {
        return view('balance_regionales',['id_calculo'=>$request->id]);
    }
    public function balance_director(Request $request)
    {
        return view('balance_director',['id_calculo'=>$request->id]);
    }
    public function pagos(Request $request)
    {
        return view('pagos',['id_calculo'=>$request->id]);
    }
    public function estado_cuenta(Request $request)
    {
        $calculo=0;
        $id_calculo=$request->id_calculo;
        $id_empleado=$request->id_empleado;

        //DESENCRIPTA NUMERO DE EMPLEADO
        $dec=DB::select(DB::raw("select AES_DECRYPT(UNHEX('".$request->id_empleado."'),'Bca2021')*1 as id_empleado from dual"));
        $id_empleado=$dec[0]->id_empleado;

        //DESENCRIPTA NUMERO DE EMPLEADO
        $dec=DB::select(DB::raw("select AES_DECRYPT(UNHEX('".$request->f_now."'),'Bca2021') as parametro, LPAD(now(),10,0) as hoy from DUAL"));
        $f_param=$dec[0]->parametro;
        $f_hoy=$dec[0]->hoy;

        if(!($f_param==$f_hoy))
         return ('ACCESO DENEGADO');


        if($id_calculo==0)
        {
            $calculo=DB::table('calculos')
                ->select(DB::raw('max(id) as id'))
                ->get()
                ->first()->id;
            /*
            $calculo1=DB::table('balance_comision_directors')
                ->select(DB::raw('max(calculo_id) as id'))
                ->where('numero_empleado',$id_empleado)
                ->get()
                ->first();
            $calculo2=DB::table('balance_comision_regionals')
                ->select(DB::raw('max(calculo_id) as id'))
                ->where('numero_empleado',$id_empleado)
                ->get()
                ->first();
            $calculo3=DB::table('balance_comision_gerentes')
                ->select(DB::raw('max(calculo_id) as id'))
                ->where('numero_empleado',$id_empleado)
                ->get()
                ->first();
            $calculo4=DB::table('balance_comision_ventas')
                ->select(DB::raw('max(calculo_id) as id'))
                ->where('numero_empleado',$id_empleado)
                ->get()
                ->first();
            $calculo=$this->mayorCalculo($calculo1->id,$calculo2->id,$calculo3->id,$calculo4->id);
            */
        }
        else
        {
            $calculo=$id_calculo;
        }
        $log=new LogConsulta();
        $log->numero_empleado=$id_empleado;
        $log->calculo_id=$calculo;
        $log->save();

        return view('estado_cuenta',['id_calculo'=>$calculo,
                                     'id_empleado'=>$id_empleado,
                                     'p1'=>$request->id_empleado,
                                     'p2'=>$request->f_now,
                                     'source'=>'erp'
                                        ]);
        //echo $calculo."-".$id_empleado;
    }
    public function estado_cuenta_interno(Request $request)
    {
        $calculo=0;
        $id_calculo=$request->id_calculo;
        $id_empleado=$request->id_empleado;

        if($id_calculo==0)
        {
            $calculo=DB::table('calculos')
                ->select(DB::raw('max(id) as id'))
                ->get()
                ->first()->id;
           /* $calculo1=DB::table('balance_comision_directors')
                ->select(DB::raw('max(calculo_id) as id'))
                ->where('numero_empleado',$id_empleado)
                ->get()
                ->first();
            $calculo2=DB::table('balance_comision_regionals')
                ->select(DB::raw('max(calculo_id) as id'))
                ->where('numero_empleado',$id_empleado)
                ->get()
                ->first();
            $calculo3=DB::table('balance_comision_gerentes')
                ->select(DB::raw('max(calculo_id) as id'))
                ->where('numero_empleado',$id_empleado)
                ->get()
                ->first();
            $calculo4=DB::table('balance_comision_ventas')
                ->select(DB::raw('max(calculo_id) as id'))
                ->where('numero_empleado',$id_empleado)
                ->get()
                ->first();
            $calculo=$this->mayorCalculo($calculo1->id,$calculo2->id,$calculo3->id,$calculo4->id);
            */
        }
        else
        {
            $calculo=$id_calculo;
        }
        return view('estado_cuenta',['id_calculo'=>$calculo,
                                     'id_empleado'=>$id_empleado,
                                     'p1'=>$request->id_empleado,
                                     'p2'=>$request->f_now,
                                     'source'=>'interno'
                                        ]);
        //echo $calculo."-".$id_empleado;
    }
    public function mayorCalculo($id1,$id2,$id3,$id4)
    {
        if(is_null($id1)){$id1=0;}
        if(is_null($id2)){$id2=0;}
        if(is_null($id3)){$id3=0;}
        if(is_null($id4)){$id4=0;}
        $calculo=$id1;
        if($id2>$calculo) {$calculo=$id2;}
        if($id3>$calculo) {$calculo=$id3;}
        if($id4>$calculo) {$calculo=$id4;}
        return($calculo);
    }
    public function calculos_guardados(Request $request)
    {
        $calculos=Calculo::orderBy('id','desc')
                ->get()
                ->take(6);
        return($calculos);
    }
    public function conciliaciones_guardadas(Request $request)
    {
        $conciliaciones=Conciliacion::orderBy('id','desc')
                ->get()
                ->take(6);
        return($conciliaciones);
    }
    public function listado_conciliaciones(Request $request)
    {
        return view('listado_conciliaciones');
    }
    public function aclaracion(Request $request)
    {
        return view('aclaracion',['periodo'=>$request->periodo,
                                  'conciliacion_id'=>$request->conciliacion_id,
                                  'concepto'=>$request->concepto]);
    }
    public function alerta(Request $request)
    {
        return view('alerta',['periodo'=>$request->periodo,
                              'conciliacion_id'=>$request->conciliacion_id,
                              'tipo'=>$request->tipo]);
    }

    public function listado_calculos_dist(Request $request)
    {
        return view('lista_calculos_dist');
    }
    public function calculos_guardados_dist_s(Request $request)
    {
        $calculos=CalculoDistribuidores::orderBy('id','desc')
                ->where('tipo','1')
                ->get()
                ->take(6);
        return($calculos);
    }
    public function calculos_guardados_dist_m(Request $request)
    {
        $calculos=CalculoDistribuidores::orderBy('id','desc')
                ->where('tipo','2')
                ->get()
                ->take(6);
        return($calculos);
    }
    public function detalle_calculo_dist(Request $request)
    {
        $calculo=CalculoDistribuidores::find($request->id);

        $transacciones=DB::table('transaccion_distribuidors')
                ->select('calculo_id','tipo_venta',DB::raw('count(tipo_venta) as unidades'),DB::raw('sum(importe) as rentas'))
                ->where('calculo_id',$request->id)
                //->where('credito',1)
                ->groupBy('calculo_id','tipo_venta')
                ->get();
        $tr_activaciones=0;
        $tr_renta_activ=0;
        $tr_aep=0;
        $tr_renta_aep=0;
        $tr_renovaciones=0;
        $tr_renta_renov=0;
        $tr_rep=0;
        $tr_renta_rep=0;
        $tr_seguros=0;
        $tr_renta_seguros=0;
        $tr_addon=0;
        $tr_renta_addon=0;
        foreach($transacciones as $transaccion)
        {
            if($transaccion->tipo_venta=="Activación" || $transaccion->tipo_venta=="Activacion"){
                $tr_activaciones=$tr_activaciones+$transaccion->unidades;
                $tr_renta_activ=$tr_renta_activ+$transaccion->rentas;
            }
            if($transaccion->tipo_venta=="Activación Equipo Propio" || $transaccion->tipo_venta=="Activacion Equipo Propio"){
                $tr_aep=$tr_aep+$transaccion->unidades;
                $tr_renta_aep=$tr_renta_aep+$transaccion->rentas;
            }
            if($transaccion->tipo_venta=="Renovación" || $transaccion->tipo_venta=="Renovacion" ||
               $transaccion->tipo_venta=="Renovación Empresarial" || $transaccion->tipo_venta=="Renovacion Empresarial"){
                $tr_renovaciones=$tr_renovaciones+$transaccion->unidades;
                $tr_renta_renov=$tr_renta_renov+$transaccion->rentas;
               }
            if($transaccion->tipo_venta=="Renovación Equipo Propio" || $transaccion->tipo_venta=="Renovacion Equipo Propio"){
                $tr_rep=$tr_rep+$transaccion->unidades;
                $tr_renta_rep=$tr_renta_rep+$transaccion->rentas;
            }
            if($transaccion->tipo_venta=="Protección de equipo" || $transaccion->tipo_venta=="Proteccion de equipo"){
                $tr_seguros=$tr_seguros+$transaccion->unidades;
                $tr_renta_seguros=$tr_renta_seguros+$transaccion->rentas;
            }
            if($transaccion->tipo_venta=="ADD ON"){
                $tr_addon=$tr_addon+$transaccion->unidades;
                $tr_renta_addon=$tr_renta_addon+$transaccion->rentas;
            }
        }

        return view('detalle_calculo_dist',['id_calculo'=>$request->id,
                                       'fecha_inicio'=>$calculo->fecha_inicio,
                                       'fecha_fin'=>$calculo->fecha_fin,
                                       'descripcion'=>$calculo->descripcion,
                                       'terminado'=>$calculo->terminado,
                                       'pagado_en' => $calculo->pagado_en,
                                       'tr_activaciones'=>$tr_activaciones,
                                       'tr_renta_activ'=>$tr_renta_activ,
                                       'tr_aep'=>$tr_aep,
                                       'tr_renta_aep'=>$tr_renta_aep,
                                       'tr_renovaciones'=>$tr_renovaciones,
                                       'tr_renta_renov'=>$tr_renta_renov,
                                       'tr_rep'=>$tr_rep,
                                       'tr_renta_rep'=>$tr_renta_rep,
                                       'tr_seguros'=>$tr_seguros,
                                       'tr_renta_seguros'=>$tr_renta_seguros,
                                       'tr_addon'=>$tr_addon,
                                       'tr_renta_addon'=>$tr_renta_addon
                                      ]);
    }
    public function balance_distribuidores(Request $request)
    {
        return view('balance_distribuidores',['id_calculo'=>$request->id]);
    }
    public function transacciones_distribuidores(Request $request)
    {
        return view('transacciones_distribuidores',['id_calculo'=>$request->id]);
    }
    public function pagos_distribuidores(Request $request)
    {
        return view('pagos_distribuidores',['id_calculo'=>$request->id]);
    }
    public function calculos_distribuidores(Request $request)
    {
        $titulo='';
        $query = DB::table('calculo_distribuidores')
        ->join('payment_distribuidors', 'calculo_distribuidores.id', '=', 'payment_distribuidors.calculo_id')
        ->where('payment_distribuidors.numero_distribuidor',Auth::user()->user)
        ->where('calculo_distribuidores.tipo',$request->tipo)
        ->select('calculo_distribuidores.id','calculo_distribuidores.descripcion','calculo_distribuidores.fecha_inicio','calculo_distribuidores.fecha_fin','calculo_distribuidores.pagado_en','payment_distribuidors.a_pagar')
        ->get();
        if($request->tipo=='1')
        {
            $titulo="Adelantos Semanales";
        }
        else
        {
            $titulo="Cierres Mensuales";
        }
        return(view('calculos_distribuidores',['titulo'=>$titulo,
                                               'query'=>$query,
                                              ]));
    }
    public function calculos_distribuidores_admin(Request $request)
    {
        $titulo='';
        $query = DB::table('calculo_distribuidores')
        ->where('calculo_distribuidores.tipo',$request->tipo)
        ->select('calculo_distribuidores.id','calculo_distribuidores.descripcion','calculo_distribuidores.fecha_inicio','calculo_distribuidores.fecha_fin','calculo_distribuidores.pagado_en')
        ->get();
        if($request->tipo=='1')
        {
            $titulo="Adelantos Semanales";
        }
        else
        {
            $titulo="Cierres Mensuales";
        }
        return(view('calculos_distribuidores_admin',['titulo'=>$titulo,
                                               'query'=>$query,
                                              ]));
    }
    public function lista_pagos_calculo(Request $request)
    {
        $calculo=CalculoDistribuidores::find($request->id);
        $titulo=$calculo->descripcion;
        $query = DB::table('payment_distribuidors')
        ->select('payment_distribuidors.distribuidor','payment_distribuidors.numero_distribuidor','payment_distribuidors.a_pagar','payment_distribuidors.pdf','payment_distribuidors.xml','payment_distribuidors.clabe','payment_distribuidors.titular')
        ->where('calculo_id',$request->id)
        ->orderBy('payment_distribuidors.distribuidor')
        ->get();
        return(view('lista_pagos_calculo',['titulo'=>$titulo,
                                               'query'=>$query,
                                               'fecha_inicio'=> $calculo->fecha_inicio,
                                               'fecha_fin'=> $calculo->fecha_fin,
                                               'id'=>$calculo->id,
                                               'fecha_pago'=>$calculo->pagado_en,
                                              ]));
    }
    public function estado_cuenta_distribuidor(Request $request)
    {
        if(isset($request->numero_distribuidor))
            {
                $usuario=$request->numero_distribuidor;
            }
        else{
            $usuario=Auth::user()->user;
        }


        $calculo=CalculoDistribuidores::find($request->id);

        $pago=PaymentDistribuidor::where('calculo_id',$request->id)
                            ->where('numero_distribuidor',$usuario)
                            ->get()
                            ->first();

        $masivos=TransaccionDistribuidor::select(DB::raw('tipo_venta,count(*) as lineas,sum(importe) as rentas,sum(comision) as comision'))
                                            ->where('calculo_id',$request->id)
                                            ->where('numero_distribuidor',$usuario)
                                            ->where('servicio','not like','%NEG%')
                                            ->where('credito',1)
                                            ->groupBy('tipo_venta')
                                            ->get();

        $empresariales=TransaccionDistribuidor::select(DB::raw('tipo_venta,count(*) as lineas,sum(importe) as rentas,sum(comision) as comision'))
                                            ->where('calculo_id',$request->id)
                                            ->where('numero_distribuidor',$usuario)
                                            ->where('servicio','like','%NEG%')
                                            ->where('credito',1)
                                            ->groupBy('tipo_venta')
                                            ->get();

        $cr0=TransaccionDistribuidor::select(DB::raw('razon_cr0,count(*) as lineas'))
        ->where('calculo_id',$request->id)
        ->where('numero_distribuidor',$usuario)
        ->where('credito',0)
        ->groupBy('razon_cr0')
        ->get();

 

        $ac_u_m=0;
        $ac_r_m=0;
        $ac_c_m=0;
        $as_u_m=0;
        $as_r_m=0;
        $as_c_m=0;
        $rc_u_m=0;
        $rc_r_m=0;
        $rc_c_m=0;
        $rs_u_m=0;
        $rs_r_m=0;
        $rs_c_m=0;
        foreach($masivos as $masivo)
        {
            if($masivo->tipo_venta=='Activacion' || $masivo->tipo_venta=="Activación")
            {
                $ac_u_m=$masivo->lineas;
                $ac_r_m=$masivo->rentas;
                $ac_c_m=$masivo->comision;
            }
            if($masivo->tipo_venta=='Activación Equipo Propio' || $masivo->tipo_venta=="Activacion Equipo Propio")
            {
                $as_u_m=$masivo->lineas;
                $as_r_m=$masivo->rentas;
                $as_c_m=$masivo->comision;
            }
            if($masivo->tipo_venta=='Renovacion' || $masivo->tipo_venta=="Renovación")
            {
                $rc_u_m=$masivo->lineas;
                $rc_r_m=$masivo->rentas;
                $rc_c_m=$masivo->comision;
            }
            if($masivo->tipo_venta=='Renovación Equipo Propio' || $masivo->tipo_venta=="Renovacion Equipo Propio")
            {
                $rs_u_m=$masivo->lineas;
                $rs_r_m=$masivo->rentas;
                $rs_c_m=$masivo->comision;
            }
        }
        $ac_u_e=0;
        $ac_r_e=0;
        $ac_c_e=0;
        $as_u_e=0;
        $as_r_e=0;
        $as_c_e=0;
        $rc_u_e=0;
        $rc_r_e=0;
        $rc_c_e=0;
        $rs_u_e=0;
        $rs_r_e=0;
        $rs_c_e=0;
        foreach($empresariales as $empresarial)
        {
            if($empresarial->tipo_venta=='Activacion' || $empresarial->tipo_venta=="Activación")
            {
                $ac_u_e=$empresarial->lineas;
                $ac_r_e=$empresarial->rentas;
                $ac_c_e=$empresarial->comision;
            }
            if($empresarial->tipo_venta=='Activación Equipo Propio' || $empresarial->tipo_venta=="Activacion Equipo Propio")
            {
                $as_u_e=$empresarial->lineas;
                $as_r_e=$empresarial->rentas;
                $as_c_e=$empresarial->comision;
            }
            if($empresarial->tipo_venta=='Renovacion' || $empresarial->tipo_venta=="Renovación")
            {
                $rc_u_e=$empresarial->lineas;
                $rc_r_e=$empresarial->rentas;
                $rc_c_e=$empresarial->comision;
            }
            if($empresarial->tipo_venta=='Renovación Equipo Propio' || $empresarial->tipo_venta=="Renovacion Equipo Propio")
            {
                $rs_u_e=$empresarial->lineas;
                $rs_r_e=$empresarial->rentas;
                $rs_c_e=$empresarial->comision;
            }
        }

        return(view('estado_cuenta_distribuidor',['descripcion'=>$calculo->descripcion,
                                                  'distribuidor'=>$pago->distribuidor,
                                                  'clabe'=>$pago->clabe,
                                                  'titular'=>$pago->titular,
                                                  'id'=>$request->id,
                                                  'comision'=>$pago->comision,
                                                  'residual'=>$pago->residual,
                                                  'retroactivo'=>$pago->retroactivo,
                                                  'anticipos'=>$pago->anticipos,
                                                  'cb'=>$pago->charge_back,
                                                  'a_pagar'=>$pago->a_pagar,
                                                  'ac_u_m'=>$ac_u_m,
                                                  'ac_r_m'=>$ac_r_m,
                                                  'ac_c_m'=>$ac_c_m,
                                                  'as_u_m'=>$as_u_m,
                                                  'as_r_m'=>$as_r_m,
                                                  'as_c_m'=>$as_c_m,
                                                  'rc_u_m'=>$rc_u_m,
                                                  'rc_r_m'=>$rc_r_m,
                                                  'rc_c_m'=>$rc_c_m,
                                                  'rs_u_m'=>$rs_u_m,
                                                  'rs_r_m'=>$rs_r_m,
                                                  'rs_c_m'=>$rs_c_m,
                                                  'ac_u_e'=>$ac_u_e,
                                                  'ac_r_e'=>$ac_r_e,
                                                  'ac_c_e'=>$ac_c_e,
                                                  'as_u_e'=>$as_u_e,
                                                  'as_r_e'=>$as_r_e,
                                                  'as_c_e'=>$as_c_e,
                                                  'rc_u_e'=>$rc_u_e,
                                                  'rc_r_e'=>$rc_r_e,
                                                  'rc_c_e'=>$rc_c_e,
                                                  'rs_u_e'=>$rs_u_e,
                                                  'rs_r_e'=>$rs_r_e,
                                                  'rs_c_e'=>$rs_c_e,
                                                  'pdf'=>$pago->pdf,
                                                  'xml'=>$pago->xml,
                                                  'usuario'=>$usuario,
                                                  'cr0'=>$cr0

                                                    ]));
    }
    public function export_transacciones_distribuidor(Request $request)
    {
        if(isset($request->numero_distribuidor))
            {
                $usuario=$request->numero_distribuidor;
            }
        else{
                $usuario=Auth::user()->user;
            }
        
        return view('export_transacciones_distribuidor',['id_calculo'=>$request->id,
                                                         'numero_distribuidor'=>$usuario,
                                                        ]);
    }
}
