<?php
if (isset($_POST['producto']) && isset($_POST['almacen']) && isset($_POST['stock']) && isset($_POST['stock_nuevo'])) {

	// Obtiene los parametros
	$id_almacen 	= (isset($_POST['almacen'])) ? $_POST['almacen'] : 0;
	$id_producto 	= (isset($_POST['producto'])) ? $_POST['producto'] : 0;
	$stock 			= $_POST['stock'];
	$stock_nuevo 	= $_POST['stock_nuevo']; 

	// Obtiene el almacen
	$almacen 	= $db->from('inv_almacenes')->where('id_almacen', $id_almacen)->fetch_first();
	$producto 	= $db->query("SELECT * FROM inv_productos WHERE id_producto = ".$id_producto)->fetch_first();
	$asignacion = $db->query("SELECT * FROM inv_asignaciones WHERE tipo = 'principal' AND producto_id = ".$id_producto)->fetch_first();
	
	if (true){ //$stock_nuevo-$stock != 0
		if ($stock_nuevo > $stock){
			$cantidad = $stock_nuevo-$stock;
			
			// var_dump($stock_nuevo.' - '.$stock.' = '.$cantidad);die();
			$ingreso = array(
				'fecha_ingreso' 	=> date('Y-m-d'),
				'hora_ingreso' 		=> date('H:i:s'),
				'tipo' 				=> 'Ajuste',
				'descripcion' 		=> 'Ingreso por ajuste de inventario',
				'monto_total' 		=> 0,
				
				'nombre_proveedor' 	=> '',
				'nro_registros' 	=> 1,
				'almacen_id' 		=> $id_almacen,
				'plan_de_pagos' 	=> 'no',
				'empleado_id' 		=> $_user['persona_id'],
				
				'responsable_id'	=> $_user['persona_id'],
				'proveedor_id' 		=> 0
			);

			// Guarda la informacion
			$ingreso_id = $db->insert('inv_ingresos', $ingreso);

			$detalle = array(
				'cantidad' 		=> $cantidad,
				'costo' 		=> 0,
				'asignacion_id' => $asignacion['id_asignacion'],
				'producto_id' 	=> $id_producto,
				'ingreso_id' 	=> $ingreso_id
			);
		
			// Guarda la informacion
			$db->insert('inv_ingresos_detalles', $detalle);
			
			$ajustar = array(
			    'producto_id'   => $id_producto,
			    'almacen_id'    => $id_almacen
			);

			$db->insert('tmp_ajustar', $ajustar);
			
		} else {
			$cantidad = $stock-$stock_nuevo;
			// var_dump($stock.' - '.$stock_nuevo.' = '.$cantidad);die();
			$egreso = array(
			 	'fecha_egreso' 		=> date('Y-m-d'),
				'hora_egreso' 		=> date('H:i:s'),
				'tipo' 				=> 'Ajuste',
				'provisionado' 		=> 'N',
				'descripcion' 		=> 'Egreso por ajuste de inventario',
				
				'nro_factura' 		=> 0,
				'nro_autorizacion' 	=> 0,
				'codigo_control' 	=> '',
				'fecha_limite' 		=> '0000-00-00',
				'monto_total' 		=> 0,
				
				'nombre_cliente' 	=> '',
				'nit_ci' 			=> 0,
				'nro_registros' 	=> 1,
				'dosificacion_id' 	=> 0,
				'almacen_id' 		=> $id_almacen,
				
			 	'empleado_id' 		=> $_user['persona_id'],
				'plan_de_pagos' 	=> 'no',
			 	'telefono' 			=> '',
			 	'direccion' 		=> '',
			 	'observacion' 		=> '',
			 	
			 	'descuento' 		=> 0,
			 	'estado' 			=> 'V',
			 	'tipo_de_pago' 		=> '',
			 	'sucursal_id' 		=> '0',
			 	'responsable_id' 	=> $_user['persona_id'],
				
			 	'conductor_id'		=> 0,
			 	'cliente_id'		=> 0 
			);

			// Guarda la informacion
			$egreso_id = $db->insert('inv_egresos', $egreso);

			$detalle = array(
				'cantidad' 		=> $cantidad,
				'precio' 		=> 0,
				'asignacion_id' => $asignacion['id_asignacion'],
				'descuento' 	=> 0,
				'producto_id' 	=> $id_producto,
				'egreso_id' 	=> $egreso_id
			);

			// Guarda la informacion
			$db->insert('inv_egresos_detalles', $detalle);
			
			$ajustar = array(
			    'producto_id'   =>$id_producto,
			    'almacen_id'    => $id_almacen
			);
			$db->insert('tmp_ajustar', $ajustar);
		}
		echo json_encode(['success'=>true,'producto'=>$id_producto,'almacen'=>$id_almacen,'stock'=>$stock_nuevo]);
	} else {
		echo json_encode(['success'=>false,'msg'=>'Es igual a cero']);
	}
	
} else {
	echo json_encode(['success'=>false]);
}

	
?>