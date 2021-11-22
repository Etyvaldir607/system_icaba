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
	if (isset($_POST['id_categoria_cliente']) && isset($_POST['categoria']) && isset($_POST['descripcion'])) {
		// Obtiene las datos de la categoría
		$id_categoria_cliente = trim($_POST['id_categoria_cliente']);
		$categoria = trim($_POST['categoria']);
		$descripcion = trim($_POST['descripcion']);
		
		// Instancia la categoría
		$categoria = array(
			'categoria' => $categoria,
			'descripcion' => $descripcion
		);
		
		// Verifica si es creacion o modificacion
		if ($id_categoria_cliente > 0) {
			// Genera la condicion
			$condicion = array('id_categoria_cliente' => $id_categoria_cliente);
			
			// Actualiza la informacion
			$db->where($condicion)->update('inv_categorias_cliente', $categoria);
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Actualización satisfactoria!',
				'message' => 'El registro se actualizó correctamente.'
			);
		} else {
			// Guarda la informacion
			$db->insert('inv_categorias_cliente', $categoria);
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Adición satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);
		}
		
		// Redirecciona a la pagina principal
		redirect('?/categorias_cliente/listar');
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