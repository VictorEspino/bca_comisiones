<x-app-layout>
    <x-slot name="header">
         {{ __('Detalles de Calculo') }}
    </x-slot>
    <div class="width-full flex flex-col px-3">
        <div class="text-2xl font-bold text-gray-700">
         {{$descripcion}}
        </div>
        <div class="text-xs font-thin text-gray-700 pb-5">
            Desde {{$fecha_inicio}} hasta {{$fecha_fin}}
        </div>
<?php
    if($terminado!="0")
    {
?>
        <div class="flex flex-col space-y-3 lg:space-x-10  lg:space-y-0  lg:flex-row md:flex-col pb-5">
            <div class="flex flex-row space-x-10  lg:w-1/2">
                <div class="w-1/2 flex flex-col bg-gradient-to-br from-blue-700 to-green-300 text-white rounded-lg py-3 px-3">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                        <div class="w-5/6 font-bold">Activaciones</div>
                    </div> 
                    <div class="text-3xl font-bold">{{number_format($tr_activaciones)}}</div>
                    <div class="flex flex-row">
                        <div class="font-bold text-xs">Rentas ${{number_format($tr_renta_activ)}}</div> 
                    </div> 
                </div>
                <div class="w-1/2 flex flex-col bg-gradient-to-br from-pink-600 to-yellow-300 text-white rounded-lg py-3 px-3">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                        <div class="w-5/6 font-bold">Act Eq Propio</div>
                    </div> 
                    <div class="text-3xl font-bold">{{number_format($tr_aep)}}</div>
                    <div class="flex flex-row">
                        <div class="font-bold text-xs">Rentas ${{number_format($tr_renta_aep)}}</div> 
                    </div> 
                </div>
            </div>
            <div class="flex flex-row space-x-10 lg:w-1/2">
                <div class="w-1/2 flex flex-col bg-gradient-to-br from-purple-700 to-pink-300 text-white rounded-lg py-3 px-3">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                        <div class="w-5/6 font-bold">Renovaciones</div>
                    </div> 
                    <div class="text-3xl font-bold">{{number_format($tr_renovaciones)}}</div>
                    <div class="flex flex-row">
                        <div class="font-bold text-xs">Rentas ${{number_format($tr_renta_renov)}}</div> 
                    </div> 
                </div>
                <div class="w-1/2 flex flex-col bg-gradient-to-r from-yellow-400 to-yellow-700 text-white rounded-lg py-3 px-3">
                    <div class="flex flex-col md:flex-row lg:flex-row">
                        <div class="w-5/6 font-bold">Ren Eq Propio</div>
                    </div> 
                    <div class="text-3xl font-bold">{{number_format($tr_rep)}}</div>
                    <div class="flex flex-row">
                        <div class="font-bold text-xs">Rentas ${{number_format($tr_renta_rep)}}</div> 
                    </div> 
                </div>
            </div>
        </div>
        <div class=" rounded-xl p-5 flex flex-col">
            <div class="text-xl font-bold text-gray-700 pb-6 text-center"><p>Balance de Calculo</p></div>
            <div class="flex flex-col lg:flex-row md:flex-row">
                <div class="w-full flex justify-center md:w-1/2 lg:w-1/2">
                    <table class="w-11/12 text-gray-700">
                        <tr>
                            <td class="py-4 border-l-8 border-blue-900 px-4 bg-blue-500 font-extrabold text-white">
                                Distribuidor
                                <div class="mx-3 text-sm italic font-thin">Resumen por distribuidor</div>
                            </td>
                            <td class="bg-blue-500 text-2xl text-white p-4">
                                <center><a href="{{route('balance_distribuidores',$id_calculo)}}"><i class="fas fa-cloud-download-alt"></i></a>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="w-full flex justify-center md:w-1/2 lg:w-1/2">
                    <table class="w-11/12 text-gray-700">
                        <tr>
                            <td class="py-4 border-l-8 border-blue-900 px-4 bg-blue-500 font-extrabold text-white">
                                Detalle de Transacciones
                                <div class="mx-3 text-sm italic font-thin">Ventas en detalle incluida su comision unitaria</div>
                            </td>
                            <td class="bg-blue-500 text-2xl p-4 text-white">
                                <center><a href="{{route('transacciones_distribuidores',$id_calculo)}}"><i class="fas fa-cloud-download-alt">DETALLE</i></a>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2 class="py-3"></td>
                        </tr>
                        <tr>
                            <td class="py-4 border-l-8 border-green-900 px-4 bg-green-500 font-extrabold text-white">
                                Payments
                                <div class="mx-3 text-sm italic font-thin">Archivo consolidado de pagos y cargos por distribuidor</div>
                            </td>
                            <td class="bg-green-500 text-2xl p-4 text-white">
                                <center><a href="{{route('pagos_distribuidores',$id_calculo)}}"><i class="fas fa-cloud-download-alt"></i></a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
<?php
    }
    else
    {
?>
        <div class="font-bold text-2xl text-red-700">EL CALCULO AUN TIENE ACCIONES POR CONCLUIR</div>
        <div class="font-thin text-sm">Si este es un calculo vigente, vaya a la seccion "Historial Calculos" de la barra de navegacion y termine las tareas pendientes.</div>
<?php
    }
?>
    </div>
</x-app-layout>