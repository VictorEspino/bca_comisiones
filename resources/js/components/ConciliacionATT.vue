<template>
    <main class="flex-1 p-1 overflow-hidden">
        <div class="flex flex-col">
            <!-- SECCION DE CONTENIDO-->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row lg:space-x-5 md:space-y-0 sm:space-y-3 lg:space-y-0">
                <!--SECCION DE CATURA DE NUEVOS PARAMETROS-->
                <div class="bg-white border-solid border-gray-300 rounded-lg border shadow-sm w-full md:w-1/3 lg:w-1/3">
                    <div class="text-white py-3 px-2 rounded-t-lg bg-gradient-to-br from-pink-600 to-yellow-300 boder-solid border-gray-300 border-b text-white font-bold">
                        <i class="fas fa-link"></i>
                        Periodo de Carga de Informacion
                    </div>
                    <div class="p-3">
                        <form action="" class="w-full" v-on:submit.prevent="nuevaConciliacion()">
                             <div class="flex flex-col -mx-3 mb-6">
                                <div class="w-full px-3 mb-6 md:mb-0">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-light mb-1"
                                        for="f_inicio">
                                        Periodo de Informacion
                                    </label>
                                    <div v-if="this.etapa==0">
                                        <select class="block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white-500 focus:border-gray-600"
                                            name="periodo" type="text" placeholder="YYYY-MM-DD" v-model="periodo">
                                            <option value="2021-01">Enero 2021</option>
                                            <option value="2021-02">Febrero 2021</option>
                                            <option value="2021-03">Marzo 2021</option>
                                            <option value="2021-04">Abril 2021</option>
                                            <option value="2021-05">Mayo 2021</option>
                                            <option value="2021-06">Junio 2021</option>
                                            <option value="2021-07">Julio 2021</option>
                                        </select>
                                        <p class="text-red-500 text-xs italic">Campo obligatorio</p>
                                    </div>
                                    <div v-else class="px-2 py-3 border-solid border-gray-300 border-b text-gray-700 font-bold ">
                                        {{this.periodo}}
                                    </div>
                                    
                                </div>
                            </div>
                            <button  v-if="etapa==0" class="bg-green-500 hover:bg-green-800 text-white font-bold py-2 px-4 rounded-full" type="submit">
                                Iniciar
                            </button>
                        </form>
                    </div>
                </div>
                <!--TEMINA SECCION DE CAPTURA -->
                <!--INICIA SECCION DE AVANCES-->
                <div class="bg-white border-solid border-gray-300 rounded-lg border shadow-sm w-full md:w-2/3 lg:w-2/3">
                    <div class="text-white py-3 px-2 rounded-t-lg bg-gradient-to-br from-blue-700 to-green-300 boder-solid border-gray-300 border-b text-white font-bold">
                        <i class="fab fa-codepen"></i>
                        Parametros de Conciliacion

                    </div>    
                    <div class="p-3">
                        <div class="w-full py-3">
                            <div class="flex">
                                    <ul class="StepProgress w-full">
                                        <li v-if="this.etapa==0" class="StepProgress-item current">
                                            <strong>Registo de conciliacion</strong>
                                            {{this.respuesta0}}
                                        </li>
                                        <li v-if="this.etapa==1" class="StepProgress-item is-done">
                                            <strong>Registo de Conciliacion Finalizado</strong>
                                            <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Periodo: {{this.respuesta0_periodo}}</div></div>

                                        </li>
                                        <li class="StepProgress-item" v-bind:class="{'is-done':etapa==1&&comisiones_ok,'current':etapa==1&&!comisiones_ok}">
                                            <strong>Contratos pagados por AT&T</strong>
                                            <button v-if="etapa>=1 && !comisiones_ok" v-on:click="toggleModalComisionesATT()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">Cargar Comisiones</button>
                                            <div v-if="etapa>=1 && comisiones_ok" class="flex flex-row w-2/3">
                                                <div class="flex-shrink-0 flex-auto w-5/6">
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Registros: {{this.registros_comisiones}}</div></div>
                                                </div>
                                                <div class="flex flex-wrap content-center"><div><button v-on:click="toggleModalComisionesATT()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">Refresh</button></div></div>
                                            </div>
                                        </li>
                                        <li class="StepProgress-item" v-bind:class="{'is-done':etapa==1&&residual_ok,'current':etapa==1&&!residual_ok}">
                                            <strong>Residual pagado por AT&T</strong>
                                            <button v-if="etapa>=1 && !residual_ok" v-on:click="toggleModalResidualATT()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">Cargar Residual</button>
                                            <div v-if="etapa>=1 && residual_ok" class="flex flex-row w-2/3">
                                                <div class="flex-shrink-0 flex-auto w-5/6">
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Registros: {{this.registros_residual}}</div></div>
                                                </div>
                                                <div class="flex flex-wrap content-center"><div><button v-on:click="toggleModalResidualATT()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">Refresh</button></div></div>
                                            </div>
                                        </li>
                                        <li class="StepProgress-item" v-bind:class="{'is-done':etapa==1&&cb_ok,'current':etapa==1&&!cb_ok}">
                                            <strong>Charge-Back</strong>
                                            <button v-if="etapa>=1 && !cb_ok" v-on:click="toggleModalCBATT()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">Cargar Charge-Back</button>
                                            <div v-if="etapa>=1 && cb_ok" class="flex flex-row w-2/3">
                                                <div class="flex-shrink-0 flex-auto w-5/6">
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Registros: {{this.registros_cb}}</div></div>
                                                </div>
                                                <div class="flex flex-wrap content-center"><div><button v-on:click="toggleModalCBATT()" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded">Refresh</button></div></div>
                                            </div>
                                        </li>
                                        <li class="StepProgress-item" v-bind:class="{'current':conciliacion_ready&&!fin_ok,'is-done':conciliacion_ready&&fin_ok}">
                                            <div v-if="conciliacion_ready">
                                                <strong>Ejecutar Conciliacion</strong>
                                                <!--<button v-on:click="ejecutarCalculo()" class="px-2 bg-blue-500 hover:bg-blue-800 text-white font-bold rounded-full">Ejecutar</button>-->
                                                    {{this.respuesta2}}
                                                <div class="flex flex-row w-2/3">
                                                <div class="flex-shrink-0 flex-auto w-5/6">
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Transacciones no pagadas AT&T: {{this.registros_erp_att}}</div></div>
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Sin residual a 45d AT&T: {{this.registros_45d}}</div></div>
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Alerta de Fraude 1: {{this.registros_aviso1}}</div></div>
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Alerta de Fraude 2: {{this.registros_aviso2}}</div></div>
                                                    <div class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Alerta de CHARGE-BACK: {{this.registros_alerta_cb}}</div></div>
                                                </div>
                                                <div class="flex flex-wrap content-center"><div><button v-on:click="ejecutarConciliacion()" class="px-2 bg-blue-500 hover:bg-blue-800 text-white font-bold rounded">Ejecutar Conciliacion y Alertas</button></div></div>
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
        
 

    </main>
            <!--/Main-->
