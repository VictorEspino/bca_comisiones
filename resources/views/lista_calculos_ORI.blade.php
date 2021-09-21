<x-app-layout>
    <x-slot name="header">
         {{ __('Calculos Ejecutados') }}
    </x-slot>

    <div class="flex flex-1  flex-col md:flex-row lg:flex-row mx-2">
                        <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                            <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                                <p class="text-grey-dark text-xl font-bold">
                                    Calculos Historicos
                                </p>
                            </div>
                            <div class="p-3">
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr class="bg-gradient-to-br from-blue-700 to-green-300 text-white">
                                        <th class="border px-4 py-2">Descripcion</th>
                                        <th class="border px-4 py-2">Desde</th>
                                        <th class="border px-4 py-2">Hasta</th>
                                        <th class="border px-4 py-2">Cuotas</th>
                                        <th class="border px-4 py-2">Ejecutivos CC</th>
                                        <th class="border px-4 py-2">Equipos sin Costo</th>
                                        <th class="border px-4 py-2"></th>
                                      </tr>
                                    </thead>
                                    <tbody>

<?php
    $calculos=App\Models\Calculo::orderBy('id','desc')
                ->get();
    foreach ($calculos as $calculo) {
?>
                                        <tr>
                                            <td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$calculo->descripcion}}</p></td>
                                            <td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$calculo->fecha_inicio}}</p></td>
                                            <td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$calculo->fecha_fin}}</p></td>
                                            <td class="border px-4 py-2"><center>
                                        <?php
                                            if($calculo->cuotas==1)
                                            {
                                        ?>
                                            <i class="fas fa-check" style='font-size:20px;color:green'></i>
                                        <?php        
                                            }
                                        ?>
                                            </center>    
                                            </td>
                                            <td class="border px-4 py-2"><center>
                                        <?php
                                            if($calculo->cc==1)
                                            {
                                        ?>
                                            <i class="fas fa-check" style='font-size:20px;color:green'></i>
                                        <?php        
                                            }

                                        ?>

                                            </center>
                                            </td>
                                            <td class="border px-4 py-2"><center>
                                            <?php
                                            if($calculo->eq0==1)
                                            {
                                        ?>
                                            <i class="fas fa-check" style='font-size:20px;color:green'></i>
                                        <?php          
                                            }
                                        ?>
                                            </center>
                                            </td>
                                            <td class="border px-4 py-2">
                                                <center>
                                                    <a href="{{route('detalle_calculo',$calculo->id)}}">
                                                        <i class="fas fa-bars" style='font-size:20px;color:red'></i>
                                                    </a>
                                                </center>
                                            </td>
                                        </tr>
<?php
  }
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>



</x-app-layout>
