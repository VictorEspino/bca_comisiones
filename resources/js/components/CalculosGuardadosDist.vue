<template>
    <div class="flex flex-1  flex-col md:flex-row lg:flex-row mx-2">
        <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
            <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                <p class="text-grey-dark text-xl font-bold">
                    Calculos Historicos Distribuidores
                </p>
            </div>
            <div class="p-3 flex flex-row space-x-3">
                <div class="w-1/2 flex flex-col space-y-3">
                    <registro_calculo_dist v-for="calculo in Calculos_s"
                        :key="calculo.id"
                        :id="calculo.id"
                        :descripcion="calculo.descripcion"
                        :fecha_inicio="calculo.fecha_inicio"
                        :fecha_fin="calculo.fecha_fin"
                        :pagado_en="calculo.pagado_en"
                        :eq0="calculo.eq0"
                        :cr0="calculo.cr0"
                        :terminado="calculo.terminado"
                        :actualizado="calculo.updated_at"
                        :ultimo="ultimo"
                    >
                    </registro_calculo_dist>
                </div>
                <div class="w-1/2 flex flex-col space-y-3">
                    <registro_calculo_dist v-for="calculo in Calculos_m"
                        :key="calculo.id"
                        :id="calculo.id"
                        :descripcion="calculo.descripcion"
                        :fecha_inicio="calculo.fecha_inicio"
                        :fecha_fin="calculo.fecha_fin"
                        :pagado_en="calculo.pagado_en"
                        :eq0="calculo.eq0"
                        :cr0="calculo.cr0"
                        :terminado="calculo.terminado"
                        :actualizado="calculo.updated_at"
                        :ultimo="ultimo"
                    >
                    </registro_calculo_dist>
                </div>
            </div>
        </div> 
    </div>
</template>
<script>
export default {
    data(){
			return {
				  Calculos_s : [],
                  Calculos_m : [],
                  ultimo:''
				}
		},
    mounted(){
        axios.get('/calculos_guardados_dist_s').then((response)=>
                 {
                    this.Calculos_s=response.data;
                    this.ultimo=this.Calculos_s[0].id;
                 }
                );
        axios.get('/calculos_guardados_dist_m').then((response)=>
                 {
                    this.Calculos_m=response.data;
                    this.ultimo=this.Calculos_m[0].id;
                 }
                );
    }
}
</script>