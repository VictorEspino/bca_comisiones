<?php

namespace App\Http\Controllers;

use App\Models\BalanceComisionDistribuidor;
use App\Models\Distribuidor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceComisionesDistController extends Controller
{
    public function balance_distribuidores(Request $request)
    {
        $id_calculo=$request->id;
        DB::delete('delete from balance_comision_distribuidors where calculo_id='.$id_calculo);

        $sql_mediciones=
            "
            select numero_distribuidor,
            sum(a.u_act) as u_act,sum(a.r_act) as r_act,sum(a.c_act) as c_act,
            sum(a.u_aep) as u_aep,sum(a.r_aep) as r_aep,sum(a.c_aep) as c_aep,
            sum(a.u_ren) as u_ren,sum(a.r_ren) as r_ren,sum(a.c_ren) as c_ren,
            sum(a.u_rep) as u_rep,sum(a.r_rep) as r_rep,sum(a.c_rep) as c_rep,
            sum(a.u_seg) as u_seg,sum(a.r_seg) as r_seg,sum(a.c_seg) as c_seg,
            sum(a.u_add) as u_add,sum(a.r_add) as r_add,sum(a.c_add) as c_add
            from (
                SELECT numero_distribuidor,COUNT(tipo_venta) as u_act, SUM(importe) as r_act, SUM(comision) as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccion_distribuidors where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Activación' OR tipo_venta='Activacion')
                group by numero_distribuidor
                UNION
                SELECT numero_distribuidor,0 as u_act,0 as r_act,0 as c_act,
                COUNT(tipo_venta) as u_aep,SUM(importe) as r_aep,SUM(comision) as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccion_distribuidors where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Activación Equipo Propio' OR tipo_venta='Activacion Equipo Propio')
                group by numero_distribuidor
                UNION
                SELECT numero_distribuidor,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                COUNT(tipo_venta) as u_ren,SUM(importe) as r_ren,SUM(comision) as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccion_distribuidors where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Renovación' OR tipo_venta='Renovacion')
                group by numero_distribuidor
                UNION
                SELECT numero_distribuidor,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                COUNT(tipo_venta) as u_rep,SUM(importe) as r_rep,SUM(comision) as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccion_distribuidors where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Renovación Equipo Propio' OR tipo_venta='Renovacion Equipo Propio')
                group by numero_distribuidor
                UNION
                SELECT numero_distribuidor,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                COUNT(tipo_venta) as u_seg,SUM(importe) as r_seg,SUM(comision) as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccion_distribuidors where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Protección de equipo' OR tipo_venta='Proteccion de equipo')
                group by numero_distribuidor
                UNION
                SELECT numero_distribuidor,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                COUNT(tipo_venta) as u_add,SUM(importe) as r_add,SUM(comision) as c_add
                FROM transaccion_distribuidors where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='ADD ON' OR tipo_venta='ADD ON')
                group by numero_distribuidor
                ) as a group by a.numero_distribuidor
            ";
            $mediciones=DB::select(DB::raw(
                $sql_mediciones
               ));

               

        $mediciones=collect($mediciones);

        $distribuidores=Distribuidor::all();
        $registros_medidos=0;
        $errores=0;

        foreach($mediciones as $medicion)
        {
            $cumplimiento=$this->getAlcance(
                            $medicion,
                            $distribuidores->where('numero_distribuidor',$medicion->numero_distribuidor)->first()
                            );
            if($cumplimiento["status"]=="OK")
            {
                $balance_row=new BalanceComisionDistribuidor();
                $balance_row->numero_distribuidor=$medicion->numero_distribuidor;
                $balance_row->distribuidor=$cumplimiento["nombre"];
                $balance_row->uds_activacion=$medicion->u_act;
                $balance_row->renta_activacion=$medicion->r_act;
                $balance_row->comision_activacion=$medicion->c_act;
                $balance_row->uds_aep=$medicion->u_aep;
                $balance_row->renta_aep=$medicion->r_aep;
                $balance_row->comision_aep=$medicion->c_aep;
                $balance_row->uds_renovacion=$medicion->u_ren;
                $balance_row->renta_renovacion=$medicion->r_ren;
                $balance_row->comision_renovacion=$medicion->c_ren;
                $balance_row->uds_rep=$medicion->u_rep;
                $balance_row->renta_rep=$medicion->r_rep;
                $balance_row->comision_rep=$medicion->c_rep;
                $balance_row->uds_seguro=$medicion->u_seg;
                $balance_row->renta_seguro=$medicion->r_seg;
                $balance_row->comision_seguro=$medicion->c_seg;
                $balance_row->uds_addon=$medicion->u_add;
                $balance_row->renta_addon=$medicion->r_add;
                $balance_row->comision_addon=$medicion->c_add;
                $balance_row->comision_final=$medicion->c_act+$medicion->c_aep+$medicion->c_ren+$medicion->c_rep+$medicion->c_seg+$medicion->c_add;
                $balance_row->calculo_id=$id_calculo;
                $balance_row->save();
                $registros_medidos=$registros_medidos+1;
            }
            else
            {
                $error=new Error();
                $error->calculo_id=$id_calculo;
                $error->operacion="Balance Distribuidores";
                $error->error="No se encuentra numero_distribuidor=".$medicion->numero_distribuidor;
                $error->save();
                $errores=$errores+1;
            }
            
        }
        return($registros_medidos." errores:".$errores);
    }

    private function getAlcance($mediciones,$distribuidor)
    {
        $respuesta=array(

            'nombre'=>'',
            'status'=>'Fail'
        );
        if(is_null($distribuidor)) return($respuesta);
        $respuesta["nombre"]=$distribuidor->nombre;
        $respuesta["status"]="OK";
        return($respuesta);
    }
}
