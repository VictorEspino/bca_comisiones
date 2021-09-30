<x-app-layout>
    <x-slot name="header">
         {{ __('Detalle de contrato') }}
    </x-slot>
    <main class="flex-1 p-1 overflow-hidden">
        <div class="flex flex-col">
            <!-- SECCION DE CONTENIDO-->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row lg:space-x-5 md:space-y-0 sm:space-y-3 lg:space-y-0">
                <!--SECCION DE CATURA DE NUEVOS PARAMETROS-->
                <div class="bg-white border-solid border-gray-300 rounded-lg border shadow-sm w-full md:w-1/3 lg:w-1/3">
                    <div class="text-white py-3 px-2 rounded-t-lg bg-gradient-to-br from-pink-600 to-yellow-300 boder-solid border-gray-300 border-b text-white font-bold">
                        <i class="fas fa-link"></i>
                        Contrato a consultar
                    </div>
                    <div class="p-3">
                        <form method="POST" class="w-full">
                            @csrf
                             <div class="flex flex-col -mx-3 mb-6">
                                <div class="w-full px-3 mb-6 md:mb-0">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1">
                                        Contrato
                                    </label>
                                    <div>
                                        <input class="block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600"
                                            name="contrato" type="text" placeholder="Especifique contrato" value="{{request('contrato')}}">
                                        <p class="text-red-500 text-xs italic">Campo obligatorio</p>
                                    </div>
                                    
                                </div>
                            </div>
                            <button class="bg-green-500 hover:bg-green-800 text-white font-bold py-2 px-4 rounded-full" type="submit">
                                Consultar
                            </button>
                        </form>
                    </div>
                </div>
                <!--TEMINA SECCION DE CAPTURA -->
                <!--INICIA SECCION DE AVANCES-->
                <div class="bg-white border-solid border-gray-300 rounded-lg border shadow-sm w-full md:w-2/3 lg:w-2/3">
                    <div class="text-white py-3 px-2 rounded-t-lg bg-gradient-to-br from-blue-700 to-green-300 boder-solid border-gray-300 border-b text-white font-bold">
                        <i class="fab fa-codepen"></i>
                        Detalles de contrato
                    </div>    
                    <div class="px-3 pt-3">
                        <span class="font-bold text-gray-700">Transaccion</span>
                        <table class="table-responsive w-full rounded">
                            <thead>
                            <tr class="bg-gradient-to-br from-gray-700 to-gray-300 text-white">
                                <th class="border px-4 py-1">Tipo</th>
                                <th class="border px-4 py-1">Servicio</th>
                                <th class="border px-4 py-1">Producto</th>
                                <th class="border px-4 py-1">Plazo</th>
                                <th class="border px-4 py-1">Importe</th>
                                <th class="border px-4 py-1">Fecha</th>
                            </tr>
                            </thead>
<?php
                $contratos=App\Models\Transaccion::where('contrato',request("contrato"))->get();
                foreach($contratos as $contrato)
                {
?>
                            <tr>
                                <td class="border px-4 py-1 text-sm font-thin text-gray-800">{{$contrato->tipo_venta}}</td>
                                <td class="border px-4 py-1 text-xs font-thin text-gray-800">{{$contrato->servicio}}</td>
                                <td class="border px-4 py-1 text-xs font-thin text-gray-800">{{$contrato->producto}}</td>
                                <td class="border px-4 py-1 text-sm font-thin text-gray-800"><center>{{$contrato->plazo}}</center></td>
                                <td class="border px-4 py-1 text-sm font-thin text-gray-800">${{number_format($contrato->importe)}}</td>
                                <td class="border px-4 py-1 text-sm font-thin text-gray-800">{{$contrato->fecha}}</td>
                            </tr>
<?php
                }
?>
                        </table><br>
                        <span class="font-bold text-gray-700">Residuales</span>
                        <table class="table-responsive w-full rounded">
                            <thead>
                            <tr class="bg-gradient-to-br from-green-700 to-green-300 text-white">
                                <th class="border px-4 py-1">Periodo</th>
                                <th class="border px-4 py-1">Plan</th>
                                <th class="border px-4 py-1">Comision</th>
                                <th class="border px-4 py-1">Estatus</th>
                                <th class="border px-4 py-1">Marca</th>
                            </tr>
                            </thead>
                            <?php
                $contratos=App\Models\Residual::where('contrato',request("contrato"))->get();
                foreach($contratos as $contrato)
                {
?>
                            <tr>
                                <td class="border px-4 py-1 text-sm font-thin text-gray-800">{{$contrato->periodo}}</td>
                                <td class="border px-4 py-1 text-xs font-thin text-gray-800">{{$contrato->plan}}</td>
                                <td class="border px-4 py-1 text-sm font-thin text-gray-800"><center>${{number_format($contrato->comision)}}</center></td>
                                <td class="border px-4 py-1 text-sm font-bold text-gray-800"><center>{{$contrato->estatus}}</center></td>
                                <td class="border px-4 py-1 text-sm font-thin text-gray-800">{{$contrato->marca}}</td>
                            </tr>
<?php
                }
?>
                        </table><br>
                        <span class="font-bold text-gray-700">Alertas</span>
                        <table class="table-responsive w-full rounded">
                            <thead>
                            <tr class="bg-gradient-to-br from-red-700 to-red-300 text-white">
                                <th class="border px-4 py-1">Periodo</th>
                                <th class="border px-4 py-1">Tipo</th>
                            </tr>
                            </thead>
                            <?php
                $contratos=App\Models\Alerta::where('contrato',request("contrato"))->get();
                foreach($contratos as $contrato)
                {
?>
                            <tr>
                                <td class="border px-4 py-1 text-sm font-thin text-gray-800">{{$contrato->periodo}}</td>
                                <td class="border px-4 py-1 text-sm font-bold text-gray-800">
                                    <?php
                                    if($contrato->tipo=='1')
                                     {echo 'Alerta Roja Fraude 1';}
                                     if($contrato->tipo=='12')
                                     {echo 'Alerta Amarilla Fraude 1';}
                                    if($contrato->tipo=='2')
                                     {echo 'Alerta Fraude 2';}
                                    if($contrato->tipo=='22')
                                     {echo 'Alerta Amarilla Fraude 2';}
                                    if($contrato->tipo=='3')
                                     {echo 'Alerta Roja Charge-Back';}
                                    if($contrato->tipo=='32')
                                     {echo 'Alerta Amarilla Charge-Back';}
                                    ?>


                                </td>
                            </tr>
<?php
                }
?>
                        </table><br>                           
                    </div>
                </div>
            </div>
    </main>
            <!--/Main-->

</x-app-layout>
