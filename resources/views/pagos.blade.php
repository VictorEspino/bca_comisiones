<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=payment_file.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border=1>
<tr style="background-color:#777777;color:#FFFFFF">
<td><b>numero_empleado</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_ventas</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_gerente</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_regional</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_director</td>
<td style="background-color:purple;color:#FFFFFF"><b>adeudo_anterior</td>
<td style="background-color:purple;color:#FFFFFF"><b>charge_back</td>
<td><b>sueldo</td>
<td><b>modalidad</td>
<td><b>estatus</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>PAGO</td>
<td style="background-color:#FF0000;color:#FFFFFF"><b>ADEUDO PENDIENTE</td>
</tr>
<?php
$balances=App\Models\Payment::where('calculo_id',$id_calculo)
->orderBy("numero_empleado")
->get();
foreach ($balances as $balance) {
	?>
	<tr>
	<td>{{$balance->numero_empleado}}</td>
	<td style="color:#0000FF"><b>{{$balance->comision_ventas}}</td>
	<td style="color:#0000FF"><b>{{$balance->comision_gerente}}</td>
	<td style="color:#0000FF"><b>{{$balance->comision_regional}}</td>
	<td style="color:#0000FF"><b>{{$balance->comision_director}}</td>
	<td style="color:purple"><b>{{$balance->adeudo_anterior}}</td>
	<td style="color:purple"><b>{{$balance->charge_back}}</td>
	<td style=""><b>{{$balance->sueldo}}</td>
	<td style=""><b>{{$balance->modalidad=="1"?'Sueldo Integrado a Comisiones':'Sueldo Fijo'}}</td>
    <td style="{{$balance->estatus=='Inactivo'?'color:#FF0000':'#000000'}}"><b>{{$balance->estatus}}</td>
    <td style="background-color:#0000FF;color:#FFFFFF"><b>{{$balance->a_pagar}}</td>
    <td style="background-color:#FFFFFF;color:#FF0000"><b>{{$balance->adeudo}}</td>
	</tr>
<?php
}
?>
</table>