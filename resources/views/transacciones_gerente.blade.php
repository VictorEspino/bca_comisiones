<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=transacciones_sucursal.xls");
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

<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_gerente</td>
</tr>
<?php
$transacciones=App\Models\Transaccion::where('calculo_id',$id_calculo)
->where('calculo_id',$id_calculo)
->where('udn',$udn)
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
    <td style="color:#0000FF">{{$transaccion->comision_supervisor_l1}}</td>
	</tr>
<?php
}
?>
</table>
<p>Nota: Las transacciones muestran la comision directa aplicada en el esquema de comisiones, es necesario aplicar el logro de objetivos mostrados en el balance del estado de cuenta para obtener la comision final por concepto (Activacion, Renovacion, etc.)</p>