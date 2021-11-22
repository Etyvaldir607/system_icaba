<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_cliente
$id_cliente = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el cliente
$cliente = $db->from('inv_clientes')->where('id_cliente', $id_cliente)->fetch_first();

// Verifica si el cliente existe
if ($cliente) {
	// Obtiene el nombre de la imagen
	$imagen = $cliente['imagen'];

	// Verifica si esta almacenada el avatar en la base de datos
	if ($imagen != '') {
		// Verifica si el avatar esta almacenada en la carpeta de profiles
		if (file_exists(files . '/clientes/' . $imagen)) {
			// Elimina el archivo
			unlink(files . '/clientes/' . $imagen);
		}
	}

	// Elimina el imagen del cliente
	$db->where('id_cliente', $id_cliente)->update('inv_clientes', array('imagen' => ''));

	// Define el mensaje de exito
	$_SESSION[temporary] = array(
		'alert' => 'success',
		'title' => 'Eliminación satisfactoria!',
		'message' => 'La imagen del cliente fue eliminada correctamente.'
	);

	// Redirecciona a la pagina principal
	redirect('?/clientes/modificar/' . $id_cliente);
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>