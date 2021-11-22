<style>
	.utilidad{
	position: fixed;
	background-color: #080;
	width: 430px;
	max-width: 100%;
	padding:10px 10px;
	height: 125px;
	top: 100%;
	margin-top:-125px;
	margin-left: -16px;
	color:#fff;
	z-index: 1;
}
.centryc{
	width: 100%;
}
.centryc td:nth-child(1),
.centryc th:nth-child(1){
	width: 60%;
	padding: 5px;
}
.centryc td:nth-child(2),
.centryc th:nth-child(2){
	text-align:right;
	width: 40%;
	padding: 5px;
}
</style>

<div class="utilidad">	
	<?php $utilidadTotal=Utilidad($db); ?>
	<?php $cobrar=Cuentas_por_Cobrar($db); ?>
	<?php $servicios=Servicios($db); ?>
	<?php $neto=$utilidadTotal-$cobrar-$servicios; ?>
	<table class="centryc">
	<tr>
	<td>
		Utilidad Bruta:							</td><td> 		<?php echo number_format($utilidadTotal,2,"."," ")." ".$moneda; ?>	
	</td></tr><tr><td>	
		Cuentas por cobrar pendientes:		 	</td><td>		<?php echo number_format($cobrar,2,"."," ")." ".$moneda; ?>	
	</td></tr><tr><td>	
		Pago de servicios:					 	</td><td>		<?php echo number_format($servicios,2,"."," ")." ".$moneda; ?>	
	</td></tr><tr><th>	
		<b>Utilidad Neta:</b> 					</th><th>		<?php echo number_format($neto,2,"."," ")." ".$moneda; ?>	
	</th>
	</tr>	
	</table>	
</div>

