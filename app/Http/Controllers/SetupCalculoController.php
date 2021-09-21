<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calculo;
use App\Models\Empleado;
use App\Models\Transaccion;
use App\Models\Estructura;
use App\Models\Cuota;
use App\Models\Parametro;

use Illuminate\Support\Facades\DB;

class SetupCalculoController extends Controller
{
    public function import_empleados(int $id_calculo)
    {
        $json=json_decode(file_get_contents('http://icube.com.mx/comisiones_bca/consultar_empleados.php'),true);
        $x=0;
        $empleados_actuales=Empleado::all();
        $id_empleado=0;
        foreach ($json as $empleado_json) {
            $id_empleado=0;
            //if(!$empleados_actuales->contains('numero_empleado',$empleado_json['numero_empleado']))
            {
           //CREA LA INSTANCIA GENERAL PARA LA VISTA Y SEGUIMIENTO DE EMPLEADOS
            $empleado=new Empleado();
            $empleado->numero_empleado=$empleado_json['numero_empleado'];
            $empleado->nombre=$empleado_json['nombre'];
            $empleado->udn=$empleado_json['udn'];
            $empleado->pdv=$empleado_json['pdv'];
            $empleado->puesto=$empleado_json['puesto'];
            $empleado->ingreso=$empleado_json['ingreso'];
            $empleado->adeudo=0;
            $empleado->estatus=$empleado_json['estatus'];
            $empleado->sueldo=$empleado_json['sueldo'];
            $empleado->modalidad=$empleado_json['modalidad'];
            $empleado->calculo_id=$id_calculo;
            $empleado->save();
            $id_empleado=$empleado->id;
            }
            $x=$x+1;
        }
        return($x);
    }
    public function limpiar_calculo(int $id_calculo)
    {
        //delete from transaccions;
        DB::delete('delete from transaccions where calculo_id='.$id_calculo);
        //delete from calculos;
        DB::delete('delete from calculos where id='.$id_calculo);
        //delete from cuotas;
        DB::delete('delete from cuotas where calculo_id='.$id_calculo);
        //delete from balance_comision_ventas;
        DB::delete('delete from balance_comision_ventas where calculo_id='.$id_calculo);
        //delete from balance_comision_gerente;
        DB::delete('delete from balance_comision_gerentes where calculo_id='.$id_calculo);
        //delete from balance_comision_regional;
        DB::delete('delete from balance_comision_regionals where calculo_id='.$id_calculo);
        //delete from balance_comision_regional;
        DB::delete('delete from balance_comision_directors where calculo_id='.$id_calculo);
        //delete from empleados;
        DB::delete('delete from empleados where calculo_id='.$id_calculo);
        //delete from errors;
        DB::delete('delete from errors where calculo_id='.$id_calculo);
        //delete from CB;
        DB::delete('delete from charge_back_internos where calculo_id='.$id_calculo);
        //delete from payments;
        DB::delete('delete from payments where calculo_id='.$id_calculo);

        return("OK");

    }
    public function import_transacciones(int $id_calculo,$f_inicio,$f_fin)
    {

        //http://icube.com.mx/comisiones_bca/consultar_transacciones.php?f_inicio=2020-02-01&f_fin=2020-02-15
        $json=json_decode(file_get_contents('http://icube.com.mx/comisiones_bca/consultar_transacciones.php?f_inicio='.$f_inicio.'&f_fin='.$f_fin),true);
        $x=0;
        foreach($json as $transaccion_json)
        {
            if($this->check_in_range($f_inicio,$f_fin,$transaccion_json['fecha']))
            {
                $transaccion=new Transaccion();
                $transaccion->pedido=$transaccion_json['pedido'];
                $transaccion->numero_empleado=$transaccion_json['numero_empleado'];
                $transaccion->empleado=$transaccion_json['empleado'];
                $transaccion->fecha=$transaccion_json['fecha'];
                $transaccion->region=$transaccion_json['region'];
                $transaccion->udn=$transaccion_json['udn'];
                $transaccion->pdv=$transaccion_json['pdv'];
                $transaccion->tipo_venta=$transaccion_json['tipo_venta'];
                $transaccion->transaccion=$transaccion_json['transaccion'];
                $transaccion->contrato=$transaccion_json['contrato'];
                $transaccion->importe=$transaccion_json['importe'];
                $transaccion->servicio=$transaccion_json['servicio'];
                $transaccion->producto=$transaccion_json['producto'];
                $transaccion->seguro=$transaccion_json['seguro'];
                $transaccion->add_ons=$transaccion_json['add_ons'];
                $transaccion->plazo=$transaccion_json['plazo'];
                $transaccion->estado=$transaccion_json['estado'];
                $transaccion->canal_ventas=$transaccion_json['canal_ventas'];
                $transaccion->subcanal=$transaccion_json['subcanal'];
                $transaccion->tipo_de_venta_2=$transaccion_json['tipo_de_venta_2'];
                $transaccion->desc_multilinea=$transaccion_json['desc_multilinea'];
                $transaccion->credito=true;
                if($transaccion_json['tipo_venta']=='Chip' || $transaccion_json['tipo_venta']=='Tiempo Aire')
                {
                    $transaccion->credito=false;
                }
                $transaccion->comision_venta=0.0;
                $transaccion->comision_supervisor_l1=0.0;
                $transaccion->comision_supervisor_l2=0.0;
                $transaccion->comision_supervisor_l3=0.0;
                $transaccion->ejecutivoCC=0;
                $transaccion->eq_sin_costo=false;
                $transaccion->supervisorCC=0;
                $transaccion->comisionCC=0.0;
                $transaccion->comision_supervisor_cc=0.0;
                $transaccion->periodo=substr($transaccion_json['fecha'],0,7);
                $transaccion->calculo_id=$id_calculo;
                $transaccion->save();
                $x=$x+1;
            }
        }       
        return($x);
    }

