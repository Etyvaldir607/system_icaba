 <?php 
// Obtiene la fecha

 $cerrar = 'cerrar';
 $abrir = '';
 $num = $sw = $primer_dia = 0;
 $ultimo_registro = $db->query("SELECT * FROM `inv_caja` WHERE fecha = (SELECT MAX(fecha) AS fecha FROM inv_caja) AND id_caja = (SELECT MAX(id_caja) AS fecha FROM inv_caja)")->fetch_first(); 

 if ($ultimo_registro) {

 	$fecha_ultimo_registro = strtotime(date_decode($ultimo_registro['fecha'],$_institution['formato']));
 	$fecha_actual = strtotime(date('d-m-Y'));
 	$num= 0;	

 	while ($fecha_ultimo_registro <=  $fecha_actual && $sw == 0) {
 		$num++;
 		if ($fecha_ultimo_registro ==  $fecha_actual) { 
 			$fecha = date('Y-m-d');
 		}else{
 			if ($primer_dia == 0) {
 				$fecha_ultimo_registro = date("d-m-Y",$fecha_ultimo_registro);
 				$fecha = date_encode($fecha_ultimo_registro);
 				$primer_dia = 1;
 			}else{
 				$fecha_ultimo_registro = date("d-m-Y",$fecha_ultimo_registro);
 				$fecha_nueva = new DateTime($fecha_ultimo_registro);
 				$fecha_nueva->add(new DateInterval('P1D'));
 				$fecha = $fecha_nueva->format('Y-m-d');
 			}
 		}

 		$ventas = $db->query("SELECT * FROM inv_egresos e WHERE e.fecha_egreso = '$fecha' AND e.tipo='Venta' group by id_egreso")->fetch();

 		$compras = $db->query("SELECT * FROM inv_ingresos i WHERE i.fecha_ingreso = '$fecha' AND i.tipo='Compra' group by id_ingreso")->fetch();

 		$cronogramas = $db->query("select c.periodo, cc.detalle, cc.monto, cc.fecha_pago, cc.id_cronograma_cuentas, cc.tipo_pago from cronograma c left join cronograma_cuentas cc on c.id_cronograma = cc.cronograma_id where cc.estado='1' and cc.fecha_pago='$fecha' GROUP by c.id_cronograma")->fetch();

 		$cobros = $db->query("select p.id_pago, p.movimiento_id, pd.tipo_pago, pd.fecha_pago, e.nro_factura, e.fecha_egreso,e.nombre_cliente,e.nit_ci, e.monto_total, e.tipo, ifnull(monto,0) as subtotal from inv_pagos p left join inv_pagos_detalles pd on p.id_pago= pd.pago_id LEFT JOIN inv_egresos e ON e.id_egreso=p.movimiento_id where p.tipo='Egreso' and pd.estado='1' and pd.fecha_pago='$fecha' and e.fecha_egreso!=pd.fecha_pago")->fetch(); 

 		$pagos_compras = $db->query("select p.id_pago, p.movimiento_id, i.fecha_ingreso, i.fecha_ingreso, i.tipo, i.id_ingreso, i.nombre_proveedor, i.monto_total, pd.tipo_pago, ifnull(monto,0) as subtotal from inv_pagos p left join inv_pagos_detalles pd on p.id_pago= pd.pago_id LEFT JOIN inv_ingresos i ON i.id_ingreso=p.movimiento_id where p.tipo='Ingreso' and pd.estado='1' and pd.fecha_pago='$fecha' and i.fecha_ingreso!=pd.fecha_pago")->fetch();

 		$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
 		$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';


 		$ingresos = $db->select("m.*, concat(e.nombres, ' ', e.paterno, ' ', e.materno) as empleado")->from('caj_movimientos m')->join('sys_empleados e', 'm.empleado_id = e.id_empleado', 'left')->where('m.tipo', 'i')->where('m.fecha_movimiento', $fecha)->order_by('m.fecha_movimiento desc, m.hora_movimiento desc')->fetch();

 		$egresos = $db->select("m.*, concat(e.nombres, ' ', e.paterno, ' ', e.materno) as empleado")->from('caj_movimientos m')->join('sys_empleados e', 'm.empleado_id = e.id_empleado', 'left')->where('m.tipo', 'e')->where('m.fecha_movimiento', $fecha)->order_by('m.fecha_movimiento desc, m.hora_movimiento desc')->fetch();


 		$gastos = $db->select("m.*, concat(e.nombres, ' ', e.paterno, ' ', e.materno) as empleado")->from('caj_movimientos m')->join('sys_empleados e', 'm.empleado_id = e.id_empleado', 'left')->where('m.tipo', 'g')->where('m.fecha_movimiento', $fecha)->order_by('m.fecha_movimiento desc, m.hora_movimiento desc')->fetch();

 		$total_ingresos = $total_ingresos_banco = $total_venta = $total_venta_banco = $total_egresos = $total_egresos_banco = $total_compra = $total_compra_banco = $total_gasto = $total_gasto_banco = $total_de_totales_ingreso = $total_de_totales =  $total_de_totales_egreso = 0; 

 		if ($ingresos || $cobros) { 
 			if ($cobros) {
 				foreach ($cobros as $key => $cobro) { 
 					if ($cobro['tipo_pago'] == 'Efectivo') 
 						$total_ingresos = $total_ingresos + $cobro['subtotal']; 
 					else
 						$total_ingresos_banco = $total_ingresos_banco + $cobro['subtotal'];									
 				} 
 			} 
 			foreach ($ingresos as $nro => $ingreso) { 								
 				$total_ingresos = $total_ingresos + $ingreso['monto']; 
 			} 
 			$total_total_ingresos = (($total_ingresos_banco + $total_ingresos )) ? number_format(($total_ingresos_banco + $total_ingresos), 2, '.', ''): number_format(0, 2, '.', ''); 
 		} else { 
 			$total_ingreso = 0; 
 		} 

 		if ($ventas) { 	
 			foreach ($ventas as $key => $venta) { 
 				if ($venta['plan_de_pagos'] == 'si'){ 
 					$pagos = $db->query("select id_pago, movimiento_id, tipo_pago, ifnull(SUM(monto),0) as subtotal from inv_pagos p left join inv_pagos_detalles pd on p.id_pago= pd.pago_id where p.movimiento_id = '". $venta['id_egreso'] . "' AND pd.estado='1' AND p.tipo='Egreso' GROUP by movimiento_id")->fetch_first(); 
 					if ($pagos){ 					
 						if ($pagos['tipo_pago'] == 'Efectivo') 
 							$total_venta = $total_venta + $pagos['subtotal']; 
 						else 
 							$total_venta_banco = $total_venta_banco + $pagos['subtotal']; 
 					}else{ 												
						//SIN PAGO 																
 					}
 				}else{
 					if ($venta['tipo_pago'] == 'Efectivo') 
 						$total_venta = $total_venta + $venta['monto_total']; 															
 					else
 						$total_venta_banco = $total_venta_banco + $venta['monto_total']; 									
 				} 	
 			} 
 			$total_total_venta = (($total_venta_banco + $total_venta )) ? number_format(($total_venta_banco + $total_venta), 2, '.', ''): number_format(0, 2, '.', ''); 
 		} else { 
 			$total_venta = 0; 
 		} 			

 		if ($egresos || $pagos_compras || $cronogramas) {	 
 			if ($cronogramas) {	
 				foreach ($cronogramas as $key => $cronograma) { 
 					if ($cronograma['tipo_pago'] == 'Efectivo') 
 						$total_egresos = $total_egresos + $cronograma['monto'];
 					else
 						$total_egresos_banco = $total_egresos_banco + $cronograma['monto'];										
 				} 
 			} 

 			if ($pagos_compras) {	
 				foreach ($pagos_compras as $nro => $pagos_compra) { 
 					if ($pagos_compra['tipo_pago'] == 'Efectivo') 
 						$total_egresos = $total_egresos + $pagos_compra['subtotal'];
 					else
 						$total_egresos_banco = $total_egresos_banco + $pagos_compra['subtotal']; 									
 				} 
 			} 

 			foreach ($egresos as $nro => $egreso) { 
 				$total_egresos = $total_egresos + $egreso['monto']; 								
 			} 
 			$total_total_egresos = (($total_egresos_banco + $total_egresos )) ? number_format(($total_egresos_banco + $total_egresos), 2, '.', ''): number_format(0, 2, '.', ''); 
 		}else {
 			$total_egreso = 0; 
 		} 

 		if ($compras) { 
 			foreach ($compras as $key => $compra) { 
 				if ($compra['plan_de_pagos'] == 'si'){
 					$pagos = $db->query("select id_pago, movimiento_id, pd.tipo_pago, ifnull(SUM(monto),0) as subtotal from inv_pagos p left join inv_pagos_detalles pd on p.id_pago= pd.pago_id where p.movimiento_id = '". $compra['id_ingreso'] . "' AND p.tipo='Ingreso' AND pd.estado='1' GROUP by movimiento_id")->fetch_first(); 
 					if ($pagos){ 
 						if ($pagos['tipo_pago'] == 'Efectivo') 
 							$total_compra = $total_compra + $pagos['subtotal']; 							
 						else
 							$total_compra_banco = $total_compra_banco + $pagos['monto_total']; 	
 					}else{}																														
 				}else{ 
 					if ($compra['tipo_pago'] == 'Efectivo')
 						$total_compra = $total_compra + $compra['monto_total']; 													
 					else
 						$total_venta_banco = $total_compra_banco + $compra['monto_total']; 											
 				} 	
 			} 														
 			$total_total_compra = (($total_compra_banco + $total_compra )) ? number_format(($total_compra_banco + $total_compra), 2, '.', ''): number_format(0, 2, '.', ''); 	
 		} else { 
 			$total_compra = 0; 
 		} 

 		if ($gastos) {
 			foreach ($gastos as $nro => $gasto) {
 				$total_gasto = $total_gasto + $gasto['monto']; 																	
 			}
 			$total_total_gasto = (($total_gasto_banco + $total_gasto )) ? number_format(($total_gasto_banco + $total_gasto), 2, '.', ''): number_format(0, 2, '.', ''); 
 		}else { 
 			$total_gasto = 0; 
 		}

 		$total_de_totales_ingreso = number_format(($total_ingresos + $total_venta) , 2, '.', '');
 		$total_de_totales_egreso = number_format(($total_egresos + $total_compra + $total_gasto) , 2, '.', '');
 		$total_de_totales_saldo = number_format(($total_ingresos + $total_venta) - ($total_egresos + $total_compra + $total_gasto) , 2, '.', '');	 

 		$fecha_n = '';
 		$fecha_n = $db->query("SELECT * FROM `inv_caja` WHERE fecha = (SELECT MAX(fecha) AS fecha FROM inv_caja) AND id_caja = (SELECT MAX(id_caja) AS fecha FROM inv_caja)")->fetch_first(); 	
 		
 		$fecha_ultimo_registro = strtotime(date_decode($fecha,$_institution['formato']));
 		if ($fecha_ultimo_registro <= $fecha_actual) {
 			$estado = 'CAJA';
 			$total_de_totales = number_format(($total_de_totales_ingreso + $fecha_n['total_total']) - $total_de_totales_egreso , 2, '.', '');	
 			$datos = array('fecha' => $fecha,
 				'hora_caja' => date('H:i:s'),
 				'total_ingresos' => $total_de_totales_ingreso,
 				'total_egresos' => $total_de_totales_egreso,
 				'total_saldo' => $fecha_n['total_total'],
 				'total_total' => $total_de_totales,
 				'estado' => $estado);
 			$cierre_caja = $db->insert('inv_caja',$datos);
 		}else{
 			$sw = 1;
 		}

 		if ($fecha_ultimo_registro == $fecha_actual && $cerrar == 'cerrar') {
 			$estado = 'CIERRE';
 			$total_de_totales = number_format(($total_de_totales_ingreso + $fecha_n['total_total']) - $total_de_totales_egreso , 2, '.', '');	
 			$datos = array('fecha' => $fecha,
 				'hora_caja' => date('H:i:s'),
 				'total_ingresos' => $total_de_totales_ingreso,
 				'total_egresos' => $total_de_totales_egreso,
 				'total_saldo' => $fecha_n['total_total'],
 				'total_total' => $total_de_totales,
 				'estado' => $estado);
 			$cierre_caja = $db->insert('inv_caja',$datos);
 			$sw = 1;
 		}

 		$fecha_ultimo_registro = '';
 		$fecha_n = $db->query("SELECT * FROM `inv_caja` WHERE fecha = (SELECT MAX(fecha) AS fecha FROM inv_caja) AND id_caja = (SELECT MAX(id_caja) AS fecha FROM inv_caja)")->fetch_first(); 
 		$fecha_ultimo_registro = strtotime(date_decode($fecha_n['fecha'],$_institution['formato']));
 	} 
 }



 redirect('?/movimientos/caja');

 ?>
