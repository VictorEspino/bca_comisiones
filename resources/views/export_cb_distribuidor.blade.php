<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=cargos_distribuidor_".$numero_distribuidor.".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<p  style="font-size:25px">Detalle de Saldo Charge-Back</p>
<br>
@foreach ($saldo as $registro)
<table border=1>
    <tr>
        <td style="background-color:#777777;color:#FFFFFF"><b>CB Pendiente</td><td>${{number_format($registro->saldo_anterior,2)}}</td>
    </tr>
    <tr>    
        <td style="background-color:#777777;color:#FFFFFF"><b>Cantidad aplicada en el periodo</td><td>${{number_format($registro->aplicado,2)}}</td>
    </tr>
    <tr>        
        <td style="background-color:#777777;color:#FFFFFF"><b>Nuevo Saldo pendiente</td><td>${{number_format($registro->nuevo_saldo,2)}}</td>
    </tr>
 </table>
 @endforeach
<br>
<p  style="font-size:25px">Lineas CB Periodo Actual</p>
<br>
<table border=1>
<tr style="background-color:#777777;color:#FFFFFF">
<td><b>Plan</td>
<td><b>Cuenta</td>
<td><b>Contrato</td>
<td><b>Telefono</td>
<td><b>fecha_activacion</td>
<td><b>fecha_baja</td>
<td><b>Renta</td>
<td><b>Comision</td>
<td><b>Equipo</td>
<td><b>Tipo baja</td>
<td><b>Propiedad</td>
<td style="background-color:#ff3300;color:#FFFFFF"><b>Charge-back</td>
</tr>
<?php

foreach ($detalles as $detalle) {
	?>
	<tr>
        <td>{{$detalle->plan}}</td>
        <td>{{$detalle->cuenta}}</td>
        <td>{{$detalle->contrato}}</td>
        <td>{{$detalle->dn}}</td>
        <td>{{$detalle->fecha_activacion}}</td>
        <td>{{$detalle->fecha_baja}}</td>
        <td>{{$detalle->renta}}</td>
        <td>{{$detalle->comision}}</td>
        <td>{{$detalle->equipo}}</td>
        <td>{{$detalle->tipo_baja}}</td>
        <td>{{$detalle->propiedad}}</td>
        <td style="color:#ff3300;background-color:#FFFFFF"><b>{{$detalle->cb}}</td>
	</tr>
<?php
}
?>
</table>