    public function setup_calculo(Request $request)
    {
        $estatus = array(
            "periodo" => "Periodo NO Valido - Los limites del calculo de intersectan con algun periodo previo de medicion",
            "id_calculo" => 0,
            "empleados" => 0,
            "transacciones" => 0,
            "parametros"=>0
        );
        if($this->valida_periodo($request->f_inicio,$request->f_fin))
        {
            $estatus['periodo']="OK";
            $calculo=new Calculo();
            $calculo->descripcion=$request->descripcion;
            $calculo->fecha_inicio=$request->f_inicio;
            $calculo->fecha_fin=$request->f_fin;
            $calculo->user_id=auth()->id();
            $calculo->cuotas=false;
            $calculo->cc=false;
            $calculo->eq0=false;
            $calculo->cr0=false;
            $calculo->cb=false;
            $calculo->terminado=false;
            $calculo->save();
            $estatus['id_calculo']=$calculo->id;
            $procesados=$this->import_empleados($calculo->id);
            $estatus['empleados']=$procesados;
            $procesados=$this->import_transacciones($calculo->id,$calculo->fecha_inicio,$calculo->fecha_fin);
            $estatus['transacciones']=$procesados;

            if($estatus['transacciones']==0)
            {
                $estatus['empleados']=0;
                $estatus['periodo']='Periodo OK - Descartado por no encontrar transacciones';
                $estatus['id_calculo']=0;
                $this->limpiar_calculo($calculo->id);
            }
            
        }
        return(json_encode($estatus));
    }
    public function valida_periodo($fecha_inicio,$fecha_fin)
    {
        $current_calculos=Calculo::all();
        foreach ($current_calculos as $procesado) {
            $fecha_inicio_procesada=$procesado->fecha_inicio;
            $fecha_fin_procesada=$procesado->fecha_fin;
            if($this->check_in_range($fecha_inicio_procesada,$fecha_fin_procesada,$fecha_inicio) || $this->check_in_range($fecha_inicio_procesada,$fecha_fin_procesada,$fecha_fin))
            {
                return (false);
            }            
        }
        return(true);
    }
    public function check_in_range($fecha_inicio, $fecha_fin, $fecha){

            $fecha_inicio = strtotime($fecha_inicio);
            $fecha_fin = strtotime($fecha_fin);
            $fecha = strtotime($fecha);
   
            if(($fecha >= $fecha_inicio) && ($fecha <= $fecha_fin))
                return true;
            else
                return false;
        }
    public function ejemplo_transacciones(Request $request)
    {
        $transacciones=DB::table('transaccions')
            ->select('pedido','numero_empleado','fecha','udn','pdv','tipo_venta','transaccion','contrato','importe','servicio','producto','plazo','desc_multilinea')
            ->where('pedido','158340')
            ->take(10)
            ->get();

        $empleados=DB::table('empleados')
            ->select('numero_empleado','nombre',DB::raw('65 as udn'),'pdv','puesto','ingreso',DB::raw('"Activo" as estatus'))
            ->where('numero_empleado','>','15')
            ->take(10)
            ->get();
        return($transacciones);
    }
}
