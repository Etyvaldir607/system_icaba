<?php

// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['almacen_id']) && 
		isset($_POST['tipo']) && 
		isset($_POST['descripcion']) && 
		isset($_POST['usuario']) && 
		isset($_POST['responsable']) &&
		isset($_POST['conductor']) &&
		isset($_POST['responsable_ingreso']) &&
		isset($_POST['productos']) && 
		isset($_POST['nombres']) && 
		isset($_POST['cantidades']) && 
		isset($_POST['precios']) && 
		isset($_POST['nro_registros']) && 
		isset($_POST['monto_total'])
	) {
		// Obtiene los datos de la venta
		$almacen_id 		= trim($_POST['almacen_id']);
		$tipo 				= trim($_POST['tipo']);
		$descripcion		= trim($_POST['descripcion']);
		$empleado 			= trim($_POST['usuario']);
		$responsable 		= trim($_POST['responsable']);
		$conductor 			= trim($_POST['conductor']);
		$responsable_ingreso= trim($_POST['responsable_ingreso']);
		$productos 			= (isset($_POST['productos'])) ? $_POST['productos']: array();
		$asignaciones   	= (isset($_POST['asignacion'])) ? $_POST['asignacion']: array();
		$nombres 			= (isset($_POST['nombres'])) ? $_POST['nombres']: array();
		$cantidades 		= (isset($_POST['cantidades'])) ? $_POST['cantidades']: array();
		$precios 			= (isset($_POST['precios'])) ? $_POST['precios']: array();
		$nro_registros 		= trim($_POST['nro_registros']);
		$monto_total 		= trim($_POST['monto_total']);

		if($tipo == 'Traspaso'){
            $otro_almacen = trim($_POST['almac']);
            $ingreso = array(
                'fecha_ingreso' 		=> date('Y-m-d'),
                'hora_ingreso' 			=> date('H:i:s'),
                'tipo' 					=> 'Traspaso',
                'descripcion' 			=> $descripcion,
                'monto_total' 			=> $monto_total,
                'nombre_proveedor' 		=> 'Almacen '.$almacen_id,
                'nro_registros' 		=> $nro_registros,
                'almacen_id' 			=> $otro_almacen,
				'plan_de_pagos' 		=> 'no',
				'empleado_id' 			=> $empleado,
				'responsable_id'		=> $responsable_ingreso
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
        }
		


		// Instancia la venta
		$venta = array(
			'fecha_egreso' 		=> date('Y-m-d'),
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

		// Instancia la variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Adición satisfactoria!',
			'message' => 'El registro se guardó correctamente.'
		);

		// Redirecciona a la pagina principal
		redirect('?/egresos/listar');
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