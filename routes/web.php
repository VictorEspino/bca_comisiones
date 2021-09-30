<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SetupCalculoController;
use App\Http\Controllers\CalculoComisionesController;
use App\Http\Controllers\DisplayListados;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\BalanceComisionesController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ConciliacionController;
use App\Http\Controllers\CalculoComisionesDistController;
use App\Http\Controllers\BalanceComisionesDistController;
use App\Http\Controllers\PaymentDistController;
use App\Http\Controllers\ExcelController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/nuevo_calculo',function(){
    return view('calculo_comisiones');
})->middleware('auth')->name('nuevo_calculo');

// ESTAS SON RUTAS DE LA PANTALLA INTERACTIVA DE CALCULOS

Route::put('/setup_calculo', [SetupCalculoController::class,'setup_calculo'])->middleware('auth');
Route::get('/limpiar_calculo/{id}', [SetupCalculoController::class,'limpiar_calculo'])->middleware('auth');
//Route::put('/medicion_ventas',[CalculoComisionesController::class,'medicion_actor_ventas_sucursales'])->middleware('auth');
//Route::put('/calculo_actor_ventas',[CalculoComisionesController::class,'comisiones_actor_ventas_surcursales'])->middleware('auth');
//Route::put('/calculo_gerente',[CalculoComisionesController::class,'comisiones_gerente_surcursales'])->middleware('auth');

Route::put('/ejecutar_calculo',[CalculoComisionesController::class,'calculo_comisiones'])->middleware('auth');
Route::get('upload_file', function () {
    return view('upload');
});
Route::post('/store_cuotas', [FileUploadController::class,'fileStoreCuotas']);
Route::post('/store_cc', [FileUploadController::class,'fileStoreCC']);
Route::post('/store_eq0', [FileUploadController::class,'fileStoreEQ0']);
Route::post('/store_cr0', [FileUploadController::class,'fileStoreCR0']);
Route::post('/store_cb', [FileUploadController::class,'fileStoreCB_Interno']);
Route::get('/store_cb', [FileUploadController::class,'fileStoreCB_Interno']);

//LISTADO DE CALCULOS

Route::get('/calculos', [DisplayListados::class,'listado_calculos'])->middleware('auth')->name('calculos');
Route::get('/calculos_guardados', [DisplayListados::class,'calculos_guardados'])->middleware('auth')->name('calculos_guardados');
Route::put('/terminar_calculo', [CalculoComisionesController::class,'calculo_terminar'])->middleware('auth')->name('terminar_calculo');
Route::put('/consultar_calculo', [CalculoComisionesController::class,'calculo_consulta'])->middleware('auth')->name('consultar_calculo');

Route::get('/detalle_calculo/{id}', [DisplayListados::class,'detalle_calculo'])->middleware('auth')->name('detalle_calculo');
Route::get('/transacciones_empleado/{id}/{id_empleado}', [DisplayListados::class,'transacciones_empleado'])->middleware('auth')->name('transacciones_empleado');

Route::get('/medicion_sucursal/{id}', [CalculoComisionesController::class,'medicion_sucursales']);

Route::put('/genera_balance_ejecutivos', [BalanceComisionesController::class,'balance_ejecutivos'])->middleware('auth');
Route::put('/genera_balance_gerentes', [BalanceComisionesController::class,'balance_gerentes'])->middleware('auth');
Route::put('/genera_balance_regionales', [BalanceComisionesController::class,'balance_regionales'])->middleware('auth');
Route::put('/genera_balance_director', [BalanceComisionesController::class,'balance_director'])->middleware('auth');
Route::put('/genera_pagos',[PaymentController::class,'generar_pagos'])->name("generar_pagos")->middleware('auth');


Route::get('/balance_ejecutivos/{id}',[DisplayListados::class,'balance_ejecutivos'])->name("balance_ejecutivos")->middleware('auth');
Route::get('/balance_gerentes/{id}',[DisplayListados::class,'balance_gerentes'])->name("balance_gerentes")->middleware('auth');
Route::get('/balance_regionales/{id}',[DisplayListados::class,'balance_regionales'])->name("balance_regionales")->middleware('auth');
Route::get('/balance_director/{id}',[DisplayListados::class,'balance_director'])->name("balance_director")->middleware('auth');
Route::get('/transacciones/{id}',[DisplayListados::class,'transacciones'])->name("transacciones")->middleware('auth');
Route::get('/pagos/{id}',[DisplayListados::class,'pagos'])->name("pagos")->middleware('auth');
Route::get('/transacciones_sucursal/{id}/{udn}',[DisplayListados::class,'transacciones_sucursal'])->name("transacciones_sucursal")->middleware('auth');

