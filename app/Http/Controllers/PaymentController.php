<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BalanceComisionVenta;
use App\Models\BalanceComisionGerente;
use App\Models\BalanceComisionRegional;
use App\Models\BalanceComisionDirector;
use App\Models\Cuota;
use App\Models\Empleado;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function generar_pagos(Request $request)
    {
        $id_calculo=$request->id;
        $deletedRows = Payment::where('calculo_id', $id_calculo)->delete();
        $balances=DB::select(DB::raw(
            "select a.empleado, sum(a.comision_ventas) as comision_ventas, sum(a.comision_gerente) as comision_gerente,sum(a.comision_regional) as comision_regional, sum(a.comision_director) as comision_director,sum(a.adeudo_anterior) as adeudo_anterior,sum(a.charge_back) as charge_back,sum(a.retroactivo) as retroactivo from 
            (
            SELECT numero_empleado empleado,comision_final comision_ventas,0 as comision_gerente, 0 as comision_regional, 0 as comision_director,0 as adeudo_anterior,0 as charge_back,0 as retroactivo FROM `balance_comision_ventas` WHERE calculo_id=$id_calculo 
            UNION 
            select numero_empleado empleado,0 as comision_ventas,comision_final comision_gerente, 0 as comision_regional, 0 as comision_director,0 as adeudo_anterior,0 as charge_back,0 as retroactivo FROM `balance_comision_gerentes` WHERE calculo_id=$id_calculo  
            UNION 
            select numero_empleado empleado,0 as comision_ventas,0 as comision_gerente,comision_final comision_regional,0 as comision_director,0 as adeudo_anterior,0 as charge_back,0 as retroactivo FROM `balance_comision_regionals` WHERE calculo_id=$id_calculo 
            UNION 
            select numero_empleado empleado,0 as comision_ventas,0 as comision_gerente,0 as comision_regional,comision_final comision_director,0 as adeudo_anterior,0 as charge_back,0 as retroactivo FROM `balance_comision_directors` WHERE calculo_id=$id_calculo 
            UNION
            SELECT numero_empleado empleado,0 as comision_ventas,0 as comision_gerente,0 as comision_regional,0 as comision_director, 0 as adeudo_anterior, sum(cb) as charge_back,0 as retroactivo FROM `charge_back_internos` WHERE calculo_id=$id_calculo  group by numero_empleado
            UNION
            SELECT numero_empleado empleado,0 as comision_ventas,0 as comision_gerente,0 as comision_regional,0 as comision_director, 0 as adeudo_anterior, 0 as charge_back, sum(retroactivo) as retoactivo FROM retroactivos WHERE calculo_id=$id_calculo group by numero_empleado
            UNION
            select numero_empleado empleado,0 as comision_ventas,0 as comision_gerente,0 as comision_regional,0 as comision_director,adeudo as adeudo_anterior,0 as charge_back,0 as retroactivo from empleados where calculo_id in (select max(id) as id from calculos where id<>$id_calculo  and adeudo>0)
            ) as a
            group by a.empleado
            "
               ));
        foreach ($balances as $balance) {
            $empleado=Empleado::where('numero_empleado',$balance->empleado)
                            ->where('calculo_id',$id_calculo)
                            ->get();
            $sueldo=0.0;
            $modalidad=2;
            $estatus="Desconocido";
            $registro_existente=false;
            if($empleado->isEmpty())
            {
                $sueldo=0.0;
                $modalidad="2";
                $estatus="Desconocido";
            }
            else
            {   
                $registro_existente=true;
                $sueldo=$empleado->first()->sueldo;
                $modalidad=$empleado->first()->modalidad;
                $estatus=$empleado->first()->estatus;
            }
            $pago=new Payment();
            $pago->numero_empleado=$balance->empleado;
            $pago->comision_ventas=$balance->comision_ventas;
            $pago->comision_gerente=$balance->comision_gerente;
            $pago->comision_regional=$balance->comision_regional;
            $pago->comision_director=$balance->comision_director;
            $pago->adeudo_anterior=$balance->adeudo_anterior;
            $pago->charge_back=$balance->charge_back;
            $pago->retroactivo=$balance->retroactivo;
            $pago->sueldo=$sueldo;
            $pago->modalidad=$modalidad;
            $pago->estatus=$estatus;

            $total_ingresos=$balance->comision_ventas+$balance->comision_gerente+$balance->comision_regional+$balance->comision_director+$balance->retroactivo;
            $total_retiros=$balance->adeudo_anterior+$balance->charge_back;

            $pago_calculado=0;
            $adeudo=0;
            $subsidio=0;

            //if($total_retiros>=1000)
            //{
            //    $total_retiros=1000;
            //}
            //if($total_retiros>=75000)
            //{
            //    $total_retiros=1250;
            //}
            //if($total_retiros>=100000)
            //{
            //    $total_retiros=1500;
            //}

            

            if($modalidad=="2" || $modalidad=="3") //TIENE SUELDO FIJO
            {
                if($total_retiros<=$total_ingresos)
                {
                    $pago_calculado=$total_ingresos-$total_retiros;
                    $adeudo=$balance->adeudo_anterior+$balance->charge_back-$total_retiros;
                    $subsidio=0;
                }
                else
                {
                    $pago_calculado=0;
                    $adeudo=$balance->adeudo_anterior+$balance->charge_back-$total_ingresos;
                    $subsidio=0;
                }
            }
            if($modalidad=="1") //ANTICIPO DE COMISIONES
            {
                if($total_ingresos>=$sueldo)
                {
                    $pago_calculado=$total_ingresos-$sueldo;
                    if($total_retiros<=$pago_calculado)
                    {
                        $pago_calculado=$pago_calculado-$total_retiros;
                        $adeudo=$balance->adeudo_anterior+$balance->charge_back-$total_retiros;
                        $subsidio=0;
                    }
                    else
                    {
                        $adeudo=$balance->adeudo_anterior+$balance->charge_back-$pago_calculado;
                        $pago_calculado=0;
                        $subsidio=0;
                    }
                }
                else
                {
                    $pago_calculado=0;
                    $adeudo=$balance->adeudo_anterior+$balance->charge_back;
                    $subsidio=$sueldo-$total_ingresos;
                }
            }


            $pago->a_pagar=$pago_calculado;
            $pago->adeudo=$adeudo;
            $pago->subsidio=$subsidio;
            $pago->calculo_id=$id_calculo;
            $pago->save();

            if($adeudo>0)
            {
                if(!$registro_existente)
                {
                    $empleado_control=new Empleado();
                    $empleado_control->numero_empleado=$balance->empleado;
                    $empleado_control->nombre="Sin comisiones en este periodo";
                    $empleado_control->udn=0;
                    $empleado_control->pdv='';
                    $empleado_control->puesto='-';
                    $empleado_control->ingreso='2000-01-01';
                    $empleado_control->adeudo=$adeudo;
                    $empleado_control->calculo_id=$id_calculo;
                    $empleado_control->estatus='Undefined';
                    $empleado_control->sueldo=0.0;
                    $empleado_control->modalidad=2;
                    $empleado_control->save();
                }
                else
                {
                    Empleado::where('numero_empleado',$balance->empleado)
                            ->where('calculo_id',$id_calculo)
                            ->update(['adeudo'=>$adeudo]);
                }
            }
        }
        return($balances);
    }
    public function ajuste_25(Request $request)
    {
        $id_calculo=$request->id;
        $empleados=DB::table("ajuste_25")->where('calculo_id',$id_calculo)->get();
        foreach($empleados as $empleado)
        {
            $balance=BalanceComisionVenta::where('calculo_id',$id_calculo)
                ->where('numero_empleado',$empleado->empleado)
                ->where('udn',$empleado->udn)
                ->get()
                ->first();
            $id=$balance->id;
            $comision_activacion=$balance->comision_activacion;
            $comision_aep=$balance->comision_aep;
            $comision_renovacion=$balance->comision_renovacion;
            $comision_rep=$balance->comision_rep;
            $comision_seguro=$balance->comision_seguro;
            $comision_addon=$balance->comision_addon;

            $actualizado=BalanceComisionVenta::where('id',$id)
            ->update(
                [
                    'porcentaje_cobro'=>0.25,
                    'cumple_objetivo'=>0,
                    'comision_final_activacion'=>$comision_activacion*0.25,
                    'comision_final_aep'=>$comision_aep*0.25,
                    'comision_final_renovacion'=>$comision_renovacion*0.25,
                    'comision_final_rep'=>$comision_rep*0.25,
                    'comision_final_seguro'=>$comision_seguro*0.25,
                    'comision_final_addon'=>$comision_addon*0.25,
                    'comision_final'=>($comision_activacion+$comision_aep+$comision_renovacion+$comision_rep+$comision_seguro+$comision_addon)*0.25
                ]
            );

            
        }
        return($empleados);
    }
}
