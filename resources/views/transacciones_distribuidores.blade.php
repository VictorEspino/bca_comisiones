<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=transacciones_distribuidores.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border=1>
<tr style="background-color:#777777;color:#FFFFFF">
<td><b>pedido</td>
<td><b>numero_distribuidor</td>
<td><b>distribuidor</td>
<td><b>fecha</td>
<td><b>tipo_venta</td>
<td><b>mdn</td>
<td><b>contrato</td>
<td><b>servicio</td>
<td><b>producto</td>
<td><b>importe (RENTA CON IMPUESTOS)</td>
<td><b>plazo</td>
<td><b>eq_sin_costo</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision</td>
<td style="background-color:#FF0000;color:#FFFFFF"><b>comentario</td>
</tr>
<?php
$transacciones=App\Models\TransaccionDistribuidor::where('calculo_id',$id_calculo)
->where('calculo_id',$id_calculo)
->get();
foreach ($transacciones as $transaccion) {
	?>
	<tr>
	<td>{{$transaccion->pedido}}</td>
	<td>{{$transaccion->numero_distribuidor}}</td>
	<td>{{$transaccion->distribuidor}}</td>
	<td>{{$transaccion->fecha}}</td>
	<td>{{$transaccion->tipo_venta}}</td>
    <td>{{$transaccion->mdn}}</td>
	<td>{{$transaccion->contrato}}</td>
	<td>{{$transaccion->servicio}}</td>
	<td>{{$transaccion->producto}}</td>
	<td>{{$transaccion->importe}}</td>
	<td>{{$transaccion->plazo}}</td>
	<td>{{$transaccion->eq_sin_costo}}</td>
	<td style="color:#0000FF">{{$transaccion->comision}}</td>
	<td style="color:#FF0000">{{$transaccion->razon_cr0}}</td>
	</tr>
<?php
}
?>
</table>