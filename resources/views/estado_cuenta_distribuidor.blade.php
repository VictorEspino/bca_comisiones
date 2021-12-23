<x-app-layout>
    <x-slot name="header">
         {{ __('Estado de Cuenta') }}
    </x-slot>
    <div class="w-full flex flex-col text-gray-700">
        <div class="w-full flex flex-row">
            <div class="w-1/2">
                <div class="w-full text-2xl font-bold">{{$distribuidor}}</div>
                <div class="w-full text-xl font-semibold">Estado de cuenta de comisiones</div>
                <div class="w-full">{{$descripcion}}</div>
                <div class="w-full text-xl font-bold text-red-600">Fecha limite para facturar : {{$f_limite}}</div>
                @if(!is_null($carga_factura))
                <div class="w-full text-xl font-bold text-blue-600">Fecha de carga de factura : {{$carga_factura}}</div>
                @endif
                
                @if(session('status')!='')
                <div class="w-full text-sm rounded font-bold p-2 bg-green-300 text-gray-600">
                    {{session('status')}}
                </div>
                @endif
                @if($errors->any())
                <div class="w-full text-sm rounded font-bold p-2 bg-red-300 text-gray-600">
                    La carga/actualizacion no se realizo - Revise la foma de FACTURA
                </div>
                @endif
                <div class="pt-4 w-full text-2xl text-green-600 font-bold flex justify-center"><a href="/export_transacciones_distribuidor/{{$id}}/{{$usuario}}"><i class="far fa-file-excel"></i><span class="text-sm"> Exportar a EXCEL</span></a></div>
            </div>
            <div class="w-1/2 mr-4 flex justify-center">
                <table class="w-2/3">
                    <tr class="border-b-2">
                        <td class="">Comision Total</td>
                        <td class="text-green-700 font-semibold">(+) $ {{number_format($comision,2)}}</td>
                    </tr>
                    <tr class="border-b-2">
                        <td class="">Residual</td>
                        <td class="text-green-700 font-semibold">(+) $ {{number_format($residual,2)}}</td>
                    </tr>
                    <tr class="border-b-2">
                        <td class="">Retroactivos</td>
                        <td class="text-green-700 font-semibold">(+) $ {{number_format($retroactivo,2)}}</td>
                    </tr>
                    <tr class="border-b-2">
                        <td class="">Cargos</td>
                        <td class="text-red-700 font-semibold">(-) $ {{number_format($cb,2)}}</td>
                    </tr>
                    <tr class="border-b-2">
                        <td class="">Anticipos Pagados</td>
                        <td class="text-red-700 font-semibold">(-) $ {{number_format($anticipos,2)}}</td>
                    </tr>
                    <tr class="text-xl">
                        <td class=""><b>Total a pagar</b></td>
                        <td class=" font-bold"> $ {{number_format($a_pagar,2)}}</td>
                    </tr>
                </table>
            </div>
        </div>

        



        <div class="w-full flex flex-row space-x-2 pt-4">
            <div class="w-1/2 p-2 flex flex-col">
                <div class="w-full bg-gray-200 rounded-t-lg p-3 text-xl text-gray-100 bg-gradient-to-br from-green-700 to-green-400 flex flex-row justify-between">
                    <div class="font-bold">Servicios Masivo</div>
                    <div class="font-bold">$ {{number_format($ac_c_m+$as_c_m+$rc_c_m+$rs_c_m,2)}}</div>
                </div>
                <div class="w-full flex flex-col shadow-lg rounded-b-lg pb-5">
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Activaciones Nuevas</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($ac_u_m,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($ac_r_m,2)}})</div>
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
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($as_r_m,2)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($as_c_m,2)}}</div>
                        </div>
                    </div>
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Renovaciones</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($rc_u_m,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($rc_r_m,2)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($rc_c_m,2)}}</div>
                        </div>
                    </div>
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Renovaciones Eq Propio</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($rs_u_m,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($rs_r_m,2)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($rs_c_m,2)}}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-1/2 p-2 flex flex-col">
            <div class="w-full bg-gray-200 rounded-t-lg p-3 text-xl text-gray-100 bg-gradient-to-br from-blue-600 to-indigo-400 flex flex-row justify-between">
                    <div class="font-bold">Servicios Empresarial</div>
                    <div class="font-bold">$ {{number_format($ac_c_e+$as_c_e+$rc_c_e+$rs_c_e,2)}}</div>
                </div>
                <div class="w-full flex flex-col shadow-lg rounded-b-lg pb-5">
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Activaciones Nuevas</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($ac_u_e,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($ac_r_e,2)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($ac_c_e,2)}}</div>
                        </div>
                    </div>
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Activaciones Eq Propio</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($as_u_e,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($as_r_e,2)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($as_c_e,2)}}</div>
                        </div>
                    </div>
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Renovaciones</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($rc_u_e,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($rc_r_e,2)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($rc_c_e,2)}}</div>
                        </div>
                    </div>
                    <div class="w-full flex flex-row pt-3">
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Renovaciones Eq Propio</div>
                            <div class="text-xl font-bold flex justify-center text-gray-700">{{number_format($rs_u_e,0)}}</div>
                            <div class="text-xs italic flex justify-center text-yellow-700">(Rentas : $ {{number_format($rs_r_e,2)}})</div>
                        </div>
                        <div class="font-bold flex items-center">></div>
                        <div class="w-1/2 flex flex-col">
                            <div class="text-sm font-bold flex justify-center text-gray-700">Comision</div>
                            <div class="text-2xl font-bold flex justify-center text-gray-700">$ {{number_format($rs_c_e,2)}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(!empty($cr0))
        <div class="w-full flex flex-row space-x-2 pt-4">
            <div class="w-full p-2 flex flex-col">
                <div class="w-full bg-gray-200 rounded-t-lg p-3 text-xl text-gray-100 bg-gradient-to-br from-red-700 to-red-400 flex flex-row justify-between">
                    <div class="font-bold">No comisionadas</div>
                </div>
                <div class="w-full flex flex-col shadow-lg rounded-b-lg p-5">
                    @foreach($cr0 as $no_comision)
                        <div class="text-base">{{$no_comision->razon_cr0}} - {{$no_comision->lineas}}</div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @if($tipo_calculo=="2")
        @if(!empty($registros_cargos))
        <div class="w-full flex flex-row space-x-2 pt-4">
            <div class="w-full p-2 flex flex-col">
                <div class="w-full bg-gray-200 rounded-t-lg p-3 text-xl text-gray-100 bg-gradient-to-br from-red-400 to-yellow-700 flex flex-row justify-between">
                    <div class="font-bold">Cargos</div>
                    <div>
                    <a href="/export_cb_distribuidor/{{$id}}/{{$usuario}}"><i class="far fa-file-excel"></i><span class="text-sm"> Exportar a EXCEL</span></a>
                    </div>
                </div>
                <div class="w-full flex flex-col shadow-lg rounded-b-lg p-5">
                    @foreach($registros_cargos as $registro)
                        <div class="text-base">{{$registro->concepto}} - ${{number_format($registro->cb,2)}}</div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @if(!empty($registros_anticipo))
        <div class="w-full flex flex-row space-x-2 pt-4">
            <div class="w-full p-2 flex flex-col">
                <div class="w-full bg-gray-200 rounded-t-lg p-3 text-xl text-gray-100 bg-gradient-to-br from-blue-700 to-blue-400 flex flex-row justify-between">
                    <div class="font-bold">Adelantos Pagados</div>
                </div>
                <div class="w-full flex flex-col shadow-lg rounded-b-lg p-5">
                    @foreach($registros_anticipo as $registro)
                        <div class="text-base">{{$registro->descripcion}} - ${{number_format($registro->a_pagar,2)}} (<a href="/facturas/{{$registro->pdf}}" download>
                            <i class="text-2xl text-red-700 far fa-file-pdf"></i>
                        </a>)</div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @endif
        <div class="w-full flex flex-row space-x-2 pt-4">
            <div class="w-full p-2 flex flex-col items-center">
                <div class="w-full bg-gray-200 rounded-t-lg p-3 text-xl text-gray-100 bg-gradient-to-br from-yellow-700 to-yellow-400 flex flex-row justify-between">
                    <div class="font-bold">Factura</div>
                    <div class="font-bold text-sm"></div>
                </div>
                <div class="w-1/2 flex flex-col pt-3 pb-3 px-5 rounded-lg">
                    <div class="w-full bg-gray-500 text-white p-3 rounded-t-lg">
                        TOTAL A FACTURAR
                    </div>
                    <div class="w-full flex flex-row bg-gray-200 py-1 px-3">
                        <div class="w-1/2">SUBTOTAL</div>
                        <div class="w-1/4">$ {{number_format($a_pagar,2)}}</div>
                        <div class="w-1/4"></div>
                    </div>
                    <div class="w-full flex flex-row py-1 px-3">
                        <div class="w-1/2">IVA</div>
                        <div class="w-1/4">$ {{number_format($a_pagar*0.16,2)}}</div>
                        <div class="w-1/4">(16%)</div>
                    </div>
                    @if($tipo_fiscal=="2")<!--PERSONA FISICA -->
                    <div class="w-full flex flex-row bg-blue-200 py-1 px-3">
                        <div class="w-1/2">RETENCION IVA</div>
                        <div class="w-1/4">$ {{number_format($a_pagar*0.106666667,2)}}</div>
                        <div class="w-1/4">(10.6666667%)</div>
                    </div>
                    @endif
                    <div class="w-full flex flex-row bg-gray-700 py-1 px-3 rounded-b-lg text-white font-semibold">
                        <div class="w-1/2">TOTAL</div>
                        <div class="w-1/4">$ {{number_format($a_pagar*(1.16-($tipo_fiscal=="2"?0.106666667:0)),2)}}</div>
                        <div class="w-1/4"></div>
                    </div>

                </div>
                @if(!is_null($pdf))
                <div class="w-full flex flex-col pt-3 pb-3">
                    <div class="w-full flex flex-row ">
                        <div class="w-1/3 flex flex-col">
                            <div class="flex justify-center">
                                <a href="/facturas/{{$pdf}}" download>
                                    <i class="text-2xl text-red-700 far fa-file-pdf"></i> PDF 
                                </a>
                            </div>

                        </div>
                        <div class="w-1/3 flex flex-col">
                            <div class="flex justify-center">
                                <a href="/facturas/{{$xml}}" download>
                                    <i class="text-2xl text-blue-600 far fa-file-code"></i> XML
                                </a>
                            </div>
                        </div>
                        <div class="w-1/3 flex flex-col">
                            <div class="w-full text-sm font-bold">CLABE: {{$clabe}}</div>
                            <div class="w-full text-sm font-bold">Titular: {{$titular}}</div>
                        </div>
                    </div>
                </div>
                @endif
                @if(Auth::user()->tipo=="distribuidor" && $puede_facturar=="SI")
                <div class="w-full flex flex-col rounded-b-lg p-5">
                    <form method="POST" action="{{route('cargar_factura_distribuidor')}}" enctype="multipart/form-data">
                        @csrf
                        <div class="w-full flex flex-row ">
                            <div class="w-5/12 flex flex-col">
                                <span class="text-sm text-gray-700">Archivo PDF</span>
                                <input class="w-full" type="file" name="pdf_file">
                                @error('pdf_file')
                                    <br><span class="text-xs italic text-red-700 text-xs">{{ $message }}</span>
                                @enderror  
                            </div>
                            <div class="w-5/12 flex flex-col">
                                <span class="text-sm text-gray-700">Archivo XML</span>
                                <input class="w-full" type="file" name="xml_file">
                                @error('xml_file')
                                    <br><span class="text-xs italic text-red-700 text-xs">{{ $message }}</span>
                                @enderror 
                            </div>
                            <div class="w-2/12 flex flex-col">
                                <input type="hidden" name="calculo_id" value="{{$id}}">
                                <input type="hidden" name="numero_distribuidor" value="{{Auth::user()->user}}">
                                <button class="w-full p-3 bg-green-500 hover:bg-green-700 font-bold rounded-lg text-gray-200 text-xl">Cargar</button>
                            </div>
                        </div>
                        <div class="w-full flex flex-row space-x-10 pt-5">
                            <div class="w-1/2 flex flex-col">
                                <span class="text-sm text-gray-700">CLABE</span>
                                <input class="block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600" type="text" name="clabe" value="{{old('clabe')}}">
                                @error('clabe')
                                    <br><span class="text-xs italic text-red-700 text-xs">{{ $message }}</span>
                                @enderror  
                            </div>
                            <div class="w-1/2 flex flex-col">
                                <span class="text-sm text-gray-700">Titular</span>
                                <input class="block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600" type="text" name="titular" value="{{old('titular')}}">
                                @error('titular')
                                    <br><span class="text-xs italic text-red-700 text-xs">{{ $message }}</span>
                                @enderror  
                            </div>
                        </div>
                    </form>
                </div>
                @endif
                @if(is_null($pdf) && $puede_facturar=="NO")
                <div class="w-full flex flex-col rounded-b-lg p-5 text-center">
                    <span class="text-xl text-red-700 font-bold">{{$nota_factura}}</span>
                </div>
                @endif
                
            </div>
        </div>
        
        
    </div>

</x-app-layout>
