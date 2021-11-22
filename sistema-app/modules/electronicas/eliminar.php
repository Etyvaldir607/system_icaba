<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_egreso
$id_egreso = (isset($params[0])) ? $params[0] : 0;

// Obtiene el nota
$nota = $db->from('inv_egresos')->where('tipo', 'Venta')->where('codigo_control', '')->where('provisionado', 'S')->where('id_egreso', $id_egreso)->fetch_first();

// Verifica si el nota existe
if ($nota) {
	// Elimina el nota
	$db->delete()->from('inv_egresos')->where('id_egreso', $id_egreso)->limit(1)->execute();

	// Elimina los detalles
	$db->delete()->from('inv_egresos_detalles')->where('egreso_id', $id_egreso)->execute();

	// Verifica si fue el nota eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'La orden de compra y todo su detalle fueron eliminados correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect(back());
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>