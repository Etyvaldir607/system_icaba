<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */
// var_dump('llegó a guardar'); die();
// Verifica si es una peticion post
if (is_ajax() || is_post()) { //&& is_post()
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_movimiento']) && isset($_POST['fecha_movimiento']) && isset($_POST['hora_movimiento']) && isset($_POST['nro_comprobante']) && isset($_POST['concepto']) && isset($_POST['monto'])) {
		
		// Obtiene las datos del gasto
		$id_movimiento = trim($_POST['id_movimiento']);
		$fecha_movimiento = trim($_POST['fecha_movimiento']);
		$hora_movimiento = trim($_POST['hora_movimiento']);
		$nro_comprobante = trim($_POST['nro_comprobante']);
		$concepto = trim($_POST['concepto']);
		$id_empleado_a = trim($_POST['id_empleado_a']);
		$id_empleado_r = trim($_POST['id_empleado_r']);
		$monto = trim($_POST['monto']);
		$observacion = trim($_POST['observacion']);
		
		// Instancia el gasto
		$gasto = array(
			'fecha_movimiento' => date_encode($fecha_movimiento),
			'hora_movimiento' => $hora_movimiento,
			'nro_comprobante' => $nro_comprobante,
			'tipo' => 'g',
			'concepto' => $concepto,
			'monto' => $monto,
			'observacion' => $observacion,
			'empleado_id' => $id_empleado_a,
			'recibido_por' => $id_empleado_r
		);
		
		// Verifica si es creacion o modificacion
		if ($id_movimiento > 0) {
			// Genera la condicion
			$condicion = array('id_movimiento' => $id_movimiento);
			
			// Actualiza la informacion
			$db->where($condicion)->update('caj_movimientos', $gasto);
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Actualizaci贸n satisfactoria!',
				'message' => 'El registro se actualiz贸 correctamente.'
			);
			redirect('?/movimientos/gastos_listar');
// 			echo json_encode($id_movimiento);
		} else {
			
			// Guarda la informacion
			$movimiento_id = $db->insert('caj_movimientos', $gasto);
// 			var_dump($movimiento_id);die();
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Adici贸n satisfactoria!',
				'message' => 'El registro se guard贸 correctamente.'
			);
            echo json_encode($movimiento_id);
		}
		
		// Redirecciona a la pagina principal
// 		redirect('?/movimientos/gastos_listar');
        
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