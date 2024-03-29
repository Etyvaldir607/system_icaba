<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion ajax
if (is_ajax()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_producto']) && isset($_POST['precio']) ) {
		// Obtiene los datos del producto
		$id_producto = trim($_POST['id_producto']);
		$precio = trim($_POST['precio']);
		$unidad=$_POST['unidad_id'];

		// Instancia el producto
		$producto = array(
			'precio' => $precio,
			'fecha_registro' => date('Y-m-d'),
			'hora_registro' => date('H:i:s'),
			'producto_id' => $id_producto,
			'empleado_id' => $_user['persona_id']
		);
		
		// Guarda la informacion
		$db->insert('inv_precios', $producto);


		
		// Actualiza la informacion
		$db->where('id_producto', $id_producto)->update('inv_productos', array('precio_actual' => $precio));
		
		// Envia respuesta
		echo json_encode($producto);
	} else {
		// Envia respuesta
		echo 'error';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>