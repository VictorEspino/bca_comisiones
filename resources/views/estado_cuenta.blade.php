<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.6.0/dist/alpine.js" defer></script>

<!-- PARA EL DASHBOARD -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <link href="https://unpkg.com/tailwindcss/dist/tailwind.min.css">
    
    </head>
<body>
    <div class="flex flex-col w-full">
        

<?php
 //echo $id_calculo.'-'.$id_empleado; 
 $empleado=App\Models\Empleado::where('numero_empleado',$id_empleado)
    ->where('calculo_id',$id_calculo)
    ->get()
    ->first();
if($id_empleado=="")
{
    echo "ACCESO DENEGADO";
}  
else  
if(is_null($empleado))
{
    ?>
    <div class="flex flex-row bg-blue-900 py-5 px-5 content-center space-x-10">   
        <div>
            <image src="{{url('images/bca.jpg') }}" class="w-12 rounded-lg shadow-2xl"></image>
        </div>
        <div class="flex flex-col">
            <div class="text-white text-xl"> 
                No tienes comisiones que mostrar 
            </div>
        </div>
    </div>
<?php
}
else{

$calculo=App\Models\Calculo::where('id',$id_calculo)
    ->get()
    ->first();
?>
        <div class="flex flex-row bg-blue-900 py-5 px-5 content-center space-x-10">   
            <div>
                <image src="{{url('images/bca.jpg') }}" class="w-12 rounded-lg shadow-2xl"></image>
            </div>
            <div class="flex flex-col w-8/12">
                <div class="text-white text-xl"> 
                    {{$empleado->nombre}} 
                </div>
                <div class="text-white text-sm italic">
                    {{$empleado->puesto}} - {{$empleado->pdv}} - Estado de cuenta de comisiones
                </div>
                <div class="text-white text-sm italic">
                    {{$calculo->descripcion}} - Del {{$calculo->fecha_inicio}} al {{$calculo->fecha_fin}}
                </div>
            </div>
            <div class="text-red-700 text-sm italic flex flex-col">
<?php
            $ligas_calculo=App\Models\Calculo::where('id','>=',45)->orderBy('id','desc')->take(6)->get();
            foreach($ligas_calculo as $liga)
            {
                if($source=="erp")
                {
?>
                <div>
                    <a href="{{route('estado_cuenta',['id_calculo' => $liga->id,'id_empleado'=> $p1,'f_now'=>$p2])}}">
                    <i class="far fa-calendar"></i> {{$liga->descripcion}}
                    </a>
                </div>
                
<?php
                }
                if($source=="interno")
                {
?>
                    <div>
                        <a href="{{route('estado_cuenta_interno',['id_calculo' => $liga->id,'id_empleado'=> $id_empleado,'f_now'=>0])}}">
                        <i class="far fa-calendar"></i> {{$liga->descripcion}}
                        </a>
                    </div>
                    
<?php   
                }
            }
?>
            </div>
        </div>
<?php
$vista_ventas=true;
$registros_venta=false;
$vista_ejecutivo=false;
$vista_gerente=false;
$vista_regional=false;
$vista_director=false;
if(strtoupper($empleado->puesto)=='EJECUTIVO'){$vista_ejecutivo=true;}
if(strtoupper($empleado->puesto)=='DIRECTOR'){$vista_director=true;}
if(strtoupper($empleado->puesto)=='REGIONAL' || strtoupper($empleado->puesto)=='GERENTE REGIONAL'){$vista_regional=true;}
if(strtoupper($empleado->puesto)=='GERENTE SUCURSALES' || strtoupper($empleado->puesto)=='GERENTE ROTATIVO' || strtoupper($empleado->puesto)=='GERENTE IN TRAINING'){$vista_gerente=true;}

$pagos=App\Models\Payment::where('calculo_id',$id_calculo)
            ->where('numero_empleado',$id_empleado)
            ->get()->first();

if (is_null($pagos))
{
?>

<div class="flex flex-row bg-blue-900 py-5 px-5 content-center space-x-10">   
        <div class="flex flex-col">
            <div class="text-white text-xl"> 
                No tienes comisiones que mostrar 
            </div>
        </div>
    </div>
<?php
return;

}

?>      
        <div class="flex flex-row py-5 px-5 content-center space-x-10 w-full"> 
            <div class="flex flex-col w-full">
                <div class="w-full mb-4">ESTADO DE CUENTA</div>
                <div class="flex flex-row">
                    <div class="w-1/3">
                        <div class="flex flex-row px-2">
                            <div class="w-1/2 px-2 pt-1">
                                Comisiones Ventas
                            </div>
                            <div class="px-2 pt-1">
                                ${{number_format($pagos->comision_ventas)}}
                            </div>
                        </div>
<?php
            if($vista_gerente)
            {
?>
                        <div class="flex flex-row px-2">
                            <div class="w-1/2 px-2 pt-1">
                                Comisiones Gerente
                            </div>
                            <div class="px-2 pt-1">
                                ${{number_format($pagos->comision_gerente)}}
                            </div>
                        </div>
<?php
            }
            if($vista_regional)
            {
?>
                        <div class="flex flex-row px-2">
                            <div class="w-1/2 px-2 pt-1">
                                Comisiones Regional
                            </div>
                            <div class="px-2 pt-1">
                                ${{number_format($pagos->comision_regional)}}
                            </div>
                        </div>
<?php
            }
            if($vista_director)
            {
?>
                        <div class="flex flex-row px-2">
                            <div class="w-1/2 px-2 pt-1">
                                Comisiones Director
                            </div>
                            <div class="px-2 pt-1">
                                ${{number_format($pagos->comision_director)}}
                            </div>
                        </div>
<?php
            }
?>
                        <div class="flex flex-row px-2">
                            <div class="w-1/2 px-2 pt-1">
                                Retroactivos (CIS)
                            </div>
                            <div class="px-2 pt-1">
                                ${{number_format($pagos->retroactivo)}}
                            </div>
                        </div>
                        <div class="flex flex-row px-2 text-white">
                            <div class="w-1/2 bg-gray-500 px-2 pt-1">
                                Comisiones TOTAL
                            </div>
                            <div class="bg-gray-500 px-2 pt-1">
                                ${{number_format($pagos->comision_director+$pagos->comision_regional+$pagos->comision_gerente+$pagos->comision_ventas+$pagos->retroactivo)}}
                            </div>
                        </div>
                    </div>
                    <div class="w-1/3">
                        <div class="flex flex-row px-2">
                            <div class="w-1/2 px-2 pt-1 text-red-700">
                                Adeudos Previos
                            </div>
                            <div class="px-2 pt-1 text-red-700">
                                ${{number_format($pagos->adeudo_anterior)}}
                            </div>
                        </div>
                        <div class="flex flex-row px-2">
                            <div class="w-1/2 px-2 pt-1 text-red-700 flex flex-col">
                                <div>Charge Back</div>
                                <div class="text-xs italic px-1">Calculado en este perioro</div>
                            </div>
                            <div class="px-2 pt-1 text-red-700">
                                ${{number_format($pagos->charge_back)}}
                            </div>
                        </div>
                        <div class="flex flex-row px-2 text-white">
                            <div class="w-1/2 bg-red-500 px-2 pt-1">
                                Adeudos TOTAL
                            </div>
                            <div class="bg-red-500 px-2 pt-1">
                                ${{number_format($pagos->adeudo_anterior+$pagos->charge_back)}}
                            </div>
                        </div>
                        
                    </div>
                    <div class="w-1/3">
                        <div class="flex flex-row px-2 text-white">
                            <div class="{{$empleado->modalidad=='1'?'w-1/2':'w-full'}} px-2 pt-1 flex flex-col bg-blue-500">
                                <div>{{$empleado->modalidad=='1'?'Sueldo':''}}</div>
<?php   
            if($empleado->modalidad=='1'){
?>
                                <div class="text-xs italic px-1">Se refiere a la cantidad que cobraste por concepto de SUELDO en el periodo del {{$calculo->fecha_inicio}} al {{$calculo->fecha_fin}}</div>
<?php           
                }
            if($empleado->modalidad=='3'){
?>
                                <div class="text-xs italic px-1">Por regla especial, tus comisiones se pagaran sin la integracion del sueldo base, es decir, recibiras estas comisiones adicionales a tu sueldo</div>
<?php
            }
?>
                            </div>
<?php       
            if($empleado->modalidad=='1')
            {
?>
                            <div class="w-1/2 px-2 pt-1 bg-blue-500">
                                ${{number_format($empleado->sueldo)}}
                            </div>
<?php
            }
?>
                        </div>
                        <div class="flex flex-row px-2">
                            <div class="w-1/2 px-2 pt-1 flex flex-col">
                                <div>{{$empleado->modalidad=='1'?'Comision Adicional':'Comision'}}</div>
                                <div class="text-xs italic px-1">{{$empleado->modalidad=='1'?'Se refiere a la cantidad que tus comisiones excediron la comision garantizada':''}}</div>
                            </div>
                            <div class="px-2 pt-1">
<?php
    $c_edo_cta_total=$pagos->comision_director+$pagos->comision_regional+$pagos->comision_gerente+$pagos->comision_ventas+$pagos->retroactivo;
    $c_edo_cta=0;
    $c_edo_cta_abono=0;
    if($empleado->modalidad==1)
    {
        if($c_edo_cta_total-$empleado->sueldo>=0)
        {
            $c_edo_cta=$c_edo_cta_total-$empleado->sueldo;
        }
        else{
            $c_edo_cta=0;
        }
    }
    else{
     $c_edo_cta=$c_edo_cta_total;
    }

    $retiros_totales=$pagos->adeudo_anterior+$pagos->charge_back;
    //if($retiros_totales>=1000)
    //{
    //    $retiros_totales=1000;
    //}
    //if($retiros_totales>=75000)
    //{
    //    $retiros_totales=1250;
    //}
    //if($retiros_totales>=100000)
    //{
    //    $retiros_totales=1500;
    //}

    if($c_edo_cta>=$retiros_totales)
    {   
        $c_edo_cta_abono=$retiros_totales;
    }
    else
    {
        $c_edo_cta_abono=$c_edo_cta;
    }


?>
                                ${{number_format($c_edo_cta)}}
                            </div>
                        </div>
                        <div class="flex flex-row px-2">
                            <div class="w-1/2 px-2 pt-1 text-red-700 flex flex-col">
                                <div>Abono adeudo</div>
                            </div>
                            <div class="px-2 pt-1 text-red-700">
                                -${{number_format($c_edo_cta_abono)}}
                            </div>
                        </div>
                        <div class="flex flex-row px-2 text-white">
                            <div class="w-1/2 bg-blue-500 px-2 pt-1">
                                Comisiones a Pagar
                            </div>
                            <div class="w-1/2 bg-blue-500 px-2 pt-1">
                                ${{number_format($pagos->a_pagar)}}
                            </div>
                        </div>
                        <div class="flex flex-row px-2 text-white">
                            <div class="w-1/2 bg-red-500 px-2 pt-1">
                                Adeudos pendientes
                            </div>
                            <div class="w-1/2 bg-red-500 px-2 pt-1">
                                ${{number_format($pagos->adeudo)}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php

$balance_ventas=App\Models\BalanceComisionVenta::where('calculo_id',$id_calculo)
    ->where('numero_empleado',$id_empleado)
    ->select('numero_empleado','esquema','cumple_objetivo','porcentaje_cobro',
        DB::raw('sum(uds_activacion) as uds_activacion'),
        DB::raw('sum(renta_activacion) as renta_activacion'),
        DB::raw('sum(comision_activacion) as comision_activacion'),
        DB::raw('sum(uds_aep) as uds_aep'),
        DB::raw('sum(renta_aep) as renta_aep'),
        DB::raw('sum(comision_aep) as comision_aep'),
        DB::raw('sum(uds_renovacion) as uds_renovacion'),
        DB::raw('sum(renta_renovacion) as renta_renovacion'),
        DB::raw('sum(comision_renovacion) as comision_renovacion'),
        DB::raw('sum(uds_rep) as uds_rep'),
        DB::raw('sum(renta_rep) as renta_rep'),
        DB::raw('sum(comision_rep) as comision_rep'),
        DB::raw('sum(uds_seguro) as uds_seguro'),
        DB::raw('sum(renta_seguro) as renta_seguro'),
        DB::raw('sum(comision_seguro) as comision_seguro'),
        DB::raw('sum(uds_addon) as uds_addon'),
        DB::raw('sum(renta_addon) as renta_addon'),
        DB::raw('sum(comision_addon) as comision_addon'),
        DB::raw('sum(comision_final_activacion) as comision_final_activacion'),
        DB::raw('sum(comision_final_aep) as comision_final_aep'),
        DB::raw('sum(comision_final_renovacion) as comision_final_renovacion'),
        DB::raw('sum(comision_final_rep) as comision_final_rep'),
        DB::raw('sum(comision_final_seguro) as comision_final_seguro'),
        DB::raw('sum(comision_final_addon) as comision_final_addon'),
        DB::raw('sum(comision_final) as comision_final')
    )
    ->groupBy('numero_empleado','esquema','cumple_objetivo','porcentaje_cobro')
    ->get()
    ->first();
if(!is_null($balance_ventas)) $registros_venta=true;

if($registros_venta)
{
?>
        <div class="flex flex-col content-justify py-3 px-3 space-y-3 space-x-0 lg:space-y-0 md:space-y-0 lg:px-8 md:px-8 lg:py-8 md:py-8 lg:flex-row md:flex-row lg:space-x-5 md:space-x-5">
            <div class="flex flex-col space-y-2 w-full lg:w-8/12 md:w-8/12">
                <div>COMISION POR VENTAS</div>
<?php     
    if($vista_ejecutivo){
?>
                <div class="flex flex-row space-x-8">
                    <div class="font-bold text-2xl py-5 px-5 bg-black text-white rounded-lg flex justify-center flex flex-col">
                        <div>Esquema</div>
                        <div class="flex justify-center"> {{$balance_ventas->esquema}}</div>
                    </div>
                    <div class="flex flex-col">
                        <div class="text-xs">Contribucion minima quincenal para alcanzar 100% de comision</div>
                        <div class="font-bold text-base">{{$balance_ventas->esquema=='1'?'10':'4'}} unidades activaciones</div>
                        <!--<div class="font-bold text-base">{{$balance_ventas->esquema=='1'?'6':'2'}} activaciones con equipo + {{$balance_ventas->esquema=='1'?'4':'2'}} activaciones equipo propio</div>-->
                    </div>
                </div>
                <div class="w-full flex justify-center {{$balance_ventas->cumple_objetivo?'bg-green-600':'bg-red-600'}} text-white">
                    {{$balance_ventas->cumple_objetivo?'Felicidades ':'Lo lamentamos NO '}}cumpliste con la contribucion minima!
                </div>
                <div class="w-full flex justify-center text-xs italic {{$balance_ventas->cumple_objetivo?'text-green-700':'text-red-600'}}">
                    {{$balance_ventas->cumple_objetivo?'Tus comisiones se pagan al 100':'Recibiras tus comisiones al '.$balance_ventas->porcentaje_cobro*100}}%
                </div>
<?php
    }
?>
                <div class="w-full flex justify-center text-sm">
                    Comision Directa: ${{number_format($balance_ventas->comision_activacion+$balance_ventas->comision_aep+$balance_ventas->comision_renovacion+$balance_ventas->comision_rep+$balance_ventas->comision_addon+$balance_ventas->comision_seguro)}}
                </div>
                <div class="w-full flex justify-center text-3xl font-bold">
                    Comision {{$vista_ejecutivo?'Final':''}}: ${{number_format($balance_ventas->comision_final_activacion+$balance_ventas->comision_final_aep+$balance_ventas->comision_final_renovacion+$balance_ventas->comision_final_rep+$balance_ventas->comision_final_addon+$balance_ventas->comision_final_seguro)}}
                </div>
            </div>



            <div class="lg:space-x-0 md:space-x-0 space-x-5 space-y-0 lg:space-y-3 md:space-y-3 w-full lg:w-2/12 md:w-2/12 flex flex-row lg:flex-col md:flex-col">
                <div class="flex flex-col content-center bg-gradient-to-br from-blue-600 to-green-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                        <div class="w-5/6 font-bold">Activaciones</div><div class="text-sm font-bold"></div> 
                    </div> 
                    <div class="text-3xl font-bold">{{number_format($balance_ventas->uds_activacion)}}</div>
                    <div class="flex flex-row">
                        <div class="font-bold text-xs">Rentas ${{number_format($balance_ventas->renta_activacion)}}</div> 
                    </div> 
                </div>
                <div class="flex flex-col content-center bg-gradient-to-br from-pink-600 to-yellow-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                        <div class="w-5/6 font-bold">Act Eq Propio</div><div class="text-sm font-bold"></div> 
                    </div> 
                    <div class="text-3xl font-bold">{{number_format($balance_ventas->uds_aep)}}</div>
                    <div class="flex flex-row">
                        <div class="font-bold text-xs">Rentas ${{number_format($balance_ventas->renta_aep)}}</div> 
                    </div> 
                </div>
            </div>
            <div class="lg:space-x-0 md:space-x-0 space-x-5 space-y-0 lg:space-y-3 md:space-y-3 w-full lg:w-2/12 md:w-2/12 flex flex-row lg:flex-col md:flex-col">
                <div class="flex flex-col content-center bg-gradient-to-br from-purple-700 to-pink-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                        <div class="w-5/6 font-bold">Renovaciones</div><div class="text-sm font-bold"></div> 
                    </div> 
                    <div class="text-3xl font-bold">{{number_format($balance_ventas->uds_renovacion)}}</div>
                    <div class="flex flex-row">
                        <div class="font-bold text-xs">Rentas ${{number_format($balance_ventas->renta_renovacion)}}</div> 
                    </div> 
                </div>
                <div class="flex flex-col content-center bg-gradient-to-r from-yellow-400 to-yellow-700 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                        <div class="w-5/6 font-bold">Ren Eq Propio</div><div class="text-sm font-bold"></div> 
                    </div> 
                    <div class="text-3xl font-bold">{{number_format($balance_ventas->uds_rep)}}</div>
                    <div class="flex flex-row">
                        <div class="font-bold text-xs">Rentas ${{number_format($balance_ventas->renta_rep)}}</div> 
                    </div> 
                </div>
            </div>
        </div>
        <div class="flex justify-center text-xs font-bold px-3">
            <table class="lg:w-7/12 md:w-9/12 w-full" border=1>
                    
<?php
    $transacciones_venta=App\Models\Transaccion::where('calculo_id',$id_calculo)
                                            ->where('numero_empleado',$id_empleado)
                                            //->where('credito',1)
                                            ->orderBy('tipo_venta','asc')
                                            ->orderBy('fecha','asc')
                                            ->get();
    $act_ren=$transacciones_venta->whereIn('tipo_venta',['Activación',
                                                         'Renovación',
                                                         'Activacion',
                                                         'Renovacion',
                                                         'Activación Equipo Propio',
                                                         'Activacion Equipo Propio',
                                                         'Renovación Equipo Propio',
                                                         'Renovacion Equipo Propio',
                                                         'Renovación Empresarial',
                                                         'Renovacion Empresarial',
                                                         'Activación Empresarial',
                                                         'Activacion Empresarial'
                                                        ]);
    $seguros=$transacciones_venta->whereIn('tipo_venta',['Proteccion de equipo',
                                                        'Protección de equipo'
                                                       ]);
    $addons=$transacciones_venta->whereIn('tipo_venta',['ADD ON']);

$tipo_actual="";
$color=false;
$comision_parcial=0;
foreach($act_ren as $mov_principal)
    {
        if($mov_principal->tipo_venta!=$tipo_actual)
        {
            $color=false;
?>
        <tr><td colspan="7" class="px-3 py-3 bg-gradient-to-br from-blue-900 to-blue-500 text-white text-xl  font-bold">{{$mov_principal->tipo_venta}}</td></tr>
        <tr>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Sucursal</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Pedido</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Contrato</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Servicio</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold"><center>Equipo s/c</center></td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Importe</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Comision</td>
                </tr>
<?php
        }
?>
        <tr>
            <td class="px-3 pt-2 text-gray-900 font-thin text-xs {{$color?'bg-blue-100':''}}">{{$mov_principal->pdv}}</td>
            <td class="px-3 pt-2 text-gray-900 font-thin text-sm {{$color?'bg-blue-100':''}}">{{$mov_principal->pedido}}</td>
            <td class="px-3 pt-2 text-gray-900 font-thin text-sm {{$color?'bg-blue-100':''}}">{{$mov_principal->contrato}}</td>
            <td class="px-3 pt-2 text-gray-900 font-thin text-sm w-5/12 {{$color?'bg-blue-100':''}}">{{$mov_principal->servicio}}<br><span class="text-xs font-thin italic text-red-700">{{$mov_principal->razon_cr0}}</span> </td>
            <td class="px-3 pt-2 text-gray-900 font-thin text-green-500 text-lg {{$color?'bg-blue-100':''}}">
<?php
    if($mov_principal->eq_sin_costo) {
?>
                <center><i class="fas fa-check-circle"></i></center>
<?php
    }
?>
            </td>
            <td class="text-gray-900 font-thin text-sm px-3 pt-2 text-gray-900 font-thin {{$color?'bg-blue-100':''}}">$ {{$mov_principal->importe}}</td>
            <td class="text-gray-900 font-thin text-sm px-3 pt-2 font-bold {{$color?'bg-blue-100':''}}"><center>$ {{$mov_principal->comision_venta}}</center></td>
        </tr>
<?php
    if($seguros->contains('pedido',$mov_principal->pedido))
    {
        $seguro_plan=$seguros->where('pedido',$mov_principal->pedido)->first();
?>
        <tr>
            <td class="px-3 {{$color?'bg-blue-100':''}}"></td>
            <td class="px-3 {{$color?'bg-blue-100':''}}"></td>
            <td class="px-3 {{$color?'bg-blue-100':''}}"></td>
            <td class="px-3 text-xs italic {{$color?'bg-blue-100':''}}">+ {{$seguro_plan->servicio}}</td>
            <td class="px-3 text-xs italic {{$color?'bg-blue-100':''}}"></td>
            <td class="{{$color?'bg-blue-100':''}}"></td>
            <td class="text-gray-900 font-thin text-sm px-3 pt-2 font-bold {{$color?'bg-blue-100':''}}"><center>+ $ {{$seguro_plan->comision_venta}}</center></td>
        </tr>
        <tr>
            <td colspan=5 class="{{$color?'bg-blue-100':''}}"></td>
            <td class="{{$color?'bg-blue-100':''}} px-3 py-2 font-bold">Total</td>
            <td class="px-3 py-2 font-bold {{$color?'bg-blue-100':''}}"><center>$ {{$seguro_plan->comision_venta+$mov_principal->comision_venta}}</center></td>
        </tr>


<?php

    }
    $tipo_actual=$mov_principal->tipo_venta;
    $color=!$color;
    }
    $color=false;
?>

        <tr><td colspan="7" class="px-3 py-3 bg-gradient-to-br from-blue-900 to-blue-500 text-white text-xl  font-bold">Servicios Adicionales</td></tr>
        <tr>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Sucursal</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Pedido</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Contrato</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Servicio</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold"><center>Equipo s/c</center></td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Importe</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Comision</td>
                </tr>
<?php
foreach($addons as $addon)
    {
?>
        <tr>
            <td class="text-gray-900 font-thin text-xs px-3 {{$color?'bg-blue-100':''}}">{{$addon->pdv}}</td>
            <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}">{{$addon->pedido}}</td>
            <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}">{{$addon->contrato}}</td>
            <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}">{{$addon->servicio}}<br><span class="text-xs font-thin italic text-red-700">{{$addon->razon_cr0}}</span></td>
            <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}"></td>
            <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}">$ {{$addon->importe}}</td>
            <td class="text-gray-900 font-thin text-sm px-3 pt-2 font-bold {{$color?'bg-blue-100':''}}"><center>$ {{$addon->comision_venta}}</center></td>
        </tr>
