<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=formato_".($concepto=='Comision no Pagada'?'comision':'45d').".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border=1>
		
<tr style="background-color:#777777;color:#FFFFFF">
<td style="background-color:#0000FF;color:#FFFFFF"><b>Nombre del Distribuidor</b></td>	
<td style="background-color:#0000FF;color:#FFFFFF"><b>Clave Distribuidor</b></td>	
<td style="background-color:#0000FF;color:#FFFFFF"><b>Teléfono (10 dígitos)</b></td>	
<td style="background-color:#0000FF;color:#FFFFFF"><b>Nombre Plan</b></td>	
<td style="background-color:#0000FF;color:#FFFFFF"><b>Renta con Impuestos</b></td>	
<td style="background-color:#0000FF;color:#FFFFFF"><b>Propiedad (Propio y/o Nuevo)</b></td>	
<td style="background-color:#0000FF;color:#FFFFFF"><b>ICCID</b></td>	
<td style="background-color:#0000FF;color:#FFFFFF"><b>Fecha Activación y/o Renovación dd/mm/aaaa</b></td>	
<td style="background-color:#0000FF;color:#FFFFFF"><b>Plazo Forzoso</b></td>
<td style="background-color:#0000FF;color:#FFFFFF"><b>Cuenta</b></td>		
<td style="background-color:#0000FF;color:#FFFFFF"><b>Contrato (CO_ID)</b></td>	
<td style="background-color:#0000FF;color:#FFFFFF"><b>Marca (azul roja naranja)</b></td>	
<td style="background-color:#0000FF;color:#FFFFFF"><b>Periodo del que solicita aclaración. Indicar Año-Mes.</b></td>	
<td style="background-color:#0000FF;color:#FFFFFF"><b>Observaciones Distribuidor</b></td>	
<td style="background-color:#0000FF;color:#FFFFFF"><b>Importe de comision</b></td>	
<td style="background-color:#0000FF;color:#FFFFFF"><b>Número de mes que reclama  Ejemplo: M5</b></td>	
</tr>
<?php
$reclamos=App\Models\Reclamo::where('conciliacion_id',$conciliacion_id)
->where('periodo',$periodo)
->where('observacion',$concepto)
->get();
foreach ($reclamos as $reclamo) {
	?>
	<tr>
	<td>Business Corporate Asociation SA de CV</td>
    <td>100514</td>
    <td>{{$reclamo->telefono}}</td>
    <td>{{$reclamo->plan}}</td>
    <td>{{$reclamo->renta}}</td>
    <td>{{$reclamo->propiedad}}</td>
    <td>{{$reclamo->iccid}}</td>
    <td>{{$reclamo->fecha}}</td>
    <td>{{$reclamo->plazo}}</td>
    <td>{{$reclamo->cuenta}}</td>
    <td>{{$reclamo->contrato}}</td>
    <td>{{$reclamo->marca}}</td>
    <td>{{$reclamo->periodo}}</td>
    <td>{{$reclamo->observacion}}</td>
    <td>${{number_format($reclamo->comision,2)}}</td>
    <td>M1</td>

	</tr>
<?php
}
?>
</table>