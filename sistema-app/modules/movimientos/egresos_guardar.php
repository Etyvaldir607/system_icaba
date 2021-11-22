<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */
// Verifica si es una peticion post
if (is_ajax() || is_post()) { //&& is_post()
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_sucursal']) && isset($_POST['id_movimiento']) && isset($_POST['fecha_movimiento']) && isset($_POST['hora_movimiento']) && isset($_POST['nro_comprobante']) && isset($_POST['concepto']) && isset($_POST['monto'])) {
// var_dump('lleg�� a guardar'); die();
		
		// Obtiene las datos del egreso
		$id_movimiento = trim($_POST['id_movimiento']);
		$fecha_movimiento = trim($_POST['fecha_movimiento']);
		$hora_movimiento = trim($_POST['hora_movimiento']);
		$nro_comprobante = trim($_POST['nro_comprobante']);
		$concepto = trim($_POST['concepto']);
		$id_empleado_a = trim($_POST['id_empleado_a']);
		$id_empleado_r = trim($_POST['id_empleado_r']);
		$monto = trim($_POST['monto']);
		$observacion = trim($_POST['observacion']);
		$id_sucursal = trim($_POST['id_sucursal']);
		
		// Instancia el egreso
		$egreso = array(
			'fecha_movimiento' => date_encode($fecha_movimiento),
			'hora_movimiento' => $hora_movimiento,
			'nro_comprobante' => $nro_comprobante,
			'tipo' => 'e',
			'concepto' => $concepto,
			'monto' => $monto,
			'observacion' => $observacion,
			'empleado_id' => $id_empleado_a,
			'recibido_por' => $id_empleado_r,
			'sucursal_id' => $id_sucursal
		);
		
		// Verifica si es creacion o modificacion
		if ($id_movimiento > 0) {
			// Genera la condicion
			$condicion = array('id_movimiento' => $id_movimiento);
			
			// Actualiza la informacion
			$db->where($condicion)->update('caj_movimientos', $egreso);
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Actualización satisfactoria!',
				'message' => 'El registro se actualizó correctamente.'
			);
			redirect('?/movimientos/egresos_listar');
// 			echo json_encode($id_movimiento);
		} else {
			
			// Guarda la informacion
			$movimiento_id = $db->insert('caj_movimientos', $egreso);
// 			var_dump($movimiento_id);die();
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Adición satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);
            echo json_encode($movimiento_id);
		}
		
		// Redirecciona a la pagina principal
// 		redirect('?/movimientos/egresos_listar');
        
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