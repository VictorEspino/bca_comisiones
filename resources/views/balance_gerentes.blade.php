<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=balance_gerentes.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border=1>
<tr style="background-color:#777777;color:#FFFFFF">
<td><b>numero_empleado</td>
<td><b>udn</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>uds_activacion</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>uds_aep</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>uds_renovacion</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>uds_rep</td>
<td style="background-color:purple;color:#FFFFFF"><b>cierre_activacion</td>
<td style="background-color:purple;color:#FFFFFF"><b>cierre_aep</td>
<td style="background-color:purple;color:#FFFFFF"><b>cierre_renovacion</td>
<td style="background-color:purple;color:#FFFFFF"><b>cierre_rep</td>
<td><b>cuota_activacion</td>
<td><b>alcance_activacion</td>
<td><b>cuota_aep</td>
<td><b>alcance_aep</td>
<td><b>cuota_renovacion</td>
<td><b>alcance_renovacion</td>
<td><b>cuota_rep</td>
<td><b>alcance_rep</td>
<td><b>comision_directa_activacion</td>
<td><b>comision_directa_aep</td>
<td><b>comision_directa_renovacion</td>
<td><b>comision_directa_rep</td>
<td><b>comision_directa_seguro</td>
<td><b>comision_directa_addon</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_final_activacion</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_final_aep</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_final_renovacion</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_final_rep</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_final_seguro</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_final_addon</td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>comision_final</td>
</tr>
<?php
$balances=App\Models\BalanceComisionGerente::where('calculo_id',$id_calculo)
->where('calculo_id',$id_calculo)
->orderBy("numero_empleado")
->get();
foreach ($balances as $balance) {
	?>
	<tr>
	<td>{{$balance->numero_empleado}}</td>
	<td>{{$balance->udn}}</td>
	<td style="color:#0000FF"><b>{{$balance->uds_activacion}}</td>
	<td style="color:#0000FF"><b>{{$balance->uds_aep}}</td>
	<td style="color:#0000FF"><b>{{$balance->uds_renovacion}}</td>
	<td style="color:#0000FF"><b>{{$balance->uds_rep}}</td>
	<td style="color:purple"><b>{{$balance->porc_cierre_activacion}}</td>
	<td style="color:purple"><b>{{$balance->porc_cierre_aep}}</td>
	<td style="color:purple"><b>{{$balance->porc_cierre_renovacion}}</td>
	<td style="color:purple"><b>{{$balance->porc_cierre_rep}}</td>
	<td style="background-color:{{$balance->alcance_activacion>=0.8?'#00FF00':'#FF0000'}};color:yellow"><b>{{$balance->cuota_activacion}}</td>
	<td style="background-color:{{$balance->alcance_activacion>=0.8?'#00FF00':'#FF0000'}};color:yellow"><b>{{$balance->alcance_activacion}}</td>
	<td style="background-color:{{$balance->alcance_aep>=0.8?'#00FF00':'#FF0000'}};color:yellow"><b>{{$balance->cuota_aep}}</td>
	<td style="background-color:{{$balance->alcance_aep>=0.8?'#00FF00':'#FF0000'}};color:yellow"><b>{{$balance->alcance_aep}}</td>
	<td style="background-color:{{$balance->alcance_renovacion>=0.8?'#00FF00':'#FF0000'}};color:yellow"><b>{{$balance->cuota_renovacion}}</td>
	<td style="background-color:{{$balance->alcance_renovacion>=0.8?'#00FF00':'#FF0000'}};color:yellow"><b>{{$balance->alcance_renovacion}}</td>
	<td style="background-color:{{$balance->alcance_rep>=0.8?'#00FF00':'#FF0000'}};color:yellow"><b>{{$balance->cuota_rep}}</td>
	<td style="background-color:{{$balance->alcance_rep>=0.8?'#00FF00':'#FF0000'}};color:yellow"><b>{{$balance->alcance_rep}}</td>
	<td>{{$balance->comision_directa_activacion}}</td>
	<td>{{$balance->comision_directa_aep}}</td>
	<td>{{$balance->comision_directa_renovacion}}</td>
	<td>{{$balance->comision_directa_rep}}</td>
	<td>{{$balance->comision_directa_seguro}}</td>
	<td>{{$balance->comision_directa_addon}}</td>
	<td style="color:#0000FF"><b>{{$balance->comision_final_activacion}}</td>
	<td style="color:#0000FF"><b>{{$balance->comision_final_aep}}</td>
	<td style="color:#0000FF"><b>{{$balance->comision_final_renovacion}}</td>
	<td style="color:#0000FF"><b>{{$balance->comision_final_rep}}</td>
	<td style="color:#0000FF"><b>{{$balance->comision_final_seguro}}</td>
	<td style="color:#0000FF"><b>{{$balance->comision_final_addon}}</td>
    <td style="background-color:#0000FF;color:#FFFFFF"><b>{{$balance->comision_final}}</td>
	</tr>
<?php
}
?>
</table>