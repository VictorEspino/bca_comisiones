<template>
<div>
    <div class="flex flex-row w-full border-2 p-3 shadow-md bg-blue-100">
        <div class="flex flex-col" v-bind:class="edit_mode?'w-1/2':'w-10/12'">
            <div class="font-bold text-xl">{{this.periodo}}</div>
            <div v-if="!screen_terminado" class="text-lg text-red-700">Conciliacion Pendiente</div>
            <div v-if="screen_terminado" class="text-lg text-green-700">Conciliacion Terminada</div>
            <div class="font-thin italic text-sm">Actualizado {{actualizacion}}</div>
        </div>
        <div class="flex flex-col" v-bind:class="edit_mode?'w-1/2':'w-1/12'">
            <div class="flex flex-row space-x-5 w-full">
                <div class="w-1/12">
                    <a v-bind:href='"/detalle_alertas/"+periodo+"/"+id'>
                        <i class="fas fa-bars" style='font-size:20px;color:red'></i>
                    </a>
                </div>
                <div class="w-1/12 font-bold text-green-600">
                    <i class="fas fa-edit cursor-pointer" v-on:click="toogleEditMode()"></i>
                </div>
                <div v-if="edit_mode" class="flex flex-col w-10/12 space-y-3">
                    <div class="flex flex-row space-x-3">
                        <div class="w-full">
                            <button v-on:click="toggleModalComisionesATT()" class="w-full px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">
                                <span v-if="screen_comisiones_att" class="text-sm font-bold text-green-700"><i class="fas fa-check"></i></span>
                                <span v-if="!screen_comisiones_att" class="text-sm font-bold text-red-700"><i class="fas fa-times"></i></span>
                                Comisiones ATT
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-row space-x-3">
                        <div class="w-full">
                            <button v-on:click="toggleModalResidualATT()" class="w-full px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">
                                <span v-if="screen_residual_att" class="text-sm font-bold text-green-700"><i class="fas fa-check"></i></span>
                                <span v-if="!screen_residual_att" class="text-sm font-bold text-red-700"><i class="fas fa-times"></i></span>
                                Residual ATT
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-row space-x-3">
                        <div class="w-full">
                            <button v-on:click="toggleModalCBATT()" class="w-full px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">
                                <span v-if="screen_cb_att" class="text-sm font-bold text-green-700"><i class="fas fa-check"></i></span>
                                <span v-if="!screen_cb_att" class="text-sm font-bold text-red-700"><i class="fas fa-times"></i></span>
                                Charge-Back ATT
                            </button>
                        </div>
                    </div> 
                    <div class="w-full">
                        <button v-on:click="ejecutarConciliacion()" class="px-2 bg-blue-500 hover:bg-blue-800 text-white font-bold rounded w-full">Ejecutar Conciliacion</button>
                    </div> 
                    <div class="w-full">
                        <span class="font-thin text-sm italic">{{respuesta}}</span>
                    </div> 
                 
                </div>
            </div>
        </div>
    </div>
            <!--MODAL DE COMISIONES-->
        <div v-if="showModalComisionesATT" class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center flex">
            <div class="relative w-11/12 md:w-3/5 lg:w-2/5 my-6 mx-auto max-w-3xl">
                <!--content-->
                <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                    <!--header-->
                    <div class="flex items-start justify-between p-3 border-b border-solid border-grey-300 rounded-t">
                        <h3 class="text-xl font-semibold">
                            Carga de Comisiones AT&T
                        </h3>
                        <button class="p-1 ml-auto bg-transparent border-0 text-black opacity-5 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" v-on:click="toggleModalComisionesATT()">
                            <span class="bg-transparent text-black opacity-5 h-6 w-6 text-2xl block outline-none focus:outline-none">
                                ×
                            </span>
                        </button>
                    </div>
                    <!--body-->
                    <form @submit="cargarComisionesATT" enctype="multipart/form-data" class="w-full max-w-lg px-4 py-3" >
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                    Archivo de comisiones
                                </label>
                                <input v-on:change="onFileChangeComisionesATT" class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="cuotas"  type="file">
                            </div>
                        </div>
                        <!--footer-->
                        <div class="flex items-center justify-end p-2 border-t border-solid border-gray-300 rounded-b">
                            <input type="submit" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full" value="Cargar">
                            <button v-on:click="toggleModalComisionesATT()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full">Cerrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showModalComisionesATT" class="opacity-25 fixed inset-0 z-40 bg-black"></div>
        <!--MODAL DE RESIDUAL-->
        <div v-if="showModalResidualATT" class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center flex">
            <div class="relative w-11/12 md:w-3/5 lg:w-2/5 my-6 mx-auto max-w-3xl">
                <!--content-->
                <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                    <!--header-->
                    <div class="flex items-start justify-between p-3 border-b border-solid border-grey-300 rounded-t">
                        <h3 class="text-xl font-semibold">
                            Residuales AT&T
                        </h3>
                        <button class="p-1 ml-auto bg-transparent border-0 text-black opacity-5 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" v-on:click="toggleModalResidualATT()">
                            <span class="bg-transparent text-black opacity-5 h-6 w-6 text-2xl block outline-none focus:outline-none">
                                ×
                            </span>
                        </button>
                    </div>
                    <!--body-->
                    <form @submit="cargarResidualATT" enctype="multipart/form-data" class="w-full max-w-lg px-4 py-3" >
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                    Archivo de residuales
                                </label>
                                <input v-on:change="onFileChangeResidualATT" class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="cuotas"  type="file">
                            </div>
                        </div>
                        <!--footer-->
                        <div class="flex items-center justify-end p-2 border-t border-solid border-gray-300 rounded-b">
                            <input type="submit" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full" value="Cargar">
                            <button v-on:click="toggleModalResidualATT()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full">Cerrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showModalResidualATT" class="opacity-25 fixed inset-0 z-40 bg-black"></div>
        <!--MODAL DE CB-->
        <div v-if="showModalCBATT" class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center flex">
            <div class="relative w-11/12 md:w-3/5 lg:w-2/5 my-6 mx-auto max-w-3xl">
                <!--content-->
                <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                    <!--header-->
                    <div class="flex items-start justify-between p-3 border-b border-solid border-grey-300 rounded-t">
                        <h3 class="text-xl font-semibold">
                            Contratos Charge-Back
                        </h3>
                        <button class="p-1 ml-auto bg-transparent border-0 text-black opacity-5 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" v-on:click="toggleModalCBATT()">
                            <span class="bg-transparent text-black opacity-5 h-6 w-6 text-2xl block outline-none focus:outline-none">
                                ×
                            </span>
                        </button>
                    </div>
                    <!--body-->
                    <form @submit="cargarCBATT" enctype="multipart/form-data" class="w-full max-w-lg px-4 py-3" >
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                    Archivo de Charge-Back
                                </label>
                                <input v-on:change="onFileChangeCBATT" class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="cuotas"  type="file">
                            </div>
                        </div>
                        <!--footer-->
                        <div class="flex items-center justify-end p-2 border-t border-solid border-gray-300 rounded-b">
                            <input type="submit" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full" value="Cargar">
                            <button v-on:click="toggleModalCBATT()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full">Cerrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showModalCBATT" class="opacity-25 fixed inset-0 z-40 bg-black"></div>

