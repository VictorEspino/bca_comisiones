<x-app-layout>
    <x-slot name="header">
         {{ __('Nuevo Calculo Distribuidores') }}
    </x-slot>
    <div class="bg-white border-solid border-gray-300 rounded-lg border shadow-sm w-full">
                    <div class="text-white py-3 px-2 rounded-t-lg bg-gradient-to-br from-pink-600 to-yellow-300 boder-solid border-gray-300 border-b text-white font-bold">
                        <i class="fas fa-link"></i>
                        Registro Nuevo Calculo Distribuidores
                    </div>
                    <div class="p-3">
                        <form action="{{route('nuevo_calculo_dist')}}" class="w-full" method="POST" >
                            @csrf
                            <div class="flex flex-row">
                                <div class="w-1/3 px-3">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1"
                                        for="tipo">
                                        Tipo
                                    </label>
                                    <div>
                                        <select class="block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600"
                                            name="tipo">
                                            <option value=""></option>
                                            <option value="1" {{old('tipo')=='1'?'selected':''}}>Semanal</option>
                                            <option value="2" {{old('tipo')=='2'?'selected':''}}>Mensual</option>

                                        </select>
                                        @error('tipo')
                                        <p class="text-red-700 text-xs italic">{{ $message }}</p>
                                        @enderror 
                                    </div>
                                </div>
                                <div class="w-1/3 px-3">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1"
                                            for="grid-last-name">
                                        Fecha de Pago
                                    </label>
                                    <input class="appearance-none block w-full bg-gray-200 text-grey-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600"
                                            name="f_pago" type="date" placeholder="YYYY-MM-DD" value="{{old('f_pago')}}">
                                    @error('f_pago')
                                    <p class="text-red-700 text-xs italic">{{ $message }}</p>
                                    @enderror 
                                </div>
                                <div class="w-1/3 px-3">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1"
                                            for="grid-last-name">
                                        Fecha limite facturas
                                    </label>
                                    <input class="appearance-none block w-full bg-gray-200 text-grey-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600"
                                            name="f_limite" type="datetime-local" placeholder="YYYY-MM-DD" value="{{old('f_limite')}}">
                                    @error('f_limite')
                                    <p class="text-red-700 text-xs italic">{{ $message }}</p>
                                    @enderror 
                                </div>
                            </div>
                             <div class="flex flex-row">
                                <div class="w-1/2 px-3">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1"
                                        for="f_inicio">
                                        Fecha de Inicio
                                    </label>
                                    <div>
                                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600"
                                            name="f_inicio" type="date" placeholder="YYYY-MM-DD" value="{{old('f_inicio')}}">
                                        @error('f_inicio')
                                        <p class="text-red-700 text-xs italic">{{ $message }}</p>
                                        @enderror 
                                    </div>
                                    
                                </div>
                                <div class="w-1/2 px-3">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1"
                                            for="grid-last-name">
                                        Fecha de Fin
                                    </label>
                                    <input class="appearance-none block w-full bg-gray-200 text-grey-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600"
                                            name="f_fin" type="date" placeholder="YYYY-MM-DD" value="{{old('f_fin')}}">
                                    @error('f_fin')
                                    <p class="text-red-700 text-xs italic">{{ $message }}</p>
                                    @enderror 
                                </div>
                            </div>
                            <div class="flex flex-row pb-4">
                                <div class="w-full px-3">
                                    <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1"
                                            for="grid-password">
                                        Descripción
                                    </label>
                                    <input class="appearance-none block w-full bg-grey-200 text-grey-darker border border-grey-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                            name="descripcion" type="text" placeholder="" value="{{old('descripcion')}}">
                                    @error('descripcion')
                                        <p class="text-red-700 text-xs italic">{{ $message }}</p>
                                    @enderror 
                                </div>
                            </div>
                            <button class="bg-green-500 hover:bg-green-800 text-white font-bold py-2 px-4 rounded-full" type="submit">
                                Guardar
                            </button>
                        </form>
                    </div>
                </div>
</x-applayout>