<?php
function Utilidad($db){
	$costoCompleto=0; 
	$importeCompleto=0; 

	// Obtiene las ventas
	$query="SELECT fecha_egreso ";
	$query.=" FROM inv_egresos ";
	$query.=" GROUP BY fecha_egreso ";
	$query.=" ORDER BY fecha_egreso ";
	$vFechas = $db->query($query)->fetch();
			$total = 0; 
			foreach ($vFechas as $nro => $vFecha) { 
			
				$costoSTotal=0; 
				$importeSTotal=0; 
				
				// Obtiene las ventas
				$query="SELECT  *, SUM(cantidad*tamanio)as cantidadAcumul, SUM(precio*cantidad)as importeAcumul ";
				$query.=" FROM inv_productos p ";
				
				$query.=" INNER JOIN inv_egresos_detalles vd ON vd.producto_id=p.id_producto ";
				$query.=" INNER JOIN inv_egresos v ON vd.egreso_id=v.id_egreso ";

				$query.=" LEFT JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id ";
				$query.=" LEFT JOIN inv_unidades u ON u.id_unidad=a.unidad_id ";

				$query.=" WHERE v.fecha_egreso = '".$vFecha['fecha_egreso']."'";
				$query.=" GROUP BY p.id_producto ";
				$ventas = $db->query($query)->fetch();

				foreach ($ventas as $nro1 => $venta) { 
					$cantidadTotal = escape($venta['cantidadAcumul']);
					$precio = escape($venta['precio']);
					$importeTotal = escape($venta['importeAcumul']); 		
					$total = $total + $importeTotal;
					
					$cantidadAnterior=0;
					$query="SELECT SUM(vd.cantidad*u.tamanio)as cantidadAnterior ";
					$query.=" FROM inv_egresos_detalles vd ";

					$query.=" INNER JOIN inv_egresos v ON egreso_id=id_egreso ";

					$query.=" LEFT JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id ";
					$query.=" LEFT JOIN inv_unidades u ON u.id_unidad=a.unidad_id ";

					$query.=" WHERE vd.producto_id='".$venta['id_producto']."' AND v.fecha_egreso < '".$vFecha['fecha_egreso']."' ";
					$vAntiguos = $db->query($query)->fetch();
					foreach ($vAntiguos as $nro2 => $vAntiguo) { 
						$cantidadAnterior = escape($vAntiguo['cantidadAnterior']);			
					}
					$costo=0;
					$costoTotal=0;
					$prodIngresados=0;
					$saldo=0;
					$prodAc=0;						//
					$ingresoSW=true;				//se termino de obtener los costos
					$unidad="";
					
					$ultimoSaldo=0;					
					$ultimoCosto=0;
					$ultimaUnidad="";
			
					//se obtiene las compras desde inicio de la empresa hasta la fecha limite solicitada por el usuario
					$query="SELECT  *, u.tamanio, u.unidad ";
					$query.=" FROM inv_ingresos_detalles vd ";
					$query.=" INNER JOIN inv_ingresos v ON ingreso_id=id_ingreso ";

					$query.=" LEFT JOIN inv_asignaciones a ON a.id_asignacion=vd.asignacion_id ";
					$query.=" LEFT JOIN inv_unidades u ON u.id_unidad=a.unidad_id ";

					$query.=" WHERE vd.producto_id='".$venta['id_producto']."' AND v.fecha_ingreso <= '".$vFecha['fecha_egreso']."' ";
					$query.=" ORDER BY fecha_ingreso, u.tamanio, u.unidad ";

					$iAntiguos = $db->query($query)->fetch();
					foreach ($iAntiguos as $nro3 => $iAntiguo) { 
						$prodIngresados=$prodIngresados+$iAntiguo['cantidad']*$iAntiguo['tamanio'];
						//se compara los productos previamente vendidos y costos antiguos
						//para obtener la utilidad de los ultimos productos comprados VS los productos vendidos.
						if($prodIngresados>$cantidadAnterior AND $ingresoSW){
							//verificar si es el primer Ingreso
							if($saldo>0){
								$saldo=$prodIngresados-$cantidadAnterior;						
							}
							else{
								$saldo=$iAntiguo['cantidad']*$iAntiguo['tamanio'];	
							}

							if($prodAc+$saldo<=$cantidadTotal){
								$saldo=$saldo;						
							}
							else{
								$saldo=$cantidadTotal-$prodAc;
								$ingresoSW=false;						
							}					
							
							$prodAc=$prodAc+$saldo;											
							$costoTotal+=$saldo*($iAntiguo['costo']/$iAntiguo['tamanio']);
							$costo=$iAntiguo['costo'];
							$unidad=$iAntiguo['unidad'];

							//verificar si hay un nuevo Costo
							if(($ultimoCosto!=$costo && $ultimoCosto!=0) || ($ultimaUnidad!="" && $ultimaUnidad!=$unidad) ){
								$ultimoSaldo=$saldo;
								$ultimoCosto=$costo;
								$ultimaUnidad=$unidad;						
								$nrocompras++;
							}
							else{
								$ultimoSaldo+=$saldo;
								$ultimoCosto=$costo;
								$ultimaUnidad=$unidad;						
							}
							
						}				
					}
					$costoSTotal+=$costoTotal;
					$importeSTotal+=$importeTotal;
				} 
				
				$nro + 1; 
				$costoCompleto+=$costoSTotal; 
				$importeCompleto+=$importeSTotal;
			} 

	$utilidadTotal=$importeCompleto-$costoCompleto;							

	return $utilidadTotal;
}
function Cuentas_por_Cobrar($db){
	$query="SELECT SUM(monto)as total FROM inv_pagos_detalles, inv_pagos WHERE estado='0' AND id_pago=pago_id AND tipo='Egreso' ";
	$total = $db->query($query)->fetch_first();
	
	return $total["total"];
}
function Servicios($db){
	$query="SELECT SUM(monto)as total FROM cronograma_cuentas ";
	$total = $db->query($query)->fetch_first();
	
	return $total["total"];
}
?>