</div>
    
    
</template>
<script>
export default {
    props:['id',
           'periodo',
           'comisiones_att',
           'residual_att',
           'charge_back_att',
           'terminado',
           'updated_at',
           'ultimo'
           ],
    data(){
			return {
                edit_mode:false,
                screen_terminado:false,
                screen_comisiones_att:false,
                screen_residual_att:false,
                screen_cb_att:false,
                actualizacion:'',
                respuesta:'',
                respuesta_provisional:'',
                showModalComisionesATT:false,
                showModalResidualATT:false,
                showModalCBATT:false,
                
                archivo_comisiones:'',
                archivo_residual:'',
                archivo_cb:'',



				}
		},
    mounted(){
            this.screen_terminado=this.terminado;
            this.actualizacion=this.updated_at.substring(0,10);
            this.screen_comisiones_att=this.comisiones_att;
            this.screen_residual_att=this.residual_att;
            this.screen_cb_att=this.charge_back_att;
    },
    methods:{
        toogleEditMode(){
                this.edit_mode = !this.edit_mode;
            },
        toggleModalComisionesATT(){
                this.showModalComisionesATT = !this.showModalComisionesATT;
            },
        toggleModalResidualATT(){
                this.showModalResidualATT = !this.showModalResidualATT;
            },
        toggleModalCBATT(){
                this.showModalCBATT = !this.showModalCBATT;
            },
        onFileChangeComisionesATT(e) {
                this.archivo_comisiones = e.target.files[0];
            },
        onFileChangeResidualATT(e) {
                this.archivo_residual = e.target.files[0];
            },
        onFileChangeCBATT(e) {
                this.archivo_cb = e.target.files[0];
            },
        cargarComisionesATT(e) {
                e.preventDefault();
                 const config = {
                    headers: {
                        'content-type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                }

                // form data
                let formData = new FormData();
                formData.append('file', this.archivo_comisiones);
                formData.append('periodo',this.periodo);
                formData.append('conciliacion_id',this.id);

                // send upload request
                axios.post('/store_comisiones', formData, config)
                    .then((response)=>
                        {
                            this.respuesta='ACTUALIZACION COMISIONES AT&T => Registros= '+response.data.registros;
                            this.reload();
                            this.toggleModalComisionesATT();
                        })
                    .catch((error)=>
                        {
                        response.log(error);
                        });
            },
        cargarResidualATT(e) {
                e.preventDefault();
                 const config = {
                    headers: {
                        'content-type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                }

                // form data
                let formData = new FormData();
                formData.append('file', this.archivo_residual);
                formData.append('conciliacion_id',this.id);
                formData.append('periodo',this.periodo);

                // send upload request
                axios.post('/store_residual', formData, config)
                    .then((response)=>
                        {
                            this.respuesta='ACTUALIZACION RESIDUAL AT&T => Registros= '+response.data.registros;
                            this.reload();
                            this.toggleModalResidualATT();
                        })
                    .catch((error)=>
                        {
                        response.log(error);
                        });
            },
        cargarCBATT(e) {
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
                formData.append('conciliacion_id',this.id);
                formData.append('periodo',this.periodo);

                // send upload request
                axios.post('/store_cb_att', formData, config)
                    .then((response)=>
                        {
                            this.respuesta='ACTUALIZACION CHARGE_BACK AT&T => Registros= '+response.data.registros;
                            this.reload();
                            this.toggleModalCBATT();
                        })
                    .catch((error)=>
                        {
                        response.log(error);
                        });
            },
        reload(){
                const parametros = {
                        'conciliacion_id':this.id
                    }
                    axios.put('/consultar_conciliacion',parametros)
                    .then((response)=> {
                      console.log("Conciliacion "+this.id+" recargada");
                      this.screen_terminado=response.data[0].terminado;
                      this.actualizacion=response.data[0].updated_at.substring(0,10);
                      this.screen_comisiones_att=response.data[0].comisiones_att;
                      this.screen_residual_att=response.data[0].residual_att;
                      this.screen_cb_att=response.data[0].charge_back_att;
                    });
        },
        ejecutarConciliacion(){
                const parametros_a_enviar={
                    'conciliacion_id':this.id,
                    'periodo':this.periodo

                }
                this.respuesta='Revisando lineas en ERP sin coincidencia AT&T';
                axios.put("/conciliacion_erp_att",parametros_a_enviar).then((response)=>{
                        this.respuesta='Revisando lineas con residual inicial AT&T';
                        this.respuesta_provisional="Transacciones no pagadas AT&T = "+response.data.registros;
                        axios.put("/residual_45dias",parametros_a_enviar).then((response2)=>{
                            this.respuesta='Evaluando lineas con alerta de fraude (2 eventos sin pago)';
                            this.respuesta_provisional=this.respuesta_provisional+"; Sin residual a 45d AT&T = "+response2.data.registros;
                            axios.put("/fraude_aviso1",parametros_a_enviar).then((response3)=>{
                                this.respuesta='Evaluando lineas con alerta de fraude (3 eventos sin pago)';
                                this.respuesta_provisional=this.respuesta_provisional+"; Alerta de Fraude 1 = "+response3.data.registros;
                                axios.put("/fraude_aviso2",parametros_a_enviar).then((response4)=>{
                                    this.respuesta='Evaluando lineas con alerta de CHARGE-BACK';
                                    this.respuesta_provisional=this.respuesta_provisional+"; Alerta de Fraude 2 = "+response4.data.registros;
                                    axios.put("/alerta_cb",parametros_a_enviar).then((response5)=>{
                                        this.respuesta_provisional=this.respuesta_provisional+"; Alerta de CHARGE-BACK = "+response5.data.registros;
                                        this.respuesta=this.respuesta_provisional;
                                        this.terminar_conciliacion();
                                        this.reload();
                                    });
                                });
                            });
                        });
 

                    });
            },
        terminar_conciliacion() {
                const parametros = {
                        'conciliacion_id':this.id
                    }
                    axios.put('/terminar_conciliacion',parametros)
                    .then((response)=> {
                      console.log("Conciliacion "+this.id+" terminada");
                    });
            },
    }
}
</script>