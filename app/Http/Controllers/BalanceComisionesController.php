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
use App\Models\Error;

class BalanceComisionesController extends Controller
{
    public function balance_ejecutivos(Request $request)
    {
        $id_calculo=$request->id;
        DB::delete('delete from balance_comision_ventas where calculo_id='.$id_calculo);

        $mediciones=DB::select(DB::raw(
            "
            select numero_empleado,udn,
            sum(a.u_act) as u_act,sum(a.r_act) as r_act,sum(a.c_act) as c_act,
            sum(a.u_aep) as u_aep,sum(a.r_aep) as r_aep,sum(a.c_aep) as c_aep,
            sum(a.u_ren) as u_ren,sum(a.r_ren) as r_ren,sum(a.c_ren) as c_ren,
            sum(a.u_rep) as u_rep,sum(a.r_rep) as r_rep,sum(a.c_rep) as c_rep,
            sum(a.u_seg) as u_seg,sum(a.r_seg) as r_seg,sum(a.c_seg) as c_seg,
            sum(a.u_add) as u_add,sum(a.r_add) as r_add,sum(a.c_add) as c_add
            from (
                SELECT numero_empleado,udn,COUNT(tipo_venta) as u_act, SUM(importe) as r_act, SUM(comision_venta) as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Activación' OR tipo_venta='Activacion')
                group by numero_empleado,udn
                UNION
                SELECT numero_empleado,udn,0 as u_act,0 as r_act,0 as c_act,
                COUNT(tipo_venta) as u_aep,SUM(importe) as r_aep,SUM(comision_venta) as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Activación Equipo Propio' OR tipo_venta='Activacion Equipo Propio')
                group by numero_empleado,udn
                UNION
                SELECT numero_empleado,udn,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                COUNT(tipo_venta) as u_ren,SUM(importe) as r_ren,SUM(comision_venta) as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Renovación' OR tipo_venta='Renovacion')
                group by numero_empleado,udn
                UNION
                SELECT numero_empleado,udn,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                COUNT(tipo_venta) as u_rep,SUM(importe) as r_rep,SUM(comision_venta) as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Renovación Equipo Propio' OR tipo_venta='Renovacion Equipo Propio')
                group by numero_empleado,udn
                UNION
                SELECT numero_empleado,udn,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                COUNT(tipo_venta) as u_seg,SUM(importe) as r_seg,SUM(comision_venta) as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Protección de equipo' OR tipo_venta='Proteccion de equipo')
                group by numero_empleado,udn
                UNION
                SELECT numero_empleado,udn,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                COUNT(tipo_venta) as u_add,SUM(importe) as r_add,SUM(comision_venta) as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='ADD ON' OR tipo_venta='ADD ON')
                group by numero_empleado,udn
                ) as a group by a.numero_empleado, a.udn
            "
               ));

        $mediciones=collect($mediciones);
        $cuotas=Cuota::where('calculo_id',$id_calculo)->get();
        $empleados=Empleado::where('calculo_id',$id_calculo)->get();
        $registros_medidos=0;
        $errores=0;

