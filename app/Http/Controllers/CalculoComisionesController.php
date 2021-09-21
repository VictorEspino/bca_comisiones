<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaccion;
use App\Models\Estructura;
use App\Models\Cuota;
use App\Models\Calculo;
use App\Models\MedicionActorVenta;
use App\Models\MedicionGerente;
use App\Models\Error;

use Illuminate\Support\Facades\DB;

class CalculoComisionesController extends Controller
{
    public function comisiones_actor_ventas_surcursales($id_calculo)
    {
        //$id_calculo=$request->id_calculo;
        $transacciones=Transaccion::where('calculo_id',$id_calculo)
                    ->where('credito',1)
                    ->orderBy('udn')
                    ->orderBy('numero_empleado')
                    ->orderBy('pedido')
                    ->get();
        $registros_act_ren_eq_nuevo=$transacciones->whereIn('tipo_venta',['Activación','Renovación','Activacion','Renovacion']); //PLANES CON EQUIPO NUEVO
        
        $cuotas=Cuota::where('calculo_id',$id_calculo)->get();
        $transacciones_pagadas=0;
        foreach ($transacciones as $credito) {
            $renta_transaccion=$credito->importe;
            $tipo_venta=$credito->tipo_venta;
            $plan=$credito->servicio;
            $pedido=$credito->pedido;
            $transaccion_con_seguro="NO";
            $bracket=0;
            $comision=0;
            $comision_gte=0;
            $comision_reg=0;
            $comision_dir=0;
            $esquema=0;
            $eq_sin_costo=$credito->eq_sin_costo;
            if($cuotas->contains('udn',$credito->udn)) //CON ESTE BLOQUE SE OBTIENE EL VALOR DEL ESQUEMA
            {
                $cuota_pdv=$cuotas->where('udn',$credito->udn);
                foreach ($cuota_pdv as $cuota) 
                {
                     $esquema=$cuota->esquema;
                }
            }
            else{
                $esquema=1; //SE PUEDE INDICAR UN ESQUEMA DEFAULT O GENERAR ERROR DADO QUE NO ESTA DEFINIDO EL PUNTO DE VENTA
            }
            //echo "-".$tipo_venta."-";
            //echo "<br>ESQUEMA ".$esquema."--".$credito->pedido."--".$credito->tipo_venta."--".$credito->servicio."--".$renta_transaccion."SEGURO :".$transaccion_con_seguro;
            if($tipo_venta=="Activación" || $tipo_venta=="Activacion" ||
                $tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio" ||
                $tipo_venta=="Renovación" || $tipo_venta=="Renovacion" ||
                $tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio" ||
                $tipo_venta=="Protección de equipo" || $tipo_venta=="Proteccion de equipo" ||
                $tipo_venta=="Renovación Empresarial" || $tipo_venta=="Renovacion Empresarial")
                {
                    if($tipo_venta=="Renovación Empresarial" || $tipo_venta=="Renovacion Empresarial") {$tipo_venta="Renovación";}
                    if(
                    //    strpos($plan,"COMPARTELO")=== false 
                    strpos($plan,"DAMOS")=== false 
                    && strpos($plan,"YA")=== false 
                    && strpos($plan,"Protecci")=== false 
                    && strpos($plan,"SIMPLE")=== false
                    && strpos($plan,"ARMALO")===false) 
                    //SE TRATA DE UN PLAN CONSIGUELO U OTRO NO NOMBRADO
                    {
                        $bracket=$this->obtenBracket($renta_transaccion);
                        $comision_gte=$this->comisionConsiguelo_gerente($bracket,$tipo_venta);
                        $comision_reg=$this->comisionConsiguelo_regional($bracket,$tipo_venta);
                        $comision_dir=$this->comisionConsiguelo_director($bracket,$tipo_venta);
                        if($esquema=="1" || $esquema=="2"){
                            $comision=$this->comisionConsiguelo_1_2($bracket,$tipo_venta);
                            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion") && $eq_sin_costo)
                            {
                                $comision=$comision-$this->performanceElementEjecutivo_1_2($bracket);
                                $comision_gte=$comision_gte-$this->performanceElement_gerente($bracket);
                                $comision_reg=$comision_reg-$this->performanceElement_regional($bracket);
                                $comision_dir=$comision_dir-$this->performanceElement_director($bracket);
                            }
                        }
                        else{
                            //echo "<br>-TIPO:".$tipo_venta.", BRACKET:".$bracket."<br>";
                            $comision=$this->comisionConsiguelo_3($bracket,$tipo_venta);
                            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion") && $eq_sin_costo)
                            {
                                $comision=$comision-$this->performanceElementEjecutivo_3($bracket);
                                $comision_gte=$comision_gte-$this->performanceElement_gerente($bracket);
                                $comision_reg=$comision_reg-$this->performanceElement_regional($bracket);
                                $comision_dir=$comision_dir-$this->performanceElement_director($bracket);
                            }
                        }
                    }
                    if(strpos($plan,"ARMALO")!== false)
                    {
                        $comision_gte=$this->comisionArmalo_gerente($plan,$tipo_venta);
                        $comision_reg=$this->comisionArmalo_regional($plan,$tipo_venta);
                        $comision_dir=$this->comisionArmalo_director($plan,$tipo_venta);
                        $comision=$this->comisionArmalo($plan,$tipo_venta);
                        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion") && $eq_sin_costo)
                            {
                                $comision=$comision-$this->performanceElementEjecutivoArmalo($plan,$tipo_venta);
                                $comision_gte=$comision_gte-$this->performanceElementArmalo_gerente($plan,$tipo_venta);
                                $comision_reg=$comision_reg-$this->performanceElementArmalo_regional($plan,$tipo_venta);
                                $comision_dir=$comision_dir-$this->performanceElementArmalo_director($plan,$tipo_venta);
                            }
                    }
                    if(strpos($plan,"DAMOS")!== false || strpos($plan,"YA")!== false || strpos($plan,"SIMPLE")!== false) // PLANES DAMOS MAS o YA
                    {   
                        $comision_gte=$this->comisionDamosYa_gerente($renta_transaccion,$credito->plazo);
                        $comision_reg=$this->comisionDamosYa_regional($renta_transaccion,$credito->plazo);
                        $comision_dir=$this->comisionDamosYa_director($renta_transaccion,$credito->plazo);
                        if($esquema=="1" || $esquema=="2"){
                            $comision=$this->comisionDamosYa_1_2($renta_transaccion,$credito->plazo);
                        }
                        else{
                            $comision=$this->comisionDamosYa_3($renta_transaccion,$credito->plazo);
                        }
                    }
                    //if(strpos($plan,"COMPARTELO")!== false) // POPOTES, LINEAS ADICIONALES COMPARTELO
                    //{   
                    //    $comision=$this->comisionCompartelo_gerente($tipo_venta);
                    //    $comision_gte=$this->comisionCompartelo_regional($tipo_venta);
                    //    $comision_dir=$this->comisionCompartelo_director($tipo_venta);
                    //    if($esquema=="1" || $esquema=="2"){
                    //        $comision=$this->comisionCompartelo_1_2($tipo_venta);
                    //    }
                    //    else{
                    //        $comision=$this->comisionCompartelo_3($tipo_venta);
                    //    }
                    //}
                    if(strpos($plan,"Protecci")!== false) // INSTANCIA DE SEGURO
                    {   
                       $registro_venta=$registros_act_ren_eq_nuevo->where('pedido',$pedido);
                       foreach ($registro_venta as $registro) {
                           //echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$registro->servicio."--".$registro->tipo_venta."--".$registro->importe."<br>";
                           $renta_padre=$registro->importe;
                           $plan_padre=$registro->servicio;
                           $movimiento_padre=$registro->tipo_venta;
                       }
                       //if(strpos($plan_padre,"CONSIGUELO")!== false)
                       if(
                           //strpos($plan_padre,"COMPARTELO")=== false 
                           strpos($plan_padre,"DAMOS")=== false
                            && strpos($plan_padre,"YA")=== false 
                            && strpos($plan_padre,"SIMPLE")=== false
                            && strpos($plan_padre,"ARMALO")=== false)
                       {
                           $bracket=$this->obtenBracket($renta_padre);
                           $comision_gte=$this->comisionSeguroConsiguelo_gerente($bracket,$movimiento_padre);
                           $comision_reg=$this->comisionSeguroConsiguelo_regional($bracket,$movimiento_padre);
                           $comision_dir=$this->comisionSeguroConsiguelo_director($bracket,$movimiento_padre);
                           if($esquema=="1" || $esquema=="2")
                           {
                            $comision=$this->comisionSeguroConsiguelo_1_2($bracket,$movimiento_padre);
                           }
                           else{
                            $comision=$this->comisionSeguroConsiguelo_3($bracket,$movimiento_padre);
                           }
                       }
                       if(strpos($plan_padre,"ARMALO")!== false)
                        {
                            $comision_gte=$this->comisionSeguroArmalo_gerente($plan_padre,$movimiento_padre);
                            $comision_reg=$this->comisionSeguroArmalo_regional($plan_padre,$movimiento_padre);
                            $comision_dir=$this->comisionSeguroArmalo_director($plan_padre,$movimiento_padre);
                            $comision=$this->comisionSeguroArmalo($plan_padre,$movimiento_padre);
                        }
                       //if(strpos($plan_padre,"COMPARTELO")!== false)
                       //{
                       //     $comision_gte=15;
                       //     $comision_reg=2;
                       //     $comision_dir=3;
                       //    if($esquema=="1" || $esquema=="2")
                       //    {
                       //     $comision=42;
                       //    }
                       //    else{
                       //     $comision=21;
                       //    }
                       //}
                    }
                }
            if($tipo_venta=="ADD ON") // INSTANCIA DE ADD ON
                {
                    $comision=$this->comisionAddOn($plan,$renta_transaccion);
                    $comision_gte=$this->comisionAddOn_gerente($plan,$renta_transaccion);
                    $comision_reg=$this->comisionAddOn_regional($plan,$renta_transaccion);
                    $comision_dir=$this->comisionAddOn_director($plan,$renta_transaccion);
                }
            
            
            //echo "-- Comision ".$comision;
            $transaccion_calculada=Transaccion::find($credito->id);
            $transaccion_calculada->comision_venta=$comision;
            $transaccion_calculada->comision_supervisor_l1=$comision_gte;
            $transaccion_calculada->comision_supervisor_l2=$comision_reg;
            $transaccion_calculada->comision_supervisor_l3=$comision_dir;
            $transaccion_calculada->save();
            $transacciones_pagadas=$transacciones_pagadas+1;
        }
        return($transacciones_pagadas);
   }

