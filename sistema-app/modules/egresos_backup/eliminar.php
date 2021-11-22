<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_egreso
$id_egreso = (isset($params[0])) ? $params[0] : 0;

// Obtiene el egreso
$egreso = $db->from('inv_egresos')
			 ->where('id_egreso', $id_egreso)
			 ->fetch_first();

// Verifica si el egreso existe
if ($egreso) {
	// Elimina el egreso
	$db->delete()->from('inv_egresos')->where('id_egreso', $id_egreso)->limit(1)->execute();

	// Elimina los detalles
	$db->delete()->from('inv_egresos_detalles')->where('egreso_id', $id_egreso)->execute();

	// Verifica si fue el egreso eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El egreso y todo su detalle fueron eliminados correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/egresos/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>