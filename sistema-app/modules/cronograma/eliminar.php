<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_empleado
$id_ext = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el empleado
$empleado = $db->from('cronograma')->where('id_cronograma', $id_ext)->fetch_first();

// Verifica si el empleado existe
if ($empleado) {
	// Elimina el empleado
	$db->delete()->from('cronograma')->where('id_cronograma', $id_ext)->limit(1)->execute();

	// Verifica si fue el empleado eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/cronograma/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>