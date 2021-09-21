<template>
    <main class="flex-1 p-1 overflow-hidden">
        <div class="flex flex-col">
            <!-- SECCION DE CONTENIDO-->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row lg:space-x-5 md:space-y-0 sm:space-y-3 lg:space-y-0">
                <!--SECCION DE CATURA DE NUEVOS PARAMETROS-->
                <div class="bg-white border-solid border-gray-300 rounded-lg border shadow-sm w-full md:w-1/3 lg:w-1/3">
                    <div class="text-white py-3 px-2 rounded-t-lg bg-gradient-to-br from-pink-600 to-yellow-300 boder-solid border-gray-300 border-b text-white font-bold">
                        <i class="fas fa-link"></i>
                        Limites de Calculo
                    </div>
                    <div class="p-3">
                        <form action="" class="w-full" v-on:submit.prevent="nuevoCalculo()">
 
                            <div class="flex flex-wrap -mx-3 mb-6">
                                <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1"
                                        for="f_inicio">
                                        Fecha de Inicio
                                    </label>
                                    <div v-if="this.etapa==0">
                                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600"
                                            name="f_inicio" type="text" placeholder="YYYY-MM-DD" v-model="f_inicio">
                                        <p class="text-red-500 text-xs italic">Ambos campos son obligatorios</p>
                                    </div>
                                    <div v-else class="px-2 py-3 border-solid border-gray-300 border-b text-gray-700 font-bold ">
                                        {{this.f_inicio}}
                                    </div>
                                    
                                </div>
                                <div class="w-full md:w-1/2 px-3">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1"
                                            for="grid-last-name">
                                        Fecha de Fin
                                    </label>
                                    <input v-if="etapa==0" class="appearance-none block w-full bg-gray-200 text-grey-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600"
                                            name="f_fin" type="text" placeholder="YYYY-MM-DD" v-model="f_fin">
                                    <div v-else class="px-2 py-3 border-solid border-gray-300 border-b text-grey-700 font-bold ">
                                        {{this.f_fin}}
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap -mx-3 mb-6">
                                <div class="w-full px-3">
                                    <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1"
                                            for="grid-password">
                                        Descripción
                                    </label>
                                    <div v-if="etapa==0">
                                        <input class="appearance-none block w-full bg-grey-200 text-grey-darker border border-grey-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-grey"
                                            name="descripcion" type="text" placeholder="" v-model="descripcion">
                                        <p class="text-grey-dark text-xs italic">Breve descripcion del calculo</p>
                                    </div>
                                    <div v-else class="px-2 py-3 border-solid border-gray-300 border-b text-grey-700 font-bold ">
                                        {{this.descripcion}}
                                    </div>
                                    
                                </div>
                            </div>
                            <button  v-if="etapa==0" class="bg-green-500 hover:bg-green-800 text-white font-bold py-2 px-4 rounded-full" type="submit">
                                Guardar
                            </button>
                        </form>
                    </div>
                </div>
                <!--TEMINA SECCION DE CAPTURA -->
                <!--INICIA SECCION DE AVANCES-->
                <div class="bg-white border-solid border-gray-300 rounded-lg border shadow-sm w-full md:w-2/3 lg:w-2/3">
                    <div class="text-white py-3 px-2 rounded-t-lg bg-gradient-to-br from-blue-700 to-green-300 boder-solid border-gray-300 border-b text-white font-bold">
                        <i class="fab fa-codepen"></i>
                        Ejecucion de Calculo

                    </div>    
                    <div class="p-3">
                        <div class="w-full py-3">
                            <div class="flex">
                                    <ul class="StepProgress w-full">
                                        <li v-if="this.etapa==0" class="StepProgress-item current">
                                            <strong>Setup de Calculo</strong>
                                            {{this.respuesta0}}
                                        </li>
                                        <li v-if="this.etapa==1" class="StepProgress-item is-done">
                                            <strong>Setup Finalizado</strong>
                                            <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Periodo de Calculo: {{this.respuesta0_calculo}}</div></div>
                                            <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Transacciones: {{this.respuesta0_transacciones}}</div></div>
                                            <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Empleados: {{this.respuesta0_empleados}}</div></div>

                                        </li>
                                        <li class="StepProgress-item" v-bind:class="{'is-done':etapa==1&&cuotas_ok,'current':etapa==1&&!cuotas_ok}">
                                            <strong>Cuotas</strong>
                                            <button v-if="etapa>=1 && !cuotas_ok" v-on:click="toggleModalCuotas()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">Cargar Cuotas</button>
                                            <div v-if="etapa>=1 && cuotas_ok" class="flex flex-row w-2/3">
                                                <div class="flex-shrink-0 flex-auto w-5/6">
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Activaciones: {{this.cuotas_act}}</div></div>
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Act Eq Propio: {{this.cuotas_aep}}</div></div>
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Renovaciones: {{this.cuotas_ren}}</div></div>
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Ren Eq Propio: {{this.cuotas_rep}}</div></div>
                                                </div>
                                                <div class="flex flex-wrap content-center"><div><button v-on:click="toggleModalCuotas()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">Refresh</button></div></div>
                                            </div>
                                            {{this.respuesta1}}
                                        </li>
                                        <li class="StepProgress-item" v-bind:class="{'is-done':etapa==1&&cc_ok,'current':etapa==1&&!cc_ok}">
                                            <strong>Contratos por CC</strong>
                                            <button v-if="etapa>=1 && !cc_ok" v-on:click="toggleModalCC()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">Cargar Pedidos por CC</button>
                                            <div v-if="etapa>=1 && cc_ok" class="flex flex-row w-2/3">
                                                <div class="flex-shrink-0 flex-auto w-5/6">
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Transacciones CC: {{this.cc_transacciones}}</div></div>
                                                </div>
                                                <div class="flex flex-wrap content-center"><div><button v-on:click="toggleModalCC()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">Refresh</button></div></div>
                                            </div>
                                            {{this.respuesta2}}
                                        </li>
                                        <li class="StepProgress-item" v-bind:class="{'is-done':etapa==1&&eq0_ok,'current':etapa==1&&!eq0_ok}">
                                            <strong>Pedidos EQ sin costo</strong>
                                            <button v-if="etapa>=1 && !eq0_ok" v-on:click="toggleModalEQ0()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">Cargar Pedidos EQ sin costo</button>
                                            <div v-if="etapa>=1 && eq0_ok" class="flex flex-row w-2/3">
                                                <div class="flex-shrink-0 flex-auto w-5/6">
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Transacciones Eq: {{this.eq0_transacciones}}</div></div>
                                                </div>
                                                <div class="flex flex-wrap content-center"><div><button v-on:click="toggleModalEQ0()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">Refresh</button></div></div>
                                            </div>
                                            {{this.respuesta3}}
                                        </li>
                                        <li class="StepProgress-item" v-bind:class="{'is-done':etapa==1&&cr0_ok,'current':etapa==1&&!cr0_ok}">
                                            <strong>Transacciones NO Comisionables</strong>
                                            <button v-if="etapa>=1 && !cr0_ok" v-on:click="toggleModalCR0()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">Cargar Transacciones NO comisionables</button>
                                            <div v-if="etapa>=1 && cr0_ok" class="flex flex-row w-2/3">
                                                <div class="flex-shrink-0 flex-auto w-5/6">
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Trans NO Comisionables: {{this.cr0_transacciones}}</div></div>
                                                </div>
                                                <div class="flex flex-wrap content-center"><div><button v-on:click="toggleModalCR0()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">Refresh</button></div></div>
                                            </div>
                                        </li>
                                        <li class="StepProgress-item" v-bind:class="{'current':calculo_ready&&!cb_ok,'is-done':calculo_ready&&cb_ok}">
                                            <div v-if="calculo_ready">
                                                <strong>Charge-Back</strong>
                                                <button v-if="etapa>=1 && !cb_ok" v-on:click="toggleModalCB()" class="px-2 bg-red-500 hover:bg-red-800 text-white font-bold rounded">Cargar Charge-Back</button>
                                                <div v-if="etapa>=1 && cb_ok" class="flex flex-row w-2/3">
                                                    <div class="flex-shrink-0 flex-auto w-5/6">
                                                        <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Transacciones Charge-Back: {{this.cb_transacciones}}</div></div>
                                                    </div>
                                                    <div class="flex flex-wrap content-center"><div><button v-on:click="toggleModalCB()" class="px-2 bg-red-500 hover:bg-red-800 text-white font-bold rounded">Refresh</button></div></div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="StepProgress-item" v-bind:class="{'current':calculo_ready&&!fin_ok,'is-done':calculo_ready&&fin_ok}">
                                            <div v-if="calculo_ready">
                                                <strong>Calculo de Comisiones</strong>
                                                <!--<button v-on:click="ejecutarCalculo()" class="px-2 bg-blue-500 hover:bg-blue-800 text-white font-bold rounded-full">Ejecutar</button>-->
                                                    {{this.respuesta4}}
                                                <div class="flex flex-row w-2/3">
                                                <div class="flex-shrink-0 flex-auto w-5/6">
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Transacciones Sucusales: {{this.fin_transacciones}}</div></div>
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Transacciones Telemarketing: {{this.fin_cc}}</div></div>
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Personas Medidas: {{this.fin_personas}}</div></div>
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Tiendas Medidas: {{this.fin_sucursales}}</div></div>
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Regionales: {{this.fin_regionales}}</div></div>
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Director: {{this.fin_director}}</div></div>
                                                </div>
                                                <div class="flex flex-wrap content-center"><div><button v-on:click="ejecutarCalculo()" class="px-2 bg-blue-500 hover:bg-blue-800 text-white font-bold rounded">Ejecutar Calculo de Comisiones</button></div></div>
                                                </div>
                                                
                                            </div>
                                        </li>
                                    </ul>
                   
                                
                                
                            </div>
                        </div>
                    </div>

                </div>
                        <!--CIERRA SECION DE AVANCES-->
            </div>
                    <!-- CIERRA LA SECCION DE CONTENIDO -->
        </div>
        <!--MODAL DE CUOTAS-->
        <div v-if="showModalCuotas" class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center flex">
            <div class="relative w-11/12 md:w-3/5 lg:w-2/5 my-6 mx-auto max-w-3xl">
                <!--content-->
                <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                    <!--header-->
                    <div class="flex items-start justify-between p-3 border-b border-solid border-grey-300 rounded-t">
                        <h3 class="text-xl font-semibold">
                            Carga de Cuotas
                        </h3>
                        <button class="p-1 ml-auto bg-transparent border-0 text-black opacity-5 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" v-on:click="toggleModalCuotas()">
                            <span class="bg-transparent text-black opacity-5 h-6 w-6 text-2xl block outline-none focus:outline-none">
                                ×
                            </span>
                        </button>
                    </div>
                    <!--body-->
                    <form @submit="cargarCuotas" enctype="multipart/form-data" class="w-full max-w-lg px-4 py-3" >
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                    Archivo de cuotas
                                </label>
                                <input v-on:change="onFileChangeCuotas" class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="cuotas"  type="file">
                            </div>
                        </div>
                        <!--footer-->
                        <div class="flex items-center justify-end p-2 border-t border-solid border-gray-300 rounded-b">
                            <input type="submit" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full" value="Cargar">
                            <button v-on:click="toggleModalCuotas()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full">Cerrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showModalCuotas" class="opacity-25 fixed inset-0 z-40 bg-black"></div>
        <!--MODAL DE CC-->
        <div v-if="showModalCC" class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center flex">
            <div class="relative w-11/12 md:w-3/5 lg:w-2/5 my-6 mx-auto max-w-3xl">
                <!--content-->
                <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                    <!--header-->
                    <div class="flex items-start justify-between p-3 border-b border-solid border-grey-300 rounded-t">
                        <h3 class="text-xl font-semibold">
                            Contratos de Call Center
                        </h3>
                        <button class="p-1 ml-auto bg-transparent border-0 text-black opacity-5 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" v-on:click="toggleModalCC()">
                            <span class="bg-transparent text-black opacity-5 h-6 w-6 text-2xl block outline-none focus:outline-none">
                                ×
                            </span>
                        </button>
                    </div>
                    <!--body-->
                    <form @submit="cargarCC" enctype="multipart/form-data" class="w-full max-w-lg px-4 py-3" >
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                    Archivo de contratos del CC
                                </label>
                                <input v-on:change="onFileChangeCC" class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="cuotas"  type="file">
                            </div>
                        </div>
                        <!--footer-->
                        <div class="flex items-center justify-end p-2 border-t border-solid border-gray-300 rounded-b">
                            <input type="submit" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full" value="Cargar">
                            <button v-on:click="toggleModalCC()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full">Cerrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showModalCC" class="opacity-25 fixed inset-0 z-40 bg-black"></div>
        <!--MODAL DE EQ0-->
        <div v-if="showModalEQ0" class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center flex">
            <div class="relative w-11/12 md:w-3/5 lg:w-2/5 my-6 mx-auto max-w-3xl">
                <!--content-->
                <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                    <!--header-->
                    <div class="flex items-start justify-between p-3 border-b border-solid border-grey-300 rounded-t">
                        <h3 class="text-xl font-semibold">
                            Contratos de EQ sin Costo
                        </h3>
                        <button class="p-1 ml-auto bg-transparent border-0 text-black opacity-5 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" v-on:click="toggleModalEQ0()">
                            <span class="bg-transparent text-black opacity-5 h-6 w-6 text-2xl block outline-none focus:outline-none">
                                ×
                            </span>
                        </button>
                    </div>
                    <!--body-->
                    <form @submit="cargarEQ0" enctype="multipart/form-data" class="w-full max-w-lg px-4 py-3" >
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                    Archivo de pedidos de equipos sin Costo
                                </label>
                                <input v-on:change="onFileChangeEQ0" class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="cuotas"  type="file">
                            </div>
                        </div>
                        <!--footer-->
                        <div class="flex items-center justify-end p-2 border-t border-solid border-gray-300 rounded-b">
                            <input type="submit" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full" value="Cargar">
                            <button v-on:click="toggleModalEQ0()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full">Cerrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showModalEQ0" class="opacity-25 fixed inset-0 z-40 bg-black"></div>
        <!--MODAL DE CR0-->
        <div v-if="showModalCR0" class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center flex">
            <div class="relative w-11/12 md:w-3/5 lg:w-2/5 my-6 mx-auto max-w-3xl">
                <!--content-->
                <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                    <!--header-->
                    <div class="flex items-start justify-between p-3 border-b border-solid border-grey-300 rounded-t">
                        <h3 class="text-xl font-semibold">
                            Transacciones NO Comisionables
                        </h3>
                        <button class="p-1 ml-auto bg-transparent border-0 text-black opacity-5 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" v-on:click="toggleModalCR0()">
                            <span class="bg-transparent text-black opacity-5 h-6 w-6 text-2xl block outline-none focus:outline-none">
                                ×
                            </span>
                        </button>
                    </div>
                    <!--body-->
                    <form @submit="cargarCR0" enctype="multipart/form-data" class="w-full max-w-lg px-4 py-3" >
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                    Archivo de transacciones no comisionables
                                </label>
                                <input v-on:change="onFileChangeCR0" class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="cuotas"  type="file">
                            </div>
                        </div>
                        <!--footer-->
                        <div class="flex items-center justify-end p-2 border-t border-solid border-gray-300 rounded-b">
                            <input type="submit" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full" value="Cargar">
                            <button v-on:click="toggleModalCR0()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full">Cerrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showModalCR0" class="opacity-25 fixed inset-0 z-40 bg-black"></div>
        <!--MODAL DE CB-->
        <div v-if="showModalCB" class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center flex">
            <div class="relative w-11/12 md:w-3/5 lg:w-2/5 my-6 mx-auto max-w-3xl">
                <!--content-->
                <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                    <!--header-->
                    <div class="flex items-start justify-between p-3 border-b border-solid border-grey-300 rounded-t">
                        <h3 class="text-xl font-semibold">
                            CHARGE-BACK
                        </h3>
                        <button class="p-1 ml-auto bg-transparent border-0 text-black opacity-5 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" v-on:click="toggleModalCB()">
                            <span class="bg-transparent text-black opacity-5 h-6 w-6 text-2xl block outline-none focus:outline-none">
                                ×
                            </span>
                        </button>
                    </div>
                    <!--body-->
                    <form @submit="cargarCB" enctype="multipart/form-data" class="w-full max-w-lg px-4 py-3" >
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                    Archivo de contratos para Charge-Back
                                </label>
                                <input v-on:change="onFileChangeCB" class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="cuotas"  type="file">
                            </div>
                        </div>
                        <!--footer-->
                        <div class="flex items-center justify-end p-2 border-t border-solid border-gray-300 rounded-b">
                            <input type="submit" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full" value="Cargar">
                            <button v-on:click="toggleModalCB()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full">Cerrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showModalCB" class="opacity-25 fixed inset-0 z-40 bg-black"></div>


    </main>
            <!--/Main-->
