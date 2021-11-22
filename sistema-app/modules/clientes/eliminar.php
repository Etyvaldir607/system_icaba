<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_cliente
$id_cliente = (isset($params[0])) ? $params[0] : 0;

// Obtiene la cliente
$cliente = $db->from('inv_clientes')->where('id_cliente', $id_cliente)->fetch_first();

// Verifica si la cliente existe
if ($cliente) {
	// Elimina la cliente
	$db->delete()->from('inv_clientes')->where('id_cliente', $id_cliente)->limit(1)->execute();

	// Verifica si fue la cliente eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/clientes/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>