require('./bootstrap');

window.Vue = require('vue');


Vue.component('calculo_completo',require('./components/CalculoComisiones.vue').default);
Vue.component('file-upload-component', require('./components/FileUpload.vue').default);

Vue.component('calculos_guardados',require('./components/CalculosGuardados.vue').default);
Vue.component('registro_calculo',require('./components/RegistroCalculo.vue').default);

Vue.component('conciliacion_completa',require('./components/ConciliacionATT.vue').default);
Vue.component('conciliaciones_guardadas',require('./components/ConciliacionesGuardadas.vue').default);
Vue.component('registro_conciliacion',require('./components/RegistroConciliacion.vue').default);

Vue.component('calculos_guardados_dist',require('./components/CalculosGuardadosDist.vue').default);
Vue.component('registro_calculo_dist',require('./components/RegistroCalculoDist.vue').default);

const app = new Vue({
 el:"#app"
});