        foreach($mediciones as $medicion)
        {
            $cumplimiento=$this->getAlcance(
                            $mediciones->where('numero_empleado',$medicion->numero_empleado),
                            $cuotas,
                            $empleados->where('numero_empleado',$medicion->numero_empleado)->first()
                            );
            if($cumplimiento["status"]=="OK")
            {
                $balance_row=new BalanceComisionVenta();
                $balance_row->udn=$medicion->udn;
                $balance_row->numero_empleado=$medicion->numero_empleado;
                $balance_row->nombre=$cumplimiento["nombre"];
                $balance_row->puesto=$cumplimiento["puesto"];
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
                $balance_row->esquema=$cumplimiento["esquema"];
                $balance_row->cumple_objetivo=$cumplimiento["cumple_objetivo"];
                $balance_row->porcentaje_cobro=$cumplimiento["porcentaje"];
                $balance_row->comision_final_activacion=$medicion->c_act*$cumplimiento["porcentaje"];
                $balance_row->comision_final_aep=$medicion->c_aep*$cumplimiento["porcentaje"];
                $balance_row->comision_final_renovacion=$medicion->c_ren*$cumplimiento["porcentaje"];
                $balance_row->comision_final_rep=$medicion->c_rep*$cumplimiento["porcentaje"];
                $balance_row->comision_final_seguro=$medicion->c_seg*$cumplimiento["porcentaje"];
                $balance_row->comision_final_addon=$medicion->c_add*$cumplimiento["porcentaje"];
                $balance_row->comision_final=$balance_row->comision_final_activacion+$balance_row->comision_final_aep+$balance_row->comision_final_renovacion+$balance_row->comision_final_rep+$balance_row->comision_final_seguro+$balance_row->comision_final_addon;
                $balance_row->comentario=$cumplimiento["comentario"];
                $balance_row->calculo_id=$id_calculo;
                $balance_row->save();
                $registros_medidos=$registros_medidos+1;
            }
            else
            {
                $error=new Error();
                $error->calculo_id=$id_calculo;
                $error->operacion="Balance Ejecutivos Venta";
                $error->error="No se encuentra numero_empleado=".$medicion->numero_empleado;
                $error->save();
                $errores=$errores+1;
            }
            
        }
        return($registros_medidos." errores:".$errores);
    }
    private function getAlcance($mediciones_pdv,$cuotas,$empleado)
    {
        $respuesta=array(
            'esquema'=>1,
            'cumple_objetivo'=>true,
            'porcentaje'=>1,
            'nombre'=>'',
            'puesto'=>'EJECUTIVO',
            'comentario'=>'',
            'status'=>'Fail'
        );
        $esquema=0;
        $esquema_anterior=0;
        $esquema_actual=0;
        
        $uds_activacion=0;
        $uds_aep=0;
        $comentario="Actividad en";

        foreach($mediciones_pdv as $in_udn)
        {
            $reg_cuota=$cuotas->where('udn',$in_udn->udn)->first();
            $esquema_actual=$reg_cuota->esquema;
            $comentario=$comentario." ".$reg_cuota->pdv.";";
            if($esquema_anterior==0)
            {
                $esquema=$esquema_actual;
            }
            if($esquema_actual<$esquema_anterior && $esquema_anterior!=0)
            {
                $esquema=$esquema_actual;
            }
            $esquema_anterior=$esquema_actual;

            $uds_activacion=$uds_activacion+$in_udn->u_act;
            $uds_aep=$uds_aep+$in_udn->u_aep;
        }
        $respuesta["esquema"]=$esquema;
        $respuesta["comentario"]=$comentario;
        $cumple_objetivo=true;
        $porcentaje_cobro=1.0;
        if(is_null($empleado)) return($respuesta);
        $puesto=$empleado->puesto;
        $respuesta["nombre"]=$empleado->nombre;

        if(is_null($empleado))

        $respuesta["puesto"]=$puesto;

        if($esquema==1 && strtoupper($puesto)=="EJECUTIVO")
        {
            if(!($uds_activacion>=10 || ($uds_activacion>=6 && $uds_aep>=4) || ($uds_activacion+$uds_aep>=10)))
            {
                $cumple_objetivo=false;
                $porcentaje_cobro=0.5;
            }
        }
        if($esquema==2 && strtoupper($puesto)=="EJECUTIVO")
        {
            if(!($uds_activacion>=4 || ($uds_activacion>=2 && $uds_aep>=2) || ($uds_activacion+$uds_aep>=4)))
            {
                $cumple_objetivo=false;
                $porcentaje_cobro=0.5;
            }
        }
        $respuesta["cumple_objetivo"]=$cumple_objetivo;
        $respuesta["porcentaje"]=$porcentaje_cobro;
        $respuesta["status"]="OK";
        return($respuesta);
    }
    public function balance_gerentes(Request $request)
    {
        $id_calculo=$request->id;
        $registros_medidos=0;
        $errores=0;
        
        DB::delete('delete from balance_comision_gerentes where calculo_id='.$id_calculo);
        $mediciones=DB::select(DB::raw(
                "
                select udn,
                sum(a.u_act) as u_act,sum(a.r_act) as r_act,sum(a.c_act) as c_act,
                sum(a.u_aep) as u_aep,sum(a.r_aep) as r_aep,sum(a.c_aep) as c_aep,
                sum(a.u_ren) as u_ren,sum(a.r_ren) as r_ren,sum(a.c_ren) as c_ren,
                sum(a.u_rep) as u_rep,sum(a.r_rep) as r_rep,sum(a.c_rep) as c_rep,
                sum(a.u_seg) as u_seg,sum(a.r_seg) as r_seg,sum(a.c_seg) as c_seg,
                sum(a.u_add) as u_add,sum(a.r_add) as r_add,sum(a.c_add) as c_add
                from (
                SELECT udn,COUNT(tipo_venta) as u_act, SUM(importe) as r_act, SUM(comision_supervisor_l1) as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Activación' OR tipo_venta='Activacion')
                group by udn
                UNION
                SELECT udn,0 as u_act,0 as r_act,0 as c_act,
                COUNT(tipo_venta) as u_aep,SUM(importe) as r_aep,SUM(comision_supervisor_l1) as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Activación Equipo Propio' OR tipo_venta='Activacion Equipo Propio')
                group by udn
                UNION
                SELECT udn,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                COUNT(tipo_venta) as u_ren,SUM(importe) as r_ren,SUM(comision_supervisor_l1) as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Renovación' OR tipo_venta='Renovacion')
                group by udn
                UNION
                SELECT udn,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                COUNT(tipo_venta) as u_rep,SUM(importe) as r_rep,SUM(comision_supervisor_l1) as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Renovación Equipo Propio' OR tipo_venta='Renovacion Equipo Propio')
                group by udn
                UNION
                SELECT udn,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                COUNT(tipo_venta) as u_seg,SUM(importe) as r_seg,SUM(comision_supervisor_l1) as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Protección de equipo' OR tipo_venta='Proteccion de equipo')
                group by udn
                UNION
                SELECT udn,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                COUNT(tipo_venta) as u_add,SUM(importe) as r_add,SUM(comision_supervisor_l1) as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='ADD ON' OR tipo_venta='ADD ON')
                group by udn
                ) as a group by a.udn
                "
            ));
        $cuotas=Cuota::where('calculo_id',$id_calculo)->get();
        foreach($mediciones as $medicion)
        {
            $cuota=$cuotas->where('udn',$medicion->udn)->first();
            $cumplimiento=$this->getAlcance_Sup($id_calculo,$medicion,$cuota,"GERENTE");
            if($cumplimiento["status"]=="OK")
            {
                $balance_row=new BalanceComisionGerente();
                $balance_row->numero_empleado=$cumplimiento["numero_empleado"];
                $balance_row->udn=$medicion->udn;
                $balance_row->uds_activacion=$medicion->u_act;
                $balance_row->uds_aep=$medicion->u_aep;
                $balance_row->uds_renovacion=$medicion->u_ren;
                $balance_row->uds_rep=$medicion->u_rep;
                $balance_row->porc_cierre_activacion=$cumplimiento["fin_act"];
                $balance_row->porc_cierre_aep=$cumplimiento["fin_aep"];
                $balance_row->porc_cierre_renovacion=$cumplimiento["fin_ren"];
                $balance_row->porc_cierre_rep=$cumplimiento["fin_rep"];
                $balance_row->cuota_activacion=$cuota->activaciones;
                $balance_row->alcance_activacion=$cumplimiento["alc_act"];
                $balance_row->cuota_aep=$cuota->aep;
                $balance_row->alcance_aep=$cumplimiento["alc_aep"];
                $balance_row->cuota_renovacion=$cuota->renovaciones;
                $balance_row->alcance_renovacion=$cumplimiento["alc_ren"];
                $balance_row->cuota_rep=$cuota->rep;
                $balance_row->alcance_rep=$cumplimiento["alc_rep"];
                $balance_row->comision_directa_activacion=$medicion->c_act;
                $balance_row->comision_directa_aep=$medicion->c_aep;
                $balance_row->comision_directa_renovacion=$medicion->c_ren;
                $balance_row->comision_directa_rep=$medicion->c_rep;
                $balance_row->comision_directa_seguro=$medicion->c_seg;
                $balance_row->comision_directa_addon=$medicion->c_add;
                $balance_row->comision_final_activacion=$medicion->c_act*$cumplimiento["fin_act"];
                $balance_row->comision_final_aep=$medicion->c_aep*$cumplimiento["fin_aep"];
                $balance_row->comision_final_renovacion=$medicion->c_ren*$cumplimiento["fin_ren"];
                $balance_row->comision_final_rep=$medicion->c_rep*$cumplimiento["fin_rep"];
                $balance_row->comision_final_seguro=$medicion->c_seg;
                $balance_row->comision_final_addon=$medicion->c_add;
                $balance_row->comision_final=$balance_row->comision_final_activacion+$balance_row->comision_final_aep+$balance_row->comision_final_renovacion+$balance_row->comision_final_rep+$balance_row->comision_final_seguro+$balance_row->comision_final_addon;
                $balance_row->calculo_id=$id_calculo;
                $balance_row->save();
                $registros_medidos=$registros_medidos+1;
            }
            else
            {
                $error=new Error();
                $error->calculo_id=$id_calculo;
                $error->operacion="Balance Gerente";
                $error->error=$cumplimiento["comment"]."= ".$medicion->udn;
                $error->save();
                $errores=$errores+1;
            }
        }
        return($registros_medidos." errores:".$errores);
    }
    public function balance_regionales(Request $request)
    {
        $id_calculo=$request->id;
        $registros_medidos=0;
        $errores=0;
        
        DB::delete('delete from balance_comision_regionals where calculo_id='.$id_calculo);
        $mediciones=DB::select(DB::raw(
                "
                select region,
                sum(a.u_act) as u_act,sum(a.r_act) as r_act,sum(a.c_act) as c_act,
                sum(a.u_aep) as u_aep,sum(a.r_aep) as r_aep,sum(a.c_aep) as c_aep,
                sum(a.u_ren) as u_ren,sum(a.r_ren) as r_ren,sum(a.c_ren) as c_ren,
                sum(a.u_rep) as u_rep,sum(a.r_rep) as r_rep,sum(a.c_rep) as c_rep,
                sum(a.u_seg) as u_seg,sum(a.r_seg) as r_seg,sum(a.c_seg) as c_seg,
                sum(a.u_add) as u_add,sum(a.r_add) as r_add,sum(a.c_add) as c_add
                from (
                SELECT region,COUNT(tipo_venta) as u_act, SUM(importe) as r_act, SUM(comision_supervisor_l2) as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Activación' OR tipo_venta='Activacion')
                group by region
                UNION
                SELECT region,0 as u_act,0 as r_act,0 as c_act,
                COUNT(tipo_venta) as u_aep,SUM(importe) as r_aep,SUM(comision_supervisor_l2) as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Activación Equipo Propio' OR tipo_venta='Activacion Equipo Propio')
                group by region
                UNION
                SELECT region,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                COUNT(tipo_venta) as u_ren,SUM(importe) as r_ren,SUM(comision_supervisor_l2) as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Renovación' OR tipo_venta='Renovacion')
                group by region
                UNION
                SELECT region,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                COUNT(tipo_venta) as u_rep,SUM(importe) as r_rep,SUM(comision_supervisor_l2) as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Renovación Equipo Propio' OR tipo_venta='Renovacion Equipo Propio')
                group by region
                UNION
                SELECT region,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                COUNT(tipo_venta) as u_seg,SUM(importe) as r_seg,SUM(comision_supervisor_l2) as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Protección de equipo' OR tipo_venta='Proteccion de equipo')
                group by region
                UNION
                SELECT region,0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                COUNT(tipo_venta) as u_add,SUM(importe) as r_add,SUM(comision_supervisor_l2) as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='ADD ON' OR tipo_venta='ADD ON')
                group by region
                ) as a group by a.region
                "
            ));
        $cuotas=DB::table('cuotas')
            ->select('regional','region',DB::raw('sum(activaciones) as activaciones'),DB::raw('sum(aep) as aep'),DB::raw('sum(renovaciones) as renovaciones'),DB::raw('sum(rep) as rep'))
            ->where('calculo_id',$id_calculo)
            ->groupBy('regional','region')
            ->get();

        foreach($mediciones as $medicion)
        {
            $cuota=$cuotas->where('region',$medicion->region)->first();
            $cumplimiento=$this->getAlcance_Sup($id_calculo,$medicion,$cuota,"REGIONAL");
            if($cumplimiento["status"]=="OK")
            {
                $balance_row=new BalanceComisionRegional();
                $balance_row->numero_empleado=$cumplimiento["numero_empleado"];
                $balance_row->udn=$medicion->region;
                $balance_row->uds_activacion=$medicion->u_act;
                $balance_row->uds_aep=$medicion->u_aep;
                $balance_row->uds_renovacion=$medicion->u_ren;
                $balance_row->uds_rep=$medicion->u_rep;
                $balance_row->porc_cierre_activacion=$cumplimiento["fin_act"];
                $balance_row->porc_cierre_aep=$cumplimiento["fin_aep"];
                $balance_row->porc_cierre_renovacion=$cumplimiento["fin_ren"];
                $balance_row->porc_cierre_rep=$cumplimiento["fin_rep"];
                $balance_row->cuota_activacion=$cuota->activaciones;
                $balance_row->alcance_activacion=$cumplimiento["alc_act"];
                $balance_row->cuota_aep=$cuota->aep;
                $balance_row->alcance_aep=$cumplimiento["alc_aep"];
                $balance_row->cuota_renovacion=$cuota->renovaciones;
                $balance_row->alcance_renovacion=$cumplimiento["alc_ren"];
                $balance_row->cuota_rep=$cuota->rep;
                $balance_row->alcance_rep=$cumplimiento["alc_rep"];
                $balance_row->comision_directa_activacion=$medicion->c_act;
                $balance_row->comision_directa_aep=$medicion->c_aep;
                $balance_row->comision_directa_renovacion=$medicion->c_ren;
                $balance_row->comision_directa_rep=$medicion->c_rep;
                $balance_row->comision_directa_seguro=$medicion->c_seg;
                $balance_row->comision_directa_addon=$medicion->c_add;
                $balance_row->comision_final_activacion=$medicion->c_act*$cumplimiento["fin_act"];
                $balance_row->comision_final_aep=$medicion->c_aep*$cumplimiento["fin_aep"];
                $balance_row->comision_final_renovacion=$medicion->c_ren*$cumplimiento["fin_ren"];
                $balance_row->comision_final_rep=$medicion->c_rep*$cumplimiento["fin_rep"];
                $balance_row->comision_final_seguro=$medicion->c_seg;
                $balance_row->comision_final_addon=$medicion->c_add;
                $balance_row->comision_final=$balance_row->comision_final_activacion+$balance_row->comision_final_aep+$balance_row->comision_final_renovacion+$balance_row->comision_final_rep+$balance_row->comision_final_seguro+$balance_row->comision_final_addon;
                $balance_row->calculo_id=$id_calculo;
                $balance_row->save();
                $registros_medidos=$registros_medidos+1;
            }
            else
            {
                $error=new Error();
                $error->calculo_id=$id_calculo;
                $error->operacion="Balance Regional";
                $error->error=$cumplimiento["comment"]."= ".$medicion->region;
                $error->save();
                $errores=$errores+1;
            }
        }
        return($registros_medidos." errores:".$errores);
    }
    
    public function balance_director(Request $request)
    {
        $id_calculo=$request->id;
        $registros_medidos=0;
        $errores=0;
        
        DB::delete('delete from balance_comision_directors where calculo_id='.$id_calculo);
        $mediciones=DB::select(DB::raw(
                "
                select
                sum(a.u_act) as u_act,sum(a.r_act) as r_act,sum(a.c_act) as c_act,
                sum(a.u_aep) as u_aep,sum(a.r_aep) as r_aep,sum(a.c_aep) as c_aep,
                sum(a.u_ren) as u_ren,sum(a.r_ren) as r_ren,sum(a.c_ren) as c_ren,
                sum(a.u_rep) as u_rep,sum(a.r_rep) as r_rep,sum(a.c_rep) as c_rep,
                sum(a.u_seg) as u_seg,sum(a.r_seg) as r_seg,sum(a.c_seg) as c_seg,
                sum(a.u_add) as u_add,sum(a.r_add) as r_add,sum(a.c_add) as c_add
                from (
                SELECT COUNT(tipo_venta) as u_act, SUM(importe) as r_act, SUM(comision_supervisor_l3) as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Activación' OR tipo_venta='Activacion')
                group by region
                UNION
                SELECT 0 as u_act,0 as r_act,0 as c_act,
                COUNT(tipo_venta) as u_aep,SUM(importe) as r_aep,SUM(comision_supervisor_l3) as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Activación Equipo Propio' OR tipo_venta='Activacion Equipo Propio')
                group by region
                UNION
                SELECT 0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                COUNT(tipo_venta) as u_ren,SUM(importe) as r_ren,SUM(comision_supervisor_l3) as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Renovación' OR tipo_venta='Renovacion')
                group by region
                UNION
                SELECT 0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                COUNT(tipo_venta) as u_rep,SUM(importe) as r_rep,SUM(comision_supervisor_l3) as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Renovación Equipo Propio' OR tipo_venta='Renovacion Equipo Propio')
                group by region
                UNION
                SELECT 0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                COUNT(tipo_venta) as u_seg,SUM(importe) as r_seg,SUM(comision_supervisor_l3) as c_seg,
                0 as u_add,0 as r_add,0 as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Protección de equipo' OR tipo_venta='Proteccion de equipo')
                group by region
                UNION
                SELECT 0 as u_act,0 as r_act,0 as c_act,
                0 as u_aep,0 as r_aep,0 as c_aep,
                0 as u_ren,0 as r_ren,0 as c_ren,
                0 as u_rep,0 as r_rep,0 as c_rep,
                0 as u_seg,0 as r_seg,0 as c_seg,
                COUNT(tipo_venta) as u_add,SUM(importe) as r_add,SUM(comision_supervisor_l3) as c_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='ADD ON' OR tipo_venta='ADD ON')
                group by region
                ) as a 
                "
            ));
        $cuotas=DB::table('cuotas')
            ->select('director',DB::raw('sum(activaciones) as activaciones'),DB::raw('sum(aep) as aep'),DB::raw('sum(renovaciones) as renovaciones'),DB::raw('sum(rep) as rep'))
            ->where('calculo_id',$id_calculo)
            ->groupBy('director')
            ->get();

        foreach($mediciones as $medicion)
        {
            $cuota=$cuotas->first();
            $cumplimiento=$this->getAlcance_Sup($id_calculo,$medicion,$cuota,"DIRECTOR");
            if($cumplimiento["status"]=="OK")
            {
                $balance_row=new BalanceComisionDirector();
                $balance_row->numero_empleado=$cumplimiento["numero_empleado"];
                $balance_row->udn="Sucursales";
                $balance_row->uds_activacion=$medicion->u_act;
                $balance_row->uds_aep=$medicion->u_aep;
                $balance_row->uds_renovacion=$medicion->u_ren;
                $balance_row->uds_rep=$medicion->u_rep;
                $balance_row->porc_cierre_activacion=$cumplimiento["fin_act"];
                $balance_row->porc_cierre_aep=$cumplimiento["fin_aep"];
                $balance_row->porc_cierre_renovacion=$cumplimiento["fin_ren"];
                $balance_row->porc_cierre_rep=$cumplimiento["fin_rep"];
                $balance_row->cuota_activacion=$cuota->activaciones;
                $balance_row->alcance_activacion=$cumplimiento["alc_act"];
                $balance_row->cuota_aep=$cuota->aep;
                $balance_row->alcance_aep=$cumplimiento["alc_aep"];
                $balance_row->cuota_renovacion=$cuota->renovaciones;
                $balance_row->alcance_renovacion=$cumplimiento["alc_ren"];
                $balance_row->cuota_rep=$cuota->rep;
                $balance_row->alcance_rep=$cumplimiento["alc_rep"];
                $balance_row->comision_directa_activacion=$medicion->c_act;
                $balance_row->comision_directa_aep=$medicion->c_aep;
                $balance_row->comision_directa_renovacion=$medicion->c_ren;
                $balance_row->comision_directa_rep=$medicion->c_rep;
                $balance_row->comision_directa_seguro=$medicion->c_seg;
                $balance_row->comision_directa_addon=$medicion->c_add;
                $balance_row->comision_final_activacion=$medicion->c_act*$cumplimiento["fin_act"];
                $balance_row->comision_final_aep=$medicion->c_aep*$cumplimiento["fin_aep"];
                $balance_row->comision_final_renovacion=$medicion->c_ren*$cumplimiento["fin_ren"];
                $balance_row->comision_final_rep=$medicion->c_rep*$cumplimiento["fin_rep"];
                $balance_row->comision_final_seguro=$medicion->c_seg;
                $balance_row->comision_final_addon=$medicion->c_add;
                $balance_row->comision_final=$balance_row->comision_final_activacion+$balance_row->comision_final_aep+$balance_row->comision_final_renovacion+$balance_row->comision_final_rep+$balance_row->comision_final_seguro+$balance_row->comision_final_addon;
                $balance_row->calculo_id=$id_calculo;
                $balance_row->save();
                $registros_medidos=$registros_medidos+1;
            }
            else
            {
                $error=new Error();
                $error->calculo_id=$id_calculo;
                $error->operacion="Balance Director";
                $error->error=$cumplimiento["comment"]."= Sucursales";
                $error->save();
                $errores=$errores+1;
            }
        }
        return($registros_medidos." errores:".$errores);
    }
    private function getAlcance_Sup($id_calculo,$mediciones_pdv,$cuota,$rol)
    {
        $respuesta=array(
            'numero_empleado'=>0,
            'nombre'=>'',
            'alc_act'=>1,
            'alc_aep'=>1,
            'alc_ren'=>1,
            'alc_rep'=>1,
            'fin_act'=>1,
            'fin_aep'=>1,
            'fin_ren'=>1,
            'fin_rep'=>1,
            'comment'=>'',
            'status'=>'Fail'
        );
        if(is_null($cuota)) 
        {
            $respuesta["comment"]="Cuota no encontrada para el UDN";
            return($respuesta);
        }
        $cuota_activacion=$cuota->activaciones;
        $cuota_aep=$cuota->aep;
        $cuota_renovacion=$cuota->renovaciones;
        $cuota_rep=$cuota->rep;
        if($rol=="GERENTE")
        {
            $respuesta["numero_empleado"]=$cuota->id_gerente;
            $empleado=Empleado::where('calculo_id',$id_calculo)
                    ->where('numero_empleado',$cuota->id_gerente)
                    ->get()
                    ->first();
            if(is_null($empleado))
            {
                $respuesta["comment"]="Gerente no encontrado para el UDN";
                return($respuesta);
            }
        }
        if($rol=="REGIONAL")
        {
            $respuesta["numero_empleado"]=$cuota->regional;
            $empleado=Empleado::where('calculo_id',$id_calculo)
                    ->where('numero_empleado',$cuota->regional)
                    ->get()
                    ->first();
            if(is_null($empleado))
            {
                $respuesta["comment"]="Regional no encontrado para el UDN";
                return($respuesta);
            }
        }
        if($rol=="DIRECTOR")
        {
            $respuesta["numero_empleado"]=$cuota->director;
            $empleado=Empleado::where('calculo_id',$id_calculo)
                    ->where('numero_empleado',$cuota->director)
                    ->get()
                    ->first();
            if(is_null($empleado))
            {
                $respuesta["comment"]="Director no encontrado para el UDN";
                return($respuesta);
            }
        }
        $alcance_activacion=0;
        $alcance_aep=0;
        $alcance_renovacion=0;
        $alcance_rep=0;
        $porc_cierre_activacion=0;
        $porc_cierre_aep=0;
        $porc_cierre_renovacion=0;
        $porc_cierre_rep=0;
        if($cuota_activacion!=0){$alcance_activacion=$mediciones_pdv->u_act/$cuota_activacion;}
        else{$alcance_activacion=0.9999;}    
        if($cuota_aep!=0){$alcance_aep=$mediciones_pdv->u_aep/$cuota_aep;}
        else{$alcance_aep=0.9999;}
        if($cuota_renovacion!=0){$alcance_renovacion=$mediciones_pdv->u_ren/$cuota_renovacion;}
        else{$alcance_renovacion=0.9999;}
        if($cuota_rep!=0){$alcance_rep=$mediciones_pdv->u_rep/$cuota_rep;}
        else{$alcance_rep=0.9999;}
        if($alcance_activacion<0.8)
        {
            $porc_cierre_activacion=$alcance_activacion;
            //$porc_cierre_activacion=0;
        }
        if($alcance_activacion>=0.8 && $alcance_activacion<0.85)
        {
            $porc_cierre_activacion=0.8;
        }
        if($alcance_activacion>=0.85 && $alcance_activacion<1)
        {
            $porc_cierre_activacion=1;
        }
        if($alcance_activacion>=1)
        {
            $porc_cierre_activacion=1.1;
        }
        //EQ PROPIO
        if($alcance_aep<0.8)
        {
            $porc_cierre_aep=$alcance_aep;
            //$porc_cierre_aep=0;
        }
        if($alcance_aep>=0.8 && $alcance_aep<0.85)
        {
            $porc_cierre_aep=0.8;
        }
        if($alcance_aep>=0.85 && $alcance_aep<1)
        {
            $porc_cierre_aep=1;
        }
        if($alcance_aep>=1)
        {
            $porc_cierre_aep=1;
        }
        //RENOVACION
        if($alcance_renovacion<0.8)
        {
            $porc_cierre_renovacion=$alcance_renovacion;
            //$porc_cierre_renovacion=0;
        }
        if($alcance_renovacion>=0.8 && $alcance_renovacion<0.85)
        {
            $porc_cierre_renovacion=0.8;
        }
        if($alcance_renovacion>=0.85)
        {
            $porc_cierre_renovacion=1;
        }
        //REP
        if($alcance_rep<0.8)
        {
            $porc_cierre_rep=$alcance_rep;
            //$porc_cierre_rep=0;
        }
        if($alcance_rep>=0.8 && $alcance_rep<0.85)
        {
            $porc_cierre_rep=0.8;
        }
        if($alcance_rep>=0.85)
        {
            $porc_cierre_rep=1;
        }
////TIENDAS CON PROMOCION DE LOGRO POR APERTURA

        if($rol=="GERENTE" && ($mediciones_pdv->udn=='133' || $mediciones_pdv->udn=='135'))
        {
            $porc_cierre_activacion=1;
            $porc_cierre_aep=1;
            $porc_cierre_renovacion=1;
            $porc_cierre_rep=1;
        }

        $respuesta["alc_act"]=$alcance_activacion;
        $respuesta["alc_aep"]=$alcance_aep;
        $respuesta["alc_ren"]=$alcance_renovacion;
        $respuesta["alc_rep"]=$alcance_rep;
        $respuesta["fin_act"]=$porc_cierre_activacion;
        $respuesta["fin_aep"]=$porc_cierre_aep;
        $respuesta["fin_ren"]=$porc_cierre_renovacion;
        $respuesta["fin_rep"]=$porc_cierre_rep;
        $respuesta["comment"]="OK";
        $respuesta["status"]="OK";


        return($respuesta);
    }
}
