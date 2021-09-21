<template>
<div>
    <div class="flex flex-row w-full border-2 p-3 shadow-md bg-blue-100">
        <div class="flex flex-col" v-bind:class="edit_mode?'w-1/2':'w-10/12'">
            <div class="font-bold text-xl">{{descripcion}}</div>
            <div v-if="!screen_terminado" class="text-lg text-red-700">Calculo Pendiente</div>
            <div v-if="screen_terminado" class="text-lg text-green-700">Calculo Terminado</div>
            <div class="font-thin italic text-sm">De {{fecha_inicio}} a {{fecha_fin}}</div>
            <div class="font-thin italic text-sm">Actualizado {{actualizacion}}</div>
        </div>
        <div class="flex flex-col" v-bind:class="edit_mode?'w-1/2':'w-1/12'">
            <div class="flex flex-row space-x-5 w-full">
                <div class="w-1/12">
                    <a v-bind:href='"/detalle_calculo/"+id'>
                        <i class="fas fa-bars" style='font-size:20px;color:red'></i>
                    </a>
                </div>
                <div v-if="ultimo==id || 1==1" class="w-1/12 font-bold text-green-600">
                    <i class="fas fa-edit cursor-pointer" v-on:click="toogleEditMode()"></i>
                </div>
                <div v-if="edit_mode" class="flex flex-col w-10/12 space-y-3">
                    <div class="flex flex-row space-x-3">
                        <div class="w-1/2">
                            <button v-on:click="toggleModalCuotas()" class="w-full px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">
                                <span v-if="screen_cuotas" class="text-sm font-bold text-green-700"><i class="fas fa-check"></i></span>
                                <span v-if="!screen_cuotas" class="text-sm font-bold text-red-700"><i class="fas fa-times"></i></span>
                                Cuotas
                            </button>
                        </div>
                        <div class="w-1/2">
                            <button v-on:click="toggleModalCC()" class="w-full px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">
                                <span v-if="screen_cc" class="text-sm font-bold text-green-700"><i class="fas fa-check"></i></span>
                                <span v-if="!screen_cc" class="text-sm font-bold text-red-700"><i class="fas fa-times"></i></span>
                                Registros CC
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-row space-x-3">
                        <div class="w-1/2">
                            <button v-on:click="toggleModalEQ0()" class="w-full px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">
                                <span v-if="screen_eq0" class="text-sm font-bold text-green-700"><i class="fas fa-check"></i></span>
                                <span v-if="!screen_eq0" class="text-sm font-bold text-red-700"><i class="fas fa-times"></i></span>
                                Equipos sin Costo
                            </button>
                        </div>
                        <div class="w-1/2">
                            <button v-on:click="toggleModalCR0()" class="w-full px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">
                                <span v-if="screen_cr0" class="text-sm font-bold text-green-700"><i class="fas fa-check"></i></span>
                                <span v-if="!screen_cr0" class="text-sm font-bold text-red-700"><i class="fas fa-times"></i></span>
                                Contratos no comisionables
                            </button>
                        </div>
                    </div>
                    <div class="w-full">
                        <button v-on:click="toggleModalCB()" class="px-2 bg-red-500 hover:bg-red-800 text-white font-bold rounded w-full">
                            <span v-if="screen_cb" class="text-sm font-bold text-green-700"><i class="fas fa-check"></i></span>
                            <span v-if="!screen_cb" class="text-sm font-bold text-red-700"><i class="fas fa-times"></i></span>
                            Charge-Back
                        </button>
                    </div>   
                    <div class="w-full">
                        <button v-on:click="ejecutarCalculo()" class="px-2 bg-blue-500 hover:bg-blue-800 text-white font-bold rounded w-full">Ejecutar Calculo</button>
                    </div> 
                    <div class="w-full">
                        <span class="font-thin text-sm italic">{{respuesta}}</span>
                    </div> 
                 
                </div>
            </div>
        </div>
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

