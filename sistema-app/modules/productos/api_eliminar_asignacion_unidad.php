<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_producto
$id_asignacion = (sizeof($params) > 0) ? $params[0] : 0;





// Obtiene el producto
$asignacion = $db->from('inv_asignaciones')->where('id_asignacion', $id_asignacion)->fetch_first();

// Verifica si el producto existe
if ($asignacion) {
	// Elimina el producto
	$db->delete()->from('inv_asignaciones')->where('id_asignacion', $id_asignacion)->limit(1)->execute();

	// Verifica si fue el producto eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		echo json_encode([
            "status" => 200, //status 100 informativo
            "title" => "Exito",
            "type" => "success",
            "messagge" => "La eliminacion se ha realizado correctamente"
        ]);
	}else {
        // Envia respuesta
        echo json_encode([
            "status" => 500, //status 100 informativo
            "title" => "Error",
            "type" => "danger",
            "messagge" => "Ocurrio un problema en la transaccion verifica si los datos se eliminaron parcialmente"
        ]);
    }
	
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>