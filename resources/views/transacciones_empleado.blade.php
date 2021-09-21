<x-app-layout>
    <x-slot name="header">
         {{ __('Transacciones Empleado') }}
    </x-slot>

    <div class="flex flex-1  flex-col md:flex-row lg:flex-row mx-2">
                        <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                            <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                                <p class="text-grey-dark text-xl font-bold">
                                    Transacciones Empleado
                                </p>
                            </div>
                            <div class="p-3">
                                <table class="table-responsive w-full rounded">
                                    <thead>
                                      <tr class="bg-gray-700 text-white">
                                        <th class="border px-4 py-2"><p class="text-grey-dark text-xs bold">Pedido</p></th>
                                        <th class="border px-4 py-2"><p class="text-grey-dark text-xs bold">Fecha</p></th>
                                        <th class="border px-4 py-2"><p class="text-grey-dark text-xs bold">Sucursal</p></th>
                                        <th class="border px-4 py-2"><p class="text-grey-dark text-xs bold">Tipo</p></th>
                                        <th class="border px-4 py-2"><p class="text-grey-dark text-xs bold">Servicio</p></th>
                                        <th class="border px-4 py-2"><p class="text-grey-dark text-xs bold">Plazo</p></th>
                                        <th class="border px-4 py-2"><p class="text-grey-dark text-xs bold">Eq Sin $</p></th>
                                        <th class="border px-4 py-2"><p class="text-grey-dark text-xs bold">Importe</p></th>
                                        <th class="border px-4 py-2"><p class="text-grey-dark text-xs bold">Comision</p></th>
                                        <th class="border px-4 py-2"><p class="text-grey-dark text-xs bold">Gerente</p></th>
                                      </tr>
                                    </thead>
                                    <tbody>

<?php
    $transacciones=App\Models\Transaccion::where('calculo_id',$id_calculo)
                ->where('numero_empleado',$id_empleado)
                ->where('credito',1)
                ->orderBy('pedido')
                ->get();
    $color=true;
    $pedido_ant='';
    foreach ($transacciones as $transaccion) {
        if($transaccion->pedido!=$pedido_ant)
        {
            $color=!$color;
        }
?>
                                        <tr class="{{$color ? '' : 'bg-blue-100'}}">
                                            <td class="border px-2 py-1"><center><p class="text-grey-dark text-xs">{{$transaccion->pedido}}</p></td>
                                            <td class="border px-2 py-1"><center><p class="text-grey-dark text-xs">{{$transaccion->fecha}}</p></td>
                                            <td class="border px-2 py-1"><center><p class="text-grey-dark text-xs">{{$transaccion->pdv}}</p></td>
                                            <td class="border px-2 py-1"><center><p class="text-grey-dark text-xs">{{$transaccion->tipo_venta}}</p></td>
                                            <td class="border px-2 py-1"><p class="text-grey-dark text-xs">{{$transaccion->servicio}}</p></td>
                                            <td class="border px-2 py-1"><center><p class="text-grey-dark text-xs">{{$transaccion->plazo}}</p></td>
                                            <td class="border px-2 py-1"><center><p class="text-grey-dark text-xs">
                                    <?php
                                            if($transaccion->eq_sin_costo)
                                            {
                                    ?>
                                                <i class="fas fa-check" style='font-size:20px;color:green'></i>
                                    <?php
                                            }

                                    ?>
                                                
                                            </p></td> 
                                            <td class="border px-2 py-1"><center><p class="text-grey-dark text-xs">${{money_format('%i',$transaccion->importe)}}</p></td>
                                            <td class="border px-2 py-1 bg-blue-500" ><center><p class="text-white text-xs font-bold">${{money_format('%i',$transaccion->comision_venta)}}</p></td>
                                            <td class="border px-1 py-1 bg-green-500"><center><p class="text-white text-xs font-bold">${{money_format('%i',$transaccion->comision_supervisor_l1)}}</p></td>
                                        </tr>
<?php
    $pedido_ant=$transaccion->pedido;
  }
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>



</x-app-layout>