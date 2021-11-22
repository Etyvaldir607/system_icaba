<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_almacen
$id_almacen = (isset($params[0])) ? $params[0] : 0;

// Obtiene el almacén
$almacen = $db->from('inv_almacenes')->where('id_almacen', $id_almacen)->fetch_first();


// Verifica si el almacén existe
if ($almacen) {
	
	$existe = $db->from('inv_egresos')->where('almacen_id', $almacen['id_almacen'])->fetch();
    if(count($existe) > 0){
        $_SESSION[temporary] = array(
    		'alert' => 'danger',
    		'title' => 'No se puede eliminar!',
    		'message' => 'El almacen ya cuenta con egresos registrados, no se puede eliminar.'
    	);
    	redirect('?/almacenes/listar'); 
    }
    
	// Elimina el almacén
	$db->delete()->from('inv_almacenes')->where('id_almacen', $id_almacen)->limit(1)->execute();

	// Verifica si fue el almacén eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
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