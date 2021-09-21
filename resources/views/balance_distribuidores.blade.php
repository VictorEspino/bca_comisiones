<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=balance_distribuidores.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table>
<tr style="background-color:#AAAAAA;">
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>numero_distribuidor</p></td>
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>distribuidor</p></td>
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
<td class="border px-4 py-2"><p class="text-grey-dark text-xs"><b>comision_final</p></td>
</tr>
<?php
$balances=App\Models\BalanceComisionDistribuidor::where('calculo_id',$id_calculo)
->where('calculo_id',$id_calculo)
->get();
foreach ($balances as $balance) {
	?>
	<tr>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->numero_distribuidor}}</p></td>
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->distribuidor}}</p></td>
	
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
	<td class="border px-4 py-2"><p class="text-grey-dark text-xs">{{$balance->comision_final}}</p></td>
	</tr>
	
	
	
	<?php
}
?>
</table>