<?php
        $color=!$color;
    }
?>
    </table>
<?php
}
else
{    
    echo "";
}
?>
            
        </div>

    </div>
<?php
    if($vista_gerente){

        $balance_gerente_tdas=App\Models\BalanceComisionGerente::where('calculo_id',$id_calculo)
        ->where('numero_empleado',$id_empleado)
        ->get();
        foreach($balance_gerente_tdas as $balance_gerente)
        {
?>
    <div class="flex flex-col content-justify py-3 px-3 space-y-3 space-x-0 lg:space-y-0 md:space-y-0 lg:px-8 md:px-8 lg:py-8 md:py-8 lg:flex-row md:flex-row lg:space-x-5 md:space-x-5">
            <div class="flex flex-col space-y-2 w-full">
            <div>ESQUEMA GERENTE</div>
            <div class="font-bold text-3xl">Comision Obtenida : ${{number_format($balance_gerente->comision_final)}}</div>
                <div class="flex flex-row space-x-8">
                    <div class="flex flex-col content-center bg-gradient-to-br from-blue-600 to-green-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                        <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Activaciones</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="text-3xl font-bold">{{number_format($balance_gerente->uds_activacion)}}</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Cuota: {{number_format($balance_gerente->cuota_activacion)}}</div> 
                            <div class="font-bold text-base">Alcance Directo: {{$balance_gerente->alcance_activacion*100}}%</div>
                        </div> 
                        <div class="text-base font-bold">Alcance Otorgado por Esquema: {{number_format($balance_gerente->porc_cierre_activacion*100)}}%</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Comision Directa: ${{number_format($balance_gerente->comision_directa_activacion)}}</div> 
                            <div class="font-bold text-base">Comision Final: ${{number_format($balance_gerente->comision_final_activacion)}}</div>
                        </div>
                    </div>
                    <div class="flex flex-col content-center bg-gradient-to-br from-pink-600 to-yellow-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Act Eq Propio</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="text-3xl font-bold">{{number_format($balance_gerente->uds_aep)}}</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Cuota: {{number_format($balance_gerente->cuota_aep)}}</div> 
                            <div class="font-bold text-base">Alcance Directo: {{$balance_gerente->alcance_aep*100}}%</div>
                        </div> 
                        <div class="text-base font-bold">Alcance Otorgado por Esquema: {{number_format($balance_gerente->porc_cierre_aep*100)}}%</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Comision Directa: ${{number_format($balance_gerente->comision_directa_aep)}}</div> 
                            <div class="font-bold text-base">Comision Final: ${{number_format($balance_gerente->comision_final_aep)}}</div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-row space-x-8">
                    <div class="flex flex-col content-center bg-gradient-to-br from-purple-700 to-pink-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Renovaciones</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="text-3xl font-bold">{{number_format($balance_gerente->uds_renovacion)}}</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Cuota: {{number_format($balance_gerente->cuota_renovacion)}}</div> 
                            <div class="font-bold text-base">Alcance Directo: {{$balance_gerente->alcance_renovacion*100}}%</div>
                        </div> 
                        <div class="text-base font-bold">Alcance Otorgado por Esquema: {{number_format($balance_gerente->porc_cierre_renovacion*100)}}%</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Comision Directa: ${{number_format($balance_gerente->comision_directa_renovacion)}}</div> 
                            <div class="font-bold text-base">Comision Final: ${{number_format($balance_gerente->comision_final_renovacion)}}</div>
                        </div>
                    </div>
                    <div class="flex flex-col content-center bg-gradient-to-r from-yellow-400 to-yellow-700 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                        <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Ren Eq Propio</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="text-3xl font-bold">{{number_format($balance_gerente->uds_rep)}}</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Cuota: {{number_format($balance_gerente->cuota_rep)}}</div> 
                            <div class="font-bold text-base">Alcance Directo: {{$balance_gerente->alcance_rep*100}}%</div>
                        </div> 
                        <div class="text-base font-bold">Alcance Otorgado por Esquema: {{number_format($balance_gerente->porc_cierre_rep*100)}}%</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Comision Directa: ${{number_format($balance_gerente->comision_directa_rep)}}</div> 
                            <div class="font-bold text-base">Comision Final: ${{number_format($balance_gerente->comision_final_rep)}}</div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-row space-x-8">
                    <div class="flex flex-col content-center bg-gradient-to-br from-green-700 to-green-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Otros</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="font-bold text-base">Comision Seguros: ${{number_format($balance_gerente->comision_final_seguro)}}</div> 
                        <div class="font-bold text-base">Comision Add-ons: ${{number_format($balance_gerente->comision_final_addon)}}</div> 
                    </div>
                </div>






            </div>
    </div>

    <div class="flex justify-center text-xs font-bold px-3 ">
        <table class="lg:w-7/12 md:w-9/12 w-full" border=1>
                    
                    <?php
                        $transacciones_venta=App\Models\Transaccion::where('calculo_id',$id_calculo)
                                                                ->where('udn',$balance_gerente->udn)
                                                                //->where('credito',1)
                                                                ->orderBy('tipo_venta','asc')
                                                                ->orderBy('fecha','asc')
                                                                ->get();
                        $act_ren=$transacciones_venta->whereIn('tipo_venta',['Activación',
                                                                             'Renovación',
                                                                             'Activacion',
                                                                             'Renovacion',
                                                                             'Activación Equipo Propio',
                                                                             'Activacion Equipo Propio',
                                                                             'Renovación Equipo Propio',
                                                                             'Renovacion Equipo Propio',
                                                                             'Renovación Empresarial',
                                                                             'Renovacion Empresarial',
                                                                             'Activación Empresarial',
                                                                             'Activacion Empresarial'
                                                                            ]);
                        $seguros=$transacciones_venta->whereIn('tipo_venta',['Proteccion de equipo',
                                                                            'Protección de equipo'
                                                                           ]);
                        $addons=$transacciones_venta->whereIn('tipo_venta',['ADD ON']);
                    
                    $tipo_actual="";
                    $color=false;
                    $comision_parcial=0;
                    foreach($act_ren as $mov_principal)
                        {
                            if($mov_principal->tipo_venta!=$tipo_actual)
                            {
                                $color=false;
                    ?>
                            <tr><td colspan="7" class="px-3 py-3 bg-gradient-to-br from-blue-900 to-blue-500 text-white text-xl  font-bold">{{$mov_principal->tipo_venta}}</td></tr>
                            <tr>
                                        <td class="px-3 py-2 bg-gray-300 font-bold">Sucursal</td>
                                        <td class="px-3 py-2 bg-gray-300 font-bold">Pedido</td>
                                        <td class="px-3 py-2 bg-gray-300 font-bold">Contrato</td>
                                        <td class="px-3 py-2 bg-gray-300 font-bold">Servicio</td>
                                        <td class="px-3 py-2 bg-gray-300 font-bold"><center>Equipo s/c</center></td>
                                        <td class="px-3 py-2 bg-gray-300 font-bold">Importe</td>
                                        <td class="px-3 py-2 bg-gray-300 font-bold">Comision</td>
                                    </tr>
                    <?php
                            }
                    ?>
                            <tr>
                                <td class="px-3 pt-2 text-gray-900 font-thin text-xs {{$color?'bg-blue-100':''}}">{{$mov_principal->pdv}}</td>
                                <td class="px-3 pt-2 text-gray-900 font-thin text-sm {{$color?'bg-blue-100':''}}">{{$mov_principal->pedido}}</td>
                                <td class="px-3 pt-2 text-gray-900 font-thin text-sm {{$color?'bg-blue-100':''}}">{{$mov_principal->contrato}}</td>
                                <td class="px-3 pt-2 text-gray-900 font-thin text-sm w-5/12 {{$color?'bg-blue-100':''}}">{{$mov_principal->servicio}}<br><span class="text-xs font-thin italic text-red-700">{{$mov_principal->razon_cr0}}</span></td>
                                <td class="px-3 pt-2 text-gray-900 font-thin text-green-500 text-lg {{$color?'bg-blue-100':''}}">
                    <?php
                        if($mov_principal->eq_sin_costo) {
                    ?>
                                    <center><i class="fas fa-check-circle"></i></center>
                    <?php
                        }
                    ?>
                                </td>
                                <td class="text-gray-900 font-thin text-sm px-3 pt-2 text-gray-900 font-thin {{$color?'bg-blue-100':''}}">$ {{$mov_principal->importe}}</td>
                                <td class="text-gray-900 font-thin text-sm px-3 pt-2 font-bold {{$color?'bg-blue-100':''}}"><center>$ {{$mov_principal->comision_supervisor_l1}}</center></td>
                            </tr>
                    <?php
                        if($seguros->contains('pedido',$mov_principal->pedido))
                        {
                            $seguro_plan=$seguros->where('pedido',$mov_principal->pedido)->first();
                    ?>
                            <tr>
                                <td class="px-3 {{$color?'bg-blue-100':''}}"></td>
                                <td class="px-3 {{$color?'bg-blue-100':''}}"></td>
                                <td class="px-3 {{$color?'bg-blue-100':''}}"></td>
                                <td class="px-3 text-xs italic {{$color?'bg-blue-100':''}}">+ {{$seguro_plan->servicio}}</td>
                                <td class="px-3 text-xs italic {{$color?'bg-blue-100':''}}"></td>
                                <td class="{{$color?'bg-blue-100':''}}"></td>
                                <td class="text-gray-900 font-thin text-sm px-3 pt-2 font-bold {{$color?'bg-blue-100':''}}"><center>+ $ {{$seguro_plan->comision_supervisor_l1}}</center></td>
                            </tr>
                            <tr>
                                <td colspan=5 class="{{$color?'bg-blue-100':''}}"></td>
                                <td class="{{$color?'bg-blue-100':''}} px-3 py-2 font-bold">Total</td>
                                <td class="px-3 py-2 font-bold {{$color?'bg-blue-100':''}}"><center>$ {{$seguro_plan->comision_supervisor_l1+$mov_principal->comision_supervisor_l1}}</center></td>
                            </tr>
                    
                    
                    <?php
                    
                        }
                        $tipo_actual=$mov_principal->tipo_venta;
                        $color=!$color;
                        }
                        $color=false;
                    ?>
                    
                            <tr><td colspan="7" class="px-3 py-3 bg-gradient-to-br from-blue-900 to-blue-500 text-white text-xl  font-bold">Servicios Adicionales</td></tr>
                            <tr>
                                        <td class="px-3 py-2 bg-gray-300 font-bold">Sucursal</td>
                                        <td class="px-3 py-2 bg-gray-300 font-bold">Pedido</td>
                                        <td class="px-3 py-2 bg-gray-300 font-bold">Contrato</td>
                                        <td class="px-3 py-2 bg-gray-300 font-bold">Servicio</td>
                                        <td class="px-3 py-2 bg-gray-300 font-bold"><center>Equipo s/c</center></td>
                                        <td class="px-3 py-2 bg-gray-300 font-bold">Importe</td>
                                        <td class="px-3 py-2 bg-gray-300 font-bold">Comision</td>
                                    </tr>
                    <?php
                    foreach($addons as $addon)
                        {
                    ?>
                            <tr>
                                <td class="text-gray-900 font-thin text-xs px-3 {{$color?'bg-blue-100':''}}">{{$addon->pdv}}</td>
                                <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}">{{$addon->pedido}}</td>
                                <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}">{{$addon->contrato}}</td>
                                <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}">{{$addon->servicio}}<br><span class="text-xs font-thin italic text-red-700">{{$addon->razon_cr0}}</span></td>
                                <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}"></td>
                                <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}">$ {{$addon->importe}}</td>
                                <td class="text-gray-900 font-thin text-sm px-3 pt-2 font-bold {{$color?'bg-blue-100':''}}"><center>$ {{$addon->comision_supervisor_l1}}</center></td>
                            </tr>
                    <?php
                            $color=!$color;
                        }
                    ?>
        </table>
   </div> 
   <div class="flex justify-center text-xl font-bold px-3 pt-5">
    EXPORTAR TRANSACCIONES&nbsp;&nbsp;&nbsp;<span class="text-green-700"><a href="{{route('transacciones_sucursal',['id' => $id_calculo,'udn'=> $balance_gerente->udn])}}"><i class="far fa-file-excel"></i></a></span>
   </div>



