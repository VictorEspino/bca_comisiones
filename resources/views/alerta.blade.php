<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=".($tipo=='1'?'fraude_aviso1':($tipo=='2'?'fraude_aviso2':'alerta_charge_back')).".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border=1>
		
<tr style="background-color:#777777;color:#FFFFFF">
<td style="background-color:#FF0000;color:#FFFFFF"><b>Contrato</b></td>	
<td style="background-color:#FF0000;color:#FFFFFF"><b>Tipo Venta</b></td>	
<td style="background-color:#FF0000;color:#FFFFFF"><b>Plan</b></td>	
<td style="background-color:#FF0000;color:#FFFFFF"><b>Fecha</b></td>	
<td style="background-color:#FF0000;color:#FFFFFF"><b>Renta con Impuestos</b></td>	
<td style="background-color:#FF0000;color:#FFFFFF"><b>Numero_Empleado</b></td>	
<td style="background-color:#FF0000;color:#FFFFFF"><b>Empleado</b></td>	
<td style="background-color:#FF0000;color:#FFFFFF"><b>UDN</b></td>	
<td style="background-color:#FF0000;color:#FFFFFF"><b>PDV</b></td>
<td style="background-color:#FF0000;color:#FFFFFF"><b>Conciliacion</b></td>		
</tr>
<?php
$alertas=App\Models\Alerta::where('conciliacion_id',$conciliacion_id)
->where('periodo',$periodo)
->where('tipo',$tipo)
->get();
foreach ($alertas as $alerta) {
	?>
	<tr>
    <td>{{$alerta->contrato}}</td>
    <td>{{$alerta->tipo_venta}}</td>
    <td>{{$alerta->plan}}</td>
    <td>{{$alerta->fecha}}</td>
    <td>${{number_format($alerta->importe,2)}}</td>
    <td>{{$alerta->numero_empleado}}</td>
    <td>{{$alerta->empleado}}</td>
    <td>{{$alerta->udn}}</td>
    <td>{{$alerta->pdv}}</td>
    <td>{{$alerta->periodo}}</td>

	</tr>
<?php
}
?>
</table>