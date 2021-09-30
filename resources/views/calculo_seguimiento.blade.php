<x-app-layout>
    <x-slot name="header">
         {{ __('Calculo Comisiones - Sucursales') }}
    </x-slot>
<div class="w-full">
    transacciones
<form action="{{route('carga_transacciones')}}" enctype="multipart/form-data" method="POST">
    @csrf
    <input type="hidden" name="calculo_id" value="{{$id}}">
    <input type="file" name="file">
    <button>Cargar</button>
</form>
empleados
<form action="{{route('carga_empleados')}}" enctype="multipart/form-data" method="POST">
    @csrf
    <input type="hidden" name="calculo_id" value="{{$id}}">
    <input type="file" name="file">
    <button>Cargar</button>
</form>
cuotas
<form action="{{route('carga_cuotas')}}" enctype="multipart/form-data" method="POST">
    @csrf
    <input type="hidden" name="calculo_id" value="{{$id}}">
    <input type="file" name="file">
    <button>Cargar</button>
</form>
ajustes
<form action="{{route('carga_ajustes')}}" enctype="multipart/form-data" method="POST">
    @csrf
    <input type="hidden" name="calculo_id" value="{{$id}}">
    <input type="file" name="file">
    <button>Cargar</button>
</form>

</div>
@if(session('status'))
        <div class="bg-green-200 p-4 flex justify-center font-bold rounded-b-lg">
            {{session('status')}}
        </div>
        @endif
        @if(session()->has('failures'))
        <div class="bg-red-200 p-4 flex justify-center font-bold">
            El archivo no fue cargado!
        </div>
        <div class="bg-red-200 p-4 flex justify-center rounded-b-lg">
            <table class="text-sm">
                <tr>
                    <td class="bg-red-700 text-gray-100 px-3">Row</td>
                    <td class="bg-red-700 text-gray-100 px-3">Columna</td>
                    <td class="bg-red-700 text-gray-100 px-3">Error</td>
                    <td class="bg-red-700 text-gray-100 px-3">Valor</td>
                </tr>
            
                @foreach(session()->get('failures') as $validation)
                <tr>
                    <td class="px-3"><center>{{$validation->row()}}</td>
                    <td class="px-3"><center>{{$validation->attribute()}}</td>
                    <td class="px-3">
                        <ul>
                        @foreach($validation->errors() as $e)
                            <li>{{$e}}</li>
                        @endforeach
                        </ul>
                    </td>
                    
                    <td class="px-3"><center>
                    <?php
                     try{
                    ?>    
                        {{$validation->values()[$validation->attribute()]}}
                    <?php
                        }
                        catch(\Exception $e)
                        {
                            ;
                        }
                    ?>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
        @endif
        <input size=100 id="respuesta">
        <input type="button" onClick="ejecutarCalculo()" value="ejecutar">
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    function ejecutarCalculo(){
            //alert('Ejecutando Calculo Distribuidores');
            //this.terminar_calculo();
            
                respuesta_provisional='';
                respuesta='';
                flag_cambios=true;
                const parametros_a_enviar={
                    'id':{{$id}}
                };
                document.getElementById('respuesta').value='Ejecutando Calculo Comisiones...';
                axios.put("/ejecutar_calculo",parametros_a_enviar).then((response)=>{
                        document.getElementById('respuesta').value='Ejecutando Balance Ejeutivos Venta ...';
                        this.respuesta_provisional="ACTUALIZACION CALCULO => Transacciones Calculadas= "+response.data.transacciones_calculadas;
                        this.respuesta_provisional=respuesta_provisional+"; Transacciones CC= "+response.data.transacciones_cc;
                        axios.put("/genera_balance_ejecutivos",parametros_a_enviar).then((response2)=>{
                            document.getElementById('respuesta').value="Ejecutando Balace Gerentes de Tienda ...";
                            respuesta_provisional=respuesta_provisional+"; Personas Medidas= "+response2.data;
                            axios.put("/genera_balance_gerentes",parametros_a_enviar).then((response3)=>{
                                respuesta_provisional=respuesta_provisional+"; Sucursales Medidas= "+response3.data;
                                document.getElementById('respuesta').value="Ejecutando Balance Regionales...";
                                axios.put("/genera_balance_regionales",parametros_a_enviar).then((response4)=>{
                                    respuesta_provisional=respuesta_provisional+"; Regiones Medidas= "+response4.data;
                                    document.getElementById('respuesta').value="Ejecutando Balance Director Sucursales... ";
                                    axios.put("/genera_balance_director",parametros_a_enviar).then((response5)=>{
                                        respuesta_provisional=respuesta_provisional+"; Directores= "+response5.data;
                                        document.getElementById('respuesta').value="Generando Payments..."
                                        axios.put("/genera_pagos",parametros_a_enviar).then((response6)=>{
                                            respuesta_provisional=respuesta_provisional+"; Pagos Generados"
                                            document.getElementById('respuesta').value=respuesta_provisional;
                                        
                                        });
                                    });

                                    

                                });
                            });
                        });

                    });
                    
            }
</script>
</x-app-layout>