   public function comisiones_gerente_surcursales($id_calculo)
   {
       //$id_calculo=$request->id_calculo;
       $transacciones=Transaccion::where('calculo_id',$id_calculo)
                   ->where('credito',1)
                   ->orderBy('udn')
                   ->orderBy('numero_empleado')
                   ->orderBy('pedido')
                   ->get();
       $registros_act_ren_eq_nuevo=$transacciones->whereIn('tipo_venta',['Activación','Renovación','Activacion','Renovacion']); //PLANES CON EQUIPO NUEVO

       $mediciones=MedicionActorVenta::where('calculo_id',$id_calculo)
                   ->get();

        $transacciones_pagadas=0;

       foreach ($transacciones as $credito) {
           $renta_transaccion=$credito->importe;
           $tipo_venta=$credito->tipo_venta;
           $plan=$credito->servicio;
           $pedido=$credito->pedido;
           $transaccion_con_seguro="NO";
           $bracket=0;
           $comision=0;
           $eq_sin_costo=$credito->eq_sin_costo;

           //echo "<br>".$credito->pedido."--".$credito->tipo_venta."--".$credito->servicio."--".$renta_transaccion."SEGURO :".$transaccion_con_seguro;
           if($tipo_venta=="Activación" || $tipo_venta=="Activacion" ||
               $tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio" ||
               $tipo_venta=="Renovación" || $tipo_venta=="Renovacion" ||
               $tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio" ||
               $tipo_venta=="Protección de equipo" || $tipo_venta=="Proteccion de equipo" ||
               $tipo_venta=="Renovación Empresarial" || $tipo_venta=="Renovacion Empresarial")
               {
                   if($tipo_venta=="Renovación Empresarial" || $tipo_venta=="Renovacion Empresarial") {$tipo_venta="Renovación";}
                   if(
                   // strpos($plan,"COMPARTELO")=== false && 
                   strpos($plan,"DAMOS")=== false && 
                   strpos($plan,"YA")=== false && 
                   strpos($plan,"Protecci")=== false && 
                   strpos($plan,"SIMPLE")=== false) 
                   //SE TRATA DE UN PLAN CONSIGUELO U OTRO NO NOMBRADO
                   {
                        $bracket=$this->obtenBracket($renta_transaccion);
                        $comision=$this->comisionConsiguelo_gerente($bracket,$tipo_venta);
                        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion") && $eq_sin_costo)
                            {
                                $comision=$comision-$this->performanceElement_gerente($bracket);
                            }
                   }
                   if(strpos($plan,"DAMOS")!== false || strpos($plan,"YA")!== false || strpos($plan,"SIMPLE")!== false) // PLANES DAMOS MAS o YA
                   {   
                        $comision=$this->comisionDamosYa_gerente($renta_transaccion,$credito->plazo);
                   }
                   //if(strpos($plan,"COMPARTELO")!== false) // POPOTES, LINEAS ADICIONALES COMPARTELO
                   //{   
                   //     $comision=$this->comisionCompartelo_gerente($tipo_venta);
                   //}
                   if(strpos($plan,"Protecci")!== false) // INSTANCIA DE SEGURO
                   {   
                      $registro_venta=$registros_act_ren_eq_nuevo->where('pedido',$pedido);
                      foreach ($registro_venta as $registro) {
                          //echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$registro->servicio."--".$registro->tipo_venta."--".$registro->importe."<br>";
                          $renta_padre=$registro->importe;
                          $plan_padre=$registro->servicio;
                          $movimiento_padre=$registro->tipo_venta;
                      }
                      //if(strpos($plan_padre,"CONSIGUELO")!== false)
                      if(
                          //strpos($plan_padre,"COMPARTELO")=== false && 
                          strpos($plan_padre,"DAMOS")=== false && 
                          strpos($plan_padre,"YA")=== false && 
                          strpos($plan_padre,"SIMPLE")=== false)
                      {
                            $bracket=$this->obtenBracket($renta_padre);
                            $comision=$this->comisionSeguroConsiguelo_gerente($bracket,$movimiento_padre);
                      }
                      //if(strpos($plan_padre,"COMPARTELO")!== false)
                      //{
                      //     $comision=15;
                      //}
                   }
               }
           if($tipo_venta=="ADD ON") // INSTANCIA DE ADD ON
               {
                   $comision=$this->comisionAddOn_gerente($plan,$renta_transaccion);
               }
           //echo "-- Comision ".$comision;
           $transaccion_calculada=Transaccion::find($credito->id);
           $transaccion_calculada->comision_supervisor_l1=$comision;
           $transaccion_calculada->save();
           $transacciones_pagadas=$transacciones_pagadas+1;;
       }
       return($transacciones_pagadas);
  }


