<?php

// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['almacen_id']) && 
		isset($_POST['nombre_proveedor']) && 
		isset($_POST['descripcion']) && 
		isset($_POST['usuario']) && 
		isset($_POST['responsable']) && 
		isset($_POST['nro_registros']) && 
		isset($_POST['monto_total']) && 
		isset($_POST['productos']) && 
		isset($_POST['cantidades']) && 
		isset($_POST['costos'])
	) {
		
		// Obtiene los datos del producto
		$id_compra 			= isset($_POST['id_compra']) ? trim($_POST['id_compra']) : 0;
		$almacen_id 		= trim($_POST['almacen_id']);
		$nombre_proveedor 	= trim($_POST['nombre_proveedor']);
		
		$vec_proveedor 		= explode("###", $nombre_proveedor);
		$id_proveedor 		= $vec_proveedor[0];
		$nombre_proveedor 	= $vec_proveedor[1];
		
		$descripcion 		= trim($_POST['descripcion']);
		$empleado 			= trim($_POST['usuario']);
		$responsable 		= trim($_POST['responsable']);
		$nro_registros 		= trim($_POST['nro_registros']);
		$monto_total 		= trim($_POST['monto_total']);
		$detalles 			= (isset($_POST['detalles'])) ? $_POST['detalles']: array();
		$productos 			= (isset($_POST['productos'])) ? $_POST['productos']: array();
		$asignaciones   	= (isset($_POST['asignacion'])) ? $_POST['asignacion']: array();
		$cantidades 		= (isset($_POST['cantidades'])) ? $_POST['cantidades']: array();
		$costos 			= (isset($_POST['costos'])) ? $_POST['costos']: array();
		$nro_cuentas 		= trim($_POST['nro_cuentas']);
		$plan 				= trim($_POST['forma_pago']);
		$plan 				= ($plan=="2") ? "si" : "no";

		$id_pago 		= (isset($_POST['id_pago'])) ? $_POST['id_pago'] : 0;
		$nro_cuentas 	= trim($_POST['nro_cuentas']);			

		if ($plan == "si") {
			$fechas = (isset($_POST['fecha'])) ? $_POST['fecha']: array();
			$cuotas = (isset($_POST['cuota'])) ? $_POST['cuota']: array();
		}
		$des_reserva = trim($_POST['des_reserva']);
		if ($_POST['reserva']) {
			$reserva = 1;
		} else {
			$reserva = 0;
		}

        // var_dump('prueba transitorio:  '.$des_reserva.' '.$reserva.' /prueba');die();
		// Obtiene el almacen
		$almacen = $db->from('inv_almacenes')->where('id_almacen', $almacen_id)->fetch_first();

		if($id_compra > 0) {
			$condicion = array('id_ingreso' => $id_compra);
			
			// Actualiza la informacion
			$db->where($condicion)->update('inv_ingresos', array(
				'nombre_proveedor' 	=> $nombre_proveedor,
				'proveedor_id' 		=> $id_proveedor,
				'monto_total' 		=> $monto_total
			));

			$db->delete()->from('inv_ingresos_detalles')->where('ingreso_id', $id_compra)->execute();
	
			foreach ($productos as $key => $elemento) {
					$db->insert('inv_ingresos_detalles', array(
						'cantidad' 		=> (isset($cantidades[$key])) ? $cantidades[$key]: 0,
						'costo' 		=> (isset($costos[$key])) ? $costos[$key]: 0,
						'asignacion_id' => $asignaciones[$key],
						'producto_id' 	=> $productos[$key],
						'ingreso_id' 	=> $id_compra
					));
					
			}

			$db->insert('his_ingresos', array(
				'id_ingreso' 	=> $id_compra,
				'fecha' 		=> date('Y-m-d'),
				'hora' 			=> date('H:i:s'),
				'descripcion' 	=> 'Se modificó la compra con identificador '.$id_compra,
				'empleado_id' 	=> $empleado
			));
			
			if($plan=="no"){
				$db->delete()->from('inv_pagos')->where(['id_pago' => $id_compra])->execute();
				$db->delete()->from('inv_pagos_detalles')->where(['pago_id' => $id_compra])->execute();
			}else{
				if($id_pago==0){
					$ingresoPlan = array(
						'movimiento_id' => $id_compra,
						'interes_pago' 	=> "0",
						'tipo' 			=> 'Ingreso'
					);
					$id_pago = $db->insert('inv_pagos', $ingresoPlan);
				}

				$queryy="";
				$nro_cuota=0;
				for($nro2=0; $nro2<$nro_cuentas; $nro2++) {
					$fecha_format=(isset($fechas[$nro2]) and $fechas[$nro2] != '') ? $fechas[$nro2]: "00-00-0000";
					$vfecha=explode("-",$fecha_format);
					
					$fecha_format=$vfecha[2]."-".$vfecha[1]."-".$vfecha[0];
					$nro_cuota++;

					$id_pago_detalle_x=(isset($id_pago_detalle[$nro2]))? $id_pago_detalle[$nro2]:0;
					
					$queryx = $db->query("	SELECT *
											FROM inv_pagos_detalles 
											WHERE id_pago_detalle = '$id_pago_detalle_x'")
									->fetch_first();

					$queryy .= " AND id_pago_detalle != '$id_pago_detalle_x'";

					if($queryx){
						$detallePlan = array(
								'nro_cuota' 	=> $nro_cuota,
								'pago_id' 		=> $id_pago,
								'fecha' 		=> $fecha_format,
								'monto' 		=> (isset($cuotas[$nro2])) ? $cuotas[$nro2]: 0, 	 	
							);
						$condicion = array('id_pago_detalle' => $id_pago_detalle_x);

						$db->where($condicion)->update('inv_pagos_detalles', $detallePlan);
					}
					else{
						if ($nro2 == 0){
							$detallePlan = array(
								'nro_cuota' 	=> $nro_cuota,
								'pago_id' 		=> $id_pago,
								'fecha' 		=> $fecha_format,
								'fecha_pago'	=> $fecha_format,
								'monto' 		=> (isset($cuotas[$nro2])) ? $cuotas[$nro2]: 0, 	 	
								'tipo_pago' 	=> $tipo_pago,
								'empleado_id'	=> $empleado,
								'estado'  		=> '1'
							);
						}else{
							$detallePlan = array(
								'nro_cuota' 	=> $nro_cuota,
								'pago_id' 		=> $id_pago,
								'fecha' 		=> $fecha_format, 	
								'fecha_pago' 	=> '00-00-0000',
								'monto' 		=> (isset($cuotas[$nro2])) ? $cuotas[$nro2]: 0, 	 	
								'tipo_pago' 	=> '',
								'empleado_id' 	=> '0',
								'estado'  		=> '0'
							);
						}
						// Guarda la informacion
						$id_pago_detalle_x=$db->insert('inv_pagos_detalles', $detallePlan);
						$queryy .= " AND id_pago_detalle != '$id_pago_detalle_x'";
					}
				}

				$queryy .= " AND id_pago_detalle != '$id_pago_detalle_x'";

				$db->query("	DELETE FROM inv_pagos_detalles  
								WHERE pago_id = '$id_pago' ".$queryy)->execute();

				
				$forma_de_pago="FORMA DE PAGO: Pago por cuotas";
			}
			




			// Redirecciona a la pagina principal
			redirect('?/ingresos/editar/'.$id_compra);
		} else {
			// Instancia el ingreso
			$ingreso = array(
				'fecha_ingreso' 	=> date('Y-m-d'),
				'hora_ingreso' 		=> date('H:i:s'),
				'tipo' 				=> 'Compra',
				'descripcion' 		=> $descripcion,
				'monto_total' 		=> $monto_total,
				'nombre_proveedor' 	=> $nombre_proveedor,
				'nro_registros' 	=> $nro_registros,
				'almacen_id' 		=> $almacen_id,
				'plan_de_pagos' 	=> $plan,
				'empleado_id' 		=> $empleado,
				'proveedor_id' 		=> $id_proveedor,
				'responsable_id'	=> $responsable,
				'transitorio'       => $reserva,
			    'des_transitorio'   => $des_reserva
			);

			// Guarda la informacion
			$ingreso_id = $db->insert('inv_ingresos', $ingreso);

			foreach ($productos as $nro => $elemento) {
				// Forma el detalle
				$detalle = array(
					'cantidad' 		=> (isset($cantidades[$nro])) ? $cantidades[$nro]: 0,
					'costo' 		=> (isset($costos[$nro])) ? $costos[$nro]: 0,
					'asignacion_id' => $asignaciones[$nro],
					'producto_id' 	=> $productos[$nro],
					'ingreso_id' 	=> $ingreso_id
				);

				// Guarda la informacion
				$db->insert('inv_ingresos_detalles', $detalle);

				$actualizar_precio = $db->query("UPDATE inv_asignaciones 
												 SET costo_actual = $costos[$nro]
												 WHERE id_asignacion = $asignaciones[$nro]")->execute();

				//instacia de precio
	            $costo = array(
	                'costo' 		=> $costos[$nro],
	                'fecha_registro'=> date('Y-m-d'),
	                'hora_registro' => date('H:i:s'),
	                'asignacion_id' => $asignaciones[$nro],
	                'empleado_id' 	=> $empleado
	            );

	            // Crea el costo
	            $id_costo = $db->insert('inv_precios_entrada', $costo);
			}

			if($plan=="si"){
				// Instancia el ingreso
				$ingresoPlan = array(
					'movimiento_id' => $ingreso_id,
					'tipo' 			=> 'Ingreso'
				);
				
				// Guarda la informacion del ingreso general
				$ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);
						
				$nro_cuota=0;
				for($nro2=0; $nro2<$nro_cuentas; $nro2++) {
					$fecha_format=(isset($fechas[$nro2])) ? $fechas[$nro2]: "00-00-0000";
					$vfecha=explode("-",$fecha_format);
					$fecha_format=$vfecha[2]."-".$vfecha[1]."-".$vfecha[0];
					
					$nro_cuota++;
					

					if($nro2==0){
						$detallePlan = array(
							'nro_cuota' => $nro_cuota,
							'pago_id' 	=> $ingreso_id_plan,
							'fecha' 	=> $fecha_format, 	
							'fecha_pago'=> $fecha_format,
							'monto' 	=> (isset($cuotas[$nro2])) ? $cuotas[$nro2]: 0, 	 	
							'empleado_id'	=> $empleado,
							'tipo_pago' => "Efectivo",
							'estado'  	=> '1'
						);
					}else{
						$detallePlan = array(
							'nro_cuota' => $nro_cuota,
							'pago_id' 	=> $ingreso_id_plan,
							'fecha' 	=> $fecha_format, 	
							'monto' 	=> (isset($cuotas[$nro2])) ? $cuotas[$nro2]: 0, 	 	
							'estado'  	=> '0'
						);
					}
					// Guarda la informacion
					$db->insert('inv_pagos_detalles', $detallePlan);
				}
			}

			// Redirecciona a la pagina principal
			redirect('?/ingresos/listar');
		}
		
		
	}
	else{
		// Error 401
		require_once bad_request();
		exit;
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>