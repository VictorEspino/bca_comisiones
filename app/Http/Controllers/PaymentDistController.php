<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Distribuidor;
use App\Models\CalculoDistribuidores;
use App\Models\PaymentDistribuidor;
use App\Models\BalanceComisionDistribuidor;

class PaymentDistController extends Controller
{
    public function generar_pagos_distribuidores(Request $request)
    {
        $id_calculo=$request->id;
        $calculo=CalculoDistribuidores::find($id_calculo);
        $deletedRows = PaymentDistribuidor::where('calculo_id', $id_calculo)->delete();
        $sql_balance=
        "select a.numero_distribuidor, sum(a.comision) as comision,sum(a.cb) as cb,sum(a.retroactivo) as retroactivo,sum(a.residual) as residual from 
            (
            SELECT numero_distribuidor,comision_final as comision,0 as cb,0 as retroactivo,0 as residual FROM `balance_comision_distribuidors` WHERE calculo_id=$id_calculo 
            UNION 
            SELECT numero_distribuidor,0 as comision,cb as cb,0 as retroactivo,0 as residual FROM `charge_back_distribuidors` WHERE calculo_id=$id_calculo 
            UNION
            SELECT numero_distribuidor,0 as comision,0 as cb,retroactivo as retroactivo,0 as residual FROM `retroactivo_distribuidors` WHERE calculo_id=$id_calculo 
            UNION
            SELECT numero_distribuidor,0 as comision,0 as cb,0 as retroactivo,residual as residual FROM `residual_distribuidors` WHERE calculo_id=$id_calculo 
            ) as a
            group by a.numero_distribuidor
            ";
        $balances=DB::select(DB::raw(
            $sql_balance
               ));
        foreach ($balances as $balance) {
            $distribuidor=Distribuidor::where('numero_distribuidor',$balance->numero_distribuidor)
                            ->get()
                            ->first();
            $pago=new PaymentDistribuidor();
            $pago->numero_distribuidor=$balance->numero_distribuidor;
            $pago->distribuidor=$distribuidor->nombre;
            $pago->charge_back=$balance->cb;
            $pago->comision=$balance->comision;
            $pago->retroactivo=$balance->retroactivo;
            $pago->residual=$balance->residual;
            $pago->adelantos=0;
            if($calculo->tipo=="2")
            {
                $sql_adelantos="
                SELECT sum(a_pagar) as adelantos FROM 
                payment_distribuidors,calculo_distribuidores 
                WHERE 
                payment_distribuidors.calculo_id=calculo_distribuidores.id and 
                payment_distribuidors.estatus=1 and 
                calculo_distribuidores.tipo=1 and
                payment_distribuidors.numero_distribuidor='".$balance->numero_distribuidor."' and 
                calculo_distribuidores.fecha_inicio>='".$calculo->fecha_inicio."' and 
                calculo_distribuidores.fecha_fin<='".$calculo->fecha_fin."'
                ";
                //dd($sql_adelantos);
                $adelantos_pagados=DB::select(DB::raw(
                    $sql_adelantos
                       ));
                $adelantos_pagados=collect($adelantos_pagados)->first();
                try{
                $pago->adelantos=!is_null($adelantos_pagados->adelantos)?$adelantos_pagados->adelantos:0;
                }
                catch(\Exception $e)
                {
                    $pago->adelantos=0;
                }
            }
            
            
            $pago->a_pagar=$balance->comision+$balance->residual+$balance->retroactivo-$balance->cb-$pago->adelantos;
            $pago->calculo_id=$id_calculo;
            $pago->save();
        }
        return($balances);
    }
}
