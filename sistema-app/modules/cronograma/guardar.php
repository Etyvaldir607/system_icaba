<?php
/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['detalle']) && isset($_POST['periodo']) && isset($_POST['fecha']) && isset($_POST['monto'])) {
		// Obtiene los datos del producto
		$fecha = trim($_POST['fecha']);
		$fecha_format=((isset($fecha)) ? $fecha: "00-00-0000");
		$vfecha=explode("-",$fecha_format);
		$fecha_format=$vfecha[2]."-".$vfecha[1]."-".$vfecha[0];
		//$ff = trim($_POST['fecha']);
		$dd = trim($_POST['detalle']);
		$pp = trim($_POST['periodo']);
		$monto = trim($_POST['monto']);
		
		// Instancia el ingreso
		$ingreso = array(
			'detalle' => $dd,
			'fecha' => $fecha_format,
			'monto' => $monto,
			'periodo' => $pp			
		);

		// Guarda la informacion
		$db->insert('cronograma', $ingreso);
			
		// Redirecciona a la pagina principal
		redirect('?/cronograma/listar');
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