Route::get('/estado_cuenta/{id_calculo}/{id_empleado}/{f_now}',[DisplayListados::class,'estado_cuenta'])->name("estado_cuenta");
Route::get('/estado_cuenta_interno/{id_calculo}/{id_empleado}/{f_now}',[DisplayListados::class,'estado_cuenta_interno'])->middleware('auth')->name("estado_cuenta_interno");




Route::get('/ejemplos', [SetupCalculoController::class,'ejemplo_transacciones']);

/////////////////////////////////////////////////////////
//RUTAS DE CONCILIACION
/////////////////////////////////////////////////////////


Route::get('/conciliacion_att',function(){
    return view('conciliacion_att');
})->middleware('auth')->name('conciliacion_att');

Route::put('/setup_conciliacion', [ConciliacionController::class,'setup_conciliacion'])->middleware('auth');
Route::post('/store_comisiones', [FileUploadController::class,'fileStoreComisionesATT']);
Route::post('/store_residual', [FileUploadController::class,'fileStoreResidualATT']);
Route::post('/store_cb_att', [FileUploadController::class,'fileStoreCBATT']);

Route::put('/conciliacion_erp_att', [ConciliacionController::class,'conciliacion_erp_att']);
Route::put('/residual_45dias', [ConciliacionController::class,'residual_45dias']);
Route::put('/fraude_aviso1', [ConciliacionController::class,'fraude_aviso1']);
Route::put('/fraude_aviso2', [ConciliacionController::class,'fraude_aviso2']);
Route::put('/alerta_cb', [ConciliacionController::class,'alerta_cb']);
Route::put('/terminar_conciliacion', [ConciliacionController::class,'conciliacion_terminar'])->middleware('auth')->name('terminar_conciliacion');

Route::get('/detalle_alertas/{periodo}/{conciliacion_id}',[ConciliacionController::class,'detalle_alertas'])->name("detalle_alertas")->middleware('auth');
Route::get('/conciliaciones_guardadas', [DisplayListados::class,'conciliaciones_guardadas'])->middleware('auth')->name('conciliaciones_guardadas');
Route::get('/conciliaciones', [DisplayListados::class,'listado_conciliaciones'])->middleware('auth')->name('conciliaciones');
Route::put('/consultar_conciliacion', [ConciliacionController::class,'conciliacion_consulta'])->middleware('auth')->name('consultar_conciliacion');

Route::get('/aclaracion/{periodo}/{conciliacion_id}/{concepto}',[DisplayListados::class,'aclaracion'])->name("aclaracion")->middleware('auth');
Route::get('/alerta/{periodo}/{conciliacion_id}/{tipo}',[DisplayListados::class,'alerta'])->name("alerta")->middleware('auth');

Route::get('/seguimiento_contrato', function () {
    return view('seguimiento_contrato');
})->name("seguimiento_contrato");

Route::post('/seguimiento_contrato', function () {
    return view('seguimiento_contrato');
})->name("seguimiento_contrato");

Route::get('/genera_pagos/{id}',[PaymentController::class,'generar_pagos'])->name("generar_pagos")->middleware('auth');
Route::get('/ajuste_25/{id}',[PaymentController::class,'ajuste_25'])->name("ajuste_25")->middleware('auth');

Route::get('/nuevo_calculo_dist', function(){return view('nuevo_calculo_dist');})->middleware('auth')->name("nuevo_calculo_dist");
Route::post('/nuevo_calculo_dist', [CalculoComisionesDistController::class,'nuevo_calculo'])->middleware('auth')->name("nuevo_calculo_dist");

Route::get('/calculos_dist', [DisplayListados::class,'listado_calculos_dist'])->middleware('auth')->name('calculos_dist');