    public function obtenBracket($renta)
    {
        if($renta>=0 && $renta<240){return (1);}
        if($renta>=240 && $renta<300){return (2);}
        if($renta>=300 && $renta<400){return (3);}
        if($renta>=400 && $renta<500){return (4);}
        if($renta>=500 && $renta<600){return (5);}
        if($renta>=600 && $renta<700){return (6);}
        if($renta>=700 && $renta<800){return (7);}
        if($renta>=800 && $renta<1000){return (8);}
        if($renta>=1000 && $renta<1500){return (9);}
        if($renta>=1500 && $renta<2200){return (10);}
        if($renta>=2200 && $renta<2800){return (11);}
        if($renta>=2800 && $renta<4100){return (12);}
        if($renta>=4100 && $renta<5400){return (13);}
        if($renta>=5400){return (14);}

    }
    public function performanceElementEjecutivo_1_2($bracket)
    {
        if($bracket>=1 && $bracket<=2){return(100);}
        if($bracket>=3 && $bracket<=8){return(150);}
        if($bracket>=9) {return(250);}

    }
    public function performanceElementEjecutivo_3($bracket)
    {
        if($bracket>=1 && $bracket<=2){return(50);}
        if($bracket>=3 && $bracket<=8){return(75);}
        if($bracket>=9) {return(125);}

    }
    public function performanceElement_gerente($bracket)
    {
        return(26);
    }
    public function performanceElement_regional($bracket)
    {
        $pe=0;
        if($bracket==1){$pe=10;}
        if($bracket==2){$pe=11;}
        if($bracket==3){$pe=10;}
        if($bracket==4){$pe=13;}
        if($bracket==5){$pe=8;}
        if($bracket==6){$pe=10;}
        if($bracket==7){$pe=5;}
        if($bracket==8){$pe=6;}
        if($bracket==9){$pe=11;}
        if($bracket==10){$pe=28;}
        if($bracket==11){$pe=39;}
        if($bracket==12){$pe=144;}
        if($bracket==13){$pe=73;}
        if($bracket==14){$pe=73;}
        return($pe);
    }
    public function performanceElement_director($bracket)
    {
        $pe=0;
        if($bracket==1){$pe=5;}
        if($bracket==2){$pe=6;}
        if($bracket==3){$pe=5;}
        if($bracket==4){$pe=7;}
        if($bracket==5){$pe=5;}
        if($bracket==6){$pe=5;}
        if($bracket==7){$pe=6;}
        if($bracket==8){$pe=6;}
        if($bracket==9){$pe=9;}
        if($bracket==10){$pe=21;}
        if($bracket==11){$pe=36;}
        if($bracket==12){$pe=39;}
        if($bracket==13){$pe=58;}
        if($bracket==14){$pe=79;}
        return($pe);
    }
    public function comisionConsiguelo_1_2($bracket,$tipo_venta)
    {
        $comision=0;
        if($bracket==1)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=182;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=93;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=136;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=68;}
        }
        if($bracket==2)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=202;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=110;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=160;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=80;}
        }
        if($bracket==3)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=342;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=181;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=264;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=132;}
        }
        if($bracket==4)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=430;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=244;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=355;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=178;}
        }
        if($bracket==5)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=495;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=280;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=406;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=203;}
        }
        if($bracket==6)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=594;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=336;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=488;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=244;}
        }
        if($bracket==7)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=693;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=392;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=569;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=285;}
        }
        if($bracket==8)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=792;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=448;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=650;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=325;}
        }
        if($bracket==9)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=990;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=560;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=813;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=406;}
        }
        if($bracket==10)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=1484;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=841;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=1219;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=610;}
        }
        if($bracket==11)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=1711;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=1233;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=1250;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=625;}
        }
        if($bracket==12)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=2771;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=1569;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=1600;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=800;}
        }
        if($bracket==13)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=4058;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=2297;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=2350;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=1175;}
        }
        if($bracket==14)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=5344;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=3026;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3100;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=1550;}
        }
        return($comision);
    }
    public function comisionConsiguelo_3($bracket,$tipo_venta)
    {
        $comision=0;
        if($bracket==1)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=91;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=47;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=68;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=34;}
        }
        if($bracket==2)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=101;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=55;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=80;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=40;}
        }
        if($bracket==3)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=171;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=91;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=132;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=66;}
        }
        if($bracket==4)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=215;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=122;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=178;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=89;}
        }
        if($bracket==5)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=247;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=140;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=203;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=102;}
        }
        if($bracket==6)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=297;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=168;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=244;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=122;}
        }
        if($bracket==7)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=346;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=196;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=285;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=142;}
        }
        if($bracket==8)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=396;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=224;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=325;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=163;}
        }
        if($bracket==9)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=495;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=280;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=406;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=203;}
        }
        if($bracket==10)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=742;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=420;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=610;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=305;}
        }
        if($bracket==11)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=855;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=616;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=625;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=313;}
        }
        if($bracket==12)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=1386;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=784;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=800;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=400;}
        }
        if($bracket==13)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=2029;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=1149;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=1175;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=588;}
        }
        if($bracket==14)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=2672;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=1513;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=1550;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=775;}
        }
        return($comision);
    }
    public function comisionConsiguelo_gerente($bracket,$tipo_venta)
    {
        $comision=0;
        if($bracket==1)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=34;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=37;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=37;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=19;}
        }
        if($bracket==2)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=43;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=44;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=44;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=22;}
        }
        if($bracket==3)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=99;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=72;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=72;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=36;}
        }
        if($bracket==4)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=129;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=97;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=97;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=48;}
        }
        if($bracket==5)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=130;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=111;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=111;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=56;}
        }
        if($bracket==6)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=156;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=134;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=134;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=67;}
        }
        if($bracket==7)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=182;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=156;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=156;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=78;}
        }
        if($bracket==8)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=208;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=178;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=178;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=89;}
        }
        if($bracket==9)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=260;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=223;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=223;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=111;}
        }
        if($bracket==10)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=390;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=334;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=334;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=167;}
        }
        if($bracket==11)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=572;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=490;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=490;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=245;}
        }
        if($bracket==12)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=728;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=624;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=624;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=312;}
        }
        if($bracket==13)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=1066;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=914;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=914;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=457;}
        }
        if($bracket==14)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=1405;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=1204;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=1204;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=602;}
        }
        return($comision);
    }
    public function comisionConsiguelo_regional($bracket,$tipo_venta)
    {
        $comision=0;
        if($bracket==1)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=11;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=14;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=8;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=4;}
        }
        if($bracket==2)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=11;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=17;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=9;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=5;}
        }
        if($bracket==3)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=20;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=28;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=14;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=7;}
        }
        if($bracket==4)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=24;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=37;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=19;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=10;}
        }
        if($bracket==5)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=28;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=43;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=22;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=11;}
        }
        if($bracket==6)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=34;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=52;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=26;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=13;}
        }
        if($bracket==7)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=39;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=60;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=30;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=15;}
        }
        if($bracket==8)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=45;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=69;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=34;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=17;}
        }
        if($bracket==9)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=56;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=86;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=43;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=22;}
        }
        if($bracket==10)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=84;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=129;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=65;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=32;}
        }
        if($bracket==11)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=123;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=190;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=95;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=47;}
        }
        if($bracket==12)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=157;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=241;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=121;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=60;}
        }
        if($bracket==13)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=230;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=353;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=177;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=88;}
        }
        if($bracket==14)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=303;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=466;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=233;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=116;}
        }
        return($comision);
    }
    public function comisionConsiguelo_director($bracket,$tipo_venta)
    {
        $comision=0;
        if($bracket==1)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=5;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=8;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=2;}
        }
        if($bracket==2)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=6;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=9;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=4;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=2;}
        }
        if($bracket==3)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=10;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=14;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=7;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=4;}
        }
        if($bracket==4)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=13;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=19;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=10;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=5;}
        }
        if($bracket==5)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=15;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=22;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=11;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=5;}
        }
        if($bracket==6)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=18;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=26;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=13;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=6;}
        }
        if($bracket==7)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=21;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=30;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=15;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=8;}
        }
        if($bracket==8)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=24;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=34;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=9;}
        }
        if($bracket==9)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=30;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=43;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=22;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=11;}
        }
        if($bracket==10)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=45;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=65;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=32;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=16;}
        }
        if($bracket==11)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=66;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=95;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=47;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=24;}
        }
        if($bracket==12)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=84;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=121;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=60;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=30;}
        }
        if($bracket==13)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=124;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=177;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=88;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=44;}
        }
        if($bracket==14)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=163;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=233;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=116;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=58;}
        }
        return($comision);
    }
    public function comisionConsiguelo_cc($bracket,$tipo_venta,$act,$ren)
    {
        $comision=0;
        if($bracket==1)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=47;}
                if($act>=16){$comision=91;}
            }
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=46;}
                if($act>=16){$comision=64;}
            }
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=35;}
                if($ren>=16){$comision=76;}
            }
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=18;}
                if($ren>=16){$comision=38;}
            }
        }
        if($bracket==2)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=56;}
                if($act>=16){$comision=107;}
            }
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=55;}
                if($act>=16){$comision=76;}
            }
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=42;}
                if($ren>=16){$comision=90;}
            }
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=21;}
                if($ren>=16){$comision=45;}
            }
        }
        if($bracket==3)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=92;}
                if($act>=16){$comision=176;}
            }
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=91;}
                if($act>=16){$comision=125;}
            }
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=69;}
                if($ren>=16){$comision=149;}
            }
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=35;}
                if($ren>=16){$comision=75;}
            }
        }
        if($bracket==4)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
            {
                //echo '-------'.$act.'------';
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=123;}
                if($act>=16){$comision=237;}
            }
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=123;}
                if($act>=16){$comision=170;}
            }
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=93;}
                if($ren>=16){$comision=201;}
            }
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=47;}
                if($ren>=16){$comision=101;}
            }
        }
        if($bracket==5)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=141;}
                if($act>=16){$comision=272;}
            }
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=141;}
                if($act>=16){$comision=194;}
            }
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=106;}
                if($ren>=16){$comision=230;}
            }
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=53;}
                if($ren>=16){$comision=115;}
            }
        }
        if($bracket==6)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=170;}
                if($act>=16){$comision=326;}
            }
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=170;}
                if($act>=16){$comision=233;}
            }
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=127;}
                if($ren>=16){$comision=276;}
            }
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=64;}
                if($ren>=16){$comision=138;}
            }
        }
        if($bracket==7)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=198;}
                if($act>=16){$comision=380;}
            }
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=198;}
                if($act>=16){$comision=272;}
            }
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=148;}
                if($ren>=16){$comision=322;}
            }
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=74;}
                if($ren>=16){$comision=161;}
            }
        }
        if($bracket==8)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=226;}
                if($act>=16){$comision=434;}
            }
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=226;}
                if($act>=16){$comision=311;}
            }
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=170;}
                if($ren>=16){$comision=368;}
            }
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=85;}
                if($ren>=16){$comision=184;}
            }
        }
        if($bracket==9)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=283;}
                if($act>=16){$comision=543;}
            }
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=283;}
                if($act>=16){$comision=389;}
            }
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=212;}
                if($ren>=16){$comision=459;}
            }
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=106;}
                if($ren>=16){$comision=230;}
            }
        }
        if($bracket==10)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=424;}
                if($act>=16){$comision=815;}
            }
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=424;}
                if($act>=16){$comision=583;}
            }
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=318;}
                if($ren>=16){$comision=689;}
            }
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=159;}
                if($ren>=16){$comision=345;}
            }
        }
        if($bracket==11)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=622;}
                if($act>=16){$comision=1195;}
            }
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=622;}
                if($act>=16){$comision=855;}
            }
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=467;}
                if($ren>=16){$comision=1011;}
            }
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=233;}
                if($ren>=16){$comision=505;}
            }
        }
        if($bracket==12)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=792;}
                if($act>=16){$comision=1521;}
            }
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=792;}
                if($act>=16){$comision=1089;}
            }
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=594;}
                if($ren>=16){$comision=1287;}
            }
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=297;}
                if($ren>=16){$comision=643;}
            }
        }
        if($bracket==13)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=1159;}
                if($act>=16){$comision=2227;}
            }
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=1159;}
                if($act>=16){$comision=1594;}
            }
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=869;}
                if($ren>=16){$comision=1884;}
            }
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=435;}
                if($ren>=16){$comision=942;}
            }
        }
        if($bracket==14)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=1527;}
                if($act>=16){$comision=2933;}
            }
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
            {
                if($act<6){$comision=0;}
                if($act>=6 && $act<16){$comision=1527;}
                if($act>=16){$comision=2099;}
            }
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=1145;}
                if($ren>=16){$comision=2481;}
            }
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
            {
                if($ren<6){$comision=0;}
                if($ren>=6 && $ren<16){$comision=573;}
                if($ren>=16){$comision=1241;}
            }
        }
        
        return($comision);
    }
    public function comisionConsiguelo_gerente_cc($bracket,$tipo_venta)
    {
        $comision=0;
        if($bracket==1)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=28;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=21;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=22;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=11;}
        }
        if($bracket==2)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=34;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=25;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=13;}
        }
        if($bracket==3)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=56;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=42;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=42;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=21;}
        }
        if($bracket==4)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=75;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=57;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=57;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=29;}
        }
        if($bracket==5)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=86;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=65;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=65;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=32;}
        }
        if($bracket==6)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=103;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=78;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=78;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=39;}
        }
        if($bracket==7)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=121;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=91;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=91;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=45;}
        }
        if($bracket==8)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=138;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=103;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=103;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=52;}
        }
        if($bracket==9)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=172;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=129;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=129;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=65;}
        }
        if($bracket==10)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=259;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=194;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=194;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=97;}
        }
        if($bracket==11)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=379;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=284;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=284;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=142;}
        }
        if($bracket==12)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=483;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=362;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=362;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=181;}
        }
        if($bracket==13)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=707;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=530;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=530;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=265;}
        }
        if($bracket==14)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=931;}
            if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=698;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=698;}
            if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=349;}
        }
        return($comision);
    }
    
    public function comisionDamosYa_1_2($renta,$plazo)
    {
        $comision=0;
        if($plazo=="6")
        {
            $comision=$renta*0.15/1.16;
        }
        if($plazo=="12")
        {
            $comision=$renta*0.4/1.16;
        }
        if($plazo=="18")
        {
            $comision=$renta*0.65/1.16;
        }
        if($plazo=="24")
        {
            $comision=$renta*0.8/1.16;
        }
        return($comision);
    }
    public function comisionDamosYa_3($renta,$plazo)
    {
        $comision=0;
        if($plazo=="6")
        {
            $comision=$renta*0.08/1.16;
        }
        if($plazo=="12")
        {
            $comision=$renta*0.2/1.16;
        }
        if($plazo=="18")
        {
            $comision=$renta*0.33/1.16;
        }
        if($plazo=="24")
        {
            $comision=$renta*0.4/1.16;
        }
        return($comision);
    }
    public function comisionDamosYa_gerente($renta,$plazo)
    {
        $comision=0;
        if($plazo=="6")
        {
            $comision=$renta*0.04/1.16;
        }
        if($plazo=="12")
        {
            $comision=$renta*0.13/1.16;
        }
        if($plazo=="18")
        {
            $comision=$renta*0.16/1.16;
        }
        if($plazo=="24")
        {
            $comision=$renta*0.18/1.16;
        }
        return($comision);
    }
    public function comisionDamosYa_regional($renta,$plazo)
    {
        $comision=0;
        if($plazo=="6")
        {
            $comision=$renta*0.02/1.16;
        }
        if($plazo=="12")
        {
            $comision=$renta*0.06/1.16;
        }
        if($plazo=="18")
        {
            $comision=$renta*0.07/1.16;
        }
        if($plazo=="24")
        {
            $comision=$renta*0.08/1.16;
        }
        return($comision);
    }
    public function comisionDamosYa_director($renta,$plazo)
    {
        $comision=0;
        if($plazo=="6")
        {
            $comision=$renta*0.01/1.16;
        }
        if($plazo=="12")
        {
            $comision=$renta*0.04/1.16;
        }
        if($plazo=="18")
        {
            $comision=$renta*0.05/1.16;
        }
        if($plazo=="24")
        {
            $comision=$renta*0.06/1.16;
        }
        return($comision);
    }
    public function comisionCompartelo_1_2($tipo_venta)
    {
        $comision=0;
        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=213;}
        if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=180;}
        if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=213;}
        if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=180;}
        return($comision);
    }
    public function comisionCompartelo_3($tipo_venta)
    {
        $comision=0;
        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=107;}
        if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=90;}
        if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=107;}
        if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=90;}
        return($comision);
    }
    public function comisionCompartelo_gerente($tipo_venta)
    {
        $comision=0;
        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=52;}
        if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=43;}
        if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=52;}
        if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=43;}
        return($comision);
    }
    public function comisionCompartelo_regional($tipo_venta)
    {
        $comision=0;
        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=15;}
        if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=10;}
        if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=15;}
        if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=10;}
        return($comision);
    }
    public function comisionCompartelo_director($tipo_venta)
    {
        $comision=0;
        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=7;}
        if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=5;}
        if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=7;}
        if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=5;}
        return($comision);
    }
    public function comisionCompartelo_cc($tipo_venta)
    {
        $comision=0;
        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=94;}
        if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=68;}
        if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=94;}
        if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=68;}
        return($comision);
    }
    public function comisionCompartelo_gerente_cc($tipo_venta)
    {
        $comision=0;
        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=34;}
        if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")){$comision=29;}
        if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=34;}
        if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")){$comision=29;}
        return($comision);
    }
    public function comisionSeguroConsiguelo_1_2($bracket,$tipo_venta)
    {
        $comision=0;
        if($bracket==1)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=61;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if($bracket==2)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=62;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if($bracket==3)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=66;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if($bracket==4)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=70;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if($bracket==5)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=75;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if($bracket==6)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=80;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if($bracket==7)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=85;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if($bracket==8)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=90;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if($bracket==9)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=99;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if($bracket==10)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=124;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if($bracket==11)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=136;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if($bracket==12)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=189;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if($bracket==13)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=253;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if($bracket==14)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=317;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        return($comision);
    }
    public function comisionSeguroConsiguelo_3($bracket,$tipo_venta)
    {
        $comision=0;
        if($bracket==1)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=30;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
        }
        if($bracket==2)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=31;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
        }
        if($bracket==3)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=33;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
        }
        if($bracket==4)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=35;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
        }
        if($bracket==5)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=38;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
        }
        if($bracket==6)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=40;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
        }
        if($bracket==7)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=43;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
        }
        if($bracket==8)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=45;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
        }
        if($bracket==9)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=50;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
        }
        if($bracket==10)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=62;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
        }
        if($bracket==11)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=68;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
        }
        if($bracket==12)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=94;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
        }
        if($bracket==13)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=126;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
        }
        if($bracket==14)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=159;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=25;}
        }
        return($comision);
    }
    public function comisionSeguroConsiguelo_gerente($bracket,$tipo_venta)
    {
        $comision=0;
        if($bracket==1)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=18;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if($bracket==2)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=18;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if($bracket==3)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=19;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if($bracket==4)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=19;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if($bracket==5)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=20;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if($bracket==6)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=20;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if($bracket==7)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=21;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if($bracket==8)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=21;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if($bracket==9)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=22;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if($bracket==10)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=25;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if($bracket==11)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=29;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if($bracket==12)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=32;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if($bracket==13)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=39;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if($bracket==14)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=37;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        return($comision);
    }
    public function comisionSeguroConsiguelo_regional($bracket,$tipo_venta)
    {
        $comision=0;
        if($bracket==1)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=5;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if($bracket==2)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=5;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if($bracket==3)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=5;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if($bracket==4)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=5;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if($bracket==5)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=6;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if($bracket==6)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=6;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if($bracket==7)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=6;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if($bracket==8)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=6;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if($bracket==9)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=6;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if($bracket==10)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=7;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if($bracket==11)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=7;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if($bracket==12)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=8;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if($bracket==13)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=10;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if($bracket==14)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=11;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        return($comision);
    }
    public function comisionSeguroConsiguelo_director($bracket,$tipo_venta)
    {
        $comision=0;
        if($bracket==1)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if($bracket==2)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if($bracket==3)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if($bracket==4)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if($bracket==5)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if($bracket==6)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if($bracket==7)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if($bracket==8)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if($bracket==9)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=4;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if($bracket==10)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=4;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if($bracket==11)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=4;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if($bracket==12)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=5;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if($bracket==13)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=5;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if($bracket==14)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=6;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        return($comision);
    }
    public function comisionSeguroConsiguelo_cc($bracket,$tipo_venta,$act,$ren)
    {
        $comision=0;

        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
        {
            if($act<6){$comision=0;}
            if($act>=6 && $act<16){$comision=35;}
            if($act>=16){$comision=40;}
        }
        if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
        {
            if($ren<6){$comision=0;}
            if($ren>=6 && $ren<16){$comision=35;}
            if($ren>=16){$comision=40;}
        }
        return($comision);
    }
    public function comisionSeguroConsiguelo_gerente_cc($bracket,$tipo_venta)
    {
        $comision=0;

        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=20;}
        if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=20;}
        return($comision);
    }
    public function comisionAddOn($addon,$renta)
    {
        return($renta/1.16*0.5);
    }
    public function comisionAddOn_gerente($addon,$renta)
    {
        if(strpos($addon,'CONTROL')===false && strpos($addon,'MPP')===false)
        {
            return($renta/1.16*0.25);
        }
        else{
            return(0);
        }
    }
    public function comisionAddOn_regional($addon,$renta)
    {
        if(strpos($addon,'CONTROL')===false && strpos($addon,'MPP')===false)
        {
            return($renta/1.16*0.15);
        }
        else{
            return(0);
        }
    }
    public function comisionAddOn_director($addon,$renta)
    {
        if(strpos($addon,'CONTROL')===false && strpos($addon,'MPP')===false)
        {
            return($renta/1.16*0.10);
        }
        else{
            return(0);
        }
    }
    public function medicion_actor_ventas_sucursales($id_calculo)
    {
        $borrados=MedicionActorVenta::where('calculo_id',$id_calculo)->delete();

        $conteos = DB::table('transaccions')
            ->select('numero_empleado', 'tipo_venta',DB::raw('count(tipo_venta) as uds'),DB::raw('sum(importe) as rentas'))
            ->where('calculo_id',$id_calculo)
            ->where('credito',1)
            ->groupBy('numero_empleado','tipo_venta')
            ->get();

        $mediciones=0;
        $anterior=0;
        $uds_activacion=0;
        $renta_activacion=0.0;
        $uds_aep=0;
        $renta_aep=0.0;
        $uds_renovacion=0;
        $renta_renovacion=0.0;
        $uds_rep=0;
        $renta_rep=0.0;
        $uds_seguro=0;
        $renta_seguro=0.0;
        $uds_addon=0;
        $renta_addon=0.0;

        foreach($conteos as $conteo)
        {
            if($anterior!=$conteo->numero_empleado)
            {
                if($anterior!=0)
                {
                    $medicion_corte=new MedicionActorVenta();
                    $medicion_corte->numero_empleado=$anterior;
                    $medicion_corte->uds_activacion=$uds_activacion;
                    $medicion_corte->renta_activacion=$renta_activacion;
                    $medicion_corte->uds_aep=$uds_aep;
                    $medicion_corte->renta_aep=$renta_aep;
                    $medicion_corte->uds_renovacion=$uds_renovacion;
                    $medicion_corte->renta_renovacion=$renta_renovacion;
                    $medicion_corte->uds_rep=$uds_rep;
                    $medicion_corte->renta_rep=$renta_rep;
                    $medicion_corte->uds_seguro=$uds_seguro;
                    $medicion_corte->renta_seguro=$renta_seguro;
                    $medicion_corte->uds_addon=$uds_addon;
                    $medicion_corte->renta_addon=$renta_addon;
                    $medicion_corte->calculo_id=$id_calculo;
                    $medicion_corte->save();
                    $mediciones=$mediciones+1;
                }
                
                $uds_activacion=0;
                $renta_activacion=0.0;
                $uds_aep=0;
                $renta_aep=0.0;
                $uds_renovacion=0;
                $renta_renovacion=0.0;
                $uds_rep=0;
                $renta_rep=0.0;
                $uds_seguro=0;
                $renta_seguro=0.0;
                $uds_addon=0;
                $renta_addon=0.0; 
            }
            if($conteo->tipo_venta=="Activación" || $conteo->tipo_venta=="Activacion")
             {
                 $uds_activacion=$uds_activacion+$conteo->uds;
                 $renta_activacion=$renta_activacion+$conteo->rentas;
             }
             if($conteo->tipo_venta=="Activación Equipo Propio" || $conteo->tipo_venta=="Activacion Equipo Propio")
             {
                 $uds_aep=$uds_aep+$conteo->uds;
                 $renta_aep=$renta_aep+$conteo->rentas;
             }
             if($conteo->tipo_venta=="Renovación" || $conteo->tipo_venta=="Renovación Empresarial" || $conteo->tipo_venta=="Renovacion" || $conteo->tipo_venta=="Renovacion Empresarial")
             {
                 $uds_renovacion=$uds_renovacion+$conteo->uds;
                 $renta_renovacion=$renta_renovacion+$conteo->rentas;
             }
             if($conteo->tipo_venta=="Renovación Equipo Propio" || $conteo->tipo_venta=="Renovacion Equipo Propio")
             {
                 $uds_rep=$uds_rep+$conteo->uds;
                 $renta_rep=$renta_rep+$conteo->rentas;
             }
             if($conteo->tipo_venta=="Protección de equipo" || $conteo->tipo_venta=="Proteccion de equipo")
             {
                 $uds_seguro=$uds_seguro+$conteo->uds;
                 $renta_seguro=$renta_seguro+$conteo->rentas;
             }
             if($conteo->tipo_venta=="ADD ON")
             {
                 $uds_addon=$uds_addon+$conteo->uds;
                 $renta_addon=$renta_addon+$conteo->rentas;
             }

            $anterior=$conteo->numero_empleado;
        }
        if($anterior!=0)
            {
                $medicion_corte=new MedicionActorVenta();
                $medicion_corte->numero_empleado=$anterior;
                $medicion_corte->uds_activacion=$uds_activacion;
                $medicion_corte->renta_activacion=$renta_activacion;
                $medicion_corte->uds_aep=$uds_aep;
                $medicion_corte->renta_aep=$renta_aep;
                $medicion_corte->uds_renovacion=$uds_renovacion;
                $medicion_corte->renta_renovacion=$renta_renovacion;
                $medicion_corte->uds_rep=$uds_rep;
                $medicion_corte->renta_rep=$renta_rep;
                $medicion_corte->uds_seguro=$uds_seguro;
                $medicion_corte->renta_seguro=$renta_seguro;
                $medicion_corte->uds_addon=$uds_addon;
                $medicion_corte->renta_addon=$renta_addon;
                $medicion_corte->calculo_id=$id_calculo;
                $medicion_corte->save();
                $mediciones=$mediciones+1;
                }
        return($mediciones);


    } 
    public function medicion_sucursales($id_calculo)
    {
        $borrados=MedicionGerente::where('calculo_id',$id_calculo)->delete();

        $conteos = DB::table('transaccions')
            ->select('udn', 'tipo_venta',DB::raw('count(tipo_venta) as uds'),DB::raw('sum(importe) as rentas'))
            ->where('calculo_id',$id_calculo)
            ->where('credito',1)
            ->groupBy('udn','tipo_venta')
            ->get();

        $mediciones=0;
        $sucursal_anterior=0;
        $uds_activacion=0;
        $renta_activacion=0.0;
        $uds_aep=0;
        $renta_aep=0.0;
        $uds_renovacion=0;
        $renta_renovacion=0.0;
        $uds_rep=0;
        $renta_rep=0.0;
        $uds_seguro=0;
        $renta_seguro=0.0;
        $uds_addon=0;
        $renta_addon=0.0;

        foreach($conteos as $conteo)
        {
            if($sucursal_anterior!=$conteo->udn)
            {
                if($sucursal_anterior!=0)
                {
                    $medicion_corte=new MedicionGerente();
                    $medicion_corte->udn=$sucursal_anterior;
                    $medicion_corte->uds_activacion=$uds_activacion;
                    $medicion_corte->renta_activacion=$renta_activacion;
                    $medicion_corte->uds_aep=$uds_aep;
                    $medicion_corte->renta_aep=$renta_aep;
                    $medicion_corte->uds_renovacion=$uds_renovacion;
                    $medicion_corte->renta_renovacion=$renta_renovacion;
                    $medicion_corte->uds_rep=$uds_rep;
                    $medicion_corte->renta_rep=$renta_rep;
                    $medicion_corte->uds_seguro=$uds_seguro;
                    $medicion_corte->renta_seguro=$renta_seguro;
                    $medicion_corte->uds_addon=$uds_addon;
                    $medicion_corte->renta_addon=$renta_addon;
                    $medicion_corte->calculo_id=$id_calculo;
                    $medicion_corte->save();
                    $mediciones=$mediciones+1;
                }
                
                $uds_activacion=0;
                $renta_activacion=0.0;
                $uds_aep=0;
                $renta_aep=0.0;
                $uds_renovacion=0;
                $renta_renovacion=0.0;
                $uds_rep=0;
                $renta_rep=0.0;
                $uds_seguro=0;
                $renta_seguro=0.0;
                $uds_addon=0;
                $renta_addon=0.0; 
            }
            if($conteo->tipo_venta=="Activación" || $conteo->tipo_venta=="Activacion")
             {
                 $uds_activacion=$uds_activacion+$conteo->uds;
                 $renta_activacion=$renta_activacion+$conteo->rentas;
             }
             if($conteo->tipo_venta=="Activación Equipo Propio" || $conteo->tipo_venta=="Activacion Equipo Propio")
             {
                 $uds_aep=$uds_aep+$conteo->uds;
                 $renta_aep=$renta_aep+$conteo->rentas;
             }
             if($conteo->tipo_venta=="Renovación" || $conteo->tipo_venta=="Renovación Empresarial" || $conteo->tipo_venta=="Renovacion" || $conteo->tipo_venta=="Renovacion Empresarial")
             {
                 $uds_renovacion=$uds_renovacion+$conteo->uds;
                 $renta_renovacion=$renta_renovacion+$conteo->rentas;
             }
             if($conteo->tipo_venta=="Renovación Equipo Propio" || $conteo->tipo_venta=="Renovacion Equipo Propio")
             {
                 $uds_rep=$uds_rep+$conteo->uds;
                 $renta_rep=$renta_rep+$conteo->rentas;
             }
             if($conteo->tipo_venta=="Protección de equipo" || $conteo->tipo_venta=="Proteccion de equipo")
             {
                 $uds_seguro=$uds_seguro+$conteo->uds;
                 $renta_seguro=$renta_seguro+$conteo->rentas;
             }
             if($conteo->tipo_venta=="ADD ON")
             {
                 $uds_addon=$uds_addon+$conteo->uds;
                 $renta_addon=$renta_addon+$conteo->rentas;
             }

            $sucursal_anterior=$conteo->udn;
        }
        if($sucursal_anterior!=0)
            {
                $medicion_corte=new MedicionGerente();
                $medicion_corte->udn=$sucursal_anterior;
                $medicion_corte->uds_activacion=$uds_activacion;
                $medicion_corte->renta_activacion=$renta_activacion;
                $medicion_corte->uds_aep=$uds_aep;
                $medicion_corte->renta_aep=$renta_aep;
                $medicion_corte->uds_renovacion=$uds_renovacion;
                $medicion_corte->renta_renovacion=$renta_renovacion;
                $medicion_corte->uds_rep=$uds_rep;
                $medicion_corte->renta_rep=$renta_rep;
                $medicion_corte->uds_seguro=$uds_seguro;
                $medicion_corte->renta_seguro=$renta_seguro;
                $medicion_corte->uds_addon=$uds_addon;
                $medicion_corte->renta_addon=$renta_addon;
                $medicion_corte->calculo_id=$id_calculo;
                $medicion_corte->save();
                $mediciones=$mediciones+1;
                }
        return($mediciones);
    } 
    public function comisiones_apoyo_tmkt($id_calculo)
    {
        $transacciones_calculadas_tmkt=0;
        $transacciones_cc=Transaccion::where('calculo_id',$id_calculo)
                    ->where('ejecutivoCC','<>',0)
                    ->where('credito',1)
                    ->get();
        $registros_act_ren_eq_nuevo=$transacciones_cc->whereIn('tipo_venta',['Activación','Renovación','Activacion','Renovacion']); //PLANES CON EQUIPO NUEVO
        $empleados_cc=DB::table('transaccions')
                    ->select('ejecutivoCC')
                    ->distinct()
                    ->where('calculo_id',$id_calculo)
                    ->where('credito',1)
                    ->where('ejecutivoCC','<>',0)
                    ->get();
        foreach($empleados_cc as $empleado)
        {
            $activaciones_logradas=0;
            $renovaciones_logradas=0;
            $transacciones_empleado=$transacciones_cc->where('ejecutivoCC',$empleado->ejecutivoCC);
            foreach($transacciones_empleado as $transaccion)
            {
                //echo 'T'.$transaccion->tipo_venta.'-'.$transaccion->ejecutivoCC.'-ACT'.$activaciones_logradas.'-REN'.$renovaciones_logradas;
                if($transaccion->tipo_venta=='Activacion' ||
                   $transaccion->tipo_venta=='Activación')
                   {
                       $activaciones_logradas=$activaciones_logradas+1;
                   }
                if($transaccion->tipo_venta=='Renovacion' ||
                   $transaccion->tipo_venta=='Renovación' ||
                   $transaccion->tipo_venta=='Renovación Empresarial' ||
                   $transaccion->tipo_venta=='Renovacion Empresarial')
                   {
                       $renovaciones_logradas=$renovaciones_logradas+1;
                   }
            }
            //echo 'Q'.$empleado->ejecutivoCC.'-'.$activaciones_logradas.'-'.$renovaciones_logradas;
            foreach($transacciones_empleado as $transaccion)
            {
                $comision=0;
                $comision_gte=0;
                if($transaccion->tipo_venta=="Activación" || $transaccion->tipo_venta=="Activacion" ||
                $transaccion->tipo_venta=="Activación Equipo Propio" || $transaccion->tipo_venta=="Activacion Equipo Propio" ||
                $transaccion->tipo_venta=="Renovación" || $transaccion->tipo_venta=="Renovacion" ||
                $transaccion->tipo_venta=="Renovación Equipo Propio" || $transaccion->tipo_venta=="Renovacion Equipo Propio" ||
                $transaccion->tipo_venta=="Protección de equipo" || $transaccion->tipo_venta=="Proteccion de equipo" ||
                $transaccion->tipo_venta=="Renovación Empresarial" || $transaccion->tipo_venta=="Renovacion Empresarial")
                {
                    if($transaccion->tipo_venta=="Renovación Empresarial" || $transaccion->tipo_venta=="Renovacion Empresarial") {$transaccion->tipo_venta="Renovación";}
                    if(
                        //strpos($transaccion->servicio,"COMPARTELO")=== false && 
                        strpos($transaccion->servicio,"DAMOS")=== false && 
                        strpos($transaccion->servicio,"YA")=== false && 
                        strpos($transaccion->servicio,"Protecci")=== false && 
                        strpos($transaccion->servicio,"SIMPLE")=== false) 
                    {
                        $bracket=$this->obtenBracket($transaccion->importe);
                        $comision=$this->comisionConsiguelo_cc($bracket,$transaccion->tipo_venta,$activaciones_logradas,$renovaciones_logradas);
                        $comision_gte=$this->comisionConsiguelo_gerente_cc($bracket,$transaccion->tipo_venta);
                    }
                    //if(strpos($transaccion->servicio,"COMPARTELO")!== false)
                    //{
                    //    $comision=$this->comisionCompartelo_cc($transaccion->tipo_venta);
                    //    $comision_gte=$this->comisionCompartelo_gerente_cc($transaccion->tipo_venta);
                    //
                    //}
                    if(strpos($transaccion->servicio,"Protecci")!== false) // INSTANCIA DE SEGURO
                   {   
                      $registro_venta=$registros_act_ren_eq_nuevo->where('pedido',$transaccion->pedido);
                      foreach ($registro_venta as $registro) {
                          //echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$registro->servicio."--".$registro->tipo_venta."--".$registro->importe."<br>";
                          $renta_padre=$registro->importe;
                          $plan_padre=$registro->servicio;
                          $movimiento_padre=$registro->tipo_venta;
                      }
                      //if(strpos($plan_padre,"CONSIGUELO")!== false)
                      if(
                          //strpos($plan_padre,"COMPARTELO")=== false && 
                          strpos($plan_padre,"DAMOS")=== false && 
                          strpos($plan_padre,"YA")=== false && 
                          strpos($plan_padre,"SIMPLE")=== false)
                      {
                            $bracket=$this->obtenBracket($renta_padre);
                            $comision=$this->comisionSeguroConsiguelo_cc($bracket,$movimiento_padre,$activaciones_logradas,$renovaciones_logradas);
                            $comision_gte=$this->comisionSeguroConsiguelo_gerente_cc($bracket,$movimiento_padre);
                      }
                      //if(strpos($plan_padre,"COMPARTELO")!== false)
                      //{
                      //     $comision=42;
                      //     $comision_gte=17;
                      //}
                   }

                }

                $registro_existente=Transaccion::find($transaccion->id);
                $registro_existente->comisionCC=$comision;
                $registro_existente->comision_supervisor_cc=$comision_gte;
                $registro_existente->comision_venta=$registro_existente->comision_venta-$comision;
                $registro_existente->comision_supervisor_l1=$registro_existente->comision_supervisor_l1-$comision_gte;
                $transacciones_calculadas_tmkt=$transacciones_calculadas_tmkt+1;
                $registro_existente->save();
                    
            }

        }
        return($transacciones_calculadas_tmkt);
    }
    public function calculo_comisiones(Request $request)
    {
        $id_calculo=$request->id;
        $respuesta=array(
            'transacciones_calculadas'=>0,
            'transacciones_cc'=>0
        );
        //$respuesta['personas_medidas']=$this->medicion_actor_ventas_sucursales($id_calculo);
        //$respuesta['sucursales_medidas']=$this->medicion_sucursales($id_calculo);
        DB::delete('delete from errors where calculo_id='.$id_calculo);
        $respuesta['transacciones_calculadas']=$this->comisiones_actor_ventas_surcursales($id_calculo);
        //$respuesta['transacciones_calculadas_gerente']=$this->comisiones_gerente_surcursales($id_calculo);
        $respuesta['transacciones_cc']=$this->comisiones_apoyo_tmkt($id_calculo);
        return($respuesta);
    }
    public function calculo_terminar(Request $request)
    {
        Calculo::where('id',$request->id_calculo)
                ->update(['terminado'=>true]);
    }
    public function calculo_consulta(Request $request)
    {
        $calculoRow=Calculo::where('id',$request->id_calculo)
        ->get();
        return($calculoRow);
    }
    public function comisionArmalo($plan,$tipo_venta)
    {
        $comision=0;

        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
        {
            if(strpos($plan,"3")!== false){$comision=196;}
            if(strpos($plan,"5")!== false){$comision=368;}
            if(strpos($plan,"9")!== false){$comision=501;} //NUEVO
            if(strpos($plan,"11")!== false){$comision=504;}
            if(strpos($plan,"14")!== false){$comision=607;} //NUEVO
            if(strpos($plan,"17")!== false){$comision=696;}
            if(strpos($plan,"20")!== false){$comision=795;} //NUEVO
            if(strpos($plan,"26")!== false){$comision=917;}
            if(strpos($plan,"40")!== false){$comision=1242;}
        }
        if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
        {
            if(strpos($plan,"3")!== false){$comision=110;}
            if(strpos($plan,"5")!== false){$comision=205;}
            if(strpos($plan,"9")!== false){$comision=284;}
            if(strpos($plan,"11")!== false){$comision=287;}
            if(strpos($plan,"14")!== false){$comision=344;}
            if(strpos($plan,"17")!== false){$comision=394;}
            if(strpos($plan,"20")!== false){$comision=450;}
            if(strpos($plan,"26")!== false){$comision=536;}
            if(strpos($plan,"40")!== false){$comision=751;}
        }
        if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
        {
            if(strpos($plan,"3")!== false){$comision=159;}
            if(strpos($plan,"5")!== false){$comision=300;}
            if(strpos($plan,"9")!== false){$comision=413;}
            if(strpos($plan,"11")!== false){$comision=417;}
            if(strpos($plan,"14")!== false){$comision=498;}
            if(strpos($plan,"17")!== false){$comision=572;}
            if(strpos($plan,"20")!== false){$comision=653;}
            if(strpos($plan,"26")!== false){$comision=777;}
            if(strpos($plan,"40")!== false){$comision=1090;}
        }
        if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
        {
            if(strpos($plan,"3")!== false){$comision=80;}
            if(strpos($plan,"5")!== false){$comision=150;}
            if(strpos($plan,"9")!== false){$comision=207;}
            if(strpos($plan,"11")!== false){$comision=209;}
            if(strpos($plan,"14")!== false){$comision=249;}
            if(strpos($plan,"17")!== false){$comision=286;}
            if(strpos($plan,"20")!== false){$comision=327;}
            if(strpos($plan,"26")!== false){$comision=389;}
            if(strpos($plan,"40")!== false){$comision=545;}
        }
        
        return($comision);
    }

    public function performanceElementEjecutivoArmalo($plan)
    {
        if(strpos($plan,"3")!== false){return(100);}
        if(strpos($plan,"5")!== false){return(150);}
        if(strpos($plan,"9")!== false){return(150);}
        if(strpos($plan,"11")!== false){return(150);}
        if(strpos($plan,"14")!== false){return(150);}
        if(strpos($plan,"17")!== false){return(150);}
        if(strpos($plan,"20")!== false){return(150);}
        if(strpos($plan,"26")!== false){return(150);}
        if(strpos($plan,"40")!== false){return(250);}
        return(0);
    }
    public function comisionArmalo_gerente($plan,$tipo_venta)
    {
        $comision=0;

        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
        {
            if(strpos($plan,"3")!== false){$comision=36;}
            if(strpos($plan,"5")!== false){$comision=109;}
            if(strpos($plan,"9")!== false){$comision=177;}
            if(strpos($plan,"11")!== false){$comision=133;}
            if(strpos($plan,"14")!== false){$comision=186;}
            if(strpos($plan,"17")!== false){$comision=183;}
            if(strpos($plan,"20")!== false){$comision=235;}
            if(strpos($plan,"26")!== false){$comision=244;}
            if(strpos($plan,"40")!== false){$comision=340;}
        }
        if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
        {
            if(strpos($plan,"3")!== false){$comision=43;}
            if(strpos($plan,"5")!== false){$comision=82;}
            if(strpos($plan,"9")!== false){$comision=113;}
            if(strpos($plan,"11")!== false){$comision=114;}
            if(strpos($plan,"14")!== false){$comision=137;}
            if(strpos($plan,"17")!== false){$comision=157;}
            if(strpos($plan,"20")!== false){$comision=179;}
            if(strpos($plan,"26")!== false){$comision=213;}
            if(strpos($plan,"40")!== false){$comision=299;}
        }
        if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
        {
            if(strpos($plan,"3")!== false){$comision=44;}
            if(strpos($plan,"5")!== false){$comision=82;}
            if(strpos($plan,"9")!== false){$comision=113;}
            if(strpos($plan,"11")!== false){$comision=114;}
            if(strpos($plan,"14")!== false){$comision=137;}
            if(strpos($plan,"17")!== false){$comision=157;}
            if(strpos($plan,"20")!== false){$comision=179;}
            if(strpos($plan,"26")!== false){$comision=213;}
            if(strpos($plan,"40")!== false){$comision=299;}
        }
        if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
        {
            if(strpos($plan,"3")!== false){$comision=22;}
            if(strpos($plan,"5")!== false){$comision=41;}
            if(strpos($plan,"9")!== false){$comision=57;}
            if(strpos($plan,"11")!== false){$comision=57;}
            if(strpos($plan,"14")!== false){$comision=69;}
            if(strpos($plan,"17")!== false){$comision=79;}
            if(strpos($plan,"20")!== false){$comision=90;}
            if(strpos($plan,"26")!== false){$comision=107;}
            if(strpos($plan,"40")!== false){$comision=150;}
        }
        
        return($comision);
    }

    public function performanceElementArmalo_gerente($plan)
    {
        if(strpos($plan,"3")!== false){return(26);}
        if(strpos($plan,"5")!== false){return(26);}
        if(strpos($plan,"9")!== false){return(26);}
        if(strpos($plan,"11")!== false){return(26);}
        if(strpos($plan,"14")!== false){return(26);}
        if(strpos($plan,"17")!== false){return(26);}
        if(strpos($plan,"20")!== false){return(26);}
        if(strpos($plan,"26")!== false){return(26);}
        if(strpos($plan,"40")!== false){return(26);}
        return(0);
    }
    public function comisionArmalo_regional($plan,$tipo_venta)
    {
        $comision=0;

        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
        {
            if(strpos($plan,"3")!== false){$comision=12;}
            if(strpos($plan,"5")!== false){$comision=23;}
            if(strpos($plan,"9")!== false){$comision=28;}
            if(strpos($plan,"11")!== false){$comision=29;}
            if(strpos($plan,"14")!== false){$comision=34;}
            if(strpos($plan,"17")!== false){$comision=39;}
            if(strpos($plan,"20")!== false){$comision=45;}
            if(strpos($plan,"26")!== false){$comision=54;}
            if(strpos($plan,"40")!== false){$comision=75;}
        }
        if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
        {
            if(strpos($plan,"3")!== false){$comision=16;}
            if(strpos($plan,"5")!== false){$comision=32;}
            if(strpos($plan,"9")!== false){$comision=43;}
            if(strpos($plan,"11")!== false){$comision=44;}
            if(strpos($plan,"14")!== false){$comision=53;}
            if(strpos($plan,"17")!== false){$comision=61;}
            if(strpos($plan,"20")!== false){$comision=69;}
            if(strpos($plan,"26")!== false){$comision=82;}
            if(strpos($plan,"40")!== false){$comision=116;}
        }
        if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
        {
            if(strpos($plan,"3")!== false){$comision=9;}
            if(strpos($plan,"5")!== false){$comision=16;}
            if(strpos($plan,"9")!== false){$comision=22;}
            if(strpos($plan,"11")!== false){$comision=22;}
            if(strpos($plan,"14")!== false){$comision=26;}
            if(strpos($plan,"17")!== false){$comision=30;}
            if(strpos($plan,"20")!== false){$comision=35;}
            if(strpos($plan,"26")!== false){$comision=41;}
            if(strpos($plan,"40")!== false){$comision=58;}
        }
        if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
        {
            if(strpos($plan,"3")!== false){$comision=5;}
            if(strpos($plan,"5")!== false){$comision=8;}
            if(strpos($plan,"9")!== false){$comision=11;}
            if(strpos($plan,"11")!== false){$comision=11;}
            if(strpos($plan,"14")!== false){$comision=13;}
            if(strpos($plan,"17")!== false){$comision=15;}
            if(strpos($plan,"20")!== false){$comision=18;}
            if(strpos($plan,"26")!== false){$comision=21;}
            if(strpos($plan,"40")!== false){$comision=29;}
        }
        
        return($comision);
    }

    public function performanceElementArmalo_regional($plan)
    {
        return(0);
    }
    public function comisionArmalo_director($plan,$tipo_venta)
    {
        $comision=0;

        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion"))
        {
            if(strpos($plan,"3")!== false){$comision=6;}
            if(strpos($plan,"5")!== false){$comision=11;}
            if(strpos($plan,"9")!== false){$comision=15;}
            if(strpos($plan,"11")!== false){$comision=15;}
            if(strpos($plan,"14")!== false){$comision=18;}
            if(strpos($plan,"17")!== false){$comision=21;}
            if(strpos($plan,"20")!== false){$comision=24;}
            if(strpos($plan,"26")!== false){$comision=29;}
            if(strpos($plan,"40")!== false){$comision=40;}
        }
        if(($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio"))
        {
            if(strpos($plan,"3")!== false){$comision=9;}
            if(strpos($plan,"5")!== false){$comision=16;}
            if(strpos($plan,"9")!== false){$comision=22;}
            if(strpos($plan,"11")!== false){$comision=22;}
            if(strpos($plan,"14")!== false){$comision=26;}
            if(strpos($plan,"17")!== false){$comision=30;}
            if(strpos($plan,"20")!== false){$comision=34;}
            if(strpos($plan,"26")!== false){$comision=41;}
            if(strpos($plan,"40")!== false){$comision=58;}
        }
        if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion"))
        {
            if(strpos($plan,"3")!== false){$comision=4;}
            if(strpos($plan,"5")!== false){$comision=8;}
            if(strpos($plan,"9")!== false){$comision=11;}
            if(strpos($plan,"11")!== false){$comision=11;}
            if(strpos($plan,"14")!== false){$comision=13;}
            if(strpos($plan,"17")!== false){$comision=15;}
            if(strpos($plan,"20")!== false){$comision=17;}
            if(strpos($plan,"26")!== false){$comision=21;}
            if(strpos($plan,"40")!== false){$comision=29;}
        }
        if(($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio"))
        {
            if(strpos($plan,"3")!== false){$comision=2;}
            if(strpos($plan,"5")!== false){$comision=4;}
            if(strpos($plan,"9")!== false){$comision=5;}
            if(strpos($plan,"11")!== false){$comision=6;}
            if(strpos($plan,"14")!== false){$comision=7;}
            if(strpos($plan,"17")!== false){$comision=8;}
            if(strpos($plan,"20")!== false){$comision=9;}
            if(strpos($plan,"26")!== false){$comision=11;}
            if(strpos($plan,"40")!== false){$comision=15;}
        }
        
        return($comision);
    }

    public function performanceElementArmalo_director($plan)
    {
        return(0);
    }
    public function comisionSeguroArmalo($plan,$tipo_venta)
    {
        $comision=0;
        if(strpos($plan,"3")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=61;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if(strpos($plan,"5")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=66;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if(strpos($plan,"9")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=70;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if(strpos($plan,"11")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=75;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if(strpos($plan,"14")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=80;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if(strpos($plan,"17")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=84;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if(strpos($plan,"20")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=90;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if(strpos($plan,"26")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=90;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        if(strpos($plan,"40")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=99;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=50;}
        }
        
        return($comision);
    }
    public function comisionSeguroArmalo_gerente($plan,$tipo_venta)
    {
        $comision=0;
        if(strpos($plan,"3")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=18;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if(strpos($plan,"5")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=19;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if(strpos($plan,"9")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=19;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if(strpos($plan,"11")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=19;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if(strpos($plan,"14")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=20;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if(strpos($plan,"17")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=21;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if(strpos($plan,"20")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=21;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if(strpos($plan,"26")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=21;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        if(strpos($plan,"40")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=22;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=17;}
        }
        
        return($comision);
    }
    public function comisionSeguroArmalo_regional($plan,$tipo_venta)
    {
        $comision=0;
        if(strpos($plan,"3")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=5;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if(strpos($plan,"5")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=5;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if(strpos($plan,"9")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=6;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if(strpos($plan,"11")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=6;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if(strpos($plan,"14")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=6;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if(strpos($plan,"17")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=6;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if(strpos($plan,"20")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=6;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if(strpos($plan,"26")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=6;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        if(strpos($plan,"40")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=6;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=5;}
        }
        
        return($comision);
    }
    public function comisionSeguroArmalo_director($plan,$tipo_venta)
    {
        $comision=0;
        if(strpos($plan,"3")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if(strpos($plan,"5")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if(strpos($plan,"9")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if(strpos($plan,"11")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if(strpos($plan,"14")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if(strpos($plan,"17")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if(strpos($plan,"20")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if(strpos($plan,"26")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=3;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        if(strpos($plan,"40")!== false)
        {
            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion")){$comision=4;}
            if(($tipo_venta=="Renovación" || $tipo_venta=="Renovacion")){$comision=3;}
        }
        
        return($comision);
    }
}