</div>
</template>
<script>
export default {
    props:['id',
           'ultimo',
           'descripcion',
           'fecha_inicio',
           'fecha_fin',
           'cuotas',
           'cc',
           'eq0',
           'cr0',
           'cb',
           'terminado',
           'actualizado'
           ],
    data(){
			return {
                actualizacion:'',
                edit_mode:false,
                showModalCuotas:false,
                showModalCC:false,
                showModalEQ0:false,
                showModalCR0:false,
                showModalCB:false,
                archivo_cuotas:'',
                archivo_cc:'',
                archivo_eq0:'',
                archivo_cr0:'',
                archivo_cb:'',
                respuesta:'',
                respuesta_provisional:'',
                flag_cambios:false,
                screen_cuotas:'',
                screen_cc:'',
                screen_eq0:'',
                screen_cr0:'',
                screen_cb:'',
                screen_terminado:'',
                screen_actualizado:''
				}
		},
    mounted(){ 
        this.actualizacion=this.actualizado.substring(0,10);
        this.screen_cuotas=this.cuotas;
        this.screen_cc=this.cc;
        this.screen_eq0=this.eq0;
        this.screen_cr0=this.cr0;
        this.screen_cb=this.cb;
        this.screen_terminado=this.terminado;
        this.screen_actualizado=this.actualizado;
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
        toogleEditMode()
        {
            this.edit_mode=!this.edit_mode;
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
                this.respuesta='Cargando Cuotas...';
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
                formData.append('file', this.archivo_cuotas);
                formData.append('calculo_id',this.id);

                // send upload request
                axios.post('/store_cuotas', formData, config)
                    .then((response)=>
                        {
                            this.respuesta='ACTUALIZACION DE CUOTAS => Activaciones= '+response.data.activaciones+'; Activaciones Eq Propio= '+response.data.aep+'; Renovaciones= '+response.data.renovaciones+'; Renovaciones Eq Propio= '+response.data.rep;
                            this.reload();
                            this.toggleModalCuotas();
                        })
                    .catch((error)=>
                        {
                        response.log(error);
                        });
            },
        cargarCC(e) {
                this.respuesta='Cargando CC...';
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
                formData.append('file', this.archivo_cc);
                formData.append('calculo_id',this.id);

                // send upload request
                axios.post('/store_cc', formData, config)
                    .then((response)=>
                        {
                            this.respuesta='ACTUALIZACION PEDIDOS CC => Pedidos= '+response.data.pedidos;
                            this.reload();
                            this.toggleModalCC();
                        })
                    .catch((error)=>
                        {
                        response.log(error);
                        });
            },
        cargarEQ0(e) {
                this.respuesta='Cargando EQ sin Costo...';
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
                formData.append('file', this.archivo_eq0);
                formData.append('calculo_id',this.id);

                // send upload request
                axios.post('/store_eq0', formData, config)
                    .then((response)=>
                        {
                            this.respuesta='ACTUALIZACION EQUIPOS SIN COSTO => Pedidos= '+response.data.pedidos;
                            this.reload();
                            this.toggleModalEQ0();
                        })
                    .catch((error)=>
                        {
                        response.log(error);
                        });
            },
        cargarCR0(e) {
                this.respuesta='Cargando no comisionables...';
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
                formData.append('file', this.archivo_cr0);
                formData.append('calculo_id',this.id);

                // send upload request
                axios.post('/store_cr0', formData, config)
                    .then((response)=>
                        {
                            this.respuesta='ACTUALIZACION TRANSACCIONES NO COMISIONABLES => Transacciones= '+response.data.pedidos;
                            this.reload();
                            this.toggleModalCR0();
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
                formData.append('calculo_id',this.id);

                // send upload request
                axios.post('/store_cb', formData, config)
                    .then((response)=>
                        {
                            this.respuesta='ACTUALIZACION CHARGE-BACK => Contratos Encontrados= '+response.data.recibidos+"; Contratos Aplicados= "+response.data.aplicados;
                            this.reload();
                            this.toggleModalCB();
                        })
                    .catch((error)=>
                        {
                        response.log(error);
                        });
            },    
        ejecutarCalculo(){
                this.respuesta_provisional='';
                this.flag_cambios=true;
                const parametros_a_enviar={
                    'id':this.id
                }
                this.respuesta='Ejecutando Calculo Comisiones...';
                axios.put("/ejecutar_calculo",parametros_a_enviar).then((response)=>{
                        this.respuesta4='Ejecutando Balance Ejeutivos Venta ...';
                        this.respuesta_provisional="ACTUALIZACION CALCULO => Transacciones Calculadas= "+response.data.transacciones_calculadas;
                        this.respuesta_provisional=this.respuesta_provisional+"; Transacciones CC= "+response.data.transacciones_cc;
                        axios.put("/genera_balance_ejecutivos",parametros_a_enviar).then((response2)=>{
                            this.respuesta="Ejecutando Balace Gerentes de Tienda ...";
                            this.respuesta_provisional=this.respuesta_provisional+"; Personas Medidas= "+response2.data;
                            axios.put("/genera_balance_gerentes",parametros_a_enviar).then((response3)=>{
                                this.respuesta_provisional=this.respuesta_provisional+"; Sucursales Medidas= "+response3.data;
                                this.respuesta="Ejecutando Balance Regionales...";
                                axios.put("/genera_balance_regionales",parametros_a_enviar).then((response4)=>{
                                    this.respuesta_provisional=this.respuesta_provisional+"; Regiones Medidas= "+response4.data;
                                    this.respuesta="Ejecutando Balance Director Sucursales... ";
                                    axios.put("/genera_balance_director",parametros_a_enviar).then((response5)=>{
                                        this.respuesta_provisional=this.respuesta_provisional+"; Directores= "+response5.data;
                                        this.respuesta="Generando Payments..."
                                        axios.put("/genera_pagos",parametros_a_enviar).then((response6)=>{
                                            this.respuesta_provisional=this.respuesta_provisional+"; Pagos Generados"
                                            this.respuesta=this.respuesta_provisional;
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
                        'id_calculo':this.id
                    }
                    axios.put('/terminar_calculo',parametros)
                    .then((response)=> {
                      console.log("Calculo "+this.id+" terminado");
                      this.reload();
                    });
            },
        reload(){
                const parametros = {
                        'id_calculo':this.id
                    }
                    axios.put('/consultar_calculo',parametros)
                    .then((response)=> {
                      console.log("Calculo "+this.id+" recargado");
                      this.screen_cuotas=response.data[0].cuotas;
                      this.screen_cc=response.data[0].cc;
                      this.screen_eq0=response.data[0].eq0;
                      this.screen_cr0=response.data[0].cr0;
                      this.screen_cb=response.data[0].cb;
                      this.screen_terminado=response.data[0].terminado;
                      this.screen_actualizado=response.data[0].updated_at;
                      this.actualizacion=this.actualizado.substring(0,10);
                    });
        }
    }
}
</script>