<?php
        }        
    }
    if($vista_regional){

    $balance_gerente_tdas=App\Models\BalanceComisionRegional::where('calculo_id',$id_calculo)
        ->where('numero_empleado',$id_empleado)
        ->get();
        foreach($balance_gerente_tdas as $balance_gerente)
        {
?>
    <div class="flex flex-col content-justify py-3 px-3 space-y-3 space-x-0 lg:space-y-0 md:space-y-0 lg:px-8 md:px-8 lg:py-8 md:py-8 lg:space-x-5 md:space-x-5">
            <div class="flex flex-col space-y-2 w-full">
            <div>ESQUEMA REGIONAL</div>
            <div class="font-bold text-3xl">Comision Obtenida : ${{number_format($balance_gerente->comision_final)}}</div>
                <div class="flex flex-row space-x-8">
                    <div class="flex flex-col content-center bg-gradient-to-br from-blue-600 to-green-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                        <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Activaciones</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="text-3xl font-bold">{{number_format($balance_gerente->uds_activacion)}}</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Cuota: {{number_format($balance_gerente->cuota_activacion)}}</div> 
                            <div class="font-bold text-base">Alcance Directo: {{$balance_gerente->alcance_activacion*100}}%</div>
                        </div> 
                        <div class="text-base font-bold">Alcance Otorgado por Esquema: {{number_format($balance_gerente->porc_cierre_activacion*100)}}%</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Comision Directa: ${{number_format($balance_gerente->comision_directa_activacion)}}</div> 
                            <div class="font-bold text-base">Comision Final: ${{number_format($balance_gerente->comision_final_activacion)}}</div>
                        </div>
                    </div>
                    <div class="flex flex-col content-center bg-gradient-to-br from-pink-600 to-yellow-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Act Eq Propio</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="text-3xl font-bold">{{number_format($balance_gerente->uds_aep)}}</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Cuota: {{number_format($balance_gerente->cuota_aep)}}</div> 
                            <div class="font-bold text-base">Alcance Directo: {{$balance_gerente->alcance_aep*100}}%</div>
                        </div> 
                        <div class="text-base font-bold">Alcance Otorgado por Esquema: {{number_format($balance_gerente->porc_cierre_aep*100)}}%</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Comision Directa: ${{number_format($balance_gerente->comision_directa_aep)}}</div> 
                            <div class="font-bold text-base">Comision Final: ${{number_format($balance_gerente->comision_final_aep)}}</div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-row space-x-8">
                    <div class="flex flex-col content-center bg-gradient-to-br from-purple-700 to-pink-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Renovaciones</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="text-3xl font-bold">{{number_format($balance_gerente->uds_renovacion)}}</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Cuota: {{number_format($balance_gerente->cuota_renovacion)}}</div> 
                            <div class="font-bold text-base">Alcance Directo: {{$balance_gerente->alcance_renovacion*100}}%</div>
                        </div> 
                        <div class="text-base font-bold">Alcance Otorgado por Esquema: {{number_format($balance_gerente->porc_cierre_renovacion*100)}}%</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Comision Directa: ${{number_format($balance_gerente->comision_directa_renovacion)}}</div> 
                            <div class="font-bold text-base">Comision Final: ${{number_format($balance_gerente->comision_final_renovacion)}}</div>
                        </div>
                    </div>
                    <div class="flex flex-col content-center bg-gradient-to-r from-yellow-400 to-yellow-700 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                        <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Ren Eq Propio</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="text-3xl font-bold">{{number_format($balance_gerente->uds_rep)}}</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Cuota: {{number_format($balance_gerente->cuota_rep)}}</div> 
                            <div class="font-bold text-base">Alcance Directo: {{$balance_gerente->alcance_rep*100}}%</div>
                        </div> 
                        <div class="text-base font-bold">Alcance Otorgado por Esquema: {{number_format($balance_gerente->porc_cierre_rep*100)}}%</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Comision Directa: ${{number_format($balance_gerente->comision_directa_rep)}}</div> 
                            <div class="font-bold text-base">Comision Final: ${{number_format($balance_gerente->comision_final_rep)}}</div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-row space-x-8">
                    <div class="flex flex-col content-center bg-gradient-to-br from-green-700 to-green-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Otros</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="font-bold text-base">Comision Seguros: ${{number_format($balance_gerente->comision_final_seguro)}}</div> 
                        <div class="font-bold text-base">Comision Add-ons: ${{number_format($balance_gerente->comision_final_addon)}}</div> 
                    </div>
                </div>
            </div>
            <div class="flex justify-center text-xs font-bold px-3 py-5">
                <table class="lg:w-7/12 md:w-9/12 w-full" border=1>
                <tr><td colspan="9" class="px-3 py-3 bg-gradient-to-br from-blue-900 to-blue-500 text-white text-xl  font-bold">Sucursales</td></tr>
                <tr>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Sucursal</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Activaciones</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">AEP</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Renovaciones</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">REP</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Seguros</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Add-ons</td>
                </tr>
<?php
            $tiendas_region=Illuminate\Support\Facades\DB::select(Illuminate\Support\Facades\DB::raw(
            "
            select * from (
            select udn,pdv,
                sum(a.u_act) as u_act,
                sum(a.u_aep) as u_aep,
                sum(a.u_ren) as u_ren,
                sum(a.u_rep) as u_rep,
                sum(a.u_seg) as u_seg,
                sum(a.u_add) as u_add
                from (
                SELECT udn,pdv,COUNT(tipo_venta) as u_act,
                0 as u_aep,
                0 as u_ren,
                0 as u_rep,
                0 as u_seg,
                0 as u_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Activación' OR tipo_venta='Activacion')
                group by udn,pdv
                UNION
                SELECT udn,pdv,0 as u_act,
                COUNT(tipo_venta) as u_aep,
                0 as u_ren,
                0 as u_rep,
                0 as u_seg,
                0 as u_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Activación Equipo Propio' OR tipo_venta='Activacion Equipo Propio')
                group by udn,pdv
                UNION
                SELECT udn,pdv,0 as u_act,
                0 as u_aep,
                COUNT(tipo_venta) as u_ren,
                0 as u_rep,
                0 as u_seg,
                0 as u_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Renovación' OR tipo_venta='Renovacion')
                group by udn,pdv
                UNION
                SELECT udn,pdv,0 as u_act,
                0 as u_aep,
                0 as u_ren,
                COUNT(tipo_venta) as u_rep,
                0 as u_seg,
                0 as u_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Renovación Equipo Propio' OR tipo_venta='Renovacion Equipo Propio')
                group by udn,pdv
                UNION
                SELECT udn,pdv,0 as u_act,
                0 as u_aep,
                0 as u_ren,
                0 as u_rep,
                COUNT(tipo_venta) as u_seg,
                0 as u_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='Protección de equipo' OR tipo_venta='Proteccion de equipo')
                group by udn,pdv
                UNION
                SELECT udn,pdv,0 as u_act,
                0 as u_aep,
                0 as u_ren,
                0 as u_rep,
                0 as u_seg,
                COUNT(tipo_venta) as u_add
                FROM transaccions where (credito=1 or credito=0) AND calculo_id='$id_calculo' AND (tipo_venta='ADD ON' OR tipo_venta='ADD ON')
                group by udn,pdv
                ) as a group by a.udn,a.pdv
    ) as z where z.udn in (select udn from cuotas where regional='$id_empleado')
    "
                ));
        $color=true;
        foreach($tiendas_region as $tienda)
        {
?>  
                            <tr>
                                <td class="text-gray-900 font-thin text-xs px-3 {{$color?'bg-blue-100':''}}"><center>{{$tienda->pdv}}</td>
                                <td class="text-gray-900 font-thin text-xs px-3 {{$color?'bg-blue-100':''}}"><center>{{$tienda->u_act}}</td>
                                <td class="text-gray-900 font-thin text-xs px-3 {{$color?'bg-blue-100':''}}"><center>{{$tienda->u_aep}}</td>
                                <td class="text-gray-900 font-thin text-xs px-3 {{$color?'bg-blue-100':''}}"><center>{{$tienda->u_ren}}</td>
                                <td class="text-gray-900 font-thin text-xs px-3 {{$color?'bg-blue-100':''}}"><center>{{$tienda->u_rep}}</td>
                                <td class="text-gray-900 font-thin text-xs px-3 {{$color?'bg-blue-100':''}}"><center>{{$tienda->u_seg}}</td>
                                <td class="text-gray-900 font-thin text-xs px-3 {{$color?'bg-blue-100':''}}"><center>{{$tienda->u_add}}</td>
                            </tr>
<?php
                $color=!$color;
        }
?>
            </table>

        </div><!--LISTADO TIENDAS-->

    </div>
<?php
        } //EL DEL FOREACH
    }
    if($vista_director)
    {

        $balance_gerente_tdas=App\Models\BalanceComisionDirector::where('calculo_id',$id_calculo)
        ->where('numero_empleado',$id_empleado)
        ->get();
        foreach($balance_gerente_tdas as $balance_gerente)
        {
?>
    <div class="flex flex-col content-justify py-3 px-3 space-y-3 space-x-0 lg:space-y-0 md:space-y-0 lg:px-8 md:px-8 lg:py-8 md:py-8 lg:space-x-5 md:space-x-5">
            <div class="flex flex-col space-y-2 w-full">
            <div>ESQUEMA DIRECTOR</div>
            <div class="font-bold text-3xl">Comision Obtenida : ${{number_format($balance_gerente->comision_final)}}</div>
                <div class="flex flex-row space-x-8">
                    <div class="flex flex-col content-center bg-gradient-to-br from-blue-600 to-green-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                        <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Activaciones</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="text-3xl font-bold">{{number_format($balance_gerente->uds_activacion)}}</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Cuota: {{number_format($balance_gerente->cuota_activacion)}}</div> 
                            <div class="font-bold text-base">Alcance Directo: {{$balance_gerente->alcance_activacion*100}}%</div>
                        </div> 
                        <div class="text-base font-bold">Alcance Otorgado por Esquema: {{number_format($balance_gerente->porc_cierre_activacion*100)}}%</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Comision Directa: ${{number_format($balance_gerente->comision_directa_activacion)}}</div> 
                            <div class="font-bold text-base">Comision Final: ${{number_format($balance_gerente->comision_final_activacion)}}</div>
                        </div>
                    </div>
                    <div class="flex flex-col content-center bg-gradient-to-br from-pink-600 to-yellow-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Act Eq Propio</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="text-3xl font-bold">{{number_format($balance_gerente->uds_aep)}}</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Cuota: {{number_format($balance_gerente->cuota_aep)}}</div> 
                            <div class="font-bold text-base">Alcance Directo: {{$balance_gerente->alcance_aep*100}}%</div>
                        </div> 
                        <div class="text-base font-bold">Alcance Otorgado por Esquema: {{number_format($balance_gerente->porc_cierre_aep*100)}}%</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Comision Directa: ${{number_format($balance_gerente->comision_directa_aep)}}</div> 
                            <div class="font-bold text-base">Comision Final: ${{number_format($balance_gerente->comision_final_aep)}}</div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-row space-x-8">
                    <div class="flex flex-col content-center bg-gradient-to-br from-purple-700 to-pink-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Renovaciones</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="text-3xl font-bold">{{number_format($balance_gerente->uds_renovacion)}}</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Cuota: {{number_format($balance_gerente->cuota_renovacion)}}</div> 
                            <div class="font-bold text-base">Alcance Directo: {{$balance_gerente->alcance_renovacion*100}}%</div>
                        </div> 
                        <div class="text-base font-bold">Alcance Otorgado por Esquema: {{number_format($balance_gerente->porc_cierre_renovacion*100)}}%</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Comision Directa: ${{number_format($balance_gerente->comision_directa_renovacion)}}</div> 
                            <div class="font-bold text-base">Comision Final: ${{number_format($balance_gerente->comision_final_renovacion)}}</div>
                        </div>
                    </div>
                    <div class="flex flex-col content-center bg-gradient-to-r from-yellow-400 to-yellow-700 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                        <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Ren Eq Propio</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="text-3xl font-bold">{{number_format($balance_gerente->uds_rep)}}</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Cuota: {{number_format($balance_gerente->cuota_rep)}}</div> 
                            <div class="font-bold text-base">Alcance Directo: {{$balance_gerente->alcance_rep*100}}%</div>
                        </div> 
                        <div class="text-base font-bold">Alcance Otorgado por Esquema: {{number_format($balance_gerente->porc_cierre_rep*100)}}%</div>
                        <div class="flex flex-row space-x-3">
                            <div class="font-bold text-base">Comision Directa: ${{number_format($balance_gerente->comision_directa_rep)}}</div> 
                            <div class="font-bold text-base">Comision Final: ${{number_format($balance_gerente->comision_final_rep)}}</div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-row space-x-8">
                    <div class="flex flex-col content-center bg-gradient-to-br from-green-700 to-green-300 text-white rounded-lg py-3 px-3 w-1/2 md:w-full lg:w-full">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                            <div class="w-5/6 font-bold">Otros</div><div class="text-sm font-bold"></div> 
                        </div> 
                        <div class="font-bold text-base">Comision Seguros: ${{number_format($balance_gerente->comision_final_seguro)}}</div> 
                        <div class="font-bold text-base">Comision Add-ons: ${{number_format($balance_gerente->comision_final_addon)}}</div> 
                    </div>
                </div>
            </div>
    </div>
<?php
        } //EL DEL FOREACH
    }
