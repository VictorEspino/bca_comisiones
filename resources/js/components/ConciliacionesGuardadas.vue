<template>
    <div class="flex flex-1  flex-col md:flex-row lg:flex-row mx-2">
        <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
            <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b">
                <p class="text-grey-dark text-xl font-bold">
                    Conciliaciones Historicas
                </p>
            </div>
            <div class="p-3">
                <div class="w-full flex flex-col space-y-3">
                    <registro_conciliacion v-for="conciliacion in Conciliaciones"
                        :key="conciliacion.id"
                        :id="conciliacion.id"
                        :periodo="conciliacion.periodo"
                        :comisiones_att="conciliacion.comisiones_att"
                        :residual_att="conciliacion.residual_att"
                        :charge_back_att="conciliacion.charge_back_att"
                        :terminado="conciliacion.terminado"
                        :updated_at="conciliacion.updated_at"
                        :ultimo="ultimo"
                    >
                    </registro_conciliacion>
                </div>
            </div>
        </div> 
    </div>
</template>
<script>
export default {
    data(){
			return {
				  Conciliaciones : [],
                  ultimo:''
				}
		},
    mounted(){
        axios.get('/conciliaciones_guardadas').then((response)=>
                 {
                    this.Conciliaciones=response.data;
                    this.ultimo=this.Conciliaciones[0].id;
                 }
                );
    }
}
</script>