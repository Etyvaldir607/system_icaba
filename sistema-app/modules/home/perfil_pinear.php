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
	if (isset($_POST['pin'])) {
		// Obtiene los datos del user
		$id_user = trim($_user['id_user']);
		$pin = trim($_POST['pin']);

		// Actualiza la informacion
		$db->where(array('id_user' => $id_user))->update('sys_users', array('pin' => $pin));

		// Define la variable para mostrar los cambios
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => '',
			'message' => 'El pin ha sido modificado correctamente.'
		);

		// Redirecciona a la pagina principal
		redirect('?/home/perfil_ver');
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