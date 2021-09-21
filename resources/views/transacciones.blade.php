<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=transacciones.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border=1>
<tr style="background-color:#777777;color:#FFFFFF">
<td><b>pedido</td>
<td><b>numero_empleado</td>
<td><b>empleado</td>
<td><b>fecha</td>
<td><b>udn</td>
<td><b>pdv</td>
<td><b>tipo_venta</td>
<td><b>contrato</td>
<td><b>servicio</td>
<td><b>producto</td>
<td><b>importe</td>
<td><b>plazo</td>
<td><b>eq_sin_costo</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_venta</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_gerente</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_regional</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_director</td>
<td><b>ejecutivo_cc</td>
<td><b>supervisor_cc</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_cc</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_supervisor_cc</td>
</tr>
<?php
$transacciones=App\Models\Transaccion::where('calculo_id',$id_calculo)
->where('calculo_id',$id_calculo)
->orderBy("numero_empleado")
->get();
foreach ($transacciones as $transaccion) {
	?>
	<tr>
	<td>{{$transaccion->pedido}}</td>
	<td>{{$transaccion->numero_empleado}}</td>
	<td>{{$transaccion->empleado}}</td>
	<td>{{$transaccion->fecha}}</td>
	<td>{{$transaccion->udn}}</td>
	<td>{{$transaccion->pdv}}</td>
	<td>{{$transaccion->tipo_venta}}</td>
	<td>{{$transaccion->contrato}}</td>
	<td>{{$transaccion->servicio}}</td>
	<td>{{$transaccion->producto}}</td>
	<td>{{$transaccion->importe}}</td>
	<td>{{$transaccion->plazo}}</td>
	<td>{{$transaccion->eq_sin_costo}}</td>
	<td style="color:#0000FF">{{$transaccion->comision_venta}}</td>
	<td style="color:#0000FF">{{$transaccion->comision_supervisor_l1}}</td>
	<td style="color:#0000FF">{{$transaccion->comision_supervisor_l2}}</td>
	<td style="color:#0000FF">{{$transaccion->comision_supervisor_l3}}</td>
	<td>{{$transaccion->ejecutivoCC}}</td>
	<td>{{$transaccion->supervisorCC}}</td>
	<td style="color:#0000FF">{{$transaccion->comisionCC}}</td>
	<td style="color:#0000FF">{{$transaccion->comision_supervisor_cc}}</td>
	</tr>
<?php
}
?>
</table>