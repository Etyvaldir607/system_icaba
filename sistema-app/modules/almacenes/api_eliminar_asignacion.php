<?php

/**
 * Consulta a la base de datos
 * 
 * @package  Simple API
 * @author   Erick Machicado <etyvaldirmc@gmail.com>
 */

// Obtiene el id_almacen
$empleado_almacen_id = (isset($params[0])) ? $params[0] : 0;

// Obtiene el almacén
$respuesta = $db->query("select count(ae.id) as existe from inv_almacen_empleados ae where ae.id = $empleado_almacen_id")->fetch_first();


// Verifica si el almacén existe
if ($respuesta['existe']) {
	
	// Elimina el almacén
	$db->delete()->from('inv_almacen_empleados')->where('id', $empleado_almacen_id)->limit(1)->execute();

	// Verifica si fue el almacén eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Exito!',
			'message' => 'Se removio la asignacion correctamente.'
		);
	}else{
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'danger',
			'title' => 'Error!',
			'message' => 'Upss! Ocurrio un problema en la transaccion comprueba que la asignacion se removio parcialmente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/almacenes/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>