</template>
<script>
    export default{
        data(){
            return{
                showModalCuotas:false,
                showModalCC:false,
                showModalEQ0:false,
                showModalCR0:false,
                showModalCB:false,
                id_calculo:0,
                f_inicio:'',
                f_fin:'',
                descripcion:'',
                etapa0_active:true,
                cuotas_ok:false,
                cc_ok:false,
                eq0_ok:false,
                cr0_ok:false,
                cb_ok:false,
                respuesta0:'',
                respuesta0_calculo:'',
                respuesta0_transacciones:'',
                respuesta0_empleados:'',
                respuesta1:'',
                cuotas_act:0,
                cuotas_aep:0,
                cuotas_ren:0,
                cuotas_rep:0,
                respuesta2:'',
                cc_transacciones:0,
                respuesta3:'',
                eq0_transacciones:0,
                respuesta4:'',
                cr0_transacciones:0,
                respuesta5:'',
                cb_transacciones:0,
                respuesta6:'',
                fin_transacciones:0,
                fin_cc:0,
                fin_personas:0,
                fin_sucursales:0,
                fin_regionales:0,
                fin_director:0,
                fin_ok:false,
                etapa:0,
                archivo_cuotas:'',
                archivo_cc:'',
                archivo_eq0:'',
                archivo_cr0:'',
                archivo_cb:'',
                calculo_ready:false           
            }
        },
       mounted(){ 
            console.log('Template de Captura OK')
        },
        methods:{
            toggleModalCuotas(){
                this.showModalCuotas = !this.showModalCuotas;
            },
            toggleModalCC(){
                this.showModalCC = !this.showModalCC;
            },
            toggleModalEQ0(){
                this.showModalEQ0 = !this.showModalEQ0;
            },
            toggleModalCR0(){
                this.showModalCR0 = !this.showModalCR0;
            },
            toggleModalCB(){
                this.showModalCB = !this.showModalCB;
            },
            nuevoCalculo(){
				const parametros_a_enviar={
                    'f_inicio':this.f_inicio,
                    'f_fin':this.f_fin,
                    'descripcion':this.descripcion
                }
                this.respuesta0="Realizando Setup Calculo... "
                axios.put("/setup_calculo",parametros_a_enviar).then((response)=>{
                    this.respuesta_show=true;
                    console.log(response.data);
                    this.respuesta0="Periodo de Calculo: "+response.data.periodo+"; Transacciones importadas: "+response.data.transacciones+"; Empleados importados:"+response.data.empleados;
                    
                    if(response.data.id_calculo!=0)
                        {
                            this.id_calculo=response.data.id_calculo;
                            this.etapa=1;
                            this.respuesta0='';
                            this.respuesta0_calculo=response.data.periodo;
                            this.respuesta0_transacciones=response.data.transacciones;
                            this.respuesta0_empleados=response.data.empleados;
                        }
                    else{
                            this.respuesta0_ok=false;
                        }

                    }
                );
            },
            onFileChangeCuotas(e) {
                this.archivo_cuotas = e.target.files[0];
            },
            onFileChangeCC(e) {
                this.archivo_cc = e.target.files[0];
            },
            onFileChangeEQ0(e) {
                this.archivo_eq0 = e.target.files[0];
            },
            onFileChangeCR0(e) {
                this.archivo_cr0 = e.target.files[0];
            },
            onFileChangeCB(e) {
                this.archivo_cb = e.target.files[0];
            },
            cargarCuotas(e) {
                e.preventDefault();
                 const config = {
                    headers: {
                        'content-type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                }

                // form data
                let formData = new FormData();
                formData.append('file', this.archivo_cuotas);
                formData.append('calculo_id',this.id_calculo);

                // send upload request
                axios.post('/store_cuotas', formData, config)
                    .then((response)=>
                        {
                            this.cuotas_ok=true;
                            this.cuotas_act=response.data.activaciones;
                            this.cuotas_aep=response.data.aep;
                            this.cuotas_ren=response.data.renovaciones;
                            this.cuotas_rep=response.data.rep;
                            this.toggleModalCuotas();
                            if(this.cuotas_ok && this.cc_ok && this.eq0_ok && this.cr0_ok) { this.calculo_ready=true };
                        })
                    .catch((error)=>
                        {
                        response.log(error);
                        });
            },
            cargarCC(e) {
                e.preventDefault();
                 const config = {
                    headers: {
                        'content-type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                }

                // form data
                let formData = new FormData();
                formData.append('file', this.archivo_cc);
                formData.append('calculo_id',this.id_calculo);

                // send upload request
                axios.post('/store_cc', formData, config)
                    .then((response)=>
                        {
                            this.cc_ok=true;
                            this.cc_transacciones=response.data.pedidos;
                            this.toggleModalCC();
                            if(this.cuotas_ok && this.cc_ok && this.eq0_ok && this.cr0_ok) { this.calculo_ready=true };
                        })
                    .catch((error)=>
                        {
                        response.log(error);
                        });
            },
            cargarEQ0(e) {
                e.preventDefault();
                 const config = {
                    headers: {
                        'content-type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                }

                // form data
                let formData = new FormData();
                formData.append('file', this.archivo_eq0);
                formData.append('calculo_id',this.id_calculo);

                // send upload request
                axios.post('/store_eq0', formData, config)
                    .then((response)=>
                        {
                            this.eq0_ok=true;
                            this.eq0_transacciones=response.data.pedidos;
                            this.toggleModalEQ0();
                            if(this.cuotas_ok && this.cc_ok && this.eq0_ok && this.cr0_ok) { this.calculo_ready=true };
                        })
                    .catch((error)=>
                        {
                        response.log(error);
                        });
            },
            cargarCR0(e) {
                e.preventDefault();
                 const config = {
                    headers: {
                        'content-type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                }

                // form data
                let formData = new FormData();
                formData.append('file', this.archivo_cr0);
                formData.append('calculo_id',this.id_calculo);

                // send upload request
                axios.post('/store_cr0', formData, config)
                    .then((response)=>
                        {
                            this.cr0_ok=true;
                            this.cr0_transacciones=response.data.pedidos;
                            this.toggleModalCR0();
                            if(this.cuotas_ok && this.cc_ok && this.eq0_ok && this.cr0_ok) { this.calculo_ready=true };
                        })
                    .catch((error)=>
                        {
                        response.log(error);
                        });
            },
            cargarCB(e) {
                this.respuesta='Cargando CHARGE-BACK...';
                this.flag_cambios=true;
                e.preventDefault();
                 const config = {
                    headers: {
                        'content-type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                }

                // form data
                let formData = new FormData();
                formData.append('file', this.archivo_cb);
                formData.append('calculo_id',this.id_calculo);

                // send upload request
                axios.post('/store_cb', formData, config)
                    .then((response)=>
                        {
                            this.cb_ok=true;
                            this.cb_transacciones='Contratos Encontrados= '+response.data.recibidos+"; Contratos Aplicados= "+response.data.aplicados;
                            this.toggleModalCB();
                        })
                    .catch((error)=>
                        {
                        response.log(error);
                        });
            },
            ejecutarCalculo(){
                const parametros_a_enviar={
                    'id':this.id_calculo
                }
                this.respuesta4='Ejecutando Calculo Comisiones...';
                axios.put("/ejecutar_calculo",parametros_a_enviar).then((response)=>{
                        this.respuesta4='Ejecutando Balance Ejeutivos Venta ...';
                        this.fin_transacciones=response.data.transacciones_calculadas;
                        this.fin_cc=response.data.transacciones_cc;
                        axios.put("/genera_balance_ejecutivos",parametros_a_enviar).then((response2)=>{
                            this.respuesta4="Ejecutando Balace Gerentes de Tienda";
                            this.fin_personas=response2.data;
                            axios.put("/genera_balance_gerentes",parametros_a_enviar).then((response3)=>{
                                this.fin_sucursales=response3.data;
                                this.respuesta4="Ejecutando Balance Regionales...";
                                axios.put("/genera_balance_regionales",parametros_a_enviar).then((response4)=>{
                                    this.fin_regionales=response4.data;
                                    this.respuesta4="Ejecutando Balance Director Sucursales... ";
                                    axios.put("/genera_balance_director",parametros_a_enviar).then((response5)=>{
                                        this.fin_director=response5.data;
                                        this.respuesta4="Ejecutando Balance General..."
                                        axios.put("/genera_pagos",parametros_a_enviar).then((response5)=>{
                                            this.fin_ok=true;
                                            this.respuesta4="";
                                            this.terminar_calculo();
                                        });
                                    });

                                    

                                });
                            });
                        });

                    });
            },
            terminar_calculo() {
                const parametros = {
                        'id_calculo':this.id_calculo
                    }
                    axios.put('/terminar_calculo',parametros)
                    .then((response)=> {
                      console.log("Calculo "+this.id+" terminado");
                    });
            },
        }
}
</script>