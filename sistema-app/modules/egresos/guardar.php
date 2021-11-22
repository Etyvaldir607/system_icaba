<?php

// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (//isset($_POST['almacen_id']) && 
		isset($_POST['tipo']) && 
		isset($_POST['descripcion']) && 
		isset($_POST['usuario']) && 
		isset($_POST['responsable']) &&
		isset($_POST['conductor']) &&
		//isset($_POST['responsable_ingreso']) &&
		isset($_POST['productos']) && 
		isset($_POST['nombres']) && 
		isset($_POST['cantidades']) && 
		isset($_POST['precios']) && 
		isset($_POST['nro_registros']) && 
		isset($_POST['monto_total'])
	) {
		// Obtiene los datos de la venta
		$id_egreso 			= isset($_POST['id_egreso']) ? trim($_POST['id_egreso']) : 0;
		$almacen_id 		= trim($_POST['almacen_id']);
		
		$tipo 				= trim($_POST['tipo']);
		$descripcion		= trim($_POST['descripcion']);
		$empleado 			= trim($_POST['usuario']);
		$responsable 		= trim($_POST['responsable']);
		$conductor 			= trim($_POST['conductor']);
		$responsable_ingreso= trim($_POST['responsable_ingreso']);
		$detalles 			= (isset($_POST['detalles'])) ? $_POST['detalles']: array();
		$productos 			= (isset($_POST['productos'])) ? $_POST['productos']: array();
		$asignaciones   	= (isset($_POST['asignacion'])) ? $_POST['asignacion']: array();
		$nombres 			= (isset($_POST['nombres'])) ? $_POST['nombres']: array();
		$cantidades 		= (isset($_POST['cantidades'])) ? $_POST['cantidades']: array();
		$precios 			= (isset($_POST['precios'])) ? $_POST['precios']: array();
		$nro_registros 		= trim($_POST['nro_registros']);
		$monto_total 		= trim($_POST['monto_total']);

		$fecha = date('Y-m-d');
        

		if ($id_egreso > 0) {
			// Elimina el detalle de egreso
			$db->delete()->from('inv_egresos_detalles')->where('egreso_id', $id_egreso)->execute();

			$condicion = array('id_egreso' => $id_egreso);
			
			$db->where($condicion)->update('inv_egresos', array('monto_total' => $monto_total));

			$ingresos = $db->query("SELECT *
                                	FROM inv_egresos, inv_ingresos
                                	WHERE fecha_ingreso=fecha_egreso AND hora_ingreso=hora_egreso AND id_egreso='$id_egreso'
                                	")->fetch_first();
			
			if ($tipo == 'Traspaso') {	            
	  			// Elimina el detalle de egreso
				$db->delete()->from('inv_ingresos_detalles')->where('ingreso_id', $ingresos['id_ingreso'])->execute();

				$datos = array(
	                'monto_total' => $monto_total
	            );
	            // Guarda la informacion
				$condicion = array('id_ingreso' => $ingresos['id_ingreso']);
	            $db->where($condicion)->update('inv_ingresos', $datos);

	            foreach ($productos as $nro => $elemento) {
	                $detalle = array(
	                    'cantidad' => (isset($cantidades[$nro])) ? $cantidades[$nro]: 0,
	                    'costo' => (isset($precios[$nro])) ? $precios[$nro]: 0,
	                    'producto_id' => $productos[$nro],
	       				'asignacion_id' => $asignaciones[$nro],					
	                    'ingreso_id' => $ingresos['id_ingreso']
	                );
	                $db->insert('inv_ingresos_detalles', $detalle);
	            }
	        }
			
			// Recorre los productos
			foreach ($productos as $nro => $elemento) {
				// Forma el detalle
				$detalle = array(
					'cantidad' => $cantidades[$nro],
					'precio' => $precios[$nro],
					'asignacion_id' => $asignaciones[$nro],
					'descuento' => 0,
					'producto_id' => $productos[$nro],
					'egreso_id' => $id_egreso
				);
				// Guarda la informacion
				$db->insert('inv_egresos_detalles', $detalle);
			}

			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Adición satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);

			$db->insert('his_egresos', array(
				'id_egreso' 	=> $id_egreso,
				'fecha' 		=> $fecha,
				'hora' 			=> date('H:i:s'),
				'tipo' 			=> 'Egreso',
				'descripcion' 	=> 'Se modificó la salida con identificador '.$id_egreso,
				'empleado_id' 	=> $_user['persona_id']
			));

			// Redirecciona a la pagina principal
			redirect('?/egresos/listar');
			//redirect('?/egresos/editar/'.$id_egreso);
		} else {
			
			// Instancia la venta
			$venta = array(
				'fecha_egreso' 		=> $fecha,
				'hora_egreso' 		=> date('H:i:s'),
				'tipo' 				=> $tipo,
				'provisionado' 		=> 'N',
				'descripcion' 		=> $descripcion,
				'nro_factura' 		=> 0,
				'nro_autorizacion' 	=> 0,
				'codigo_control' 	=> '',
				'fecha_limite' 		=> '0000-00-00',
				'monto_total' 		=> $monto_total,
				'nombre_cliente' 	=> '',
				'nit_ci' 			=> 0,
				'nro_registros' 	=> $nro_registros,
				'dosificacion_id' 	=> 0,
				'almacen_id' 		=> $almacen_id,
				'empleado_id' 		=> $empleado,
				'responsable_id' 	=> $responsable,
				'conductor_id'		=> $conductor,
				'cliente_id'		=> 0
				
			);

			// Guarda la informacion
			$egreso_id = $db->insert('inv_egresos', $venta);
			
			// Recorre los productos
			foreach ($productos as $nro => $elemento) {
				// Forma el detalle
				$detalle = array(
					'cantidad' => $cantidades[$nro],
					'precio' => $precios[$nro],
					'asignacion_id' => $asignaciones[$nro],
					'descuento' => 0,
					'producto_id' => $productos[$nro],
					'egreso_id' => $egreso_id
				);
				// Guarda la informacion
				$db->insert('inv_egresos_detalles', $detalle);
			}
			
			//realiza el ingreso en caso de traspaso ::BECA 
			if ($tipo == 'Traspaso') {
	            $otro_almacen = trim($_POST['almac']);
	            $ingreso = array(
	                'fecha_ingreso' 		=> $fecha,
	                'hora_ingreso' 			=> date('H:i:s'),
	                'tipo' 					=> 'Traspaso',
	                'descripcion' 			=> $descripcion,
	                'monto_total' 			=> $monto_total,
	                'nombre_proveedor' 		=> $almacen_id,
	                'proveedor_id'          => $egreso_id,// en proveedor_id se guardará el id del egreso para saber de dónde proviene ::BECA
	                'nro_registros' 		=> $nro_registros,
	                'almacen_id' 			=> $otro_almacen,
					'plan_de_pagos' 		=> 'no',
					'empleado_id' 			=> $empleado,
					'responsable_id'		=> $responsable_ingreso,

	            );
	            // Guarda la informacion
	            $ingreso_id = $db->insert('inv_ingresos', $ingreso);
                
	            foreach ($productos as $nro => $elemento) {
	                // Forma el detalle
	                $detalle = array(
	                    'cantidad' => (isset($cantidades[$nro])) ? $cantidades[$nro]: 0,
	                    'costo' => (isset($precios[$nro])) ? $precios[$nro]: 0,
	                    'producto_id' => $productos[$nro],
	       				'asignacion_id' => $asignaciones[$nro],					
	                    'ingreso_id' => $ingreso_id
	                );

	                // Guarda la informacion
	                $db->insert('inv_ingresos_detalles', $detalle);
	            }
				
				//guarda el estado transitorio y todos sus datos ::BECA
				$des_reserva = trim($_POST['des_reserva']);
				if ($_POST['reserva']) {
					$egreso_id_transitorio = $egreso_id;
					$reserva = 1;
				} else {
					$egreso_id_transitorio = 0;
					$reserva = 0;
				}
				
				$datos = array(
					'des_transitorio'       => $des_reserva,
					'egreso_id_transitorio' => $egreso_id_transitorio,
					'transitorio'=>$reserva
					
				);
				// Guarda la informacion
				$condicion = array('id_ingreso' => $ingreso_id);
				$db->where($condicion)->update('inv_ingresos', $datos);				
	        }
			

			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Adición satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);

			// Redirecciona a la pagina principal
			redirect('?/egresos/listar');
		}
	} else {
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