</template>
<script>
    export default{
        data(){
            return{
                showModalComisionesATT:false,
                showModalResidualATT:false,
                showModalCBATT:false,

                
                id_conciliacion:0,
                periodo:'',
                respuesta0:'',
                respuesta2:'',
                etapa:0,
                respuesta0_periodo:'',
                registros_comisiones:'',
                registros_residual:'',
                registros_cb:'',
                registros_erp_att:'',
                registros_45d:'',
                registros_aviso1:'',
                registros_aviso2:'',
                registros_alerta_cb:'',

                archivo_comisiones:'',
                archivo_residual:'',
                archivo_cb:'',
                conciliacion_ready:false,

                comisiones_ok:false,
                residual_ok:false,
                cb_ok:false,
                fin_ok:false

                          
            }
        },
       mounted(){ 
            console.log('Template de Captura OK')
        },
        methods:{
            toggleModalComisionesATT(){
                this.showModalComisionesATT = !this.showModalComisionesATT;
            },
            toggleModalResidualATT(){
                this.showModalResidualATT = !this.showModalResidualATT;
            },
            toggleModalCBATT(){
                this.showModalCBATT = !this.showModalCBATT;
            },
            nuevaConciliacion(){
				const parametros_a_enviar={
                    'periodo':this.periodo,
                }
                this.respuesta0="Realizando Setup Conciliacion... "
                axios.put("/setup_conciliacion",parametros_a_enviar).then((response)=>{
                    console.log(response.data);
                    
                    if(response.data.id!=0)
                        {
                            this.etapa=1;
                            this.respuesta0_periodo=response.data.periodo;
                            this.id_conciliacion=response.data.id;
                        }
                    else{
                            this.respuesta0='Periodo '+this.periodo+' ya procesado';
                        }

                    }
                );
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
                formData.append('conciliacion_id',this.id_conciliacion);

                // send upload request
                axios.post('/store_comisiones', formData, config)
                    .then((response)=>
                        {
                            this.comisiones_ok=true;
                            this.registros_comisiones=response.data.registros;
                            this.toggleModalComisionesATT();
                            if(this.comisiones_ok && this.residual_ok && this.cb_ok) { this.conciliacion_ready=true };
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
                formData.append('conciliacion_id',this.id_conciliacion);
                formData.append('periodo',this.periodo);

                // send upload request
                axios.post('/store_residual', formData, config)
                    .then((response)=>
                        {
                            this.residual_ok=true;
                            this.registros_residual=response.data.registros;
                            this.toggleModalResidualATT();
                            if(this.comisiones_ok && this.residual_ok && this.cb_ok) { this.conciliacion_ready=true };
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
                formData.append('conciliacion_id',this.id_conciliacion);
                formData.append('periodo',this.periodo);

                // send upload request
                axios.post('/store_cb_att', formData, config)
                    .then((response)=>
                        {
                            this.cb_ok=true;
                            this.registros_cb=response.data.registros;
                            this.toggleModalCBATT();
                            if(this.comisiones_ok && this.residual_ok && this.cb_ok) { this.conciliacion_ready=true };
                        })
                    .catch((error)=>
                        {
                        response.log(error);
                        });
            },
            
            ejecutarConciliacion(){
                const parametros_a_enviar={
                    'conciliacion_id':this.id_conciliacion,
                    'periodo':this.periodo

                }
                this.respuesta2='Revisando lineas en ERP sin coincidencia AT&T';
                axios.put("/conciliacion_erp_att",parametros_a_enviar).then((response)=>{
                        this.respuesta2='Revisando lineas con residual inicial AT&T';
                        this.registros_erp_att=response.data.registros;
                        axios.put("/residual_45dias",parametros_a_enviar).then((response2)=>{
                            this.respuesta2='Evaluando lineas con alerta de fraude (2 eventos sin pago)';
                            this.registros_45d=response2.data.registros;
                            axios.put("/fraude_aviso1",parametros_a_enviar).then((response3)=>{
                                this.respuesta2='Evaluando lineas con alerta de fraude (3 eventos sin pago)';
                                this.registros_aviso1=response3.data.registros;
                                axios.put("/fraude_aviso2",parametros_a_enviar).then((response4)=>{
                                    this.respuesta2='Evaluando lineas con alerta de CHARGE-BACK';
                                    this.registros_aviso2=response4.data.registros;
                                    axios.put("/alerta_cb",parametros_a_enviar).then((response5)=>{
                                        this.respuesta2='';
                                        this.registros_alerta_cb=response5.data.registros;
                                        this.fin_ok=true;
                                        this.terminar_conciliacion();
                                    });
                                });
                            });
                        });
 

                    });
            },
            terminar_conciliacion() {
                const parametros = {
                        'conciliacion_id':this.id_conciliacion
                    }
                    axios.put('/terminar_conciliacion',parametros)
                    .then((response)=> {
                      console.log("Conciliacion "+this.id_conciliacion+" terminada");
                    });
            },
        }
}
</script>