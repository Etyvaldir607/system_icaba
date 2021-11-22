<?php
/**
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
*/

// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['ix']) && isset($_POST['tipo']) && isset($_POST['estado']) && isset($_POST['fecha'])) {
		// Obtiene los datos del producto
		$fecha = trim($_POST['fecha']);
		$fecha_format=((isset($fecha)) ? $fecha: "00-00-0000");
		$fecha_format=date_encode($fecha_format)

		$ip = trim($_POST['ip']);
		$id = trim($_POST['ix']);
		$tipo = trim($_POST['tipo']);
		$estado = trim($_POST['estado']);
		
		// Instancia el ingreso
		$ingreso = array(
			'tipo_pago' => $tipo,
			'fecha_pago' => $fecha_format,
			'estado' => $estado			
		);

		$condicion = array('id_pago_detalle' => $id);			
		$db->where($condicion)->update('inv_pagos_detalles', $ingreso);

		// Redirecciona a la pagina principal
		redirect('?/ingresos/ver/'.$ip);
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