Route::get('/calculos_guardados_dist_s', [DisplayListados::class,'calculos_guardados_dist_s'])->middleware('auth')->name('calculos_guardados_dist_s');
Route::get('/calculos_guardados_dist_m', [DisplayListados::class,'calculos_guardados_dist_m'])->middleware('auth')->name('calculos_guardados_dist_m');
Route::put('/terminar_calculo_dist', [CalculoComisionesDistController::class,'calculo_terminar_dist'])->middleware('auth')->name('terminar_calculo_dist');
Route::put('/consultar_calculo_dist', [CalculoComisionesDistController::class,'calculo_consulta_dist'])->middleware('auth')->name('consultar_calculo_dist');
Route::put('/ejecutar_calculo_dist',[CalculoComisionesDistController::class,'calculo_comisiones_dist'])->middleware('auth');

Route::get('/detalle_calculo_dist/{id}', [DisplayListados::class,'detalle_calculo_dist'])->middleware('auth')->name('detalle_calculo_dist');
Route::put('/genera_balance_distribuidores',[BalanceComisionesDistController::class,'balance_distribuidores'])->middleware('auth');
Route::put('/genera_pagos_distribuidores',[PaymentDistController::class,'generar_pagos_distribuidores'])->name("generar_pagos_distribuidores")->middleware('auth');
Route::get('/transacciones_distribuidores/{id}',[DisplayListados::class,'transacciones_distribuidores'])->name("transacciones_distribuidores")->middleware('auth');
Route::get('/balance_distribuidores/{id}',[DisplayListados::class,'balance_distribuidores'])->name("balance_distribuidores")->middleware('auth');
Route::get('/pagos_distribuidores/{id}',[DisplayListados::class,'pagos_distribuidores'])->name("pagos_distribuidores")->middleware('auth');

Route::get('/calculos_semanales/{tipo}',[DisplayListados::class,'calculos_distribuidores'])->name("calculos_semanales")->middleware('auth');
Route::get('/calculos_mensuales/{tipo}',[DisplayListados::class,'calculos_distribuidores'])->name("calculos_mensuales")->middleware('auth');

Route::get('/calculos_semanales_admin/{tipo}',[DisplayListados::class,'calculos_distribuidores_admin'])->name("calculos_semanales_admin")->middleware('auth');
Route::get('/calculos_mensuales_admin/{tipo}',[DisplayListados::class,'calculos_distribuidores_admin'])->name("calculos_mensuales_admin")->middleware('auth');
Route::get('/lista_pagos_calculo/{id}',[DisplayListados::class,'lista_pagos_calculo'])->middleware('auth');

Route::get('/estado_cuenta_distribuidor/{id}',[DisplayListados::class,'estado_cuenta_distribuidor'])->name("estado_cuenta_distribuidor")->middleware('auth');
Route::get('/estado_cuenta_distribuidor/{id}/{numero_distribuidor}',[DisplayListados::class,'estado_cuenta_distribuidor'])->name("estado_cuenta_distribuidor")->middleware('auth');
Route::get('/export_transacciones_distribuidor/{id}',[DisplayListados::class,'export_transacciones_distribuidor'])->middleware('auth');
Route::post('/cargar_factura_distribuidor',[FileUploadController::class,'cargar_factura_distribuidor'])->name('cargar_factura_distribuidor')->middleware('auth');

Route::get('/conciliacion_erp_att/{conciliacion_id}/{periodo}', [ConciliacionController::class,'conciliacion_erp_att']);
Route::get('/residual_45dias/{conciliacion_id}/{periodo}', [ConciliacionController::class,'residual_45dias']);
Route::get('/fraude_aviso1/{conciliacion_id}/{periodo}', [ConciliacionController::class,'fraude_aviso1']);
Route::get('/fraude_aviso2/{conciliacion_id}/{periodo}', [ConciliacionController::class,'fraude_aviso2']);
Route::get('/alerta_cb/{conciliacion_id}/{periodo}', [ConciliacionController::class,'alerta_cb']);
Route::get('/terminar_conciliacion/{conciliacion_id}/{periodo}', [ConciliacionController::class,'conciliacion_terminar']);

Route::post('/carga_transacciones',[ExcelController::class,'transaccions_import'])->name('carga_transacciones')->middleware('auth');
Route::post('/carga_empleados',[ExcelController::class,'empleados_import'])->name('carga_empleados')->middleware('auth');
Route::post('/carga_cuotas',[ExcelController::class,'cuotas_import'])->name('carga_cuotas')->middleware('auth');
Route::post('/carga_ajustes',[ExcelController::class,'ajustes_import'])->name('carga_ajustes')->middleware('auth');
Route::get('/calculo_seguimiento/{id}',[CalculoComisionesController::class,'calculo_seguimiento'])->middleware('auth');