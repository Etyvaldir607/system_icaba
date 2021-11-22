<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_movimiento
$id_movimiento = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el gasto
$gasto = $db->from('caj_movimientos')->where('id_movimiento', $id_movimiento)->fetch_first();

// Verifica si el gasto existe
if ($gasto) {
	// Elimina el gasto
	$db->delete()->from('caj_movimientos')->where('id_movimiento', $id_movimiento)->limit(1)->execute();

	// Verifica si fue el gasto eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/movimientos/gastos_listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>