?>

    <div class="flex flex-col content-justify py-3 px-3 space-y-3 space-x-0 lg:space-y-0 md:space-y-0 lg:px-8 md:px-8 lg:py-8 md:py-8lg:space-x-5 md:space-x-5">
            <div class="flex flex-col space-y-2 w-full lg:w-8/12 md:w-8/12">
                <div class="text-3xl text-red-700 font-bold">CHARGE-BACK</div>
            </div>
            <div class="flex justify-center text-xs font-bold px-3 ">
                <table class="lg:w-7/12 md:w-9/12 w-full" border=1>
                <tr><td colspan="9" class="px-3 py-3 bg-gradient-to-br from-red-900 to-yellow-500 text-white text-xl  font-bold">Charge-Back</td></tr>
        <tr>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Origen</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Fecha</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Servicio</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Importe</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Contrato</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Venta</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Tienda</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Rol</td>
                    <td class="px-3 py-2 bg-gray-300 font-bold">Charge-Back</td>
                </tr>
<?php
                    $color=true;
                    $cbs=App\Models\ChargeBackInterno::where('numero_empleado',$id_empleado)
                        ->where('calculo_id',$id_calculo)
                        ->get();
                    foreach($cbs as $cb)
                        {
?>
                            <tr>
                                <td class="text-gray-900 font-thin text-xs px-3 {{$color?'bg-blue-100':''}}">{{$cb->pagado_en}}</td>
                                <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}">{{$cb->fecha}}</td>
                                <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}">{{$cb->servicio}}</td>
                                <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}"><center>${{number_format($cb->importe)}}</td>
                                <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}">{{$cb->contrato}}</td>
                                <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}">{{$cb->tipo_venta}}</td>
                                <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}">{{$cb->pdv}}</td>
                                <td class="text-gray-900 font-thin text-sm px-3 {{$color?'bg-blue-100':''}}">{{$cb->rol}}</td>
                                <td class="text-gray-900 font-bold text-sm text-red-700 px-3 {{$color?'bg-blue-100':''}}"><center>${{number_format($cb->cb)}}</td>
                            </tr>
<?php
                            $color=!$color;
                        }
?>


                </table>
            </div>
    </div>
<?php
}
?>
</body>
