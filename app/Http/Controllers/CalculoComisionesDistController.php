<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CalculoDistribuidores;
use App\Models\TransaccionDistribuidor;
use App\Models\Distribuidor;
use Illuminate\Support\Facades\Auth;

class CalculoComisionesDistController extends Controller
{
    public function nuevo_calculo(Request $request)
    {
        $request->validate([
            'tipo' => 'required',
            'f_pago' => 'required|date_format:Y-m-d',
            'f_inicio' => 'required|date_format:Y-m-d',
            'f_fin' => 'required|date_format:Y-m-d',
            'f_limite' => 'required',
            'descripcion' => 'required|max:255',
        ]);

        $registro=new CalculoDistribuidores;
        $registro->tipo=$request->tipo;
        $registro->fecha_inicio=$request->f_inicio;
        $registro->fecha_fin=$request->f_fin;
        $registro->pagado_en=$request->f_pago;
        $registro->cr0=false;
        $registro->eq0=false;
        $registro->terminado=false;
        $registro->user_id=Auth::user()->id;
        $registro->descripcion=$request->descripcion;
        $registro->fecha_limite=$request->f_limite;
        $registro->save();

        return(view('mensaje',[ 'estatus'=>'OK',
                                'mensaje'=>'El registro del calculo '.($request->tipo=='1'?'semanal':'mensual').' ('.$request->descripcion.') se realizo de manera exitosa!'
                              ]));
    }
    public function calculo_terminar_dist(Request $request)
    {
        CalculoDistribuidores::where('id',$request->id_calculo)
                ->update(['terminado'=>true]);
    }
    public function calculo_consulta_dist(Request $request)
    {
        $calculoRow=CalculoDistribuidores::where('id',$request->id_calculo)
        ->get();
        return($calculoRow);
    }
    public function calculo_comisiones_dist(Request $request)
    {
        $id_calculo=$request->id;
        $respuesta=array(
            'transacciones_calculadas'=>0,
        );

        $respuesta['transacciones_calculadas']=$this->comisiones_distribuidor($id_calculo);
        return($respuesta);
    }
    public function comisiones_distribuidor($id_calculo)
    {
        $calculo=CalculoDistribuidores::find($id_calculo);
        $tipo_calculo=$calculo->tipo;
        $transacciones=TransaccionDistribuidor::where('calculo_id',$id_calculo)
                    ->where('credito',1)
                    ->get();
        $registros_act_ren_eq_nuevo=$transacciones->whereIn('tipo_venta',['Activación','Renovación','Activacion','Renovacion']); //PLANES CON EQUIPO NUEVO
        
        $distribuidores=Distribuidor::all();
        $transacciones_pagadas=0;
        $tipos_venta=[];
        foreach ($transacciones as $credito) {
            $comision_default=$credito->razon_cr0;
            $renta_transaccion=$credito->importe;
            $tipo_venta=$credito->tipo_venta;
            $plan=$credito->servicio;
            $pedido=$credito->pedido;
            $eq_sin_costo=$credito->eq_sin_costo;
            $bracket=0;
            $comision=0;
            $esquema_mas=0;
            $esquema_emp=0;
            $reg_distribuidor=[];
            if($distribuidores->contains('numero_distribuidor',$credito->numero_distribuidor)) //CON ESTE BLOQUE SE OBTIENE EL VALOR DEL ESQUEMA
            {
                $reg_distribuidor=$distribuidores->where('numero_distribuidor',$credito->numero_distribuidor);
                foreach ($reg_distribuidor as $registro) 
                {
                    if($tipo_calculo=="1") //SEMANAL
                    {
                        $esquema_mas=$registro->esquema_s_mas;
                        $esquema_emp=$registro->esquema_s_emp;
                    }
                    else
                    {
                        $esquema_mas=$registro->esquema_m_mas;
                        $esquema_emp=$registro->esquema_m_emp;
                    }
                    
                }
            }
            else{
                    $esquema_mas=1;
                    $esquema_emp=1;
            }

            if($tipo_venta=="Activación" || $tipo_venta=="Activacion" ||
                $tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio" ||
                $tipo_venta=="Renovación" || $tipo_venta=="Renovacion" ||
                $tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio" ||
                $tipo_venta=="Protección de equipo" || $tipo_venta=="Proteccion de equipo" ||
                $tipo_venta=="Protección de Equipo" || $tipo_venta=="Proteccion de Equipo" ||
                $tipo_venta=="Renovación Empresarial" || $tipo_venta=="Renovacion Empresarial")
                {
                    
                    try{
                        $conteo_actual=$tipos_venta[$tipo_venta];
                        $tipos_venta[$tipo_venta]=$conteo_actual+1;
                    }
                    catch(\Exception $e)
                    {
                        $tipos_venta[$tipo_venta]=1;
                    }

                    if($tipo_venta=="Renovación Empresarial" || $tipo_venta=="Renovacion Empresarial") {$tipo_venta="Renovación";}
                    if(
                    //    strpos($plan,"COMPARTELO")=== false 
                    strpos($plan,"DAMOS")=== false 
                    && strpos($plan,"YA")=== false 
                    && strpos(strtoupper($plan),"PROTECCI")=== false
                    && strpos($plan,"SIMPLE")=== false
                    && strpos($plan,"ARMALO")===false
                    && strpos(strtoupper($plan),"NEG")===false
                    ) 
                    //SE TRATA DE UN PLAN CONSIGUELO U OTRO NO NOMBRADO
                    {
                        $bracket=$this->obtenBracket($renta_transaccion);
                        $comision=$this->comisionConsiguelo($bracket,$tipo_venta,$esquema_mas);
                            if(($tipo_venta=="Activación" || $tipo_venta=="Activacion") && $eq_sin_costo)
                            {
                                $comision=$comision-$this->performanceElement($bracket);
                                
                            }
                    }
                    if(strpos($plan,"ARMALO")!== false 
                        || strpos($plan,"AZUL")!== false
                        || strpos($plan,"PLATA")!== false
                        || strpos($plan,"ORO")!== false
                        || strpos($plan,"BLACK")!== false
                        || strpos($plan,"DIAMANTE")!== false
                        || strpos($plan,"TITANIO")!== false
                    )
                    {
                        $comision=$this->comisionArmalo($plan,$tipo_venta,$esquema_mas);
                        //$comision=$this->comisionEspecialArmalo($reg_distribuidor,$comision,$plan,$tipo_venta);
                        if(($tipo_venta=="Activación" || $tipo_venta=="Activacion") && $eq_sin_costo)
                            {
                                $comision=$comision-$this->performanceElementArmalo($plan,$tipo_venta);
                            }
                
                    }
                    if(strpos($plan,"DAMOS")!== false || strpos($plan,"YA")!== false) // PLANES DAMOS MAS o YA
                    {   
                        $comision=$this->comisionDamosYa($renta_transaccion,$credito->plazo);

                    }
                    if(strpos($plan,"SIMPLE")!== false)
                    {
                        $comision=$this->comisionSimple($credito->numero_distribuidor,$esquema_mas,$renta_transaccion,$credito->plazo);
                    }
                    if(strpos(strtoupper($plan),"PROTECCI")!== false) // INSTANCIA DE SEGURO
                    {  
                        if($renta_transaccion<99)
                        {
                            $comision=89;
                        }
                        if($renta_transaccion>=99 && $renta_transaccion<139)
                        {
                            $comision=128;
                        }
                        if($renta_transaccion>=139 && $renta_transaccion<179)
                        {
                            $comision=180;
                        }
                        if($renta_transaccion>=179 && $renta_transaccion<=199)
                        {
                            $comision=231;
                        }
                        if($renta_transaccion>=199 && $renta_transaccion<239)
                        {
                            $comision=257;
                        }
                        if($renta_transaccion>=239)
                        {
                            $comision=309;
                        }
                        
                    }
                    if(strpos(strtoupper($plan),"NEG")!== false) // EMPRESARIAL
                    {   
                        $comision=$this->comisionEmpresarial($tipo_venta,$credito->plazo,$renta_transaccion,$esquema_emp);
                    }
                    if(strpos($plan,"BYOD")!== false)
                    {
                        $comision=200;
                    }
                    if(strpos($plan,"INTERNET EN CASA")!== false)
                    {
                        $comision=$credito->importe/1.16/1.03;
                    }

                    if(strpos($plan,"SEMANA")!== false) // ATT POR SEMANA
                    {
                        $comision=$this->comisionPorSemana($credito->numero_distribuidor);
                    }

                    $factor_mayo_2022=1;
                    if($tipo_venta=="Renovación" || $tipo_venta=="Renovacion" || $tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
                        {        
                            if($credito->numero_distribuidor=='100011' || $credito->numero_distribuidor=='100013' || $credito->numero_distribuidor=='100028' 
                            )
                            {$factor_mayo_2022=0.85;}
                            //$comision=$factor_mayo_2022*$comision;

                        }

                }
            if($tipo_venta=="ADD ON") // INSTANCIA DE ADD ON
                {
                    $comision=$this->comisionAddOn($plan,$renta_transaccion);
                }
            
            //echo "-- Comision ".$comision;
            $transaccion_calculada=TransaccionDistribuidor::find($credito->id);
            if($tipo_calculo=="1") //SEMANAL
            {
                $transaccion_calculada->comision=$comision*0.5;

            }
            else{
                $transaccion_calculada->comision=$comision;
            }

            if($comision_default>1)
            {
                $transaccion_calculada->comision=$comision_default;
            }
            if($comision_default<1 && $comision_default>0)
            {
                $transaccion_calculada->comision=$comision*$comision_default;
            }

            $transaccion_calculada->save();
            $transacciones_pagadas=$transacciones_pagadas+1;
        }
        //return($tipos_venta);
        return($transacciones_pagadas);
   }
   public function obtenBracket($renta)
    {
        if($renta>=0 && $renta<265){return (1);}
        if($renta>=265 && $renta<325){return (2);}
        if($renta>=325 && $renta<435){return (3);}
        if($renta>=435 && $renta<535){return (4);}
        if($renta>=535 && $renta<635){return (5);}
        if($renta>=635 && $renta<745){return (6);}
        if($renta>=745 && $renta<845){return (7);}
        if($renta>=845 && $renta<1045){return (8);}
        if($renta>=1045 && $renta<1565){return (9);}
        if($renta>=1565 && $renta<2265){return (10);}
        if($renta>=2265 && $renta<2885){return (11);}
        if($renta>=2885 && $renta<4185){return (12);}
        if($renta>=4185 && $renta<5505){return (13);}
        if($renta>=5505){return (14);}

    }
   function comisionConsiguelo($bracket,$tipo_venta,$esquema)
   {
       $comision=0;
       if($esquema=="1"){$comision=$this->comisionConsiguelo_E1($bracket,$tipo_venta);}
       if($esquema=="2"){$comision=$this->comisionConsiguelo_E2($bracket,$tipo_venta);}
       if($esquema=="3"){$comision=$this->comisionConsiguelo_E3($bracket,$tipo_venta);}
       if($esquema=="4"){$comision=$this->comisionConsiguelo_E4($bracket,$tipo_venta);}
       if($esquema=="5"){$comision=$this->comisionConsiguelo_E5($bracket,$tipo_venta);}
       if($esquema=="6"){$comision=$this->comisionConsiguelo_E6($bracket,$tipo_venta);}
       if($esquema=="7"){$comision=$this->comisionConsiguelo_E7($bracket,$tipo_venta);}
       if($esquema=="8"){$comision=$this->comisionConsiguelo_E8($bracket,$tipo_venta);}
       if($esquema=="9"){$comision=$this->comisionConsiguelo_E9($bracket,$tipo_venta);}
       if($esquema=="10"){$comision=$this->comisionConsiguelo_E7($bracket,$tipo_venta);}
       if($esquema=="11"){$comision=$this->comisionConsiguelo_E8($bracket,$tipo_venta);}
       //CASTELAN
       if($esquema=="12"){$comision=$this->comisionConsiguelo_E9($bracket,$tipo_venta);}
       return($comision);
   }
   function performanceElement($bracket)
   {
       return(400);
   }
   function comisionArmalo($plan,$tipo_venta,$esquema)
   {
       $comision=0;
        if($esquema=="1"){$comision=$this->comisionArmalo_E1($plan,$tipo_venta);}
        if($esquema=="2"){$comision=$this->comisionArmalo_E2($plan,$tipo_venta);}
        if($esquema=="3"){$comision=$this->comisionArmalo_E3($plan,$tipo_venta);}
        if($esquema=="4"){$comision=$this->comisionArmalo_E4($plan,$tipo_venta);}
        if($esquema=="5"){$comision=$this->comisionArmalo_E5($plan,$tipo_venta);}
        if($esquema=="6"){$comision=$this->comisionArmalo_E6($plan,$tipo_venta);}
        if($esquema=="7"){$comision=$this->comisionArmalo_E7($plan,$tipo_venta);}
        if($esquema=="8"){$comision=$this->comisionArmalo_E8($plan,$tipo_venta);}
        if($esquema=="9"){$comision=$this->comisionArmalo_E9($plan,$tipo_venta);}
        if($esquema=="10"){$comision=$this->comisionArmalo_E10($plan,$tipo_venta);}
        if($esquema=="11"){$comision=$this->comisionArmalo_E11($plan,$tipo_venta);}
        if($esquema=="12"){$comision=$this->comisionArmalo_E12($plan,$tipo_venta);}
        if($esquema=="13"){$comision=$this->comisionArmalo_E13($plan,$tipo_venta);}
       return($comision);
   }
   function performanceElementArmalo($plan,$tipo_venta)
   {
       return(400);
   }
   function comisionDamosYa($renta,$plazo)
   {
       $comision=0;
       $factor=0;
       if($plazo=="12"){$factor=6;}
       if($plazo=="18"){$factor=8;}
       if($plazo=="24"){$factor=10;}
       $comision=($renta/1.16/1.03)*$factor*0.21; //SE CAMBIA A PETICION DE JON POR LA PRECICION DE EXCEL de 0.205 a 0.21
       return($comision);
   }
   function comisionSeguro()
   {
       return(75);
   }
   function comisionAddOn($renta,$plan)
   {
       return(0);
   }
   function comisionEmpresarial($tipo_venta,$plazo,$renta,$esquema)
   {
       $comision=0;
       if($esquema=="1"){$comision=$this->comisionEmpresarial_E1($tipo_venta,$plazo,$renta);}
       if($esquema=="2"){$comision=$this->comisionEmpresarial_E2($tipo_venta,$plazo,$renta);}
       if($esquema=="3"){$comision=$this->comisionEmpresarial_E3($tipo_venta,$plazo,$renta);}
       if($esquema=="4"){$comision=$this->comisionEmpresarial_E4($tipo_venta,$plazo,$renta);}
       if($esquema=="5"){$comision=$this->comisionEmpresarial_E5($tipo_venta,$plazo,$renta);}
       if($esquema=="6"){$comision=$this->comisionEmpresarial_E6($tipo_venta,$plazo,$renta);}
       if($esquema=="7"){$comision=$this->comisionEmpresarial_E7($tipo_venta,$plazo,$renta);}
       if($esquema=="8"){$comision=$this->comisionEmpresarial_E8($tipo_venta,$plazo,$renta);}
       if($esquema=="9"){$comision=$this->comisionEmpresarial_E9($tipo_venta,$plazo,$renta);}
       if($esquema=="10"){$comision=$this->comisionEmpresarial_E10($tipo_venta,$plazo,$renta);}

       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación" || $tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
                //$comision=$comision*0.7845;
       }
       return($comision);
   }
   public function comisionConsiguelo_E1($bracket,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if($bracket=='1'){$comision=780;}
        if($bracket=='2'){$comision=875;}
        if($bracket=='3'){$comision=1415;}
        if($bracket=='4'){$comision=1712;}
        if($bracket=='5'){$comision=1925;}
        if($bracket=='6'){$comision=2250;}
        if($bracket=='7'){$comision=2575;}
        if($bracket=='8'){$comision=2900;}
        if($bracket=='9'){$comision=3550;}
        if($bracket=='10'){$comision=5175;}
        if($bracket=='11'){$comision=4810;}
        if($bracket=='12'){$comision=6040;}
        if($bracket=='13'){$comision=8705;}
        if($bracket=='14'){$comision=11370;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if($bracket=='1'){$comision=230;}
        if($bracket=='2'){$comision=272;}
        if($bracket=='3'){$comision=445;}
        if($bracket=='4'){$comision=600;}
        if($bracket=='5'){$comision=700;}
        if($bracket=='6'){$comision=840;}
        if($bracket=='7'){$comision=980;}
        if($bracket=='8'){$comision=1120;}
        if($bracket=='9'){$comision=1400;}
        if($bracket=='10'){$comision=2100;}
        if($bracket=='11'){$comision=3080;}
        if($bracket=='12'){$comision=3920;}
        if($bracket=='13'){$comision=5740;}
        if($bracket=='14'){$comision=7560;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if($bracket=='1'){$comision=574;}
        if($bracket=='2'){$comision=680;}
        if($bracket=='3'){$comision=1182;}
        if($bracket=='4'){$comision=1499;}
        if($bracket=='5'){$comision=1750;}
        if($bracket=='6'){$comision=2100;}
        if($bracket=='7'){$comision=2450;}
        if($bracket=='8'){$comision=2800;}
        if($bracket=='9'){$comision=3500;}
        if($bracket=='10'){$comision=5250;}
        if($bracket=='11'){$comision=4840;}
        if($bracket=='12'){$comision=6160;}
        if($bracket=='13'){$comision=9020;}
        if($bracket=='14'){$comision=11880;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if($bracket=='1'){$comision=287;}
        if($bracket=='2'){$comision=340;}
        if($bracket=='3'){$comision=591;}
        if($bracket=='4'){$comision=750;}
        if($bracket=='5'){$comision=875;}
        if($bracket=='6'){$comision=1050;}
        if($bracket=='7'){$comision=1225;}
        if($bracket=='8'){$comision=1400;}
        if($bracket=='9'){$comision=1750;}
        if($bracket=='10'){$comision=2625;}
        if($bracket=='11'){$comision=2420;}
        if($bracket=='12'){$comision=3080;}
        if($bracket=='13'){$comision=4510;}
        if($bracket=='14'){$comision=5940;}
       }
       return($comision);
   }
   public function comisionConsiguelo_E2($bracket,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if($bracket=='1'){$comision=830;}
        if($bracket=='2'){$comision=935;}
        if($bracket=='3'){$comision=1490;}
        if($bracket=='4'){$comision=1812;}
        if($bracket=='5'){$comision=2050;}
        if($bracket=='6'){$comision=2400;}
        if($bracket=='7'){$comision=2750;}
        if($bracket=='8'){$comision=3100;}
        if($bracket=='9'){$comision=3800;}
        if($bracket=='10'){$comision=5550;}
        if($bracket=='11'){$comision=5360;}
        if($bracket=='12'){$comision=6740;}
        if($bracket=='13'){$comision=9730;}
        if($bracket=='14'){$comision=12720;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
            if($bracket=='1'){$comision=280;}
            if($bracket=='2'){$comision=332;}
            if($bracket=='3'){$comision=520;}
            if($bracket=='4'){$comision=700;}
            if($bracket=='5'){$comision=825;}
            if($bracket=='6'){$comision=990;}
            if($bracket=='7'){$comision=1155;}
            if($bracket=='8'){$comision=1320;}
            if($bracket=='9'){$comision=1650;}
            if($bracket=='10'){$comision=2475;}
            if($bracket=='11'){$comision=3630;}
            if($bracket=='12'){$comision=4620;}
            if($bracket=='13'){$comision=6765;}
            if($bracket=='14'){$comision=8910;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if($bracket=='1'){$comision=574;}
        if($bracket=='2'){$comision=680;}
        if($bracket=='3'){$comision=1182;}
        if($bracket=='4'){$comision=1499;}
        if($bracket=='5'){$comision=1750;}
        if($bracket=='6'){$comision=2100;}
        if($bracket=='7'){$comision=2450;}
        if($bracket=='8'){$comision=2800;}
        if($bracket=='9'){$comision=3500;}
        if($bracket=='10'){$comision=5250;}
        if($bracket=='11'){$comision=4840;}
        if($bracket=='12'){$comision=6160;}
        if($bracket=='13'){$comision=9020;}
        if($bracket=='14'){$comision=11880;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if($bracket=='1'){$comision=287;}
        if($bracket=='2'){$comision=340;}
        if($bracket=='3'){$comision=591;}
        if($bracket=='4'){$comision=750;}
        if($bracket=='5'){$comision=875;}
        if($bracket=='6'){$comision=1050;}
        if($bracket=='7'){$comision=1225;}
        if($bracket=='8'){$comision=1400;}
        if($bracket=='9'){$comision=1750;}
        if($bracket=='10'){$comision=2625;}
        if($bracket=='11'){$comision=2420;}
        if($bracket=='12'){$comision=3080;}
        if($bracket=='13'){$comision=4510;}
        if($bracket=='14'){$comision=5940;}
       }
       return($comision);
   }
   public function comisionConsiguelo_E3($bracket,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if($bracket=='1'){$comision=775;}
        if($bracket=='2'){$comision=868;}
        if($bracket=='3'){$comision=1403;}
        if($bracket=='4'){$comision=1685;}
        if($bracket=='5'){$comision=1890;}
        if($bracket=='6'){$comision=2205;}
        if($bracket=='7'){$comision=2520;}
        if($bracket=='8'){$comision=2835;}
        if($bracket=='9'){$comision=3465;}
        if($bracket=='10'){$comision=5040;}
        if($bracket=='11'){$comision=4473;}
        if($bracket=='12'){$comision=5607;}
        if($bracket=='13'){$comision=8064;}
        if($bracket=='14'){$comision=10521;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if($bracket=='1'){$comision=230;}
        if($bracket=='2'){$comision=272;}
        if($bracket=='3'){$comision=445;}
        if($bracket=='4'){$comision=600;}
        if($bracket=='5'){$comision=700;}
        if($bracket=='6'){$comision=840;}
        if($bracket=='7'){$comision=980;}
        if($bracket=='8'){$comision=1120;}
        if($bracket=='9'){$comision=1400;}
        if($bracket=='10'){$comision=2100;}
        if($bracket=='11'){$comision=3080;}
        if($bracket=='12'){$comision=3920;}
        if($bracket=='13'){$comision=5740;}
        if($bracket=='14'){$comision=7560;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if($bracket=='1'){$comision=533;}
        if($bracket=='2'){$comision=632;}
        if($bracket=='3'){$comision=1102;}
        if($bracket=='4'){$comision=1391;}
        if($bracket=='5'){$comision=1625;}
        if($bracket=='6'){$comision=1950;}
        if($bracket=='7'){$comision=2275;}
        if($bracket=='8'){$comision=2600;}
        if($bracket=='9'){$comision=3250;}
        if($bracket=='10'){$comision=4875;}
        if($bracket=='11'){$comision=4290;}
        if($bracket=='12'){$comision=5460;}
        if($bracket=='13'){$comision=7995;}
        if($bracket=='14'){$comision=10530;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if($bracket=='1'){$comision=267;}
        if($bracket=='2'){$comision=316;}
        if($bracket=='3'){$comision=551;}
        if($bracket=='4'){$comision=696;}
        if($bracket=='5'){$comision=813;}
        if($bracket=='6'){$comision=975;}
        if($bracket=='7'){$comision=1138;}
        if($bracket=='8'){$comision=1300;}
        if($bracket=='9'){$comision=1625;}
        if($bracket=='10'){$comision=2438;}
        if($bracket=='11'){$comision=2145;}
        if($bracket=='12'){$comision=2730;}
        if($bracket=='13'){$comision=3998;}
        if($bracket=='14'){$comision=5265;}
       }
       return($comision);
   }
   public function comisionConsiguelo_E4($bracket,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if($bracket=='1'){$comision=800;}
        if($bracket=='2'){$comision=895;}
        if($bracket=='3'){$comision=1447;}
        if($bracket=='4'){$comision=1740;}
        if($bracket=='5'){$comision=1950;}
        if($bracket=='6'){$comision=2275;}
        if($bracket=='7'){$comision=2600;}
        if($bracket=='8'){$comision=2925;}
        if($bracket=='9'){$comision=3575;}
        if($bracket=='10'){$comision=5200;}
        if($bracket=='11'){$comision=4615;}
        if($bracket=='12'){$comision=5785;}
        if($bracket=='13'){$comision=8320;}
        if($bracket=='14'){$comision=10855;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if($bracket=='1'){$comision=230;}
        if($bracket=='2'){$comision=272;}
        if($bracket=='3'){$comision=445;}
        if($bracket=='4'){$comision=600;}
        if($bracket=='5'){$comision=700;}
        if($bracket=='6'){$comision=840;}
        if($bracket=='7'){$comision=980;}
        if($bracket=='8'){$comision=1120;}
        if($bracket=='9'){$comision=1400;}
        if($bracket=='10'){$comision=2100;}
        if($bracket=='11'){$comision=3080;}
        if($bracket=='12'){$comision=3920;}
        if($bracket=='13'){$comision=5740;}
        if($bracket=='14'){$comision=7560;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if($bracket=='1'){$comision=574;}
        if($bracket=='2'){$comision=632;}
        if($bracket=='3'){$comision=1136;}
        if($bracket=='4'){$comision=1285;}
        if($bracket=='5'){$comision=1500;}
        if($bracket=='6'){$comision=1800;}
        if($bracket=='7'){$comision=2100;}
        if($bracket=='8'){$comision=2400;}
        if($bracket=='9'){$comision=3000;}
        if($bracket=='10'){$comision=4125;}
        if($bracket=='11'){$comision=3630;}
        if($bracket=='12'){$comision=4620;}
        if($bracket=='13'){$comision=6765;}
        if($bracket=='14'){$comision=8910;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if($bracket=='1'){$comision=288;}
        if($bracket=='2'){$comision=316;}
        if($bracket=='3'){$comision=568;}
        if($bracket=='4'){$comision=643;}
        if($bracket=='5'){$comision=750;}
        if($bracket=='6'){$comision=900;}
        if($bracket=='7'){$comision=1050;}
        if($bracket=='8'){$comision=1200;}
        if($bracket=='9'){$comision=1500;}
        if($bracket=='10'){$comision=2063;}
        if($bracket=='11'){$comision=1815;}
        if($bracket=='12'){$comision=2310;}
        if($bracket=='13'){$comision=3383;}
        if($bracket=='14'){$comision=4455;}
       }
       return($comision);
   }
   public function comisionConsiguelo_E5($bracket,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if($bracket=='1'){$comision=861;}
        if($bracket=='2'){$comision=965;}
        if($bracket=='3'){$comision=1558;}
        if($bracket=='4'){$comision=1875;}
        if($bracket=='5'){$comision=2100;}
        if($bracket=='6'){$comision=2450;}
        if($bracket=='7'){$comision=2800;}
        if($bracket=='8'){$comision=3150;}
        if($bracket=='9'){$comision=3850;}
        if($bracket=='10'){$comision=5600;}
        if($bracket=='11'){$comision=4970;}
        if($bracket=='12'){$comision=6230;}
        if($bracket=='13'){$comision=8960;}
        if($bracket=='14'){$comision=11690;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if($bracket=='1'){$comision=533;}
        if($bracket=='2'){$comision=640;}
        if($bracket=='3'){$comision=1135;}
        if($bracket=='4'){$comision=1445;}
        if($bracket=='5'){$comision=1700;}
        if($bracket=='6'){$comision=2050;}
        if($bracket=='7'){$comision=2400;}
        if($bracket=='8'){$comision=2750;}
        if($bracket=='9'){$comision=3450;}
        if($bracket=='10'){$comision=5200;}
        if($bracket=='11'){$comision=4570;}
        if($bracket=='12'){$comision=5830;}
        if($bracket=='13'){$comision=8560;}
        if($bracket=='14'){$comision=11290;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if($bracket=='1'){$comision=656;}
        if($bracket=='2'){$comision=778;}
        if($bracket=='3'){$comision=1358;}
        if($bracket=='4'){$comision=1712;}
        if($bracket=='5'){$comision=2000;}
        if($bracket=='6'){$comision=2400;}
        if($bracket=='7'){$comision=2800;}
        if($bracket=='8'){$comision=3200;}
        if($bracket=='9'){$comision=4000;}
        if($bracket=='10'){$comision=6000;}
        if($bracket=='11'){$comision=5280;}
        if($bracket=='12'){$comision=6720;}
        if($bracket=='13'){$comision=9840;}
        if($bracket=='14'){$comision=12960;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if($bracket=='1'){$comision=328;}
        if($bracket=='2'){$comision=389;}
        if($bracket=='3'){$comision=679;}
        if($bracket=='4'){$comision=856;}
        if($bracket=='5'){$comision=1000;}
        if($bracket=='6'){$comision=1200;}
        if($bracket=='7'){$comision=1400;}
        if($bracket=='8'){$comision=1600;}
        if($bracket=='9'){$comision=2000;}
        if($bracket=='10'){$comision=3000;}
        if($bracket=='11'){$comision=2640;}
        if($bracket=='12'){$comision=3360;}
        if($bracket=='13'){$comision=4920;}
        if($bracket=='14'){$comision=6480;}
       }
       return($comision);
   }
   public function comisionConsiguelo_E6($bracket,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if($bracket=='1'){$comision=861;}
        if($bracket=='2'){$comision=965;}
        if($bracket=='3'){$comision=1558;}
        if($bracket=='4'){$comision=1873;}
        if($bracket=='5'){$comision=2100;}
        if($bracket=='6'){$comision=2450;}
        if($bracket=='7'){$comision=2800;}
        if($bracket=='8'){$comision=3150;}
        if($bracket=='9'){$comision=3850;}
        if($bracket=='10'){$comision=5600;}
        if($bracket=='11'){$comision=4970;}
        if($bracket=='12'){$comision=6230;}
        if($bracket=='13'){$comision=8960;}
        if($bracket=='14'){$comision=11690;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if($bracket=='1'){$comision=246;}
        if($bracket=='2'){$comision=292;}
        if($bracket=='3'){$comision=480;}
        if($bracket=='4'){$comision=642;}
        if($bracket=='5'){$comision=750;}
        if($bracket=='6'){$comision=900;}
        if($bracket=='7'){$comision=1050;}
        if($bracket=='8'){$comision=1200;}
        if($bracket=='9'){$comision=1500;}
        if($bracket=='10'){$comision=2250;}
        if($bracket=='11'){$comision=3300;}
        if($bracket=='12'){$comision=4200;}
        if($bracket=='13'){$comision=6150;}
        if($bracket=='14'){$comision=8100;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if($bracket=='1'){$comision=656;}
        if($bracket=='2'){$comision=778;}
        if($bracket=='3'){$comision=1358;}
        if($bracket=='4'){$comision=1712;}
        if($bracket=='5'){$comision=2000;}
        if($bracket=='6'){$comision=2400;}
        if($bracket=='7'){$comision=2800;}
        if($bracket=='8'){$comision=3200;}
        if($bracket=='9'){$comision=4000;}
        if($bracket=='10'){$comision=6000;}
        if($bracket=='11'){$comision=5280;}
        if($bracket=='12'){$comision=6720;}
        if($bracket=='13'){$comision=9840;}
        if($bracket=='14'){$comision=12960;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if($bracket=='1'){$comision=328;}
        if($bracket=='2'){$comision=389;}
        if($bracket=='3'){$comision=679;}
        if($bracket=='4'){$comision=856;}
        if($bracket=='5'){$comision=1000;}
        if($bracket=='6'){$comision=1200;}
        if($bracket=='7'){$comision=1400;}
        if($bracket=='8'){$comision=1600;}
        if($bracket=='9'){$comision=2000;}
        if($bracket=='10'){$comision=3000;}
        if($bracket=='11'){$comision=2640;}
        if($bracket=='12'){$comision=3360;}
        if($bracket=='13'){$comision=4920;}
        if($bracket=='14'){$comision=6480;}
       }
       return($comision);
   }
   public function comisionConsiguelo_E7($bracket,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if($bracket=='1'){$comision=738;}
        if($bracket=='2'){$comision=826;}
        if($bracket=='3'){$comision=1336;}
        if($bracket=='4'){$comision=1605;}
        if($bracket=='5'){$comision=1800;}
        if($bracket=='6'){$comision=2100;}
        if($bracket=='7'){$comision=2400;}
        if($bracket=='8'){$comision=2700;}
        if($bracket=='9'){$comision=3300;}
        if($bracket=='10'){$comision=4800;}
        if($bracket=='11'){$comision=4260;}
        if($bracket=='12'){$comision=5340;}
        if($bracket=='13'){$comision=7680;}
        if($bracket=='14'){$comision=10020;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if($bracket=='1'){$comision=230;}
        if($bracket=='2'){$comision=272;}
        if($bracket=='3'){$comision=445;}
        if($bracket=='4'){$comision=600;}
        if($bracket=='5'){$comision=700;}
        if($bracket=='6'){$comision=840;}
        if($bracket=='7'){$comision=980;}
        if($bracket=='8'){$comision=1120;}
        if($bracket=='9'){$comision=1400;}
        if($bracket=='10'){$comision=2100;}
        if($bracket=='11'){$comision=3080;}
        if($bracket=='12'){$comision=3920;}
        if($bracket=='13'){$comision=5740;}
        if($bracket=='14'){$comision=7560;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if($bracket=='1'){$comision=533;}
        if($bracket=='2'){$comision=632;}
        if($bracket=='3'){$comision=1102;}
        if($bracket=='4'){$comision=1392;}
        if($bracket=='5'){$comision=1625;}
        if($bracket=='6'){$comision=1950;}
        if($bracket=='7'){$comision=2275;}
        if($bracket=='8'){$comision=2600;}
        if($bracket=='9'){$comision=3250;}
        if($bracket=='10'){$comision=4875;}
        if($bracket=='11'){$comision=4290;}
        if($bracket=='12'){$comision=5460;}
        if($bracket=='13'){$comision=7995;}
        if($bracket=='14'){$comision=10530;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if($bracket=='1'){$comision=267;}
        if($bracket=='2'){$comision=316;}
        if($bracket=='3'){$comision=551;}
        if($bracket=='4'){$comision=696;}
        if($bracket=='5'){$comision=813;}
        if($bracket=='6'){$comision=975;}
        if($bracket=='7'){$comision=1138;}
        if($bracket=='8'){$comision=1300;}
        if($bracket=='9'){$comision=1625;}
        if($bracket=='10'){$comision=2438;}
        if($bracket=='11'){$comision=2145;}
        if($bracket=='12'){$comision=2730;}
        if($bracket=='13'){$comision=3998;}
        if($bracket=='14'){$comision=5265;}
       }
       return($comision);
   }
   public function comisionConsiguelo_E8($bracket,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
           if($bracket=="1"){$comision=677;}
           if($bracket=="2"){$comision=757;}
           if($bracket=="3"){$comision=1225;}
           if($bracket=="4"){$comision=1472;}
           if($bracket=="5"){$comision=1650;}
           if($bracket=="6"){$comision=1925;}
           if($bracket=="7"){$comision=2200;}
           if($bracket=="8"){$comision=2475;}
           if($bracket=="9"){$comision=3025;}
           if($bracket=="10"){$comision=4400;}
           if($bracket=="11"){$comision=3905;}
           if($bracket=="12"){$comision=4895;}
           if($bracket=="13"){$comision=7040;}
           if($bracket=="14"){$comision=9185;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
           if($bracket=="1"){$comision=197;}
           if($bracket=="2"){$comision=233;}
           if($bracket=="3"){$comision=382;}
           if($bracket=="4"){$comision=515;}
           if($bracket=="5"){$comision=600;}
           if($bracket=="6"){$comision=720;}
           if($bracket=="7"){$comision=840;}
           if($bracket=="8"){$comision=960;}
           if($bracket=="9"){$comision=1200;}
           if($bracket=="10"){$comision=1800;}
           if($bracket=="11"){$comision=2640;}
           if($bracket=="12"){$comision=3360;}
           if($bracket=="13"){$comision=4920;}
           if($bracket=="14"){$comision=6480;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
           if($bracket=="1"){$comision=492;}
           if($bracket=="2"){$comision=583;}
           if($bracket=="3"){$comision=1018;}
           if($bracket=="4"){$comision=1285;}
           if($bracket=="5"){$comision=1500;}
           if($bracket=="6"){$comision=1800;}
           if($bracket=="7"){$comision=2100;}
           if($bracket=="8"){$comision=2400;}
           if($bracket=="9"){$comision=3000;}
           if($bracket=="10"){$comision=4500;}
           if($bracket=="11"){$comision=3960;}
           if($bracket=="12"){$comision=5040;}
           if($bracket=="13"){$comision=7380;}
           if($bracket=="14"){$comision=9720;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
           if($bracket=="1"){$comision=246;}
           if($bracket=="2"){$comision=292;}
           if($bracket=="3"){$comision=509;}
           if($bracket=="4"){$comision=643;}
           if($bracket=="5"){$comision=750;}
           if($bracket=="6"){$comision=900;}
           if($bracket=="7"){$comision=1050;}
           if($bracket=="8"){$comision=1200;}
           if($bracket=="9"){$comision=1500;}
           if($bracket=="10"){$comision=2250;}
           if($bracket=="11"){$comision=1980;}
           if($bracket=="12"){$comision=2520;}
           if($bracket=="13"){$comision=3690;}
           if($bracket=="14"){$comision=4860;}
       }
       return($comision);
   }
   public function comisionConsiguelo_E9($bracket,$tipo_venta)
   {
        $comision=0;
        if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
        {
            if($bracket=='1'){$comision=780;}
            if($bracket=='2'){$comision=875;}
            if($bracket=='3'){$comision=1415;}
            if($bracket=='4'){$comision=1712;}
            if($bracket=='5'){$comision=1925;}
            if($bracket=='6'){$comision=2250;}
            if($bracket=='7'){$comision=2575;}
            if($bracket=='8'){$comision=2900;}
            if($bracket=='9'){$comision=3550;}
            if($bracket=='10'){$comision=5175;}
            if($bracket=='11'){$comision=4810;}
            if($bracket=='12'){$comision=6040;}
            if($bracket=='13'){$comision=8705;}
            if($bracket=='14'){$comision=11370;}
        }
        if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
        {
            if($bracket=='1'){$comision=230;}
            if($bracket=='2'){$comision=272;}
            if($bracket=='3'){$comision=445;}
            if($bracket=='4'){$comision=600;}
            if($bracket=='5'){$comision=700;}
            if($bracket=='6'){$comision=840;}
            if($bracket=='7'){$comision=980;}
            if($bracket=='8'){$comision=1120;}
            if($bracket=='9'){$comision=1400;}
            if($bracket=='10'){$comision=2100;}
            if($bracket=='11'){$comision=3080;}
            if($bracket=='12'){$comision=3920;}
            if($bracket=='13'){$comision=5740;}
            if($bracket=='14'){$comision=7560;}
        }
        if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
        {
            if($bracket=='1'){$comision=574;}
            if($bracket=='2'){$comision=680;}
            if($bracket=='3'){$comision=1182;}
            if($bracket=='4'){$comision=1499;}
            if($bracket=='5'){$comision=1750;}
            if($bracket=='6'){$comision=2100;}
            if($bracket=='7'){$comision=2450;}
            if($bracket=='8'){$comision=2800;}
            if($bracket=='9'){$comision=3500;}
            if($bracket=='10'){$comision=5250;}
            if($bracket=='11'){$comision=4840;}
            if($bracket=='12'){$comision=6160;}
            if($bracket=='13'){$comision=9020;}
            if($bracket=='14'){$comision=11880;}
        }
        if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
        {
            if($bracket=='1'){$comision=287;}
            if($bracket=='2'){$comision=340;}
            if($bracket=='3'){$comision=591;}
            if($bracket=='4'){$comision=750;}
            if($bracket=='5'){$comision=875;}
            if($bracket=='6'){$comision=1050;}
            if($bracket=='7'){$comision=1225;}
            if($bracket=='8'){$comision=1400;}
            if($bracket=='9'){$comision=1750;}
            if($bracket=='10'){$comision=2625;}
            if($bracket=='11'){$comision=2420;}
            if($bracket=='12'){$comision=3080;}
            if($bracket=='13'){$comision=4510;}
            if($bracket=='14'){$comision=5940;}
        }
        return($comision);
   }
   public function comisionArmalo_E1($plan,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if(strpos($plan,'2 ')!== false) {$comision=573;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=906;} //AZUL 1
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1631;} //AZUL 2
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1872;} //AZUL 3
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1872;} //AZUL 3
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2187;} //PLATA
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2187;} //PLATA
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2190;} //ORO
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2420;} //BLACK o TITANIO
        if(strpos($plan,'17')!== false) {$comision=2705;}
        if(strpos($plan,'20')!== false) {$comision=3050;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3387;} //DIAMANTE
        if(strpos($plan,'40')!== false) {$comision=4624;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if(strpos($plan,'2 ')!== false) {$comision=268;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=751;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1349;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1613;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1613;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1959;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1959;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=1962;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2125;}
        if(strpos($plan,'17')!== false) {$comision=2418;}
        if(strpos($plan,'20')!== false) {$comision=2760;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3100;}
        if(strpos($plan,'40')!== false) {$comision=4400;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if(strpos($plan,'2')!== false) {$comision=274;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=734;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1421;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1699;}
        if(strpos($plan,'9 GB')!== false) {$comision=1875;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1875;}
        if(strpos($plan,'11 GB')!== false) {$comision=2084;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2084;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2264;}
        if(strpos($plan,'17')!== false) {$comision=2581;}
        if(strpos($plan,'20')!== false) {$comision=2932;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3346;}
        if(strpos($plan,'40')!== false) {$comision=4693;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=337;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=671;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=874;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=898;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=1073;}
        if(strpos($plan,'17')!== false) {$comision=1232;}
        if(strpos($plan,'20')!== false) {$comision=1407;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1673;}
        if(strpos($plan,'40')!== false) {$comision=2347;}
        $comision=0;
       }
       return($comision);
   }
   public function comisionArmalo_E2($plan,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if(strpos($plan,'2 ')!== false) {$comision=597;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=965;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1716;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1973;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1973;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2310;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2310;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2312;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2580;}
        if(strpos($plan,'17')!== false) {$comision=2881;}
        if(strpos($plan,'20')!== false) {$comision=3270;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3626;}
        if(strpos($plan,'40')!== false) {$comision=4959;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if(strpos($plan,'2 ')!== false) {$comision=269;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=751;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1349;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1613;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1613;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1959;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1959;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=1962;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2120;}
        if(strpos($plan,'17')!== false) {$comision=2418;}
        if(strpos($plan,'20')!== false) {$comision=2760;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3100;}
        if(strpos($plan,'40')!== false) {$comision=4400;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if(strpos($plan,'2')!== false) {$comision=275;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=734;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1421;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1699;}
        if(strpos($plan,'9 GB')!== false) {$comision=1874;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1874;}
        if(strpos($plan,'11 GB')!== false) {$comision=2084;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2084;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2264;}
        if(strpos($plan,'17')!== false) {$comision=2581;}
        if(strpos($plan,'20')!== false) {$comision=2932;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3346;}
        if(strpos($plan,'40')!== false) {$comision=4693;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=337;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=671;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=874;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=898;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=1073;}
        if(strpos($plan,'17')!== false) {$comision=1232;}
        if(strpos($plan,'20')!== false) {$comision=1407;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1673;}
        if(strpos($plan,'40')!== false) {$comision=2347;}
        $comision=0;
       }
       return($comision);
   }
   public function comisionArmalo_E3($plan,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=840;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1538;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1898;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1929;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2245;}
        if(strpos($plan,'17')!== false) {$comision=2532;}
        if(strpos($plan,'20')!== false) {$comision=2847;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3310;}
        if(strpos($plan,'40')!== false) {$comision=4510;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=389;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=554;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=749;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=824;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=974;}
        if(strpos($plan,'17')!== false) {$comision=1124;}
        if(strpos($plan,'20')!== false) {$comision=1274;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1499;}
        if(strpos($plan,'40')!== false) {$comision=2099;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=625;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1251;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1622;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1667;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=1993;}
        if(strpos($plan,'17')!== false) {$comision=2287;}
        if(strpos($plan,'20')!== false) {$comision=2612;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3107;}
        if(strpos($plan,'40')!== false) {$comision=4358;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=312;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=626;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=811;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=833;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=996;}
        if(strpos($plan,'17')!== false) {$comision=1142;}
        if(strpos($plan,'20')!== false) {$comision=1306;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1554;}
        if(strpos($plan,'40')!== false) {$comision=2179;}
        $comision=0;
       }
       return($comision);
   }
   public function comisionArmalo_E4($plan,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if(strpos($plan,'2 ')!== false) {$comision=583;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=930;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1668;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1916;}
        if(strpos($plan,'9 GB')!== false) {$comision=2916;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2241;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2241;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2243;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2450;}
        if(strpos($plan,'17')!== false) {$comision=2730;}
        if(strpos($plan,'20')!== false) {$comision=3080;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3417;}
        if(strpos($plan,'40')!== false) {$comision=4658;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if(strpos($plan,'2 ')!== false) {$comision=152;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=450;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=633;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=925;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=925;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=653;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=653;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=656;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=1090;}
        if(strpos($plan,'17')!== false) {$comision=1242;}
        if(strpos($plan,'20')!== false) {$comision=1330;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1499;}
        if(strpos($plan,'40')!== false) {$comision=2099;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if(strpos($plan,'2')!== false) {$comision=256;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=679;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1229;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1459;}
        if(strpos($plan,'9 GB')!== false) {$comision=1656;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1656;}
        if(strpos($plan,'11 GB')!== false) {$comision=1809;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=1809;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2083;}
        if(strpos($plan,'17')!== false) {$comision=2229;}
        if(strpos($plan,'20')!== false) {$comision=2530;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=2868;}
        if(strpos($plan,'40')!== false) {$comision=4022;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=309;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=575;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=749;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=770;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=920;}
        if(strpos($plan,'17')!== false) {$comision=1056;}
        if(strpos($plan,'20')!== false) {$comision=1206;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1434;}
        if(strpos($plan,'40')!== false) {$comision=2011;}
        $comision=0;
       }
       return($comision);
   }
   public function comisionArmalo_E5($plan,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if(strpos($plan,'2 ')!== false) {$comision=610;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=1001;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1794;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=2067;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=2067;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2424;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2424;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2427;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2620;}
        if(strpos($plan,'17')!== false) {$comision=2931;}
        if(strpos($plan,'20')!== false) {$comision=3320;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3686;}
        if(strpos($plan,'40')!== false) {$comision=5026;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if(strpos($plan,'2 ')!== false) {$comision=243;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=686;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1368;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1635;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1635;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1986;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1986;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=1989;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2220;}
        if(strpos($plan,'17')!== false) {$comision=2531;}
        if(strpos($plan,'20')!== false) {$comision=2920;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3286;}
        if(strpos($plan,'40')!== false) {$comision=4626;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if(strpos($plan,'2')!== false) {$comision=229;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=830;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1621;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1938;}
        if(strpos($plan,'9 GB')!== false) {$comision=2123;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2123;}
        if(strpos($plan,'11 GB')!== false) {$comision=2169;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2169;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2571;}
        if(strpos($plan,'17')!== false) {$comision=2933;}
        if(strpos($plan,'20')!== false) {$comision=3334;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3824;}
        if(strpos($plan,'40')!== false) {$comision=5363;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=385;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=771;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=998;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1026;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=1226;}
        if(strpos($plan,'17')!== false) {$comision=1408;}
        if(strpos($plan,'20')!== false) {$comision=1608;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1912;}
        if(strpos($plan,'40')!== false) {$comision=2682;}
        $comision=0;
       }
       return($comision);
   }
   public function comisionArmalo_E6($plan,$tipo_venta) //EX MASTERS
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if(strpos($plan,'2 ')!== false) {$comision=610;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=1001;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1794;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=2067;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=2067;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2424;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2424;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2427;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2620;}
        if(strpos($plan,'17')!== false) {$comision=2931;}
        if(strpos($plan,'20')!== false) {$comision=3320;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3686;}
        if(strpos($plan,'40')!== false) {$comision=5026;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if(strpos($plan,'2 ')!== false) {$comision=152;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=450;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=633;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=757;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=757;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=917;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=917;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=920;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=1090;}
        if(strpos($plan,'17')!== false) {$comision=1242;}
        if(strpos($plan,'20')!== false) {$comision=1330;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1499;}
        if(strpos($plan,'40')!== false) {$comision=2099;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if(strpos($plan,'2')!== false) {$comision=229;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=830;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1621;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1938;}
        if(strpos($plan,'9 GB')!== false) {$comision=2123;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2123;}
        if(strpos($plan,'11 GB')!== false) {$comision=2169;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2169;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2571;}
        if(strpos($plan,'17')!== false) {$comision=2933;}
        if(strpos($plan,'20')!== false) {$comision=3334;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3824;}
        if(strpos($plan,'40')!== false) {$comision=5363;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=385;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=771;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=998;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1026;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=1226;}
        if(strpos($plan,'17')!== false) {$comision=1408;}
        if(strpos($plan,'20')!== false) {$comision=1608;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1912;}
        if(strpos($plan,'40')!== false) {$comision=2682;}
        $comision=0;
       }
       return($comision);
   }
   public function comisionArmalo_E7($plan,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if(strpos($plan,'2 ')!== false) {$comision=555;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=857;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1560;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1765;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1765;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2058;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2058;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2061;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2270;}
        if(strpos($plan,'17')!== false) {$comision=2529;}
        if(strpos($plan,'20')!== false) {$comision=2840;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3149;}
        if(strpos($plan,'40')!== false) {$comision=4288;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if(strpos($plan,'2 ')!== false) {$comision=152;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=450;} //ORIG 270
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=633;} //ORIG 505
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=757;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=757;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=919;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=919;} //ORIG 718
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=921;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=1090;}
        if(strpos($plan,'17')!== false) {$comision=1242;} //ORIG 985
        if(strpos($plan,'20')!== false) {$comision=1330;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1499;} //ORIG 1338
        if(strpos($plan,'40')!== false) {$comision=2099;} //ORIG 1877
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if(strpos($plan,'2')!== false) {$comision=229;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=686;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1390;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1590;}
        if(strpos($plan,'9 GB')!== false) {$comision=1750;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1750;}
        if(strpos($plan,'11 GB')!== false) {$comision=1784;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=1784;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2111;}
        if(strpos($plan,'17')!== false) {$comision=2405;}
        if(strpos($plan,'20')!== false) {$comision=2731;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3107;}
        if(strpos($plan,'40')!== false) {$comision=4358;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=313;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=626;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=811;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=834;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=996;}
        if(strpos($plan,'17')!== false) {$comision=1144;}
        if(strpos($plan,'20')!== false) {$comision=1306;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1554;}
        if(strpos($plan,'40')!== false) {$comision=2179;}
        $comision=0;
       }
       return($comision);
   }
   public function comisionArmalo_E8($plan,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if(strpos($plan,'2 ')!== false) {$comision=526;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=786;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1416;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1615;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1615;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1875;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1875;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=1878;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2090;}
        if(strpos($plan,'17')!== false) {$comision=2328;}
        if(strpos($plan,'20')!== false) {$comision=2608;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=2880;}
        if(strpos($plan,'40')!== false) {$comision=3920;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if(strpos($plan,'2 ')!== false) {$comision=151;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=450;} //ORIG=231
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=633;} //ORIG=434
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=757;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=757;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=919;} 
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=919;} //ORIG 616
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=921;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=1090;}
        if(strpos($plan,'17')!== false) {$comision=1242;}
        if(strpos($plan,'20')!== false) {$comision=1330;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1499;} //ORIG 1147
        if(strpos($plan,'40')!== false) {$comision=2099;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if(strpos($plan,'2')!== false) {$comision=229;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=638;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1235;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1416;}
        if(strpos($plan,'9 GB')!== false) {$comision=1625;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1625;}
        if(strpos($plan,'11 GB')!== false) {$comision=1656;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=1656;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=1918;}
        if(strpos($plan,'17')!== false) {$comision=2229;}
        if(strpos($plan,'20')!== false) {$comision=2530;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=2868;}
        if(strpos($plan,'40')!== false) {$comision=4022;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=289;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=578;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=749;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=770;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=920;}
        if(strpos($plan,'17')!== false) {$comision=1056;}
        if(strpos($plan,'20')!== false) {$comision=1206;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1434;}
        if(strpos($plan,'40')!== false) {$comision=2011;}
        $comision=0;
       }
       return($comision);
   }
   public function comisionArmalo_E9($plan,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if(strpos($plan,'2 ')!== false) {$comision=573;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=1345;}//OK
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1916;}//OK
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=2316;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=2316;}//OK
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2597;}
        if(strpos($plan,'11 GB')!== false) {$comision=2616;}//OK
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2796;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=3196;}//OK
        if(strpos($plan,'17')!== false) {$comision=2355;}//OK
        if(strpos($plan,'20')!== false) {$comision=3726;}//OK
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=4396;}//OK
        if(strpos($plan,'40')!== false) {$comision=5576;}//OK
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if(strpos($plan,'2 ')!== false) {$comision=321;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=945;}//OK
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1516;}//OK
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1916;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1916;}//OK
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2196;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2196;}//OK
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2396;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2796;}//OK
        if(strpos($plan,'17')!== false) {$comision=2956;}//OK
        if(strpos($plan,'20')!== false) {$comision=3326;}//OK
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3996;}//OK
        if(strpos($plan,'40')!== false) {$comision=5176;}//OK
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if(strpos($plan,'2')!== false) {$comision=321;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=945;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1516;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1916;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1916;} 
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2196;}
        if(strpos($plan,'11 GB')!== false) {$comision=2216;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2396;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2796;}
        if(strpos($plan,'17')!== false) {$comision=2956;}
        if(strpos($plan,'20')!== false) {$comision=3326;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3996;}
        if(strpos($plan,'40')!== false) {$comision=5176;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        $comision=0;
       }
       return($comision);
   }
   public function comisionArmalo_E10($plan,$tipo_venta) //JN
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if(strpos($plan,'2 ')!== false) {$comision=555;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=857;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1450;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1765;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1765;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2058;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2058;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2061;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2270;}
        if(strpos($plan,'17')!== false) {$comision=2529;}
        if(strpos($plan,'20')!== false) {$comision=2840;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3149;}
        if(strpos($plan,'40')!== false) {$comision=4288;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if(strpos($plan,'2 ')!== false) {$comision=152;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=751;} //ORIG 270
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1349;} //ORIG 505
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1546;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1546;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1879;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1879;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=1881;} 
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2120;}
        if(strpos($plan,'17')!== false) {$comision=1252;} //ORIG 985
        if(strpos($plan,'20')!== false) {$comision=1330;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3100;} //ORIG 1338
        if(strpos($plan,'40')!== false) {$comision=2099;} //ORIG 1877
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if(strpos($plan,'2')!== false) {$comision=229;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=686;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1050;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1590;}
        if(strpos($plan,'9 GB')!== false) {$comision=1750;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1750;}
        if(strpos($plan,'11 GB')!== false) {$comision=1784;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=1784;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2111;}
        if(strpos($plan,'17')!== false) {$comision=2405;}
        if(strpos($plan,'20')!== false) {$comision=2731;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3107;}
        if(strpos($plan,'40')!== false) {$comision=4358;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=313;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=626;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=811;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=834;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=996;}
        if(strpos($plan,'17')!== false) {$comision=1144;}
        if(strpos($plan,'20')!== false) {$comision=1306;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1554;}
        if(strpos($plan,'40')!== false) {$comision=2179;}
        $comision=0;
       }
       return($comision);
   }
   public function comisionArmalo_E11($plan,$tipo_venta)
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if(strpos($plan,'2 ')!== false) {$comision=526;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=786;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1416;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1780;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1800;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2090;}
        if(strpos($plan,'17')!== false) {$comision=2328;}
        if(strpos($plan,'20')!== false) {$comision=2600;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=2880;}
        if(strpos($plan,'40')!== false) {$comision=3920;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if(strpos($plan,'2 ')!== false) {$comision=268;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=751;} //ORIG=231
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1349;} //ORIG=434
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1780;} 
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1797;} //ORIG 616
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2120;}
        if(strpos($plan,'17')!== false) {$comision=2418;}
        if(strpos($plan,'20')!== false) {$comision=2760;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3100;} //ORIG 1147
        if(strpos($plan,'40')!== false) {$comision=4400;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=638;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1235;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1625;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1656;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=1918;}
        if(strpos($plan,'17')!== false) {$comision=2229;}
        if(strpos($plan,'20')!== false) {$comision=2530;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=2868;}
        if(strpos($plan,'40')!== false) {$comision=4022;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=289;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=578;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=749;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=770;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=920;}
        if(strpos($plan,'17')!== false) {$comision=1056;}
        if(strpos($plan,'20')!== false) {$comision=1206;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1434;}
        if(strpos($plan,'40')!== false) {$comision=2011;}
        $comision=0;
       }
       return($comision);
   }
   public function comisionArmalo_E12($plan,$tipo_venta) //CASTELAN
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if(strpos($plan,'2 ')!== false) {$comision=573;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=1461;}//OK
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=2220;}//OK
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=2700;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=2700;}//OK
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=3036;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=3036;}//OK
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=3276;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=3756;}//OK
        if(strpos($plan,'17')!== false) {$comision=3996;}//OK
        if(strpos($plan,'20')!== false) {$comision=4446;}//OK
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=5196;}//OK
        if(strpos($plan,'40')!== false) {$comision=6696;}//OK
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if(strpos($plan,'2 ')!== false) {$comision=173;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=1061;}//OK
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1820;}//OK
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=2300;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=2300;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2636;}//OK
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2636;}//OK
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2876;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=3356;}//OK
        if(strpos($plan,'17')!== false) {$comision=3596;}//OK
        if(strpos($plan,'20')!== false) {$comision=4046;}//OK
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=4796;}//OK
        if(strpos($plan,'40')!== false) {$comision=6296;}//OK
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if(strpos($plan,'2')!== false) {$comision=321;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=945;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1668;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=2108;}
        if(strpos($plan,'9 GB')!== false) {$comision=2251;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2416;} 
        if(strpos($plan,'11 GB')!== false) {$comision=2456;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2636;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=3076;}
        if(strpos($plan,'17')!== false) {$comision=3276;}
        if(strpos($plan,'20')!== false) {$comision=3686;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=4396;}
        if(strpos($plan,'40')!== false) {$comision=5736;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        $comision=0;
       }
       return($comision);
   }
   public function comisionArmalo_E13($plan,$tipo_venta) //IH
   {
       $comision=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación")
       {
        if(strpos($plan,'2 ')!== false) {$comision=555;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=857;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1542;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1765;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1765;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2058;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=2058;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=2060;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2270;}
        if(strpos($plan,'17')!== false) {$comision=2529;}
        if(strpos($plan,'20')!== false) {$comision=2840;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3149;}
        if(strpos($plan,'40')!== false) {$comision=4288;}
       }
       if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
        if(strpos($plan,'2 ')!== false) {$comision=265;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=751;} //ORIG 270
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1349;} //ORIG 505
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1546;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1546;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1879;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1879;} //ORIG 718
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=1881;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2120;}
        if(strpos($plan,'17')!== false) {$comision=2418;} //ORIG 985
        if(strpos($plan,'20')!== false) {$comision=2723;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3100;} //ORIG 1338
        if(strpos($plan,'40')!== false) {$comision=4400;} //ORIG 1877
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
        if(strpos($plan,'2')!== false) {$comision=243;}
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=686;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=1330;}
        if(strpos($plan,'8 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=1590;}
        if(strpos($plan,'9 GB')!== false) {$comision=1750;}
        if(strpos($plan,'10 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=1750;}
        if(strpos($plan,'11 GB')!== false) {$comision=1784;}
        if(strpos($plan,'12 GB')!== false || strpos($plan,'ORO')!== false) {$comision=1784;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=2111;}
        if(strpos($plan,'17')!== false) {$comision=2405;}
        if(strpos($plan,'20')!== false) {$comision=2731;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=3107;}
        if(strpos($plan,'40')!== false) {$comision=4358;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
        if(strpos($plan,'3 GB')!== false || strpos($plan,'AZUL 1')!== false) {$comision=313;}
        if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {$comision=626;}
        if(strpos($plan,'9 GB')!== false || strpos($plan,'AZUL 3')!== false) {$comision=811;}
        if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {$comision=834;}
        if(strpos($plan,'14 GB')!== false || strpos($plan,'BLACK')!== false || strpos($plan,'TITANIO')!== false) {$comision=996;}
        if(strpos($plan,'17')!== false) {$comision=1144;}
        if(strpos($plan,'20')!== false) {$comision=1306;}
        if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {$comision=1554;}
        if(strpos($plan,'40')!== false) {$comision=2179;}
        $comision=0;
       }
       return($comision);
   }
   public function comisionEmpresarial_E1($tipo_venta,$plazo,$renta)
   {
       $comision=0;
       $factor=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación" || $tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
            if($plazo=="12"){$factor=4;}
            if($plazo=="18"){$factor=4.25;}
            if($plazo>=24){$factor=4.5;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
            if($plazo=="12"){$factor=3;}
            if($plazo=="18"){$factor=3.4;}
            if($plazo>=24){$factor=3.93;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
            if($plazo=="12"){$factor=1.5;}
            if($plazo=="18"){$factor=1.7;}
            if($plazo>=24){$factor=1.97;}
       }
       $comision=$renta/1.16/1.03*$factor;
       return($comision);
   }
   public function comisionEmpresarial_E2($tipo_venta,$plazo,$renta)
   {
       $comision=0;
       $factor=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación" || $tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
            if($plazo=="12"){$factor=4;}
            if($plazo=="18"){$factor=4.25;}
            if($plazo>=24){$factor=4.5;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
            if($plazo=="12"){$factor=3;}
            if($plazo=="18"){$factor=3.4;}
            if($plazo>=24){$factor=3.75;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
            if($plazo=="12"){$factor=1.5;}
            if($plazo=="18"){$factor=1.7;}
            if($plazo>=24){$factor=1.88;}
       }
       $comision=$renta/1.16/1.03*$factor;
       return($comision);
   }
   public function comisionEmpresarial_E3($tipo_venta,$plazo,$renta)
   {
       $comision=0;
       $factor=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación" || $tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
            if($plazo=="12"){$factor=3.75;}
            if($plazo=="18"){$factor=4;}
            if($plazo>=24){$factor=4.25;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
            if($plazo=="12"){$factor=3;}
            if($plazo=="18"){$factor=3;}
            if($plazo>=24){$factor=3;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
            if($plazo=="12"){$factor=3;}
            if($plazo=="18"){$factor=3;}
            if($plazo>=24){$factor=3;}
       }
       $comision=$renta/1.16/1.03*$factor;
       return($comision);
   }
   public function comisionEmpresarial_E4($tipo_venta,$plazo,$renta)
   {
       $comision=0;
       $factor=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación" || $tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
            if($plazo=="12"){$factor=3.25;}
            if($plazo=="18"){$factor=3.5;}
            if($plazo>=24){$factor=3.75;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
            if($plazo=="12"){$factor=1.83;}
            if($plazo=="18"){$factor=2.13;}
            if($plazo>=24){$factor=2.35;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
            if($plazo=="12"){$factor=0.92;}
            if($plazo=="18"){$factor=1.07;}
            if($plazo>=24){$factor=1.18;}
       }
       $comision=$renta/1.16/1.03*$factor;
       return($comision);
   }
   public function comisionEmpresarial_E5($tipo_venta,$plazo,$renta)
   {
       $comision=0;
       $factor=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación" || $tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
            if($plazo=="12"){$factor=3;}
            if($plazo=="18"){$factor=3.25;}
            if($plazo>=24){$factor=3.75;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
            if($plazo=="12"){$factor=1.68;}
            if($plazo=="18"){$factor=1.95;}
            if($plazo>=24){$factor=2.35;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
            if($plazo=="12"){$factor=0.84;}
            if($plazo=="18"){$factor=0.98;}
            if($plazo>=24){$factor=1.18;}
       }
       $comision=$renta/1.16/1.03*$factor;
       return($comision);
   }
   public function comisionEmpresarial_E6($tipo_venta,$plazo,$renta)
   {
       $comision=0;
       $factor=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación" || $tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
            if($plazo=="12"){$factor=2.75;}
            if($plazo=="18"){$factor=3;}
            if($plazo>=24){$factor=3.25;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación" || $tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
            if($plazo=="12"){$factor=1.38;}
            if($plazo=="18"){$factor=1.5;}
            if($plazo>=24){$factor=1.63;}
       }
       $comision=$renta/1.16/1.03*$factor;
       return($comision);
   }
   public function comisionEmpresarial_E7($tipo_venta,$plazo,$renta)
   {
       $comision=0;
       $factor=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación" || $tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
            if($plazo=="12"){$factor=3.5;}
            if($plazo=="18"){$factor=4;}
            if($plazo>=24){$factor=4.25;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
            if($plazo=="12"){$factor=2.07;}
            if($plazo=="18"){$factor=2.9;}
            if($plazo>=24){$factor=3.6;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
            if($plazo=="12"){$factor=1.04;}
            if($plazo=="18"){$factor=1.45;}
            if($plazo>=24){$factor=1.8;}
       }
       $comision=$renta/1.16/1.03*$factor;
       return($comision);
   }
   public function comisionEmpresarial_E8($tipo_venta,$plazo,$renta)
   {
       $comision=0;
       $factor=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación" || $tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
            if($plazo=="12"){$factor=2.75;}
            if($plazo=="18"){$factor=3;}
            if($plazo>=24){$factor=3.25;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
            if($plazo=="12"){$factor=1.55;}
            if($plazo=="18"){$factor=1.8;}
            if($plazo>=24){$factor=2.05;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
            if($plazo=="12"){$factor=0.78;}
            if($plazo=="18"){$factor=0.9;}
            if($plazo>=24){$factor=1.03;}
       }
       $comision=$renta/1.16/1.03*$factor;
       return($comision);
   }
   public function comisionEmpresarial_E9($tipo_venta,$plazo,$renta)
   {
       $comision=0;
       $factor=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación" || $tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
            if($plazo=="12"){$factor=3;}
            if($plazo=="18"){$factor=3.75;}
            if($plazo>=24){$factor=4.25;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
            if($plazo=="12"){$factor=2.25;}
            if($plazo=="18"){$factor=2.8;}
            if($plazo>=24){$factor=3.35;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
            if($plazo=="12"){$factor=1.13;}
            if($plazo=="18"){$factor=1.4;}
            if($plazo>=24){$factor=1.68;}
       }
       $comision=$renta/1.16/1.03*$factor;
       return($comision);
   }
   public function comisionEmpresarial_E10($tipo_venta,$plazo,$renta)
   {
       $comision=0;
       $factor=0;
       if($tipo_venta=="Activacion" || $tipo_venta=="Activación" || $tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
       {
            if($plazo=="12"){$factor=2.75;}
            if($plazo=="18"){$factor=3.25;}
            if($plazo>=24){$factor=4;}
       }
       if($tipo_venta=="Renovacion" || $tipo_venta=="Renovación")
       {
            if($plazo=="12"){$factor=2.07;}
            if($plazo=="18"){$factor=2.6;}
            if($plazo>=24){$factor=3.35;}
       }
       if($tipo_venta=="Renovación Equipo Propio" || $tipo_venta=="Renovacion Equipo Propio")
       {
            if($plazo=="12"){$factor=1.04;}
            if($plazo=="18"){$factor=1.3;}
            if($plazo>=24){$factor=1.68;}
       }
       $comision=$renta/1.16/1.03*$factor;
       return($comision);
   }
   public function comisionEspecialArmalo($reg_distribuidor,$comision_original,$plan,$tipo_venta)
   {
       $numero_distribuidor=$reg_distribuidor->first()->numero_distribuidor;

       if(
            $numero_distribuidor=='100002' || //	AFG CONECTIVITI SOLUTIONS SAS
            $numero_distribuidor=='100033' || //	CONECTA SERVICIOS MOVILES DE MEXICO SAS DE CV
            $numero_distribuidor=='100009' || //	D Y A COMMUNICATION SAS
            $numero_distribuidor=='100015' || //	JN & TELECOMUNICACIONES VENTAS Y SERVICIOS SA DE CV
            $numero_distribuidor=='100020' || //	NAXE COMMUNIQUE SAS
            $numero_distribuidor=='100027'    //	TECNOLOGIA DIGITAL IH SAS' 
         )
       {
            if($tipo_venta=="Activación Equipo Propio" || $tipo_venta=="Activacion Equipo Propio")
            {
                if(strpos($plan,'3')!== false) {return(690);} 
                if(strpos($plan,'5 GB')!== false || strpos($plan,'AZUL 2')!== false) {return(1270);}
                if(strpos($plan,'11 GB')!== false || strpos($plan,'PLATA')!== false) {return(1680);}
                if(strpos($plan,'17')!== false) {return(2300);}
                if(strpos($plan,'26 GB')!== false || strpos($plan,'DIAMANTE')!== false) {return(3100);}
                if(strpos($plan,'40')!== false) {return(4400);}
            }
        }
       return($comision_original);
   }
   public function comisionPorSemana($numero_distribuidor)
   {
        $comision=750;
        if($numero_distribuidor=='100005') { $comision=900;}
        if($numero_distribuidor=='100008') { $comision=900;}
        if($numero_distribuidor=='100009') { $comision=770;}
        if($numero_distribuidor=='100011') { $comision=800;}
        if($numero_distribuidor=='100013') { $comision=750;}
        if($numero_distribuidor=='100014') { $comision=800;}
        if($numero_distribuidor=='100022') { $comision=900;}
        if($numero_distribuidor=='100024') { $comision=820;}
        if($numero_distribuidor=='100026') { $comision=750;}
        if($numero_distribuidor=='100027') { $comision=740;}
        if($numero_distribuidor=='100028') { $comision=750;}
        if($numero_distribuidor=='100029') { $comision=750;}
        if($numero_distribuidor=='100032') { $comision=900;}
        if($numero_distribuidor=='100033') { $comision=740;}
        if($numero_distribuidor=='100035') { $comision=900;}
        if($numero_distribuidor=='100042') { $comision=750;}
        if($numero_distribuidor=='100044') { $comision=750;}
        if($numero_distribuidor=='100045') { $comision=750;}
        if($numero_distribuidor=='100047') { $comision=990;}
        if($numero_distribuidor=='100048') { $comision=750;}
        if($numero_distribuidor=='100049') { $comision=790;}
        return($comision);
   }
   public function comisionSimple($distribuidor,$esquema,$renta,$plazo)
   {
        $factor=0;
        $comision=0;
        if($distribuidor=='100049'){if($plazo<12){$factor=38;} if($plazo>=12 && $plazo<18){$factor=1.2;} if($plazo>=18 && $plazo<24){$factor=2;} if($plazo>=24){$factor=2.4;}}
        if($distribuidor=='100009'){if($plazo<12){$factor=38;} if($plazo>=12 && $plazo<18){$factor=1.2;} if($plazo>=18 && $plazo<24){$factor=2;} if($plazo>=24){$factor=2.4;}}
        if($distribuidor=='100008'){if($plazo<12){$factor=38;} if($plazo>=12 && $plazo<18){$factor=1.2;} if($plazo>=18 && $plazo<24){$factor=2;} if($plazo>=24){$factor=2.4;}}
        if($distribuidor=='100047'){if($plazo<12){$factor=55;} if($plazo>=12 && $plazo<18){$factor=1.3;} if($plazo>=18 && $plazo<24){$factor=2.15;} if($plazo>=24){$factor=2.6;}}
        if($distribuidor=='100046'){if($plazo<12){$factor=38;} if($plazo>=12 && $plazo<18){$factor=1.2;} if($plazo>=18 && $plazo<24){$factor=2;} if($plazo>=24){$factor=2.4;}}
        if($distribuidor=='100033'){if($plazo<12){$factor=38;} if($plazo>=12 && $plazo<18){$factor=1.2;} if($plazo>=18 && $plazo<24){$factor=2;} if($plazo>=24){$factor=2.4;}}
        if($distribuidor=='100027'){if($plazo<12){$factor=38;} if($plazo>=12 && $plazo<18){$factor=1.2;} if($plazo>=18 && $plazo<24){$factor=2;} if($plazo>=24){$factor=2.4;}}
        if($distribuidor=='100032'){if($plazo<12){$factor=55;} if($plazo>=12 && $plazo<18){$factor=1.15;} if($plazo>=18 && $plazo<24){$factor=1.9;} if($plazo>=24){$factor=2.25;}}
        if($distribuidor=='100005'){if($plazo<12){$factor=55;} if($plazo>=12 && $plazo<18){$factor=1.15;} if($plazo>=18 && $plazo<24){$factor=1.9;} if($plazo>=24){$factor=2.25;}}
        if($distribuidor=='100011'){if($plazo<12){$factor=38;} if($plazo>=12 && $plazo<18){$factor=1.2;} if($plazo>=18 && $plazo<24){$factor=2;} if($plazo>=24){$factor=2.4;}}
        if($distribuidor=='100014'){if($plazo<12){$factor=38;} if($plazo>=12 && $plazo<18){$factor=1.2;} if($plazo>=18 && $plazo<24){$factor=2;} if($plazo>=24){$factor=2.4;}}
        if($distribuidor=='100035'){if($plazo<12){$factor=38;} if($plazo>=12 && $plazo<18){$factor=1.2;} if($plazo>=18 && $plazo<24){$factor=2;} if($plazo>=24){$factor=2.4;}}
        if($distribuidor=='100035'){if($plazo<12){$factor=38;} if($plazo>=12 && $plazo<18){$factor=1.2;} if($plazo>=18 && $plazo<24){$factor=2;} if($plazo>=24){$factor=2.4;}}
        if($esquema==6){if($plazo<12){$factor=38;} if($plazo>=12 && $plazo<18){$factor=1.2;} if($plazo>=18 && $plazo<24){$factor=2;} if($plazo>=24){$factor=2.4;}}
        if($esquema==7){if($plazo<12){$factor=38;} if($plazo>=12 && $plazo<18){$factor=1.05;} if($plazo>=18 && $plazo<24){$factor=1.75;} if($plazo>=24){$factor=2.1;}}
        if($esquema==8){if($plazo<12){$factor=38;} if($plazo>=12 && $plazo<18){$factor=1;} if($plazo>=18 && $plazo<24){$factor=1.65;} if($plazo>=24){$factor=2;}}

        if($factor>3)
        {
            $comision=$factor;
        }
        else
        {
            $comision=($renta/1.16/1.03)*$factor;
        }
        return($comision);
   }
}