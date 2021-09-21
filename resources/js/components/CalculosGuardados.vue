<template>
    <div class="flex flex-1  flex-col md:flex-row lg:flex-row mx-2">
        <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
            <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                <p class="text-grey-dark text-xl font-bold">
                    Calculos Historicos
                </p>
            </div>
            <div class="p-3">
                <div class="w-full flex flex-col space-y-3">
                    <registro_calculo v-for="calculo in Calculos"
                        :key="calculo.id"
                        :id="calculo.id"
                        :descripcion="calculo.descripcion"
                        :fecha_inicio="calculo.fecha_inicio"
                        :fecha_fin="calculo.fecha_fin"
                        :cuotas="calculo.cuotas"
                        :cc="calculo.cc"
                        :eq0="calculo.eq0"
                        :cr0="calculo.cr0"
                        :cb="calculo.cb"
                        :terminado="calculo.terminado"
                        :actualizado="calculo.updated_at"
                        :ultimo="ultimo"
                    >
                    </registro_calculo>
                </div>
            </div>
        </div> 
    </div>
</template>
<script>
export default {
    data(){
			return {
				  Calculos : [],
                  ultimo:''
				}
		},
    mounted(){
        axios.get('/calculos_guardados').then((response)=>
                 {
                    this.Calculos=response.data;
                    this.ultimo=this.Calculos[0].id;
                 }
                );
    }
}
</script>