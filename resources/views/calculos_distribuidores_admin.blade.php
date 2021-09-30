<x-app-layout>
    <x-slot name="header">
         {{ __('Principal') }}
    </x-slot>
    <div class="text-xl text-gray-700 flex flex-col">
        <div class="">{{$titulo}}</div>
        <div class="w-full flex justify-center pt-10">
        <table>
                    <tr class="">
                        <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"></td>
                        <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>Descripcion</td>
                        <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>Desde</td>
                        <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>A</td>
                        <td class="border border-gray-300 font-semibold bg-blue-500 text-gray-200 p-1 text-lg"><center>Pago en</td>
                    </tr>
                <?php
                    $color=false;
                    foreach($query as $registro)
                    {
                ?>
                    <tr class="">
                        <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm"><center><a href="/lista_pagos_calculo/{{$registro->id}}"><i class="far fa-edit"></i></a></td>
                        <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">{{$registro->descripcion}}</td>
                        <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">{{$registro->fecha_inicio}}</td>
                        <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">{{$registro->fecha_fin}}</td>
                        <td class="border border-gray-300 font-light {{$color?'bg-gray-100':''}} text-gray-700 p-1 text-sm">{{$registro->pagado_en}}</td>
                        
                    </tr>
                <?php
                    $color=!$color;
                    }
                ?>
                </table>
        </div>
        
    </div>
</x-app-layout>
