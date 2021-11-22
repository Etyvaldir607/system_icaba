<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_usuario
$id_usuario = (isset($_user['id_user'])) ? $_user['id_user'] : 0;

// Obtiene el usuario
$usuario = $db->from('sys_users')->where('id_user', $id_usuario)->fetch_first();

// Verifica si el usuario existe
if ($usuario) {
	// Obtiene el nombre del avatar
	$avatar = $usuario['avatar'];

	// Verifica si esta almacenada el avatar en la base de datos
	if ($avatar != '') {
		// Verifica si el avatar esta almacenada en la carpeta de profiles
		if (file_exists(files . '/profiles/' . $avatar)) {
			// Elimina el archivo
			unlink(files . '/profiles/' . $avatar);
		}
	}

	// Elimina el avatar del usuario
	$db->where('id_user', $id_usuario)->update('sys_users', array('avatar' => ''));

	// Define el mensaje de exito
	$_SESSION[temporary] = array(
		'alert' => 'success',
		'title' => '',
		'message' => 'La imagen del usuario fue eliminada correctamente.'
	);

	// Redirecciona a la pagina principal
	redirect('?/home/perfil_ver');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>