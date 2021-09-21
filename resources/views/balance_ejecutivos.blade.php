<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=balance_ejecutivos.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table>
<tr style="background-color:#AAAAAA;">
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>numero_empleado</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>UDN</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>puesto</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>uds_activacion</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>renta_activacion</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>comision_activacion</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>uds_aep</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>renta_aep</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>comision_aep</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>uds_renovacion</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>renta_renovacion</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>comision_renovacion</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>uds_rep</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>renta_rep</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>comision_rep</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>uds_seguro</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>renta_seguro</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>comision_seguro</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>uds_addon</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>renta_addon</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>comision_addon</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>esquema</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>cumple_objetivo</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>porcentaje_cobro</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>comision_final_activacion</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>comision_final_aep</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>comision_final_renovacion</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>comision_final_rep</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>comision_final_seguro</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>comision_final_addon</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>comentario</p></td>
</tr>
<?php
$balances=App\Models\BalanceComisionVenta::where('calculo_id',$id_calculo)
->where('calculo_id',$id_calculo)
->orderBy("numero_empleado")
->get();
foreach ($balances as $balance) {
	?>
	<tr>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->numero_empleado}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->udn}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->puesto}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->uds_activacion}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->renta_activacion}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->comision_activacion}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->uds_aep}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->renta_aep}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->comision_aep}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->uds_renovacion}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->renta_renovacion}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->comision_renovacion}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->uds_rep}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->renta_rep}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->comision_rep}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->uds_seguro}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->renta_seguro}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->comision_seguro}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->uds_addon}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->renta_addon}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->comision_addon}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->esquema}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->cumple_objetivo}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->porcentaje_cobro}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->comision_final_activacion}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->comision_final_aep}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->comision_final_renovacion}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->comision_final_rep}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->comision_final_seguro}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->comision_final_addon}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->comentario}}</p></td>
	</tr>
	
	
	
	<?php
}
?>
</table>