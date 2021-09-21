<template>
    <main class="flex-1 p-1 overflow-hidden">
        <div class="flex flex-col">
            <!-- SECCION DE CONTENIDO-->
            <div class="flex flex-1 flex-col">
                <!--SECCION DE CATURA DE NUEVOS PARAMETROS-->
                <div class="bg-white border-solid border-gray-300 rounded-lg border shadow-sm w-full mb-3">
                    <div class="text-white py-3 px-2 rounded-t-lg bg-gradient-to-br from-pink-600 to-yellow-300 boder-solid border-gray-300 border-b text-white font-bold">
                        <i class="fas fa-link"></i>
                        Carga de Archivo de Charge Back
                    </div>
                    <div class="p-3">
                        <form @submit="cargarComisiones" enctype="multipart/form-data">
                        <div class="flex flex-wrap  mb-6">
                            <div class="w-full px-3 mt-3">
                                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                    Archivo de charge back
                                </label>
                                <input v-on:change="onFileChangeComisiones" class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="cuotas"  type="file">
                            </div>
                        </div>
                        <div class="flex flex-row  mb-6">
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                    Periodo
                                </label>
                                <select v-model="mes">
                                    <option value="Ene">Ene</option>
                                    <option value="Feb">Feb</option>
                                    <option value="Mar">Mar</option>
                                    <option value="Abr">Abr</option>
                                    <option value="May">May</option>
                                    <option value="Jun">Jun</option>
                                    <option value="Jul">Jul</option>
                                    <option value="Ago">Ago</option>
                                    <option value="Sep">Sep</option>
                                    <option value="Oct">Oct</option>
                                    <option value="Nov">Nov</option>
                                    <option value="Dic">Dic</option>
                                </select>
                            </div>
                            <div class="w-full px-3">
                                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                    Periodo
                                </label>
                                <select v-model="anno">
                                    <option value="2019">2019</option>
                                    <option value="2020">2020</option>
                                    <option value="2021">2021</option>
                                    <option value="2022">2022</option>
                                </select>
                            </div>
                        </div>
                        <!--footer-->
                        <div class="flex items-center justify-end p-2 border-t border-solid border-gray-300 rounded-b">
                            <input type="submit" class="px-2 bg-green-500 hover:bg-green-800 text-white font-bold rounded-full" value="Cargar">
                        </div>
                    </form>
                    </div>

                    <div v-if="r_ok" class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Estatus: {{respuesta.success}}</div></div>
                    <div v-if="r_ok" class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Periodo: {{respuesta.periodo}}</div></div>
                    <div v-if="r_ok" class="text-green-600 px-2 font-bold flex flex-row space-x-3 text-sm mb-4"><div><i class="fas fa-check"></i></div><div class="text-gray-700 font-thin">Lineas Cargadas: {{respuesta.lineas}}</div></div>
                </div>
                <!--TEMINA SECCION DE CAPTURA -->
            </div>
                    <!-- CIERRA LA SECCION DE CONTENIDO -->
        </div>

    </main>
            <!--/Main-->
</template>
<script>
    export default{
        data(){
            return{
                
                id_calculo:0,
                archivo_comisiones:'', 
                periodo_cargado:'', 
                mes:'',
                anno:'',
                respuesta:'',
                r_ok:false   

            }
        },
       mounted(){ 
            console.log('Template de Residual OK')
        },
        methods:{
            onFileChangeComisiones(e) {
                this.archivo_comisiones = e.target.files[0];
            },
            
            cargarComisiones(e) {
                e.preventDefault();
                 const config = {
                    headers: {
                        'content-type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                }

                // form data
                this.periodo_cargado=this.mes+"-"+this.anno;
                let formData = new FormData();
                formData.append('file', this.archivo_comisiones);
                formData.append('calculo_id',this.id_calculo);
                formData.append('periodo_cargado',this.periodo_cargado);

                // send upload request
                axios.post('/store_comisiones', formData, config)
                    .then((response)=>
                        {
                            console.log(response.data);
                            this.respuesta=response.data;
                            this.r_ok=true;
                        })
                    .catch((error)=>
                        {
                        response.log(error);
                        });
                    }
        }
}
</script>