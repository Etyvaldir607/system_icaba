<?php

/**
 * Consulta a la base de datos
 * 
 * @package  Simple API
 * @author   Erick Machicado <etyvaldirmc@gmail.com>
 */

// Verifica si es una peticion ajax
if (is_ajax()) {
	// Verifica la existencia de los datos enviados
	if (
		isset($_POST['id_sucursal']) && 
		isset($_POST['sucursal']) && $_POST['sucursal'] != null &&
		isset($_POST['direccion']) && $_POST['direccion'] != null &&
		isset($_POST['telefono']) && 
		isset($_POST['almacen']) && $_POST['almacen'] != null &&
		isset($_POST['descripcion'])
	) {
		// Obtiene los datos del almacén
		$id_sucursal = trim($_POST['id_sucursal']);
		$sucursal = trim($_POST['sucursal']);
		$direccion = trim($_POST['direccion']);
		$telefono = trim($_POST['telefono']);
		$almacen = trim($_POST['almacen']);
		$descripcion = trim($_POST['descripcion']);
		
		// Instancia el almacén
		$sucursal = array(
			'sucursal' => $sucursal,
			'direccion' => $direccion,
			'telefono' => $telefono,
			'almacen_id' => $almacen,
			'descripcion' => $descripcion
		);

		// Verifica si es creacion o modificacion
		if ($id_sucursal > 0) {
			// Genera la condicion
			$condicion = array('id_sucursal' => $id_sucursal);
			
			// Actualiza la informacion
			$db->where($condicion)->update('inv_sucursal', $sucursal);
			
			// Instancia la variable de notificacion
			$respuesta = array(
				'status' => 200,
				'alert' => 'success',
				'title' => '¡Actualización satisfactoria!',
				'message' => 'El registro se actualizó correctamente.'
			);
		} else {
			// Guarda la informacion
			$db->insert('inv_sucursal', $sucursal);
			
			// Instancia la variable de notificacion
			$respuesta = array(
				'status' => 200,
				'alert' => 'success',
				'title' => '¡Adición satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);
		}
		echo json_encode($respuesta);
	} else {
		// Instancia la variable de notificacion
		$respuesta = array(
			'status' => 202,
			'alert' => 'danger',
			'title' => '¡Algo salio mal!',
			'message' => 'No se logro procesar la solicitud.'
		);
		echo json_encode($respuesta);
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>