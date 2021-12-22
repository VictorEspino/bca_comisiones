<x-app-layout>
    <x-slot name="header">
         {{ __('Principal') }}
    </x-slot>
    <div class="text-xl text-gray-700 flex flex-col">
        <div class="">{{$titulo}}</div>
        <div class="text-sm">Ventas del {{$fecha_inicio}} al {{$fecha_fin}}</div>
        <div class="text-sm">Pagado el {{$fecha_pago}}</div>
        <div class="w-full flex justify-center pt-10">
        <table>
                    <tr class="">
                        <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"></td>
                        <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>Distribuidor</td>
                        <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>Total</td>       
                        <td class=""><center>&nbsp</td>   
                        <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>CLABE</td> 
                        <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>Titular</td> 
                        <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>PDF</td> 
                        <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>XML</td> 
                    </tr>
                <?php
                    $color=false;
                    foreach($query as $registro)
                    {
                ?>
                    <tr class="">
                        <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm"><center><a href="/estado_cuenta_distribuidor/{{$id}}/{{$registro->numero_distribuidor}}"><i class="far fa-edit"></i></a></td>
                        <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">{{$registro->distribuidor}}</td>
                        <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">${{number_format($registro->a_pagar,2)}}</td>
                        <td class=""><center>&nbsp</td> 
                        <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">{{$registro->clabe}}</td>
                        <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">{{$registro->titular}}</td>
                        <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm"><center>
                            @if(!is_null($registro->pdf))
                            <a href="/facturas/{{$registro->pdf}}" download>
                                <i class="text-2xl text-red-700 far fa-file-pdf"></i>
                            </a>
                            @endif
                        </td>
                        <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm"><center>
                            @if(!is_null($registro->xml))
                            <a href="/facturas/{{$registro->xml}}" download>
                                <i class="text-2xl text-blue-600 far fa-file-code"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                <?php
                    $color=!$color;
                    }
                ?>
                </table>
        </div>
        <div class="pt-5">FACTURAS OTROS PERIODOS (Cargadas en periodo actual de pago)</div>
        <div class="w-full flex justify-center pt-5">
            <table>
                <tr class="">
                    <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"></td>
                    <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>Distribuidor</td>
                    <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>Total</td>       
                    <td class=""><center>&nbsp</td>   
                    <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>CLABE</td> 
                    <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>Titular</td> 
                    <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>PDF</td> 
                    <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>XML</td> 
                </tr>
            <?php
                $color=false;
                foreach($atrasados as $registro)
                {
            ?>
                <tr class="">
                    <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm"><center><a href="/estado_cuenta_distribuidor/{{$registro->calculo_id}}/{{$registro->numero_distribuidor}}"><i class="far fa-edit"></i></a></td>
                    <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">{{$registro->distribuidor}}<br>{{$calculos_historicos->where('id',$registro->calculo_id)->first()->descripcion}}</td>
                    <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">${{number_format($registro->a_pagar,2)}}</td>
                    <td class=""><center>&nbsp</td> 
                    <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">{{$registro->clabe}}</td>
                    <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">{{$registro->titular}}</td>
                    <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm"><center>
                        @if(!is_null($registro->pdf))
                        <a href="/facturas/{{$registro->pdf}}" download>
                            <i class="text-2xl text-red-700 far fa-file-pdf"></i>
                        </a>
                        @endif
                    </td>
                    <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm"><center>
                        @if(!is_null($registro->xml))
                        <a href="/facturas/{{$registro->xml}}" download>
                            <i class="text-2xl text-blue-600 far fa-file-code"></i>
                        </a>
                        @endif
                    </td>
                </tr>
            <?php
                $color=!$color;
                }
            ?>
            </table>
        </div>       
        <div class="pt-5">PAGO CANCELADO (No se facturo, se incluye cantidad en pago mensual)</div>
        <div class="w-full flex justify-center pt-5">
            <table>
                <tr class="">
                    <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"></td>
                    <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>Distribuidor</td>
                    <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>Total</td>       
                    <td class=""><center>&nbsp</td>   
                    <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>CLABE</td> 
                    <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>Titular</td> 
                    <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>PDF</td> 
                    <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>XML</td> 
                </tr>
            <?php
                $color=false;
                foreach($cancelados as $registro)
                {
            ?>
                <tr class="">
                    <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm"><center><a href="/estado_cuenta_distribuidor/{{$registro->calculo_id}}/{{$registro->numero_distribuidor}}"><i class="far fa-edit"></i></a></td>
                    <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">{{$registro->distribuidor}}</td>
                    <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">${{number_format($registro->a_pagar,2)}}</td>
                    <td class=""><center>&nbsp</td> 
                    <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">{{$registro->clabe}}</td>
                    <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">{{$registro->titular}}</td>
                    <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm"><center>
                        @if(!is_null($registro->pdf))
                        <a href="/facturas/{{$registro->pdf}}" download>
                            <i class="text-2xl text-red-700 far fa-file-pdf"></i>
                        </a>
                        @endif
                    </td>
                    <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm"><center>
                        @if(!is_null($registro->xml))
                        <a href="/facturas/{{$registro->xml}}" download>
                            <i class="text-2xl text-blue-600 far fa-file-code"></i>
                        </a>
                        @endif
                    </td>
                </tr>
            <?php
                $color=!$color;
                }
            ?>
            </table>
        </div>       
    </div>
</x-app-layout>
