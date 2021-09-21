<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=payment_distribuidores.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border=1>
<tr style="background-color:#777777;color:#FFFFFF">
<td><b>numero_distribuidor</td>
<td><b>distribuidor</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>residual</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>retroactivo</td>
<td style="background-color:purple;color:#FFFFFF"><b>adelantos</td>
<td style="background-color:purple;color:#FFFFFF"><b>charge-back</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>PAGO</td>
</tr>
<?php
$balances=App\Models\PaymentDistribuidor::where('calculo_id',$id_calculo)
->get();
foreach ($balances as $balance) {
	?>
	<tr>
	<td>{{$balance->numero_distribuidor}}</td>
    <td>{{$balance->distribuidor}}</td>
	<td style="color:#0000FF"><b>{{$balance->comision}}</td>
	<td style="color:#0000FF"><b>{{$balance->residual}}</td>
	<td style="color:#0000FF"><b>{{$balance->retroactivo}}</td>
	<td style="color:purple"><b>{{$balance->adelantos}}</td>
	<td style="color:purple"><b>{{$balance->charge_back}}</td>
    <td style="background-color:#0000FF;color:#FFFFFF"><b>{{$balance->a_pagar}}</td>
	</tr>
<?php
}
?>
</table>