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
	if (isset($_POST['ix']) && isset($_POST['tipo']) && isset($_POST['estado']) && isset($_POST['fecha']) && isset($_POST['monto'])) {
		// Obtiene los datos del producto
		$fecha = trim($_POST['fecha']);
		$fecha_format=((isset($fecha)) ? $fecha: "00-00-0000");
		$vfecha=explode("-",$fecha_format);
		$fecha_format=$vfecha[2]."-".$vfecha[1]."-".$vfecha[0];

		$id = trim($_POST['ix']);
		$tipo = trim($_POST['tipo']);
		$estado = trim($_POST['estado']);
		$monto = trim($_POST['monto']);
		$id_cr= trim($_POST['id_cr']);
		
		// Instancia el ingreso
		$ingreso = array(
			'tipo_pago' => $tipo,
			'fecha_pago' => $fecha_format,
			'monto' => $monto,
			'estado' => $estado			
		);

		$condicion = array('id_cronograma_cuentas' => $id);			
		$db->where($condicion)->update('cronograma_cuentas', $ingreso);

		// Redirecciona a la pagina principal
		redirect('?/cronograma/listar/'.$id_cr);
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