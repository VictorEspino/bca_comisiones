<x-app-layout>
    <x-slot name="header">
         {{ __('Principal') }}
    </x-slot>
    <div class="w-full flex flex-col text-gray-700">
        <div class="w-full flex flex-row">
            <div class="w-1/2">
                <div class="w-full text-2xl font-semibold">Estado de cuenta de comisiones</div>
                <div class="w-full">{{$descripcion}}</div>
                <div class="pt-4 w-full text-2xl text-green-600 font-bold flex justify-center"><a href="/export_transacciones_distribuidor/{{$id}}"><i class="far fa-file-excel"></i><span class="text-sm"> Exportar a EXCEL</span></a></div>
            </div>
            <div class="w-1/2 mr-4 flex justify-center">
                <table class="w-2/3">
                    <tr class="border-b-2">
                        <td class="">Comision Total</td>
                        <td class="text-green-700 font-semibold">(+) $ {{number_format($comision,0)}}</td>
                    </tr>
                    <tr class="border-b-2">
                        <td class="">Residual</td>
                        <td class="text-green-700 font-semibold">(+) $ {{number_format($residual,0)}}</td>
                    </tr>
                    <tr class="border-b-2">
                        <td class="">Retroactivos</td>
                        <td class="text-green-700 font-semibold">(+) $ {{number_format($retroactivo,0)}}</td>
                    </tr>
                    <tr class="border-b-2">
                        <td class="">Charge-Back</td>
                        <td class="text-red-700 font-semibold">(-) $ {{number_format($cb,0)}}</td>
                    </tr>
                    <tr class="border-b-2">
                        <td class="">Anticipos Pagados</td>
                        <td class="text-red-700 font-semibold">(-) $ {{number_format($anticipos,0)}}</td>
                    </tr>
                    <tr class="text-xl">
                        <td class=""><b>Total a pagar</b></td>
                        <td class=" font-bold"> $ {{number_format($a_pagar,0)}}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="w-full flex flex-row space-x-2 pt-4">
            <div class="w-1/2 p-2 flex flex-col">
                <div class="w-full bg-gray-200 rounded-t-lg p-3 text-xl text-gray-100 bg-gradient-to-br from-green-700 to-green-400 flex flex-row justify-between">
                    <div class="font-bold">Servicios Masivo</div>
                    <div class="font-bold">$ {{number_format($ac_c_m+$as_c_m+$rc_c_m+$rs_c_m,0)}}</div>
                </div>
                <div class="w-full flex flex-col shadow-lg rounded-b-lg pb-5">
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Activaciones Nuevas</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($ac_u_m,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($ac_r_m,0)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($ac_c_m,0)}}</div>
                        </div>
                    </div>
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Activaciones Eq Propio</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($as_u_m,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($as_r_m,0)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($as_c_m,0)}}</div>
                        </div>
                    </div>
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Renovaciones</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($rc_u_m,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($rc_r_m,0)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($rc_c_m,0)}}</div>
                        </div>
                    </div>
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Renovaciones Eq Propio</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($rs_u_m,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($rs_r_m,0)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($rs_c_m,0)}}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-1/2 p-2 flex flex-col">
            <div class="w-full bg-gray-200 rounded-t-lg p-3 text-xl text-gray-100 bg-gradient-to-br from-blue-600 to-indigo-400 flex flex-row justify-between">
                    <div class="font-bold">Servicios Empresarial</div>
                    <div class="font-bold">$ {{number_format($ac_c_e+$as_c_e+$rc_c_e+$rs_c_e,0)}}</div>
                </div>
                <div class="w-full flex flex-col shadow-lg rounded-b-lg pb-5">
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Activaciones Nuevas</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($ac_u_e,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($ac_r_e,0)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($ac_c_e,0)}}</div>
                        </div>
                    </div>
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Activaciones Eq Propio</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($as_u_e,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($as_r_e,0)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($as_c_e,0)}}</div>
                        </div>
                    </div>
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Renovaciones</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($rc_u_e,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($rc_r_e,0)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($rc_c_e,0)}}</div>
                        </div>
                    </div>
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Renovaciones Eq Propio</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($rs_u_e,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($rs_r_e,0)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($rs_c_e,0)}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
